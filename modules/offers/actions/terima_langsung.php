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
if (!radius_driver_pesanan($db, $driverId, $orderId)) {
    json_response(['error' => 'Pesanan tidak tersedia atau berada di luar radius 3 km. Perbarui lokasi driver.'], 409);
}

$db->begin_transaction();
try {
    $query = $db->prepare(
        'UPDATE orders SET status = "accepted", selected_driver_id = ?
         WHERE id = ? AND status = "searching"'
    );
    $query->bind_param('ii', $driverId, $orderId);
    $query->execute();
    if ($query->affected_rows !== 1) {
        $db->rollback();
        json_response(['error' => 'Pesanan baru saja diterima driver lain.'], 409);
    }
    $reject = $db->prepare('UPDATE offers SET status = "rejected" WHERE order_id = ?');
    $reject->bind_param('i', $orderId);
    $reject->execute();
    $db->commit();
} catch (Throwable $error) {
    $db->rollback();
    throw $error;
}
json_response(['message' => 'Pesanan diterima.', 'order_id' => $orderId]);
