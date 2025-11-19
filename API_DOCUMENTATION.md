# REST API Documentation - Booking Ruangan

## Base URL
```
http://localhost/PlsworkUKK/api.php
```

## Authentication
Untuk endpoint yang memerlukan autentikasi, tambahkan header:
```
Authorization: Bearer {token}
```

---

## 📋 ENDPOINTS

### 🔐 Authentication

#### 1. Login
**Endpoint:** `POST /api.php?endpoint=login`

**Request Body:**
```json
{
  "username": "john_doe",
  "password": "password123"
}
```

**Response Success:**
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "token": "eyJhbGc...",
    "user": {
      "id": 1,
      "username": "john_doe",
      "nama_lengkap": "John Doe",
      "email": "john@example.com",
      "no_hp": "081234567890",
      "role": "user"
    }
  },
  "timestamp": "2025-11-13 10:30:00"
}
```

---

#### 2. Register
**Endpoint:** `POST /api.php?endpoint=register`

**Request Body:**
```json
{
  "username": "jane_doe",
  "password": "password123",
  "nama_lengkap": "Jane Doe",
  "email": "jane@example.com",
  "no_hp": "081234567890"
}
```

**Response Success:**
```json
{
  "success": true,
  "message": "Registrasi berhasil",
  "data": {
    "token": "eyJhbGc...",
    "user": {
      "id": 2,
      "username": "jane_doe",
      "nama_lengkap": "Jane Doe",
      "email": "jane@example.com",
      "no_hp": "081234567890",
      "role": "user"
    }
  }
}
```

---

#### 3. Get Profile
**Endpoint:** `GET /api.php?endpoint=profile`

**Headers:** `Authorization: Bearer {token}`

**Response Success:**
```json
{
  "success": true,
  "message": "Profile data retrieved",
  "data": {
    "id": 1,
    "username": "john_doe",
    "nama_lengkap": "John Doe",
    "email": "john@example.com",
    "no_hp": "081234567890",
    "role": "user",
    "created_at": "2025-01-01 10:00:00"
  }
}
```

---

#### 4. Update Profile
**Endpoint:** `POST /api.php?endpoint=update_profile`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "nama_lengkap": "John Doe Updated",
  "email": "john.new@example.com",
  "no_hp": "089999999999",
  "password": "newpassword123"
}
```

**Response Success:**
```json
{
  "success": true,
  "message": "Profile updated successfully"
}
```

---

### 🏢 Ruangan

#### 5. Get All Ruangan
**Endpoint:** `GET /api.php?endpoint=ruangan`

**Query Parameters:**
- `status` (optional): tersedia | tidak_tersedia
- `search` (optional): search by nama, lokasi, atau fasilitas

**Example:**
```
GET /api.php?endpoint=ruangan&status=tersedia&search=lab
```

**Response Success:**
```json
{
  "success": true,
  "message": "Ruangan retrieved successfully",
  "data": [
    {
      "id": 1,
      "nama_ruangan": "Lab Komputer 1",
      "kapasitas": 40,
      "lokasi": "Lantai 2, Gedung A",
      "fasilitas": "AC, Proyektor, Whiteboard, 40 Komputer",
      "status": "tersedia",
      "foto": "http://localhost/PlsworkUKK/uploads/lab1.jpg",
      "created_at": "2025-01-01 10:00:00"
    }
  ]
}
```

---

#### 6. Get Ruangan Detail
**Endpoint:** `GET /api.php?endpoint=ruangan_detail&id={id}`

**Example:**
```
GET /api.php?endpoint=ruangan_detail&id=1
```

**Response Success:**
```json
{
  "success": true,
  "message": "Ruangan detail retrieved",
  "data": {
    "id": 1,
    "nama_ruangan": "Lab Komputer 1",
    "kapasitas": 40,
    "lokasi": "Lantai 2, Gedung A",
    "fasilitas": "AC, Proyektor, Whiteboard, 40 Komputer",
    "status": "tersedia",
    "foto": "http://localhost/PlsworkUKK/uploads/lab1.jpg",
    "created_at": "2025-01-01 10:00:00"
  }
}
```

---

### 📅 Booking

