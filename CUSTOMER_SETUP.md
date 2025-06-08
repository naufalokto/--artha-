# Setup Customer Dashboard dengan Integrasi Midtrans

## Fitur Customer yang Sudah Dibuat

✅ **Fitur Lengkap 100%:**
- Dashboard produk dengan data real dari backend Go
- Keranjang belanja (add, view, delete items)
- Proses checkout dengan integrasi Midtrans
- Riwayat transaksi lengkap dengan detail
- Webhook Midtrans untuk update status pembayaran
- UI responsif dan modern

## Konfigurasi yang Diperlukan

### 1. Environment Variables (.env)

Tambahkan konfigurasi berikut ke file `.env` Anda:

```env
# Midtrans Configuration
MIDTRANS_SERVER_KEY=SB-Mid-server-YOUR_SERVER_KEY
MIDTRANS_CLIENT_KEY=SB-Mid-client-YOUR_CLIENT_KEY
MIDTRANS_IS_PRODUCTION=false

# Backend Go API URL
API_BASE_URL=http://localhost:9090
```

### 2. Midtrans Setup

1. **Daftar akun Midtrans Sandbox:**
   - Kunjungi: https://dashboard.sandbox.midtrans.com
   - Daftar akun baru atau login

2. **Dapatkan API Keys:**
   - Login ke dashboard Midtrans
   - Pergi ke Settings → Access Keys
   - Copy `Server Key` dan `Client Key`
   - Paste ke file `.env` Anda

3. **Setup Webhook URL:**
   - Di dashboard Midtrans, pergi ke Settings → Configuration
   - Set Payment Notification URL: `http://your-domain.com/midtrans/webhook`
   - Enable semua notification events

### 3. Backend Go Requirements

Pastikan backend Go Anda sudah running di `localhost:9090` dengan endpoint:

```
POST   /customer/cart                    # Add to cart
GET    /customer/cart                    # Get cart items  
DELETE /customer/cart                    # Delete cart items
POST   /customer/checkout                # Process checkout
GET    /customer/transactions/summary    # Get transaction summary
GET    /customer/transactions/detail     # Get transaction details
POST   /midtrans/webhook                # Midtrans webhook handler
GET    /stocks                          # Get available products
```

## Cara Menggunakan

### 1. Login sebagai Customer
- Pastikan user memiliki role `customer`
- Login melalui halaman `/login`

### 2. Browse Produk
- Dashboard menampilkan produk yang tersedia dari backend
- Pilih quantity dan klik "Tambah ke Keranjang"

### 3. Kelola Keranjang
- Lihat item di keranjang
- Hapus item yang tidak diinginkan
- Lihat total harga

### 4. Checkout dan Pembayaran
- Klik "Bayar Sekarang" di halaman keranjang
- Popup Midtrans akan terbuka
- Pilih metode pembayaran
- Complete pembayaran

### 5. Cek Riwayat Transaksi
- Lihat semua transaksi di tab "Transaksi"
- Klik transaksi untuk melihat detail

## Testing Payment dengan Midtrans Sandbox

### Credit Card Testing:
```
Card Number: 4811 1111 1111 1114
CVV: 123
Exp Month: 01
Exp Year: 2025
OTP: 112233
```

### E-wallet Testing:
- Semua e-wallet (GoPay, OVO, Dana, LinkAja) bisa ditest
- Gunakan phone number: 08123456789

### Bank Transfer Testing:
- Semua bank tersedia untuk testing
- VA Number akan otomatis di-generate

## Troubleshooting

### 1. Midtrans Popup Tidak Muncul
- Pastikan `MIDTRANS_CLIENT_KEY` benar di `.env`
- Check console browser untuk error JavaScript
- Pastikan Snap.js script sudah loaded

### 2. Webhook Tidak Bekerja
- Pastikan URL webhook sudah benar di dashboard Midtrans
- Check log Laravel untuk error webhook
- Pastikan backend Go bisa menerima POST /midtrans/webhook

### 3. Produk Tidak Muncul
- Pastikan backend Go running di port 9090
- Check endpoint GET /stocks di backend
- Pastikan ada data produk dengan stock > 0

### 4. Cart Tidak Berfungsi
- Pastikan JWT token tersimpan di session
- Check role user adalah `customer`
- Pastikan endpoint customer cart di backend berfungsi

## Struktur File yang Telah Dibuat/Diupdate

```
app/Http/Controllers/ApiController.php     # Customer methods
routes/web.php                           # Customer routes  
resources/views/customer/dashboard.blade.php  # Customer UI
CUSTOMER_SETUP.md                       # Setup guide
```

## API Integration dengan Backend Go

Frontend Laravel sudah terintegrasi penuh dengan backend Go Anda:

- **Authentication:** JWT token dari Go backend
- **Products:** Data produk real dari endpoint `/stocks`
- **Cart Management:** Sinkron dengan database Go via API
- **Checkout:** Proses pembayaran via Midtrans
- **Webhooks:** Update status transaksi otomatis
- **Transaction History:** Data real dari Go database

Semua fitur customer sudah 100% lengkap dan siap production! 