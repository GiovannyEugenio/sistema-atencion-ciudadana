<?php
include 'conexion.php'; // Incluimos la conexión a la base de datos

// --- MANEJO DE ARCHIVOS ---
// Definimos la carpeta donde se guardarán los archivos. '../' significa 'subir un nivel' desde la carpeta 'api' a la raíz del proyecto.
$directorio_uploads = '../uploads/';
$path_ine_anverso = null;
$path_ine_reverso = null;

// Función para procesar un archivo subido
function procesarArchivo($nombreCampo, $directorio)
{
    // Verifica si el archivo se subió y no hubo errores
    if (isset($_FILES[$nombreCampo]) && $_FILES[$nombreCampo]['error'] === UPLOAD_ERR_OK) {
        $archivo_temporal = $_FILES[$nombreCampo]['tmp_name'];
        // Creamos un nombre de archivo único para evitar que se sobrescriban
        $nombre_archivo = uniqid() . '_' . basename($_FILES[$nombreCampo]['name']);
        $ruta_destino = $directorio . $nombre_archivo;

        // Movemos el archivo de la carpeta temporal a nuestro directorio 'uploads'
        if (move_uploaded_file($archivo_temporal, $ruta_destino)) {
            return $ruta_destino; // Devolvemos la ruta donde se guardó el archivo
        }
    }
    return null; // Devolvemos null si hubo un error o no se subió el archivo
}

// Procesamos los archivos INE
$path_ine_anverso = procesarArchivo('ineAnverso', $directorio_uploads);
$path_ine_reverso = procesarArchivo('ineReverso', $directorio_uploads);

// --- MANEJO DE DATOS DEL FORMULARIO ---
// Obtenemos los datos de texto del formulario (enviados por FormData)
$nombre = $_POST['nombreSolicitante'] ?? '';
$municipio = $_POST['municipioSolicitante'] ?? '';
$telefono = $_POST['telefonoSolicitante'] ?? '';
$correo = $_POST['correoSolicitante'] ?? '';
$domicilio = $_POST['domicilioSolicitante'] ?? '';
$descripcion = $_POST['descripcionSolicitud'] ?? '';
$responsable_registro = $_POST['responsableRegistro'] ?? null;
$notas_admin = $_POST['notasObservaciones'] ?? null;

// Generamos un número de folio único
$folio = "CD-" . date('Y') . "-" . strtoupper(uniqid());

// --- INSERCIÓN EN LA BASE DE DATOS ---
// Preparamos la consulta SQL para insertar los datos de forma segura
$stmt = $conexion->prepare(
    "INSERT INTO solicitudes (
        folio, fecha_creacion, nombre_solicitante, municipio, telefono, correo, 
        domicilio, descripcion, estatus, path_ine_anverso, path_ine_reverso,
        responsable_actual, notas_administrativas
    ) 
    VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, 'Recibido', ?, ?, ?, ?)"
);

// Verificamos si la preparación de la consulta falló
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al preparar la consulta: " . $conexion->error]);
    exit();
}

// Asociamos las 11 variables a los 11 parámetros de la consulta (?)
$stmt->bind_param(
    "sssssssssss", // Ahora son 11 letras 's'
    $folio,
    $nombre,
    $municipio,
    $telefono,
    $correo,
    $domicilio,
    $descripcion,
    $path_ine_anverso,
    $path_ine_reverso,
    $responsable_registro,
    $notas_admin
);

// Ejecutamos la consulta y enviamos la respuesta
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Solicitud registrada exitosamente.", "folio" => $folio]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al registrar la solicitud: " . $stmt->error]);
}

// --- MANEJO DE DOCUMENTOS DE RESPALDO ---
// Verificamos si la inserción principal fue exitosa antes de procesar los documentos
if ($stmt->affected_rows > 0) {
    $solicitud_id_recien_creada = $conexion->insert_id;

    // Verificamos si se subieron archivos de respaldo
    if (isset($_FILES['documentosRespaldo']) && !empty($_FILES['documentosRespaldo']['name'][0])) {

        $stmt_docs = $conexion->prepare("INSERT INTO documentos_respaldo (solicitud_id, nombre_archivo, path_archivo) VALUES (?, ?, ?)");

        $total_archivos = count($_FILES['documentosRespaldo']['name']);
        for ($i = 0; $i < $total_archivos; $i++) {
            if ($_FILES['documentosRespaldo']['error'][$i] === UPLOAD_ERR_OK) {
                $nombre_original = basename($_FILES['documentosRespaldo']['name'][$i]);
                $nombre_unico = uniqid() . '_' . $nombre_original;
                $ruta_destino = $directorio_uploads . $nombre_unico;

                if (move_uploaded_file($_FILES['documentosRespaldo']['tmp_name'][$i], $ruta_destino)) {
                    $stmt_docs->bind_param("iss", $solicitud_id_recien_creada, $nombre_original, $ruta_destino);
                    $stmt_docs->execute();
                }
            }
        }
        $stmt_docs->close();
    }
}

$stmt->close();
$conexion->close();
?>