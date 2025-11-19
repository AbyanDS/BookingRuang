<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

// Admin dan petugas bisa kelola ruangan
require_admin_or_petugas();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_ruangan = clean_input($_POST['nama_ruangan']);
    $kapasitas = clean_input($_POST['kapasitas']);
    $fasilitas = clean_input($_POST['fasilitas']);
    $status = clean_input($_POST['status']);
    
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $foto = upload_foto($_FILES['foto']);
        if (!$foto) {
            $error = 'Gagal upload foto. Pastikan file adalah gambar (JPG, PNG, GIF) dan ukuran maksimal 5MB.';
        } else {
            $success = 'Foto berhasil diupload: ' . $foto;
        }
    }
    
    if (!$error) {
        $query = "INSERT INTO ruangan (nama_ruangan, kapasitas, fasilitas, status, foto) 
                  VALUES ('$nama_ruangan', '$kapasitas', '$fasilitas', '$status', '$foto')";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Ruangan berhasil ditambahkan!';
            header("Location: ruangan.php?success=" . urlencode($success));
            exit();
        } else {
            $error = 'Gagal menambah ruangan: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Ruangan - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <h1>Tambah Ruangan</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama Ruangan *</label>
                    <input type="text" name="nama_ruangan" required>
                </div>
                
                <div class="form-group">
                    <label>Kapasitas (orang) *</label>
                    <input type="number" name="kapasitas" required min="1">
                </div>
                
                <div class="form-group">
                    <label>Fasilitas *</label>
                    <textarea name="fasilitas" rows="4" required placeholder="Contoh: Proyektor, AC, Whiteboard, Wifi"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" required>
                        <option value="tersedia">Tersedia</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Foto Ruangan</label>
                    <input type="file" name="foto" accept="image/*">
                    <small>Format: JPG, PNG, GIF. Maksimal 5MB.</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="ruangan.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
