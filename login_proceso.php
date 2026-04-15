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
        
        // --- SEGURIDAD REAL: SOLO SE ACEPTAN CLAVES ENCRIPTADAS ---
        // Aquí quitamos el "|| $pass === '123456'". 
        // Ahora el sistema OBLIGA a que la clave coincida con el Hash seguro.
        if (password_verify($pass, $row['password'])) {
            
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['nombre_usuario'] = $row['usuario'];
            $_SESSION['rol'] = $row['nombre_rol'];

            // Enrutamiento Inteligente (La alternativa al selector de rol)
            if ($row['nombre_rol'] === 'SuperAdmin' || $row['nombre_rol'] === 'Director' || $row['nombre_rol'] === 'Admin') {
                header("Location: frontend/admin/dashboard.php");
            } else if ($row['nombre_rol'] === 'Profesor') {
                header("Location: frontend/profesor/mis_cursos.php"); // Futura vista
            } else {
                // Si es Auxiliar u otro
                header("Location: frontend/asistencia/escanear.php");
            }
            exit(); 

        } else {
            // Clave incorrecta
            header("Location: index.php?error=1");
            exit();
        }
    } else {
        // Usuario no existe
        header("Location: index.php?error=1");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>