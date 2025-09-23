<?php
include 'conexion.php'; // Incluye la conexión PDO

// --- MANEJO DE ARCHIVOS (Esta parte no cambia) ---
$directorio_uploads = '../uploads/';
$path_ine_anverso = null;
$path_ine_reverso = null;

function procesarArchivo($nombreCampo, $directorio)
{
    if (isset($_FILES[$nombreCampo]) && $_FILES[$nombreCampo]['error'] === UPLOAD_ERR_OK) {
        $archivo_temporal = $_FILES[$nombreCampo]['tmp_name'];
        $nombre_archivo = uniqid() . '_' . basename($_FILES[$nombreCampo]['name']);
        $ruta_destino = $directorio . $nombre_archivo;

        if (move_uploaded_file($archivo_temporal, $ruta_destino)) {
            return $ruta_destino;
        }
    }
    return null;
}

$path_ine_anverso = procesarArchivo('ineAnverso', $directorio_uploads);
$path_ine_reverso = procesarArchivo('ineReverso', $directorio_uploads);

// --- MANEJO DE DATOS DEL FORMULARIO (Esta parte no cambia) ---
$nombre = $_POST['nombreSolicitante'] ?? '';
$municipio = $_POST['municipioSolicitante'] ?? '';
$telefono = $_POST['telefonoSolicitante'] ?? '';
$correo = $_POST['correoSolicitante'] ?? '';
$domicilio = $_POST['domicilioSolicitante'] ?? '';
$descripcion = $_POST['descripcionSolicitud'] ?? '';
$responsable_registro = $_POST['responsableRegistro'] ?? null;
$notas_admin = $_POST['notasObservaciones'] ?? null;
$folio = "CD-" . date('y') . "-" . strtoupper(uniqid());

// --- INSERCIÓN EN LA BASE DE DATOS (Versión PDO) ---
try {
    $sql = "INSERT INTO solicitudes (
        folio, fecha_creacion, nombre_solicitante, municipio, telefono, correo, 
        domicilio, descripcion, estatus, path_ine_anverso, path_ine_reverso,
        responsable_actual, notas_administrativas
    ) VALUES (
        :folio, NOW(), :nombre, :municipio, :telefono, :correo, 
        :domicilio, :descripcion, 'Recibido', :path_anverso, :path_reverso,
        :responsable, :notas
    )";
    
    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ':folio' => $folio,
        ':nombre' => $nombre,
        ':municipio' => $municipio,
        ':telefono' => $telefono,
        ':correo' => $correo,
        ':domicilio' => $domicilio,
        ':descripcion' => $descripcion,
        ':path_anverso' => $path_ine_anverso,
        ':path_reverso' => $path_ine_reverso,
        ':responsable' => $responsable_registro,
        ':notas' => $notas_admin
    ]);

    $solicitud_id_recien_creada = $conexion->lastInsertId();

    // Manejo de documentos de respaldo con PDO
    if ($solicitud_id_recien_creada && isset($_FILES['documentosRespaldo']) && !empty($_FILES['documentosRespaldo']['name'][0])) {
        $sql_docs = "INSERT INTO documentos_respaldo (solicitud_id, nombre_archivo, path_archivo) VALUES (:solicitud_id, :nombre_archivo, :path_archivo)";
        $stmt_docs = $conexion->prepare($sql_docs);

        $total_archivos = count($_FILES['documentosRespaldo']['name']);
        for ($i = 0; $i < $total_archivos; $i++) {
            if ($_FILES['documentosRespaldo']['error'][$i] === UPLOAD_ERR_OK) {
                $nombre_original = basename($_FILES['documentosRespaldo']['name'][$i]);
                $nombre_unico = uniqid() . '_' . $nombre_original;
                $ruta_destino = $directorio_uploads . $nombre_unico;

                if (move_uploaded_file($_FILES['documentosRespaldo']['tmp_name'][$i], $ruta_destino)) {
                    $stmt_docs->execute([
                        ':solicitud_id' => $solicitud_id_recien_creada,
                        ':nombre_archivo' => $nombre_original,
                        ':path_archivo' => $ruta_destino
                    ]);
                }
            }
        }
    }

    echo json_encode(["success" => true, "message" => "Solicitud registrada exitosamente.", "folio" => $folio]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al registrar la solicitud: " . $e->getMessage()]);
}

$conexion = null;
?>
