<?php
// backend/obtener_reporte_director.php
include '../config/db.php';
header('Content-Type: application/json');

$id_grado = $_GET['id_grado'];
$fecha = date('Y-m-d');

$sql = "SELECT a.nombres, a.apellidos, asi.hora, asi.estado 
        FROM asistencias asi
        INNER JOIN alumnos a ON asi.id_alumno = a.id
        WHERE a.id_grado_seccion = ? AND asi.fecha = ?
        ORDER BY asi.hora ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id_grado, $fecha);
$stmt->execute();
$res = $stmt->get_result();

$asistencias = [];
while($row = $res->fetch_assoc()){
    $asistencias[] = $row;
}

echo json_encode($asistencias);
?>