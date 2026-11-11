<?php
// Proyecto IoT - UNAD
// Consulta de limites configurados

header('Content-Type: application/json; charset=utf-8');

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

    $sql = "SELECT ph_min, ph_max, turbidez_max,
                   actualizado_en
            FROM configuracion_limites
            WHERE id = 1";

    $consulta = $conexion->query($sql);
    $limites = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$limites) {
        http_response_code(404);
        echo json_encode([
            'estado' => 'error',
            'mensaje' => 'Configuracion no encontrada'
        ]);
        exit;
    }

    echo json_encode([
        'estado' => 'correcto',
        'limites' => [
            'ph_min' => (float) $limites['ph_min'],
            'ph_max' => (float) $limites['ph_max'],
            'turbidez_max' => (float) $limites['turbidez_max'],
            'actualizado_en' => $limites['actualizado_en']
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    error_log('Error al consultar limites: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'No fue posible consultar los limites'
    ]);
}
