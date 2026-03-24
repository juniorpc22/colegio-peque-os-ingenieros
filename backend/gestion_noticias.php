<?php
// backend/gestion_noticias.php
include '../config/db.php';

// RUTA DONDE SE GUARDAN LAS FOTOS
$target_dir = "../assets/uploads/noticias/";

// --- ACCIÓN: ELIMINAR ---
if (isset($_GET['borrar_id'])) {
    $id = intval($_GET['borrar_id']);
    
    // Primero borramos la foto física si existe
    $sql_foto = "SELECT imagen FROM noticias WHERE id = $id";
    $res = $conn->query($sql_foto);
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['imagen']) && file_exists($target_dir . $row['imagen'])) {
            unlink($target_dir . $row['imagen']); // Borrar archivo
        }
    }

    $sql = "DELETE FROM noticias WHERE id = $id";
    if($conn->query($sql)) {
        header("Location: ../frontend/admin/noticias.php?msg=borrado");
    } else {
        header("Location: ../frontend/admin/noticias.php?error=db");
    }
    exit;
}

// --- ACCIONES POST (GUARDAR O EDITAR) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $desc   = $conn->real_escape_string($_POST['descripcion']);
    $tipo   = $_POST['tipo'];
    $id     = !empty($_POST['id']) ? intval($_POST['id']) : null;
    $nombre_foto = null;

    // 1. PROCESAR IMAGEN (SI SE SUBIÓ UNA)
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        // Generar nombre único: noticia_TIMESTAMP.jpg
        $nombre_foto = "noticia_" . time() . "." . $ext;
        
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $target_dir . $nombre_foto)) {
            // Subida exitosa
        } else {
            $nombre_foto = null; // Falló la subida
        }
    }

    // 2. ACTUALIZAR (EDITAR)
    if ($id) {
        $sql = "UPDATE noticias SET titulo='$titulo', descripcion='$desc', tipo='$tipo'";
        // Solo actualizamos la foto si el usuario subió una nueva
        if ($nombre_foto) {
            $sql .= ", imagen='$nombre_foto'";
        }
        $sql .= " WHERE id=$id";
        $msg = "actualizado";
    } 
    // 3. INSERTAR (NUEVO)
    else {
        // Si no subió foto, insertamos NULL
        $foto_sql = $nombre_foto ? "'$nombre_foto'" : "NULL";
        $sql = "INSERT INTO noticias (titulo, descripcion, tipo, imagen) VALUES ('$titulo', '$desc', '$tipo', $foto_sql)";
        $msg = "guardado";
    }

    if($conn->query($sql)) {
        header("Location: ../frontend/admin/noticias.php?msg=$msg");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>