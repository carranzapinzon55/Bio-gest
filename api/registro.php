<?php
// api/registro.php
header('Content-Type: application/json; charset=utf-8');

// Cambiar la línea 4 por esta ruta:
require_once '../configuracion/conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

$nombre     = trim($data['nombre'] ?? '');
$apellido   = trim($data['apellido'] ?? '');
$correo     = trim($data['correo'] ?? '');
$telefono   = trim($data['telefono'] ?? '');
$contrasena = trim($data['contrasena'] ?? '');

if (empty($nombre) || empty($apellido) || empty($correo) || empty($contrasena)) {
    echo json_encode(["success" => false, "message" => "Por favor completa todos los campos obligatorios."]);
    exit;
}

try {
    // 1. Verificar si el correo ya existe
    $checkStmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE correo = :correo");
    $checkStmt->execute([':correo' => $correo]);

    if ($checkStmt->fetch()) {
        echo json_encode(["success" => false, "message" => "El correo ingresado ya está registrado."]);
        exit;
    }

    // 2. Encriptar contraseña
    $hashContrasena = password_hash($contrasena, PASSWORD_BCRYPT);

    // 3. Insertar usuario con id_rol = 2 (Cliente)
    $sql = "INSERT INTO usuario (nombre, apellido, correo, contrasena, telefono, id_rol) 
            VALUES (:nombre, :apellido, :correo, :contrasena, :telefono, 2)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre'     => $nombre,
        ':apellido'   => $apellido,
        ':correo'     => $correo,
        ':contrasena' => $hashContrasena,
        ':telefono'   => $telefono
    ]);

    echo json_encode(["success" => true, "message" => "¡Registro completado con éxito! Redirigiendo al inicio de sesión..."]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error en la base de datos: " . $e->getMessage()]);
}
?>