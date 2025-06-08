@extends('layouts.customer')

@section('title', 'Dashboard Customer')

@section('content')
<div class="page-header">
    <h1 class="page-title">Dashboard Customer</h1>
    <p class="page-subtitle">Selamat datang di sistem belanja Artha Minyak</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="card">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-full">
                <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold">Keranjang</h3>
                <p class="text-gray-600" id="cart-items-count">0 items</p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-full">
                <i class="fas fa-receipt text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold">Transaksi</h3>
                <p class="text-gray-600" id="transactions-count">0 transaksi</p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-full">
                <i class="fas fa-credit-card text-yellow-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold">Total Belanja</h3>
                <p class="text-gray-600" id="total-spent">Rp 0</p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Sections -->
<div id="content-sections">
    <!-- Products Section -->
    <div id="products-section" class="content-section">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Produk Tersedia</h2>
            <div class="flex gap-2">
                <input type="text" id="search-products" placeholder="Cari produk..." class="px-4 py-2 border rounded-lg">
                <select id="sort-products" class="px-4 py-2 border rounded-lg">
                    <option value="name">Urutkan berdasarkan Nama</option>
                    <option value="price_low">Harga Terendah</option>
                    <option value="price_high">Harga Tertinggi</option>
                    <option value="stock">Stok Tersedia</option>
                </select>
            </div>
        </div>
        
        <div id="products-loading" class="loading">
            <div class="spinner"></div>
            <p>Memuat produk...</p>
        </div>
        
        <div id="products-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" style="display: none;">
            <!-- Products will be loaded here -->
        </div>
        
        <div id="no-products" class="text-center py-12" style="display: none;">
            <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600">Tidak Ada Produk</h3>
            <p class="text-gray-500">Belum ada produk yang tersedia saat ini</p>
        </div>
    </div>

    <!-- Cart Section -->
    <div id="cart-section" class="content-section" style="display: none;">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Keranjang Belanja</h2>
            <button onclick="clearCart()" class="btn btn-secondary">
                <i class="fas fa-trash"></i>
                Kosongkan Keranjang
            </button>
        </div>
        
        <div id="cart-loading" class="loading">
            <div class="spinner"></div>
            <p>Memuat keranjang...</p>
        </div>
        
        <div id="cart-content" style="display: none;">
            <div class="card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3">Produk</th>
                                <th class="text-center py-3">Harga</th>
                                <th class="text-center py-3">Jumlah</th>
                                <th class="text-center py-3">Subtotal</th>
                                <th class="text-center py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="cart-items">
                            <!-- Cart items will be loaded here -->
                        </tbody>
                    </table>
                </div>
                
                <div class="border-t pt-4 mt-4">
                    <div class="flex justify-between items-center text-xl font-semibold">
                        <span>Total:</span>
                        <span id="cart-total">Rp 0</span>
                    </div>
                    <button onclick="processCheckout()" class="btn btn-success w-full mt-4">
                        <i class="fas fa-credit-card"></i>
                        Bayar Sekarang
                    </button>
                </div>
            </div>
        </div>
        
        <div id="empty-cart" class="text-center py-12" style="display: none;">
            <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600">Keranjang Kosong</h3>
            <p class="text-gray-500">Belum ada produk dalam keranjang Anda</p>
            <button onclick="showSection('products')" class="btn btn-primary mt-4">
                <i class="fas fa-plus"></i>
                Mulai Belanja
            </button>
        </div>
    </div>

    <!-- Transactions Section -->
    <div id="transactions-section" class="content-section" style="display: none;">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Riwayat Transaksi</h2>
            <select id="filter-transactions" class="px-4 py-2 border rounded-lg">
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="success">Berhasil</option>
                <option value="failed">Gagal</option>
                <option value="expired">Kadaluarsa</option>
            </select>
        </div>
        
        <div id="transactions-loading" class="loading">
            <div class="spinner"></div>
            <p>Memuat transaksi...</p>
        </div>
        
        <div id="transactions-list" style="display: none;">
            <!-- Transactions will be loaded here -->
        </div>
        
        <div id="no-transactions" class="text-center py-12" style="display: none;">
            <i class="fas fa-receipt text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600">Belum Ada Transaksi</h3>
            <p class="text-gray-500">Anda belum memiliki riwayat transaksi</p>
        </div>
    </div>

    <!-- Profile Section -->
    <div id="profile-section" class="content-section" style="display: none;">
        <h2 class="text-xl font-semibold mb-6">Profil Saya</h2>
        
        <div class="card max-w-2xl">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Nama Lengkap</label>
                    <input type="text" value="{{ session('user')['name'] ?? '' }}" readonly class="w-full px-3 py-2 border rounded-lg bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Email</label>
                    <input type="email" value="{{ session('user')['email'] ?? '' }}" readonly class="w-full px-3 py-2 border rounded-lg bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Role</label>
                    <input type="text" value="{{ session('user')['role'] ?? '' }}" readonly class="w-full px-3 py-2 border rounded-lg bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Status</label>
                    <span class="inline-block px-3 py-2 bg-green-100 text-green-800 rounded-lg">Aktif</span>
                </div>
            </div>
            
            <div class="mt-6 pt-6 border-t">
                <h3 class="text-lg font-semibold mb-4">Statistik Akun</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600" id="profile-transactions">0</div>
                        <div class="text-sm text-gray-600">Transaksi</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600" id="profile-success">0</div>
                        <div class="text-sm text-gray-600">Berhasil</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600" id="profile-pending">0</div>
                        <div class="text-sm text-gray-600">Pending</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600" id="profile-total">Rp 0</div>
                        <div class="text-sm text-gray-600">Total Belanja</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Detail Modal -->
