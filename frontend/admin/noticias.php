<?php
// frontend/admin/noticias.php
include '../../config/db.php';
include '../includes/header_admin.php';

$sql = "SELECT * FROM noticias ORDER BY fecha_creacion DESC";
$resultado = $conn->query($sql);
?>

<div class="container pb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0"><i class="bi bi-newspaper me-2"></i>Gestión de Comunicados</h3>
            <p class="text-muted small">Noticias visibles en el Portal de Padres.</p>
        </div>
        <button class="btn btn-primary shadow-sm" onclick="abrirModal()">
            <i class="bi bi-plus-lg me-2"></i>Nueva Publicación
        </button>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Imagen</th>
                            <th>Título</th>
                            <th style="width: 35%;">Descripción</th>
                            <th class="text-center">Tipo</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($resultado->num_rows > 0): ?>
                            <?php while($row = $resultado->fetch_assoc()): ?>
                                <?php 
                                    $bg = "bg-info text-dark";
                                    if($row['tipo'] == 'alerta') $bg = "bg-danger";
                                    if($row['tipo'] == 'evento') $bg = "bg-warning text-dark";

                                    // Ruta de imagen o placeholder
                                    $img_src = !empty($row['imagen']) 
                                        ? "../../assets/uploads/noticias/".$row['imagen'] 
                                        : "https://via.placeholder.com/80?text=Sin+Foto";
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <img src="<?php echo $img_src; ?>" class="rounded border" style="width: 60px; height: 60px; object-fit: cover;">
                                    </td>
                                    <td class="fw-bold"><?php echo $row['titulo']; ?></td>
                                    <td>
                                        <small class="text-muted text-truncate d-block" style="max-width: 350px;">
                                            <?php echo $row['descripcion']; ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?php echo $bg; ?>"><?php echo ucfirst($row['tipo']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary border-0" 
                                                onclick="editar('<?php echo $row['id']; ?>', '<?php echo addslashes($row['titulo']); ?>', '<?php echo addslashes($row['descripcion']); ?>', '<?php echo $row['tipo']; ?>')">
                                            <i class="bi bi-pencil-square fs-5"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger border-0" onclick="eliminar(<?php echo $row['id']; ?>)">
                                            <i class="bi bi-trash fs-5"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-5">No hay noticias.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNoticia" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tituloModal">Nueva Publicación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="../../backend/gestion_noticias.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="noticia_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Título</label>
                        <input type="text" name="titulo" id="titulo" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Imagen (Opcional)</label>
                        <input type="file" name="imagen" class="form-control" accept="image/*">
                        <div class="form-text small">Se recomienda formato horizontal (JPG, PNG).</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tipo</label>
                        <select name="tipo" id="tipo" class="form-select">
                            <option value="info">Información</option>
                            <option value="alerta">Alerta</option>
                            <option value="evento">Evento</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Descripción</label>
                        <textarea name="descripcion" id="descripcion" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const myModal = new bootstrap.Modal(document.getElementById('modalNoticia'));

    function abrirModal() {
        document.getElementById('noticia_id').value = "";
        document.getElementById('titulo').value = "";
        document.getElementById('descripcion').value = "";
        document.getElementById('tipo').value = "info";
        document.getElementById('tituloModal').innerText = "Nueva Publicación";
        myModal.show();
    }

    function editar(id, titulo, desc, tipo) {
        document.getElementById('noticia_id').value = id;
        document.getElementById('titulo').value = titulo;
        document.getElementById('descripcion').value = desc;
        document.getElementById('tipo').value = tipo;
        document.getElementById('tituloModal').innerText = "Editar Publicación";
        myModal.show();
    }

    function eliminar(id) {
        if(confirm('¿Seguro que deseas eliminar esta noticia?')) {
            window.location.href = `../../backend/gestion_noticias.php?borrar_id=${id}`;
        }
    }
</script>
</body>
</html>