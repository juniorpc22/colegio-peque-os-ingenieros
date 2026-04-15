<?php
// backend/gestion_cursos.php
session_start();
include '../config/db.php';

// Verificación de seguridad
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['SuperAdmin', 'Admin'])) {
    die("Acceso no autorizado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $accion = $_POST['accion']; // Nos dirá si es "crear" o "editar"
    $nombre_curso = mysqli_real_escape_string($conn, trim($_POST['nombre_curso']));
    $id_profesor = intval($_POST['id_profesor']);
    
    $nivel = $_POST['nivel'];
    $grado = $_POST['grado'];
    $seccion = $_POST['seccion'];

    // PASO 1: Buscar el ID del salón en la base de datos
    $sql_salon = "SELECT id FROM grados_secciones WHERE nivel = ? AND grado = ? AND seccion = ?";
    $stmt_salon = $conn->prepare($sql_salon);
    $stmt_salon->bind_param("sss", $nivel, $grado, $seccion);
    $stmt_salon->execute();
    $res_salon = $stmt_salon->get_result();

    if ($row_salon = $res_salon->fetch_assoc()) {
        $id_grado_seccion = $row_salon['id'];

        // PASO 2A: CREAR CURSO NUEVO
        if ($accion == 'crear') {
            $sql = "INSERT INTO cursos (nombre_curso, id_grado_seccion, id_profesor) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sii", $nombre_curso, $id_grado_seccion, $id_profesor);

            if ($stmt->execute()) {
                header("Location: ../frontend/admin/cursos.php?msg=curso_creado");
            } else {
                echo "Error al crear el curso: " . $conn->error;
            }
        } 
        // PASO 2B: EDITAR CURSO EXISTENTE
        elseif ($accion == 'editar') {
            $id_curso = intval($_POST['id_curso']); // Viene del input oculto
            
            $sql = "UPDATE cursos SET nombre_curso=?, id_grado_seccion=?, id_profesor=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siii", $nombre_curso, $id_grado_seccion, $id_profesor, $id_curso);

            if ($stmt->execute()) {
                header("Location: ../frontend/admin/cursos.php?msg=curso_editado");
            } else {
                echo "Error al editar el curso: " . $conn->error;
            }
        }

    } else {
        // Si eligieron un salón fantasma que no está registrado
        header("Location: ../frontend/admin/cursos.php?error=salon_no_existe");
    }
    exit();
}

// PASO 3: BORRAR CURSO
if (isset($_GET['accion']) && $_GET['accion'] == 'borrar' && isset($_GET['id'])) {
    $id_borrar = intval($_GET['id']);
    
    $sql = "DELETE FROM cursos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_borrar);
    
    if ($stmt->execute()) {
        header("Location: ../frontend/admin/cursos.php?msg=curso_borrado");
    } else {
        echo "Error al borrar: " . $conn->error;
    }
    exit();
}

header("Location: ../frontend/admin/cursos.php");
exit();
?>