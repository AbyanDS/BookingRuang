# Sistem Booking Ruangan

Sistem booking ruangan berbasis web menggunakan PHP Native dan MySQL.

## Fitur

### User
- Register dan Login
- Melihat daftar ruangan yang tersedia
- Lihat jadwal ruangan (jadwal rutin)
- **Search & Filter ruangan** (nama, kapasitas, lokasi) 🔍 BARU
- Booking ruangan dengan memilih tanggal dan waktu
- **Modal detail ruangan dengan foto** 📷 BARU
- Melihat riwayat booking
- **Lihat history peminjaman (timeline aktivitas)**
- Cancel booking (status pending)
- Filter booking berdasarkan status

### Petugas
- Dashboard dengan statistik
- **Kelola data ruangan (CRUD)** ✨ BARU
- Upload foto ruangan
- Kelola booking (Approve/Reject)
- **Kelola jadwal ruangan (CRUD)**
- **Filter jadwal berdasarkan hari dan ruangan**
- **Generate Laporan (Excel & PDF)** 📊 BARU
- **Lihat history peminjaman semua user**
- **Filter history berdasarkan user, ruangan, dan aktivitas**
- ❌ TIDAK bisa booking ruangan (hanya user yang bisa)
- ❌ TIDAK bisa mengelola user

### Admin
- Dashboard dengan statistik
- **Kelola data ruangan (CRUD)**
- Upload foto ruangan
- Kelola booking (Approve/Reject)
- **Kelola jadwal ruangan (CRUD)**
- **Filter jadwal berdasarkan hari dan ruangan**
- **Generate Laporan (Excel & PDF)** 📊 BARU
- **Lihat history peminjaman semua user**
- **Filter history berdasarkan user, ruangan, dan aktivitas**
- **Kelola user (CRUD)**
- ❌ TIDAK bisa booking ruangan (hanya user yang bisa)

## Teknologi yang Digunakan
- PHP Native (tanpa framework)
- MySQL Database
- HTML5, CSS3
- JavaScript (vanilla)

## Instalasi

### Prasyarat
- XAMPP (Apache & MySQL)
- Browser (Chrome, Firefox, dll)

### Langkah Instalasi

1. **Clone atau copy project ke folder htdocs XAMPP**
   ```
   C:\xampp2\htdocs\PlsworkUKK
   ```

2. **Buat database**
   - Buka phpMyAdmin: `http://localhost/phpmyadmin`
   - Import file `database.sql` atau jalankan query SQL di dalam file tersebut

3. **Konfigurasi database** (jika diperlukan)
   - Buka file `config/database.php`
   - Sesuaikan konfigurasi:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'booking_ruangan');
     ```

4. **Jalankan aplikasi**
   - Buka browser dan akses: `http://localhost/PlsworkUKK`

## Akun Default

### Admin
- Username: `admin`
- Password: `password`

### Petugas
- Username: `petugas`
- Password: `password`

> ⚠️ **PENTING:** Segera ubah password default setelah login pertama kali!

## 📊 Fitur Laporan (BARU)

### Export Excel
- Filter berdasarkan bulan, ruangan, dan status booking
- Statistik lengkap dalam satu file
- Format `.xls` yang kompatibel dengan Microsoft Excel
- Download otomatis

### Print/PDF
- Tampilan profesional siap cetak
- Header dengan informasi periode dan pencetak
- Statistik dan detail booking lengkap
- Tanda tangan digital
- Fungsi auto-print

### Filter Laporan
- **Bulan**: Pilih bulan dan tahun tertentu
- **Ruangan**: Semua ruangan atau ruangan tertentu
- **Status**: All, Pending, Approved, Rejected, Completed, Cancelled

### Akses Laporan
- URL: `admin/laporan.php`
- Hanya bisa diakses oleh **Admin** dan **Petugas**
- Menu navigasi: **Laporan** 📊

## 🔍 Fitur Search & Filter (BARU)

### Halaman Home
- **Search Bar**: Cari ruangan berdasarkan nama, fasilitas, atau lokasi
- **Filter Kapasitas**: 10+, 20+, 30+, 50+, 100+ orang
- **Filter Lokasi**: Filter berdasarkan lokasi ruangan (dinamis dari database)
- **Auto Scroll**: Otomatis scroll ke hasil pencarian
- **Result Counter**: Menampilkan jumlah ruangan yang ditemukan

### Halaman Booking User
- **Modal Detail Ruangan** 📷
  * Tombol "Lihat Detail Ruangan" muncul setelah memilih ruangan
  * Popup modal menampilkan:
    - Foto ruangan (full size)
    - Nama ruangan
    - Lokasi lengkap
    - Kapasitas
    - Fasilitas yang tersedia
    - Status ketersediaan
  * Design responsif dan animasi smooth
  * Klik di luar modal untuk menutup
  * Tombol close dengan efek hover

### User
Daftar akun baru melalui halaman register

## Struktur Database

### Tabel Users
- id, username, password, nama_lengkap, email, role, created_at

### Tabel Ruangan
- id, nama_ruangan, kapasitas, fasilitas, lokasi, status, foto, created_at

### Tabel Booking
- id, user_id, ruangan_id, tanggal_booking, waktu_mulai, waktu_selesai, keperluan, status, keterangan, created_at, updated_at