<div id="transaction-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 m-4 max-w-2xl w-full max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Detail Transaksi</h3>
            <button onclick="closeTransactionModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="transaction-detail-content">
            <!-- Transaction details will be loaded here -->
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .grid {
        display: grid;
    }
    .grid-cols-1 { grid-template-columns: repeat(1, 1fr); }
    .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
    .grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
    .grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
    .gap-4 { gap: 1rem; }
    .gap-6 { gap: 1.5rem; }
    
    @media (min-width: 768px) {
        .md\\:grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
        .md\\:grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
        .md\\:grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
    }
    
    @media (min-width: 1024px) {
        .lg\\:grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
    }
    
    @media (min-width: 1280px) {
        .xl\\:grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
    }
    
    .product-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid #e5e7eb;
    }
    
    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .product-image {
        width: 100%;
        height: 200px;
        background: linear-gradient(45deg, #f3f4f6, #e5e7eb);
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        color: #9ca3af;
        font-size: 3rem;
    }
    
    .transaction-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .transaction-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-color: #3b82f6;
    }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-success { background: #d1fae5; color: #065f46; }
    .status-failed { background: #fee2e2; color: #991b1b; }
    .status-expired { background: #f3f4f6; color: #374151; }
</style>
@endpush

@push('scripts')
<script>
    // Global state
    let currentProducts = [];
    let currentCart = [];
    let currentTransactions = [];
    
    // Initialize dashboard
    document.addEventListener('DOMContentLoaded', function() {
        setupNavigation();
        loadDashboardData();
        showSection('products');
    });
    
    // Navigation setup
    function setupNavigation() {
        document.querySelectorAll('[data-section]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const section = this.dataset.section;
                showSection(section);
                
                // Update active state
                document.querySelectorAll('.nav-item').forEach(item => {
                    item.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
    }
    
    // Show section
    function showSection(sectionName) {
        // Hide all sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.style.display = 'none';
        });
        
        // Show selected section
        const targetSection = document.getElementById(sectionName + '-section');
        if (targetSection) {
            targetSection.style.display = 'block';
            
            // Load data based on section
            switch(sectionName) {
                case 'products':
                    loadProducts();
                    break;
                case 'cart':
                    loadCart();
                    break;
                case 'transactions':
                    loadTransactions();
                    break;
                case 'profile':
                    loadProfileStats();
                    break;
            }
        }
    }
    
    // Load dashboard data
    async function loadDashboardData() {
        try {
            // Load cart count
            const cartData = await apiCall('/customer/cart');
            if (cartData.status === 'success') {
                updateCartCount(cartData.cartItems.length);
            }
            
            // Load transactions count
            const transactionsData = await apiCall('/customer/transactions/summary');
            if (transactionsData.status === 'success') {
                updateTransactionsCount(transactionsData.transactions.length);
                updateTotalSpent(transactionsData.transactions);
            }
        } catch (error) {
            console.error('Error loading dashboard data:', error);
        }
    }
    
    // Load products
    async function loadProducts() {
        const loadingEl = document.getElementById('products-loading');
        const gridEl = document.getElementById('products-grid');
        const noProductsEl = document.getElementById('no-products');
        
        loadingEl.style.display = 'flex';
        gridEl.style.display = 'none';
        noProductsEl.style.display = 'none';
        
        try {
            const data = await apiCall('/customer/products');
            
            if (data.status === 'success' && data.products.length > 0) {
                currentProducts = data.products;
                renderProducts(data.products);
                gridEl.style.display = 'grid';
            } else {
                noProductsEl.style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading products:', error);
            noProductsEl.style.display = 'block';
        } finally {
            loadingEl.style.display = 'none';
        }
    }
    
    // Render products
    function renderProducts(products) {
        const gridEl = document.getElementById('products-grid');
        gridEl.innerHTML = '';
        
        products.forEach(product => {
            const productEl = document.createElement('div');
            productEl.className = 'product-card';
            productEl.innerHTML = `
                <div class="product-image">
                    <i class="fas fa-oil-can"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">${product.product_name}</h3>
                <p class="text-gray-600 text-sm mb-3">${product.description}</p>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-xl font-bold text-blue-600">${formatCurrency(product.price)}</span>
                    <span class="text-sm text-gray-500">Stok: ${product.stock}</span>
                </div>
                <div class="flex gap-2">
                    <input type="number" min="1" max="${product.stock}" value="1" 
                           class="flex-1 px-3 py-2 border rounded-lg text-center" 
                           id="qty-${product.product_id}">
                    <button onclick="addToCart(${product.product_id}, '${product.product_name}', ${product.price})" 
                            class="btn btn-primary"
                            ${product.stock === 0 ? 'disabled' : ''}>
                        <i class="fas fa-cart-plus"></i>
                        ${product.stock === 0 ? 'Habis' : 'Tambah'}
                    </button>
                </div>
            `;
            gridEl.appendChild(productEl);
        });
    }
    
    // Add to cart
    async function addToCart(productId, productName, price) {
        try {
            const qtyInput = document.getElementById(`qty-${productId}`);
            const quantity = parseInt(qtyInput.value) || 1;
            
            const data = await apiCall('/customer/cart', {
                method: 'POST',
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            });
            
            if (data.status === 'success') {
                showAlert('success', `${productName} berhasil ditambahkan ke keranjang!`);
                qtyInput.value = 1;
                loadDashboardData(); // Refresh cart count
            } else {
                showAlert('error', data.message || 'Gagal menambahkan produk ke keranjang');
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            showAlert('error', 'Terjadi kesalahan saat menambahkan produk ke keranjang');
        }
    }
    
    // Load cart
    async function loadCart() {
        const loadingEl = document.getElementById('cart-loading');
        const contentEl = document.getElementById('cart-content');
        const emptyEl = document.getElementById('empty-cart');
        
        loadingEl.style.display = 'flex';
        contentEl.style.display = 'none';
        emptyEl.style.display = 'none';
        
        try {
            const data = await apiCall('/customer/cart');
            
            if (data.status === 'success' && data.cartItems.length > 0) {
                currentCart = data.cartItems;
                renderCart(data.cartItems);
                contentEl.style.display = 'block';
            } else {
                emptyEl.style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading cart:', error);
            emptyEl.style.display = 'block';
        } finally {
            loadingEl.style.display = 'none';
        }
    }
    
    // Render cart
    function renderCart(cartItems) {
        const itemsEl = document.getElementById('cart-items');
        const totalEl = document.getElementById('cart-total');
        
        itemsEl.innerHTML = '';
        let total = 0;
        
        cartItems.forEach(item => {
            const subtotal = item.quantity * item.product.price;
            total += subtotal;
            
            const row = document.createElement('tr');
            row.className = 'border-b';
            row.innerHTML = `
                <td class="py-3">
                    <div class="font-semibold">${item.product.product_name}</div>
                </td>
                <td class="text-center py-3">${formatCurrency(item.product.price)}</td>
                <td class="text-center py-3">
                    <div class="flex justify-center items-center gap-2">
                        <button onclick="updateCartQuantity(${item.cart_id}, ${item.quantity - 1})" 
                                class="btn btn-secondary btn-sm" ${item.quantity <= 1 ? 'disabled' : ''}>
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="mx-2">${item.quantity}</span>
                        <button onclick="updateCartQuantity(${item.cart_id}, ${item.quantity + 1})" 
                                class="btn btn-secondary btn-sm">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </td>
                <td class="text-center py-3">${formatCurrency(subtotal)}</td>
                <td class="text-center py-3">
                    <button onclick="removeFromCart([${item.cart_id}])" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            itemsEl.appendChild(row);
        });
        
        totalEl.textContent = formatCurrency(total);
    }
    
    // Remove from cart
    async function removeFromCart(cartIds) {
        if (!confirm('Hapus item dari keranjang?')) return;
        
        try {
            const data = await apiCall('/customer/cart', {
                method: 'DELETE',
                body: JSON.stringify({ cart_ids: cartIds })
            });
            
            if (data.status === 'success') {
                showAlert('success', 'Item berhasil dihapus dari keranjang!');
                loadCart();
                loadDashboardData();
            } else {
                showAlert('error', data.message || 'Gagal menghapus item dari keranjang');
            }
        } catch (error) {
            console.error('Error removing from cart:', error);
            showAlert('error', 'Terjadi kesalahan saat menghapus item');
        }
    }
    
    // Process checkout
    async function processCheckout() {
        if (currentCart.length === 0) {
            showAlert('warning', 'Keranjang kosong!');
            return;
        }
        
        try {
            const items = currentCart.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity,
                price: item.product.price,
                name: item.product.product_name
            }));
            
            const data = await apiCall('/customer/checkout', {
                method: 'POST',
                body: JSON.stringify({ items: items })
            });
            
            if (data.status === 'success' && data.token) {
                // Open Midtrans Snap
                snap.pay(data.token, {
                    onSuccess: function(result) {
                        showAlert('success', 'Pembayaran berhasil!');
                        loadCart();
                        loadDashboardData();
                        showSection('transactions');
                    },
                    onPending: function(result) {
                        showAlert('info', 'Pembayaran menunggu konfirmasi.');
                        showSection('transactions');
                    },
                    onError: function(result) {
                        showAlert('error', 'Pembayaran gagal!');
                    },
                    onClose: function() {
                        console.log('Payment popup closed');
                    }
                });
            } else {
                showAlert('error', data.message || 'Gagal memproses checkout');
            }
        } catch (error) {
            console.error('Checkout error:', error);
            showAlert('error', 'Terjadi kesalahan saat memproses pembayaran');
        }
    }
    
    // Load transactions
    async function loadTransactions() {
        const loadingEl = document.getElementById('transactions-loading');
        const listEl = document.getElementById('transactions-list');
        const noTransactionsEl = document.getElementById('no-transactions');
        
        loadingEl.style.display = 'flex';
        listEl.style.display = 'none';
        noTransactionsEl.style.display = 'none';
        
        try {
            const data = await apiCall('/customer/transactions/summary');
            
            if (data.status === 'success' && data.transactions.length > 0) {
                currentTransactions = data.transactions;
                renderTransactions(data.transactions);
                listEl.style.display = 'block';
            } else {
                noTransactionsEl.style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading transactions:', error);
            noTransactionsEl.style.display = 'block';
        } finally {
            loadingEl.style.display = 'none';
        }
    }
    
    // Render transactions
    function renderTransactions(transactions) {
        const listEl = document.getElementById('transactions-list');
        listEl.innerHTML = '';
        
        transactions.forEach(transaction => {
            const card = document.createElement('div');
            card.className = 'transaction-card';
            card.onclick = () => viewTransactionDetails(transaction.transaction_id);
            card.innerHTML = `
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold text-lg">Transaksi #${transaction.transaction_id}</h3>
                        <p class="text-gray-600">${formatDate(transaction.date)}</p>
                        <p class="text-sm text-gray-500 mt-1">${transaction.items_summary}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-xl font-bold text-blue-600">${formatCurrency(transaction.total_price)}</div>
                        <span class="status-badge status-${transaction.status.toLowerCase()}">${transaction.status}</span>
                    </div>
                </div>
            `;
            listEl.appendChild(card);
        });
    }
    
    // View transaction details
    async function viewTransactionDetails(transactionId) {
        try {
            const data = await apiCall(`/customer/transactions/detail?id=${transactionId}`);
            
            if (data.status === 'success') {
                renderTransactionDetails(transactionId, data.transaction_details);
                document.getElementById('transaction-modal').classList.remove('hidden');
                document.getElementById('transaction-modal').classList.add('flex');
            } else {
                showAlert('error', 'Gagal memuat detail transaksi');
            }
        } catch (error) {
            console.error('Error loading transaction details:', error);
            showAlert('error', 'Terjadi kesalahan saat memuat detail transaksi');
        }
    }
    
    // Render transaction details
    function renderTransactionDetails(transactionId, details) {
        const contentEl = document.getElementById('transaction-detail-content');
        
        let total = 0;
        let itemsHtml = '';
        
        details.forEach(detail => {
            const subtotal = detail.quantity * detail.price;
            total += subtotal;
            
            itemsHtml += `
                <tr class="border-b">
                    <td class="py-2">${detail.product.product_name}</td>
                    <td class="text-center py-2">${detail.quantity}</td>
                    <td class="text-right py-2">${formatCurrency(detail.price)}</td>
                    <td class="text-right py-2">${formatCurrency(subtotal)}</td>
                </tr>
            `;
        });
        
        contentEl.innerHTML = `
            <div class="mb-4">
                <h4 class="font-semibold text-lg">Transaksi #${transactionId}</h4>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="text-left py-3 px-2">Produk</th>
                            <th class="text-center py-3 px-2">Jumlah</th>
                            <th class="text-right py-3 px-2">Harga</th>
                            <th class="text-right py-3 px-2">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 pt-4 border-t">
                <div class="flex justify-between items-center text-xl font-bold">
                    <span>Total:</span>
                    <span>${formatCurrency(total)}</span>
                </div>
            </div>
        `;
    }
    
    // Close transaction modal
    function closeTransactionModal() {
        document.getElementById('transaction-modal').classList.add('hidden');
        document.getElementById('transaction-modal').classList.remove('flex');
    }
    
    // Update cart count
    function updateCartCount(count) {
        document.getElementById('cart-count').textContent = count;
        document.getElementById('cart-items-count').textContent = `${count} items`;
    }
    
    // Update transactions count
    function updateTransactionsCount(count) {
        document.getElementById('transactions-count').textContent = `${count} transaksi`;
    }
    
    // Update total spent
    function updateTotalSpent(transactions) {
        const total = transactions.reduce((sum, tx) => sum + tx.total_price, 0);
        document.getElementById('total-spent').textContent = formatCurrency(total);
    }
    
    // Show alert
    function showAlert(type, message) {
        const alertEl = document.createElement('div');
        alertEl.className = `alert alert-${type}`;
        alertEl.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            ${message}
        `;
        
        const mainContent = document.querySelector('.main-content');
        mainContent.insertBefore(alertEl, mainContent.firstChild);
        
        setTimeout(() => {
            alertEl.style.opacity = '0';
            setTimeout(() => alertEl.remove(), 300);
        }, 5000);
    }
</script>
@endpush 