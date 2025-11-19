<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

// Admin dan petugas bisa kelola ruangan
require_admin_or_petugas();

$id = isset($_GET['id']) ? clean_input($_GET['id']) : 0;

// Ambil data ruangan
$query = "SELECT * FROM ruangan WHERE id = '$id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: ruangan.php");
    exit();
}

$ruangan = mysqli_fetch_assoc($result);
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_ruangan = clean_input($_POST['nama_ruangan']);
    $kapasitas = clean_input($_POST['kapasitas']);
    $fasilitas = clean_input($_POST['fasilitas']);
    $status = clean_input($_POST['status']);
    
    $foto = $ruangan['foto'];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $new_foto = upload_foto($_FILES['foto']);
        if ($new_foto) {
            // Hapus foto lama
            if ($ruangan['foto']) {
                delete_foto($ruangan['foto']);
            }
            $foto = $new_foto;
        } else {
            $error = 'Gagal upload foto. Pastikan file adalah gambar (JPG, PNG, GIF) dan ukuran maksimal 5MB.';
        }
    }
    
    if (!$error) {
        $query = "UPDATE ruangan SET 
                  nama_ruangan = '$nama_ruangan',
                  kapasitas = '$kapasitas',
                  fasilitas = '$fasilitas',
                  status = '$status',
                  foto = '$foto'
                  WHERE id = '$id'";
        
        if (mysqli_query($conn, $query)) {
            header("Location: ruangan.php");
            exit();
        } else {
            $error = 'Gagal update ruangan: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ruangan - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <h1>Edit Ruangan</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama Ruangan *</label>
                    <input type="text" name="nama_ruangan" value="<?php echo $ruangan['nama_ruangan']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Kapasitas (orang) *</label>
                    <input type="number" name="kapasitas" value="<?php echo $ruangan['kapasitas']; ?>" required min="1">
                </div>
                
                <div class="form-group">
                    <label>Fasilitas *</label>
                    <textarea name="fasilitas" rows="4" required><?php echo $ruangan['fasilitas']; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" required>
                        <option value="tersedia" <?php echo ($ruangan['status'] == 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                        <option value="maintenance" <?php echo ($ruangan['status'] == 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Foto Ruangan</label>
                    <?php if ($ruangan['foto']): ?>
                        <div>
                            <img src="../uploads/<?php echo $ruangan['foto']; ?>" alt="Current" style="max-width: 200px; margin-bottom: 10px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="foto" accept="image/*">
                    <small>Format: JPG, PNG, GIF. Maksimal 5MB. Kosongkan jika tidak ingin mengubah foto.</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="ruangan.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
