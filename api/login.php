<?php
header('Content-Type: application/json; charset=utf-8');

// Subimos un nivel (../) para salir de 'api' e ingresar a 'config/conexion.php'
require_once '../configuracion/conexion.php';


// api/login.php
session_start();
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);

$correo     = trim($data['correo'] ?? '');
$contrasena = trim($data['contrasena'] ?? '');

if (empty($correo) || empty($contrasena)) {
    echo json_encode(["success" => false, "message" => "Ingresa tu correo y contraseña."]);
    exit;
}

try {
    // Buscar usuario y obtener su nombre de rol
    $sql = "SELECT u.*, r.nombre_rol 
            FROM usuario u 
            JOIN rol r ON u.id_rol = r.id_rol 
            WHERE u.correo = :correo AND u.estado = 'Activo'";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':correo' => $correo]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
        
        // Actualizar último acceso
        $updateStmt = $pdo->prepare("UPDATE usuario SET ultimo_acceso = NOW() WHERE id_usuario = :id");
        $updateStmt->execute([':id' => $usuario['id_usuario']]);

        // Guardar sesión PHP
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