# 🚀 Panduan Deploy Sistem Booking Ruangan ke Railway

## 📋 Persiapan Sebelum Deploy

### 1. Persyaratan
- Akun Railway (daftar di [railway.app](https://railway.app))
- Akun GitHub (untuk repository)
- Project sudah berjalan dengan baik di local

### 2. Struktur yang Dibutuhkan
Project ini adalah aplikasi PHP Native yang akan di-deploy ke Railway menggunakan:
- **Railway PHP Server** (Apache + PHP)
- **Railway MySQL Database**

---

## 📁 Langkah 1: Persiapan File Konfigurasi

### 1.1 Buat File `.htaccess`
Buat file `.htaccess` di root project untuk konfigurasi Apache:

```apache
# Enable PHP
AddType application/x-httpd-php .php

# Set default index
DirectoryIndex index.php index.html

# Error handling
php_flag display_errors Off
php_flag log_errors On

# Security
Options -Indexes

# Rewrite rules (opsional)
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
</IfModule>

# Protect config files
<FilesMatch "^(database\.php|functions\.php)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### 1.2 Update File `config/database.php`
Ubah konfigurasi database untuk mendukung environment variables:

```php
<?php
// Set timezone ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi Database dengan support environment variables (untuk Railway)
define('DB_HOST', getenv('MYSQL_HOST') ?: 'localhost');
define('DB_USER', getenv('MYSQL_USER') ?: 'root');
define('DB_PASS', getenv('MYSQL_PASSWORD') ?: '');
define('DB_NAME', getenv('MYSQL_DATABASE') ?: 'booking_ruangan');
define('DB_PORT', getenv('MYSQL_PORT') ?: '3306');

// Koneksi ke database dengan port
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Cek koneksi
if (!$conn) {
    error_log("Koneksi database gagal: " . mysqli_connect_error());
    die("Koneksi database gagal. Silakan hubungi administrator.");
}

// Set charset ke utf8
mysqli_set_charset($conn, "utf8");
?>
```

### 1.3 Buat File `nixpacks.toml`
File ini memberitahu Railway cara build dan run aplikasi PHP:

```toml
[phases.setup]
nixPkgs = ['php82', 'php82Extensions.mysqli', 'php82Extensions.pdo', 'php82Extensions.pdo_mysql', 'apache']

[phases.install]
cmds = ['echo "Installing dependencies..."']

[start]
cmd = 'apachectl -D FOREGROUND'
```

### 1.4 Buat File `railway.json`
Konfigurasi tambahan untuk Railway:

```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "NIXPACKS"
  },
  "deploy": {
    "numReplicas": 1,
    "sleepApplication": false,
    "restartPolicyType": "ON_FAILURE",
    "restartPolicyMaxRetries": 10
  }
}
```

### 1.5 Buat File `.gitignore`
Untuk tidak mengupload file yang tidak perlu:

```
# Local config
config/database.php.local

