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
echo "<tr><th>Variable</th><th>Value</th><th>Status</th></tr>";

$env_vars = [
    'RAILWAY_ENVIRONMENT',
    'MYSQL_HOST',
    'MYSQLHOST',
    'MYSQL_USER',
    'MYSQLUSER',
    'MYSQL_PASSWORD',
    'MYSQLPASSWORD',
    'MYSQL_DATABASE',
    'MYSQLDATABASE',
    'MYSQL_PORT',
    'MYSQLPORT',
    'DATABASE_URL'
];

foreach ($env_vars as $var) {
    $value = getenv($var);
    if ($value === false) {
        $display = '<span style="color:red;">Not Set</span>';
        $status = '❌';
    } else {
        $display = ($var === 'MYSQL_PASSWORD' || $var === 'MYSQLPASSWORD') ? '***hidden***' : $value;
        $status = '✅';
    }
    echo "<tr><td><strong>$var</strong></td><td>$display</td><td>$status</td></tr>";
}
echo "</table>";

// Deteksi environment
$is_railway = getenv('RAILWAY_ENVIRONMENT') !== false || getenv('MYSQL_HOST') !== false;
echo "<p><strong>Environment Detected:</strong> " . ($is_railway ? "☁️ Railway Cloud" : "💻 Local Development") . "</p>";

// Cek koneksi database
echo "<h3>2. Database Connection Test</h3>";

$host = getenv('MYSQL_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQL_USER') ?: getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQL_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';
$db = getenv('MYSQL_DATABASE') ?: getenv('MYSQLDATABASE') ?: 'booking_ruangan';
$port = getenv('MYSQL_PORT') ?: getenv('MYSQLPORT') ?: '3306';

echo "<p><strong>Connecting to:</strong> $user@$host:$port/$db</p>";

// Test koneksi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect($host, $user, $pass, $db, $port);
    
    echo "<div style='background:#d4edda; padding:10px; border:1px solid #c3e6cb; color:#155724;'>";
    echo "✅ <strong>SUCCESS!</strong> Database connection established.";
    echo "</div>";
    
    // Test query
    echo "<h3>3. Database Tables Test</h3>";
    $tables = ['users', 'ruangan', 'booking', 'jadwal_ruangan', 'history_peminjaman'];
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Table</th><th>Status</th><th>Row Count</th></tr>";
    
    foreach ($tables as $table) {
        try {
            $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM $table");
            if ($result) {
                $row = mysqli_fetch_assoc($result);
                $count = $row['count'];
                echo "<tr><td>$table</td><td style='color:green;'>✅ Exists</td><td>$count rows</td></tr>";
            }
        } catch (mysqli_sql_exception $e) {
            echo "<tr><td>$table</td><td style='color:red;'>❌ Not Found</td><td>" . $e->getMessage() . "</td></tr>";
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
    
} catch (mysqli_sql_exception $e) {
    echo "<div style='background:#f8d7da; padding:10px; border:1px solid #f5c6cb; color:#721c24;'>";
    echo "❌ <strong>FAILED!</strong> Cannot connect to database.<br>";
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Error Code:</strong> " . $e->getCode();
    echo "</div>";
    
    echo "<h3>🔍 Debug Information</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><td><strong>DB_HOST</strong></td><td>$host</td></tr>";
    echo "<tr><td><strong>DB_USER</strong></td><td>$user</td></tr>";
    echo "<tr><td><strong>DB_NAME</strong></td><td>$db</td></tr>";
    echo "<tr><td><strong>DB_PORT</strong></td><td>$port</td></tr>";
    echo "<tr><td><strong>Password Set?</strong></td><td>" . (empty($pass) ? 'No (empty)' : 'Yes') . "</td></tr>";
    echo "</table>";
    
    echo "<h3>📋 Troubleshooting Steps:</h3>";
    echo "<ol>";
    echo "<li><strong>Check MySQL Service:</strong> Make sure MySQL service is running in Railway (green status)</li>";
    echo "<li><strong>Link Database:</strong> In Railway → PHP App → Variables → Add Reference → Select MySQL</li>";
    echo "<li><strong>Wait:</strong> Wait 1-2 minutes after linking for changes to take effect</li>";
    echo "<li><strong>Check Variables:</strong> Verify MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE are set</li>";
    echo "<li><strong>Redeploy:</strong> Try redeploying the application</li>";
    echo "<li><strong>Check Logs:</strong> Review Railway deployment logs for errors</li>";
    echo "</ol>";
    
    echo "<h3>🔗 Quick Fix Commands:</h3>";
    echo "<pre style='background:#f0f0f0; padding:10px;'>";
    echo "# In Railway Dashboard:\n";
    echo "1. Click MySQL service\n";
    echo "2. Go to 'Variables' tab\n";
    echo "3. Copy all MYSQL_* variables\n";
    echo "4. Click PHP App service\n";
    echo "5. Go to 'Variables' tab\n";
    echo "6. Click 'Add Reference' → Select MySQL\n";
    echo "</pre>";
}

echo "<hr>";
echo "<p><strong>⚠️ SECURITY WARNING:</strong> Delete this file after testing!</p>";
echo "<p><em>File: test_db_connection.php</em></p>";
?>

