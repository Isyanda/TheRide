<?php

declare(strict_types=1);

$passengerId = require_page_user('passenger');
/** @var mysqli $db */
$db = require dirname(__DIR__, 3) . '/core/koneksi.php';
$query = $db->prepare(
    'SELECT id, pickup_address, destination_address, offered_fare, status, created_at
     FROM orders WHERE passenger_id = ? ORDER BY created_at DESC LIMIT 50'
);
$query->bind_param('i', $passengerId);
$query->execute();
$orders = $query->get_result()->fetch_all(MYSQLI_ASSOC);
$labels = [
    'searching' => 'Mencari driver',
    'accepted' => 'Driver dipilih',
    'in_progress' => 'Dalam perjalanan',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan',
];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Pesanan - TawarJalan</title>
    <link rel="stylesheet" href="assets/css/dasar.css">
    <link rel="stylesheet" href="assets/css/komponen.css">
</head>
<body>
<main class="page-shell">
    <section class="panel">
        <div class="panel-heading">
            <div><h1>Riwayat pesanan</h1><p><?= e($_SESSION['pengguna']['nama'] ?? '') ?></p></div>
            <a class="button button-secondary" href="?m=pesanan&a=buat">Pesan perjalanan</a>
        </div>
        <div class="order-list">
            <?php if ($orders === []): ?>
                <p>Belum ada pesanan.</p>
            <?php endif; ?>
            <?php foreach ($orders as $order): ?>
                <article class="order-card">
                    <header>
                        <strong><?= e($order['pickup_address']) ?> &rarr; <?= e($order['destination_address']) ?></strong>
                        <span class="price">Rp <?= number_format((float) $order['offered_fare'], 0, ',', '.') ?></span>
                    </header>
                    <p><?= e($labels[$order['status']] ?? $order['status']) ?> &middot; <?= e($order['created_at']) ?></p>
                    <?php if (in_array($order['status'], ['searching', 'accepted', 'in_progress'], true)): ?>
                        <a href="?m=tawaran&a=masuk&order_id=<?= (int) $order['id'] ?>">Lihat status dan tawaran</a>
                    <?php endif; ?>
                    <?php if (in_array($order['status'], ['searching', 'accepted'], true)): ?>
                        <form method="post" action="?m=pesanan&a=aksi_batalkan">
                            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                            <button class="button button-secondary" type="submit">Batalkan</button>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <p id="history-message" class="form-message" role="status"></p>
        <form method="post" action="?m=akun&a=aksi_keluar">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <button class="button button-secondary" type="submit">Keluar</button>
        </form>
    </section>
</main>
<script>
    (function () {
        var token = <?= json_encode(csrf_token(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        document.querySelectorAll('form[action="?m=pesanan&a=aksi_batalkan"]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                if (!window.confirm('Yakin ingin membatalkan pesanan?')) return;
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { Accept: 'application/json', 'X-CSRF-Token': token }
                }).then(function (response) {
                    return response.json().then(function (data) {
                        if (!response.ok) throw new Error(data.error || 'Gagal membatalkan pesanan.');
                        return data;
                    });
                }).then(function (data) {
                    document.getElementById('history-message').textContent = data.message;
                    window.location.reload();
                }).catch(function (error) {
                    document.getElementById('history-message').textContent = error.message;
                });
            });
        });
    })();
</script>
</body>
</html>
