<?php
// backend/actualizar_perfil.php
session_start();
include '../config/db.php';

// 1. SEGURIDAD: Verificamos que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado.");
}

$accion = $_POST['accion'];

// 2. SEGURIDAD: Nunca confiamos en el ID que viene del formulario HTML. 
// Usamos el ID de la sesión, así nadie puede hackear el perfil de otra persona.
$id = $_SESSION['usuario_id']; 

// --- CASO 1: ACTUALIZAR DATOS (NOMBRE/USUARIO) ---
if ($accion == 'datos') {
    $nombre = trim($_POST['nombre']);
    $usuario = trim($_POST['usuario']);

    // 3. SEGURIDAD: Uso de Consultas Preparadas para evitar Inyección SQL
    $sql = "UPDATE usuarios SET nombre=?, usuario=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $nombre, $usuario, $id);
    
    if ($stmt->execute()) {
        // Actualizamos la sesión para que el saludo del menú cambie al instante
        $_SESSION['nombre_usuario'] = $usuario; 
        header("Location: ../frontend/admin/perfil.php?msg=ok_datos");
    } else {
        echo "Error: " . $conn->error;
    }
    exit();
}

// --- CASO 2: ACTUALIZAR PASSWORD ---
if ($accion == 'password') {
    $pass_actual = $_POST['pass_actual'];
    $pass_nueva  = $_POST['pass_nueva'];
    $pass_conf   = $_POST['pass_confirmar'];

    // 1. Verificar que la nueva y la confirmación coincidan
    if ($pass_nueva !== $pass_conf) {
        header("Location: ../frontend/admin/perfil.php?msg=error_coincidencia");
        exit();
    }

    // 2. Obtener la contraseña actual de forma segura
    $sql_check = "SELECT password FROM usuarios WHERE id=?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $fila = $stmt_check->get_result()->fetch_assoc();
    $pass_bd = $fila['password'];

    // Validar si la contraseña actual es correcta (Soporta claves viejas planas o las nuevas encriptadas)
    $es_correcta = false;
    if ($pass_actual === $pass_bd) {
        $es_correcta = true;
    } else if (password_verify($pass_actual, $pass_bd)) {
        $es_correcta = true;
    }

    if ($es_correcta) {
        // 4. SEGURIDAD CRÍTICA: Encriptamos la nueva contraseña obligatoriamente
        $nueva_hash = password_hash($pass_nueva, PASSWORD_DEFAULT);
        
        $sql_update = "UPDATE usuarios SET password=? WHERE id=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $nueva_hash, $id);
        $stmt_update->execute();
        
        header("Location: ../frontend/admin/perfil.php?msg=ok_pass");
    } else {
        header("Location: ../frontend/admin/perfil.php?msg=error_pass");
    }
    exit();
}
?>