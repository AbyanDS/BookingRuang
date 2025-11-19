# 🔐 Sistem Reset Password Baru

## Overview
Sistem reset password yang telah diperbarui dengan alur yang lebih aman dan terkontrol:
- User mengirim permintaan reset via email
- Admin menerima notifikasi dan meng-approve
- Sistem generate link reset otomatis (valid 24 jam)
- User menggunakan link untuk reset password sendiri

---

## 🔄 Alur Reset Password

### 👤 **Dari Sisi User:**

1. **Request Reset Password**
   - Akses: `request_reset_password.php`
   - Input: Email + Alasan reset
   - Output: Permintaan terkirim + Info WhatsApp admin

2. **Hubungi Admin (Opsional)**
   - WhatsApp admin: **+62 812-3456-7890**
   - Untuk mempercepat proses approval

3. **Tunggu Approval**
   - Admin akan review dan approve/reject
   - Jika approved: User dapat link reset password

4. **Reset Password**
   - Klik link yang dikirim admin
   - Buat password baru (min 6 karakter)
   - Login dengan password baru

### 👨‍💼 **Dari Sisi Admin:**

1. **Lihat Request**
   - Akses: `admin/password_reset.php`
   - Badge notifikasi di navbar (jumlah pending)
   - Tabel lengkap dengan info user

2. **Review & Approve**
   - Klik tombol "✓ Approve"
   - Sistem generate link reset otomatis
   - Link valid 24 jam

3. **Kirim Link ke User**
   - Copy link reset password
   - Kirim via email/WhatsApp
   - Tombol "📱 WA" untuk share langsung

4. **Monitor Status**
   - Pending: Menunggu approval
   - Approved: Link sudah digenerate
   - Completed: User sudah reset password
   - Rejected: Ditolak dengan alasan

---

## 📋 Database Updates

**Jalankan query SQL ini di phpMyAdmin:**

```sql
-- File: update_password_reset_table.sql

-- Tambah kolom baru
ALTER TABLE `password_reset_requests` 
ADD COLUMN `request_email` VARCHAR(255) NULL AFTER `user_id`,
ADD COLUMN `reset_token` VARCHAR(64) NULL AFTER `approved_date`,
ADD COLUMN `token_expiry` DATETIME NULL AFTER `reset_token`;

-- Update status enum
ALTER TABLE `password_reset_requests` 
MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending';

-- Tambah index untuk performa
ALTER TABLE `password_reset_requests`
ADD INDEX `idx_reset_token` (`reset_token`),
ADD INDEX `idx_token_expiry` (`token_expiry`),
ADD INDEX `idx_status` (`status`);
```

**Struktur Tabel `password_reset_requests`:**
- `id` - Primary key
- `user_id` - Foreign key ke tabel users
- `request_email` - **BARU**: Email yang digunakan user saat request
- `request_date` - Tanggal request
- `status` - pending/approved/rejected/completed
- `approved_by` - Admin yang approve/reject
- `approved_date` - Tanggal approve/reject
- `reset_token` - **BARU**: Token unik untuk link reset (64 char)
- `token_expiry` - **BARU**: Waktu expired token (24 jam)
- `keterangan` - Alasan reset dari user atau alasan reject dari admin

---

## 🎯 Fitur Utama

### 1. **Email Request Form**
File: `request_reset_password.php`
- User input email terdaftar
- User input alasan reset password
- Validasi email di database
- Cegah duplicate request (1 pending per user)

### 2. **WhatsApp Admin Info**
Setelah submit, tampil:
```
📞 Hubungi Admin untuk Percepatan
WhatsApp Admin: +62 812-3456-7890
[📱 Chat WhatsApp Admin]
```
- Link langsung ke WhatsApp
- Pre-filled message dengan email user
- Nomor WA bisa diubah di `request_reset_password.php` line 60

### 3. **Admin Dashboard**
File: `admin/password_reset.php`

**Tabel Request:**
| ID | User | Email | Alasan | Tanggal | Status | Diproses Oleh | Aksi |
|----|------|-------|--------|---------|--------|---------------|------|

**Kolom Aksi:**
- **Pending**: Tombol "✓ Approve" dan "✕ Reject"
- **Approved**: Tombol "🔗 Lihat Link" dan "📱 WA" untuk share
- **Completed/Rejected**: Status "Selesai"

### 4. **Generate Link Otomatis**
Saat admin approve:
- Sistem generate token random (64 karakter)
- Set expiry 24 jam dari sekarang
- Format link: `https://domain.com/PlsworkUKK/reset_password.php?token=xxx`
- Link ditampilkan di modal untuk admin copy/share

### 5. **Modal Link Management**
- **Modal Approve**: Konfirmasi sebelum approve
- **Modal Reset Link**: Tampil setelah approve
  - Show link lengkap
  - Tombol "📋 Copy Link" - Copy ke clipboard
  - Tombol "📱 Share via WhatsApp" - Share langsung
  - Info expiry time

