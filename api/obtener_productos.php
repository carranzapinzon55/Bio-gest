<?php
// api/obtener_productos.php
header('Content-Type: application/json; charset=utf-8');
require_once '../configuracion/conexion.php';

try {
    // Consultar todos los productos de la base de datos
    $sql = "SELECT id_producto, nombre_producto, precio, descripcion, imagen FROM producto";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "productos" => $productos]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al obtener productos: " . $e->getMessage()]);
}
?>