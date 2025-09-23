<?php
header("Content-Type: application/json; charset=UTF-8");

// Lee las credenciales desde las Variables de Entorno de Render
$servidor = getenv('DB_HOST');
$usuario_db = getenv('DB_USER');
$password_db = getenv('DB_PASSWORD');
$nombre_db = getenv('DB_NAME');
$puerto = 5432; // Puerto estándar de PostgreSQL

// Cambiamos la conexión a pgsql para PostgreSQL
$conexion_str = "pgsql:host=$servidor;port=$puerto;dbname=$nombre_db;user=$usuario_db;password=$password_db";

try {
    // Usamos PDO que es más compatible con diferentes bases de datos
    $conexion = new PDO($conexion_str);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Falló la conexión: " . $e->getMessage()]);
    exit();
}
?>
