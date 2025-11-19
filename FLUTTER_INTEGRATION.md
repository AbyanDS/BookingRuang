# Flutter Integration Example

## 📦 Dependencies

Tambahkan di `pubspec.yaml`:

```yaml
dependencies:
  http: ^1.1.0
  shared_preferences: ^2.2.2
```

---

## 🔧 API Service Class

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static const String baseUrl = 'http://localhost/PlsworkUKK/api.php';
  String? _token;
  
  // Singleton pattern
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();
  
  // Load token from storage
  Future<void> loadToken() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
  }
  
  // Save token to storage
  Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
    _token = token;
  }
  
  // Remove token
  Future<void> removeToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    _token = null;
  }
  
  // Get headers
  Map<String, String> _getHeaders({bool needAuth = false}) {
    final headers = {
      'Content-Type': 'application/json',
    };
    
    if (needAuth && _token != null) {
      headers['Authorization'] = 'Bearer $_token';
    }
    
    return headers;
  }
  
  // ============ AUTHENTICATION ============
  
  Future<Map<String, dynamic>> login(String username, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl?endpoint=login'),
        headers: _getHeaders(),
        body: jsonEncode({
          'username': username,
          'password': password,
        }),
      );
      
      final data = jsonDecode(response.body);
      
      if (data['success'] == true) {
        await saveToken(data['data']['token']);
      }
      
      return data;
    } catch (e) {
      return {
        'success': false,
        'message': 'Error: $e',
      };
    }
  }
  
  Future<Map<String, dynamic>> register({
    required String username,
    required String password,
    required String namaLengkap,
    required String email,
    required String noHp,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl?endpoint=register'),
        headers: _getHeaders(),
        body: jsonEncode({
          'username': username,
          'password': password,
          'nama_lengkap': namaLengkap,
          'email': email,
          'no_hp': noHp,
        }),
      );
      
      final data = jsonDecode(response.body);
      
      if (data['success'] == true) {
        await saveToken(data['data']['token']);
      }
      
      return data;
    } catch (e) {
      return {
        'success': false,
        'message': 'Error: $e',
      };
    }
  }
  
  Future<Map<String, dynamic>> getProfile() async {
    try {
      await loadToken();
      
      final response = await http.get(
        Uri.parse('$baseUrl?endpoint=profile'),
        headers: _getHeaders(needAuth: true),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Error: $e',
      };
    }
  }
  
  Future<Map<String, dynamic>> logout() async {
    await removeToken();
    return {
      'success': true,
      'message': 'Logged out successfully',
    };
  }
  
  // ============ RUANGAN ============
  
  Future<Map<String, dynamic>> getRuangan({
    String? status,
    String? search,
  }) async {
    try {
      var uri = Uri.parse('$baseUrl?endpoint=ruangan');
      
      Map<String, String> params = {};
      if (status != null) params['status'] = status;
      if (search != null) params['search'] = search;
      
      uri = uri.replace(queryParameters: {...uri.queryParameters, ...params});
      
      final response = await http.get(
        uri,
        headers: _getHeaders(),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Error: $e',
      };
    }
  }
  
  Future<Map<String, dynamic>> getRuanganDetail(int id) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl?endpoint=ruangan_detail&id=$id'),
        headers: _getHeaders(),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Error: $e',
      };
    }
  }
  
  // ============ BOOKING ============
  
  Future<Map<String, dynamic>> createBooking({
    required int ruanganId,
    required String tanggalBooking,
    required String waktuMulai,
    required String waktuSelesai,
    required String keperluan,
    String? keterangan,
  }) async {
    try {
      await loadToken();
      
      final response = await http.post(
        Uri.parse('$baseUrl?endpoint=create_booking'),
        headers: _getHeaders(needAuth: true),
        body: jsonEncode({
          'ruangan_id': ruanganId,
          'tanggal_booking': tanggalBooking,
          'waktu_mulai': waktuMulai,
          'waktu_selesai': waktuSelesai,
          'keperluan': keperluan,
          'keterangan': keterangan,
        }),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Error: $e',
      };
    }
  }
  
  Future<Map<String, dynamic>> getMyBookings({String? status}) async {
    try {
      await loadToken();
      
      var uri = Uri.parse('$baseUrl?endpoint=my_bookings');
      
      if (status != null) {
        uri = uri.replace(queryParameters: {'status': status});
      }
      
      final response = await http.get(
        uri,
        headers: _getHeaders(needAuth: true),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Error: $e',
      };
    }
  }
  
  Future<Map<String, dynamic>> getBookingDetail(int id) async {
    try {
      await loadToken();
      
      final response = await http.get(
        Uri.parse('$baseUrl?endpoint=booking_detail&id=$id'),
        headers: _getHeaders(needAuth: true),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Error: $e',
      };
    }
  }
  
  Future<Map<String, dynamic>> cancelBooking(int id) async {
    try {
      await loadToken();
      
      final response = await http.post(
        Uri.parse('$baseUrl?endpoint=cancel_booking&id=$id'),
        headers: _getHeaders(needAuth: true),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Error: $e',
      };
    }
  }
  
  Future<Map<String, dynamic>> getListBooking() async {
    try {
      await loadToken();
      
      final response = await http.get(
        Uri.parse('$baseUrl?endpoint=list_booking'),
        headers: _getHeaders(needAuth: true),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Error: $e',
      };
    }
  }
  
  // ============ JADWAL ============
  
  Future<Map<String, dynamic>> getJadwal({
    String? hari,
    int? ruanganId,
  }) async {
    try {
      var uri = Uri.parse('$baseUrl?endpoint=jadwal');
      
      Map<String, String> params = {};
      if (hari != null) params['hari'] = hari;
      if (ruanganId != null) params['ruangan'] = ruanganId.toString();
      
      uri = uri.replace(queryParameters: {...uri.queryParameters, ...params});
      
      final response = await http.get(
        uri,
        headers: _getHeaders(),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Error: $e',
      };
    }
  }
  
  Future<Map<String, dynamic>> getJadwalDaily({
    String? date,
    int? ruanganId,
  }) async {
    try {
      var uri = Uri.parse('$baseUrl?endpoint=jadwal_daily');
      
      Map<String, String> params = {};
      if (date != null) params['date'] = date;
      if (ruanganId != null) params['ruangan'] = ruanganId.toString();
      
      uri = uri.replace(queryParameters: {...uri.queryParameters, ...params});
      
      final response = await http.get(
        uri,
        headers: _getHeaders(),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Error: $e',
      };
    }
  }
  
  Future<Map<String, dynamic>> checkAvailability({
    required int ruanganId,
    required String tanggal,
    required String waktuMulai,
    required String waktuSelesai,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl?endpoint=check_availability').replace(
        queryParameters: {
          'ruangan_id': ruanganId.toString(),
          'tanggal': tanggal,
          'waktu_mulai': waktuMulai,
          'waktu_selesai': waktuSelesai,
        },
      );
      
      final response = await http.get(
        uri,
        headers: _getHeaders(),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Error: $e',
      };
    }
  }
  
  // ============ HISTORY ============
  
  Future<Map<String, dynamic>> getHistory() async {
    try {
      await loadToken();
      
      final response = await http.get(
        Uri.parse('$baseUrl?endpoint=history'),
        headers: _getHeaders(needAuth: true),
      );
      
      return jsonDecode(response.body);
    } catch (e) {
      return {
        'success': false,
        'message': 'Error: $e',
      };
    }
  }
}
```

---

## 🏗️ Model Classes

### User Model

```dart
class User {
  final int id;
  final String username;
  final String namaLengkap;
  final String email;
  final String noHp;
  final String role;
  final String? createdAt;
  
