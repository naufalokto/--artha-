@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-blue-600 text-white">
        <div class="p-4">
            <h2 class="text-xl font-bold mb-4">{{ session('user')['name'] }}</h2>
            <nav>
                <ul class="space-y-2">
                    <li>
                        <a href="#" onclick="showSection('dashboard')" class="flex items-center p-2 hover:bg-blue-700 rounded">
                            <i class="fas fa-box mr-2"></i>
                            <span>Produk</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="showSection('cart')" class="flex items-center p-2 hover:bg-blue-700 rounded">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            <span>Keranjang</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="showSection('transactions')" class="flex items-center p-2 hover:bg-blue-700 rounded">
                            <i class="fas fa-history mr-2"></i>
                            <span>Transaksi</span>
                        </a>
                    </li>
                    <li>
                        <a href="/api/logout" class="flex items-center p-2 bg-red-500 hover:bg-red-600 rounded">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
        <!-- Dashboard/Products Section -->
        <section id="dashboard-section">
            <h2 class="text-2xl font-bold mb-4">Produk Tersedia</h2>
            <div id="loading-products" class="text-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-4 text-gray-600">Memuat produk...</p>
            </div>
            <div id="products-grid" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Products will be loaded here -->
            </div>
        </section>

        <!-- Cart Section -->
        <section id="cart-section" class="hidden">
            <div class="mb-6">
                <h2 class="text-2xl font-bold mb-4">Keranjang Belanja</h2>
                <div id="loading-cart" class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-4 text-gray-600">Memuat keranjang...</p>
                </div>
                <div id="empty-cart" class="hidden text-center py-8">
                    <p class="text-gray-600">Keranjang belanja kosong</p>
                    <button onclick="showSection('dashboard')" class="mt-4 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                        Lihat Produk
                    </button>
                </div>
                <div id="cart-content" class="hidden">
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3">Produk</th>
                                    <th class="text-right py-3">Harga</th>
                                    <th class="text-center py-3">Jumlah</th>
                                    <th class="text-right py-3">Subtotal</th>
                                    <th class="text-center py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="cart-list">
                                <!-- Cart items will be loaded here -->
                            </tbody>
                            <tfoot class="border-t">
                                <tr>
                                    <td colspan="3" class="py-4 text-right font-bold">Total:</td>
                                    <td class="py-4 text-right font-bold" id="total-amount">Rp 0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="text-right">
                        <button onclick="processCheckout()" class="bg-green-600 text-white py-3 px-6 rounded-lg hover:bg-green-700">
                            <i class="fas fa-shopping-cart mr-2"></i>Checkout
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Transactions Section -->
        <section id="transactions-section" class="hidden">
            <h2 class="text-2xl font-bold mb-4">Riwayat Transaksi</h2>
            <div id="loading-transactions" class="text-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-4 text-gray-600">Memuat transaksi...</p>
            </div>
            <div id="empty-transactions" class="hidden text-center py-8">
                <p class="text-gray-600">Belum ada transaksi</p>
            </div>
            <div id="transactions-content" class="hidden space-y-4">
                <!-- Transaction items will be loaded here -->
            </div>
        </section>
    </main>
</div>

