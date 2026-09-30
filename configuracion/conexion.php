<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "bio_gest";

try {
    $conexion = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Asignación para que funcionen tanto $conexion como $pdo
    $pdo = $conexion;
} catch (PDOException $e) {
    die(json_encode(["success" => false, "error" => $e->getMessage()]));
}
?>