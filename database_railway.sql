-- =====================================================
-- Database untuk Sistem Booking Ruangan - Railway Version
-- Database name: railway (already exists in Railway)
-- =====================================================
-- Railway akan menggunakan database yang sudah ada
-- Tidak perlu CREATE DATABASE dan USE

-- Drop existing tables jika ada (opsional, hati-hati!)
-- DROP TABLE IF EXISTS history_peminjaman;
-- DROP TABLE IF EXISTS password_reset_requests;
-- DROP TABLE IF EXISTS jadwal_ruangan;
-- DROP TABLE IF EXISTS booking;
-- DROP TABLE IF EXISTS ruangan;
-- DROP TABLE IF EXISTS users;

-- Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'petugas', 'user') DEFAULT 'user',
    profile_picture VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Ruangan
CREATE TABLE IF NOT EXISTS ruangan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_ruangan VARCHAR(100) NOT NULL,
    kapasitas INT NOT NULL,
    fasilitas TEXT,
    lokasi VARCHAR(255) DEFAULT NULL,
    status ENUM('tersedia', 'maintenance') DEFAULT 'tersedia',
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Booking
CREATE TABLE IF NOT EXISTS booking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ruangan_id INT NOT NULL,
    tanggal_booking DATE NOT NULL,
    waktu_mulai TIME NOT NULL,
    waktu_selesai TIME NOT NULL,
    keperluan TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ruangan_id) REFERENCES ruangan(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_ruangan_id (ruangan_id),
    INDEX idx_tanggal (tanggal_booking),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Jadwal Ruangan
CREATE TABLE IF NOT EXISTS jadwal_ruangan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ruangan_id INT NOT NULL,
    kelas VARCHAR(50) NOT NULL,
    sesi ENUM('1', '2') NOT NULL,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') NOT NULL,
    waktu_mulai TIME NOT NULL,
    waktu_selesai TIME NOT NULL,
    kegiatan VARCHAR(200) NOT NULL,
    penanggung_jawab VARCHAR(100),
    keterangan TEXT,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ruangan_id) REFERENCES ruangan(id) ON DELETE CASCADE,
    INDEX idx_ruangan_id (ruangan_id),
    INDEX idx_hari (hari),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel History Peminjaman
