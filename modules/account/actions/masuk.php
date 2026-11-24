<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/sesi.php';
require_once dirname(__DIR__, 3) . '/core/bantu.php';
require_once dirname(__DIR__, 3) . '/core/http.php';
require_once dirname(__DIR__, 3) . '/modules/account/data/kueri_pengguna.php';

if (pengguna_sudah_masuk()) {
    arahkan_setelah_login();
}

if (!request_method('POST')) {
    http_response_code(405);
    exit('Method POST diperlukan.');
}

if (!csrf_is_valid()) {
    simpan_pesan('error', 'Sesi formulir kedaluwarsa. Silakan coba lagi.');
    alihkan('?m=akun&a=masuk');
}

$email = filter_var(strtolower(trim((string) ($_POST['email'] ?? ''))), FILTER_VALIDATE_EMAIL);
$password = (string) ($_POST['password'] ?? '');
if ($email === false || $password === '' || strlen($password) > 256) {
    simpan_pesan('error', 'Email atau kata sandi tidak valid.');
    alihkan('?m=akun&a=masuk');
}

/** @var mysqli $db */
$db = require dirname(__DIR__, 3) . '/core/koneksi.php';
$user = cari_pengguna_dengan_email($db, $email);
if ($user === null || !password_verify($password, $user['password_hash'])) {
    simpan_pesan('error', 'Email atau kata sandi salah.');
    alihkan('?m=akun&a=masuk');
}

session_regenerate_id(true);
$_SESSION['pengguna'] = [
    'id' => (int) $user['id'],
    'nama' => $user['name'],
    'email' => $user['email'],
    'peran' => $user['role'],
];
unset($_SESSION['csrf_token']);
arahkan_setelah_login();
