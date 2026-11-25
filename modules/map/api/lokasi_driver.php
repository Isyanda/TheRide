<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/sesi.php';
require_once dirname(__DIR__, 3) . '/core/http.php';

$passengerId = require_api_user('passenger');
$orderId = filter_var($_GET['order_id'] ?? null, FILTER_VALIDATE_INT);
if ($orderId === false || $orderId < 1) {
    json_response(['error' => 'order_id tidak valid.'], 422);
}

/** @var mysqli $db */
$db = require dirname(__DIR__, 3) . '/core/koneksi.php';
$query = $db->prepare(
    'SELECT u.id AS driver_id, u.name, dl.latitude, dl.longitude, dl.updated_at
     FROM orders o
     JOIN users u ON u.id = o.selected_driver_id AND u.role = "driver"
     JOIN driver_locations dl ON dl.driver_id = u.id
     WHERE o.id = ? AND o.passenger_id = ?
       AND o.status IN ("accepted", "in_progress")
       AND dl.updated_at >= CURRENT_TIMESTAMP - INTERVAL 5 MINUTE
     LIMIT 1'
);
$query->bind_param('ii', $orderId, $passengerId);
$query->execute();
$driver = $query->get_result()->fetch_assoc();
if ($driver === null) {
    json_response(['error' => 'Lokasi driver belum tersedia.'], 404);
}

json_response(['driver' => $driver]);
