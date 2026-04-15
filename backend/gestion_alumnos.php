<?php
// backend/gestion_alumnos.php
include '../config/db.php';
header('Content-Type: application/json');

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

if ($accion == 'GUARDAR') {
    $id = $_POST['id'];
    $nombres = strtoupper(trim($_POST['nombres']));
    $apellidos = strtoupper(trim($_POST['apellidos']));
    $dni = trim($_POST['dni']);
    $telefono = isset($_POST['telefono_apoderado']) ? $conn->real_escape_string($_POST['telefono_apoderado']) : ''; 
    $codigo = trim($_POST['codigo_barra']);
    
    // --- ESTO ES LO NUEVO: Recibimos los 3 datos separados ---
    $nivel = $_POST['nivel'];
    $grado = $_POST['grado'];
    $seccion = $_POST['seccion'];

    // Buscar el ID del salón en la base de datos según lo que eligió el usuario
    $sql_salon = "SELECT id FROM grados_secciones WHERE nivel = ? AND grado = ? AND seccion = ?";
    $stmt_salon = $conn->prepare($sql_salon);
    $stmt_salon->bind_param("sss", $nivel, $grado, $seccion);
    $stmt_salon->execute();
    $res_salon = $stmt_salon->get_result();

    if ($row_salon = $res_salon->fetch_assoc()) {
        $id_grado_seccion = $row_salon['id']; // ¡Encontramos el ID!
    } else {
        // Si el administrador eligió un salón que no existe en la tabla grados_secciones
        echo json_encode(['status' => 'error', 'msg' => 'La combinación de Nivel, Grado y Sección no existe en el colegio.']);
        exit;
    }

    // Validar duplicados
    $sql_check = "SELECT id FROM alumnos WHERE (codigo_barra = '$codigo' OR dni = '$dni') AND id != '$id'";
    $check = $conn->query($sql_check);
    
    if ($check->num_rows > 0) {
        echo json_encode(['status' => 'error', 'msg' => '¡El DNI o Código de Barras ya existen!']);
        exit;
    }

    // Lógica de la Foto
    $campo_foto_sql = ""; 
    $nombre_foto = "default.jpg"; 

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $directorio = "../assets/fotos_alumnos/";
        if (!file_exists($directorio)) { mkdir($directorio, 0777, true); }
        
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = $dni . "." . $ext; 
        
        if(move_uploaded_file($_FILES['foto']['tmp_name'], $directorio . $nombre_archivo)){
            $nombre_foto = $nombre_archivo;
            $campo_foto_sql = ", foto='$nombre_foto'";
        }
    }

    // GUARDAR EN BD
    if (empty($id)) {
        // Nuevo Alumno
        if(empty($campo_foto_sql)) $campo_foto_sql = ""; 
        $sql = "INSERT INTO alumnos (nombres, apellidos, dni, telefono_apoderado, id_grado_seccion, codigo_barra, foto) 
                VALUES ('$nombres', '$apellidos', '$dni', '$telefono', $id_grado_seccion, '$codigo', '$nombre_foto')";
        $msg_ok = "Alumno matriculado correctamente.";
    } else {
        // Editar Alumno
        $sql = "UPDATE alumnos SET 
                nombres='$nombres', 
                apellidos='$apellidos', 
                dni='$dni', 
                telefono_apoderado='$telefono',
                id_grado_seccion=$id_grado_seccion, 
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
    $id = $_POST['id'];
    $conn->query("DELETE FROM asistencias WHERE id_alumno = '$id'");
    $conn->query("DELETE FROM asistencia_cursos WHERE id_alumno = '$id'"); 
    $sql = "DELETE FROM alumnos WHERE id = '$id'";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success', 'msg' => 'Alumno eliminado.']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Error al eliminar.']);
    }
}
?>