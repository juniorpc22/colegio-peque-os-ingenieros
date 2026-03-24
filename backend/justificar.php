<?php
// backend/justificar.php
include '../config/db.php';
date_default_timezone_set('America/Lima');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $id_alumno = $_POST['id_alumno'];
    $fecha = $_POST['fecha'];
    $tipo_accion = $_POST['accion']; 
    $observacion = strtoupper(trim($_POST['observacion']));

    // Verificar si ya existe registro para ese alumno y fecha
    $sql_check = "SELECT id FROM asistencias WHERE id_alumno = '$id_alumno' AND fecha = '$fecha'";
    $check = $conn->query($sql_check);

    // --- OPCIÓN 1: BORRAR / RESTABLECER ---
    if ($tipo_accion == 'BORRAR') {
        if ($check->num_rows > 0) {
            $conn->query("DELETE FROM asistencias WHERE id_alumno = '$id_alumno' AND fecha = '$fecha'");
        }
        // Si no existía, no hacemos nada (ya estaba borrado/pendiente)
    }
    
    // --- OPCIÓN 2 y 3: JUSTIFICAR O FALTA ---
    else {
        // Definir estado
        $estado = ($tipo_accion == 'JUSTIFICAR') ? 'JUSTIFICADO' : 'FALTA';

        if ($check->num_rows > 0) {
            // EDITAR existente: Ponemos hora NULL y cambiamos estado
            $fila = $check->fetch_assoc();
            $sql = "UPDATE asistencias SET estado = '$estado', hora_llegada = NULL, observacion = '$observacion' WHERE id = '" . $fila['id'] . "'";
        } else {
            // CREAR nuevo (porque estaba Pendiente)
            $sql = "INSERT INTO asistencias (id_alumno, fecha, hora_llegada, estado, observacion) VALUES ('$id_alumno', '$fecha', NULL, '$estado', '$observacion')";
        }

        if (!$conn->query($sql)) {
            // Si falla, mostramos el error técnico para que sepas qué pasó
            die("Error SQL: " . $conn->error . " <br> Intenta ejecutar: ALTER TABLE asistencias MODIFY hora_llegada TIME NULL;");
        }
    }

    // Volver al reporte
    header("Location: ../frontend/admin/reporte_diario.php?fecha=$fecha&msg=ok");
    exit;
}
?>