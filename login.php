<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Routa</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="assets/css/pages/login.css">
    <link rel="shortcut icon" href="assets/images/Logo.png" type="image/x-icon">
</head>
<body>
    <div class="container"> 
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-12 col-md-6 col-lg-5">
                <a href="index.php" class="back-link">
                    <i class="bi bi-arrow-left"></i>
                    Back to Home
                </a>

                <div class="card p-4 p-md-5">
                    <div class="text-center mb-2">
                        <img src="assets/images/Logo.png" alt="Routa" height="48" class="mb-4">
                        <h1 class="h4 fw-bold mb-2">Login to Routa</h1>
                        <p class="text-muted mb-4">Fill in your credentials to access your account</p>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button class="social-btn" id="googleLoginBtn" type="button">
                            <i class="bi bi-google"></i>
                            <span>Continue with Google</span>
                        </button>
                        <button class="social-btn" id="facebookLoginBtn" type="button">
                            <i class="bi bi-facebook"></i>
                            <span>Continue with Facebook</span>
                        </button>
                    </div>

                    <div class="text-center divider">
                        Or continue with email
                    </div>

                    <form id="loginForm" method="post" action="php/login.php">
                        <div id="loginAlert" class="alert alert-danger" style="display: none;"></div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-envelope text-muted"></i>
                                </span>
                                <input type="email" name="email" id="email" class="form-control border-start-0" placeholder="your@email.com" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <div class="password-input-group">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-lock text-muted"></i>
                                    </span>
                                    <input type="password" name="password" class="form-control border-start-0" placeholder="Enter your password" id="password" required>
                                </div>
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label text-muted" for="remember">Remember me</label>
                            </div>
                            <a href="#" class="text-success text-decoration-none">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn btn-success w-100 mb-3">Login</button>

                        <p class="text-center mb-0">
                            Don't have an account? <a href="register.php" class="text-success text-decoration-none">Sign up for free</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        // Google OAuth Login
        document.getElementById('googleLoginBtn')?.addEventListener('click', function() {
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
            
            window.location.href = googleAuthUrl;
        });

        // Facebook OAuth Login
        document.getElementById('facebookLoginBtn')?.addEventListener('click', function() {
            const facebookAppId = 'YOUR_FACEBOOK_APP_ID';
            const redirectUri = 'http://localhost/Routa/php/facebook-callback.php';
            const scope = 'email,public_profile';
            
            const facebookAuthUrl = `https://www.facebook.com/v18.0/dialog/oauth?` +
                `client_id=${facebookAppId}&` +
                `redirect_uri=${encodeURIComponent(redirectUri)}&` +
                `scope=${encodeURIComponent(scope)}&` +
                `response_type=code`;
            
            window.location.href = facebookAuthUrl;
        });

        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Get form inputs
                    const email = document.getElementById('email').value.trim();
                    const password = document.getElementById('password').value;
                    const rememberMe = document.getElementById('remember').checked;
                    
                    // Show loading state
                    const submitButton = loginForm.querySelector('button[type="submit"]');
                    const originalText = submitButton.innerHTML;
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Signing in...';

                    // Create the request
                    const formData = new FormData();
                    formData.append('email', email);
                    formData.append('password', password);
                    formData.append('remember', rememberMe);

                    // Show any existing alert
                    const alertDiv = document.getElementById('loginAlert');
                    alertDiv.style.display = 'none';

                    fetch('php/login.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            alertDiv.className = 'alert alert-success';
                            alertDiv.textContent = 'Login successful! Redirecting...';
                            alertDiv.style.display = 'block';

                            // Direct redirect to the provided path
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 500);
                        } else {
                            // Show error message
                            alertDiv.className = 'alert alert-danger';
                            alertDiv.textContent = data.message || 'Invalid email or password';
                            alertDiv.style.display = 'block';
                            
                            // Reset button
                            submitButton.disabled = false;
                            submitButton.innerHTML = originalText;
                        }
                    })
                    .catch(error => {
                        console.error('Login error:', error);
                        alertDiv.className = 'alert alert-danger';
                        alertDiv.textContent = 'An error occurred. Please try again.';
                        alertDiv.style.display = 'block';
                        
                        // Reset button
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;
                    });
                });
            }
        });
    </script>
</body>
</html>