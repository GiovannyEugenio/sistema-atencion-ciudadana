<?php
include 'conexion.php'; // Incluye la conexión PDO

// Verificamos que se haya enviado un ID por la URL
if (!isset($_GET['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "No se proporcionó un ID de solicitud."]);
    exit();
}
$solicitud_id = intval($_GET['id']);

try {
    // --- 1. Obtener los datos principales de la solicitud ---
    $stmt_solicitud = $conexion->prepare("SELECT * FROM solicitudes WHERE id = :id");
    $stmt_solicitud->execute([':id' => $solicitud_id]);
    $expediente = $stmt_solicitud->fetch(PDO::FETCH_ASSOC);

    if (!$expediente) {
        http_response_code(404); // Not Found
        echo json_encode(["error" => "No se encontró la solicitud."]);
        exit();
    }

    // --- 2. Obtener el historial de cambios de estatus ---
    $stmt_historial = $conexion->prepare("SELECT * FROM historial WHERE solicitud_id = :solicitud_id ORDER BY fecha_cambio DESC");
    $stmt_historial->execute([':solicitud_id' => $solicitud_id]);
    $historial = $stmt_historial->fetchAll(PDO::FETCH_ASSOC);
    $expediente['historial'] = $historial; // Añadimos el historial al resultado

    // --- 3. Obtener los documentos de respaldo ---
    $stmt_docs = $conexion->prepare("SELECT * FROM documentos_respaldo WHERE solicitud_id = :solicitud_id");
    $stmt_docs->execute([':solicitud_id' => $solicitud_id]);
    $documentos = $stmt_docs->fetchAll(PDO::FETCH_ASSOC);
    $expediente['documentos'] = $documentos; // Añadimos los documentos al resultado

    // --- 4. Enviar el resultado combinado ---
    echo json_encode($expediente);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al obtener el expediente: " . $e->getMessage()]);
}

$conexion = null;
?>
