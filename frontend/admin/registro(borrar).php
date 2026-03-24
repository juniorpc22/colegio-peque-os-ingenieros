<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Alumno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>📝 Registrar Alumno</h4>
        </div>
        <div class="card-body">

            <?php 
            if(isset($_GET['status'])){
                if($_GET['status'] == 'success'){
                    echo "<div class='alert alert-success'>✅ Alumno guardado correctamente.</div>";
                } else {
                    echo "<div class='alert alert-danger'>❌ Error: " . $_GET['msg'] . "</div>";
                }
            }
            ?>

            <form action="../../backend/registrar_alumno.php" method="POST" enctype="multipart/form-data">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Nombres</label>
                        <input type="text" name="nombres" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Apellidos</label>
                        <input type="text" name="apellidos" class="form-control" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>DNI</label>
                        <input type="number" name="dni" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-danger">Código de Barras</label>
                        <input type="text" name="codigo_barra" class="form-control" placeholder="Escanea aquí..." required autofocus>
                    </div>
                </div>
                
                <div class="mb-3">
                     <label>Grado</label>
                     <select name="grado" class="form-select"><option>1ro</option><option>2do</option></select>
                </div>
                <div class="mb-3">
                     <label>Sección</label>
                     <select name="seccion" class="form-select"><option>A</option><option>B</option></select>
                </div>
                <div class="mb-3">
                    <label>Foto</label>
                    <input type="file" name="foto" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary w-100">Guardar</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>