<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Artha Makmur Jaya - Products</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-card {
            transition: transform 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .auth-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            width: 90%;
            max-width: 500px;
        }
        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        .loading {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1001;
        }
        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/" class="text-xl font-bold text-blue-600">
                            <i class="fas fa-oil-can mr-2"></i>
                            Artha Makmur Jaya
                        </a>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="/" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">Home</a>
                        <a href="/about" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">About</a>
                        <a href="/products" class="text-blue-600 px-3 py-2 rounded-md text-sm font-medium">Products</a>
                    </div>
                </div>
                <div class="flex items-center">
                    @if(session('user'))
                        <a href="/{{ strtolower(session('user')['role']) }}/dashboard" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                        <form method="POST" action="{{ route('auth.logout') }}" class="ml-4">
                            @csrf
                            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700">Logout</button>
                        </form>
                    @else
                        <a href="/login" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                        <a href="/signup" class="ml-4 bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Sign Up</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold mb-4">Our Products</h1>
            <p class="text-xl">Discover our high-quality oil and chemical products for your needs</p>
        </div>
    </div>

    <!-- Products Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Loading State -->
        <div id="loading" class="loading">
            <div class="loading-spinner"></div>
        </div>

        <!-- Search and Filter -->
        <div class="mb-8 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="relative flex-1 max-w-xs">
                <input type="text" 
                       id="searchInput" 
                       placeholder="Search products..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            <div class="flex gap-4">
                <select id="sortSelect" 
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="name">Sort by Name</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                    <option value="stock">Stock Available</option>
                </select>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="product-list">
            <!-- Products will be loaded here -->
        </div>

        <!-- Empty State -->
        <div id="empty-state" class="hidden text-center py-12">
            <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600">No Products Found</h3>
            <p class="text-gray-500">Try adjusting your search or filters</p>
        </div>
    </div>

    <!-- Auth Modal -->
    <div class="modal-backdrop" id="modal-backdrop"></div>
    <div class="auth-modal bg-white rounded-lg shadow-xl p-8" id="auth-modal">
        <div class="text-center">
            <i class="fas fa-lock text-4xl text-blue-600 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Authentication Required</h3>
            <p class="text-gray-500 mb-6">Please login or create an account to make a purchase</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="/login" class="w-full sm:w-auto bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </a>
                <a href="/signup" class="w-full sm:w-auto bg-gray-200 text-gray-800 px-8 py-3 rounded-lg hover:bg-gray-300 transition-colors">
                    <i class="fas fa-user-plus mr-2"></i>Sign Up
                </a>
            </div>
        </div>
    </div>

    <script>
        let allProducts = [];
        const loading = document.getElementById('loading');
        const emptyState = document.getElementById('empty-state');
        const searchInput = document.getElementById('searchInput');
        const sortSelect = document.getElementById('sortSelect');

        // Fetch products from API
        async function fetchProducts() {
            try {
                loading.style.display = 'block';
                const response = await fetch('/stocks');
                const data = await response.json();
                if (Array.isArray(data)) {
                    allProducts = data.map(item => ({
                        product_id: item.product_id,
                        product_name: item.product_name,
                        description: item.note || 'High quality product',
                        price: parseFloat(item.price),
                        stock: parseInt(item.stock),
                    }));
                    return allProducts;
                }
                return [];
            } catch (error) {
                console.error('Error fetching products:', error);
                return [];
            } finally {
                loading.style.display = 'none';
            }
        }

        // Filter and sort products
        function filterAndSortProducts() {
            const searchTerm = searchInput.value.toLowerCase();
            const sortBy = sortSelect.value;

            let filteredProducts = allProducts.filter(product => 
                product.product_name.toLowerCase().includes(searchTerm) ||
                (product.description && product.description.toLowerCase().includes(searchTerm))
            );

            switch(sortBy) {
                case 'price_asc':
                    filteredProducts.sort((a, b) => a.price - b.price);
                    break;
                case 'price_desc':
                    filteredProducts.sort((a, b) => b.price - a.price);
                    break;
                case 'stock':
                    filteredProducts.sort((a, b) => b.stock - a.stock);
                    break;
                default:
                    filteredProducts.sort((a, b) => a.product_name.localeCompare(b.product_name));
            }

            displayProducts(filteredProducts);
        }

        // Display products
        function displayProducts(products) {
            const productList = document.getElementById('product-list');
            
            if (products.length === 0) {
                productList.style.display = 'none';
                emptyState.style.display = 'block';
                return;
            }

            emptyState.style.display = 'none';
            productList.style.display = 'grid';
            
            productList.innerHTML = products.map(product => `
                <div class="product-card bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <div class="h-40 bg-gray-100 rounded-lg flex items-center justify-center mb-4">
                            <i class="fas fa-oil-can text-4xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">${product.product_name}</h3>
                        <p class="text-gray-600 text-sm mb-4">${product.description}</p>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-lg font-bold text-blue-600">Rp ${product.price.toLocaleString('id-ID')}</span>
                            <span class="text-sm ${product.stock > 10 ? 'text-green-600' : 'text-orange-600'}">
                                Stock: ${product.stock}
                            </span>
                        </div>
                        <button onclick="handlePurchase(${product.product_id})" 
                                class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2 ${product.stock === 0 ? 'opacity-50 cursor-not-allowed' : ''}"
                                ${product.stock === 0 ? 'disabled' : ''}>
                            <i class="fas fa-shopping-cart"></i>
                            ${product.stock === 0 ? 'Out of Stock' : 'Purchase Now'}
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // Handle purchase
        function handlePurchase(productId) {
            const isLoggedIn = {{ session('user') ? 'true' : 'false' }};
            if (!isLoggedIn) {
                showAuthModal();
            } else {
                window.location.href = '/customer/dashboard';
            }
        }

        // Show authentication modal
        function showAuthModal() {
            document.getElementById('modal-backdrop').style.display = 'block';
            document.getElementById('auth-modal').style.display = 'block';
        }

        // Close authentication modal when clicking outside
        document.getElementById('modal-backdrop').addEventListener('click', function() {
            document.getElementById('modal-backdrop').style.display = 'none';
            document.getElementById('auth-modal').style.display = 'none';
        });

        // Event listeners for search and sort
        searchInput.addEventListener('input', filterAndSortProducts);
        sortSelect.addEventListener('change', filterAndSortProducts);

        // Load products when page loads
        document.addEventListener('DOMContentLoaded', async () => {
            const products = await fetchProducts();
            displayProducts(products);
        });
    </script>
</body>
</html> 