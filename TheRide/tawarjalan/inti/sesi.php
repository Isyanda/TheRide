<?php

declare(strict_types=1);

/** Memulai sesi sekali saja untuk seluruh request aplikasi. */
if (session_status() !== PHP_SESSION_ACTIVE) {
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
