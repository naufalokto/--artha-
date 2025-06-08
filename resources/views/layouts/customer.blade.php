<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Artha Minimarket') }} - Customer Dashboard</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Midtrans -->
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .active {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 16rem;
            z-index: 40;
        }
        .main-content {
            margin-left: 16rem;
            min-height: 100vh;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="antialiased bg-gray-100">
    <!-- Mobile Menu Button -->
    <div class="fixed top-4 left-4 z-50 md:hidden">
        <button id="menu-toggle" class="bg-blue-600 text-white p-2 rounded-lg shadow-lg">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Sidebar -->
    <aside class="sidebar bg-blue-600 text-white">
        <div class="p-4">
            <h2 class="text-xl font-bold mb-4">{{ session('user')['name'] }}</h2>
            <nav>
                <a href="#" class="block py-2 px-4 hover:bg-blue-700 rounded mb-1 active" data-section="dashboard">
                    <i class="fas fa-home mr-2"></i>Produk
                </a>
                <a href="#" class="block py-2 px-4 hover:bg-blue-700 rounded mb-1" data-section="cart">
                    <i class="fas fa-shopping-cart mr-2"></i>Keranjang
                </a>
                <a href="#" class="block py-2 px-4 hover:bg-blue-700 rounded mb-1" data-section="transactions">
                    <i class="fas fa-history mr-2"></i>Transaksi
                </a>
            </nav>
            <form action="/logout" method="POST" class="mt-8">
                @csrf
                <button type="submit" class="w-full bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content p-8">
        @yield('content')
    </main>

    @stack('scripts')

    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !menuToggle.contains(e.target) && 
                sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
            }
        });
    </script>
</body>
</html> 