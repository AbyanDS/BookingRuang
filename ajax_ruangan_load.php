<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Display errors for debugging
ini_set('log_errors', 1);

session_start();
require_once 'config/database.php';
require_once 'config/functions.php';

// Set header untuk JSON response
header('Content-Type: application/json');

try {
    // Ambil filter dari GET
    $search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
    $filter_kapasitas = isset($_GET['kapasitas']) ? clean_input($_GET['kapasitas']) : '';
    $filter_kategori = isset($_GET['kategori']) ? clean_input($_GET['kategori']) : '';

    // Ambil data ruangan dengan filter
    $where = array();
    $where[] = "r.status = 'tersedia'";

    if ($search) {
        $where[] = "(r.nama_ruangan LIKE '%$search%' OR r.fasilitas LIKE '%$search%')";
    }

    if ($filter_kapasitas) {
        $where[] = "r.kapasitas >= '$filter_kapasitas'";
    }

    if ($filter_kategori) {
        if ($filter_kategori == 'lab') {
            $where[] = "r.nama_ruangan LIKE '%Lab%'";
        } elseif ($filter_kategori == 'ruang') {
            $where[] = "(r.nama_ruangan LIKE '%Ruang%' AND r.nama_ruangan NOT LIKE '%Lab%')";
        } elseif ($filter_kategori == 'lainnya') {
            $where[] = "(r.nama_ruangan NOT LIKE '%Lab%' AND r.nama_ruangan NOT LIKE '%Ruang%')";
        }
    }

    $where_clause = implode(' AND ', $where);
    $query = "SELECT DISTINCT r.* 
              FROM ruangan r 
              LEFT JOIN jadwal_ruangan j ON r.id = j.ruangan_id 
              WHERE $where_clause 
              ORDER BY r.nama_ruangan ASC";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception('Database query failed: ' . mysqli_error($conn));
    }

    // Hitung total hasil
    $total_ruangan = mysqli_num_rows($result);

// Get current time for room status checks
$current_time = date('H:i:s');
$current_date = date('Y-m-d');
$current_day = date('N'); // 1 (Monday) to 7 (Sunday)
$day_names = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$current_day_name = $day_names[$current_day];

// Build HTML output
ob_start();

