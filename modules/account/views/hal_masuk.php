<?php

declare(strict_types=1);

if (pengguna_sudah_masuk()) {
    arahkan_setelah_login();
}
$flash = ambil_pesan();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - TawarJalan</title>
    <link rel="stylesheet" href="assets/css/dasar.css">
    <link rel="stylesheet" href="assets/css/komponen.css">
</head>
<body>
<main class="page-shell auth-shell">
    <section class="panel auth-panel">
        <p class="muted">Transportasi yang harganya bisa kamu tawar.</p>
        <h1>Masuk ke TawarJalan</h1>
        <?php if ($flash !== null): ?>
            <p class="notice notice-<?= e($flash['jenis']) ?>" role="alert"><?= e($flash['isi']) ?></p>
        <?php endif; ?>
        <form class="stack-form" method="post" action="?m=akun&a=aksi_masuk">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <label>Email <input type="email" name="email" autocomplete="email" required maxlength="190"></label>
            <label>Kata sandi <input type="password" name="password" autocomplete="current-password" required></label>
            <button class="button button-primary" type="submit">Masuk</button>
        </form>
        <p>Belum punya akun? <a href="?m=akun&a=daftar">Daftar di sini</a>.</p>
    </section>
</main>
</body>
</html>
