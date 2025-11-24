# 🔧 Quick Troubleshooting Guide for InfinityFree

Common issues and their solutions when deploying Routa on InfinityFree.

---

## 🔴 DATABASE CONNECTION ERRORS

### Error: "Connection failed: SQLSTATE[HY000] [1045] Access denied"

**Cause:** Wrong database credentials

**Solution:**
1. Check your `php/config.php` file
2. Verify these details match EXACTLY from your cPanel:
   ```php
   $host = 'sqlXXX.infinityfreeapp.com';  // Check this!
   $dbname = 'epiz_XXXXXXXX_routadb';     // Include the full prefix!
   $username = 'epiz_XXXXXXXX_routauser'; // Include the full prefix!
   $password = 'YOUR_PASSWORD';            // The password you created
   ```
3. Common mistake: Forgetting the `epiz_XXXXXXXX_` prefix
4. Test in phpMyAdmin: Can you login with these credentials?

---

### Error: "Connection failed: SQLSTATE[HY000] [2002] php_network_getaddresses"

**Cause:** Wrong MySQL hostname

**Solution:**
1. Go to your InfinityFree cPanel
2. Find "MySQL Databases"
3. Look for "MySQL Hostname" (looks like: `sql123.infinityfreeapp.com`)
4. Copy EXACTLY and update `$host` in config.php
5. DO NOT use "localhost" - it won't work on InfinityFree!

---

### Error: "Unknown database 'routa_db'"

**Cause:** Database name doesn't include the prefix

**Solution:**
1. Your database name on InfinityFree is: `epiz_XXXXXXXX_routadb`
2. NOT just `routadb` or `routa_db`
3. Update `$dbname` in config.php with the FULL name

---

## 🔴 500 INTERNAL SERVER ERROR

### When visiting any page

**Cause 1:** PHP syntax error

**Solution:**
1. Go to cPanel → Error Logs
2. Read the latest error
3. Fix the syntax error in the mentioned file
4. Re-upload the fixed file

**Cause 2:** Wrong file permissions

**Solution:**
1. In FileZilla, check file permissions
2. Folders should be: 755
3. PHP files should be: 644 or 755
4. Fix any files showing 777 or 666

**Cause 3:** Missing .htaccess rules

**Solution:**
1. Check if you have a `.htaccess` file with complex rules
2. Try temporarily renaming it to `.htaccess.bak`
3. If site works, the .htaccess had an issue
4. Simplify or remove problematic rules

---

## 🔴 BLANK WHITE PAGE

### Page loads but shows nothing

**Cause:** PHP error not displayed

**Solution:**
1. Check cPanel → Error Logs (most important!)
2. Temporarily enable error display:
   
   Edit `php/config.php`, find this line:
   ```php
   ini_set('display_errors', 0);
   ```
   Change to:
   ```php
   ini_set('display_errors', 1);
   ```
   
3. Reload page - you'll see the error
4. Fix the error
5. Change back to `0` for production

---

## 🔴 IMAGES NOT LOADING

### CSS works but images show broken icons

**Cause 1:** File path issues

**Solution:**
1. Check your HTML/PHP uses relative paths:
   ```php
   ✅ GOOD: <img src="assets/images/logo.png">
   ❌ BAD:  <img src="/Routa/assets/images/logo.png">
   ❌ BAD:  <img src="C:/xampp/htdocs/Routa/assets/images/logo.png">
   ```

**Cause 2:** Files didn't upload

**Solution:**
1. In FileZilla, navigate to `htdocs/assets/images/`
2. Check if image files are there
3. If missing, re-upload the `assets/` folder

**Cause 3:** Case sensitivity

**Solution:**
1. Linux servers (like InfinityFree) are case-sensitive
2. If file is `logo.PNG` but you reference `logo.png`, it won't work
3. Rename files to lowercase or update references

---

## 🔴 CSS NOT LOADING

### Page loads but looks unstyled

**Cause 1:** Wrong CSS path

**Solution:**
1. Right-click page → View Source
2. Find the CSS `<link>` tags
3. Click the CSS link - does it load?
4. If 404 error, fix the path in your HTML/PHP

