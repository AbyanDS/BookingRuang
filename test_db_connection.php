<?php
/**
 * Test Database Connection untuk Railway
 * File ini untuk test apakah koneksi database berhasil
 * HAPUS FILE INI setelah deployment berhasil untuk keamanan!
 */

// Set timezone
date_default_timezone_set('Asia/Jakarta');

echo "<h2>🔍 Railway Database Connection Test</h2>";
echo "<hr>";

// Cek environment variables
echo "<h3>1. Environment Variables</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Variable</th><th>Value</th></tr>";

$env_vars = [
    'MYSQL_HOST',
    'MYSQL_USER',
    'MYSQL_PASSWORD',
    'MYSQL_DATABASE',
    'MYSQL_PORT',
    'DATABASE_URL'
];

foreach ($env_vars as $var) {
    $value = getenv($var);
    $display = $value ? ($var === 'MYSQL_PASSWORD' ? '***hidden***' : $value) : '<span style="color:red;">Not Set</span>';
    echo "<tr><td><strong>$var</strong></td><td>$display</td></tr>";
}
echo "</table>";

// Cek koneksi database
echo "<h3>2. Database Connection Test</h3>";

$host = getenv('MYSQL_HOST') ?: 'localhost';
$user = getenv('MYSQL_USER') ?: 'root';
$pass = getenv('MYSQL_PASSWORD') ?: '';
$db = getenv('MYSQL_DATABASE') ?: 'booking_ruangan';
$port = getenv('MYSQL_PORT') ?: '3306';

echo "<p><strong>Connecting to:</strong> $user@$host:$port/$db</p>";

// Test koneksi
$conn = @mysqli_connect($host, $user, $pass, $db, $port);

if ($conn) {
    echo "<div style='background:#d4edda; padding:10px; border:1px solid #c3e6cb; color:#155724;'>";
    echo "✅ <strong>SUCCESS!</strong> Database connection established.";
    echo "</div>";
    
    // Test query
    echo "<h3>3. Database Tables Test</h3>";
    $tables = ['users', 'ruangan', 'booking', 'jadwal_ruangan', 'history_peminjaman'];
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Table</th><th>Status</th><th>Row Count</th></tr>";
    
    foreach ($tables as $table) {
        $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM $table");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $count = $row['count'];
            echo "<tr><td>$table</td><td style='color:green;'>✅ Exists</td><td>$count rows</td></tr>";
        } else {
            echo "<tr><td>$table</td><td style='color:red;'>❌ Not Found</td><td>-</td></tr>";
        }
    }
    echo "</table>";
    
    // PHP Info
    echo "<h3>4. PHP Configuration</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Setting</th><th>Value</th></tr>";
    echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";
    echo "<tr><td>Upload Max Size</td><td>" . ini_get('upload_max_filesize') . "</td></tr>";
    echo "<tr><td>Post Max Size</td><td>" . ini_get('post_max_size') . "</td></tr>";
    echo "<tr><td>Max Execution Time</td><td>" . ini_get('max_execution_time') . "s</td></tr>";
    echo "<tr><td>Memory Limit</td><td>" . ini_get('memory_limit') . "</td></tr>";
    echo "<tr><td>MySQLi Extension</td><td>" . (extension_loaded('mysqli') ? '✅ Loaded' : '❌ Not Loaded') . "</td></tr>";
    echo "<tr><td>PDO Extension</td><td>" . (extension_loaded('pdo') ? '✅ Loaded' : '❌ Not Loaded') . "</td></tr>";
    echo "</table>";
    
    mysqli_close($conn);
} else {
    echo "<div style='background:#f8d7da; padding:10px; border:1px solid #f5c6cb; color:#721c24;'>";
    echo "❌ <strong>FAILED!</strong> Cannot connect to database.<br>";
    echo "<strong>Error:</strong> " . mysqli_connect_error();
    echo "</div>";
    
    echo "<h3>Troubleshooting Steps:</h3>";
    echo "<ol>";
    echo "<li>Make sure MySQL service is running in Railway</li>";
    echo "<li>Check if environment variables are properly linked</li>";
    echo "<li>In Railway: Go to PHP App → Variables → Add Reference → Select MySQL</li>";
    echo "<li>Wait 1-2 minutes after linking for changes to take effect</li>";
    echo "<li>Check Railway deployment logs for errors</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p><strong>⚠️ SECURITY WARNING:</strong> Delete this file after testing!</p>";
echo "<p><em>File: test_db_connection.php</em></p>";
?>
