<?php
// api/obtener_producto.php
header('Content-Type: application/json; charset=utf-8');
require_once '../configuracion/conexion.php';

// Obtener el ID enviado por la URL (?id=2)
$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "ID de producto no proporcionado."]);
    exit;
}

try {
    // Consulta a la tabla producto
    $sql = "SELECT * FROM producto WHERE id_producto = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        echo json_encode(["success" => true, "producto" => $producto]);
    } else {
        echo json_encode(["success" => false, "message" => "Producto no encontrado."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error en la consulta: " . $e->getMessage()]);
}
?>