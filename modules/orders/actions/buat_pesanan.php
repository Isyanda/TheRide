<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/sesi.php';
require_once dirname(__DIR__, 3) . '/core/http.php';
require_once dirname(__DIR__, 3) . '/modules/orders/data/kueri_pesanan.php';

if (!request_method('POST')) {
    json_response(['error' => 'Method POST diperlukan.'], 405);
}

$passengerId = require_api_user('passenger');
require_csrf();
$pickupAddress = post_string('pickup_address', 255);
$destinationAddress = post_string('destination_address', 255);
$pickupLat = post_float('pickup_lat', -90, 90);
$pickupLng = post_float('pickup_lng', -180, 180);
$destinationLat = post_float('destination_lat', -90, 90);
$destinationLng = post_float('destination_lng', -180, 180);
$offeredFare = post_float('offered_fare', 1, 100000000);
$note = isset($_POST['note']) ? trim((string) $_POST['note']) : null;
$note = $note === '' ? null : substr($note, 0, 500);

/** @var mysqli $db */
$db = require dirname(__DIR__, 3) . '/core/koneksi.php';
$orderId = buat_pesanan(
    $db,
    $passengerId,
    $pickupAddress,
    $destinationAddress,
    $pickupLat,
    $pickupLng,
    $destinationLat,
    $destinationLng,
    $offeredFare,
    $note
);

json_response(['message' => 'Pesanan berhasil dibuat.', 'order_id' => $orderId], 201);
