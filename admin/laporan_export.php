<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

// Admin dan petugas bisa akses laporan
require_admin_or_petugas();

// Filter
$filter_bulan = isset($_GET['bulan']) ? clean_input($_GET['bulan']) : date('Y-m');
$filter_status = isset($_GET['status']) ? clean_input($_GET['status']) : 'all';
$filter_ruangan = isset($_GET['ruangan']) ? clean_input($_GET['ruangan']) : 'all';

// Query untuk laporan
$where = array();
$where[] = "DATE_FORMAT(b.tanggal_booking, '%Y-%m') = '$filter_bulan'";

if ($filter_status != 'all') {
    $where[] = "b.status = '$filter_status'";
}

if ($filter_ruangan != 'all') {
    $where[] = "b.ruangan_id = '$filter_ruangan'";
}

$where_clause = implode(' AND ', $where);

// Query laporan booking
$query = "SELECT b.*, r.nama_ruangan, u.nama_lengkap, u.email 
          FROM booking b 
          JOIN ruangan r ON b.ruangan_id = r.id 
          JOIN users u ON b.user_id = u.id 
          WHERE $where_clause
          ORDER BY b.tanggal_booking DESC, b.waktu_mulai DESC";
$result = mysqli_query($conn, $query);

// Statistik
$query_stats = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                FROM booking 
                WHERE DATE_FORMAT(tanggal_booking, '%Y-%m') = '$filter_bulan'";

if ($filter_ruangan != 'all') {
    $query_stats .= " AND ruangan_id = '$filter_ruangan'";
}

$result_stats = mysqli_query($conn, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);

// Set header untuk download Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Booking_" . $filter_bulan . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Booking Ruangan</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .stats {
            margin: 20px 0;
        }
        .stats td {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN BOOKING RUANGAN</h2>
        <h3>Periode: <?php echo date('F Y', strtotime($filter_bulan . '-01')); ?></h3>
        <p>Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>
    
    <table class="stats">
        <tr>
            <td>Total Booking:</td>
            <td><?php echo $stats['total']; ?></td>
            <td>Pending:</td>
            <td><?php echo $stats['pending']; ?></td>
            <td>Approved:</td>
            <td><?php echo $stats['approved']; ?></td>
        </tr>
        <tr>
            <td>Rejected:</td>
            <td><?php echo $stats['rejected']; ?></td>
            <td>Completed:</td>
            <td><?php echo $stats['completed']; ?></td>
            <td>Cancelled:</td>
            <td><?php echo $stats['cancelled']; ?></td>
        </tr>
    </table>
    
    <br>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Booking</th>
                <th>Ruangan</th>
                <th>Peminjam</th>
                <th>Email</th>
                <th>Waktu Mulai</th>
                <th>Waktu Selesai</th>
                <th>Keperluan</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while ($booking = mysqli_fetch_assoc($result)): 
            ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($booking['tanggal_booking'])); ?></td>
                    <td><?php echo $booking['nama_ruangan']; ?></td>
                    <td><?php echo $booking['nama_lengkap']; ?></td>
                    <td><?php echo $booking['email']; ?></td>
                    <td><?php echo date('H:i', strtotime($booking['waktu_mulai'])); ?></td>
                    <td><?php echo date('H:i', strtotime($booking['waktu_selesai'])); ?></td>
                    <td><?php echo $booking['keperluan']; ?></td>
                    <td><?php echo ucfirst($booking['status']); ?></td>
                    <td><?php echo $booking['keterangan']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <br>
    <p><i>Laporan ini dibuat oleh sistem Booking Ruangan</i></p>
</body>
</html>
