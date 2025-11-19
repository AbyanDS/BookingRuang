<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

require_login();

// Redirect admin dan petugas
if (is_admin_or_petugas()) {
    header("Location: ../admin/history.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil history user
$history = get_history(null, $user_id, 50);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Peminjaman Saya</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <h1>History Peminjaman Saya</h1>
        <p class="subtitle">Riwayat aktivitas booking ruangan Anda</p>
        
        <div class="card">
            <h2>Timeline Aktivitas</h2>
            <div class="timeline">
                <?php if (mysqli_num_rows($history) > 0): ?>
                    <?php while ($h = mysqli_fetch_assoc($history)): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong><?php echo $h['action']; ?></strong>
                                    <span class="timeline-booking">Booking #<?php echo $h['booking_id']; ?></span>
                                </div>
                                <div class="timeline-ruangan">
                                    📍 <?php echo $h['nama_ruangan']; ?>
                                </div>
                                <?php if ($h['status_lama'] && $h['status_baru']): ?>
                                    <div class="status-change">
                                        <span class="badge badge-<?php echo $h['status_lama']; ?>"><?php echo ucfirst($h['status_lama']); ?></span>
                                        →
                                        <span class="badge badge-<?php echo $h['status_baru']; ?>"><?php echo ucfirst($h['status_baru']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($h['keterangan']): ?>
                                    <p class="timeline-note"><?php echo $h['keterangan']; ?></p>
                                <?php endif; ?>
                                <div class="timeline-meta">
                                    <?php if ($h['nama_admin']): ?>
                                        Oleh: <?php echo $h['nama_admin']; ?> • 
                                    <?php endif; ?>
                                    <?php echo date('d F Y, H:i', strtotime($h['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center">Belum ada riwayat aktivitas</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <h2>Statistik Aktivitas Saya</h2>
            <div class="stats-grid">
                <?php
                // Total aktivitas
                $query_total = "SELECT COUNT(*) as total FROM history_peminjaman WHERE user_id = '$user_id'";
                $result_total = mysqli_query($conn, $query_total);
                $total = mysqli_fetch_assoc($result_total)['total'];
                
                // Booking dibuat
                $query_created = "SELECT COUNT(*) as total FROM history_peminjaman WHERE user_id = '$user_id' AND action LIKE '%Dibuat%'";
                $result_created = mysqli_query($conn, $query_created);
                $created = mysqli_fetch_assoc($result_created)['total'];
                
                // Booking disetujui
                $query_approved = "SELECT COUNT(*) as total FROM history_peminjaman WHERE user_id = '$user_id' AND action LIKE '%Disetujui%'";
                $result_approved = mysqli_query($conn, $query_approved);
                $approved = mysqli_fetch_assoc($result_approved)['total'];
                
                // Booking dibatalkan
                $query_cancelled = "SELECT COUNT(*) as total FROM history_peminjaman WHERE user_id = '$user_id' AND action LIKE '%Dibatalkan%'";
                $result_cancelled = mysqli_query($conn, $query_cancelled);
                $cancelled = mysqli_fetch_assoc($result_cancelled)['total'];
                ?>
                <div class="stat-card stat-blue">
                    <h3>Total Aktivitas</h3>
                    <p class="stat-number"><?php echo $total; ?></p>
                </div>
                <div class="stat-card stat-green">
                    <h3>Booking Dibuat</h3>
                    <p class="stat-number"><?php echo $created; ?></p>
                </div>
                <div class="stat-card stat-yellow">
                    <h3>Booking Disetujui</h3>
                    <p class="stat-number"><?php echo $approved; ?></p>
                </div>
                <div class="stat-card stat-purple">
                    <h3>Booking Dibatalkan</h3>
                    <p class="stat-number"><?php echo $cancelled; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
