# TheRide

Aplikasi web ride-hailing roda 4 dengan konsep tawar-menawar tarif, terinspirasi dari inDrive.

## Teknologi

- PHP Native (tanpa framework)
- MySQL / MySQLi
- HTML, CSS, JavaScript
- Leaflet.js dan OpenStreetMap
- Git dan GitHub

## Struktur proyek

```text
tawarjalan/
├── publik/                         # Satu-satunya folder yang diakses browser
│   ├── index.php                   # Entry point aplikasi
│   ├── .htaccess
│   └── aset/
│       ├── css/                    # dasar.css, komponen.css, peta.css
│       ├── js/                     # peta_init.js, tawar.js, polling_lokasi.js
│       ├── gambar/
│       └── pustaka/leaflet/        # Letakkan file distribusi Leaflet di sini
├── inti/                           # Koneksi, routing, sesi, helper
├── modul/
│   ├── akun/                       # Register, login, logout, profil
│   ├── driver/                     # Status driver dan kendaraan
│   ├── pesanan/                    # Pembuatan dan riwayat pesanan
│   ├── tawaran/                    # Penawaran dan pemilihan harga
│   └── peta/                       # API lokasi/rute dan tampilan peta
├── templat/                        # Header, footer, navigasi bersama
├── konfigurasi/                    # Konfigurasi aplikasi dan contoh DB
├── basisdata/                      # Skema dan data awal SQL
├── dokumen/                        # Charter, ERD, mockup, wawancara
├── uji/                            # Skenario pengujian dan catatan bug
├── .gitignore
└── README.md
```

## Persiapan lokal

1. Install XAMPP atau Laragon.
2. Clone repository ke folder kerja lokal.
3. Salin `konfigurasi/basisdata.contoh.php` menjadi `konfigurasi/basisdata.php`.
4. Isi nama database, username, dan password MySQL di `konfigurasi/basisdata.php`.
5. Buat database MySQL, lalu import `basisdata/skema.sql` dan `basisdata/data_awal.sql` jika file tersebut sudah diisi.
6. Atur document root Apache ke folder `publik/`, bukan root proyek. Dengan begitu `inti/`, `modul/`, dan file konfigurasi tidak bisa diakses langsung melalui URL.

> Kerangka ini menyiapkan struktur folder dan file awal. Fitur aplikasi, skema database, dan tampilan masih perlu diimplementasikan oleh anggota sesuai pembagian tugas.

## Aturan kolaborasi GitHub

- `main`: versi stabil untuk demo/presentasi.
- `develop`: integrasi pekerjaan seluruh anggota.
- Setiap anggota bekerja pada branch fitur masing-masing, misalnya `fitur/akun-daftar-andi`.
- Gunakan format commit `modul: keterangan singkat`, misalnya `akun: tambah validasi form daftar`.
- Gabungkan pekerjaan ke `develop` melalui Pull Request dan minta minimal satu anggota melakukan review.

### Perintah Git awal

```bash
# Setelah repository GitHub dibuat dan URL-nya tersedia:
git clone https://github.com/USERNAME/tawarjalan.git
cd tawarjalan

git switch -c develop
git push -u origin develop
```

### Mulai mengerjakan fitur

```bash
git switch develop
git pull origin develop
git switch -c fitur/nama-modul-nama-anggota

# Setelah mengubah file:
git add .
git commit -m "modul: deskripsi perubahan"
git push -u origin fitur/nama-modul-nama-anggota
```

Setelah push, buat Pull Request dari branch fitur menuju `develop` di GitHub.

## Keamanan konfigurasi

File `konfigurasi/basisdata.php` sengaja diabaikan oleh Git karena berisi kredensial lokal. Jangan pernah mengunggah password database, token, atau secret ke repository.