### 6. **Reset Password Page**
File: `reset_password.php`

**Flow:**
1. User klik link dengan token
2. Validasi token di database:
   - Token valid?
   - Status = approved?
   - Belum expired?
3. Jika valid: Form reset password
4. User input password baru 2x
5. Update password + set status = completed

**Jika token invalid/expired:**
- Pesan error
- Link ke ajukan request baru

---

## 🔒 Keamanan

### Token Security
- **Random 64 characters** (bin2hex(random_bytes(32)))
- **One-time use**: Status berubah ke completed setelah dipakai
- **Time-limited**: Valid 24 jam
- **Database hashed**: Token disimpan di database
- **No brute force**: Token sangat panjang dan random

### Validation
- Email format validation
- Password minimum 6 karakter
- Confirm password match
- Token expiry check
- Prevent duplicate pending requests
- XSS protection dengan htmlspecialchars()

### Access Control
- Only admin can approve/reject
- User can only reset with valid token
- Expired tokens automatically invalid
- Completed requests cannot be reused

---

## 📱 Konfigurasi WhatsApp Admin

**Edit nomor WhatsApp admin:**

1. File: `request_reset_password.php`
2. Cari line ~60:
```php
href="https://wa.me/6281234567890?text=..."
```
3. Ganti dengan nomor admin:
```php
href="https://wa.me/628XXXXXXXXXX?text=..."
```

Format: `628` + nomor tanpa `0` di depan
- Contoh: 081234567890 → 6281234567890

---

## 🐛 Troubleshooting

### Issue: "Link tidak valid atau kadaluarsa"
**Penyebab:**
- Token sudah digunakan (status completed)
- Token expired (>24 jam)
- Token tidak ada di database

**Solusi:**
- User ajukan request baru
- Admin approve lagi (akan generate token baru)

### Issue: "Permintaan sudah diproses"
**Penyebab:**
- User sudah punya request pending

**Solusi:**
- Tunggu admin approve request yang ada
- Atau admin reject dulu, baru user ajukan lagi

### Issue: Admin tidak dapat approve
**Penyebab:**
- Kolom database belum ditambahkan

**Solusi:**
- Jalankan `update_password_reset_table.sql` di phpMyAdmin

### Issue: Link terlalu panjang di WhatsApp
**Penyebab:**
- URL memang panjang (token 64 char)

**Solusi:**
- Gunakan URL shortener (bit.ly, tinyurl)
- Atau kirim via email saja

---

## 📊 Monitoring

### Check Request Statistics
```sql
-- Jumlah request per status
SELECT status, COUNT(*) as total 
FROM password_reset_requests 
GROUP BY status;

-- Request pending
SELECT u.nama_lengkap, u.email, pr.request_date, pr.keterangan
FROM password_reset_requests pr
JOIN users u ON pr.user_id = u.id
WHERE pr.status = 'pending'
ORDER BY pr.request_date DESC;

-- Token yang akan expired < 1 jam
SELECT u.nama_lengkap, u.email, pr.token_expiry
FROM password_reset_requests pr
JOIN users u ON pr.user_id = u.id
WHERE pr.status = 'approved' 
AND pr.token_expiry < DATE_ADD(NOW(), INTERVAL 1 HOUR)
AND pr.token_expiry > NOW();
```

### Clean Up Old Data
```sql
-- Hapus request completed > 30 hari
DELETE FROM password_reset_requests 
WHERE status = 'completed' 
AND approved_date < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Hapus request rejected > 7 hari
DELETE FROM password_reset_requests 
WHERE status = 'rejected' 
AND approved_date < DATE_SUB(NOW(), INTERVAL 7 DAY);

-- Reset token expired tapi masih approved
UPDATE password_reset_requests 
SET status = 'rejected', 
    keterangan = 'Token expired automatically'
WHERE status = 'approved' 
AND token_expiry < NOW();
```

---

## 🔄 Update Notes

**Version 2.0 - November 2025**

**Added:**
- ✅ Email input di request form
- ✅ Alasan reset password field
- ✅ WhatsApp admin contact info
- ✅ Auto-generate reset link dengan token
- ✅ Token expiry 24 jam
- ✅ Copy link & share WhatsApp buttons
- ✅ Modal untuk manage links
- ✅ Status "completed" untuk tracking
- ✅ Token validation di reset page

**Changed:**
- 🔄 Reset flow: email-based → token-based
- 🔄 Admin tidak set password → user reset sendiri
- 🔄 Manual contact → automated link delivery

**Security:**
- 🔒 64-char random token
- 🔒 Time-limited links (24 hours)
- 🔒 One-time use tokens
- 🔒 Status tracking untuk prevent reuse

---

## 📞 Support

**Admin WhatsApp:** +62 812-3456-7890  
**Email:** admin@booking-ruangan.com  
**Sistem:** Booking Ruangan v2.0

---

**Dokumentasi dibuat:** November 18, 2025  
**Last updated:** November 18, 2025
