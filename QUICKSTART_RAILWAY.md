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
2. **New Project** → **Provision MySQL** (tunggu selesai)
3. **New** → **GitHub Repo** → Pilih `booking-ruangan`
4. Klik PHP App → **Variables** → **Add Reference** → Pilih MySQL
5. **Settings** → **Generate Domain**

### 3️⃣ Import Database
1. Klik MySQL service → **Data** → **Query**
2. Copy paste isi file `database.sql` → **Run**

### 4️⃣ Buka Aplikasi
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

1. **Ubah password default** setelah login pertama
2. **File upload akan hilang** saat restart (gunakan Cloudinary untuk production)
3. **Free tier**: $5 credit/bulan (cukup untuk development)

---

## 📖 Dokumentasi Lengkap
Baca file `RAILWAY_DEPLOYMENT.md` untuk panduan detail.

## 🆘 Troubleshooting
- **Database error**: Cek Variables sudah linked ke MySQL
- **404 error**: Tunggu build selesai (2-3 menit)
- **500 error**: Cek logs di Deployments → View Logs

---

**Selamat Deploy!** 🎉
