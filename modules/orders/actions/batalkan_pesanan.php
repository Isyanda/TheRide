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
$orderId = (int) post_float('order_id', 1, PHP_INT_MAX);
/** @var mysqli $db */
$db = require dirname(__DIR__, 3) . '/core/koneksi.php';
$query = $db->prepare(
    'UPDATE orders SET status = "cancelled"
     WHERE id = ? AND passenger_id = ? AND status IN ("searching", "accepted")'
);
$query->bind_param('ii', $orderId, $passengerId);
$query->execute();
if ($query->affected_rows !== 1) {
    json_response(['error' => 'Pesanan tidak dapat dibatalkan.'], 409);
}

$offers = $db->prepare('UPDATE offers SET status = "rejected" WHERE order_id = ? AND status = "pending"');
$offers->bind_param('i', $orderId);
$offers->execute();
json_response(['message' => 'Pesanan dibatalkan.']);
