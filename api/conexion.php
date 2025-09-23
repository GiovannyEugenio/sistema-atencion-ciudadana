<?php
header("Content-Type: application/json; charset=UTF-8");

$servidor = "127.0.0.1";
$usuario_db = "root";
$password_db = "";
$nombre_db = "atencion_ciudadana";

$conexion = new mysqli($servidor, $usuario_db, $password_db, $nombre_db);

if ($conexion->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Falló la conexión: " . $conexion->connect_error]);
    exit();
}

$conexion->set_charset("utf8mb4");
?>