# Uploads
uploads/*.jpg
uploads/*.jpeg
uploads/*.png
uploads/*.gif
uploads/*.pdf
uploads/profile/*
!uploads/README.txt
!uploads/.gitkeep

# Logs
*.log
uploads/cron_log.txt

# OS files
.DS_Store
Thumbs.db

# IDE
.vscode/
.idea/
*.swp
*.swo

# Temporary files
*.tmp
*.bak
```

### 1.6 Buat File `uploads/.gitkeep`
Agar folder uploads tetap ada di Git:

```
# Keep this directory in git
```

---

## 🔧 Langkah 2: Push ke GitHub

### 2.1 Inisialisasi Git Repository
Buka terminal di folder project dan jalankan:

```bash
# Inisialisasi git
git init

# Tambahkan semua file
git add .

# Commit pertama
git commit -m "Initial commit - Sistem Booking Ruangan"
```

### 2.2 Buat Repository di GitHub
1. Buka [github.com](https://github.com)
2. Klik tombol **New Repository**
3. Beri nama: `booking-ruangan`
4. **Jangan** centang "Initialize with README"
5. Klik **Create Repository**

### 2.3 Push ke GitHub
```bash
# Tambahkan remote repository
git remote add origin https://github.com/USERNAME/booking-ruangan.git

# Ganti main jika perlu
git branch -M main

# Push ke GitHub
git push -u origin main
```

---

## ☁️ Langkah 3: Deploy ke Railway

### 3.1 Login ke Railway
1. Buka [railway.app](https://railway.app)
2. Login dengan akun GitHub
3. Klik **New Project**

### 3.2 Buat Database MySQL
1. Pilih **Provision MySQL**
2. Railway akan otomatis membuat MySQL database
3. **Tunggu hingga deployment selesai** (status hijau)

### 3.3 Deploy Aplikasi PHP
1. Klik **New** → **GitHub Repo**
2. Pilih repository `booking-ruangan`
3. Railway akan otomatis detect PHP dan mulai build

### 3.4 Link Database ke Aplikasi
1. Pada project Railway, klik aplikasi PHP Anda
2. Buka tab **Variables**
3. Klik **Add Reference** → Pilih MySQL database
4. Railway akan otomatis menambahkan environment variables:
   - `MYSQL_HOST`
   - `MYSQL_USER`
   - `MYSQL_PASSWORD`
   - `MYSQL_DATABASE`
   - `MYSQL_PORT`
   - `DATABASE_URL`

### 3.5 Setup Database Schema
1. Klik pada MySQL database
2. Buka tab **Data**
3. Klik **Query**
4. Copy paste isi file `database.sql`
5. Klik **Run** untuk execute query
6. Atau gunakan MySQL client eksternal dengan credentials dari Railway

---

## 🔐 Langkah 4: Konfigurasi Domain & Environment

### 4.1 Generate Domain
1. Klik aplikasi PHP Anda di Railway
2. Buka tab **Settings**
3. Scroll ke **Domains**
4. Klik **Generate Domain**
5. Railway akan memberikan domain seperti: `https://booking-ruangan-production.up.railway.app`

### 4.2 Custom Domain (Opsional)
Jika punya domain sendiri:
1. Klik **Custom Domain**
2. Masukkan domain Anda (contoh: `booking.domain.com`)
3. Tambahkan CNAME record di DNS provider:
   - Type: `CNAME`
   - Name: `booking` (atau sesuai subdomain)
   - Value: domain Railway yang di-generate
   - TTL: `3600` (atau default)

### 4.3 Tambahan Environment Variables (Opsional)
Jika perlu konfigurasi tambahan:
1. Tab **Variables**
2. Klik **New Variable**
3. Tambahkan:
   - `PHP_TIMEZONE=Asia/Jakarta`
   - `PHP_MEMORY_LIMIT=256M`
   - `PHP_UPLOAD_MAX_FILESIZE=10M`

---

## 📊 Langkah 5: Import Data & Testing

### 5.1 Import Database
**Opsi 1: Via Railway Query Editor**
1. Buka MySQL service di Railway
2. Tab **Data** → **Query**
3. Copy paste isi `database.sql`
4. Execute

**Opsi 2: Via MySQL Client (Recommended)**
```bash
# Dapatkan credentials dari Railway Variables
mysql -h [MYSQL_HOST] -P [MYSQL_PORT] -u [MYSQL_USER] -p[MYSQL_PASSWORD] [MYSQL_DATABASE] < database.sql
```

**Opsi 3: Via phpMyAdmin (TablePlus/DBeaver)**
1. Download TablePlus atau DBeaver
2. Buat koneksi baru dengan credentials Railway
3. Import file `database.sql`

### 5.2 Insert Data Awal
Setelah schema dibuat, insert data default:

```sql
-- Admin default
INSERT INTO users (username, password, nama_lengkap, email, role) 
VALUES ('admin', '$2y$10$YourHashedPassword', 'Administrator', 'admin@booking.com', 'admin');

-- Petugas default  
INSERT INTO users (username, password, nama_lengkap, email, role)
VALUES ('petugas', '$2y$10$YourHashedPassword', 'Petugas Booking', 'petugas@booking.com', 'petugas');
```

💡 **Generate password hash** via PHP:
```php
echo password_hash('password', PASSWORD_DEFAULT);
```

### 5.3 Testing Aplikasi
1. Buka domain Railway Anda
2. Test login dengan akun admin/petugas
3. Test semua fitur utama:
   - ✅ Login/Register
   - ✅ Kelola Ruangan
   - ✅ Booking
   - ✅ Upload foto
   - ✅ Laporan

---

## 🐛 Troubleshooting

### Error: Database Connection Failed
**Solusi:**
1. Cek Variables di Railway sudah benar
2. Pastikan MySQL service sudah running
3. Cek `config/database.php` menggunakan environment variables
4. Test koneksi manual:
```php
echo "Host: " . getenv('MYSQL_HOST') . "\n";
echo "User: " . getenv('MYSQL_USER') . "\n";
echo "Database: " . getenv('MYSQL_DATABASE') . "\n";
```

### Error: 404 Not Found
**Solusi:**
1. Cek file `.htaccess` ada di root
2. Pastikan DirectoryIndex sudah diset
3. Cek Apache mod_rewrite enabled

### Error: Upload File Failed
**Solusi:**
1. Pastikan folder `uploads/` ada dan writable
2. Cek PHP upload settings:
```php
php_value upload_max_filesize 10M
php_value post_max_size 10M
```
3. Di Railway, file uploaded akan hilang saat restart (gunakan external storage seperti Cloudinary/S3)

### Error: 500 Internal Server Error
**Solusi:**
1. Cek logs di Railway Dashboard → **Deployments** → klik build → **View Logs**
2. Enable error logging:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```
3. Cek permission file dan folder

### File Upload Hilang Setelah Deploy
**Penyebab:** Railway menggunakan ephemeral storage

**Solusi:**
1. **Gunakan External Storage** (Recommended):
   - Cloudinary (free tier)
   - AWS S3
   - Railway Volumes (berbayar)

2. **Setup Cloudinary** (contoh):
```php
// Install via composer
composer require cloudinary/cloudinary_php

// Upload ke cloudinary
$cloudinary->uploadApi()->upload($file_path, [
    'folder' => 'booking-ruangan'
]);
```

---

## 🔄 Update Aplikasi

### Deploy Update Baru
```bash
# Buat perubahan di local
# ...

# Commit dan push
git add .
git commit -m "Update: deskripsi perubahan"
git push origin main
```

Railway akan **otomatis** detect perubahan dan deploy ulang.

---

## 💰 Estimasi Biaya Railway

### Free Tier (Starter)
- ✅ $5 credit gratis per bulan
- ✅ 500 jam execution
- ✅ Cukup untuk project kecil-menengah
- ⚠️ Aplikasi akan sleep setelah 15 menit tidak ada traffic (pada hobby plan)

### Upgrade ke Developer Plan
- 💵 $5/bulan per service
- ✅ Tanpa sleep
- ✅ Priority support
- ✅ Custom domains unlimited

### Tips Hemat
1. Gunakan 1 MySQL database untuk semua environment
2. Gunakan Railway Volumes hanya jika perlu
3. Optimize query database
4. Cache static assets

---

## 📚 Resource Tambahan

### Dokumentasi
- [Railway Docs](https://docs.railway.app)
- [Railway PHP Guide](https://docs.railway.app/guides/php)
- [Nixpacks](https://nixpacks.com)

### Alternative Deployment
Jika Railway tidak cocok, alternatif lain:
1. **Heroku** (dengan ClearDB MySQL)
2. **DigitalOcean App Platform**
3. **Render.com**
4. **VPS** (Vultr, Linode, DigitalOcean Droplet)

### Tools yang Berguna
- **TablePlus** - MySQL GUI client
- **DBeaver** - Universal database tool
- **Postman** - Test API
- **GitHub Desktop** - Git GUI

---

## ✅ Checklist Deploy

- [ ] File `.htaccess` dibuat
- [ ] `config/database.php` sudah support environment variables
- [ ] File `nixpacks.toml` dibuat
- [ ] File `.gitignore` dibuat
- [ ] Project di-push ke GitHub
- [ ] Railway project dibuat
- [ ] MySQL database di-provision
- [ ] Aplikasi PHP di-deploy
- [ ] Database linked ke aplikasi
- [ ] Schema database di-import
- [ ] Data default di-insert
- [ ] Domain di-generate
- [ ] Testing lengkap
- [ ] Password admin diganti

---

## 🎉 Selesai!

Aplikasi Sistem Booking Ruangan Anda sekarang sudah live di Railway!

**URL Aplikasi:** `https://your-app-production.up.railway.app`

**Login Info:**
- Admin: `admin` / `password` (ganti segera!)
- Petugas: `petugas` / `password` (ganti segera!)

---

## 📞 Support

Jika ada pertanyaan atau masalah:
1. Cek Railway logs
2. Cek dokumentasi Railway
3. Community Railway Discord
4. StackOverflow

**Good Luck!** 🚀

---

&copy; 2025 Sistem Booking Ruangan - Railway Deployment Guide
