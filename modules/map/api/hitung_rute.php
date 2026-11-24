<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/sesi.php';
require_once dirname(__DIR__, 3) . '/core/http.php';

require_api_user();
$fromLat = filter_var($_GET['from_lat'] ?? null, FILTER_VALIDATE_FLOAT);
$fromLng = filter_var($_GET['from_lng'] ?? null, FILTER_VALIDATE_FLOAT);
$toLat = filter_var($_GET['to_lat'] ?? null, FILTER_VALIDATE_FLOAT);
$toLng = filter_var($_GET['to_lng'] ?? null, FILTER_VALIDATE_FLOAT);
if ($fromLat === false || $fromLng === false || $toLat === false || $toLng === false
    || $fromLat < -90 || $fromLat > 90 || $toLat < -90 || $toLat > 90
    || $fromLng < -180 || $fromLng > 180 || $toLng < -180 || $toLng > 180) {
    json_response(['error' => 'Koordinat rute tidak valid.'], 422);
}

$earthRadiusKm = 6371;
$latDistance = deg2rad((float) $toLat - (float) $fromLat);
$lngDistance = deg2rad((float) $toLng - (float) $fromLng);
$a = sin($latDistance / 2) ** 2
    + cos(deg2rad((float) $fromLat)) * cos(deg2rad((float) $toLat))
    * sin($lngDistance / 2) ** 2;
$distanceKm = $earthRadiusKm * 2 * asin(min(1, sqrt($a)));

json_response([
    'distance_km' => round($distanceKm, 2),
    'duration_minutes' => (int) max(1, round(($distanceKm / 30) * 60)),
    'coordinates' => [
        [(float) $fromLat, (float) $fromLng],
        [(float) $toLat, (float) $toLng],
    ],
]);
