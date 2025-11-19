-- Script untuk update database ruangan
-- Jalankan script ini untuk menghapus kolom lokasi dan update data ruangan

USE booking_ruangan;

-- Hapus semua data ruangan yang ada
TRUNCATE TABLE jadwal_ruangan;
TRUNCATE TABLE history_peminjaman;
DELETE FROM booking;
DELETE FROM ruangan;

-- Reset auto increment
ALTER TABLE ruangan AUTO_INCREMENT = 1;

-- Hapus kolom lokasi
ALTER TABLE ruangan DROP COLUMN IF EXISTS lokasi;

-- Insert data ruangan baru sesuai daftar
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

SELECT 'Update ruangan berhasil! Total ruangan:' AS message, COUNT(*) AS total FROM ruangan;
