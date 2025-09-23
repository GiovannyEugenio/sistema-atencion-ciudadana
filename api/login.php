<?php
session_start();
include 'conexion.php';

// 1. Leer el cuerpo de la petición de forma segura
$input = file_get_contents("php://input");
if (!$input) {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "No se recibieron datos."]);
    exit();
}
$data = json_decode($input);

// 2. Obtener las variables directamente del objeto decodificado
$usuario_form = $data->username;
$password_form = $data->password;

// 3. Preparar la consulta para buscar al usuario
$stmt = $conexion->prepare("SELECT id, password, nombre_completo FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario_form);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $admin = $resultado->fetch_assoc();

    // 4. Verificar la contraseña hasheada
    if (password_verify($password_form, $admin['password'])) {
        // Si es correcta, iniciar sesión
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_name'] = $admin['nombre_completo'];
        
        echo json_encode(["success" => true, "message" => "Inicio de sesión exitoso."]);
    } else {
        // Si la contraseña es incorrecta
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Contraseña incorrecta."]);
    }
} else {
    // Si el usuario no existe
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Usuario no encontrado."]);
}

$stmt->close();
$conexion->close();
?>