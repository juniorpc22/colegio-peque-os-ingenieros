<?php
// frontend/admin/index.php
include '../../config/db.php';
// HEADER COMÚN
include '../includes/header_admin.php';

// CONSULTAR NOTICIAS (Últimas 5)
$sql_noticias = "SELECT * FROM noticias ORDER BY fecha_creacion DESC LIMIT 5";
$res_noticias = $conn->query($sql_noticias);
?>

<div class="container">
    <div class="row">
        
        <div class="col-lg-8 mb-4">
            <h3 class="fw-bold text-dark mb-4">📌 Panel de Control</h3>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <a href="alumnos.php" class="card card-menu bg-white text-dark shadow-sm">
                        <div class="card-body text-center">
                            <div class="icon-box text-primary"><i class="bi bi-people-fill"></i></div>
                            <h5 class="fw-bold">Alumnos</h5>
                            <p class="text-muted small">Registrar, editar y eliminar estudiantes.</p>
                        </div>
                    </a>
                </div>

                <div class="col-md-6">
                    <a href="../asistencia/escanear.php" class="card card-menu bg-primary text-white shadow">
                        <div class="card-body text-center">
                            <div class="icon-box"><i class="bi bi-qr-code-scan"></i></div>
                            <h5 class="fw-bold">Tomar Asistencia</h5>
                            <p class="small opacity-75">Escanear códigos de barra (Entrada/Salida).</p>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="reporte_diario.php" class="card card-menu bg-white text-dark shadow-sm">
                        <div class="card-body text-center">
                            <div class="icon-box text-success"><i class="bi bi-clipboard-data"></i></div>
                            <h6 class="fw-bold">Reporte de Hoy</h6>
                            <p class="text-muted small" style="font-size: 0.75rem;">Justificar y corregir.</p>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="historial.php" class="card card-menu bg-white text-dark shadow-sm">
                        <div class="card-body text-center">
                            <div class="icon-box text-warning"><i class="bi bi-calendar3"></i></div>
                            <h6 class="fw-bold">Historial</h6>
                            <p class="text-muted small" style="font-size: 0.75rem;">Ver fechas pasadas.</p>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="carnets.php" class="card card-menu bg-white text-dark shadow-sm">
                        <div class="card-body text-center">
                            <div class="icon-box text-danger"><i class="bi bi-person-badge"></i></div>
                            <h6 class="fw-bold">Carnets</h6>
                            <p class="text-muted small" style="font-size: 0.75rem;">Generar e imprimir.</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark m-0"><i class="bi bi-bell-fill me-2"></i>Novedades</h5>
                </div>

            <div class="noticias-container">
                <?php if ($res_noticias->num_rows > 0): ?>
                    <?php while($n = $res_noticias->fetch_assoc()): ?>
                        <?php 
                            $clase = "tipo-" . $n['tipo'];  
                            $icono = "bi-info-circle-fill";
                            if($n['tipo']=='alerta') $icono = "bi-exclamation-triangle-fill text-danger";
                            if($n['tipo']=='evento') $icono = "bi-calendar-event-fill text-warning";
                            
                            $titulo_safe = addslashes($n['titulo']);
                            $desc_safe   = addslashes($n['descripcion']);
                            $img_safe    = !empty($n['imagen']) ? "../../assets/uploads/noticias/".$n['imagen'] : "";
                            $fecha_fmt   = date("d/m/Y h:i A", strtotime($n['fecha_creacion']));
                        ?>
                        <div class="card card-noticia p-3 <?php echo $clase; ?> position-relative mb-2" 
                             style="cursor: pointer;"
                             onclick="verNoticia('<?php echo $titulo_safe; ?>', '<?php echo $desc_safe; ?>', '<?php echo $fecha_fmt; ?>', '<?php echo $img_safe; ?>')">
                            
                            <a href="../../backend/gestion_noticias.php?borrar_id=<?php echo $n['id']; ?>&origen=index.php" 
                               class="position-absolute top-0 end-0 m-2 text-muted"
                               onclick="event.stopPropagation(); return confirm('¿Borrar noticia?')">
                                <i class="bi bi-x-lg"></i>
                            </a>

                            <h6 class="fw-bold mb-1">
                                <i class="bi <?php echo $icono; ?> me-1"></i> <?php echo $n['titulo']; ?>
                            </h6>
                            <p class="mb-1 small text-secondary text-truncate"><?php echo $n['descripcion']; ?></p>
                            <small class="text-muted" style="font-size: 0.7rem;">
                                <?php echo date("d/m - h:i A", strtotime($n['fecha_creacion'])); ?>
                            </small>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1"></i>
                        <p>No hay noticias recientes.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="modalVerNoticia" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="verTitulo"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <p class="text-muted small mb-3"><i class="bi bi-clock"></i> <span id="verFecha"></span></p>
                
                <div id="verImagenContenedor" class="mb-3 text-center d-none" style="background: #f8f9fa; border-radius: 10px; overflow: hidden;">
                    <img id="verImagen" src="" style="max-height: 250px; width: auto; max-width: 100%; object-fit: contain; display: inline-block;">
                </div>

                <p id="verDesc" class="text-secondary" style="white-space: pre-line;"></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-secondary w-100" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<footer class="text-center text-muted py-4 mt-5 border-top">
    <small>&copy; <?php echo date('Y'); ?> Pequeños Ingenieros - Sistema de Gestión Escolar v1.0</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
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