<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Successful - Welcome!</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow: hidden;
        }
        
        .success-container {
            background: #fff;
            padding: 3rem 2.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px rgba(59, 130, 246, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
            position: relative;
            animation: slideInUp 0.8s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #38a169 0%, #48bb78 100%);
            border-radius: 50%;
            margin: 0 auto 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: bounceIn 1s ease-out 0.3s both;
            position: relative;
        }
        
        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3);
            }
            50% {
                opacity: 1;
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .success-icon::before {
            content: '';
            width: 60px;
            height: 30px;
            border: 4px solid #fff;
            border-top: none;
            border-right: none;
            transform: rotate(-45deg);
            margin-top: -5px;
            animation: checkmark 0.6s ease-out 0.8s both;
        }
        
        @keyframes checkmark {
            0% {
                opacity: 0;
                transform: rotate(-45deg) scale(0.5);
            }
            100% {
                opacity: 1;
                transform: rotate(-45deg) scale(1);
            }
        }
        
        .success-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 1rem;
            animation: fadeInUp 0.8s ease-out 0.5s both;
        }
        
        .success-subtitle {
            font-size: 1.1rem;
            color: #374151;
            margin-bottom: 2rem;
            line-height: 1.6;
            animation: fadeInUp 0.8s ease-out 0.7s both;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .user-info {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border-radius: 1rem;
            padding: 1.5rem;
            margin: 2rem 0;
            border-left: 4px solid #2563eb;
            animation: fadeInUp 0.8s ease-out 0.9s both;
        }
        
        .user-info h3 {
            color: #2563eb;
            font-size: 1.2rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .user-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            text-align: left;
        }
        
        .user-detail {
            display: flex;
            flex-direction: column;
        }
        
        .user-detail label {
            font-size: 0.85rem;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .user-detail span {
            font-size: 1rem;
            color: #374151;
            font-weight: 500;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            animation: fadeInUp 0.8s ease-out 1.1s both;
        }
        
        .btn {
            flex: 1;
            padding: 0.875rem 1.5rem;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
            color: #fff;
        }
        
        .btn-primary:hover {
            background: linear-gradient(90deg, #1d4ed8 0%, #3b82f6 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        }
        
        .btn-secondary {
            background: #f7fafc;
            color: #374151;
            border: 2px solid #e2e8f0;
        }
        
        .btn-secondary:hover {
            background: #edf2f7;
            border-color: #cbd5e0;
            transform: translateY(-1px);
        }
        
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #fbbf24;
            animation: confetti-fall 3s linear infinite;
        }
        
        .confetti:nth-child(2n) {
            background: #34d399;
        }
        
        .confetti:nth-child(3n) {
            background: #60a5fa;
        }
        
        .confetti:nth-child(4n) {
            background: #f87171;
        }
        
        @keyframes confetti-fall {
            0% {
                transform: translateY(-100vh) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(720deg);
                opacity: 0;
            }
        }
        
        .auto-redirect {
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: #6b7280;
            animation: fadeInUp 0.8s ease-out 1.3s both;
        }
        
        .countdown {
            font-weight: 600;
            color: #2563eb;
        }
        
        @media (max-width: 600px) {
            .success-container {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }
            
            .success-title {
                font-size: 1.8rem;
            }
            
            .user-details {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Confetti Animation -->
    <div class="confetti" style="left: 10%; animation-delay: 0s;"></div>
    <div class="confetti" style="left: 20%; animation-delay: 0.5s;"></div>
    <div class="confetti" style="left: 30%; animation-delay: 1s;"></div>
    <div class="confetti" style="left: 40%; animation-delay: 1.5s;"></div>
    <div class="confetti" style="left: 50%; animation-delay: 2s;"></div>
    <div class="confetti" style="left: 60%; animation-delay: 0.3s;"></div>
    <div class="confetti" style="left: 70%; animation-delay: 0.8s;"></div>
    <div class="confetti" style="left: 80%; animation-delay: 1.3s;"></div>
    <div class="confetti" style="left: 90%; animation-delay: 1.8s;"></div>
    
    <div class="success-container">
        <div class="success-icon"></div>
        
        <h1 class="success-title">🎉 Registration Successful!</h1>
        <p class="success-subtitle">
            Welcome to our platform! Your account has been created successfully. 
            You can now log in and start exploring our services.
        </p>
        
        @if(isset($userData))
        <div class="user-info">
            <h3>📋 Account Details</h3>
            <div class="user-details">
                <div class="user-detail">
                    <label>Username</label>
                    <span>{{ $userData['username'] ?? 'N/A' }}</span>
                </div>
                <div class="user-detail">
                    <label>Email</label>
                    <span>{{ $userData['email'] ?? 'N/A' }}</span>
                </div>
                <div class="user-detail">
                    <label>Full Name</label>
                    <span>{{ ($userData['firstname'] ?? '') . ' ' . ($userData['lastname'] ?? '') }}</span>
                </div>
                <div class="user-detail">
                    <label>Role</label>
                    <span>{{ $userData['role'] ?? 'Customer' }}</span>
                </div>
            </div>
        </div>
        @endif
        
        <div class="action-buttons">
            <a href="{{ route('login') }}" class="btn btn-primary">
                🚀 Login Now
            </a>
            <a href="/" class="btn btn-secondary">
                🏠 Go Home
            </a>
        </div>
        
        <div class="auto-redirect">
            <p>You will be automatically redirected to login page in <span class="countdown" id="countdown">5</span> seconds...</p>
        </div>
    </div>

    <script>
        // Auto redirect countdown
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');
        
        const countdownTimer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(countdownTimer);
                window.location.href = '{{ route("login") }}';
            }
        }, 1000);
        
        // Add more confetti on click
        document.addEventListener('click', function(e) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = e.clientX + 'px';
            confetti.style.top = e.clientY + 'px';
            confetti.style.animationDelay = '0s';
            document.body.appendChild(confetti);
            
            setTimeout(() => {
                confetti.remove();
            }, 3000);
        });
        
        // Prevent form resubmission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html> 