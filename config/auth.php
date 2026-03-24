<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no hay sesión, mandamos al index de la raíz
if (!isset($_SESSION['usuario_id'])) {
    // Usamos una ruta absoluta desde el localhost para evitar errores de nivel de carpeta
    header("Location: /pequenos_ingenieros/index.php?error=debe_iniciar_sesion");
    exit();
}
?>