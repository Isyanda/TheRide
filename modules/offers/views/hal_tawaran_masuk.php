<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/modules/orders/data/kueri_pesanan.php';

$passengerId = require_page_user('passenger');
$orderId = filter_var($_GET['order_id'] ?? null, FILTER_VALIDATE_INT);
if ($orderId === false || $orderId < 1) {
    http_response_code(400);
    exit('ID pesanan tidak valid.');
}
/** @var mysqli $db */
$db = require dirname(__DIR__, 3) . '/core/koneksi.php';
$order = status_pesanan_penumpang($db, (int) $orderId, $passengerId);
if ($order === null) {
    http_response_code(404);
    exit('Pesanan tidak ditemukan.');
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tawaran Driver - TawarJalan</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="assets/css/dasar.css">
    <link rel="stylesheet" href="assets/css/komponen.css">
    <link rel="stylesheet" href="assets/css/peta.css">
</head>
<body>
<main class="page-shell">
    <section class="panel">
        <nav class="page-links">
            <a href="?m=pesanan&a=riwayat">Kembali ke riwayat</a>
            <form method="post" action="?m=akun&a=aksi_keluar">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <button class="link-button" type="submit">Keluar</button>
            </form>
        </nav>
        <h1>Tawaran dari driver</h1>
        <p class="muted"><?= e($order['pickup_address'] ?? '') ?> &rarr; <?= e($order['destination_address'] ?? '') ?></p>
        <p id="order-status" class="notice" role="status">Status pesanan: <?= e($order['status']) ?></p>
        <div id="driver-map" class="map"></div>
        <div id="offers" class="offer-list" data-order-id="<?= (int) $orderId ?>"></div>
        <p id="offers-message" class="form-message" role="status"></p>
        <form id="cancel-order-form" method="post" action="?m=pesanan&a=aksi_batalkan">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="order_id" value="<?= (int) $orderId ?>">
            <button class="button button-secondary" type="submit">Batalkan pesanan</button>
        </form>
    </section>
</main>
<script>
    window.TAWARJALAN = {
        orderId: <?= (int) $orderId ?>,
        csrf: <?= json_encode(csrf_token(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
    };
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="assets/js/tawar.js"></script>
</body>
</html>