  User({
    required this.id,
    required this.username,
    required this.namaLengkap,
    required this.email,
    required this.noHp,
    required this.role,
    this.createdAt,
  });
  
  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: int.parse(json['id'].toString()),
      username: json['username'],
      namaLengkap: json['nama_lengkap'],
      email: json['email'],
      noHp: json['no_hp'],
      role: json['role'],
      createdAt: json['created_at'],
    );
  }
  
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'username': username,
      'nama_lengkap': namaLengkap,
      'email': email,
      'no_hp': noHp,
      'role': role,
      'created_at': createdAt,
    };
  }
}
```

### Ruangan Model

```dart
class Ruangan {
  final int id;
  final String namaRuangan;
  final int kapasitas;
  final String lokasi;
  final String fasilitas;
  final String status;
  final String? foto;
  final String? createdAt;
  
  Ruangan({
    required this.id,
    required this.namaRuangan,
    required this.kapasitas,
    required this.lokasi,
    required this.fasilitas,
    required this.status,
    this.foto,
    this.createdAt,
  });
  
  factory Ruangan.fromJson(Map<String, dynamic> json) {
    return Ruangan(
      id: int.parse(json['id'].toString()),
      namaRuangan: json['nama_ruangan'],
      kapasitas: int.parse(json['kapasitas'].toString()),
      lokasi: json['lokasi'],
      fasilitas: json['fasilitas'],
      status: json['status'],
      foto: json['foto'],
      createdAt: json['created_at'],
    );
  }
}
```

### Booking Model

```dart
class Booking {
  final int id;
  final int userId;
  final int ruanganId;
  final String tanggalBooking;
  final String waktuMulai;
  final String waktuSelesai;
  final String keperluan;
  final String? keterangan;
  final String status;
  final String? namaRuangan;
  final String? lokasi;
  final String? foto;
  final String? namaLengkap;
  final String? createdAt;
  
