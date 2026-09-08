<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
header_remove('X-Powered-By');
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDatabaseConnection();
    $rows = $pdo->query('SELECT location_a_latitude, location_a_longitude, location_b_latitude, location_b_longitude, distance_km, created_at FROM distance_calculations ORDER BY created_at DESC, id DESC LIMIT 10')->fetchAll();
    echo json_encode(['success' => true, 'items' => $rows], JSON_PRESERVE_ZERO_FRACTION);
} catch (Throwable $exception) {
    error_log('GeoDistance history error: ' . $exception->getMessage());
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Recent calculations are unavailable.']);
}
