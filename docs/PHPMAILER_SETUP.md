# PHPMailer Setup Guide for Routa

## 📧 Why Use PHPMailer?

PHPMailer is more reliable than PHP's `mail()` function because:
- ✅ Works with SMTP servers (Gmail, SendGrid, etc.)
- ✅ Better error handling and debugging
- ✅ More compatible across hosting providers
- ✅ Supports attachments, HTML emails, and more
- ✅ Production-ready and widely used

## 🚀 Quick Setup (3 Steps)

### Step 1: Install PHPMailer with Composer

Open **PowerShell** in your Routa directory and run:

```powershell
cd d:\xampp\htdocs\Routa
composer require phpmailer/phpmailer
```

**Don't have Composer?** Install it first:

**Option A: Download Composer Installer**
1. Go to https://getcomposer.org/download/
2. Download and run `Composer-Setup.exe`
3. Follow the installer prompts
4. Restart PowerShell
5. Run the command above

**Option B: Manual Install (Without Composer)**
1. Download PHPMailer: https://github.com/PHPMailer/PHPMailer/releases
2. Extract to `d:\xampp\htdocs\Routa\vendor\phpmailer\phpmailer\`
3. No composer needed!

### Step 2: Configure Your Gmail Account

**2.1 Enable 2-Factor Authentication**
1. Go to https://myaccount.google.com/security
2. Click "2-Step Verification"
3. Follow the setup process

**2.2 Generate App Password**
1. Go to https://myaccount.google.com/apppasswords
2. Select "Mail" and "Windows Computer"
3. Click "Generate"
4. Copy the 16-character password (e.g., `abcd efgh ijkl mnop`)

**2.3 Update Configuration**

Your `php/email_config.php` is already configured with:
```php
define('EMAIL_METHOD', 'smtp'); // ✅ Already set
define('SMTP_HOST', 'smtp.gmail.com'); // ✅ Already set
define('SMTP_PORT', 587); // ✅ Already set
define('SMTP_USERNAME', 'cyclopes543@gmail.com'); // ✅ Your email
define('SMTP_PASSWORD', 'ancweqnezjloprek'); // ✅ Your app password
```

⚠️ **Security Note**: This password is visible in the file. For production, use environment variables.

### Step 3: Test It!

Run the test:
```
http://localhost/Routa/test_email.php
```

## 📁 What Was Updated

The `php/email_helper.php` now has 3 functions:

1. **`sendEmail()`** - Main function (auto-detects method)
2. **`sendEmailViaSMTP()`** - Uses PHPMailer with SMTP
3. **`sendEmailViaMailFunction()`** - Fallback to PHP mail()

## 🧪 Testing Steps

### Test 1: Check Configuration
```powershell
cd d:\xampp\htdocs\Routa
php -r "echo file_exists('vendor/autoload.php') ? 'PHPMailer installed!' : 'PHPMailer NOT installed';"
```

### Test 2: Use Test Page
1. Open: `http://localhost/Routa/test_email.php`
2. Enter your email address
3. Click "Send Test Email"
4. Check your inbox

### Test 3: Check Logs
```powershell
Get-Content C:\xampp\php\logs\php_error_log -Tail 20 | Select-String "Email"
```

Look for:
```
[2025-11-18 10:30:45] Email to: yourname@gmail.com | Subject: ... | Status: SUCCESS (SMTP)
```

### Test 4: Real Driver Application
1. Go to: `http://localhost/Routa/be-a-driver.php`
2. Fill out and submit the application
3. Check email inbox

## ⚙️ Configuration Options

### Using Gmail SMTP (Current Setup)
```php
define('EMAIL_METHOD', 'smtp');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

### Using Other SMTP Providers

**SendGrid**
```php
define('SMTP_HOST', 'smtp.sendgrid.net');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'apikey');
define('SMTP_PASSWORD', 'your-sendgrid-api-key');
```

**Mailgun**
```php
define('SMTP_HOST', 'smtp.mailgun.org');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-mailgun-username');
define('SMTP_PASSWORD', 'your-mailgun-password');
```

**Outlook/Hotmail**
```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@outlook.com');
define('SMTP_PASSWORD', 'your-password');
```

### Switch Back to PHP mail()
```php
define('EMAIL_METHOD', 'mail'); // Change from 'smtp' to 'mail'
```

## 🔍 Troubleshooting

### Problem: "PHPMailer not found"

**Solution 1: Install via Composer**
```powershell
cd d:\xampp\htdocs\Routa
composer require phpmailer/phpmailer
```

**Solution 2: Manual Install**
1. Download: https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip
2. Extract to: `d:\xampp\htdocs\Routa\vendor\phpmailer\phpmailer\`
3. Create `vendor/autoload.php` with:
```php
<?php
require_once __DIR__ . '/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/phpmailer/phpmailer/src/Exception.php';
```

### Problem: "SMTP connect() failed"

**Possible Causes:**
1. Wrong SMTP host or port
2. Firewall blocking port 587
3. Wrong credentials
4. 2FA not enabled or wrong App Password

**Solutions:**
```powershell
# Test SMTP connection
telnet smtp.gmail.com 587

