<?php
// Proyecto IoT - UNAD
// Actualizacion de limites de alerta

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Solo se permite POST'
    ]);
    exit;
}

session_start();

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'No autorizado'
    ]);
    exit;
}

// Comprobar origen de solicitudes del navegador
$origen = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origen !== '') {
    $origenHost = parse_url($origen, PHP_URL_HOST);
    $origenPuerto = parse_url($origen, PHP_URL_PORT);

    $hostActual = $_SERVER['HTTP_HOST'] ?? '';
    $hostEsperado = parse_url('http://' . $hostActual, PHP_URL_HOST);
    $puertoEsperado = parse_url(
        'http://' . $hostActual,
        PHP_URL_PORT
    );

    if ($origenHost !== $hostEsperado ||
        $origenPuerto !== $puertoEsperado) {
        http_response_code(403);
        echo json_encode([
            'estado' => 'error',
            'mensaje' => 'Origen no permitido'
        ]);
        exit;
    }
}

$datos = json_decode(file_get_contents('php://input'), true);

if (!is_array($datos)) {
    http_response_code(400);
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'JSON invalido'
    ]);
    exit;
}

$campos = ['ph_min', 'ph_max', 'turbidez_max'];

foreach ($campos as $campo) {
    if (!isset($datos[$campo]) ||
        !is_numeric($datos[$campo])) {
        http_response_code(400);
        echo json_encode([
            'estado' => 'error',
            'mensaje' => 'Campo invalido: ' . $campo
        ]);
        exit;
    }
}

$phMin = (float) $datos['ph_min'];
$phMax = (float) $datos['ph_max'];
$turbidezMax = (float) $datos['turbidez_max'];

if (!is_finite($phMin) ||
    !is_finite($phMax) ||
    !is_finite($turbidezMax) ||
    $phMin < 0 ||
    $phMax > 14 ||
    $phMin >= $phMax ||
    $turbidezMax < 0 ||
    $turbidezMax > 999999.99) {

    http_response_code(400);
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Limites fuera de rango'
    ]);
    exit;
}

require_once __DIR__ . '/conexion.php';

try {

    $sql = "UPDATE configuracion_limites
            SET ph_min = :ph_min,
                ph_max = :ph_max,
                turbidez_max = :turbidez_max
            WHERE id = 1";

    $consulta = $conexion->prepare($sql);

    $consulta->execute([
        ':ph_min' => $phMin,
        ':ph_max' => $phMax,
        ':turbidez_max' => $turbidezMax
    ]);

    echo json_encode([
        'estado' => 'correcto',
        'mensaje' => 'Limites actualizados',
        'limites' => [
            'ph_min' => $phMin,
            'ph_max' => $phMax,
            'turbidez_max' => $turbidezMax
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    error_log('Error al actualizar limites: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'No fue posible actualizar los limites'
    ]);
}
