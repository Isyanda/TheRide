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
├── public/                         # Satu-satunya folder yang diakses browser
│   ├── index.php                   # Entry point aplikasi
│   ├── .htaccess
│   └── assets/
│       ├── css/                    # dasar.css, komponen.css, peta.css
│       ├── js/                     # peta_init.js, tawar.js, polling_lokasi.js
│       └── libraries/leaflet/      # Letakkan file distribusi Leaflet di sini
├── core/                           # Koneksi, routing, sesi, helper
├── modules/
│   ├── account/                    # Register, login, logout, profil
│   ├── driver/                     # Status driver dan kendaraan
│   ├── orders/                     # Pembuatan dan riwayat pesanan
│   ├── offers/                     # Penawaran dan pemilihan harga
│   └── map/                        # API lokasi/rute dan tampilan peta
├── presentation/                   # Layer HTTP: controller, request, response, dan view
├── application/                    # Layer use case dan orkestrasi proses bisnis
├── domain/                         # Layer inti bisnis: entitas, value object, dan kontrak
├── infrastructure/                 # Layer implementasi teknis: database dan layanan eksternal
├── templates/                      # Header, footer, navigasi bersama
├── config/                         # Konfigurasi aplikasi dan contoh DB
├── database/                       # Skema dan data awal SQL
├── docs/                           # Charter, ERD, mockup, wawancara
├── tests/                          # Skenario pengujian dan catatan bug
├── .gitignore
└── README.md
```

## Menjalankan aplikasi

1. Jalankan MySQL di Laragon/XAMPP dan buat database sesuai `nama_database` di `config/basisdata.php` (nilai contoh: `theride`).
2. Salin `config/basisdata.contoh.php` menjadi `config/basisdata.php`, lalu sesuaikan host, port, username, dan password MySQL lokal.
3. Import `database/skema.sql` ke database tersebut.
4. Dari folder proyek, jalankan `php -S 127.0.0.1:8000 -t public` lalu buka `http://127.0.0.1:8000`.
   Alternatifnya, arahkan document root Apache ke `public/`.

Daftar sebagai penumpang untuk membuat perjalanan. Daftar sebagai driver dengan data kendaraan, buka peta driver, lalu izinkan akses lokasi. Driver dapat menerima harga penumpang atau mengirim tawaran harga lain. Penumpang memilih tawaran; driver kemudian memulai dan menyelesaikan perjalanan.

Lokasi browser hanya tersedia pada HTTPS atau alamat loopback seperti `localhost` dan `127.0.0.1`. Peta menggunakan tile OpenStreetMap dan Leaflet dari internet.

Gunakan akun berbeda di browser biasa dan jendela privat (atau browser/perangkat berbeda) untuk menguji alur penumpang dan driver secara bersamaan.

## Arsitektur berlapis

Empat folder layer (`presentation/`, `application/`, `domain/`, dan
`infrastructure/`) telah disiapkan sebagai kerangka kosong. Migrasi fitur dari
`modules/` akan dilakukan bertahap setelah kontrak dan pembagian tanggung jawab
tiap layer disepakati.

Aturan dependensi yang dituju:

```text
public/ -> presentation/ -> application/ -> domain/
                            application/ -> infrastructure/
infrastructure/ -------------------------> domain/
```

- `presentation/` menangani HTTP dan tidak mengakses database secara langsung.
- `application/` mengoordinasikan use case tanpa bergantung pada detail HTTP.
- `domain/` tidak bergantung pada layer lain.
- `infrastructure/` mengimplementasikan kontrak domain dan menangani detail
  database atau layanan eksternal.

Untuk sementara, `modules/` dan `core/` tetap dipertahankan agar perilaku
aplikasi yang ada tidak berubah. File `.gitkeep` pada setiap layer menandai
folder scaffold sampai implementasi pertama dipindahkan.

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

File `config/basisdata.php` sengaja diabaikan oleh Git karena berisi kredensial lokal. Jangan pernah mengunggah password database, token, atau secret ke repository.
