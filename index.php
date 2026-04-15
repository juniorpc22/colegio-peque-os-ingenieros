<?php
// index.php (RAÍZ DEL PROYECTO)
session_start();

// --- ENRUTAMIENTO INTELIGENTE ---
// Si ya está logueado, lo enviamos a su panel correspondiente según su rol
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol'] == 'Auxiliar') {
        header("Location: frontend/asistencia/escanear.php");
    } else if ($_SESSION['rol'] == 'Profesor') {
        header("Location: frontend/profesor/mis_cursos.php");
    } else {
        header("Location: frontend/admin/dashboard.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema - Pequeños Ingenieros</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card-login { border: 0; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.3); overflow: hidden; width: 100%; max-width: 400px; }
        .login-header { background: white; padding: 40px 30px 20px; text-align: center; }
        .login-body { background: #f8f9fa; padding: 30px; }
        .btn-login { background: #0d6efd; border: 0; padding: 12px; font-weight: bold; transition: 0.3s; width: 100%; }
        .btn-login:hover { background: #0b5ed7; transform: translateY(-2px); }
        
        /* ESTILO PARA EL BOTÓN DE PADRES */
        .btn-padres { background: white; color: #0d6efd; border: 2px solid #0d6efd; padding: 10px; font-weight: bold; transition: 0.3s; width: 100%; text-decoration: none; display: inline-block; text-align: center; border-radius: 8px;}
        .btn-padres:hover { background: #f1f4f9; transform: translateY(-2px); color: #0b5ed7;}
    </style>
</head>
<body>

<div class="card card-login">
    <div class="login-header">
        <div class="mb-3"><i class="bi bi-mortarboard-fill text-primary" style="font-size: 4rem;"></i></div>
        <h4 class="fw-bold text-dark">Pequeños Ingenieros</h4>
        <p class="text-muted small">Panel Administrativo</p>
    </div>
    
    <div class="login-body">
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger text-center p-2 mb-3 small fw-bold">
                <i class="bi bi-exclamation-circle me-1"></i> Datos incorrectos o acceso denegado
            </div>
        <?php endif; ?>

        <form action="login_proceso.php" method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">Usuario</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-person text-secondary"></i></span>
                    <input type="text" name="usuario" class="form-control border-start-0 ps-0" placeholder="Ej: admin" required autofocus>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label small fw-bold text-muted">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-key text-secondary"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-login text-white shadow mb-3 rounded-3">
                ENTRAR AL SISTEMA
            </button>
        </form>

        <hr class="opacity-25 my-4">
        
        <p class="text-center small text-muted mb-2 fw-bold">¿Eres apoderado?</p>
        <a href="frontend/padres/index.php" class="btn-padres shadow-sm">
            <i class="bi bi-people-fill me-2"></i>Ir al Portal de Familia
        </a>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 