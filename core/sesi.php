<?php

declare(strict_types=1);

/** Memulai sesi sekali saja untuk seluruh request aplikasi. */
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Periksa status login pengguna. */
function pengguna_sudah_masuk(): bool
{
    return isset($_SESSION['pengguna']['id']);
}

/** Periksa apakah pengguna memiliki peran tertentu. */
function pengguna_memiliki_peran(string $peran): bool
{
    return ($_SESSION['pengguna']['peran'] ?? null) === $peran;
}

function arahkan_setelah_login(): never
{
    $tujuan = pengguna_memiliki_peran('driver')
        ? '?m=driver&a=beranda'
        : '?m=pesanan&a=buat';
    header('Location: ' . $tujuan);
    exit;
}
