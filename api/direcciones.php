<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../configuracion/conexion.php';

// Habilitar errores de PDO en modo excepción
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id_usuario = $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? null;

if (!$id_usuario) {
    echo json_encode(["success" => false, "message" => "Sesión no válida o caducada. Inicia sesión de nuevo."]);
    exit;
}

$metodo = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    // 1. OBTENER DIRECCIONES (GET)
    if ($metodo === 'GET') {
        $stmt = $pdo->prepare("SELECT * FROM direccion_usuario WHERE id_usuario = :id ORDER BY es_principal DESC, id_direccion DESC");
        $stmt->execute([':id' => $id_usuario]);
        echo json_encode(["success" => true, "direcciones" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } 
    
    // 2. AGREGAR DIRECCIÓN (POST)
    else if ($metodo === 'POST') {
        $nombreUbicacion = trim($input['nombre_ubicacion'] ?? 'Casa');
        $direccion = trim($input['direccion'] ?? '');
        $ciudad = trim($input['ciudad'] ?? '');
        $departamento = trim($input['departamento'] ?? '');

        if (empty($direccion) || empty($ciudad)) {
            echo json_encode(["success" => false, "message" => "Por favor completa la dirección y la ciudad."]);
            exit;
        }

        // Si es la primera dirección, se marca automáticamente como principal
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM direccion_usuario WHERE id_usuario = :id");
        $stmtCheck->execute([':id' => $id_usuario]);
        $esPrincipal = ($stmtCheck->fetchColumn() == 0) ? 1 : 0;

        $stmt = $pdo->prepare("INSERT INTO direccion_usuario (id_usuario, nombre_ubicacion, direccion, ciudad, departamento, es_principal) 
                               VALUES (:id, :u, :d, :c, :dep, :p)");
        $stmt->execute([
            ':id' => $id_usuario,
            ':u' => $nombreUbicacion,
            ':d' => $direccion,
            ':c' => $ciudad,
            ':dep' => $departamento,
            ':p' => $esPrincipal
        ]);

        echo json_encode(["success" => true, "message" => "¡Dirección guardada correctamente!"]);
    } 
    
   // 3. CAMBIAR A PRINCIPAL (PUT)
    else if ($metodo === 'PUT') {
        $id_direccion = $input['id_direccion'] ?? $input['id'] ?? null;

        if (!$id_direccion) {
            echo json_encode(["success" => false, "message" => "ID de dirección no recibido."]);
            exit;
        }

        // Quitar principal a todas las del usuario y asignar a la seleccionada
        $pdo->prepare("UPDATE direccion_usuario SET es_principal = 0 WHERE id_usuario = :id")->execute([':id' => $id_usuario]);
        $pdo->prepare("UPDATE direccion_usuario SET es_principal = 1 WHERE (id_direccion = :dir OR id = :dir) AND id_usuario = :id")
            ->execute([':dir' => $id_direccion, ':id' => $id_usuario]);
        
        echo json_encode(["success" => true, "message" => "Dirección principal actualizada."]);
    } 
    
    // 4. ELIMINAR DIRECCIÓN (DELETE)
    else if ($metodo === 'DELETE') {
        $id_direccion = $input['id_direccion'] ?? $input['id'] ?? null;

        if (!$id_direccion) {
            echo json_encode(["success" => false, "message" => "ID de dirección no recibido."]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM direccion_usuario WHERE (id_direccion = :dir OR id = :dir) AND id_usuario = :id");
        $stmt->execute([':dir' => $id_direccion, ':id' => $id_usuario]);

        echo json_encode(["success" => true, "message" => "Dirección eliminada correctamente."]);
    }