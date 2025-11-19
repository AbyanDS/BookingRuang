<?php
// Suppress PHP errors from being output
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once '../config/database.php';

// Ensure only JSON is output
ob_clean();

header('Content-Type: application/json');
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$ruangan_id = isset($_POST['ruangan_id']) ? mysqli_real_escape_string($conn, $_POST['ruangan_id']) : '';
$tanggal = isset($_POST['tanggal']) ? mysqli_real_escape_string($conn, $_POST['tanggal']) : '';

if (empty($ruangan_id) || empty($tanggal)) {
    echo json_encode(array('success' => false, 'message' => 'Data tidak lengkap'));
    exit;
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    echo json_encode(array('success' => false, 'message' => 'Format tanggal tidak valid'));
    exit;
}

// Get day name from date
$timestamp = strtotime($tanggal);
$day_number = date('N', $timestamp); // 1 (Monday) to 7 (Sunday)
$day_names = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$day_name = $day_names[$day_number];

$busy_times = array();
$warnings = array();

try {
    // Check bookings on that date
    $query_booking = "SELECT waktu_mulai, waktu_selesai, keperluan, users.nama_lengkap as user_nama
                      FROM booking 
                      LEFT JOIN users ON booking.user_id = users.id
                      WHERE ruangan_id = '$ruangan_id' 
                      AND tanggal_booking = '$tanggal'
                      AND status IN ('approved', 'completed')
                      ORDER BY waktu_mulai ASC";
    $result_booking = mysqli_query($conn, $query_booking);
    
    if (!$result_booking) {
        throw new Exception('Database query failed: ' . mysqli_error($conn));
    }

    while ($booking = mysqli_fetch_assoc($result_booking)) {
        $start = $booking['waktu_mulai'];
        $end = $booking['waktu_selesai'];
        
        // Generate all time slots that are busy
        $current = strtotime($start);
        $end_time = strtotime($end);
        
        while ($current < $end_time) {
            $time_slot = date('H:i', $current);
            if (!in_array($time_slot, $busy_times)) {
                $busy_times[] = $time_slot;
            }
            $current = strtotime('+30 minutes', $current);
        }
        
        $warnings[] = array(
            'type' => 'booking',
            'start' => date('H:i', strtotime($start)),
            'end' => date('H:i', strtotime($end)),
            'info' => 'Booking: ' . $booking['keperluan'],
            'user' => $booking['user_nama']
        );
    }

    // Check jadwal on that day
    $query_jadwal = "SELECT waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab
                     FROM jadwal_ruangan 
                     WHERE ruangan_id = '$ruangan_id' 
                     AND hari = '$day_name'
                     AND status = 'aktif'
                 ORDER BY waktu_mulai ASC";
    $result_jadwal = mysqli_query($conn, $query_jadwal);
    
    if (!$result_jadwal) {
        throw new Exception('Database query failed: ' . mysqli_error($conn));
    }

    while ($jadwal = mysqli_fetch_assoc($result_jadwal)) {
        $start = $jadwal['waktu_mulai'];
        $end = $jadwal['waktu_selesai'];
        
        // Generate all time slots that are busy
        $current = strtotime($start);
        $end_time = strtotime($end);
        
        while ($current < $end_time) {
            $time_slot = date('H:i', $current);
            if (!in_array($time_slot, $busy_times)) {
                $busy_times[] = $time_slot;
            }
            $current = strtotime('+30 minutes', $current);
        }
        
        $warnings[] = array(
            'type' => 'jadwal',
            'start' => date('H:i', strtotime($start)),
            'end' => date('H:i', strtotime($end)),
            'info' => 'Jadwal: ' . $jadwal['kegiatan'],
            'pic' => $jadwal['penanggung_jawab']
        );
    }
    
} catch (Exception $e) {
    echo json_encode(array(
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ));
    exit;
}

$result = array(
    'success' => true,
    'busy_times' => $busy_times,
    'warnings' => $warnings,
    'date' => $tanggal,
    'day' => $day_name
);

echo json_encode($result);
exit;
?>