# 🚂 Cara Import Database ke Railway MySQL

## ❌ Masalah dengan MySQL XAMPP

MySQL client di XAMPP versi lama dan tidak support `caching_sha2_password` yang digunakan Railway.

Error yang muncul:
```
ERROR 1045 (28000): Plugin caching_sha2_password could not be loaded
```

---

## ✅ Solusi - 3 Cara Alternatif

### **Cara 1: Via Railway Dashboard (Paling Mudah)** ⭐ RECOMMENDED

1. Buka [railway.app](https://railway.app)
2. Login dan buka project Anda
3. Klik **MySQL service**
4. Tab **"Data"**
5. Klik **"Query"**
6. Copy seluruh isi file `database.sql`
7. Paste di query editor
8. Klik **"Run"** atau tekan Ctrl+Enter
9. Tunggu hingga selesai (mungkin 10-30 detik)
10. Ulangi untuk file `insert_default_data.sql`

**Keuntungan:**
- ✅ Tidak perlu install software
- ✅ Langsung dari browser
- ✅ Tidak ada masalah authentication

---

### **Cara 2: Menggunakan TablePlus** (GUI Client)

**Download:** https://tableplus.com/

1. Install TablePlus (Free trial available)
2. Klik **"Create a new connection"**
3. Pilih **MySQL**
4. Masukkan details:
   ```
   Name: Railway MySQL
   Host: metro.proxy.rlwy.net
   Port: 39338
   User: root
   Password: MClfPGdILKmmIxLDjwgZesirmxvnXqiQ
   Database: railway
   ```
5. Klik **"Test"** untuk test connection
6. Klik **"Connect"**
7. Klik kanan pada database → **"Import"** → **"From SQL Dump"**
8. Pilih file `database.sql`
9. Klik **"Import"**
10. Ulangi untuk `insert_default_data.sql`

**Keuntungan:**
- ✅ GUI yang mudah digunakan
- ✅ Support modern MySQL features
- ✅ Bisa manage database dengan mudah

---

### **Cara 3: Menggunakan DBeaver** (Free & Open Source)

**Download:** https://dbeaver.io/

1. Install DBeaver (100% free)
2. Klik **"New Database Connection"**
3. Pilih **MySQL**
4. Masukkan details:
   ```
   Server Host: metro.proxy.rlwy.net
   Port: 39338
   Database: railway
   Username: root
   Password: MClfPGdILKmmIxLDjwgZesirmxvnXqiQ
   ```
5. Klik **"Test Connection"** (akan download driver jika perlu)
6. Klik **"Finish"**
7. Klik kanan pada database → **"SQL Editor"** → **"Open SQL Script"**
8. Pilih file `database.sql`
9. Klik **"Execute SQL Script"** (Ctrl+Alt+X)
10. Ulangi untuk `insert_default_data.sql`

**Keuntungan:**
- ✅ Gratis selamanya
- ✅ Cross-platform
- ✅ Powerful features

---

### **Cara 4: Install MySQL Client Terbaru**

Jika tetap ingin pakai command line:

**Download MySQL Shell:**
https://dev.mysql.com/downloads/shell/

Setelah install:

```bash
mysqlsh --sql -h metro.proxy.rlwy.net -P 39338 -u root -pMClfPGdILKmmIxLDjwgZesirmxvnXqiQ railway

# Setelah connect:
\source database.sql
\source insert_default_data.sql
```

---

## 🎯 Rekomendasi

**Untuk sekarang:** Gunakan **Cara 1** (Railway Dashboard Query Editor)
- Paling cepat
- Tidak perlu install apapun
- Langsung dari browser

**Untuk development jangka panjang:** Install **TablePlus** atau **DBeaver**
- Lebih mudah manage database
- GUI yang user-friendly
- Bisa digunakan untuk project lain

---

## 📋 Verifikasi Import Berhasil

Setelah import, cek dengan query ini di Railway Query Editor:

```sql
-- Cek semua tables
SHOW TABLES;

-- Cek jumlah users
SELECT COUNT(*) as total_users FROM users;

-- Cek default users
SELECT username, nama_lengkap, role FROM users;

-- Cek jumlah ruangan
SELECT COUNT(*) as total_ruangan FROM ruangan;
```

Expected results:
- ✅ 5 tables (users, ruangan, booking, jadwal_ruangan, history_peminjaman)
- ✅ 3 users (admin, petugas, user)
- ✅ 5 ruangan (demo data)

---

## 🔗 Connection Details (Simpan untuk referensi)

```
Host: metro.proxy.rlwy.net
Port: 39338
User: root
Password: MClfPGdILKmmIxLDjwgZesirmxvnXqiQ
Database: railway
```

---

## 🆘 Troubleshooting

**Connection Timeout:**
- Check internet connection
- Verify Railway MySQL service is running (green status)

**Access Denied:**
- Double check password (case sensitive)
- Verify credentials di Railway dashboard

**Import Error:**
- Check SQL syntax
- Import `database.sql` dulu, baru `insert_default_data.sql`
- Jangan import 2x (akan error duplicate)

---

**Pilih salah satu cara di atas dan database akan ter-import dengan sukses!** 🎉
