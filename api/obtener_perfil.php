<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Comprobar cualquier variante de clave de sesión común
$id_usuario = $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['user_id'] ?? null;

if (!$id_usuario) {
    echo json_encode([
        "success" => false,
        "message" => "No hay sesión activa."
    ]);
    exit;
}

// Búsqueda dinámica de la conexión a la base de datos
$rutas_conexion = [
    '../config/conexion.php',
    '../conexion.php',
    'config/conexion.php',
    'conexion.php'
];

$pdo = null;
foreach ($rutas_conexion as $ruta) {
    if (file_exists($ruta)) {
        require_once $ruta;
        break;
    }
}

if (!$pdo) {
    echo json_encode([
        "success" => false,
        "message" => "Error de conexión a la base de datos."
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_usuario, nombre, apellido, correo, telefono, id_rol FROM usuario WHERE id_usuario = :id");
    $stmt->execute([':id' => $id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        echo json_encode([
            "success" => true,
            "usuario" => $usuario
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Usuario no encontrado."
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error en la consulta: " . $e->getMessage()
    ]);
}
?>