<!-- Transaction Detail Modal -->
<div class="fixed inset-0 bg-black bg-opacity-50 hidden" id="modal-bg">
    <div class="bg-white rounded-lg max-w-2xl mx-auto mt-20 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Detail Transaksi</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="modal-content">
            <!-- Transaction details will be loaded here -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentCart = [];
    let currentSection = 'dashboard';

    document.addEventListener('DOMContentLoaded', function() {
        loadProducts();
        showSection('dashboard');
    });

    function showSection(section) {
        document.getElementById('dashboard-section').classList.add('hidden');
        document.getElementById('cart-section').classList.add('hidden');
        document.getElementById('transactions-section').classList.add('hidden');

        document.getElementById(section + '-section').classList.remove('hidden');
        
        if (section === 'dashboard') {
            loadProducts();
        } else if (section === 'cart') {
            loadCart();
        } else if (section === 'transactions') {
            loadTransactions();
        }
        
        currentSection = section;
    }

    function loadProducts() {
        const loadingEl = document.getElementById('loading-products');
        const productsGrid = document.getElementById('products-grid');
        
        loadingEl.classList.remove('hidden');
        productsGrid.classList.add('hidden');
        productsGrid.innerHTML = '';

        const token = getToken();
        if (!token) {
            alert('Silakan login terlebih dahulu!');
            window.location.href = '/login';
            return;
        }

        fetch('http://localhost:9090/stocks', {
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        })
        .then(res => {
            if (!res.ok) {
                if (res.status === 401 || res.status === 403) {
                    alert('Session expired, silakan login ulang!');
                    localStorage.removeItem('token');
                    window.location.href = '/login';
                    return;
                }
                throw new Error('Gagal memuat produk');
            }
            return res.json();
        })
        .then(data => {
            loadingEl.classList.add('hidden');
            productsGrid.classList.remove('hidden');

            if (Array.isArray(data)) {
                data.forEach(product => {
                    const card = document.createElement('div');
                    card.className = 'bg-white rounded-lg shadow-md p-6';
                    card.innerHTML = `
                        <h3 class="text-lg font-semibold mb-2">${product.product_name}</h3>
                        <p class="text-gray-600 mb-4">${product.description || 'No description available'}</p>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-lg font-bold">Rp ${product.price.toLocaleString('id-ID')}</span>
                            <span class="text-sm text-gray-500">Stock: ${product.quantity}</span>
                        </div>
                        <button onclick="addToCart(${product.product_id})" 
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition-colors"
                                ${product.quantity < 1 ? 'disabled' : ''}>
                            ${product.quantity < 1 ? 'Stok Habis' : 'Tambah ke Keranjang'}
                        </button>
                    `;
                    productsGrid.appendChild(card);
                });
            } else {
                productsGrid.innerHTML = '<p class="text-center text-gray-600">Tidak ada produk tersedia</p>';
            }
        })
        .catch(err => {
            console.error('Error loading products:', err);
            loadingEl.classList.add('hidden');
            productsGrid.classList.remove('hidden');
            productsGrid.innerHTML = '<p class="text-center text-red-600">Gagal memuat produk</p>';
        });
    }

    function addToCart(productId, qty = 1) {
        const token = getToken();
        if (!token) {
            alert('Silakan login terlebih dahulu!');
            window.location.href = '/login';
            return;
        }
        fetch('http://localhost:9090/customer/cart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: qty
            })
        })
        .then(res => {
            if (!res.ok) {
                if (res.status === 401 || res.status === 403) {
                    alert('Session expired, silakan login ulang!');
                    localStorage.removeItem('token');
                    window.location.href = '/login';
                    return;
                }
                throw new Error('Gagal menambahkan ke keranjang');
            }
            return res.json();
        })
        .then(data => {
            alert('Produk berhasil ditambahkan ke keranjang: ' + (data.product?.product_name || ''));
            loadCart();
        })
        .catch(err => {
            alert(err.message);
        });
    }

    // Ambil token dari localStorage
    function getToken() {
        return localStorage.getItem('token');
    }

    function loadCart() {
        const loadingEl = document.getElementById('loading-cart');
        const cartItems = document.getElementById('cart-items');
        const cartTotal = document.getElementById('cart-total');
        if (!loadingEl || !cartItems || !cartTotal) return;
        loadingEl.classList.remove('hidden');
        cartItems.innerHTML = '';

        const token = getToken();
        if (!token) {
            alert('Silakan login terlebih dahulu!');
            window.location.href = '/login';
            return;
        }

        fetch('http://localhost:9090/customer/cart', {
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        })
        .then(res => {
            if (!res.ok) {
                if (res.status === 401 || res.status === 403) {
                    alert('Session expired, silakan login ulang!');
                    localStorage.removeItem('token');
                    window.location.href = '/login';
                    return Promise.reject('Unauthorized');
                }
                return res.text().then(text => { throw new Error(text || 'Gagal memuat keranjang'); });
            }
            return res.json();
        })
        .then(data => {
            loadingEl.classList.add('hidden');
            if (!Array.isArray(data) || !data.length) {
                cartItems.innerHTML = '<li class="text-center text-gray-600 py-4">Keranjang kosong</li>';
                cartTotal.textContent = 'Rp 0';
                document.getElementById('checkout-btn').disabled = true;
                return;
            }
            let total = 0;
            data.forEach(item => {
                const product = item.product || {};
                const itemTotal = (product.price || 0) * item.quantity;
                total += itemTotal;
                const li = document.createElement('li');
                li.className = 'flex justify-between items-center py-2';
                li.innerHTML = `
                    <div>
                        <span class="font-medium">${product.product_name || '-'} </span>
                        <span class="text-gray-600"> x ${item.quantity}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="mr-4">Rp ${itemTotal.toLocaleString('id-ID')}</span>
                        <button onclick="removeFromCart(${item.cart_id})" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                    </div>
                `;
                cartItems.appendChild(li);
            });
            cartTotal.textContent = `Rp ${total.toLocaleString('id-ID')}`;
            document.getElementById('checkout-btn').disabled = false;
        })
        .catch(err => {
            loadingEl.classList.add('hidden');
            cartItems.innerHTML = '<li class="text-center text-red-600 py-4">' + (err.message || 'Gagal memuat keranjang') + '</li>';
            cartTotal.textContent = 'Rp 0';
            document.getElementById('checkout-btn').disabled = true;
        });
    }

    function removeFromCart(cartId) {
        if (!confirm('Apakah Anda yakin ingin menghapus item ini dari keranjang?')) {
            return;
        }
        const token = getToken();
        if (!token) {
            alert('Silakan login terlebih dahulu!');
            window.location.href = '/login';
            return;
        }
        fetch('http://localhost:9090/customer/cart', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({
                cart_ids: [cartId]
            })
        })
        .then(res => {
            if (!res.ok) {
                if (res.status === 401 || res.status === 403) {
                    alert('Session expired, silakan login ulang!');
                    localStorage.removeItem('token');
                    window.location.href = '/login';
                    return;
                }
                throw new Error('Gagal menghapus item dari keranjang');
            }
            // Biasanya response kosong, tetap reload cart
            return res.text();
        })
        .then(() => {
            alert('Item berhasil dihapus dari keranjang');
            loadCart();
        })
        .catch(err => {
            alert(err.message);
        });
    }

    // Process checkout
    function processCheckout() {
        if (currentCart.length === 0) {
            alert('Keranjang kosong!');
            return;
        }

        const token = getToken();
        if (!token) {
            alert('Silakan login terlebih dahulu!');
            window.location.href = '/login';
            return;
        }

        const items = currentCart.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            price: item.product.price,
            name: item.product.product_name
        }));

        fetch('/customer/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ items: items })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && data.token) {
                snap.pay(data.token, {
                    onSuccess: function(result) {
                        alert('Pembayaran berhasil!');
                        loadCart();
                        showSection('transactions');
                    },
                    onPending: function(result) {
                        alert('Pembayaran menunggu konfirmasi.');
                        showSection('transactions');
                    },
                    onError: function(result) {
                        alert('Pembayaran gagal!');
                    },
                    onClose: function() {
                        console.log('Payment popup closed');
                    }
                });
            } else {
                alert(data.message || 'Gagal memproses checkout');
            }
        })
        .catch(err => {
            console.error('Error processing checkout:', err);
            alert('Terjadi kesalahan saat memproses pembayaran');
        });
    }

    // Load transactions
    function loadTransactions() {
        const loadingEl = document.getElementById('loading-transactions');
        const transactionsList = document.getElementById('transactions-list');
        if (!loadingEl || !transactionsList) return;
        loadingEl.classList.remove('hidden');
        transactionsList.innerHTML = '';

        const token = getToken();
        if (!token) {
            alert('Silakan login terlebih dahulu!');
            window.location.href = '/login';
            return;
        }

        let url = '';
        let isLaravel = false;
        if (window.location.port === '8000') {
            url = '/transactions/summary';
            isLaravel = true;
        } else {
            url = 'http://localhost:9090/customer/transactions/summary';
        }

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                ...(isLaravel ? {} : {'Authorization': 'Bearer ' + token})
            }
        })
        .then(res => {
            if (!res.ok) {
                if (res.status === 401 || res.status === 403) {
                    alert('Session expired, silakan login ulang!');
                    localStorage.removeItem('token');
                    window.location.href = '/login';
                    return Promise.reject('Unauthorized');
                }
                return res.text().then(text => { throw new Error(text || 'Gagal memuat riwayat transaksi'); });
            }
            return res.json();
        })
        .then(data => {
            let items = [];
            if (isLaravel) {
                if (data && data.status === 'success' && Array.isArray(data.data)) {
                    items = data.data;
                }
            } else if (Array.isArray(data)) {
                items = data;
            }
            loadingEl.classList.add('hidden');
            if (!items.length) {
                transactionsList.innerHTML = '<li class="text-center text-gray-600 py-4">Belum ada transaksi</li>';
                return;
            }
            items.forEach(transaction => {
                const li = document.createElement('li');
                li.className = 'bg-white rounded-lg shadow-md p-4 mb-4';
                li.innerHTML = `
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-medium">Order #${transaction.id || transaction.transaction_id || '-'}</span>
                        <span class="text-sm ${getStatusColor(transaction.status)}">${transaction.status}</span>
                            </div>
                    <div class="text-gray-600 mb-2">
                        ${transaction.created_at ? new Date(transaction.created_at).toLocaleString('id-ID') : '-'}
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-bold">Rp ${(transaction.total_amount || 0).toLocaleString('id-ID')}</span>
                        <button onclick="viewTransactionDetail('${transaction.id || transaction.transaction_id}')" class="text-blue-600 hover:text-blue-800">Detail</button>
                    </div>
                `;
                transactionsList.appendChild(li);
            });
        })
        .catch(err => {
            loadingEl.classList.add('hidden');
            transactionsList.innerHTML = '<li class="text-center text-red-600 py-4">' + (err.message || 'Gagal memuat riwayat transaksi') + '</li>';
        });
    }

    function getStatusColor(status) {
        switch (status.toLowerCase()) {
            case 'success':
                return 'text-green-600';
            case 'pending':
                return 'text-yellow-600';
            case 'failed':
                return 'text-red-600';
            default:
                return 'text-gray-600';
        }
    }

    function viewTransactionDetail(transactionId) {
        const token = getToken();
        if (!token) {
            alert('Silakan login terlebih dahulu!');
            window.location.href = '/login';
            return;
        }

        fetch(`http://localhost:9090/customer/transactions/detail?id=${transactionId}`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        })
        .then(res => {
            if (!res.ok) {
                if (res.status === 401 || res.status === 403) {
                    alert('Session expired, silakan login ulang!');
                    localStorage.removeItem('token');
                    window.location.href = '/login';
                    return;
                }
                throw new Error('Gagal memuat detail transaksi');
            }
            return res.json();
        })
        .then(data => {
            // Tampilkan detail transaksi dalam modal
            const modal = document.getElementById('transaction-detail-modal');
            const modalContent = document.getElementById('transaction-detail-content');
            
            modalContent.innerHTML = `
                <div class="p-4">
                    <h3 class="text-lg font-semibold mb-4">Detail Transaksi #${data.id}</h3>
                    <div class="space-y-2">
                        <p><span class="font-medium">Status:</span> ${data.status}</p>
                        <p><span class="font-medium">Tanggal:</span> ${new Date(data.created_at).toLocaleString('id-ID')}</p>
                        <p><span class="font-medium">Total:</span> Rp ${data.total_amount.toLocaleString('id-ID')}</p>
                        <div class="mt-4">
                            <h4 class="font-medium mb-2">Items:</h4>
                            <ul class="space-y-2">
                                ${data.items.map(item => `
                                    <li class="flex justify-between">
                                        <span>${item.product_name} x ${item.quantity}</span>
                                        <span>Rp ${(item.price * item.quantity).toLocaleString('id-ID')}</span>
                                    </li>
                                `).join('')}
                            </ul>
                            </div>
                        </div>
                    </div>
                `;
            
            modal.classList.remove('hidden');
        })
        .catch(err => {
            console.error('Error loading transaction detail:', err);
            alert(err.message);
        });
    }

    // Close modal
    function closeModal() {
        document.getElementById('modal-bg').classList.add('hidden');
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('id-ID').format(price);
    }

    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `fixed top-4 right-4 p-4 rounded-lg ${
            type === 'error' ? 'bg-red-500' : 'bg-green-500'
        } text-white`;
        alertDiv.textContent = message;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 3000);
    }

    function logoutCustomer() {
        localStorage.removeItem('token');
        window.location.href = '/logout';
    }
</script>
@endpush