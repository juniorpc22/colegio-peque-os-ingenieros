<?php
// frontend/admin/historial.php
include '../../config/db.php';
include '../includes/header_admin.php';

// FECHAS POR DEFECTO (Del 1ro del mes actual hasta hoy)
$fecha_inicio = isset($_GET['inicio']) ? $_GET['inicio'] : date('Y-m-01');
$fecha_fin    = isset($_GET['fin']) ? $_GET['fin'] : date('Y-m-d');
$grado_filtro = isset($_GET['grado']) ? $_GET['grado'] : '';

// CONSTRUIR CONSULTA
$sql = "SELECT a.*, al.nombres, al.apellidos, al.grado, al.seccion 
        FROM asistencias a
        JOIN alumnos al ON a.id_alumno = al.id
        WHERE a.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";

if($grado_filtro != '') {
    $sql .= " AND al.grado = '$grado_filtro'";
}

$sql .= " ORDER BY a.fecha DESC, a.hora_llegada ASC";
$resultado = $conn->query($sql);
?>

<div class="container pb-5">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold m-0"><i class="bi bi-calendar3-range me-2 text-primary"></i>Historial de Asistencia</h5>
        </div>
        <div class="card-body bg-light">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Desde:</label>
                    <input type="date" name="inicio" class="form-control" value="<?php echo $fecha_inicio; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Hasta:</label>
                    <input type="date" name="fin" class="form-control" value="<?php echo $fecha_fin; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Filtrar Grado:</label>
                    <select name="grado" class="form-select">
                        <option value="">- Todos -</option>
                        <option value="1" <?php if($grado_filtro=='1') echo 'selected'; ?>>1ro</option>
                        <option value="2" <?php if($grado_filtro=='2') echo 'selected'; ?>>2do</option>
                        <option value="3" <?php if($grado_filtro=='3') echo 'selected'; ?>>3ro</option>
                        <option value="4" <?php if($grado_filtro=='4') echo 'selected'; ?>>4to</option>
                        <option value="5" <?php if($grado_filtro=='5') echo 'selected'; ?>>5to</option>
                        <option value="6" <?php if($grado_filtro=='6') echo 'selected'; ?>>6to</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-2"></i>Buscar</button>
                    <button type="button" onclick="exportarExcel()" class="btn btn-success" title="Descargar Excel">
                        <i class="bi bi-file-earmark-excel-fill"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaHistorial">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Fecha</th>
                            <th>Alumno</th>
                            <th class="text-center">Grado</th>
                            <th class="text-center">Hora</th>
                            <th class="text-center">Estado</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($resultado->num_rows > 0): ?>
                            <?php while($row = $resultado->fetch_assoc()): ?>
                                <?php 
                                    // Colores
                                    $bg = "bg-secondary";
                                    if($row['estado'] == 'PUNTUAL') $bg = "bg-success";
                                    if($row['estado'] == 'TARDE') $bg = "bg-warning text-dark";
                                    if($row['estado'] == 'JUSTIFICADO') $bg = "bg-info text-dark";
                                    if($row['estado'] == 'FALTA') $bg = "bg-danger";
                                    
                                    $hora = ($row['hora_llegada']) ? date("h:i A", strtotime($row['hora_llegada'])) : '-';
                                    $fecha_fmt = date("d/m/Y", strtotime($row['fecha']));
                                ?>
                                <tr>
                                    <td class="ps-4 font-monospace"><?php echo $fecha_fmt; ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo $row['apellidos'] . ", " . $row['nombres']; ?></div>
                                    </td>
                                    <td class="text-center"><?php echo $row['grado']."° ".$row['seccion']; ?></td>
                                    <td class="text-center font-monospace"><?php echo $hora; ?></td>
                                    <td class="text-center">
                                        <span class="badge <?php echo $bg; ?>"><?php echo $row['estado']; ?></span>
                                    </td>
                                    <td class="small text-muted text-truncate" style="max-width: 150px;">
                                        <?php echo isset($row['observacion']) ? $row['observacion'] : ''; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-5">No hay registros en este rango de fechas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function exportarExcel() {
    let tabla = document.getElementById("tablaHistorial");
    let html = tabla.outerHTML;
    
    // Generar un blob compatible con Excel
    let url = 'data:application/vnd.ms-excel,' + escape(html);
    let link = document.createElement("a");
    link.href = url;
    link.download = "Reporte_Asistencia.xls";
    link.click();
}
</script>

</body>
</html>