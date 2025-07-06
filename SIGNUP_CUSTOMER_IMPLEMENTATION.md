# Implementasi Signup Customer dengan Go Backend

## Overview

Signup customer telah diupdate untuk menggunakan Go backend API sesuai dengan spesifikasi yang diberikan. Implementasi ini memungkinkan customer untuk mendaftar dengan role "Customer" secara otomatis.

## Perubahan yang Dilakukan

### 1. Update ApiController (`app/Http/Controllers/ApiController.php`)

**Method:** `signup()`

**Perubahan:**
- Menambahkan validasi untuk field `username`
- Mengubah format role dari lowercase ke proper case (`Customer`, `Manager`, `Sales`, `Admin`)
- Menghapus validasi `unique:users` untuk email (karena validasi dilakukan di Go backend)
- Mengubah urutan field sesuai dengan Go backend API
- Menambahkan proper error handling untuk response dari Go backend

**Format Request yang Dikirim ke Go Backend:**
```json
{
  "username": "username_baru",
  "email": "email@example.com", 
  "password": "password123",
  "firstname": "Nama",
  "lastname": "Belakang",
  "role": "Customer"
}
```

### 2. Update Signup View (`resources/views/signup.blade.php`)

**Perubahan:**
- Menambahkan field `username` di posisi pertama
- Menghapus field `address` (tidak ada di Go backend API)
- Menambahkan hidden field `role` dengan value "Customer"
- Menambahkan validasi `minlength="8"` untuk password
- Update JavaScript untuk mengirim data sesuai format baru
- Update title dan heading menjadi lebih generic

**Field yang Tersedia:**
1. Username (required)
2. First Name (required)
3. Last Name (required)
4. Email (required)
5. Password (required, min 8 karakter)
6. Role (hidden, otomatis "Customer")

## API Endpoint

**URL:** `POST /signup`

**Laravel Route:** `Route::post('/signup', [ApiController::class, 'signup'])->name('auth.signup');`

**Go Backend URL:** `http://localhost:9090/signup`

## Response Format

### Success Response
```json
{
  "status": "success",
  "message": "User registered successfully",
  "user": {
    "user_id": 123,
    "username": "john_doe",
    "email": "john@example.com",
    "firstname": "John",
    "lastname": "Doe",
    "role": "Customer"
  }
}
```

### Error Response
```json
{
  "status": "error",
  "message": "Email already exists"
}
```

## Validasi

### Frontend Validation
- Username: required, string, max 255 karakter
- First Name: required, string, max 255 karakter
- Last Name: required, string, max 255 karakter
- Email: required, valid email format, max 255 karakter
- Password: required, minimum 8 karakter
- Role: otomatis "Customer"

### Backend Validation (Laravel)
```php
$validator = Validator::make($request->all(), [
    'username' => 'required|string|max:255',
    'email' => 'required|string|email|max:255',
    'password' => 'required|string|min:8',
    'firstname' => 'required|string|max:255',
    'lastname' => 'required|string|max:255',
    'role' => 'required|string|in:Customer,Manager,Sales,Admin'
]);
```

## Flow Signup

1. User mengakses `/signup`
2. User mengisi form dengan data yang diperlukan
3. JavaScript mengirim data ke Laravel endpoint `/signup`
4. Laravel memvalidasi data
5. Laravel mengirim request ke Go backend `http://localhost:9090/signup`
6. Go backend memproses dan menyimpan user
7. Response dikirim kembali ke Laravel
8. Laravel mengirim response ke frontend
9. Jika berhasil, user diarahkan ke halaman login

## Testing

File test telah dibuat: `test_signup_customer.php`

**Cara menjalankan test:**
```bash
php test_signup_customer.php
```

**Test akan:**
1. Test langsung ke Go backend
2. Test melalui Laravel route
3. Menampilkan response dan status code

## Catatan Penting

1. **Password di-hash otomatis** oleh Go backend menggunakan bcrypt
2. **Email dan username harus unik** (validasi di Go backend)
3. **Role otomatis "Customer"** untuk signup melalui form ini
4. **Tidak memerlukan authentication** untuk mengakses endpoint signup
5. **Setelah signup berhasil**, user dapat login menggunakan email dan password yang sama

## Troubleshooting

### Error "Connection refused"
- Pastikan Go backend berjalan di `http://localhost:9090`
- Cek apakah port 9090 tidak diblokir

### Error "Email already exists"
- Email sudah terdaftar di database
- Gunakan email yang berbeda

### Error "Username already exists"
- Username sudah terdaftar di database
- Gunakan username yang berbeda

### Error "Invalid role"
- Role harus salah satu dari: Customer, Manager, Sales, Admin
- Untuk signup customer, role otomatis "Customer"

## File yang Dimodifikasi

1. `app/Http/Controllers/ApiController.php` - Update method signup
2. `resources/views/signup.blade.php` - Update form dan JavaScript
3. `test_signup_customer.php` - File test baru
4. `SIGNUP_CUSTOMER_IMPLEMENTATION.md` - Dokumentasi ini 