<?php
/**
 * Auto Cleanup Completed Bookings from Jadwal View
 * Script ini akan otomatis menghapus booking yang sudah completed atau expired dari tampilan jadwal
 * 
 * Cara menjalankan:
 * 1. Manual: Akses file ini via browser
 * 2. Cron Job: Setup cron job untuk menjalankan setiap 1 jam
 * 3. Auto: Dipanggil otomatis saat user membuka halaman jadwal_view.php
 */

require_once __DIR__ . '/database.php';

// Log file untuk tracking
$log_file = __DIR__ . '/../uploads/cleanup_completed_log.txt';

function log_cleanup($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Mulai cleanup process
log_cleanup("=== Cleanup Process Started ===");

// 1. Update status booking yang waktu selesainya sudah lewat menjadi 'completed'
$today = date('Y-m-d');
$now = date('H:i:s');

$query_auto_complete = "UPDATE booking 
                        SET status = 'completed',
                            updated_at = NOW()
                        WHERE status IN ('approved', 'pending')
                          AND (
                              tanggal_booking < '$today'
                              OR (tanggal_booking = '$today' AND waktu_selesai < '$now')
                          )";

$result_auto = mysqli_query($conn, $query_auto_complete);
$affected_auto = mysqli_affected_rows($conn);

if ($result_auto) {
    log_cleanup("✓ Auto-completed $affected_auto expired bookings");
} else {
    log_cleanup("✗ Error auto-completing: " . mysqli_error($conn));
}

// 2. Hitung booking yang akan hilang dari jadwal (completed)
$query_count = "SELECT COUNT(*) as total 
                FROM booking 
                WHERE status = 'completed'
                  AND tanggal_booking < DATE_SUB(NOW(), INTERVAL 7 DAY)";

$result_count = mysqli_query($conn, $query_count);
$count_data = mysqli_fetch_assoc($result_count);
$total_old_completed = $count_data['total'];

log_cleanup("📊 Total old completed bookings (>7 days): $total_old_completed");

// 3. Optional: Archive atau hapus booking lama (>30 hari) yang sudah completed
// Uncomment jika ingin auto-delete booking lama
/*
$query_archive = "DELETE FROM booking 
                  WHERE status = 'completed'
                    AND tanggal_booking < DATE_SUB(NOW(), INTERVAL 30 DAY)";

$result_archive = mysqli_query($conn, $query_archive);
$archived_count = mysqli_affected_rows($conn);

if ($result_archive) {
    log_cleanup("🗄️ Archived/deleted $archived_count old completed bookings (>30 days)");
} else {
    log_cleanup("✗ Error archiving: " . mysqli_error($conn));
}
*/

// 4. Statistik current bookings
$stats_query = "SELECT 
                    status,
                    COUNT(*) as count
                FROM booking
                GROUP BY status";

$stats_result = mysqli_query($conn, $stats_query);
$stats = [];
while ($row = mysqli_fetch_assoc($stats_result)) {
    $stats[$row['status']] = $row['count'];
    log_cleanup("📈 Status '{$row['status']}': {$row['count']} bookings");
}

log_cleanup("=== Cleanup Process Completed ===\n");

// Return JSON response jika dipanggil via AJAX
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'auto_completed' => $affected_auto,
        'stats' => $stats,
        'message' => "Cleanup completed successfully. $affected_auto bookings auto-completed."
    ]);
    exit;
}

// Jika diakses langsung via browser
if (!isset($_GET['silent'])) {
    echo "<!DOCTYPE html>";
    echo "<html><head><title>Cleanup Completed</title>";
    echo "<style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        .success { color: #27ae60; font-weight: bold; }
        .stat { padding: 10px; margin: 10px 0; background: #ecf0f1; border-left: 4px solid #3498db; }
        .btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #2980b9; }
    </style></head><body>";
    echo "<div class='container'>";
    echo "<h2>🧹 Cleanup Completed Bookings</h2>";
    echo "<p class='success'>✓ Cleanup process completed successfully!</p>";
    echo "<div class='stat'>🔄 Auto-completed: <strong>$affected_auto</strong> expired bookings</div>";
    
    foreach ($stats as $status => $count) {
        echo "<div class='stat'>📊 Status '<strong>$status</strong>': <strong>$count</strong> bookings</div>";
    }
    
    echo "<p><small>Last run: " . date('Y-m-d H:i:s') . "</small></p>";
    echo "<a href='../jadwal_view.php' class='btn'>← Back to Jadwal</a>";
    echo "<a href='?silent=1' class='btn' style='background: #95a5a6;'>Run Silent</a>";
    echo "</div></body></html>";
}

mysqli_close($conn);
?>
