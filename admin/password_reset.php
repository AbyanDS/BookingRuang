<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

// Cek apakah user sudah login dan adalah admin
if (!is_logged_in() || !is_admin()) {
    header('Location: /PlsworkUKK/login.php');
    exit;
}

$success_message = '';
$error_message = '';

// Proses approve/reject reset password
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve'])) {
        $request_id = (int)$_POST['request_id'];
        $admin_id = $_SESSION['user_id'];
        
        // Get user_id from request
        $query_user = "SELECT pr.user_id, u.email, u.nama_lengkap FROM password_reset_requests pr 
                       JOIN users u ON pr.user_id = u.id 
                       WHERE pr.id = ?";
        $stmt_user = mysqli_prepare($conn, $query_user);
        mysqli_stmt_bind_param($stmt_user, "i", $request_id);
        mysqli_stmt_execute($stmt_user);
        $result_user = mysqli_stmt_get_result($stmt_user);
        $request_data = mysqli_fetch_assoc($result_user);
        
        if ($request_data) {
            // Generate unique token for reset password link
            $reset_token = bin2hex(random_bytes(32));
            $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours')); // Valid for 24 hours
            
            // Update request status to approved with token
            $update_request = "UPDATE password_reset_requests 
                              SET status = 'approved', 
                                  approved_by = ?, 
                                  approved_date = NOW(),
                                  reset_token = ?,
                                  token_expiry = ?
                              WHERE id = ?";
            $stmt_request = mysqli_prepare($conn, $update_request);
            mysqli_stmt_bind_param($stmt_request, "issi", $admin_id, $reset_token, $token_expiry, $request_id);
            
            if (mysqli_stmt_execute($stmt_request)) {
                // Generate reset link
                $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
                $reset_link = $base_url . "/PlsworkUKK/reset_password.php?token=" . $reset_token;
                
                $success_message = "Permintaan reset password telah disetujui! Link reset password: <br><br>
                                   <div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0; word-break: break-all;'>
                                       <strong>Link Reset:</strong><br>
                                       <a href='$reset_link' target='_blank' style='color: #667eea;'>$reset_link</a>
                                   </div>
                                   <br><strong>📧 Email User:</strong> " . htmlspecialchars($request_data['email']) . "<br>
                                   <strong>👤 Nama:</strong> " . htmlspecialchars($request_data['nama_lengkap']) . "<br><br>
                                   <div class='alert alert-info' style='background: #d1ecf1; padding: 12px; border-radius: 8px; margin-top: 10px;'>
                                       💡 <strong>Info:</strong> Link ini valid selama <strong>24 jam</strong>. Kirimkan link ini ke user via email atau WhatsApp.
                                   </div>";
            } else {
                $error_message = "Gagal menyetujui permintaan!";
            }
        }
    }
    
    if (isset($_POST['reject'])) {
        $request_id = (int)$_POST['request_id'];
        $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
        $admin_id = $_SESSION['user_id'];
        
        $update_request = "UPDATE password_reset_requests SET status = 'rejected', approved_by = ?, approved_date = NOW(), keterangan = ? WHERE id = ?";
        $stmt_request = mysqli_prepare($conn, $update_request);
        mysqli_stmt_bind_param($stmt_request, "isi", $admin_id, $keterangan, $request_id);
        
        if (mysqli_stmt_execute($stmt_request)) {
            $success_message = "Permintaan reset password berhasil ditolak!";
        } else {
            $error_message = "Gagal menolak permintaan!";
        }
    }
}

// Ambil data request reset password
$query = "SELECT pr.*, u.username, u.nama_lengkap, u.email, 
          admin.nama_lengkap as admin_name,
          pr.reset_token, pr.token_expiry, pr.request_email
          FROM password_reset_requests pr
          JOIN users u ON pr.user_id = u.id
          LEFT JOIN users admin ON pr.approved_by = admin.id
          ORDER BY 
            CASE pr.status 
                WHEN 'pending' THEN 1
                WHEN 'approved' THEN 2
                WHEN 'rejected' THEN 3
                WHEN 'completed' THEN 4
            END,
            pr.request_date DESC";
