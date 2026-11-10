<?php
// Proyecto IoT UNAD
// Prueba de recepcion de solicitudes HTTP

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

$contenido = file_get_contents('php://input');
$datos = json_decode($contenido, true);

if (!is_array($datos)) {
    http_response_code(400);
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'JSON invalido'
    ]);
    exit;
}

echo json_encode([
    'estado' => 'correcto',
    'mensaje' => 'Solicitud recibida',
    'datos' => $datos
]);
