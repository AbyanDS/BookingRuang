<?php
session_start();
require_once 'config/database.php';
require_once 'config/functions.php';

$success = '';
$error = '';
$can_reset = false;
$user_email = '';
$user_name = '';
$token = '';

// Cek apakah ada token di URL
if (isset($_GET['token'])) {
    $token = clean_input($_GET['token']);
    
    // Validasi token
    $query = "SELECT pr.*, u.email, u.username, u.nama_lengkap 
              FROM password_reset_requests pr
              JOIN users u ON pr.user_id = u.id
              WHERE pr.reset_token = ? 
              AND pr.status = 'approved'
              AND pr.token_expiry > NOW()
              LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $request_data = mysqli_fetch_assoc($result);
        $can_reset = true;
        $user_email = $request_data['email'];
        $user_name = $request_data['nama_lengkap'];
        $user_id = $request_data['user_id'];
        $request_id = $request_data['id'];
    } else {
        $error = 'Link reset password tidak valid atau sudah kadaluarsa. Silakan ajukan permintaan reset password baru.';
    }
}

// Proses reset password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $token = clean_input($_POST['token']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Validasi token lagi
        $query_check = "SELECT pr.user_id, pr.id as request_id
                        FROM password_reset_requests pr
                        WHERE pr.reset_token = ? 
                        AND pr.status = 'approved'
                        AND pr.token_expiry > NOW()
                        LIMIT 1";
        $stmt_check = mysqli_prepare($conn, $query_check);
        mysqli_stmt_bind_param($stmt_check, "s", $token);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        
        if (mysqli_num_rows($result_check) > 0) {
            $check_data = mysqli_fetch_assoc($result_check);
            $user_id = $check_data['user_id'];
            $request_id = $check_data['request_id'];
            
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_pass = "UPDATE users SET password = ? WHERE id = ?";
            $stmt_pass = mysqli_prepare($conn, $update_pass);
            mysqli_stmt_bind_param($stmt_pass, "si", $hashed_password, $user_id);
            
            if (mysqli_stmt_execute($stmt_pass)) {
                // Update request status menjadi completed
                $update_request = "UPDATE password_reset_requests SET status = 'completed' WHERE id = ?";
                $stmt_request = mysqli_prepare($conn, $update_request);
                mysqli_stmt_bind_param($stmt_request, "i", $request_id);
                mysqli_stmt_execute($stmt_request);
                
                $success = 'Password berhasil direset! Silakan login dengan password baru Anda.';
                $can_reset = false;
            } else {
                $error = 'Gagal mereset password. Silakan coba lagi.';
            }
        } else {
            $error = 'Link reset password tidak valid atau sudah kadaluarsa.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Sistem Booking Ruangan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <div class="auth-container">
            <div class="auth-box">
                <h2>Reset Password</h2>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <h3 style="margin-top: 0;">✅ <?php echo $success; ?></h3>
                    </div>
                    <a href="login.php" class="btn btn-primary btn-block">🔐 Login Sekarang</a>
                <?php elseif ($can_reset): ?>
                    <div class="alert alert-info" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 12px;">
                        <h3 style="margin-top: 0; color: white;">👤 Reset Password</h3>
                        <p style="margin: 5px 0;"><strong>Nama:</strong> <?php echo htmlspecialchars($user_name); ?></p>
                        <p style="margin: 5px 0;"><strong>Email:</strong> <?php echo htmlspecialchars($user_email); ?></p>
                    </div>
                    
                    <p class="text-muted">Masukkan password baru Anda di bawah ini:</p>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        
                        <div class="form-group">
                            <label>🔒 Password Baru</label>
                            <input type="password" name="new_password" required minlength="6" placeholder="Minimal 6 karakter" autocomplete="new-password">
                            <small class="text-muted">Password minimal 6 karakter</small>
                        </div>
                        
                        <div class="form-group">
                            <label>🔒 Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" required minlength="6" placeholder="Ulangi password baru" autocomplete="new-password">
                            <small class="text-muted">Ulangi password untuk konfirmasi</small>
                        </div>
                        
                        <button type="submit" name="reset_password" class="btn btn-primary btn-block">🔄 Reset Password</button>
                    </form>
                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <strong>❌ <?php echo $error; ?></strong>
                        </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-warning" style="background: #fff3cd; padding: 20px; border-radius: 10px;">
                        <h4 style="margin-top: 0;">⚠️ Link Reset Password Diperlukan</h4>
                        <p>Untuk mereset password, Anda memerlukan <strong>link reset password</strong> yang dikirim oleh admin.</p>
                        <p><strong>Langkah-langkah:</strong></p>
                        <ol style="text-align: left; margin-left: 20px;">
                            <li>Ajukan permintaan reset password</li>
                            <li>Hubungi admin untuk percepatan</li>
                            <li>Tunggu admin menyetujui</li>
                            <li>Admin akan mengirim link reset via email/WhatsApp</li>
                            <li>Klik link tersebut untuk mereset password</li>
                        </ol>
                    </div>
                    
                    <a href="request_reset_password.php" class="btn btn-primary btn-block">📧 Ajukan Reset Password</a>
                <?php endif; ?>
                
                <p class="text-center" style="margin-top: 20px;">
                    <a href="login.php">Kembali ke Login</a>
                </p>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
