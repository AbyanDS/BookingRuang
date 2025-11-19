<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

require_admin();

$success = '';
$error = '';

// Ambil pesan dari session
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Handle approve/reject reset password
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve_reset'])) {
        $request_id = (int)$_POST['request_id'];
        $admin_id = $_SESSION['user_id'];
        
        $update_request = "UPDATE password_reset_requests SET status = 'approved', approved_by = ?, approved_date = NOW() WHERE id = ?";
        $stmt_request = mysqli_prepare($conn, $update_request);
        mysqli_stmt_bind_param($stmt_request, "ii", $admin_id, $request_id);
        
        if (mysqli_stmt_execute($stmt_request)) {
            $success = "Permintaan reset password telah disetujui! User dapat mereset password mereka.";
        } else {
            $error = "Gagal menyetujui permintaan!";
        }
    }
    
    if (isset($_POST['reject_reset'])) {
        $request_id = (int)$_POST['request_id'];
        $keterangan = clean_input($_POST['keterangan']);
        $admin_id = $_SESSION['user_id'];
        
        $update_request = "UPDATE password_reset_requests SET status = 'rejected', approved_by = ?, approved_date = NOW(), keterangan = ? WHERE id = ?";
        $stmt_request = mysqli_prepare($conn, $update_request);
        mysqli_stmt_bind_param($stmt_request, "isi", $admin_id, $keterangan, $request_id);
        
        if (mysqli_stmt_execute($stmt_request)) {
            $success = "Permintaan reset password berhasil ditolak!";
        } else {
            $error = "Gagal menolak permintaan!";
        }
    }
}

// Tab aktif
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'users';

// Count pending reset password requests
$count_query = "SELECT COUNT(*) as total FROM password_reset_requests WHERE status = 'pending'";
$count_result = mysqli_query($conn, $count_query);
$pending_count = mysqli_fetch_assoc($count_result)['total'];

// Handle perubahan role
if (isset($_POST['change_role']) && isset($_POST['user_id'])) {
    $user_id = clean_input($_POST['user_id']);
    $new_role = clean_input($_POST['change_role']);
    
    // Validasi role
    if (in_array($new_role, ['user', 'petugas', 'admin'])) {
        // Cek apakah user sedang mengubah role dirinya sendiri
        if ($user_id == $_SESSION['user_id']) {
            $error = 'Anda tidak dapat mengubah role akun Anda sendiri!';
        } else {
            $query = "UPDATE users SET role = '$new_role' WHERE id = '$user_id'";
            if (mysqli_query($conn, $query)) {
                $success = 'Role user berhasil diubah menjadi ' . ucfirst($new_role) . '!';
            } else {
                $error = 'Gagal mengubah role user!';
            }
        }
    } else {
        $error = 'Role tidak valid!';
    }
}

// Ambil data users
$query = "SELECT * FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Ambil data reset password requests
$reset_query = "SELECT pr.*, u.username, u.nama_lengkap, u.email, 
          admin.nama_lengkap as admin_name
          FROM password_reset_requests pr
          JOIN users u ON pr.user_id = u.id
          LEFT JOIN users admin ON pr.approved_by = admin.id
          ORDER BY 
            CASE pr.status 
                WHEN 'pending' THEN 1
                WHEN 'approved' THEN 2
                WHEN 'rejected' THEN 3
            END,
            pr.request_date DESC";
