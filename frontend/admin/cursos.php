<?php
// frontend/admin/cursos.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- GUARDIÁN DE SEGURIDAD ---
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['SuperAdmin', 'Admin'])) {
    header("Location: dashboard.php?error=acceso_denegado");
    exit(); 
}
// -----------------------------

include '../../config/db.php';
include '../includes/header_admin.php';

// 1. Obtener la lista de cursos ordenados por Salón
$sql_cursos = "SELECT c.id, c.nombre_curso, c.id_profesor, gs.nivel, gs.grado, gs.seccion, u.usuario as profesor 
               FROM cursos c 
               JOIN grados_secciones gs ON c.id_grado_seccion = gs.id 
               LEFT JOIN usuarios u ON c.id_profesor = u.id 
               ORDER BY gs.nivel DESC, gs.grado ASC, gs.seccion ASC, c.nombre_curso ASC";
$res_cursos = $conn->query($sql_cursos);

// --- MAGIA DE AGRUPACIÓN POR SALÓN ---
$cursos_agrupados = [];
$total_cursos = 0;
if($res_cursos && $res_cursos->num_rows > 0) {
    while($row = $res_cursos->fetch_assoc()) {
        // Creamos el nombre del módulo/salón
        $nombre_salon = $row['nivel'] . " - " . $row['grado'] . "° " . $row['seccion'];
        // Metemos el curso dentro de la categoría de su salón
        $cursos_agrupados[$nombre_salon][] = $row;
        $total_cursos++;
    }
}

// 2. Obtener Profesores para el formulario
$sql_profesores = "SELECT u.id, u.usuario 
                   FROM usuarios u 
                   JOIN roles r ON u.id_rol = r.id 
                   WHERE r.nombre_rol = 'Profesor'";
$res_prof = $conn->query($sql_profesores);
$profesores = [];
if($res_prof && $res_prof->num_rows > 0) {
    while($p = $res_prof->fetch_assoc()) {
        $profesores[] = $p;
    }
}
?>

