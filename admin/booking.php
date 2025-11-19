<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

// Admin dan petugas bisa kelola booking
require_admin_or_petugas();

$success = '';
$error = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = clean_input($_GET['delete']);
    $query = "DELETE FROM booking WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        $success = 'Booking berhasil dihapus!';
    } else {
        $error = 'Gagal menghapus booking: ' . mysqli_error($conn);
    }
}

// Filter
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

$query = "SELECT b.*, r.nama_ruangan, u.nama_lengkap 
          FROM booking b 
          JOIN ruangan r ON b.ruangan_id = r.id 
          JOIN users u ON b.user_id = u.id 
          WHERE 1=1";

if ($filter_status) {
    $query .= " AND b.status = '$filter_status'";
}

if ($filter_date) {
    $query .= " AND b.tanggal_booking = '$filter_date'";
}

$query .= " ORDER BY b.created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Booking - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <h1>Kelola Booking</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="filter-box">
            <form method="GET" action="">
                <label>Filter Status:</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <option value="pending" <?php echo ($filter_status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo ($filter_status == 'approved') ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo ($filter_status == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                    <option value="completed" <?php echo ($filter_status == 'completed') ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo ($filter_status == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                
                <label>Filter Tanggal:</label>
                <input type="date" name="date" value="<?php echo $filter_date; ?>" onchange="this.form.submit()">
            </form>
        </div>
        
        <div class="card">
            <h2>Daftar Booking</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Ruangan</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Keperluan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><?php echo $row['nama_lengkap']; ?></td>
                                <td><?php echo $row['nama_ruangan']; ?></td>
                                <td><?php echo format_tanggal($row['tanggal_booking']); ?></td>
                                <td><?php echo format_waktu($row['waktu_mulai']) . ' - ' . format_waktu($row['waktu_selesai']); ?></td>
                                <td><?php echo substr($row['keperluan'], 0, 30) . '...'; ?></td>
                                <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                <td>
                                    <a href="booking_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-small">Detail</a>
                                    <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus booking ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Belum ada data booking</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
