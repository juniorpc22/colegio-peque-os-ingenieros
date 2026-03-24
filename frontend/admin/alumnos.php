<?php
// frontend/admin/alumnos.php
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

// Funciones auxiliares
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
                                        <button class="btn btn-sm btn-outline-primary" onclick="editar('<?php echo $row['id']; ?>', '<?php echo $row['nombres']; ?>', '<?php echo $row['apellidos']; ?>', '<?php echo $row['dni']; ?>', '<?php echo $row['telefono_apoderado']; ?>', '<?php echo $row['id_grado_seccion']; ?>', '<?php echo $row['codigo_barra']; ?>')">
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

<script>
    // MEJORA: BUSCADOR EN TIEMPO REAL
    document.getElementById('buscador').addEventListener('keyup', function(){
        let valor = this.value.toLowerCase();
        let filas = document.querySelectorAll('.alumno-fila');
        
        filas.forEach(fila => {
            let texto = fila.innerText.toLowerCase();
            fila.style.display = texto.includes(valor) ? '' : 'none';
        });
    });

    // MEJORA: FILTRO POR NIVEL
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

    // Mantén tus funciones abrirModal, editar, guardarAlumno y eliminar tal cual las tienes.
</script>