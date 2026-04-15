<?php
// backend/guardar_asistencia_aula.php
session_start();
include '../config/db.php';

// Seguridad: Solo Profesores (y SuperAdmin) pueden guardar asistencia de aula
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['SuperAdmin', 'Profesor'])) {
    die("Acceso no autorizado");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_curso']) && isset($_POST['asistencia'])) {
    
    $id_curso = intval($_POST['id_curso']);
    $asistencias = $_POST['asistencia']; // Esto es un Array con [id_alumno => 'Estado']
    $fecha_hoy = date('Y-m-d');

    // Usamos INSERT ... ON DUPLICATE KEY UPDATE 
    // Esto es magia de SQL: Si ya habían pasado lista hoy, lo actualiza. Si no, lo crea.
    $sql = "INSERT INTO asistencia_cursos (id_alumno, id_curso, fecha, estado) VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE estado = VALUES(estado)";
    
    $stmt = $conn->prepare($sql);

    // Recorremos a cada alumno que vino en el formulario
    foreach ($asistencias as $id_alumno => $estado) {
        $id_alum_int = intval($id_alumno);
        $estado_safe = mysqli_real_escape_string($conn, $estado);
        
        $stmt->bind_param("iiss", $id_alum_int, $id_curso, $fecha_hoy, $estado_safe);
        $stmt->execute();
    }

    // Regresamos al profe a su lista con un mensaje de éxito
    header("Location: ../frontend/profesor/pasar_lista.php?id_curso=" . $id_curso . "&msg=guardado");
    exit();
}

header("Location: ../frontend/admin/dashboard.php");
exit();
?>