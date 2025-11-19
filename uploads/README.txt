# 1. Push ke GitHub
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/USERNAME/booking-ruangan.git
git push -u origin main

# 2. Di Railway:
# - New Project → Provision MySQL
# - New → GitHub Repo → Pilih repo
# - Link database ke app (Variables → Add Reference)
# - Generate Domain

# 3. Import database.sql & insert_default_data.sql

# 4. Akses aplikasi & login!# Folder Uploads

Folder ini digunakan untuk menyimpan file upload seperti:
- Foto ruangan
- Foto profil user
- File dokumen booking

## ⚠️ PENTING untuk Railway Deployment

Railway menggunakan **ephemeral storage**, artinya:
- File yang diupload akan HILANG setelah aplikasi restart/redeploy
- Ini adalah behavior normal untuk platform PaaS seperti Railway

## 🔧 Solusi untuk Production

### Opsi 1: Cloudinary (Recommended - Free Tier Available)
Setup external storage untuk file persistent.

### Opsi 2: Railway Volumes (Berbayar)
Persistent storage dari Railway dengan biaya tambahan.

## 🧪 Development/Testing

Untuk development, file upload akan tetap berfungsi tapi akan hilang saat redeploy.

---
Update: 2025-11-19

