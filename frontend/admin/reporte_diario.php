<?php
// frontend/admin/reporte_diario.php
include_once '../../config/auth.php'; // Seguridad por roles
include '../../config/db.php';
include '../includes/header_admin.php';

date_default_timezone_set('America/Lima');
$fecha_hoy = date('Y-m-d');

// --- 1. FILTROS ---
$fecha_filtro = isset($_GET['fecha']) ? $_GET['fecha'] : $fecha_hoy;
$estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : ''; // '' = Todos

// --- 2. CONSULTA MAESTRA ACTUALIZADA (CON GRADOS Y SECCIONES) ---
// Unimos Alumnos con Grados_Secciones y luego con Asistencias
$sql = "SELECT al.id as id_alumno, al.nombres, al.apellidos, 
               gs.nivel, gs.grado as num_grado, gs.seccion, 
               a.id as id_asistencia, a.hora as hora_llegada, a.estado, a.observacion
        FROM alumnos al 
        INNER JOIN grados_secciones gs ON al.id_grado_seccion = gs.id
        LEFT JOIN asistencias a ON al.id = a.id_alumno AND a.fecha = '$fecha_filtro' 
        ORDER BY gs.nivel DESC, gs.grado ASC, gs.seccion ASC, al.apellidos ASC";

$resultado = $conn->query($sql);

// --- 3. CÁLCULO DE ESTADÍSTICAS EN TIEMPO REAL ---
$stats = ['TOTAL'=>0, 'ASISTIERON'=>0, 'PENDIENTE'=>0, 'PUNTUAL'=>0, 'TARDE'=>0, 'FALTA'=>0, 'JUSTIFICADO'=>0];
$datos_alumnos = [];

if($resultado) {
    while($row = $resultado->fetch_assoc()) {
        $stats['TOTAL']++;
        
        // Determinar estado real
        if (empty($row['estado'])) {
            $row['estado_real'] = 'PENDIENTE';
            $stats['PENDIENTE']++;
        } else {
            $row['estado_real'] = $row['estado'];
            $stats['ASISTIERON']++;
            if(isset($stats[$row['estado']])) {
                $stats[$row['estado']]++;
            }
        }
        
        // Aplicar Filtro Visual (Tu lógica original)
        if ($estado_filtro != '' && $estado_filtro != 'TODOS') {
            if ($estado_filtro == 'ASISTIERON' && $row['estado_real'] == 'PENDIENTE') continue;
            if ($estado_filtro == 'PENDIENTE' && $row['estado_real'] != 'PENDIENTE') continue;
            if ($estado_filtro != 'ASISTIERON' && $estado_filtro != 'PENDIENTE' && $row['estado_real'] != $estado_filtro) continue;
        }
        
        $datos_alumnos[] = $row;
    }
}
?>

