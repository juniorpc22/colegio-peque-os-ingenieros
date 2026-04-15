<?php
// frontend/admin/dashboard.php
session_start();

if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['SuperAdmin', 'Admin', 'Director'])) {
    header("Location: ../../index.php");
    exit(); 
}

include '../../config/db.php';
include '../includes/header_admin.php';

// --- 1. ESTADÍSTICAS RÁPIDAS ---
$tot_alumnos = $conn->query("SELECT COUNT(*) as total FROM alumnos")->fetch_assoc()['total'];
$tot_profesores = $conn->query("SELECT COUNT(u.id) as total FROM usuarios u JOIN roles r ON u.id_rol = r.id WHERE r.nombre_rol = 'Profesor'")->fetch_assoc()['total'];
$tot_cursos = $conn->query("SELECT COUNT(*) as total FROM cursos")->fetch_assoc()['total'];

// --- 2. ASISTENCIA DE HOY (Para el Gráfico) ---
$fecha_hoy = date('Y-m-d');
$sql_asistencia = "SELECT estado, COUNT(*) as total FROM asistencias WHERE fecha = '$fecha_hoy' GROUP BY estado";
$res_asistencia = $conn->query($sql_asistencia);

$asistencia_hoy = ['PUNTUAL' => 0, 'TARDE' => 0, 'FALTA' => 0, 'JUSTIFICADO' => 0];
$hubo_asistencia_hoy = false;

while($row = $res_asistencia->fetch_assoc()) {
    $asistencia_hoy[$row['estado']] = $row['total'];
    $hubo_asistencia_hoy = true;
}
$tot_puntuales = $asistencia_hoy['PUNTUAL'];

// --- 3. ÚLTIMAS NOTICIAS ---
$res_noticias = $conn->query("SELECT * FROM noticias ORDER BY fecha_creacion DESC LIMIT 5");
?>

