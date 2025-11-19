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

$nama_ruangan_filter = 'Semua Ruangan';
if ($filter_ruangan != 'all') {
    $query_nama = "SELECT nama_ruangan FROM ruangan WHERE id = '$filter_ruangan'";
    $result_nama = mysqli_query($conn, $query_nama);
    if ($row_nama = mysqli_fetch_assoc($result_nama)) {
        $nama_ruangan_filter = $row_nama['nama_ruangan'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Booking Ruangan - Print</title>
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
        }
        
        .header h2 {
            margin: 0;
            color: #333;
        }
        
        .header h3 {
            margin: 10px 0;
            color: #666;
        }
        
        .info {
            margin: 20px 0;
        }
        
        .info table {
            width: 100%;
            border: none;
        }
        
        .info td {
            padding: 5px;
            border: none;
        }
        
        .stats {
            margin: 20px 0;
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
        }
        
        .stats table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .stats td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        
        .stats td:nth-child(odd) {
            background-color: #e0e0e0;
            font-weight: bold;
            width: 20%;
        }
        
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        table.data th,
        table.data td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }
        
        table.data th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
        
        table.data tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .footer {
            margin-top: 40px;
            text-align: right;
        }
        
        .signature {
            margin-top: 60px;
            display: inline-block;
            text-align: center;
        }
        
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #000;
            width: 200px;
        }
        
        .btn-print {
            margin: 20px 0;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }
        
        .btn-print:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="btn-print no-print">Print Laporan</button>
    
    <div class="header">
        <h2>LAPORAN BOOKING RUANGAN</h2>
        <h3>SISTEM PEMINJAMAN RUANGAN</h3>
    </div>
    
    <div class="info">
        <table>
            <tr>
                <td width="150"><strong>Periode</strong></td>
                <td>: <?php echo date('F Y', strtotime($filter_bulan . '-01')); ?></td>
            </tr>
            <tr>
                <td><strong>Ruangan</strong></td>
                <td>: <?php echo $nama_ruangan_filter; ?></td>
            </tr>
            <tr>
                <td><strong>Status</strong></td>
                <td>: <?php echo $filter_status == 'all' ? 'Semua Status' : ucfirst($filter_status); ?></td>
            </tr>
            <tr>
                <td><strong>Dicetak Pada</strong></td>
                <td>: <?php echo date('d F Y, H:i:s'); ?></td>
            </tr>
            <tr>
                <td><strong>Dicetak Oleh</strong></td>
                <td>: <?php echo $_SESSION['nama_lengkap'] . ' (' . ucfirst($_SESSION['role']) . ')'; ?></td>
            </tr>
        </table>
    </div>
    
    <div class="stats">
        <h3>STATISTIK BOOKING</h3>
        <table>
            <tr>
                <td>Total Booking</td>
                <td><?php echo $stats['total']; ?></td>
                <td>Pending</td>
                <td><?php echo $stats['pending']; ?></td>
                <td>Approved</td>
                <td><?php echo $stats['approved']; ?></td>
            </tr>
            <tr>
                <td>Rejected</td>
                <td><?php echo $stats['rejected']; ?></td>
                <td>Completed</td>
                <td><?php echo $stats['completed']; ?></td>
                <td>Cancelled</td>
                <td><?php echo $stats['cancelled']; ?></td>
            </tr>
        </table>
    </div>
    
    <h3>DETAIL BOOKING</h3>
    <table class="data">
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Tanggal</th>
                <th>Ruangan</th>
                <th>Peminjam</th>
                <th>Waktu</th>
                <th>Keperluan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (mysqli_num_rows($result) > 0):
                while ($booking = mysqli_fetch_assoc($result)): 
            ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($booking['tanggal_booking'])); ?></td>
                    <td><?php echo $booking['nama_ruangan']; ?></td>
                    <td>
                        <?php echo $booking['nama_lengkap']; ?><br>
                        <small><?php echo $booking['email']; ?></small>
                    </td>
                    <td><?php echo date('H:i', strtotime($booking['waktu_mulai'])) . ' - ' . date('H:i', strtotime($booking['waktu_selesai'])); ?></td>
                    <td><?php echo $booking['keperluan']; ?></td>
                    <td><?php echo ucfirst($booking['status']); ?></td>
                </tr>
            <?php 
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data booking untuk periode yang dipilih</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <div class="signature">
            <p><?php echo date('d F Y'); ?></p>
            <p>Mengetahui,</p>
            <div class="signature-line"></div>
            <p><strong><?php echo $_SESSION['nama_lengkap']; ?></strong></p>
            <p><?php echo ucfirst($_SESSION['role']); ?></p>
        </div>
    </div>
    
    <script>
        // Auto print saat halaman load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
