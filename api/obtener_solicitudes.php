<?php
include 'conexion.php'; 

// Ahora también seleccionamos el responsable_actual para la tabla de seguimiento
$sql = "SELECT id, folio, fecha_creacion, nombre_solicitante, municipio, estatus, responsable_actual FROM solicitudes ORDER BY fecha_creacion DESC";

$resultado = $conexion->query($sql);

$solicitudes = [];

if ($resultado->num_rows > 0) {
    while($fila = $resultado->fetch_assoc()) {
        // Formateamos la fecha para que sea más legible en el front-end
        $fila['fecha_creacion_formateada'] = date("d/m/Y", strtotime($fila['fecha_creacion']));
        $solicitudes[] = $fila;
    }
}

echo json_encode($solicitudes);

$conexion->close();
?>