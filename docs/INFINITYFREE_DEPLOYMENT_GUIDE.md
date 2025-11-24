# 🚀 InfinityFree Deployment Guide for Routa

Complete step-by-step guide to deploy your Routa tricycle booking system on InfinityFree hosting.

---

## 📋 BEFORE YOU START - Preparation Checklist

### ✅ What You Need:
1. Your Routa project files (you have them!)
2. InfinityFree account (free, sign up at infinityfree.net)
3. An email address for account verification
4. FTP client (FileZilla recommended - free download)
5. Your database backup ready

---

## PART 1: Setting Up InfinityFree Account

### Step 1: Sign Up for InfinityFree
1. Go to **https://infinityfree.net**
2. Click "Sign Up Now" (top right)
3. Fill in:
   - Email address
   - Password
   - Complete CAPTCHA
4. Click "Sign Up"
5. Check your email and verify your account

### Step 2: Create Your Website
1. Log in to InfinityFree Client Area
2. Click **"Create Account"**
3. Choose your domain option:
   - **Option A:** Use free subdomain (e.g., `routa.rf.gd` or `routa.42web.io`)
   - **Option B:** Use your own domain (if you have one)
4. Enter username (will be your hosting username - remember this!)
5. Click "Create Account"
6. Wait 2-10 minutes for account activation

### Step 3: Get Your Hosting Details
After activation, note down these important details:
```
FTP Hostname: ftpupload.net (or from your control panel)
FTP Username: epiz_XXXXXXXX (your account username)
FTP Password: (the one you set during creation)
MySQL Hostname: sqlXXX.infinityfreeapp.com
MySQL Username: epiz_XXXXXXXX_routadb
MySQL Password: (you'll set this)
cPanel URL: https://cpanel.infinityfreeapp.com
```

---

## PART 2: Database Setup

### Step 1: Access MySQL in cPanel
1. Go to your cPanel (link in your account email)
2. Find **"MySQL Databases"** icon
3. Click on it

### Step 2: Create Database
1. In "Create New Database" section:
   - Database Name: `routadb` (or any name you prefer)
   - Click "Create Database"
2. **IMPORTANT:** Your full database name will be: `epiz_XXXXXXXX_routadb`
   - Write this down!

### Step 3: Create Database User
1. Scroll to "MySQL Users" section:
   - Username: `routauser` (or any name)
   - Password: Create a strong password
   - Click "Create User"
2. **IMPORTANT:** Your full username will be: `epiz_XXXXXXXX_routauser`
   - Write this down!

### Step 4: Add User to Database
1. Scroll to "Add User to Database"
2. Select your user and database
3. Click "Add"
4. Grant **ALL PRIVILEGES**
5. Click "Make Changes"

### Step 5: Import Your Database
1. In cPanel, find **"phpMyAdmin"** icon
2. Click on it (opens in new tab)
3. Select your database (epiz_XXXXXXXX_routadb) from left sidebar
4. Click **"Import"** tab at the top
5. Click **"Choose File"**
6. Select: `Routa/database/routa_database.sql` from your computer
7. Scroll down and click **"Go"**
8. Wait for success message ✅

**IMPORTANT NOTE:** InfinityFree has a 50MB import limit. Your database.sql should be under this. If you get an error, we'll need to split it.

---

## PART 3: Uploading Your Files

### Step 1: Install FileZilla (if you don't have it)
1. Download from: **https://filezilla-project.org/**
2. Install and open FileZilla

### Step 2: Connect to Your Hosting
1. In FileZilla, enter at the top:
   - Host: `ftpupload.net`
   - Username: `epiz_XXXXXXXX` (your FTP username)
   - Password: (your FTP password)
   - Port: `21`
2. Click "Quickconnect"
3. Wait for connection (green text = success)

### Step 3: Navigate to Web Root
1. In the RIGHT panel (remote site):
2. Open folder: `htdocs` (this is your web root)
3. This is where your website files go

### Step 4: Upload Routa Files
1. In the LEFT panel (local site):
   - Navigate to: `d:\xampp\htdocs\Routa`
2. Select ALL files and folders:
   - index.php
   - login.php
   - register.php
   - userdashboard.php
   - driver_dashboard.php
   - admin.php
   - assets/ (folder)
   - php/ (folder)
   - database/ (folder)
   - docs/ (folder)
   - components/ (folder)
   - uploads/ (folder)
   - ALL OTHER FILES
3. **Right-click** → **Upload**
4. Wait for upload to complete (may take 5-15 minutes)

