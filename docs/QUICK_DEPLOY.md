# 🚀 Quick Start: Deploy Routa to InfinityFree in 30 Minutes

This is your EXPRESS guide to get Routa live FAST!

---

## ⏱️ TIME ESTIMATE: 30 MINUTES

**Breakdown:**
- 5 min: InfinityFree account setup
- 10 min: Database setup and import
- 10 min: File upload via FTP
- 5 min: Configuration and testing

---

## STEP 1: InfinityFree Account (5 minutes)

1. **Go to:** https://infinityfree.net
2. **Click:** "Sign Up Now"
3. **Fill in:** Email, password, CAPTCHA
4. **Click:** Sign Up → Check email → Verify
5. **In Client Area:** Click "Create Account"
6. **Choose domain:** Something like `routa` (becomes `routa.rf.gd`)
7. **Wait:** 2-10 minutes for activation

**✅ YOU NOW HAVE:**
- FTP Username: `epiz_XXXXXXXX`
- cPanel access
- Free subdomain: `yoursite.rf.gd`

---

## STEP 2: Database Setup (10 minutes)

### Create Database (3 minutes)

1. **Open:** cPanel (link from email)
2. **Click:** "MySQL Databases"
3. **Create Database:**
   - Name: `routadb`
   - Click "Create Database"
4. **Write down:** `epiz_XXXXXXXX_routadb` (full name)

### Create User (2 minutes)

5. **Scroll to:** "MySQL Users"
6. **Create User:**
   - Username: `routauser`
   - Password: [CREATE STRONG PASSWORD]
   - Click "Create User"
7. **Write down:** Username and password

### Add User to Database (1 minute)

8. **Scroll to:** "Add User to Database"
9. **Select:** Your user and database
10. **Click:** "Add"
11. **Grant:** ALL PRIVILEGES
12. **Click:** "Make Changes"

### Import Database (4 minutes)

13. **In cPanel:** Click "phpMyAdmin"
14. **Select:** Your database (epiz_XXXXXXXX_routadb)
15. **Click:** "Import" tab
16. **Choose File:** Browse to `d:\xampp\htdocs\Routa\database\routa_database.sql`
17. **Click:** "Go" at the bottom
18. **Wait:** Success message ✅

**✅ DATABASE IS READY!**

---

## STEP 3: Upload Files (10 minutes)

### Setup FTP (2 minutes)

1. **Download FileZilla:** https://filezilla-project.org/ (if needed)
2. **Open FileZilla**
3. **Connect:**
   - Host: `ftpupload.net`
   - Username: `epiz_XXXXXXXX`
   - Password: [Your FTP password]
   - Port: `21`
4. **Click:** Quickconnect

### Upload Files (8 minutes)

5. **On RIGHT panel:** Navigate to `htdocs` folder
6. **On LEFT panel:** Navigate to `d:\xampp\htdocs\Routa`
7. **Select these files/folders to upload:**
   ```
   ✅ index.php
   ✅ login.php
   ✅ register.php
   ✅ userdashboard.php
   ✅ driver_dashboard.php
   ✅ admin.php
   ✅ driver-application.php
   ✅ view_driver_applications.php
   ✅ be-a-driver.php
   ✅ assets/ (entire folder)
   ✅ php/ (entire folder)
   ✅ components/ (entire folder)
   ✅ .htaccess (the file you just created)
   ```

8. **Right-click** → **Upload**
9. **Wait:** Files uploading...

### Create Additional Folders (1 minute)

10. **On RIGHT panel:** Right-click in htdocs
11. **Create directory:** `uploads`
12. **Right-click uploads** → **File Permissions** → Set to `777`
13. **Create directory:** `sessions`
14. **Right-click sessions** → **File Permissions** → Set to `777`

**✅ FILES ARE UPLOADED!**

---

## STEP 4: Configure (5 minutes)

### Get Your Details Ready

**From cPanel MySQL section, write down:**
```
MySQL Hostname: sql_____.infinityfreeapp.com
Database Name: epiz_________routadb
Database Username: epiz_________routauser
Database Password: ___________________
Your Domain: http://________.rf.gd
```

### Update config.php (3 minutes)

1. **On your computer:** Open `d:\xampp\htdocs\Routa\php\config.php`

2. **Find lines 7-10, replace with:**
```php
$host = 'sql123.infinityfreeapp.com';          // YOUR MySQL hostname
$dbname = 'epiz_12345678_routadb';             // YOUR database name
$username = 'epiz_12345678_routauser';         // YOUR database username
$password = 'YourPasswordHere';                // YOUR database password
```

3. **Find line 36, replace with:**
```php
define('BASE_URL', 'http://yoursite.rf.gd');   // YOUR domain (no trailing slash!)
```

4. **Find lines 28, 33, replace with:**
```php
define('GOOGLE_REDIRECT_URI', 'http://yoursite.rf.gd/php/google-callback.php');
define('FACEBOOK_REDIRECT_URI', 'http://yoursite.rf.gd/php/facebook-callback.php');
```