# If telnet doesn't work, try:
Test-NetConnection -ComputerName smtp.gmail.com -Port 587
```

If blocked, try port 465 with SSL:
```php
define('SMTP_PORT', 465);
define('SMTP_SECURE', 'ssl');
```

### Problem: "Authentication failed"

**Check:**
1. ✅ 2-Factor Authentication is enabled
2. ✅ App Password (not regular password) is used
3. ✅ App Password has no spaces
4. ✅ Username is your full email address

**Generate new App Password:**
1. Go to: https://myaccount.google.com/apppasswords
2. Delete old one
3. Create new one
4. Update `SMTP_PASSWORD` in config

### Problem: Emails still not sending

**Enable Debug Mode:**

Edit `php/email_helper.php` and add after `$mail = new...`:
```php
$mail->SMTPDebug = 2; // Enable verbose debug output
$mail->Debugoutput = function($str, $level) {
    error_log("SMTP Debug: $str");
};
```

Then check error logs:
```powershell
Get-Content C:\xampp\php\logs\php_error_log -Tail 50
```

### Problem: "Could not instantiate mail function"

This means falling back to `mail()` function. Solutions:

1. **Install PHPMailer** (see Step 1)
2. **Or configure XAMPP sendmail** (see EMAIL_SETUP_GUIDE.md)
3. **Or disable SMTP:**
```php
define('EMAIL_METHOD', 'mail');
```

## 📊 Verify Installation

### Check if PHPMailer is installed:
```powershell
cd d:\xampp\htdocs\Routa
dir vendor\phpmailer\phpmailer\src\PHPMailer.php
```

Should show the file. If not, run:
```powershell
composer require phpmailer/phpmailer
```

### Check current configuration:
Open `http://localhost/Routa/test_email.php` - it shows all settings

## 🔐 Security Best Practices

### For Development (Current)
- ✅ Hardcode credentials in `email_config.php`
- ✅ Keep `EMAIL_ENABLED = true`
- ✅ Use test Gmail account

### For Production
1. **Use Environment Variables**
```php
define('SMTP_USERNAME', getenv('EMAIL_USERNAME'));
define('SMTP_PASSWORD', getenv('EMAIL_PASSWORD'));
```

2. **Separate Config File** (not in git)
```php
// email_config.local.php (add to .gitignore)
define('SMTP_PASSWORD', 'your-secret-password');
```

3. **Use Dedicated Email Service**
- SendGrid (100 emails/day free)
- Mailgun (5,000 emails/month free)
- Amazon SES (pay as you go)

## 🎯 What Happens Now

When a driver submits an application:

```
1. Form submitted
   ↓
2. Data validated & saved
   ↓
3. sendDriverApplicationThankYou() called
   ↓
4. sendEmail() checks EMAIL_METHOD
   ↓
5. If 'smtp': sendEmailViaSMTP()
   ↓
6. PHPMailer connects to Gmail SMTP
   ↓
7. Email sent via authenticated SMTP
   ↓
8. Success logged to error log
   ↓
9. Driver receives email in inbox
```

## 📧 Email Features Now Available

With PHPMailer, you can now:
- ✅ Send via authenticated SMTP
- ✅ Send from custom domain
- ✅ Add CC/BCC recipients
- ✅ Send attachments
- ✅ Use HTML templates
- ✅ Track delivery status
- ✅ Better error messages

## 🚀 Production Deployment

### InfinityFree / Free Hosting
```php
// Use external SMTP (don't rely on host's mail server)
define('EMAIL_METHOD', 'smtp');
define('SMTP_HOST', 'smtp.gmail.com');
// ... rest of config
```

### VPS / Dedicated Server
```php
// Can use local mail server or SMTP
define('EMAIL_METHOD', 'mail'); // If you've configured Postfix
// OR
define('EMAIL_METHOD', 'smtp'); // Use external SMTP
```

### Shared Hosting (cPanel)
Most shared hosts support PHP's mail() but SMTP is more reliable:
```php
define('EMAIL_METHOD', 'smtp'); // Recommended
```

## 📞 Quick Commands Reference

```powershell
# Install PHPMailer
composer require phpmailer/phpmailer

# Check if installed
dir vendor\phpmailer\phpmailer\src\PHPMailer.php

# View email logs
Get-Content C:\xampp\php\logs\php_error_log -Tail 20

# Search for email entries
Get-Content C:\xampp\php\logs\php_error_log | Select-String "Email"

# Test SMTP connection
Test-NetConnection -ComputerName smtp.gmail.com -Port 587

# Clear error log (if needed)
Clear-Content C:\xampp\php\logs\php_error_log
```

## ✅ Success Checklist

- [ ] Composer installed
- [ ] PHPMailer installed (`composer require phpmailer/phpmailer`)
- [ ] Gmail 2FA enabled
- [ ] App Password generated
- [ ] `email_config.php` updated with credentials
- [ ] `EMAIL_METHOD` set to `'smtp'`
- [ ] Test email sent successfully
- [ ] Driver application sends email
- [ ] Email appears in inbox (not spam)
- [ ] Logs show "SUCCESS (SMTP)"

## 🎓 Learning Resources

- **PHPMailer Docs**: https://github.com/PHPMailer/PHPMailer
- **Gmail SMTP Setup**: https://support.google.com/mail/answer/7126229
- **Composer Docs**: https://getcomposer.org/doc/
- **PHP mail()**: https://www.php.net/manual/en/function.mail.php

---

**Need Help?** Check the test page: `http://localhost/Routa/test_email.php`

**Still Having Issues?** Check `EMAIL_SETUP_GUIDE.md` for more troubleshooting steps.
