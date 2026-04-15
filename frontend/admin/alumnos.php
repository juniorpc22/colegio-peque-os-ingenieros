<?php
// frontend/admin/alumnos.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- GUARDIÁN DE SEGURIDAD ---
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['SuperAdmin', 'Admin', 'Director'])) {
    header("Location: dashboard.php?error=acceso_denegado");
    exit(); 
}
// -----------------------------

include_once '../../config/auth.php'; 
include '../../config/db.php';
include '../includes/header_admin.php';

// --- LÓGICA DE ORDENAMIENTO ---
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'grado';
$dir   = isset($_GET['dir']) ? $_GET['dir'] : 'ASC'; 
$dir = ($dir == 'DESC') ? 'DESC' : 'ASC';

switch ($orden) {
    case 'apellido':
        $sql_order = "a.apellidos $dir, a.nombres $dir"; 
        break;
    case 'dni':
        $sql_order = "a.dni $dir"; 
        break;
    case 'grado':
    default:
        $sql_order = "gs.nivel $dir, gs.grado $dir, gs.seccion $dir, a.apellidos ASC"; 
        break;
}

// SQL CON JOIN Y CONTADORES RÁPIDOS
$sql = "SELECT a.*, gs.nivel, gs.grado as num_grado, gs.seccion 
        FROM alumnos a 
        LEFT JOIN grados_secciones gs ON a.id_grado_seccion = gs.id 
        ORDER BY " . $sql_order;
$resultado = $conn->query($sql);

function obtenerLink($columna, $ordenActual, $dirActual) {
    $nuevaDir = ($columna == $ordenActual && $dirActual == 'ASC') ? 'DESC' : 'ASC';
    return "?orden=$columna&dir=$nuevaDir";
}

function obtenerIcono($columna, $ordenActual, $dirActual, $tipo) {
    if ($columna != $ordenActual) return '<i class="bi bi-arrow-down-up opacity-25 ms-1"></i>';
    return ($dirActual == 'ASC') ? '<i class="bi bi-sort-down ms-1"></i>' : '<i class="bi bi-sort-up ms-1"></i>';
}
?>