  Booking({
    required this.id,
    required this.userId,
    required this.ruanganId,
    required this.tanggalBooking,
    required this.waktuMulai,
    required this.waktuSelesai,
    required this.keperluan,
    this.keterangan,
    required this.status,
    this.namaRuangan,
    this.lokasi,
    this.foto,
    this.namaLengkap,
    this.createdAt,
  });
  
  factory Booking.fromJson(Map<String, dynamic> json) {
    return Booking(
      id: int.parse(json['id'].toString()),
      userId: int.parse(json['user_id'].toString()),
      ruanganId: int.parse(json['ruangan_id'].toString()),
      tanggalBooking: json['tanggal_booking'],
      waktuMulai: json['waktu_mulai'],
      waktuSelesai: json['waktu_selesai'],
      keperluan: json['keperluan'],
      keterangan: json['keterangan'],
      status: json['status'],
      namaRuangan: json['nama_ruangan'],
      lokasi: json['lokasi'],
      foto: json['foto'],
      namaLengkap: json['nama_lengkap'],
      createdAt: json['created_at'],
    );
  }
}
```

---

## 📱 Usage Examples

### Login Screen

```dart
import 'package:flutter/material.dart';

class LoginScreen extends StatefulWidget {
  @override
  _LoginScreenState createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  final _apiService = ApiService();
  bool _isLoading = false;
  
