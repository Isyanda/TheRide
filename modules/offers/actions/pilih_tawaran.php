<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/sesi.php';
require_once dirname(__DIR__, 3) . '/core/http.php';
require_once dirname(__DIR__, 3) . '/modules/offers/data/kueri_tawaran.php';

if (!request_method('POST')) {
    json_response(['error' => 'Method POST diperlukan.'], 405);
}

$passengerId = require_api_user('passenger');
require_csrf();
$offerId = (int) post_float('offer_id', 1, PHP_INT_MAX);
/** @var mysqli $db */
$db = require dirname(__DIR__, 3) . '/core/koneksi.php';
try {
    $orderId = pilih_tawaran($db, $offerId, $passengerId);
} catch (DomainException $error) {
    json_response(['error' => $error->getMessage()], 409);
}
json_response(['message' => 'Driver berhasil dipilih.', 'order_id' => $orderId]);