#### 7. Create Booking
**Endpoint:** `POST /api.php?endpoint=create_booking`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "ruangan_id": 1,
  "tanggal_booking": "2025-11-15",
  "waktu_mulai": "08:00:00",
  "waktu_selesai": "10:00:00",
  "keperluan": "Rapat koordinasi tim",
  "keterangan": "Butuh proyektor"
}
```

**Response Success:**
```json
{
  "success": true,
  "message": "Booking berhasil dibuat",
  "data": {
    "booking_id": 10
  }
}
```

**Response Error:**
```json
{
  "success": false,
  "message": "Waktu bentrok dengan jadwal tetap ruangan",
  "data": null
}
```

---

#### 8. Get My Bookings
**Endpoint:** `GET /api.php?endpoint=my_bookings`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `status` (optional): pending | approved | rejected | completed | cancelled

**Example:**
```
GET /api.php?endpoint=my_bookings&status=pending
```

**Response Success:**
```json
{
  "success": true,
  "message": "Bookings retrieved successfully",
  "data": [
    {
      "id": 10,
      "user_id": 1,
      "ruangan_id": 1,
      "tanggal_booking": "2025-11-15",
      "waktu_mulai": "08:00:00",
      "waktu_selesai": "10:00:00",
      "keperluan": "Rapat koordinasi tim",
      "keterangan": "Butuh proyektor",
      "status": "pending",
      "nama_ruangan": "Lab Komputer 1",
      "lokasi": "Lantai 2, Gedung A",
      "kapasitas": 40,
      "foto": "http://localhost/PlsworkUKK/uploads/lab1.jpg",
      "created_at": "2025-11-13 10:30:00"
    }
  ]
}
```

---

#### 9. Get Booking Detail
**Endpoint:** `GET /api.php?endpoint=booking_detail&id={id}`

**Headers:** `Authorization: Bearer {token}`

**Example:**
```
GET /api.php?endpoint=booking_detail&id=10
```

**Response Success:**
```json
{
  "success": true,
  "message": "Booking detail retrieved",
  "data": {
    "id": 10,
    "user_id": 1,
    "ruangan_id": 1,
    "tanggal_booking": "2025-11-15",
    "waktu_mulai": "08:00:00",
    "waktu_selesai": "10:00:00",
    "keperluan": "Rapat koordinasi tim",
    "keterangan": "Butuh proyektor",
    "status": "pending",
    "nama_ruangan": "Lab Komputer 1",
    "lokasi": "Lantai 2, Gedung A",
    "kapasitas": 40,
    "fasilitas": "AC, Proyektor, Whiteboard, 40 Komputer",
    "foto": "http://localhost/PlsworkUKK/uploads/lab1.jpg",
    "nama_lengkap": "John Doe",
    "email": "john@example.com",
    "no_hp": "081234567890"
  }
}
```

---

#### 10. Cancel Booking
**Endpoint:** `POST /api.php?endpoint=cancel_booking&id={id}`

**Headers:** `Authorization: Bearer {token}`

**Example:**
```
POST /api.php?endpoint=cancel_booking&id=10
```

**Response Success:**
```json
{
  "success": true,
  "message": "Booking berhasil dibatalkan"
}
```

---

#### 11. Get List Booking (Approved Only)
**Endpoint:** `GET /api.php?endpoint=list_booking`

**Headers:** `Authorization: Bearer {token}`

**Response Success:**
```json
{
  "success": true,
  "message": "Approved bookings retrieved",
  "data": {
    "total": 5,
    "bookings": [
      {
        "id": 10,
        "user_id": 1,
        "ruangan_id": 1,
        "tanggal_booking": "2025-11-15",
        "waktu_mulai": "08:00:00",
        "waktu_selesai": "10:00:00",
        "keperluan": "Rapat koordinasi tim",
        "status": "approved",
        "nama_ruangan": "Lab Komputer 1",
        "lokasi": "Lantai 2, Gedung A",
        "foto": "http://localhost/PlsworkUKK/uploads/lab1.jpg",
        "countdown": "2 hari lagi",
        "status_text": "upcoming"
      }
    ]
  }
}
```

---

### 📆 Jadwal

#### 12. Get Jadwal (Weekly)
**Endpoint:** `GET /api.php?endpoint=jadwal`

**Query Parameters:**
- `hari` (optional): Senin | Selasa | Rabu | Kamis | Jumat | Sabtu | Minggu
- `ruangan` (optional): ruangan_id

**Example:**
```
GET /api.php?endpoint=jadwal&hari=Senin&ruangan=1
```

**Response Success:**
```json
{
  "success": true,
  "message": "Jadwal retrieved successfully",
  "data": [
    {
      "hari": "Senin",
      "count": 3,
      "jadwal": [
        {
          "id": 1,
          "ruangan_id": 1,
          "waktu_mulai": "08:00:00",
          "waktu_selesai": "10:00:00",
          "kegiatan": "Kelas Pemrograman Web",
          "keterangan": "Kelas reguler",
          "penanggung_jawab": "Pak Budi",
          "nama_ruangan": "Lab Komputer 1",
          "lokasi": "Lantai 2, Gedung A",
          "source_type": "jadwal"
        },
        {
          "waktu_mulai": "13:00:00",
          "waktu_selesai": "15:00:00",
          "kegiatan": "Rapat koordinasi",
          "nama_ruangan": "Lab Komputer 1",
          "lokasi": "Lantai 2, Gedung A",
          "penanggung_jawab": "John Doe",
          "tanggal_booking": "2025-11-18",
          "source_type": "booking"
        }
      ]
    }
  ]
}
```

---

#### 13. Get Jadwal (Daily)
**Endpoint:** `GET /api.php?endpoint=jadwal_daily`

**Query Parameters:**
- `date` (optional): YYYY-MM-DD (default: today)
- `ruangan` (optional): ruangan_id

**Example:**
```
GET /api.php?endpoint=jadwal_daily&date=2025-11-15&ruangan=1
```

**Response Success:**
```json
{
  "success": true,
  "message": "Daily jadwal retrieved",
  "data": {
    "date": "2025-11-15",
    "hari": "Jumat",
    "count": 2,
    "jadwal": [
      {
        "id": 5,
        "ruangan_id": 1,
        "waktu_mulai": "08:00:00",
        "waktu_selesai": "10:00:00",
        "kegiatan": "Kelas Database",
        "nama_ruangan": "Lab Komputer 1",
        "lokasi": "Lantai 2, Gedung A",
        "foto": "http://localhost/PlsworkUKK/uploads/lab1.jpg",
        "source_type": "jadwal"
      }
    ]
  }
}
```

---

#### 14. Check Availability
**Endpoint:** `GET /api.php?endpoint=check_availability`

**Query Parameters:**
- `ruangan_id`: required
- `tanggal`: required (YYYY-MM-DD)
- `waktu_mulai`: required (HH:mm:ss)
- `waktu_selesai`: required (HH:mm:ss)

**Example:**
```
GET /api.php?endpoint=check_availability&ruangan_id=1&tanggal=2025-11-15&waktu_mulai=08:00:00&waktu_selesai=10:00:00
```

**Response Success (Available):**
```json
{
  "success": true,
  "message": "Availability checked",
  "data": {
    "available": true,
    "conflicts": []
  }
}
```

**Response Success (Not Available):**
```json
{
  "success": true,
  "message": "Availability checked",
  "data": {
    "available": false,
    "conflicts": [
      {
        "type": "jadwal",
        "kegiatan": "Kelas Pemrograman Web",
        "waktu_mulai": "08:00:00",
        "waktu_selesai": "10:00:00"
      }
    ]
  }
}
```

---

### 📜 History

#### 15. Get History
**Endpoint:** `GET /api.php?endpoint=history`

**Headers:** `Authorization: Bearer {token}`

**Response Success:**
```json
{
  "success": true,
  "message": "History retrieved successfully",
  "data": [
    {
      "id": 1,
      "booking_id": 10,
      "user_id": 1,
      "ruangan_id": 1,
      "action": "Booking Dibuat",
      "old_status": null,
      "new_status": "pending",
      "keterangan": "Booking baru dibuat oleh user",
      "nama_ruangan": "Lab Komputer 1",
      "nama_lengkap": "John Doe",
      "created_at": "2025-11-13 10:30:00"
    }
  ]
}
```

---

## ⚠️ Error Responses

### Validation Error
```json
{
  "success": false,
  "message": "Missing required fields: username, password",
  "data": null
}
```

### Unauthorized
```json
{
  "success": false,
  "message": "Unauthorized - No token provided",
  "data": null
}
```

### Not Found
```json
{
  "success": false,
  "message": "Ruangan not found",
  "data": null
}
```

### Server Error
```json
{
  "success": false,
  "message": "Booking gagal: Database error",
  "data": null
}
```

---

## 📱 Testing dengan Postman

1. Import collection dengan endpoints di atas
2. Set environment variable:
   - `base_url`: http://localhost/PlsworkUKK
   - `token`: (dari response login)

3. Test flow:
   - Register/Login → Get token
   - Get ruangan list
   - Check availability
   - Create booking
   - Get my bookings

---

## 🔒 Security Notes

1. Token expires dalam 30 hari
2. Password di-hash menggunakan bcrypt
3. SQL injection protection dengan mysqli_real_escape_string
4. CORS enabled untuk mobile apps
5. Input validation pada semua endpoints

---

## 📊 Response Format

Semua response menggunakan format JSON dengan struktur:

```json
{
  "success": boolean,
  "message": string,
  "data": object | array | null,
  "timestamp": string
}
```

- `success`: true/false status operasi
- `message`: pesan deskriptif
- `data`: data hasil query (null jika error)
- `timestamp`: waktu response dibuat
