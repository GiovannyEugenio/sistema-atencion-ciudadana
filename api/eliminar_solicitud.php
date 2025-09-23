<?php
include 'conexion.php'; // Incluye la conexión PDO

// Leemos los datos JSON que nos envía JavaScript
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No se proporcionó un ID."]);
    exit();
}
$solicitud_id = intval($data->id);

try {
    // --- Paso 1: Obtener las rutas de los archivos ANTES de borrar el registro ---
    $stmt_select = $conexion->prepare("SELECT path_ine_anverso, path_ine_reverso FROM solicitudes WHERE id = :id");
    $stmt_select->execute([':id' => $solicitud_id]);
    $fila = $stmt_select->fetch(PDO::FETCH_ASSOC);

    if ($fila) {
        // Si los archivos existen, los borramos del servidor
        if (!empty($fila['path_ine_anverso']) && file_exists($fila['path_ine_anverso'])) {
            unlink($fila['path_ine_anverso']);
        }
        if (!empty($fila['path_ine_reverso']) && file_exists($fila['path_ine_reverso'])) {
            unlink($fila['path_ine_reverso']);
        }
    }

    // --- Paso 2: Ahora, eliminar el registro de la base de datos ---
    // Gracias a "ON DELETE CASCADE", esto también borrará el historial y documentos asociados.
    $stmt_delete = $conexion->prepare("DELETE FROM solicitudes WHERE id = :id");
    $stmt_delete->execute([':id' => $solicitud_id]);

    echo json_encode(["success" => true, "message" => "Solicitud eliminada exitosamente."]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al eliminar la solicitud: " . $e->getMessage()]);
}

$conexion = null;
?>
