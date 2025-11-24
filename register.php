<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#10b981">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>Create Account - Routa</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/pages/register.css">
    <link rel="shortcut icon" href="assets/images/Logo.png" type="image/x-icon">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row min-vh-100">
            <!-- Left Column - Promotional Content -->
            <div class="col-lg-6 left-section d-flex align-items-center justify-content-center">
                <div class="promo-content">
                    <!-- Back to Home Link -->
                    <a href="index.php" class="back-link">
                        <i class="bi bi-arrow-left"></i> Back to Home
                    </a>

                    <!-- Logo -->
                    <div class="logo-section mb-4">
                        <img src="assets/images/Logo.png" alt="Routa Logo" class="logo-img">
                        <span class="logo-text">Routa</span>
                    </div>

                    <!-- Main Heading -->
                    <h1 class="promo-title">Join Routa Today</h1>
                    <p class="promo-subtitle">Create your account and start enjoying convenient tricycle rides across the Philippines.</p>

                    <!-- Features List -->
                    <ul class="features-list">
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Book rides instantly</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Real-time tracking</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Cashless payments</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Affordable rates</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>24/7 support</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Local drivers</span>
                        </li>
                    </ul>

                    <!-- Testimonial Card -->
                    <div class="testimonial-card">
                        <div class="stars">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">"Routa has completely changed how I commute. Fast, safe, and reliable!"</p>
                        <div class="testimonial-author">
                            <div class="author-avatar">MS</div>
                            <div class="author-info">
                                <div class="author-name">Maria Santos</div>
                                <div class="author-role">Daily Commuter</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Registration Form -->
            <div class="col-lg-6 right-section d-flex align-items-center justify-content-center">
                <!-- Mobile Back Link -->
                <a href="index.php" class="back-link-mobile">
                    <i class="bi bi-arrow-left"></i> Back to Home
                </a>
                
                <div class="form-container">
                    <div class="form-card">
                        <h2 class="form-title">Create Account</h2>
                        <p class="form-subtitle">Fill in your details to get started</p>

                        <!-- Social Login Buttons -->
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <button type="button" class="social-btn" id="googleLoginBtn">
                                    <i class="bi bi-google"></i>
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="social-btn" id="facebookLoginBtn">
                                    <i class="bi bi-facebook"></i>
                                </button>
                            </div>
                        </div>

                        <div class="divider">Or register with email</div>

                        <!-- Registration Form -->
                        <form id="registerForm">
                            <!-- Hidden field for verified phone -->
                            <input type="hidden" id="verifiedPhoneHidden" name="verified_phone" value="">
                            
                            <!-- Full Name -->
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <div class="input-wrapper">
                                    <i class="bi bi-person input-icon"></i>
                                    <input type="text" class="form-control" placeholder="Juan dela Cruz" id="fullName" name="fullName" required>
                                </div>
                            </div>

                            <!-- Email Address -->
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <div class="input-wrapper">
                                    <i class="bi bi-envelope input-icon"></i>
                                    <input type="email" class="form-control" placeholder="your@email.com" id="email" name="email" required>
                                </div>
                            </div>

                            <!-- Phone Number -->
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <div class="input-wrapper" style="position: relative;">
                                    <i class="bi bi-telephone input-icon"></i>
                                    <input type="tel" class="form-control" placeholder="+63 912 345 6789" id="phone" name="phone" required style="padding-right: 100px;">
                                    <button type="button" class="btn btn-sm btn-outline-success" id="sendOtpBtn" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); font-size: 12px; padding: 4px 12px; border-radius: 6px;">
                                        <i class="bi bi-shield-check me-1"></i>Verify
                                    </button>
                                </div>
                                <div id="phoneVerificationStatus" style="font-size: 12px; margin-top: 6px; display: none;">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <span class="text-success">Phone verified</span>
                                </div>
                                <small style="font-size: 11px; color: #718096; margin-top: 4px; display: block;">
                                    Format: 09XXXXXXXXX
                                </small>
                            </div>

                            <!-- Password Fields -->
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Password</label>
                                        <div class="input-wrapper">
                                            <i class="bi bi-lock input-icon"></i>
                                            <input type="password" class="form-control" placeholder="••••••••" id="password" name="password" required minlength="8">
                                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Confirm Password</label>
                                        <div class="input-wrapper">
                                            <i class="bi bi-lock input-icon"></i>
                                            <input type="password" class="form-control" placeholder="••••••••" id="confirmPassword" name="confirmPassword" required minlength="8">
                                            <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms Checkbox -->
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="terms.php">Terms of Service</a> and <a href="privacy.php">Privacy Policy</a>
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn-create">Create Account</button>
                        </form>

                        <!-- Login Link -->
                        <div class="login-link">
                            Already have an account? <a href="login.php">Login here</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OTP Verification Modal -->
    <div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="otpModalLabel">
                        <i class="bi bi-phone text-success me-2"></i>Verify Your Phone
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center px-4">
                    <div class="mb-3">
                        <div class="otp-icon mb-3">
                            <i class="bi bi-shield-check" style="font-size: 64px; color: #10b981;"></i>
                        </div>
                        <p class="text-muted" id="otpPhoneDisplay">
                            We've sent a 6-digit verification code to<br>
                            <strong id="displayPhone"></strong>
                        </p>
                    </div>
                    
                    <div class="otp-input-group mb-3">
                        <input type="text" class="otp-input" maxlength="1" id="otp1" />
                        <input type="text" class="otp-input" maxlength="1" id="otp2" />
                        <input type="text" class="otp-input" maxlength="1" id="otp3" />
                        <input type="text" class="otp-input" maxlength="1" id="otp4" />
                        <input type="text" class="otp-input" maxlength="1" id="otp5" />
                        <input type="text" class="otp-input" maxlength="1" id="otp6" />
                    </div>
                    
                    <div id="otpError" class="text-danger mb-3" style="display: none;"></div>
                    
                    <button type="button" class="btn btn-success w-100 mb-3" id="verifyOtpBtn">
                        <i class="bi bi-check-circle me-2"></i>Verify Code
                    </button>
                    
                    <div class="text-center">
                        <small class="text-muted">
                            Didn't receive the code? 
                            <a href="#" id="resendOtpBtn" class="text-success fw-bold">Resend OTP</a>
                        </small>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted" id="otpTimer"></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-5 px-4">
                    <div class="success-animation mb-4">
                        <div class="checkmark-circle">
                            <i class="bi bi-check" style="font-size: 64px; color: white;"></i>
                        </div>
                    </div>
                    <h3 class="mb-3">Registration Successful!</h3>
                    <p class="text-muted mb-4">
                        Your account has been created successfully.<br>
                        Welcome to Routa!
                    </p>
                    <button type="button" class="btn btn-success px-5" id="goToLoginBtn">
                        Continue to Login
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Styles for Modals -->
    <style>
        .otp-input-group {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .otp-input {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            outline: none;
            transition: all 0.2s;
        }
        
        .otp-input:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        
        .otp-input.error {
            border-color: #dc3545;
            animation: shake 0.3s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .checkmark-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            animation: scaleIn 0.5s ease-out;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
        }
        
        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .success-animation {
            animation: fadeInUp 0.6s ease-out;
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
        
        @media (max-width: 576px) {
            .otp-input {
                width: 42px;
                height: 42px;
                font-size: 20px;
            }
            
            .otp-input-group {
                gap: 6px;
            }
            
            .checkmark-circle {
                width: 100px;
                height: 100px;
            }
            
            .checkmark-circle i {
                font-size: 48px !important;
            }
        }
    </style>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentElement.querySelector('.password-toggle i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        // Google OAuth Login
        document.getElementById('googleLoginBtn').addEventListener('click', function() {
            // Google OAuth URL
            const googleClientId = '941913119965-kld04cl0a3ugka2b0est8l022ji6b8ur.apps.googleusercontent.com';
            // Use exact redirect URI that matches Google Console configuration
            const redirectUri = 'http://localhost/Routas/php/google-callback.php';
            const scope = 'email profile';
            
            console.log('Redirect URI being sent:', redirectUri);
            
            const googleAuthUrl = `https://accounts.google.com/o/oauth2/v2/auth?` +
                `client_id=${googleClientId}&` +
                `redirect_uri=${encodeURIComponent(redirectUri)}&` +
                `response_type=code&` +
                `scope=${encodeURIComponent(scope)}&` +
                `access_type=online&` +
                `prompt=select_account`;
            
            console.log('Full Google Auth URL:', googleAuthUrl);
            
            // Redirect to Google
            window.location.href = googleAuthUrl;
        });

        // Facebook OAuth Login
        document.getElementById('facebookLoginBtn').addEventListener('click', function() {
            // Facebook OAuth URL
            const facebookAppId = 'YOUR_FACEBOOK_APP_ID';
            const redirectUri = 'http://localhost/Routa/php/facebook-callback.php';
            const scope = 'email,public_profile';
            
            const facebookAuthUrl = `https://www.facebook.com/v18.0/dialog/oauth?` +
                `client_id=${facebookAppId}&` +
                `redirect_uri=${encodeURIComponent(redirectUri)}&` +
                `scope=${encodeURIComponent(scope)}&` +
                `response_type=code`;
            
            // Redirect to Facebook
            window.location.href = facebookAuthUrl;
        });

        // Show error messages if present in URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('error')) {
            const errorMessages = {
                'google_auth_failed': 'Google authentication failed. Please try again.',
                'facebook_auth_failed': 'Facebook authentication failed. Please try again.',
                'token_failed': 'Failed to get access token. Please try again.',
                'user_info_failed': 'Failed to get user information. Please try again.',
                'database_error': 'A database error occurred. Please try again.'
            };
            
            const error = urlParams.get('error');
            if (errorMessages[error]) {
                alert(errorMessages[error]);
            }
        }
    </script>
    <script src="assets/js/pages/register.js"></script>
</body>
</html>