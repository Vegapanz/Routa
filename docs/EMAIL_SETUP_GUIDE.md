# Email Notification Setup Guide

This guide explains how to configure email notifications for driver applications in Routa.

## 📧 Feature Overview

When a driver submits their application through the "Be a Driver" page, they will automatically receive a professional thank you email containing:

- Confirmation of application submission
- Application ID for tracking
- Expected processing timeline (2-3 business days)
- What happens next in the application process
- Contact information for questions
- Benefits of driving with Routa

## 🚀 Quick Setup (Local Development)

### Step 1: Configure PHP Mail (XAMPP)

1. Open `php.ini` file (in XAMPP: `C:\xampp\php\php.ini`)

2. Find and update these settings:
```ini
[mail function]
SMTP=smtp.gmail.com
smtp_port=587
sendmail_from=your-email@gmail.com
sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t"
```

3. Open `sendmail.ini` file (in XAMPP: `C:\xampp\sendmail\sendmail.ini`)

4. Update these settings:
```ini
[sendmail]
smtp_server=smtp.gmail.com
smtp_port=587
error_logfile=error.log
debug_logfile=debug.log
auth_username=your-email@gmail.com
auth_password=your-app-password
force_sender=your-email@gmail.com
```

5. Restart Apache in XAMPP

### Step 2: Enable the Feature

Email is enabled by default. To disable:

Edit `php/email_config.php`:
```php
define('EMAIL_ENABLED', false);
```

## 🔧 Configuration Files

### `php/email_config.php`

Main configuration file for email settings:

```php
// Basic settings
define('EMAIL_FROM_ADDRESS', 'noreply@routa.ph');
define('EMAIL_FROM_NAME', 'Routa');
define('EMAIL_SUPPORT_ADDRESS', 'support@routa.ph');
define('EMAIL_DRIVERS_ADDRESS', 'drivers@routa.ph');
define('EMAIL_CONTACT_PHONE', '+63 123 456 7890');

// Enable/disable email
define('EMAIL_ENABLED', true);

// Email logging
define('EMAIL_LOG_ENABLED', true);
```

### `php/email_helper.php`

Contains email sending functions:
- `sendEmail()` - Generic email sender
- `sendDriverApplicationThankYou()` - Driver application thank you email
- `sendDriverApplicationStatusUpdate()` - Status update emails

### `php/submit_driver_application.php`

Handles form submission and triggers the email.

## 📨 Setting Up Gmail SMTP (Recommended for Production)

### Option 1: Using App Password (Recommended)

1. **Enable 2-Factor Authentication** on your Gmail account
   - Go to Google Account → Security → 2-Step Verification

2. **Generate App Password**
   - Go to Google Account → Security → App passwords
   - Select "Mail" and "Windows Computer"
   - Copy the 16-character password

3. **Update Configuration**
   
   Edit `sendmail.ini` (XAMPP) or `php.ini`:
   ```ini
   auth_username=your-email@gmail.com
   auth_password=your-16-char-app-password
   ```

### Option 2: Using PHPMailer with SMTP (Advanced)

1. **Install PHPMailer**
   ```bash
   composer require phpmailer/phpmailer
   ```

2. **Update email_helper.php** to use PHPMailer instead of mail()

3. **Configure SMTP** in `email_config.php`:
   ```php
   define('EMAIL_METHOD', 'smtp');
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_PORT', 587);
   define('SMTP_USERNAME', 'your-email@gmail.com');
   define('SMTP_PASSWORD', 'your-app-password');
   ```

## 🧪 Testing Email Functionality

### Test 1: Submit a Driver Application

1. Go to `http://localhost/Routa/be-a-driver.php`
2. Click "Apply to Drive"
3. Fill out the complete application form
4. Submit the form
5. Check the email address you provided

### Test 2: Check Email Logs

Check PHP error logs for email status:
- **XAMPP**: `C:\xampp\php\logs\php_error_log`
- **Look for**: `Email sent to [email]: Success` or `Failed`

### Test 3: Using MailHog (Development Tool)

MailHog catches emails locally for testing:

1. **Download MailHog**: https://github.com/mailhog/MailHog
2. **Run MailHog**: `MailHog.exe`
3. **Configure PHP** to use MailHog:
   ```ini
   SMTP=localhost
   smtp_port=1025
   ```
