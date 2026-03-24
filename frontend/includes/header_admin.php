<?php
// frontend/includes/header_admin.php

// 1. INICIAR SESIÓN (Si no está iniciada ya)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_url = "http://localhost/pequenos_ingenieros/"; 
$rol_actual = isset($_SESSION['rol']) ? $_SESSION['rol'] : ''; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Escolar - Pequeños Ingenieros</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../../assets/css/estilos_admin.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4 sticky-top">
    <div class="container-fluid px-4">
        
        <a class="navbar-brand d-flex align-items-center" href="../admin/dashboard.php">
            <img src="../../assets/img/logo.png" alt="Logo" style="height: 45px; width: auto; margin-right: 12px; border-radius: 5px; background: rgba(255,255,255,0.9); padding: 2px;">
            <div style="line-height: 1;">
                <div>PEQUEÑOS INGENIEROS</div>
                <div style="font-size: 0.6em; font-weight: 400; opacity: 0.9;">Perfil: <?php echo $rol_actual; ?></div>
            </div>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav gap-2 align-items-center">
                
                <?php if (in_array($rol_actual, ['SuperAdmin', 'Admin', 'Director'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/dashboard.php"><i class="bi bi-speedometer2"></i> Inicio</a>
                </li>
                <?php endif; ?>

                <?php if ($rol_actual === 'SuperAdmin'): ?>
                <li class="nav-item">
                    <a class="nav-link fw-bold text-warning" href="../admin/usuarios.php"><i class="bi bi-shield-lock"></i> Usuarios</a>
                </li>
                <?php endif; ?>

                <?php if (in_array($rol_actual, ['SuperAdmin', 'Admin'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-mortarboard"></i> Académico
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../admin/alumnos.php"><i class="bi bi-people"></i> Matrícula Alumnos</a></li>
                        <li><a class="dropdown-item" href="../admin/cursos.php"><i class="bi bi-journal-bookmark"></i> Gestión de Cursos</a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if (in_array($rol_actual, ['SuperAdmin', 'Admin', 'Director'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/reporte_diario.php"><i class="bi bi-clipboard-check"></i> Reportes</a>
                </li>
                <?php endif; ?>

                <?php if ($rol_actual === 'Profesor'): ?>
                <li class="nav-item">
                    <a class="nav-link text-info" href="../profesor/mis_cursos.php"><i class="bi bi-list-check"></i> Pasar Lista (Aula)</a>
                </li>
                <?php endif; ?>

                <?php if (in_array($rol_actual, ['SuperAdmin', 'Auxiliar'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="../asistencia/escanear.php"><i class="bi bi-qr-code-scan"></i> Escanear (Puerta)</a>
                </li>
                <?php endif; ?>

                <li class="nav-item d-none d-lg-block text-white opacity-50 mx-2">|</li>

                <li class="nav-item d-flex align-items-center gap-2">
                    <span class="text-white small d-none d-md-block">
                        Hola, <strong><?php echo isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : 'Usuario'; ?></strong>
                    </span>
                    <a class="btn btn-danger btn-sm fw-bold shadow-sm" href="../../backend/logout.php">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>