<?php
// backend/login.php
session_start();
include '../config/db.php';

$usuario = $conn->real_escape_string($_POST['usuario']);
$password = $_POST['password'];

// Consultar usuario en la BD
$sql = "SELECT * FROM usuarios WHERE usuario = '$usuario'";
$resultado = $conn->query($sql);

if ($resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
    
    // Validar contraseña (compatible con texto plano o encriptado)
    // Si la contraseña de la BD es igual a la escrita O si la encriptación coincide
    if ($password == $fila['password'] || password_verify($password, $fila['password'])) {
        
        // ¡LOGIN CORRECTO!
        $_SESSION['admin_id'] = $fila['id'];
        $_SESSION['admin_nombre'] = $fila['nombre'];
        
        header("Location: ../frontend/admin/index.php");
        exit;
    }
}

// SI FALLA ALGO:
header("Location: ../index.php?error=1");
exit;
?>