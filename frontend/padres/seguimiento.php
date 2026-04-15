<?php
// frontend/padres/seguimiento.php
include '../../config/db.php';

if (!isset($_GET['dni']) || empty($_GET['dni'])) { header("Location: index.php"); exit; }
$dni = $conn->real_escape_string($_GET['dni']);

// 1. DATOS DEL ALUMNO (AQUÍ ESTÁ LA MAGIA DEL JOIN PARA TRAER EL NOMBRE DEL SALÓN)
$sql_alumno = "SELECT a.*, gs.nivel, gs.grado, gs.seccion 
               FROM alumnos a 
               LEFT JOIN grados_secciones gs ON a.id_grado_seccion = gs.id 
               WHERE a.dni = '$dni'";
$res_alumno = $conn->query($sql_alumno);

if ($res_alumno->num_rows == 0) { header("Location: index.php?error=notfound"); exit; }
$alumno = $res_alumno->fetch_assoc();
$id_alumno = $alumno['id'];
$foto = !empty($alumno['foto']) ? $alumno['foto'] : 'default.jpg';

// ARMAMOS EL NOMBRE DEL SALÓN
$nombre_salon = $alumno['nivel'] ? $alumno['nivel'] . " - " . $alumno['grado'] . "° " . $alumno['seccion'] : 'Sin asignar';

// 2. ESTADÍSTICAS ASISTENCIA (Puerta Principal)
$sql_stats = "SELECT estado, COUNT(*) as total FROM asistencias WHERE id_alumno = '$id_alumno' GROUP BY estado";
$res_stats = $conn->query($sql_stats);
$stats = ['PUNTUAL' => 0, 'TARDE' => 0, 'JUSTIFICADO' => 0, 'FALTA' => 0];
$total_asistencias = 0;
while($row = $res_stats->fetch_assoc()) {
    $stats[$row['estado']] = $row['total'];
    $total_asistencias += $row['total'];
}

// 3. NOTICIAS (Últimas 10)
$sql_noticias = "SELECT * FROM noticias ORDER BY fecha_creacion DESC LIMIT 10";
$res_noticias = $conn->query($sql_noticias);

// 4. HISTORIAL RECIENTE EN PUERTA (Últimos 10 días)
$sql_historial = "SELECT * FROM asistencias WHERE id_alumno = '$id_alumno' ORDER BY fecha DESC LIMIT 10";
$res_historial = $conn->query($sql_historial);

// 5. NUEVO: HISTORIAL RECIENTE EN AULA (Módulo del Profesor)
$sql_aula = "SELECT ac.fecha, ac.estado, c.nombre_curso 
             FROM asistencia_cursos ac
             JOIN cursos c ON ac.id_curso = c.id
             WHERE ac.id_alumno = '$id_alumno'
             ORDER BY ac.fecha DESC, c.nombre_curso ASC
             LIMIT 10";
