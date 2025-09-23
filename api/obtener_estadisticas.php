<?php
include 'conexion.php';

// Consulta para contar solicitudes, agrupadas por municipio y luego por estatus
$sql = "SELECT municipio, estatus, COUNT(*) as total FROM solicitudes GROUP BY municipio, estatus";
$resultado = $conexion->query($sql);

$stats_por_municipio = [];

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $municipio = $fila['municipio'];
        $estatus = $fila['estatus'];
        $total = intval($fila['total']);

        // Si es la primera vez que vemos este municipio, lo inicializamos
        if (!isset($stats_por_municipio[$municipio])) {
            $stats_por_municipio[$municipio] = [
                'nombre' => $municipio,
                'total' => 0,
                'Recibido' => 0,
                'En proceso' => 0,
                'Concluido' => 0,
                'Archivado' => 0
            ];
        }

        // Sumamos los totales
        $stats_por_municipio[$municipio]['total'] += $total;
        if (isset($stats_por_municipio[$municipio][$estatus])) {
            $stats_por_municipio[$municipio][$estatus] = $total;
        }
    }
}

// Convertimos el array asociativo a un array simple para que sea más fácil de usar en JS
echo json_encode(array_values($stats_por_municipio));

$conexion->close();
?>