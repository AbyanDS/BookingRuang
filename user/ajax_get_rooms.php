<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Get current time for real-time status check
$current_time = date('H:i:s');
$current_date = date('Y-m-d');
$current_day = date('N');
$day_names = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$current_day_name = $day_names[$current_day];

// Get all rooms
$query = "SELECT * FROM ruangan WHERE status = 'tersedia' ORDER BY nama_ruangan ASC";
$result = mysqli_query($conn, $query);

$rooms = array();

while ($room = mysqli_fetch_assoc($result)) {
    $room_id = $room['id'];
    
    // Check if room is currently being used (booking)
    $query_booking = "SELECT * FROM booking 
                      WHERE ruangan_id = '$room_id' 
                      AND tanggal_booking = '$current_date'
                      AND waktu_mulai <= '$current_time'
                      AND waktu_selesai >= '$current_time'
                      AND status IN ('approved', 'completed')
                      LIMIT 1";
    $result_booking = mysqli_query($conn, $query_booking);
    $booking_now = mysqli_fetch_assoc($result_booking);
    
    // Check if room has active schedule now
    $query_jadwal = "SELECT * FROM jadwal_ruangan 
                     WHERE ruangan_id = '$room_id' 
                     AND hari = '$current_day_name'
                     AND waktu_mulai <= '$current_time'
                     AND waktu_selesai >= '$current_time'
                     AND status = 'aktif'
                     LIMIT 1";
    $result_jadwal = mysqli_query($conn, $query_jadwal);
    $jadwal_now = mysqli_fetch_assoc($result_jadwal);
    
    // Check if room has any schedule today
    $query_jadwal_today = "SELECT * FROM jadwal_ruangan 
                           WHERE ruangan_id = '$room_id' 
                           AND hari = '$current_day_name'
                           AND status = 'aktif'
                           ORDER BY waktu_mulai ASC";
    $result_jadwal_today = mysqli_query($conn, $query_jadwal_today);
    $jadwal_today_list = array();
    while ($jt = mysqli_fetch_assoc($result_jadwal_today)) {
        $jadwal_today_list[] = $jt;
    }
    
    $is_busy = ($booking_now || $jadwal_now);
    $has_jadwal_today = count($jadwal_today_list) > 0;
    
    // Determine status - ruangan busy tidak bisa dipilih
    $status = 'tersedia';
    $status_text = '✨ Tersedia';
    $busy_info = '';
    $busy_until = '';
    $can_select = true; // Default: bisa dipilih
    $disable_reason = '';
    
    if ($is_busy) {
        $status = 'sedang_dipakai'; // Ubah status menjadi sedang dipakai
        $can_select = false; // Tidak bisa dipilih
        
        if ($booking_now) {
            $status_text = '🔒 Sedang Dibooking';
            $busy_info = $booking_now['keperluan'];
            $busy_until = date('H:i', strtotime($booking_now['waktu_selesai']));
            $disable_reason = 'Ruangan sedang dibooking untuk "' . $busy_info . '" sampai ' . $busy_until;
        } elseif ($jadwal_now) {
            $status_text = '📚 Sedang Digunakan';
            $busy_info = $jadwal_now['kegiatan'];
            $busy_until = date('H:i', strtotime($jadwal_now['waktu_selesai']));
            $disable_reason = 'Ruangan sedang digunakan untuk "' . $busy_info . '" sampai ' . $busy_until;
        }
    }
    
    $rooms[] = array(
        'id' => $room['id'],
        'nama' => $room['nama_ruangan'],
        'kapasitas' => $room['kapasitas'],
        'fasilitas' => $room['fasilitas'],
        'foto' => $room['foto'] ? '../uploads/' . $room['foto'] : '../assets/img/no-image.svg',
        'status' => $status,
        'status_text' => $status_text,
        'is_busy' => $is_busy,
        'busy_info' => $busy_info,
        'busy_until' => $busy_until,
        'has_jadwal_today' => $has_jadwal_today,
        'jadwal_today' => $jadwal_today_list,
        'can_select' => $can_select,
        'disable_reason' => $disable_reason
    );
}

echo json_encode(array(
    'success' => true,
    'data' => $rooms,
    'current_time' => $current_time,
    'current_date' => $current_date,
    'current_day' => $current_day_name
));
?>
