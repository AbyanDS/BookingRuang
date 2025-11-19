-- Update tabel password_reset_requests untuk sistem reset password baru
-- Jalankan query ini di phpMyAdmin atau MySQL client

-- Tambah kolom jika belum ada
ALTER TABLE `password_reset_requests` 
ADD COLUMN IF NOT EXISTS `request_email` VARCHAR(255) NULL AFTER `user_id`,
ADD COLUMN IF NOT EXISTS `reset_token` VARCHAR(64) NULL AFTER `approved_date`,
ADD COLUMN IF NOT EXISTS `token_expiry` DATETIME NULL AFTER `reset_token`;

-- Update keterangan column untuk menampung alasan user
ALTER TABLE `password_reset_requests` 
MODIFY COLUMN `keterangan` TEXT NULL COMMENT 'Alasan reset password dari user atau alasan reject dari admin';

-- Tambah index untuk performa
ALTER TABLE `password_reset_requests`
ADD INDEX IF NOT EXISTS `idx_reset_token` (`reset_token`),
ADD INDEX IF NOT EXISTS `idx_token_expiry` (`token_expiry`),
ADD INDEX IF NOT EXISTS `idx_status` (`status`);

-- Update status enum jika perlu (untuk completed status)
ALTER TABLE `password_reset_requests` 
MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending';
