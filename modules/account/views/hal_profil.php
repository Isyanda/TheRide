<?php

declare(strict_types=1);

require_page_user();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil - TawarJalan</title>
    <link rel="stylesheet" href="assets/css/dasar.css">
    <link rel="stylesheet" href="assets/css/komponen.css">
</head>
<body>
<main class="page-shell">
    <section class="panel">
        <h1>Profil</h1>
        <p><strong><?= e($_SESSION['pengguna']['nama'] ?? '') ?></strong></p>
        <p><?= e($_SESSION['pengguna']['email'] ?? '') ?></p>
        <p><?= pengguna_memiliki_peran('driver') ? 'Driver' : 'Penumpang' ?></p>
        <form method="post" action="?m=akun&a=aksi_keluar">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <button class="button button-primary" type="submit">Keluar</button>
        </form>
    </section>
</main>
</body>
</html>
