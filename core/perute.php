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
            'masuk' => 'modules/account/views/hal_masuk.php',
            'daftar' => 'modules/account/views/hal_daftar.php',
            'profil' => 'modules/account/views/hal_profil.php',
            'aksi_masuk' => 'modules/account/actions/masuk.php',
            'aksi_daftar' => 'modules/account/actions/daftar.php',
            'aksi_keluar' => 'modules/account/actions/keluar.php',
            'aksi_ubah_profil' => 'modules/account/actions/ubah_profil.php',
        ],
        'driver' => [
            'beranda' => 'modules/driver/views/hal_beranda_driver.php',
            'aksi_status' => 'modules/driver/actions/atur_status.php',
            'aksi_kendaraan' => 'modules/driver/actions/simpan_kendaraan.php',
        ],
        'pesanan' => [
            'buat' => 'modules/orders/views/hal_pesan.php',
            'riwayat' => 'modules/orders/views/hal_riwayat.php',
            'aksi_buat' => 'modules/orders/actions/buat_pesanan.php',
            'aksi_batalkan' => 'modules/orders/actions/batalkan_pesanan.php',
            'aksi_selesaikan' => 'modules/orders/actions/selesaikan_pesanan.php',
            'aksi_mulai' => 'modules/orders/actions/mulai_pesanan.php',
            'api_status' => 'modules/orders/api/status_pesanan.php',
        ],
        'tawaran' => [
            'masuk' => 'modules/offers/views/hal_tawaran_masuk.php',
            'aksi_kirim' => 'modules/offers/actions/kirim_tawaran.php',
            'aksi_pilih' => 'modules/offers/actions/pilih_tawaran.php',
            'aksi_terima' => 'modules/offers/actions/terima_langsung.php',
            'api_daftar' => 'modules/offers/api/daftar_tawaran.php',
        ],
        'peta' => [
            'driver' => 'modules/map/views/hal_peta_driver.php',
            'bagian' => 'modules/map/views/bagian_peta.php',
            'api_simpan_lokasi' => 'modules/map/api/simpan_lokasi.php',
            'api_lokasi_driver' => 'modules/map/api/lokasi_driver.php',
            'api_pesanan_sekitar' => 'modules/map/api/pesanan_sekitar.php',
            'api_hitung_rute' => 'modules/map/api/hitung_rute.php',
        ],
    ];

    if (!isset($_GET['m']) && pengguna_sudah_masuk()) {
        arahkan_setelah_login();
    }

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
