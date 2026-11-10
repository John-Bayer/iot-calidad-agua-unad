<?php
// Proyecto IoT UNAD
// Prueba de conexión PHP - MySQL

require_once __DIR__ . '/conexion.php';

try {
    $consulta = $conexion->query(
        "SELECT id, nombre, estado FROM dispositivos"
    );

    $dispositivos = $consulta->fetchAll();

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'conexion' => 'correcta',
        'dispositivos' => $dispositivos
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'error' => 'Error al consultar los dispositivos'
    ]);
}
