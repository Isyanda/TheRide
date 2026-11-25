<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/sesi.php';
require_once dirname(__DIR__, 3) . '/core/http.php';

if (!request_method('POST')) {
    json_response(['error' => 'Method POST diperlukan.'], 405);
}

$driverId = require_api_user('driver');
require_csrf();
$orderId = (int) post_float('order_id', 1, PHP_INT_MAX);
/** @var mysqli $db */
$db = require dirname(__DIR__, 3) . '/core/koneksi.php';
$query = $db->prepare(
    'UPDATE orders SET status = "in_progress"
     WHERE id = ? AND selected_driver_id = ? AND status = "accepted"'
);
$query->bind_param('ii', $orderId, $driverId);
$query->execute();
if ($query->affected_rows !== 1) {
    json_response(['error' => 'Pesanan tidak tersedia untuk dimulai.'], 409);
}

json_response(['message' => 'Perjalanan dimulai.', 'order_id' => $orderId]);
