-- Script untuk mengisi jadwal ruangan
-- Pastikan sudah menjalankan database.sql dan update_ruangan.sql terlebih dahulu

USE booking_ruangan;

-- Hapus jadwal lama jika ada
TRUNCATE TABLE jadwal_ruangan;

-- ========================================
-- JADWAL KELAS X
-- ========================================

-- X RPL 1 - Senin
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 1' LIMIT 1), 'X RPL 1', '1', 'Senin', '07:00:00', '09:30:00', 'Pemrograman Web', 'Pak Budi', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 2' LIMIT 1), 'X RPL 1', '2', 'Senin', '10:00:00', '12:30:00', 'Database', 'Bu Ani', 'aktif');

-- X RPL 1 - Selasa
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Ruang 1' LIMIT 1), 'X RPL 1', '1', 'Selasa', '07:00:00', '09:30:00', 'Matematika', 'Pak Hendra', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab. IPA' LIMIT 1), 'X RPL 1', '2', 'Selasa', '10:00:00', '12:30:00', 'Fisika', 'Bu Sari', 'aktif');

-- X RPL 1 - Rabu
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 3' LIMIT 1), 'X RPL 1', '1', 'Rabu', '07:00:00', '09:30:00', 'Sistem Komputer', 'Pak Rudi', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Ruang 2' LIMIT 1), 'X RPL 1', '2', 'Rabu', '10:00:00', '12:30:00', 'Bahasa Indonesia', 'Bu Tuti', 'aktif');

-- X RPL 1 - Kamis
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 4' LIMIT 1), 'X RPL 1', '1', 'Kamis', '07:00:00', '09:30:00', 'Pemrograman Dasar', 'Pak Agus', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab. PB' LIMIT 1), 'X RPL 1', '2', 'Kamis', '10:00:00', '12:30:00', 'Praktik Jaringan', 'Bu Lisa', 'aktif');

-- X RPL 1 - Jumat
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Ruang 3' LIMIT 1), 'X RPL 1', '1', 'Jumat', '07:00:00', '09:30:00', 'Bahasa Inggris', 'Bu Maya', 'aktif');

-- ========================================
-- X RPL 2
-- ========================================

-- X RPL 2 - Senin
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 5' LIMIT 1), 'X RPL 2', '1', 'Senin', '07:00:00', '09:30:00', 'Pemrograman Web', 'Pak Joko', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 6' LIMIT 1), 'X RPL 2', '2', 'Senin', '10:00:00', '12:30:00', 'Database', 'Bu Rita', 'aktif');

-- X RPL 2 - Selasa
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Ruang 1' LIMIT 1), 'X RPL 2', '1', 'Selasa', '07:00:00', '09:30:00', 'Matematika', 'Pak Dedi', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 7' LIMIT 1), 'X RPL 2', '2', 'Selasa', '10:00:00', '12:30:00', 'Sistem Komputer', 'Bu Nina', 'aktif');

-- X RPL 2 - Rabu
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 8' LIMIT 1), 'X RPL 2', '1', 'Rabu', '07:00:00', '09:30:00', 'Pemrograman Mobile', 'Pak Faisal', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Ruang 2' LIMIT 1), 'X RPL 2', '2', 'Rabu', '10:00:00', '12:30:00', 'Bahasa Indonesia', 'Bu Yani', 'aktif');

-- X RPL 2 - Kamis
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 9' LIMIT 1), 'X RPL 2', '1', 'Kamis', '07:00:00', '09:30:00', 'Jaringan Komputer', 'Pak Iwan', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 10' LIMIT 1), 'X RPL 2', '2', 'Kamis', '10:00:00', '12:30:00', 'Praktik Web', 'Bu Sinta', 'aktif');

-- X RPL 2 - Jumat
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Ruang 3' LIMIT 1), 'X RPL 2', '1', 'Jumat', '07:00:00', '09:30:00', 'Bahasa Inggris', 'Bu Linda', 'aktif');

-- ========================================
-- JADWAL KELAS XI
-- ========================================

-- XI RPL 1 - Senin
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 11' LIMIT 1), 'XI RPL 1', '1', 'Senin', '07:00:00', '09:30:00', 'Web Framework', 'Pak Bambang', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 12' LIMIT 1), 'XI RPL 1', '2', 'Senin', '10:00:00', '12:30:00', 'Mobile Development', 'Bu Dewi', 'aktif');

