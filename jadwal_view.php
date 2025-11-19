<?php
session_start();
require_once 'config/database.php';
require_once 'config/functions.php';

// Auto cleanup completed bookings
// Script akan otomatis update status booking yang sudah expired
$today = date('Y-m-d');
$now = date('H:i:s');

$cleanup_query = "UPDATE booking 
                  SET status = 'completed',
                      updated_at = NOW()
                  WHERE status IN ('approved', 'pending')
                    AND (
                        tanggal_booking < '$today'
                        OR (tanggal_booking = '$today' AND waktu_selesai < '$now')
                    )";
mysqli_query($conn, $cleanup_query);

// Get current day
$current_day = date('N'); // 1 (Monday) to 7 (Sunday)
$day_names = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$today_name = $day_names[$current_day];

// Filter
$filter_ruangan = isset($_GET['ruangan']) ? $_GET['ruangan'] : '';
$filter_sesi = isset($_GET['sesi']) ? $_GET['sesi'] : '1'; // Default to Sesi 1
$filter_hari = isset($_GET['hari']) ? $_GET['hari'] : $today_name; // Default to today

// Ambil daftar ruangan untuk filter
$ruangan_query = "SELECT * FROM ruangan ORDER BY nama_ruangan ASC";
$ruangan_list = mysqli_query($conn, $ruangan_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Ruangan</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Hero Section -->
    <div class="jadwal-hero">
        <div class="container">
            <div class="hero-content-jadwal">
                <h1 class="hero-title-jadwal">
                    <i class="fas fa-calendar-day"></i> Jadwal Ruangan
                </h1>
                <p class="hero-subtitle-jadwal">
                    <?php echo $filter_hari; ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="container jadwal-container">
        <!-- Day Selection Buttons -->
        <div class="day-selection-section">
            <h3 class="selection-title">
                <i class="fas fa-calendar-week"></i> Pilih Hari
            </h3>
            <div class="day-buttons-grid">
                <?php foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari): ?>
                    <button class="day-btn <?php echo $filter_hari == $hari ? 'active' : ''; ?> <?php echo $hari == $today_name ? 'today-indicator' : ''; ?>" 
                            onclick="switchToDay('<?php echo $hari; ?>')">
                        <?php if ($hari == $today_name): ?>
                            <span class="today-badge">Hari Ini</span>
                        <?php endif; ?>
                        <span class="day-name"><?php echo $hari; ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Session Toggle -->
        <div class="today-schedule-header">
            <div class="schedule-info">
                <div class="schedule-day-badge">
                    <i class="fas fa-calendar-check"></i>
                    <span id="selectedDayName"><?php echo $filter_hari; ?></span>
                </div>
            </div>
        </div>
        
        <div class="view-toggle-jadwal">
            <button class="view-btn-jadwal <?php echo $filter_sesi == '1' ? 'active' : ''; ?>" onclick="switchToSesi('1')" id="sesi1Btn">
                Sesi 1
            </button>
            <button class="view-btn-jadwal <?php echo $filter_sesi == '2' ? 'active' : ''; ?>" onclick="switchToSesi('2')" id="sesi2Btn">
                Sesi 2
            </button>
        </div>
        
        <!-- Search & Filter Section - REDESIGNED -->
        <div class="schedule-filter-section-new">
            <div class="search-filter-wrapper">
                <!-- Search Bar -->
                <div class="search-box-modern">
                    <div class="search-icon-wrapper">
                        <i class="fas fa-search"></i>
                    </div>
                    <input type="text" 
                           id="searchSchedule" 
                           class="search-input-modern"
                           placeholder="Cari ruangan atau kelas... (misal: Lab 1, X RPL 1)"
                           autocomplete="off">
                    <button id="clearSearch" class="clear-search-modern" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Filter Pills -->
                <div class="filter-pills-container">
                    <div class="filter-pill-item">
                        <div class="filter-pill-label">
                            <i class="fas fa-door-open"></i>
                            <span>Tipe Ruangan</span>
                        </div>
                        <select id="filterRoomType" class="filter-select-modern">
                            <option value="">Semua</option>
                            <option value="lab">🔬 Lab</option>
                            <option value="ruang">🏫 Ruang Kelas</option>
                            <option value="other">🏢 Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="filter-pill-item">
                        <div class="filter-pill-label">
                            <i class="fas fa-users"></i>
                            <span>Kelas</span>
                        </div>
                        <select id="filterClass" class="filter-select-modern">
                            <option value="">Semua</option>
                            <?php
                            // Get unique classes from jadwal_ruangan
                            $query_classes = "SELECT DISTINCT kelas 
                                            FROM jadwal_ruangan 
                                            WHERE kelas IS NOT NULL 
                                            AND kelas != '' 
                                            AND status = 'aktif'
                                            ORDER BY kelas ASC";
                            $result_classes = mysqli_query($conn, $query_classes);
                            while($class = mysqli_fetch_assoc($result_classes)):
                            ?>
                                <option value="<?php echo htmlspecialchars($class['kelas']); ?>">
                                    <?php echo htmlspecialchars($class['kelas']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <button id="resetFilters" class="reset-btn-modern" title="Reset semua filter">
                        <i class="fas fa-redo"></i>
                        <span>Reset</span>
                    </button>
                </div>
            </div>
            
            <!-- Results Counter -->
            <div class="filter-results-badge" id="filterResultsInfo" style="display: none;">
                <div class="results-badge-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="results-badge-text">
                    <span id="resultsCount">0</span> ruangan ditemukan
                </div>
            </div>
        </div>
        
        <!-- Room Schedule View -->
        <div id="todayView" class="jadwal-weekly-view">
            <div class="jadwal-grid-today" id="scheduleContent">
                <?php
                // Query untuk semua ruangan dengan jadwal di hari dan sesi ini
                $query_rooms = "SELECT DISTINCT r.* 
                               FROM ruangan r 
                               LEFT JOIN jadwal_ruangan j ON r.id = j.ruangan_id 
                                   AND j.hari = '$filter_hari' 
                                   AND j.status = 'aktif'
                                   AND j.sesi = '$filter_sesi'
                               WHERE r.status = 'tersedia'";
                
                if ($filter_ruangan) {
                    $query_rooms .= " AND r.id = '$filter_ruangan'";
                }
                
                $query_rooms .= " ORDER BY r.nama_ruangan ASC";
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
                                               AND hari = '$filter_hari' 
                                               AND status = 'aktif'
                                               AND sesi = '$filter_sesi'
                                               ORDER BY waktu_mulai ASC";
                            $result_schedules = mysqli_query($conn, $query_schedules);
                            $schedule_count = mysqli_num_rows($result_schedules);
                            ?>
                            
                            <div class="room-schedule-card">
                                <div class="room-schedule-header">
                                    <div class="room-name-section">
                                        <i class="fas fa-door-open"></i>
                                        <h3><?php echo $room['nama_ruangan']; ?></h3>
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
                                                ?>
                                                <button class="class-badge-btn <?php echo $status_class; ?>" 
                                                        onclick="showScheduleDetail(<?php echo htmlspecialchars(json_encode($schedule)); ?>, '<?php echo $room['nama_ruangan']; ?>')">
                                                    <div class="class-badge-content">
                                                        <div class="class-name"><?php echo $schedule['kelas']; ?></div>
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
            </div>
        </div>
        
        <!-- Modal Detail Jadwal -->
        <div id="scheduleDetailModal" class="schedule-modal">
            <div class="schedule-modal-content">
                <div class="schedule-modal-header">
                    <h2><i class="fas fa-info-circle"></i> Detail Jadwal</h2>
                    <button class="schedule-modal-close" onclick="closeScheduleModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="schedule-modal-body" id="scheduleModalBody">
                    <!-- Content will be filled by JavaScript -->
                </div>
            </div>
        </div>
        
        <!-- Info Box -->
        <?php if (is_logged_in() && !is_admin_or_petugas()): ?>
        <div class="info-box-jadwal">
            <div class="info-icon-box">
                <i class="fas fa-lightbulb"></i>
            </div>
            <div class="info-content-box">
                <h4>💡 Tips Booking</h4>
                <p>Cek jadwal ruangan terlebih dahulu untuk memastikan waktu yang Anda pilih tidak bentrok dengan kegiatan rutin yang sudah terjadwal.</p>
                <a href="user/booking.php" class="btn-booking-now">
                    <i class="fas fa-calendar-plus"></i> Booking Ruangan Sekarang
                </a>
            </div>
        </div>
        <?php elseif (is_admin_or_petugas()): ?>
        <div class="info-box-jadwal admin-info">
            <div class="info-icon-box">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="info-content-box">
                <h4>ℹ️ Informasi</h4>
                <p>Anda login sebagai <?php echo is_admin() ? 'Admin' : 'Petugas'; ?>. Anda tidak dapat melakukan booking ruangan, hanya mengelola approval dan jadwal.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
    let currentSesi = '<?php echo $filter_sesi; ?>';
    let currentHari = '<?php echo $filter_hari; ?>';
    
    // Show schedule detail modal
    function showScheduleDetail(schedule, roomName) {
        const modal = document.getElementById('scheduleDetailModal');
        const modalBody = document.getElementById('scheduleModalBody');
        
        // Determine status
        const now = new Date();
        const today = new Date().toISOString().split('T')[0];
        const currentTime = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0') + ':00';
        
        let statusBadge = '';
        let statusClass = '';
        
        if (currentTime >= schedule.waktu_mulai && currentTime <= schedule.waktu_selesai) {
            statusBadge = '<span class="detail-status-badge ongoing"><i class="fas fa-circle"></i> Sedang Berlangsung</span>';
            statusClass = 'ongoing';
        } else if (currentTime > schedule.waktu_selesai) {
            statusBadge = '<span class="detail-status-badge done"><i class="fas fa-check-circle"></i> Selesai</span>';
            statusClass = 'done';
        } else {
            statusBadge = '<span class="detail-status-badge upcoming"><i class="fas fa-clock"></i> Akan Datang</span>';
            statusClass = 'upcoming';
        }
        
        // Build modal content
        let content = `
            <div class="schedule-detail-container ${statusClass}">
                <div class="detail-status-section">
                    ${statusBadge}
                </div>
                
                <div class="detail-section">
                    <div class="detail-icon"><i class="fas fa-door-open"></i></div>
                    <div class="detail-content">
                        <div class="detail-label">Ruangan</div>
                        <div class="detail-value">${roomName}</div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <div class="detail-icon"><i class="fas fa-users"></i></div>
                    <div class="detail-content">
                        <div class="detail-label">Kelas</div>
                        <div class="detail-value">${schedule.kelas}</div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <div class="detail-icon"><i class="fas fa-calendar-day"></i></div>
                    <div class="detail-content">
                        <div class="detail-label">Hari</div>
                        <div class="detail-value">${schedule.hari} - Sesi ${schedule.sesi}</div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <div class="detail-icon"><i class="fas fa-clock"></i></div>
                    <div class="detail-content">
                        <div class="detail-label">Waktu</div>
                        <div class="detail-value">${schedule.waktu_mulai.substring(0, 5)} - ${schedule.waktu_selesai.substring(0, 5)} WIB</div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <div class="detail-icon"><i class="fas fa-book"></i></div>
                    <div class="detail-content">
                        <div class="detail-label">Kegiatan</div>
                        <div class="detail-value">${schedule.kegiatan}</div>
                    </div>
                </div>
                
                ${schedule.penanggung_jawab ? `
                <div class="detail-section">
                    <div class="detail-icon"><i class="fas fa-user-tie"></i></div>
                    <div class="detail-content">
                        <div class="detail-label">Penanggung Jawab</div>
                        <div class="detail-value">${schedule.penanggung_jawab}</div>
                    </div>
                </div>
                ` : ''}
                
                ${schedule.keterangan ? `
                <div class="detail-section">
                    <div class="detail-icon"><i class="fas fa-sticky-note"></i></div>
                    <div class="detail-content">
                        <div class="detail-label">Keterangan</div>
                        <div class="detail-value">${schedule.keterangan}</div>
                    </div>
                </div>
                ` : ''}
            </div>
        `;
        
        modalBody.innerHTML = content;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    // Close schedule detail modal
    function closeScheduleModal() {
        const modal = document.getElementById('scheduleDetailModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('scheduleDetailModal');
        if (event.target === modal) {
            closeScheduleModal();
        }
    });
    
    // Close modal with ESC key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeScheduleModal();
        }
    });
    
    // Switch to different day without page reload
    function switchToDay(hari) {
        currentHari = hari;
        
        // Update active state of day buttons
        document.querySelectorAll('.day-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.closest('.day-btn').classList.add('active');
        
        // Update selected day name
        document.getElementById('selectedDayName').textContent = hari;
        
        // Load schedule via AJAX
        loadSchedule(hari, currentSesi);
        
        // Update URL without reload
        const url = new URLSearchParams(window.location.search);
        url.set('hari', hari);
        url.set('sesi', currentSesi);
        window.history.pushState({}, '', 'jadwal_view.php?' + url.toString());
    }
    
    // Switch between sesi 1 and sesi 2 without page reload
    function switchToSesi(sesi) {
        currentSesi = sesi;
        
        // Update button states
        const sesi1Btn = document.getElementById('sesi1Btn');
        const sesi2Btn = document.getElementById('sesi2Btn');
        
        if (sesi === '1') {
            sesi1Btn.classList.add('active');
            sesi2Btn.classList.remove('active');
        } else {
            sesi2Btn.classList.add('active');
            sesi1Btn.classList.remove('active');
        }
        
        // Load schedule via AJAX
        loadSchedule(currentHari, sesi);
        
        // Update URL without reload
        const url = new URLSearchParams(window.location.search);
        url.set('sesi', sesi);
        url.set('hari', currentHari);
        window.history.pushState({}, '', 'jadwal_view.php?' + url.toString());
    }
    
    // Load schedule via AJAX
    function loadSchedule(hari, sesi) {
        const scheduleContent = document.getElementById('scheduleContent');
        
        // Show loading state
        scheduleContent.style.opacity = '0.5';
        scheduleContent.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin" style="font-size: 48px; color: #667eea;"></i><p style="margin-top: 20px; color: #7f8c8d;">Memuat jadwal...</p></div>';
        
        // Fetch data from server
        fetch(`ajax_jadwal_load.php?hari=${encodeURIComponent(hari)}&sesi=${sesi}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    scheduleContent.innerHTML = data.html;
                    scheduleContent.style.opacity = '1';
                } else {
                    scheduleContent.innerHTML = '<div class="no-schedule-message"><div class="no-schedule-icon"><i class="fas fa-exclamation-triangle"></i></div><h3>Terjadi Kesalahan</h3><p>Gagal memuat jadwal. Silakan coba lagi.</p></div>';
                    scheduleContent.style.opacity = '1';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                scheduleContent.innerHTML = '<div class="no-schedule-message"><div class="no-schedule-icon"><i class="fas fa-exclamation-triangle"></i></div><h3>Terjadi Kesalahan</h3><p>Gagal memuat jadwal. Silakan coba lagi.</p></div>';
                scheduleContent.style.opacity = '1';
            });
    }
    
    // Auto refresh every 1 minute to update ongoing status
    setInterval(function() {
        loadSchedule(currentHari, currentSesi);
    }, 60000);
    
    // ===== SEARCH & FILTER FUNCTIONALITY =====
    
    const searchInput = document.getElementById('searchSchedule');
    const filterRoomType = document.getElementById('filterRoomType');
    const filterClass = document.getElementById('filterClass');
    const resetFiltersBtn = document.getElementById('resetFilters');
    const clearSearchBtn = document.getElementById('clearSearch');
    const filterResultsInfo = document.getElementById('filterResultsInfo');
    const resultsCount = document.getElementById('resultsCount');
    
    // Apply filters
    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const roomType = filterRoomType.value;
        const classFilter = filterClass.value;
        
        const allCards = document.querySelectorAll('.room-schedule-card');
        let visibleCount = 0;
        
        allCards.forEach(card => {
            const roomName = card.querySelector('.room-name-section h3').textContent.toLowerCase();
            const classBadges = card.querySelectorAll('.class-name');
            
            // Get all class names in this room
            let classNames = [];
            classBadges.forEach(badge => {
                classNames.push(badge.textContent.toLowerCase());
            });
            
            // Search filter
            let matchSearch = true;
            if (searchTerm) {
                matchSearch = roomName.includes(searchTerm) || 
                            classNames.some(className => className.includes(searchTerm));
            }
            
            // Room type filter
            let matchRoomType = true;
            if (roomType) {
                if (roomType === 'lab') {
                    matchRoomType = roomName.includes('lab');
                } else if (roomType === 'ruang') {
                    matchRoomType = roomName.includes('ruang') && !roomName.includes('lab');
                } else if (roomType === 'other') {
                    matchRoomType = !roomName.includes('lab') && !roomName.includes('ruang');
                }
            }
            
            // Class filter
            let matchClass = true;
            if (classFilter) {
                matchClass = classNames.some(className => className === classFilter.toLowerCase());
            }
            
            // Show/hide card based on all filters
            if (matchSearch && matchRoomType && matchClass) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Update results info
        if (searchTerm || roomType || classFilter) {
            filterResultsInfo.style.display = 'flex';
            resultsCount.textContent = visibleCount;
        } else {
            filterResultsInfo.style.display = 'none';
        }
        
        // Show/hide clear search button
        if (searchTerm) {
            clearSearchBtn.style.display = 'flex';
        } else {
            clearSearchBtn.style.display = 'none';
        }
        
        // Show message if no results
        const scheduleGrid = document.querySelector('.room-schedule-grid');
        if (scheduleGrid) {
            let noResultsMsg = document.querySelector('.no-results-filter-msg');
            
            if (visibleCount === 0 && (searchTerm || roomType || classFilter)) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'no-results-filter-msg';
                    noResultsMsg.innerHTML = `
                        <div class="no-schedule-icon">
                            <i class="fas fa-filter"></i>
                        </div>
                        <h3>Tidak Ada Hasil</h3>
                        <p>Tidak ditemukan ruangan atau kelas yang sesuai dengan filter</p>
                    `;
                    scheduleGrid.parentNode.appendChild(noResultsMsg);
                }
                noResultsMsg.style.display = 'block';
                scheduleGrid.style.display = 'none';
            } else {
                if (noResultsMsg) {
                    noResultsMsg.style.display = 'none';
                }
                scheduleGrid.style.display = 'grid';
            }
        }
    }
    
    // Event listeners for filters
    searchInput.addEventListener('input', applyFilters);
    filterRoomType.addEventListener('change', applyFilters);
    filterClass.addEventListener('change', applyFilters);
    
    // Clear search
    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearSearchBtn.style.display = 'none';
        applyFilters();
    });
    
    // Reset all filters
    resetFiltersBtn.addEventListener('click', function() {
        searchInput.value = '';
        filterRoomType.value = '';
        filterClass.value = '';
        clearSearchBtn.style.display = 'none';
        filterResultsInfo.style.display = 'none';
        applyFilters();
    });
    
    // Apply filters after AJAX load
    const originalLoadSchedule = loadSchedule;
    loadSchedule = function(hari, sesi) {
        originalLoadSchedule(hari, sesi);
        
        // Wait for content to load, then apply filters
        setTimeout(function() {
            applyFilters();
        }, 500);
    };
    </script>
</body>
</html>