**⚠️ DO NOT UPLOAD:**
- `_old_migrations/` folder (not needed)
- `tests/` folder (optional, but not needed)
- Any test_*.html or test_*.php files from root
- debug_*.php files

---

## PART 4: Configure Your Application

### Step 1: Update config.php
You need to update your database settings for InfinityFree.

**IN YOUR LOCAL COMPUTER:**
1. Open: `d:\xampp\htdocs\Routa\php\config.php`
2. Find these lines (around line 7-10):
```php
$host = 'localhost';
$dbname = 'routa_db';
$username = 'root';
$password = '';
```

3. Replace with your InfinityFree details:
```php
$host = 'sqlXXX.infinityfreeapp.com';  // Your MySQL hostname
$dbname = 'epiz_XXXXXXXX_routadb';     // Your full database name
$username = 'epiz_XXXXXXXX_routauser'; // Your full database username
$password = 'YOUR_DATABASE_PASSWORD';   // Database password you created
```

4. Find the BASE_URL line (around line 36):
```php
define('BASE_URL', 'http://localhost/Routa');
```

5. Replace with your InfinityFree URL:
```php
define('BASE_URL', 'http://yoursite.rf.gd');  // Your actual domain
```

6. Update OAuth redirect URLs (lines 28, 33):
```php
define('GOOGLE_REDIRECT_URI', 'http://yoursite.rf.gd/php/google-callback.php');
define('FACEBOOK_REDIRECT_URI', 'http://yoursite.rf.gd/php/facebook-callback.php');
```

7. **SAVE THE FILE**

### Step 2: Upload Updated config.php
1. In FileZilla, navigate to `htdocs/php/`
2. Upload the updated `config.php` (overwrite the old one)

### Step 3: Update OAuth Settings (IF USING)

**For Google OAuth:**
1. Go to: https://console.cloud.google.com
2. Select your project
3. Go to "Credentials"
4. Edit your OAuth 2.0 Client ID
5. Add to "Authorized redirect URIs":
   - `http://yoursite.rf.gd/php/google-callback.php`
   - `https://yoursite.rf.gd/php/google-callback.php`
6. Save

**For Facebook OAuth:**
1. Go to: https://developers.facebook.com
2. Select your app
3. Go to Settings → Basic
4. Update "App Domains": `yoursite.rf.gd`
5. Go to Facebook Login → Settings
6. Update "Valid OAuth Redirect URIs":
   - `http://yoursite.rf.gd/php/facebook-callback.php`
   - `https://yoursite.rf.gd/php/facebook-callback.php`
7. Save

---

## PART 5: Fix File Permissions

### Step 1: Set Uploads Folder Permissions
The `uploads/` folder needs write permissions for file uploads to work.

**In FileZilla:**
1. Navigate to `htdocs/uploads/`
2. Right-click on `uploads` folder
3. Select **"File Permissions"**
4. Set to: `755` or `777` (tick all boxes)
5. Check "Recurse into subdirectories"
6. Click OK

### Step 2: Check PHP Folder Permissions
Some hosts need specific permissions for security:
1. Right-click on `htdocs/php/` folder
2. File Permissions → Set to `755`

---

## PART 6: Testing Your Website

### Step 1: Visit Your Website
1. Open browser
2. Go to: `http://yoursite.rf.gd`
3. You should see your Routa homepage

### Step 2: Test Database Connection
If you see errors, check:
- Database hostname is correct
- Database name, username, password are correct
- Database was imported successfully

### Step 3: Test Login System
1. Go to: `http://yoursite.rf.gd/login.php`
2. Try logging in with test accounts:
   - **User:** juan@email.com / password
   - **Driver:** pedro@driver.com / password
   - **Admin:** admin@routa.com / admin123

### Step 4: Test User Dashboard
1. Log in as user
2. Go to dashboard
3. Try booking a ride
4. Check if map loads (requires Google Maps API key)

### Step 5: Test Driver Dashboard
1. Log in as driver
2. Check if dashboard loads
3. Try accepting a ride request

### Step 6: Test Admin Panel
1. Log in as admin
2. Go to admin panel
3. Check all sections work

---

## PART 7: Common Issues & Solutions

### Issue 1: "Connection Failed" Error
**Cause:** Wrong database credentials
**Solution:**
- Double-check `php/config.php`
- Verify database name includes full prefix: `epiz_XXXXXXXX_routadb`
- Test database connection in phpMyAdmin

### Issue 2: "500 Internal Server Error"
**Cause:** PHP errors or file permissions
**Solution:**
- Check error logs in cPanel (Error Logs icon)
- Set file permissions correctly (755 for folders, 644 for files)
- Check if all files uploaded correctly

