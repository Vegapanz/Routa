/**
 * Login Page JavaScript
 * This file contains JavaScript specific to the login page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form inputs
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const rememberMe = document.getElementById('remember').checked;
            
            // Simple validation
            if (!email) {
                showAlert('Please enter your email address', 'danger');
                return;
            }
            
            if (!isValidEmail(email)) {
                showAlert('Please enter a valid email address', 'danger');
                return;
            }
            
            if (!password) {
                showAlert('Please enter your password', 'danger');
                return;
            }
            
            // Send the login request to the server
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            formData.append('remember', rememberMe);

            // Show loading state
            const submitButton = loginForm.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Signing in...';

            fetch('php/login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Login successful! Redirecting...', 'success');
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.reload();
                    }
                } else {
                    showAlert(data.message || 'Invalid email or password', 'danger');
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
            })
            .catch(error => {
                                showAlert('An error occurred. Please try again.', 'danger');
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        });
    }
    
    // Toggle password visibility
    const togglePassword = document.querySelector('.toggle-password');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });
    }
    
    // Social login buttons
    document.querySelectorAll('.social-login .btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const provider = this.getAttribute('data-provider');
                        // Here you would typically redirect to the OAuth provider
            // window.location.href = `/auth/${provider}`;
            
            // For demo purposes, show a message
            showAlert(`Redirecting to ${provider} login...`, 'info');
        });
    });
    
    // Forgot password link
    const forgotPasswordLink = document.querySelector('.forgot-password');
    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show password reset form
            const email = document.getElementById('email').value.trim();
            const resetForm = `
                <div class="reset-password-form mt-4">
                    <h5 class="mb-3">Reset Password</h5>
                    <div class="mb-3">
                        <label for="resetEmail" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="resetEmail" value="${email}" required>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="cancelReset">Cancel</button>
                        <button type="button" class="btn btn-primary btn-sm" id="submitReset">Send Reset Link</button>
                    </div>
                </div>
            `;
            
            // Replace the form with reset form
            const formContainer = document.querySelector('.card-body');
            const originalForm = formContainer.innerHTML;
            formContainer.innerHTML = resetForm;
            
            // Handle cancel
            document.getElementById('cancelReset').addEventListener('click', function() {
                formContainer.innerHTML = originalForm;
                // Re-attach event listeners
                attachEventListeners();
            });
            
            // Handle reset submission
            document.getElementById('submitReset').addEventListener('click', function() {
                const resetEmail = document.getElementById('resetEmail').value.trim();
                
                if (!resetEmail || !isValidEmail(resetEmail)) {
                    showAlert('Please enter a valid email address', 'danger');
                    return;
                }
                
                // Here you would typically send a password reset email
                                
                // Show success message and restore form
                showAlert('Password reset link has been sent to your email', 'success');
                formContainer.innerHTML = originalForm;
                
                // Re-attach event listeners
                attachEventListeners();
            });
        });
    }
    
    // Helper function to validate email
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    // Helper function to show alerts
    function showAlert(message, type) {
        // Remove any existing alerts
        const existingAlert = document.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        const cardBody = document.querySelector('.card-body');
        if (cardBody) {
            cardBody.insertBefore(alertDiv, cardBody.firstChild);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alertDiv);
                bsAlert.close();
            }, 5000);
        }
    }
    
    // Function to simulate login (for demo purposes)
    function simulateLogin(email, password, rememberMe) {
        // Show loading state
        const submitButton = loginForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Signing in...';
        
        // Simulate API call delay
        setTimeout(() => {
            // For demo purposes, consider it a success if email contains "demo"
            if (email.includes('demo')) {
                showAlert('Login successful! Redirecting...', 'success');
                
                // In a real app, you would redirect after successful login
                // window.location.href = '/dashboard';
            } else {
                showAlert('Invalid email or password. Please try again.', 'danger');
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        }, 1500);
    }
    
    // Function to re-attach event listeners after DOM changes
    function attachEventListeners() {
        // Re-attach any event listeners that were lost due to DOM updates
        // This is called after reset password form is closed
        
        // Re-attach toggle password
        const togglePassword = document.querySelector('.toggle-password');
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle icon
                this.querySelector('i').classList.toggle('bi-eye');
                this.querySelector('i').classList.toggle('bi-eye-slash');
            });
        }
        
        // Re-attach form submission
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form inputs
                const email = document.getElementById('email').value.trim();
                const password = document.getElementById('password').value;
                const rememberMe = document.getElementById('remember').checked;
                
                // Simple validation
                if (!email) {
                    showAlert('Please enter your email address', 'danger');
                    return;
                }
                
                if (!isValidEmail(email)) {
                    showAlert('Please enter a valid email address', 'danger');
                    return;
                }
                
                if (!password) {
                    showAlert('Please enter your password', 'danger');
                    return;
                }
                
                // Simulate login
                simulateLogin(email, password, rememberMe);
            });
        }
    }
});