-- XI RPL 1 - Selasa
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 13' LIMIT 1), 'XI RPL 1', '1', 'Selasa', '07:00:00', '09:30:00', 'Basis Data Lanjut', 'Pak Eko', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Ruang 1' LIMIT 1), 'XI RPL 1', '2', 'Selasa', '10:00:00', '12:30:00', 'Pemodelan Perangkat Lunak', 'Bu Fitri', 'aktif');

-- XI RPL 1 - Rabu
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 14' LIMIT 1), 'XI RPL 1', '1', 'Rabu', '07:00:00', '09:30:00', 'API Development', 'Pak Rizki', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 15' LIMIT 1), 'XI RPL 1', '2', 'Rabu', '10:00:00', '12:30:00', 'Testing Software', 'Bu Gina', 'aktif');

-- XI RPL 1 - Kamis
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 16' LIMIT 1), 'XI RPL 1', '1', 'Kamis', '07:00:00', '09:30:00', 'Web Server', 'Pak Hadi', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Ruang 2' LIMIT 1), 'XI RPL 1', '2', 'Kamis', '10:00:00', '12:30:00', 'Matematika Diskrit', 'Bu Ira', 'aktif');

-- XI RPL 1 - Jumat
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'HALL' LIMIT 1), 'XI RPL 1', '1', 'Jumat', '07:00:00', '09:30:00', 'Presentasi Project', 'Pak Budi', 'aktif');

-- ========================================
-- XI RPL 2
-- ========================================

-- XI RPL 2 - Senin
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 17' LIMIT 1), 'XI RPL 2', '1', 'Senin', '07:00:00', '09:30:00', 'Laravel Framework', 'Pak Jono', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 18' LIMIT 1), 'XI RPL 2', '2', 'Senin', '10:00:00', '12:30:00', 'Flutter', 'Bu Kartika', 'aktif');

-- XI RPL 2 - Selasa
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 19' LIMIT 1), 'XI RPL 2', '1', 'Selasa', '07:00:00', '09:30:00', 'MySQL Advanced', 'Pak Lukman', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 20' LIMIT 1), 'XI RPL 2', '2', 'Selasa', '10:00:00', '12:30:00', 'UI/UX Design', 'Bu Mira', 'aktif');

-- XI RPL 2 - Rabu
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 21' LIMIT 1), 'XI RPL 2', '1', 'Rabu', '07:00:00', '09:30:00', 'REST API', 'Pak Nanda', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 22' LIMIT 1), 'XI RPL 2', '2', 'Rabu', '10:00:00', '12:30:00', 'Version Control', 'Bu Oki', 'aktif');

-- XI RPL 2 - Kamis
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 1' LIMIT 1), 'XI RPL 2', '1', 'Kamis', '07:00:00', '09:30:00', 'Cloud Computing', 'Pak Pandu', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Ruang 3' LIMIT 1), 'XI RPL 2', '2', 'Kamis', '10:00:00', '12:30:00', 'Kewirausahaan', 'Bu Qori', 'aktif');

-- XI RPL 2 - Jumat
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'HALL' LIMIT 1), 'XI RPL 2', '1', 'Jumat', '07:00:00', '09:30:00', 'Seminar IT', 'Pak Budi', 'aktif');

-- ========================================
-- JADWAL KELAS XII
-- ========================================

-- XII RPL 1 - Senin
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 2' LIMIT 1), 'XII RPL 1', '1', 'Senin', '07:00:00', '09:30:00', 'Project Akhir', 'Pak Rahman', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 3' LIMIT 1), 'XII RPL 1', '2', 'Senin', '10:00:00', '12:30:00', 'Bimbingan PKL', 'Bu Sarah', 'aktif');

-- XII RPL 1 - Selasa
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 4' LIMIT 1), 'XII RPL 1', '1', 'Selasa', '07:00:00', '09:30:00', 'DevOps', 'Pak Tono', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 5' LIMIT 1), 'XII RPL 1', '2', 'Selasa', '10:00:00', '12:30:00', 'Security Programming', 'Bu Umi', 'aktif');

-- XII RPL 1 - Rabu
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 6' LIMIT 1), 'XII RPL 1', '1', 'Rabu', '07:00:00', '09:30:00', 'Microservices', 'Pak Vino', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Ruang 1' LIMIT 1), 'XII RPL 1', '2', 'Rabu', '10:00:00', '12:30:00', 'Proposal Project', 'Bu Wati', 'aktif');

-- XII RPL 1 - Kamis
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 7' LIMIT 1), 'XII RPL 1', '1', 'Kamis', '07:00:00', '09:30:00', 'Blockchain', 'Pak Xaverius', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 8' LIMIT 1), 'XII RPL 1', '2', 'Kamis', '10:00:00', '12:30:00', 'AI Programming', 'Bu Yuli', 'aktif');

