<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sales-dashboard.css') }}">
    <!-- Add SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .sidebar {
            background-color: #0066FF;
            min-height: 100vh;
            width: 280px;
        }
        
        .sidebar-link {
            color: white;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .sidebar-link:hover, .sidebar-link.active {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-link svg {
            width: 1.5rem;
            height: 1.5rem;
            margin-right: 0.75rem;
        }

        .main-content {
            margin-left: 280px;
            padding: 2rem;
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .top-bar {
            background-color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Dropdown styles */
        .dropdown-menu {
            transform-origin: top right;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .dropdown-menu.hidden {
            transform: scale(0.95);
            opacity: 0;
            pointer-events: none;
        }

        .dropdown-item {
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: #f3f4f6;
        }

        .dropdown-item svg {
            transition: transform 0.2s ease;
        }

        .dropdown-item:hover svg {
            transform: translateX(2px);
        }

        /* Card and animation styles */
        .card-3d {
            transform: perspective(1000px) rotateX(0deg) rotateY(0deg);
            transition: transform 0.3s ease;
        }
        
        .card-3d:hover {
            transform: perspective(1000px) rotateX(5deg) rotateY(5deg);
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .gradient-animate {
            background-size: 200% 200%;
            animation: gradientMove 5s ease infinite;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
        }

        .status-badge {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .table-hover tr:hover {
            transform: scale(1.01);
            transition: transform 0.2s ease;
        }

        /* Modal styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
        }

        .loading {
            opacity: 0.5;
            pointer-events: none;
            position: relative;
        }

        .loading::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            transform: translate(-50%, -50%);
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 4px;
            color: white;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }

        .notification.success {
            background: #48bb78;
        }

        .notification.error {
            background: #f56565;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <aside class="sidebar fixed left-0 top-0">
        <div class="p-4">
            <h1 class="text-white text-2xl font-bold mb-8">Sales Dashboard</h1>
        </div>
        
        <nav>
            <a href="{{ route('sales.dashboard') }}" class="sidebar-link {{ request()->routeIs('sales.dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>
            
            <a href="{{ route('sales.products.stock.view') }}" class="sidebar-link {{ request()->routeIs('sales.products.stock.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Stock Management
            </a>
            
            <a href="{{ route('sales.raw-materials.view') }}" class="sidebar-link {{ request()->routeIs('sales.raw-materials.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Raw Materials
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h2 class="text-2xl font-semibold text-gray-800">Sales Dashboard</h2>
            <div class="flex items-center">
                <div class="relative">
                    <button id="userDropdown" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900">
                        <span>{{ session('user')['name'] ?? 'sales2' }}</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <!-- Dropdown menu -->
                    <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                        <button id="logoutButton" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Logout
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Welcome Section -->
        <div class="p-6">
            <div class="welcome-section mb-8">
                <h1 class="text-4xl font-bold text-gray-800 mb-2">Welcome Back, {{ session('user')['name'] ?? 'sales2' }}! 👋</h1>
                <p class="text-gray-600">Here's what's happening with your inventory today.</p>
            </div>

            <!-- Stats Overview -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Total Products Card -->
                <div class="card-3d float-animation stats-card">
                    <div class="bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl shadow-lg p-8 relative overflow-hidden gradient-animate">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 rounded-full bg-blue-300 opacity-20"></div>
                        <div class="flex items-center space-x-4">
                            <div class="icon-container p-3 bg-blue-400 bg-opacity-30 rounded-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div>
            <h3 class="text-lg font-semibold text-white mb-2">Total Products</h3>
                                <p class="stats-number text-white" id="totalProducts">0</p>
                                <div class="mt-2">
                                    <span class="text-blue-100 text-sm bg-blue-500 bg-opacity-30 px-3 py-1 rounded-full">Active products</span>
                                </div>
                            </div>
            </div>
        </div>
    </div>

                <!-- Low Stock Items Card -->
                <div class="card-3d float-animation stats-card" style="animation-delay: 0.2s">
                    <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-2xl shadow-lg p-8 relative overflow-hidden gradient-animate">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 rounded-full bg-yellow-300 opacity-20"></div>
                        <div class="flex items-center space-x-4">
                            <div class="icon-container p-3 bg-yellow-400 bg-opacity-30 rounded-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
            <h3 class="text-lg font-semibold text-white mb-2">Low Stock Items</h3>
                                <p class="stats-number text-white" id="lowStockItems">0</p>
                                <div class="mt-2">
                                    <span class="text-yellow-100 text-sm bg-yellow-500 bg-opacity-30 px-3 py-1 rounded-full">Need attention</span>
                                </div>
                            </div>
            </div>
        </div>
    </div>

                <!-- Raw Materials Card -->
                <div class="card-3d float-animation stats-card" style="animation-delay: 0.4s">
                    <div class="bg-gradient-to-br from-green-400 to-green-600 rounded-2xl shadow-lg p-8 relative overflow-hidden gradient-animate">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 rounded-full bg-green-300 opacity-20"></div>
                        <div class="flex items-center space-x-4">
                            <div class="icon-container p-3 bg-green-400 bg-opacity-30 rounded-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <div>
            <h3 class="text-lg font-semibold text-white mb-2">Raw Materials</h3>
                                <p class="stats-number text-white" id="totalRawMaterials">0</p>
                                <div class="mt-2">
                                    <span class="text-green-100 text-sm bg-green-500 bg-opacity-30 px-3 py-1 rounded-full">Available</span>
                                </div>
                            </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
            <div class="mt-12">
                <div class="bg-white rounded-2xl shadow-lg p-8 transform hover:shadow-xl transition-all duration-300 glass-effect">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Quick Actions
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <button onclick="showSetProductModal()" 
                            class="quick-action group btn-3d p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl hover:from-blue-100 hover:to-blue-200 transition-all duration-300">
                            <div class="flex flex-col items-center space-y-4">
                                <div class="icon-container p-4 bg-blue-500 rounded-full text-white">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                                </div>
                                <span class="text-blue-600 font-semibold text-lg">Add Product</span>
                                <p class="text-blue-500 text-sm text-center">Add new products to inventory</p>
                            </div>
            </button>
            
            <button onclick="showSetRawMaterialModal()" 
                            class="quick-action group btn-3d p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-xl hover:from-green-100 hover:to-green-200 transition-all duration-300">
                            <div class="flex flex-col items-center space-y-4">
                                <div class="icon-container p-4 bg-green-500 rounded-full text-white">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                                </div>
                                <span class="text-green-600 font-semibold text-lg">Add Material</span>
                                <p class="text-green-500 text-sm text-center">Add new raw materials</p>
                            </div>
            </button>
            
                        <a href="{{ route('sales.products.stock.view') }}" 
                            class="quick-action group btn-3d p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl hover:from-purple-100 hover:to-purple-200 transition-all duration-300">
                            <div class="flex flex-col items-center space-y-4">
                                <div class="icon-container p-4 bg-purple-500 rounded-full text-white">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                                </div>
                                <span class="text-purple-600 font-semibold text-lg">View Stock</span>
                                <p class="text-purple-500 text-sm text-center">Check current inventory</p>
                            </div>
            </a>
        </div>
    </div>
</div>
        </div>
    </main>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal" style="display: none;">
        <div class="modal-content">
            <h3 class="text-xl font-bold mb-4">Confirm Delete</h3>
            <p class="mb-6">Are you sure you want to delete selected item(s)?</p>
            <div class="flex justify-end space-x-4">
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300" id="cancelDelete">Cancel</button>
                <button class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>

    <!-- Add SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
        const selectedItems = new Set();
        const deleteModal = document.getElementById('deleteModal');
        const dropdownButton = document.getElementById('userDropdown');
        const dropdownMenu = document.getElementById('dropdownMenu');
        const logoutButton = document.getElementById('logoutButton');

        // Toggle dropdown
        dropdownButton.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdownButton.contains(e.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });

        // Handle logout
        logoutButton.addEventListener('click', function() {
            // Show loading state
            document.body.classList.add('loading');
            
            // Send logout request
            fetch('{{ route("auth.logout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Logout failed');
                window.location.href = '/login';
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Failed to logout. Please try again.');
                document.body.classList.remove('loading');
            });
        });

        // Load stats data saat halaman dimuat
        $(document).ready(function() {
            loadStats();
            setupDeleteHandlers();
            loadStocksTable();
            loadRawMaterialsTable();
        });

        // Fungsi untuk memuat data statistik
        function loadStats() {
            $.ajax({
                url: '/sales/stocks',
                method: 'GET',
                success: function(data) {
                    if (Array.isArray(data)) {
                        let totalProducts = data.length;
                        let lowStockItems = data.filter(item => item.quantity < 10).length;
                        $('#totalProducts').text(totalProducts);
                        $('#lowStockItems').text(lowStockItems);
                    }
                }
            });

            $.ajax({
                url: '/sales/rawmaterial',
                method: 'GET',
                success: function(data) {
                    if (Array.isArray(data)) {
                        $('#totalRawMaterials').text(data.length);
                    }
                }
            });
        }

        // Fungsi untuk menampilkan modal tambah produk
        function showSetProductModal() {
            window.location.href = "{{ route('sales.products.stock.view') }}";
        }

        // Fungsi untuk menampilkan modal tambah raw material
        function showSetRawMaterialModal() {
            window.location.href = "{{ route('sales.raw-materials.view') }}";
        }

        // Setup delete handlers
        function setupDeleteHandlers() {
            // Show delete confirmation modal
            function showDeleteModal() {
                deleteModal.style.display = 'block';
            }

            // Hide delete confirmation modal
            function hideDeleteModal() {
                deleteModal.style.display = 'none';
            }

            // Show notification
            function showNotification(type, message) {
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.textContent = message;
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }

            // Delete selected items
            async function deleteSelected() {
                try {
                    document.body.classList.add('loading');
                    
                    const response = await fetch('/sales/stocks', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            product_ids: Array.from(selectedItems)
                        })
                    });

                    if (!response.ok) throw new Error('Delete failed');

                    showNotification('success', 'Products deleted successfully');
                    selectedItems.clear();
                    loadStats();
                    
                } catch (error) {
                    showNotification('error', 'Failed to delete products');
                    console.error('Error:', error);
                } finally {
                    document.body.classList.remove('loading');
                    hideDeleteModal();
                }
            }

            // Error handling
            function handleError(error) {
                if (error.status === 401) {
                    window.location.href = '/login';
                } else if (error.status === 403) {
                    showNotification('error', 'You do not have permission to perform this action');
                } else {
                    showNotification('error', 'An error occurred. Please try again later.');
                }
            }

            // Modal event listeners
            document.getElementById('confirmDelete').addEventListener('click', deleteSelected);
            document.getElementById('cancelDelete').addEventListener('click', hideDeleteModal);
        }

        async function loadStocks() {
            try {
                const response = await $.ajax({
                    url: '/sales/stocks', // FE endpoint, diteruskan ke Go 9090 oleh Laravel
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                // render data stok ke tabel
            } catch (error) {
                // handle error
            }
        }

        async function loadRawMaterials() {
            try {
                const response = await $.ajax({
                    url: '/sales/rawmaterial', // FE endpoint, diteruskan ke Go 9090 oleh Laravel
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                // render data raw material ke tabel
            } catch (error) {
                // handle error
            }
        }

        function loadStocksTable() {
            const tbody = $('#stocksTableBody');
            tbody.html('<tr><td colspan="6" class="text-center text-gray-400">Loading...</td></tr>');
            $.ajax({
                url: '/sales/stocks',
                method: 'GET',
                success: function(data) {
                    tbody.empty();
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(stock => {
                            tbody.append(`
                                <tr>
                                    <td class="px-4 py-2">${stock.stock_id ?? '-'}</td>
                                    <td class="px-4 py-2">${stock.product_id ?? '-'}</td>
                                    <td class="px-4 py-2">${stock.product_name ?? '-'}</td>
                                    <td class="px-4 py-2">${stock.price ?? '-'}</td>
                                    <td class="px-4 py-2">${stock.quantity ?? '-'}</td>
                                    <td class="px-4 py-2">${stock.note ?? '-'}</td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.html('<tr><td colspan="6" class="text-center text-gray-400">No stock data</td></tr>');
                    }
                },
                error: function() {
                    tbody.html('<tr><td colspan="6" class="text-center text-red-400">Failed to load stock data</td></tr>');
                }
            });
        }

        function loadRawMaterialsTable() {
            const tbody = $('#rawMaterialsTableBody');
            tbody.html('<tr><td colspan="5" class="text-center text-gray-400">Loading...</td></tr>');
            $.ajax({
                url: '/sales/rawmaterial',
                method: 'GET',
                success: function(data) {
                    tbody.empty();
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(mat => {
                            tbody.append(`
                                <tr>
                                    <td class="px-4 py-2">${mat.raw_material_id ?? mat.id ?? '-'}</td>
                                    <td class="px-4 py-2">${mat.raw_material_name ?? mat.name ?? '-'}</td>
                                    <td class="px-4 py-2">${mat.quantity ?? '-'}</td>
                                    <td class="px-4 py-2">${mat.unit ?? '-'}</td>
                                    <td class="px-4 py-2">${mat.note ?? mat.supplier ?? '-'}</td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.html('<tr><td colspan="5" class="text-center text-gray-400">No raw material data</td></tr>');
                    }
                },
                error: function() {
                    tbody.html('<tr><td colspan="5" class="text-center text-red-400">Failed to load raw material data</td></tr>');
                }
            });
        }
</script>
</body>
</html>