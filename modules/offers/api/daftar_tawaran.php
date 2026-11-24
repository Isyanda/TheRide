<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/sesi.php';
require_once dirname(__DIR__, 3) . '/core/http.php';
require_once dirname(__DIR__, 3) . '/modules/offers/data/kueri_tawaran.php';
require_once dirname(__DIR__, 3) . '/modules/orders/data/kueri_pesanan.php';

$passengerId = require_api_user('passenger');
$orderId = filter_var($_GET['order_id'] ?? null, FILTER_VALIDATE_INT);
if ($orderId === false || $orderId < 1) {
    json_response(['error' => 'order_id tidak valid.'], 422);
}

/** @var mysqli $db */
$db = require dirname(__DIR__, 3) . '/core/koneksi.php';
$order = status_pesanan_penumpang($db, $orderId, $passengerId);
if ($order === null) {
    json_response(['error' => 'Pesanan tidak ditemukan.'], 404);
}
json_response([
    'order' => $order,
    'offers' => daftar_tawaran($db, $orderId, $passengerId),
]);
