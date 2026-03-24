<?php
// backend/marcar_asistencia.php
header('Content-Type: application/json');
include '../config/db.php'; // Tu conexión actualizada con Charset UTF-8
date_default_timezone_set('America/Lima');

// Configuración de Green-API (Mantén tus credenciales actuales aquí)
$idInstance = "TU_ID_INSTANCE"; 
$apiTokenInstance = "TU_API_TOKEN";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_barra = $_POST['codigo_barra'];
    $fecha_actual = date('Y-m-d');
    $hora_actual = date('H:i:s');

    // 1. CONSULTA NORMALIZADA: Unimos alumnos con la nueva tabla grados_secciones
    $sql_alumno = "SELECT a.*, gs.grado as num_grado, gs.seccion, gs.nivel 
                   FROM alumnos a
                   INNER JOIN grados_secciones gs ON a.id_grado_seccion = gs.id
                   WHERE a.dni = ? OR a.codigo_barra = ?";
    
    $stmt = $conn->prepare($sql_alumno);
    $stmt->bind_param("ss", $codigo_barra, $codigo_barra);
    $stmt->execute();
    $res_alumno = $stmt->get_result();

    if ($alumno = $res_alumno->fetch_assoc()) {
        $id_alumno = $alumno['id'];
        $nombre_completo = $alumno['nombres'] . " " . $alumno['apellidos'];
        $telefono = $alumno['telefono_apoderado'];
        $foto = $alumno['foto'];
        
        // Formateamos el grado para la interfaz del Auxiliar
        $grado_texto = $alumno['num_grado'] . "° " . $alumno['seccion'] . " - " . $alumno['nivel'];

        // 2. VALIDACIÓN: ¿Ya marcó hoy?
        $sql_check = "SELECT id FROM asistencias WHERE id_alumno = ? AND fecha = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("is", $id_alumno, $fecha_actual);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            echo json_encode([
                "status" => "warning",
                "msg" => "Ya registró asistencia hoy",
                "nombre" => $nombre_completo,
                "grado" => $grado_texto,
                "foto" => $foto
            ]);
            exit;
        }

        // 3. LÓGICA DE ESTADO (PUNTUAL / TARDE)
        // Suponiendo entrada 08:00 AM
        $hora_limite = "08:00:00";
        $estado = ($hora_actual <= $hora_limite) ? 'PUNTUAL' : 'TARDE';

        // 4. INSERTAR EN LA BASE DE DATOS
        $sql_ins = "INSERT INTO asistencias (id_alumno, fecha, hora, estado) VALUES (?, ?, ?, ?)";
        $stmt_ins = $conn->prepare($sql_ins);
        $stmt_ins->bind_param("isss", $id_alumno, $fecha_actual, $hora_actual, $estado);
        
        if ($stmt_ins->execute()) {
            
            // 5. ENVÍO DE WHATSAPP VÍA GREEN-API
            $mensaje = "SISTEMA PEQUEÑOS INGENIEROS\n";
            $mensaje .= "Hola, se registró la asistencia de: " . $nombre_completo . "\n";
            $mensaje .= "Estado: " . $estado . "\n";
            $mensaje .= "Hora: " . $hora_actual . "\n";
            $mensaje .= "Grado: " . $grado_texto;

            $url = "https://api.green-api.com/waInstance$idInstance/sendMessage/$apiTokenInstance";
            $data = [
                "chatId" => "51" . $telefono . "@c.us",
                "message" => $mensaje
            ];

            $options = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-Type: application/json',
                    'content' => json_encode($data),
                    'timeout' => 5 // Para evitar que el sistema se cuelgue si WhatsApp demora
                ]
            ];

            $context  = stream_context_create($options);
            @file_get_contents($url, false, $context); // Envío silencioso

            // 6. RESPUESTA EXITOSA AL FRONTEND (FETCH)
            echo json_encode([
                "status" => "success",
                "nombre" => $nombre_completo,
                "grado" => $grado_texto,
                "foto" => $foto,
                "tipo" => $estado,
                "hora" => $hora_actual
            ]);
        } else {
            echo json_encode(["status" => "error", "msg" => "Error al guardar en DB"]);
        }

    } else {
        echo json_encode(["status" => "error", "msg" => "Alumno no registrado en el sistema"]);
    }
}
?>