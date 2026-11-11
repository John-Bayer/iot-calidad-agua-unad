
<?php
// Proyecto IoT - UNAD
// Recepcion de mediciones del ESP32

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Solo se permite POST'
    ]);
    exit;
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

// Validar campos obligatorios
$campos = ['dispositivo_id', 'ph', 'turbidez', 'temperatura'];

foreach ($campos as $campo) {
    if (!isset($datos[$campo]) || !is_numeric($datos[$campo])) {
        http_response_code(400);
        echo json_encode([
            'estado' => 'error',
            'mensaje' => 'Campo invalido: ' . $campo
        ]);
        exit;
    }
}

$id = filter_var(
    $datos['dispositivo_id'],
    FILTER_VALIDATE_INT
);

$ph = (float) $datos['ph'];
$turbidez = (float) $datos['turbidez'];
$temperatura = (float) $datos['temperatura'];

if ($id === false || $id < 1 ||
    $ph < 0 || $ph > 14 ||
    $turbidez < 0 || $turbidez > 999999.99 ||
    $temperatura < -100 || $temperatura > 100) {

    http_response_code(400);
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Valores fuera de rango'
    ]);
    exit;
}

require_once __DIR__ . '/conexion.php';

try {
    $sql = "INSERT INTO mediciones
            (dispositivo_id, ph, turbidez, temperatura)
            VALUES (:id, :ph, :turbidez, :temperatura)";

    $consulta = $conexion->prepare($sql);

    $consulta->execute([
        ':id' => $id,
        ':ph' => $ph,
        ':turbidez' => $turbidez,
        ':temperatura' => $temperatura
    ]);

    echo json_encode([
        'estado' => 'correcto',
        'mensaje' => 'Medicion almacenada',
        'medicion_id' => $conexion->lastInsertId()
    ]);

} catch (PDOException $e) {
    error_log('Error al almacenar medicion: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'No fue posible almacenar la medicion'
    ]);
}