<?php

declare(strict_types=1);

require_page_user('driver');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Peta Pesanan - TawarJalan</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="assets/css/dasar.css">
    <link rel="stylesheet" href="assets/css/komponen.css">
    <link rel="stylesheet" href="assets/css/peta.css">
</head>
<body>
<main class="page-shell">
    <section class="panel">
        <div class="panel-heading">
            <div>
                <h1>Peta pesanan</h1>
                <p class="muted">Halo, <?= e($_SESSION['pengguna']['nama'] ?? '') ?>. Pesanan dalam radius 3 km.</p>
            </div>
            <div class="location-actions">
                <button id="driver-location" class="button button-secondary" type="button">Aktifkan GPS</button>
                <button id="choose-driver-location" class="button button-primary" type="button">Pilih lokasi di peta</button>
            </div>
        </div>
        <nav class="page-links">
            <span id="location-status" role="status">Aktifkan lokasi agar pesanan terdekat muncul.</span>
            <form method="post" action="?m=akun&a=aksi_keluar">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <button class="link-button" type="submit">Keluar</button>
            </form>
        </nav>
        <div id="driver-map" class="map map-large"></div>
        <h2>Pesanan sekitar</h2>
        <div id="nearby-orders" class="order-list"></div>
        <h2>Pesanan diterima</h2>
        <div id="assigned-orders" class="order-list"></div>
    </section>
</main>
<script>
    window.TAWARJALAN = {
        driverMap: 'driver-map',
        nearbyOrders: 'nearby-orders',
        assignedOrders: 'assigned-orders',
        csrf: <?= json_encode(csrf_token(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
    };
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="assets/js/polling_lokasi.js"></script>
</body>
</html>
