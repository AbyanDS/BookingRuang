<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

require_login();

// Redirect admin dan petugas
if (is_admin_or_petugas()) {
    header("Location: ../admin/booking.php");
    exit();
}

$booking_id = isset($_GET['id']) ? clean_input($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Ambil detail booking
$query = "SELECT b.*, r.nama_ruangan, r.kapasitas, r.fasilitas, u.nama_lengkap 
          FROM booking b 
          JOIN ruangan r ON b.ruangan_id = r.id 
          JOIN users u ON b.user_id = u.id
          WHERE b.id = '$booking_id' AND b.user_id = '$user_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: riwayat.php");
    exit();
}

$booking = mysqli_fetch_assoc($result);

// Handle cancel booking
if (isset($_POST['cancel'])) {
    $query = "UPDATE booking SET status = 'cancelled' WHERE id = '$booking_id' AND user_id = '$user_id'";
    if (mysqli_query($conn, $query)) {
        // Catat history
        log_history($booking_id, $user_id, $booking['ruangan_id'], 'Booking Dibatalkan', 'pending', 'cancelled', 'Dibatalkan oleh user');
        header("Location: booking_detail.php?id=$booking_id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Booking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <h1>Detail Booking</h1>
        
        <div class="card">
            <h2>Informasi Booking</h2>
            <table class="detail-table">
                <tr>
                    <th>ID Booking</th>
                    <td>#<?php echo $booking['id']; ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><span class="badge badge-<?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                </tr>
                <tr>
                    <th>Nama Pemesan</th>
                    <td><?php echo $booking['nama_lengkap']; ?></td>
                </tr>
                <tr>
                    <th>Ruangan</th>
                    <td><?php echo $booking['nama_ruangan']; ?></td>
                </tr>
                <tr>
                    <th>Kapasitas</th>
                    <td><?php echo $booking['kapasitas']; ?> orang</td>
                </tr>
                <tr>
                    <th>Fasilitas</th>
                    <td><?php echo $booking['fasilitas']; ?></td>
                </tr>
                <tr>
                    <th>Tanggal Booking</th>
                    <td><?php echo format_tanggal($booking['tanggal_booking']); ?></td>
                </tr>
                <tr>
                    <th>Waktu</th>
                    <td><?php echo format_waktu($booking['waktu_mulai']) . ' - ' . format_waktu($booking['waktu_selesai']); ?></td>
                </tr>
                <tr>
                    <th>Keperluan</th>
                    <td><?php echo nl2br($booking['keperluan']); ?></td>
                </tr>
                <?php if ($booking['keterangan']): ?>
                <tr>
                    <th>Keterangan</th>
                    <td><?php echo nl2br($booking['keterangan']); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th>Dibuat</th>
                    <td><?php echo date('d/m/Y H:i', strtotime($booking['created_at'])); ?></td>
                </tr>
            </table>
            
            <div class="action-buttons">
                <?php if ($booking['status'] == 'pending'): ?>
                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan booking ini?');">
                        <button type="submit" name="cancel" class="btn btn-danger">Batalkan Booking</button>
                    </form>
                <?php endif; ?>
                <a href="riwayat.php" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
        
        <!-- History Peminjaman -->
        <div class="card">
            <h2>Riwayat Perubahan</h2>
            <div class="timeline">
                <?php
                $history = get_history($booking_id);
                if (mysqli_num_rows($history) > 0):
                    while ($h = mysqli_fetch_assoc($history)):
                ?>
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <strong><?php echo $h['action']; ?></strong>
                            <?php if ($h['status_lama'] && $h['status_baru']): ?>
                                <span class="status-change">
                                    <span class="badge badge-<?php echo $h['status_lama']; ?>"><?php echo ucfirst($h['status_lama']); ?></span>
                                    →
                                    <span class="badge badge-<?php echo $h['status_baru']; ?>"><?php echo ucfirst($h['status_baru']); ?></span>
                                </span>
                            <?php endif; ?>
                            <?php if ($h['keterangan']): ?>
                                <p class="timeline-note"><?php echo $h['keterangan']; ?></p>
                            <?php endif; ?>
                            <div class="timeline-meta">
                                <?php if ($h['nama_admin']): ?>
                                    Oleh: <?php echo $h['nama_admin']; ?> • 
                                <?php endif; ?>
                                <?php echo date('d/m/Y H:i', strtotime($h['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php 
                    endwhile;
                else:
                ?>
                    <p class="text-center">Belum ada riwayat perubahan</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
