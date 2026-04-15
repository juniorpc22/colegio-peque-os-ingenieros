<?php
// backend/gestion_noticias.php
session_start();
include '../config/db.php';

// Solo Admins y Directores pueden publicar noticias
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['SuperAdmin', 'Admin', 'Director'])) {
    die("Acceso no autorizado");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'crear') {
    
    $titulo = $conn->real_escape_string(trim($_POST['titulo']));
    $tipo = $conn->real_escape_string($_POST['tipo']);
    $descripcion = $conn->real_escape_string(trim($_POST['descripcion']));
    $nombre_foto = "";

    // Subir imagen si es que adjuntaron una
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $directorio = "../assets/uploads/noticias/";
        if (!file_exists($directorio)) { mkdir($directorio, 0777, true); }
        
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        // Renombramos la imagen con la fecha actual para evitar nombres duplicados
        $nombre_archivo = "noticia_" . time() . "." . $ext; 
        
        if(move_uploaded_file($_FILES['imagen']['tmp_name'], $directorio . $nombre_archivo)){
            $nombre_foto = $nombre_archivo;
        }
    }

    $sql = "INSERT INTO noticias (titulo, descripcion, tipo, imagen) VALUES ('$titulo', '$descripcion', '$tipo', '$nombre_foto')";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: ../frontend/admin/dashboard.php?msg=noticia_ok");
    } else {
        echo "Error: " . $conn->error;
    }
    exit();
}

// Borrar Noticia
if (isset($_GET['accion']) && $_GET['accion'] == 'borrar' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Opcional: Borrar la imagen física del servidor si existe
    $res = $conn->query("SELECT imagen FROM noticias WHERE id = $id");
    if($res->num_rows > 0) {
        $img = $res->fetch_assoc()['imagen'];
        if(!empty($img) && file_exists("../assets/uploads/noticias/".$img)) {
            unlink("../assets/uploads/noticias/".$img);
        }
    }

    $conn->query("DELETE FROM noticias WHERE id = $id");
    header("Location: ../frontend/admin/dashboard.php?msg=noticia_borrada");
    exit();
}

header("Location: ../frontend/admin/dashboard.php");
exit();
?>