### Tabel Jadwal Ruangan (Baru)
- id, ruangan_id, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, keterangan, status, created_at, updated_at

### Tabel History Peminjaman (Baru)
- id, booking_id, user_id, ruangan_id, action, status_lama, status_baru, keterangan, dilakukan_oleh, created_at

## Status Booking

- **Pending**: Menunggu approval admin
- **Approved**: Disetujui oleh admin
- **Rejected**: Ditolak oleh admin
- **Completed**: Booking telah selesai
- **Cancelled**: Dibatalkan oleh user

## Struktur Folder

```
PlsworkUKK/
├── admin/                  # Halaman admin & petugas
│   ├── index.php          # Dashboard admin
│   ├── ruangan.php        # Kelola ruangan
│   ├── ruangan_add.php    # Tambah ruangan
│   ├── ruangan_edit.php   # Edit ruangan
│   ├── booking.php        # Kelola booking
│   ├── booking_detail.php # Detail & approval booking
│   ├── jadwal.php         # Kelola jadwal
│   ├── jadwal_add.php     # Tambah jadwal
│   ├── jadwal_edit.php    # Edit jadwal
│   ├── laporan.php        # Generate laporan (BARU) 📊
│   ├── laporan_export.php # Export Excel (BARU) 📥
│   ├── laporan_print.php  # Print PDF (BARU) 🖨️
│   ├── history.php        # History semua user
│   └── users.php          # Daftar user (admin only)
├── assets/
│   └── css/
│       └── style.css      # Stylesheet utama
├── config/
│   ├── database.php       # Konfigurasi database
│   └── functions.php      # Fungsi helper
├── includes/
│   ├── navbar.php         # Navigasi bar
│   └── footer.php         # Footer
├── petugas/                # Dashboard petugas
│   └── index.php          # Dashboard khusus petugas
├── user/                   # Halaman user
│   ├── index.php          # Dashboard user
│   ├── booking.php        # Form booking
│   ├── booking_detail.php # Detail booking
│   ├── riwayat.php        # Riwayat booking
│   └── history.php        # History aktivitas
├── uploads/                # Folder upload foto
├── database.sql           # Database schema
├── index.php              # Halaman utama
├── login.php              # Halaman login
├── register.php           # Halaman register
├── logout.php             # Logout
├── jadwal_view.php        # Lihat jadwal
└── README.md              # Dokumentasi
```

## Fitur Keamanan

- Password di-hash menggunakan `password_hash()`
- Input sanitization untuk mencegah SQL Injection
- Session management untuk autentikasi
- Role-based access control (Admin, Petugas & User)

## Catatan

- Pastikan folder `uploads/` memiliki permission write
- Default password admin dan petugas adalah `password`, segera ganti setelah login pertama kali
- Sistem menggunakan validasi untuk mencegah double booking pada ruangan yang sama di waktu yang sama
- Jadwal ruangan dapat membantu user melihat kegiatan rutin sebelum melakukan booking
- **History peminjaman mencatat semua aktivitas booking (create, approve, reject, cancel, complete)**
- **Timeline history memudahkan tracking perubahan status booking**
- **Admin dan Petugas TIDAK BISA booking ruangan, hanya bisa approve/reject**
- **Petugas sekarang bisa mengelola ruangan (sama seperti admin)**
- **Fitur laporan tersedia untuk Admin dan Petugas dengan filter lengkap**

## 🎯 Sistem Role & Permission

| Fitur | User | Petugas | Admin |
|-------|------|---------|-------|
| Booking Ruangan | ✅ | ❌ | ❌ |
| Lihat Riwayat Sendiri | ✅ | ❌ | ❌ |
| Approve/Reject Booking | ❌ | ✅ | ✅ |
| Kelola Ruangan | ❌ | ✅ | ✅ |
| Kelola Jadwal | ❌ | ✅ | ✅ |
| **Generate Laporan** | ❌ | ✅ | ✅ |
| Lihat History Semua | ❌ | ✅ | ✅ |
| Kelola User | ❌ | ❌ | ✅ |

## Screenshot

### Halaman Utama
Menampilkan daftar ruangan yang tersedia

### Dashboard User
- Statistik booking
- Riwayat booking terbaru

### Dashboard Admin
- Statistik sistem
- Kelola ruangan dan booking
- Approval booking

## 🚀 Deploy ke Railway

Aplikasi ini sudah siap untuk di-deploy ke Railway! 

### Quick Start
Lihat file `QUICKSTART_RAILWAY.md` untuk panduan cepat (5 menit).

### Dokumentasi Lengkap
Lihat file `RAILWAY_DEPLOYMENT.md` untuk panduan detail deployment ke Railway.

### File Pendukung
- ✅ `.htaccess` - Konfigurasi Apache
- ✅ `nixpacks.toml` - Build configuration
- ✅ `railway.json` - Railway settings
- ✅ `.gitignore` - Ignore unnecessary files
- ✅ `config/database.php` - Support environment variables
- ✅ `insert_default_data.sql` - Data default untuk production
- ✅ `test_db_connection.php` - Test database connection

### Fitur Railway Ready
- Database configuration menggunakan environment variables
- Support MySQL Railway
- Apache + PHP 8.2
- Auto-scaling support

## Support

Untuk pertanyaan atau masalah, silakan hubungi administrator sistem.

---
&copy; 2025 Sistem Booking Ruangan
