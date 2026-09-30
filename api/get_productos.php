<?php
// Configuración de cabeceras para respuesta JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Requerir el archivo de conexión a la base de datos
// Nota: Ajusta la ruta si tu carpeta de conexión se llama distinto (ej: ../config/conexion.php)
require_once '../configuracion/conexion.php';

try {
    // Validar que la variable de conexión exista
    if (!isset($conexion) || $conexion === null) {
        throw new Exception("La variable \$conexion no fue inicializada en conexion.php");
    }

    // Consulta SQL para obtener los productos activos
    $sql = "SELECT 
                id_producto, 
                id_categoria, 
                nombre_producto, 
                descripcion, 
                precio, 
                stock, 
                imagen, 
                estado 
            FROM producto 
            WHERE estado = 'Activo'
            ORDER BY id_producto ASC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    
    // Obtener los datos en un arreglo asociativo
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Responder en formato JSON
    echo json_encode([
        'success' => true,
        'count'   => count($productos),
        'data'    => $productos
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    // Manejo de errores de base de datos
    echo json_encode([
        'success' => false,
        'error'   => 'Error en la base de datos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    // Manejo de errores generales
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>