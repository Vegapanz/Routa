<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Agreements - Routa</title>
    
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

        .terms-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 100px 0 80px;
            text-align: center;
            position: relative;
        }

        .terms-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(16,185,129,0.03)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            opacity: 0.5;
        }

        .document-icon {
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

        .document-icon i {
            font-size: 36px;
            color: white;
        }

        .terms-title {
            font-size: 3rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }

        .terms-subtitle {
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

        .terms-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .terms-card:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .terms-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .terms-icon i {
            font-size: 24px;
            color: #10b981;
        }

        .terms-card h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
        }

        .terms-card h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1e293b;
            margin-top: 24px;
            margin-bottom: 12px;
        }

        .terms-card p {
            color: #64748b;
            line-height: 1.8;
            margin-bottom: 16px;
        }

        .terms-card ul {
            list-style: none;
            padding: 0;
            margin: 0 0 16px 0;
        }

        .terms-card ul li {
            padding: 12px 0 12px 32px;
            position: relative;
            color: #64748b;
            line-height: 1.7;
        }

        .terms-card ul li::before {
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

        .important-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid #f59e0b;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
        }

        .important-box h4 {
            color: #92400e;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .important-box h4 i {
            color: #f59e0b;
        }

        .important-box p {
            color: #78350f;
            margin: 0;
            line-height: 1.7;
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
            .terms-title {
                font-size: 2rem;
            }

            .terms-card {
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

    <!-- Terms Header -->
    <section class="terms-header">
        <div class="container">
            <div class="document-icon">
                <i class="bi bi-file-text"></i>
            </div>
            <h1 class="terms-title">Terms of Agreements</h1>
            <p class="terms-subtitle">Please read these terms and conditions carefully before using our service.</p>
            <p class="last-updated">Last Updated: November 1, 2025</p>
        </div>
    </section>

    <!-- Content Section -->
    <section class="content-section">
        <div class="container">
            <!-- Introduction -->
            <div class="intro-text">
                <p>Welcome to Routa! These Terms of Agreements ("Terms") govern your use of the Routa tricycle booking platform and services. By accessing or using our services, you agree to be bound by these Terms.</p>
                <p>If you do not agree with any part of these terms, you may not access the service. These Terms apply to all visitors, users, and others who access or use the Service.</p>
            </div>

            <!-- Acceptance of Terms -->
            <div class="terms-card">
                <div class="terms-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <h2>1. Acceptance of Terms</h2>
                <p>By creating an account and using Routa's services, you acknowledge that you have read, understood, and agree to be bound by these Terms, as well as our Privacy Policy. You represent that you are at least 18 years old and have the legal capacity to enter into this agreement.</p>
                <div class="important-box">
                    <h4><i class="bi bi-exclamation-triangle"></i> Important</h4>
                    <p>If you are using our services on behalf of an organization, you represent that you have the authority to bind that organization to these Terms.</p>
                </div>
            </div>

            <!-- User Accounts -->
            <div class="terms-card">
                <div class="terms-icon">
                    <i class="bi bi-person-circle"></i>
                </div>
                <h2>2. User Accounts</h2>
                <h3>2.1 Account Creation</h3>
                <p>To use our services, you must create an account by providing accurate, current, and complete information. You are responsible for maintaining the confidentiality of your account credentials.</p>
                
                <h3>2.2 Account Security</h3>
                <p>You are responsible for all activities that occur under your account. You must:</p>
                <ul>
                    <li>Keep your password secure and confidential</li>
                    <li>Notify us immediately of any unauthorized use of your account</li>
                    <li>Not share your account with others</li>
                    <li>Ensure your account information is accurate and up to date</li>
                </ul>

                <h3>2.3 Account Termination</h3>
                <p>We reserve the right to suspend or terminate your account if you violate these Terms or engage in fraudulent, illegal, or harmful activities.</p>
            </div>

            <!-- Service Usage -->
            <div class="terms-card">
                <div class="terms-icon">
                    <i class="bi bi-gear"></i>
                </div>
                <h2>3. Use of Services</h2>
                <h3>3.1 Service Description</h3>
                <p>Routa provides a platform that connects riders with tricycle drivers. We facilitate bookings but do not provide transportation services directly. Drivers are independent contractors, not employees of Routa.</p>
                
                <h3>3.2 Booking and Payment</h3>
                <p>When you book a ride through Routa:</p>
                <ul>
                    <li>You agree to pay the fare displayed in the app</li>
                    <li>Payment will be processed through your chosen payment method</li>
                    <li>Fares are calculated based on distance, time, and demand</li>
                    <li>You may be charged cancellation fees if applicable</li>
                </ul>

                <h3>3.3 Acceptable Use</h3>
                <p>You agree not to:</p>
                <ul>
                    <li>Use the service for any illegal or unauthorized purpose</li>
                    <li>Harass, abuse, or harm drivers or other users</li>
                    <li>Attempt to manipulate fares or ratings</li>
                    <li>Reverse engineer or tamper with our platform</li>
                    <li>Share inappropriate content or engage in fraudulent activities</li>
                </ul>
            </div>

            <!-- Driver Terms -->
            <div class="terms-card">
                <div class="terms-icon">
                    <i class="bi bi-car-front"></i>
                </div>
                <h2>4. Driver Terms</h2>
                <h3>4.1 Driver Requirements</h3>
                <p>Drivers must meet all local licensing, insurance, and regulatory requirements. All drivers undergo background checks and vehicle inspections before being approved.</p>
                
                <h3>4.2 Driver Responsibilities</h3>
                <p>Drivers agree to:</p>
                <ul>
                    <li>Maintain a valid driver's license and vehicle registration</li>
                    <li>Provide safe and courteous service to all riders</li>
                    <li>Maintain their vehicle in good working condition</li>
                    <li>Follow all traffic laws and regulations</li>
                    <li>Accept rides in a timely and professional manner</li>
                </ul>

                <h3>4.3 Driver Ratings</h3>
                <p>Drivers are subject to rider ratings. Consistently low ratings may result in account suspension or termination.</p>
            </div>

            <!-- Payments and Fees -->
            <div class="terms-card">
                <div class="terms-icon">
                    <i class="bi bi-credit-card"></i>
                </div>
                <h2>5. Payments and Fees</h2>
                <h3>5.1 Fare Calculation</h3>
                <p>Fares are calculated based on factors including distance, time, route, and current demand. The fare estimate shown before booking is an approximation and may vary based on actual trip conditions.</p>
                
                <h3>5.2 Payment Methods</h3>
                <p>We accept various payment methods including credit/debit cards, e-wallets, and cash. All electronic payments are processed securely through certified payment processors.</p>

                <h3>5.3 Cancellation Fees</h3>
                <p>Cancellation fees may apply if you cancel after a driver has been assigned and is en route to your location. Fees help compensate drivers for their time and effort.</p>

                <h3>5.4 Refunds</h3>
                <p>Refund requests must be submitted within 48 hours of the ride. We review all requests on a case-by-case basis and reserve the right to approve or deny refunds.</p>
            </div>

            <!-- Liability and Disclaimers -->
            <div class="terms-card">
                <div class="terms-icon">
                    <i class="bi bi-shield-exclamation"></i>
                </div>
                <h2>6. Liability and Disclaimers</h2>
                <h3>6.1 Service Availability</h3>
                <p>We strive to provide reliable service but cannot guarantee uninterrupted access. Services may be temporarily unavailable due to maintenance, updates, or circumstances beyond our control.</p>
                
                <h3>6.2 Limitation of Liability</h3>
                <p>Routa acts as an intermediary between riders and drivers. We are not responsible for:</p>
                <ul>
                    <li>Actions or omissions of drivers or riders</li>
                    <li>Lost or damaged personal property during rides</li>
                    <li>Delays, accidents, or incidents during transportation</li>
                    <li>Service interruptions or technical issues</li>
                </ul>

                <h3>6.3 Insurance</h3>
                <p>All drivers are required to maintain appropriate insurance coverage. However, riders should verify coverage details with their driver when necessary.</p>

                <div class="important-box">
                    <h4><i class="bi bi-exclamation-triangle"></i> Disclaimer</h4>
                    <p>The service is provided "as is" without warranties of any kind. We do not guarantee the quality, safety, or legality of services provided by drivers.</p>
                </div>
            </div>

            <!-- Intellectual Property -->
            <div class="terms-card">
                <div class="terms-icon">
                    <i class="bi bi-c-circle"></i>
                </div>
                <h2>7. Intellectual Property</h2>
                <p>All content, features, and functionality of the Routa platform, including but not limited to text, graphics, logos, icons, images, audio clips, and software, are the exclusive property of Routa and are protected by copyright, trademark, and other intellectual property laws.</p>
                <p>You may not reproduce, distribute, modify, create derivative works of, publicly display, or exploit any content from our platform without our prior written permission.</p>
            </div>

            <!-- Privacy and Data -->
            <div class="terms-card">
                <div class="terms-icon">
                    <i class="bi bi-lock"></i>
                </div>
                <h2>8. Privacy and Data Protection</h2>
                <p>Your privacy is important to us. Our collection, use, and disclosure of personal information is governed by our Privacy Policy. By using our services, you consent to our data practices as described in the Privacy Policy.</p>
                <p>We implement appropriate security measures to protect your personal information, but cannot guarantee absolute security of data transmitted over the internet.</p>
            </div>

            <!-- Dispute Resolution -->
            <div class="terms-card">
                <div class="terms-icon">
                    <i class="bi bi-balance-scale"></i>
                </div>
                <h2>9. Dispute Resolution</h2>
                <h3>9.1 Governing Law</h3>
                <p>These Terms shall be governed by and construed in accordance with the laws of the Philippines, without regard to its conflict of law provisions.</p>
                
                <h3>9.2 Arbitration</h3>
                <p>Any disputes arising from these Terms or use of our services shall first be attempted to be resolved through good faith negotiations. If unsuccessful, disputes may be subject to binding arbitration in accordance with Philippine law.</p>

                <h3>9.3 Class Action Waiver</h3>
                <p>You agree to resolve disputes with Routa on an individual basis and waive any right to participate in class action lawsuits or class-wide arbitration.</p>
            </div>

            <!-- Changes to Terms -->
            <div class="terms-card">
                <div class="terms-icon">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <h2>10. Changes to Terms</h2>
                <p>We reserve the right to modify or replace these Terms at any time. We will provide notice of significant changes by posting the new Terms on our platform and updating the "Last Updated" date.</p>
                <p>Your continued use of the service after changes become effective constitutes acceptance of the revised Terms. We encourage you to review these Terms periodically.</p>
            </div>

            <!-- Termination -->
            <div class="terms-card">
                <div class="terms-icon">
                    <i class="bi bi-x-circle"></i>
                </div>
                <h2>11. Termination</h2>
                <p>Either party may terminate this agreement at any time. You may delete your account through the app settings. We may terminate or suspend your access immediately, without prior notice, for any reason including breach of these Terms.</p>
                <p>Upon termination, your right to use the service will cease immediately. Provisions that by their nature should survive termination shall survive, including ownership provisions, warranty disclaimers, and limitations of liability.</p>
            </div>

            <!-- Severability -->
            <div class="intro-text">
                <h2 class="section-title">12. Severability</h2>
                <p class="section-description">If any provision of these Terms is held to be invalid or unenforceable, such provision shall be struck and the remaining provisions shall remain in full force and effect.</p>
            </div>

            <!-- Entire Agreement -->
            <div class="intro-text">
                <h2 class="section-title">13. Entire Agreement</h2>
                <p class="section-description">These Terms, together with our Privacy Policy and any other legal notices published by us on the platform, constitute the entire agreement between you and Routa concerning the use of our services.</p>
            </div>

            <!-- Contact Us -->
            <div class="contact-box">
                <h3>Questions About These Terms?</h3>
                <p>If you have any questions about these Terms of Agreements, please contact us:</p>
                <div class="contact-info">
                    <div class="contact-info-item">
                        <i class="bi bi-envelope me-2"></i>Email: legal@routa.ph
                    </div>
                    <div class="contact-info-item">
                        <i class="bi bi-telephone me-2"></i>Phone: +63 123 456 7890
                    </div>
                    <div class="contact-info-item">
                        <i class="bi bi-geo-alt me-2"></i>Address: Manila, Philippines
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
