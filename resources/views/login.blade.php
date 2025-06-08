<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Artha</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: #fff;
            padding: 36px 32px 28px 32px;
            border-radius: 18px;
            box-shadow: 0 4px 32px rgba(59,130,246,0.10);
            width: 100%;
            max-width: 420px;
        }
        .login-container h2 {
            text-align: center;
            color: #2563eb;
            margin-bottom: 24px;
            font-weight: 800;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            color: #374151;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid #dbeafe;
            border-radius: 6px;
            font-size: 1rem;
            background: #f1f5f9;
            margin-bottom: 4px;
            transition: all 0.2s;
        }
        input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
            background: #fff;
        }
        .btn-primary {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #1d4ed8 0%, #3b82f6 100%);
            transform: translateY(-1px);
        }
        .btn-primary:active {
            transform: translateY(0);
        }
        #error-message {
            text-align: center;
            margin-bottom: 12px;
            font-size: 1rem;
            color: #dc2626;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 18px;
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <a href="/" class="back-link">&larr; Kembali</a>
        <h2>Login</h2>
        <div id="error-message" style="display: none;"></div>
        <form id="loginForm">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary" id="loginButton">Login</button>
        </form>
    </div>

    <script>
    $(document).ready(function() {
        // Input validation function
        function validateInput() {
            const email = $('#email').val().trim();
            const password = $('#password').val();
            
            if (!email || !password) {
                showError('Email and password are required');
                return false;
            }
            
            if (!isValidEmail(email)) {
                showError('Please enter a valid email address');
                return false;
            }
            
            return true;
        }
        
        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }
        
        function showError(message) {
            $('#error-message').text(message).show();
            const loginButton = $('#loginButton');
            loginButton.prop('disabled', false);
            loginButton.text('Login');
        }
        
        function handleLoginError(error) {
            console.error('Login Error:', error);
            
            let errorMessage = 'An error occurred during login. Please try again.';
            
            if (error.responseJSON) {
                if (error.status === 401) {
                    errorMessage = error.responseJSON.message || 'Invalid credentials';
                } else if (error.status === 422) {
                    errorMessage = error.responseJSON.message || 'Please check your input';
                } else if (error.status === 429) {
                    errorMessage = 'Too many attempts. Please try again later';
                } else if (error.status === 500) {
                    errorMessage = 'Server error. Please try again later';
                }
            }
            
            showError(errorMessage);
        }

        function handleRedirect(response) {
            if (response.status === 'success' && response.data) {
                // Store user data in localStorage
                localStorage.setItem('user', JSON.stringify(response.data));
                localStorage.setItem('token', response.data.token);
                
                // Get role from response and ensure it's lowercase
                const role = (response.data.role || '').toLowerCase();
                
                // Get the redirect URL from the response or determine based on role
                let redirectUrl = response.redirect;
                if (!redirectUrl) {
                    switch (role) {
                        case 'admin':
                            redirectUrl = '/admin/dashboard';
                            break;
                        case 'manager':
                            redirectUrl = '/manager/dashboard';
                            break;
                        case 'sales':
                            redirectUrl = '/sales/dashboard';
                            break;
                        case 'customer':
                            redirectUrl = '/customer/dashboard';
                            break;
                        default:
                            console.warn('Unknown role:', role);
                            redirectUrl = '/login';
                            break;
                    }
                }
                
                console.log('Redirecting to:', redirectUrl, 'Role:', role);
                window.location.href = redirectUrl;
            } else {
                showError(response.message || 'Login failed');
            }
        }

        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            // Validate input first
            if (!validateInput()) {
                return;
            }
            
            // Disable button and show loading state
            const loginButton = $('#loginButton');
            loginButton.prop('disabled', true);
            loginButton.text('Loading...');
            
            // Hide any previous error message
            $('#error-message').hide();

            // Get form data
            const formData = {
                email: $('#email').val().trim(),
                password: $('#password').val()
            };

            // Send login request
            $.ajax({
                url: '{{ route("auth.login") }}',
                method: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    console.log('Login Response:', response);
                    handleRedirect(response);
                },
                error: function(xhr) {
                    handleLoginError(xhr);
                }
            });
        });
    });
    </script>
</body>
</html>