$result = mysqli_query($conn, $query);

$page_title = 'Password Reset Requests';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container">
    <div class="page-header">
        <h1>🔐 Permintaan Reset Password</h1>
        <p>Kelola permintaan reset password dari user</p>
    </div>

    <?php if ($success_message): ?>
    <div class="alert alert-success">
        <span class="alert-icon">✓</span>
        <?php echo $success_message; ?>
    </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
    <div class="alert alert-error">
        <span class="alert-icon">✕</span>
        <?php echo $error_message; ?>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Alasan</th>
                            <th>Tanggal Request</th>
                            <th>Status</th>
                            <th>Diproses Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['nama_lengkap']); ?></strong><br>
                                    <small>@<?php echo htmlspecialchars($row['username']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <?php if ($row['keterangan']): ?>
                                        <small><?php echo htmlspecialchars($row['keterangan']); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['request_date'])); ?></td>
                                <td>
                                    <?php if ($row['status'] == 'pending'): ?>
                                        <span class="badge badge-warning">⏳ Pending</span>
                                    <?php elseif ($row['status'] == 'approved'): ?>
                                        <span class="badge badge-success">✅ Approved</span>
                                        <?php if ($row['token_expiry']): ?>
                                            <br><small style="color: <?php echo strtotime($row['token_expiry']) > time() ? '#28a745' : '#dc3545'; ?>;">
                                                Exp: <?php echo date('d/m H:i', strtotime($row['token_expiry'])); ?>
                                            </small>
                                        <?php endif; ?>
                                    <?php elseif ($row['status'] == 'completed'): ?>
                                        <span class="badge badge-info">✔️ Completed</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">❌ Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['admin_name']): ?>
                                        <?php echo htmlspecialchars($row['admin_name']); ?><br>
                                        <small><?php echo date('d/m/Y H:i', strtotime($row['approved_date'])); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['status'] == 'pending'): ?>
                                        <button type="button" class="btn btn-sm btn-success" onclick="showApproveModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nama_lengkap']); ?>', '<?php echo htmlspecialchars($row['email']); ?>')">
                                            ✓ Approve
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="showRejectModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nama_lengkap']); ?>')">
                                            ✕ Reject
                                        </button>
                                    <?php elseif ($row['status'] == 'approved' && $row['reset_token']): ?>
                                        <?php 
                                        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
                                        $reset_link = $base_url . "/PlsworkUKK/reset_password.php?token=" . $row['reset_token'];
                                        ?>
                                        <button type="button" class="btn btn-sm btn-info" onclick="showResetLink('<?php echo $reset_link; ?>', '<?php echo htmlspecialchars($row['email']); ?>')">
                                            🔗 Lihat Link
                                        </button>
                                        <a href="https://wa.me/?text=<?php echo urlencode("Halo " . $row['nama_lengkap'] . ", berikut link reset password Anda:\n\n" . $reset_link . "\n\nLink valid selama 24 jam."); ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-success" 
                                           style="background: #25D366;">
                                            📱 WA
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Selesai</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada permintaan reset password</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Approve -->
<div id="approveModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal('approveModal')">&times;</span>
        <h2>✅ Approve Reset Password</h2>
        <p>User: <strong id="approve_user_name"></strong></p>
        <p>Email: <strong id="approve_user_email"></strong></p>
        <div class="alert alert-info" style="background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 15px 0;">
            <strong>ℹ️ Informasi:</strong><br>
            Dengan menyetujui permintaan ini, sistem akan:<br>
            • Generate <strong>link reset password</strong> yang valid 24 jam<br>
            • Link dapat dikirim ke user via email/WhatsApp<br>
            • User dapat mereset password sendiri menggunakan link tersebut
        </div>
        <form method="POST">
            <input type="hidden" name="request_id" id="approve_request_id">
            <button type="submit" name="approve" class="btn btn-success">✅ Approve & Generate Link</button>
            <button type="button" class="btn btn-secondary" onclick="closeModal('approveModal')">Batal</button>
        </form>
    </div>
