<?php
// backend/obtener_estadisticas.php
header('Content-Type: application/json');
include '../config/db.php';
date_default_timezone_set('America/Lima');
$fecha_hoy = date('Y-m-d');

// Contamos los estados de hoy
$sql = "SELECT estado, COUNT(*) as total FROM asistencias WHERE fecha = ? GROUP BY estado";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $fecha_hoy);
$stmt->execute();
$res = $stmt->get_result();

$stats = ['PUNTUAL' => 0, 'TARDE' => 0, 'FALTA' => 0, 'JUSTIFICADO' => 0];
while($row = $res->fetch_assoc()){
    $stats[$row['estado']] = (int)$row['total'];
}

echo json_encode($stats);
?>