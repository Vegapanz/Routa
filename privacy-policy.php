<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Routa</title>
    
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
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
            color: #4a5568;
        }

        .privacy-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 100px 0 80px;
            text-align: center;
            position: relative;
        }

        .privacy-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(16,185,129,0.03)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            opacity: 0.5;
        }

        .shield-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.2);
            position: relative;
            z-index: 1;
        }

        .shield-icon i {
            font-size: 36px;
            color: white;
        }

        .privacy-title {
            font-size: 3rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }

        .privacy-subtitle {
            font-size: 1.1rem;
            color: #64748b;
            max-width: 600px;
            margin: 0 auto 12px;
            position: relative;
            z-index: 1;
        }

        .last-updated {
            color: #94a3b8;
            font-size: 0.95rem;
            position: relative;
            z-index: 1;
        }

        .content-section {
            padding: 60px 0;
        }

        .intro-text {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
        }

        .intro-text p {
            font-size: 1.05rem;
            line-height: 1.8;
            color: #64748b;
            margin-bottom: 20px;
        }

        .intro-text p:last-child {
            margin-bottom: 0;
        }

        .policy-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .policy-card:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .policy-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .policy-icon i {
            font-size: 24px;
            color: #10b981;
        }

        .policy-card h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .policy-card ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .policy-card ul li {
            padding: 12px 0 12px 32px;
            position: relative;
            color: #64748b;
            line-height: 1.7;
        }

        .policy-card ul li::before {
            content: '●';
            position: absolute;
            left: 0;
            color: #10b981;
            font-size: 1.2rem;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 16px;
        }

        .section-description {
            color: #64748b;
            font-size: 1.05rem;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .contact-box {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            margin-top: 40px;
        }

        .contact-box h3 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 16px;
        }

        .contact-box p {
            color: #64748b;
            margin-bottom: 24px;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: center;
        }

        .contact-info-item {
            color: #10b981;
            font-weight: 600;
            font-size: 1.05rem;
        }

        @media (max-width: 768px) {
            .privacy-title {
                font-size: 2rem;
            }

            .policy-card {
                padding: 30px 20px;
            }
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
                    <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link btn-book-ride" href="login.php">Book a Ride</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Privacy Policy Header -->
    <section class="privacy-header">
        <div class="container">
            <div class="shield-icon">
                <i class="bi bi-shield-check"></i>
            </div>
            <h1 class="privacy-title">Privacy Policy</h1>
            <p class="privacy-subtitle">Your privacy is important to us. Learn how we collect, use, and protect your data.</p>
            <p class="last-updated">Last Updated: November 1, 2025</p>
        </div>
    </section>

    <!-- Content Section -->
    <section class="content-section">
        <div class="container">
            <!-- Introduction -->
            <div class="intro-text">
                <p>At Routa, we are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your data when you use our tricycle booking platform and related services.</p>
                <p>By using Routa's services, you agree to the collection and use of information in accordance with this policy. If you do not agree with our policies and practices, please do not use our services.</p>
            </div>

            <!-- Information We Collect -->
            <div class="policy-card">
                <div class="policy-icon">
                    <i class="bi bi-file-text"></i>
                </div>
                <h2>Information We Collect</h2>
                <ul>
                    <li>Personal information (name, email, phone number, address)</li>
                    <li>Location data for ride tracking and service optimization</li>
                    <li>Payment information for transaction processing</li>
                    <li>Device information and usage data</li>
                    <li>Trip history and preferences</li>
                </ul>
            </div>

            <!-- How We Use Your Information -->
            <div class="policy-card">
                <div class="policy-icon">
                    <i class="bi bi-lock"></i>
                </div>
                <h2>How We Use Your Information</h2>
                <ul>
                    <li>To provide and improve our tricycle booking services</li>
                    <li>To process payments and prevent fraud</li>
                    <li>To communicate with you about your rides and account</li>
                    <li>To ensure safety and security for all users</li>
                    <li>To comply with legal obligations and enforce our terms</li>
                </ul>
            </div>

            <!-- Data Security -->
            <div class="policy-card">
                <div class="policy-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h2>Data Security</h2>
                <ul>
                    <li>We implement industry-standard security measures</li>
                    <li>All sensitive data is encrypted in transit and at rest</li>
                    <li>Regular security audits and monitoring</li>
                    <li>Access controls and authentication protocols</li>
                    <li>Secure payment processing through certified partners</li>
                </ul>
            </div>

            <!-- Information Sharing -->
            <div class="policy-card">
                <div class="policy-icon">
                    <i class="bi bi-eye"></i>
                </div>
                <h2>Information Sharing</h2>
                <ul>
                    <li>We share location data with drivers to facilitate your ride</li>
                    <li>Payment information is shared with payment processors</li>
                    <li>We may share data with law enforcement when required by law</li>
                    <li>Anonymous analytics may be shared with third-party services</li>
                    <li>We never sell your personal information to third parties</li>
                </ul>
            </div>

            <!-- Your Rights -->
            <div class="policy-card">
                <div class="policy-icon">
                    <i class="bi bi-person-check"></i>
                </div>
                <h2>Your Rights</h2>
                <ul>
                    <li>Access, update, or delete your personal information</li>
                    <li>Opt-out of marketing communications</li>
                    <li>Request a copy of your data</li>
                    <li>Object to certain data processing activities</li>
                    <li>Lodge a complaint with data protection authorities</li>
                </ul>
            </div>

            <!-- Data Retention -->
            <div class="policy-card">
                <div class="policy-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h2>Data Retention</h2>
                <ul>
                    <li>Active account data is retained while your account is active</li>
                    <li>Trip history is kept for 7 years for legal compliance</li>
                    <li>Payment records are retained as required by financial regulations</li>
                    <li>You can request deletion of certain data at any time</li>
                    <li>Deleted data is removed from active systems within 30 days</li>
                </ul>
            </div>

            <!-- Cookies and Tracking -->
            <div class="intro-text">
                <h2 class="section-title">Cookies and Tracking</h2>
                <p class="section-description">We use cookies and similar tracking technologies to track activity on our service and store certain information. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent. However, if you do not accept cookies, you may not be able to use some portions of our service.</p>
            </div>

            <!-- Children's Privacy -->
            <div class="intro-text">
                <h2 class="section-title">Children's Privacy</h2>
                <p class="section-description">Our services are not intended for individuals under the age of 18. We do not knowingly collect personal information from children. If you are a parent or guardian and believe your child has provided us with personal information, please contact us so we can delete such information.</p>
            </div>

            <!-- Changes to This Policy -->
            <div class="intro-text">
                <h2 class="section-title">Changes to This Policy</h2>
                <p class="section-description">We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last Updated" date. You are advised to review this Privacy Policy periodically for any changes.</p>
            </div>

            <!-- Contact Us -->
            <div class="contact-box">
                <h3>Contact Us</h3>
                <p>If you have any questions about this Privacy Policy or our data practices, please contact us:</p>
                <div class="contact-info">
                    <div class="contact-info-item">
                        <i class="bi bi-envelope me-2"></i>Email: privacy@routa.ph
                    </div>
                    <div class="contact-info-item">
                        <i class="bi bi-telephone me-2"></i>Phone: +63 123 456 7890
                    </div>
                    <div class="contact-info-item">
                        <i class="bi bi-geo-alt me-2"></i>Address: Cavite, Philippines
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
