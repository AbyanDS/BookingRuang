<?php
session_start();
require_once 'config/database.php';
require_once 'config/functions.php';

// Prevent caching untuk real-time status
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

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

// Hitung total hasil
$total_ruangan = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Booking Ruangan</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Hero Section - Modern & Dynamic -->
    <div class="hero-modern">
        <div class="hero-overlay"></div>
        <div class="hero-content-modern">
            <div class="hero-badge">🏢 Smart Booking System</div>
            <h1 class="hero-title-modern">Sistem Booking Ruangan</h1>
            <p class="hero-subtitle-modern">Reservasi ruangan dengan mudah, cepat, dan efisien</p>
            <?php if (!is_logged_in()): ?>
                <div class="hero-actions">
                    <a href="login.php" class="btn-hero btn-hero-primary">
                        <span class="btn-icon">🔐</span>
                        <span>Login untuk Booking</span>
                    </a>
                    <a href="register.php" class="btn-hero btn-hero-secondary">
                        <span class="btn-icon">📝</span>
                        <span>Daftar Akun</span>
                    </a>
                </div>
            <?php elseif (is_admin_or_petugas()): ?>
                <div class="hero-actions">
                    <a href="admin/index.php" class="btn-hero btn-hero-primary">
                        <span class="btn-icon">📊</span>
                        <span>Ke Dashboard</span>
                    </a>
                </div>
            <?php else: ?>
                <div class="hero-actions">
                    <a href="user/booking.php" class="btn-hero btn-hero-primary">
                        <span class="btn-icon">📝</span>
                        <span>Booking Sekarang</span>
                    </a>
                    <a href="user/riwayat.php" class="btn-hero btn-hero-secondary">
                        <span class="btn-icon">📚</span>
                        <span>Riwayat Booking</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <div class="hero-scroll-indicator">
            <span>Scroll ke bawah</span>
            <div class="scroll-arrow">↓</div>
        </div>
    </div>
    
    <div class="container">
        <!-- Section Header Modern -->
        <div class="section-header-modern" id="ruangan-section">
            <div class="section-badge">🏢 Pilihan Ruangan</div>
            <h2 class="section-title-modern">Ruangan Tersedia</h2>
            <p class="section-description">Temukan ruangan yang sesuai dengan kebutuhan Anda</p>
        </div>
        
        <!-- Search and Filter Section - Enhanced REAL TIME -->
        <div class="filter-box-modern">
            <form method="GET" action="#ruangan-section" class="filter-form-modern" id="filterForm">
                <div class="filter-grid-modern">
                    <div class="filter-item-modern">
                        <label class="filter-label-modern">
                            <span class="label-icon">🔍</span>
                            <span>Cari Ruangan</span>
                        </label>
                        <div class="input-with-clear">
                            <input type="text" 
                                   name="search" 
                                   id="searchInput"
                                   class="input-modern"
                                   placeholder="Nama atau fasilitas..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   autocomplete="off">
                            <button type="button" id="clearSearch" class="clear-input-btn" style="display: none;">
                                ✕
                            </button>
                        </div>
                    </div>
                    
                    <div class="filter-item-modern">
                        <label class="filter-label-modern">
                            <span class="label-icon">🏢</span>
                            <span>Kategori Ruangan</span>
                        </label>
                        <select name="kategori" id="kategoriSelect" class="select-modern">
                            <option value="">Semua Kategori</option>
                            <option value="lab" <?php echo $filter_kategori == 'lab' ? 'selected' : ''; ?>>🔬 Lab / Laboratorium</option>
                            <option value="ruang" <?php echo $filter_kategori == 'ruang' ? 'selected' : ''; ?>>🏫 Ruang Kelas</option>
                            <option value="lainnya" <?php echo $filter_kategori == 'lainnya' ? 'selected' : ''; ?>>🏢 Lainnya (Hall, Lapangan, dll)</option>
                        </select>
                    </div>
                    
                    <div class="filter-item-modern">
                        <label class="filter-label-modern">
                            <span class="label-icon">👥</span>
                            <span>Kapasitas Minimal</span>
                        </label>
                        <select name="kapasitas" id="kapasitasSelect" class="select-modern">
                            <option value="">Semua Kapasitas</option>
                            <option value="10" <?php echo $filter_kapasitas == '10' ? 'selected' : ''; ?>>10+ orang</option>
                            <option value="20" <?php echo $filter_kapasitas == '20' ? 'selected' : ''; ?>>20+ orang</option>
                            <option value="30" <?php echo $filter_kapasitas == '30' ? 'selected' : ''; ?>>30+ orang</option>
                            <option value="50" <?php echo $filter_kapasitas == '50' ? 'selected' : ''; ?>>50+ orang</option>
                            <option value="100" <?php echo $filter_kapasitas == '100' ? 'selected' : ''; ?>>100+ orang</option>
                        </select>
                    </div>
                    
                    <div class="filter-actions-modern">
                        <button type="button" id="resetBtn" class="btn-filter-secondary" style="<?php echo (!$search && !$filter_kapasitas && !$filter_kategori) ? 'display: none;' : ''; ?>">
                            <span>🔄</span> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Result Count - Modern -->
        <div class="result-info-modern" id="resultInfo" style="display: <?php echo ($search || $filter_kapasitas || $filter_kategori) ? 'flex' : 'none'; ?>;">
            <span class="result-icon">📊</span>
            <p>Menampilkan <strong id="totalRuangan"><?php echo $total_ruangan; ?></strong> ruangan dari pencarian Anda</p>
        </div>
        
        <!-- Loading State -->
        <div class="loading-state-modern" id="loadingState" style="display: none;">
            <div class="spinner-modern"></div>
            <p>Memuat data ruangan...</p>
        </div>
        
        <!-- Room Grid - Enhanced -->
        <div id="roomGridContainer">
        <?php if ($total_ruangan > 0): ?>
        <div class="room-grid-modern" id="roomGrid">
            <?php 
            // Ambil waktu sekarang sekali saja untuk efisiensi dan konsistensi
            $current_time = date('H:i:s');
            $current_date = date('Y-m-d');
            $current_day = date('N'); // 1 (Monday) to 7 (Sunday)
            $day_names = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
            $current_day_name = $day_names[$current_day];
            
            while ($row = mysqli_fetch_assoc($result)): ?>
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
            <a href="index.php" class="btn-empty-state">
                <span>🔄</span> Lihat Semua Ruangan
            </a>
        </div>
        <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
    // ===== REAL-TIME FILTER FUNCTIONALITY =====
    
    const searchInput = document.getElementById('searchInput');
    const kapasitasSelect = document.getElementById('kapasitasSelect');
    const kategoriSelect = document.getElementById('kategoriSelect');
    const resetBtn = document.getElementById('resetBtn');
    const clearSearchBtn = document.getElementById('clearSearch');
    const roomGridContainer = document.getElementById('roomGridContainer');
    const loadingState = document.getElementById('loadingState');
    const resultInfo = document.getElementById('resultInfo');
    const totalRuangan = document.getElementById('totalRuangan');
    
    let searchTimeout = null;
    
    // Show/hide clear button
    function toggleClearButton() {
        if (searchInput.value.trim()) {
            clearSearchBtn.style.display = 'flex';
        } else {
            clearSearchBtn.style.display = 'none';
        }
    }
    
    // Show/hide reset button
    function toggleResetButton() {
        const hasFilters = searchInput.value.trim() || kapasitasSelect.value || kategoriSelect.value;
        resetBtn.style.display = hasFilters ? 'flex' : 'none';
    }
    
    // Load rooms via AJAX
    function loadRooms(showLoading = true) {
        const search = searchInput.value.trim();
        const kapasitas = kapasitasSelect.value;
        const kategori = kategoriSelect.value;
        
        // Update URL without reload
        const url = new URLSearchParams();
        if (search) url.set('search', search);
        if (kapasitas) url.set('kapasitas', kapasitas);
        if (kategori) url.set('kategori', kategori);
        
        const newUrl = url.toString() ? '?' + url.toString() + '#ruangan-section' : 'index.php';
        window.history.pushState({}, '', newUrl);
        
        // Show loading state
        if (showLoading) {
            loadingState.style.display = 'flex';
            roomGridContainer.style.opacity = '0.5';
        }
        
        // Fetch data
        fetch('ajax_ruangan_load.php?' + url.toString())
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update room grid
                    roomGridContainer.innerHTML = data.html;
                    
                    // Update result count
                    if (search || kapasitas || kategori) {
                        resultInfo.style.display = 'flex';
                        totalRuangan.textContent = data.total;
                    } else {
                        resultInfo.style.display = 'none';
                    }
                    
                    // Hide loading
                    loadingState.style.display = 'none';
                    roomGridContainer.style.opacity = '1';
                    
                    // Update buttons
                    toggleResetButton();
                    
                    // Smooth scroll if filtering
                    if (search || kapasitas || kategori) {
                        const section = document.getElementById('ruangan-section');
                        if (section) {
                            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    }
                } else {
                    console.error('Error loading rooms:', data.message);
                    loadingState.style.display = 'none';
                    roomGridContainer.style.opacity = '1';
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                loadingState.style.display = 'none';
                roomGridContainer.style.opacity = '1';
            });
    }
    
    // Search input with debounce
    searchInput.addEventListener('input', function() {
        toggleClearButton();
        
        // Clear previous timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        // Set new timeout (500ms delay)
        searchTimeout = setTimeout(() => {
            loadRooms();
        }, 500);
    });
    
    // Kapasitas select - instant filter
    kapasitasSelect.addEventListener('change', function() {
        loadRooms();
    });
    
    // Kategori select - instant filter
    kategoriSelect.addEventListener('change', function() {
        loadRooms();
    });
    
    // Clear search button
    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearSearchBtn.style.display = 'none';
        loadRooms();
    });
    
    // Reset button
    resetBtn.addEventListener('click', function() {
        searchInput.value = '';
        kapasitasSelect.value = '';
        kategoriSelect.value = '';
        clearSearchBtn.style.display = 'none';
        resetBtn.style.display = 'none';
        resultInfo.style.display = 'none';
        
        // Update URL
        window.history.pushState({}, '', 'index.php');
        
        // Reload all rooms
        loadRooms();
    });
    
    // Initialize button states
    toggleClearButton();
    toggleResetButton();
    
    // Smooth scroll on page load if filtered
    <?php if ($search || $filter_kapasitas || $filter_kategori): ?>
    window.addEventListener('load', function() {
        const section = document.getElementById('ruangan-section');
        if (section) {
            setTimeout(() => {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    });
    <?php endif; ?>
    </script>
</body>
</html>
