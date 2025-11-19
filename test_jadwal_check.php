<?php
require_once 'config/database.php';
require_once 'config/functions.php';

echo "<h2>Debug: Jadwal UNION Query Test</h2>";
echo "<style>
body { font-family: Arial; padding: 20px; }
table { border-collapse: collapse; width: 100%; margin: 20px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background: #4CAF50; color: white; }
.error { background: #ffebee; padding: 10px; border-left: 4px solid #f44336; margin: 10px 0; }
.success { background: #e8f5e9; padding: 10px; border-left: 4px solid #4CAF50; margin: 10px 0; }
.query-box { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 15px 0; font-family: monospace; }
</style>";

$today = date('Y-m-d');
$hari = 'Senin';
$hari_numeric = ['Minggu' => 1, 'Senin' => 2, 'Selasa' => 3, 'Rabu' => 4, 'Kamis' => 5, 'Jumat' => 6, 'Sabtu' => 7];
$day_num = $hari_numeric[$hari];

// Test 1: Query Jadwal Tetap
echo "<h3>Test 1: Query Jadwal Tetap (Senin)</h3>";
$query_jadwal = "SELECT 
                    j.waktu_mulai,
                    j.waktu_selesai,
                    j.kegiatan,
                    j.keterangan,
                    r.nama_ruangan,
                    r.lokasi,
                    j.penanggung_jawab,
                    'jadwal' as source_type,
                    NULL as tanggal_booking,
                    NULL as booking_status
                 FROM jadwal_ruangan j 
                 JOIN ruangan r ON j.ruangan_id = r.id 
                 WHERE j.hari = '$hari' AND j.status = 'aktif'";

echo "<div class='query-box'>" . nl2br(htmlspecialchars($query_jadwal)) . "</div>";

$result = mysqli_query($conn, $query_jadwal);
if (!$result) {
    echo "<div class='error'>❌ Error: " . mysqli_error($conn) . "</div>";
} else {
    $count = mysqli_num_rows($result);
    echo "<div class='success'>✓ Query berhasil! Ditemukan: $count rows</div>";
    
    if ($count > 0) {
        echo "<table>";
        echo "<tr><th>Waktu</th><th>Kegiatan</th><th>Ruangan</th><th>Lokasi</th><th>PIC</th><th>Source</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>{$row['waktu_mulai']} - {$row['waktu_selesai']}</td>";
            echo "<td>{$row['kegiatan']}</td>";
            echo "<td>{$row['nama_ruangan']}</td>";
            echo "<td>{$row['lokasi']}</td>";
            echo "<td>" . ($row['penanggung_jawab'] ?: '-') . "</td>";
            echo "<td><strong>{$row['source_type']}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

// Test 2: Query Booking
echo "<h3>Test 2: Query Booking (DAYOFWEEK = $day_num untuk $hari)</h3>";
$query_booking = "SELECT 
                    TIME_FORMAT(b.waktu_mulai, '%H:%i:%s') as waktu_mulai,
                    TIME_FORMAT(b.waktu_selesai, '%H:%i:%s') as waktu_selesai,
                    b.keperluan as kegiatan,
                    b.keterangan,
                    r.nama_ruangan,
                    r.lokasi,
                    u.nama_lengkap as penanggung_jawab,
                    'booking' as source_type,
                    b.tanggal_booking,
                    b.status as booking_status
                  FROM booking b
                  JOIN ruangan r ON b.ruangan_id = r.id
                  JOIN users u ON b.user_id = u.id
                  WHERE b.status IN ('pending', 'approved')
                    AND b.tanggal_booking >= '$today'
                    AND DAYOFWEEK(b.tanggal_booking) = $day_num";

echo "<div class='query-box'>" . nl2br(htmlspecialchars($query_booking)) . "</div>";

$result = mysqli_query($conn, $query_booking);
if (!$result) {
    echo "<div class='error'>❌ Error: " . mysqli_error($conn) . "</div>";
} else {
    $count = mysqli_num_rows($result);
    echo "<div class='success'>✓ Query berhasil! Ditemukan: $count rows</div>";
    
    if ($count > 0) {
        echo "<table>";
        echo "<tr><th>Tanggal</th><th>Waktu</th><th>Kegiatan</th><th>Ruangan</th><th>User</th><th>Status</th><th>Source</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr style='background: #fff9e6;'>";
            echo "<td>" . date('d/m/Y (l)', strtotime($row['tanggal_booking'])) . "</td>";
            echo "<td>{$row['waktu_mulai']} - {$row['waktu_selesai']}</td>";
            echo "<td>{$row['kegiatan']}</td>";
            echo "<td>{$row['nama_ruangan']}</td>";
            echo "<td>{$row['penanggung_jawab']}</td>";
            echo "<td><span style='background: #ffc107; padding: 3px 8px; border-radius: 3px;'>{$row['booking_status']}</span></td>";
            echo "<td><strong>{$row['source_type']}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Tidak ada booking untuk hari $hari (DAYOFWEEK=$day_num) >= $today dengan status pending/approved</p>";
    }
}

// Test 3: UNION Query
echo "<h3>Test 3: UNION ALL Query (Gabungan)</h3>";
$query_union = "($query_jadwal) UNION ALL ($query_booking) ORDER BY waktu_mulai ASC";

echo "<div class='query-box'>" . nl2br(htmlspecialchars($query_union)) . "</div>";

$result = mysqli_query($conn, $query_union);
if (!$result) {
    echo "<div class='error'>❌ UNION Error: " . mysqli_error($conn) . "</div>";
    echo "<p><strong>Kemungkinan penyebab:</strong></p>";
    echo "<ul>";
    echo "<li>Jumlah kolom tidak sama antara kedua SELECT</li>";
    echo "<li>Tipe data kolom tidak compatible</li>";
    echo "<li>Format waktu berbeda (TIME vs VARCHAR)</li>";
    echo "</ul>";
} else {
    $count = mysqli_num_rows($result);
    echo "<div class='success'>✓ UNION Query berhasil! Total: $count rows</div>";
    
    if ($count > 0) {
        echo "<table>";
        echo "<tr><th>Waktu</th><th>Kegiatan</th><th>Ruangan</th><th>PIC</th><th>Source</th><th>Tanggal Booking</th><th>Status</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            $bg = $row['source_type'] == 'booking' ? 'background: #fff9e6;' : '';
            echo "<tr style='$bg'>";
            echo "<td>{$row['waktu_mulai']} - {$row['waktu_selesai']}</td>";
            echo "<td>{$row['kegiatan']}</td>";
            echo "<td>{$row['nama_ruangan']}</td>";
            echo "<td>" . ($row['penanggung_jawab'] ?: '-') . "</td>";
            echo "<td><strong>{$row['source_type']}</strong></td>";
            echo "<td>" . ($row['tanggal_booking'] ? date('d/m/Y', strtotime($row['tanggal_booking'])) : '-') . "</td>";
            echo "<td>" . ($row['booking_status'] ?: '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

// Test 4: Check booking data
echo "<h3>Test 4: Semua Booking (pending/approved) >= Hari ini</h3>";
$query_all_booking = "SELECT 
                        b.*,
                        r.nama_ruangan,
                        u.nama_lengkap,
                        DAYOFWEEK(b.tanggal_booking) as day_num,
                        DAYNAME(b.tanggal_booking) as day_eng,
                        CASE DAYOFWEEK(b.tanggal_booking)
                            WHEN 1 THEN 'Minggu'
                            WHEN 2 THEN 'Senin'
                            WHEN 3 THEN 'Selasa'
                            WHEN 4 THEN 'Rabu'
                            WHEN 5 THEN 'Kamis'
                            WHEN 6 THEN 'Jumat'
                            WHEN 7 THEN 'Sabtu'
                        END as hari_indonesia
                      FROM booking b
                      JOIN ruangan r ON b.ruangan_id = r.id
                      JOIN users u ON b.user_id = u.id
                      WHERE b.status IN ('pending', 'approved')
                        AND b.tanggal_booking >= '$today'
                      ORDER BY b.tanggal_booking ASC";

$result = mysqli_query($conn, $query_all_booking);
$count = mysqli_num_rows($result);

echo "<p><strong>Total booking aktif:</strong> $count</p>";

if ($count > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Tanggal</th><th>DAYOFWEEK</th><th>Hari</th><th>Waktu</th><th>Ruangan</th><th>Keperluan</th><th>User</th><th>Status</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . date('d/m/Y', strtotime($row['tanggal_booking'])) . "</td>";
        echo "<td>{$row['day_num']}</td>";
        echo "<td><strong>{$row['hari_indonesia']}</strong></td>";
        echo "<td>{$row['waktu_mulai']} - {$row['waktu_selesai']}</td>";
        echo "<td>{$row['nama_ruangan']}</td>";
        echo "<td>{$row['keperluan']}</td>";
        echo "<td>{$row['nama_lengkap']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<p><a href='jadwal_view.php'>← Kembali ke Jadwal View</a> | <a href='ajax_jadwal.php?view=weekly'>Test AJAX Weekly</a> | <a href='ajax_jadwal.php?view=table'>Test AJAX Table</a></p>";

mysqli_close($conn);
?>
