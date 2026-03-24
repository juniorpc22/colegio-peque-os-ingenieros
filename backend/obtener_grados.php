<?php
// backend/obtener_grados.php
include '../config/db.php';

$sql = "SELECT id, nivel, grado, seccion FROM grados_secciones ORDER BY nivel, grado, seccion";
$result = $conn->query($sql);

$opciones = "";
while ($row = $result->fetch_assoc()) {
    $opciones .= "<option value='{$row['id']}'>{$row['nivel']} - {$row['grado']}° {$row['seccion']}</option>";
}
echo $opciones;
?>  