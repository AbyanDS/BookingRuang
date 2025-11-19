<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

// Admin dan petugas bisa approve/reject booking
require_admin_or_petugas();

$booking_id = isset($_GET['id']) ? clean_input($_GET['id']) : 0;

// Ambil detail booking
$query = "SELECT b.*, r.nama_ruangan, r.kapasitas, r.fasilitas, u.nama_lengkap, u.email 
          FROM booking b 
          JOIN ruangan r ON b.ruangan_id = r.id 
          JOIN users u ON b.user_id = u.id
          WHERE b.id = '$booking_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: booking.php");
    exit();
}

$booking = mysqli_fetch_assoc($result);

$success = '';
$error = '';

// Handle approve
if (isset($_POST['approve'])) {
    $keterangan = clean_input($_POST['keterangan']);
    $query = "UPDATE booking SET status = 'approved', keterangan = '$keterangan' WHERE id = '$booking_id'";
    if (mysqli_query($conn, $query)) {
        // Catat history
        log_history($booking_id, $booking['user_id'], $booking['ruangan_id'], 'Booking Disetujui', 'pending', 'approved', $keterangan ? $keterangan : 'Booking disetujui oleh admin');
        $success = 'Booking berhasil disetujui!';
        // Refresh data
        $result = mysqli_query($conn, "SELECT b.*, r.nama_ruangan, r.kapasitas, r.fasilitas, u.nama_lengkap, u.email 
                                       FROM booking b 
                                       JOIN ruangan r ON b.ruangan_id = r.id 
                                       JOIN users u ON b.user_id = u.id
                                       WHERE b.id = '$booking_id'");
        $booking = mysqli_fetch_assoc($result);
    }
}

// Handle reject
if (isset($_POST['reject'])) {
    $keterangan = clean_input($_POST['keterangan']);
    $query = "UPDATE booking SET status = 'rejected', keterangan = '$keterangan' WHERE id = '$booking_id'";
    if (mysqli_query($conn, $query)) {
        // Catat history
        log_history($booking_id, $booking['user_id'], $booking['ruangan_id'], 'Booking Ditolak', 'pending', 'rejected', $keterangan ? $keterangan : 'Booking ditolak oleh admin');
        $success = 'Booking berhasil ditolak!';
        $result = mysqli_query($conn, "SELECT b.*, r.nama_ruangan, r.kapasitas, r.fasilitas, u.nama_lengkap, u.email 
                                       FROM booking b 
                                       JOIN ruangan r ON b.ruangan_id = r.id 
                                       JOIN users u ON b.user_id = u.id
                                       WHERE b.id = '$booking_id'");
        $booking = mysqli_fetch_assoc($result);
    }
}

// Handle complete
if (isset($_POST['complete'])) {
    $query = "UPDATE booking SET status = 'completed' WHERE id = '$booking_id'";
    if (mysqli_query($conn, $query)) {
        // Catat history
        log_history($booking_id, $booking['user_id'], $booking['ruangan_id'], 'Booking Selesai', 'approved', 'completed', 'Booking ditandai selesai oleh admin');
        $success = 'Booking telah diselesaikan!';
        $result = mysqli_query($conn, "SELECT b.*, r.nama_ruangan, r.kapasitas, r.fasilitas, u.nama_lengkap, u.email 
                                       FROM booking b 
                                       JOIN ruangan r ON b.ruangan_id = r.id 
                                       JOIN users u ON b.user_id = u.id
                                       WHERE b.id = '$booking_id'");
        $booking = mysqli_fetch_assoc($result);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Booking - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <h1>Detail Booking</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
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
                    <th>Email</th>
                    <td><?php echo $booking['email']; ?></td>
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
                <tr>
                    <th>Update Terakhir</th>
                    <td><?php echo date('d/m/Y H:i', strtotime($booking['updated_at'])); ?></td>
                </tr>
            </table>
            
            <?php if ($booking['status'] == 'pending'): ?>
                <div class="action-section">
                    <h3>Approval Booking</h3>
                    <form method="POST" action="" style="margin-bottom: 20px;">
                        <div class="form-group">
                            <label>Keterangan (Opsional)</label>
                            <textarea name="keterangan" rows="3" placeholder="Tambahkan keterangan..."></textarea>
                        </div>
                        <button type="submit" name="approve" class="btn btn-success">Setujui Booking</button>
                        <button type="submit" name="reject" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menolak booking ini?')">Tolak Booking</button>
                    </form>
                </div>
            <?php elseif ($booking['status'] == 'approved'): ?>
                <div class="action-section">
                    <form method="POST" action="">
                        <button type="submit" name="complete" class="btn btn-success">Tandai Selesai</button>
                    </form>
                </div>
            <?php endif; ?>
            
            <a href="booking.php" class="btn btn-secondary">Kembali</a>
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
                                User: <?php echo $h['nama_user']; ?>
                                <?php if ($h['nama_admin']): ?>
                                    • Oleh: <?php echo $h['nama_admin']; ?>
                                <?php endif; ?>
                                • <?php echo date('d/m/Y H:i', strtotime($h['created_at'])); ?>
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
