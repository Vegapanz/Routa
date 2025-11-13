// Driver Application Form JavaScript

document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 4;
    
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('driverApplicationForm');
    
    // Check if elements exist
    if (!form || !prevBtn || !nextBtn || !submitBtn) {
        console.error('Required form elements not found');
        return;
    }
    
    // File upload handlers
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            const fileNameSpan = document.querySelector(`[data-for="${e.target.id}"]`);
            const uploadBox = e.target.closest('.upload-box');
            
            if (fileName) {
                fileNameSpan.textContent = fileName;
                uploadBox.classList.add('active');
            } else {
                fileNameSpan.textContent = '';
                uploadBox.classList.remove('active');
            }
        });
    });
    
    // Show step
    function showStep(step) {
        console.log('Showing step:', step);
        
        // Hide all steps
        document.querySelectorAll('.form-step').forEach(el => {
            el.classList.remove('active');
            el.style.display = 'none';
        });
        
        // Show current step
        const currentFormStep = document.querySelector(`.form-step[data-step="${step}"]`);
        console.log('Current form step:', currentFormStep);
        if (currentFormStep) {
            currentFormStep.classList.add('active');
            currentFormStep.style.display = 'block';
        }
        
        // Update progress indicators
        document.querySelectorAll('.progress-steps .step').forEach((el, index) => {
            const stepNum = index + 1;
            if (stepNum < step) {
                el.classList.add('completed');
                el.classList.remove('active');
            } else if (stepNum === step) {
                el.classList.add('active');
                el.classList.remove('completed');
            } else {
                el.classList.remove('active', 'completed');
            }
        });
        
        // Update button visibility
        if (step === 1) {
            prevBtn.style.display = 'none';
        } else {
            prevBtn.style.display = 'inline-block';
        }
        
        if (step === totalSteps) {
            nextBtn.style.display = 'none';
            submitBtn.style.display = 'inline-block';
        } else {
            nextBtn.style.display = 'inline-block';
            submitBtn.style.display = 'none';
        }
    }
    
    // Validate current step with detailed error messages
    function validateStep(step) {
        const currentFormStep = document.querySelector(`.form-step[data-step="${step}"]`);
        const requiredFields = currentFormStep.querySelectorAll('[required]');
        let isValid = true;
        let errors = [];
        
        requiredFields.forEach(field => {
            const fieldName = field.name || field.id;
            const label = field.closest('.col-md-4, .col-md-6, .col-12')?.querySelector('label')?.textContent.replace('*', '').trim() || fieldName;
            
            // Check file upload
            if (field.type === 'file') {
                if (!field.files || field.files.length === 0) {
                    isValid = false;
                    const uploadBox = field.closest('.upload-box');
                    if (uploadBox) {
                        uploadBox.style.borderColor = '#dc3545';
                        uploadBox.style.backgroundColor = '#fff5f5';
                    }
                    
                    // Get document name from h5 tag
                    const docName = uploadBox?.querySelector('h5')?.textContent || fieldName;
                    errors.push(`• ${docName} is required`);
                    
                    // Remove error styling on file change
                    field.addEventListener('change', function() {
                        if (this.files && this.files.length > 0) {
                            const box = this.closest('.upload-box');
                            if (box) {
                                box.style.borderColor = '';
                                box.style.backgroundColor = '';
                            }
                        }
                    }, { once: true });
                }
            }
            // Check if field is empty (not checkbox or file)
            else if (!field.value.trim() && field.type !== 'checkbox') {
                isValid = false;
                field.classList.add('is-invalid');
                errors.push(`• ${label} is required`);
                
                // Add error message below field
                addErrorMessage(field, `${label} is required`);
                
                // Remove invalid class on input
                field.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                    removeErrorMessage(this);
                }, { once: true });
            } 
            // Check checkbox
            else if (field.type === 'checkbox' && !field.checked) {
                isValid = false;
                field.classList.add('is-invalid');
                errors.push(`• You must agree to the ${label}`);
                
                field.addEventListener('change', function() {
                    this.classList.remove('is-invalid');
                }, { once: true });
            }
            // Check pattern validity
            else if (field.value && field.validity && !field.validity.valid) {
                isValid = false;
                field.classList.add('is-invalid');
                
                let errorMsg = field.getAttribute('title') || `Invalid format for ${label}`;
                errors.push(`• ${label}: ${errorMsg}`);
                
                // Add error message below field
                addErrorMessage(field, errorMsg);
                
                field.addEventListener('input', function() {
                    if (this.validity.valid) {
                        this.classList.remove('is-invalid');
                        removeErrorMessage(this);
                    }
                }, { once: true });
            } else {
                field.classList.remove('is-invalid');
                removeErrorMessage(field);
            }
        });
        
        if (!isValid) {
            // Show error summary panel at top of page
            const errorPanel = document.getElementById('errorSummaryPanel');
            const errorList = document.getElementById('errorSummaryList');
            if (errorPanel && errorList) {
                errorList.innerHTML = errors.map(err => `<div style="margin: 5px 0;">${err}</div>`).join('');
                errorPanel.classList.remove('d-none');
                errorPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            
            // Show detailed error notification
            const errorHtml = `
                <div style="text-align: left;">
                    <strong>Please fix the following errors:</strong><br>
                    ${errors.slice(0, 3).join('<br>')}
                    ${errors.length > 3 ? '<br>• <em>...and ' + (errors.length - 3) + ' more</em>' : ''}
                </div>
            `;
            showNotification('error', 'Validation Errors', errorHtml);
            
            // Scroll to first invalid field
            const firstInvalid = currentFormStep.querySelector('.is-invalid');
            if (firstInvalid) {
                setTimeout(() => {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }, 500);
            }
        } else {
            // Hide error panel if validation passes
            const errorPanel = document.getElementById('errorSummaryPanel');
            if (errorPanel) {
                errorPanel.classList.add('d-none');
            }
        }
        
        return isValid;
    }
    
    // Helper function to add error message below field
    function addErrorMessage(field, message) {
        removeErrorMessage(field); // Remove existing first
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.style.color = '#dc3545';
        errorDiv.style.fontSize = '0.875rem';
        errorDiv.style.marginTop = '0.25rem';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
    
    // Helper function to remove error message
    function removeErrorMessage(field) {
        const existingError = field.parentNode.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
    }
    
    // Next button
    nextBtn.addEventListener('click', function() {
        if (validateStep(currentStep)) {
            currentStep++;
            if (currentStep > totalSteps) {
                currentStep = totalSteps;
            }
            showStep(currentStep);
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
    
    // Previous button
    prevBtn.addEventListener('click', function() {
        currentStep--;
        if (currentStep < 1) {
            currentStep = 1;
        }
        showStep(currentStep);
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('🔵 SUBMIT BUTTON CLICKED - Form submitted, current step:', currentStep);
        
        // Extra validation logging
        const termsCheckbox = document.getElementById('termsCheck');
        const privacyCheckbox = document.getElementById('privacyCheck');
        console.log('Terms checked:', termsCheckbox ? termsCheckbox.checked : 'NOT FOUND');
        console.log('Privacy checked:', privacyCheckbox ? privacyCheckbox.checked : 'NOT FOUND');
        
        if (!validateStep(currentStep)) {
            console.log('❌ Validation failed for step', currentStep);
            // Validation function now shows detailed errors on screen
            return;
        }
        
        console.log('✅ Validation passed, preparing to submit...');
        
        // Get form data
        const formData = new FormData(this);
        
        // Log form data for debugging
        console.log('📦 Form data being sent:');
        for (let [key, value] of formData.entries()) {
            if (value instanceof File) {
                console.log(key + ':', value.name, '(' + value.size + ' bytes)');
            } else {
                console.log(key + ':', value);
            }
        }
        
        // Show loading state
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
        submitBtn.disabled = true;
        
        console.log('🚀 Sending request to server...');
        
        // Submit to backend
        fetch('php/submit_driver_application.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('📡 Server responded with status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('📨 Server response data:', data);
            submitBtn.innerHTML = 'Submit Application';
            submitBtn.disabled = false;
            
            if (data.success) {
                // Show success message
                console.log('✅ SUCCESS! Application submitted.');
                showNotification('success', 'Application Submitted!', data.message);
                
                // Redirect after a delay
                setTimeout(() => {
                    window.location.href = 'be-a-driver.php';
                }, 3000);
            } else {
                // Show error message
                console.log('❌ Server returned error:', data.message);
                showNotification('error', 'Submission Failed', data.message);
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('❌ FETCH ERROR:', error);
            submitBtn.innerHTML = 'Submit Application';
            submitBtn.disabled = false;
            alert('Network error: ' + error.message + '\n\nPlease check if XAMPP is running and try again.');
            showNotification('error', 'Submission Failed', 'An error occurred while submitting your application. Please try again.');
        });
    });
    
    // Enhanced notification function with HTML support
    function showNotification(type, title, message) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 350px; max-width: 500px; box-shadow: 0 8px 16px rgba(0,0,0,0.15); animation: slideIn 0.3s;';
        notification.innerHTML = `
            <div style="display: flex; align-items: start;">
                <div style="margin-right: 10px; font-size: 1.5rem;">
                    ${type === 'success' ? '✅' : '❌'}
                </div>
                <div style="flex: 1;">
                    <strong style="font-size: 1.1rem;">${title}</strong><br>
                    <div style="margin-top: 8px;">${message}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" style="margin-left: 10px;"></button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after longer time for error messages
        const timeout = type === 'error' ? 8000 : 5000;
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, timeout);
    }
    
    // Real-time input validation and formatting
    function setupInputValidation() {
        // Phone number formatting - Strictly 11 digits starting with 09
        const phoneInputs = document.querySelectorAll('input[name="phone"], input[name="emergencyPhone"]');
        phoneInputs.forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
                
                // Don't modify if empty
                if (value === '') {
                    e.target.value = '';
                    return;
                }
                
                // Ensure it starts with 09
                if (value.length > 0 && value[0] !== '0') {
                    value = '0' + value;
                }
                if (value.length > 1 && value[1] !== '9') {
                    value = '09' + value.slice(2);
                }
                
                // Limit to 11 digits
                if (value.length > 11) {
                    value = value.slice(0, 11);
                }
                
                e.target.value = value;
            });
        });

        // Plate number uppercase
        const plateInput = document.querySelector('input[name="plateNumber"]');
        if (plateInput) {
            plateInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            });
        }

        // License number formatting
        const licenseInput = document.querySelector('input[name="licenseNumber"]');
        if (licenseInput) {
            licenseInput.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
                // Auto-format: X00-00-000000
                if (value.length > 3 && value[3] !== '-') {
                    value = value.slice(0, 3) + '-' + value.slice(3);
                }
                if (value.length > 6 && value[6] !== '-') {
                    value = value.slice(0, 6) + '-' + value.slice(6);
                }
                e.target.value = value.slice(0, 15);
            });
        }

        // Franchise number formatting
        const franchiseInput = document.querySelector('input[name="franchiseNumber"]');
        if (franchiseInput) {
            franchiseInput.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
                // Auto-format: XX-0000-00000
                if (value.length > 2 && value[2] !== '-') {
                    value = value.slice(0, 2) + '-' + value.slice(2);
                }
                if (value.length > 7 && value[7] !== '-') {
                    value = value.slice(0, 7) + '-' + value.slice(7);
                }
                e.target.value = value.slice(0, 15);
            });
        }

        // Zip code - numbers only
        const zipInput = document.querySelector('input[name="zipCode"]');
        if (zipInput) {
            zipInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4);
            });
        }

        // Year validation
        const yearInput = document.querySelector('input[name="year"]');
        if (yearInput) {
            yearInput.addEventListener('input', function(e) {
                const value = parseInt(e.target.value);
                if (value < 2000) e.target.value = 2000;
                if (value > 2025) e.target.value = 2025;
            });
        }

        // Name fields - letters only
        const nameFields = ['firstName', 'middleName', 'lastName', 'emergencyName', 'barangay', 'city', 'make'];
        nameFields.forEach(fieldName => {
            const field = document.querySelector(`input[name="${fieldName}"]`);
            if (field) {
                field.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/[^A-Za-zñÑáéíóúÁÉÍÓÚ\s.-]/g, '');
                });
            }
        });

        // Email - lowercase
        const emailInput = document.querySelector('input[name="email"]');
        if (emailInput) {
            emailInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.toLowerCase();
            });
        }

        // Textarea character counter
        const textarea = document.querySelector('textarea[name="previousExperience"]');
        if (textarea) {
            const counter = document.createElement('div');
            counter.className = 'form-text text-end';
            counter.style.marginTop = '-10px';
            counter.textContent = '0/500 characters';
            textarea.parentNode.insertBefore(counter, textarea.nextSibling);
            
            textarea.addEventListener('input', function(e) {
                const length = e.target.value.length;
                counter.textContent = `${length}/500 characters`;
                counter.style.color = length > 450 ? '#dc3545' : '#6c757d';
            });
        }
    }

    // Add invalid feedback and notification styles
    const style = document.createElement('style');
    style.textContent = `
        .is-invalid {
            border-color: #dc3545 !important;
            background-color: #fff5f5 !important;
            animation: shake 0.3s;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .form-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        .invalid-feedback {
            color: #dc3545 !important;
            font-size: 0.875rem;
            font-weight: 500;
            margin-top: 0.25rem;
        }

        /* Valid input styling - GREEN */
        input:valid:not(:placeholder-shown):not(.is-invalid),
        select:valid:not(.is-invalid),
        input[type="date"]:valid:not(.is-invalid) {
            border-color: #10b981 !important;
            background-color: #f0fdf4 !important;
        }

        /* Date input with value */
        input[type="date"]:not(:placeholder-shown) {
            background-color: white;
            color: #1e293b;
        }

        input:invalid:not(:placeholder-shown),
        select:invalid {
            border-color: #ffc107;
        }

        /* Focus state - GREEN glow */
        .form-control:focus,
        .form-select:focus,
        input:focus:not([type="checkbox"]),
        select:focus,
        textarea:focus {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.25) !important;
            outline: none !important;
        }

        /* Checkbox styling - ONLY for .custom-checkbox */
        input[type="checkbox"].custom-checkbox {
            cursor: pointer !important;
            flex-shrink: 0 !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
        }

        input[type="checkbox"].custom-checkbox:checked {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
        }

        input[type="checkbox"].custom-checkbox:focus {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.25) !important;
            outline: none !important;
        }

        /* Invalid focus - RED glow */
        .is-invalid:focus {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }

        /* Date input specific */
        input[type="date"] {
            background-color: white !important;
            color: #1e293b !important;
        }

        input[type="date"]:focus {
            background-color: white !important;
        }
    `;
    document.head.appendChild(style);
    
    // Initialize validation
    setupInputValidation();
    
    // Fix checkbox functionality
    const checkboxes = document.querySelectorAll('.custom-checkbox');
    checkboxes.forEach(checkbox => {
        // Ensure checkbox works properly
        checkbox.addEventListener('click', function(e) {
            // Let the default behavior happen, just log for debugging
            console.log('Checkbox clicked:', this.id, 'Checked:', this.checked);
        });
        
        // Force re-render on change
        checkbox.addEventListener('change', function() {
            console.log('Checkbox changed:', this.id, 'Checked:', this.checked);
            // Force visual update
            if (this.checked) {
                this.setAttribute('checked', 'checked');
            } else {
                this.removeAttribute('checked');
            }
        });
    });
    
    // Initialize first step - make sure it shows
    console.log('Initializing driver application form');
    showStep(currentStep);
    
    // Force show first step if still hidden
    setTimeout(() => {
        const firstStep = document.querySelector('.form-step[data-step="1"]');
        if (firstStep && !firstStep.classList.contains('active')) {
            console.log('Force activating first step');
            firstStep.classList.add('active');
        }
    }, 100);
});
