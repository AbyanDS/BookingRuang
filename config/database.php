<?php
// Set timezone ke Asia/Jakarta untuk konsistensi waktu
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi Database dengan support environment variables (untuk Railway/Cloud)
// Gunakan getenv() untuk membaca environment variables, fallback ke default local

// Deteksi apakah running di Railway (cek keberadaan RAILWAY_ENVIRONMENT)
$is_railway = getenv('RAILWAY_ENVIRONMENT') !== false || getenv('MYSQL_HOST') !== false;

// Set database credentials
if ($is_railway) {
    // Railway environment - gunakan environment variables
    define('DB_HOST', getenv('MYSQL_HOST') ?: getenv('MYSQLHOST') ?: 'localhost');
    define('DB_USER', getenv('MYSQL_USER') ?: getenv('MYSQLUSER') ?: 'root');
    define('DB_PASS', getenv('MYSQL_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '');
    define('DB_NAME', getenv('MYSQL_DATABASE') ?: getenv('MYSQLDATABASE') ?: 'booking_ruangan');
    define('DB_PORT', getenv('MYSQL_PORT') ?: getenv('MYSQLPORT') ?: '3306');
} else {
    // Local development environment
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'booking_ruangan');
    define('DB_PORT', '3306');
}

// Koneksi ke database dengan port
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    // Set charset ke utf8
    mysqli_set_charset($conn, "utf8");
    
} catch (mysqli_sql_exception $e) {
    // Log error untuk debugging
    error_log("Database Connection Error: " . $e->getMessage());
    error_log("DB_HOST: " . DB_HOST);
    error_log("DB_USER: " . DB_USER);
    error_log("DB_NAME: " . DB_NAME);
    error_log("DB_PORT: " . DB_PORT);
    
    // User-friendly error message
    die("Koneksi database gagal. Pastikan MySQL service sudah running dan linked dengan aplikasi. Error: " . $e->getMessage());
}
?>