</div>

<!-- Modal Reset Link -->
<div id="resetLinkModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <span class="close" onclick="closeModal('resetLinkModal')">&times;</span>
        <h2>🔗 Link Reset Password</h2>
        <p>Email User: <strong id="reset_link_email"></strong></p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; word-break: break-all;">
            <strong>Link Reset Password:</strong><br>
            <a href="#" id="reset_link_url" target="_blank" style="color: #667eea; font-size: 14px;"></a>
        </div>
        <button type="button" class="btn btn-primary" onclick="copyResetLink()">📋 Copy Link</button>
        <button type="button" class="btn btn-success" onclick="shareViaWhatsApp()" style="background: #25D366;">📱 Share via WhatsApp</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal('resetLinkModal')">Tutup</button>
        <div class="alert alert-warning" style="background: #fff3cd; padding: 12px; border-radius: 8px; margin-top: 15px;">
            ⚠️ Link valid selama <strong>24 jam</strong> sejak dibuat.
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div id="rejectModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal('rejectModal')">&times;</span>
        <h2>Reject Reset Password</h2>
        <p>User: <strong id="reject_user_name"></strong></p>
        <form method="POST">
            <input type="hidden" name="request_id" id="reject_request_id">
            <div class="form-group">
                <label>Alasan Penolakan (Opsional)</label>
                <textarea name="keterangan" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" name="reject" class="btn btn-danger">Reject Permintaan</button>
            <button type="button" class="btn btn-secondary" onclick="closeModal('rejectModal')">Batal</button>
        </form>
    </div>
</div>

<style>
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 30px;
    border-radius: 10px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #000;
}

.badge {
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: bold;
}

.badge-warning {
    background: #ffc107;
    color: #000;
}

.badge-success {
    background: #28a745;
    color: white;
}

.badge-danger {
    background: #dc3545;
    color: white;
}

.text-info {
    color: #17a2b8;
    font-size: 14px;
    margin: 15px 0;
}
</style>

<script>
let currentResetLink = '';

function showApproveModal(requestId, userName, userEmail) {
    document.getElementById('approve_request_id').value = requestId;
    document.getElementById('approve_user_name').innerText = userName;
    document.getElementById('approve_user_email').innerText = userEmail;
    document.getElementById('approveModal').style.display = 'block';
}

function showRejectModal(requestId, userName) {
    document.getElementById('reject_request_id').value = requestId;
    document.getElementById('reject_user_name').innerText = userName;
    document.getElementById('rejectModal').style.display = 'block';
}

function showResetLink(resetLink, userEmail) {
    currentResetLink = resetLink;
    document.getElementById('reset_link_url').href = resetLink;
    document.getElementById('reset_link_url').innerText = resetLink;
    document.getElementById('reset_link_email').innerText = userEmail;
    document.getElementById('resetLinkModal').style.display = 'block';
}

function copyResetLink() {
    navigator.clipboard.writeText(currentResetLink).then(function() {
        alert('✅ Link berhasil dicopy ke clipboard!');
    }, function(err) {
        alert('❌ Gagal copy link: ' + err);
    });
}

function shareViaWhatsApp() {
    const email = document.getElementById('reset_link_email').innerText;
    const message = `Halo, berikut link reset password untuk akun ${email}:\n\n${currentResetLink}\n\nLink valid selama 24 jam. Silakan klik link tersebut untuk mereset password Anda.`;
    window.open('https://wa.me/?text=' + encodeURIComponent(message), '_blank');
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
