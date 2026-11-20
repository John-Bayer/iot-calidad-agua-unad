<?php
// Proyecto IoT - UNAD
// Autenticacion administrativa

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

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

$datos = json_decode(file_get_contents('php://input'), true);

if (!is_array($datos) ||
    !is_string($datos['usuario'] ?? null) ||
    !is_string($datos['password'] ?? null)) {

    http_response_code(400);
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Datos invalidos'
    ]);
    exit;
}

require_once __DIR__ . '/conexion.php';

try {

    $sql = "SELECT id, usuario, password_hash
            FROM administradores
            WHERE usuario = :usuario
            LIMIT 1";

    $consulta = $conexion->prepare($sql);

    $consulta->execute([
        ':usuario' => $datos['usuario']
    ]);

    $admin = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$admin ||
        !password_verify(
            $datos['password'],
            $admin['password_hash']
        )) {

        http_response_code(401);
        echo json_encode([
            'estado' => 'error',
            'mensaje' => 'Credenciales incorrectas'
        ]);
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_usuario'] = $admin['usuario'];

    echo json_encode([
        'estado' => 'correcto',
        'mensaje' => 'Sesion iniciada',
        'usuario' => $admin['usuario']
    ]);

} catch (PDOException $e) {

    error_log('Error de autenticacion: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Error interno'
    ]);
}