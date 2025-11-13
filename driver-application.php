<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Application - Routa</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pages/driver-application.css">
    <link rel="shortcut icon" href="assets/images/Logo.png" type="image/x-icon">
    
    <!-- Custom Checkbox Styling -->
    <style>
        /* COMPLETE RESET - Remove all browser defaults */
        input[type="checkbox"].custom-checkbox {
            /* Remove defaults */
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            
            /* Size */
            width: 22px !important;
            height: 22px !important;
            min-width: 22px !important;
            min-height: 22px !important;
            
            /* Border and background */
            border: 2px solid #cbd5e1 !important;
            border-radius: 4px !important;
            background: white !important;
            
            /* Layout */
            cursor: pointer !important;
            position: relative !important;
            flex-shrink: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            
            /* Spacing */
            margin: 0 !important;
            padding: 0 !important;
            vertical-align: middle !important;
            
            /* Transition */
            transition: all 0.2s ease !important;
        }
        
        /* Hover state */
        input[type="checkbox"].custom-checkbox:hover {
            border-color: #10b981 !important;
        }
        
        /* CHECKED STATE - Fill with green */
        input[type="checkbox"].custom-checkbox:checked {
            background: #10b981 !important;
            border-color: #10b981 !important;
        }
        
        /* WHITE CHECKMARK - Using CSS border trick */
        input[type="checkbox"].custom-checkbox:checked::before {
            content: '' !important;
            position: absolute !important;
            left: 7px !important;
            top: 3px !important;
            width: 5px !important;
            height: 10px !important;
            border: solid white !important;
            border-width: 0 3px 3px 0 !important;
            transform: rotate(45deg) !important;
            display: block !important;
        }
        
        /* Focus state */
        input[type="checkbox"].custom-checkbox:focus {
            outline: none !important;
            border-color: #10b981 !important;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2) !important;
        }
        
        /* Invalid state */
        input[type="checkbox"].custom-checkbox.is-invalid {
            border-color: #dc3545 !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/images/Logo.png" alt="Routa Logo" height="30"> Routa
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#download">Download App</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="be-a-driver.php">Be a Driver</a></li>
                    <li class="nav-item"><a class="nav-link btn-book-ride" href="login.php">Book a Ride</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Application Form Section -->
    <section class="application-section">
        <div class="container">
            <!-- Error Summary Panel (hidden by default) -->
            <div id="errorSummaryPanel" class="alert alert-danger d-none" role="alert" style="margin-bottom: 30px;">
                <div style="display: flex; align-items: start;">
                    <div style="font-size: 2rem; margin-right: 15px;">⚠️</div>
                    <div style="flex: 1;">
                        <h5 class="alert-heading mb-3">Please Fix the Following Errors:</h5>
                        <div id="errorSummaryList"></div>
                    </div>
                    <button type="button" class="btn-close" onclick="document.getElementById('errorSummaryPanel').classList.add('d-none')"></button>
                </div>
            </div>
            
            <!-- Progress Steps -->
            <div class="progress-steps">
                <div class="step active" data-step="1">
                    <div class="step-circle" data-number="1">
                        <i class="bi bi-check-lg step-check"></i>
                    </div>
                    <div class="step-label">Personal Info</div>
                </div>
                <div class="step-line"></div>
                <div class="step" data-step="2">
                    <div class="step-circle" data-number="2">
                        <i class="bi bi-check-lg step-check"></i>
                    </div>
                    <div class="step-label">Driver Details</div>
                </div>
                <div class="step-line"></div>
                <div class="step" data-step="3">
                    <div class="step-circle" data-number="3">
                        <i class="bi bi-check-lg step-check"></i>
                    </div>
                    <div class="step-label">Vehicle Info</div>
                </div>
                <div class="step-line"></div>
                <div class="step" data-step="4">
                    <div class="step-circle" data-number="4">
                        <i class="bi bi-check-lg step-check"></i>
                    </div>
                    <div class="step-label">Documents</div>
                </div>
            </div>

            <!-- Form Container -->
            <div class="form-container">
                <form id="driverApplicationForm" novalidate>
                    
                    <!-- Step 1: Personal Information -->
                    <div class="form-step active" data-step="1">
                        <h2 class="form-step-title">Personal Information</h2>
                        <p class="form-step-subtitle">Please provide your basic personal details</p>
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="firstName" placeholder="Juan" 
                                    pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+" 
                                    title="Only letters and spaces allowed"
                                    minlength="2" maxlength="50" required>
                                <div class="form-text">Letters only, 2-50 characters</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Middle Name</label>
                                <input type="text" class="form-control" name="middleName" placeholder="Cruz"
                                    pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]*"
                                    title="Only letters and spaces allowed"
                                    maxlength="50">
                                <div class="form-text">Optional, letters only</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="lastName" placeholder="Dela Cruz" 
                                    pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+" 
                                    title="Only letters and spaces allowed"
                                    minlength="2" maxlength="50" required>
                                <div class="form-text">Letters only, 2-50 characters</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="dateOfBirth" 
                                    min="1940-01-01" max="2007-01-01" required>
                                <div class="form-text">Must be at least 18 years old</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="phone" placeholder="09123456789"
                                    pattern="09[0-9]{9}"
                                    title="Format: 09XXXXXXXXX (11 digits)"
                                    minlength="11" maxlength="11" required>
                                <div class="form-text">Format: 09123456789</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" placeholder="juan.delacruz@email.com"
                                    pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                                    title="Enter a valid email address"
                                    maxlength="100" required>
                                <div class="form-text">Valid email format required</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password" placeholder="••••••••"
                                    minlength="8" maxlength="50" required>
                                <div class="form-text">Minimum 8 characters</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Complete Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="address" placeholder="123 Main Street, Block 5, Lot 10"
                                    minlength="10" maxlength="200" required>
                                <div class="form-text">Street, Building, House Number (10-200 characters)</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Barangay <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="barangay" placeholder="Barangay San Jose"
                                    pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ0-9\s.-]+"
                                    title="Letters, numbers, spaces, dots and hyphens only"
                                    minlength="3" maxlength="100" required>
                                <div class="form-text">Barangay name</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City/Municipality <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="city" placeholder="Manila"
                                    pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s.-]+"
                                    title="Letters, spaces, dots and hyphens only"
                                    minlength="3" maxlength="100" required>
                                <div class="form-text">City or Municipality name</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Zip Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="zipCode" placeholder="1000"
                                    pattern="[0-9]{4}"
                                    title="4-digit zip code"
                                    minlength="4" maxlength="4" required>
                                <div class="form-text">4-digit Philippine zip code</div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Driver Information -->
                    <div class="form-step" data-step="2">
                        <h2 class="form-step-title">Driver Information</h2>
                        <p class="form-step-subtitle">Tell us about your driving credentials</p>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Driver's License Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="licenseNumber" placeholder="N01-12-345678"
                                    pattern="[A-Z][0-9]{2}-[0-9]{2}-[0-9]{6}"
                                    title="Format: N01-12-345678 (Letter + 2 digits - 2 digits - 6 digits)"
                                    maxlength="15" required>
                                <div class="form-text">Format: N01-12-345678</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">License Expiry Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="licenseExpiry" 
                                    min="2025-01-01" required>
                                <div class="form-text">License must be valid (not expired)</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Years of Driving Experience <span class="text-danger">*</span></label>
                                <select class="form-select" name="drivingExperience" required>
                                    <option value="">Select experience</option>
                                    <option value="1">Less than 1 year</option>
                                    <option value="2">1-2 years</option>
                                    <option value="3">2-3 years</option>
                                    <option value="5">3-5 years</option>
                                    <option value="10">5-10 years</option>
                                    <option value="15">10+ years</option>
                                </select>
                            </div>
                        </div>

                        <h3 class="mt-5 mb-3 h5 fw-bold">Emergency Contact</h3>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Contact Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="emergencyName" placeholder="Maria Dela Cruz"
                                    pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+"
                                    title="Only letters and spaces allowed"
                                    minlength="2" maxlength="100" required>
                                <div class="form-text">Full name of emergency contact</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Phone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="emergencyPhone" placeholder="09123456789"
                                    pattern="09[0-9]{9}"
                                    title="Format: 09XXXXXXXXX (11 digits)"
                                    minlength="11" maxlength="11" required>
                                <div class="form-text">Format: 09123456789</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Relationship <span class="text-danger">*</span></label>
                                <select class="form-select" name="relationship" required>
                                    <option value="">Select relationship</option>
                                    <option value="spouse">Spouse</option>
                                    <option value="parent">Parent</option>
                                    <option value="sibling">Sibling</option>
                                    <option value="child">Child</option>
                                    <option value="friend">Friend</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <h3 class="mt-4 mb-3 h6">Previous Driving Experience (Optional)</h3>
                        <textarea class="form-control" name="previousExperience" rows="3" 
                            placeholder="Tell us about your previous experience as a driver (e.g., taxi, delivery, personal driver, etc.)"
                            maxlength="500"></textarea>
                        <div class="form-text">Maximum 500 characters</div>
                    </div>

                    <!-- Step 3: Vehicle Information -->
                    <div class="form-step" data-step="3">
                        <h2 class="form-step-title">Vehicle Information</h2>
                        <p class="form-step-subtitle">Provide details about your tricycle</p>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="vehicleType" required>
                                    <option value="">Select vehicle type</option>
                                    <option value="tricycle">Standard Tricycle</option>
                                    <option value="motorcycle">Motorcycle</option>
                                    <option value="car">Car</option>
                                    <option value="van">Van</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Plate Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="plateNumber" placeholder="ABC1234"
                                    pattern="[A-Z]{3}[0-9]{3,4}|[A-Z]{2}[0-9]{4,5}"
                                    title="Format: ABC1234 or AB12345"
                                    maxlength="8" required style="text-transform: uppercase;">
                                <div class="form-text">Format: ABC1234 (3 letters + 3-4 numbers)</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Franchise/TODA Number</label>
                                <input type="text" class="form-control" name="franchiseNumber" placeholder="FR-2024-12345"
                                    pattern="[A-Z]{2}-[0-9]{4}-[0-9]{5}"
                                    title="Format: FR-2024-12345"
                                    maxlength="15" style="text-transform: uppercase;">
                                <div class="form-text">Format: FR-2024-12345 (Optional)</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Make/Brand <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="make" placeholder="Honda"
                                    pattern="[A-Za-z\s-]+"
                                    title="Letters, spaces, and hyphens only"
                                    minlength="2" maxlength="50" required>
                                <div class="form-text">e.g., Honda, Yamaha, Toyota</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Model <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="model" placeholder="TMX 155"
                                    pattern="[A-Za-z0-9\s-]+"
                                    title="Letters, numbers, spaces, and hyphens only"
                                    minlength="1" maxlength="50" required>
                                <div class="form-text">e.g., TMX 155, Vios, Innova</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Year <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="year" placeholder="2020"
                                    min="2000" max="2025"
                                    title="Year must be between 2000-2025"
                                    required>
                                <div class="form-text">Vehicle year (2000-2025)</div>
                            </div>
                        </div>

                        <div class="alert alert-warning mt-4" role="alert">
                            <strong><i class="bi bi-clipboard-check me-2"></i>Vehicle Requirements Checklist:</strong>
                            <ul class="mb-0 mt-2">
                                <li>✓ Valid registration (OR/CR)</li>
                                <li>✓ Comprehensive insurance coverage</li>
                                <li>✓ Valid franchise/permit to operate</li>
                                <li>✓ Vehicle in good working condition</li>
                                <li>✓ Clean and presentable appearance</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Step 4: Documents -->
                    <div class="form-step" data-step="4">
                        <h2 class="form-step-title">Required Documents</h2>
                        <p class="form-step-subtitle">Upload the necessary documents for verification</p>
                        
                        <div class="document-upload">
                            <div class="upload-box">
                                <i class="bi bi-upload"></i>
                                <h5>Valid Driver's License</h5>
                                <p>Front and back copy</p>
                                <input type="file" id="license" name="license" accept=".pdf,.jpg,.jpeg,.png,.heic,.heif,.webp,.bmp,.tiff,.tif,.raw,.cr2,.nef,.arw,.dng,.orf,.rw2,.pef,.srw,.gif" hidden required>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('license').click()">Choose File</button>
                                <span class="file-name" data-for="license"></span>
                            </div>

                            <div class="upload-box">
                                <i class="bi bi-upload"></i>
                                <h5>Valid Government ID</h5>
                                <p>Passport, SSS, UMID, or Postal ID</p>
                                <input type="file" id="governmentId" name="governmentId" accept=".pdf,.jpg,.jpeg,.png,.heic,.heif,.webp,.bmp,.tiff,.tif,.raw,.cr2,.nef,.arw,.dng,.orf,.rw2,.pef,.srw,.gif" hidden required>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('governmentId').click()">Choose File</button>
                                <span class="file-name" data-for="governmentId"></span>
                            </div>

                            <div class="upload-box">
                                <i class="bi bi-upload"></i>
                                <h5>Vehicle Registration (OR/CR)</h5>
                                <p>Official receipt and certificate of registration</p>
                                <input type="file" id="registration" name="registration" accept=".pdf,.jpg,.jpeg,.png,.heic,.heif,.webp,.bmp,.tiff,.tif,.raw,.cr2,.nef,.arw,.dng,.orf,.rw2,.pef,.srw,.gif" hidden required>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('registration').click()">Choose File</button>
                                <span class="file-name" data-for="registration"></span>
                            </div>

                            <div class="upload-box">
                                <i class="bi bi-upload"></i>
                                <h5>Franchise/TODA Permit</h5>
                                <p>Valid franchise or permit to operate</p>
                                <input type="file" id="franchise" name="franchise" accept=".pdf,.jpg,.jpeg,.png,.heic,.heif,.webp,.bmp,.tiff,.tif,.raw,.cr2,.nef,.arw,.dng,.orf,.rw2,.pef,.srw,.gif" hidden required>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('franchise').click()">Choose File</button>
                                <span class="file-name" data-for="franchise"></span>
                            </div>

                            <div class="upload-box">
                                <i class="bi bi-upload"></i>
                                <h5>Insurance Documents</h5>
                                <p>Comprehensive insurance coverage</p>
                                <input type="file" id="insurance" name="insurance" accept=".pdf,.jpg,.jpeg,.png,.heic,.heif,.webp,.bmp,.tiff,.tif,.raw,.cr2,.nef,.arw,.dng,.orf,.rw2,.pef,.srw,.gif" hidden required>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('insurance').click()">Choose File</button>
                                <span class="file-name" data-for="insurance"></span>
                            </div>

                            <div class="upload-box">
                                <i class="bi bi-upload"></i>
                                <h5>Barangay Clearance</h5>
                                <p>Valid barangay clearance</p>
                                <input type="file" id="clearance" name="clearance" accept=".pdf,.jpg,.jpeg,.png,.heic,.heif,.webp,.bmp,.tiff,.tif,.raw,.cr2,.nef,.arw,.dng,.orf,.rw2,.pef,.srw,.gif" hidden required>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('clearance').click()">Choose File</button>
                                <span class="file-name" data-for="clearance"></span>
                            </div>

                            <div class="upload-box">
                                <i class="bi bi-upload"></i>
                                <h5>2x2 ID Photo</h5>
                                <p>Recent photo with white background</p>
                                <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png,.heic,.heif,.webp,.bmp,.tiff,.tif,.raw,.cr2,.nef,.arw,.dng,.orf,.rw2,.pef,.srw,.gif" hidden required>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('photo').click()">Choose File</button>
                                <span class="file-name" data-for="photo"></span>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4" role="alert">
                            <strong><i class="bi bi-info-circle me-2"></i>All documents should be:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Clear and readable (scanned or high-quality photos)</li>
                                <li>Valid and not expired</li>
                                <li>In PDF, JPG, or PNG format</li>
                                <li>Maximum file size: 5MB per document</li>
                            </ul>
                        </div>

                        <div class="form-check mt-4" style="display: flex; align-items: flex-start;">
                            <input class="form-check-input custom-checkbox" type="checkbox" id="termsCheck" required 
                                style="width: 20px; height: 20px; margin-top: 2px; margin-right: 10px; cursor: pointer;">
                            <label class="form-check-label" for="termsCheck" style="cursor: pointer;">
                                I agree to the <a href="#" class="text-success">Terms and Conditions</a> and <a href="#" class="text-success">Privacy Policy</a> of Routa.
                            </label>
                        </div>

                        <div class="form-check mt-3" style="display: flex; align-items: flex-start;">
                            <input class="form-check-input custom-checkbox" type="checkbox" id="consentCheck" required
                                style="width: 20px; height: 20px; margin-top: 2px; margin-right: 10px; cursor: pointer;">
                            <label class="form-check-label" for="consentCheck" style="cursor: pointer;">
                                I consent to a background check and verification of all submitted documents and information.
                            </label>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="form-navigation">
                        <button type="button" class="btn btn-outline-secondary btn-lg" id="prevBtn" style="display: none;">Previous</button>
                        <button type="button" class="btn btn-success btn-lg" id="nextBtn">Next Step</button>
                        <button type="submit" class="btn btn-success btn-lg" id="submitBtn" style="display: none;">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">© 2025 Routa. All rights reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/pages/driver-application.js"></script>
</body>
</html>
