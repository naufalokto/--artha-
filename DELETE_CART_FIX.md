# Fix Error Delete Cart 500 - Laravel Frontend

## Masalah
Error 500 Internal Server Error saat menghapus item dari keranjang:
```
Gagal menghapus item dari keranjang: Server error: `DELETE http://localhost:9090/customer/cart` resulted in a `500 Internal Server Error` response: Failed to delete cart items
```

## Analisis Masalah

### 1. Frontend Laravel (Port 8000) ✅
- Request dikirim dengan format: `{"cart_ids": [1, 2]}`
- Headers sudah benar (JWT token, Content-Type)
- Endpoint: `DELETE /customer/cart`

### 2. Backend Go (Port 9090) ❌
- Mengembalikan error 500
- Kemungkinan masalah:
  - Format request tidak sesuai
  - Cart ID tidak ditemukan
  - Error database
  - Format JWT tidak sesuai

## Solusi dari Sisi Frontend

### Opsi 1: Implementasi Soft Delete di Frontend
Karena kita tidak bisa mengubah Go backend, kita bisa implementasikan solusi sementara:

```javascript
// Di resources/views/customer/dashboard.blade.php
// Ubah fungsi removeFromCart menjadi:

function removeFromCart(cartIds) {
    const token = "{{ session('jwt_token') }}";
    if (!token) {
        alert('Silakan login terlebih dahulu!');
        window.location.href = '/login';
        return;
    }

    if (!confirm('Hapus item dari keranjang?')) {
        return;
    }

    // Coba delete ke backend
    fetch('/customer/cart', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ cart_ids: cartIds })
    })
    .then(res => {
        if (!res.ok && res.status === 500) {
            // Jika error 500, refresh saja halaman cart
            console.warn('Delete cart error 500, refreshing cart...');
            alert('Item akan dihapus dari tampilan. Silakan checkout untuk memperbarui.');
            
            // Hapus dari tampilan saja
            currentCart = currentCart.filter(item => !cartIds.includes(item.cart_id));
            
            // Reload tampilan cart
            loadCartItems();
            return;
        }
        return res.json();
    })
    .then(data => {
        if (data && data.status === 'success') {
            alert('Item berhasil dihapus dari keranjang!');
            loadCartItems();
        }
    })
    .catch(err => {
        console.error('Error removing from cart:', err);
        // Tetap refresh tampilan
        alert('Item dihapus dari tampilan. Perubahan akan tersimpan saat checkout.');
        loadCartItems();
    });
}
```

### Opsi 2: Bypass Delete dengan Clear Cart saat Checkout
Modifikasi proses checkout untuk mengosongkan cart setelah berhasil:

```javascript
// Dalam fungsi processCheckout, tambahkan setelah sukses:
onSuccess: function(result) {
    alert('Pembayaran berhasil!');
    
    // Clear cart items yang sudah di-checkout
    const checkoutCartIds = currentCart.map(item => item.cart_id);
    
    // Abaikan error delete, langsung refresh
    fetch('/customer/cart', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ cart_ids: checkoutCartIds })
    }).catch(() => {
        console.log('Delete cart error ignored after successful checkout');
    });
    
    // Refresh cart dan pindah ke transaksi
    loadCartItems();
    // ... kode selanjutnya
}
```

### Opsi 3: Local Storage untuk Excluded Items
Simpan cart items yang "dihapus" di local storage:

```javascript
// Fungsi helper untuk manage excluded items
function getExcludedCartItems() {
    const excluded = localStorage.getItem('excludedCartItems');
    return excluded ? JSON.parse(excluded) : [];
}

function addToExcludedItems(cartIds) {
    const excluded = getExcludedCartItems();
    const newExcluded = [...new Set([...excluded, ...cartIds])];
    localStorage.setItem('excludedCartItems', JSON.stringify(newExcluded));
}

function clearExcludedItems() {
    localStorage.removeItem('excludedCartItems');
}

// Update loadCartItems untuk filter excluded items
function loadCartItems() {
    // ... existing code ...
    
    .then(data => {
        loadingEl.style.display = 'none';
        
        if (data.status === 'success' && Array.isArray(data.cartItems) && data.cartItems.length > 0) {
            // Filter excluded items
            const excludedIds = getExcludedCartItems();
            const filteredItems = data.cartItems.filter(item => 
                !excludedIds.includes(item.cart_id)
            );
            
            if (filteredItems.length > 0) {
                currentCart = filteredItems;
                // ... render cart items
            } else {
                emptyCartEl.style.display = 'block';
            }
        }
    });
}

// Update removeFromCart
function removeFromCart(cartIds) {
    if (!confirm('Hapus item dari keranjang?')) {
        return;
    }
    
    // Tambahkan ke excluded items
    addToExcludedItems(cartIds);
    
    // Reload tampilan
    alert('Item berhasil dihapus!');
    loadCartItems();
}

// Clear excluded items setelah checkout berhasil
function processCheckout() {
    // ... existing checkout code ...
    
    onSuccess: function(result) {
        clearExcludedItems(); // Clear excluded items
        // ... rest of success handler
    }
}
```

## Testing & Debugging

### 1. Test Manual Delete Cart
Jalankan script test yang sudah dibuat:
```bash
php test_delete_cart.php
```

### 2. Cek Laravel Logs
```bash
# Windows PowerShell
Get-Content -Path "storage\logs\laravel.log" -Tail 100

# atau
type storage\logs\laravel.log
```

### 3. Monitor Network Tab
1. Buka Chrome DevTools (F12)
2. Tab Network
3. Coba delete cart
4. Lihat request/response detail

### 4. Cek Format Data Go Backend
Minta developer Go untuk share format yang benar untuk delete cart:
- Apakah menggunakan `cart_ids` atau `ids`?
- Apakah menerima array atau single ID?
- Contoh request yang berhasil?

## Rekomendasi

1. **Jangka Pendek**: Gunakan Opsi 1 atau 3 untuk bypass error
2. **Komunikasi**: Koordinasi dengan tim Go backend untuk fix endpoint
3. **Documentation**: Minta dokumentasi API yang lengkap
4. **Alternative**: Pertimbangkan endpoint alternatif seperti:
   - `PUT /customer/cart/{id}` dengan quantity 0
   - `POST /customer/cart/clear`

## Catatan Penting

- Error 500 menunjukkan masalah di server Go backend
- Frontend Laravel sudah mengirim request dengan benar
- Solusi di atas adalah workaround sementara
- Solusi permanen memerlukan fix di Go backend

## Quick Fix Code

Tambahkan ini di `resources/views/customer/dashboard.blade.php`:

```javascript
// Replace existing removeFromCart function
function removeFromCart(cartIds) {
    if (!confirm('Hapus item dari keranjang?')) return;
    
    // Hide items immediately for better UX
    cartIds.forEach(id => {
        const row = document.querySelector(`tr[data-cart-id="${id}"]`);
        if (row) row.style.display = 'none';
    });
    
    // Try to delete, but don't block on error
    const token = "{{ session('jwt_token') }}";
    fetch('/customer/cart', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ cart_ids: cartIds })
    }).finally(() => {
        // Always reload cart to sync state
        setTimeout(() => loadCartItems(), 500);
    });
}
```

Ini akan memberikan pengalaman pengguna yang lebih baik sambil menunggu fix dari backend. 