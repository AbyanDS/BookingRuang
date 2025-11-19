<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

require_login();

// Cegah admin dan petugas mengakses halaman ini
if (is_admin_or_petugas()) {
    header("Location: ../admin/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Query untuk mendapatkan booking yang approved (tidak termasuk completed)
$query = "SELECT b.*, r.nama_ruangan, r.kapasitas, r.foto
          FROM booking b
          JOIN ruangan r ON b.ruangan_id = r.id
          WHERE b.user_id = '$user_id' 
          AND b.status = 'approved'
          ORDER BY b.tanggal_booking ASC, b.waktu_mulai ASC";

$result = mysqli_query($conn, $query);

// Hitung total booking
$total_booking = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Booking Approved</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .list-booking-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header-list {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            border-radius: 20px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .page-header-list h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .page-header-list .subtitle {
            font-size: 1.1rem;
            opacity: 0.95;
        }

        .stats-card {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(67, 233, 123, 0.3);
        }

        .stats-card h2 {
            font-size: 3rem;
            margin: 0;
        }

        .stats-card p {
            margin: 10px 0 0 0;
            font-size: 1.2rem;
        }

        .booking-list-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .booking-card-list {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid #e0e0e0;
        }

        .booking-card-list:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-color: #667eea;
        }

        .booking-card-header-list {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            position: relative;
        }

        .booking-card-header-list h3 {
            margin: 0 0 5px 0;
            font-size: 1.4rem;
        }

        .booking-card-header-list .lokasi {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .status-badge-approved {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            box-shadow: 0 3px 10px rgba(67, 233, 123, 0.4);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .booking-card-body-list {
            padding: 25px;
        }

        .booking-info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .booking-info-row .icon {
            font-size: 1.5rem;
            min-width: 30px;
            text-align: center;
        }

        .booking-info-row .content {
            flex: 1;
        }

        .booking-info-row .label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 3px;
        }

        .booking-info-row .value {
            font-size: 1.05rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .keperluan-box {
            background: #f0f4ff;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            margin-top: 15px;
        }

        .keperluan-box .label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .keperluan-box .text {
            color: #2c3e50;
            line-height: 1.6;
        }

        .countdown-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-top: 15px;
        }

        .countdown-box .label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .countdown-box .time {
            font-size: 1.3rem;
            font-weight: bold;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .empty-state .icon {
            font-size: 5rem;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 25px;
        }

        .btn-primary-action {
            display: inline-block;
            padding: 15px 35px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .filter-section h3 {
            margin: 0 0 15px 0;
            color: #2c3e50;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .filter-item label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-size: 0.9rem;
        }

        .filter-item select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .filter-item select:focus {
            outline: none;
            border-color: #667eea;
        }

        @media (max-width: 768px) {
            .booking-list-grid {
                grid-template-columns: 1fr;
            }

            .page-header-list h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="list-booking-container">
        <!-- Page Header -->
        <div class="page-header-list">
            <h1>
                <span>📋</span>
                <span>List Booking Approved</span>
            </h1>
        </div>

        <!-- Stats Card -->
        <div class="stats-card">
            <h2><?php echo $total_booking; ?></h2>
            <p>Total Booking Approved</p>
        </div>

        <?php if ($total_booking > 0): ?>
            <!-- Filter Section -->
            <div class="filter-section">
                <h3>🔍 Filter Booking</h3>
                <div class="filter-grid">
                    <div class="filter-item">
                        <label>Urutkan Berdasarkan</label>
                        <select id="filter_sort">
                            <option value="tanggal_asc">Tanggal (Terlama)</option>
                            <option value="tanggal_desc">Tanggal (Terbaru)</option>
                            <option value="ruangan">Ruangan (A-Z)</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label>Ruangan</label>
                        <select id="filter_ruangan">
                            <option value="">Semua Ruangan</option>
                            <?php
                            // Get unique rooms from approved bookings
                            mysqli_data_seek($result, 0);
                            $rooms = [];
                            while($row = mysqli_fetch_assoc($result)) {
                                if (!in_array($row['nama_ruangan'], $rooms)) {
                                    $rooms[] = $row['nama_ruangan'];
                                }
                            }
                            foreach($rooms as $room):
                            ?>
                                <option value="<?php echo htmlspecialchars($room); ?>"><?php echo htmlspecialchars($room); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Booking List Grid -->
            <div class="booking-list-grid" id="booking_grid">
                <?php
                mysqli_data_seek($result, 0);
                while($booking = mysqli_fetch_assoc($result)):
                    $tanggal = date('d/m/Y', strtotime($booking['tanggal_booking']));
                    $hari = date('l', strtotime($booking['tanggal_booking']));
                    $hari_indo = [
                        'Sunday' => 'Minggu',
                        'Monday' => 'Senin',
                        'Tuesday' => 'Selasa',
                        'Wednesday' => 'Rabu',
                        'Thursday' => 'Kamis',
                        'Friday' => 'Jumat',
                        'Saturday' => 'Sabtu'
                    ];
                    $hari = $hari_indo[$hari];
                    
                    $waktu_mulai = date('H:i', strtotime($booking['waktu_mulai']));
                    $waktu_selesai = date('H:i', strtotime($booking['waktu_selesai']));
                    
                    // Calculate countdown to booking
                    $booking_datetime = strtotime($booking['tanggal_booking'] . ' ' . $booking['waktu_mulai']);
                    $now = time();
                    $diff = $booking_datetime - $now;
                    
                    $countdown = '';
                    if ($diff > 0) {
                        $days = floor($diff / 86400);
                        $hours = floor(($diff % 86400) / 3600);
                        $minutes = floor(($diff % 3600) / 60);
                        
                        if ($days > 0) {
                            $countdown = "$days hari lagi";
                        } else if ($hours > 0) {
                            $countdown = "$hours jam lagi";
                        } else {
                            $countdown = "$minutes menit lagi";
                        }
                    } else if ($diff > -3600 * 8) { // Still ongoing (within 8 hours after start)
                        $countdown = "Sedang Berlangsung";
                    } else {
                        $countdown = "Selesai";
                    }
                ?>
                <div class="booking-card-list" data-ruangan="<?php echo htmlspecialchars($booking['nama_ruangan']); ?>" data-tanggal="<?php echo $booking['tanggal_booking']; ?>">
                    <div class="booking-card-header-list">
                        <div class="status-badge-approved">
                            <span>✓</span>
                            <span>Approved</span>
                        </div>
                        <h3><?php echo htmlspecialchars($booking['nama_ruangan']); ?></h3>
                    </div>
                    
                    <div class="booking-card-body-list">
                        <div class="booking-info-row">
                            <div class="icon">📅</div>
                            <div class="content">
                                <div class="label">Tanggal</div>
                                <div class="value"><?php echo $hari . ', ' . $tanggal; ?></div>
                            </div>
                        </div>
                        
                        <div class="booking-info-row">
                            <div class="icon">🕐</div>
                            <div class="content">
                                <div class="label">Waktu</div>
                                <div class="value"><?php echo $waktu_mulai . ' - ' . $waktu_selesai; ?></div>
                            </div>
                        </div>
                        
                        <div class="booking-info-row">
                            <div class="icon">👥</div>
                            <div class="content">
                                <div class="label">Kapasitas Ruangan</div>
                                <div class="value"><?php echo $booking['kapasitas']; ?> orang</div>
                            </div>
                        </div>
                        
                        <div class="keperluan-box">
                            <div class="label">📝 Keperluan</div>
                            <div class="text"><?php echo htmlspecialchars($booking['keperluan']); ?></div>
                        </div>
                        
                        <?php if ($diff > 0): ?>
                        <div class="countdown-box">
                            <div class="label">⏰ Dimulai dalam</div>
                            <div class="time"><?php echo $countdown; ?></div>
                        </div>
                        <?php elseif ($diff > -3600 * 8): ?>
                        <div class="countdown-box" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                            <div class="label">🎯 Status</div>
                            <div class="time"><?php echo $countdown; ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="icon">📭</div>
                <h3>Belum Ada Booking Approved</h3>
                <p>Anda belum memiliki booking yang disetujui saat ini</p>
                <a href="booking.php" class="btn-primary-action">
                    <span>➕</span> Buat Booking Baru
                </a>
            </div>
        <?php endif; ?>

        <!-- Info Box -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 15px; margin-top: 30px; text-align: center;">
            <h3 style="margin: 0 0 15px 0;">💡 Informasi</h3>
            <p style="margin: 0; line-height: 1.8;">
                Halaman ini menampilkan semua booking Anda yang telah <strong>disetujui (approved)</strong> oleh admin.<br>
                Booking yang sudah <strong>selesai (completed)</strong> tidak ditampilkan di sini.<br>
                Untuk melihat riwayat lengkap, silakan kunjungi menu <strong>Riwayat</strong>.
            </p>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Filter functionality
        const filterSort = document.getElementById('filter_sort');
        const filterRuangan = document.getElementById('filter_ruangan');
        const bookingGrid = document.getElementById('booking_grid');
        
        function filterBookings() {
            const sortValue = filterSort.value;
            const ruanganValue = filterRuangan.value;
            
            let bookings = Array.from(bookingGrid.querySelectorAll('.booking-card-list'));
            
            // Filter by room
            bookings.forEach(booking => {
                const ruangan = booking.dataset.ruangan;
                if (ruanganValue === '' || ruangan === ruanganValue) {
                    booking.style.display = 'block';
                } else {
                    booking.style.display = 'none';
                }
            });
            
            // Get visible bookings
            bookings = bookings.filter(b => b.style.display !== 'none');
            
            // Sort bookings
            bookings.sort((a, b) => {
                if (sortValue === 'tanggal_asc') {
                    return new Date(a.dataset.tanggal) - new Date(b.dataset.tanggal);
                } else if (sortValue === 'tanggal_desc') {
                    return new Date(b.dataset.tanggal) - new Date(a.dataset.tanggal);
                } else if (sortValue === 'ruangan') {
                    return a.dataset.ruangan.localeCompare(b.dataset.ruangan);
                }
                return 0;
            });
            
            // Reorder in DOM
            bookings.forEach(booking => {
                bookingGrid.appendChild(booking);
            });
        }
        
        if (filterSort && filterRuangan) {
            filterSort.addEventListener('change', filterBookings);
            filterRuangan.addEventListener('change', filterBookings);
        }
        
        // Auto-refresh countdown every minute
        setInterval(() => {
            location.reload();
        }, 60000);
    </script>
</body>
</html>
