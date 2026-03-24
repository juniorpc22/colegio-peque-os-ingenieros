<?php
// frontend/admin/usuarios.php
session_start(); // Aseguramos que la sesión esté iniciada

// --- GUARDIÁN DE SEGURIDAD ---
// Si no hay sesión activa, o si el rol NO es "SuperAdmin"
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'SuperAdmin') {
    // Lo pateamos de vuelta al dashboard con un mensaje de error
    header("Location: dashboard.php?error=acceso_denegado");
    exit(); // Detiene la carga de la página
}
// -----------------------------

include '../../config/db.php';
include '../includes/header_admin.php';

// ... (Aquí sigue tu código normal para mostrar la tabla) ...
?>
    <?php
// backend/gestion_usuarios.php
session_start();
include '../config/db.php';

// Verificamos que los datos lleguen por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $accion = $_POST['accion'];

    // CASO A: CREAR USUARIO
    if ($accion == 'crear') {
        // Limpiamos los datos
        $usuario = mysqli_real_escape_string($conn, trim($_POST['usuario']));
        $password_plano = $_POST['password'];
        $id_rol = intval($_POST['id_rol']);

        // Encriptamos la clave nueva
        $password_hash = password_hash($password_plano, PASSWORD_DEFAULT);

        // SQL Simplificado (Solo las columnas que vimos en tu captura de phpMyAdmin)
        $sql = "INSERT INTO usuarios (usuario, password, id_rol) VALUES (?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $usuario, $password_hash, $id_rol);

        if ($stmt->execute()) {
            // Si funciona, regresamos a la tabla con mensaje de éxito
            header("Location: ../frontend/admin/usuarios.php?msg=creado");
        } else {
            // Si hay error de SQL, lo mostramos en pantalla
            die("Error en la Base de Datos: " . $conn->error);
        }
    }

    // CASO B: BORRAR USUARIO
    if ($accion == 'borrar') {
        $id_borrar = intval($_GET['id']); // Ajustado para recibirlo por URL desde el botón
        $conn->query("DELETE FROM usuarios WHERE id = $id_borrar");
        header("Location: ../frontend/admin/usuarios.php?msg=borrado");
    }
} else {
    // Si entran directo al archivo sin el formulario
    die("Acceso no permitido");
}
?>