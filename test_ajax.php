<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config/database.php';
require_once 'config/functions.php';

echo "Database connection: " . ($conn ? "OK" : "FAILED") . "<br>";
echo "Functions loaded: OK<br>";

// Test query
$query = "SELECT COUNT(*) as total FROM ruangan WHERE status = 'tersedia'";
$result = mysqli_query($conn, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "Total ruangan tersedia: " . $row['total'] . "<br>";
} else {
    echo "Query error: " . mysqli_error($conn) . "<br>";
}

echo "<br>Test AJAX filter:<br>";
$search = '';
$filter_kapasitas = '';
$filter_kategori = 'lab';

$where = array();
$where[] = "r.status = 'tersedia'";

if ($filter_kategori) {
    if ($filter_kategori == 'lab') {
        $where[] = "r.nama_ruangan LIKE '%Lab%'";
    }
}

$where_clause = implode(' AND ', $where);
$query = "SELECT DISTINCT r.* 
          FROM ruangan r 
          LEFT JOIN jadwal_ruangan j ON r.id = j.ruangan_id 
          WHERE $where_clause 
          ORDER BY r.nama_ruangan ASC";
          
echo "Query: " . $query . "<br><br>";

$result = mysqli_query($conn, $query);
if ($result) {
    $total = mysqli_num_rows($result);
    echo "Lab rooms found: " . $total . "<br>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "- " . $row['nama_ruangan'] . "<br>";
    }
} else {
    echo "Query error: " . mysqli_error($conn) . "<br>";
}
?>