$reset_result = mysqli_query($conn, $reset_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header-manage">
            <div>
                <h1><i class="fas fa-users"></i> Kelola User</h1>
                <p class="page-subtitle">Manajemen user dan permintaan reset password</p>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <a href="?tab=users" class="tab-link <?php echo $active_tab == 'users' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Daftar User
            </a>
            <a href="?tab=reset_requests" class="tab-link <?php echo $active_tab == 'reset_requests' ? 'active' : ''; ?>">
                <i class="fas fa-key"></i> Reset Password
                <?php if ($pending_count > 0): ?>
                    <span class="notification-badge"><?php echo $pending_count; ?></span>
                <?php endif; ?>
            </a>
        </div>
        
        <!-- Tab Content: Users List -->
        <?php if ($active_tab == 'users'): ?>
        <div class="card">
            <h2><i class="fas fa-list"></i> Daftar User</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Tanggal Daftar</th>
                        <th>Total Booking</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $no = 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <?php
                            // Hitung total booking per user
                            $user_id = $row['id'];
                            $booking_query = "SELECT COUNT(*) as total FROM booking WHERE user_id = '$user_id'";
                            $booking_result = mysqli_query($conn, $booking_query);
                            $total_booking = mysqli_fetch_assoc($booking_result)['total'];
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $row['username']; ?></td>
                                <td><?php echo $row['nama_lengkap']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><span class="badge badge-<?php echo $row['role']; ?>"><?php echo ucfirst($row['role']); ?></span></td>
                                <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                <td><?php echo $total_booking; ?></td>
                                <td>
                                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                        <div style="display: flex; gap: 10px; align-items: center;">
                                            <form method="POST" action="" style="display: inline-block;" onsubmit="return confirm('Ubah role <?php echo htmlspecialchars($row['username']); ?> menjadi ' + this.change_role.options[this.change_role.selectedIndex].text + '?')">
                                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                                <select name="change_role" class="role-select" onchange="this.form.submit()">
                                                    <option value="user" <?php echo ($row['role'] == 'user') ? 'selected' : ''; ?>>👤 User</option>
                                                    <option value="petugas" <?php echo ($row['role'] == 'petugas') ? 'selected' : ''; ?>>👨‍💼 Petugas</option>
                                                    <option value="admin" <?php echo ($row['role'] == 'admin') ? 'selected' : ''; ?>>👨‍💻 Admin</option>
                                                </select>
                                            </form>
                                            <a href="user_delete.php?id=<?php echo $row['id']; ?>" 
                                               class="btn-delete-user" 
                                               onclick="return confirm('⚠️ PERHATIAN!\n\nApakah Anda yakin ingin menghapus user:\n\nUsername: <?php echo htmlspecialchars($row['username']); ?>\nNama: <?php echo htmlspecialchars($row['nama_lengkap']); ?>\nTotal Booking: <?php echo $total_booking; ?>\n\n⚠️ Semua data booking dan history user ini akan ikut terhapus!\n\nTindakan ini TIDAK DAPAT dibatalkan!');">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge badge-info">Akun Anda</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Belum ada data user</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Tab Content: Reset Password Requests -->
        <?php if ($active_tab == 'reset_requests'): ?>
        <div class="card">
            <div class="card-header-flex">
                <h2><i class="fas fa-key"></i> Permintaan Reset Password</h2>
                <?php if ($pending_count > 0): ?>
                    <span class="pending-badge"><?php echo $pending_count; ?> Pending</span>
                <?php endif; ?>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th style="width: 150px;">Tanggal Request</th>
                        <th style="width: 120px;">Status</th>
                        <th>Diproses Oleh</th>
                        <th style="width: 200px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($reset_result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($reset_result)): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['nama_lengkap']); ?></strong><br>
                                <small class="text-muted">@<?php echo htmlspecialchars($row['username']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($row['request_date'])); ?><br>
                                <small class="text-muted"><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($row['request_date'])); ?></small>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'pending'): ?>
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                <?php elseif ($row['status'] == 'approved'): ?>
                                    <span class="status-badge status-approved">
                                        <i class="fas fa-check"></i> Approved
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-rejected">
                                        <i class="fas fa-times"></i> Rejected
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['admin_name']): ?>
                                    <strong><?php echo htmlspecialchars($row['admin_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($row['approved_date'])); ?></small>
                                    <?php if ($row['keterangan']): ?>
                                        <br><small class="text-info"><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($row['keterangan']); ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'pending'): ?>
                                    <div class="action-buttons-group">
                                        <button onclick="showApproveModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nama_lengkap']); ?>')" 
                                                class="btn-action btn-approve" 
                                                title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button onclick="showRejectModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nama_lengkap']); ?>')" 
                                                class="btn-action btn-reject" 
                                                title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted text-center" style="display:block;">Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-data-message">
                                <div class="no-data-content">
                                    <i class="fas fa-inbox"></i>
                                    <h3>Tidak Ada Permintaan</h3>
                                    <p>Belum ada permintaan reset password dari user</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Modal Approve -->
    <div id="approveModal" class="modal-overlay" style="display: none;">
        <div class="modal-content-form" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2><i class="fas fa-check-circle"></i> Approve Reset Password</h2>
                <button class="modal-close-btn" onclick="closeModal('approveModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div style="padding: 30px;">
                <div class="approval-info">
                    <p><strong>User:</strong> <span id="approve_user_name"></span></p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Dengan menyetujui permintaan ini, user akan dapat mereset password mereka sendiri melalui halaman login.
                    </div>
                </div>
                
                <form method="POST" action="?tab=reset_requests">
                    <input type="hidden" name="request_id" id="approve_request_id">
                    <div class="modal-footer">
                        <button type="button" onclick="closeModal('approveModal')" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" name="approve_reset" class="btn btn-success">
                            <i class="fas fa-check"></i> Approve Permintaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Reject -->
    <div id="rejectModal" class="modal-overlay" style="display: none;">
        <div class="modal-content-form" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2><i class="fas fa-times-circle"></i> Reject Reset Password</h2>
                <button class="modal-close-btn" onclick="closeModal('rejectModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div style="padding: 30px;">
                <p><strong>User:</strong> <span id="reject_user_name"></span></p>
                
                <form method="POST" action="?tab=reset_requests">
                    <input type="hidden" name="request_id" id="reject_request_id">
                    <div class="form-group">
                        <label><i class="fas fa-comment"></i> Alasan Penolakan (Opsional)</label>
                        <textarea name="keterangan" rows="3" placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" onclick="closeModal('rejectModal')" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" name="reject_reset" class="btn btn-danger">
                            <i class="fas fa-times"></i> Reject Permintaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    function showApproveModal(requestId, userName) {
        document.getElementById('approve_request_id').value = requestId;
        document.getElementById('approve_user_name').innerText = userName;
        document.getElementById('approveModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function showRejectModal(requestId, userName) {
        document.getElementById('reject_request_id').value = requestId;
        document.getElementById('reject_user_name').innerText = userName;
        document.getElementById('rejectModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('approveModal');
            closeModal('rejectModal');
        }
    });
    
    // Close modal when clicking outside
    document.getElementById('approveModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal('approveModal');
    });
    
    document.getElementById('rejectModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal('rejectModal');
    });
    </script>
</body>
</html>
