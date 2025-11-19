# Fitur Auto-Display Booking di Jadwal Ruangan

## Overview
Sistem ini akan otomatis menampilkan booking user di menu **Jadwal Ruangan** dan menghapusnya dari tampilan ketika booking selesai atau statusnya berubah menjadi "completed".

## Cara Kerja

### 1. **Tampilan di Jadwal Ruangan**
- Booking dengan status `pending` atau `approved` akan muncul di jadwal ruangan
- Hanya booking dengan tanggal >= hari ini yang ditampilkan
- Booking yang sudah `completed` TIDAK akan muncul di jadwal

### 2. **Identifikasi Visual**
- **Booking User**: Background kuning/orange dengan badge status
  - Badge Orange (Pending): "🔴 Booking Pending"
  - Badge Hijau (Approved): "✓ Booking Approved"
- **Jadwal Tetap**: Background abu-abu normal

### 3. **Auto-Cleanup System**

#### Automatic Cleanup
Setiap kali halaman `jadwal_view.php` dibuka, sistem akan:
- Auto-update status booking yang sudah expired menjadi `completed`
- Booking yang tanggal/waktunya sudah lewat akan otomatis hilang dari jadwal

#### Manual Cleanup (Optional)
Akses: `config/auto_cleanup_completed_bookings.php`
- Via Browser: Untuk melihat report detail
- Via Cron Job: Setup untuk run otomatis setiap jam
- Via AJAX: Tambahkan `?ajax=1` untuk JSON response

## File yang Dimodifikasi

### 1. `ajax_jadwal.php`
- **Weekly View**: UNION query untuk gabungkan jadwal_ruangan + booking
- **Table View**: UNION query dengan filter hari otomatis
- Menambahkan badge status untuk booking
- Filtering berdasarkan DAYOFWEEK untuk booking

### 2. `jadwal_view.php`
- Menambahkan auto-cleanup query di bagian atas
- Update status booking expired setiap halaman di-load

### 3. `assets/css/style.css`
- `.booking-item`: Styling untuk booking di weekly view
- `.booking-row`: Styling untuk booking di table view
- `.booking-badge`: Badge untuk status pending/approved
- Animation pulse untuk pending bookings

### 4. `config/auto_cleanup_completed_bookings.php` (NEW)
- Script standalone untuk cleanup manual
- Support AJAX call
- Logging system
- Statistics report

## Query Logic

### Weekly View
```sql
-- Jadwal Tetap
SELECT j.*, r.nama_ruangan, r.lokasi, 'jadwal' as source_type
FROM jadwal_ruangan j 
JOIN ruangan r ON j.ruangan_id = r.id 
WHERE j.hari = 'Senin' AND j.status = 'aktif'

UNION

-- Booking User (hari Senin = DAYOFWEEK = 2)
SELECT b.*, r.nama_ruangan, u.nama_lengkap as penanggung_jawab, 'booking' as source_type
FROM booking b
JOIN ruangan r ON b.ruangan_id = r.id
JOIN users u ON b.user_id = u.id
WHERE b.status IN ('pending', 'approved')
  AND b.tanggal_booking >= CURDATE()
  AND DAYOFWEEK(b.tanggal_booking) = 2
```

### Auto-Cleanup
```sql
UPDATE booking 
SET status = 'completed', updated_at = NOW()
WHERE status IN ('approved', 'pending')
  AND (
    tanggal_booking < CURDATE()
    OR (tanggal_booking = CURDATE() AND waktu_selesai < CURTIME())
  )
```

## Setup Cron Job (Optional)

### Linux/Mac
```bash
# Edit crontab
crontab -e

# Add line (run setiap jam)
0 * * * * php /path/to/config/auto_cleanup_completed_bookings.php?silent=1
```

### Windows (Task Scheduler)
1. Open Task Scheduler
2. Create Basic Task
3. Trigger: Daily, repeat every 1 hour
4. Action: Start Program
5. Program: `C:\xampp\php\php.exe`
6. Arguments: `C:\xampp\htdocs\PlsworkUKK\config\auto_cleanup_completed_bookings.php`

## Testing

### 1. Test Booking Muncul di Jadwal
1. Login sebagai user
2. Buat booking untuk besok dengan status pending
3. Buka `jadwal_view.php`
4. Booking akan muncul dengan badge orange "Booking Pending"

### 2. Test Auto-Cleanup
1. Edit database: Set tanggal_booking ke kemarin
2. Refresh `jadwal_view.php`
3. Status akan otomatis berubah jadi "completed"
4. Booking hilang dari tampilan jadwal

### 3. Test Filter
1. Filter berdasarkan ruangan tertentu
2. Booking dan jadwal tetap akan terfilter
3. Filter berdasarkan hari juga berfungsi untuk booking

## Log File
- Location: `uploads/cleanup_completed_log.txt`
- Contains: Timestamp, actions, statistics
- Auto-created when cleanup script runs

## Database Changes
**TIDAK ADA PERUBAHAN DATABASE**
- Menggunakan tabel existing: `booking`, `jadwal_ruangan`, `ruangan`, `users`
- Hanya menggunakan query UNION untuk gabungkan data
- Auto-update status booking yang expired

## Troubleshooting

### Booking tidak muncul?
- Cek status booking: harus `pending` atau `approved`
- Cek tanggal booking: harus >= hari ini
- Cek query di `ajax_jadwal.php` line 34-60

### Booking tidak hilang setelah selesai?
- Pastikan auto-cleanup berjalan (refresh jadwal_view.php)
- Cek status di database (harus berubah jadi `completed`)
- Jalankan manual cleanup: `config/auto_cleanup_completed_bookings.php`

### Badge tidak muncul?
- Clear browser cache
- Check CSS file di-load dengan `?v=timestamp`
- Inspect element untuk cek class `booking-item` dan `booking-badge`

## Fitur Tambahan (Optional)

### Archive Old Bookings
Uncomment di `auto_cleanup_completed_bookings.php` line 68-78 untuk:
- Auto-delete booking completed > 30 hari
- Hemat space database
- Keep only recent history

### Email Notification
Tambahkan di auto-cleanup untuk kirim email:
- Notif saat booking auto-completed
- Daily summary untuk admin
- Warning untuk booking pending > 7 hari

---
**Last Updated**: November 2025
**Developer**: UKK Project Team
