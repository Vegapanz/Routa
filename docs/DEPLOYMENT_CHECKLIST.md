# 📋 InfinityFree Deployment Checklist

Use this checklist to ensure you don't miss any steps during deployment.

---

## PHASE 1: PREPARATION (Before Uploading)

### Account Setup
- [ ] Sign up for InfinityFree account
- [ ] Verify email address
- [ ] Create website/hosting account
- [ ] Note down FTP credentials
- [ ] Note down MySQL hostname

### Database Preparation
- [ ] Backup local database (already have: `database/routa_database.sql`)
- [ ] Check database file size (should be under 50MB)
- [ ] Test database locally one last time

### File Preparation
- [ ] Review all files in `d:\xampp\htdocs\Routa`
- [ ] Identify files NOT to upload (tests, debug files)
- [ ] Update `config.production.php` with placeholders
- [ ] Make note of current `config.php` settings

---

## PHASE 2: REMOTE SETUP (On InfinityFree)

### Database Setup
- [ ] Log in to cPanel
- [ ] Create MySQL database (name: `routadb`)
- [ ] Write down full database name: `epiz_________routadb`
- [ ] Create database user
- [ ] Write down full username: `epiz_________routauser`
- [ ] Write down database password: `_______________`
- [ ] Add user to database with ALL PRIVILEGES
- [ ] Open phpMyAdmin
- [ ] Import `routa_database.sql` successfully
- [ ] Verify all tables are present (check users, tricycle_drivers, admins, etc.)

### FTP Connection
- [ ] Download and install FileZilla (if needed)
- [ ] Connect to FTP server: `ftpupload.net`
- [ ] Navigate to `htdocs` folder
- [ ] Connection successful

---

## PHASE 3: FILE UPLOAD

### Upload Files
- [ ] Upload `index.php`
- [ ] Upload `login.php`
- [ ] Upload `register.php`
- [ ] Upload `userdashboard.php`
- [ ] Upload `driver_dashboard.php`
- [ ] Upload `admin.php`
- [ ] Upload `driver-application.php`
- [ ] Upload `view_driver_applications.php`
- [ ] Upload `be-a-driver.php`

### Upload Folders
- [ ] Upload `assets/` folder (CSS, JS, images)
- [ ] Upload `php/` folder (backend scripts)
- [ ] Upload `components/` folder
- [ ] Upload `database/` folder (for reference)
- [ ] Upload `docs/` folder (optional)
- [ ] Create empty `uploads/` folder on server
- [ ] Create empty `sessions/` folder on server (optional)

### Files NOT to Upload
- [ ] Confirmed: NOT uploading `_old_migrations/`
- [ ] Confirmed: NOT uploading `tests/`
- [ ] Confirmed: NOT uploading test_*.html files
- [ ] Confirmed: NOT uploading test_*.php files
- [ ] Confirmed: NOT uploading debug_*.php files

---

## PHASE 4: CONFIGURATION

### Update config.php
- [ ] Open local `php/config.php`
- [ ] Update `$host` to MySQL hostname
- [ ] Update `$dbname` to full database name
- [ ] Update `$username` to full database username
- [ ] Update `$password` to database password
- [ ] Update `BASE_URL` to your domain: `http://________.rf.gd`
- [ ] Update `GOOGLE_REDIRECT_URI` to your domain
- [ ] Update `FACEBOOK_REDIRECT_URI` to your domain
- [ ] Save file
- [ ] Upload updated `config.php` via FTP (overwrite)

### Set File Permissions
- [ ] Set `uploads/` folder to 755 or 777
- [ ] Set `sessions/` folder to 755 or 777 (if created)
- [ ] Set `php/` folder to 755
- [ ] Verify all PHP files are 644 or 755

### Update OAuth Settings (If Using)
- [ ] Update Google OAuth redirect URI in Google Cloud Console
- [ ] Update Facebook OAuth redirect URI in Facebook Developers
- [ ] Test OAuth callback URLs are accessible

---

## PHASE 5: TESTING

### Basic Tests
- [ ] Visit homepage: `http://yoursite.rf.gd`
- [ ] Homepage loads without errors
- [ ] CSS and images load correctly
- [ ] No 404 errors in browser console

### Database Connection Test
- [ ] No "Connection failed" errors
- [ ] Database connects successfully

### User Login Test
- [ ] Go to login page: `http://yoursite.rf.gd/login.php`
- [ ] Try logging in as user: `juan@email.com` / `password`
- [ ] Login successful
- [ ] Redirects to user dashboard
- [ ] Dashboard loads correctly
- [ ] Test logout

