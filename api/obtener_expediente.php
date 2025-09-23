<?php
include 'conexion.php';

// Verificamos que se haya enviado un ID por la URL (método GET)
if (!isset($_GET['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "No se proporcionó un ID de solicitud."]);
    exit();
}

$solicitud_id = intval($_GET['id']);

// --- 1. Obtener los datos principales de la solicitud ---
$stmt_solicitud = $conexion->prepare("SELECT * FROM solicitudes WHERE id = ?");
$stmt_solicitud->bind_param("i", $solicitud_id);
$stmt_solicitud->execute();
$resultado_solicitud = $stmt_solicitud->get_result();

if ($resultado_solicitud->num_rows === 0) {
    http_response_code(404); // Not Found
    echo json_encode(["error" => "No se encontró la solicitud."]);
    exit();
}

$expediente = $resultado_solicitud->fetch_assoc();

// --- 2. Obtener el historial de cambios de estatus ---
$stmt_historial = $conexion->prepare("SELECT * FROM historial WHERE solicitud_id = ? ORDER BY fecha_cambio DESC");
$stmt_historial->bind_param("i", $solicitud_id);
$stmt_historial->execute();
$resultado_historial = $stmt_historial->get_result();

$historial = [];
while ($fila = $resultado_historial->fetch_assoc()) {
    $historial[] = $fila;
}

// --- 3. Combinar los resultados y enviarlos ---
$expediente['historial'] = $historial;

// --- 4. Obtener los documentos de respaldo (AÑADIR ESTO) ---
$stmt_docs = $conexion->prepare("SELECT * FROM documentos_respaldo WHERE solicitud_id = ?");
$stmt_docs->bind_param("i", $solicitud_id);
$stmt_docs->execute();
$resultado_docs = $stmt_docs->get_result();

$documentos = [];
while ($fila_doc = $resultado_docs->fetch_assoc()) {
    $documentos[] = $fila_doc;
}
$expediente['documentos'] = $documentos; // Añadimos el array de documentos al resultado final
$stmt_docs->close();

echo json_encode($expediente);

$stmt_solicitud->close();
$stmt_historial->close();
$conexion->close();

?>