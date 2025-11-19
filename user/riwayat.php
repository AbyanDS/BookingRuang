<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

require_login();

// Redirect admin dan petugas
if (is_admin_or_petugas()) {
    header("Location: ../admin/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil statistik booking user
$stats = array();

// Total booking
$query_stats = "SELECT COUNT(*) as total FROM booking WHERE user_id = '$user_id'";
$result_stats = mysqli_query($conn, $query_stats);
$stats['total'] = mysqli_fetch_assoc($result_stats)['total'];

// Booking pending
$query_stats = "SELECT COUNT(*) as total FROM booking WHERE user_id = '$user_id' AND status = 'pending'";
$result_stats = mysqli_query($conn, $query_stats);
$stats['pending'] = mysqli_fetch_assoc($result_stats)['total'];

// Booking approved
$query_stats = "SELECT COUNT(*) as total FROM booking WHERE user_id = '$user_id' AND status = 'approved'";
$result_stats = mysqli_query($conn, $query_stats);
$stats['approved'] = mysqli_fetch_assoc($result_stats)['total'];

// Booking rejected
$query_stats = "SELECT COUNT(*) as total FROM booking WHERE user_id = '$user_id' AND status = 'rejected'";
$result_stats = mysqli_query($conn, $query_stats);
$stats['rejected'] = mysqli_fetch_assoc($result_stats)['total'];

// Filter
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT b.*, r.nama_ruangan 
          FROM booking b 
          JOIN ruangan r ON b.ruangan_id = r.id 
          WHERE b.user_id = '$user_id'";

if ($filter_status) {
    $query .= " AND b.status = '$filter_status'";
}

$query .= " ORDER BY b.created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Booking</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="riwayat-hero">
        <div class="container">
            <div class="hero-content-riwayat">
                <h1 class="hero-title-riwayat">
                    <i class="fas fa-history"></i> Riwayat Booking
                </h1>
                <p class="hero-subtitle-riwayat">Kelola dan pantau semua riwayat peminjaman ruangan Anda</p>
            </div>
        </div>
    </div>
    
    <div class="container riwayat-container">
        <!-- Statistics Dashboard -->
        <div class="stats-dashboard">
            <div class="stat-card-modern stat-total">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Total Booking</h3>
                    <p class="stat-value"><?php echo $stats['total']; ?></p>
                </div>
            </div>
            <div class="stat-card-modern stat-pending">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Menunggu</h3>
                    <p class="stat-value"><?php echo $stats['pending']; ?></p>
                </div>
            </div>
            <div class="stat-card-modern stat-approved">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Disetujui</h3>
                    <p class="stat-value"><?php echo $stats['approved']; ?></p>
                </div>
            </div>
            <div class="stat-card-modern stat-rejected">
                <div class="stat-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-label">Ditolak</h3>
                    <p class="stat-value"><?php echo $stats['rejected']; ?></p>
                </div>
            </div>
        </div>
        
        <!-- Filter and Search Section -->
        <div class="filter-search-section">
            <div class="search-box-riwayat">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Cari ruangan, keperluan, atau tanggal..." onkeyup="applyFilter()">
            </div>
            
            <div class="filter-controls">
                <div class="filter-group">
                    <label><i class="fas fa-filter"></i> Filter:</label>
                    <select name="status" id="filterStatus" onchange="applyFilter()" class="filter-select">
                        <option value="">Semua Status</option>
                        <option value="pending">⏳ Pending</option>
                        <option value="approved">✓ Approved</option>
                        <option value="rejected">✗ Rejected</option>
                        <option value="completed">✓ Completed</option>
                        <option value="cancelled">⊘ Cancelled</option>
                    </select>
                </div>
                
                <button onclick="resetFilter()" class="btn-reset-riwayat" id="resetBtn" style="display: none;">
                    <i class="fas fa-redo"></i> Reset
                </button>
                
                <div class="view-toggle">
                    <button class="view-btn active" onclick="switchView('card')" id="cardViewBtn">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button class="view-btn" onclick="switchView('list')" id="listViewBtn">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Booking Cards View -->
        <div id="cardView" class="booking-cards-grid">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php mysqli_data_seek($result, 0); ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="booking-card-history" data-status="<?php echo $row['status']; ?>">
                        <div class="booking-card-header-history status-<?php echo $row['status']; ?>">
                            <div class="booking-card-title">
                                <i class="fas fa-door-open"></i>
                                <h3><?php echo $row['nama_ruangan']; ?></h3>
                            </div>
                            <span class="status-badge-new badge-<?php echo $row['status']; ?>">
                                <?php 
                                $status_icons = [
                                    'pending' => '⏳',
                                    'approved' => '✓',
                                    'rejected' => '✗',
                                    'completed' => '✓',
                                    'cancelled' => '⊘'
                                ];
                                echo $status_icons[$row['status']] . ' ' . ucfirst($row['status']);
                                ?>
                            </span>
                        </div>
                        
                        <div class="booking-card-body-history">
                            <div class="booking-info-row">
                                <div class="info-icon"><i class="fas fa-calendar"></i></div>
                                <div class="info-content">
                                    <span class="info-label">Tanggal</span>
                                    <span class="info-value"><?php echo format_tanggal($row['tanggal_booking']); ?></span>
                                </div>
                            </div>
                            
                            <div class="booking-info-row">
                                <div class="info-icon"><i class="fas fa-clock"></i></div>
                                <div class="info-content">
                                    <span class="info-label">Waktu</span>
                                    <span class="info-value"><?php echo format_waktu($row['waktu_mulai']) . ' - ' . format_waktu($row['waktu_selesai']); ?></span>
                                </div>
                            </div>
                            
                            <div class="booking-info-row">
                                <div class="info-icon"><i class="fas fa-file-alt"></i></div>
                                <div class="info-content">
                                    <span class="info-label">Keperluan</span>
                                    <span class="info-value"><?php echo substr($row['keperluan'], 0, 60) . (strlen($row['keperluan']) > 60 ? '...' : ''); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="booking-card-footer-history">
                            <small class="booking-date-created">
                                <i class="fas fa-info-circle"></i> 
                                Dibuat: <?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?>
                            </small>
                            <a href="booking_detail.php?id=<?php echo $row['id']; ?>" class="btn-detail-history">
                                Lihat Detail <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h3>Belum Ada Riwayat</h3>
                    <p>Anda belum memiliki riwayat booking ruangan</p>
                    <a href="booking.php" class="btn-primary-modern">
                        <i class="fas fa-plus"></i> Buat Booking Baru
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- List View (Hidden by default) -->
        <div id="listView" class="booking-list-view" style="display: none;">
            <div class="table-responsive-modern">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> No</th>
                            <th><i class="fas fa-door-open"></i> Ruangan</th>
                            <th><i class="fas fa-calendar"></i> Tanggal</th>
                            <th><i class="fas fa-clock"></i> Waktu</th>
                            <th><i class="fas fa-file-alt"></i> Keperluan</th>
                            <th><i class="fas fa-info-circle"></i> Status</th>
                            <th><i class="fas fa-cog"></i> Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php mysqli_data_seek($result, 0); ?>
                            <?php $no = 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr class="table-row-modern">
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <div class="table-room-info">
                                            <strong><?php echo $row['nama_ruangan']; ?></strong>
                                        </div>
                                    </td>
                                    <td><?php echo format_tanggal($row['tanggal_booking']); ?></td>
                                    <td class="text-nowrap"><?php echo format_waktu($row['waktu_mulai']) . ' - ' . format_waktu($row['waktu_selesai']); ?></td>
                                    <td><span class="keperluan-text"><?php echo substr($row['keperluan'], 0, 40) . '...'; ?></span></td>
                                    <td>
                                        <span class="status-badge-new badge-<?php echo $row['status']; ?>">
                                            <?php 
                                            $status_icons = [
                                                'pending' => '⏳',
                                                'approved' => '✓',
                                                'rejected' => '✗',
                                                'completed' => '✓',
                                                'cancelled' => '⊘'
                                            ];
                                            echo $status_icons[$row['status']] . ' ' . ucfirst($row['status']);
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="booking_detail.php?id=<?php echo $row['id']; ?>" class="btn-action-modern">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="empty-state-small">
                                        <i class="fas fa-inbox"></i>
                                        <p>Belum ada riwayat booking</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    let currentView = 'card';
    let filterTimeout = null;
    
    // Apply filter with AJAX
    function applyFilter() {
        // Clear previous timeout
        if (filterTimeout) {
            clearTimeout(filterTimeout);
        }
        
        // Delay execution for better UX (debounce)
        filterTimeout = setTimeout(() => {
            const status = document.getElementById('filterStatus').value;
            const search = document.getElementById('searchInput').value;
            const resetBtn = document.getElementById('resetBtn');
            
            // Show/hide reset button
            if (status || search) {
                resetBtn.style.display = 'flex';
            } else {
                resetBtn.style.display = 'none';
            }
            
            // Show loading indicator
            showLoading();
            
            // Build URL with parameters
            const url = '../ajax_riwayat.php?view=' + currentView + 
                        (status ? '&status=' + status : '') + 
                        (search ? '&search=' + encodeURIComponent(search) : '');
            
            // Fetch data with AJAX
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (currentView === 'card') {
                        document.querySelector('.booking-cards-grid').innerHTML = data.html;
                    } else {
                        document.querySelector('.table-modern tbody').innerHTML = data.html;
                    }
                    hideLoading();
                })
                .catch(error => {
                    console.error('Error:', error);
                    hideLoading();
                    alert('Terjadi kesalahan saat memuat data. Silakan coba lagi.');
                });
        }, 300); // 300ms delay
    }
    
    // Reset filter
    function resetFilter() {
        document.getElementById('filterStatus').value = '';
        document.getElementById('searchInput').value = '';
        document.getElementById('resetBtn').style.display = 'none';
        applyFilter();
    }
    
    // Show loading indicator
    function showLoading() {
        if (currentView === 'card') {
            const container = document.querySelector('.booking-cards-grid');
            container.style.opacity = '0.5';
            container.style.pointerEvents = 'none';
        } else {
            const tbody = document.querySelector('.table-modern tbody');
            tbody.style.opacity = '0.5';
            tbody.style.pointerEvents = 'none';
        }
    }
    
    // Hide loading indicator
    function hideLoading() {
        if (currentView === 'card') {
            const container = document.querySelector('.booking-cards-grid');
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
        } else {
            const tbody = document.querySelector('.table-modern tbody');
            tbody.style.opacity = '1';
            tbody.style.pointerEvents = 'auto';
        }
    }
    
    // Switch between card and list view
    function switchView(view) {
        currentView = view;
        const cardView = document.getElementById('cardView');
        const listView = document.getElementById('listView');
        const cardBtn = document.getElementById('cardViewBtn');
        const listBtn = document.getElementById('listViewBtn');
        
        if (view === 'card') {
            cardView.style.display = 'grid';
            listView.style.display = 'none';
            cardBtn.classList.add('active');
            listBtn.classList.remove('active');
            localStorage.setItem('riwayatView', 'card');
        } else {
            cardView.style.display = 'none';
            listView.style.display = 'block';
            listBtn.classList.add('active');
            cardBtn.classList.remove('active');
            localStorage.setItem('riwayatView', 'list');
        }
    }
    
    // Load saved view preference
    document.addEventListener('DOMContentLoaded', function() {
        const savedView = localStorage.getItem('riwayatView') || 'card';
        switchView(savedView);
        
        // Add animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fadeInUp');
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.booking-card-history, .stat-card-modern').forEach(el => {
            observer.observe(el);
        });
    });
    </script>
</body>
</html>
