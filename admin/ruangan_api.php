<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

// Admin dan petugas bisa kelola ruangan
require_admin_or_petugas();

header('Content-Type: application/json');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// GET - Retrieve room data
if ($action == 'get' && isset($_GET['id'])) {
    $id = clean_input($_GET['id']);
    $query = "SELECT * FROM ruangan WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode([
            'success' => true,
            'data' => $row
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Ruangan tidak ditemukan'
        ]);
    }
    exit();
}

// ADD - Create new room
if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_ruangan = clean_input($_POST['nama_ruangan']);
    $kapasitas = clean_input($_POST['kapasitas']);
    $fasilitas = clean_input($_POST['fasilitas']);
    $status = clean_input($_POST['status']);
    
    // Handle photo upload
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $foto = upload_foto($_FILES['foto']);
        if (!$foto) {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal upload foto. Pastikan file adalah gambar (JPG, PNG, GIF) dan ukuran maksimal 5MB.'
            ]);
            exit();
        }
    }
    
    $query = "INSERT INTO ruangan (nama_ruangan, kapasitas, fasilitas, status, foto) 
              VALUES ('$nama_ruangan', '$kapasitas', '$fasilitas', '$status', '$foto')";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode([
            'success' => true,
            'message' => 'Ruangan berhasil ditambahkan!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menambah ruangan: ' . mysqli_error($conn)
        ]);
    }
    exit();
}

// UPDATE - Edit existing room
if ($action == 'update' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = clean_input($_POST['id']);
    $nama_ruangan = clean_input($_POST['nama_ruangan']);
    $kapasitas = clean_input($_POST['kapasitas']);
    $fasilitas = clean_input($_POST['fasilitas']);
    $status = clean_input($_POST['status']);
    
    // Get current photo
    $query = "SELECT foto FROM ruangan WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $current = mysqli_fetch_assoc($result);
    $foto = $current['foto'];
    
    // Handle new photo upload
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $new_foto = upload_foto($_FILES['foto']);
        if ($new_foto) {
            // Delete old photo
            if ($foto) {
                delete_foto($foto);
            }
            $foto = $new_foto;
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal upload foto. Pastikan file adalah gambar (JPG, PNG, GIF) dan ukuran maksimal 5MB.'
            ]);
            exit();
        }
    }
    
    $query = "UPDATE ruangan SET 
              nama_ruangan = '$nama_ruangan',
              kapasitas = '$kapasitas',
              fasilitas = '$fasilitas',
              status = '$status',
              foto = '$foto'
              WHERE id = '$id'";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode([
            'success' => true,
            'message' => 'Ruangan berhasil diupdate!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal update ruangan: ' . mysqli_error($conn)
        ]);
    }
    exit();
}

// DELETE - Remove room
if ($action == 'delete' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = clean_input($_POST['id']);
    
    // Get foto untuk dihapus
    $query = "SELECT foto FROM ruangan WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $ruangan = mysqli_fetch_assoc($result);
    
    // Cek apakah ada booking yang menggunakan ruangan ini
    $query_check = "SELECT COUNT(*) as total FROM booking WHERE ruangan_id = '$id'";
    $result_check = mysqli_query($conn, $query_check);
    $check = mysqli_fetch_assoc($result_check);
    
    if ($check['total'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Tidak dapat menghapus ruangan yang masih memiliki booking!'
        ]);
        exit();
    }
    
    // Cek apakah ada jadwal yang menggunakan ruangan ini
    $query_jadwal = "SELECT COUNT(*) as total FROM jadwal_ruangan WHERE ruangan_id = '$id'";
    $result_jadwal = mysqli_query($conn, $query_jadwal);
    $check_jadwal = mysqli_fetch_assoc($result_jadwal);
    
    if ($check_jadwal['total'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Tidak dapat menghapus ruangan yang masih memiliki jadwal!'
        ]);
        exit();
    }
    
    $query = "DELETE FROM ruangan WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        // Hapus foto jika ada
        if ($ruangan['foto']) {
            delete_foto($ruangan['foto']);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Ruangan berhasil dihapus!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menghapus ruangan: ' . mysqli_error($conn)
        ]);
    }
    exit();
}

// Invalid action
echo json_encode([
    'success' => false,
    'message' => 'Invalid action'
]);
?>
