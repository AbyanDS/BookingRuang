<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

require_admin();

if (isset($_GET['id'])) {
    $user_id = clean_input($_GET['id']);
    
    // Cek apakah user mencoba menghapus dirinya sendiri
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = 'Anda tidak dapat menghapus akun Anda sendiri!';
        header('Location: users.php');
        exit;
    }
    
    // Cek apakah user memiliki booking
    $check_booking = "SELECT COUNT(*) as total FROM booking WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $check_booking);
    $total_booking = mysqli_fetch_assoc($result)['total'];
    
    if ($total_booking > 0) {
        // Hapus history peminjaman user terlebih dahulu
        $delete_history = "DELETE FROM history_peminjaman WHERE user_id = '$user_id'";
        mysqli_query($conn, $delete_history);
        
        // Hapus booking user
        $delete_booking = "DELETE FROM booking WHERE user_id = '$user_id'";
        mysqli_query($conn, $delete_booking);
    }
    
    // Hapus user
    $delete_user = "DELETE FROM users WHERE id = '$user_id'";
    
    if (mysqli_query($conn, $delete_user)) {
        $_SESSION['success'] = 'User berhasil dihapus!';
    } else {
        $_SESSION['error'] = 'Gagal menghapus user: ' . mysqli_error($conn);
    }
} else {
    $_SESSION['error'] = 'ID user tidak ditemukan!';
}

header('Location: users.php');
exit;
?>
