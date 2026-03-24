<?php
// frontend/admin/usuarios.php
session_start(); // Aseguramos que la sesión esté iniciada

// --- GUARDIÁN DE SEGURIDAD ---
// Si no hay sesión activa, o si el rol NO es "SuperAdmin"
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'SuperAdmin') {
    // Lo pateamos de vuelta al dashboard con un mensaje de error
    header("Location: dashboard.php?error=acceso_denegado");
    exit(); // Detiene la carga de la página
}
// -----------------------------

include '../../config/db.php';
include '../includes/header_admin.php';

// ... (Aquí sigue tu código normal para mostrar la tabla) ...
?>
<?php
// frontend/admin/usuarios.php

include '../../config/db.php';
include '../includes/header_admin.php';

// Consulta unificada: Traemos el ID, el nombre de usuario y el ID del Rol
$sql = "SELECT id, usuario, id_rol FROM usuarios";
$resultado = $conn->query($sql);
?>

<div class="container pb-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0"><i class="bi bi-shield-lock me-2"></i>Control de Accesos</h3>
            <p class="text-muted small">Usuarios registrados en el sistema Pequeños Ingenieros.</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUsuario">
            <i class="bi bi-person-plus-fill me-2"></i>Nuevo Usuario
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Usuario (Login)</th>
                            <th class="text-center">Permisos (Rol)</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($resultado && $resultado->num_rows > 0): ?>
                            <?php while($row = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 text-secondary">#<?php echo $row['id']; ?></td>
                                    <td class="fw-bold"><?php echo $row['usuario']; ?></td>
                                    <td class="text-center">
                                        <span class="badge <?php echo ($row['id_rol'] == 1) ? 'bg-primary' : 'bg-info text-dark'; ?>">
                                            <?php echo ($row['id_rol'] == 1) ? 'Administrador' : 'Auxiliar'; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if($row['id'] != $_SESSION['usuario_id']): ?>
                                            <button class="btn btn-sm btn-outline-danger border-0"
                                                    onclick="confirmarBorrado(<?php echo $row['id']; ?>, '<?php echo $row['usuario']; ?>')">
                                                <i class="bi bi-trash fs-5"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="badge bg-success opacity-75">Tu Sesión</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-5">No hay usuarios en la base de datos.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Nuevo Acceso</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../../backend/gestion_usuarios.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="crear">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Usuario</label>
                        <input type="text" name="usuario" class="form-control" placeholder="Ej: junior_admin" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Contraseña</label>
                        <input type="password" name="password" class="form-control" placeholder="******" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Rol</label>
                        <select name="id_rol" class="form-select">
                            <option value="1">Director / SuperAdmin</option>
                            <option value="3">Auxiliar</option>
                        </select>
                    </div>
                    <hr>
                    <div class="mb-2 bg-light p-2 rounded">
                        <label class="form-label small fw-bold text-danger">Confirmar con tu clave actual:</label>
                        <input type="password" name="admin_password" class="form-control border-danger" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary fw-bold">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmarBorrado(id, nombre) {
        if(confirm('¿Seguro que quieres eliminar a ' + nombre + '?')) {
            window.location.href = "../../backend/gestion_usuarios.php?accion=borrar&id=" + id;
        }
    }
</script>