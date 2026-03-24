<?php
// backend/registrar_alumno.php
include '../config/db.php';

// Verificamos si alguien nos envió datos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Limpieza de datos originales
    $nombres = strtoupper(trim($_POST['nombres']));
    $apellidos = strtoupper(trim($_POST['apellidos']));
    $dni = trim($_POST['dni']);
    $telefono = isset($_POST['telefono_apoderado']) ? trim($_POST['telefono_apoderado']) : '';
    $codigo_barra = trim($_POST['codigo_barra']); 
    
    // --- CAMBIO CLAVE: Recibimos el ID del salón ---
    $id_grado_seccion = $_POST['id_grado_seccion']; 
    
    // Lógica de la Foto (Tu lógica original intacta)
    $nombre_foto = "default.jpg";
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $directorio = "../assets/fotos_alumnos/";
        if (!file_exists($directorio)) { 
            mkdir($directorio, 0777, true); 
        }

        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = $dni . "." . $ext;
        if(move_uploaded_file($_FILES['foto']['tmp_name'], $directorio . $nombre_archivo)){
            $nombre_foto = $nombre_archivo;
        }
    }

    // Insertar en BD usando la nueva columna id_grado_seccion
    // Nota: Usamos Sentencias Preparadas para evitar errores de comillas en nombres
    $sql = "INSERT INTO alumnos (dni, codigo_barra, nombres, apellidos, telefono_apoderado, id_grado_seccion, foto) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssis", $dni, $codigo_barra, $nombres, $apellidos, $telefono, $id_grado_seccion, $nombre_foto);

    if ($stmt->execute()) {
        // Redirección de éxito (ajustada a tu ruta de registro)
        header("Location: ../frontend/admin/alumnos.php?status=success");
    } else {
        // Redirección de error
        header("Location: ../frontend/admin/alumnos.php?status=error&msg=" . urlencode($conn->error));
    }
    exit;
}
?>