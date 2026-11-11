<?php
// Proyecto IoT - UNAD
// API para consultar las ultimas mediciones

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

require_once __DIR__ . '/conexion.php';

try {
    $sql = "
        SELECT
            m.id,
            m.dispositivo_id,
            d.nombre AS dispositivo,
            m.ph,
            m.turbidez,
            m.temperatura,
            m.fecha
        FROM mediciones m
        INNER JOIN dispositivos d
            ON d.id = m.dispositivo_id
        ORDER BY m.fecha DESC, m.id DESC
        LIMIT 50
    ";

    $consulta = $conexion->query($sql);
    $mediciones = $consulta->fetchAll();

    echo json_encode([
        'estado' => 'correcto',
        'total' => count($mediciones),
        'mediciones' => $mediciones
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

} catch (PDOException $e) {
    error_log('Error consultando mediciones: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'No fue posible consultar las mediciones.'
    ]);
}
