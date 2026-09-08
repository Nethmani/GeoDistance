<?php
declare(strict_types=1);

/** Validates one coordinate without coercing malformed input. */
function parseCoordinate(mixed $value, string $type): array
{
    $label = $type === 'latitude' ? 'Latitude' : 'Longitude';
    $min = $type === 'latitude' ? -90.0 : -180.0;
    $max = $type === 'latitude' ? 90.0 : 180.0;

    if (!is_string($value) && !is_int($value) && !is_float($value)) {
        return [null, 'Please enter valid numeric coordinates.'];
    }

    $raw = trim((string) $value);
    if ($raw === '') {
        return [null, 'Please enter all four coordinates.'];
    }

    // Decimal notation only; prevents values such as 1e100 or mixed text.
    if (!preg_match('/^[+-]?(?:\d+(?:\.\d*)?|\.\d+)$/', $raw)) {
        return [null, 'Please enter valid numeric coordinates.'];
    }

    $number = (float) $raw;
    if (!is_finite($number)) {
        return [null, 'Please enter valid numeric coordinates.'];
    }
    if ($number < $min || $number > $max) {
        return [null, "$label must be between $min and $max degrees."];
    }

    return [$number, null];
}

/**
 * Custom Haversine implementation. This calculates great-circle distance in
 * kilometres; no geographic package, map service, or distance API is used.
 */
function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $degreesToRadians = M_PI / 180.0;
    $lat1Rad = $lat1 * $degreesToRadians;
    $lat2Rad = $lat2 * $degreesToRadians;
    $deltaLat = ($lat2 - $lat1) * $degreesToRadians;
    $deltaLon = ($lon2 - $lon1) * $degreesToRadians;

    $sinLat = sin($deltaLat / 2.0);
    $sinLon = sin($deltaLon / 2.0);
    $a = ($sinLat * $sinLat) + cos($lat1Rad) * cos($lat2Rad) * ($sinLon * $sinLon);
    $a = min(1.0, max(0.0, $a)); // protects rounding at antipodal points
    $centralAngle = 2.0 * atan2(sqrt($a), sqrt(1.0 - $a));

    return 6371.0 * $centralAngle;
}
