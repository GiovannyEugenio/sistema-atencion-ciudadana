<?php
include 'conexion.php';

// Leemos los datos que nos envía JavaScript. Usaremos POST por seguridad.
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No se proporcionó un ID."]);
    exit();
}

$solicitud_id = intval($data->id);

// --- Paso Clave: Primero, obtener las rutas de los archivos ANTES de borrar el registro ---
$stmt_select = $conexion->prepare("SELECT path_ine_anverso, path_ine_reverso FROM solicitudes WHERE id = ?");
$stmt_select->bind_param("i", $solicitud_id);
$stmt_select->execute();
$resultado = $stmt_select->get_result();
if ($fila = $resultado->fetch_assoc()) {
    // Si los archivos existen, los borramos del servidor
    if (!empty($fila['path_ine_anverso']) && file_exists($fila['path_ine_anverso'])) {
        unlink($fila['path_ine_anverso']);
    }
    if (!empty($fila['path_ine_reverso']) && file_exists($fila['path_ine_reverso'])) {
        unlink($fila['path_ine_reverso']);
    }
}
$stmt_select->close();

// --- Ahora, eliminar el registro de la base de datos ---
$stmt_delete = $conexion->prepare("DELETE FROM solicitudes WHERE id = ?");
$stmt_delete->bind_param("i", $solicitud_id);

if ($stmt_delete->execute()) {
    // Gracias a "ON DELETE CASCADE" en la base de datos, los registros en la tabla 'historial'
    // que estén relacionados con esta solicitud se borrarán automáticamente.
    echo json_encode(["success" => true, "message" => "Solicitud eliminada exitosamente."]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al eliminar la solicitud."]);
}

$stmt_delete->close();
$conexion->close();
?>