  Future<void> _login() async {
    if (!_formKey.currentState!.validate()) return;
    
    setState(() => _isLoading = true);
    
    final result = await _apiService.login(
      _usernameController.text,
      _passwordController.text,
    );
    
    setState(() => _isLoading = false);
    
    if (result['success']) {
      Navigator.pushReplacementNamed(context, '/home');
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result['message'])),
      );
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Login')),
      body: Padding(
        padding: EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              TextFormField(
                controller: _usernameController,
                decoration: InputDecoration(
                  labelText: 'Username',
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value?.isEmpty ?? true) {
                    return 'Username harus diisi';
                  }
                  return null;
                },
              ),
              SizedBox(height: 16),
              TextFormField(
                controller: _passwordController,
                decoration: InputDecoration(
                  labelText: 'Password',
                  border: OutlineInputBorder(),
                ),
                obscureText: true,
                validator: (value) {
                  if (value?.isEmpty ?? true) {
                    return 'Password harus diisi';
                  }
                  return null;
                },
              ),
              SizedBox(height: 24),
              ElevatedButton(
                onPressed: _isLoading ? null : _login,
                child: _isLoading
                    ? CircularProgressIndicator()
                    : Text('LOGIN'),
                style: ElevatedButton.styleFrom(
                  minimumSize: Size(double.infinity, 48),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
```

### Ruangan List Screen

```dart
class RuanganListScreen extends StatefulWidget {
  @override
  _RuanganListScreenState createState() => _RuanganListScreenState();
}

class _RuanganListScreenState extends State<RuanganListScreen> {
  final _apiService = ApiService();
  List<Ruangan> _ruanganList = [];
  bool _isLoading = true;
  
  @override
  void initState() {
    super.initState();
    _loadRuangan();
  }
  
  Future<void> _loadRuangan() async {
    setState(() => _isLoading = true);
    
    final result = await _apiService.getRuangan(status: 'tersedia');
    
    if (result['success']) {
      setState(() {
        _ruanganList = (result['data'] as List)
            .map((json) => Ruangan.fromJson(json))
            .toList();
        _isLoading = false;
      });
    } else {
      setState(() => _isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result['message'])),
      );
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Pilih Ruangan')),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : ListView.builder(
              itemCount: _ruanganList.length,
              itemBuilder: (context, index) {
                final ruangan = _ruanganList[index];
                return Card(
                  margin: EdgeInsets.all(8),
                  child: ListTile(
                    leading: ruangan.foto != null
                        ? Image.network(
                            ruangan.foto!,
                            width: 60,
                            height: 60,
                            fit: BoxFit.cover,
                          )
                        : Icon(Icons.meeting_room, size: 60),
                    title: Text(ruangan.namaRuangan),
                    subtitle: Text(
                      '${ruangan.lokasi}\nKapasitas: ${ruangan.kapasitas} orang',
                    ),
                    isThreeLine: true,
                    trailing: Icon(Icons.arrow_forward_ios),
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => BookingScreen(ruangan: ruangan),
                        ),
                      );
                    },
                  ),
                );
              },
            ),
    );
  }
}
```

### Create Booking Screen

```dart
class BookingScreen extends StatefulWidget {
  final Ruangan ruangan;
  
  BookingScreen({required this.ruangan});
  
  @override
  _BookingScreenState createState() => _BookingScreenState();
}

class _BookingScreenState extends State<BookingScreen> {
  final _formKey = GlobalKey<FormState>();
  final _keperluanController = TextEditingController();
  final _keteranganController = TextEditingController();
  final _apiService = ApiService();
  
  DateTime? _selectedDate;
  TimeOfDay? _waktuMulai;
  TimeOfDay? _waktuSelesai;
  bool _isLoading = false;
  
