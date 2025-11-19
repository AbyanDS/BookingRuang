-- =====================================================
-- Insert Data Default untuk Railway Deployment
-- =====================================================
-- Password untuk admin dan petugas adalah: password
-- Hash menggunakan password_hash() PHP dengan PASSWORD_DEFAULT
-- =====================================================

-- Insert Admin Default
INSERT INTO users (username, password, nama_lengkap, email, role) 
VALUES (
    'admin', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'Administrator', 
    'admin@booking.com', 
    'admin'
) ON DUPLICATE KEY UPDATE username=username;

-- Insert Petugas Default
INSERT INTO users (username, password, nama_lengkap, email, role)
VALUES (
    'petugas', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'Petugas Booking', 
    'petugas@booking.com', 
    'petugas'
) ON DUPLICATE KEY UPDATE username=username;

-- Insert User Demo (opsional)
INSERT INTO users (username, password, nama_lengkap, email, role)
VALUES (
    'user', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'User Demo', 
    'user@booking.com', 
    'user'
) ON DUPLICATE KEY UPDATE username=username;

-- =====================================================
-- Insert Ruangan Demo
-- =====================================================

INSERT INTO ruangan (nama_ruangan, kapasitas, fasilitas, lokasi, status) VALUES
('Ruang Rapat A', 20, 'Proyektor, AC, Whiteboard, WiFi', 'Lantai 1', 'tersedia'),
('Ruang Rapat B', 15, 'TV LCD, AC, Meja Bundar, WiFi', 'Lantai 1', 'tersedia'),
('Aula Serbaguna', 100, 'Sound System, Proyektor, AC, Panggung, WiFi', 'Lantai 2', 'tersedia'),
('Ruang Seminar', 50, 'Proyektor, AC, Sound System, WiFi, Podium', 'Lantai 2', 'tersedia'),
('Lab Komputer', 30, 'Komputer (30 unit), Proyektor, AC, WiFi', 'Lantai 3', 'tersedia')
ON DUPLICATE KEY UPDATE nama_ruangan=VALUES(nama_ruangan);

-- =====================================================
-- CATATAN PENTING
-- =====================================================
-- 1. Password default untuk semua user: password
-- 2. SEGERA ubah password setelah login pertama!
-- 3. Hash password dibuat dengan: password_hash('password', PASSWORD_DEFAULT)
-- 4. Untuk generate password baru, gunakan PHP:
--    echo password_hash('password_baru', PASSWORD_DEFAULT);
-- =====================================================
