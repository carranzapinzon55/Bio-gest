<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../configuracion/conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? null;

if (!$id_usuario) {
    echo json_encode(["success" => false, "message" => "Sesión no válida."]);
    exit;
}

$datos = json_decode(file_get_contents('php://input'), true);

if (!$datos) {
    echo json_encode(["success" => false, "message" => "Datos de entrada vacíos."]);
    exit;
}

try {
    $nombre = trim($datos['nombre'] ?? '');
    $apellido = trim($datos['apellido'] ?? '');
    $correo = trim($datos['correo'] ?? '');
    $telefono = trim($datos['telefono'] ?? '');
    $passActual = $datos['pass_actual'] ?? '';
    $passNueva = $datos['pass_nueva'] ?? '';

    if (!empty($passNueva)) {
        $stmtPass = $pdo->prepare("SELECT contrasena FROM usuario WHERE id_usuario = :id");
        $stmtPass->execute([':id' => $id_usuario]);
        $userPass = $stmtPass->fetchColumn();

        if (!password_verify($passActual, $userPass) && $passActual !== $userPass) {
            echo json_encode(["success" => false, "message" => "La contraseña actual es incorrecta."]);
            exit;
        }

        $hashNueva = password_hash($passNueva, PASSWORD_BCRYPT);
        $sqlUpdate = "UPDATE usuario SET nombre = :n, apellido = :a, correo = :c, telefono = :t, contrasena = :p WHERE id_usuario = :id";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([':n' => $nombre, ':a' => $apellido, ':c' => $correo, ':t' => $telefono, ':p' => $hashNueva, ':id' => $id_usuario]);
    } else {
        $sqlUpdate = "UPDATE usuario SET nombre = :n, apellido = :a, correo = :c, telefono = :t WHERE id_usuario = :id";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([':n' => $nombre, ':a' => $apellido, ':c' => $correo, ':t' => $telefono, ':id' => $id_usuario]);
    }

    echo json_encode(["success" => true, "message" => "Perfil actualizado correctamente."]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}