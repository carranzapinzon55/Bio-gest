<?php
$host = "localhost";
$user = "root";
$password = ""; // Contraseña de XAMPP por defecto (vacía)
$database = "bio_gest";

try {
    $conexion = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die(json_encode([
        "success" => false, 
        "error" => "Error de conexión a la base de datos: " . $e->getMessage()
    ]));
}
?>