<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/sesi.php';
require_once dirname(__DIR__, 3) . '/core/http.php';
require_once dirname(__DIR__, 3) . '/modules/orders/data/kueri_pesanan.php';

if (!request_method('POST')) {
    json_response(['error' => 'Method POST diperlukan.'], 405);
}

$driverId = require_api_user('driver');
require_csrf();
$orderId = (int) post_float('order_id', 1, PHP_INT_MAX);
/** @var mysqli $db */
$db = require dirname(__DIR__, 3) . '/core/koneksi.php';
$query = $db->prepare(
    'UPDATE orders SET status = "completed"
     WHERE id = ? AND selected_driver_id = ? AND status = "in_progress"'
);
$query->bind_param('ii', $orderId, $driverId);
$query->execute();
if ($query->affected_rows !== 1) {
    json_response(['error' => 'Pesanan tidak ditemukan atau belum berjalan.'], 409);
}
json_response(['message' => 'Pesanan diselesaikan.']);
