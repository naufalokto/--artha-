<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
        }
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .admin-header {
            background: #2563eb;
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-nav {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
        }
        .admin-nav a {
            padding: 12px 16px;
            background: white;
            color: #2563eb;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        .admin-nav a:hover {
            background: #1d4ed8;
            color: white;
        }
        .admin-nav a.active {
            background: #1d4ed8;
            color: white;
        }
        .admin-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }
        .admin-card h3 {
            color: #2563eb;
            margin-bottom: 16px;
            font-weight: 700;
        }
        .logout-btn {
            background: white;
            color: #dc2626;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }
        .manage-user-form {
            display: none;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-top: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #374151;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }
        .btn-primary {
            background: #2563eb;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: #1d4ed8;
        }
        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .user-table th, .user-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .user-table th {
            background: #f3f4f6;
            font-weight: 600;
        }
        .btn-delete {
            background: #dc2626;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-delete:hover {
            background: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <div>
                <span style="margin-right: 15px; color: white;">{{ session('user')['name'] }}</span>
                <button class="logout-btn" onclick="logout()">Logout</button>
            </div>
        </div>
        
        <div class="admin-nav">
            <a href="#" onclick="toggleManageUser()" class="active">Manage Users</a>
            <a href="#" onclick="toggleTab('products')">Products</a>
            <a href="#" onclick="toggleTab('orders')">Orders</a>
            <a href="#" onclick="toggleTab('settings')">Settings</a>
        </div>
        
        <!-- Form Manage User -->
        <div id="manageUserForm" class="manage-user-form">
            <h3>Buat Akun Baru</h3>
            <form id="createAccountForm">
                @csrf
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="Manager">Manager</option>
                        <option value="Sales">Sales</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="firstname" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="lastname" required>
                </div>
                <button type="submit" class="btn-primary" id="submitBtn">Buat Akun</button>
            </form>
        </div>

        <!-- Tabel User -->
        <div class="admin-card">
            <h3>Daftar User</h3>
            <button class="btn-primary" onclick="toggleManageUser()" style="margin-bottom: 15px;">
                Tambah User Baru
            </button>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <!-- Data user akan diisi melalui JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Pastikan token CSRF dan Authorization tersedia untuk semua request AJAX
        const token = '{{ session("jwt_token") }}';
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Authorization': `Bearer ${token}`
            }
        });

        function toggleManageUser() {
            const form = document.getElementById('manageUserForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function toggleTab(tabName) {
            // For future implementation of tab switching
            alert('Fitur ' + tabName + ' belum tersedia');
        }

        async function logout() {
            try {
                await $.post('/logout');
                window.location.replace('/login');
            } catch (error) {
                console.error('Logout error:', error);
                alert('Gagal logout. Silakan coba lagi.');
            }
        }

        async function loadUsers() {
            const tbody = $('#userTableBody');
            
            try {
                // Tampilkan loading state
                tbody.html('<tr><td colspan="4" style="text-align: center;">Loading...</td></tr>');
                
                // Ambil token dari session
                const token = '{{ session("user")["token"] ?? "" }}';
                
                if (!token) {
                    throw new Error('Token tidak ditemukan. Silakan login kembali.');
                }
                
                console.log('Mengambil daftar users...');
                const response = await $.ajax({
                    url: '/admin/users',
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Authorization': `Bearer ${token}`
                    },
                    timeout: 10000 // Timeout setelah 10 detik
                });
                
                console.log('Daftar users diterima:', response);
                
                tbody.empty();
                
                // Validasi response
                if (!response || response.status !== 'success') {
                    throw new Error(response?.message || 'Tidak ada data yang diterima dari server');
                }
                
                // Tangani jika response bukan array
                let users = response.data || [];
                
                if (!Array.isArray(users) || users.length === 0) {
                    tbody.html(`
                        <tr>
                            <td colspan="4" style="text-align: center;">
                                Belum ada user. 
                                <a href="#" onclick="toggleManageUser()" style="color: #2563eb; text-decoration: underline;">
                                    Tambahkan user baru
                                </a>
                            </td>
                        </tr>
                    `);
                    return;
                }
                
                // Tambahkan ID sementara untuk setiap user berdasarkan indeks
                users.forEach((user, index) => {
                    // Skip admin dari list
                    if (user.role && user.role.toLowerCase() === 'admin') return;
                    const userId = user.user_id;
                    tbody.append(`
                        <tr>
                            <td>${user.name || '-'}</td>
                            <td>${user.email || '-'}</td>
                            <td>${user.role || '-'}</td>
                            <td>
                                <button 
                                    onclick="deleteUser(${userId}, '${user.email}')"
                                    class="btn-delete"
                                    data-user-id="${userId}"
                                    data-email="${user.email}"
                                >
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    `);
                });
            } catch (error) {
                console.error('Error loading users:', error);
                const statusCode = error.status || '';
                const errorMessage = error.responseJSON?.message || error.message || 'Gagal memuat daftar user';
                
                // Log error details
                console.error('Error details:', {
                    status: error.status,
                    statusText: error.statusText,
                    responseText: error.responseText,
                    readyState: error.readyState
                });
                
                tbody.html(`
                    <tr>
                        <td colspan="4" style="text-align: center; color: #dc2626;">
                            ${errorMessage} (${statusCode})
                            <br><small>Silakan coba refresh halaman</small>
                            <br><br>
                            <button class="btn-primary" onclick="loadUsers()">
                                Refresh Data
                            </button>
                        </td>
                    </tr>
                `);
                
                // Jika unauthorized, redirect ke login
                if (error.status === 401) {
                    alert('Sesi Anda telah berakhir. Silakan login kembali.');
                    window.location.replace('/login');
                }
            }
        }

        async function deleteUser(userId, email) {
            if (!userId) {
                alert('User ID tidak ditemukan. Tidak dapat menghapus user.');
                return;
            }
            if (!confirm(`Anda yakin ingin menghapus user ${email}?`)) return;
            const token = '{{ session("user")['token'] ?? '' }}';
            try {
                const response = await $.ajax({
                    url: '/admin/delete-user',
                    method: 'DELETE',
                    contentType: 'application/json',
                    data: JSON.stringify({ user_id: userId }),
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                alert('User berhasil dihapus!');
                await loadUsers();
            } catch (error) {
                alert('Gagal menghapus user. ' + (error.responseJSON?.message || error.message));
            }
        }

        $(document).ready(function() {
            loadUsers();

            $('#createAccountForm').on('submit', async function(e) {
                e.preventDefault();
                const submitBtn = $('#submitBtn');
                submitBtn.prop('disabled', true).text('Memproses...');

                try {
                    const formData = {
                        username: this.elements['username'].value,
                        firstname: this.elements['firstname'].value,
                        lastname: this.elements['lastname'].value,
                        email: this.elements['email'].value,
                        password: this.elements['password'].value,
                        role: this.elements['role'].value
                    };

                    if (formData.password.length < 8) {
                        alert('Password minimal 8 karakter');
                        submitBtn.prop('disabled', false).text('Buat Akun');
                        return;
                    }

                    const token = '{{ session("user")["token"] ?? "" }}';

                    const response = await $.ajax({
                        url: '/admin/create-account',
                        method: 'POST',
                        data: JSON.stringify(formData),
                        contentType: 'application/json',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });

                    if (response.status === 'success') {
                        this.reset();
                        await loadUsers();
                        alert('Akun berhasil dibuat!');
                    } else {
                        throw new Error(response.message || 'Gagal membuat akun');
                    }
                } catch (error) {
                    alert('Gagal membuat akun. ' + (error.responseJSON?.message || error.message));
                } finally {
                    submitBtn.prop('disabled', false).text('Buat Akun');
                }
            });
        });

        // Fungsi untuk memeriksa dan menampilkan form jika tidak ada data user
        async function checkAndShowCreateForm() {
            try {
                const response = await $.ajax({
                    url: '/admin/viewuser',
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    },
                    timeout: 5000
                });
                
                // Jika tidak ada data user atau terjadi error, tampilkan form tambah user
                if (!response || !Array.isArray(response) || response.length === 0) {
                    // Tampilkan form tambah user
                    toggleManageUser();
                    
                    // Tambahkan pesan
                    $('#manageUserForm').prepend(`
                        <div class="alert alert-info" style="background-color: #e0f2fe; padding: 10px; border-radius: 6px; margin-bottom: 15px; border-left: 4px solid #38bdf8;">
                            <strong>Belum ada user!</strong> Silakan tambahkan user pertama (Manager/Sales).
                        </div>
                    `);
                }
            } catch (error) {
                console.error('Error checking users:', error);
                // Tampilkan form tambah user jika terjadi error
                toggleManageUser();
                
                // Tambahkan pesan
                $('#manageUserForm').prepend(`
                    <div class="alert alert-warning" style="background-color: #fef3c7; padding: 10px; border-radius: 6px; margin-bottom: 15px; border-left: 4px solid #f59e0b;">
                        <strong>Gagal memeriksa data user!</strong> Silakan tambahkan user baru.
                    </div>
                `);
            }
        }
        
        // Panggil fungsi untuk memeriksa dan menampilkan form
        $(document).ready(function() {
            checkAndShowCreateForm();
        });
    </script>
</body>
</html>