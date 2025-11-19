<?php
/**
 * Test File untuk Verifikasi Auto-Display Booking di Jadwal
 */
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Auto-Display Booking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        .section {
            margin: 30px 0;
            padding: 20px;
            background: #ecf0f1;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }
        .success {
            color: #27ae60;
            font-weight: bold;
        }
        .warning {
            color: #e67e22;
            font-weight: bold;
        }
        .error {
            color: #e74c3c;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #34495e;
            color: white;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-pending {
            background: #ffc107;
            color: white;
        }
        .badge-approved {
            background: #4caf50;
            color: white;
        }
        .badge-completed {
            background: #9e9e9e;
            color: white;
        }
        .badge-cancelled {
            background: #f44336;
            color: white;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .btn:hover {
            background: #2980b9;
        }
        .btn-danger {
            background: #e74c3c;
        }
        .btn-danger:hover {
            background: #c0392b;
        }
        .btn-success {
            background: #27ae60;
        }
        .btn-success:hover {
            background: #229954;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Auto-Display Booking di Jadwal Ruangan</h1>
        
        <!-- Test 1: Query Booking Aktif -->
        <div class="section">
            <h2>📋 Test 1: Booking Aktif (akan muncul di jadwal)</h2>
            <?php
            $today = date('Y-m-d');
            $query_active = "SELECT 
                                b.id,
                                b.tanggal_booking,
                                DATE_FORMAT(b.waktu_mulai, '%H:%i') as waktu_mulai,
                                DATE_FORMAT(b.waktu_selesai, '%H:%i') as waktu_selesai,
                                b.keperluan,
                                b.status,
                                r.nama_ruangan,
                                u.nama_lengkap,
                                CASE DAYOFWEEK(b.tanggal_booking)
                                    WHEN 1 THEN 'Minggu'
                                    WHEN 2 THEN 'Senin'
                                    WHEN 3 THEN 'Selasa'
                                    WHEN 4 THEN 'Rabu'
                                    WHEN 5 THEN 'Kamis'
                                    WHEN 6 THEN 'Jumat'
                                    WHEN 7 THEN 'Sabtu'
                                END as hari
                            FROM booking b
                            JOIN ruangan r ON b.ruangan_id = r.id
                            JOIN users u ON b.user_id = u.id
                            WHERE b.status IN ('pending', 'approved')
                              AND b.tanggal_booking >= '$today'
                            ORDER BY b.tanggal_booking ASC, b.waktu_mulai ASC
                            LIMIT 10";
            
            $result_active = mysqli_query($conn, $query_active);
            $count_active = mysqli_num_rows($result_active);
            
            if ($count_active > 0) {
                echo "<p class='success'>✓ Ditemukan $count_active booking aktif</p>";
                echo "<table>";
                echo "<tr><th>ID</th><th>Hari</th><th>Tanggal</th><th>Waktu</th><th>Ruangan</th><th>Keperluan</th><th>User</th><th>Status</th></tr>";
                
                while ($row = mysqli_fetch_assoc($result_active)) {
                    $badge_class = 'badge-' . $row['status'];
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td><strong>{$row['hari']}</strong></td>";
                    echo "<td>" . date('d/m/Y', strtotime($row['tanggal_booking'])) . "</td>";
                    echo "<td>{$row['waktu_mulai']} - {$row['waktu_selesai']}</td>";
                    echo "<td>{$row['nama_ruangan']}</td>";
                    echo "<td>{$row['keperluan']}</td>";
                    echo "<td>{$row['nama_lengkap']}</td>";
                    echo "<td><span class='badge $badge_class'>{$row['status']}</span></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='warning'>⚠ Tidak ada booking aktif (pending/approved) untuk hari ini dan kedepan</p>";
            }
            ?>
        </div>
        
        <!-- Test 2: Booking Expired (akan auto-completed) -->
        <div class="section">
            <h2>⏰ Test 2: Booking Expired (perlu auto-complete)</h2>
            <?php
            $now = date('H:i:s');
            $query_expired = "SELECT 
                                b.id,
                                b.tanggal_booking,
                                DATE_FORMAT(b.waktu_mulai, '%H:%i') as waktu_mulai,
                                DATE_FORMAT(b.waktu_selesai, '%H:%i') as waktu_selesai,
                                b.keperluan,
                                b.status,
                                r.nama_ruangan,
                                u.nama_lengkap
                            FROM booking b
                            JOIN ruangan r ON b.ruangan_id = r.id
                            JOIN users u ON b.user_id = u.id
                            WHERE b.status IN ('pending', 'approved')
                              AND (
                                  b.tanggal_booking < '$today'
                                  OR (b.tanggal_booking = '$today' AND b.waktu_selesai < '$now')
                              )
                            ORDER BY b.tanggal_booking DESC
                            LIMIT 10";
            
            $result_expired = mysqli_query($conn, $query_expired);
            $count_expired = mysqli_num_rows($result_expired);
            
            if ($count_expired > 0) {
                echo "<p class='warning'>⚠ Ditemukan $count_expired booking expired (akan di-auto-complete)</p>";
                echo "<table>";
                echo "<tr><th>ID</th><th>Tanggal</th><th>Waktu</th><th>Ruangan</th><th>Keperluan</th><th>User</th><th>Status</th></tr>";
                
                while ($row = mysqli_fetch_assoc($result_expired)) {
                    $badge_class = 'badge-' . $row['status'];
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>" . date('d/m/Y', strtotime($row['tanggal_booking'])) . "</td>";
                    echo "<td>{$row['waktu_mulai']} - {$row['waktu_selesai']}</td>";
                    echo "<td>{$row['nama_ruangan']}</td>";
                    echo "<td>{$row['keperluan']}</td>";
                    echo "<td>{$row['nama_lengkap']}</td>";
                    echo "<td><span class='badge $badge_class'>{$row['status']}</span></td>";
                    echo "</tr>";
                }
                echo "</table>";
                echo "<p><a href='?auto_complete=1' class='btn btn-success'>🔄 Auto-Complete Sekarang</a></p>";
            } else {
                echo "<p class='success'>✓ Tidak ada booking expired. Semua booking up-to-date!</p>";
            }
            ?>
        </div>
        
        <!-- Test 3: Statistik -->
        <div class="section">
            <h2>📊 Test 3: Statistik Booking</h2>
            <?php
            $stats_query = "SELECT 
                                status,
                                COUNT(*) as count
                            FROM booking
                            GROUP BY status";
            $stats_result = mysqli_query($conn, $stats_query);
            
            echo "<table>";
            echo "<tr><th>Status</th><th>Jumlah</th></tr>";
            
            while ($row = mysqli_fetch_assoc($stats_result)) {
                $badge_class = 'badge-' . $row['status'];
                echo "<tr>";
                echo "<td><span class='badge $badge_class'>{$row['status']}</span></td>";
                echo "<td><strong>{$row['count']}</strong></td>";
                echo "</tr>";
            }
            echo "</table>";
            ?>
        </div>
        
        <!-- Test 4: Preview Jadwal View -->
        <div class="section">
            <h2>👁️ Test 4: Preview Jadwal untuk Hari Ini</h2>
            <?php
            $today_name = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][date('w')];
            $today_daynum = date('w') + 1; // DAYOFWEEK format
            if ($today_daynum == 8) $today_daynum = 1; // Sunday correction
            
            echo "<p><strong>Hari ini:</strong> $today_name (" . date('d/m/Y') . ")</p>";
            
            // Query jadwal tetap
            $query_jadwal_today = "SELECT 
                                      j.waktu_mulai,
                                      j.waktu_selesai,
                                      j.kegiatan,
                                      r.nama_ruangan,
                                      'Jadwal Tetap' as tipe
                                  FROM jadwal_ruangan j
                                  JOIN ruangan r ON j.ruangan_id = r.id
                                  WHERE j.hari = '$today_name'
                                    AND j.status = 'aktif'";
            
            // Query booking hari ini
            $query_booking_today = "SELECT 
                                      DATE_FORMAT(b.waktu_mulai, '%H:%i:%s') as waktu_mulai,
                                      DATE_FORMAT(b.waktu_selesai, '%H:%i:%s') as waktu_selesai,
                                      b.keperluan as kegiatan,
                                      r.nama_ruangan,
                                      CONCAT('Booking (', b.status, ')') as tipe
                                  FROM booking b
                                  JOIN ruangan r ON b.ruangan_id = r.id
                                  WHERE b.status IN ('pending', 'approved')
                                    AND b.tanggal_booking = '$today'";
            
            // Gabungkan
            $query_combined = "($query_jadwal_today) UNION ($query_booking_today) ORDER BY waktu_mulai ASC";
            $result_combined = mysqli_query($conn, $query_combined);
            $count_combined = mysqli_num_rows($result_combined);
            
            if ($count_combined > 0) {
                echo "<p class='success'>✓ Total $count_combined kegiatan/booking untuk hari ini</p>";
                echo "<table>";
                echo "<tr><th>Waktu</th><th>Kegiatan</th><th>Ruangan</th><th>Tipe</th></tr>";
                
                while ($row = mysqli_fetch_assoc($result_combined)) {
                    $is_booking = strpos($row['tipe'], 'Booking') !== false;
                    $row_style = $is_booking ? "style='background: #fff9e6;'" : "";
                    
                    echo "<tr $row_style>";
                    echo "<td>" . format_waktu($row['waktu_mulai']) . " - " . format_waktu($row['waktu_selesai']) . "</td>";
                    echo "<td>{$row['kegiatan']}</td>";
                    echo "<td>{$row['nama_ruangan']}</td>";
                    echo "<td><strong>{$row['tipe']}</strong></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='warning'>⚠ Tidak ada jadwal/booking untuk hari ini</p>";
            }
            ?>
        </div>
        
        <!-- Actions -->
        <div class="section">
            <h2>🔧 Actions</h2>
            <a href="../jadwal_view.php" class="btn">📅 Lihat Jadwal Ruangan</a>
            <a href="../config/auto_cleanup_completed_bookings.php" class="btn btn-success">🧹 Run Cleanup Manual</a>
            <a href="?" class="btn" style="background: #95a5a6;">🔄 Refresh Test</a>
        </div>
        
        <?php
        // Auto-complete action
        if (isset($_GET['auto_complete']) && $_GET['auto_complete'] == '1') {
            $cleanup_query = "UPDATE booking 
                              SET status = 'completed',
                                  updated_at = NOW()
                              WHERE status IN ('approved', 'pending')
                                AND (
                                    tanggal_booking < '$today'
                                    OR (tanggal_booking = '$today' AND waktu_selesai < '$now')
                                )";
            
            if (mysqli_query($conn, $cleanup_query)) {
                $affected = mysqli_affected_rows($conn);
                echo "<div class='section' style='background: #d4edda; border-color: #27ae60;'>";
                echo "<p class='success'>✓ Auto-complete berhasil! $affected booking di-update menjadi 'completed'</p>";
                echo "<p><a href='?' class='btn'>Refresh untuk lihat hasil</a></p>";
                echo "</div>";
            } else {
                echo "<div class='section' style='background: #f8d7da; border-color: #e74c3c;'>";
                echo "<p class='error'>✗ Error: " . mysqli_error($conn) . "</p>";
                echo "</div>";
            }
        }
        ?>
    </div>
</body>
</html>
