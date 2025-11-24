# Installation Guide

Complete step-by-step installation guide for Routa Tricycle Booking System.

## Prerequisites

Before installing, ensure you have:

- **Web Server**: Apache 2.4+ or Nginx
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 8.0 or higher
- **Composer**: For dependency management
- **Git**: For cloning the repository

## Step 1: Download/Clone Project

### Option A: Clone from GitHub

```bash
git clone https://github.com/vegapanz/routa
cd routa
```

### Option B: Download ZIP

1. Download the ZIP file from GitHub
2. Extract to your web server directory (e.g., `htdocs`, `www`, `public_html`)

## Step 2: Install Dependencies

Install PHP dependencies using Composer:

```bash
composer install
```

This will install:
- PHPMailer (email functionality)
- Other required packages

## Step 3: Database Setup

### Create Database

```sql
CREATE DATABASE routa_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Import Database Structure

Import the SQL file using one of these methods:

**Option A: phpMyAdmin**
1. Open phpMyAdmin
2. Select `routa_db` database
3. Click "Import" tab
4. Choose `database/routa_db.sql`
5. Click "Go"

**Option B: MySQL Command Line**
```bash
mysql -u root -p routa_db < database/routa_db.sql
```

**Option C: MySQL Workbench**
1. Open MySQL Workbench
2. Server → Data Import
3. Import from Self-Contained File
4. Select `database/routa_db.sql`
5. Start Import

### Verify Tables

Check that these tables were created:
- `users`
- `drivers`
- `driver_applications`
- `bookings`
- `ratings`
- `notifications`

## Step 4: Configuration

### Database Configuration

Edit `includes/config/database.php`:

```php
<?php
$host = 'localhost';
$dbname = 'routa_db';
$username = 'root';  // Your MySQL username
$password = '';      // Your MySQL password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    date_default_timezone_set('Asia/Manila');
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}
?>
```

### Application Constants

Edit `includes/config/constants.php`:

```php
<?php
// Base URL
define('BASE_URL', 'http://localhost/routa/');

// Google OAuth
define('GOOGLE_CLIENT_ID', 'your-google-client-id');
define('GOOGLE_CLIENT_SECRET', 'your-google-client-secret');
define('GOOGLE_REDIRECT_URI', BASE_URL . 'php/google_callback.php');

// Facebook OAuth
define('FACEBOOK_APP_ID', 'your-facebook-app-id');
define('FACEBOOK_APP_SECRET', 'your-facebook-app-secret');
define('FACEBOOK_REDIRECT_URI', BASE_URL . 'php/facebook_callback.php');

// Google Maps
define('GOOGLE_MAPS_API_KEY', 'your-google-maps-api-key');

// Email Settings (PHPMailer)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'Routa Booking System');

// File Upload
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf']);

// Session
define('SESSION_LIFETIME', 3600); // 1 hour
?>
```

## Step 5: File Permissions

Set proper permissions for upload and log directories:

### Linux/Mac:
```bash
chmod 755 uploads/
chmod 755 logs/
```

### Windows:
Right-click folders → Properties → Security → Edit → Allow "Full Control"

## Step 6: Apache Configuration (Optional)

If using Apache, ensure `mod_rewrite` is enabled and `.htaccess` is working.

Create `.htaccess` in root directory:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /routa/
    
    # Force HTTPS (production only)
    # RewriteCond %{HTTPS} off
    # RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>

# Prevent directory browsing
Options -Indexes

# Protect sensitive files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>
```

## Step 7: Test Installation

### Test Database Connection

Create a test file `test_connection.php`:

```php
<?php
require_once 'includes/init.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    echo "✅ Database connection successful!<br>";
    echo "Users table accessible!";
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage();
}
?>
```

Visit: `http://localhost/routa/test_connection.php`



**Login Credentials:**
- Email: `admin@routa.com`
- Password: `admin123`

⚠️ **Change this password immediately after first login!**

## Step 8: Verify Installation

Test each page:

1. **Homepage** - `http://localhost/routa/`
2. **Login** - `http://localhost/routa/login.php`
3. **Register** - `http://localhost/routa/register.php`
4. **Admin Dashboard** - Login as admin → `admin.php`

## Common Issues

### Issue: "Call to undefined function mb_strlen"

**Solution:** Enable `mbstring` extension in `php.ini`:
```ini
extension=mbstring
```

### Issue: "PDO connection failed"

**Solution:** 
1. Verify MySQL is running
2. Check database credentials
3. Ensure database exists

### Issue: "Headers already sent"

**Solution:** 
1. Check for whitespace before `<?php`
2. Save files with UTF-8 encoding (no BOM)

### Issue: Email not sending

**Solution:**
1. For Gmail, use App Password (not regular password)
2. Enable "Less secure app access" or use OAuth2
3. Check SMTP credentials

### Issue: File upload errors

**Solution:**
1. Check folder permissions (755 or 777)
2. Verify `upload_max_filesize` in `php.ini`
3. Check `post_max_size` in `php.ini`

## Next Steps

After successful installation:

1. ✅ Change admin password
2. ✅ Configure email settings
3. ✅ Set up Google Maps API
4. ✅ Configure OAuth (optional)
5. ✅ Test booking flow
6. ✅ Review security settings

## Production Deployment

For production deployment, see:
- `DEPLOYMENT_FINAL_CHECKLIST.md`
- `QUICK_START_DEPLOY.md`
- `INFINITYFREE_DEPLOYMENT_GUIDE.md`

## Support

If you encounter issues:
1. Check `logs/` directory for error logs
2. Enable error reporting in development
3. Review `TROUBLESHOOTING.md`
4. Open an issue on GitHub

