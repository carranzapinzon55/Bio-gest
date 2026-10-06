<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../configuracion/conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? null;

if (!$id_usuario) {
    echo json_encode(["success" => false, "message" => "No has iniciado sesión."]);
    exit;
}

try {
    if (isset($_GET['id_pedido'])) {
        $id_pedido = intval($_GET['id_pedido']);
        
        $stmtP = $pdo->prepare("SELECT * FROM pedido WHERE id_pedido = :id_p AND id_usuario = :id_u");
        $stmtP->execute([':id_p' => $id_pedido, ':id_u' => $id_usuario]);
        $pedido = $stmtP->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            echo json_encode(["success" => false, "message" => "Pedido no encontrado."]);
            exit;
        }

        $stmtD = $pdo->prepare("SELECT pd.*, p.nombre AS nombre_producto 
                                FROM pedido_detalle pd 
                                LEFT JOIN producto p ON pd.id_producto = p.id_producto 
                                WHERE pd.id_pedido = :id_p");
        $stmtD->execute([':id_p' => $id_pedido]);
        $detalles = $stmtD->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["success" => true, "pedido" => $pedido, "detalles" => $detalles]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM pedido WHERE id_usuario = :id ORDER BY id_pedido DESC");
        $stmt->execute([':id' => $id_usuario]);
        echo json_encode(["success" => true, "pedidos" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}