<?php

declare(strict_types=1);

/** Format angka menjadi rupiah. */
function format_rupiah(int|float $nominal): string
{
    return 'Rp' . number_format($nominal, 0, ',', '.');
}

/** Escape teks sebelum ditampilkan ke HTML. */
function e(string|null $teks): string
{
    return htmlspecialchars($teks ?? '', ENT_QUOTES, 'UTF-8');
}

/** Redirect ke URL lokal lalu hentikan eksekusi. */
function alihkan(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function simpan_pesan(string $jenis, string $pesan): void
{
    $_SESSION['pesan_flash'] = ['jenis' => $jenis, 'isi' => $pesan];
}

function ambil_pesan(): array|null
{
    $pesan = $_SESSION['pesan_flash'] ?? null;
    unset($_SESSION['pesan_flash']);

    return is_array($pesan) ? $pesan : null;
}
