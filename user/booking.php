<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

require_login();

// Cegah admin dan petugas mengakses halaman booking
if (is_admin_or_petugas()) {
    header("Location: ../admin/index.php");
    exit();
}

$error = '';
$success = '';
$selected_ruangan = isset($_GET['ruangan_id']) ? $_GET['ruangan_id'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $ruangan_id = clean_input($_POST['ruangan_id']);
    $tanggal_booking = clean_input($_POST['tanggal_booking']);
    $waktu_mulai = clean_input($_POST['waktu_mulai']);
    $waktu_selesai = clean_input($_POST['waktu_selesai']);
    $keperluan = clean_input($_POST['keperluan']);
    
    // Debug: Log received data
    error_log("Booking POST data - Room ID: $ruangan_id, Date: $tanggal_booking, Start: $waktu_mulai, End: $waktu_selesai");
    
    // Validasi tanggal tidak boleh di masa lalu
    if (strtotime($tanggal_booking) < strtotime(date('Y-m-d'))) {
        $error = 'Tanggal booking tidak boleh di masa lalu!';
    }
    // Validasi waktu selesai harus lebih besar dari waktu mulai
    else if ($waktu_selesai <= $waktu_mulai) {
        $error = 'Waktu selesai harus lebih besar dari waktu mulai!';
    }
    // Validasi waktu harus dalam jam sekolah (07:00 - 15:30)
    else if (strtotime($waktu_mulai) < strtotime('07:00:00') || strtotime($waktu_selesai) > strtotime('15:30:00')) {
        $error = 'Waktu booking hanya tersedia pada jam sekolah (07:00 - 15:30)!';
    }
    
    // Cek ketersediaan ruangan jika validasi lulus
    if (empty($error)) {
        // Cek jadwal ruangan tetap terlebih dahulu
        $day_of_week = date('N', strtotime($tanggal_booking));
        $day_names = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $hari = $day_names[$day_of_week];
        
        $query_jadwal_check = "SELECT * FROM jadwal_ruangan 
                               WHERE ruangan_id = '$ruangan_id' 
                               AND hari = '$hari'
                               AND status = 'aktif'
                               AND (
                                   (waktu_mulai < '$waktu_selesai' AND waktu_selesai > '$waktu_mulai')
                               )";
        $result_jadwal_check = mysqli_query($conn, $query_jadwal_check);
        
        if (mysqli_num_rows($result_jadwal_check) > 0) {
            $jadwal_conflict = mysqli_fetch_assoc($result_jadwal_check);
            $error = 'Ruangan tidak dapat dibooking! Ada jadwal tetap: <strong>' . $jadwal_conflict['kegiatan'] . '</strong> pada hari <strong>' . $hari . '</strong> pukul <strong>' . date('H:i', strtotime($jadwal_conflict['waktu_mulai'])) . ' - ' . date('H:i', strtotime($jadwal_conflict['waktu_selesai'])) . '</strong>';
        } else if (!cek_ketersediaan_ruangan($ruangan_id, $tanggal_booking, $waktu_mulai, $waktu_selesai)) {
            $error = 'Ruangan sudah dibooking oleh user lain pada waktu tersebut!';
        }
    }
    
    // Insert booking jika tidak ada error
    if (empty($error)) {
        // Validasi ruangan_id exists di database
        $check_room = "SELECT id FROM ruangan WHERE id = '$ruangan_id' AND status = 'tersedia'";
        $result_check = mysqli_query($conn, $check_room);
        
        if (mysqli_num_rows($result_check) == 0) {
            $error = 'Ruangan tidak valid atau tidak tersedia! Silakan pilih ruangan lain.';
        } else {
            // Escape data untuk keamanan
            $user_id_safe = mysqli_real_escape_string($conn, $user_id);
            $ruangan_id_safe = mysqli_real_escape_string($conn, $ruangan_id);
            $tanggal_safe = mysqli_real_escape_string($conn, $tanggal_booking);
            $waktu_mulai_safe = mysqli_real_escape_string($conn, $waktu_mulai);
            $waktu_selesai_safe = mysqli_real_escape_string($conn, $waktu_selesai);
            $keperluan_safe = mysqli_real_escape_string($conn, $keperluan);
            
            $query = "INSERT INTO booking (user_id, ruangan_id, tanggal_booking, waktu_mulai, waktu_selesai, keperluan) 
                      VALUES ('$user_id_safe', '$ruangan_id_safe', '$tanggal_safe', '$waktu_mulai_safe', '$waktu_selesai_safe', '$keperluan_safe')";
            
            if (mysqli_query($conn, $query)) {
                $booking_id = mysqli_insert_id($conn);
                // Catat history
                log_history($booking_id, $user_id, $ruangan_id, 'Booking Dibuat', null, 'pending', 'Booking baru dibuat oleh user');
                $success = 'Booking berhasil dibuat! Menunggu approval admin.';
            } else {
                $error = 'Booking gagal: ' . mysqli_error($conn);
            }
        }
    }
}

// Ambil data ruangan yang tersedia dan memiliki jadwal aktif
$query = "SELECT DISTINCT r.* 
          FROM ruangan r 
          LEFT JOIN jadwal_ruangan j ON r.id = j.ruangan_id 
          WHERE r.status = 'tersedia' 
          ORDER BY r.nama_ruangan ASC";
