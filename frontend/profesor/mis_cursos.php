<?php
// frontend/profesor/mis_cursos.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- GUARDIÁN DE SEGURIDAD ---
// Solo Profesores (y el SuperAdmin para que tú puedas probar) tienen acceso aquí.
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['SuperAdmin', 'Profesor'])) {
    header("Location: ../admin/dashboard.php?error=acceso_denegado");
    exit(); 
}
// -----------------------------

include '../../config/db.php';
// Reutilizamos tu header que ya es inteligente y adapta el menú según el rol
include '../includes/header_admin.php'; 

// Obtenemos el ID del usuario logueado (El profesor)
$id_profesor = $_SESSION['usuario_id'];

// Consultamos solo los cursos asignados a este profesor en específico
$sql = "SELECT c.id as id_curso, c.nombre_curso, gs.nivel, gs.grado, gs.seccion 
        FROM cursos c 
        JOIN grados_secciones gs ON c.id_grado_seccion = gs.id 
        WHERE c.id_profesor = ? 
        ORDER BY gs.nivel DESC, gs.grado ASC, gs.seccion ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_profesor);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<div class="container pb-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <div>
            <h3 class="fw-bold text-dark mb-0"><i class="bi bi-person-workspace me-2"></i>Mis Cursos Asignados</h3>
            <p class="text-muted small">Selecciona un salón para registrar la asistencia del día.</p>
        </div>
    </div>

    <div class="row g-4">
        <?php if($resultado && $resultado->num_rows > 0): ?>
            <?php while($row = $resultado->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0 border-top border-primary border-4 rounded-3 hover-effect">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="badge bg-light text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-bold">
                                    <i class="bi bi-book-half me-1"></i> <?php echo $row['nombre_curso']; ?>
                                </div>
                                <i class="bi bi-mortarboard text-muted fs-4 opacity-50"></i>
                            </div>
                            
                            <h4 class="fw-bold text-dark mb-1">
                                <?php echo $row['grado'] . "° " . $row['seccion']; ?>
                            </h4>
                            <p class="text-muted small mb-4">Nivel: <?php echo $row['nivel']; ?></p>
                            
                            <div class="d-grid mt-auto">
                                <a href="pasar_lista.php?id_curso=<?php echo $row['id_curso']; ?>" class="btn btn-primary fw-bold shadow-sm">
                                    <i class="bi bi-list-check me-2"></i>Pasar Lista
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info shadow-sm border-0 d-flex align-items-center p-4">
                    <i class="bi bi-info-circle-fill fs-3 text-info me-3"></i>
                    <div>
                        <h5 class="fw-bold mb-1">No tienes cursos asignados</h5>
                        <p class="mb-0 text-muted">Aún no se te ha asignado ninguna materia o salón para este periodo escolar. Por favor, comunícate con la administración académica.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Pequeño efecto visual para que las tarjetas se eleven al pasar el mouse */
    .hover-effect {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .hover-effect:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>