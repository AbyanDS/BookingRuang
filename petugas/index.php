<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

require_admin_or_petugas();

// Ambil statistik
$stats = array();

// Total ruangan
$query = "SELECT COUNT(*) as total FROM ruangan";
$result = mysqli_query($conn, $query);
$stats['total_ruangan'] = mysqli_fetch_assoc($result)['total'];

// Total booking
$query = "SELECT COUNT(*) as total FROM booking";
$result = mysqli_query($conn, $query);
$stats['total_booking'] = mysqli_fetch_assoc($result)['total'];

// Booking pending
$query = "SELECT COUNT(*) as total FROM booking WHERE status = 'pending'";
$result = mysqli_query($conn, $query);
$stats['pending'] = mysqli_fetch_assoc($result)['total'];

// Total user
$query = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$result = mysqli_query($conn, $query);
$stats['total_user'] = mysqli_fetch_assoc($result)['total'];

// Booking hari ini
$query = "SELECT COUNT(*) as total FROM booking WHERE DATE(tanggal_booking) = CURDATE()";
$result = mysqli_query($conn, $query);
$stats['today'] = mysqli_fetch_assoc($result)['total'];

// Booking approved
$query = "SELECT COUNT(*) as total FROM booking WHERE status = 'approved'";
$result = mysqli_query($conn, $query);
$stats['approved'] = mysqli_fetch_assoc($result)['total'];

// Booking terbaru
$query = "SELECT b.*, r.nama_ruangan, u.nama_lengkap 
          FROM booking b 
          JOIN ruangan r ON b.ruangan_id = r.id 
          JOIN users u ON b.user_id = u.id 
          ORDER BY b.created_at DESC 
          LIMIT 10";
$recent_bookings = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard <?php echo is_admin() ? 'Admin' : 'Petugas'; ?> - Booking Ruangan</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <!-- Hero Section -->
    <div class="dashboard-hero">
        <div class="container">
            <div class="hero-content-dashboard">
                <h1 class="hero-title-dashboard">
                    <i class="fas fa-tachometer-alt"></i> Dashboard <?php echo is_admin() ? 'Admin' : 'Petugas'; ?>
                </h1>
                <p class="hero-subtitle-dashboard">Selamat datang, <strong><?php echo $_SESSION['nama_lengkap']; ?></strong>!</p>
            </div>
        </div>
    </div>
    
    <div class="container dashboard-container">
        <div class="alert alert-info">
            <strong>ℹ️ Informasi:</strong> Sebagai Petugas, Anda dapat mengelola ruangan, booking, jadwal, laporan, dan approval peminjaman ruangan. Anda tidak dapat melakukan booking ruangan atau mengelola user.
        </div>
        
        <!-- Statistics Dashboard -->
        <div class="stats-dashboard-admin">
            <a href="../admin/ruangan.php" class="stat-card-admin stat-total">
                <div class="stat-icon-admin">
                    <i class="fas fa-door-open"></i>
                </div>
                <div class="stat-content-admin">
                    <h3 class="stat-label-admin">Total Ruangan</h3>
                    <p class="stat-value-admin"><?php echo $stats['total_ruangan']; ?></p>
                    <span class="stat-desc">Ruangan tersedia</span>
                </div>
            </a>
            
            <a href="../admin/booking.php" class="stat-card-admin stat-booking">
                <div class="stat-icon-admin">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-content-admin">
                    <h3 class="stat-label-admin">Total Booking</h3>
                    <p class="stat-value-admin"><?php echo $stats['total_booking']; ?></p>
                    <span class="stat-desc">Semua peminjaman</span>
                </div>
            </a>
            
            <a href="../admin/booking.php?status=pending" class="stat-card-admin stat-pending">
                <div class="stat-icon-admin">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content-admin">
                    <h3 class="stat-label-admin">Menunggu Approval</h3>
                    <p class="stat-value-admin"><?php echo $stats['pending']; ?></p>
                    <span class="stat-desc">Perlu ditinjau</span>
                </div>
            </a>
            
            <a href="../admin/booking.php" class="stat-card-admin stat-today">
                <div class="stat-icon-admin">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-content-admin">
                    <h3 class="stat-label-admin">Booking Hari Ini</h3>
                    <p class="stat-value-admin"><?php echo $stats['today']; ?></p>
                    <span class="stat-desc">Peminjaman hari ini</span>
                </div>
            </a>
            
            <a href="../admin/booking.php?status=approved" class="stat-card-admin stat-approved">
                <div class="stat-icon-admin">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content-admin">
                    <h3 class="stat-label-admin">Disetujui</h3>
                    <p class="stat-value-admin"><?php echo $stats['approved']; ?></p>
                    <span class="stat-desc">Booking approved</span>
                </div>
            </a>
            
            <a href="../admin/jadwal.php" class="stat-card-admin stat-users">
                <div class="stat-icon-admin">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content-admin">
                    <h3 class="stat-label-admin">Total User</h3>
                    <p class="stat-value-admin"><?php echo $stats['total_user']; ?></p>
                    <span class="stat-desc">User terdaftar</span>
                </div>
            </a>
        </div>
        
        <div class="card">
            <h2>Booking Terbaru</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
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
                                <td>#<?php echo $row['id']; ?></td>
                                <td><?php echo $row['nama_lengkap']; ?></td>
                                <td><?php echo $row['nama_ruangan']; ?></td>
                                <td><?php echo format_tanggal($row['tanggal_booking']); ?></td>
                                <td><?php echo format_waktu($row['waktu_mulai']) . ' - ' . format_waktu($row['waktu_selesai']); ?></td>
                                <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                <td>
                                    <a href="../admin/booking_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-small">Detail</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Belum ada booking</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($stats['pending'] > 0): ?>
        <div class="alert alert-warning">
            <strong>⚠️ Perhatian!</strong> Ada <strong><?php echo $stats['pending']; ?></strong> booking yang menunggu approval. 
            <a href="../admin/booking.php?status=pending" style="color: #856404; text-decoration: underline;">Lihat sekarang</a>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
