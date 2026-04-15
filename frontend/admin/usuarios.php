<?php
// frontend/admin/usuarios.php
session_start(); 

// --- GUARDIÁN DE SEGURIDAD ---
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'SuperAdmin') {
    header("Location: dashboard.php?error=acceso_denegado");
    exit(); 
}
// -----------------------------

include '../../config/db.php';
include '../includes/header_admin.php';

// 1. Obtener la lista de usuarios con los nuevos campos
$sql = "SELECT u.id, u.usuario, u.id_rol, u.nombres, u.apellidos, u.correo, r.nombre_rol 
        FROM usuarios u 
        LEFT JOIN roles r ON u.id_rol = r.id";
$resultado = $conn->query($sql);

// 2. Obtener los roles disponibles
$sql_roles = "SELECT id, nombre_rol FROM roles ORDER BY id ASC";
$res_roles = $conn->query($sql_roles);
?>

<div class="container pb-5 mt-4">
    
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'creado'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i> Usuario creado exitosamente.
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'editado'): ?>
        <div class="alert alert-info alert-dismissible fade show shadow-sm">
            <i class="bi bi-pencil-square me-2"></i> Datos del usuario actualizados correctamente.
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'borrado'): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm">
            <i class="bi bi-trash me-2"></i> Usuario eliminado del sistema.
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(isset($_GET['error']) && $_GET['error'] == 'clave_incorrecta'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="bi bi-shield-x me-2"></i> <strong>Error de Seguridad:</strong> La clave de confirmación actual era incorrecta.
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <div>
            <h3 class="fw-bold text-dark mb-0"><i class="bi bi-shield-lock me-2"></i>Control de Accesos</h3>
            <p class="text-muted small">Gestión de personal y accesos al sistema.</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
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
                            <th>Cuenta de Acceso</th>
                            <th>Datos Personales</th>
                            <th class="text-center">Rol</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($resultado && $resultado->num_rows > 0): ?>
                            <?php while($row = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 text-secondary">#<?php echo $row['id']; ?></td>
                                    <td>
                                        <div class="fw-bold text-primary"><i class="bi bi-person-circle me-1"></i> <?php echo $row['usuario']; ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo $row['nombres'] ? $row['nombres'] . " " . $row['apellidos'] : '<span class="text-muted fst-italic">Sin nombre registrado</span>'; ?></div>
                                        <div class="text-muted small"><?php echo $row['correo'] ?: 'Sin correo'; ?></div>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $bg_color = 'bg-secondary';
                                            if($row['nombre_rol'] == 'SuperAdmin') $bg_color = 'bg-danger';
                                            if($row['nombre_rol'] == 'Admin') $bg_color = 'bg-primary';
                                            if($row['nombre_rol'] == 'Director') $bg_color = 'bg-info text-dark';
                                            if($row['nombre_rol'] == 'Profesor') $bg_color = 'bg-success';
                                            if($row['nombre_rol'] == 'Auxiliar') $bg_color = 'bg-warning text-dark';
                                        ?>
                                        <span class="badge <?php echo $bg_color; ?>">
                                            <?php echo $row['nombre_rol'] ?: 'Sin Asignar'; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if($row['id'] != $_SESSION['usuario_id']): ?>
                                            <button class="btn btn-sm btn-outline-primary border-0 me-1" 
                                                    onclick="abrirEditar(<?php echo $row['id']; ?>, '<?php echo addslashes($row['usuario']); ?>', '<?php echo addslashes($row['nombres']); ?>', '<?php echo addslashes($row['apellidos']); ?>', '<?php echo addslashes($row['correo']); ?>', <?php echo $row['id_rol']; ?>)">
                                                <i class="bi bi-pencil fs-5"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger border-0"
                                                    onclick="confirmarBorrado(<?php echo $row['id']; ?>, '<?php echo addslashes($row['usuario']); ?>')">
                                                <i class="bi bi-trash fs-5"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="badge bg-success opacity-75">Tu Sesión</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-5">No hay usuarios en la base de datos.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCrearUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Nuevo Acceso al Sistema</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../../backend/gestion_usuarios.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="crear">
                    
                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">1. Datos Personales</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Nombres</label>
                            <input type="text" name="nombres" class="form-control" placeholder="Ej: Juan Carlos" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Apellidos</label>
                            <input type="text" name="apellidos" class="form-control" placeholder="Ej: Pérez Ramos" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold small">Correo Electrónico (Opcional)</label>
                            <input type="email" name="correo" class="form-control" placeholder="ejemplo@colegio.edu.pe">
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2 mt-2">2. Credenciales de Acceso</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Usuario (Login)</label>
                            <input type="text" name="usuario" class="form-control" placeholder="Ej: jperez" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Contraseña</label>
                            <input type="password" name="password" class="form-control" placeholder="******" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Rol del Sistema</label>
                            <select name="id_rol" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php $res_roles->data_seek(0); while($rol = $res_roles->fetch_assoc()): ?>
                                    <option value="<?php echo $rol['id']; ?>"><?php echo $rol['nombre_rol']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <div class="mb-2 bg-light p-3 rounded border border-danger border-opacity-25">
                        <label class="form-label small fw-bold text-danger"><i class="bi bi-shield-lock me-1"></i> Confirmación de SuperAdmin</label>
                        <input type="password" name="admin_password" class="form-control border-danger" required placeholder="Tu clave actual para autorizar...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary fw-bold">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-dark">
                <h5 class="modal-title fw-bold">Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../../backend/gestion_usuarios.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id_usuario" id="edit_id">
                    
                    <h6 class="fw-bold text-info mb-3 border-bottom pb-2">1. Datos Personales</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Nombres</label>
                            <input type="text" name="nombres" id="edit_nombres" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Apellidos</label>
                            <input type="text" name="apellidos" id="edit_apellidos" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold small">Correo Electrónico</label>
                            <input type="email" name="correo" id="edit_correo" class="form-control">
                        </div>
                    </div>

                    <h6 class="fw-bold text-info mb-3 border-bottom pb-2 mt-2">2. Accesos</h6>
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Usuario (Login)</label>
                            <input type="text" name="usuario" id="edit_usuario" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small">Rol</label>
                            <select name="id_rol" id="edit_rol" class="form-select" required>
                                <?php $res_roles->data_seek(0); while($rol = $res_roles->fetch_assoc()): ?>
                                    <option value="<?php echo $rol['id']; ?>"><?php echo $rol['nombre_rol']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small text-danger">Resetear Contraseña</label>
                            <input type="password" name="nueva_password" class="form-control border-danger" placeholder="Dejar vacío para no cambiar">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info fw-bold text-white">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmarBorrado(id, nombre) {
        if(confirm('¿Seguro que quieres eliminar el acceso de ' + nombre + '?')) {
            window.location.href = "../../backend/gestion_usuarios.php?accion=borrar&id=" + id;
        }
    }

    function abrirEditar(id, usuario, nombres, apellidos, correo, id_rol) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_usuario').value = usuario;
        document.getElementById('edit_nombres').value = nombres;
        document.getElementById('edit_apellidos').value = apellidos;
        document.getElementById('edit_correo').value = correo;
        document.getElementById('edit_rol').value = id_rol;
        
        new bootstrap.Modal(document.getElementById('modalEditarUsuario')).show();
    }
</script>
</body>
</html>