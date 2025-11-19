<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

require_login();

header('Content-Type: application/json');

$ruangan_id = isset($_POST['ruangan_id']) ? clean_input($_POST['ruangan_id']) : '';
$tanggal = isset($_POST['tanggal']) ? clean_input($_POST['tanggal']) : '';
$waktu_mulai = isset($_POST['waktu_mulai']) ? clean_input($_POST['waktu_mulai']) : '';
$waktu_selesai = isset($_POST['waktu_selesai']) ? clean_input($_POST['waktu_selesai']) : '';

if (empty($ruangan_id) || empty($tanggal) || empty($waktu_mulai) || empty($waktu_selesai)) {
    echo json_encode(['available' => false, 'message' => 'Data tidak lengkap']);
    exit();
}

// Cek jadwal ruangan tetap
$day_of_week = date('N', strtotime($tanggal));
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

if (mysqli_num_rows($result_jadwal) > 0) {
    $jadwal = mysqli_fetch_assoc($result_jadwal);
    echo json_encode([
        'available' => false,
        'type' => 'jadwal',
        'message' => 'Ruangan tidak dapat dibooking! Ada jadwal tetap',
        'detail' => [
            'kegiatan' => $jadwal['kegiatan'],
            'hari' => $hari,
            'waktu_mulai' => date('H:i', strtotime($jadwal['waktu_mulai'])),
            'waktu_selesai' => date('H:i', strtotime($jadwal['waktu_selesai'])),
            'penanggung_jawab' => $jadwal['penanggung_jawab']
        ]
    ]);
    exit();
}

// Cek booking yang sudah ada
$query_booking = "SELECT b.*, u.nama_lengkap FROM booking b
                  LEFT JOIN users u ON b.user_id = u.id
                  WHERE b.ruangan_id = '$ruangan_id' 
                  AND b.tanggal_booking = '$tanggal'
                  AND b.status NOT IN ('rejected', 'cancelled')
                  AND (
                      (b.waktu_mulai < '$waktu_selesai' AND b.waktu_selesai > '$waktu_mulai')
                  )";

$result_booking = mysqli_query($conn, $query_booking);

if (mysqli_num_rows($result_booking) > 0) {
    $booking = mysqli_fetch_assoc($result_booking);
    echo json_encode([
        'available' => false,
        'type' => 'booking',
        'message' => 'Ruangan sudah dibooking pada waktu tersebut',
        'detail' => [
            'waktu_mulai' => date('H:i', strtotime($booking['waktu_mulai'])),
            'waktu_selesai' => date('H:i', strtotime($booking['waktu_selesai'])),
            'status' => $booking['status']
        ]
    ]);
    exit();
}

// Ruangan tersedia
echo json_encode([
    'available' => true,
    'message' => 'Ruangan tersedia untuk waktu yang dipilih'
]);
?>
