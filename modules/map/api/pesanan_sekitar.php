<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/sesi.php';
require_once dirname(__DIR__, 3) . '/core/http.php';
require_once dirname(__DIR__, 3) . '/modules/orders/data/kueri_pesanan.php';
require_once dirname(__DIR__, 3) . '/modules/map/data/kueri_lokasi.php';

$driverId = require_api_user('driver');
$radius = filter_var($_GET['radius'] ?? 3, FILTER_VALIDATE_FLOAT);
if ($radius === false || $radius <= 0 || $radius > 3) {
    json_response(['error' => 'Radius tidak valid.'], 422);
}

/** @var mysqli $db */
$db = require dirname(__DIR__, 3) . '/core/koneksi.php';
$location = ambil_lokasi_driver_terbaru($db, $driverId);
if ($location === null) {
    json_response(['error' => 'Aktifkan lokasi driver untuk melihat pesanan sekitar.'], 409);
}
json_response([
    'driver_id' => $driverId,
    'orders' => daftar_pesanan_sekitar(
        $db,
        (float) $location['latitude'],
        (float) $location['longitude'],
        (float) $radius
    ),
    'assigned_orders' => daftar_pesanan_driver($db, $driverId),
]);
