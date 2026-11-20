<?php
// Proyecto IoT - UNAD
// Consulta de alertas registradas

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

require_once __DIR__ . '/conexion.php';

try {
    
    if (!isset($_GET['dispositivo_id']) ||
        !ctype_digit((string) $_GET['dispositivo_id']) ||
        (int) $_GET['dispositivo_id'] <= 0) {

        http_response_code(400);

        echo json_encode([
            'estado' => 'error',
            'mensaje' => 'dispositivo_id invalido'
        ]);

        exit;
    }

    $idDispositivo = (int) $_GET['dispositivo_id'];
    $sql = "SELECT
                a.id,
                a.medicion_id,
                a.parametro,
                a.mensaje,
                a.fecha,
                d.ubicacion AS dispositivo
            FROM alertas a
            INNER JOIN mediciones m
                ON m.id = a.medicion_id
            INNER JOIN dispositivos d
                ON d.id = m.dispositivo_id
            WHERE m.dispositivo_id = :dispositivo_id
            ORDER BY a.fecha DESC, a.id DESC
            LIMIT 50";

    $consulta = $conexion->prepare($sql);
    $consulta->execute([
        ':dispositivo_id' => $idDispositivo
    ]);
    $alertas = $consulta->fetchAll();

    echo json_encode([
        'estado' => 'correcto',
        'total' => count($alertas),
        'alertas' => $alertas
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

} catch (PDOException $e) {
    error_log('Error consultando alertas: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'No fue posible consultar las alertas'
    ]);
}