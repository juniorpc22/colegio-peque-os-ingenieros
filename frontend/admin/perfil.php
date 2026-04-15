<?php
// frontend/admin/perfil.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../config/db.php';
include '../includes/header_admin.php';

// Obtener datos del usuario logueado usando la variable correcta
$id_usuario = $_SESSION['usuario_id'];
$sql = "SELECT * FROM usuarios WHERE id = '$id_usuario'";
$res = $conn->query($sql);
$usuario = $res->fetch_assoc();
?>

<div class="container pb-5 mt-4">
    <h3 class="fw-bold mb-4 text-dark"><i class="bi bi-person-gear me-2"></i>Mi Perfil</h3>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom border-primary border-3">
                    <h5 class="m-0 fw-bold text-primary">Datos de Cuenta</h5>
                </div>
                <div class="card-body">
                    <?php if(isset($_GET['msg']) && $_GET['msg']=='ok_datos'): ?>
                        <div class="alert alert-success alert-dismissible fade show small">
                            Datos actualizados correctamente. <button class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="../../backend/actualizar_perfil.php" method="POST">
                        <input type="hidden" name="accion" value="datos">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Nombre a mostrar</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo $usuario['nombres'] ?? $usuario['nombre']; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Usuario de Login</label>
                            <input type="text" name="usuario" class="form-control" value="<?php echo $usuario['usuario']; ?>" required>
                            <div class="form-text small">Con este usuario iniciarás sesión en el sistema.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold mt-2">
                            <i class="bi bi-save me-2"></i>Guardar Datos
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom border-danger border-3">
                    <h5 class="m-0 fw-bold text-danger">Seguridad</h5>
                </div>
                <div class="card-body">
                    
                    <?php if(isset($_GET['msg'])): ?>
                        <?php if($_GET['msg']=='ok_pass'): ?>
                            <div class="alert alert-success alert-dismissible fade show small">
                                ¡Contraseña actualizada con éxito! <button class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php elseif($_GET['msg']=='error_pass'): ?>
                            <div class="alert alert-danger alert-dismissible fade show small">
                                La contraseña actual no es correcta. <button class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php elseif($_GET['msg']=='error_coincidencia'): ?>
                            <div class="alert alert-warning alert-dismissible fade show small">
                                La nueva contraseña y la confirmación no coinciden. <button class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <form action="../../backend/actualizar_perfil.php" method="POST">
                        <input type="hidden" name="accion" value="password">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Contraseña Actual</label>
                            <input type="password" name="pass_actual" class="form-control" required placeholder="••••••">
                        </div>
                        
                        <hr class="opacity-25">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-danger">Nueva Contraseña</label>
                            <input type="password" name="pass_nueva" class="form-control border-danger" required placeholder="Ingresa la nueva clave">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-danger">Confirmar Nueva Contraseña</label>
                            <input type="password" name="pass_confirmar" class="form-control border-danger" required placeholder="Repite la nueva clave">
                        </div>

                        <button type="submit" class="btn btn-danger w-100 fw-bold mt-2">
                            <i class="bi bi-shield-lock me-2"></i>Actualizar Contraseña
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>