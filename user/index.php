<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

require_login();

// Redirect admin dan petugas ke dashboard mereka
if (is_admin_or_petugas()) {
    header("Location: ../admin/index.php");
    exit();
}

// Ambil statistik booking user
$user_id = $_SESSION['user_id'];

// Booking terbaru
$query = "SELECT b.*, r.nama_ruangan 
          FROM booking b 
          JOIN ruangan r ON b.ruangan_id = r.id 
          WHERE b.user_id = '$user_id' 
          ORDER BY b.created_at DESC 
          LIMIT 5";
$recent_bookings = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Booking Ruangan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <h1>Dashboard User</h1>
        <p>Selamat datang, <strong><?php echo $_SESSION['nama_lengkap']; ?></strong>!</p>
        
        <div class="alert alert-info">
            <strong>ℹ️ Informasi:</strong> Anda dapat melakukan booking ruangan dan melihat riwayat peminjaman Anda di sini.
        </div>
        
        <div class="card">
            <h2>Booking Terbaru</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Ruangan</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($recent_bookings) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($recent_bookings)): ?>
                            <tr>
                                <td><?php echo $row['nama_ruangan']; ?></td>
                                <td><?php echo format_tanggal($row['tanggal_booking']); ?></td>
                                <td><?php echo format_waktu($row['waktu_mulai']) . ' - ' . format_waktu($row['waktu_selesai']); ?></td>
                                <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                <td>
                                    <a href="booking_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-small">Detail</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Belum ada booking</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <a href="riwayat.php" class="btn btn-primary">Lihat Semua Riwayat</a>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
