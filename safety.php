<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safety - Routa</title>
    
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

        /* Hero Section */
        .safety-hero {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 120px 0 80px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .safety-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            opacity: 0.5;
        }

        .safety-hero .container {
            position: relative;
            z-index: 1;
        }

        .safety-hero h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .safety-hero h1 i {
            font-size: 2.5rem;
        }

        .safety-hero p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.95;
        }

        .contact-safety-btn {
            background: #fbbf24;
            color: #1e293b;
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
        }

        .contact-safety-btn:hover {
            background: #f59e0b;
            color: #1e293b;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(251, 191, 36, 0.4);
        }

        .hero-image {
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
        }

        /* Features Section */
        .features-section {
            padding: 80px 0;
            background: white;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 16px;
        }

        .section-header p {
            font-size: 1.1rem;
            color: #64748b;
            max-width: 700px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid #e2e8f0;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            font-size: 28px;
            color: white;
        }

        .feature-icon.blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .feature-icon.green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .feature-icon.purple {
            background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%);
        }

        .feature-icon.red {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .feature-icon.yellow {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .feature-icon.indigo {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        }

        .feature-card h3 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .feature-card p {
            color: #64748b;
            line-height: 1.7;
            margin: 0;
        }

        /* Safety Tips Section */
        .safety-tips-section {
            padding: 80px 0;
            background: #f8fafc;
        }

        .tips-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            height: 100%;
        }

        .tips-card h3 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 24px;
        }

        .tip-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .tip-item:last-child {
            margin-bottom: 0;
        }

        .tip-icon {
            width: 32px;
            height: 32px;
            background: #d1fae5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-right: 16px;
            margin-top: 2px;
        }

        .tip-icon i {
            color: #10b981;
            font-size: 16px;
        }

        .tip-content {
            flex: 1;
        }

        .tip-content p {
            margin: 0;
            color: #64748b;
            line-height: 1.7;
        }

        /* Driver Screening Section */
        .screening-section {
            padding: 80px 0;
            background: white;
        }

        .screening-step {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #10b981;
            transition: all 0.3s ease;
            position: relative;
        }

        .screening-step:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .step-number {
            position: absolute;
            top: 30px;
            right: 30px;
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .step-content {
            padding-right: 70px;
        }

        .step-content h4 {
            margin: 0 0 8px 0;
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
        }

        .step-content p {
            margin: 0;
            color: #64748b;
            line-height: 1.7;
        }

        /* Emergency Section */
        .emergency-section {
            padding: 80px 0;
            background: #f8fafc;
        }

        .emergency-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.3);
        }

        .emergency-icon i {
            font-size: 36px;
            color: white;
        }

        .emergency-card {
            background: white;
            border: 2px solid #ef4444;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
        }

        .emergency-card:hover {
            box-shadow: 0 12px 24px rgba(239, 68, 68, 0.2);
            transform: translateY(-5px);
        }

        .emergency-card i {
            font-size: 48px;
            color: #ef4444;
            margin-bottom: 16px;
        }

        .emergency-card h4 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .emergency-card .phone-number {
            font-size: 2rem;
            font-weight: 700;
            color: #10b981;
            margin-bottom: 8px;
        }

        .emergency-card p {
            color: #64748b;
            margin: 0;
        }

        .report-btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 16px 48px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-top: 40px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .report-btn:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
        }

        /* Community Guidelines */
        .guidelines-section {
            padding: 80px 0;
            background: white;
        }

        .guideline-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .guideline-icon i {
            font-size: 40px;
            color: white;
        }

        .guideline-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .guideline-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 32px;
            padding-bottom: 32px;
            border-bottom: 1px solid #e2e8f0;
        }

        .guideline-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .guideline-item-icon {
            width: 32px;
            height: 32px;
            background: #d1fae5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-right: 20px;
            margin-top: 2px;
        }

        .guideline-item-icon i {
            color: #10b981;
            font-size: 16px;
        }

        .guideline-item-content h5 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .guideline-item-content p {
            margin: 0;
            color: #64748b;
            line-height: 1.7;
        }

        @media (max-width: 768px) {
            .safety-hero h1 {
                font-size: 2rem;
            }

            .safety-hero p {
                font-size: 1rem;
            }

            .hero-image {
                margin-top: 30px;
            }

            .section-header h2 {
                font-size: 2rem;
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

    <!-- Hero Section -->
    <section class="safety-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1><i class="bi bi-shield-check"></i> Your Safety Matters</h1>
                    <p>We're committed to making every ride safe and secure. Learn about our safety features and best practices.</p>
                    <a href="#contact-safety" class="contact-safety-btn">Contact Safety Team</a>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=800&q=80" alt="Safety Team" class="hero-image">
                </div>
            </div>
        </div>
    </section>

    <!-- Safety Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="section-header">
                <h2>Our Safety Features</h2>
                <p>Multiple layers of protection to keep you safe on every ride</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon blue">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3>Verified Drivers</h3>
                        <p>All drivers undergo thorough background checks and verification before joining Routa.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon green">
                            <i class="bi bi-eye"></i>
                        </div>
                        <h3>Real-Time Tracking</h3>
                        <p>Share your ride with friends and family. They can track your journey in real-time.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon purple">
                            <i class="bi bi-telephone"></i>
                        </div>
                        <h3>24/7 Support</h3>
                        <p>Our safety team is available round-the-clock to assist you with any concerns.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon red">
                            <i class="bi bi-exclamation-circle"></i>
                        </div>
                        <h3>Emergency Button</h3>
                        <p>Quick access to emergency services and immediate alert to our safety team.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon yellow">
                            <i class="bi bi-star"></i>
                        </div>
                        <h3>Driver Ratings</h3>
                        <p>Rate your driver after each trip. Low-rated drivers are removed from the platform.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon indigo">
                            <i class="bi bi-lock"></i>
                        </div>
                        <h3>Secure Payments</h3>
                        <p>Your payment information is encrypted and secure. No need to carry cash.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Safety Tips Section -->
    <section class="safety-tips-section">
        <div class="container">
            <div class="section-header">
                <h2>Safety Tips</h2>
                <p>Follow these guidelines to ensure a safe ride experience</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="tips-card">
                        <h3>Before Your Ride</h3>
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="tip-content">
                                <p>Verify the driver's name, photo, and license plate before getting in</p>
                            </div>
                        </div>
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="tip-content">
                                <p>Check the driver's rating and reviews</p>
                            </div>
                        </div>
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="tip-content">
                                <p>Share your trip details with a friend or family member</p>
                            </div>
                        </div>
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="tip-content">
                                <p>Wait in a safe, well-lit area for your driver</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="tips-card">
                        <h3>During Your Ride</h3>
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="tip-content">
                                <p>Always wear your seatbelt</p>
                            </div>
                        </div>
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="tip-content">
                                <p>Follow the GPS route and speak up if the driver goes off course</p>
                            </div>
                        </div>
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="tip-content">
                                <p>Sit in the back seat when riding alone</p>
                            </div>
                        </div>
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="tip-content">
                                <p>Trust your instincts - if something feels wrong, it probably is</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="tips-card">
                        <h3>After Your Ride</h3>
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="tip-content">
                                <p>Make sure you have all your belongings before exiting</p>
                            </div>
                        </div>
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="tip-content">
                                <p>Rate your driver honestly to help maintain quality</p>
                            </div>
                        </div>
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="tip-content">
                                <p>Report any issues or concerns immediately</p>
                            </div>
                        </div>
                        <div class="tip-item">
                            <div class="tip-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="tip-content">
                                <p>Check your trip receipt for accuracy</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Driver Screening Section -->
    <section class="screening-section">
        <div class="container">
            <div class="section-header">
                <h2>Driver Screening Process</h2>
                <p>We ensure every driver meets our strict safety standards</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="screening-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Document Verification</h4>
                            <p>Driver's license, vehicle registration, insurance, and franchise permits are thoroughly verified.</p>
                        </div>
                    </div>
                    <div class="screening-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Background Check</h4>
                            <p>Comprehensive background screening including criminal records and driving history.</p>
                        </div>
                    </div>
                    <div class="screening-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Vehicle Inspection</h4>
                            <p>Physical inspection to ensure the tricycle meets safety and quality standards.</p>
                        </div>
                    </div>
                    <div class="screening-step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h4>Safety Training</h4>
                            <p>Mandatory safety orientation and training on customer service and emergency procedures.</p>
                        </div>
                    </div>
                    <div class="screening-step">
                        <div class="step-number">5</div>
                        <div class="step-content">
                            <h4>Continuous Monitoring</h4>
                            <p>Ongoing performance reviews, random checks, and rating system to maintain standards.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Emergency Contacts Section -->
    <section class="emergency-section" id="contact-safety">
        <div class="container">
            <div class="text-center mb-5">
                <div class="emergency-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h2 class="section-header h1 fw-bold">Emergency Contacts</h2>
                <p class="text-muted" style="font-size: 1.1rem;">In case of emergency, contact these numbers immediately</p>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="emergency-card">
                        <i class="bi bi-telephone-fill"></i>
                        <h4>Routa Safety Hotline</h4>
                        <div class="phone-number">+63 123 456 7890</div>
                        <p>Available 24/7</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-5">
                    <div class="emergency-card">
                        <i class="bi bi-shield-exclamation"></i>
                        <h4>Emergency Services</h4>
                        <div class="phone-number">911</div>
                        <p>Police, Fire, Medical</p>
                    </div>
                </div>
            </div>
            <div class="text-center">
                <a href="index.php#contact" class="report-btn">Report Safety Incident</a>
            </div>
        </div>
    </section>

    <!-- Community Guidelines Section -->
    <section class="guidelines-section">
        <div class="container">
            <div class="text-center mb-5">
                <div class="guideline-icon">
                    <i class="bi bi-people"></i>
                </div>
                <h2>Community Guidelines</h2>
                <p class="text-muted" style="font-size: 1.1rem;">We expect all riders and drivers to treat each other with respect</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="guideline-card">
                        <div class="guideline-item">
                            <div class="guideline-item-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="guideline-item-content">
                                <h5>Be Respectful</h5>
                                <p>Treat everyone with courtesy and respect, regardless of differences.</p>
                            </div>
                        </div>
                        <div class="guideline-item">
                            <div class="guideline-item-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="guideline-item-content">
                                <h5>No Discrimination</h5>
                                <p>Discrimination of any kind will not be tolerated and will result in account termination.</p>
                            </div>
                        </div>
                        <div class="guideline-item">
                            <div class="guideline-item-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="guideline-item-content">
                                <h5>Follow the Law</h5>
                                <p>All traffic rules and local laws must be followed at all times.</p>
                            </div>
                        </div>
                        <div class="guideline-item">
                            <div class="guideline-item-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="guideline-item-content">
                                <h5>Report Issues</h5>
                                <p>Report any safety concerns or violations immediately through the app.</p>
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
