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

// Query ruangan untuk filter
$query_ruangan = "SELECT id, nama_ruangan FROM ruangan ORDER BY nama_ruangan";
$result_ruangan = mysqli_query($conn, $query_ruangan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Booking - Booking Ruangan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

<div class="main-content">
    <div class="container">
        <!-- Header Section -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-text">
                    <h1>Laporan Booking Ruangan</h1>
                    <p>Analisis dan monitoring booking ruangan</p>
                </div>
            </div>
            <div class="header-actions">
                <a href="laporan_export.php?bulan=<?php echo $filter_bulan; ?>&status=<?php echo $filter_status; ?>&ruangan=<?php echo $filter_ruangan; ?>" class="btn btn-success btn-icon" target="_blank">
                    <span class="text">Export Excel</span>
                </a>
                <a href="laporan_print.php?bulan=<?php echo $filter_bulan; ?>&status=<?php echo $filter_status; ?>&ruangan=<?php echo $filter_ruangan; ?>" class="btn btn-secondary btn-icon" target="_blank">
                    <span class="text">Print PDF</span>
                </a>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="card card-filter">
            <div class="card-header-custom">
                <h3>Filter & Pencarian Laporan</h3>
                <p>Pilih kriteria untuk melihat laporan yang spesifik</p>
            </div>
            <form method="GET" action="" class="filter-form-advanced">
                <div class="filter-grid">
                    <div class="filter-item-advanced">
                        <label class="filter-label">
                            <span class="label-icon">📅</span>
                            <span class="label-text">Periode Bulan</span>
                        </label>
                        <input type="month" name="bulan" value="<?php echo $filter_bulan; ?>" class="form-control-modern">
                    </div>
                    
                    <div class="filter-item-advanced">
                        <label class="filter-label">
                            <span class="label-icon">🏢</span>
                            <span class="label-text">Ruangan</span>
                        </label>
                        <select name="ruangan" class="form-control-modern">
                            <option value="all" <?php echo $filter_ruangan == 'all' ? 'selected' : ''; ?>>Semua Ruangan</option>
                            <?php 
                            mysqli_data_seek($result_ruangan, 0);
                            while ($ruangan = mysqli_fetch_assoc($result_ruangan)): 
                            ?>
                                <option value="<?php echo $ruangan['id']; ?>" <?php echo $filter_ruangan == $ruangan['id'] ? 'selected' : ''; ?>>
                                    <?php echo $ruangan['nama_ruangan']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="filter-item-advanced">
                        <label class="filter-label">
                            <span class="label-icon">📌</span>
                            <span class="label-text">Status Booking</span>
                        </label>
                        <select name="status" class="form-control-modern">
                            <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>Semua Status</option>
                            <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                            <option value="approved" <?php echo $filter_status == 'approved' ? 'selected' : ''; ?>>✅ Approved</option>
                            <option value="rejected" <?php echo $filter_status == 'rejected' ? 'selected' : ''; ?>>❌ Rejected</option>
                            <option value="completed" <?php echo $filter_status == 'completed' ? 'selected' : ''; ?>>✔️ Completed</option>
                            <option value="cancelled" <?php echo $filter_status == 'cancelled' ? 'selected' : ''; ?>>🚫 Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="filter-item-advanced filter-actions">
                        <label class="filter-label" style="opacity: 0;">Action</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            Tampilkan Laporan
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Statistik Section -->
        <div class="section-header">
            <h3>Statistik Bulan <?php echo date('F Y', strtotime($filter_bulan . '-01')); ?></h3>
            <p>Ringkasan data booking dalam periode yang dipilih</p>
        </div>
        <div class="stats-grid stats-grid-6">
            <div class="stat-card-modern stat-blue">
                <div class="stat-content">
                    <h3>Total Booking</h3>
                    <p class="stat-number"><?php echo $stats['total']; ?></p>
                    <span class="stat-label">Total keseluruhan</span>
                </div>
            </div>
            <div class="stat-card-modern stat-yellow">
                <div class="stat-content">
                    <h3>Pending</h3>
                    <p class="stat-number"><?php echo $stats['pending']; ?></p>
                    <span class="stat-label">Menunggu approval</span>
                </div>
            </div>
            <div class="stat-card-modern stat-green">
                <div class="stat-content">
                    <h3>Approved</h3>
                    <p class="stat-number"><?php echo $stats['approved']; ?></p>
                    <span class="stat-label">Disetujui</span>
                </div>
            </div>
            <div class="stat-card-modern stat-red">
                <div class="stat-content">
                    <h3>Rejected</h3>
                    <p class="stat-number"><?php echo $stats['rejected']; ?></p>
                    <span class="stat-label">Ditolak</span>
                </div>
            </div>
            <div class="stat-card-modern stat-teal">
                <div class="stat-content">
                    <h3>Completed</h3>
                    <p class="stat-number"><?php echo $stats['completed']; ?></p>
                    <span class="stat-label">Selesai</span>
                </div>
            </div>
            <div class="stat-card-modern stat-purple">
                <div class="stat-content">
                    <h3>Cancelled</h3>
                    <p class="stat-number"><?php echo $stats['cancelled']; ?></p>
                    <span class="stat-label">Dibatalkan</span>
                </div>
            </div>
        </div>
        
        <!-- Tabel Laporan -->
        <div class="section-header" style="margin-top: 40px;">
            <h3>Detail Data Booking</h3>
            <p>Daftar lengkap booking berdasarkan filter yang dipilih</p>
        </div>
        <div class="card card-table">
            <div class="card-header-table">
                <div class="table-info">
                    <span class="table-badge">Total: <?php echo mysqli_num_rows($result); ?> data</span>
                </div>
            </div>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th style="width: 120px;">Tanggal</th>
                                <th>Ruangan</th>
                                <th>Peminjam</th>
                                <th style="width: 150px;">Waktu</th>
                                <th>Keperluan</th>
                                <th style="width: 130px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            mysqli_data_seek($result, 0);
                            while ($booking = mysqli_fetch_assoc($result)): 
                            ?>
                                <tr>
                                    <td class="text-center">
                                        <span class="row-number"><?php echo $no++; ?></span>
                                    </td>
                                    <td>
                                        <div class="date-display">
                                            <span class="date-day"><?php echo date('d', strtotime($booking['tanggal_booking'])); ?></span>
                                            <span class="date-month"><?php echo date('M Y', strtotime($booking['tanggal_booking'])); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="room-display">
                                            <span class="room-icon">🏢</span>
                                            <span class="room-name"><?php echo $booking['nama_ruangan']; ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-display">
                                            <strong><?php echo $booking['nama_lengkap']; ?></strong>
                                            <small><?php echo $booking['email']; ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="time-display">
                                            <span class="time-icon">🕐</span>
                                            <span><?php echo date('H:i', strtotime($booking['waktu_mulai'])) . ' - ' . date('H:i', strtotime($booking['waktu_selesai'])); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="keperluan-text">
                                            <?php echo strlen($booking['keperluan']) > 50 ? substr($booking['keperluan'], 0, 50) . '...' : $booking['keperluan']; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $status_config = [
                                            'pending' => ['class' => 'badge-warning', 'icon' => '⏳', 'text' => 'Pending'],
                                            'approved' => ['class' => 'badge-success', 'icon' => '✅', 'text' => 'Approved'],
                                            'rejected' => ['class' => 'badge-danger', 'icon' => '❌', 'text' => 'Rejected'],
                                            'completed' => ['class' => 'badge-info', 'icon' => '✔️', 'text' => 'Completed'],
                                            'cancelled' => ['class' => 'badge-secondary', 'icon' => '🚫', 'text' => 'Cancelled']
                                        ];
                                        $status = $status_config[$booking['status']] ?? ['class' => 'badge-secondary', 'icon' => '', 'text' => ucfirst($booking['status'])];
                                        ?>
                                        <span class="badge <?php echo $status['class']; ?> badge-modern">
                                            <span class="badge-icon"><?php echo $status['icon']; ?></span>
                                            <span class="badge-text"><?php echo $status['text']; ?></span>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3>Tidak Ada Data</h3>
                    <p>Tidak ada data booking untuk periode yang dipilih.</p>
                    <p>Coba ubah filter pencarian Anda.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
