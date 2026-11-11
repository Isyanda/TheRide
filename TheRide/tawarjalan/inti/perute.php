<?php

declare(strict_types=1);

/**
 * Router berbasis daftar izin. Parameter URL tidak pernah dipakai sebagai
 * path file secara langsung.
 */
function jalankan_rute(): void
{
    $rute = [
        'akun' => [
            'masuk' => 'modul/akun/tampilan/hal_masuk.php',
            'daftar' => 'modul/akun/tampilan/hal_daftar.php',
            'profil' => 'modul/akun/tampilan/hal_profil.php',
            'aksi_masuk' => 'modul/akun/aksi/masuk.php',
            'aksi_daftar' => 'modul/akun/aksi/daftar.php',
            'aksi_keluar' => 'modul/akun/aksi/keluar.php',
            'aksi_ubah_profil' => 'modul/akun/aksi/ubah_profil.php',
        ],
        'driver' => [
            'beranda' => 'modul/driver/tampilan/hal_beranda_driver.php',
            'aksi_status' => 'modul/driver/aksi/atur_status.php',
            'aksi_kendaraan' => 'modul/driver/aksi/simpan_kendaraan.php',
        ],
        'pesanan' => [
            'buat' => 'modul/pesanan/tampilan/hal_pesan.php',
            'riwayat' => 'modul/pesanan/tampilan/hal_riwayat.php',
            'aksi_buat' => 'modul/pesanan/aksi/buat_pesanan.php',
            'aksi_batalkan' => 'modul/pesanan/aksi/batalkan_pesanan.php',
            'aksi_selesaikan' => 'modul/pesanan/aksi/selesaikan_pesanan.php',
        ],
        'tawaran' => [
            'masuk' => 'modul/tawaran/tampilan/hal_tawaran_masuk.php',
            'aksi_kirim' => 'modul/tawaran/aksi/kirim_tawaran.php',
            'aksi_pilih' => 'modul/tawaran/aksi/pilih_tawaran.php',
            'aksi_terima' => 'modul/tawaran/aksi/terima_langsung.php',
            'api_daftar' => 'modul/tawaran/api/daftar_tawaran.php',
        ],
        'peta' => [
            'driver' => 'modul/peta/tampilan/hal_peta_driver.php',
            'bagian' => 'modul/peta/tampilan/bagian_peta.php',
            'api_simpan_lokasi' => 'modul/peta/api/simpan_lokasi.php',
            'api_lokasi_driver' => 'modul/peta/api/lokasi_driver.php',
            'api_pesanan_sekitar' => 'modul/peta/api/pesanan_sekitar.php',
            'api_hitung_rute' => 'modul/peta/api/hitung_rute.php',
        ],
    ];

    $modul = (string) ($_GET['m'] ?? 'akun');
    $aksi = (string) ($_GET['a'] ?? 'masuk');
    $fileRelatif = $rute[$modul][$aksi] ?? null;

    if ($fileRelatif === null) {
        http_response_code(404);
        echo 'Halaman tidak ditemukan.';
        return;
    }

    $fileAbsolut = dirname(__DIR__) . '/' . $fileRelatif;

    if (!is_file($fileAbsolut)) {
        http_response_code(501);
        echo 'Modul belum diimplementasikan: ' . htmlspecialchars($aksi, ENT_QUOTES, 'UTF-8');
        return;
    }

    require $fileAbsolut;
}
