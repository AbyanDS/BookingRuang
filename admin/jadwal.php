<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

require_admin_or_petugas();

$success = '';
$error = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = clean_input($_GET['delete']);
    $query = "DELETE FROM jadwal_ruangan WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        $success = 'Jadwal berhasil dihapus!';
    } else {
        $error = 'Gagal menghapus jadwal: ' . mysqli_error($conn);
    }
}

// Filter
$filter_ruangan = isset($_GET['ruangan']) ? $_GET['ruangan'] : '';
$filter_hari = isset($_GET['hari']) ? $_GET['hari'] : '';
$filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';
$filter_sesi = isset($_GET['sesi']) ? $_GET['sesi'] : '';

$query = "SELECT j.*, r.nama_ruangan 
          FROM jadwal_ruangan j 
          JOIN ruangan r ON j.ruangan_id = r.id 
          WHERE 1=1";

if ($filter_ruangan) {
    $query .= " AND j.ruangan_id = '$filter_ruangan'";
}

if ($filter_hari) {
    $query .= " AND j.hari = '$filter_hari'";
}

if ($filter_kelas) {
    $query .= " AND j.kelas = '$filter_kelas'";
}

if ($filter_sesi) {
    $query .= " AND j.sesi = '$filter_sesi'";
}

$query .= " ORDER BY 
            j.kelas ASC,
            j.sesi ASC,
            FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
            j.waktu_mulai ASC";
$result = mysqli_query($conn, $query);

// Ambil daftar ruangan untuk filter
$ruangan_query = "SELECT * FROM ruangan ORDER BY nama_ruangan ASC";
$ruangan_list = mysqli_query($conn, $ruangan_query);

// Ambil daftar kelas untuk filter
$kelas_query = "SELECT DISTINCT kelas FROM jadwal_ruangan ORDER BY kelas ASC";
$kelas_list = mysqli_query($conn, $kelas_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Ruangan - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <h1>Jadwal Ruangan</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="filter-box">
            <form method="GET" action="" class="filter-form-grid">
                <div class="filter-item">
                    <label>Filter Kelas:</label>
                    <select name="kelas" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        <?php while ($k = mysqli_fetch_assoc($kelas_list)): ?>
                            <option value="<?php echo htmlspecialchars($k['kelas']); ?>" <?php echo ($filter_kelas == $k['kelas']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($k['kelas']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-item">
                    <label>Filter Sesi:</label>
                    <select name="sesi" onchange="this.form.submit()">
                        <option value="">Semua Sesi</option>
                        <option value="1" <?php echo ($filter_sesi == '1') ? 'selected' : ''; ?>>Sesi 1</option>
                        <option value="2" <?php echo ($filter_sesi == '2') ? 'selected' : ''; ?>>Sesi 2</option>
                    </select>
                </div>
                
                <div class="filter-item">
                    <label>Filter Ruangan:</label>
                    <select name="ruangan" onchange="this.form.submit()">
                        <option value="">Semua Ruangan</option>
                        <?php 
                        mysqli_data_seek($ruangan_list, 0);
                        while ($r = mysqli_fetch_assoc($ruangan_list)): 
                        ?>
                            <option value="<?php echo $r['id']; ?>" <?php echo ($filter_ruangan == $r['id']) ? 'selected' : ''; ?>>
                                <?php echo $r['nama_ruangan']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-item">
                    <label>Filter Hari:</label>
                    <select name="hari" onchange="this.form.submit()">
                        <option value="">Semua Hari</option>
                        <option value="Senin" <?php echo ($filter_hari == 'Senin') ? 'selected' : ''; ?>>Senin</option>
                        <option value="Selasa" <?php echo ($filter_hari == 'Selasa') ? 'selected' : ''; ?>>Selasa</option>
                        <option value="Rabu" <?php echo ($filter_hari == 'Rabu') ? 'selected' : ''; ?>>Rabu</option>
                        <option value="Kamis" <?php echo ($filter_hari == 'Kamis') ? 'selected' : ''; ?>>Kamis</option>
                        <option value="Jumat" <?php echo ($filter_hari == 'Jumat') ? 'selected' : ''; ?>>Jumat</option>
                        <option value="Sabtu" <?php echo ($filter_hari == 'Sabtu') ? 'selected' : ''; ?>>Sabtu</option>
                        <option value="Minggu" <?php echo ($filter_hari == 'Minggu') ? 'selected' : ''; ?>>Minggu</option>
                    </select>
                </div>
                
                <?php if ($filter_kelas || $filter_sesi || $filter_ruangan || $filter_hari): ?>
                    <div class="filter-item filter-reset">
                        <a href="jadwal.php" class="btn btn-small">Reset Filter</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>Daftar Jadwal</h2>
                <a href="jadwal_add.php" class="btn btn-primary">+ Tambah Jadwal</a>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Ruangan</th>
                        <th>Kelas</th>
                        <th>Sesi</th>
                        <th>Hari</th>
                        <th>Waktu</th>
                        <th>Kegiatan</th>
                        <th>Penanggung Jawab</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $no = 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <?php echo $row['nama_ruangan']; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($row['kelas'] ?? '-'); ?></strong></td>
                                <td>
                                    <span class="badge badge-<?php echo $row['sesi'] == '1' ? 'info' : 'warning'; ?>">
                                        Sesi <?php echo $row['sesi'] ?? '-'; ?>
                                    </span>
                                </td>
                                <td><?php echo $row['hari']; ?></td>
                                <td><?php echo format_waktu($row['waktu_mulai']) . ' - ' . format_waktu($row['waktu_selesai']); ?></td>
                                <td><?php echo $row['kegiatan']; ?></td>
                                <td><?php echo $row['penanggung_jawab']; ?></td>
                                <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                <td>
                                    <a href="jadwal_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-small btn-edit">Edit</a>
                                    <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">Belum ada data jadwal</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card">
            <h2>Jadwal Per Hari</h2>
            <div class="jadwal-grid">
                <?php
                $hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                foreach ($hari_list as $hari):
                    $query_hari = "SELECT j.*, r.nama_ruangan 
                                   FROM jadwal_ruangan j 
                                   JOIN ruangan r ON j.ruangan_id = r.id 
                                   WHERE j.hari = '$hari' AND j.status = 'aktif'
                                   ORDER BY j.kelas ASC, j.sesi ASC, j.waktu_mulai ASC";
                    $result_hari = mysqli_query($conn, $query_hari);
                ?>
                    <div class="jadwal-day-card">
                        <h3><?php echo $hari; ?></h3>
                        <?php if (mysqli_num_rows($result_hari) > 0): ?>
                            <ul class="jadwal-list">
                                <?php while ($j = mysqli_fetch_assoc($result_hari)): ?>
                                    <li>
                                        <div class="jadwal-item-header">
                                            <span class="badge badge-<?php echo $j['sesi'] == '1' ? 'info' : 'warning'; ?>">
                                                <?php echo htmlspecialchars($j['kelas'] ?? ''); ?> - Sesi <?php echo $j['sesi'] ?? ''; ?>
                                            </span>
                                        </div>
                                        <strong><?php echo format_waktu($j['waktu_mulai']) . ' - ' . format_waktu($j['waktu_selesai']); ?></strong><br>
                                        <?php echo $j['kegiatan']; ?><br>
                                        <small><?php echo $j['nama_ruangan']; ?></small>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p class="no-jadwal">Tidak ada jadwal</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
