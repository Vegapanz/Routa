// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const inputs = form.querySelectorAll('input[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    });

    // Email validation
    const emailInput = form.querySelector('input[type="email"]');
    if (emailInput && emailInput.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailInput.value)) {
            isValid = false;
            emailInput.classList.add('is-invalid');
        }
    }

    // Password validation
    const passwordInput = form.querySelector('input[type="password"]');
    if (passwordInput && passwordInput.value) {
        if (passwordInput.value.length < 6) {
            isValid = false;
            passwordInput.classList.add('is-invalid');
        }
    }

    return isValid;
}

// Handle form submission
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            if (validateForm('loginForm')) {
                // Submit form using AJAX
                const formData = new FormData(loginForm);
                fetch('php/login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.reload();
                        }
                    } else {
                        Swal.fire({
                            title: 'Login Failed',
                            text: data.message,
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
                });
            }
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            if (validateForm('registerForm')) {
                // Submit form using AJAX
                const formData = new FormData(registerForm);
                fetch('php/register.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success',
                            text: 'Registration successful! Please login.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            $('#registerModal').modal('hide');
                            $('#loginModal').modal('show');
                        });
                    } else {
                        Swal.fire({
                            title: 'Registration Failed',
                            text: data.message,
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
                });
            }
        });
    }

    // Clean up validation state when modal is hidden
    $('.modal').on('hidden.bs.modal', function () {
        const forms = this.querySelectorAll('form');
        forms.forEach(form => {
            form.reset();
            const inputs = form.querySelectorAll('.is-invalid');
            inputs.forEach(input => input.classList.remove('is-invalid'));
        });
    });
});