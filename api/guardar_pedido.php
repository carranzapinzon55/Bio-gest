<?php
// api/guardar_pedido.php
header('Content-Type: application/json; charset=utf-8');
session_start();

ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once '../configuracion/conexion.php';

    $inputJSON = file_get_contents('php://input');
    $datos = json_decode($inputJSON, true);

    if (!$datos) {
        echo json_encode(["success" => false, "message" => "Petición no válida o formato JSON incorrecto."]);
        exit;
    }

    $envio = $datos['envio'] ?? null;
    $carrito = $datos['carrito'] ?? [];
    $total = floatval($datos['total'] ?? 0);

    if (empty($carrito) || !$envio) {
        echo json_encode(["success" => false, "message" => "Datos de envío o carrito incompletos."]);
        exit;
    }

    // 1. Obtener ID del usuario desde la sesión o por correo registrado
    $id_usuario = $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? null;

    if (!$id_usuario && !empty($envio['email'])) {
        $stmtUsuario = $pdo->prepare("SELECT id_usuario FROM usuario WHERE correo = :correo LIMIT 1");
        $stmtUsuario->execute([':correo' => $envio['email']]);
        $userFound = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
        if ($userFound) {
            $id_usuario = $userFound['id_usuario'];
        }
    }

    if (!$id_usuario) {
        echo json_encode([
            "success" => false, 
            "message" => "No se encontró un usuario asociado al correo. Por favor inicia sesión o regístrate para comprar."
        ]);
        exit;
    }

    $pdo->beginTransaction();

    // 2. Insertar en la tabla 'pedido' (columnas exactas de tu estructura SQL)
    $sqlPedido = "INSERT INTO pedido (id_usuario, total, estado_pedido) 
                  VALUES (:id_usuario, :total, 'Pendiente')";

    $stmtPedido = $pdo->prepare($sqlPedido);
    $stmtPedido->execute([
        ':id_usuario' => $id_usuario,
        ':total'      => $total
    ]);

    $id_pedido = $pdo->lastInsertId();

    // 3. Insertar detalle en 'pedido_detalle'
    $sqlDetalle = "INSERT INTO pedido_detalle (id_pedido, id_producto, cantidad, precio_unitario, subtotal) 
                   VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario, :subtotal)";

    $stmtDetalle = $pdo->prepare($sqlDetalle);

    foreach ($carrito as $item) {
        $cant = intval($item['cantidad'] ?? 1);
        $precioUnit = floatval($item['precio'] ?? 0);
        $subtotalItem = $cant * $precioUnit;

        $stmtDetalle->execute([
            ':id_pedido'       => $id_pedido,
            ':id_producto'     => $item['id'],
            ':cantidad'        => $cant,
            ':precio_unitario' => $precioUnit,
            ':subtotal'        => $subtotalItem
        ]);
    }

    $pdo->commit();

    echo json_encode([
        "success"   => true, 
        "message"   => "Pedido registrado con éxito.", 
        "id_pedido" => $id_pedido
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        "success" => false, 
        "message" => "Error en el servidor: " . $e->getMessage()
    ]);
}