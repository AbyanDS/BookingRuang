# 🚀 Quick Start - Deploy ke Railway

## ⚡ Langkah Cepat (5 Menit)

### 1️⃣ Push ke GitHub
```bash
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/USERNAME/booking-ruangan.git
git push -u origin main
```

### 2️⃣ Deploy ke Railway
1. Buka [railway.app](https://railway.app) → Login with GitHub
2. **New Project** → **Provision MySQL** (tunggu selesai, status hijau ✅)
3. **New** → **GitHub Repo** → Pilih `booking-ruangan`
4. ⚠️ **PENTING:** Klik PHP App → **Variables** → **Add Reference** → Pilih MySQL
5. Tunggu 2-3 menit (auto-redeploy dengan database credentials)
6. **Settings** → **Generate Domain**

### 3️⃣ Import Database
1. Klik MySQL service → **Data** → **Query**
2. Copy paste isi file `database.sql` → **Run**
3. Copy paste isi file `insert_default_data.sql` → **Run**

### 4️⃣ Test & Verifikasi
1. Buka `https://your-app.railway.app/test_db_connection.php`
2. Pastikan semua check ✅ (hijau)
3. Jika error, tunggu 2-3 menit dan refresh

### 5️⃣ Buka Aplikasi
URL: `https://your-app-production.up.railway.app`

Login:
- Admin: `admin` / `password`
- Petugas: `petugas` / `password`

---

## 📋 File yang Sudah Disiapkan

✅ `.htaccess` - Konfigurasi Apache  
✅ `nixpacks.toml` - Build configuration  
✅ `railway.json` - Railway settings  
✅ `.gitignore` - Ignore unnecessary files  
✅ `config/database.php` - Support environment variables  

---

## ⚠️ Penting!

1. **Link Database!** Ini adalah langkah paling krusial. Tanpa ini, error "No such file or directory"
2. **Tunggu Auto-Redeploy:** Setelah link database, tunggu 2-3 menit untuk redeploy
3. **Test Connection:** Selalu test dengan `test_db_connection.php` sebelum login
4. **Ubah password default** setelah login pertama
5. **File upload akan hilang** saat restart (gunakan Cloudinary untuk production)
6. **Free tier**: $5 credit/bulan (cukup untuk development)

---

## 📖 Dokumentasi Lengkap
Baca file `RAILWAY_DEPLOYMENT.md` untuk panduan detail.

## 🆘 Troubleshooting
- **Database error**: Cek Variables sudah linked ke MySQL
- **404 error**: Tunggu build selesai (2-3 menit)
- **500 error**: Cek logs di Deployments → View Logs

---

**Selamat Deploy!** 🎉
