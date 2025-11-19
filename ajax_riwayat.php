<?php
session_start();
require_once 'config/database.php';
require_once 'config/functions.php';

require_login();

// Redirect admin dan petugas
if (is_admin_or_petugas()) {
    header("Location: admin/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get filter parameters
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$view_type = isset($_GET['view']) ? $_GET['view'] : 'card';

$response = array();

// Query untuk data booking
$query = "SELECT b.*, r.nama_ruangan 
          FROM booking b 
          JOIN ruangan r ON b.ruangan_id = r.id 
          WHERE b.user_id = '$user_id'";

if ($filter_status) {
    $query .= " AND b.status = '$filter_status'";
}

if ($search) {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $query .= " AND (r.nama_ruangan LIKE '%$search_safe%' 
                OR b.keperluan LIKE '%$search_safe%' 
                OR b.tanggal_booking LIKE '%$search_safe%')";
}

$query .= " ORDER BY b.created_at DESC";
$result = mysqli_query($conn, $query);

$html = '';

if ($view_type == 'card') {
    // Card View
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $status_icons = [
                'pending' => '⏳',
                'approved' => '✓',
                'rejected' => '✗',
                'completed' => '✓',
                'cancelled' => '⊘'
            ];
            
            $html .= '<div class="booking-card-history" data-status="' . $row['status'] . '">';
            $html .= '<div class="booking-card-header-history status-' . $row['status'] . '">';
            $html .= '<div class="booking-card-title">';
            $html .= '<i class="fas fa-door-open"></i>';
            $html .= '<h3>' . htmlspecialchars($row['nama_ruangan']) . '</h3>';
            $html .= '</div>';
            $html .= '<span class="status-badge-new badge-' . $row['status'] . '">';
            $html .= $status_icons[$row['status']] . ' ' . ucfirst($row['status']);
            $html .= '</span>';
            $html .= '</div>';
            
            $html .= '<div class="booking-card-body-history">';
            
            $html .= '<div class="booking-info-row">';
            $html .= '<div class="info-icon"><i class="fas fa-calendar"></i></div>';
            $html .= '<div class="info-content">';
            $html .= '<span class="info-label">Tanggal</span>';
            $html .= '<span class="info-value">' . format_tanggal($row['tanggal_booking']) . '</span>';
            $html .= '</div>';
            $html .= '</div>';
            
            $html .= '<div class="booking-info-row">';
            $html .= '<div class="info-icon"><i class="fas fa-clock"></i></div>';
            $html .= '<div class="info-content">';
            $html .= '<span class="info-label">Waktu</span>';
            $html .= '<span class="info-value">' . format_waktu($row['waktu_mulai']) . ' - ' . format_waktu($row['waktu_selesai']) . '</span>';
            $html .= '</div>';
            $html .= '</div>';
            
            $html .= '<div class="booking-info-row">';
            $html .= '<div class="info-icon"><i class="fas fa-file-alt"></i></div>';
            $html .= '<div class="info-content">';
            $html .= '<span class="info-label">Keperluan</span>';
            $keperluan = htmlspecialchars($row['keperluan']);
            $html .= '<span class="info-value">' . (strlen($keperluan) > 60 ? substr($keperluan, 0, 60) . '...' : $keperluan) . '</span>';
            $html .= '</div>';
            $html .= '</div>';
            
            $html .= '</div>';
            
            $html .= '<div class="booking-card-footer-history">';
            $html .= '<small class="booking-date-created">';
            $html .= '<i class="fas fa-info-circle"></i> Dibuat: ' . date('d M Y, H:i', strtotime($row['created_at']));
            $html .= '</small>';
            $html .= '<a href="user/booking_detail.php?id=' . $row['id'] . '" class="btn-detail-history">';
            $html .= 'Lihat Detail <i class="fas fa-arrow-right"></i>';
            $html .= '</a>';
            $html .= '</div>';
            
            $html .= '</div>';
        }
    } else {
        $html .= '<div class="empty-state">';
        $html .= '<div class="empty-icon"><i class="fas fa-inbox"></i></div>';
        $html .= '<h3>Belum Ada Riwayat</h3>';
        $html .= '<p>Tidak ada data yang sesuai dengan filter</p>';
        $html .= '<a href="user/booking.php" class="btn-primary-modern">';
        $html .= '<i class="fas fa-plus"></i> Buat Booking Baru';
        $html .= '</a>';
        $html .= '</div>';
    }
} else {
    // List View
    if (mysqli_num_rows($result) > 0) {
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            $status_icons = [
                'pending' => '⏳',
                'approved' => '✓',
                'rejected' => '✗',
                'completed' => '✓',
                'cancelled' => '⊘'
            ];
            
            $html .= '<tr class="table-row-modern">';
            $html .= '<td>' . $no++ . '</td>';
            $html .= '<td>';
            $html .= '<div class="table-room-info">';
            $html .= '<strong>' . htmlspecialchars($row['nama_ruangan']) . '</strong>';
            $html .= '</div>';
            $html .= '</td>';
            $html .= '<td>' . format_tanggal($row['tanggal_booking']) . '</td>';
            $html .= '<td class="text-nowrap">' . format_waktu($row['waktu_mulai']) . ' - ' . format_waktu($row['waktu_selesai']) . '</td>';
            $keperluan = htmlspecialchars($row['keperluan']);
            $html .= '<td><span class="keperluan-text">' . (strlen($keperluan) > 40 ? substr($keperluan, 0, 40) . '...' : $keperluan) . '</span></td>';
            $html .= '<td>';
            $html .= '<span class="status-badge-new badge-' . $row['status'] . '">';
            $html .= $status_icons[$row['status']] . ' ' . ucfirst($row['status']);
            $html .= '</span>';
            $html .= '</td>';
            $html .= '<td>';
            $html .= '<a href="user/booking_detail.php?id=' . $row['id'] . '" class="btn-action-modern">';
            $html .= '<i class="fas fa-eye"></i> Detail';
            $html .= '</a>';
            $html .= '</td>';
            $html .= '</tr>';
        }
    } else {
        $html .= '<tr>';
        $html .= '<td colspan="7" class="text-center">';
        $html .= '<div class="empty-state-small">';
        $html .= '<i class="fas fa-inbox"></i>';
        $html .= '<p>Tidak ada data yang sesuai dengan filter</p>';
        $html .= '</div>';
        $html .= '</td>';
        $html .= '</tr>';
    }
}

$response['html'] = $html;
$response['count'] = mysqli_num_rows($result);

header('Content-Type: application/json');
echo json_encode($response);
?>
