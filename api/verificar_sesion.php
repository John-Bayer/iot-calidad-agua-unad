<?php
// Proyecto IoT - UNAD
// Verificacion de sesion administrativa

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Solo se permite GET'
    ]);
    exit;
}

session_start();

if (!isset($_SESSION['admin_id'])) {

    http_response_code(401);

    echo json_encode([
        'estado' => 'error',
        'autenticado' => false,
        'mensaje' => 'Sesion no iniciada'
    ]);

    exit;
}

echo json_encode([
    'estado' => 'correcto',
    'autenticado' => true,
    'usuario' => $_SESSION['admin_usuario']
], JSON_UNESCAPED_UNICODE);
