<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/sesi.php';
require_once dirname(__DIR__, 3) . '/core/http.php';
require_once dirname(__DIR__, 3) . '/modules/orders/data/kueri_pesanan.php';
require_once dirname(__DIR__, 3) . '/modules/offers/data/kueri_tawaran.php';

if (!request_method('POST')) {
    json_response(['error' => 'Method POST diperlukan.'], 405);
}

$driverId = require_api_user('driver');
require_csrf();
$orderId = (int) post_float('order_id', 1, PHP_INT_MAX);
$fare = post_float('fare', 1, 100000000);
/** @var mysqli $db */
$db = require dirname(__DIR__, 3) . '/core/koneksi.php';
$db->begin_transaction();
try {
    $query = $db->prepare(
        'SELECT id FROM orders WHERE id = ? AND status = "searching" FOR UPDATE'
    );
    $query->bind_param('i', $orderId);
    $query->execute();
    if (!$query->get_result()->fetch_assoc() || !radius_driver_pesanan($db, $driverId, $orderId)) {
        $db->rollback();
        json_response(['error' => 'Pesanan tidak tersedia atau berada di luar radius 3 km. Perbarui lokasi driver.'], 409);
    }

    $offerId = kirim_tawaran($db, $orderId, $driverId, $fare);
    $db->commit();
} catch (Throwable $error) {
    $db->rollback();
    throw $error;
}

json_response(['message' => 'Tawaran berhasil dikirim.', 'offer_id' => $offerId], 201);
