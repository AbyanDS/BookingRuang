<?php
session_start();
require_once 'config/database.php';
require_once 'config/functions.php';

header('Content-Type: application/json');

$hari = isset($_GET['hari']) ? clean_input($_GET['hari']) : '';
$sesi = isset($_GET['sesi']) ? clean_input($_GET['sesi']) : '1';
$now = date('H:i:s');

ob_start();

// Query untuk semua ruangan dengan jadwal di hari dan sesi ini
$query_rooms = "SELECT DISTINCT r.* 
               FROM ruangan r 
               LEFT JOIN jadwal_ruangan j ON r.id = j.ruangan_id 
                   AND j.hari = '$hari' 
                   AND j.status = 'aktif'
                   AND j.sesi = '$sesi'
               WHERE r.status = 'tersedia'
               ORDER BY r.nama_ruangan ASC";

$result_rooms = mysqli_query($conn, $query_rooms);
$room_count = mysqli_num_rows($result_rooms);
?>

<?php if ($room_count > 0): ?>
    <div class="room-schedule-grid">
        <?php while ($room = mysqli_fetch_assoc($result_rooms)): ?>
            <?php
            // Query jadwal untuk ruangan ini
            $room_id = $room['id'];
            $query_schedules = "SELECT * FROM jadwal_ruangan 
                               WHERE ruangan_id = '$room_id' 
                               AND hari = '$hari' 
                               AND status = 'aktif'
                               AND sesi = '$sesi'
                               ORDER BY waktu_mulai ASC";
            $result_schedules = mysqli_query($conn, $query_schedules);
            $schedule_count = mysqli_num_rows($result_schedules);
            ?>
            
            <div class="room-schedule-card">
                <div class="room-schedule-header">
                    <div class="room-name-section">
                        <i class="fas fa-door-open"></i>
                        <h3><?php echo htmlspecialchars($room['nama_ruangan']); ?></h3>
                    </div>
                    <div class="room-capacity-badge">
                        <i class="fas fa-users"></i>
                        <?php echo $room['kapasitas']; ?> orang
                    </div>
                </div>
                
                <div class="room-schedule-body">
                    <?php if ($schedule_count > 0): ?>
                        <div class="class-list-header">
                            <i class="fas fa-list"></i>
                            <span>Kelas Terjadwal (<?php echo $schedule_count; ?>)</span>
                        </div>
                        <div class="class-badges-container">
                            <?php while ($schedule = mysqli_fetch_assoc($result_schedules)): ?>
                                <?php
                                // Cek status jadwal
                                $is_ongoing = ($now >= $schedule['waktu_mulai'] && $now <= $schedule['waktu_selesai']);
                                $is_done = ($now > $schedule['waktu_selesai']);
                                $status_class = $is_ongoing ? 'ongoing' : ($is_done ? 'done' : 'upcoming');
                                
                                $schedule_json = htmlspecialchars(json_encode($schedule), ENT_QUOTES, 'UTF-8');
                                $room_name = htmlspecialchars($room['nama_ruangan'], ENT_QUOTES, 'UTF-8');
                                ?>
                                <button class="class-badge-btn <?php echo $status_class; ?>" 
                                        onclick='showScheduleDetail(<?php echo $schedule_json; ?>, "<?php echo $room_name; ?>")'>
                                    <div class="class-badge-content">
                                        <div class="class-name"><?php echo htmlspecialchars($schedule['kelas']); ?></div>
                                        <div class="class-time">
                                            <?php echo date('H:i', strtotime($schedule['waktu_mulai'])); ?> - 
                                            <?php echo date('H:i', strtotime($schedule['waktu_selesai'])); ?>
                                        </div>
                                    </div>
                                    <?php if ($is_ongoing): ?>
                                        <span class="status-indicator">🔴</span>
                                    <?php elseif ($is_done): ?>
                                        <span class="status-indicator">✓</span>
                                    <?php endif; ?>
                                </button>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-class-scheduled">
                            <i class="fas fa-calendar-check"></i>
                            <span>Tidak ada kelas terjadwal</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="no-schedule-message">
        <div class="no-schedule-icon">
            <i class="fas fa-calendar-times"></i>
        </div>
        <h3>Tidak Ada Data</h3>
        <p>Tidak ada ruangan tersedia</p>
    </div>
<?php endif; ?>

<?php
$html = ob_get_clean();
echo json_encode([
    'success' => true,
    'html' => $html
]);
?>
