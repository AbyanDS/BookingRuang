<?php
date_default_timezone_set('Asia/Jakarta');

// Deteksi lingkungan Railway
$isRailway = getenv('RAILWAY_ENVIRONMENT') || getenv('MYSQLHOST');

// Konfigurasi database
if ($isRailway) {
    define('DB_HOST', getenv('metro.proxy.rlwy.net'));
    define('DB_USER', getenv('root'));
    define('DB_PASS', getenv('MClfPGdILKmmIxLDjwgZesirmxvnXqiQ'));
    define('DB_NAME', getenv('railway'));
    define('DB_PORT', getenv('39338'));
} else {
    // Localhost
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'booking_ruangan');
    define('DB_PORT', 3306);
}

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    mysqli_set_charset($conn, "utf8");

} catch (Exception $e) {
    error_log("DB Error: " . $e->getMessage());
    error_log("Host: " . DB_HOST);
    error_log("User: " . DB_USER);
    error_log("DB: " . DB_NAME);
    die("Koneksi database gagal. Error: " . $e->getMessage());
}
?>
