<?php

declare(strict_types=1);

/** Membuat koneksi MySQLi dari konfigurasi lokal. */

$configPath = dirname(__DIR__) . '/konfigurasi/basisdata.php';

if (!is_file($configPath)) {
    throw new RuntimeException(
        'Konfigurasi database belum ada. Salin konfigurasi/basisdata.contoh.php menjadi konfigurasi/basisdata.php.'
    );
}

$dbConfig = require $configPath;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$koneksi = new mysqli(
    $dbConfig['host'],
    $dbConfig['username'],
    $dbConfig['password'],
    $dbConfig['nama_database']
);

$koneksi->set_charset($dbConfig['charset'] ?? 'utf8mb4');

return $koneksi;
