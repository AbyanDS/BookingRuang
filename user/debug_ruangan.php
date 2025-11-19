<?php
require_once '../config/database.php';

echo "<h2>Debug: Check Ruangan Data</h2>";

// Check all ruangan
$query = "SELECT * FROM ruangan ORDER BY id ASC";
$result = mysqli_query($conn, $query);

echo "<h3>Total Ruangan: " . mysqli_num_rows($result) . "</h3>";

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Nama</th><th>Lokasi</th><th>Kapasitas</th><th>Status</th><th>Created At</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['nama_ruangan']}</td>";
    echo "<td>{$row['lokasi']}</td>";
    echo "<td>{$row['kapasitas']}</td>";
    echo "<td>{$row['status']}</td>";
    echo "<td>{$row['created_at']}</td>";
    echo "</tr>";
}

echo "</table>";

// Check foreign key constraint
echo "<h3>Check Foreign Key Constraint</h3>";
$fk_query = "SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'booking_ruangan'
    AND TABLE_NAME = 'booking'
    AND REFERENCED_TABLE_NAME IS NOT NULL";

$fk_result = mysqli_query($conn, $fk_query);

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Constraint</th><th>Table</th><th>Column</th><th>Referenced Table</th><th>Referenced Column</th></tr>";

while ($row = mysqli_fetch_assoc($fk_result)) {
    echo "<tr>";
    echo "<td>{$row['CONSTRAINT_NAME']}</td>";
    echo "<td>{$row['TABLE_NAME']}</td>";
    echo "<td>{$row['COLUMN_NAME']}</td>";
    echo "<td>{$row['REFERENCED_TABLE_NAME']}</td>";
    echo "<td>{$row['REFERENCED_COLUMN_NAME']}</td>";
    echo "</tr>";
}

echo "</table>";

mysqli_close($conn);
?>
