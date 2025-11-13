<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Be a Driver - Routa</title>
    
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
    <link rel="stylesheet" href="assets/css/pages/be-a-driver.css">
    <link rel="shortcut icon" href="assets/images/Logo.png" type="image/x-icon">
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

    <!-- Hero Section -->
    <section class="driver-hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h1 class="driver-hero-title">Drive with Routa</h1>
                    <p class="driver-hero-subtitle">Join thousands of drivers earning flexible income on their own schedule. Start your journey with Routa today.</p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="driver-application.php" class="btn btn-warning btn-lg px-4 rounded-pill">
                            <span>Apply to Drive</span>
                            <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                        <a href="#learn-more" class="btn btn-outline-light btn-lg px-4 rounded-pill">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="assets/images/RoutaApp.jpg" alt="Drive with Routa" class="img-fluid shadow-lg" style="border-radius: 24px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Why Drive with Routa Section -->
    <section class="driver-benefits-section" id="learn-more">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Why Drive with Routa?</h2>
                <p class="section-subtitle">Experience the benefits of being your own boss while earning competitive income</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="benefit-card">
                        <div class="benefit-icon mint-bg">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <h3 class="benefit-title">Earn More</h3>
                        <p class="benefit-description">Competitive rates and keep 85% of your earnings. Plus weekly bonuses and incentives.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="benefit-card">
                        <div class="benefit-icon yellow-bg">
                            <i class="bi bi-clock"></i>
                        </div>
                        <h3 class="benefit-title">Flexible Schedule</h3>
                        <p class="benefit-description">Drive whenever you want. Set your own hours and work at your own pace.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="benefit-card">
                        <div class="benefit-icon mint-bg">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3 class="benefit-title">Safety First</h3>
                        <p class="benefit-description">Comprehensive insurance coverage and 24/7 support for your peace of mind.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="benefit-card">
                        <div class="benefit-icon peach-bg">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h3 class="benefit-title">Weekly Payouts</h3>
                        <p class="benefit-description">Get paid every week directly to your bank account. Fast and reliable.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Driver Requirements Section -->
    <section class="driver-requirements-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Driver Requirements</h2>
                <p class="section-subtitle">Make sure you meet these basic requirements before applying</p>
            </div>
            <div class="row g-5">
                <div class="col-lg-6">
                    <h3 class="requirements-heading">Driver Requirements</h3>
                    <ul class="requirements-list">
                        <li>
                            <div class="requirement-icon green">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <span>At least 21 years old</span>
                        </li>
                        <li>
                            <div class="requirement-icon green">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <span>Valid driver's license (at least 2 years)</span>
                        </li>
                        <li>
                            <div class="requirement-icon green">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <span>Clean driving record</span>
                        </li>
                        <li>
                            <div class="requirement-icon green">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <span>Valid government ID</span>
                        </li>
                        <li>
                            <div class="requirement-icon green">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <span>Barangay clearance</span>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <h3 class="requirements-heading">Vehicle Requirements</h3>
                    <ul class="requirements-list">
                        <li>
                            <div class="requirement-icon yellow">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <span>Tricycle in good condition</span>
                        </li>
                        <li>
                            <div class="requirement-icon yellow">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <span>Valid registration (OR/CR)</span>
                        </li>
                        <li>
                            <div class="requirement-icon yellow">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <span>Valid franchise/permit to operate</span>
                        </li>
                        <li>
                            <div class="requirement-icon yellow">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <span>Comprehensive insurance</span>
                        </li>
                        <li>
                            <div class="requirement-icon yellow">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <span>Android smartphone with GPS</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- How to Get Started Section -->
    <section class="getting-started-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">How to Get Started</h2>
                <p class="section-subtitle">Join Routa in three simple steps</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">
                            <span>1</span>
                        </div>
                        <h3 class="step-title">Apply Online</h3>
                        <p class="step-description">Fill out our simple application form with your details and upload required documents.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">
                            <span>2</span>
                        </div>
                        <h3 class="step-title">Get Approved</h3>
                        <p class="step-description">Our team will review your application within 2-3 business days and verify your documents.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">
                            <span>3</span>
                        </div>
                        <h3 class="step-title">Start Earning</h3>
                        <p class="step-description">Download the driver app, complete orientation, and start accepting rides immediately.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="statistics-section">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="stat-card">
                        <h2 class="stat-number">10,000+</h2>
                        <p class="stat-label">Active Drivers</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <h2 class="stat-number">₱25,000</h2>
                        <p class="stat-label">Average Monthly Earnings</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <h2 class="stat-number">4.8★</h2>
                        <p class="stat-label">Driver Satisfaction</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="driver-cta-section" id="apply-form">
        <div class="container">
            <div class="text-center">
                <h2 class="cta-title">Ready to Start Earning?</h2>
                <p class="cta-subtitle">Join the Routa driver community today and take control of your income</p>
                <a href="driver-application.php" class="btn btn-warning btn-lg px-5 mt-3 rounded-pill">
                    Apply Now
                    <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h3 class="text-white mb-4">Routa</h3>
                    <p class="text-white-50">Your trusted partner for safe, affordable, and convenient tricycle rides.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 mb-4">
                    <h4 class="text-white mb-4">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php#home">Home</a></li>
                        <li><a href="index.php#about">About Us</a></li>
                        <li><a href="index.php#services">Services</a></li>
                        <li><a href="index.php#download">Download App</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h4 class="text-white mb-4">Support</h4>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Safety</a></li>
                        <li><a href="#">Terms of Agreements</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h4 class="text-white mb-4">Contact Us</h4>
                    <ul class="footer-links">
                        <li><a href="mailto:support@routa.ph">support@routa.ph</a></li>
                        <li><a href="tel:+63123456789">+63 123 456 7890</a></li>
                        <li>Cavite, Philippines</li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="text-center text-white-50 py-3">
                © 2025 Routa. All rights reserved. Made with ❤️ in the Philippines.
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/pages/be-a-driver.js"></script>
</body>
</html>
