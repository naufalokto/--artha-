# 🚨 Quick Fix untuk Error 500 "Gagal menyimpan transaksi"

## ⚡ Langkah Cepat (5 menit)

### 1. Cek Laravel Logs Sekarang
```bash
# Buka terminal baru dan jalankan:
tail -f storage/logs/laravel.log

# Lalu test checkout di browser untuk melihat error detail
```

### 2. Pastikan .env Sudah Benar
```bash
# Cek file .env berisi:
MIDTRANS_SERVER_KEY=SB-Mid-server-43gE8qD7eYGxQzpdvvf0G-tl
MIDTRANS_CLIENT_KEY=SB-Mid-client-44JyAuImP_XPOzeZ
MIDTRANS_IS_PRODUCTION=false
API_BASE_URL=http://localhost:9090
```

### 3. Test Go Backend Langsung
```bash
# Test apakah Go backend berjalan:
curl http://localhost:9090/stocks

# Jika error "connection refused":
# - Pastikan Go backend running di port 9090
# - Jalankan: go run main.go
```

### 4. Cek JWT Token Valid
```javascript
// Di browser console (F12):
console.log('JWT Token:', sessionStorage.getItem('jwt_token'));
console.log('User Data:', JSON.parse(sessionStorage.getItem('user') || '{}'));
```

## 🎯 Kemungkinan Penyebab & Solusi

### Penyebab 1: Database Table Belum Ada
**Error di Go backend:** `table "transactions" doesn't exist`

**Solusi:**
```sql
-- Jalankan di database PostgreSQL:
CREATE TABLE IF NOT EXISTS transactions (
    id SERIAL PRIMARY KEY,
    transaction_id VARCHAR(255) UNIQUE NOT NULL,
    user_id INTEGER NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    payment_token VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transaction_details (
    id SERIAL PRIMARY KEY,
    transaction_id INTEGER REFERENCES transactions(id),
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Penyebab 2: Go Backend Tidak Terhubung Database
**Error di Go console:** `failed to connect to database`

**Solusi:**
1. Cek koneksi database di file config Go
2. Pastikan PostgreSQL running
3. Restart Go backend

### Penyebab 3: JWT Token Invalid
**Error di Laravel log:** `JWT token invalid`

**Solusi:**
```bash
# Login ulang sebagai customer:
# 1. Buka http://localhost:8000/login
# 2. Login dengan role "customer"
# 3. Test checkout lagi
```

### Penyebab 4: Midtrans Keys Salah di Go Backend
**Error di Go console:** `Midtrans authentication failed`

**Solusi:**
Pastikan Go backend menggunakan keys yang sama:
```go
// Di file config Go backend:
MIDTRANS_SERVER_KEY=SB-Mid-server-43gE8qD7eYGxQzpdvvf0G-tl
MIDTRANS_CLIENT_KEY=SB-Mid-client-44JyAuImP_XPOzeZ
```

## 🔍 Debug Tools yang Sudah Dibuat

### 1. Laravel Debug Logging
```bash
# Sudah ditambahkan ke ApiController.php
# Cek log dengan: tail -f storage/logs/laravel.log
```

### 2. Manual Test Script
```bash
# Jalankan test manual:
php test_checkout.php

# Ganti JWT token di script dengan token real dari browser
```

### 3. Debug Guide
```bash
# Baca panduan lengkap:
cat CHECKOUT_DEBUG.md
```

## ⚡ Test Langkah demi Langkah

### Step 1: Test Go Backend Health
```bash
curl http://localhost:9090/stocks
# Expected: JSON response dengan data produk
```

### Step 2: Test Customer Authentication
```bash
# Login sebagai customer di browser
# Cek JWT token di Developer Tools
```

### Step 3: Test Checkout dengan Debug
```bash
# Buka browser console saat checkout
# Lihat network tab untuk error detail
```

### Step 4: Check Laravel Logs
```bash
tail -f storage/logs/laravel.log
# Cari log "=== CHECKOUT DEBUG START ==="
```

## 🚀 Most Likely Solutions

### Solusi #1: Database Schema Missing (90% kemungkinan)
```sql
-- Jalankan SQL schema di atas
-- Restart Go backend
-- Test checkout
```

### Solusi #2: JWT Token Expired
```bash
# Login ulang sebagai customer
# Test checkout lagi
```

### Solusi #3: Go Backend Not Running
```bash
# Terminal baru, jalankan:
cd /path/to/go/backend
go run main.go
# Pastikan running di port 9090
```

## 📞 Next Steps

1. **Immediately Check:**
   - Go backend console logs
   - Laravel logs: `tail -f storage/logs/laravel.log`
   - Database tables exist

2. **If Still Error:**
   - Run `php test_checkout.php` dengan real JWT token
   - Check exact error message di Go backend

3. **After Fix:**
   - Test checkout flow end-to-end
   - Verify Midtrans popup appears

---

**Dengan debug logging yang sudah ditambahkan, kita akan dapat melihat exactly where the error happens!** 🔍 