### Issue 3: Blank Page / White Screen
**Cause:** PHP errors not displayed
**Solution:**
- Check error logs in cPanel
- Enable error display temporarily (not recommended for production)

### Issue 4: Images Not Loading
**Cause:** Wrong paths or missing files
**Solution:**
- Verify `assets/images/` folder uploaded correctly
- Check file paths in HTML/CSS use relative paths
- Clear browser cache

### Issue 5: Upload Features Not Working
**Cause:** Wrong folder permissions
**Solution:**
- Set `uploads/` folder to 777 permissions
- Check if folder exists on server
- Verify PHP upload settings in cPanel

### Issue 6: Sessions Not Working / Keep Logging Out
**Cause:** Session path issues
**Solution:**
Add to top of `php/config.php` (after `<?php`):
```php
// Fix session path for InfinityFree
ini_set('session.save_path', getcwd() . '/sessions');
if (!is_dir('sessions')) {
    mkdir('sessions', 0777);
}
```

### Issue 7: Google Maps Not Working
**Cause:** API key restrictions
**Solution:**
1. Go to Google Cloud Console
2. Update API key restrictions
3. Add your domain to allowed referrers

---

## PART 8: Important InfinityFree Limitations

### Be Aware Of:
1. **Daily Hits Limit:** 50,000 hits/day (usually enough for small apps)
2. **CPU Time:** Limited - optimize your queries
3. **Cron Jobs:** Not available on free plan
4. **Email:** Use external SMTP (Gmail, SendGrid, etc.)
5. **File Uploads:** Max 10MB per file
6. **Execution Time:** 30 seconds max
7. **No SSH Access:** FTP only
8. **No Real-Time Features:** WebSockets not supported

### Optimization Tips:
- Use caching where possible
- Optimize images (compress before upload)
- Minimize database queries
- Use CDN for assets if needed
- Enable browser caching

---

## PART 9: Post-Deployment Tasks

### Security Improvements:
1. **Remove Test Files:**
   - Delete all test_*.html and test_*.php files
   - Remove debug_*.php files

2. **Update Passwords:**
   - Change default admin password
   - Update test user passwords

3. **Disable Error Display:**
   In `php/config.php`, add:
   ```php
   // Disable error display in production
   ini_set('display_errors', 0);
   error_reporting(0);
   ```

4. **Set Up SSL (Highly Recommended):**
   - InfinityFree offers free SSL
   - Go to cPanel → SSL/TLS
   - Click "Install SSL"
   - Wait 5 minutes for activation
   - Update all URLs to use `https://`

5. **Enable .htaccess Protection:**
   Create `.htaccess` in root with:
   ```apache
   # Protect sensitive files
   <FilesMatch "\.(sql|md|log)$">
       Order allow,deny
       Deny from all
   </FilesMatch>
   
   # Prevent directory browsing
   Options -Indexes
   ```

### Monitoring:
1. Check error logs daily (first week)
2. Monitor disk space usage
3. Track bandwidth usage
4. Test all features regularly

---

## PART 10: Quick Reference

### Your Login URLs:
```
Website: http://yoursite.rf.gd
cPanel: https://cpanel.infinityfreeapp.com
FTP: ftpupload.net
phpMyAdmin: (link in cPanel)
```

### Test Accounts:
```
User Login:
  Email: juan@email.com
  Password: password

Driver Login:
  Email: pedro@driver.com
  Password: password

Admin Login:
  Email: admin@routa.com
  Password: admin123
```

### Important Files to Update:
```
✅ php/config.php - Database & URLs
✅ OAuth settings in Cloud Console
✅ Google Maps API restrictions
✅ Remove test files
```

### Support Resources:
```
InfinityFree Forum: https://forum.infinityfree.net
Documentation: https://infinityfree.net/support
Your cPanel: Error logs and file manager
```

---

## 🎉 Deployment Complete!

Once everything is working:
1. ✅ Share your live URL with users
2. ✅ Update your README.md with live URL
3. ✅ Monitor for any issues
4. ✅ Consider upgrading to paid hosting when you grow

---

## 📞 Need Help?

If you encounter issues:
1. Check the "Common Issues" section above
2. Review error logs in cPanel
3. Search InfinityFree forum
4. Check your file paths and permissions

**Remember:** Free hosting is great for testing and small projects, but for production with real users, consider paid hosting for better performance and support.

---

**Good luck with your deployment! 🚀🎊**