<div class="container pb-5 mt-4">
    
    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg'] == 'curso_creado'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm">
                <i class="bi bi-check-circle-fill me-2"></i> Curso asignado correctamente al salón.
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['msg'] == 'curso_editado'): ?>
            <div class="alert alert-info alert-dismissible fade show shadow-sm">
                <i class="bi bi-pencil-square me-2"></i> Datos del curso actualizados correctamente.
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['msg'] == 'curso_borrado'): ?>
            <div class="alert alert-warning alert-dismissible fade show shadow-sm">
                <i class="bi bi-trash me-2"></i> Curso eliminado del sistema.
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <?php if(isset($_GET['error']) && $_GET['error'] == 'salon_no_existe'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Error:</strong> La combinación de Nivel, Grado y Sección que elegiste no está registrada en el colegio.
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="bi bi-journal-bookmark me-2"></i>Malla Curricular</h3>
            <p class="text-muted small">Cursos totales: <span class="badge bg-primary"><?php echo $total_cursos; ?></span></p>
        </div>
        <button class="btn btn-primary shadow-sm" onclick="abrirModalCrear()">
            <i class="bi bi-plus-circle me-2"></i>Nueva Materia
        </button>
    </div>

    <?php if(!empty($cursos_agrupados)): ?>
        <div class="accordion shadow-sm" id="accordionCursos">
            <?php $i = 0; foreach($cursos_agrupados as $salon => $lista_cursos): $i++; ?>
                <div class="accordion-item border-0 mb-2 rounded overflow-hidden">
                    <h2 class="accordion-header" id="heading<?php echo $i; ?>">
                        <button class="accordion-button <?php echo ($i != 1) ? 'collapsed' : ''; ?> fw-bold bg-light text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $i; ?>">
                            <i class="bi bi-door-open-fill text-primary fs-5 me-2"></i> 
                            Salón: <?php echo $salon; ?> 
                            <span class="badge bg-secondary ms-auto me-3"><?php echo count($lista_cursos); ?> materias</span>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $i; ?>" class="accordion-collapse collapse <?php echo ($i == 1) ? 'show' : ''; ?>" data-bs-parent="#accordionCursos">
                        <div class="accordion-body p-0 border-top">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">Materia</th>
                                            <th class="text-center">Profesor a Cargo</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($lista_cursos as $curso): ?>
                                            <tr>
                                                <td class="ps-4 fw-bold text-primary">
                                                    <i class="bi bi-book-half me-2 opacity-50"></i><?php echo $curso['nombre_curso']; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if($curso['profesor']): ?>
                                                        <span class="badge bg-success opacity-75"><i class="bi bi-person-video3 me-1"></i> <?php echo $curso['profesor']; ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Sin Asignar</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-outline-primary border-0 me-1" 
                                                            onclick="abrirModalEditar(<?php echo $curso['id']; ?>, '<?php echo addslashes($curso['nombre_curso']); ?>', '<?php echo $curso['nivel']; ?>', '<?php echo $curso['grado']; ?>', '<?php echo $curso['seccion']; ?>', '<?php echo $curso['id_profesor']; ?>')">
                                                        <i class="bi bi-pencil fs-5"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger border-0" onclick="borrarCurso(<?php echo $curso['id']; ?>)">
                                                        <i class="bi bi-trash fs-5"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center py-5 shadow-sm border-0">
            <i class="bi bi-journal-x fs-1 d-block mb-3"></i>
            <h5>Aún no hay cursos asignados</h5>
            <p class="text-muted">Presiona "Nueva Materia" para comenzar a armar la malla curricular de los salones.</p>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalCurso" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white" id="modalHeader">
                <h5 class="modal-title fw-bold" id="modalTitle">Nueva Materia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../../backend/gestion_cursos.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" id="accionCurso" value="crear">
                    <input type="hidden" name="id_curso" id="id_curso" value="">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nombre de la Materia</label>
                        <input type="text" name="nombre_curso" id="nombre_curso" class="form-control" placeholder="Ej: Matemáticas" required>
                    </div>
                    
                    <label class="form-label fw-bold small">Asignar al Salón</label>
                    <div class="row bg-light p-2 rounded mb-3 mx-0 border">
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

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Profesor a Cargo</label>
                        <select name="id_profesor" id="id_profesor" class="form-select" required>
                            <option value="">Seleccione un profesor...</option>
                            <?php foreach($profesores as $prof): ?>
                                <option value="<?php echo $prof['id']; ?>">
                                    <?php echo $prof['usuario']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold" id="btnGuardar">Guardar Materia</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ESTA ES LA CORRECCIÓN: La variable myModal no se inicializa suelta, 
    // se crea una función segura para abrir el modal.
    
    function obtenerModal() {
        return new bootstrap.Modal(document.getElementById('modalCurso'));
    }

    function abrirModalCrear() {
        document.getElementById('accionCurso').value = 'crear';
        document.getElementById('id_curso').value = '';
        document.getElementById('nombre_curso').value = '';
        document.getElementById('nivel').value = '';
        document.getElementById('grado').value = '';
        document.getElementById('seccion').value = '';
        document.getElementById('id_profesor').value = '';
        
        document.getElementById('modalHeader').className = 'modal-header bg-primary text-white';
        document.getElementById('modalTitle').innerText = 'Nueva Materia';
        document.getElementById('btnGuardar').className = 'btn btn-primary fw-bold';
        document.getElementById('btnGuardar').innerText = 'Guardar Materia';
        
        obtenerModal().show();
    }

    function abrirModalEditar(id, nombre, nivel, grado, seccion, id_prof) {
        document.getElementById('accionCurso').value = 'editar';
        document.getElementById('id_curso').value = id;
        document.getElementById('nombre_curso').value = nombre;
        document.getElementById('nivel').value = nivel;
        document.getElementById('grado').value = grado;
        document.getElementById('seccion').value = seccion;
        document.getElementById('id_profesor').value = id_prof;
        
        document.getElementById('modalHeader').className = 'modal-header bg-info text-dark';
        document.getElementById('modalTitle').innerText = 'Editar Materia';
        document.getElementById('btnGuardar').className = 'btn btn-info fw-bold text-white';
        document.getElementById('btnGuardar').innerText = 'Guardar Cambios';
        
        obtenerModal().show();
    }

    function borrarCurso(id) {
        if(confirm('¿Seguro que quieres eliminar esta materia? ATENCIÓN: Se borrarán todos los registros de asistencia de aula de los alumnos en este curso.')) {
            window.location.href = "../../backend/gestion_cursos.php?accion=borrar&id=" + id;
        }
    }
</script>
</body>
</html>