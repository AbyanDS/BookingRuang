<?php
session_start();
require_once 'config/database.php';
require_once 'config/functions.php';

// Jika sudah login, redirect ke dashboard
if (is_logged_in()) {
    header("Location: index.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = clean_input($_POST['email']);
    $keterangan = clean_input($_POST['keterangan']); // Alasan reset
    
    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Cek apakah email terdaftar
        $query = "SELECT id, username, nama_lengkap FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Cek apakah ada request pending
            $check_query = "SELECT id FROM password_reset_requests WHERE user_id = ? AND status = 'pending'";
            $stmt_check = mysqli_prepare($conn, $check_query);
            mysqli_stmt_bind_param($stmt_check, "i", $user['id']);
            mysqli_stmt_execute($stmt_check);
            $check_result = mysqli_stmt_get_result($stmt_check);
            
            if (mysqli_num_rows($check_result) > 0) {
                $error = 'Anda sudah memiliki permintaan reset password yang sedang diproses. Harap tunggu verifikasi admin.';
            } else {
                // Buat request reset password dengan keterangan
                $insert_query = "INSERT INTO password_reset_requests (user_id, keterangan, request_email) VALUES (?, ?, ?)";
                $stmt_insert = mysqli_prepare($conn, $insert_query);
                mysqli_stmt_bind_param($stmt_insert, "iss", $user['id'], $keterangan, $email);
                
                if (mysqli_stmt_execute($stmt_insert)) {
                    $success = 'Permintaan reset password berhasil dikirim!';
                } else {
                    $error = 'Gagal mengirim permintaan reset password!';
                }
            }
        } else {
            $error = 'Email tidak terdaftar dalam sistem!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Reset Password - Sistem Booking Ruangan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <div class="auth-container">
            <div class="auth-box">
                <h2>Reset Password</h2>
                <p class="text-muted">Masukkan email Anda untuk mengajukan permintaan reset password. Admin akan memverifikasi permintaan Anda.</p>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <h3 style="margin-top: 0;">✅ <?php echo $success; ?></h3>
                    </div>
                    
                    <div class="alert alert-info" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 15px; margin: 20px 0;">
                        <h3 style="margin-top: 0; color: white;">📞 Hubungi Admin untuk Percepatan</h3>
                        <div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 10px; margin: 15px 0;">
                            <p style="margin: 5px 0; font-size: 16px;"><strong>WhatsApp Admin:</strong></p>
                            <a href="https://wa.me/6281234567890?text=Halo%20Admin,%20saya%20ingin%20reset%20password%20untuk%20email:%20<?php echo urlencode($email); ?>" 
                               target="_blank" 
                               style="display: inline-block; background: #25D366; color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-top: 10px;">
                                <span style="font-size: 20px;">📱</span> Chat WhatsApp Admin
                            </a>
                            <p style="margin: 10px 0; font-size: 14px; opacity: 0.9;">
                                Nomor: <strong>+62 812-3456-7890</strong>
                            </p>
                        </div>
                        <p style="margin: 10px 0; font-size: 14px;">
                            💡 <strong>Tips:</strong> Kirim screenshot permintaan ini atau sebutkan email Anda kepada admin untuk mempercepat proses verifikasi.
                        </p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <strong>📋 Langkah selanjutnya:</strong><br>
                        1. Hubungi admin via WhatsApp di atas (opsional, untuk mempercepat)<br>
                        2. Tunggu admin menyetujui permintaan Anda<br>
                        3. Setelah disetujui, Anda akan menerima <strong>link reset password</strong><br>
                        4. Buka link tersebut dan buat password baru Anda
                    </div>
                    
                    <a href="login.php" class="btn btn-secondary btn-block" style="margin-top: 10px;">Kembali ke Login</a>
                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Email Terdaftar</label>
                            <input type="email" name="email" placeholder="email@example.com" required>
                            <small class="text-muted">Masukkan email yang terdaftar di sistem</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Alasan Reset Password</label>
                            <textarea name="keterangan" rows="3" placeholder="Contoh: Lupa password, akun terkunci, dll" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit;"></textarea>
                            <small class="text-muted">Jelaskan alasan Anda meminta reset password</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">📧 Kirim Permintaan</button>
                    </form>
                    
                    <p class="text-center">
                        <a href="login.php">Kembali ke Login</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