if ($total_ruangan > 0):
?>
<div class="room-grid-modern">
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <?php
        // Cek apakah ruangan memiliki jadwal aktif
        $ruangan_id = $row['id'];
        $query_jadwal = "SELECT COUNT(*) as total FROM jadwal_ruangan WHERE ruangan_id = '$ruangan_id' AND status = 'aktif'";
        $result_jadwal = mysqli_query($conn, $query_jadwal);
        $has_jadwal = mysqli_fetch_assoc($result_jadwal)['total'] > 0;
        
        // Cek apakah ada jadwal HARI INI untuk ruangan ini
        $query_jadwal_today = "SELECT COUNT(*) as total FROM jadwal_ruangan 
                               WHERE ruangan_id = '$ruangan_id' 
                               AND hari = '$current_day_name'
                               AND status = 'aktif'";
        $result_jadwal_today = mysqli_query($conn, $query_jadwal_today);
        $has_jadwal_today = mysqli_fetch_assoc($result_jadwal_today)['total'] > 0;
        
        // Cek dari booking yang approved atau completed
        $query_booking = "SELECT * FROM booking 
                          WHERE ruangan_id = '$ruangan_id' 
                          AND tanggal_booking = '$current_date'
                          AND waktu_mulai <= '$current_time'
                          AND waktu_selesai >= '$current_time'
                          AND status IN ('approved', 'completed')
                          LIMIT 1";
        $result_booking = mysqli_query($conn, $query_booking);
        $booking_now = mysqli_fetch_assoc($result_booking);
        
        // Cek dari jadwal ruangan YANG SEDANG BERLANGSUNG HARI INI
        $query_jadwal_now = "SELECT * FROM jadwal_ruangan 
                             WHERE ruangan_id = '$ruangan_id' 
                             AND hari = '$current_day_name'
                             AND waktu_mulai <= '$current_time'
                             AND waktu_selesai >= '$current_time'
                             AND status = 'aktif'
                             LIMIT 1";
        $result_jadwal_now = mysqli_query($conn, $query_jadwal_now);
        $jadwal_now = mysqli_fetch_assoc($result_jadwal_now);
        
        $is_being_used = ($booking_now || $jadwal_now);
        ?>
        <div class="room-card-modern <?php echo $is_being_used ? 'room-in-use' : ''; ?>">
            <div class="room-image-container">
                <?php if ($row['foto']): ?>
                    <img src="uploads/<?php echo $row['foto']; ?>" alt="<?php echo $row['nama_ruangan']; ?>" class="room-image">
                <?php else: ?>
                    <img src="assets/img/no-image.svg" alt="No Image" class="room-image">
                <?php endif; ?>
                
                <?php if ($is_being_used): ?>
                    <div class="room-badge room-badge-busy">🔴 Sedang Dipakai</div>
                <?php elseif ($has_jadwal_today): ?>
                    <div class="room-badge room-badge-scheduled">📅 Terjadwal Hari Ini</div>
                <?php elseif ($has_jadwal): ?>
                    <div class="room-badge room-badge-scheduled-other">📅 Ada Jadwal</div>
                <?php else: ?>
                    <div class="room-badge">✨ Tersedia</div>
                <?php endif; ?>
                
                <?php if ($has_jadwal_today && !$is_being_used): ?>
                    <div class="room-badge-jadwal-top">⏰ Terjadwal Hari Ini</div>
                <?php endif; ?>
            </div>
            <div class="room-content">
                <h3 class="room-title"><?php echo $row['nama_ruangan']; ?></h3>
                
                <?php if ($is_being_used): ?>
                    <div class="room-current-use">
                        <div class="current-use-header">
                            <span class="use-icon">🔴</span>
                            <span class="use-status">Sedang Digunakan</span>
                        </div>
                        <?php if ($booking_now): ?>
                            <div class="current-use-details">
                                <div class="use-detail-item">
                                    <strong>Keperluan:</strong> <?php echo substr($booking_now['keperluan'], 0, 40); ?><?php echo strlen($booking_now['keperluan']) > 40 ? '...' : ''; ?>
                                </div>
                                <div class="use-detail-item">
                                    <strong>Waktu:</strong> <?php echo date('H:i', strtotime($booking_now['waktu_mulai'])); ?> - <?php echo date('H:i', strtotime($booking_now['waktu_selesai'])); ?>
                                </div>
                            </div>
                        <?php elseif ($jadwal_now): ?>
                            <div class="current-use-details">
                                <div class="use-detail-item">
                                    <strong>Kegiatan:</strong> <?php echo $jadwal_now['kegiatan']; ?>
                                </div>
                                <div class="use-detail-item">
                                    <strong>Waktu:</strong> <?php echo date('H:i', strtotime($jadwal_now['waktu_mulai'])); ?> - <?php echo date('H:i', strtotime($jadwal_now['waktu_selesai'])); ?>
                                </div>
                                <?php if ($jadwal_now['penanggung_jawab']): ?>
                                <div class="use-detail-item">
                                    <strong>PIC:</strong> <?php echo $jadwal_now['penanggung_jawab']; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="room-details">
                    <div class="room-detail-item">
                        <span class="detail-icon">👥</span>
                        <span class="detail-text"><?php echo $row['kapasitas']; ?> orang</span>
                    </div>
                </div>
                <div class="room-facilities">
                    <span class="facility-label">🔧 Fasilitas:</span>
                    <p class="facility-text"><?php echo strlen($row['fasilitas']) > 60 ? substr($row['fasilitas'], 0, 60) . '...' : $row['fasilitas']; ?></p>
                </div>
                
                <?php if ($has_jadwal_today && !$is_being_used): ?>
                <div class="room-schedule-box room-schedule-today-warning">
                    <div class="schedule-box-header">
                        <span class="schedule-header-icon">⚠️</span>
                        <span class="schedule-header-text">Jadwal Hari Ini</span>
                        <?php
                        // Hitung total jadwal hari ini
                        $query_count_today = "SELECT COUNT(*) as total FROM jadwal_ruangan 
                                       WHERE ruangan_id = '$ruangan_id' 
                                       AND hari = '$current_day_name'
                                       AND status = 'aktif'";
                        $result_count_today = mysqli_query($conn, $query_count_today);
                        $total_jadwal_today = mysqli_fetch_assoc($result_count_today)['total'];
                        ?>
                        <span style="margin-left: auto; background: rgba(255,255,255,0.3); padding: 3px 10px; border-radius: 12px; font-size: 12px;">
                            <?php echo $total_jadwal_today; ?> jadwal
                        </span>
                    </div>
                    <?php
                    // Ambil jadwal aktif HARI INI untuk ruangan ini
                    $query_jadwal_list_today = "SELECT * FROM jadwal_ruangan 
                                          WHERE ruangan_id = '$ruangan_id' 
                                          AND hari = '$current_day_name'
                                          AND status = 'aktif' 
                                          ORDER BY waktu_mulai ASC";
                    $result_jadwal_list_today = mysqli_query($conn, $query_jadwal_list_today);
                    $jadwal_count_today = mysqli_num_rows($result_jadwal_list_today);
                    ?>
                    <div class="schedule-box-content">
                        <?php if ($jadwal_count_today > 0): ?>
                            <div class="schedule-warning-info">
                                <strong>⚠️ Perhatian:</strong> Ruangan tidak dapat dibooking selama jadwal berlangsung
                            </div>
                            <?php while ($jadwal = mysqli_fetch_assoc($result_jadwal_list_today)): 
                                // Cek apakah jadwal ini sudah lewat
                                $jadwal_selesai = strtotime($jadwal['waktu_selesai']);
                                $current_time_stamp = strtotime($current_time);
                                $is_jadwal_done = $current_time_stamp > $jadwal_selesai;
                            ?>
                            <div class="schedule-item <?php echo $is_jadwal_done ? 'schedule-item-done' : 'schedule-item-active'; ?>">
                                <div class="schedule-day-time">
                                    <span class="schedule-day"><?php echo $jadwal['hari']; ?></span>
                                    <span class="schedule-time"><?php echo date('H:i', strtotime($jadwal['waktu_mulai'])); ?> - <?php echo date('H:i', strtotime($jadwal['waktu_selesai'])); ?></span>
                                    <?php if ($is_jadwal_done): ?>
                                        <span class="schedule-status schedule-done">✓ Selesai</span>
                                    <?php else: ?>
                                        <span class="schedule-status schedule-active">🔴 Aktif</span>
                                    <?php endif; ?>
                                </div>
                                <div class="schedule-activity"><?php echo $jadwal['kegiatan']; ?></div>
                            </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                    <a href="jadwal_view.php?ruangan=<?php echo $row['id']; ?>" class="schedule-view-all">
                        Lihat Semua Jadwal <span class="arrow-icon">→</span>
                    </a>
                </div>
                <?php endif; ?>
                
                <div class="room-action">
                    <?php if (is_logged_in() && !is_admin_or_petugas()): ?>
                        <?php if ($is_being_used): ?>
                            <button class="btn-room-modern btn-room-disabled" disabled>
                                <span>🚫</span> Sedang Dipakai
                            </button>
                        <?php elseif ($has_jadwal_today): ?>
                            <button class="btn-room-modern btn-room-disabled" disabled title="Ruangan memiliki jadwal hari ini. Booking akan dibatasi untuk menghindari konflik.">
                                <span>⚠️</span> Ada Jadwal Hari Ini
                            </button>
                        <?php else: ?>
                            <a href="user/booking.php?ruangan_id=<?php echo $row['id']; ?>" class="btn-room-modern">
                                <span>📝</span> Booking Sekarang
                            </a>
                        <?php endif; ?>
                    <?php elseif (!is_logged_in()): ?>
                        <a href="login.php" class="btn-room-modern btn-room-secondary">
                            <span>🔐</span> Login untuk Booking
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>
<?php else: ?>
<div class="empty-state-modern">
    <div class="empty-icon-large">🔍</div>
    <h3>Tidak Ada Ruangan Ditemukan</h3>
    <p>Maaf, tidak ada ruangan yang sesuai dengan kriteria pencarian Anda.</p>
    <button onclick="window.location.href='index.php'" class="btn-empty-state">
        <span>🔄</span> Lihat Semua Ruangan
    </button>
</div>
<?php endif;

    $html = ob_get_clean();

    // Return JSON response
    echo json_encode([
        'success' => true,
        'html' => $html,
        'total' => $total_ruangan
    ]);

} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'total' => 0
    ]);
}
?>
