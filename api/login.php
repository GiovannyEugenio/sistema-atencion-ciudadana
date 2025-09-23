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

// 2. Obtener las variables
$usuario_form = $data->username;
$password_form = $data->password;

// 3. Preparar la consulta para PDO
$stmt = $conexion->prepare("SELECT id, password, nombre_completo FROM usuarios WHERE usuario = :usuario");
$stmt->execute([':usuario' => $usuario_form]);

$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
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

// En PDO, la conexión se cierra al terminar el script o asignando null
$conexion = null;
?>
