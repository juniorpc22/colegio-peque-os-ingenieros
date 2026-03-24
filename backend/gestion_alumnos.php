<?php
// backend/gestion_alumnos.php
include '../config/db.php';

// Respuesta JSON para evitar recargas
header('Content-Type: application/json');

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

if ($accion == 'GUARDAR') {
    // --- 1. RECIBIR DATOS ---
    $id = $_POST['id']; // Si viene ID es editar, si no, es nuevo
    $nombres = strtoupper(trim($_POST['nombres']));
    $apellidos = strtoupper(trim($_POST['apellidos']));
    $dni = trim($_POST['dni']);
    
    // --- RECIBIR TELÉFONO ---
    $telefono = isset($_POST['telefono_apoderado']) ? $conn->real_escape_string($_POST['telefono_apoderado']) : ''; 
    
    $grado = $_POST['grado'];
    $seccion = $_POST['seccion'];
    $codigo = trim($_POST['codigo_barra']);

    // --- 2. VALIDAR DUPLICADOS ---
    $sql_check = "SELECT id FROM alumnos WHERE (codigo_barra = '$codigo' OR dni = '$dni') AND id != '$id'";
    $check = $conn->query($sql_check);
    
    if ($check->num_rows > 0) {
        echo json_encode(['status' => 'error', 'msg' => '¡El DNI o Código ya existen!']);
        exit;
    }

    // --- 3. LÓGICA DE LA FOTO ---
    $campo_foto_sql = ""; 
    $nombre_foto = "default.jpg"; 

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $directorio = "../assets/fotos_alumnos/";
        // Crear carpeta si no existe
        if (!file_exists($directorio)) { mkdir($directorio, 0777, true); }
        
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = $dni . "." . $ext; 
        
        if(move_uploaded_file($_FILES['foto']['tmp_name'], $directorio . $nombre_archivo)){
            $nombre_foto = $nombre_archivo;
            $campo_foto_sql = ", foto='$nombre_foto'";
        }
    }

    // --- 4. GUARDAR EN BD ---
    if (empty($id)) {
        // === NUEVO ALUMNO ===
        if(empty($campo_foto_sql)) $campo_foto_sql = ""; 
        
        $sql = "INSERT INTO alumnos (nombres, apellidos, dni, telefono_apoderado, grado, seccion, codigo_barra, foto) 
                VALUES ('$nombres', '$apellidos', '$dni', '$telefono', '$grado', '$seccion', '$codigo', '$nombre_foto')";
        $msg_ok = "Alumno registrado correctamente.";
    } else {
        // === EDITAR ALUMNO ===
        $sql = "UPDATE alumnos SET 
                nombres='$nombres', 
                apellidos='$apellidos', 
                dni='$dni', 
                telefono_apoderado='$telefono',
                grado='$grado', 
                seccion='$seccion', 
                codigo_barra='$codigo' 
                $campo_foto_sql 
                WHERE id='$id'";
        $msg_ok = "Datos actualizados correctamente.";
    }

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success', 'msg' => $msg_ok]);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Error BD: ' . $conn->error]);
    }

} elseif ($accion == 'ELIMINAR') {
    // --- ELIMINAR ---
    $id = $_POST['id'];
    $conn->query("DELETE FROM asistencias WHERE id_alumno = '$id'"); // Borrar historial primero
    $sql = "DELETE FROM alumnos WHERE id = '$id'"; // Borrar alumno
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success', 'msg' => 'Alumno eliminado.']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Error al eliminar.']);
    }
}
?>