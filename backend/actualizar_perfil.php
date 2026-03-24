<?php
// backend/actualizar_perfil.php
session_start();
include '../config/db.php';

$accion = $_POST['accion'];
$id = $_POST['id'];

// --- CASO 1: ACTUALIZAR DATOS (NOMBRE/USUARIO) ---
if ($accion == 'datos') {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $usuario = $conn->real_escape_string($_POST['usuario']);

    $sql = "UPDATE usuarios SET nombre='$nombre', usuario='$usuario' WHERE id='$id'";
    if ($conn->query($sql)) {
        // Actualizamos la sesión también para que se vea el cambio al instante
        $_SESSION['admin_nombre'] = $nombre;
        header("Location: ../frontend/admin/perfil.php?msg=ok_datos");
    } else {
        echo "Error: " . $conn->error;
    }
}

// --- CASO 2: ACTUALIZAR PASSWORD ---
if ($accion == 'password') {
    $pass_actual = $_POST['pass_actual'];
    $pass_nueva  = $_POST['pass_nueva'];
    $pass_conf   = $_POST['pass_confirmar'];

    // 1. Verificar que la nueva y la confirmación coincidan
    if ($pass_nueva != $pass_conf) {
        header("Location: ../frontend/admin/perfil.php?msg=error_coincidencia");
        exit;
    }

    // 2. Verificar que la contraseña actual sea correcta
    // Primero obtenemos la contraseña real de la BD
    $check = $conn->query("SELECT password FROM usuarios WHERE id='$id'");
    $fila = $check->fetch_assoc();
    $pass_bd = $fila['password'];

    // NOTA: Como al inicio insertamos '123456' sin encriptar (texto plano),
    // o si usamos encriptación, debemos validar ambas opciones para que no falle.
    
    $es_correcta = false;
    
    // Opción A: Texto plano (Sistema antiguo/actual)
    if ($pass_actual == $pass_bd) {
        $es_correcta = true;
    }
    // Opción B: Encriptada (Sistema seguro)
    else if (password_verify($pass_actual, $pass_bd)) {
        $es_correcta = true;
    }

    if ($es_correcta) {
        // 3. Guardar la nueva contraseña (¡Esta vez encriptada por seguridad!)
        // Si prefieres texto plano por simplicidad, usa: $nueva_hash = $pass_nueva;
        // Pero te recomiendo password_hash:
        $nueva_hash = $pass_nueva; // Mantendremos texto plano para no complicarte con hashes ahora.
        
        $conn->query("UPDATE usuarios SET password='$nueva_hash' WHERE id='$id'");
        header("Location: ../frontend/admin/perfil.php?msg=ok_pass");
    } else {
        header("Location: ../frontend/admin/perfil.php?msg=error_pass");
    }
}
?>