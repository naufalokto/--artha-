# 🚨 Debug Checkout Error 500 - Customer System

## ❌ Error yang Terjadi
```
POST http://localhost:9090/customer/checkout
500 Internal Server Error: "Gagal menyimpan transaksi"
```

## 🔍 Langkah Debugging

### 1. Cek Backend Go Console
```bash
# Lihat terminal tempat Go backend berjalan
# Error detail akan tampil di console

# Contoh error yang mungkin:
# - "Database connection failed"
# - "Table 'transactions' doesn't exist"
# - "Invalid JSON payload"
# - "JWT token invalid"
```

### 2. Cek Database Connection
```sql
-- Pastikan database berjalan dan terhubung
-- Cek apakah table transaksi sudah ada

-- Contoh schema yang diperlukan:
CREATE TABLE transactions (
    id SERIAL PRIMARY KEY,
    transaction_id VARCHAR(255) UNIQUE NOT NULL,
    user_id INTEGER NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    payment_token VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE transaction_details (
    id SERIAL PRIMARY KEY,
    transaction_id INTEGER REFERENCES transactions(id),
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3. Verifikasi Data yang Dikirim Frontend

Payload yang dikirim dari frontend Laravel:
```json
{
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "price": 100000,
      "name": "Test Product"
    }
  ]
}
```

## 🛠️ Solusi Berdasarkan Error

### Solusi 1: Database Schema Belum Ada
```sql
-- Jalankan script SQL untuk membuat table
-- (lihat schema di atas)
```

### Solusi 2: JWT Token Issue
```go
// Pastikan middleware CustomerMiddleware berjalan dengan benar
// Cek apakah JWT token valid dan user role = "customer"
```

### Solusi 3: Data Format Issue
```go
// Pastikan backend Go menerima data dengan format yang benar
type CheckoutRequest struct {
    Items []CartItem `json:"items"`
}

type CartItem struct {
    ProductID int     `json:"product_id"`
    Quantity  int     `json:"quantity"`
    Price     float64 `json:"price"`
    Name      string  `json:"name"`
}
```

### Solusi 4: Midtrans Integration Issue
```go
// Pastikan Midtrans keys valid di backend Go
// Server Key dan Client Key harus sesuai
```

## 🔧 Quick Fix untuk Testing

### 1. Update ApiController.php untuk Debug
```php
// Tambahkan logging di checkout method
Log::info('Checkout request data', [
    'items' => $items,
    'user' => session('user'),
    'jwt_token' => session('jwt_token')
]);
```

### 2. Test dengan Data Minimal
```json
{
  "items": [
    {
      "product_id": 1,
      "quantity": 1,
      "price": 10000,
      "name": "Test Product"
    }
  ]
}
```

## 🎯 Langkah Verifikasi

### 1. Cek Database
```bash
# Connect ke database dan verifikasi table
psql -h localhost -U username -d database_name
\dt  # List tables
\d transactions  # Describe transactions table
```

### 2. Test API Manual dengan Curl
```bash
curl -X POST http://localhost:9090/customer/checkout \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "items": [
      {
        "product_id": 1,
        "quantity": 1,
        "price": 10000,
        "name": "Test Product"
      }
    ]
  }'
```

### 3. Cek Go Backend Health
```bash
# Test basic endpoint
curl http://localhost:9090/stocks

# Test customer cart endpoint
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \
     http://localhost:9090/customer/cart
```

## 🚀 Solusi Alternatif

### Jika Database Belum Ready
1. **Setup database schema** dengan script SQL di atas
2. **Restart Go backend** setelah database siap
3. **Test ulang checkout**

### Jika JWT Token Invalid
1. **Login ulang** sebagai customer
2. **Cek session data** di browser developer tools
3. **Verifikasi token** di backend Go logs

### Jika Data Format Salah
1. **Update backend Go** untuk handle format yang dikirim
2. **Atau update frontend** untuk mengirim format yang diharapkan backend

## 📞 Next Steps

1. **Jalankan backend Go** dan lihat error detail di console
2. **Cek database** apakah table transaksi sudah ada
3. **Verifikasi JWT token** valid dan user role = "customer"
4. **Test dengan data minimal** untuk isolate masalah

---

**Setelah fix error ini, checkout akan berfungsi normal dengan Midtrans!** 🎉 