**Cause 2:** BASE_URL is wrong

**Solution:**
1. In `php/config.php`, check:
   ```php
   define('BASE_URL', 'http://yoursite.rf.gd');  // No trailing slash!
   ```
2. Make sure it matches your actual domain
3. No `/Routa` at the end!

---

## 🔴 LOGIN ISSUES

### "Invalid credentials" but password is correct

**Cause:** Database not imported or wrong database

**Solution:**
1. Go to phpMyAdmin in cPanel
2. Select your database (epiz_XXXXXXXX_routadb)
3. Check if `users` table exists
4. Click on `users` table
5. Check if test accounts exist:
   - `juan@email.com`
   - `pedro@driver.com`
   - `admin@routa.com`
6. If table is empty, re-import `database/routa_database.sql`

---

### Login works but immediately logs out

**Cause:** Session issues

**Solution:**
1. Edit `php/config.php`, add at the top (after `<?php`):
   ```php
   // Fix sessions for InfinityFree
   ini_set('session.save_path', getcwd() . '/../sessions');
   if (!is_dir('../sessions')) {
       @mkdir('../sessions', 0777);
   }
   ```

2. Via FileZilla, create a `sessions` folder in `htdocs/`
3. Set folder permissions to 777
4. Try logging in again

---

### "Headers already sent" error

**Cause:** Whitespace before `<?php` or after `?>`

**Solution:**
1. Open the file mentioned in the error
2. Make sure there's NO space or blank lines before `<?php`
3. Remove any `?>` at the end of PHP files (not needed)
4. Save with UTF-8 encoding (no BOM)
5. Re-upload

---

## 🔴 FILE UPLOAD ERRORS

### "Failed to upload file" or "Permission denied"

**Cause:** uploads folder permissions

**Solution:**
1. Via FileZilla, right-click `htdocs/uploads` folder
2. File Permissions → Set to 777
3. Check "Recurse into subdirectories"
4. Apply

---

### Upload works locally but not on InfinityFree

**Cause:** File size or type restrictions

**Solution:**
1. InfinityFree has 10MB upload limit
2. Check your file is under 10MB
3. Allowed types on InfinityFree: jpg, png, gif, pdf, txt
4. Update your validation to match InfinityFree limits

---

## 🔴 OAUTH ERRORS

### Google OAuth: "redirect_uri_mismatch"

**Solution:**
1. Go to: https://console.cloud.google.com
2. Select your project
3. Credentials → Edit OAuth 2.0 Client
4. Add BOTH of these to "Authorized redirect URIs":
   - `http://yoursite.rf.gd/php/google-callback.php`
   - `https://yoursite.rf.gd/php/google-callback.php`
5. Save and wait 5 minutes
6. Update `php/config.php`:
   ```php
   define('GOOGLE_REDIRECT_URI', 'http://yoursite.rf.gd/php/google-callback.php');
   ```

---

### Facebook OAuth: "URL Blocked"

**Solution:**
1. Go to: https://developers.facebook.com
2. Select your app
3. Settings → Basic
4. Update "App Domains": `yoursite.rf.gd`
5. Facebook Login → Settings
6. Add to "Valid OAuth Redirect URIs":
   - `http://yoursite.rf.gd/php/facebook-callback.php`
   - `https://yoursite.rf.gd/php/facebook-callback.php`
7. Save

---

## 🔴 GOOGLE MAPS NOT WORKING

### Map shows "For development purposes only" watermark

**Cause:** API key restrictions

**Solution:**
1. Go to: https://console.cloud.google.com
2. APIs & Services → Credentials
3. Click your API key
4. Under "Application restrictions":
   - Choose "HTTP referrers"
   - Add: `yoursite.rf.gd/*`
   - Add: `*.rf.gd/*` (if using subdomain)
5. Save

---

### Map doesn't load at all

**Cause:** Missing or invalid API key

**Solution:**
1. Check if you have Google Maps API key
2. Check if it's properly defined in your JavaScript:
   ```javascript
   // In your HTML/PHP file
   <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY"></script>
   ```