  Future<void> _submitBooking() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedDate == null || _waktuMulai == null || _waktuSelesai == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Lengkapi semua data')),
      );
      return;
    }
    
    setState(() => _isLoading = true);
    
    // Check availability first
    final checkResult = await _apiService.checkAvailability(
      ruanganId: widget.ruangan.id,
      tanggal: _selectedDate!.toIso8601String().split('T')[0],
      waktuMulai: '${_waktuMulai!.hour.toString().padLeft(2, '0')}:${_waktuMulai!.minute.toString().padLeft(2, '0')}:00',
      waktuSelesai: '${_waktuSelesai!.hour.toString().padLeft(2, '0')}:${_waktuSelesai!.minute.toString().padLeft(2, '0')}:00',
    );
    
    if (!checkResult['data']['available']) {
      setState(() => _isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Waktu bentrok dengan jadwal lain')),
      );
      return;
    }
    
    // Create booking
    final result = await _apiService.createBooking(
      ruanganId: widget.ruangan.id,
      tanggalBooking: _selectedDate!.toIso8601String().split('T')[0],
      waktuMulai: '${_waktuMulai!.hour.toString().padLeft(2, '0')}:${_waktuMulai!.minute.toString().padLeft(2, '0')}:00',
      waktuSelesai: '${_waktuSelesai!.hour.toString().padLeft(2, '0')}:${_waktuSelesai!.minute.toString().padLeft(2, '0')}:00',
      keperluan: _keperluanController.text,
      keterangan: _keteranganController.text,
    );
    
    setState(() => _isLoading = false);
    
    if (result['success']) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Booking berhasil dibuat')),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result['message'])),
      );
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Booking Ruangan')),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Ruangan info
              Card(
                child: Padding(
                  padding: EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        widget.ruangan.namaRuangan,
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                      SizedBox(height: 8),
                      Text('Lokasi: ${widget.ruangan.lokasi}'),
                      Text('Kapasitas: ${widget.ruangan.kapasitas} orang'),
                    ],
                  ),
                ),
              ),
              SizedBox(height: 16),
              
              // Date picker
              ListTile(
                title: Text('Tanggal Booking'),
                subtitle: Text(_selectedDate == null
                    ? 'Pilih tanggal'
                    : _selectedDate!.toIso8601String().split('T')[0]),
                trailing: Icon(Icons.calendar_today),
                onTap: () async {
                  final date = await showDatePicker(
                    context: context,
                    initialDate: DateTime.now(),
                    firstDate: DateTime.now(),
                    lastDate: DateTime.now().add(Duration(days: 365)),
                  );
                  if (date != null) {
                    setState(() => _selectedDate = date);
                  }
                },
              ),
              
              // Time pickers
              ListTile(
                title: Text('Waktu Mulai'),
                subtitle: Text(_waktuMulai == null
                    ? 'Pilih waktu'
                    : _waktuMulai!.format(context)),
                trailing: Icon(Icons.access_time),
                onTap: () async {
                  final time = await showTimePicker(
                    context: context,
                    initialTime: TimeOfDay.now(),
                  );
                  if (time != null) {
                    setState(() => _waktuMulai = time);
                  }
                },
              ),
              
              ListTile(
                title: Text('Waktu Selesai'),
                subtitle: Text(_waktuSelesai == null
                    ? 'Pilih waktu'
                    : _waktuSelesai!.format(context)),
                trailing: Icon(Icons.access_time),
                onTap: () async {
                  final time = await showTimePicker(
                    context: context,
                    initialTime: TimeOfDay.now(),
                  );
                  if (time != null) {
                    setState(() => _waktuSelesai = time);
                  }
                },
              ),
              
              SizedBox(height: 16),
              
              // Keperluan
              TextFormField(
                controller: _keperluanController,
                decoration: InputDecoration(
                  labelText: 'Keperluan',
                  border: OutlineInputBorder(),
                ),
                maxLines: 2,
                validator: (value) {
                  if (value?.isEmpty ?? true) {
                    return 'Keperluan harus diisi';
                  }
                  return null;
                },
              ),
              
              SizedBox(height: 16),
              
              // Keterangan
              TextFormField(
                controller: _keteranganController,
                decoration: InputDecoration(
                  labelText: 'Keterangan (optional)',
                  border: OutlineInputBorder(),
                ),
                maxLines: 3,
              ),
              
              SizedBox(height: 24),
              
              // Submit button
              ElevatedButton(
                onPressed: _isLoading ? null : _submitBooking,
                child: _isLoading
                    ? CircularProgressIndicator()
                    : Text('BOOKING SEKARANG'),
                style: ElevatedButton.styleFrom(
                  minimumSize: Size(double.infinity, 48),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
```

---

## ✅ Testing Checklist

- [ ] Login dengan credentials valid
- [ ] Login dengan credentials invalid
- [ ] Register user baru
- [ ] Get profile setelah login
- [ ] Get list ruangan
- [ ] Get ruangan detail
- [ ] Check availability ruangan
- [ ] Create booking baru
- [ ] Get my bookings
- [ ] Get booking detail
- [ ] Cancel booking
- [ ] Get jadwal weekly
- [ ] Get jadwal daily
- [ ] Get history
- [ ] Logout

---

## 🔧 Common Issues & Solutions

### Issue: Connection refused
**Solution:** Pastikan XAMPP Apache running dan base URL benar

### Issue: CORS error
**Solution:** Sudah dihandle di api.php dengan headers:
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

### Issue: Token expired
**Solution:** Implement refresh token atau re-login otomatis

### Issue: Image not loading
**Solution:** Pastikan path uploads/ accessible dari luar dan gunakan absolute URL
