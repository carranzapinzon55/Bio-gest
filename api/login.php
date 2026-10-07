<?php
// api/login.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../configuracion/conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

$correo     = trim($data['correo'] ?? '');
$contrasena = trim($data['contrasena'] ?? '');

if (empty($correo) || empty($contrasena)) {
    echo json_encode(["success" => false, "message" => "Ingresa tu correo y contraseña."]);
    exit;
}

try {
    $sql = "SELECT u.*, r.nombre_rol 
            FROM usuario u 
            JOIN rol r ON u.id_rol = r.id_rol 
            WHERE u.correo = :correo AND u.estado = 'Activo'";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':correo' => $correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch asociativo explícito

    if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
        
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre']     = $usuario['nombre'];
        $_SESSION['apellido']   = $usuario['apellido'];
        $_SESSION['id_rol']     = $usuario['id_rol'];
        $_SESSION['nombre_rol'] = $usuario['nombre_rol'];

        echo json_encode([
            "success" => true,
            "message" => "¡Bienvenido/a " . $usuario['nombre'] . "!",
            "usuario" => [
                "id_usuario" => $usuario['id_usuario'],
                "nombre"     => $usuario['nombre'] . " " . $usuario['apellido'],
                "rol"        => $usuario['nombre_rol']
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Credenciales incorrectas o usuario inactivo."]);
    }

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error en la base de datos: " . $e->getMessage()]);
}
?>