$ruangan_list = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Ruangan</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <!-- Page Header Modern -->
    <div class="page-header-booking">
        <div class="header-overlay"></div>
        <div class="container">
            <div class="header-content-booking">
                <div class="header-badge">
                    <span class="badge-icon">📝</span>
                    <span>Booking Form</span>
                </div>
                <h1 class="header-title-booking">Buat Booking Ruangan</h1>
                <p class="header-subtitle">Isi form di bawah untuk membuat booking ruangan baru</p>
            </div>
        </div>
    </div>
    
    <div class="container" style="margin-top: -60px; position: relative; z-index: 10;">
        <!-- Debug Info: Real-time Status -->
        <div style="background: #f0f9ff; border-left: 4px solid #3b82f6; padding: 10px 15px; margin-bottom: 20px; border-radius: 8px;">
            <small style="color: #1e40af;">
                ⏰ <strong>Current Time:</strong> <?php echo date('H:i:s'); ?> | 
                📅 <strong>Date:</strong> <?php echo date('Y-m-d'); ?> | 
                📆 <strong>Day:</strong> <?php $day_names = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']; echo $day_names[date('N')]; ?>
            </small>
        </div>
        
        <!-- Alert Messages -->
        <?php if ($error): ?>
            <div class="alert-modern alert-error-modern">
                <div class="alert-icon">❌</div>
                <div class="alert-content">
                    <strong>Oops! Terjadi Kesalahan</strong>
                    <p><?php echo $error; ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert-modern alert-success-modern">
                <div class="alert-icon">✅</div>
                <div class="alert-content">
                    <strong>Berhasil!</strong>
                    <p><?php echo $success; ?></p>
                </div>
                <div class="alert-actions">
                    <a href="riwayat.php" class="btn-alert">Lihat Riwayat</a>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Progress Steps -->
        <div class="booking-progress">
            <div class="progress-step active" data-step="1">
                <div class="step-circle">
                    <span class="step-number">1</span>
                    <span class="step-icon">🏢</span>
                </div>
                <div class="step-label">Pilih Ruangan</div>
            </div>
            <div class="progress-line"></div>
            <div class="progress-step" data-step="2">
                <div class="step-circle">
                    <span class="step-number">2</span>
                    <span class="step-icon">📅</span>
                </div>
                <div class="step-label">Jadwal & Waktu</div>
            </div>
            <div class="progress-line"></div>
            <div class="progress-step" data-step="3">
                <div class="step-circle">
                    <span class="step-number">3</span>
                    <span class="step-icon">📝</span>
                </div>
                <div class="step-label">Detail Keperluan</div>
            </div>
            <div class="progress-line"></div>
            <div class="progress-step" data-step="4">
                <div class="step-circle">
                    <span class="step-number">4</span>
                    <span class="step-icon">✅</span>
                </div>
                <div class="step-label">Konfirmasi</div>
            </div>
        </div>
        
        <!-- Booking Form Card -->
        <div class="card-booking-modern">
            <form method="POST" action="" id="booking_form">
                <input type="hidden" name="ruangan_id" id="ruangan_id_input" required>
                
                <!-- Step 1: Pilih Ruangan -->
                <div class="form-section-booking active" id="step_1">
                    <div class="section-header-booking">
                        <span class="section-icon">🏢</span>
                        <div>
                            <h3>Pilih Ruangan</h3>
                            <p>Pilih ruangan yang ingin Anda booking</p>
                        </div>
                    </div>
                    
                    <div class="form-group-booking">
                        <label class="label-modern">
                            <span class="label-icon">🏢</span>
                            <span class="label-text">Ruangan</span>
                            <span class="label-required">*</span>
                        </label>
                        
                        <!-- Selected Room Display -->
                        <div id="selected_room_display" class="selected-room-display" style="display: none;">
                            <!-- Will be filled by JavaScript -->
                        </div>
                        
                        <!-- Button to Open Modal -->
                        <button type="button" class="btn-select-room-modal" id="btn_open_modal">
                            <span class="btn-icon">🏢</span>
                            <span class="btn-text">Pilih Ruangan</span>
                            <span class="btn-arrow">→</span>
                        </button>
                        <div class="input-hint">� Klik tombol di atas untuk memilih ruangan</div>
                    </div>
                    
                    <div class="form-navigation">
                        <button type="button" class="btn-nav btn-nav-next" onclick="nextStep(2)" id="next_step_1">
                            Lanjut ke Jadwal <span class="nav-icon">→</span>
                        </button>
                    </div>
                </div>
                
                <!-- Step 2: Jadwal & Waktu -->
                <div class="form-section-booking" id="step_2">
                    <div class="section-header-booking">
                        <span class="section-icon">📅</span>
                        <div>
                            <h3>Jadwal & Waktu</h3>
                            <p>Tentukan tanggal dan waktu booking</p>
                        </div>
                    </div>
                    
                    <div class="form-grid-booking">
                        <div class="form-group-booking full-width">
                            <label class="label-modern">
                                <span class="label-icon">📅</span>
                                <span class="label-text">Tanggal Booking</span>
                                <span class="label-required">*</span>
                            </label>
                            <input type="date" name="tanggal_booking" id="tanggal_booking" class="input-modern" required min="<?php echo date('Y-m-d'); ?>">
                            <div class="input-helper">
                                <span id="day_info">Pilih tanggal untuk booking ruangan</span>
                            </div>
                        </div>
                        
                        <div class="form-group-booking">
                            <label class="label-modern">
                                <span class="label-icon">🕐</span>
                                <span class="label-text">Waktu Mulai</span>
                                <span class="label-required">*</span>
                            </label>
                            <select name="waktu_mulai" id="waktu_mulai" class="input-modern" required>
                                <option value="">-- Pilih Waktu Mulai --</option>
                                <option value="07:00:00">07:00</option>
                                <option value="07:30:00">07:30</option>
                                <option value="08:00:00">08:00</option>
                                <option value="08:30:00">08:30</option>
                                <option value="09:00:00">09:00</option>
                                <option value="09:30:00">09:30</option>
                                <option value="10:00:00">10:00</option>
                                <option value="10:30:00">10:30</option>
                                <option value="11:00:00">11:00</option>
                                <option value="11:30:00">11:30</option>
                                <option value="12:00:00">12:00</option>
                                <option value="12:30:00">12:30</option>
                                <option value="13:00:00">13:00</option>
                                <option value="13:30:00">13:30</option>
                                <option value="14:00:00">14:00</option>
                                <option value="14:30:00">14:30</option>
                                <option value="15:00:00">15:00</option>
                            </select>
                            <div class="input-helper">
                                <span id="time_constraint_start">Jam mulai booking (07:00 - 15:00)</span>
                            </div>
                        </div>
                        
                        <div class="form-group-booking">
                            <label class="label-modern">
                                <span class="label-icon">🕐</span>
                                <span class="label-text">Waktu Selesai</span>
                                <span class="label-required">*</span>
                            </label>
                            <select name="waktu_selesai" id="waktu_selesai" class="input-modern" required>
                                <option value="">-- Pilih Waktu Selesai --</option>
                                <option value="07:30:00">07:30</option>
                                <option value="08:00:00">08:00</option>
                                <option value="08:30:00">08:30</option>
                                <option value="09:00:00">09:00</option>
                                <option value="09:30:00">09:30</option>
                                <option value="10:00:00">10:00</option>
                                <option value="10:30:00">10:30</option>
                                <option value="11:00:00">11:00</option>
                                <option value="11:30:00">11:30</option>
                                <option value="12:00:00">12:00</option>
                                <option value="12:30:00">12:30</option>
                                <option value="13:00:00">13:00</option>
                                <option value="13:30:00">13:30</option>
                                <option value="14:00:00">14:00</option>
                                <option value="14:30:00">14:30</option>
                                <option value="15:00:00">15:00</option>
                                <option value="15:30:00">15:30</option>
                            </select>
                            <div class="input-helper">
                                <span id="time_constraint_end">Jam selesai booking (07:30 - 15:30)</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Warning for school hours -->
                    <div class="time-warning-box" id="time_warning_box" style="display: none;">
                        <div class="warning-icon">⚠️</div>
                        <div class="warning-content">
                            <strong>Perhatian!</strong>
                            <p id="time_warning_text">-</p>
                        </div>
                    </div>
                    
                    <div class="duration-info" id="duration_info" style="display: none;">
                        <div class="duration-icon">⏱️</div>
                        <div class="duration-text">
                            <strong>Durasi Booking:</strong>
                            <span id="duration_display">-</span>
                        </div>
                    </div>
                    
                    <div class="form-navigation">
                        <button type="button" class="btn-nav btn-nav-prev" onclick="prevStep(1)">
                            <span class="nav-icon">←</span> Kembali
                        </button>
                        <button type="button" class="btn-nav btn-nav-next" onclick="nextStep(3)" disabled id="next_step_2">
                            Lanjut ke Keperluan <span class="nav-icon">→</span>
                        </button>
                    </div>
                </div>
                
                <!-- Step 3: Detail Keperluan -->
                <div class="form-section-booking" id="step_3">
                    <div class="section-header-booking">
                        <span class="section-icon">📝</span>
                        <div>
                            <h3>Detail Keperluan</h3>
                            <p>Jelaskan keperluan booking ruangan Anda</p>
                        </div>
                    </div>
                    
                    <div class="form-group-booking">
                        <label class="label-modern">
                            <span class="label-icon">📝</span>
                            <span class="label-text">Keperluan Booking</span>
                            <span class="label-required">*</span>
                        </label>
                        <textarea name="keperluan" id="keperluan" class="textarea-modern" rows="6" required placeholder="Contoh: Rapat koordinasi tim marketing untuk membahas strategi kampanye Q1 2024..."></textarea>
                        <div class="input-helper">
                            <span>Minimal 20 karakter</span>
                            <span class="char-count"><span id="char_count">0</span>/500</span>
                        </div>
                    </div>
                    
                    <div class="form-navigation">
                        <button type="button" class="btn-nav btn-nav-prev" onclick="prevStep(2)">
                            <span class="nav-icon">←</span> Kembali
                        </button>
                        <button type="button" class="btn-nav btn-nav-next" onclick="nextStep(4)" disabled id="next_step_3">
                            Lihat Ringkasan <span class="nav-icon">→</span>
                        </button>
                    </div>
                </div>
                
                <!-- Step 4: Konfirmasi -->
                <div class="form-section-booking" id="step_4">
                    <div class="section-header-booking">
                        <span class="section-icon">✅</span>
                        <div>
                            <h3>Konfirmasi Booking</h3>
                            <p>Periksa kembali detail booking Anda</p>
                        </div>
                    </div>
                    
                    <div class="summary-card" id="booking_summary">
                        <div class="summary-item">
                            <div class="summary-icon">🏢</div>
                            <div class="summary-content">
                                <div class="summary-label">Ruangan</div>
                                <div class="summary-value" id="summary_room">-</div>
                            </div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-icon">📅</div>
                            <div class="summary-content">
                                <div class="summary-label">Tanggal</div>
                                <div class="summary-value" id="summary_date">-</div>
                            </div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-icon">🕐</div>
                            <div class="summary-content">
                                <div class="summary-label">Waktu</div>
                                <div class="summary-value" id="summary_time">-</div>
                            </div>
                        </div>
                        <div class="summary-item full-width">
                            <div class="summary-icon">📝</div>
                            <div class="summary-content">
                                <div class="summary-label">Keperluan</div>
                                <div class="summary-value" id="summary_keperluan">-</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-navigation">
                        <button type="button" class="btn-nav btn-nav-prev" onclick="prevStep(3)">
                            <span class="nav-icon">←</span> Kembali
                        </button>
                        <button type="submit" class="btn-nav btn-nav-submit">
                            <span class="nav-icon">✅</span> Submit Booking
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Info Card -->
        <div class="info-card-booking">
            <div class="info-icon">💡</div>
            <div class="info-content">
                <h4>Tips Booking Ruangan</h4>
                <ul>
                    <li>Anda dapat booking ruangan kapan saja (24 jam)</li>
                    <li>Pastikan waktu booking tidak bentrok dengan jadwal lain</li>
                    <li>Booking akan menunggu approval dari admin/petugas</li>
                    <li>Anda akan menerima notifikasi status booking</li>
                    <li>Hubungi admin jika ada pertanyaan</li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Modal Select Room -->
    <div class="modal-select-room" id="modal_select_room">
        <div class="modal-room-content">
            <div class="modal-room-header">
                <h2>🏢 Pilih Ruangan</h2>
                <button type="button" class="modal-close" id="close_modal">&times;</button>
            </div>
            
            <!-- Search and Filter Section -->
            <div class="modal-room-filters">
                <div class="filter-row-dynamic">
                    <div class="filter-item filter-search">
                        <input type="text" id="search_room" placeholder="🔍 Cari nama atau fasilitas..." class="input-filter">
                    </div>
                    <div class="filter-item">
                        <select id="filter_kapasitas" class="input-filter">
                            <option value="">👥 Semua Kapasitas</option>
                            <option value="10">10+ orang</option>
                            <option value="20">20+ orang</option>
                            <option value="30">30+ orang</option>
                            <option value="40">40+ orang</option>
                            <option value="50">50+ orang</option>
                        </select>
                    </div>
                    <button type="button" class="btn-filter-reset-compact" id="btn_reset_filter" title="Reset Filter">
                        <span style="color: white;">🔄 Reset</span>
                    </button>
                </div>
            </div>
            
            <!-- Result Info -->
            <div class="modal-room-result-info" id="result_info">
                <span class="result-icon">📋</span>
                <span>Menampilkan <strong id="result_count">0</strong> ruangan</span>
            </div>
            
            <!-- Room Grid -->
            <div class="modal-room-grid" id="room_grid">
                <!-- Will be filled by JavaScript -->
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Form elements
        const ruanganIdInput = document.getElementById('ruangan_id_input');
        const selectedRoomDisplay = document.getElementById('selected_room_display');
        const btnOpenModal = document.getElementById('btn_open_modal');
        const modalSelectRoom = document.getElementById('modal_select_room');
        const closeModal = document.getElementById('close_modal');
        const roomGrid = document.getElementById('room_grid');
        const searchRoom = document.getElementById('search_room');
        const filterKapasitas = document.getElementById('filter_kapasitas');
        const btnResetFilter = document.getElementById('btn_reset_filter');
        const resultInfo = document.getElementById('result_info');
        const resultCount = document.getElementById('result_count');
        const tanggalBooking = document.getElementById('tanggal_booking');
        const waktuMulai = document.getElementById('waktu_mulai');
        const waktuSelesai = document.getElementById('waktu_selesai');
        const keperluan = document.getElementById('keperluan');
        const charCount = document.getElementById('char_count');
        const durationInfo = document.getElementById('duration_info');
        const durationDisplay = document.getElementById('duration_display');
        
        // Current step
        let currentStep = 1;
        
        // Rooms data
        let allRooms = [];
        let filteredRooms = [];
        let selectedRoom = null;
        
        // Busy times data
        let busyTimes = [];
        let busyWarnings = [];
        
        // Check busy times for selected room and date
        function checkBusyTimes() {
            const roomId = ruanganIdInput.value;
            const tanggal = tanggalBooking.value;
            
            console.log('checkBusyTimes called:', {roomId, tanggal});
            
            if (!roomId || !tanggal) {
                console.log('Missing roomId or tanggal, skipping check');
                return;
            }
            
            // Show loading indicator
            const warningContainer = document.getElementById('time_warning_box');
            if (warningContainer) {
                warningContainer.style.display = 'flex';
                warningContainer.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                warningContainer.style.color = '#fff';
                document.getElementById('time_warning_text').innerHTML = '<strong>⏳ Memeriksa ketersediaan waktu...</strong>';
            }
            
            console.log('Fetching busy times...');
            
            fetch('ajax_check_busy_times.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ruangan_id=' + roomId + '&tanggal=' + tanggal
            })
            .then(response => {
                console.log('Response received:', response);
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('Data received:', data);
                    
                    if (data.success) {
                        busyTimes = data.busy_times;
                        busyWarnings = data.warnings;
                        
                        console.log('Busy times:', busyTimes);
                        console.log('Warnings:', busyWarnings);
                        
                        // Update time options based on busy times
                        updateTimeConstraints();
                        
                        // Show warnings if any
                        if (busyWarnings.length > 0) {
                            showBusyWarnings();
                        } else {
                            console.log('No busy warnings to show');
                            // If no warnings, check if we should show school hours warning
                            const tanggal = tanggalBooking.value;
                            if (tanggal) {
                                const date = new Date(tanggal);
                                const dayOfWeek = date.getDay();
                                if (dayOfWeek >= 1 && dayOfWeek <= 5) {
                                    warningContainer.style.display = 'flex';
                                    warningContainer.style.background = 'linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%)';
                                    warningContainer.style.color = '#2c3e50';
                                    warningContainer.style.borderLeft = '4px solid #ff9800';
                                    document.getElementById('time_warning_text').innerHTML = 'Untuk hari Senin-Jumat, waktu booking hanya tersedia pada jam sekolah (07:00 - 15:30) dengan interval 30 menit.';
                                } else {
                                    warningContainer.style.display = 'none';
                                }
                            }
                        }
                    } else {
                        console.error('API returned error:', data.message);
                        const warningContainer = document.getElementById('time_warning_box');
                        if (warningContainer) {
                            warningContainer.style.display = 'flex';
                            warningContainer.style.background = 'linear-gradient(135deg, #eb3349 0%, #f45c43 100%)';
                            warningContainer.style.color = '#fff';
                            document.getElementById('time_warning_text').innerHTML = '<strong>❌ Error:</strong> ' + data.message;
                        }
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    console.error('Response text:', text);
                    throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                }
            })
            .catch(error => {
                console.error('Error checking busy times:', error);
                const warningContainer = document.getElementById('time_warning_box');
                if (warningContainer) {
                    warningContainer.style.display = 'flex';
                    warningContainer.style.background = 'linear-gradient(135deg, #eb3349 0%, #f45c43 100%)';
                    warningContainer.style.color = '#fff';
                    document.getElementById('time_warning_text').innerHTML = '<strong>❌ Gagal memeriksa ketersediaan waktu.</strong><br>Silakan coba lagi.';
                }
            });
        }
        
        // Show busy warnings
        function showBusyWarnings() {
            const warningContainer = document.getElementById('time_warning_box');
            const warningText = document.getElementById('time_warning_text');
            
            if (busyWarnings.length > 0) {
                // Count blocked times
                const blockedCount = busyTimes.length;
                
                let warningHtml = `<strong style="font-size: 16px; color: #fff;">⚠️ PERHATIAN: Ruangan terpakai pada ${busyWarnings.length} periode berikut!</strong><br>`;
                warningHtml += `<small style="color: #fff; font-weight: 600;">(${blockedCount} slot waktu TIDAK TERSEDIA)</small><br><br>`;
                
                busyWarnings.forEach((warning, index) => {
                    if (warning.type === 'booking') {
                        warningHtml += `<div style="margin-bottom: 10px; padding: 12px; background: rgba(255,255,255,0.95); border-radius: 10px; border-left: 4px solid #dc3545; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                            <strong style="color: #dc3545; font-size: 15px;">🔴 ${warning.start} - ${warning.end}</strong><br>
                            <span style="color: #333; font-weight: 600;">${warning.info}</span><br>
                            <small style="color: #666;">Dibooking oleh: ${warning.user}</small>
                        </div>`;
                    } else if (warning.type === 'jadwal') {
                        warningHtml += `<div style="margin-bottom: 10px; padding: 12px; background: rgba(255,255,255,0.95); border-radius: 10px; border-left: 4px solid #ff6b6b; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                            <strong style="color: #ff6b6b; font-size: 15px;">📅 ${warning.start} - ${warning.end}</strong><br>
                            <span style="color: #333; font-weight: 600;">${warning.info}</span><br>
                            ${warning.pic ? '<small style="color: #666;">PIC: ' + warning.pic + '</small>' : ''}
                        </div>`;
                    }
                });
                
                warningHtml += '<br><div style="padding: 10px; background: rgba(255,255,255,0.95); border-radius: 8px; text-align: center; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">';
                warningHtml += '<strong style="color: #dc3545; font-size: 14px;">💡 PENTING:</strong> <span style="color: #333; font-weight: 600;">Waktu yang terpakai telah DIHAPUS dari pilihan waktu!</span>';
                warningHtml += '</div>';
                
                warningText.innerHTML = warningHtml;
                warningContainer.style.display = 'flex';
                warningContainer.style.background = 'linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 50%, #c92a2a 100%)';
                warningContainer.style.color = '#fff';
                warningContainer.style.borderLeft = '6px solid #fff';
                warningContainer.style.boxShadow = '0 4px 20px rgba(220, 53, 69, 0.5)';
                warningContainer.style.animation = 'pulse 2s infinite';
            } else {
                // Hide warning if no busy times
                warningContainer.style.display = 'none';
            }
        }
        
        // Load rooms from database via AJAX
        function loadRooms() {
            fetch('ajax_get_rooms.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allRooms = data.data;
                        filteredRooms = allRooms;
                        renderRooms(filteredRooms);
                    }
                })
                .catch(error => {
                    console.error('Error loading rooms:', error);
                    roomGrid.innerHTML = '<div class="error-message">❌ Gagal memuat data ruangan. Silakan refresh halaman.</div>';
                });
        }
        
        // Render rooms in grid
        function renderRooms(rooms) {
            if (rooms.length === 0) {
                roomGrid.innerHTML = '<div class="empty-result"><h3>😔 Tidak ada ruangan ditemukan</h3><p>Coba ubah kriteria filter Anda</p></div>';
                resultCount.textContent = '0';
                return;
            }
            
            resultCount.textContent = rooms.length;
            
            roomGrid.innerHTML = rooms.map(room => {
                const isDisabled = !room.can_select;
                const disabledClass = isDisabled ? 'room-card-disabled' : '';
                const disabledAttr = isDisabled ? 'disabled' : '';
                
                return `
                <div class="room-card-modal ${disabledClass}" data-room-id="${room.id}">
                    <div class="room-card-image">
                        <img src="${room.foto}" alt="${room.nama}">
                        <div class="room-status-badge room-status-${room.status}">${room.status_text}</div>
                    </div>
                    <div class="room-card-body">
                        <h3 class="room-card-title">${room.nama}</h3>
                        
                        <div class="room-card-info">
                            <div class="info-item">
                                <span class="info-icon">👥</span>
                                <span>${room.kapasitas} orang</span>
                            </div>
                            <div class="info-item">
                                <span class="info-icon">🔧</span>
                                <span>${room.fasilitas.substring(0, 50)}${room.fasilitas.length > 50 ? '...' : ''}</span>
                            </div>
                        </div>
                        
                        ${isDisabled ? `
                        <div class="room-disabled-reason">
                            <span class="reason-icon">⚠️</span>
                            <span class="reason-text">${room.disable_reason}</span>
                        </div>
                        ` : ''}
                        
                        <button type="button" class="btn-select-room" data-room='${JSON.stringify(room)}' ${disabledAttr}>
                            <span>${isDisabled ? '🔒' : '✓'}</span> ${isDisabled ? 'Tidak Tersedia' : 'Pilih Ruangan Ini'}
                        </button>
                    </div>
                </div>
                `;
            }).join('');
            
            // Attach event listeners to select buttons
            document.querySelectorAll('.btn-select-room').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Jangan proses jika button disabled
                    if (this.disabled) {
                        return;
                    }
                    
                    const room = JSON.parse(this.dataset.room);
                    
                    // Double check: jangan pilih ruangan yang tidak bisa dipilih
                    if (!room.can_select) {
                        alert('⚠️ Ruangan ini sedang tidak tersedia untuk dibooking.\n\n' + room.disable_reason);
                        return;
                    }
                    
                    selectedRoom = room;
                    selectRoom(selectedRoom);
                });
            });
        }
        
        // Select room function
        function selectRoom(room) {
            selectedRoom = room;
            ruanganIdInput.value = room.id;
            
            console.log('Room selected:', room);
            console.log('ruanganIdInput.value set to:', ruanganIdInput.value);
            console.log('ruanganIdInput element:', ruanganIdInput);
            
            // Check busy times if date is already selected
            if (tanggalBooking.value) {
                checkBusyTimes();
            }
            
            // Update display
            selectedRoomDisplay.innerHTML = `
                <div class="selected-room-card">
                    <div class="selected-room-image">
                        <img src="${room.foto}" alt="${room.nama}">
                    </div>
                    <div class="selected-room-info">
                        <h4>${room.nama}</h4>
                        <p><span class="info-icon">👥</span> ${room.kapasitas} orang</p>
                        <p><span class="info-icon">🔧</span> ${room.fasilitas}</p>
                    </div>
                    <button type="button" class="btn-change-room" onclick="openModal()">
                        <span>🔄</span>
                    </button>
                </div>
            `;
            selectedRoomDisplay.style.display = 'block';
            btnOpenModal.style.display = 'none';
            
            // Enable next button
            document.getElementById('next_step_1').disabled = false;
            
            // Close modal
            modalSelectRoom.style.display = 'none';
        }
        
        // Filter rooms
        function filterRooms() {
            const searchText = searchRoom.value.toLowerCase();
            const minKapasitas = parseInt(filterKapasitas.value) || 0;
            
            filteredRooms = allRooms.filter(room => {
                // Search filter
                const matchSearch = !searchText || 
                    room.nama.toLowerCase().includes(searchText) ||
                    room.fasilitas.toLowerCase().includes(searchText);
                
                // Capacity filter
                const matchKapasitas = room.kapasitas >= minKapasitas;
                
                return matchSearch && matchKapasitas;
            });
            
            renderRooms(filteredRooms);
        }
        
        // Open modal
        function openModal() {
            modalSelectRoom.style.display = 'flex';
            loadRooms();
        }
        
        // Modal event listeners
        btnOpenModal.addEventListener('click', openModal);
        
        closeModal.addEventListener('click', function() {
            modalSelectRoom.style.display = 'none';
        });
        
        window.addEventListener('click', function(event) {
            if (event.target === modalSelectRoom) {
                modalSelectRoom.style.display = 'none';
            }
        });
        
        // Filter event listeners
        searchRoom.addEventListener('input', filterRooms);
        filterKapasitas.addEventListener('change', filterRooms);
        
        btnResetFilter.addEventListener('click', function() {
            searchRoom.value = '';
            filterKapasitas.value = '';
            filterRooms();
        });
        
        // Step Navigation Functions
        function nextStep(step) {
            // Hide current step
            document.querySelectorAll('.form-section-booking').forEach(section => {
                section.classList.remove('active');
            });
            
            // Show next step
            document.getElementById('step_' + step).classList.add('active');
            
            // Update progress
            document.querySelectorAll('.progress-step').forEach(progressStep => {
                const stepNum = parseInt(progressStep.dataset.step);
                if (stepNum < step) {
                    progressStep.classList.add('completed');
                    progressStep.classList.remove('active');
                } else if (stepNum === step) {
                    progressStep.classList.add('active');
                    progressStep.classList.remove('completed');
                } else {
                    progressStep.classList.remove('active', 'completed');
                }
            });
            
            currentStep = step;
            
            // Update summary if going to step 4
            if (step === 4) {
                updateSummary();
            }
            
            // Smooth scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        function prevStep(step) {
            nextStep(step);
        }
        
        // Update Summary
        function updateSummary() {
            // Get selected room from selectedRoom object
            if (selectedRoom) {
                document.getElementById('summary_room').innerHTML = `
                    <strong>${selectedRoom.nama}</strong><br>
                    <small>👥 ${selectedRoom.kapasitas} orang</small>
                `;
            }
            
            const tanggal = tanggalBooking.value;
            if (tanggal) {
                const date = new Date(tanggal + 'T00:00:00');
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                document.getElementById('summary_date').textContent = date.toLocaleDateString('id-ID', options);
            }
            
            const mulai = waktuMulai.value;
            const selesai = waktuSelesai.value;
            if (mulai && selesai) {
                document.getElementById('summary_time').innerHTML = `
                    ${mulai} - ${selesai}<br>
                    <small>${calculateDuration(mulai, selesai)}</small>
                `;
            }
            
            const keperluanText = keperluan.value;
            if (keperluanText) {
                document.getElementById('summary_keperluan').textContent = keperluanText;
            }
        }
        
        // Calculate Duration
        function calculateDuration(start, end) {
            const startTime = new Date('2000-01-01 ' + start);
            const endTime = new Date('2000-01-01 ' + end);
            const diff = endTime - startTime;
            
            if (diff <= 0) return 'Invalid';
            
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            
            let result = '';
            if (hours > 0) result += hours + ' jam ';
            if (minutes > 0) result += minutes + ' menit';
            
            return result || '0 menit';
        }
        
        // Validation for Step 2 with AJAX check
        function validateStep2() {
            const tanggal = tanggalBooking.value;
            const mulai = waktuMulai.value;
            const selesai = waktuSelesai.value;
            
            // Check if weekend
            if (tanggal) {
                const date = new Date(tanggal + 'T00:00:00');
                const dayOfWeek = date.getDay();
                if (dayOfWeek === 0 || dayOfWeek === 6) {
                    document.getElementById('next_step_2').disabled = true;
                    return; // Don't proceed with validation
                }
            }
            
            const allFilled = tanggal && mulai && selesai;
            const validTime = mulai && selesai && selesai > mulai;
            
            document.getElementById('next_step_2').disabled = !(allFilled && validTime);
            
            // Show duration
            if (mulai && selesai && selesai > mulai) {
                durationInfo.style.display = 'flex';
                durationDisplay.textContent = calculateDuration(mulai, selesai);
                
                // Check jadwal conflict via AJAX
                if (selectedRoomData && allFilled && validTime) {
                    checkJadwalConflict(selectedRoomData.id, tanggal, mulai, selesai);
                }
            } else {
                durationInfo.style.display = 'none';
            }
        }
        
        // AJAX Check Jadwal Conflict
        function checkJadwalConflict(ruanganId, tanggal, waktuMulai, waktuSelesai) {
            // Show loading indicator
            const conflictBox = document.getElementById('jadwal_conflict_box');
            if (!conflictBox) {
                const box = document.createElement('div');
                box.id = 'jadwal_conflict_box';
                box.style.marginTop = '15px';
                durationInfo.parentElement.appendChild(box);
            }
            
            fetch('check_jadwal_conflict.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ruangan_id=${ruanganId}&tanggal=${tanggal}&waktu_mulai=${waktuMulai}&waktu_selesai=${waktuSelesai}`
            })
            .then(response => response.json())
            .then(data => {
                const box = document.getElementById('jadwal_conflict_box');
                
                if (!data.available) {
                    if (data.type === 'jadwal') {
                        box.innerHTML = `
                            <div class="alert-modern alert-error-modern" style="animation: shake 0.5s;">
                                <div class="alert-icon">🚫</div>
                                <div class="alert-content">
                                    <strong>Tidak Dapat Dibooking!</strong>
                                    <p><strong>Jadwal Tetap:</strong> ${data.detail.kegiatan}</p>
                                    <p><strong>Hari:</strong> ${data.detail.hari} | <strong>Waktu:</strong> ${data.detail.waktu_mulai} - ${data.detail.waktu_selesai}</p>
                                    ${data.detail.penanggung_jawab ? `<p><strong>PIC:</strong> ${data.detail.penanggung_jawab}</p>` : ''}
                                </div>
                            </div>
                        `;
                        document.getElementById('next_step_2').disabled = true;
                    } else if (data.type === 'booking') {
                        box.innerHTML = `
                            <div class="alert-modern alert-warning-modern" style="animation: shake 0.5s;">
                                <div class="alert-icon">⚠️</div>
                                <div class="alert-content">
                                    <strong>Ruangan Sudah Dibooking</strong>
                                    <p>Waktu: ${data.detail.waktu_mulai} - ${data.detail.waktu_selesai}</p>
                                    <p>Status: ${data.detail.status}</p>
                                </div>
                            </div>
                        `;
                        document.getElementById('next_step_2').disabled = true;
                    }
                } else {
                    box.innerHTML = `
                        <div class="alert-modern alert-success-modern">
                            <div class="alert-icon">✅</div>
                            <div class="alert-content">
                                <strong>Ruangan Tersedia!</strong>
                                <p>${data.message}</p>
                            </div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error checking conflict:', error);
            });
        }
        
        // Update time constraints and helper text based on selected date
        function updateTimeConstraints() {
            const tanggal = tanggalBooking.value;
            const dayInfo = document.getElementById('day_info');
            const timeWarningBox = document.getElementById('time_warning_box');
            const timeWarningText = document.getElementById('time_warning_text');
            const timeConstraintStart = document.getElementById('time_constraint_start');
            const timeConstraintEnd = document.getElementById('time_constraint_end');
            
            if (tanggal) {
                const date = new Date(tanggal + 'T00:00:00');
                const dayOfWeek = date.getDay();
                const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const dayName = dayNames[dayOfWeek];
                
                dayInfo.innerHTML = `📅 <strong>${dayName}</strong> - ${date.toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'})}`;
                
                // Enable time selects for all days (including weekends)
                waktuMulai.disabled = false;
                waktuSelesai.disabled = false;
                
                // Show school hours info
                if (busyWarnings.length === 0) {
                    timeWarningBox.style.display = 'flex';
                    timeWarningBox.style.background = 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)';
                    timeWarningBox.style.color = '#fff';
                    timeWarningBox.style.borderLeft = '4px solid #0284c7';
                    
                    if (dayOfWeek === 0 || dayOfWeek === 6) {
                        timeWarningText.innerHTML = '<strong>ℹ️ Booking Akhir Pekan</strong><br>Waktu booking tersedia: <strong>07:00 - 15:30</strong> dengan interval 30 menit.';
                    } else {
                        timeWarningText.innerHTML = '<strong>ℹ️ Informasi Jam Sekolah</strong><br>Waktu booking tersedia: <strong>07:00 - 15:30</strong> dengan interval 30 menit.';
                    }
                }
                
                // Set minimum time if today
                const today = new Date();
                const isToday = tanggal === today.toISOString().split('T')[0];
                
                if (isToday) {
                    timeConstraintStart.innerHTML = '⏰ Pilih waktu mulai (minimal 1 jam dari sekarang)';
                } else {
                    timeConstraintStart.innerHTML = '⏰ Jam mulai booking (07:00 - 15:00)';
                }
                
                timeConstraintEnd.innerHTML = '⏰ Jam selesai booking (07:30 - 15:30)';
            } else {
                dayInfo.textContent = 'Pilih tanggal untuk booking ruangan';
                busyTimes = []; // Reset busy times
                busyWarnings = []; // Reset warnings
                timeWarningBox.style.display = 'none';
                timeConstraintStart.textContent = 'Jam mulai booking';
                timeConstraintEnd.textContent = 'Jam selesai booking';
                
                // Reset attributes
                waktuMulai.removeAttribute('min');
                waktuMulai.removeAttribute('max');
                waktuSelesai.removeAttribute('min');
                waktuSelesai.removeAttribute('max');
                waktuMulai.value = '';
                waktuSelesai.value = '';
            }
        }
        
        tanggalBooking.addEventListener('change', function() {
            console.log('Tanggal changed:', this.value);
            checkBusyTimes(); // Check busy times first
            updateTimeConstraints();
            validateStep2();
        });
        
        waktuMulai.addEventListener('change', function() {
            // Update minimum waktu_selesai based on waktu_mulai
            if (this.value) {
                const [hours, minutes] = this.value.split(':');
                const minEndHours = String(parseInt(hours) + 1).padStart(2, '0');
                const minEndTime = minEndHours + ':' + minutes;
                waktuSelesai.min = minEndTime;
                
                // Clear waktu_selesai if it's less than minimum
                if (waktuSelesai.value && waktuSelesai.value < minEndTime) {
                    waktuSelesai.value = '';
                }
            }
            validateStep2();
        });
        
        waktuSelesai.addEventListener('change', validateStep2);
        
        // Validation for Step 3
        function validateStep3() {
            const text = keperluan.value;
            const length = text.length;
            charCount.textContent = length;
            
            document.getElementById('next_step_3').disabled = length < 20;
            
            if (length >= 20) {
                keperluan.classList.add('valid');
                keperluan.classList.remove('invalid');
            } else if (length > 0) {
                keperluan.classList.add('invalid');
                keperluan.classList.remove('valid');
            } else {
                keperluan.classList.remove('valid', 'invalid');
            }
        }
        
        keperluan.addEventListener('input', validateStep3);
        
        // Form submit validation and animation
        document.getElementById('booking_form').addEventListener('submit', function(e) {
            // Validasi final
            const roomId = ruanganIdInput.value;
            const tanggal = tanggalBooking.value;
            const waktuMulaiValue = waktuMulai.value;
            const waktuSelesaiValue = waktuSelesai.value;
            
            console.log('Form submitting...');
            console.log('Room ID value:', roomId);
            console.log('Form data:', new FormData(this));
            
            if (!roomId || roomId === '' || roomId === 'undefined') {
                e.preventDefault();
                alert('❌ Error: Ruangan belum dipilih! Silakan pilih ruangan terlebih dahulu.');
                return false;
            }
            
            // Validasi room ID adalah angka
            if (isNaN(roomId) || parseInt(roomId) <= 0) {
                e.preventDefault();
                alert('❌ Error: ID Ruangan tidak valid! Silakan pilih ulang ruangan.');
                console.error('Invalid room ID:', roomId);
                return false;
            }
            
            // Validasi waktu tidak boleh di masa lalu jika hari ini
            const today = new Date();
            const selectedDate = new Date(tanggal + 'T00:00:00');
            const isToday = tanggal === today.toISOString().split('T')[0];
            
            if (isToday) {
                const now = new Date();
                const [startHours, startMinutes] = waktuMulaiValue.split(':');
                const startDateTime = new Date(selectedDate);
                startDateTime.setHours(parseInt(startHours), parseInt(startMinutes), 0);
                
                if (startDateTime <= now) {
                    e.preventDefault();
                    alert('❌ Error: Waktu mulai tidak boleh di masa lalu! Silakan pilih waktu yang lebih dari sekarang.');
                    return false;
                }
            }
            
            // Validasi waktu selesai harus lebih besar dari waktu mulai
            if (waktuSelesaiValue <= waktuMulaiValue) {
                e.preventDefault();
                alert('❌ Error: Waktu selesai harus lebih besar dari waktu mulai!');
                return false;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<span class="spinner"></span> Memproses...';
            submitBtn.disabled = true;
        });
        
        // Auto-select if coming from URL parameter
        <?php if ($selected_ruangan): ?>
        // Load rooms first, then auto-select
        fetch('ajax_get_rooms.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const autoRoom = data.data.find(r => r.id == '<?php echo $selected_ruangan; ?>');
                    if (autoRoom) {
                        selectRoom(autoRoom);
                    }
                }
            });
        <?php endif; ?>
        
        // Initialize time dropdowns on load
        updateTimeConstraints();
        
        // Add smooth animations on load
        setTimeout(() => {
            document.querySelector('.page-header-booking').classList.add('loaded');
        }, 100);
    </script>
</body>
</html>
