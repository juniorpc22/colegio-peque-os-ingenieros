<?php
// backend/gestion_usuarios.php
session_start();
include '../config/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'SuperAdmin') {
    die("Acceso no autorizado");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $accion = $_POST['accion'];

    // --- CASO A: CREAR USUARIO ---
    if ($accion == 'crear') {
        
        $admin_password_input = $_POST['admin_password'];
        $admin_id = $_SESSION['usuario_id'];
        
        $sql_admin = "SELECT password FROM usuarios WHERE id = ?";
        $stmt_admin = $conn->prepare($sql_admin);
        $stmt_admin->bind_param("i", $admin_id);
        $stmt_admin->execute();
        $admin_data = $stmt_admin->get_result()->fetch_assoc();
        
        if ($admin_password_input !== $admin_data['password'] && !password_verify($admin_password_input, $admin_data['password'])) {
            header("Location: ../frontend/admin/usuarios.php?error=clave_incorrecta");
            exit();
        }

        $usuario = mysqli_real_escape_string($conn, trim($_POST['usuario']));
        $password_plano = $_POST['password'];
        $id_rol = intval($_POST['id_rol']);
        $nombres = mysqli_real_escape_string($conn, trim($_POST['nombres']));
        $apellidos = mysqli_real_escape_string($conn, trim($_POST['apellidos']));
        $correo = mysqli_real_escape_string($conn, trim($_POST['correo']));

        $password_hash = password_hash($password_plano, PASSWORD_DEFAULT);

        // Inserción con los nuevos campos
        $sql = "INSERT INTO usuarios (usuario, password, id_rol, nombres, apellidos, correo) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisss", $usuario, $password_hash, $id_rol, $nombres, $apellidos, $correo);

        if ($stmt->execute()) {
            header("Location: ../frontend/admin/usuarios.php?msg=creado");
        } else {
            die("Error BD: " . $conn->error);
        }
        exit();
    }

    // --- CASO B: EDITAR USUARIO ---
    if ($accion == 'editar') {
        $id_usuario = intval($_POST['id_usuario']);
        $usuario = mysqli_real_escape_string($conn, trim($_POST['usuario']));
        $id_rol = intval($_POST['id_rol']);
        $nombres = mysqli_real_escape_string($conn, trim($_POST['nombres']));
        $apellidos = mysqli_real_escape_string($conn, trim($_POST['apellidos']));
        $correo = mysqli_real_escape_string($conn, trim($_POST['correo']));
        $nueva_password = $_POST['nueva_password'];

        // Si escribieron algo en "Resetear Contraseña"
        if (!empty($nueva_password)) {
            $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET usuario=?, id_rol=?, nombres=?, apellidos=?, correo=?, password=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sissssi", $usuario, $id_rol, $nombres, $apellidos, $correo, $password_hash, $id_usuario);
        } else {
            // Si lo dejaron en blanco, actualiza todo MENOS la contraseña
            $sql = "UPDATE usuarios SET usuario=?, id_rol=?, nombres=?, apellidos=?, correo=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisssi", $usuario, $id_rol, $nombres, $apellidos, $correo, $id_usuario);
        }

        if ($stmt->execute()) {
            header("Location: ../frontend/admin/usuarios.php?msg=editado");
        } else {
            die("Error BD: " . $conn->error);
        }
        exit();
    }
}

// --- CASO C: BORRAR USUARIO ---
if (isset($_GET['accion']) && $_GET['accion'] == 'borrar') {
    $id_borrar = intval($_GET['id']);
    
    if ($id_borrar == $_SESSION['usuario_id']) {
        die("Error de seguridad: No puedes borrar tu propia cuenta activa.");
    }
    
    $conn->query("DELETE FROM usuarios WHERE id = $id_borrar");
    header("Location: ../frontend/admin/usuarios.php?msg=borrado");
    exit();
}

header("Location: ../frontend/admin/usuarios.php");
exit();
?>  