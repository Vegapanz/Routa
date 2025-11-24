<?php
/**
 * Email Configuration
 * Configure your email settings here
 */

// Email settings
define('EMAIL_FROM_ADDRESS', 'noreply@routa.ph');
define('EMAIL_FROM_NAME', 'Routa');
define('EMAIL_SUPPORT_ADDRESS', 'support@routa.ph');
define('EMAIL_DRIVERS_ADDRESS', 'drivers@routa.ph');
define('EMAIL_CONTACT_PHONE', '+63 123 456 7890');

// Email sending method: 'mail' or 'smtp'
define('EMAIL_METHOD', 'smtp'); // Use 'mail' for PHP mail() function, 'smtp' for PHPMailer

// SMTP Settings (if using PHPMailer - recommended for production)
// Uncomment and configure these if you want to use SMTP

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'
define('SMTP_USERNAME', 'cyclopes543@gmail.com');
define('SMTP_PASSWORD', 'ancweqnezjloprek');
define('SMTP_AUTH', true);


/**
 * Instructions for setting up email:
 * 
 * 1. LOCAL DEVELOPMENT (XAMPP):
 *    - Use 'mail' method (default)
 *    - Configure php.ini to use a mail server
 *    - Or use a tool like MailHog for testing
 * 
 * 2. PRODUCTION WITH GMAIL SMTP:
 *    a. Change EMAIL_METHOD to 'smtp'
 *    b. Install PHPMailer: composer require phpmailer/phpmailer
 *    c. Enable "Less secure app access" or create an "App Password" in Gmail
 *    d. Configure SMTP settings above
 * 
 * 3. PRODUCTION WITH OTHER SMTP PROVIDERS:
 *    - SendGrid, Mailgun, Amazon SES, etc.
 *    - Update SMTP settings accordingly
 * 
 * 4. INFINITYFREE OR SHARED HOSTING:
 *    - Use external SMTP (hosting provider's mail() is often unreliable)
 *    - Recommended: Gmail SMTP or SendGrid
 */

// Enable email sending (set to false to disable all emails)
define('EMAIL_ENABLED', true);

// Email templates directory
define('EMAIL_TEMPLATES_DIR', __DIR__ . '/email_templates/');

// Enable email logging
define('EMAIL_LOG_ENABLED', true);
?>
