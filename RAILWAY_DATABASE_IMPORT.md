# 🚀 Cara Import Database ke Railway MySQL

## File yang Harus Digunakan

✅ **`database_railway.sql`** - Versi Railway (RECOMMENDED)
- ❌ Tidak ada `CREATE DATABASE` dan `USE`
- ✅ Langsung create tables
- ✅ Include data default (admin, petugas, ruangan)
- ✅ Optimized untuk Railway MySQL
- ✅ UTF8MB4 charset
- ✅ Indexes untuk performa

📄 **`database.sql`** - Versi original (untuk local XAMPP)
- ⚠️ Ada `CREATE DATABASE` dan `USE` (tidak kompatibel Railway)

---

## 📋 Langkah Import (Railway Dashboard)

### 1️⃣ Buka Railway Dashboard
- Login ke https://railway.app
- Pilih project Anda
- Klik **MySQL service**

### 2️⃣ Buka Query Editor
- Klik tab **"Data"**
- Klik tombol **"Query"**

### 3️⃣ Import database_railway.sql
1. Buka file `database_railway.sql` di editor
2. **Copy semua isinya** (Ctrl+A, Ctrl+C)
3. **Paste** ke Railway Query Editor
4. Klik **"Run"** atau tekan **Ctrl+Enter**
5. Tunggu hingga selesai (30-60 detik)

### 4️⃣ Verifikasi Import Berhasil

Jalankan query ini:

```sql
-- Cek tables
SHOW TABLES;

-- Cek users
SELECT username, nama_lengkap, role FROM users;

-- Cek ruangan
SELECT nama_ruangan, kapasitas, lokasi FROM ruangan LIMIT 10;

-- Cek jadwal
SELECT * FROM jadwal_ruangan LIMIT 5;
```

**Expected Results:**
```
✅ 6 tables: users, ruangan, booking, jadwal_ruangan, history_peminjaman, password_reset_requests
✅ 2 users: admin, petugas
✅ 28 ruangan: Lab IPA, Lab PB, Lab 1-22, Ruang 1-3, Lapangan, HALL
✅ 10 jadwal contoh
```

---

## 🎯 Login Credentials

Setelah import berhasil, gunakan:

```
Admin:
Username: admin
Password: password

Petugas:
Username: petugas
Password: password
```

⚠️ **PENTING:** Ubah password setelah login pertama!

---

## 🔄 Jika Perlu Import Ulang

Jika ada error atau ingin mulai dari awal:

```sql
-- HATI-HATI! Ini akan menghapus semua data!
DROP TABLE IF EXISTS history_peminjaman;
DROP TABLE IF EXISTS password_reset_requests;
DROP TABLE IF EXISTS jadwal_ruangan;
DROP TABLE IF EXISTS booking;
DROP TABLE IF EXISTS ruangan;
DROP TABLE IF EXISTS users;
```

Setelah itu, import `database_railway.sql` lagi.

---

## ✅ Perbedaan Utama

### ❌ database.sql (Original - JANGAN DIPAKAI DI RAILWAY)
```sql
CREATE DATABASE IF NOT EXISTS booking_ruangan;
USE booking_ruangan;  ❌ Error di Railway!
CREATE TABLE...
```

### ✅ database_railway.sql (Railway Version)
```sql
-- Langsung create tables, no CREATE DATABASE
CREATE TABLE IF NOT EXISTS users...
-- Dengan charset dan indexes
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📊 Fitur Tambahan di database_railway.sql

1. **UTF8MB4 Charset** - Support emoji dan karakter internasional
2. **Indexes** - Performa query lebih cepat
3. **InnoDB Engine** - Mendukung foreign keys dan transactions
4. **ON DUPLICATE KEY UPDATE** - Bisa dijalankan berulang tanpa error
5. **Kolom lokasi** di tabel ruangan
6. **Data lengkap** - 28 ruangan dengan jadwal

---

## 🆘 Troubleshooting

**Error: Table already exists**
- Gunakan `DROP TABLE` seperti di atas, atau
- File sudah include `IF NOT EXISTS` jadi aman

**Error: Foreign key constraint fails**
- Import dengan urutan benar (sudah diatur di file)
- Drop tables dengan urutan yang benar

**Data tidak muncul**
- Check query: `SELECT * FROM users;`
- Pastikan import selesai tanpa error
- Lihat di Railway → MySQL → Data → Tables

---

**File siap digunakan! Import sekarang ke Railway!** 🚀
