<?php
// backend/marcar_asistencia.php
error_reporting(0); 
ini_set('display_errors', 0);
header('Content-Type: application/json');

include '../config/db.php';
include 'whatsapp_helper.php'; 
date_default_timezone_set('America/Lima');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_barra = isset($_POST['codigo_barra']) ? $_POST['codigo_barra'] : '';
    $fecha_actual = date('Y-m-d');
    $hora_actual = date('H:i:s'); // Ejemplo interno: "08:10:00"

    // ==========================================
    // ⚙️ REGLAS DE NEGOCIO (HORARIOS DEL COLEGIO)
    // ==========================================
    $inicio_ingreso  = "07:00:00"; // Desde a qué hora pueden entrar
    $limite_puntual  = "08:15:00"; // Hasta a qué hora es puntual
    $fin_ingreso     = "09:30:00"; // Después de esta hora, ya no se acepta ingreso
    
    $inicio_salida   = "12:30:00"; // Desde a qué hora pueden salir
    $fin_salida      = "15:00:00"; // A qué hora se apaga el escáner
    // ==========================================

    if(empty($codigo_barra)) {
        echo json_encode(["status" => "error", "mensaje" => "Código vacío"]);
        exit;
    }

    // 1. CONSULTA DEL ALUMNO
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
        $foto = !empty($alumno['foto']) ? $alumno['foto'] : 'default.jpg';
        $grado_texto = $alumno['num_grado'] . "° " . $alumno['seccion'] . " - " . $alumno['nivel'];

        // 2. VERIFICAR QUÉ HA HECHO EL ALUMNO HOY
        $sql_check = "SELECT id, hora_salida FROM asistencias WHERE id_alumno = ? AND fecha = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("is", $id_alumno, $fecha_actual);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        // ---------------------------------------------------------
        // ESCENARIO A: EL ALUMNO YA MARCÓ SU INGRESO HOY
        // ---------------------------------------------------------
        if ($res_check->num_rows > 0) {
            $registro_existente = $res_check->fetch_assoc();
            
            // Si le falta marcar la salida...
            if (empty($registro_existente['hora_salida'])) {
                
                // REGLAS DE TIEMPO PARA LA SALIDA
                if ($hora_actual < $inicio_salida) {
                    echo json_encode(["status" => "error", "mensaje" => "Aún es horario de clases. La salida inicia a las " . date("h:i A", strtotime($inicio_salida))]);
                    exit;
                }
                if ($hora_actual > $fin_salida) {
                    echo json_encode(["status" => "error", "mensaje" => "El horario de salida ya terminó."]);
                    exit;
                }

                // Si está en el horario correcto, registramos la salida
                $sql_update = "UPDATE asistencias SET hora_salida = ? WHERE id = ?";
                $stmt_upd = $conn->prepare($sql_update);
                $stmt_upd->bind_param("si", $hora_actual, $registro_existente['id']);
                $stmt_upd->execute();

                // ENVÍO DE WHATSAPP SALIDA
                if (!empty($telefono)) {
                    $mensaje = "🏫 *SISTEMA PEQUEÑOS INGENIEROS*\n\n";
                    $mensaje .= "Hola, se registró un movimiento de: *" . $nombre_completo . "*\n";
                    $mensaje .= "Tipo: *SALIDA*\n";
                    $mensaje .= "Hora: *" . date("h:i A", strtotime($hora_actual)) . "*\n";
                    $mensaje .= "Salón: " . $grado_texto;
                    enviarWhatsApp($telefono, $mensaje);
                }

                echo json_encode([
                    "status" => "success",
                    "mensaje" => "SALIDA registrada: " . date("h:i A", strtotime($hora_actual)),
                    "alumno" => ["nombres" => $alumno['nombres'], "apellidos" => $alumno['apellidos'], "salon" => $grado_texto, "foto" => $foto],
                    "asistencia" => ["estado" => "SALIDA"]
                ]);

            } else {
                echo json_encode(["status" => "error", "mensaje" => "El estudiante ya completó su ciclo de asistencia (Ingreso y Salida) hoy."]);
            }
            exit;
        }

        // ---------------------------------------------------------
        // ESCENARIO B: EL ALUMNO RECIÉN LLEGA AL COLEGIO (INGRESO)
        // ---------------------------------------------------------
        
        // REGLAS DE TIEMPO PARA EL INGRESO
        if ($hora_actual < $inicio_ingreso) {
            echo json_encode(["status" => "error", "mensaje" => "Es muy temprano. El ingreso inicia a las " . date("h:i A", strtotime($inicio_ingreso))]);
            exit;
        }
        if ($hora_actual > $fin_ingreso) {
            echo json_encode(["status" => "error", "mensaje" => "El horario de ingreso ya cerró. Diríjase a la dirección."]);
            exit;
        }

        // Si pasó los filtros de tiempo, vemos si llegó puntual o tarde
        $estado = ($hora_actual <= $limite_puntual) ? 'PUNTUAL' : 'TARDE';

        // INSERTAR INGRESO
        $sql_ins = "INSERT INTO asistencias (id_alumno, fecha, hora_llegada, estado) VALUES (?, ?, ?, ?)";
        $stmt_ins = $conn->prepare($sql_ins);
        $stmt_ins->bind_param("isss", $id_alumno, $fecha_actual, $hora_actual, $estado);
        
        if ($stmt_ins->execute()) {
            // ENVÍO DE WHATSAPP INGRESO
            if (!empty($telefono)) {
                $mensaje = "🏫 *SISTEMA PEQUEÑOS INGENIEROS*\n\n";
                $mensaje .= "Hola, se registró un movimiento de: *" . $nombre_completo . "*\n";
                $mensaje .= "Tipo: *" . $estado . "*\n";
                $mensaje .= "Hora: *" . date("h:i A", strtotime($hora_actual)) . "*\n";
                $mensaje .= "Salón: " . $grado_texto;
                enviarWhatsApp($telefono, $mensaje);
            }

            echo json_encode([
                "status" => "success",
                "mensaje" => "INGRESO registrado ($estado): " . date("h:i A", strtotime($hora_actual)),
                "alumno" => ["nombres" => $alumno['nombres'], "apellidos" => $alumno['apellidos'], "salon" => $grado_texto, "foto" => $foto],
                "asistencia" => ["estado" => $estado]
            ]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => "Error DB: " . $conn->error]);
        }
    } else {
        echo json_encode(["status" => "error", "mensaje" => "Estudiante no encontrado."]);
    }
}