-- XII RPL 1 - Jumat
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'HALL' LIMIT 1), 'XII RPL 1', '1', 'Jumat', '07:00:00', '09:30:00', 'Defense Project', 'Pak Budi', 'aktif');

-- ========================================
-- XII RPL 2
-- ========================================

-- XII RPL 2 - Senin
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 9' LIMIT 1), 'XII RPL 2', '1', 'Senin', '07:00:00', '09:30:00', 'Project Capstone', 'Pak Zainal', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 10' LIMIT 1), 'XII RPL 2', '2', 'Senin', '10:00:00', '12:30:00', 'Magang Industri', 'Bu Ayu', 'aktif');

-- XII RPL 2 - Selasa
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 11' LIMIT 1), 'XII RPL 2', '1', 'Selasa', '07:00:00', '09:30:00', 'Docker & Kubernetes', 'Pak Beni', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 12' LIMIT 1), 'XII RPL 2', '2', 'Selasa', '10:00:00', '12:30:00', 'Cyber Security', 'Bu Citra', 'aktif');

-- XII RPL 2 - Rabu
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 13' LIMIT 1), 'XII RPL 2', '1', 'Rabu', '07:00:00', '09:30:00', 'GraphQL', 'Pak Dani', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Ruang 2' LIMIT 1), 'XII RPL 2', '2', 'Rabu', '10:00:00', '12:30:00', 'Business Plan', 'Bu Elsa', 'aktif');

-- XII RPL 2 - Kamis
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 14' LIMIT 1), 'XII RPL 2', '1', 'Kamis', '07:00:00', '09:30:00', 'Machine Learning', 'Pak Fajar', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 15' LIMIT 1), 'XII RPL 2', '2', 'Kamis', '10:00:00', '12:30:00', 'IoT Development', 'Bu Gita', 'aktif');

-- XII RPL 2 - Jumat
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'HALL' LIMIT 1), 'XII RPL 2', '1', 'Jumat', '07:00:00', '09:30:00', 'Ujian Praktik', 'Pak Agus', 'aktif');

-- ========================================
-- JADWAL EKSTRAKURIKULER / KEGIATAN UMUM
-- ========================================

-- Sabtu - Kegiatan Ekstra
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 16' LIMIT 1), 'UMUM', '1', 'Sabtu', '07:00:00', '09:30:00', 'Workshop Web Development', 'Pak Budi', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab 17' LIMIT 1), 'UMUM', '1', 'Sabtu', '07:00:00', '09:30:00', 'Kelas Programming', 'Bu Ani', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'HALL' LIMIT 1), 'UMUM', '1', 'Sabtu', '07:00:00', '12:00:00', 'Rapat Guru', 'Kepala Sekolah', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lapangan Olahraga' LIMIT 1), 'UMUM', '1', 'Sabtu', '07:00:00', '09:30:00', 'Olahraga Pagi', 'Pak Hendra', 'aktif');

-- Tambahan jadwal Lab. IPA dan Lab. PB
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab. IPA' LIMIT 1), 'X RPL 2', '1', 'Rabu', '07:00:00', '09:30:00', 'Praktikum Kimia', 'Bu Sari', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab. IPA' LIMIT 1), 'XI RPL 1', '2', 'Rabu', '10:00:00', '12:30:00', 'Praktikum Biologi', 'Pak Dodi', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab. PB' LIMIT 1), 'XII RPL 1', '1', 'Rabu', '07:00:00', '09:30:00', 'Bahasa Inggris Conversation', 'Ms. Jane', 'aktif'),
((SELECT id FROM ruangan WHERE nama_ruangan = 'Lab. PB' LIMIT 1), 'XII RPL 2', '2', 'Rabu', '10:00:00', '12:30:00', 'TOEFL Preparation', 'Mr. John', 'aktif');

-- Minggu - Kegiatan Khusus (opsional, biasanya libur)
INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, status) VALUES
((SELECT id FROM ruangan WHERE nama_ruangan = 'HALL' LIMIT 1), 'UMUM', '1', 'Minggu', '07:00:00', '12:00:00', 'Upacara Bendera', 'Pembina', 'nonaktif');

-- Pesan sukses
SELECT 'Jadwal berhasil diisi! Total jadwal:' AS Status, COUNT(*) AS 'Jumlah Jadwal' FROM jadwal_ruangan;
SELECT 'Breakdown per kelas:' AS Info;
SELECT kelas, COUNT(*) as 'Jumlah Jadwal' FROM jadwal_ruangan GROUP BY kelas ORDER BY kelas;
