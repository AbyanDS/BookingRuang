<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

// Cek apakah user sudah login
if (!is_logged_in()) {
    header('Location: /PlsworkUKK/login.php');
    exit;
}

// Cek apakah user adalah user biasa
if (!is_user()) {
    header('Location: /PlsworkUKK/index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Ambil data user
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Proses update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Proses upload profile picture
    if (isset($_POST['upload_photo'])) {
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            $file_type = $_FILES['profile_picture']['type'];
            $file_size = $_FILES['profile_picture']['size'];
            
            if (!in_array($file_type, $allowed_types)) {
                $error_message = "Format file tidak valid! Gunakan JPG, PNG, atau GIF.";
            } elseif ($file_size > 2000000) { // 2MB
                $error_message = "Ukuran file terlalu besar! Maksimal 2MB.";
            } else {
                $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
                $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
                $upload_dir = '../uploads/profile/';
                
                // Buat folder jika belum ada
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $target_file = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                    // Hapus foto lama jika ada
                    if (isset($user['profile_picture']) && $user['profile_picture'] && file_exists($upload_dir . $user['profile_picture'])) {
                        unlink($upload_dir . $user['profile_picture']);
                    }
                    
                    // Update database
                    $update_photo_query = "UPDATE users SET profile_picture = ? WHERE id = ?";
                    $stmt_photo = mysqli_prepare($conn, $update_photo_query);
                    mysqli_stmt_bind_param($stmt_photo, "si", $new_filename, $user_id);
                    
                    if (mysqli_stmt_execute($stmt_photo)) {
                        $success_message = "Foto profile berhasil diupload!";
                        $user['profile_picture'] = $new_filename;
                    } else {
                        $error_message = "Gagal menyimpan foto profile!";
                    }
                } else {
                    $error_message = "Gagal mengupload file!";
                }
            }
        } else {
            $error_message = "Pilih file terlebih dahulu!";
        }
    }
    
    // Proses hapus profile picture
    if (isset($_POST['delete_photo'])) {
        if (isset($user['profile_picture']) && $user['profile_picture']) {
            $upload_dir = '../uploads/profile/';
            if (file_exists($upload_dir . $user['profile_picture'])) {
                unlink($upload_dir . $user['profile_picture']);
            }
            
            $delete_photo_query = "UPDATE users SET profile_picture = NULL WHERE id = ?";
            $stmt_delete = mysqli_prepare($conn, $delete_photo_query);
            mysqli_stmt_bind_param($stmt_delete, "i", $user_id);
            
            if (mysqli_stmt_execute($stmt_delete)) {
                $success_message = "Foto profile berhasil dihapus!";
                $user['profile_picture'] = null;
            } else {
                $error_message = "Gagal menghapus foto profile!";
            }
        }
    }
    
    if (isset($_POST['update_profile'])) {
        $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        
        // Validasi username tidak kosong
        if (empty($username)) {
            $error_message = "Username tidak boleh kosong!";
        } elseif (strlen($username) < 3) {
            $error_message = "Username minimal 3 karakter!";
        } else {
            // Cek apakah username sudah digunakan user lain
            $check_username = "SELECT id FROM users WHERE username = ? AND id != ?";
            $stmt_check = mysqli_prepare($conn, $check_username);
            mysqli_stmt_bind_param($stmt_check, "si", $username, $user_id);
            mysqli_stmt_execute($stmt_check);
            $result_check = mysqli_stmt_get_result($stmt_check);
            
            if (mysqli_num_rows($result_check) > 0) {
                $error_message = "Username sudah digunakan oleh user lain!";
            } else {
                // Update profile
                $update_query = "UPDATE users SET nama_lengkap = ?, username = ? WHERE id = ?";
                $stmt_update = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt_update, "ssi", $nama_lengkap, $username, $user_id);
                
                if (mysqli_stmt_execute($stmt_update)) {
                    $success_message = "Profile berhasil diperbarui!";
                    // Refresh data user dan session
                    $user['nama_lengkap'] = $nama_lengkap;
                    $user['username'] = $username;
                    $_SESSION['username'] = $username;
                } else {
                    $error_message = "Gagal memperbarui profile!";
                }
            }
        }
    }
    
    // Proses update password
    if (isset($_POST['update_password'])) {
        $password_lama = $_POST['password_lama'];
        $password_baru = $_POST['password_baru'];
        $konfirmasi_password = $_POST['konfirmasi_password'];
        
        // Validasi password lama
        if (!password_verify($password_lama, $user['password'])) {
            $error_message = "Password lama tidak sesuai!";
        } elseif ($password_baru !== $konfirmasi_password) {
            $error_message = "Konfirmasi password tidak cocok!";
        } elseif (strlen($password_baru) < 6) {
            $error_message = "Password baru minimal 6 karakter!";
        } else {
            // Update password
            $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
            $update_pass_query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt_pass = mysqli_prepare($conn, $update_pass_query);
            mysqli_stmt_bind_param($stmt_pass, "si", $hashed_password, $user_id);
            
            if (mysqli_stmt_execute($stmt_pass)) {
                $success_message = "Password berhasil diperbarui!";
            } else {
                $error_message = "Gagal memperbarui password!";
            }
        }
    }
}

