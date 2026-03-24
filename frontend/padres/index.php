<?php
// frontend/padres/index.php
include '../../config/db.php';

// Cargar noticias (Últimas 3)
$sql_noticias = "SELECT * FROM noticias ORDER BY fecha_creacion DESC LIMIT 3";
$res_noticias = $conn->query($sql_noticias);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pequeños Ingenieros - Portal Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fa; }
        
        /* === PORTADA (Usa tu imagen local) === */
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../../assets/img/portada.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
            border-radius: 0 0 50px 50px;
            margin-bottom: 50px;
        }
        
        .search-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 500px;
            margin: 0 auto;
            color: #333;
        }

        /* ESTILOS NOTICIAS */
        .news-card {
            border: 0; border-radius: 15px; transition: transform 0.3s, box-shadow 0.3s;
            height: 100%; overflow: hidden;
        }
        .news-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .card-img-top { height: 200px; object-fit: cover; background-color: #eee; }
        .tag-tipo { position: absolute; top: 15px; right: 15px; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase; }
        
        /* ESTILOS GALERÍA */
        .gallery-item { border-radius: 15px; overflow: hidden; height: 250px; position: relative; }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
        .gallery-item:hover img { transform: scale(1.1); }
        
        footer { background: #2c3e50; color: white; padding: 50px 0 20px; margin-top: 50px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="#">
            <img src="../../assets/img/logo.png" alt="Logo" style="height: 40px; margin-right: 10px;">
            PEQUEÑOS INGENIEROS
        </a>
        <div class="ms-auto">
            <a href="../../index.php" class="btn btn-outline-light btn-sm rounded-pill px-4">Acceso Administrativo</a>
        </div>
    </div>
</nav>

<header class="hero-section">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Bienvenidos a Nuestro Portal</h1>
        <p class="lead mb-5 opacity-75">Formando líderes para el futuro con educación de calidad.</p>

        <div class="search-box text-start animate__animated animate__fadeInUp">
            <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-person-circle me-2"></i>Consulta de Alumno</h5>
            
            <?php if (isset($_GET['error']) && $_GET['error'] == 'notfound'): ?>
                <div class="alert alert-danger p-2 small mb-3">DNI no encontrado. Intente nuevamente.</div>
            <?php endif; ?>

            <form action="seguimiento.php" method="GET" onsubmit="return validar()">
                <label class="form-label small text-muted fw-bold">Ingrese DNI del estudiante:</label>
                <div class="input-group mb-3">
                    <input type="text" id="dni" name="dni" class="form-control form-control-lg" placeholder="Ej: 70123123" maxlength="8" required>
                    <button class="btn btn-primary px-4 fw-bold" type="submit">BUSCAR</button>
                </div>
            </form>
            <small class="text-muted"><i class="bi bi-lock-fill me-1"></i>Acceso seguro solo para padres.</small>
        </div>
    </div>
</header>

<div class="container mb-5">
    <div class="text-center mb-5">
        <h6 class="text-primary fw-bold text-uppercase ls-2">Actualidad</h6>
        <h2 class="fw-bold">Noticias y Comunicados</h2>
    </div>

    <div class="row g-4">
        <?php if($res_noticias->num_rows > 0): ?>
            <?php while($news = $res_noticias->fetch_assoc()): ?>
                <?php 
                    // 1. Colores de la etiqueta
                    $badge_color = "bg-info";
                    if($news['tipo']=='alerta') $badge_color = "bg-danger";
                    if($news['tipo']=='evento') $badge_color = "bg-warning text-dark";
                    
                    // 2. LÓGICA DE IMAGEN (La parte nueva importante)
                    if (!empty($news['imagen'])) {
                        // Si subiste foto, usa esa
                        $ruta_img = "../../assets/uploads/noticias/" . $news['imagen'];
                    } else {
                        // Si NO subiste foto, usa una genérica bonita de internet según el tipo
                        if($news['tipo'] == 'alerta') $ruta_img = "https://images.unsplash.com/photo-1599256621730-d3ae171b9201?auto=format&fit=crop&w=400&q=80";
                        else if($news['tipo'] == 'evento') $ruta_img = "https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=400&q=80";
                        else $ruta_img = "https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=400&q=80";
                    }
                ?>
                <div class="col-md-4">
                    <div class="card news-card shadow-sm">
                        <span class="badge <?php echo $badge_color; ?> tag-tipo"><?php echo $news['tipo']; ?></span>
                        
                        <img src="<?php echo $ruta_img; ?>" class="card-img-top" alt="Noticia">
                        
                        <div class="card-body">
                            <small class="text-muted"><i class="bi bi-calendar3 me-2"></i><?php echo date("d/m/Y", strtotime($news['fecha_creacion'])); ?></small>
                            <h5 class="card-title fw-bold mt-2"><?php echo $news['titulo']; ?></h5>
                            <p class="card-text text-secondary small"><?php echo substr($news['descripcion'], 0, 100); ?>...</p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted py-5">
                <i class="bi bi-newspaper display-4 mb-3 d-block opacity-25"></i>
                No hay noticias recientes.
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="bg-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <h6 class="text-warning fw-bold text-uppercase">Vida Escolar</h6>
                <h2 class="fw-bold mb-4">Nuestros Momentos</h2>
                <p class="text-secondary">Un vistazo a las actividades diarias, ferias de ciencias, deportes y momentos especiales.</p>
            </div>
            <div class="col-lg-8">
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="gallery-item">
                            <img src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Clase">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="gallery-item">
                            <img src="https://images.unsplash.com/photo-1577896333243-596c52296652?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Recreo">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="d-flex align-items-center mb-3">
                    <img src="../../assets/img/logo.png" alt="Logo" style="height: 30px; margin-right: 10px;">
                    <h5 class="fw-bold m-0">Pequeños Ingenieros</h5>
                </div>
                <p class="small opacity-75">Educación de calidad y valores.</p>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold mb-3">Contacto</h5>
                <ul class="list-unstyled small opacity-75">
                    <li class="mb-2">Av. Principal 123, Lima</li>
                    <li class="mb-2">(01) 234-5678</li>
                </ul>
            </div>
            <div class="col-md-4 mb-4 text-md-end">
                <h5 class="fw-bold mb-3">Síguenos</h5>
                <a href="#" class="text-white me-3 fs-4"><i class="bi bi-facebook"></i></a>
                <a href="#" class="text-white fs-4"><i class="bi bi-instagram"></i></a>
            </div>
        </div>
        <div class="text-center small opacity-50 mt-4">&copy; <?php echo date('Y'); ?> Pequeños Ingenieros.</div>
    </div>
</footer>

<script>
    document.getElementById('dni').addEventListener('input', function (e) { this.value = this.value.replace(/[^0-9]/g, ''); });
    function validar() {
        let dni = document.getElementById('dni').value;
        if (dni.length !== 8) { Swal.fire({ icon: 'warning', title: 'Atención', text: 'El DNI debe tener 8 dígitos.', confirmButtonColor: '#0d6efd' }); return false; }
        return true;
    }
</script>

</body>
</html>