<?php
// Incluimos la conexión que ya sabe cómo hablar con la base de datos de Render
include 'conexion.php';

// Desactivamos el header JSON para poder ver texto HTML normal
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Iniciando instalación de la base de datos...</h1>";

// El "plano" de nuestras tablas, traducido para PostgreSQL
$sql_schema = "
CREATE TABLE usuarios (
  id SERIAL PRIMARY KEY,
  usuario VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  nombre_completo VARCHAR(100)
);

CREATE TABLE solicitudes (
  id SERIAL PRIMARY KEY,
  folio VARCHAR(20) UNIQUE NOT NULL,
  fecha_creacion TIMESTAMP NOT NULL,
  nombre_solicitante VARCHAR(255) NOT NULL,
  municipio VARCHAR(100) NOT NULL,
  telefono VARCHAR(15) NOT NULL,
  correo VARCHAR(100) NOT NULL,
  domicilio TEXT NOT NULL,
  descripcion TEXT NOT NULL,
  estatus VARCHAR(50) NOT NULL DEFAULT 'Recibido',
  responsable_actual VARCHAR(100),
  notas_administrativas TEXT,
  path_ine_anverso VARCHAR(255),
  path_ine_reverso VARCHAR(255)
);

CREATE TABLE historial (
  id SERIAL PRIMARY KEY,
  solicitud_id INTEGER REFERENCES solicitudes(id) ON DELETE CASCADE,
  fecha_cambio TIMESTAMP NOT NULL,
  estatus_nuevo VARCHAR(50) NOT NULL,
  responsable_cambio VARCHAR(100) NOT NULL,
  observaciones TEXT
);

CREATE TABLE documentos_respaldo (
  id SERIAL PRIMARY KEY,
  solicitud_id INTEGER REFERENCES solicitudes(id) ON DELETE CASCADE,
  nombre_archivo VARCHAR(255) NOT NULL,
  path_archivo VARCHAR(255) NOT NULL
);
";

// Ejecutamos la consulta para crear todas las tablas a la vez
if ($conexion->multi_query($sql_schema)) {
    // Es necesario limpiar los resultados de la conexión después de un multi_query
    while ($conexion->next_result()) {
        if ($result = $conexion->store_result()) {
            $result->free();
        }
    }
    echo "<h2 style='color: green;'>¡Éxito! Todas las tablas fueron creadas correctamente.</h2>";
    echo "<p>Por seguridad, ahora debes eliminar el archivo setup_database.php de tu proyecto y subir el cambio a GitHub.</p>";
} else {
    echo "<h2 style='color: red;'>Error al crear las tablas:</h2>";
    echo "<pre>" . $conexion->error . "</pre>";
}

$conexion->close();
?>
