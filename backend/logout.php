<?php
session_start(); // Iniciamos para poder destruir
session_unset(); // Limpia las variables de sesión
session_destroy(); // Destruye la sesión en el servidor

// Redirigimos al login que está en la raíz
header("Location: ../index.php");
exit();
?>