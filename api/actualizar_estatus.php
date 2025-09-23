<?php
include 'conexion.php';
session_start(); // Usaremos la sesión para identificar al admin que hace el cambio

// Leemos los datos JSON que nos envía JavaScript
$data = json_decode(file_get_contents("php://input"));

// Asignamos los datos a variables
$solicitud_id = $data->id;
$nuevo_estatus = $data->nuevoEstatus;
$observaciones = $data->observaciones;
// El responsable es el admin que tiene la sesión iniciada
$responsable = $_SESSION['user_name'] ?? 'Administrador';

// --- 1. Actualizar la tabla 'solicitudes' ---
$stmt_update = $conexion->prepare("UPDATE solicitudes SET estatus = ?, responsable_actual = ? WHERE id = ?");
$stmt_update->bind_param("ssi", $nuevo_estatus, $responsable, $solicitud_id);

// --- 2. Insertar en la tabla 'historial' ---
$stmt_historial = $conexion->prepare("INSERT INTO historial (solicitud_id, fecha_cambio, estatus_nuevo, responsable_cambio, observaciones) VALUES (?, NOW(), ?, ?, ?)");
$stmt_historial->bind_param("isss", $solicitud_id, $nuevo_estatus, $responsable, $observaciones);

// Ejecutamos ambas consultas
if ($stmt_update->execute() && $stmt_historial->execute()) {
    echo json_encode(["success" => true, "message" => "Estatus actualizado correctamente."]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al actualizar el estatus."]);
}

$stmt_update->close();
$stmt_historial->close();
$conexion->close();
?>