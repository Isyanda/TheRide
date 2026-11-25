<?php

declare(strict_types=1);

/** Membuat koneksi MySQLi dari konfigurasi lokal. */

$configPath = dirname(__DIR__) . '/config/basisdata.php';

if (!is_file($configPath)) {
    throw new RuntimeException(
        'Konfigurasi database belum ada. Salin config/basisdata.contoh.php menjadi config/basisdata.php.'
    );
}

$dbConfig = require $configPath;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$koneksi = new mysqli(
    $dbConfig['host'],
    $dbConfig['username'],
    $dbConfig['password'],
    $dbConfig['nama_database'],
    (int) ($dbConfig['port'] ?? 3306)
);

$koneksi->set_charset($dbConfig['charset'] ?? 'utf8mb4');

return $koneksi;
