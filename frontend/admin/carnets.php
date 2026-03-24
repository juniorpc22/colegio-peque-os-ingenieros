<?php
// frontend/admin/carnets.php
include '../../config/db.php';
// INCLUIMOS EL HEADER CENTRALIZADO
include '../includes/header_admin.php';

// Filtros PHP
$grado = isset($_GET['grado']) ? $_GET['grado'] : '';
$seccion = isset($_GET['seccion']) ? $_GET['seccion'] : '';

$where = "";
if($grado != '' || $seccion != '') {
    $clauses = [];
    if($grado != '') $clauses[] = "grado = '$grado'";
    if($seccion != '') $clauses[] = "seccion = '$seccion'";
    $where = "WHERE " . implode(" AND ", $clauses);
}

$sql = "SELECT * FROM alumnos $where ORDER BY grado, seccion, apellidos ASC";
$resultado = $conn->query($sql);
?>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<style>
    /* === ESTILOS ESPECÍFICOS PARA CARNETS === */
    
    /* Contenedor Fluido */
    .contenedor-carnets {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
        padding-bottom: 50px;
    }

    /* Tarjeta Física */
    .carnet {
        width: 9.5cm;
        height: 6.2cm;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        overflow: hidden;
        position: relative;
        background-color: white;
        page-break-inside: avoid;
        break-inside: avoid;
        transition: transform 0.2s, border-color 0.2s;
        cursor: pointer;
    }

    /* Efecto Selección */
    .carnet.seleccionado {
        border: 2px solid #0d6efd;
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(13, 110, 253, 0.2);
    }

    /* Checkbox */
    .carnet-check {
        position: absolute; top: 10px; right: 10px; z-index: 10;
        width: 25px; height: 25px; cursor: pointer;
        border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    /* Estructura Interna */
    .carnet-header {
        flex: 0 0 1.6cm;
        background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
        display: flex; align-items: center; padding: 0 10px; color: white;
    }
    .logo-icon { font-size: 2rem; margin-right: 8px; }
    .school-info { line-height: 1; }
    .school-name { font-weight: 800; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .school-sub { font-size: 0.65rem; opacity: 0.9; }

    .carnet-body {
        flex: 1; display: flex; align-items: center; padding: 8px 10px;
    }
    .foto-marco {
        width: 2.2cm; height: 2.2cm; border-radius: 50%;
        border: 2px solid #0d6efd; overflow: hidden; flex-shrink: 0; margin-right: 10px;
    }
    .foto-marco img { width: 100%; height: 100%; object-fit: cover; }
    
    .info-alumno {
        flex-grow: 1; display: flex; flex-direction: column; justify-content: center; min-width: 0;
    }
    .apellidos { font-weight: 800; color: #212529; font-size: 1rem; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .nombres { font-weight: 600; color: #555; font-size: 0.9rem; margin-bottom: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dato-row { display: flex; align-items: center; font-size: 0.8rem; margin-bottom: 2px; }
    .etiqueta { font-weight: bold; color: #0d6efd; width: 45px; flex-shrink: 0; }

    .barcode-area {
        flex: 0 0 1.4cm; width: 100%; display: flex; justify-content: center; align-items: flex-end; padding-bottom: 5px; background: white;
    }

    /* === REGLAS DE IMPRESIÓN === */
    @media print {
        @page { margin: 0.5cm; size: auto; }
        body { background: white; margin: 0; padding: 0; -webkit-print-color-adjust: exact; }
        
        /* OCULTAR ELEMENTOS DEL SISTEMA */
        .no-print, 
        .navbar,  /* Oculta el Header incluido */
        footer { 
            display: none !important; 
        }
        
        .container-fluid { padding: 0 !important; margin: 0 !important; }
        .contenedor-carnets { display: block; padding-bottom: 0; }
        
        .carnet {
            display: inline-flex;
            border: 1px dashed #aaa;
            margin: 5px;
            box-shadow: none;
            transform: none !important; /* Quitar efecto de selección */
            border-color: #aaa !important;
        }

        .ocultar-impresion { display: none !important; }
    }
</style>

<div class="container-fluid px-4">
    
    <div class="card shadow-sm border-0 mb-4 no-print">
        <div class="card-body py-2">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                
                <div class="d-flex align-items-center gap-2">
                    <h5 class="fw-bold text-dark m-0"><i class="bi bi-person-badge-fill text-primary me-2"></i>Generador</h5>
                    <div class="vr mx-2"></div>
                    
                    <form id="filtroForm" class="d-flex gap-2" method="GET">
                        <select name="grado" class="form-select form-select-sm" style="width: 100px;" onchange="this.form.submit()">
                            <option value="">- Grado -</option>
                            <option value="1" <?php if($grado=='1') echo 'selected'; ?>>1ro</option>
                            <option value="2" <?php if($grado=='2') echo 'selected'; ?>>2do</option>
                            <option value="3" <?php if($grado=='3') echo 'selected'; ?>>3ro</option>
                            <option value="4" <?php if($grado=='4') echo 'selected'; ?>>4to</option>
                            <option value="5" <?php if($grado=='5') echo 'selected'; ?>>5to</option>
                            <option value="6" <?php if($grado=='6') echo 'selected'; ?>>6to</option>
                        </select>
                        <select name="seccion" class="form-select form-select-sm" style="width: 80px;" onchange="this.form.submit()">
                            <option value="">- Sec -</option>
                            <option value="A" <?php if($seccion=='A') echo 'selected'; ?>>A</option>
                            <option value="B" <?php if($seccion=='B') echo 'selected'; ?>>B</option>
                            <option value="C" <?php if($seccion=='C') echo 'selected'; ?>>C</option>
                            <option value="D" <?php if($seccion=='D') echo 'selected'; ?>>D</option>
                        </select>
                        <?php if($grado != '' || $seccion != ''): ?>
                            <a href="carnets.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small" id="infoSeleccion">Mostrando: <strong><?php echo $resultado->num_rows; ?></strong> alumnos</span>
                    <button onclick="imprimirInteligente()" class="btn btn-success fw-bold shadow-sm">
                        <i class="bi bi-printer-fill me-2"></i>IMPRIMIR
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="contenedor-carnets">
        <?php if($resultado->num_rows > 0): ?>
            <?php while($row = $resultado->fetch_assoc()): ?>
                <?php 
                    $foto = !empty($row['foto']) ? $row['foto'] : 'default.jpg';
                    $ruta_foto = "../../assets/fotos_alumnos/" . $foto;
                ?>

                <div class="carnet" onclick="toggleSeleccion(this)">
                    <input type="checkbox" class="form-check-input carnet-check no-print" onclick="event.stopPropagation()">

                    <div class="carnet-header">
                        <div class="logo-icon"><i class="bi bi-mortarboard-fill"></i></div>
                        <div class="school-info">
                            <div class="school-name">Pequeños Ingenieros</div>
                            <div class="school-sub">Educación Primaria</div>
                        </div>
                    </div>

                    <div class="carnet-body">
                        <div class="foto-marco">
                            <img src="<?php echo $ruta_foto; ?>" onerror="this.src='../../assets/fotos_alumnos/default.jpg'">
                        </div>
                        <div class="info-alumno">
                            <div class="apellidos"><?php echo $row['apellidos']; ?></div>
                            <div class="nombres"><?php echo ucwords(strtolower($row['nombres'])); ?></div>
                            <div class="dato-row">
                                <span class="etiqueta">DNI:</span> <span><?php echo $row['dni']; ?></span>
                            </div>
                            <div class="dato-row">
                                <span class="etiqueta">Grado:</span>
                                <span class="badge bg-light text-dark border p-1"><?php echo $row['grado']."° - ".$row['seccion']; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="barcode-area">
                        <svg class="barcode"
                             jsbarcode-format="CODE128"
                             jsbarcode-value="<?php echo $row['codigo_barra']; ?>"
                             jsbarcode-textmargin="0"
                             jsbarcode-height="35"
                             jsbarcode-width="1.5"
                             jsbarcode-displayValue="true"
                             jsbarcode-fontSize="12">
                        </svg>
                    </div>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info mt-5 text-center w-50">
                <i class="bi bi-info-circle me-2"></i> No hay alumnos con el filtro seleccionado.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    JsBarcode(".barcode").init();

    function toggleSeleccion(tarjeta) {
        let checkbox = tarjeta.querySelector('.carnet-check');
        checkbox.checked = !checkbox.checked;
        if(checkbox.checked) tarjeta.classList.add('seleccionado');
        else tarjeta.classList.remove('seleccionado');
        actualizarTextoInfo();
    }

    function actualizarTextoInfo() {
        let seleccionados = document.querySelectorAll('.carnet-check:checked').length;
        let label = document.getElementById('infoSeleccion');
        if(seleccionados > 0) {
            label.innerHTML = "Seleccionados: <strong class='text-primary'>" + seleccionados + "</strong>";
        } else {
            label.innerHTML = "Mostrando: <strong><?php echo $resultado->num_rows; ?></strong> alumnos";
        }
    }

    function imprimirInteligente() {
        let todos = document.querySelectorAll('.carnet');
        let seleccionados = document.querySelectorAll('.carnet-check:checked');

        if (seleccionados.length > 0) {
            todos.forEach(carnet => {
                if (!carnet.querySelector('.carnet-check').checked) {
                    carnet.classList.add('ocultar-impresion');
                }
            });
        }
        window.print();
        setTimeout(() => {
            todos.forEach(c => c.classList.remove('ocultar-impresion'));
        }, 500);
    }
    
    // Compatibilidad extra
    window.onafterprint = function() {
        document.querySelectorAll('.carnet').forEach(c => c.classList.remove('ocultar-impresion'));
    };

    document.querySelectorAll('.carnet-check').forEach(check => {
        check.addEventListener('click', (e) => {
            e.stopPropagation();
            let tarjeta = e.target.closest('.carnet');
            if(e.target.checked) tarjeta.classList.add('seleccionado');
            else tarjeta.classList.remove('seleccionado');
            actualizarTextoInfo();
        });
    });
</script>