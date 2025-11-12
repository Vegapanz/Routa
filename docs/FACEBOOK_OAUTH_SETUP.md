# Facebook OAuth Setup Guide for Routa

## 📋 Steps to Enable Facebook OAuth Login

### 1. Create Facebook App

1. Go to [Facebook for Developers](https://developers.facebook.com/)
2. Click **"My Apps"** in the top right corner
3. Click **"Create App"**
4. Select **"Consumer"** as the app type
5. Click **"Next"**
6. Fill in the app details:
   - **App Name:** Routa
   - **App Contact Email:** Your email
   - **Business Account:** (Optional, can skip)
7. Click **"Create App"**

### 2. Add Facebook Login Product

1. In your app dashboard, find **"Add Products to Your App"** section
2. Locate **"Facebook Login"** and click **"Set Up"**
3. Select **"Web"** as the platform
4. Enter your site URL:
   - For local development: `http://localhost/Routa/`
5. Click **"Save"** and **"Continue"**

### 3. Configure Facebook Login Settings

1. In the left sidebar, go to **"Facebook Login"** → **"Settings"**
2. Under **"Valid OAuth Redirect URIs"**, add:
   ```
   http://localhost/Routa/php/facebook-callback.php
   ```
3. Make sure **"Client OAuth Login"** is **enabled**
4. Make sure **"Web OAuth Login"** is **enabled**
5. Set **"Valid OAuth Redirect URIs"** (same as above)
6. Click **"Save Changes"**

### 4. Get Your App Credentials

1. In the left sidebar, go to **"Settings"** → **"Basic"**
2. You'll see:
   - **App ID** (e.g., 1234567890123456)
   - **App Secret** (click **"Show"** to reveal)
3. **Copy both values** - you'll need them in the next step

### 5. Configure App Settings

1. Still in **"Settings"** → **"Basic"**:
   - **App Domains:** Add `localhost`
   - **Privacy Policy URL:** Add your privacy policy URL or use a placeholder
   - **Terms of Service URL:** (Optional)
   - **Category:** Select "Travel & Transportation"

2. Scroll down to **"Add Platform"**:
   - Click **"Add Platform"**
   - Select **"Website"**
   - **Site URL:** `http://localhost/Routa/`
   - Click **"Save Changes"**

### 6. Make Your App Live

⚠️ **Important:** Your app starts in Development Mode

1. At the top of the page, you'll see a toggle that says **"In Development"**
2. For testing, you can keep it in Development Mode
3. Add test users:
   - Go to **"Roles"** → **"Test Users"**
   - Click **"Add"** to create test users
   - Or go to **"Roles"** → **"Roles"** and add your Facebook account as a test user

4. To make it live for everyone:
   - Complete all required fields in App Review
   - Toggle the switch to make the app **Live**

### 7. Update Configuration Files

#### A. Update `php/config.php`

Replace these values with your actual credentials:
```php
define('FACEBOOK_APP_ID', 'YOUR_ACTUAL_APP_ID');
define('FACEBOOK_APP_SECRET', 'YOUR_ACTUAL_APP_SECRET');
```

Example:
```php
define('FACEBOOK_APP_ID', '1234567890123456');
define('FACEBOOK_APP_SECRET', 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6');
```

#### B. Update `register.php`

Find this line (around line 427):
```javascript
const facebookAppId = 'YOUR_FACEBOOK_APP_ID';
```

Replace with your actual App ID:
```javascript
const facebookAppId = '1234567890123456';
```

#### C. Update `login.php`

Find this line (around line 135):
```javascript
const facebookAppId = 'YOUR_FACEBOOK_APP_ID';
```

Replace with your actual App ID:
```javascript
const facebookAppId = '1234567890123456';
```

### 8. Update Database

Run the SQL file to add Facebook OAuth support:

**Option 1: Using phpMyAdmin**
1. Open phpMyAdmin at `http://localhost/phpmyadmin`
2. Select `routa_db` database
3. Click on **"SQL"** tab
4. Copy and paste the contents of `add_facebook_oauth.sql`
5. Click **"Go"**

**Option 2: Direct SQL**
```sql
USE routa_db;

ALTER TABLE users 
ADD COLUMN facebook_id VARCHAR(255) NULL UNIQUE AFTER google_id;

CREATE INDEX idx_facebook_id ON users(facebook_id);
```

### 9. Request Email Permission (Optional)

By default, Facebook provides email, but some users may not have verified emails:

1. Go to **"App Review"** → **"Permissions and Features"**
2. Find **"email"** permission
3. It should already be approved by default
4. If you need additional permissions, request them here

### 10. Test the Integration

1. Open `http://localhost/Routa/register.php`
2. Click **"Continue with Facebook"** button
3. You should be redirected to Facebook login
4. Log in with your Facebook account (or test user)
5. Authorize the app to access your email and public profile
6. You'll be redirected back to Routa
7. Your account will be created automatically
8. You'll be logged in and redirected to the dashboard

### 11. For Production Deployment

When deploying to a live server:

1. **Update Facebook App Settings:**
   - Go to **"Settings"** → **"Basic"**
   - **App Domains:** Add your domain (e.g., `yourdomain.com`)
   - **Site URL:** Change to `https://yourdomain.com`
   
2. **Update OAuth Redirect URIs:**
   - Go to **"Facebook Login"** → **"Settings"**
   - Add: `https://yourdomain.com/php/facebook-callback.php`
   - Keep the localhost URL for testing

3. **Update Configuration Files:**
   
   `php/config.php`:
   ```php
   define('FACEBOOK_REDIRECT_URI', 'https://yourdomain.com/php/facebook-callback.php');
   define('BASE_URL', 'https://yourdomain.com');
   ```

   `register.php` and `login.php`:
   ```javascript
   const redirectUri = 'https://yourdomain.com/php/facebook-callback.php';
   ```

4. **Make App Live:**
   - Complete App Review if needed
   - Switch from Development to Live mode

## 🔧 Troubleshooting

### Issue: "URL Blocked: This redirect failed because the redirect URI is not whitelisted"
**Solution:** Make sure you've added the exact redirect URI to the **Valid OAuth Redirect URIs** in Facebook Login Settings. Check for typos and trailing slashes.

### Issue: "App Not Setup: This app is still in development mode"
**Solution:** 
- Keep the app in Development mode for testing
- Add your Facebook account as a test user in **Roles** → **"Roles"**
- Or make the app Live after completing all requirements

### Issue: "Can't Load URL: The domain of this URL isn't included in the app's domains"
**Solution:** Add `localhost` to the **App Domains** in **Settings** → **Basic**

### Issue: User email is not received
**Solution:** 
- Some Facebook users don't have verified emails
- The app will create a placeholder email: `{facebook_id}@facebook.user`
- User can still log in with Facebook

### Issue: "Given URL is not allowed by the Application configuration"
**Solution:** 
- Check that your **Site URL** matches your redirect URI domain
- Ensure **Valid OAuth Redirect URIs** exactly matches your callback URL
- Protocol must match (http vs https)

## 📝 How It Works

1. **User clicks "Continue with Facebook"**
   - JavaScript redirects to Facebook OAuth URL
   - Includes App ID, redirect URI, and requested permissions (email, public_profile)

2. **Facebook authenticates user**
   - User logs in to their Facebook account
   - User authorizes Routa to access email and public profile

3. **Facebook redirects back to callback**
   - Includes authorization code in URL
   - `php/facebook-callback.php` receives the code

4. **Backend exchanges code for access token**
   - Sends code to Facebook Graph API to get access token
   - Uses access token to fetch user info from `/me` endpoint

5. **Create or login user**
   - If user exists with Facebook ID: Log them in
   - If user exists with same email: Link Facebook account
   - If new user: Create account with Facebook data and log them in
   - Redirect to user dashboard

## 🎯 Features

- ✅ Automatic account creation
- ✅ Instant login for existing users
- ✅ Links Facebook to existing email accounts
- ✅ Email verification (Facebook emails are pre-verified)
- ✅ Secure password-less authentication
- ✅ User profile data from Facebook (name, email, picture)

## 🔒 Privacy & Data

Facebook OAuth requests:
- **email:** User's email address
- **public_profile:** Name, profile picture, age range, gender

Data is stored in your database:
- `facebook_id`: Unique Facebook user ID
- `email`: User's email
- `name`: User's full name
- `email_verified`: Set to 1 (Facebook emails are verified)

Users can:
- Remove app access from Facebook settings
- Still login with email/password if they registered traditionally

## 🚀 Testing Tips

**Development Mode Testing:**
1. Add yourself as an admin/developer in **Roles**
2. Create test users for others to test
3. Test users can log in without app being Live

**Common Test Scenarios:**
- ✅ New user registration via Facebook
- ✅ Existing user login via Facebook
- ✅ User with same email linking Facebook account
- ✅ User without email (creates placeholder)
- ✅ Logging in on different devices

## 📱 Mobile App Support

If you build a mobile app later:
1. Add **iOS** or **Android** platform in Facebook App settings
2. Configure Bundle ID (iOS) or Package Name (Android)
3. Add Key Hashes for Android
4. Update OAuth redirect URIs for mobile

## 🌐 Multiple Environments

For development, staging, and production:

1. **Option A: Multiple Apps**
   - Create separate Facebook apps for each environment
   - Use different App IDs and Secrets

2. **Option B: Single App**
   - Add multiple redirect URIs in Facebook Login Settings
   - Use environment variables in your code

## 🎓 Best Practices

1. **Never commit secrets to Git:**
   - Add `config.php` to `.gitignore`
   - Use environment variables for production

2. **Handle edge cases:**
   - User declines email permission
   - User cancels Facebook login
   - User removes app access later

3. **Privacy compliance:**
   - Have a privacy policy
   - Explain what data you collect
   - Allow users to delete their data

4. **Security:**
   - Always use HTTPS in production
   - Validate tokens server-side
   - Never expose App Secret in frontend code

## 🔗 Useful Links

- [Facebook for Developers](https://developers.facebook.com/)
- [Facebook Login Documentation](https://developers.facebook.com/docs/facebook-login/web)
- [Graph API Explorer](https://developers.facebook.com/tools/explorer/)
- [Facebook App Dashboard](https://developers.facebook.com/apps/)

---

**Need Help?** Check the [Facebook Login Guide](https://developers.facebook.com/docs/facebook-login/manually-build-a-login-flow) for detailed documentation.

## ✅ You're Ready!

Once configured, users can:
- Register with one click using Facebook
- Login instantly without passwords
- Link Facebook to existing accounts
- Faster and more secure authentication

🎉 **Facebook OAuth is now integrated with Routa!**
