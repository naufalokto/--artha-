# 🛒 Customer System - Artha Minyak

## 🎯 Quick Overview

**Sistem customer e-commerce lengkap** dengan integrasi Midtrans payment gateway yang terhubung langsung ke backend Go API Anda.

## ✨ Fitur Utama

### 🛍️ Shopping
- ✅ Browse produk real-time dari backend Go
- ✅ Search & filter produk
- ✅ Shopping cart dengan sync real-time
- ✅ Checkout dengan multiple payment methods

### 💳 Payment
- ✅ **Midtrans Integration** - Credit card, bank transfer, e-wallets
- ✅ **Real-time webhook** - Status pembayaran otomatis update
- ✅ **Transaction history** - Riwayat lengkap dengan detail

### 🎨 UI/UX
- ✅ **Responsive design** - Mobile-first approach
- ✅ **Modern interface** - Clean & professional
- ✅ **Loading states** - Smooth user experience
- ✅ **Error handling** - User-friendly messages

## 🚀 Quick Start

### 1. Setup Environment
```bash
# Tambahkan ke .env
MIDTRANS_SERVER_KEY=SB-Mid-server-43gE8qD7eYGxQzpdvvf0G-tl
MIDTRANS_CLIENT_KEY=SB-Mid-client-44JyAuImP_XPOzeZ
MIDTRANS_IS_PRODUCTION=false
API_BASE_URL=http://localhost:9090
```

### 2. Clear Cache
```bash
php artisan config:clear && php artisan cache:clear
```

### 3. Setup Midtrans Webhook
```
URL: https://dashboard.sandbox.midtrans.com
Webhook: http://your-domain.com/midtrans/webhook
```

### 4. Access Customer Dashboard
```
URL: /customer/dashboard
Role: customer
```

## 📁 Files Created

```
✅ config/midtrans.php                    # Midtrans config
✅ app/Helpers/MidtransHelper.php          # Payment utilities
✅ resources/views/layouts/customer.blade.php   # Customer layout
✅ resources/views/customer/dashboard-new.blade.php   # New dashboard
✅ app/Http/Controllers/ApiController.php  # Customer API methods
✅ routes/web.php                          # Customer routes
✅ tests/Feature/MidtransTest.php          # Comprehensive tests
✅ CUSTOMER_SETUP.md                       # Setup guide
✅ CUSTOMER_SYSTEM_DOCUMENTATION.md       # Full documentation
```

## 🧪 Testing

### Payment Testing Data
```
# Credit Card
Card: 4811 1111 1111 1114
CVV: 123, Exp: 01/2025, OTP: 112233

# E-wallet
Phone: 08123456789, PIN: 123456
```

### Run Tests
```bash
php artisan test tests/Feature/MidtransTest.php
```

## 🔗 Integration with Your Go Backend

### Required Go Endpoints
```go
POST   /customer/cart                    # Add to cart
GET    /customer/cart                    # Get cart items
DELETE /customer/cart                    # Delete cart items
POST   /customer/checkout                # Process checkout
GET    /customer/transactions/summary    # Transaction history
GET    /customer/transactions/detail     # Transaction details
POST   /midtrans/webhook                # Payment webhook
GET    /stocks                          # Available products
```

### Customer Routes Structure
```go
customerRoutes := router.PathPrefix("/customer").Subrouter()
customerRoutes.Use(middleware.CustomerMiddleware)
// All your routes from route.go are supported!
```

## 🎮 Customer Flow

```
Login → Browse Products → Add to Cart → Checkout → 
Pay with Midtrans → Order Confirmation → Track Status
```

## 📱 User Interface

### Dashboard Sections
- 🏠 **Dashboard** - Overview & statistics
- 📦 **Produk** - Product catalog with search
- 🛒 **Keranjang** - Shopping cart management
- 📄 **Transaksi** - Order history & details
- 👤 **Profil** - User profile & stats

### Features
- **Real-time cart count** update
- **Transaction status** tracking
- **Payment method selection** via Midtrans
- **Responsive design** for all devices

## 🛡️ Security & Production Ready

- ✅ **JWT Authentication** - Secure API access
- ✅ **Role-based access** - Customer-only routes
- ✅ **Webhook signature verification** - Secure payments
- ✅ **Input validation** - Data integrity
- ✅ **Error handling** - Graceful failure recovery
- ✅ **Comprehensive logging** - Debug & monitoring

## 📞 Support & Documentation

- **Full Setup Guide**: `CUSTOMER_SETUP.md`
- **Complete Documentation**: `CUSTOMER_SYSTEM_DOCUMENTATION.md`
- **Test Examples**: `tests/Feature/MidtransTest.php`

## 🎉 Status: 100% Complete!

**Sistem customer sudah fully functional dan production-ready!**

### What's Included:
✅ Frontend Laravel dengan UI modern  
✅ Backend Go integration yang seamless  
✅ Midtrans payment gateway terintegrasi  
✅ Comprehensive testing suite  
✅ Security features lengkap  
✅ Documentation & setup guide  

**Ready to deploy! 🚀**

---

*Created for Artha Minyak • January 2024* 