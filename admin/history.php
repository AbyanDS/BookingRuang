<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

require_admin_or_petugas();

// Filter
$filter_user = isset($_GET['user']) ? $_GET['user'] : '';
$filter_ruangan = isset($_GET['ruangan']) ? $_GET['ruangan'] : '';
$filter_action = isset($_GET['action']) ? $_GET['action'] : '';

$query = "SELECT h.*, 
          u.nama_lengkap as nama_user, 
          r.nama_ruangan,
          admin.nama_lengkap as nama_admin,
          b.tanggal_booking
          FROM history_peminjaman h
          LEFT JOIN users u ON h.user_id = u.id
          LEFT JOIN ruangan r ON h.ruangan_id = r.id
          LEFT JOIN users admin ON h.dilakukan_oleh = admin.id
          LEFT JOIN booking b ON h.booking_id = b.id
          WHERE 1=1";

if ($filter_user) {
    $query .= " AND h.user_id = '$filter_user'";
}

if ($filter_ruangan) {
    $query .= " AND h.ruangan_id = '$filter_ruangan'";
}

if ($filter_action) {
    $query .= " AND h.action LIKE '%$filter_action%'";
}

$query .= " ORDER BY h.created_at DESC LIMIT 100";
$result = mysqli_query($conn, $query);

// Ambil data untuk filter
$users_query = "SELECT id, nama_lengkap FROM users WHERE role = 'user' ORDER BY nama_lengkap ASC";
$users_list = mysqli_query($conn, $users_query);

$ruangan_query = "SELECT id, nama_ruangan FROM ruangan ORDER BY nama_ruangan ASC";
$ruangan_list = mysqli_query($conn, $ruangan_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Peminjaman - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <h1>History Peminjaman Ruangan</h1>
        <div class="filter-box">
            <form method="GET" action="">
                <label>Filter User:</label>
                <select name="user" onchange="this.form.submit()">
                    <option value="">Semua User</option>
                    <?php while ($u = mysqli_fetch_assoc($users_list)): ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo ($filter_user == $u['id']) ? 'selected' : ''; ?>>
                            <?php echo $u['nama_lengkap']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <label>Filter Ruangan:</label>
                <select name="ruangan" onchange="this.form.submit()">
                    <option value="">Semua Ruangan</option>
                    <?php while ($r = mysqli_fetch_assoc($ruangan_list)): ?>
                        <option value="<?php echo $r['id']; ?>" <?php echo ($filter_ruangan == $r['id']) ? 'selected' : ''; ?>>
                            <?php echo $r['nama_ruangan']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <label>Filter Aktivitas:</label>
                <select name="action" onchange="this.form.submit()">
                    <option value="">Semua Aktivitas</option>
                    <option value="Dibuat" <?php echo ($filter_action == 'Dibuat') ? 'selected' : ''; ?>>Booking Dibuat</option>
                    <option value="Disetujui" <?php echo ($filter_action == 'Disetujui') ? 'selected' : ''; ?>>Booking Disetujui</option>
                    <option value="Ditolak" <?php echo ($filter_action == 'Ditolak') ? 'selected' : ''; ?>>Booking Ditolak</option>
                    <option value="Dibatalkan" <?php echo ($filter_action == 'Dibatalkan') ? 'selected' : ''; ?>>Booking Dibatalkan</option>
                    <option value="Selesai" <?php echo ($filter_action == 'Selesai') ? 'selected' : ''; ?>>Booking Selesai</option>
                </select>
                
                <?php if ($filter_user || $filter_ruangan || $filter_action): ?>
                    <a href="history.php" class="btn btn-small">Reset Filter</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="card">
            <h2>Daftar Riwayat (100 Terbaru)</h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal & Waktu</th>
                            <th>Booking ID</th>
                            <th>User</th>
                            <th>Ruangan</th>
                            <th>Aktivitas</th>
                            <th>Status</th>
                            <th>Dilakukan Oleh</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                    <td><a href="booking_detail.php?id=<?php echo $row['booking_id']; ?>">#<?php echo $row['booking_id']; ?></a></td>
                                    <td><?php echo $row['nama_user']; ?></td>
                                    <td><?php echo $row['nama_ruangan']; ?></td>
                                    <td><?php echo $row['action']; ?></td>
                                    <td>
                                        <?php if ($row['status_lama'] && $row['status_baru']): ?>
                                            <span class="badge badge-<?php echo $row['status_lama']; ?>"><?php echo ucfirst($row['status_lama']); ?></span>
                                            →
                                            <span class="badge badge-<?php echo $row['status_baru']; ?>"><?php echo ucfirst($row['status_baru']); ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $row['nama_admin'] ? $row['nama_admin'] : 'System'; ?></td>
                                    <td><?php echo $row['keterangan'] ? substr($row['keterangan'], 0, 50) . '...' : '-'; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">Belum ada riwayat</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card">
            <h2>Statistik History</h2>
            <div class="stats-grid">
                <?php
                // Total aktivitas
                $query_total = "SELECT COUNT(*) as total FROM history_peminjaman";
                $result_total = mysqli_query($conn, $query_total);
                $total = mysqli_fetch_assoc($result_total)['total'];
                
                // Booking dibuat hari ini
                $query_today = "SELECT COUNT(*) as total FROM history_peminjaman WHERE DATE(created_at) = CURDATE()";
                $result_today = mysqli_query($conn, $query_today);
                $today = mysqli_fetch_assoc($result_today)['total'];
                
                // Booking disetujui
                $query_approved = "SELECT COUNT(*) as total FROM history_peminjaman WHERE action LIKE '%Disetujui%'";
                $result_approved = mysqli_query($conn, $query_approved);
                $approved = mysqli_fetch_assoc($result_approved)['total'];
                
                // Booking ditolak
                $query_rejected = "SELECT COUNT(*) as total FROM history_peminjaman WHERE action LIKE '%Ditolak%'";
                $result_rejected = mysqli_query($conn, $query_rejected);
                $rejected = mysqli_fetch_assoc($result_rejected)['total'];
                ?>
                <div class="stat-card stat-blue">
                    <h3>Total Aktivitas</h3>
                    <p class="stat-number"><?php echo $total; ?></p>
                </div>
                <div class="stat-card stat-green">
                    <h3>Aktivitas Hari Ini</h3>
                    <p class="stat-number"><?php echo $today; ?></p>
                </div>
                <div class="stat-card stat-yellow">
                    <h3>Total Disetujui</h3>
                    <p class="stat-number"><?php echo $approved; ?></p>
                </div>
                <div class="stat-card stat-purple">
                    <h3>Total Ditolak</h3>
                    <p class="stat-number"><?php echo $rejected; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
