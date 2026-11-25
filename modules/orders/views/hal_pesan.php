<?php

declare(strict_types=1);

require_page_user('passenger');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buat Pesanan - TawarJalan</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="assets/css/dasar.css">
    <link rel="stylesheet" href="assets/css/komponen.css">
    <link rel="stylesheet" href="assets/css/peta.css">
</head>
<body>
<main class="page-shell">
    <section class="panel">
        <h1>Pesan perjalanan</h1>
        <p class="muted">Tentukan lokasi dan harga yang sanggup kamu bayar.</p>
        <nav class="page-links">
            <span>Hai, <?= e($_SESSION['pengguna']['nama'] ?? '') ?></span>
            <a href="?m=pesanan&a=riwayat">Riwayat</a>
            <form method="post" action="?m=akun&a=aksi_keluar">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <button class="link-button" type="submit">Keluar</button>
            </form>
        </nav>
        <div id="order-map" class="map"></div>
        <p id="route-estimate" class="muted" role="status">Klik peta untuk memilih titik jemput dan tujuan.</p>
        <form id="order-form" class="stack-form">
            <label>Alamat jemput <input name="pickup_address" required maxlength="255"></label>
            <label>Alamat tujuan <input name="destination_address" required maxlength="255"></label>
            <div class="grid-2">
                <label>Latitude jemput <input name="pickup_lat" type="number" step="any" required></label>
                <label>Longitude jemput <input name="pickup_lng" type="number" step="any" required></label>
                <label>Latitude tujuan <input name="destination_lat" type="number" step="any" required></label>
                <label>Longitude tujuan <input name="destination_lng" type="number" step="any" required></label>
            </div>
            <label>Tawaran kamu (Rp) <input name="offered_fare" type="number" min="1" required></label>
            <label>Catatan untuk driver <textarea name="note" maxlength="500"></textarea></label>
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <button class="button button-primary" type="submit">Cari driver</button>
            <p id="order-message" class="form-message" role="status"></p>
        </form>
    </section>
</main>
<script>
    window.TAWARJALAN = {
        orderMap: 'order-map',
        orderForm: 'order-form',
        csrf: <?= json_encode(csrf_token(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
    };
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="assets/js/peta_init.js"></script>
<script src="assets/js/tawar.js"></script>
</body>
</html>