CREATE TABLE IF NOT EXISTS history_peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    ruangan_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    status_lama VARCHAR(20),
    status_baru VARCHAR(20),
    keterangan TEXT,
    dilakukan_oleh INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ruangan_id) REFERENCES ruangan(id) ON DELETE CASCADE,
    FOREIGN KEY (dilakukan_oleh) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_booking_id (booking_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Password Reset Requests
CREATE TABLE IF NOT EXISTS password_reset_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    approved_by INT,
    approved_date TIMESTAMP NULL,
    new_password VARCHAR(255),
    keterangan TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Insert Data Default
-- =====================================================

-- Insert data admin dan petugas default
-- Password: password (hashed dengan bcrypt)
INSERT INTO users (username, password, nama_lengkap, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@booking.com', 'admin'),
('petugas', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Petugas Ruangan', 'petugas@booking.com', 'petugas')
ON DUPLICATE KEY UPDATE username=username;

-- Insert data ruangan
INSERT INTO ruangan (nama_ruangan, kapasitas, fasilitas, lokasi, status) VALUES
-- Laboratorium
('Lab. IPA', 40, 'Laboratorium Ilmu Pengetahuan Alam - Peralatan Lab, Meja Praktikum, AC, Proyektor', 'Lantai 2', 'tersedia'),
('Lab. PB', 35, 'Laboratorium Penataan Barang - Peralatan Penataan, Rak Display, AC', 'Lantai 2', 'tersedia'),
-- Lab 1-22
('Lab 1', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 3', 'tersedia'),
('Lab 2', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 3', 'tersedia'),
('Lab 3', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 3', 'tersedia'),
('Lab 4', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 3', 'tersedia'),
('Lab 5', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 3', 'tersedia'),
('Lab 6', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 4', 'tersedia'),
('Lab 7', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 4', 'tersedia'),
('Lab 8', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 4', 'tersedia'),
('Lab 9', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 4', 'tersedia'),
('Lab 10', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 4', 'tersedia'),
('Lab 11', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 5', 'tersedia'),
('Lab 12', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 5', 'tersedia'),
('Lab 13', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 5', 'tersedia'),
('Lab 14', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 5', 'tersedia'),
('Lab 15', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 5', 'tersedia'),
('Lab 16', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 6', 'tersedia'),
('Lab 17', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 6', 'tersedia'),
('Lab 18', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 6', 'tersedia'),
('Lab 19', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 6', 'tersedia'),
('Lab 20', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 6', 'tersedia'),
('Lab 21', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 7', 'tersedia'),
('Lab 22', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'Lantai 7', 'tersedia'),
-- Ruang Kelas 1-3
('Ruang 1', 40, 'Meja Kursi, Whiteboard, Proyektor, AC, Wifi', 'Lantai 1', 'tersedia'),
('Ruang 2', 40, 'Meja Kursi, Whiteboard, Proyektor, AC, Wifi', 'Lantai 1', 'tersedia'),
('Ruang 3', 40, 'Meja Kursi, Whiteboard, Proyektor, AC, Wifi', 'Lantai 1', 'tersedia'),
-- Fasilitas Lain
('Lapangan Olahraga', 100, 'Lapangan Basket, Lapangan Voli, Tribun, Area Olahraga Outdoor', 'Outdoor', 'tersedia'),
('HALL', 200, 'Aula Besar, Sound System, Proyektor, AC, Panggung, Kursi', 'Lantai 1', 'tersedia')
ON DUPLICATE KEY UPDATE nama_ruangan=VALUES(nama_ruangan);

-- Insert data jadwal ruangan contoh
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, keterangan, status) VALUES
-- Kelas X RPL 1
(1, 'X RPL 1', '1', 'Senin', '07:00:00', '09:30:00', 'Matematika', 'Budi Santoso', 'Sesi pagi', 'aktif'),
(1, 'X RPL 1', '2', 'Senin', '09:45:00', '12:15:00', 'Bahasa Indonesia', 'Siti Nurhaliza', 'Sesi kedua', 'aktif'),
(1, 'X RPL 1', '1', 'Selasa', '07:00:00', '09:30:00', 'Bahasa Inggris', 'Ahmad Dahlan', 'Sesi pagi', 'aktif'),
(1, 'X RPL 1', '2', 'Selasa', '09:45:00', '12:15:00', 'Pemrograman Dasar', 'Andi Wijaya', 'Sesi kedua', 'aktif'),
-- Kelas X RPL 2
(2, 'X RPL 2', '1', 'Senin', '07:00:00', '09:30:00', 'Bahasa Indonesia', 'Siti Nurhaliza', 'Sesi pagi', 'aktif'),
(2, 'X RPL 2', '2', 'Senin', '09:45:00', '12:15:00', 'Matematika', 'Budi Santoso', 'Sesi kedua', 'aktif'),
(2, 'X RPL 2', '1', 'Selasa', '07:00:00', '09:30:00', 'Pemrograman Dasar', 'Andi Wijaya', 'Sesi pagi', 'aktif'),
(2, 'X RPL 2', '2', 'Selasa', '09:45:00', '12:15:00', 'Bahasa Inggris', 'Ahmad Dahlan', 'Sesi kedua', 'aktif'),
-- Kelas XI RPL 1
(3, 'XI RPL 1', '1', 'Rabu', '07:00:00', '09:30:00', 'Basis Data', 'Dr. Hendra', 'Sesi pagi', 'aktif'),
(3, 'XI RPL 1', '2', 'Rabu', '09:45:00', '12:15:00', 'Pemrograman Web', 'Andi Wijaya', 'Sesi kedua', 'aktif')
ON DUPLICATE KEY UPDATE kelas=VALUES(kelas);

-- =====================================================
-- Selesai! Database siap digunakan
-- =====================================================
-- Default Login:
-- Admin: admin / password
-- Petugas: petugas / password
-- =====================================================
