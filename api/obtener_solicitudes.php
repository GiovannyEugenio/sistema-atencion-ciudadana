<?php
include 'conexion.php'; // Incluye la conexión PDO

$sql = "SELECT id, folio, fecha_creacion, nombre_solicitante, municipio, estatus, responsable_actual FROM solicitudes ORDER BY fecha_creacion DESC";

try {
    // En PDO, preparamos y ejecutamos la consulta
    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    // Obtenemos todos los resultados de una vez
    $solicitudes_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $solicitudes = [];

    // Recorremos los resultados para formatear la fecha (esto no cambia)
    foreach ($solicitudes_raw as $fila) {
        $fila['fecha_creacion_formateada'] = date("d/m/Y", strtotime($fila['fecha_creacion']));
        $solicitudes[] = $fila;
    }

    echo json_encode($solicitudes);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al obtener las solicitudes: " . $e->getMessage()]);
}

$conexion = null;
?>
