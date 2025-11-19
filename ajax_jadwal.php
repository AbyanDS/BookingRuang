<?php
// Error handling untuk AJAX
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);

// Start output buffering
ob_start();

session_start();
require_once 'config/database.php';
require_once 'config/functions.php';

// Get filter parameters
$filter_ruangan = isset($_GET['ruangan']) ? mysqli_real_escape_string($conn, $_GET['ruangan']) : '';
$filter_hari = isset($_GET['hari']) ? mysqli_real_escape_string($conn, $_GET['hari']) : '';
$filter_kelas = isset($_GET['kelas']) ? mysqli_real_escape_string($conn, $_GET['kelas']) : '';
$filter_sesi = isset($_GET['sesi']) ? mysqli_real_escape_string($conn, $_GET['sesi']) : '';
$view_type = isset($_GET['view']) ? $_GET['view'] : 'weekly';

$response = array();

if ($view_type == 'weekly') {
    // Weekly View
    $hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    
    // Filter hari jika dipilih
    if ($filter_hari) {
        $hari_list = [$filter_hari];
    }
    
    $day_colors = [
        'Senin' => '#667eea',
        'Selasa' => '#764ba2',
        'Rabu' => '#f093fb',
        'Kamis' => '#4facfe',
        'Jumat' => '#43e97b',
        'Sabtu' => '#fa709a',
        'Minggu' => '#feca57'
    ];
    
    $html = '';
    
    foreach ($hari_list as $hari) {
        // Query untuk jadwal ruangan tetap
        $query_hari = "SELECT 
                        j.waktu_mulai,
                        j.waktu_selesai,
                        j.kegiatan,
                        j.keterangan,
                        j.kelas,
                        j.sesi,
                        r.nama_ruangan,
                        j.penanggung_jawab,
                        'jadwal' as source_type,
                        NULL as tanggal_booking,
                        NULL as booking_status
                       FROM jadwal_ruangan j 
                       JOIN ruangan r ON j.ruangan_id = r.id 
                       WHERE j.hari = '$hari' AND j.status = 'aktif'";
        
        if ($filter_ruangan) {
            $query_hari .= " AND j.ruangan_id = '$filter_ruangan'";
        }
        
        if ($filter_kelas) {
            $query_hari .= " AND j.kelas = '$filter_kelas'";
        }
        
        if ($filter_sesi) {
            $query_hari .= " AND j.sesi = '$filter_sesi'";
        }
        
        // Query untuk booking user (hanya yang pending/approved dan belum selesai)
        $today = date('Y-m-d');
        // DAYOFWEEK: 1=Sunday, 2=Monday, 3=Tuesday, 4=Wednesday, 5=Thursday, 6=Friday, 7=Saturday
        $hari_numeric = ['Minggu' => 1, 'Senin' => 2, 'Selasa' => 3, 'Rabu' => 4, 'Kamis' => 5, 'Jumat' => 6, 'Sabtu' => 7];
        $day_num = $hari_numeric[$hari];
        
        $query_booking = "SELECT 
                            TIME_FORMAT(b.waktu_mulai, '%H:%i:%s') as waktu_mulai,
                            TIME_FORMAT(b.waktu_selesai, '%H:%i:%s') as waktu_selesai,
                            b.keperluan as kegiatan,
                            b.keterangan,
                            NULL as kelas,
                            NULL as sesi,
                            r.nama_ruangan,
                            u.nama_lengkap as penanggung_jawab,
                            'booking' as source_type,
                            b.tanggal_booking,
                            b.status as booking_status
                          FROM booking b
                          JOIN ruangan r ON b.ruangan_id = r.id
                          JOIN users u ON b.user_id = u.id
                          WHERE b.status = 'approved'
                            AND b.tanggal_booking >= '$today'
                            AND DAYOFWEEK(b.tanggal_booking) = $day_num";
        
        if ($filter_ruangan) {
            $query_booking .= " AND b.ruangan_id = '$filter_ruangan'";
        }
        
        // Gabungkan kedua query dengan UNION
        $query_combined = "($query_hari) UNION ALL ($query_booking) ORDER BY waktu_mulai ASC";
        
        $result_hari = mysqli_query($conn, $query_combined);
        
        // Check for SQL errors
        if (!$result_hari) {
            error_log("SQL Error in ajax_jadwal.php (weekly): " . mysqli_error($conn));
            continue; // Skip this day if error
        }
        
        $jadwal_count = mysqli_num_rows($result_hari);
        
        $html .= '<div class="day-card-modern">';
        $html .= '<div class="day-header" style="background: linear-gradient(135deg, ' . $day_colors[$hari] . ', ' . $day_colors[$hari] . 'dd);">';
        $html .= '<h3>' . $hari . '</h3>';
        $html .= '<span class="jadwal-count">' . $jadwal_count . ' Kegiatan</span>';
        $html .= '</div>';
        
        $html .= '<div class="day-body">';
        
        if ($jadwal_count > 0) {
            while ($j = mysqli_fetch_assoc($result_hari)) {
                // Cek apakah ini booking atau jadwal tetap
                $is_booking = ($j['source_type'] == 'booking');
                $item_class = $is_booking ? 'jadwal-item-modern booking-item' : 'jadwal-item-modern';
                
                $html .= '<div class="' . $item_class . '">';
                
                // Badge untuk kelas dan sesi (hanya untuk jadwal tetap, bukan booking)
                if (!$is_booking && isset($j['kelas']) && $j['kelas']) {
                    $kelas = htmlspecialchars($j['kelas']);
                    $sesi = isset($j['sesi']) && $j['sesi'] ? htmlspecialchars($j['sesi']) : '1';
                    
                    $html .= '<div class="jadwal-class-sesi-badges">';
                    $html .= '<span class="badge-kelas"><i class="fas fa-users"></i> ' . $kelas . '</span>';
                    $html .= '<span class="badge-sesi badge-sesi-' . $sesi . '"><i class="fas fa-layer-group"></i> Sesi ' . $sesi . '</span>';
                    $html .= '</div>';
                }
                
                // Badge untuk membedakan booking dan jadwal tetap
                if ($is_booking) {
                    // Hanya tampilkan badge approved karena pending tidak muncul di jadwal
                    $html .= '<div class="booking-badge-container"><span class="booking-badge badge-approved"><i class="fas fa-check-circle"></i> Booking Approved</span></div>';
                }
                
                $html .= '<div class="jadwal-time-badge">';
                $html .= '<i class="fas fa-clock"></i> ';
                $html .= format_waktu($j['waktu_mulai']) . ' - ' . format_waktu($j['waktu_selesai']);
                $html .= '</div>';
                
                $html .= '<div class="jadwal-kegiatan-title">';
                $html .= '<i class="fas fa-bookmark"></i> ' . htmlspecialchars($j['kegiatan']);
                $html .= '</div>';
                
                $html .= '<div class="jadwal-info-grid">';
                $html .= '<div class="jadwal-info-item">';
                $html .= '<i class="fas fa-door-open"></i>';
                $html .= '<span>' . htmlspecialchars($j['nama_ruangan']) . '</span>';
                $html .= '</div>';
                $html .= '</div>';
                
                if ($j['penanggung_jawab']) {
                    $html .= '<div class="jadwal-pic">';
                    $html .= '<i class="fas fa-user-tie"></i> ' . htmlspecialchars($j['penanggung_jawab']);
                    $html .= '</div>';
                }
                
                // Tambahkan info tanggal untuk booking
                if ($is_booking && isset($j['tanggal_booking'])) {
                    $html .= '<div class="jadwal-pic">';
                    $html .= '<i class="fas fa-calendar"></i> ' . date('d/m/Y', strtotime($j['tanggal_booking']));
                    $html .= '</div>';
                }
                
                if ($j['keterangan']) {
                    $html .= '<div class="jadwal-keterangan-modern">';
                    $html .= '<i class="fas fa-info-circle"></i> ' . htmlspecialchars($j['keterangan']);
                    $html .= '</div>';
                }
                
                $html .= '</div>';
            }
        } else {
            $html .= '<div class="no-jadwal-modern">';
            $html .= '<i class="fas fa-calendar-times"></i>';
            $html .= '<p>Tidak ada jadwal</p>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    $response['html'] = $html;
    
} else {
    // Table View
    $today = date('Y-m-d');
    
    // Query untuk jadwal ruangan tetap - SAMAKAN KOLOM dengan booking
    $query_jadwal = "SELECT 
                        j.waktu_mulai,
                        j.waktu_selesai,
                        j.kegiatan,
                        j.keterangan,
                        j.kelas,
                        j.sesi,
                        r.nama_ruangan,
                        j.penanggung_jawab,
                        'jadwal' as source_type,
                        NULL as tanggal_booking,
                        NULL as booking_status,
                        j.hari
                     FROM jadwal_ruangan j 
                     JOIN ruangan r ON j.ruangan_id = r.id 
                     WHERE j.status = 'aktif'";
    
    if ($filter_ruangan) {
        $query_jadwal .= " AND j.ruangan_id = '$filter_ruangan'";
    }
    
    if ($filter_hari) {
        $query_jadwal .= " AND j.hari = '$filter_hari'";
    }
    
    if ($filter_kelas) {
        $query_jadwal .= " AND j.kelas = '$filter_kelas'";
    }
    
    if ($filter_sesi) {
        $query_jadwal .= " AND j.sesi = '$filter_sesi'";
    }
    
    // Query untuk booking user (hanya yang pending/approved dan belum selesai)
    $query_booking = "SELECT 
                        TIME_FORMAT(b.waktu_mulai, '%H:%i:%s') as waktu_mulai,
                        TIME_FORMAT(b.waktu_selesai, '%H:%i:%s') as waktu_selesai,
                        b.keperluan as kegiatan,
                        b.keterangan,
                        NULL as kelas,
                        NULL as sesi,
                        r.nama_ruangan,
                        u.nama_lengkap as penanggung_jawab,
                        'booking' as source_type,
                        b.tanggal_booking,
                        b.status as booking_status,
                        CASE DAYOFWEEK(b.tanggal_booking)
                            WHEN 1 THEN 'Minggu'
                            WHEN 2 THEN 'Senin'
                            WHEN 3 THEN 'Selasa'
                            WHEN 4 THEN 'Rabu'
                            WHEN 5 THEN 'Kamis'
                            WHEN 6 THEN 'Jumat'
                            WHEN 7 THEN 'Sabtu'
                        END as hari
                      FROM booking b
                      JOIN ruangan r ON b.ruangan_id = r.id
                      JOIN users u ON b.user_id = u.id
                      WHERE b.status = 'approved'
                        AND b.tanggal_booking >= '$today'";
    
    if ($filter_ruangan) {
        $query_booking .= " AND b.ruangan_id = '$filter_ruangan'";
    }
    
    if ($filter_hari) {
        // Convert hari to day number for booking filter
        $hari_to_daynum = [
            'Senin' => 2, 'Selasa' => 3, 'Rabu' => 4, 'Kamis' => 5, 
            'Jumat' => 6, 'Sabtu' => 7, 'Minggu' => 1
        ];
        if (isset($hari_to_daynum[$filter_hari])) {
            $day_num = $hari_to_daynum[$filter_hari];
            $query_booking .= " AND DAYOFWEEK(b.tanggal_booking) = $day_num";
        }
    }
    
    // Gabungkan kedua query dengan UNION ALL
    $query_all = "($query_jadwal) UNION ALL ($query_booking) 
                  ORDER BY 
                  FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
                  IFNULL(kelas, 'ZZZZZ') ASC,
                  IFNULL(sesi, '9') ASC,
                  waktu_mulai ASC";
    
    $result_all = mysqli_query($conn, $query_all);
    
    // Check for SQL errors
    if (!$result_all) {
        error_log("SQL Error in ajax_jadwal.php (table): " . mysqli_error($conn));
        $response['error'] = true;
        $response['message'] = 'Terjadi kesalahan database: ' . mysqli_error($conn);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    $html = '';
    
    if (mysqli_num_rows($result_all) > 0) {
        while ($row = mysqli_fetch_assoc($result_all)) {
            $is_booking = ($row['source_type'] == 'booking');
            $row_class = $is_booking ? 'table-row-jadwal booking-row' : 'table-row-jadwal';
            
            $html .= '<tr class="' . $row_class . '">';
            
            // Kolom Kelas - tampilkan '-' untuk booking
            $kelas = (isset($row['kelas']) && $row['kelas']) ? htmlspecialchars($row['kelas']) : '-';
            if ($kelas != '-') {
                $html .= '<td><span class="kelas-badge-table">' . $kelas . '</span></td>';
            } else {
                $html .= '<td><span style="color: #999;">-</span></td>';
            }
            
            // Kolom Sesi - tampilkan '-' untuk booking
            $sesi = (isset($row['sesi']) && $row['sesi']) ? htmlspecialchars($row['sesi']) : '';
            if ($sesi) {
                $html .= '<td><span class="sesi-badge-table sesi-' . $sesi . '">Sesi ' . $sesi . '</span></td>';
            } else {
                $html .= '<td><span style="color: #999;">-</span></td>';
            }
            
            // Kolom Hari dengan badge booking jika perlu
            $html .= '<td>';
            $html .= '<span class="day-badge">' . $row['hari'] . '</span>';
            if ($is_booking) {
                // Hanya tampilkan approved karena pending tidak muncul di jadwal
                $html .= '<br><span class="booking-badge-table badge-approved"><i class="fas fa-check-circle"></i> Booking</span>';
            }
            $html .= '</td>';
            
            $html .= '<td class="time-cell">' . format_waktu($row['waktu_mulai']) . ' - ' . format_waktu($row['waktu_selesai']) . '</td>';
            $html .= '<td>';
            $html .= '<strong>' . htmlspecialchars($row['kegiatan']) . '</strong>';
            if ($is_booking && isset($row['tanggal_booking'])) {
                $html .= '<br><small class="date-small"><i class="fas fa-calendar"></i> ' . date('d/m/Y', strtotime($row['tanggal_booking'])) . '</small>';
            }
            if ($row['keterangan']) {
                $html .= '<br><small class="keterangan-small">' . htmlspecialchars($row['keterangan']) . '</small>';
            }
            $html .= '</td>';
            $html .= '<td>';
            $html .= '<div class="room-cell">';
            $html .= '<strong>' . htmlspecialchars($row['nama_ruangan']) . '</strong>';
            $html .= '</div>';
            $html .= '</td>';
            $html .= '<td>' . ($row['penanggung_jawab'] ? htmlspecialchars($row['penanggung_jawab']) : '-') . '</td>';
            $html .= '</tr>';
        }
    } else {
        $html .= '<tr>';
        $html .= '<td colspan="7" class="empty-table-cell">';
        $html .= '<div class="empty-state-table">';
        $html .= '<i class="fas fa-calendar-times"></i>';
        $html .= '<p>Belum ada jadwal</p>';
        $html .= '</div>';
        $html .= '</td>';
        $html .= '</tr>';
    }
    
    $response['html'] = $html;
}

// Ensure no extra output before JSON
ob_clean();

// Set proper headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Add success flag
$response['success'] = true;

echo json_encode($response);
exit;
?>
