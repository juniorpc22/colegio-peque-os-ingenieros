<?php
// backend/generar_carnet.php
require('../libs/fpdf/fpdf.php');
include '../config/db.php';

// Verificación de seguridad básica
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de alumno no proporcionado.");
}

$id_alumno = $_GET['id'];

// Consulta con JOIN para traer el salón profesional
$sql = "SELECT a.*, gs.nivel, gs.grado as num_grado, gs.seccion 
        FROM alumnos a 
        INNER JOIN grados_secciones gs ON a.id_grado_seccion = gs.id 
        WHERE a.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_alumno);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();

if (!$alumno) die("Alumno no encontrado en el sistema.");

// 1. Configuración del PDF (Horizontal 'L', mm, tamaño carnet estándar: 85x55)
$pdf = new FPDF('L', 'mm', array(55, 85));
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

// --- DISEÑO DE CABECERA ---
$pdf->SetFillColor(0, 86, 179); // Azul corporativo más elegante
$pdf->Rect(0, 0, 85, 12, 'F');
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetXY(5, 4);
$pdf->Cell(0, 5, utf8_decode("PEQUEÑOS INGENIEROS"), 0, 1, 'L');

// --- FOTO DEL ALUMNO ---
$pdf->SetDrawColor(200, 200, 200);
$foto = "../assets/fotos_alumnos/" . ($alumno['foto'] ?: 'default.jpg');
// Rectángulo de borde para la foto
$pdf->Rect(4.8, 14.8, 25.4, 30.4, 'D'); 
$pdf->Image($foto, 5, 15, 25, 30);

// --- DATOS DEL ESTUDIANTE ---
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(32, 16);
$pdf->SetFont('Arial', 'B', 9);
$pdf->MultiCell(50, 4, utf8_decode(strtoupper($alumno['apellidos'] . "\n" . $alumno['nombres'])), 0, 'L');

$pdf->SetXY(32, 28);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(10, 5, "DNI: ", 0, 0);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 5, $alumno['dni'], 0, 1);

$pdf->SetX(32);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(10, 5, "NIVEL: ", 0, 0);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 5, utf8_decode($alumno['nivel']), 0, 1);

$pdf->SetX(32);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(10, 5, "GRADO: ", 0, 0);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 5, utf8_decode($alumno['num_grado'] . " " . $alumno['seccion']), 0, 1);

// --- ZONA DE CÓDIGO DE BARRAS ---
$pdf->SetFillColor(245, 245, 245);
$pdf->Rect(32, 44, 48, 8, 'F');
$pdf->SetXY(32, 44);
$pdf->SetFont('Courier', 'B', 11);
$pdf->SetTextColor(0, 0, 150); // Color distintivo para el código
$pdf->Cell(48, 8, $alumno['codigo_barra'], 0, 0, 'C');

// --- PIE DE PÁGINA ---
$pdf->SetXY(0, 52);
$pdf->SetFillColor(0, 86, 179);
$pdf->Rect(0, 53, 85, 2, 'F');

$pdf->Output('I', 'Carnet_' . $alumno['dni'] . '.pdf');
?>