// Statistik booking user
$stats_query = "SELECT 
    COUNT(*) as total_booking,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
FROM booking WHERE user_id = ?";
$stmt_stats = mysqli_prepare($conn, $stats_query);
mysqli_stmt_bind_param($stmt_stats, "i", $user_id);
mysqli_stmt_execute($stmt_stats);
$stats_result = mysqli_stmt_get_result($stmt_stats);
$stats = mysqli_fetch_assoc($stats_result);

$page_title = 'Profile';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-profile">
    <div class="profile-header">
        <div class="profile-header-content">
            <div class="profile-avatar">
                <?php if (isset($user['profile_picture']) && $user['profile_picture']): ?>
                    <img src="/PlsworkUKK/uploads/profile/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="avatar-image">
                <?php else: ?>
                    <span class="avatar-text"><?php echo strtoupper(substr($user['nama_lengkap'], 0, 2)); ?></span>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($user['nama_lengkap']); ?></h1>
                <p class="profile-username">@<?php echo htmlspecialchars($user['username']); ?></p>
                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                <p class="profile-member-since">Member sejak <?php echo date('d F Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>
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

    <div class="profile-content">
        <!-- Statistik Booking -->
        <div class="stats-section">
            <h2 class="section-title">Statistik Booking</h2>
            <div class="stats-grid">
                <div class="stat-card stat-total">
                    <div class="stat-icon">📊</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_booking']; ?></h3>
                        <p>Total Booking</p>
                    </div>
                </div>
                <div class="stat-card stat-pending">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['pending']; ?></h3>
                        <p>Pending</p>
                    </div>
                </div>
                <div class="stat-card stat-approved">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['approved']; ?></h3>
                        <p>Approved</p>
                    </div>
                </div>
                <div class="stat-card stat-completed">
                    <div class="stat-icon">✔️</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['completed']; ?></h3>
                        <p>Completed</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="profile-forms">
            <!-- Form Upload Photo Profile -->
            <div class="form-section">
                <h2 class="section-title">Foto Profile</h2>
                <div class="photo-preview-container">
                    <div class="photo-preview">
                        <?php if (isset($user['profile_picture']) && $user['profile_picture']): ?>
                            <img src="/PlsworkUKK/uploads/profile/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" id="preview_image">
                        <?php else: ?>
                            <div class="photo-preview-placeholder" id="preview_placeholder">
                                <span class="placeholder-icon">📷</span>
                                <p>Belum ada foto</p>
                            </div>
                            <img src="" alt="Preview" id="preview_image" style="display: none;">
                        <?php endif; ?>
                    </div>
                </div>
                <form method="POST" enctype="multipart/form-data" class="profile-form" id="form_upload_photo">
                    <div class="form-group">
                        <label for="profile_picture">Pilih Foto</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="input-file" onchange="previewPhoto(this)">
                        <small class="form-hint">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                    </div>
                    <div class="photo-actions">
                        <button type="submit" name="upload_photo" class="btn btn-primary">
                            <span>📤</span> Upload Foto
                        </button>
                        <?php if (isset($user['profile_picture']) && $user['profile_picture']): ?>
                        <button type="submit" name="delete_photo" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus foto profile?')">
                            <span>🗑️</span> Hapus Foto
                        </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- Form Edit Profile -->
            <div class="form-section">
                <h2 class="section-title">Edit Profile</h2>
                <form method="POST" class="profile-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required class="input-field" minlength="3">
                        <small class="form-hint">Minimal 3 karakter</small>
                    </div>
                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required class="input-field">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled class="input-disabled">
                        <small class="form-hint">Email tidak dapat diubah</small>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <span>💾</span> Update Profile
                    </button>
                </form>
            </div>

            <!-- Form Ubah Password -->
            <div class="form-section">
                <h2 class="section-title">Ubah Password</h2>
                <form method="POST" class="profile-form">
                    <div class="form-group">
                        <label for="password_lama">Password Lama</label>
                        <input type="password" id="password_lama" name="password_lama" required class="input-field">
                    </div>
                    <div class="form-group">
                        <label for="password_baru">Password Baru</label>
                        <input type="password" id="password_baru" name="password_baru" required class="input-field" minlength="6">
                        <small class="form-hint">Minimal 6 karakter</small>
                    </div>
                    <div class="form-group">
                        <label for="konfirmasi_password">Konfirmasi Password Baru</label>
                        <input type="password" id="konfirmasi_password" name="konfirmasi_password" required class="input-field" minlength="6">
                    </div>
                    <button type="submit" name="update_password" class="btn btn-warning">
                        <span>🔒</span> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.container-profile {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 40px;
    color: white;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    margin-bottom: 30px;
}

.profile-header-content {
    display: flex;
    align-items: center;
    gap: 30px;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 4px solid rgba(255, 255, 255, 0.3);
    overflow: hidden;
}

.avatar-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-text {
    font-size: 48px;
    font-weight: bold;
    color: white;
}

.profile-info h1 {
    margin: 0 0 10px 0;
    font-size: 32px;
}

.profile-username {
    font-size: 18px;
    opacity: 0.9;
    margin: 5px 0;
}

.profile-email {
    font-size: 16px;
    opacity: 0.8;
    margin: 5px 0;
}

.profile-member-since {
    font-size: 14px;
    opacity: 0.7;
    margin-top: 10px;
}

.alert {
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-icon {
    font-size: 20px;
    font-weight: bold;
}

.profile-content {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.section-title {
    font-size: 24px;
    margin-bottom: 20px;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title::before {
    content: '';
    width: 4px;
    height: 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 2px;
}

.stats-section {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.stat-card {
    padding: 25px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 2px solid transparent;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.stat-total {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stat-pending {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.stat-approved {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.stat-completed {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
}

.stat-icon {
    font-size: 40px;
    color: white;
}

.stat-details h3 {
    font-size: 36px;
    margin: 0;
    font-weight: bold;
    color: white;
}

.stat-details p {
    margin: 5px 0 0 0;
    font-size: 14px;
    opacity: 0.9;
    color: white;
}

.profile-forms {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 30px;
}

.form-section {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.profile-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
}

.input-field, .input-disabled {
    padding: 12px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.input-field:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.input-disabled {
    background: #f5f5f5;
    color: #999;
    cursor: not-allowed;
}

.form-hint {
    font-size: 12px;
    color: #666;
    font-style: italic;
}

.btn {
    padding: 14px 28px;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(240, 147, 251, 0.4);
}

.btn:active {
    transform: translateY(0);
}

.btn:active {
    transform: translateY(0);
}

/* Photo Upload Styles */
.photo-preview-container {
    margin-bottom: 20px;
}

.photo-preview {
    width: 200px;
    height: 200px;
    margin: 0 auto;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid #e0e0e0;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
}

.photo-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.photo-preview-placeholder {
    text-align: center;
    color: #999;
}

.placeholder-icon {
    font-size: 60px;
    display: block;
    margin-bottom: 10px;
}

.photo-preview-placeholder p {
    margin: 0;
    font-size: 14px;
}

.input-file {
    padding: 12px;
    border: 2px dashed #e0e0e0;
    border-radius: 10px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fafafa;
}

.input-file:hover {
    border-color: #667eea;
    background: #f0f0ff;
}

.photo-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-danger {
    background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
    color: white;
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 87, 108, 0.4);
}

@media (max-width: 992px) {
    .profile-header-content {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-forms {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .profile-header {
        padding: 30px 20px;
    }
    
    .profile-avatar {
        width: 100px;
        height: 100px;
    }
    
    .avatar-text {
        font-size: 40px;
    }
    
    .profile-info h1 {
        font-size: 24px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .form-section {
        padding: 20px;
    }
}
</style>

<script>
function previewPhoto(input) {
    const preview = document.getElementById('preview_image');
    const placeholder = document.getElementById('preview_placeholder');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (placeholder) {
                placeholder.style.display = 'none';
            }
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
