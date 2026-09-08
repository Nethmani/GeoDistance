<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
header_remove('X-Powered-By');

require_once __DIR__ . '/../functions/distance.php';
require_once __DIR__ . '/../config/database.php';

function respond(int $status, array $data): never
{
    http_response_code($status);
    echo json_encode($data, JSON_PRESERVE_ZERO_FRACTION);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['success' => false, 'message' => 'Only POST requests are allowed.']);
}

$fields = [
    'location_a_latitude' => 'latitude',
    'location_a_longitude' => 'longitude',
    'location_b_latitude' => 'latitude',
    'location_b_longitude' => 'longitude',
];
$coordinates = [];
foreach ($fields as $field => $type) {
    [$coordinate, $error] = parseCoordinate($_POST[$field] ?? null, $type);
    if ($error !== null) {
        respond(422, ['success' => false, 'message' => $error, 'field' => $field]);
    }
    $coordinates[$field] = $coordinate;
}

$distance = calculateDistance(
    $coordinates['location_a_latitude'],
    $coordinates['location_a_longitude'],
    $coordinates['location_b_latitude'],
    $coordinates['location_b_longitude']
);

try {
    $pdo = getDatabaseConnection();
    $save = $pdo->prepare('CALL sp_save_distance_calculation(:a_lat, :a_lon, :b_lat, :b_lon, :distance)');
    $save->execute([
        ':a_lat' => $coordinates['location_a_latitude'],
        ':a_lon' => $coordinates['location_a_longitude'],
        ':b_lat' => $coordinates['location_b_latitude'],
        ':b_lon' => $coordinates['location_b_longitude'],
        ':distance' => $distance,
    ]);
    $save->closeCursor();

    respond(200, [
        'success' => true,
        'message' => 'Distance calculated successfully.',
        'distance' => $distance,
        'coordinates' => $coordinates,
    ]);
} catch (Throwable $exception) {
    error_log('GeoDistance database error: ' . $exception->getMessage());
    respond(503, ['success' => false, 'message' => 'Unable to save the calculation right now. Please try again.']);
}
