<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/core/sesi.php';
require_once dirname(__DIR__) . '/core/bantu.php';
require_once dirname(__DIR__) . '/core/http.php';
require_once dirname(__DIR__) . '/core/perute.php';

try {
    jalankan_rute();
} catch (mysqli_sql_exception $error) {
    error_log('Database request failed: ' . $error->getMessage());
    http_response_code(503);
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'Database belum dapat diakses. Periksa kredensial dan nama database di config/basisdata.php.',
        ]);
    } else {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html lang="id"><meta charset="utf-8"><title>Database belum siap</title>';
        echo '<body><main style="max-width:640px;margin:10vh auto;font:16px system-ui">';
        echo '<h1>Database belum dapat diakses</h1>';
        echo '<p>Periksa host, port, nama database, username, dan password di ';
        echo '<code>config/basisdata.php</code>, lalu muat ulang halaman.</p></main></body></html>';
    }
}
