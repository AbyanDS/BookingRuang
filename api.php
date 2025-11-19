<?php
/**
 * REST API untuk Booking Ruangan
 * Digunakan untuk aplikasi Flutter Mobile
 * 
 * BASE URL: http://localhost/PlsworkUKK/api.php
 * 
 * ENDPOINTS:
 * 
 * AUTH:
 * - POST /api.php?endpoint=login
 * - POST /api.php?endpoint=register
 * - GET /api.php?endpoint=profile (requires auth)
 * - POST /api.php?endpoint=update_profile (requires auth)
 * 
 * RUANGAN:
 * - GET /api.php?endpoint=ruangan
 * - GET /api.php?endpoint=ruangan_detail&id={id}
 * 
 * BOOKING:
 * - POST /api.php?endpoint=create_booking (requires auth)
 * - GET /api.php?endpoint=my_bookings (requires auth)
 * - GET /api.php?endpoint=booking_detail&id={id} (requires auth)
 * - POST /api.php?endpoint=cancel_booking&id={id} (requires auth)
 * 
 * JADWAL:
 * - GET /api.php?endpoint=jadwal&hari={hari}&ruangan={ruangan_id}
 * - GET /api.php?endpoint=jadwal_daily&date={YYYY-MM-DD}&ruangan={ruangan_id}
 * - GET /api.php?endpoint=check_availability
 * 
 * HISTORY:
 * - GET /api.php?endpoint=history (requires auth)
 * 
 * LIST BOOKING (APPROVED):
 * - GET /api.php?endpoint=list_booking (requires auth)
 */

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include dependencies
require_once 'config/database.php';
require_once 'config/functions.php';

// Helper Functions
function sendResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    exit();
}

function sendError($message, $code = 400) {
    sendResponse(false, $message, null, $code);
}

function sendSuccess($message, $data = null) {
    sendResponse(true, $message, $data, 200);
}

function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

function validateRequired($data, $fields) {
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            $missing[] = $field;
        }
    }
    if (!empty($missing)) {
        sendError('Missing required fields: ' . implode(', ', $missing));
    }
}

// Simple JWT Token Functions
function generateToken($user_id, $username, $role) {
    $secret = 'booking_ruangan_secret_2025';
    $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64_encode(json_encode([
        'user_id' => $user_id,
        'username' => $username,
        'role' => $role,
        'exp' => time() + (60 * 60 * 24 * 30) // 30 days
    ]));
    $signature = base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true));
    return "$header.$payload.$signature";
}

function verifyToken($token) {
    $secret = 'booking_ruangan_secret_2025';
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;
    
    list($header, $payload, $signature) = $parts;
    $valid_sig = base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true));
    
    if ($signature !== $valid_sig) return false;
    
    $payload_data = json_decode(base64_decode($payload), true);
    if (isset($payload_data['exp']) && $payload_data['exp'] < time()) return false;
    
    return $payload_data;
}

function requireAuth() {
    $headers = getallheaders();
    $token = null;
    
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    } elseif (isset($headers['authorization'])) {
        $token = str_replace('Bearer ', '', $headers['authorization']);
    }
    
    if (!$token) sendError('Unauthorized - No token provided', 401);
    
    $payload = verifyToken($token);
    if (!$payload) sendError('Unauthorized - Invalid token', 401);
    
    return $payload;
}

// Get endpoint
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';
$method = $_SERVER['REQUEST_METHOD'];