<style>
    .img-avatar { width: 45px; height: 45px; object-fit: cover; border-radius: 10px; border: 2px solid #eee; transition: 0.3s; }
    .img-avatar:hover { transform: scale(1.2); z-index: 10; position: relative; }
    .th-sortable { cursor: pointer; text-decoration: none; color: inherit; display: flex; align-items: center; padding: 10px; }
    .th-sortable:hover { background-color: #f1f4f9; color: #0d6efd; }
    .active-sort { background-color: #e7f1ff; color: #0d6efd; font-weight: bold; border-bottom: 2px solid #0d6efd; }
    .search-box { border-radius: 20px; padding-left: 40px; }
    .search-icon { position: absolute; left: 15px; top: 10px; color: #aaa; }
</style>

<div class="container mt-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold text-dark mb-1">Gestión de Estudiantes</h3>
            <p class="text-muted small">Total registrados: <span class="badge bg-primary"><?php echo $resultado->num_rows; ?></span></p>
        </div>
        <div class="col-md-6 text-md-end">
            <button class="btn btn-outline-secondary me-2" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Imprimir Lista
            </button>
            <button class="btn btn-success shadow-sm" onclick="abrirModal()">
                <i class="bi bi-person-plus-fill me-2"></i>Nuevo Alumno
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-8 position-relative">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" id="buscador" class="form-control search-box" placeholder="Buscar por nombre, DNI o código de barras...">
                </div>
                <div class="col-md-4">
                    <select class="form-select border-radius-20" id="filtroNivel">
                        <option value="">Todos los niveles</option>
                        <option value="Primaria">Primaria</option>
                        <option value="Secundaria">Secundaria</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow border-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablaAlumnos">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 80px;">Perfil</th>
                        <th class="<?php echo ($orden == 'apellido') ? 'active-sort' : ''; ?>">
                            <a href="<?php echo obtenerLink('apellido', $orden, $dir); ?>" class="th-sortable">
                                Estudiante <?php echo obtenerIcono('apellido', $orden, $dir, 'alpha'); ?>
                            </a>
                        </th>
                        <th class="<?php echo ($orden == 'dni') ? 'active-sort' : ''; ?>">
                            <a href="<?php echo obtenerLink('dni', $orden, $dir); ?>" class="th-sortable">
                                DNI <?php echo obtenerIcono('dni', $orden, $dir, 'numeric'); ?>
                            </a>
                        </th>
                        <th class="text-center <?php echo ($orden == 'grado') ? 'active-sort' : ''; ?>">
                            <a href="<?php echo obtenerLink('grado', $orden, $dir); ?>" class="th-sortable justify-content-center">
                                Salón <?php echo obtenerIcono('grado', $orden, $dir, 'numeric'); ?>
                            </a>
                        </th>
                        <th class="text-center">Código Barra</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($resultado->num_rows > 0): ?>
                        <?php while($row = $resultado->fetch_assoc()): ?>
                            <?php 
                                $foto = !empty($row['foto']) ? $row['foto'] : 'default.jpg';
                                $nivel_clase = ($row['nivel'] == 'Primaria') ? 'bg-primary' : 'bg-dark';
                            ?>
                            <tr class="alumno-fila" data-nivel="<?php echo $row['nivel']; ?>">
                                <td class="ps-4">
                                    <img src="../../assets/fotos_alumnos/<?php echo $foto; ?>" class="img-avatar" onerror="this.src='../../assets/fotos_alumnos/default.jpg'">
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo $row['apellidos'] . ", " . $row['nombres']; ?></div>
                                    <div class="text-muted small">Cel: <?php echo $row['telefono_apoderado'] ?: 'No registrado'; ?></div>
                                </td>
                                <td><span class="text-muted"><?php echo $row['dni']; ?></span></td>
                                <td class="text-center">
                                    <span class="badge <?php echo $nivel_clase; ?> mb-1 d-block"><?php echo $row['nivel']; ?></span>
                                    <small class="fw-bold"><?php echo $row['num_grado'] . "° " . $row['seccion']; ?></small>
                                </td>
                                <td class="text-center">
                                    <code class="text-primary fw-bold"><?php echo $row['codigo_barra']; ?></code>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="../../backend/generar_carnet.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Imprimir Carnet">
                                            <i class="bi bi-card-heading"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editar('<?php echo $row['id']; ?>', '<?php echo addslashes($row['nombres']); ?>', '<?php echo addslashes($row['apellidos']); ?>', '<?php echo $row['dni']; ?>', '<?php echo $row['telefono_apoderado']; ?>', '<?php echo $row['nivel']; ?>', '<?php echo $row['num_grado']; ?>', '<?php echo $row['seccion']; ?>', '<?php echo $row['codigo_barra']; ?>')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="eliminar('<?php echo $row['id']; ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-5">No se encontraron estudiantes.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAlumno" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="tituloModal">Matricular Alumno</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAlumno" onsubmit="event.preventDefault(); guardarAlumno();">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="GUARDAR">
                    <input type="hidden" name="id" id="alumno_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Nombres</label>
                            <input type="text" name="nombres" id="nombres" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Apellidos</label>
                            <input type="text" name="apellidos" id="apellidos" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">DNI</label>
                            <input type="text" name="dni" id="dni" class="form-control" maxlength="8" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Teléfono Apoderado</label>
                            <input type="text" name="telefono_apoderado" id="telefono_apoderado" class="form-control" maxlength="9">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small text-primary">Código de Barras</label>
                            <input type="text" name="codigo_barra" id="codigo_barra" class="form-control border-primary" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold small">Selecciona el Salón</label>
                            <div class="row bg-light p-2 rounded mx-0 border">
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <select name="nivel" id="nivel" class="form-select form-select-sm" required>
                                        <option value="">Nivel...</option>
                                        <option value="Primaria">Primaria</option>
                                        <option value="Secundaria">Secundaria</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <select name="grado" id="grado" class="form-select form-select-sm" required>
                                        <option value="">Grado...</option>
                                        <option value="1">1°</option>
                                        <option value="2">2°</option>
                                        <option value="3">3°</option>
                                        <option value="4">4°</option>
                                        <option value="5">5°</option>
                                        <option value="6">6°</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select name="seccion" id="seccion" class="form-select form-select-sm" required>
                                        <option value="">Sección...</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                        <option value="U">Única</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold small">Foto (Opcional)</label>
                            <input type="file" name="foto" id="foto" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">Guardar Alumno</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // --- BUSCADOR Y FILTROS ---
    document.getElementById('buscador').addEventListener('keyup', function(){
        let valor = this.value.toLowerCase();
        let filas = document.querySelectorAll('.alumno-fila');
        filas.forEach(fila => {
            let texto = fila.innerText.toLowerCase();
            fila.style.display = texto.includes(valor) ? '' : 'none';
        });
    });

    document.getElementById('filtroNivel').addEventListener('change', function(){
        let nivelSel = this.value;
        let filas = document.querySelectorAll('.alumno-fila');
        filas.forEach(fila => {
            if(nivelSel === "") {
                fila.style.display = '';
            } else {
                fila.style.display = (fila.getAttribute('data-nivel') === nivelSel) ? '' : 'none';
            }
        });
    });

    // --- FUNCIONES DEL MODAL Y CRUD ---
    function abrirModal() {
        document.getElementById('formAlumno').reset();
        document.getElementById('alumno_id').value = '';
        document.getElementById('tituloModal').innerText = 'Matricular Alumno';
        new bootstrap.Modal(document.getElementById('modalAlumno')).show();
    }

    // MODIFICADO: Recibe los 3 parámetros del salón y los coloca en los <select>
    function editar(id, nombres, apellidos, dni, telefono, nivel, grado, seccion, codigo_barra) {
        document.getElementById('alumno_id').value = id;
        document.getElementById('nombres').value = nombres;
        document.getElementById('apellidos').value = apellidos;
        document.getElementById('dni').value = dni;
        document.getElementById('telefono_apoderado').value = telefono;
        
        document.getElementById('nivel').value = nivel;
        document.getElementById('grado').value = grado;
        document.getElementById('seccion').value = seccion;
        
        document.getElementById('codigo_barra').value = codigo_barra;
        
        document.getElementById('tituloModal').innerText = 'Editar Alumno';
        new bootstrap.Modal(document.getElementById('modalAlumno')).show();
    }

    function guardarAlumno() {
        let formData = new FormData(document.getElementById('formAlumno'));
        fetch('../../backend/gestion_alumnos.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                alert(data.msg);
                location.reload();
            } else {
                alert(data.msg); // Aquí mostrará si el salón no existe
            }
        }).catch(err => {
            console.error(err);
            alert('Error de conexión al guardar.');
        });
    }

    function eliminar(id) {
        if(confirm('¿Estás seguro de eliminar a este estudiante? Se borrará también su historial de asistencia.')) {
            let formData = new FormData();
            formData.append('accion', 'ELIMINAR');
            formData.append('id', id);
            
            fetch('../../backend/gestion_alumnos.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert(data.msg);
                    location.reload();
                } else {
                    alert('Error: ' + data.msg);
                }
            }).catch(err => {
                console.error(err);
                alert('Error de red al eliminar.');
            });
        }
    }
</script>
</body>
</html>