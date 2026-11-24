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
    alihkan('?m=akun&a=daftar');
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = filter_var(strtolower(trim((string) ($_POST['email'] ?? ''))), FILTER_VALIDATE_EMAIL);
$password = (string) ($_POST['password'] ?? '');
$role = (string) ($_POST['role'] ?? '');
if ($name === '' || strlen($name) > 120 || $email === false
    || strlen((string) $email) > 190 || strlen($password) < 8 || strlen($password) > 72
    || !in_array($role, ['passenger', 'driver'], true)) {
    simpan_pesan('error', 'Isi nama, email, dan peran dengan benar. Kata sandi harus 8-72 byte.');
    alihkan('?m=akun&a=daftar');
}

$vehicle = null;
if ($role === 'driver') {
    $vehicle = [
        'brand' => trim((string) ($_POST['vehicle_brand'] ?? '')),
        'model' => trim((string) ($_POST['vehicle_model'] ?? '')),
        'plate_number' => strtoupper(trim((string) ($_POST['plate_number'] ?? ''))),
        'color' => trim((string) ($_POST['vehicle_color'] ?? '')),
    ];
    foreach ($vehicle as $value) {
        if ($value === '' || strlen($value) > 80) {
            simpan_pesan('error', 'Lengkapi data kendaraan driver dengan benar.');
            alihkan('?m=akun&a=daftar');
        }
    }
    if (strlen($vehicle['plate_number']) > 20 || strlen($vehicle['color']) > 40) {
        simpan_pesan('error', 'Nomor polisi atau warna kendaraan terlalu panjang.');
        alihkan('?m=akun&a=daftar');
    }
}

/** @var mysqli $db */
$db = require dirname(__DIR__, 3) . '/core/koneksi.php';
try {
    $userId = buat_pengguna(
        $db,
        $name,
        $email,
        password_hash($password, PASSWORD_DEFAULT),
        $role,
        $vehicle
    );
} catch (mysqli_sql_exception $error) {
    if ((int) $error->getCode() !== 1062) {
        throw $error;
    }
    simpan_pesan('error', 'Email atau nomor polisi tersebut sudah terdaftar.');
    alihkan('?m=akun&a=daftar');
}

session_regenerate_id(true);
$_SESSION['pengguna'] = [
    'id' => $userId,
    'nama' => $name,
    'email' => $email,
    'peran' => $role,
];
unset($_SESSION['csrf_token']);
arahkan_setelah_login();