<div class="container pb-5">
    
    <div class="card shadow-sm border-0 mb-4 mt-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h3 class="fw-bold text-dark mb-0">Reporte Diario</h3>
                    <p class="text-muted mb-0 small">Fecha: <strong><?php echo date("d/m/Y", strtotime($fecha_filtro)); ?></strong></p>
                </div>
                
                <div class="col-md-8">
                    <form method="GET" class="d-flex gap-2 justify-content-md-end mt-3 mt-md-0">
                        <input type="date" name="fecha" value="<?php echo $fecha_filtro; ?>" class="form-control form-control-sm" style="width: 130px;" onchange="this.form.submit()">
                        
                        <select name="estado" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            <option value="TODOS" <?php if($estado_filtro=='TODOS') echo 'selected'; ?>>Ver Todo</option>
                            <option value="ASISTIERON" <?php if($estado_filtro=='ASISTIERON') echo 'selected'; ?>>✅ Asistieron</option>
                            <option value="PENDIENTE" <?php if($estado_filtro=='PENDIENTE') echo 'selected'; ?>>⏳ Pendientes</option>
                            <option value="PUNTUAL" <?php if($estado_filtro=='PUNTUAL') echo 'selected'; ?>>🟢 Puntuales</option>
                            <option value="TARDE" <?php if($estado_filtro=='TARDE') echo 'selected'; ?>>⚠️ Tardanzas</option>
                            <option value="FALTA" <?php if($estado_filtro=='FALTA') echo 'selected'; ?>>❌ Faltas</option>
                        </select>
                    </form>
                </div>
            </div>
            
            <div class="d-flex flex-wrap gap-2 mt-3 justify-content-center justify-content-md-start">
                <span class="badge bg-light text-dark border">Total: <?php echo $stats['TOTAL']; ?></span>
                <span class="badge bg-success">Puntual: <?php echo $stats['PUNTUAL']; ?></span>
                <span class="badge bg-warning text-dark">Tarde: <?php echo $stats['TARDE']; ?></span>
                <span class="badge bg-secondary">Pendiente: <?php echo $stats['PENDIENTE']; ?></span>
                <span class="badge bg-danger">Faltas: <?php echo $stats['FALTA']; ?></span>
            </div>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Alumno</th>
                            <th class="text-center">Grado / Nivel</th>
                            <th class="text-center">Hora Ingreso</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($datos_alumnos) > 0): ?>
                            <?php foreach($datos_alumnos as $row): ?>
                                <?php 
                                    $estado = $row['estado_real'];
                                    $clase_badge = "bg-secondary"; 
                                    $icono = "bi-hourglass-split";

                                    if($estado == 'PUNTUAL') { $clase_badge = "bg-success"; $icono = "bi-check-circle-fill"; }
                                    if($estado == 'TARDE')   { $clase_badge = "bg-warning text-dark"; $icono = "bi-exclamation-circle-fill"; }
                                    if($estado == 'JUSTIFICADO') { $clase_badge = "bg-info text-dark"; $icono = "bi-file-medical-fill"; }
                                    if($estado == 'FALTA')   { $clase_badge = "bg-danger"; $icono = "bi-x-circle-fill"; }

                                    $ingreso = ($row['hora_llegada']) ? date("h:i A", strtotime($row['hora_llegada'])) : '<span class="text-muted small">-</span>';
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?php echo $row['apellidos'] . ", " . $row['nombres']; ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border" style="font-size: 0.75rem;">
                                            <?php echo $row['num_grado']."° ".$row['seccion']." - ".$row['nivel']; ?>
                                        </span>
                                    </td>
                                    
                                    <td class="text-center font-monospace text-primary fw-bold"><?php echo $ingreso; ?></td>
                                    
                                    <td class="text-center">
                                        <span class="badge <?php echo $clase_badge; ?>">
                                            <i class="bi <?php echo $icono; ?> me-1"></i> <?php echo $estado; ?>
                                        </span>
                                    </td>
                                    
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-secondary border-0" 
                                                onclick="abrirJustificar('<?php echo $row['id_alumno']; ?>', '<?php echo $row['nombres'] . ' ' . $row['apellidos']; ?>')">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted">No se encontraron registros.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalJustificar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Gestionar Asistencia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../../backend/justificar.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_alumno" id="id_alumno">
                    <input type="hidden" name="fecha" value="<?php echo $fecha_filtro; ?>">
                    
                    <p class="mb-3">Alumno: <strong id="nombre_alumno_modal" class="text-primary"></strong></p>
                    
                    <label class="form-label fw-bold text-muted small mb-2">Selecciona Acción:</label>
                    <div class="d-grid gap-2 mb-4">
                        <input type="radio" class="btn-check" name="accion" id="opt_justificar" value="JUSTIFICAR">
                        <label class="btn btn-outline-info text-start" for="opt_justificar">
                            <i class="bi bi-file-medical-fill me-2"></i>Justificar (Médico/Permiso)
                        </label>
                        <input type="radio" class="btn-check" name="accion" id="opt_falta" value="FALTA">
                        <label class="btn btn-outline-danger text-start" for="opt_falta">
                            <i class="bi bi-x-circle-fill me-2"></i>Registrar FALTA
                        </label>
                        <input type="radio" class="btn-check" name="accion" id="opt_borrar" value="BORRAR">
                        <label class="btn btn-outline-secondary text-start" for="opt_borrar">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Restablecer a "Pendiente"
                        </label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function abrirJustificar(id, nombre) {
        document.getElementById('id_alumno').value = id;
        document.getElementById('nombre_alumno_modal').innerText = nombre;
        new bootstrap.Modal(document.getElementById('modalJustificar')).show();
    }
</script>