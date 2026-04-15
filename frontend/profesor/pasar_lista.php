<?php
// frontend/profesor/pasar_lista.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- GUARDIÁN DE SEGURIDAD ---
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['SuperAdmin', 'Profesor'])) {
    header("Location: ../admin/dashboard.php?error=acceso_denegado");
    exit(); 
}

include '../../config/db.php';
include '../includes/header_admin.php';

// Validamos que venga el ID del curso por la URL
if (!isset($_GET['id_curso'])) {
    die("<div class='container mt-5'><div class='alert alert-danger'>Error: No se especificó el curso.</div></div>");
}

$id_curso = intval($_GET['id_curso']);
$id_profesor = $_SESSION['usuario_id'];
$fecha_hoy = date('Y-m-d');

// 1. Obtener datos del curso y validar que le pertenezca a este profesor
$sql_curso = "SELECT c.nombre_curso, c.id_grado_seccion, gs.nivel, gs.grado, gs.seccion 
              FROM cursos c 
              JOIN grados_secciones gs ON c.id_grado_seccion = gs.id 
              WHERE c.id = ? AND (c.id_profesor = ? OR ? = 1)"; // El '1' permite al SuperAdmin ver cualquier curso para pruebas
$stmt_c = $conn->prepare($sql_curso);
$is_superadmin = ($_SESSION['rol'] == 'SuperAdmin') ? 1 : 0;
$stmt_c->bind_param("iii", $id_curso, $id_profesor, $is_superadmin);
$stmt_c->execute();
$res_curso = $stmt_c->get_result();

if ($res_curso->num_rows == 0) {
    die("<div class='container mt-5'><div class='alert alert-danger'>Error de Seguridad: Este curso no te pertenece o no existe.</div></div>");
}
$datos_curso = $res_curso->fetch_assoc();
$id_salon = $datos_curso['id_grado_seccion'];

// 2. Obtener los alumnos matriculados en este salón y su asistencia de HOY (si ya la tomaron)
$sql_alumnos = "SELECT a.id as id_alumno, a.nombres, a.apellidos, ac.estado 
                FROM alumnos a 
                LEFT JOIN asistencia_cursos ac ON a.id = ac.id_alumno AND ac.id_curso = ? AND ac.fecha = ?
                WHERE a.id_grado_seccion = ? 
                ORDER BY a.apellidos ASC";
$stmt_a = $conn->prepare($sql_alumnos);
$stmt_a->bind_param("isi", $id_curso, $fecha_hoy, $id_salon);
$stmt_a->execute();
$res_alumnos = $stmt_a->get_result();
?>

<div class="container pb-5 mt-4">
    
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'guardado'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i> Asistencia registrada correctamente.
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <div>
            <a href="mis_cursos.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Volver a Mis Cursos</a>
            <h3 class="fw-bold text-dark mb-0 mt-1"><i class="bi bi-card-checklist me-2"></i>Registro de Aula</h3>
        </div>
        <div class="text-end">
            <div class="badge bg-primary fs-6 shadow-sm"><i class="bi bi-calendar3 me-2"></i><?php echo date("d/m/Y"); ?></div>
        </div>
    </div>

    <div class="card shadow-sm border-0 border-start border-info border-4 mb-4 bg-light">
        <div class="card-body">
            <h5 class="fw-bold text-info mb-1"><?php echo $datos_curso['nombre_curso']; ?></h5>
            <p class="mb-0 text-muted">Salón: <strong><?php echo $datos_curso['grado']."° ".$datos_curso['seccion']." - ".$datos_curso['nivel']; ?></strong></p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <form action="../../backend/guardar_asistencia_aula.php" method="POST">
                <input type="hidden" name="id_curso" value="<?php echo $id_curso; ?>">
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Estudiante</th>
                                <th class="text-center" style="width: 120px;">Asistió</th>
                                <th class="text-center" style="width: 120px;">Tardanza</th>
                                <th class="text-center" style="width: 120px;">Falta</th>
                                <th class="text-center" style="width: 120px;">Justificado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($res_alumnos && $res_alumnos->num_rows > 0): ?>
                                <?php while($alum = $res_alumnos->fetch_assoc()): ?>
                                    <?php 
                                        $id_a = $alum['id_alumno'];
                                        $estado_actual = $alum['estado']; // Puede ser null si aún no llaman lista
                                        // Si no han llamado lista, por defecto marcamos "Asistió" para facilitar el trabajo
                                        if(!$estado_actual) $estado_actual = 'Asistió'; 
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-secondary">
                                            <?php echo $alum['apellidos'] . ", " . $alum['nombres']; ?>
                                        </td>
                                        <td class="text-center border-start">
                                            <input class="form-check-input bg-success border-success" type="radio" name="asistencia[<?php echo $id_a; ?>]" value="Asistió" <?php if($estado_actual == 'Asistió') echo 'checked'; ?>>
                                        </td>
                                        <td class="text-center bg-warning bg-opacity-10 border-start">
                                            <input class="form-check-input bg-warning border-warning" type="radio" name="asistencia[<?php echo $id_a; ?>]" value="Tardanza" <?php if($estado_actual == 'Tardanza') echo 'checked'; ?>>
                                        </td>
                                        <td class="text-center bg-danger bg-opacity-10 border-start">
                                            <input class="form-check-input bg-danger border-danger" type="radio" name="asistencia[<?php echo $id_a; ?>]" value="Falta" <?php if($estado_actual == 'Falta') echo 'checked'; ?>>
                                        </td>
                                        <td class="text-center bg-info bg-opacity-10 border-start">
                                            <input class="form-check-input bg-info border-info" type="radio" name="asistencia[<?php echo $id_a; ?>]" value="Justificado" <?php if($estado_actual == 'Justificado') echo 'checked'; ?>>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-5">No hay alumnos matriculados en este salón.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer bg-white text-end p-3 border-top">
                    <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                        <i class="bi bi-save2 me-2"></i>Guardar Asistencia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Hacemos los radio buttons un poco más grandes para que el profe cliquee fácil */
    .form-check-input { width: 1.5rem; height: 1.5rem; cursor: pointer; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>