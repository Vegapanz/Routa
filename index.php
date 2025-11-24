<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Routa</title>
    
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
    <link rel="shortcut icon" href="assets/images/Logo.png" type="image/x-icon">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="assets/images/Logo.png" alt="Routa Logo" height="30"> Routa
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#download">Download App</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="be-a-driver.php">Be a Driver</a></li>
                    <li class="nav-item"><a class="nav-link btn-book-ride" href="login.php">Book a Ride</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="partner-badge mb-3">
                        <span class="icon">🚲</span> Your Local Ride Partner
                    </div>
                    <h1 class="hero-title">Your Ride, Your Way — <span>Book a Tricycle Instantly!</span></h1>
                    <p class="lead mb-4">Affordable, safe, and convenient tricycle rides anytime, anywhere.</p>
                    <div class="d-flex gap-3 mb-4">
                        <a href="login.php" class="btn btn-success">Book Now</a>
                        <button class="btn btn-outline-success">Download App</button>
                    </div>
                    <div class="d-flex gap-4">
                        <div>
                            <h2 class="h1 text-success mb-0" style="font-size: 2.25rem;">10k+</h2>
                            <p class="text-muted" style="font-size: 0.9rem;">Active Riders</p>
                        </div>
                        <div>
                            <h2 class="h1 text-success mb-0" style="font-size: 2.25rem;">500+</h2>
                            <p class="text-muted" style="font-size: 0.9rem;">Drivers</p>
                        </div>
                        <div>
                            <h2 class="h1 text-success mb-0" style="font-size: 2.25rem;">50+</h2>
                            <p class="text-muted" style="font-size: 0.9rem;">Cities</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="assets/images/Home.png" alt="Tricycle Ride" class="img-fluid rounded-3" style="max-width: 70%; display: block; margin-left: auto;">
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works" id="about">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="h1 mb-3">How It Works</h2>
                <p class="text-muted">Getting around has never been easier. Just three simple steps to your destination.</p>
            </div>
            <div class="row justify-content-center align-items-center">
                <div class="col-md-4 text-center">
                    <div class="step-container">
                        <div class="step-circle">
                            <i class="bi bi-geo-alt-fill"></i>
                            <div class="step-number">1</div>
                        </div>
                        <h3 class="mt-4">Set your pickup & destination</h3>
                        <p class="text-muted">Enter your current location and where you want to go in just a few taps.</p>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="step-container">
                        <div class="step-circle">
                            <i class="bi bi-send-fill"></i>
                            <div class="step-number">2</div>
                        </div>
                        <h3 class="mt-4">Choose your tricycle</h3>
                        <p class="text-muted">Pick from available drivers nearby and see their ratings and estimated arrival time.</p>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="step-container">
                        <div class="step-circle">
                            <i class="bi bi-emoji-smile-fill"></i>
                            <div class="step-number">3</div>
                        </div>
                        <h3 class="mt-4">Enjoy your ride!</h3>
                        <p class="text-muted">Sit back and relax as your driver takes you safely to your destination.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="services">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="h1 mb-3" style="font-weight: 700;">Why Choose Routa?</h2>
                <p style="color: var(--text-color); max-width: 600px; margin: 0 auto;">Experience the best tricycle booking service with features designed for your convenience.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="feature-card">
                        <div class="feature-icon green">
                            <i class="bi bi-broadcast"></i>
                        </div>
                        <h3>Real-time Tracking</h3>
                        <p>Track your driver's location in real-time and know exactly when they'll arrive.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-card">
                        <div class="feature-icon orange">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <h3>Cashless Payments</h3>
                        <p>Pay securely through the app with multiple payment options available.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-card">
                        <div class="feature-icon green">
                            <i class="bi bi-people"></i>
                        </div>
                        <h3>Local Drivers</h3>
                        <p>Supporting local tricycle drivers in your community with fair earnings.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-card">
                        <div class="feature-icon orange">
                            <i class="bi bi-tag"></i>
                        </div>
                        <h3>Affordable Rates</h3>
                        <p>Transparent pricing with no hidden fees. Know your fare before you book.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="h1 mb-3">What Our Riders Say</h2>
                <p class="lead">Join thousands of happy riders who trust Routa for their daily transportation needs.</p>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="star-rating">
                            ★★★★★
                        </div>
                        <p>"Meow! Meow! Meow! Meow! Meow! Meow! Meow! Meow! Meow! Meow! Meow! Meow! Meow! "</p>
                        <div class="d-flex align-items-center mt-3">
                            <img src="assets/images/user1.jpeg" alt="User" class="rounded-circle" width="50">
                            <div class="ms-3">
                                <h4 class="mb-0">Kiko Barzaga</h4>
                                <small>Daily Commuter</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="star-rating">
                            ★★★★★
                        </div>
                        <p>"Affordable rates and reliable service. I use Routa to escape from prison. Highly recommended!"</p>
                        <div class="d-flex align-items-center mt-3">
                            <img src="assets/images/user2.jpg" alt="User" class="rounded-circle" width="50">
                            <div class="ms-3">
                                <h4 class="mb-0">Michael Scofield</h4>
                                <small>Prisoner</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="star-rating">
                            ★★★★★
                        </div>
                        <p>"Safe, convenient, and always on time. The cashless payment feature is a game-changer! erm what the sigma?"</p>
                        <div class="d-flex align-items-center mt-3">
                            <img src="assets/images/user3.png" alt="User" class="rounded-circle" width="50">
                            <div class="ms-3">
                                <h4 class="mb-0">John Patrick Monroyo</h4>
                                <small>Yearner</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Download App Section -->
    <section class="download-app" id="download">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <span class="badge bg-light text-success mb-3">📱 Available on iOS & Android</span>
                    <h2 class="h1 text-white mb-4">Download the Routa App Today</h2>
                    <p class="text-white mb-4">Get the app and start booking tricycle rides in seconds. Available for free on both iOS and Android devices.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="#" class="store-button rounded-4">
                            <i class="fab fa-apple"></i>
                            <div>
                                <small>Download on the</small>
                                <div>App Store</div>
                            </div>
                        </a>
                        <a href="#" class="store-button rounded-4">
                            <i class="fab fa-google-play "></i>
                            <div>
                                <small>GET IT ON</small>
                                <div>Google Play</div>
                            </div>
                        </a>
                    </div>
                    <div class="mt-4">
                        <div class="d-flex gap-4 text-white">
                            <div>
                                <h3 class="h2 mb-0"><span class="white">50K+</span></h3>
                                <small>Downloads</small>
                            </div>
                            <div>
                                <h3 class="h2 mb-0"><span class="white">4.8</span></h3>
                                <small>App rating</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="assets/images/RoutaApp.jpg" alt="Routa App" class="img-fluid rounded-4">
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section" id="contact" style="background: #f8fafc; padding: 80px 0;">
        <div class="container">
            <!-- Contact Header -->
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background: #e0f2fe; border-radius: 50%;">
                    <i class="bi bi-chat-dots" style="font-size: 28px; color: #10b981;"></i>
                </div>
                <h2 class="display-5 fw-bold mb-3" style="color: #1e293b;">Contact Us</h2>
                <p class="lead text-muted" style="max-width: 600px; margin: 0 auto;">Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
            </div>

            <!-- Contact Info Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                        <div class="card-body text-center p-4">
                            <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px; background: #dcfce7; border-radius: 50%;">
                                <i class="bi bi-envelope" style="font-size: 24px; color: #10b981;"></i>
                            </div>
                            <h5 class="fw-bold mb-2">Email</h5>
                            <p class="text-muted mb-0">support@routa.ph</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                        <div class="card-body text-center p-4">
                            <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px; background: #dcfce7; border-radius: 50%;">
                                <i class="bi bi-geo-alt" style="font-size: 24px; color: #10b981;"></i>
                            </div>
                            <h5 class="fw-bold mb-2">Office</h5>
                            <p class="text-muted mb-0">Cavite, Philippines</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                        <div class="card-body text-center p-4">
                            <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px; background: #dcfce7; border-radius: 50%;">
                                <i class="bi bi-clock" style="font-size: 24px; color: #10b981;"></i>
                            </div>
                            <h5 class="fw-bold mb-2">Support Hours</h5>
                            <p class="text-muted mb-0">24/7 Available</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                        <div class="card-body p-4 p-md-5">
                            <h3 class="h4 fw-bold text-center mb-2">Send us a Message</h3>
                            <p class="text-muted text-center mb-4">Fill out the form below and our team will get back to you within 24 hours</p>
                            
                            <!-- Message container -->
                            <div id="contactFormMessage"></div>
                            
                            <form id="contactForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" placeholder="Juan Dela Cruz" required style="padding: 12px; border-radius: 8px;">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" placeholder="juan@email.com" required style="padding: 12px; border-radius: 8px;">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                                        <input type="text" name="subject" class="form-control" placeholder="How can we help?" required style="padding: 12px; border-radius: 8px;">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                                        <textarea name="message" class="form-control" rows="5" placeholder="Tell us more about your inquiry..." required style="padding: 12px; border-radius: 8px;"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-success w-100" style="padding: 14px; font-weight: 600; border-radius: 8px; font-size: 16px;">
                                            <i class="bi bi-send me-2"></i> Send Message
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="row justify-content-center mt-5 pt-5">
                <div class="col-lg-10">
                    <h3 class="h3 fw-bold text-center mb-4">Frequently Asked Questions</h3>
                    
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item border-0 shadow-sm mb-3" style="border-radius: 12px; overflow: hidden;">
                            <h2 class="accordion-header">
                                <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How do I book a tricycle ride?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Simply download the Routa app, create an account, enter your pickup and drop-off locations, and confirm your booking. A nearby driver will be assigned to you immediately.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 shadow-sm mb-3" style="border-radius: 12px; overflow: hidden;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    What payment methods do you accept?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    We accept cash payments, GCash, PayMaya, and major credit/debit cards. You can select your preferred payment method in the app before confirming your ride.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 shadow-sm mb-3" style="border-radius: 12px; overflow: hidden;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    How can I become a Routa driver?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    To become a driver, you need a valid tricycle franchise, driver's license, and other required documents. Contact us at <a href="mailto:driver@routa.ph" class="text-success">driver@routa.ph</a> or call our support line to start the registration process.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Is my personal information safe?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Yes! We take data privacy seriously and use industry-standard encryption to protect your information. Read our <a href="#" class="text-success">Privacy Policy</a> for more details.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#download">Download App</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h4 class="text-white mb-4">Support</h4>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="safety.php">Safety</a></li>
                        <li><a href="terms.php">Terms of Agreements</a></li>
                        <li><a href="privacy-policy.php">Privacy Policy</a></li>
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

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal">
        <div class="modal-dialog modal-dialog-centered auth-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Login to Routa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="loginForm" class="auth-form">
                        <div class="form-group">
                            <input type="email" class="form-control" placeholder="Email" name="email" required>
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" placeholder="Password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Login</button>
                    </form>
                    <p class="text-center mt-3">
                        Don't have an account? 
                        <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Register</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal">
        <div class="modal-dialog modal-dialog-centered auth-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create an Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="registerForm" class="auth-form">
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Full Name" name="name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" class="form-control" placeholder="Email" name="email" required>
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" placeholder="Password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Register</button>
                    </form>
                    <p class="text-center mt-3">
                        Already have an account? 
                        <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/pages/home.js"></script>
</body>
</html>