<div class="container mt-4 pb-5">
    
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'noticia_ok'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="bi bi-megaphone-fill me-2"></i> ¡Aviso publicado exitosamente para los padres!
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'noticia_borrada'): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm">
            <i class="bi bi-trash me-2"></i> Aviso eliminado.
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">Panel de Control</h3>
            <p class="text-muted small">Centro de mando e información general.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-6 shadow-sm"><i class="bi bi-calendar3 me-2"></i><?php echo date('d / m / Y'); ?></span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-bottom border-primary border-4 h-100"
                 style="transition: transform 0.2s; cursor: pointer;" 
                 onmouseover="this.style.transform='translateY(-5px)'" 
                 onmouseout="this.style.transform='translateY(0)'"
                 onclick="window.location.href='alumnos.php'">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted text-uppercase mb-1" style="font-size: 0.8rem; font-weight: 700;">Estudiantes</p>
                        <h2 class="fw-bold text-dark mb-0"><?php echo $tot_alumnos; ?></h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary"><i class="bi bi-people-fill fs-2"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-bottom border-success border-4 h-100"
                 style="transition: transform 0.2s; cursor: pointer;" 
                 onmouseover="this.style.transform='translateY(-5px)'" 
                 onmouseout="this.style.transform='translateY(0)'"
                 onclick="window.location.href='usuarios.php'">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted text-uppercase mb-1" style="font-size: 0.8rem; font-weight: 700;">Plana Docente</p>
                        <h2 class="fw-bold text-dark mb-0"><?php echo $tot_profesores; ?></h2>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success"><i class="bi bi-person-video3 fs-2"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-bottom border-info border-4 h-100"
                 style="transition: transform 0.2s; cursor: pointer;" 
                 onmouseover="this.style.transform='translateY(-5px)'" 
                 onmouseout="this.style.transform='translateY(0)'"
                 onclick="window.location.href='cursos.php'">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted text-uppercase mb-1" style="font-size: 0.8rem; font-weight: 700;">Materias</p>
                        <h2 class="fw-bold text-dark mb-0"><?php echo $tot_cursos; ?></h2>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info"><i class="bi bi-journal-bookmark-fill fs-2"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-bottom border-warning border-4 h-100"
                 style="transition: transform 0.2s; cursor: pointer;" 
                 onmouseover="this.style.transform='translateY(-5px)'" 
                 onmouseout="this.style.transform='translateY(0)'"
                 onclick="window.location.href='reporte_diario.php'">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted text-uppercase mb-1" style="font-size: 0.8rem; font-weight: 700;">Puntuales Hoy</p>
                        <h2 class="fw-bold text-dark mb-0"><?php echo $tot_puntuales; ?></h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning"><i class="bi bi-door-open-fill fs-2"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="fw-bold text-secondary mb-0"><i class="bi bi-megaphone-fill text-primary me-2"></i>Avisos del Colegio (Portal Padres)</h6>
                    <button class="btn btn-sm btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalNoticia">
                        <i class="bi bi-plus-circle me-1"></i> Publicar Aviso
                    </button>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if($res_noticias && $res_noticias->num_rows > 0): ?>
                            <?php while($noticia = $res_noticias->fetch_assoc()): ?>
                                <?php 
                                    $badge_tipo = ($noticia['tipo'] == 'alerta') ? '<span class="badge bg-danger rounded-pill">Alerta</span>' : '<span class="badge bg-info rounded-pill text-dark">Evento</span>';
                                ?>
                                <li class="list-group-item p-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold mb-1"><?php echo $noticia['titulo']; ?> <?php echo $badge_tipo; ?></h6>
                                        <p class="text-muted small mb-0 text-truncate" style="max-width: 400px;"><?php echo $noticia['descripcion']; ?></p>
                                        <small class="text-secondary" style="font-size: 0.7rem;"><i class="bi bi-clock me-1"></i><?php echo date("d/m/Y h:i A", strtotime($noticia['fecha_creacion'])); ?></small>
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger border-0" onclick="borrarNoticia(<?php echo $noticia['id']; ?>)">
                                        <i class="bi bi-trash fs-5"></i>
                                    </button>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li class="list-group-item p-4 text-center text-muted small">No hay avisos publicados actualmente.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-secondary mb-0"><i class="bi bi-pie-chart-fill text-success me-2"></i>Asistencia de Hoy (Puerta)</h6>
                </div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <?php if($hubo_asistencia_hoy): ?>
                        <div style="width: 200px; height: 200px;">
                            <canvas id="graficoAsistencia"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted">
                            <i class="bi bi-door-closed fs-1 d-block mb-2 opacity-50"></i>
                            <p class="small">Aún no hay escaneos en la puerta principal el día de hoy.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white border-0 text-center pb-3">
                    <a href="reporte_diario.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Ver reporte completo</a>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="modalNoticia" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Publicar Nuevo Aviso</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../../backend/gestion_noticias.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="crear">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Título del Aviso</label>
                        <input type="text" name="titulo" class="form-control" required placeholder="Ej: Suspensión de clases, Día de la Madre...">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tipo de Aviso</label>
                        <select name="tipo" class="form-select">
                            <option value="evento">Evento / Informativo</option>
                            <option value="alerta">Alerta Urgente</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Descripción (Se verá en el portal)</label>
                        <textarea name="descripcion" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Imagen referencial (Opcional)</label>
                        <input type="file" name="imagen" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold">Publicar Aviso</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    function borrarNoticia(id) {
        if(confirm('¿Seguro que deseas eliminar esta noticia? Desaparecerá del portal de padres.')) {
            window.location.href = "../../backend/gestion_noticias.php?accion=borrar&id=" + id;
        }
    }

    <?php if($hubo_asistencia_hoy): ?>
    // Generar el gráfico de Chart.js
    const ctx = document.getElementById('graficoAsistencia').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Puntual', 'Tarde', 'Justificado', 'Falta'],
            datasets: [{
                data: [
                    <?php echo $asistencia_hoy['PUNTUAL']; ?>, 
                    <?php echo $asistencia_hoy['TARDE']; ?>, 
                    <?php echo $asistencia_hoy['JUSTIFICADO']; ?>, 
                    <?php echo $asistencia_hoy['FALTA']; ?>
                ],
                backgroundColor: ['#198754', '#ffc107', '#0dcaf0', '#dc3545'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } },
            cutout: '70%' 
        }
    });
    <?php endif; ?>
</script>
</body>
</html>