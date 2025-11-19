<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

require_admin_or_petugas();

$error = '';
$success = '';

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
        // Cek jadwal bentrok untuk ruangan, kelas, dan sesi yang sama
        $query_check = "SELECT * FROM jadwal_ruangan 
                        WHERE ruangan_id = '$ruangan_id' 
                        AND kelas = '$kelas'
                        AND sesi = '$sesi'
                        AND hari = '$hari'
                        AND status = 'aktif'
                        AND (
                            (waktu_mulai < '$waktu_selesai' AND waktu_selesai > '$waktu_mulai')
                        )";
        $result_check = mysqli_query($conn, $query_check);
        
        if (mysqli_num_rows($result_check) > 0) {
            $error = 'Jadwal bentrok dengan jadwal yang sudah ada untuk kelas dan sesi ini!';
        } else {
            $query = "INSERT INTO jadwal_ruangan (ruangan_id, kelas, sesi, hari, waktu_mulai, waktu_selesai, kegiatan, penanggung_jawab, keterangan, status) 
                      VALUES ('$ruangan_id', '$kelas', '$sesi', '$hari', '$waktu_mulai', '$waktu_selesai', '$kegiatan', '$penanggung_jawab', '$keterangan', '$status')";
            
            if (mysqli_query($conn, $query)) {
                header("Location: jadwal.php");
                exit();
            } else {
                $error = 'Gagal menambah jadwal: ' . mysqli_error($conn);
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
    <title>Tambah Jadwal - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <h1>Tambah Jadwal Ruangan</h1>
        
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
                            <option value="<?php echo $row['id']; ?>">
                                <?php echo $row['nama_ruangan']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Kelas *</label>
                    <input type="text" name="kelas" required placeholder="Contoh: X RPL 1, XI TKJ 2, XII MM 1">
                    <small class="form-text">Format: Tingkat + Jurusan + Nomor Kelas</small>
                </div>
                
                <div class="form-group">
                    <label>Sesi *</label>
                    <select name="sesi" required>
                        <option value="">-- Pilih Sesi --</option>
                        <option value="1">Sesi 1</option>
                        <option value="2">Sesi 2</option>
                    </select>
                    <small class="form-text">Sesi 1: 07:00-09:30, Sesi 2: 09:45-12:15 (contoh)</small>
                </div>
                
                <div class="form-group">
                    <label>Hari *</label>
                    <select name="hari" required>
                        <option value="">-- Pilih Hari --</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                        <option value="Minggu">Minggu</option>
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
                                    echo "<option value='$time'>$time</option>";
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
                                    echo "<option value='$time'>$time</option>";
                                }
                            }
                            ?>
                        </select>
                        <small class="form-text">Jam sekolah: 07:00 - 15:30</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Kegiatan *</label>
                    <input type="text" name="kegiatan" required placeholder="Contoh: Rapat Mingguan">
                </div>
                
                <div class="form-group">
                    <label>Penanggung Jawab</label>
                    <input type="text" name="penanggung_jawab" placeholder="Nama penanggung jawab">
                </div>
                
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan" rows="3" placeholder="Keterangan tambahan (opsional)"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" required>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="jadwal.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
