<?php
$host = "localhost";
$user = "root";
$pass = ""; // En XAMPP suele estar vacío
$db   = "pequenos_ingenieros";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Importante para tildes y eñes
$conn->set_charset("utf8");
?>