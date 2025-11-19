<?php
// Fungsi untuk mengamankan input
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Fungsi untuk mengecek login
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk mengecek role admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Fungsi untuk mengecek role petugas
function is_petugas() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'petugas';
}

// Fungsi untuk mengecek role admin atau petugas
function is_admin_or_petugas() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'petugas');
}

// Fungsi untuk mengecek role user
function is_user() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

// Fungsi untuk redirect jika belum login
function require_login() {
    if (!is_logged_in()) {
        header("Location: /PlsworkUKK/login.php");
        exit();
    }
}

// Fungsi untuk redirect jika bukan admin
function require_admin() {
    require_login();
    if (!is_admin()) {
        // Redirect ke user dashboard jika bukan admin
        if (is_petugas()) {
            header("Location: /PlsworkUKK/admin/index.php");
        } else {
            header("Location: /PlsworkUKK/user/index.php");
        }
        exit();
    }
}

// Fungsi untuk redirect jika bukan admin atau petugas
function require_admin_or_petugas() {
    require_login();
    if (!is_admin_or_petugas()) {
        // Redirect ke user dashboard jika bukan admin/petugas
        header("Location: /PlsworkUKK/user/index.php");
        exit();
    }
}

// Fungsi untuk format tanggal Indonesia
function format_tanggal($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// Fungsi untuk format waktu
function format_waktu($waktu) {
    return date('H:i', strtotime($waktu));
}

// Fungsi untuk cek ketersediaan ruangan
function cek_ketersediaan_ruangan($ruangan_id, $tanggal, $waktu_mulai, $waktu_selesai, $booking_id = null) {
    global $conn;
    
    // Cek booking yang sudah ada
    $query = "SELECT * FROM booking 
              WHERE ruangan_id = '$ruangan_id' 
              AND tanggal_booking = '$tanggal'
              AND status NOT IN ('rejected', 'cancelled')
              AND (
                  (waktu_mulai < '$waktu_selesai' AND waktu_selesai > '$waktu_mulai')
              )";
    
    if ($booking_id) {
        $query .= " AND id != '$booking_id'";
    }
    
    $result = mysqli_query($conn, $query);
    
    // Jika ada booking yang bertabrakan, return false
    if (mysqli_num_rows($result) > 0) {
        return false;
    }
    
    // Cek jadwal ruangan tetap berdasarkan hari
    $day_of_week = date('N', strtotime($tanggal)); // 1 (Monday) through 7 (Sunday)
    $day_names = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    $hari = $day_names[$day_of_week];
    
    $query_jadwal = "SELECT * FROM jadwal_ruangan 
                     WHERE ruangan_id = '$ruangan_id' 
                     AND hari = '$hari'
                     AND status = 'aktif'
                     AND (
                         (waktu_mulai < '$waktu_selesai' AND waktu_selesai > '$waktu_mulai')
                     )";
    
    $result_jadwal = mysqli_query($conn, $query_jadwal);
    
    // Jika ada jadwal yang bertabrakan, return false
    if (mysqli_num_rows($result_jadwal) > 0) {
        return false;
    }
    
    return true;
}

// Fungsi untuk upload foto
function upload_foto($file, $folder = '../uploads/') {
    $target_dir = $folder;
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Cek apakah file adalah gambar
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
    if (!in_array($file_extension, $allowed_types)) {
        return false;
    }
    
    // Cek ukuran file (max 5MB)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_filename;
    }
    
    return false;
}

// Fungsi untuk delete foto
function delete_foto($filename, $folder = '../uploads/') {
    if ($filename && file_exists($folder . $filename)) {
        unlink($folder . $filename);
    }
}

// Fungsi untuk mencatat history peminjaman
function log_history($booking_id, $user_id, $ruangan_id, $action, $status_lama = null, $status_baru = null, $keterangan = null) {
    global $conn;
    
    $dilakukan_oleh = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NULL';
    $status_lama = $status_lama ? "'$status_lama'" : 'NULL';
    $status_baru = $status_baru ? "'$status_baru'" : 'NULL';
    $keterangan = $keterangan ? "'" . clean_input($keterangan) . "'" : 'NULL';
    
    $query = "INSERT INTO history_peminjaman 
              (booking_id, user_id, ruangan_id, action, status_lama, status_baru, keterangan, dilakukan_oleh) 
              VALUES 
              ('$booking_id', '$user_id', '$ruangan_id', '$action', $status_lama, $status_baru, $keterangan, $dilakukan_oleh)";
    
    mysqli_query($conn, $query);
}

// Fungsi untuk mendapatkan history peminjaman
function get_history($booking_id = null, $user_id = null, $limit = null) {
    global $conn;
    
    $query = "SELECT h.*, 
              u.nama_lengkap as nama_user, 
              r.nama_ruangan,
              admin.nama_lengkap as nama_admin
              FROM history_peminjaman h
              LEFT JOIN users u ON h.user_id = u.id
              LEFT JOIN ruangan r ON h.ruangan_id = r.id
              LEFT JOIN users admin ON h.dilakukan_oleh = admin.id
              WHERE 1=1";
    
    if ($booking_id) {
        $query .= " AND h.booking_id = '$booking_id'";
    }
    
    if ($user_id) {
        $query .= " AND h.user_id = '$user_id'";
    }
    
    $query .= " ORDER BY h.created_at DESC";
    
    if ($limit) {
        $query .= " LIMIT $limit";
    }
    
    return mysqli_query($conn, $query);
}
?>