// Route Handler
switch ($endpoint) {
    
    // ============ AUTH ENDPOINTS ============
    
    case 'login':
        if ($method !== 'POST') sendError('Method not allowed', 405);
        
        $data = getJsonInput();
        validateRequired($data, ['username', 'password']);
        
        $username = mysqli_real_escape_string($conn, $data['username']);
        $password = $data['password'];
        
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) === 0) {
            sendError('Username atau password salah', 401);
        }
        
        $user = mysqli_fetch_assoc($result);
        
        if (!password_verify($password, $user['password'])) {
            sendError('Username atau password salah', 401);
        }
        
        $token = generateToken($user['id'], $user['username'], $user['role']);
        
        sendSuccess('Login berhasil', [
            'token' => $token,
            'user' => [
                'id' => (int)$user['id'],
                'username' => $user['username'],
                'nama_lengkap' => $user['nama_lengkap'],
                'email' => $user['email'],
                'no_hp' => $user['no_hp'],
                'role' => $user['role']
            ]
        ]);
        break;
    
    case 'register':
        if ($method !== 'POST') sendError('Method not allowed', 405);
        
        $data = getJsonInput();
        validateRequired($data, ['username', 'password', 'nama_lengkap', 'email']);
        
        $username = mysqli_real_escape_string($conn, $data['username']);
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $nama_lengkap = mysqli_real_escape_string($conn, $data['nama_lengkap']);
        $email = mysqli_real_escape_string($conn, $data['email']);
        $no_hp = isset($data['no_hp']) ? mysqli_real_escape_string($conn, $data['no_hp']) : '';
        
        // Check username
        $check = "SELECT id FROM users WHERE username = '$username'";
        if (mysqli_num_rows(mysqli_query($conn, $check)) > 0) {
            sendError('Username sudah digunakan');
        }
        
        // Check email
        $check = "SELECT id FROM users WHERE email = '$email'";
        if (mysqli_num_rows(mysqli_query($conn, $check)) > 0) {
            sendError('Email sudah digunakan');
        }
        
        $query = "INSERT INTO users (username, password, nama_lengkap, email, no_hp, role) 
                  VALUES ('$username', '$password', '$nama_lengkap', '$email', '$no_hp', 'user')";
        
        if (mysqli_query($conn, $query)) {
            $user_id = mysqli_insert_id($conn);
            $token = generateToken($user_id, $username, 'user');
            
            sendSuccess('Registrasi berhasil', [
                'token' => $token,
                'user' => [
                    'id' => $user_id,
                    'username' => $username,
                    'nama_lengkap' => $nama_lengkap,
                    'email' => $email,
                    'no_hp' => $no_hp,
                    'role' => 'user'
                ]
            ]);
        } else {
            sendError('Registrasi gagal: ' . mysqli_error($conn), 500);
        }
        break;
    
    case 'profile':
        if ($method !== 'GET') sendError('Method not allowed', 405);
        
        $user = requireAuth();
        
        $query = "SELECT id, username, nama_lengkap, email, no_hp, role, created_at 
                  FROM users WHERE id = " . $user['user_id'];
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) === 0) sendError('User not found', 404);
        
        $userData = mysqli_fetch_assoc($result);
        $userData['id'] = (int)$userData['id'];
        
        sendSuccess('Profile data retrieved', $userData);
        break;
    
    case 'update_profile':
        if ($method !== 'POST') sendError('Method not allowed', 405);
        
        $user = requireAuth();
        $data = getJsonInput();
        
        $updates = [];
        if (isset($data['nama_lengkap'])) {
            $nama = mysqli_real_escape_string($conn, $data['nama_lengkap']);
            $updates[] = "nama_lengkap = '$nama'";
        }
        if (isset($data['email'])) {
            $email = mysqli_real_escape_string($conn, $data['email']);
            $updates[] = "email = '$email'";
        }
        if (isset($data['no_hp'])) {
            $no_hp = mysqli_real_escape_string($conn, $data['no_hp']);
            $updates[] = "no_hp = '$no_hp'";
        }
        if (isset($data['password']) && !empty($data['password'])) {
            $password = password_hash($data['password'], PASSWORD_DEFAULT);
            $updates[] = "password = '$password'";
        }
        
        if (empty($updates)) sendError('No data to update');
        
        $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = " . $user['user_id'];
        
        if (mysqli_query($conn, $query)) {
            sendSuccess('Profile updated successfully');
        } else {
            sendError('Update failed: ' . mysqli_error($conn), 500);
        }
        break;
    
    // ============ RUANGAN ENDPOINTS ============
    
    case 'ruangan':
        if ($method !== 'GET') sendError('Method not allowed', 405);
        
        $status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : 'tersedia';
        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
        
        $query = "SELECT * FROM ruangan WHERE status = '$status'";
        
        if ($search) {
            $query .= " AND (nama_ruangan LIKE '%$search%' OR fasilitas LIKE '%$search%')";
        }
        
        $query .= " ORDER BY nama_ruangan ASC";
        
        $result = mysqli_query($conn, $query);
        $ruangan = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $row['id'] = (int)$row['id'];
            $row['kapasitas'] = (int)$row['kapasitas'];
            $row['foto'] = $row['foto'] ? 'http://' . $_SERVER['HTTP_HOST'] . '/PlsworkUKK/uploads/' . $row['foto'] : null;
            $ruangan[] = $row;
        }
        
        sendSuccess('Ruangan retrieved successfully', $ruangan);
        break;
    
    case 'ruangan_detail':
        if ($method !== 'GET') sendError('Method not allowed', 405);
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) sendError('ID ruangan required');
        
        $query = "SELECT * FROM ruangan WHERE id = $id";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) === 0) sendError('Ruangan not found', 404);
        
        $ruangan = mysqli_fetch_assoc($result);
        $ruangan['id'] = (int)$ruangan['id'];
        $ruangan['kapasitas'] = (int)$ruangan['kapasitas'];
        $ruangan['foto'] = $ruangan['foto'] ? 'http://' . $_SERVER['HTTP_HOST'] . '/PlsworkUKK/uploads/' . $ruangan['foto'] : null;
        
        sendSuccess('Ruangan detail retrieved', $ruangan);
        break;
    
    // ============ BOOKING ENDPOINTS ============
    
    case 'create_booking':
        if ($method !== 'POST') sendError('Method not allowed', 405);
        
        $user = requireAuth();
        if ($user['role'] !== 'user') sendError('Only users can create bookings', 403);
        
        $data = getJsonInput();
        validateRequired($data, ['ruangan_id', 'tanggal_booking', 'waktu_mulai', 'waktu_selesai', 'keperluan']);
        
        $user_id = $user['user_id'];
        $ruangan_id = (int)$data['ruangan_id'];
        $tanggal_booking = mysqli_real_escape_string($conn, $data['tanggal_booking']);
        $waktu_mulai = mysqli_real_escape_string($conn, $data['waktu_mulai']);
        $waktu_selesai = mysqli_real_escape_string($conn, $data['waktu_selesai']);
        $keperluan = mysqli_real_escape_string($conn, $data['keperluan']);
        $keterangan = isset($data['keterangan']) ? mysqli_real_escape_string($conn, $data['keterangan']) : '';
        
        // Validasi tanggal
        if (strtotime($tanggal_booking) < strtotime(date('Y-m-d'))) {
            sendError('Tanggal booking tidak boleh di masa lalu');
        }
        
        // Validasi waktu
        if ($waktu_selesai <= $waktu_mulai) {
            sendError('Waktu selesai harus lebih besar dari waktu mulai');
        }
        
        // Cek ruangan exists
        $check_room = "SELECT id FROM ruangan WHERE id = $ruangan_id AND status = 'tersedia'";
        if (mysqli_num_rows(mysqli_query($conn, $check_room)) === 0) {
            sendError('Ruangan tidak valid atau tidak tersedia');
        }
        
        // Cek jadwal tetap bentrok
        $day_of_week = date('N', strtotime($tanggal_booking));
        $day_names = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $hari = $day_names[$day_of_week];
        
        $check_jadwal = "SELECT * FROM jadwal_ruangan 
                         WHERE ruangan_id = $ruangan_id 
                         AND hari = '$hari'
                         AND status = 'aktif'
                         AND (waktu_mulai < '$waktu_selesai' AND waktu_selesai > '$waktu_mulai')";
        
        if (mysqli_num_rows(mysqli_query($conn, $check_jadwal)) > 0) {
            sendError('Waktu bentrok dengan jadwal tetap ruangan');
        }
        
        // Cek booking lain bentrok
        $check_booking = "SELECT * FROM booking 
                          WHERE ruangan_id = $ruangan_id 
                          AND tanggal_booking = '$tanggal_booking'
                          AND status IN ('pending', 'approved')
                          AND (waktu_mulai < '$waktu_selesai' AND waktu_selesai > '$waktu_mulai')";
        
        if (mysqli_num_rows(mysqli_query($conn, $check_booking)) > 0) {
            sendError('Ruangan sudah dibooking pada waktu tersebut');
        }
        
        // Insert booking
        $query = "INSERT INTO booking (user_id, ruangan_id, tanggal_booking, waktu_mulai, waktu_selesai, keperluan, keterangan, status) 
                  VALUES ($user_id, $ruangan_id, '$tanggal_booking', '$waktu_mulai', '$waktu_selesai', '$keperluan', '$keterangan', 'pending')";
        
        if (mysqli_query($conn, $query)) {
            $booking_id = mysqli_insert_id($conn);
            
            // Log history
            $history = "INSERT INTO history (booking_id, user_id, ruangan_id, action, old_status, new_status, keterangan)
                        VALUES ($booking_id, $user_id, $ruangan_id, 'Booking Dibuat', NULL, 'pending', 'Booking baru dibuat oleh user')";
            mysqli_query($conn, $history);
            
            sendSuccess('Booking berhasil dibuat', ['booking_id' => $booking_id]);
        } else {
            sendError('Booking gagal: ' . mysqli_error($conn), 500);
        }
        break;
    
    case 'my_bookings':
        if ($method !== 'GET') sendError('Method not allowed', 405);
        
        $user = requireAuth();
        $status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
        
        $query = "SELECT b.*, r.nama_ruangan, r.kapasitas, r.foto
                  FROM booking b
                  JOIN ruangan r ON b.ruangan_id = r.id
                  WHERE b.user_id = " . $user['user_id'];
        
        if ($status) {
            $query .= " AND b.status = '$status'";
        }
        
        $query .= " ORDER BY b.tanggal_booking DESC, b.waktu_mulai DESC";
        
        $result = mysqli_query($conn, $query);
        $bookings = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $row['id'] = (int)$row['id'];
            $row['user_id'] = (int)$row['user_id'];
            $row['ruangan_id'] = (int)$row['ruangan_id'];
            $row['kapasitas'] = (int)$row['kapasitas'];
            $row['foto'] = $row['foto'] ? 'http://' . $_SERVER['HTTP_HOST'] . '/PlsworkUKK/uploads/' . $row['foto'] : null;
            $bookings[] = $row;
        }
        
        sendSuccess('Bookings retrieved successfully', $bookings);
        break;
    
    case 'booking_detail':
        if ($method !== 'GET') sendError('Method not allowed', 405);
        
        $user = requireAuth();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) sendError('ID booking required');
        
        $query = "SELECT b.*, r.nama_ruangan, r.kapasitas, r.fasilitas, r.foto, u.nama_lengkap, u.email, u.no_hp
                  FROM booking b
                  JOIN ruangan r ON b.ruangan_id = r.id
                  JOIN users u ON b.user_id = u.id
                  WHERE b.id = $id";
        
        if ($user['role'] === 'user') {
            $query .= " AND b.user_id = " . $user['user_id'];
        }
        
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) === 0) sendError('Booking not found', 404);
        
        $booking = mysqli_fetch_assoc($result);
        $booking['id'] = (int)$booking['id'];
        $booking['user_id'] = (int)$booking['user_id'];
        $booking['ruangan_id'] = (int)$booking['ruangan_id'];
        $booking['kapasitas'] = (int)$booking['kapasitas'];
        $booking['foto'] = $booking['foto'] ? 'http://' . $_SERVER['HTTP_HOST'] . '/PlsworkUKK/uploads/' . $booking['foto'] : null;
        
        sendSuccess('Booking detail retrieved', $booking);
        break;
    
    case 'cancel_booking':
        if ($method !== 'POST') sendError('Method not allowed', 405);
        
        $user = requireAuth();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) sendError('ID booking required');
        
        // Check ownership
        $check = "SELECT * FROM booking WHERE id = $id AND user_id = " . $user['user_id'];
        $result = mysqli_query($conn, $check);
        
        if (mysqli_num_rows($result) === 0) sendError('Booking not found', 404);
        
        $booking = mysqli_fetch_assoc($result);
        
        if ($booking['status'] !== 'pending') {
            sendError('Hanya booking dengan status pending yang bisa dibatalkan');
        }
        
        $query = "UPDATE booking SET status = 'cancelled', updated_at = NOW() WHERE id = $id";
        
        if (mysqli_query($conn, $query)) {
            // Log history
            $history = "INSERT INTO history (booking_id, user_id, ruangan_id, action, old_status, new_status, keterangan)
                        VALUES ($id, {$user['user_id']}, {$booking['ruangan_id']}, 'Booking Dibatalkan', 'pending', 'cancelled', 'Dibatalkan oleh user')";
            mysqli_query($conn, $history);
            
            sendSuccess('Booking berhasil dibatalkan');
        } else {
            sendError('Cancel failed: ' . mysqli_error($conn), 500);
        }
        break;
    
    // ============ JADWAL ENDPOINTS ============
    
    case 'jadwal':
        if ($method !== 'GET') sendError('Method not allowed', 405);
        
        $hari = isset($_GET['hari']) ? mysqli_real_escape_string($conn, $_GET['hari']) : '';
        $ruangan = isset($_GET['ruangan']) ? (int)$_GET['ruangan'] : 0;
        
        $hari_list = $hari ? [$hari] : ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $today = date('Y-m-d');
        
        $result_data = [];
        
        foreach ($hari_list as $h) {
            // Jadwal tetap
            $query = "SELECT j.*, r.nama_ruangan, 'jadwal' as source_type
                      FROM jadwal_ruangan j
                      JOIN ruangan r ON j.ruangan_id = r.id
                      WHERE j.hari = '$h' AND j.status = 'aktif'";
            
            if ($ruangan) $query .= " AND j.ruangan_id = $ruangan";
            
            // Booking approved
            $hari_numeric = ['Minggu' => 1, 'Senin' => 2, 'Selasa' => 3, 'Rabu' => 4, 'Kamis' => 5, 'Jumat' => 6, 'Sabtu' => 7];
            $day_num = $hari_numeric[$h];
            
            $query2 = "SELECT b.waktu_mulai, b.waktu_selesai, b.keperluan as kegiatan, b.keterangan,
                              r.nama_ruangan, u.nama_lengkap as penanggung_jawab,
                              b.tanggal_booking, 'booking' as source_type
                       FROM booking b
                       JOIN ruangan r ON b.ruangan_id = r.id
                       JOIN users u ON b.user_id = u.id
                       WHERE b.status = 'approved'
                       AND b.tanggal_booking >= '$today'
                       AND DAYOFWEEK(b.tanggal_booking) = $day_num";
            
            if ($ruangan) $query2 .= " AND b.ruangan_id = $ruangan";
            
            $combined = "($query) UNION ALL ($query2) ORDER BY waktu_mulai";
            
            $result = mysqli_query($conn, $combined);
            $jadwal = [];
            
            while ($row = mysqli_fetch_assoc($result)) {
                if (isset($row['id'])) $row['id'] = (int)$row['id'];
                if (isset($row['ruangan_id'])) $row['ruangan_id'] = (int)$row['ruangan_id'];
                $jadwal[] = $row;
            }
            
            $result_data[] = [
                'hari' => $h,
                'count' => count($jadwal),
                'jadwal' => $jadwal
            ];
        }
        
        sendSuccess('Jadwal retrieved successfully', $result_data);
        break;
    
    case 'jadwal_daily':
        if ($method !== 'GET') sendError('Method not allowed', 405);
        
        $date = isset($_GET['date']) ? mysqli_real_escape_string($conn, $_GET['date']) : date('Y-m-d');
        $ruangan = isset($_GET['ruangan']) ? (int)$_GET['ruangan'] : 0;
        
        // Get day name
        $day_of_week = date('N', strtotime($date));
        $day_names = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $hari = $day_names[$day_of_week];
        
        // Jadwal tetap untuk hari tersebut
        $query1 = "SELECT j.*, r.nama_ruangan, r.foto, 'jadwal' as source_type
                   FROM jadwal_ruangan j
                   JOIN ruangan r ON j.ruangan_id = r.id
                   WHERE j.hari = '$hari' AND j.status = 'aktif'";
        
        if ($ruangan) $query1 .= " AND j.ruangan_id = $ruangan";
        
        // Booking untuk tanggal tersebut
        $query2 = "SELECT b.*, r.nama_ruangan, r.foto, u.nama_lengkap as penanggung_jawab,
                          'booking' as source_type
                   FROM booking b
                   JOIN ruangan r ON b.ruangan_id = r.id
                   JOIN users u ON b.user_id = u.id
                   WHERE b.tanggal_booking = '$date'
                   AND b.status = 'approved'";
        
        if ($ruangan) $query2 .= " AND b.ruangan_id = $ruangan";
        
        $combined = "($query1) UNION ALL ($query2) ORDER BY waktu_mulai";
        
        $result = mysqli_query($conn, $combined);
        $jadwal = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            if (isset($row['id'])) $row['id'] = (int)$row['id'];
            if (isset($row['ruangan_id'])) $row['ruangan_id'] = (int)$row['ruangan_id'];
            $row['foto'] = isset($row['foto']) && $row['foto'] ? 'http://' . $_SERVER['HTTP_HOST'] . '/PlsworkUKK/uploads/' . $row['foto'] : null;
            $jadwal[] = $row;
        }
        
        sendSuccess('Daily jadwal retrieved', [
            'date' => $date,
            'hari' => $hari,
            'count' => count($jadwal),
            'jadwal' => $jadwal
        ]);
        break;
    
    case 'check_availability':
        if ($method !== 'GET') sendError('Method not allowed', 405);
        
        $ruangan_id = isset($_GET['ruangan_id']) ? (int)$_GET['ruangan_id'] : 0;
        $tanggal = isset($_GET['tanggal']) ? mysqli_real_escape_string($conn, $_GET['tanggal']) : '';
        $waktu_mulai = isset($_GET['waktu_mulai']) ? mysqli_real_escape_string($conn, $_GET['waktu_mulai']) : '';
        $waktu_selesai = isset($_GET['waktu_selesai']) ? mysqli_real_escape_string($conn, $_GET['waktu_selesai']) : '';
        
        if (!$ruangan_id || !$tanggal || !$waktu_mulai || !$waktu_selesai) {
            sendError('Missing required parameters');
        }
        
        $available = true;
        $conflicts = [];
        
        // Check jadwal tetap
        $day_of_week = date('N', strtotime($tanggal));
        $day_names = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $hari = $day_names[$day_of_week];
        
        $check_jadwal = "SELECT * FROM jadwal_ruangan 
                         WHERE ruangan_id = $ruangan_id 
                         AND hari = '$hari'
                         AND status = 'aktif'
                         AND (waktu_mulai < '$waktu_selesai' AND waktu_selesai > '$waktu_mulai')";
        
        $result = mysqli_query($conn, $check_jadwal);
        while ($row = mysqli_fetch_assoc($result)) {
            $available = false;
            $conflicts[] = [
                'type' => 'jadwal',
                'kegiatan' => $row['kegiatan'],
                'waktu_mulai' => $row['waktu_mulai'],
                'waktu_selesai' => $row['waktu_selesai']
            ];
        }
        
        // Check booking
        $check_booking = "SELECT b.*, u.nama_lengkap FROM booking b
                          JOIN users u ON b.user_id = u.id
                          WHERE b.ruangan_id = $ruangan_id 
                          AND b.tanggal_booking = '$tanggal'
                          AND b.status IN ('pending', 'approved')
                          AND (b.waktu_mulai < '$waktu_selesai' AND b.waktu_selesai > '$waktu_mulai')";
        
        $result = mysqli_query($conn, $check_booking);
        while ($row = mysqli_fetch_assoc($result)) {
            $available = false;
            $conflicts[] = [
                'type' => 'booking',
                'kegiatan' => $row['keperluan'],
                'waktu_mulai' => $row['waktu_mulai'],
                'waktu_selesai' => $row['waktu_selesai'],
                'user' => $row['nama_lengkap']
            ];
        }
        
        sendSuccess('Availability checked', [
            'available' => $available,
            'conflicts' => $conflicts
        ]);
        break;
    
    // ============ HISTORY ENDPOINT ============
    
    case 'history':
        if ($method !== 'GET') sendError('Method not allowed', 405);
        
        $user = requireAuth();
        
        $query = "SELECT h.*, r.nama_ruangan, u.nama_lengkap
                  FROM history h
                  LEFT JOIN ruangan r ON h.ruangan_id = r.id
                  LEFT JOIN users u ON h.user_id = u.id
                  WHERE h.user_id = " . $user['user_id'] . "
                  ORDER BY h.created_at DESC
                  LIMIT 50";
        
        $result = mysqli_query($conn, $query);
        $history = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $row['id'] = (int)$row['id'];
            $row['booking_id'] = (int)$row['booking_id'];
            $row['user_id'] = (int)$row['user_id'];
            if ($row['ruangan_id']) $row['ruangan_id'] = (int)$row['ruangan_id'];
            $history[] = $row;
        }
        
        sendSuccess('History retrieved successfully', $history);
        break;
    
    // ============ LIST BOOKING (APPROVED) ============
    
    case 'list_booking':
        if ($method !== 'GET') sendError('Method not allowed', 405);
        
        $user = requireAuth();
        
        $query = "SELECT b.*, r.nama_ruangan, r.kapasitas, r.foto
                  FROM booking b
                  JOIN ruangan r ON b.ruangan_id = r.id
                  WHERE b.user_id = " . $user['user_id'] . "
                  AND b.status = 'approved'
                  ORDER BY b.tanggal_booking ASC, b.waktu_mulai ASC";
        
        $result = mysqli_query($conn, $query);
        $bookings = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $row['id'] = (int)$row['id'];
            $row['user_id'] = (int)$row['user_id'];
            $row['ruangan_id'] = (int)$row['ruangan_id'];
            $row['kapasitas'] = (int)$row['kapasitas'];
            $row['foto'] = $row['foto'] ? 'http://' . $_SERVER['HTTP_HOST'] . '/PlsworkUKK/uploads/' . $row['foto'] : null;
            
            // Calculate countdown
            $booking_datetime = strtotime($row['tanggal_booking'] . ' ' . $row['waktu_mulai']);
            $now = time();
            $diff = $booking_datetime - $now;
            
            if ($diff > 0) {
                $days = floor($diff / 86400);
                $hours = floor(($diff % 86400) / 3600);
                $row['countdown'] = $days > 0 ? "$days hari lagi" : "$hours jam lagi";
                $row['status_text'] = 'upcoming';
            } else {
                $row['countdown'] = 'Sedang Berlangsung';
                $row['status_text'] = 'ongoing';
            }
            
            $bookings[] = $row;
        }
        
        sendSuccess('Approved bookings retrieved', [
            'total' => count($bookings),
            'bookings' => $bookings
        ]);
        break;
    
    // ============ DEFAULT ============
    
    default:
        sendError('Invalid endpoint. Please check API documentation.', 404);
}
?>
