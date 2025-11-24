/**
 * Register Page JavaScript
 * This file contains JavaScript specific to the registration page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Form elements
    const registerForm = document.getElementById('registerForm');
    const formSteps = Array.from(document.querySelectorAll('.form-step'));
    const nextBtns = document.querySelectorAll('.btn-next');
    const prevBtns = document.querySelectorAll('.btn-prev');
    const progressSteps = document.querySelectorAll('.step');
    
    let currentStep = 0;
    let isPhoneVerified = false;
    let verifiedPhone = '';
    let otpTimer = null;
    let otpExpiryTime = null;
    
    // Mobile viewport height fix
    function setVH() {
        let vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }
    
    setVH();
    window.addEventListener('resize', setVH);
    window.addEventListener('orientationchange', setVH);
    
    // Initialize form
    if (formSteps.length > 0) {
        showStep(currentStep);
    }
    
    // Next button click handler
    nextBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Validate current step before proceeding
            if (validateStep(currentStep)) {
                currentStep++;
                showStep(currentStep);
                updateProgressBar();
            }
        });
    });
    
    // Previous button click handler
    prevBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            currentStep--;
            showStep(currentStep);
            updateProgressBar();
        });
    });
    
    // Show current step and hide others
    function showStep(step) {
        formSteps.forEach((formStep, index) => {
            formStep.classList.remove('active');
            if (index === step) {
                formStep.classList.add('active');
            }
        });
        
        // Update button visibility
        const isLastStep = step === formSteps.length - 1;
        const isFirstStep = step === 0;
        
        document.querySelectorAll('.btn-next').forEach(btn => {
            btn.textContent = isLastStep ? 'Create Account' : 'Next';
        });
        
        document.querySelectorAll('.btn-prev').forEach(btn => {
            btn.style.display = isFirstStep ? 'none' : 'block';
        });
        
        // If it's the last step, change the next button to submit
        if (isLastStep) {
            const submitBtn = document.querySelector('.btn-next[type="submit"]');
            if (submitBtn) {
                submitBtn.type = 'submit';
            }
        }
    }
    
    // Update progress bar
    function updateProgressBar() {
        progressSteps.forEach((step, index) => {
            if (index < currentStep + 1) {
                step.classList.add('completed');
                step.classList.remove('active');
            } else if (index === currentStep) {
                step.classList.add('active');
                step.classList.remove('completed');
            } else {
                step.classList.remove('active', 'completed');
            }
        });
    }
    
    // Validate current step
    function validateStep(step) {
        let isValid = true;
        const currentFormStep = formSteps[step];
        
        // Get all required fields in current step
        const requiredFields = currentFormStep.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                showFieldError(field, 'This field is required');
            } else {
                // Additional validation based on field type
                if (field.type === 'email' && !isValidEmail(field.value)) {
                    isValid = false;
                    showFieldError(field, 'Please enter a valid email address');
                } else if (field.type === 'tel' && field.id === 'phone' && !isValidPhilippinePhone(field.value)) {
                    isValid = false;
                    showFieldError(field, 'Please enter a valid phone number (Format: 09XXXXXXXXX)');
                } else if (field.type === 'password' && field.id === 'password') {
                    const password = field.value;
                    const confirmPassword = document.getElementById('confirmPassword')?.value;
                    
                    if (password.length < 8) {
                        isValid = false;
                        showFieldError(field, 'Password must be at least 8 characters long');
                    } else if (confirmPassword && password !== confirmPassword) {
                        isValid = false;
                        showFieldError(document.getElementById('confirmPassword'), 'Passwords do not match');
                    } else {
                        clearFieldError(field);
                        if (confirmPassword) clearFieldError(document.getElementById('confirmPassword'));
                    }
                } else {
                    clearFieldError(field);
                }
            }
        });
        
        return isValid;
    }
    
    // Show field error
    function showFieldError(field, message) {
        const formGroup = field.closest('.form-group') || field.closest('.mb-3');
        if (!formGroup) return;
        
        // Remove existing error message
        const existingError = formGroup.querySelector('.invalid-feedback, .error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Add error class to field
        field.classList.add('is-invalid', 'error');
        field.classList.remove('is-valid');
        
        // Create and append error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback error-message';
        errorDiv.textContent = message;
        
        // Append after input wrapper if exists
        const inputWrapper = field.closest('.input-wrapper');
        if (inputWrapper) {
            inputWrapper.parentNode.insertBefore(errorDiv, inputWrapper.nextSibling);
        } else {
            formGroup.appendChild(errorDiv);
        }
    }
    
    // Clear field error
    function clearFieldError(field) {
        const formGroup = field.closest('.form-group') || field.closest('.mb-3');
        if (!formGroup) return;
        
        field.classList.remove('is-invalid', 'error');
        
        const errorMessage = formGroup.querySelector('.invalid-feedback, .error-message');
        if (errorMessage) {
            errorMessage.remove();
        }
    }
    
    // Password strength checker
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });
    }
    
    // Check password strength
    function checkPasswordStrength(password) {
        const strengthMeter = document.querySelector('.strength-meter-fill');
        const strengthText = document.querySelector('.strength-text');
        
        if (!strengthMeter || !strengthText) return;
        
        // Reset classes
        strengthMeter.parentElement.className = 'strength-meter';
        
        // Calculate strength
        let strength = 0;
        let messages = [];
        
        // Length check
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        
        // Contains lowercase
        if (/[a-z]/.test(password)) strength++;
        
        // Contains uppercase
        if (/[A-Z]/.test(password)) strength++;
        
        // Contains number
        if (/[0-9]/.test(password)) strength++;
        
        // Contains special character
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        // Update UI based on strength
        if (password.length === 0) {
            strengthMeter.style.width = '0%';
            strengthText.textContent = '';
            strengthMeter.parentElement.className = 'strength-meter';
            return;
        } else if (strength <= 2) {
            // Weak
            strengthMeter.style.width = '33%';
            strengthMeter.parentElement.className = 'strength-meter strength-weak';
            strengthText.textContent = 'Weak';
        } else if (strength <= 4) {
            // Medium
            strengthMeter.style.width = '66%';
            strengthMeter.parentElement.className = 'strength-meter strength-medium';
            strengthText.textContent = 'Medium';
        } else {
            // Strong
            strengthMeter.style.width = '100%';
            strengthMeter.parentElement.className = 'strength-meter strength-strong';
            strengthText.textContent = 'Strong';
        }
    }
    
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const inputId = this.getAttribute('data-target');
            const input = document.getElementById(inputId);
            
            if (input) {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                
                // Toggle icon
                this.querySelector('i').classList.toggle('bi-eye');
                this.querySelector('i').classList.toggle('bi-eye-slash');
            }
        });
    });
    
    // Form submission
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Check if phone is verified
            if (!isPhoneVerified) {
                showError('phone', 'Please verify your phone number first');
                phoneInput.focus();
                return;
            }
            
            // Validate form
            if (!validateForm()) {
                return;
            }
            
            // Get form data
            const formData = new FormData(registerForm);
            
            // Check if passwords match
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                showError('confirmPassword', 'Passwords do not match');
                return;
            }
            
            // Check if terms are accepted
            const termsCheckbox = document.getElementById('terms');
            if (!termsCheckbox.checked) {
                showError('terms', 'Please accept the Terms of Service and Privacy Policy');
                termsCheckbox.focus();
                return;
            }
            
            // Show loading state
            const submitButton = registerForm.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating account...';
            
            // Send data to PHP backend
            fetch('php/register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
                
                if (data.success) {
                    // Show success modal
                    successModal.show();
                    
                    // Reset form
                    registerForm.reset();
                } else {
                    // Show error message in a nice way
                    showError('email', data.message || 'Registration failed. Please try again.');
                }
            })
            .catch(error => {
                                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
                showError('email', 'An error occurred. Please try again.');
            });
        });
    }
    
    // Validate entire form
    function validateForm() {
        let isValid = true;
        
        // Full Name
        const fullName = document.getElementById('fullName');
        if (!fullName.value.trim()) {
            showError('fullName', 'Please enter your full name');
            isValid = false;
        } else {
            clearError('fullName');
        }
        
        // Email
        const email = document.getElementById('email');
        if (!email.value.trim()) {
            showError('email', 'Please enter your email address');
            isValid = false;
        } else if (!isValidEmail(email.value)) {
            showError('email', 'Please enter a valid email address');
            isValid = false;
        } else {
            clearError('email');
        }
        
        // Phone
        const phone = document.getElementById('phone');
        if (!phone.value.trim()) {
            showError('phone', 'Please enter your phone number');
            isValid = false;
        } else if (!isValidPhilippinePhone(phone.value)) {
            showError('phone', 'Please enter a valid phone number (Format: 09123456789)');
            isValid = false;
        } else {
            clearError('phone');
            // Format the phone number
            phone.value = formatPhilippinePhone(phone.value);
        }
        
        // Password
        const password = document.getElementById('password');
        if (!password.value) {
            showError('password', 'Please enter a password');
            isValid = false;
        } else if (password.value.length < 8) {
            showError('password', 'Password must be at least 8 characters');
            isValid = false;
        } else {
            clearError('password');
        }
        
        // Confirm Password
        const confirmPassword = document.getElementById('confirmPassword');
        if (!confirmPassword.value) {
            showError('confirmPassword', 'Please confirm your password');
            isValid = false;
        } else if (password.value !== confirmPassword.value) {
            showError('confirmPassword', 'Passwords do not match');
            isValid = false;
        } else {
            clearError('confirmPassword');
        }
        
        return isValid;
    }
    
    // Show error message
    function showError(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (!field) return;
        
        const formGroup = field.closest('.form-group');
        if (!formGroup) return;
        
        // Remove existing error messages
        const existingError = formGroup.querySelector('.error-message, .invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
        
        // Add error classes
        field.classList.add('error', 'is-invalid');
        field.classList.remove('is-valid');
        
        // Create error message element
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message invalid-feedback';
        errorDiv.textContent = message;
        
        // Append error message after input wrapper or directly in form group
        const inputWrapper = field.closest('.input-wrapper');
        if (inputWrapper) {
            inputWrapper.parentNode.insertBefore(errorDiv, inputWrapper.nextSibling);
        } else {
            formGroup.appendChild(errorDiv);
        }
    }
    
    // Clear error message
    function clearError(fieldId) {
        const field = document.getElementById(fieldId);
        if (!field) return;
        
        const formGroup = field.closest('.form-group');
        if (!formGroup) return;
        
        // Remove error classes
        field.classList.remove('error', 'is-invalid');
        
        // Remove error message
        const errorMessage = formGroup.querySelector('.error-message, .invalid-feedback');
        if (errorMessage) {
            errorMessage.remove();
        }
    }
    
    // Real-time validation
    const inputs = ['fullName', 'email', 'phone', 'password', 'confirmPassword'];
    inputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('blur', function() {
                if (this.value.trim()) {
                    if (inputId === 'email' && !isValidEmail(this.value)) {
                        showError(inputId, 'Please enter a valid email address');
                    } else if (inputId === 'phone' && !isValidPhilippinePhone(this.value)) {
                        showError(inputId, 'Please enter a valid phone number (Format: 09123456789)');
                    } else if (inputId === 'password' && this.value.length < 8) {
                        showError(inputId, 'Password must be at least 8 characters');
                    } else if (inputId === 'confirmPassword') {
                        const password = document.getElementById('password').value;
                        if (this.value !== password) {
                            showError(inputId, 'Passwords do not match');
                        } else {
                            clearError(inputId);
                        }
                    } else {
                        clearError(inputId);
                    }
                }
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('error') || this.classList.contains('is-invalid')) {
                    clearError(inputId);
                }
                
                // Auto-format phone number as user types
                if (inputId === 'phone') {
                    let value = this.value.replace(/[^\d]/g, ''); // Remove non-digits
                    
                    // If user starts typing and doesn't start with 0, prepend 09
                    if (value.length > 0 && value[0] !== '0') {
                        value = '09' + value;
                    }
                    
                    // If starts with 0 but not 09, convert to 09
                    if (value.length > 1 && value[0] === '0' && value[1] !== '9') {
                        value = '09' + value.substring(1);
                    }
                    
                    // Limit to 11 digits
                    if (value.length > 11) {
                        value = value.substring(0, 11);
                    }
                    
                    this.value = value;
                }
            });
        }
    });
    
    // Show alert message
    function showAlert(message, type) {
        // Remove any existing alerts
        const existingAlert = document.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
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
    
    // Helper function to validate email
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    // Helper function to validate Philippine phone numbers
    function isValidPhilippinePhone(phone) {
        // Remove all spaces, dashes, and parentheses
        const cleaned = phone.replace(/[\s\-\(\)]/g, '');
        
        // Only accept 09XXXXXXXXX format (11 digits starting with 09)
        const pattern = /^09\d{9}$/;
        
        return pattern.test(cleaned);
    }
    
    // Format phone number to Philippine format
    function formatPhilippinePhone(phone) {
        // Remove all non-numeric characters except +
        const cleaned = phone.replace(/[^\d+]/g, '');
        
        // If it starts with +63
        if (cleaned.startsWith('+63')) {
            const number = cleaned.substring(3);
            if (number.length === 10) {
                return `+63 ${number.substring(0, 3)} ${number.substring(3, 6)} ${number.substring(6)}`;
            }
        }
        // If it starts with 63
        else if (cleaned.startsWith('63') && !cleaned.startsWith('+')) {
            const number = cleaned.substring(2);
            if (number.length === 10) {
                return `+63 ${number.substring(0, 3)} ${number.substring(3, 6)} ${number.substring(6)}`;
            }
        }
        // If it starts with 09
        else if (cleaned.startsWith('09')) {
            const number = cleaned.substring(1);
            if (number.length === 10) {
                return `+63 ${number.substring(0, 3)} ${number.substring(3, 6)} ${number.substring(6)}`;
            }
        }
        // If it starts with 9
        else if (cleaned.startsWith('9') && cleaned.length === 10) {
            return `+63 ${cleaned.substring(0, 3)} ${cleaned.substring(3, 6)} ${cleaned.substring(6)}`;
        }
        
        return phone; // Return original if format is not recognized
    }
    
    // Initialize date picker for date of birth
    const dobInput = document.getElementById('dob');
    if (dobInput) {
        // In a real app, you would initialize a date picker here
        // For example, using flatpickr or another date picker library
        // flatpickr("#dob", { maxDate: "today" });
        
        // For now, just set the max date attribute
        const today = new Date();
        const dd = String(today.getDate()).padStart(2, '0');
        const mm = String(today.getMonth() + 1).padStart(2, '0'); // January is 0!
        const yyyy = today.getFullYear() - 13; // Minimum age 13
        const maxDate = yyyy + '-' + mm + '-' + dd;
        dobInput.setAttribute('max', maxDate);
    }
    
    // ========== OTP VERIFICATION FUNCTIONALITY ==========
    
    const sendOtpBtn = document.getElementById('sendOtpBtn');
    const phoneInput = document.getElementById('phone');
    const otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    const verifyOtpBtn = document.getElementById('verifyOtpBtn');
    const resendOtpBtn = document.getElementById('resendOtpBtn');
    const goToLoginBtn = document.getElementById('goToLoginBtn');
    
    // Send OTP
    if (sendOtpBtn) {
        sendOtpBtn.addEventListener('click', function() {
            const phone = phoneInput.value.trim();
            
            if (!phone) {
                showError('phone', 'Please enter your phone number');
                return;
            }
            
            if (!isValidPhilippinePhone(phone)) {
                showError('phone', 'Please enter a valid phone number (Format: 09123456789)');
                return;
            }
            
            // Disable button and show loading
            sendOtpBtn.disabled = true;
            sendOtpBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';
            
            // Send OTP request
            const formData = new FormData();
            formData.append('phone', phone);
            
            fetch('php/send_otp.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                sendOtpBtn.disabled = false;
                sendOtpBtn.innerHTML = '<i class="bi bi-shield-check me-1"></i>Verify';
                
                if (data.success) {
                    verifiedPhone = data.phone;
                    document.getElementById('displayPhone').textContent = verifiedPhone;
                    otpModal.show();
                    startOtpTimer();
                    
                    // Clear OTP inputs
                    document.querySelectorAll('.otp-input').forEach(input => {
                        input.value = '';
                        input.classList.remove('error');
                    });
                    document.getElementById('otp1').focus();
                    
                    // For development: Show OTP in console
                    if (data.debug_otp) {
                                                Swal.fire({
                            icon: 'info',
                            title: '📱 TEST MODE',
                            html: `Your OTP is <strong>${data.debug_otp}</strong><br><br>Enter this code in the verification modal.`,
                            confirmButtonColor: '#10b981'
                        });
                    }
                } else {
                    // Show detailed error message
                    let errorMsg = data.message || 'Failed to send OTP';
                    let errorHtml = errorMsg;
                    if (data.error) {
                                                errorHtml += '<br><br><small>Technical details: ' + data.error + '</small>';
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'OTP Error',
                        html: errorHtml,
                        confirmButtonColor: '#10b981'
                    });
                    showError('phone', data.message || 'Failed to send OTP');
                }
            })
            .catch(error => {
                                sendOtpBtn.disabled = false;
                sendOtpBtn.innerHTML = '<i class="bi bi-shield-check me-2"></i>Send OTP';
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Network error occurred. Please check your connection and try again.',
                    confirmButtonColor: '#10b981'
                });
                showError('phone', 'Network error');
            });
        });
    }
    
    const otpInput = document.getElementById('otpInput');
    
    // Verify OTP
    if (verifyOtpBtn && otpInput) {
        verifyOtpBtn.addEventListener('click', function() {
            const otp = otpInput.value.trim();
            const phone = phoneInput.value.trim();

            if (!otp || otp.length !== 6) {
                Swal.fire({
                    title: 'Invalid Code',
                    text: 'Please enter the 6-digit code',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }

            verifyOtpBtn.disabled = true;
            verifyOtpBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';

            const formData = new FormData();
            formData.append('phone', phone);
            formData.append('otp', otp);

            fetch('php/verify_otp.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    isPhoneVerified = true;
                    otpModal.hide();
                    
                    // Show verification status
                    document.getElementById('phoneVerificationStatus').style.display = 'block';
                    sendOtpBtn.style.display = 'none';
                    phoneInput.readOnly = true;
                    phoneInput.style.paddingRight = '12px';
                    
                    if (otpTimer) clearInterval(otpTimer);
                    
                    Swal.fire({
                        title: 'Success',
                        text: 'Phone number verified successfully!',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        timer: 2000
                    });
                } else {
                    Swal.fire({
                        title: 'Verification Failed',
                        text: data.message || 'Invalid or expired code',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                                Swal.fire({
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            })
            .finally(() => {
                verifyOtpBtn.disabled = false;
                verifyOtpBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Verify Code';
                otpInput.value = '';
            });
        });
    }
    
    // Resend OTP
    if (resendOtpBtn) {
        resendOtpBtn.addEventListener('click', function() {
            const phone = phoneInput.value.trim();
            
            resendOtpBtn.disabled = true;
            resendOtpBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

            const formData = new FormData();
            formData.append('phone', phone);

            fetch('php/send_otp.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    startOtpTimer();
                    
                    if (data.debug_otp) {
                                                Swal.fire({
                            title: 'Testing Mode',
                            text: 'Your new OTP is ' + data.debug_otp,
                            icon: 'info',
                            confirmButtonText: 'OK'
                        });
                    }
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Failed to resend OTP',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                                Swal.fire({
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            })
            .finally(() => {
                resendOtpBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Resend Code';
            });
        });
    }
    
    // OTP Timer
    function startOtpTimer() {
        otpExpiryTime = Date.now() + (5 * 60 * 1000); // 5 minutes
        
        if (otpTimer) {
            clearInterval(otpTimer);
        }
        
        otpTimer = setInterval(function() {
            const now = Date.now();
            const timeLeft = otpExpiryTime - now;
            
            if (timeLeft <= 0) {
                clearInterval(otpTimer);
                document.getElementById('otpTimer').innerHTML = '<span class="text-danger">OTP expired. Please resend.</span>';
                return;
            }
            
            const minutes = Math.floor(timeLeft / 60000);
            const seconds = Math.floor((timeLeft % 60000) / 1000);
            
            document.getElementById('otpTimer').textContent = `Code expires in ${minutes}:${seconds.toString().padStart(2, '0')}`;
        }, 1000);
    }
    
    // Go to Login button
    if (goToLoginBtn) {
        goToLoginBtn.addEventListener('click', function() {
            window.location.href = 'login.php';
        });
    }
});
