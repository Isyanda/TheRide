<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/sesi.php';
require_once dirname(__DIR__, 3) . '/core/http.php';
require_once dirname(__DIR__, 3) . '/modules/map/data/kueri_lokasi.php';

if (!request_method('POST')) {
    json_response(['error' => 'Method POST diperlukan.'], 405);
}

$driverId = require_api_user('driver');
require_csrf();
$latitude = post_float('lat', -90, 90);
$longitude = post_float('lng', -180, 180);
/** @var mysqli $db */
$db = require dirname(__DIR__, 3) . '/core/koneksi.php';
simpan_lokasi_driver($db, $driverId, $latitude, $longitude);
json_response(['message' => 'Lokasi driver diperbarui.']);
