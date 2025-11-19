<?php
// Set timezone ke Asia/Jakarta untuk konsistensi waktu
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi Database dengan support environment variables (untuk Railway/Cloud)
// Gunakan getenv() untuk membaca environment variables, fallback ke default local
define('DB_HOST', getenv('MYSQL_HOST') ?: 'localhost');
define('DB_USER', getenv('MYSQL_USER') ?: 'root');
define('DB_PASS', getenv('MYSQL_PASSWORD') ?: '');
define('DB_NAME', getenv('MYSQL_DATABASE') ?: 'booking_ruangan');
define('DB_PORT', getenv('MYSQL_PORT') ?: '3306');

// Koneksi ke database dengan port
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Cek koneksi
if (!$conn) {
    error_log("Koneksi database gagal: " . mysqli_connect_error());
    die("Koneksi database gagal. Silakan hubungi administrator.");
}

// Set charset ke utf8
mysqli_set_charset($conn, "utf8");
?>
