# Implementasi Multi-Bahasa (English-Indonesia)

## Overview
Implementasi fitur multi-bahasa untuk aplikasi Laravel dengan dukungan bahasa Inggris dan Indonesia.

## File yang Ditambahkan/Dimodifikasi

### 1. File Bahasa
- `resources/lang/en/admin.php` - Bahasa Inggris untuk admin dashboard
- `resources/lang/id/admin.php` - Bahasa Indonesia untuk admin dashboard
- `resources/lang/en/auth.php` - Bahasa Inggris untuk halaman autentikasi
- `resources/lang/id/auth.php` - Bahasa Indonesia untuk halaman autentikasi
- `resources/lang/en/general.php` - Bahasa Inggris untuk teks umum
- `resources/lang/id/general.php` - Bahasa Indonesia untuk teks umum

### 2. Middleware
- `app/Http/Middleware/SetLocale.php` - Middleware untuk mengatur bahasa berdasarkan session

### 3. Konfigurasi
- `app/Http/Kernel.php` - Menambahkan middleware SetLocale ke web group
- `routes/web.php` - Menambahkan route untuk switch bahasa

### 4. View yang Diupdate
- `resources/views/admin/dashboard.blade.php` - Menggunakan helper `__()` dan menambahkan tombol switch bahasa
- `resources/views/login.blade.php` - Menggunakan helper `__()` dan menambahkan tombol switch bahasa

## Cara Penggunaan

### 1. Menggunakan Helper Translation
```php
// Dalam view Blade
{{ __('admin.dashboard') }}
{{ __('auth.login') }}
{{ __('general.welcome') }}
```

### 2. Switch Bahasa
```javascript
// Dalam JavaScript
function switchLanguage(locale) {
    window.location.href = `/language/${locale}`;
}
```

### 3. Tombol Switch Bahasa
Tombol switch bahasa sudah ditambahkan di:
- Admin Dashboard (header kanan atas)
- Login Page (pojok kanan atas)

## Struktur File Bahasa

### Admin Dashboard (`admin.php`)
```php
return [
    'dashboard' => 'Admin Dashboard',
    'manage_users' => 'Manage Users',
    'create_new_account' => 'Create New Account',
    'username' => 'Username',
    'first_name' => 'First Name',
    'last_name' => 'Last Name',
    'email' => 'Email',
    'password' => 'Password',
    'role' => 'Role',
    'manager' => 'Manager',
    'sales' => 'Sales',
    'create_account' => 'Create Account',
    'users_list' => 'Users List',
    'name' => 'Name',
    'actions' => 'Actions',
    'delete' => 'Delete',
    'logout' => 'Logout',
    'language' => 'Language',
    'english' => 'English',
    'indonesian' => 'Indonesian',
    'switch_language' => 'Switch Language',
    'user_created_successfully' => 'User created successfully',
    'user_deleted_successfully' => 'User deleted successfully',
    'confirm_delete_user' => 'Are you sure you want to delete this user?',
    'error_loading_users' => 'Failed to load users',
    'error_creating_user' => 'Failed to create user',
    'error_deleting_user' => 'Failed to delete user',
];
```

### Authentication (`auth.php`)
```php
return [
    'login' => 'Login',
    'email' => 'Email',
    'password' => 'Password',
    'back' => 'Back',
    'email_required' => 'Email and password are required',
    'invalid_email' => 'Please enter a valid email address',
    'invalid_credentials' => 'Invalid credentials',
    'check_input' => 'Please check your input',
    'too_many_attempts' => 'Too many attempts. Please try again later',
    'server_error' => 'Server error. Please try again later',
    'login_error' => 'An error occurred during login. Please try again.',
];
```

## Route untuk Switch Bahasa
```php
Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'id'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('language.switch');
```

## Middleware SetLocale
```php
public function handle(Request $request, Closure $next)
{
    $locale = session('locale', 'en');
    App::setLocale($locale);
    
    return $next($request);
}
```

## Fitur yang Sudah Diimplementasi

### 1. Admin Dashboard
- ✅ Semua teks menggunakan helper `__()`
- ✅ Tombol switch bahasa di header
- ✅ Pesan alert dalam bahasa yang dipilih
- ✅ Form labels dan buttons dalam bahasa yang dipilih

### 2. Login Page
- ✅ Semua teks menggunakan helper `__()`
- ✅ Tombol switch bahasa di pojok kanan atas
- ✅ Error messages dalam bahasa yang dipilih

### 3. Session Management
- ✅ Bahasa tersimpan dalam session
- ✅ Default bahasa: English
- ✅ Fallback ke English jika bahasa tidak ditemukan

## Cara Menambahkan Bahasa Baru

1. Buat file bahasa baru di `resources/lang/{locale}/`
2. Tambahkan locale baru ke route language switch
3. Update middleware untuk mendukung locale baru

## Cara Menambahkan Teks Baru

1. Tambahkan key-value pair ke file bahasa yang sesuai
2. Gunakan helper `__()` di view atau controller

## Testing

1. Akses admin dashboard
2. Klik tombol "EN" atau "ID" untuk switch bahasa
3. Verifikasi semua teks berubah sesuai bahasa
4. Test di halaman login juga

## Catatan Penting

- Bahasa default adalah English (`en`)
- Session bahasa akan tersimpan selama user session
- Jika bahasa tidak ditemukan, akan fallback ke English
- Tombol switch bahasa hanya ada di dashboard admin dan login page sesuai permintaan 