<?php
// Incluimos la conexión que ya sabe cómo hablar con la base de datos de Render
include 'conexion.php';

// Desactivamos el header JSON para poder ver texto HTML normal
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Iniciando instalación de la base de datos...</h1>";

// El "plano" de nuestras tablas, traducido para PostgreSQL
$sql_schema = "AQUÍ VA EL SQL TRADUCIDO";

// Ejecutamos la consulta para crear todas las tablas a la vez
if ($conexion->multi_query($sql_schema)) {
    // Limpiamos los resultados de la conexión
    while ($conexion->next_result()) {
        if ($result = $conexion->store_result()) {
            $result->free();
        }
    }
    echo "<h2 style='color: green;'>¡Éxito! Todas las tablas fueron creadas correctamente.</h2>";
    echo "<p>Por seguridad, ahora debes eliminar el archivo setup_database.php de tu proyecto.</p>";
} else {
    echo "<h2 style='color: red;'>Error al crear las tablas:</h2>";
    echo "<pre>" . $conexion->error . "</pre>";
}

$conexion->close();
?>
