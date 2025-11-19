-- Database untuk Sistem Booking Ruangan
CREATE DATABASE IF NOT EXISTS booking_ruangan;
USE booking_ruangan;

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
);

-- Tabel Ruangan
CREATE TABLE IF NOT EXISTS ruangan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_ruangan VARCHAR(100) NOT NULL,
    kapasitas INT NOT NULL,
    fasilitas TEXT,
    status ENUM('tersedia', 'maintenance') DEFAULT 'tersedia',
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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
    FOREIGN KEY (ruangan_id) REFERENCES ruangan(id) ON DELETE CASCADE
);

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
    FOREIGN KEY (ruangan_id) REFERENCES ruangan(id) ON DELETE CASCADE
);

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
    FOREIGN KEY (dilakukan_oleh) REFERENCES users(id) ON DELETE SET NULL
);

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
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert data admin dan petugas default
INSERT INTO users (username, password, nama_lengkap, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@booking.com', 'admin'),
('petugas', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Petugas Ruangan', 'petugas@booking.com', 'petugas');
-- Password default: password

-- Insert data ruangan sesuai daftar
INSERT INTO ruangan (nama_ruangan, kapasitas, fasilitas, status) VALUES
-- Laboratorium
('Lab. IPA', 40, 'Laboratorium Ilmu Pengetahuan Alam - Peralatan Lab, Meja Praktikum, AC, Proyektor', 'tersedia'),
('Lab. PB', 35, 'Laboratorium Penataan Barang - Peralatan Penataan, Rak Display, AC', 'tersedia'),
-- Lab 1-22
('Lab 1', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 2', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 3', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 4', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 5', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 6', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 7', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 8', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 9', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 10', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 11', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 12', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 13', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 14', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 15', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 16', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 17', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 18', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 19', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 20', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 21', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
('Lab 22', 30, 'Komputer, Proyektor, AC, Whiteboard, Wifi', 'tersedia'),
-- Ruang Kelas 1-3
('Ruang 1', 40, 'Meja Kursi, Whiteboard, Proyektor, AC, Wifi', 'tersedia'),
('Ruang 2', 40, 'Meja Kursi, Whiteboard, Proyektor, AC, Wifi', 'tersedia'),
('Ruang 3', 40, 'Meja Kursi, Whiteboard, Proyektor, AC, Wifi', 'tersedia'),
-- Fasilitas Lain
('Lapangan Olahraga', 100, 'Lapangan Basket, Lapangan Voli, Tribun, Area Olahraga Outdoor', 'tersedia'),
('HALL', 200, 'Aula Besar, Sound System, Proyektor, AC, Panggung, Kursi', 'tersedia');

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
(3, 'XI RPL 1', '2', 'Rabu', '09:45:00', '12:15:00', 'Pemrograman Web', 'Andi Wijaya', 'Sesi kedua', 'aktif');
