<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

require_admin_or_petugas();

$id = isset($_GET['id']) ? clean_input($_GET['id']) : 0;

// Ambil data jadwal
$query = "SELECT * FROM jadwal_ruangan WHERE id = '$id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: jadwal.php");
    exit();
}

$jadwal = mysqli_fetch_assoc($result);
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ruangan_id = clean_input($_POST['ruangan_id']);
    $kelas = clean_input($_POST['kelas']);
    $sesi = clean_input($_POST['sesi']);
    $hari = clean_input($_POST['hari']);
    $waktu_mulai = clean_input($_POST['waktu_mulai']);
    $waktu_selesai = clean_input($_POST['waktu_selesai']);
    $kegiatan = clean_input($_POST['kegiatan']);
    $penanggung_jawab = clean_input($_POST['penanggung_jawab']);
    $keterangan = clean_input($_POST['keterangan']);
    $status = clean_input($_POST['status']);
    
    // Validasi waktu
    if ($waktu_selesai <= $waktu_mulai) {
        $error = 'Waktu selesai harus lebih besar dari waktu mulai!';
    } else {
        // Cek jadwal bentrok (kecuali jadwal yang sedang diedit)
        $query_check = "SELECT * FROM jadwal_ruangan 
                        WHERE ruangan_id = '$ruangan_id' 
                        AND kelas = '$kelas'
                        AND sesi = '$sesi'
                        AND hari = '$hari'
                        AND status = 'aktif'
                        AND id != '$id'
                        AND (
                            (waktu_mulai < '$waktu_selesai' AND waktu_selesai > '$waktu_mulai')
                        )";
        $result_check = mysqli_query($conn, $query_check);
        
        if (mysqli_num_rows($result_check) > 0) {
            $error = 'Jadwal bentrok dengan jadwal yang sudah ada untuk kelas dan sesi ini!';
        } else {
            $query = "UPDATE jadwal_ruangan SET 
                      ruangan_id = '$ruangan_id',
                      kelas = '$kelas',
                      sesi = '$sesi',
                      hari = '$hari',
                      waktu_mulai = '$waktu_mulai',
                      waktu_selesai = '$waktu_selesai',
                      kegiatan = '$kegiatan',
                      penanggung_jawab = '$penanggung_jawab',
                      keterangan = '$keterangan',
                      status = '$status'
                      WHERE id = '$id'";
            
            if (mysqli_query($conn, $query)) {
                header("Location: jadwal.php");
                exit();
            } else {
                $error = 'Gagal update jadwal: ' . mysqli_error($conn);
            }
        }
    }
}

// Ambil daftar ruangan
$query = "SELECT * FROM ruangan ORDER BY nama_ruangan ASC";
$ruangan_list = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jadwal - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <h1>Edit Jadwal Ruangan</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Pilih Ruangan *</label>
                    <select name="ruangan_id" required>
                        <option value="">-- Pilih Ruangan --</option>
                        <?php while ($row = mysqli_fetch_assoc($ruangan_list)): ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo ($jadwal['ruangan_id'] == $row['id']) ? 'selected' : ''; ?>>
                                <?php echo $row['nama_ruangan']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Kelas *</label>
                    <input type="text" name="kelas" required placeholder="Contoh: X RPL 1, XI TKJ 2, XII MM 1" value="<?php echo htmlspecialchars($jadwal['kelas'] ?? ''); ?>">
                    <small class="form-text">Format: Tingkat + Jurusan + Nomor Kelas</small>
                </div>
                
                <div class="form-group">
                    <label>Sesi *</label>
                    <select name="sesi" required>
                        <option value="">-- Pilih Sesi --</option>
                        <option value="1" <?php echo (isset($jadwal['sesi']) && $jadwal['sesi'] == '1') ? 'selected' : ''; ?>>Sesi 1</option>
                        <option value="2" <?php echo (isset($jadwal['sesi']) && $jadwal['sesi'] == '2') ? 'selected' : ''; ?>>Sesi 2</option>
                    </select>
                    <small class="form-text">Sesi 1: 07:00-09:30, Sesi 2: 09:45-12:15 (contoh)</small>
                </div>
                
                <div class="form-group">
                    <label>Hari *</label>
                    <select name="hari" required>
                        <option value="">-- Pilih Hari --</option>
                        <option value="Senin" <?php echo ($jadwal['hari'] == 'Senin') ? 'selected' : ''; ?>>Senin</option>
                        <option value="Selasa" <?php echo ($jadwal['hari'] == 'Selasa') ? 'selected' : ''; ?>>Selasa</option>
                        <option value="Rabu" <?php echo ($jadwal['hari'] == 'Rabu') ? 'selected' : ''; ?>>Rabu</option>
                        <option value="Kamis" <?php echo ($jadwal['hari'] == 'Kamis') ? 'selected' : ''; ?>>Kamis</option>
                        <option value="Jumat" <?php echo ($jadwal['hari'] == 'Jumat') ? 'selected' : ''; ?>>Jumat</option>
                        <option value="Sabtu" <?php echo ($jadwal['hari'] == 'Sabtu') ? 'selected' : ''; ?>>Sabtu</option>
                        <option value="Minggu" <?php echo ($jadwal['hari'] == 'Minggu') ? 'selected' : ''; ?>>Minggu</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Waktu Mulai *</label>
                        <select name="waktu_mulai" id="waktu_mulai" required>
                            <option value="">-- Pilih Waktu --</option>
                            <?php
                            // Generate time options for school hours (07:00 - 15:30)
                            for ($h = 7; $h <= 15; $h++) {
                                for ($m = 0; $m < 60; $m += 30) {
                                    if ($h == 15 && $m > 30) break;
                                    $time = sprintf("%02d:%02d", $h, $m);
                                    $selected = ($time == $jadwal['waktu_mulai']) ? 'selected' : '';
                                    echo "<option value='$time' $selected>$time</option>";
                                }
                            }
                            ?>
                        </select>
                        <small class="form-text">Jam sekolah: 07:00 - 15:30</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Waktu Selesai *</label>
                        <select name="waktu_selesai" id="waktu_selesai" required>
                            <option value="">-- Pilih Waktu --</option>
                            <?php
                            // Generate time options for school hours (07:00 - 15:30)
                            for ($h = 7; $h <= 15; $h++) {
                                for ($m = 0; $m < 60; $m += 30) {
                                    if ($h == 15 && $m > 30) break;
                                    $time = sprintf("%02d:%02d", $h, $m);
                                    $selected = ($time == $jadwal['waktu_selesai']) ? 'selected' : '';
                                    echo "<option value='$time' $selected>$time</option>";
                                }
                            }
                            ?>
                        </select>
                        <small class="form-text">Jam sekolah: 07:00 - 15:30</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Kegiatan *</label>
                    <input type="text" name="kegiatan" value="<?php echo $jadwal['kegiatan']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Penanggung Jawab</label>
                    <input type="text" name="penanggung_jawab" value="<?php echo $jadwal['penanggung_jawab']; ?>">
                </div>
                
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan" rows="3"><?php echo $jadwal['keterangan']; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" required>
                        <option value="aktif" <?php echo ($jadwal['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="nonaktif" <?php echo ($jadwal['status'] == 'nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="jadwal.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
