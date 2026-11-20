<?php
// Proyecto IoT - UNAD
// Consulta de dispositivos activos para el dashboard

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Solo se permite GET'
    ]);
    exit;
}

require_once __DIR__ . '/conexion.php';

try {

    $sql = "SELECT id, nombre, ubicacion, estado
            FROM dispositivos
            WHERE estado = 'activo'
            ORDER BY nombre ASC";

    $consulta = $conexion->query($sql);
    $dispositivos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'estado' => 'correcto',
        'total' => count($dispositivos),
        'dispositivos' => $dispositivos
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

} catch (PDOException $e) {

    error_log('Error consultando dispositivos: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'No fue posible consultar los dispositivos'
    ]);
}