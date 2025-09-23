<?php
// Habilitamos la visualización de todos los errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

$servidor = "localhost";
$usuario_db = "root";
$password_db = "";
$nombre_db = "atencion_ciudadana";

echo "Intentando conectar a la base de datos...<br>";

$conexion = new mysqli($servidor, $usuario_db, $password_db, $nombre_db);

if ($conexion->connect_error) {
    die("¡Falló la conexión! El error exacto es: " . $conexion->connect_error);
} else {
    echo "¡Conexión a la base de datos exitosa!";
    $conexion->close();
}
?>