$res_aula = $conn->query($sql_aula);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Escolar - <?php echo $alumno['nombres']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* HEADER CON DEGRADADO Y LOGO */
        .hero-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding-bottom: 90px;
            padding-top: 30px;
            border-radius: 0 0 40px 40px;
            margin-bottom: -60px;
            box-shadow: 0 4px 15px rgba(118, 75, 162, 0.3);
        }
        
        /* TARJETA DE PERFIL */
        .profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            text-align: center;
            padding: 30px 20px;
            margin-bottom: 25px;
            position: relative;
            z-index: 10;
        }
        .profile-img {
            width: 130px; height: 130px; object-fit: cover;
            border-radius: 50%; border: 6px solid white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            margin-top: -70px; 
            margin-bottom: 15px;
        }

        /* TARJETAS GENÉRICAS */
        .card-content {
            border: 0; border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px; overflow: hidden; background: white;
        }
        .card-header-custom { 
            background: white; 
            border-bottom: 1px solid #f0f0f0; 
            padding: 15px 20px; 
            font-weight: 700; color: #555; 
            text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px;
        }

        /* ESTILO LISTA DE ASISTENCIA */
        .asistencia-item {
            transition: background 0.2s;
            border-bottom: 1px solid #f0f0f0;
        }
        .asistencia-item:last-child { border-bottom: 0; }
        .asistencia-item:hover { background-color: #fcfcfc; }
        
        .time-box {
            font-size: 0.85rem; font-weight: 600;
            display: flex; align-items: center;
        }
        .time-entry { color: #198754; } 
        .time-exit { color: #dc3545; }  
        .time-pending { color: #aaa; font-style: italic; }

        /* NOTICIAS */
        .news-item {
            border-left: 4px solid #0d6efd; background: #fff;
            padding: 15px; margin-bottom: 15px; border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.03); transition: transform 0.2s;
        }
        .news-item:hover { transform: translateX(3px); }
        .type-alerta { border-left-color: #dc3545; }
        .type-evento { border-left-color: #ffc107; }
        
        /* LOGO HEADER */
        .logo-header {
            height: 60px; width: auto; 
            background: rgba(255,255,255,0.95); 
            padding: 5px 15px; 
            border-radius: 30px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="hero-header px-4 text-center text-md-start">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div class="d-flex align-items-center mb-3 mb-md-0">
            <img src="../../assets/img/logo.png" class="logo-header me-3" alt="Logo Colegio">
            <div>
                <h3 class="mb-0 fw-bold">Pequeños Ingenieros</h3>
                <p class="opacity-75 small mb-0">Portal de Familia</p>
            </div>
        </div>
        <a href="index.php" class="btn btn-light btn-sm rounded-pill px-4 fw-bold shadow-sm text-primary">
            <i class="bi bi-arrow-left me-1"></i> Salir
        </a>
    </div>
</div>

<div class="container pb-5">
    <div class="row">
        
        <div class="col-lg-4">
            
            <div class="profile-card animate__animated animate__fadeInUp">
                <img src="../../assets/fotos_alumnos/<?php echo $foto; ?>" class="profile-img" onerror="this.src='../../assets/fotos_alumnos/default.jpg'">
                <h4 class="fw-bold text-dark mb-0"><?php echo explode(' ', $alumno['nombres'])[0]; ?></h4>
                <p class="text-secondary small mb-2"><?php echo $alumno['apellidos']; ?></p>
                
                <div class="d-flex justify-content-center gap-2 mb-4">
                    <span class="badge bg-light text-dark border">Salón: <?php echo $nombre_salon; ?></span> <span class="badge bg-primary">Alumno</span>
                </div>
                
                <div class="row g-2 text-center border-top pt-3">
                    <div class="col-4">
                        <div class="fw-bold text-success fs-5"><?php echo $stats['PUNTUAL']; ?></div>
                        <div style="font-size:0.65rem" class="text-muted text-uppercase">Puntual</div>
                    </div>
                    <div class="col-4 border-start border-end">
                        <div class="fw-bold text-warning fs-5"><?php echo $stats['TARDE']; ?></div>
                        <div style="font-size:0.65rem" class="text-muted text-uppercase">Tardanzas</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-danger fs-5"><?php echo $stats['FALTA']; ?></div>
                        <div style="font-size:0.65rem" class="text-muted text-uppercase">Faltas</div>
                    </div>
                </div>
            </div>

            <div class="card card-content">
                <div class="card-header-custom"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Resumen General (Puerta)</div>
                <div class="card-body d-flex justify-content-center">
                    <?php if($total_asistencias > 0): ?>
                        <div style="width: 220px; height: 220px;">
                            <canvas id="chartAsistencia"></canvas>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small py-3">Sin registros aún.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <div class="col-lg-8 mt-lg-5 pt-3">
            
            <div class="row">
                <div class="col-md-12 mb-4">
                    <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-bell-fill me-2"></i>Avisos del Colegio</h5>
                    <div class="row g-3">
                        <?php if($res_noticias->num_rows > 0): ?>
                            <?php while($news = $res_noticias->fetch_assoc()): ?>
                                <?php 
                                    $clase = ($news['tipo'] == 'alerta') ? 'type-alerta' : (($news['tipo'] == 'evento') ? 'type-evento' : '');
                                    $icono = ($news['tipo'] == 'alerta') ? 'bi-exclamation-triangle-fill text-danger' : 'bi-info-circle-fill text-primary';
                                    
                                    $titulo_safe = addslashes($news['titulo']);
                                    $desc_safe   = addslashes($news['descripcion']);
                                    $img_safe    = !empty($news['imagen']) ? "../../assets/uploads/noticias/".$news['imagen'] : "";
                                    $fecha_fmt   = date("d/m/Y h:i A", strtotime($news['fecha_creacion']));
                                ?>
                                <div class="col-md-6">
                                    <div class="news-item <?php echo $clase; ?> h-100" 
                                         style="cursor: pointer;"
                                         onclick="verNoticia('<?php echo $titulo_safe; ?>', '<?php echo $desc_safe; ?>', '<?php echo $fecha_fmt; ?>', '<?php echo $img_safe; ?>')">
                                        
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted fw-bold" style="font-size:0.7rem"><?php echo date("d/m", strtotime($news['fecha_creacion'])); ?></small>
                                            <i class="bi <?php echo $icono; ?>"></i>
                                        </div>
                                        <h6 class="fw-bold mt-1 mb-1"><?php echo $news['titulo']; ?></h6>
                                        <p class="small text-secondary mb-0 text-truncate"><?php echo $news['descripcion']; ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12"><div class="alert alert-light border text-center text-muted small">No hay avisos recientes.</div></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="card card-content border-top border-success border-4">
                        <div class="card-header-custom d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-door-open-fill me-2 text-success"></i>Asistencia en Puerta Principal</span>
                            <small class="text-muted fw-normal text-lowercase">últimos 10 días</small>
                        </div>
                        <div class="card-body p-0">
                            <?php if($res_historial->num_rows > 0): ?>
                                <?php while($h = $res_historial->fetch_assoc()): ?>
                                    <?php 
                                        $fecha = date("d/m/Y", strtotime($h['fecha']));
                                        $dia_semana = ["Dom","Lun","Mar","Mié","Jue","Vie","Sáb"][date("w", strtotime($h['fecha']))];
                                        $hora_in = ($h['hora_llegada']) ? date("h:i A", strtotime($h['hora_llegada'])) : '--:--';
                                        $hora_out = ($h['hora_salida']) ? date("h:i A", strtotime($h['hora_salida'])) : null;
                                        
                                        $badge = "<span class='badge bg-secondary rounded-pill'>Pendiente</span>";
                                        if($h['estado']=='PUNTUAL') $badge = "<span class='badge bg-success bg-opacity-10 text-success border border-success px-3 rounded-pill'>Puntual</span>";
                                        if($h['estado']=='TARDE') $badge = "<span class='badge bg-warning bg-opacity-10 text-dark border border-warning px-3 rounded-pill'>Tarde</span>";
                                        if($h['estado']=='FALTA') $badge = "<span class='badge bg-danger bg-opacity-10 text-danger border border-danger px-3 rounded-pill'>Falta</span>";
                                        if($h['estado']=='JUSTIFICADO') $badge = "<span class='badge bg-info bg-opacity-10 text-primary border border-info px-3 rounded-pill'>Justificado</span>";
                                    ?>
                                    <div class="asistencia-item px-3 py-3 d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center" style="width: 130px;">
                                            <div class="bg-light rounded text-center py-1 px-2 me-3 border">
                                                <div class="fw-bold small text-uppercase"><?php echo $dia_semana; ?></div>
                                                <div class="fw-bold fs-6"><?php echo substr($fecha, 0, 2); ?></div>
                                            </div>
                                            <div><?php echo $badge; ?></div>
                                        </div>
                                        <div class="flex-grow-1 d-flex justify-content-end justify-content-md-center gap-4">
                                            <div class="text-end text-md-center">
                                                <div class="small text-muted text-uppercase" style="font-size:0.65rem">Ingreso</div>
                                                <div class="time-box time-entry"><i class="bi bi-box-arrow-in-right me-1"></i> <?php echo $hora_in; ?></div>
                                            </div>
                                            <div class="vr my-1 d-none d-md-block opacity-25"></div>
                                            <div class="text-end text-md-center">
                                                <div class="small text-muted text-uppercase" style="font-size:0.65rem">Salida</div>
                                                <?php if($hora_out): ?>
                                                    <div class="time-box time-exit"><i class="bi bi-box-arrow-right me-1"></i> <?php echo $hora_out; ?></div>
                                                <?php else: ?>
                                                    <div class="time-box time-pending">--:--</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted small">No hay registros de asistencia en puerta recientes.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mt-2">
                    <div class="card card-content border-top border-info border-4">
                        <div class="card-header-custom d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-journal-check me-2 text-info"></i>Asistencia en Aula (Por Materia)</span>
                            <small class="text-muted fw-normal text-lowercase">últimas clases</small>
                        </div>
                        <div class="card-body p-0">
                            <?php if($res_aula && $res_aula->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4">Fecha</th>
                                                <th>Materia</th>
                                                <th class="text-center">Reporte del Profesor</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($aula = $res_aula->fetch_assoc()): ?>
                                                <?php 
                                                    $fecha_aula = date("d/m/Y", strtotime($aula['fecha']));
                                                    $badge_aula = "<span class='badge bg-secondary'>Desconocido</span>";
                                                    
                                                    if($aula['estado'] == 'Asistió') $badge_aula = "<span class='badge bg-success'><i class='bi bi-check-circle me-1'></i>Asistió</span>";
                                                    if($aula['estado'] == 'Tardanza') $badge_aula = "<span class='badge bg-warning text-dark'><i class='bi bi-clock-history me-1'></i>Tardanza</span>";
                                                    if($aula['estado'] == 'Falta') $badge_aula = "<span class='badge bg-danger'><i class='bi bi-x-circle me-1'></i>Falta</span>";
                                                    if($aula['estado'] == 'Justificado') $badge_aula = "<span class='badge bg-info text-dark'><i class='bi bi-file-medical me-1'></i>Justificado</span>";
                                                ?>
                                                <tr>
                                                    <td class="ps-4 text-muted small fw-bold"><i class="bi bi-calendar-event me-1"></i><?php echo $fecha_aula; ?></td>
                                                    <td class="fw-bold text-primary"><?php echo $aula['nombre_curso']; ?></td>
                                                    <td class="text-center"><?php echo $badge_aula; ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted small">Los profesores aún no han registrado asistencias por materia.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVerNoticia" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="verTitulo"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3"><i class="bi bi-clock"></i> <span id="verFecha"></span></p>
                
                <div id="verImagenContenedor" class="mb-3 text-center d-none" style="background: #f8f9fa; border-radius: 10px; overflow: hidden;">
                    <img id="verImagen" src="" style="max-height: 250px; width: auto; max-width: 100%; object-fit: contain; display: inline-block;">
                </div>

                <div class="p-2">
                    <p id="verDesc" class="text-secondary mb-0" style="white-space: pre-line; font-size: 0.95rem;"></p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-secondary w-100 rounded-pill" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    <?php if($total_asistencias > 0): ?>
    const ctx = document.getElementById('chartAsistencia').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Puntual', 'Tarde', 'Justificado', 'Falta'],
            datasets: [{
                data: [<?php echo $stats['PUNTUAL']; ?>, <?php echo $stats['TARDE']; ?>, <?php echo $stats['JUSTIFICADO']; ?>, <?php echo $stats['FALTA']; ?>],
                backgroundColor: ['#198754', '#ffc107', '#0dcaf0', '#dc3545'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } },
            cutout: '75%' 
        }
    });
    <?php endif; ?>

    function verNoticia(titulo, desc, fecha, imagen) {
        document.getElementById('verTitulo').innerText = titulo;
        document.getElementById('verDesc').innerText = desc;
        document.getElementById('verFecha').innerText = fecha;
        
        const imgContenedor = document.getElementById('verImagenContenedor');
        const imgElement = document.getElementById('verImagen');

        if (imagen && imagen.trim() !== "") {
            imgElement.src = imagen;
            imgContenedor.classList.remove('d-none');
        } else {
            imgContenedor.classList.add('d-none');
        }

        new bootstrap.Modal(document.getElementById('modalVerNoticia')).show();
    }
</script>

</body>
</html>