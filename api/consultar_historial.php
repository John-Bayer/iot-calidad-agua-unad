<?php
// Proyecto IoT - UNAD
// Consulta historica de mediciones por fechas

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

// Fechas recibidas desde la pagina web
$fechaInicio = $_GET['inicio'] ?? '';
$fechaFin = $_GET['fin'] ?? '';

// Validar formato YYYY-MM-DD
function validarFecha($fecha) {
    $d = DateTime::createFromFormat('!Y-m-d', $fecha);

    return $d !== false &&
           $d->format('Y-m-d') === $fecha;
}

if (!validarFecha($fechaInicio) ||
    !validarFecha($fechaFin)) {

    http_response_code(400);

    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Fechas invalidas'
    ]);

    exit;
}

if ($fechaInicio > $fechaFin) {

    http_response_code(400);

    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'La fecha inicial supera la final'
    ]);

    exit;
}

try {

    // Incluye todo el dia final
    $inicio = $fechaInicio . ' 00:00:00';

    $fin = (new DateTime($fechaFin))
        ->modify('+1 day')
        ->format('Y-m-d 00:00:00');

    $sql = "SELECT *
            FROM mediciones
            WHERE fecha_hora >= :inicio
              AND fecha_hora < :fin
            ORDER BY fecha_hora ASC
            LIMIT 1000";

    $consulta = $conexion->prepare($sql);

    $consulta->execute([
        ':inicio' => $inicio,
        ':fin' => $fin
    ]);

    $mediciones = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'estado' => 'correcto',
        'total' => count($mediciones),
        'limite_consulta' => 1000,
        'mediciones' => $mediciones
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    error_log('Error al consultar historial: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Error consultando historial'
    ]);
}
