<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/core/sesi.php';
require_once dirname(__DIR__, 3) . '/core/http.php';

if (!request_method('POST')) {
    http_response_code(405);
    exit('Method POST diperlukan.');
}

if (!csrf_is_valid()) {
    http_response_code(419);
    exit('Sesi formulir kedaluwarsa. Muat ulang halaman.');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}
session_destroy();
header('Location: ?m=akun&a=masuk');
exit;