4. **View emails**: http://localhost:8025

## 📧 Email Template Customization

To customize the thank you email, edit the `sendDriverApplicationThankYou()` function in `php/email_helper.php`.

### Available Variables:
- `$email` - Recipient email
- `$firstName` - Driver's first name
- `$lastName` - Driver's last name
- `$fullName` - Full name
- `$applicationId` - Application ID

### Styling:
The email uses inline CSS for maximum compatibility across email clients.

## 🚨 Troubleshooting

### Problem: Emails not sending

**Check:**
1. Is `EMAIL_ENABLED` set to `true` in `email_config.php`?
2. Is XAMPP/Apache running?
3. Check PHP error logs
4. Verify SMTP settings in `sendmail.ini`
5. Test with a simple PHP mail script

**Simple Test Script** (`test_email.php`):
```php
<?php
$to = "your-test-email@gmail.com";
$subject = "Test Email";
$message = "This is a test email from Routa";
$headers = "From: noreply@routa.ph";

if(mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully!";
} else {
    echo "Failed to send email.";
}
?>
```

### Problem: Gmail blocking emails

**Solutions:**
1. Use App Password instead of regular password
2. Enable "Less secure app access" (not recommended)
3. Add your server IP to Gmail's allowed list
4. Use a different SMTP provider (SendGrid, Mailgun, Amazon SES)

### Problem: Emails going to spam

**Fix:**
1. Set up SPF records for your domain
2. Set up DKIM authentication
3. Use a reputable SMTP service
4. Don't use free email addresses as sender
5. Include unsubscribe link (for bulk emails)

## 🌐 Production Deployment

### For Shared Hosting (InfinityFree, etc.)

1. **Don't rely on PHP mail()** - Use external SMTP
2. **Recommended Services:**
   - **SendGrid** (Free tier: 100 emails/day)
   - **Mailgun** (Free tier: 5,000 emails/month)
   - **Amazon SES** (Very cheap, pay as you go)
   - **Gmail SMTP** (Limited, but works)

3. **Update Configuration:**
   ```php
   define('EMAIL_METHOD', 'smtp');
   // Configure SMTP settings
   ```

### For VPS/Dedicated Server

1. **Install and configure Postfix** or use SMTP
2. **Set up DNS records** (SPF, DKIM, DMARC)
3. **Monitor email delivery** with logs

## 📊 Email Logging

Email attempts are logged in PHP error logs when `EMAIL_LOG_ENABLED` is true.

**Log Format:**
```
[2025-11-18 10:30:45] Email to: driver@example.com | Subject: Thank You for Your Driver Application - Routa | Status: SUCCESS
```

**Location:**
- XAMPP: `C:\xampp\php\logs\php_error_log`
- Linux: `/var/log/apache2/error.log`

## 🔐 Security Best Practices

1. **Never commit credentials** to version control
2. **Use environment variables** for sensitive data
3. **Use App Passwords** instead of real passwords
4. **Validate email addresses** before sending
5. **Rate limit** email sending to prevent abuse
6. **Log email activity** for security auditing

## 📝 Future Enhancements

Potential improvements for the email system:

1. **Email Queue System** - Queue emails for background processing
2. **Email Templates** - Store templates in database or separate files
3. **Multiple Languages** - Send emails in user's preferred language
4. **Email Preferences** - Let users opt-in/out of notifications
5. **Analytics** - Track email open rates and clicks
6. **Attachments** - Send PDFs or other documents

## 💡 Additional Email Types

You can easily add more email notifications:

### Application Approved Email
```php
sendDriverApplicationStatusUpdate(
    $email, 
    $firstName, 
    'approved', 
    'Congratulations! Your application has been approved...'
);
```

### Application Rejected Email
```php
sendDriverApplicationStatusUpdate(
    $email, 
    $firstName, 
    'rejected', 
    'We regret to inform you...'
);
```

## 📞 Support

If you encounter issues with email setup:

- Check PHP documentation: https://www.php.net/manual/en/function.mail.php
- Gmail SMTP guide: https://support.google.com/mail/answer/7126229
- PHPMailer docs: https://github.com/PHPMailer/PHPMailer

---

**Last Updated:** November 18, 2025  
**Version:** 1.0
