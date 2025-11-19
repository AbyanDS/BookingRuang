<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

// Admin dan petugas bisa kelola ruangan
require_admin_or_petugas();

// Search & pagination
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query with filters
$where = "WHERE 1=1";
if ($search) {
    $where .= " AND (nama_ruangan LIKE '%$search%' OR fasilitas LIKE '%$search%')";
}
if ($status_filter) {
    $where .= " AND status = '$status_filter'";
}

// Count total
$count_query = "SELECT COUNT(*) as total FROM ruangan $where";
$count_result = mysqli_query($conn, $count_query);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

// Ambil data ruangan
$query = "SELECT * FROM ruangan $where ORDER BY nama_ruangan ASC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Ruangan - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header-manage">
            <div>
                <h1><i class="fas fa-door-open"></i> Kelola Ruangan</h1>
                <p class="page-subtitle">Manajemen data ruangan dengan mudah dan cepat</p>
            </div>
            <button onclick="openAddModal()" class="btn btn-primary btn-add-new">
                <i class="fas fa-plus"></i> Tambah Ruangan
            </button>
        </div>
        
        <!-- Alert Messages -->
        <div id="alertContainer"></div>
        
        <!-- Search & Filter Section -->
        <div class="manage-filter-section">
            <div class="filter-container-manage">
                <div class="search-box-manage">
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchInput" placeholder="Cari nama ruangan atau fasilitas..." value="<?php echo htmlspecialchars($search); ?>">
                        <button class="clear-search-btn" id="clearSearch" style="<?php echo $search ? 'display:flex;' : 'display:none;'; ?>">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <div class="filter-group-manage">
                    <div class="filter-item-manage">
                        <label><i class="fas fa-filter"></i> Status</label>
                        <select id="statusFilter" class="filter-select-manage">
                            <option value="">Semua Status</option>
                            <option value="tersedia" <?php echo ($status_filter == 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                            <option value="maintenance" <?php echo ($status_filter == 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                        </select>
                    </div>
                    
                    <button onclick="resetFilters()" class="reset-filters-btn">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>
            
            <!-- Results Info -->
            <div class="filter-results-info-manage" style="<?php echo ($search || $status_filter) ? 'display:flex;' : 'display:none;'; ?>">
                <i class="fas fa-info-circle"></i>
                <span>Menampilkan <strong id="resultsCount"><?php echo $total_rows; ?></strong> dari <?php echo $total_rows; ?> ruangan</span>
            </div>
        </div>
        
        <!-- Rooms Table -->
        <div class="card manage-card">
            <div class="manage-table-container">
                <table class="table manage-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th style="width: 100px;">Foto</th>
                            <th>Nama Ruangan</th>
                            <th style="width: 120px;">Kapasitas</th>
                            <th>Fasilitas</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 200px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php $no = $offset + 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr id="row-<?php echo $row['id']; ?>" class="manage-table-row">
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <?php if ($row['foto']): ?>
                                            <img src="../uploads/<?php echo $row['foto']; ?>" 
                                                 alt="<?php echo $row['nama_ruangan']; ?>" 
                                                 class="room-thumbnail-img" 
                                                 onclick="showImageModal(this.src, '<?php echo $row['nama_ruangan']; ?>')">
                                        <?php else: ?>
                                            <div class="room-thumbnail-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong class="room-name-text"><?php echo $row['nama_ruangan']; ?></strong>
                                    </td>
                                    <td>
                                        <span class="capacity-badge">
                                            <i class="fas fa-users"></i> <?php echo $row['kapasitas']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="facilities-text" title="<?php echo htmlspecialchars($row['fasilitas']); ?>">
                                            <?php 
                                            $fasilitas = $row['fasilitas'];
                                            echo strlen($fasilitas) > 60 ? substr($fasilitas, 0, 60) . '...' : $fasilitas;
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $row['status']; ?>">
                                            <i class="fas fa-circle"></i> <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons-group">
                                            <button onclick="viewRoom(<?php echo $row['id']; ?>)" 
                                                    class="btn-action btn-view" 
                                                    title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="editRoom(<?php echo $row['id']; ?>)" 
                                                    class="btn-action btn-edit" 
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="deleteRoom(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nama_ruangan']); ?>')" 
                                                    class="btn-action btn-delete" 
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data-message">
                                    <div class="no-data-content">
                                        <i class="fas fa-inbox"></i>
                                        <h3>Tidak Ada Data</h3>
                                        <p><?php echo $search ? 'Tidak ditemukan ruangan yang sesuai pencarian' : 'Belum ada ruangan yang ditambahkan'; ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination-container">
                    <div class="pagination-info">
                        Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>
                    </div>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page-1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $status_filter ? '&status='.$status_filter : ''; ?>" class="pagination-btn">
                                <i class="fas fa-chevron-left"></i> Prev
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $status_filter ? '&status='.$status_filter : ''; ?>" 
                               class="pagination-btn <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page+1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $status_filter ? '&status='.$status_filter : ''; ?>" class="pagination-btn">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal Preview Image -->
    <div id="imageModal" class="modal-overlay" onclick="closeImageModal()">
        <span class="modal-close">&times;</span>
        <img class="modal-image-content" id="modalImage">
        <div class="modal-caption" id="imageCaption"></div>
    </div>
    
    <!-- Modal Add/Edit Room -->
    <div id="roomModal" class="modal-overlay">
        <div class="modal-content-form" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2 id="modalTitle"><i class="fas fa-plus-circle"></i> Tambah Ruangan</h2>
                <button class="modal-close-btn" onclick="closeRoomModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="roomForm" enctype="multipart/form-data">
                <input type="hidden" id="roomId" name="id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-door-open"></i> Nama Ruangan *</label>
                        <input type="text" id="namaRuangan" name="nama_ruangan" required placeholder="Contoh: Lab 1">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-users"></i> Kapasitas *</label>
                        <input type="number" id="kapasitas" name="kapasitas" required min="1" placeholder="Jumlah orang">
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-list"></i> Fasilitas *</label>
                    <textarea id="fasilitas" name="fasilitas" rows="3" required placeholder="Contoh: Proyektor, AC, Whiteboard, Wifi"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-toggle-on"></i> Status *</label>
                        <select id="status" name="status" required>
                            <option value="tersedia">Tersedia</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-image"></i> Foto Ruangan</label>
                        <input type="file" id="foto" name="foto" accept="image/*">
                        <small>Format: JPG, PNG, GIF. Max 5MB</small>
                    </div>
                </div>
                
                <div id="currentPhotoPreview" style="display:none;" class="current-photo-section">
                    <label>Foto Saat Ini:</label>
                    <img id="currentPhoto" src="" alt="Current Photo" class="current-photo-img">
                    <small>Upload foto baru untuk mengganti</small>
                </div>
                
                <div class="modal-footer">
                    <button type="button" onclick="closeRoomModal()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal View Room Details -->
    <div id="viewModal" class="modal-overlay">
        <div class="modal-content-view" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2><i class="fas fa-info-circle"></i> Detail Ruangan</h2>
                <button class="modal-close-btn" onclick="closeViewModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="view-content" id="viewContent">
                <!-- Content loaded via JavaScript -->
            </div>
            
            <div class="modal-footer">
                <button onclick="closeViewModal()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    // ===== SEARCH & FILTER =====
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const clearSearchBtn = document.getElementById('clearSearch');
    
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            applyFilters();
        }, 500);
        
        clearSearchBtn.style.display = searchInput.value ? 'flex' : 'none';
    });
    
    statusFilter.addEventListener('change', applyFilters);
    
    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearSearchBtn.style.display = 'none';
        applyFilters();
    });
    
    function applyFilters() {
        const search = searchInput.value;
        const status = statusFilter.value;
        
        let url = 'ruangan.php?';
        if (search) url += 'search=' + encodeURIComponent(search) + '&';
        if (status) url += 'status=' + status + '&';
        
        window.location.href = url.slice(0, -1);
    }
    
    function resetFilters() {
        window.location.href = 'ruangan.php';
    }
    
    // ===== IMAGE MODAL =====
    function showImageModal(src, title) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        const caption = document.getElementById('imageCaption');
        
        modal.style.display = 'flex';
        modalImg.src = src;
        caption.textContent = title || 'Foto Ruangan';
        document.body.style.overflow = 'hidden';
    }
    
    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // ===== ROOM MODAL (ADD/EDIT) =====
    function openAddModal() {
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Tambah Ruangan';
        document.getElementById('roomForm').reset();
        document.getElementById('roomId').value = '';
        document.getElementById('currentPhotoPreview').style.display = 'none';
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Simpan';
        
        const modal = document.getElementById('roomModal');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function editRoom(id) {
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Ruangan';
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Update';
        
        // Fetch room data
        fetch('ruangan_api.php?action=get&id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const room = data.data;
                    document.getElementById('roomId').value = room.id;
                    document.getElementById('namaRuangan').value = room.nama_ruangan;
                    document.getElementById('kapasitas').value = room.kapasitas;
                    document.getElementById('fasilitas').value = room.fasilitas;
                    document.getElementById('status').value = room.status;
                    
                    if (room.foto) {
                        document.getElementById('currentPhotoPreview').style.display = 'block';
                        document.getElementById('currentPhoto').src = '../uploads/' + room.foto;
                    } else {
                        document.getElementById('currentPhotoPreview').style.display = 'none';
                    }
                    
                    const modal = document.getElementById('roomModal');
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                } else {
                    showAlert('error', data.message || 'Gagal mengambil data ruangan');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Terjadi kesalahan saat mengambil data');
            });
    }
    
    function closeRoomModal() {
        const modal = document.getElementById('roomModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // ===== VIEW MODAL =====
    function viewRoom(id) {
        fetch('ruangan_api.php?action=get&id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const room = data.data;
                    const content = document.getElementById('viewContent');
                    
                    content.innerHTML = `
                        <div class="view-detail-grid">
                            ${room.foto ? `
                            <div class="view-photo-section">
                                <img src="../uploads/${room.foto}" alt="${room.nama_ruangan}" class="view-photo-large">
                            </div>
                            ` : `
                            <div class="view-photo-placeholder">
                                <i class="fas fa-image"></i>
                                <p>Tidak ada foto</p>
                            </div>
                            `}
                            
                            <div class="view-info-section">
                                <div class="view-detail-item">
                                    <div class="view-label"><i class="fas fa-door-open"></i> Nama Ruangan</div>
                                    <div class="view-value">${room.nama_ruangan}</div>
                                </div>
                                
                                <div class="view-detail-item">
                                    <div class="view-label"><i class="fas fa-users"></i> Kapasitas</div>
                                    <div class="view-value">${room.kapasitas} orang</div>
                                </div>
                                
                                <div class="view-detail-item">
                                    <div class="view-label"><i class="fas fa-list"></i> Fasilitas</div>
                                    <div class="view-value">${room.fasilitas}</div>
                                </div>
                                
                                <div class="view-detail-item">
                                    <div class="view-label"><i class="fas fa-toggle-on"></i> Status</div>
                                    <div class="view-value">
                                        <span class="status-badge status-${room.status}">
                                            <i class="fas fa-circle"></i> ${room.status.charAt(0).toUpperCase() + room.status.slice(1)}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    const modal = document.getElementById('viewModal');
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                } else {
                    showAlert('error', data.message || 'Gagal mengambil data ruangan');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Terjadi kesalahan saat mengambil data');
            });
    }
    
    function closeViewModal() {
        const modal = document.getElementById('viewModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // ===== FORM SUBMIT =====
    document.getElementById('roomForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const id = document.getElementById('roomId').value;
        formData.append('action', id ? 'update' : 'add');
        
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        
        fetch('ruangan_api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                closeRoomModal();
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showAlert('error', data.message || 'Gagal menyimpan data');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Simpan';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Terjadi kesalahan saat menyimpan data');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Simpan';
        });
    });
    
    // ===== DELETE ROOM =====
    function deleteRoom(id, nama) {
        if (confirm(`Apakah Anda yakin ingin menghapus ruangan "${nama}"?\n\nPerhatian: Ruangan yang masih memiliki booking tidak dapat dihapus.`)) {
            fetch('ruangan_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=delete&id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    // Remove row with animation
                    const row = document.getElementById('row-' + id);
                    if (row) {
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(-20px)';
                        setTimeout(() => {
                            row.remove();
                            // Check if table is empty
                            const tbody = document.querySelector('.manage-table tbody');
                            if (tbody.children.length === 0) {
                                setTimeout(() => window.location.reload(), 500);
                            }
                        }, 300);
                    }
                } else {
                    showAlert('error', data.message || 'Gagal menghapus ruangan');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Terjadi kesalahan saat menghapus data');
            });
        }
    }
    
    // ===== ALERT SYSTEM =====
    function showAlert(type, message) {
        const container = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-animated`;
        alert.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.remove()" class="alert-close">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        container.appendChild(alert);
        
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    }
    
    // Close modals on ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
            closeRoomModal();
            closeViewModal();
        }
    });
    
    // Close modals on backdrop click
    document.getElementById('roomModal').addEventListener('click', function(e) {
        if (e.target === this) closeRoomModal();
    });
    
    document.getElementById('viewModal').addEventListener('click', function(e) {
        if (e.target === this) closeViewModal();
    });
    </script>
</body>
</html>
