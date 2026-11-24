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
    <title>Daftar - TawarJalan</title>
    <link rel="stylesheet" href="assets/css/dasar.css">
    <link rel="stylesheet" href="assets/css/komponen.css">
</head>
<body>
<main class="page-shell auth-shell">
    <section class="panel auth-panel">
        <h1>Buat akun TawarJalan</h1>
        <?php if ($flash !== null): ?>
            <p class="notice notice-<?= e($flash['jenis']) ?>" role="alert"><?= e($flash['isi']) ?></p>
        <?php endif; ?>
        <form id="register-form" class="stack-form" method="post" action="?m=akun&a=aksi_daftar">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <label>Nama lengkap <input name="name" autocomplete="name" maxlength="120" required></label>
            <label>Email <input type="email" name="email" autocomplete="email" maxlength="190" required></label>
            <label>Kata sandi <input type="password" name="password" autocomplete="new-password" minlength="8" maxlength="72" required></label>
            <label>Daftar sebagai
                <select id="account-role" name="role" required>
                    <option value="passenger">Penumpang</option>
                    <option value="driver">Driver</option>
                </select>
            </label>
            <fieldset id="vehicle-fields" class="stack-form" hidden>
                <legend>Informasi kendaraan</legend>
                <label>Merek <input name="vehicle_brand" maxlength="80"></label>
                <label>Model <input name="vehicle_model" maxlength="80"></label>
                <label>Nomor polisi <input name="plate_number" maxlength="20"></label>
                <label>Warna <input name="vehicle_color" maxlength="40"></label>
            </fieldset>
            <button class="button button-primary" type="submit">Daftar</button>
        </form>
        <p>Sudah punya akun? <a href="?m=akun&a=masuk">Masuk</a>.</p>
    </section>
</main>
<script>
    (function () {
        var role = document.getElementById('account-role');
        var fields = document.getElementById('vehicle-fields');
        function toggleVehicleFields() {
            fields.hidden = role.value !== 'driver';
            fields.querySelectorAll('input').forEach(function (input) {
                input.required = !fields.hidden;
            });
        }
        role.addEventListener('change', toggleVehicleFields);
        toggleVehicleFields();
    })();
</script>
</body>
</html>