3. Make sure the API key is active and has Maps JavaScript API enabled

---

## 🔴 EMAIL NOT SENDING

### OTP or notification emails not sending

**Cause:** InfinityFree has limited email functionality

**Solution:**
1. InfinityFree's mail() function is unreliable
2. Use external SMTP instead:
   - Gmail SMTP
   - SendGrid (free tier)
   - Mailgun
   - Amazon SES

3. Install PHPMailer:
   - Not available? Upgrade to paid hosting
   - Or use external email API (SendGrid API, etc.)

---

## 🔴 PERFORMANCE ISSUES

### Site is very slow

**Cause 1:** Unoptimized database queries

**Solution:**
1. Check if you're using proper indexes
2. Avoid `SELECT *` - select only needed columns
3. Use LIMIT in queries
4. Cache results when possible

**Cause 2:** Too many external resources

**Solution:**
1. Minimize API calls
2. Combine CSS/JS files
3. Optimize and compress images
4. Use lazy loading for images

**Cause 3:** InfinityFree limitations

**Solution:**
1. Free hosting has CPU time limits
2. During high traffic, site may slow down
3. Consider upgrading to paid hosting
4. Or use caching strategies

---

## 🔴 CRON JOBS NOT WORKING

### Scheduled tasks not running

**Cause:** Free hosting doesn't support cron jobs

**Solution:**
1. InfinityFree free plan doesn't have cron jobs
2. Alternatives:
   - Use external cron services (cron-job.org, easycron.com)
   - Upgrade to paid hosting
   - Trigger tasks on user visits (not ideal)

---

## 🔴 DATABASE SIZE LIMITS

### "Database quota exceeded"

**Cause:** Free plan has 400MB database limit

**Solution:**
1. Clean up old data
2. Delete unnecessary logs
3. Archive old records
4. Optimize tables (phpMyAdmin → Optimize)
5. Consider upgrading to paid plan

---

## 🔴 BANDWIDTH EXCEEDED

### "Account suspended: Daily hit limit reached"

**Cause:** Exceeded 50,000 hits/day limit

**Solution:**
1. Wait until next day (resets automatically)
2. Optimize to reduce hits:
   - Enable browser caching
   - Combine files
   - Use CDN for assets
3. Consider upgrading to paid plan
4. Block bots if being scraped

---

## 📞 STILL HAVING ISSUES?

### Where to get help:

1. **Check Error Logs First!**
   - cPanel → Error Logs
   - This shows the exact error

2. **InfinityFree Forum**
   - https://forum.infinityfree.net
   - Very active community
   - Search before posting

3. **InfinityFree Knowledge Base**
   - https://infinityfree.net/support
   - Common issues documented

4. **Your Config File**
   - Double-check EVERY setting
   - Most issues are wrong credentials

5. **Test Locally First**
   - Make sure it works on XAMPP
   - Then upload to InfinityFree

---

## ✅ PREVENTION TIPS

**Before uploading:**
- ✅ Test everything locally
- ✅ Check all file paths are relative
- ✅ Verify no hardcoded localhost URLs
- ✅ Back up your database
- ✅ Document your credentials

**After uploading:**
- ✅ Check error logs immediately
- ✅ Test all features one by one
- ✅ Monitor for issues first 24 hours
- ✅ Keep local copy as backup

---

## 🔍 DEBUGGING CHECKLIST

When something breaks:

1. [ ] Check cPanel Error Logs
2. [ ] Enable display_errors temporarily
3. [ ] Check browser Console (F12)
4. [ ] Check Network tab for failed requests
5. [ ] Verify file permissions
6. [ ] Confirm database connection works
7. [ ] Test in phpMyAdmin
8. [ ] Check if file uploaded correctly
9. [ ] Verify config.php settings
10. [ ] Compare with local version that works

---

**Remember:** 90% of issues are either:
- Wrong database credentials
- Wrong file paths
- Wrong permissions
- Forgot to update config.php

**Always check these four things first!** 🎯