5. **SAVE the file!**

### Upload Updated config.php (2 minutes)

6. **In FileZilla:**
   - Navigate to `htdocs/php/`
   - Upload the updated `config.php`
   - Overwrite the old one

**✅ CONFIGURATION COMPLETE!**

---

## STEP 5: TEST! (5 minutes)

### Test Homepage (1 minute)

1. **Open browser**
2. **Go to:** `http://yoursite.rf.gd`
3. **Check:** Homepage loads? ✅
4. **Check:** Images load? ✅
5. **Check:** No errors? ✅

### Test Login (2 minutes)

6. **Go to:** `http://yoursite.rf.gd/login.php`
7. **Login as User:**
   - Email: `juan@email.com`
   - Password: `password`
8. **Check:** Login works? ✅
9. **Check:** Dashboard loads? ✅

10. **Logout**

11. **Login as Driver:**
   - Email: `pedro@driver.com`
   - Password: `password`
12. **Check:** Driver dashboard loads? ✅

13. **Logout**

14. **Login as Admin:**
   - Email: `admin@routa.com`
   - Password: `admin123`
15. **Check:** Admin panel loads? ✅

### Test Booking (2 minutes)

16. **Login as user** again
17. **Try booking a ride:**
    - Enter pickup location
    - Enter destination
    - Click book
18. **Check:** Booking created? ✅

**✅ YOUR SITE IS LIVE!**

---

## 🎉 CONGRATULATIONS!

Your Routa tricycle booking system is now live at:

**🌐 http://yoursite.rf.gd**

### Test Accounts for Your Users:

```
👤 USER LOGIN:
   Email: juan@email.com
   Password: password

🚗 DRIVER LOGIN:
   Email: pedro@driver.com
   Password: password

👨‍💼 ADMIN LOGIN:
   Email: admin@routa.com
   Password: admin123
```

---

## 📋 NEXT STEPS

### Immediately:

1. **Change admin password!**
   - Login as admin
   - Go to settings/profile
   - Update password

2. **Test all features:**
   - User booking
   - Driver accepting rides
   - Admin assigning drivers
   - File uploads (driver application)

3. **Check for errors:**
   - cPanel → Error Logs
   - Fix any issues

### Within 24 Hours:

4. **Install SSL Certificate:**
   - cPanel → SSL/TLS
   - Click "Install SSL"
   - Wait 5-10 minutes
   - Update all URLs to use `https://`

5. **Update OAuth settings** (if using):
   - Google Cloud Console
   - Facebook Developers
   - Add your new domain to redirect URIs

6. **Remove test files:**
   - Delete any test_*.html, test_*.php, debug_*.php

### This Week:

7. **Share with friends/testers**
8. **Gather feedback**
9. **Fix any bugs**
10. **Consider custom domain** (optional)

---

## ❗ IMPORTANT REMINDERS

**FREE HOSTING LIMITS:**
- 50,000 hits/day
- 10MB file uploads
- No SSH access
- Limited CPU time
- Basic email support

**When to upgrade:**
- Getting real users
- Need better performance
- Need custom domain
- Need email support
- Need cron jobs

---

## 🆘 IF SOMETHING GOES WRONG

1. **Check:** `TROUBLESHOOTING.md` file
2. **Check:** cPanel Error Logs
3. **Review:** `INFINITYFREE_DEPLOYMENT_GUIDE.md`
4. **Visit:** https://forum.infinityfree.net

**Most common issues:**
- Wrong database credentials in config.php
- Forgot to include `epiz_XXXXXXXX_` prefix
- Wrong domain in BASE_URL
- Uploads folder permissions not 777

---

## 📞 YOUR DEPLOYMENT INFO

Save this for future reference:

```
==============================================
      ROUTA - INFINITYFREE DEPLOYMENT
==============================================

Website URL: http://____________.rf.gd
Deployed on: ____/____/2025

FTP Details:
  Host: ftpupload.net
  Username: epiz___________
  Password: _______________

Database Details:
  Host: sql_____.infinityfreeapp.com
  Database: epiz___________routadb
  Username: epiz___________routauser
  Password: _______________

cPanel: https://cpanel.infinityfreeapp.com

Admin Account:
  Email: admin@routa.com
  Password: admin123 (CHANGE THIS!)

==============================================
```

---

## ✅ DEPLOYMENT COMPLETE!

**Time taken:** _____ minutes

**Status:** 🟢 LIVE

**URL:** http://____________.rf.gd

**Share your success!** 🎊

---

**Need the detailed guide?** Check `INFINITYFREE_DEPLOYMENT_GUIDE.md`

**Having issues?** Check `TROUBLESHOOTING.md`

**Track your progress?** Use `DEPLOYMENT_CHECKLIST.md`
