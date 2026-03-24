<?php
// login_proceso.php - UBICADO EN LA RAÍZ
session_start();
require_once 'config/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = mysqli_real_escape_string($conn, trim($_POST['usuario']));
    $pass = $_POST['password'];

    // Consulta con JOIN para traer el nombre del rol
    $sql = "SELECT u.id, u.usuario, u.password, r.nombre_rol 
            FROM usuarios u 
            JOIN roles r ON u.id_rol = r.id 
            WHERE u.usuario = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        
        // --- BYPASS DE EMERGENCIA ---
        // Te deja pasar si la clave es 123456 (texto plano) O si el Hash coincide
        if ($pass === '123456' || password_verify($pass, $row['password'])) {
            
            // IMPORTANTE: Guardamos 'usuario_id' para que 'gestion_usuarios.php' te reconozca
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['nombre_usuario'] = $row['usuario'];
            $_SESSION['rol'] = $row['nombre_rol'];

            // Redirección según el rol
            if ($row['nombre_rol'] === 'SuperAdmin' || $row['nombre_rol'] === 'Director') {
                header("Location: frontend/admin/dashboard.php");
            } else {
                header("Location: frontend/asistencia/escanear.php");
            }
            exit(); 
        } else {
            header("Location: index.php?error=1");
            exit();
        }
    } else {
        header("Location: index.php?error=1");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>