### Driver Login Test
- [ ] Go to login page
- [ ] Try logging in as driver: `pedro@driver.com` / `password`
- [ ] Login successful
- [ ] Redirects to driver dashboard
- [ ] Dashboard loads correctly
- [ ] Test logout

### Admin Login Test
- [ ] Go to login page
- [ ] Try logging in as admin: `admin@routa.com` / `admin123`
- [ ] Login successful
- [ ] Redirects to admin panel
- [ ] Admin panel loads correctly
- [ ] Can view bookings
- [ ] Can view statistics
- [ ] Test logout

### Feature Tests
- [ ] Test user registration (create new account)
- [ ] Test booking a ride
- [ ] Test driver accepting ride
- [ ] Test driver completing ride
- [ ] Test viewing ride history
- [ ] Test driver application form
- [ ] Test file upload (driver application)
- [ ] Test admin viewing applications

### Mobile Responsiveness
- [ ] Test on mobile browser
- [ ] All features work on mobile
- [ ] Layout is responsive

---

## PHASE 6: SECURITY & OPTIMIZATION

### Security Hardening
- [ ] Change default admin password
- [ ] Update test user passwords (or delete test accounts)
- [ ] Disable error display in `config.php`
- [ ] Create `.htaccess` file for security
- [ ] Remove/delete all test files from server
- [ ] Remove debug files from server

### SSL Setup (Highly Recommended)
- [ ] Go to cPanel → SSL/TLS
- [ ] Install free SSL certificate
- [ ] Wait for SSL activation (5-10 minutes)
- [ ] Update all URLs to use `https://`
- [ ] Test HTTPS access

### Performance Optimization
- [ ] Check page load times
- [ ] Optimize images if needed
- [ ] Enable browser caching (`.htaccess`)
- [ ] Test on different browsers

---

## PHASE 7: MONITORING & MAINTENANCE

### First Week Monitoring
- [ ] Check error logs daily (cPanel → Error Logs)
- [ ] Monitor bandwidth usage
- [ ] Monitor disk space usage
- [ ] Track any user-reported issues

### Documentation
- [ ] Update README.md with live URL
- [ ] Document any deployment issues encountered
- [ ] Create admin documentation
- [ ] Create user guide (optional)

### Backup Plan
- [ ] Schedule regular database backups
- [ ] Download backups to local computer
- [ ] Keep backup of all uploaded files
- [ ] Document backup procedure

---

## PHASE 8: POST-DEPLOYMENT

### Share Your Site
- [ ] Test final URL: `http://yoursite.rf.gd`
- [ ] Share with friends/testers
- [ ] Gather feedback
- [ ] Make improvements

### Future Upgrades
- [ ] Consider upgrading to paid hosting when traffic grows
- [ ] Consider custom domain name
- [ ] Plan for scaling
- [ ] Monitor for feature requests

---

## 📝 IMPORTANT INFORMATION TO KEEP

```
=== MY INFINITYFREE DETAILS ===

Domain: http://____________.rf.gd
FTP Host: ftpupload.net
FTP Username: epiz___________
FTP Password: _______________

MySQL Host: sql_____.infinityfreeapp.com
Database Name: epiz___________routadb
Database User: epiz___________routauser
Database Pass: _______________

cPanel URL: https://cpanel.infinityfreeapp.com

=== DEFAULT LOGIN CREDENTIALS ===

Admin:
  Email: admin@routa.com
  Password: admin123

Driver:
  Email: pedro@driver.com
  Password: password

User:
  Email: juan@email.com
  Password: password

=== OAUTH SETTINGS ===

Google Client ID: _____________________
Google Client Secret: _________________

Facebook App ID: _____________________
Facebook App Secret: __________________

=== API KEYS ===

Google Maps API Key: __________________

```

---

## ✅ DEPLOYMENT STATUS

**Started:** ___ / ___ / 202___

**Completed:** ___ / ___ / 202___

**Status:** [ ] In Progress  [ ] Testing  [ ] Live

**Issues Encountered:**
- 
- 
- 

**Notes:**
- 
- 
- 

---

## 🆘 TROUBLESHOOTING QUICK REFERENCE

| Issue | Solution Location |
|-------|------------------|
| Connection failed | Check config.php database settings |
| 500 Error | Check error logs in cPanel |
| Blank page | Check error logs, enable error display temporarily |
| Images not loading | Check file paths, verify upload |
| Can't upload files | Check uploads/ folder permissions (777) |
| Sessions not working | Create sessions/ folder with 777 permissions |
| OAuth not working | Update redirect URIs in OAuth providers |
| Database import failed | Check file size, split if > 50MB |

---

**🎉 Once everything is checked off, your Routa app is live!**

**Live URL:** http://____________.rf.gd

**Deployed by:** _______________

**Date:** ___ / ___ / 202___
