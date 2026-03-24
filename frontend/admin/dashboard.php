<?php
// frontend/admin/dashboard.php
include_once '../../config/auth.php'; // 1. SEGURIDAD: Solo entran usuarios logueados
include '../../config/db.php';        // 2. CONEXIÓN: Tu BD actual
include '../includes/header_admin.php'; // 3. INTERFAZ: Tu menú superior

date_default_timezone_set('America/Lima');
$fecha_hoy = date('Y-m-d');

// --- 4. CONSULTA DE CONTADORES RÁPIDOS ---
// Total de alumnos registrados en el sistema
$res_total = $conn->query("SELECT COUNT(*) as total FROM alumnos");
$total_alumnos = $res_total->fetch_assoc()['total'];

// Asistencias registradas hoy
$res_hoy = $conn->query("SELECT 
    SUM(CASE WHEN estado = 'PUNTUAL' THEN 1 ELSE 0 END) as puntuales,
    SUM(CASE WHEN estado = 'TARDE' THEN 1 ELSE 0 END) as tardanzas
    FROM asistencias WHERE fecha = '$fecha_hoy'");
$stats_hoy = $res_hoy->fetch_assoc();
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold text-dark"><i class="bi bi-speedometer2 me-2"></i>Panel de Control</h2>
            <p class="text-muted">Bienvenido, <strong><?php echo $_SESSION['nombre_usuario']; ?></strong>. Resumen del día: <?php echo date("d/m/Y"); ?></p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="opacity-75">Total Alumnos</small>
                        <h3 class="fw-bold mb-0"><?php echo $total_alumnos; ?></h3>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="opacity-75">Puntuales Hoy</small>
                        <h3 class="fw-bold mb-0"><?php echo $stats_hoy['puntuales'] ?? 0; ?></h3>
                    </div>
                    <i class="bi bi-check-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="opacity-75">Tardanzas Hoy</small>
                        <h3 class="fw-bold mb-0"><?php echo $stats_hoy['tardanzas'] ?? 0; ?></h3>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="opacity-75">Niveles</small>
                        <h3 class="fw-bold mb-0">2</h3>
                    </div>
                    <i class="bi bi-mortarboard fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7 mb-4">
            <div class="card shadow border-0 p-4 h-100">
                <h5 class="fw-bold mb-3">Distribución de Asistencia</h5>
                <canvas id="graficoAsistencia" style="max-height: 350px;"></canvas>
            </div>
        </div>

        <div class="col-md-5 mb-4">
            <div class="card shadow border-0 p-4 h-100">
                <h5 class="fw-bold mb-3">Acciones Rápidas</h5>
                <div class="list-group list-group-flush">
                    <a href="alumnos.php" class="list-group-item list-group-item-action py-3">
                        <i class="bi bi-person-plus me-2 text-primary"></i> Gestionar Estudiantes
                    </a>
                    <a href="reporte_diario.php" class="list-group-item list-group-item-action py-3">
                        <i class="bi bi-file-earmark-text me-2 text-success"></i> Ver Reporte de Hoy
                    </a>
                    <a href="../asistencia/escanear.php" class="list-group-item list-group-item-action py-3">
                        <i class="bi bi-qr-code-scan me-2 text-danger"></i> Abrir Escáner de Carnets
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Llamamos al backend que creamos para obtener datos frescos
    fetch('../../backend/obtener_estadisticas.php')
    .then(r => r.json())
    .then(data => {
        const ctx = document.getElementById('graficoAsistencia').getContext('2d');
        new Chart(ctx, {
            type: 'bar', // Cambiado a barras para que se vea más "Ingeniería"
            data: {
                labels: ['Puntual', 'Tarde', 'Falta', 'Justificado'],
                datasets: [{
                    label: 'Cantidad de Alumnos',
                    data: [data.PUNTUAL, data.TARDE, data.FALTA, data.JUSTIFICADO],
                    backgroundColor: ['#198754', '#ffc107', '#dc3545', '#0dcaf0'],
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { display: false } }
            }
        });
    });
});
</script>
</body>
</html>