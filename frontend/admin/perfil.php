<?php
// frontend/admin/perfil.php
include '../../config/db.php';
include '../includes/header_admin.php';

// Obtener datos del usuario logueado
$id_usuario = $_SESSION['admin_id'];
$sql = "SELECT * FROM usuarios WHERE id = '$id_usuario'";
$res = $conn->query($sql);
$usuario = $res->fetch_assoc();
?>

<div class="container pb-5">
    <h3 class="fw-bold mb-4"><i class="bi bi-person-gear me-2"></i>Mi Perfil</h3>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="m-0 fw-bold text-primary">Datos Personales</h5>
                </div>
                <div class="card-body">
                    <form action="../../backend/actualizar_perfil.php" method="POST">
                        <input type="hidden" name="accion" value="datos">
                        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo $usuario['nombre']; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Usuario (Login)</label>
                            <input type="text" name="usuario" class="form-control" value="<?php echo $usuario['usuario']; ?>" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-save me-2"></i>Guardar Cambios
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="m-0 fw-bold text-danger">Seguridad</h5>
                </div>
                <div class="card-body">
                    
                    <?php if(isset($_GET['msg'])): ?>
                        <?php if($_GET['msg']=='ok_pass'): ?>
                            <div class="alert alert-success small">¡Contraseña actualizada con éxito!</div>
                        <?php elseif($_GET['msg']=='error_pass'): ?>
                            <div class="alert alert-danger small">La contraseña actual no es correcta.</div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <form action="../../backend/actualizar_perfil.php" method="POST">
                        <input type="hidden" name="accion" value="password">
                        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Contraseña Actual</label>
                            <input type="password" name="pass_actual" class="form-control" required placeholder="••••••">
                        </div>
                        
                        <hr>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Nueva Contraseña</label>
                            <input type="password" name="pass_nueva" class="form-control" required placeholder="Min. 4 caracteres">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Confirmar Nueva</label>
                            <input type="password" name="pass_confirmar" class="form-control" required placeholder="Repetir contraseña">
                        </div>

                        <button type="submit" class="btn btn-danger w-100">
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