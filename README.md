# SISTEM PARKIR SISWA

Aplikasi web sistem parkir sederhana dengan PHP Native dan MySQL.

## FITUR

### Admin
- Kelola User (tambah, hapus user)
- Kelola Tarif Parkir
- Kelola Kapasitas Parkir
- Lihat Data Kendaraan
- Laporan Pendapatan

### Petugas
- Input Kendaraan Masuk
- Proses Kendaraan Keluar & Pembayaran
- Cetak Struk Pembayaran

### Owner
- Lihat Laporan Pendapatan
- Filter Laporan Berdasarkan Tanggal

## INSTALASI

### 1. Requirements
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Apache/Nginx Web Server
- XAMPP/WAMP/LAMP (recommended untuk development)

### 2. Langkah Instalasi

#### A. Setup Database
1. Buka phpMyAdmin atau MySQL client
2. Import file `database.sql` atau jalankan query di dalamnya
3. Database `parkir_siswa` akan otomatis terbuat

#### B. Konfigurasi
1. Buka file `config.php`
2. Sesuaikan konfigurasi database jika perlu:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'parkir_siswa');
   ```

#### C. Deploy Aplikasi
1. Copy folder `sistem-parkir` ke folder `htdocs` (XAMPP) atau `www` (WAMP)
2. Akses aplikasi melalui browser: `http://localhost/sistem-parkir`

### 3. Login Default

**Admin:**
- Username: admin
- Password: admin123

**Petugas:**
- Username: petugas1
- Password: petugas123

**Owner:**
- Username: owner
- Password: owner123

## STRUKTUR FILE

```
sistem-parkir/
├── config.php              # Konfigurasi database & session
├── database.sql            # File SQL untuk database
├── index.php              # Halaman login
├── logout.php             # Logout handler
├── admin/                 # Folder halaman admin
│   ├── index.php         # Dashboard admin
│   ├── users.php         # Kelola user
│   ├── tarif.php         # Kelola tarif
│   ├── kapasitas.php     # Kelola kapasitas
│   ├── kendaraan.php     # Data kendaraan
│   └── laporan.php       # Laporan pendapatan
├── petugas/              # Folder halaman petugas
│   ├── index.php         # Dashboard petugas
│   ├── masuk.php         # Kendaraan masuk
│   ├── keluar.php        # Kendaraan keluar & bayar
│   └── struk.php         # Cetak struk
└── owner/                # Folder halaman owner
    └── index.php         # Dashboard & laporan owner
```

## FLOWCHART SISTEM

Sistem ini mengikuti flowchart yang ada di file Excel:

1. **Login** - Autentikasi user berdasarkan role
2. **Kendaraan Masuk** - Input plat & jenis, update kapasitas
3. **Kendaraan Keluar** - Hitung durasi & biaya, proses pembayaran
4. **Cetak Struk** - Preview dan print struk pembayaran
5. **Laporan Owner** - Filter tanggal dan tampilkan total pendapatan

## CATATAN TEKNIS

- Perhitungan durasi parkir menggunakan pembulatan ke atas (ceiling)
- Contoh: parkir 1 jam 15 menit = 2 jam
- Status kendaraan: 'parkir' atau 'keluar'
- Password disimpan dengan MD5 (untuk production gunakan bcrypt/password_hash)

## TROUBLESHOOTING

**Error koneksi database:**
- Pastikan MySQL service sudah running
- Cek konfigurasi di `config.php`
- Pastikan database `parkir_siswa` sudah dibuat

**Tidak bisa login:**
- Pastikan data user sudah di-insert dari `database.sql`
- Cek username dan password

**Kapasitas tidak update:**
- Cek trigger update di halaman kendaraan masuk/keluar
- Pastikan query update kapasitas berjalan

## PENGEMBANGAN LEBIH LANJUT

Fitur yang bisa ditambahkan:
- Export laporan ke PDF/Excel
- Grafik statistik pendapatan
- Notifikasi kapasitas penuh
- History login user
- Foto kendaraan
- Barcode/QR untuk tiket parkir
- API untuk mobile app

## LISENSI

Free to use untuk keperluan pembelajaran dan ujian.
