<?php

declare(strict_types=1);

function request_method(string $method): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === strtoupper($method);
}

function post_string(string $key, int $maxLength = 500): string
{
    $value = trim((string) ($_POST[$key] ?? ''));
    if ($value === '' || strlen($value) > $maxLength) {
        json_response(['error' => "Field {$key} tidak valid."], 422);
    }

    return $value;
}

function post_float(string $key, float $minimum, float $maximum): float
{
    $value = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_FLOAT);
    if ($value === false || $value < $minimum || $value > $maximum) {
        json_response(['error' => "Field {$key} tidak valid."], 422);
    }

    return (float) $value;
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_THROW_ON_ERROR);
    exit;
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_is_valid(): bool
{
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function require_csrf(): void
{
    if (!csrf_is_valid()) {
        json_response(['error' => 'Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.'], 419);
    }
}

function require_api_user(string|null $role = null): int
{
    $id = filter_var($_SESSION['pengguna']['id'] ?? null, FILTER_VALIDATE_INT);
    if ($id === false || $id < 1) {
        json_response(['error' => 'Anda harus login terlebih dahulu.'], 401);
    }

    if ($role !== null && ($_SESSION['pengguna']['peran'] ?? null) !== $role) {
        json_response(['error' => 'Anda tidak memiliki akses ke endpoint ini.'], 403);
    }

    return (int) $id;
}

function require_page_user(string|null $role = null): int
{
    $id = filter_var($_SESSION['pengguna']['id'] ?? null, FILTER_VALIDATE_INT);
    if ($id === false || $id < 1) {
        header('Location: ?m=akun&a=masuk');
        exit;
    }

    if ($role !== null && ($_SESSION['pengguna']['peran'] ?? null) !== $role) {
        arahkan_setelah_login();
    }

    return (int) $id;
}
