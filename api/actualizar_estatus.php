<?php
session_start();
include 'conexion.php'; // Incluye la conexión PDO

// Leemos los datos JSON que nos envía JavaScript
$data = json_decode(file_get_contents("php://input"));

// Asignamos los datos a variables
$solicitud_id = $data->id;
$nuevo_estatus = $data->nuevoEstatus;
$observaciones = $data->observaciones;
$responsable = $_SESSION['user_name'] ?? 'Administrador';

try {
    // Iniciamos una transacción para asegurar la integridad de los datos
    $conexion->beginTransaction();

    // --- 1. Actualizar la tabla 'solicitudes' ---
    $stmt_update = $conexion->prepare(
        "UPDATE solicitudes SET estatus = :estatus, responsable_actual = :responsable WHERE id = :id"
    );
    $stmt_update->execute([
        ':estatus' => $nuevo_estatus,
        ':responsable' => $responsable,
        ':id' => $solicitud_id
    ]);

    // --- 2. Insertar en la tabla 'historial' ---
    $stmt_historial = $conexion->prepare(
        "INSERT INTO historial (solicitud_id, fecha_cambio, estatus_nuevo, responsable_cambio, observaciones) VALUES (:solicitud_id, NOW(), :estatus_nuevo, :responsable, :observaciones)"
    );
    $stmt_historial->execute([
        ':solicitud_id' => $solicitud_id,
        ':estatus_nuevo' => $nuevo_estatus,
        ':responsable' => $responsable,
        ':observaciones' => $observaciones
    ]);

    // Si ambas consultas se ejecutaron sin error, confirmamos los cambios
    $conexion->commit();

    echo json_encode(["success" => true, "message" => "Estatus actualizado correctamente."]);

} catch (PDOException $e) {
    // Si algo falló, deshacemos todos los cambios de la transacción
    $conexion->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al actualizar el estatus: " . $e->getMessage()]);
}

$conexion = null;
?>
