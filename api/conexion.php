<?php
// Proyecto IoT - UNAD
// Conexion a la base de datos MySQL

$config = require dirname(__DIR__) . '/config/config_privada.php';

try {
    $conexion = new PDO(
        "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log('Error de conexion MySQL: ' . $e->getMessage());
    http_response_code(500);
    exit('Error interno de conexion.');
}
