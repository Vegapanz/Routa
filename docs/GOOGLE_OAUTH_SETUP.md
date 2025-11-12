# Google OAuth Setup Guide for Routa

## 📋 Steps to Enable Google OAuth Login

### 1. Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click **"Select a project"** → **"New Project"**
3. Enter project name: **"Routa"**
4. Click **"Create"**

### 2. Enable Google+ API

1. In the left sidebar, click **"APIs & Services"** → **"Library"**
2. Search for **"Google+ API"** or **"Google People API"**
3. Click on it and press **"Enable"**

### 3. Configure OAuth Consent Screen

1. Go to **"APIs & Services"** → **"OAuth consent screen"**
2. Select **"External"** (for testing) and click **"Create"**
3. Fill in the required information:
   - **App name:** Routa
   - **User support email:** Your email
   - **Developer contact email:** Your email
4. Click **"Save and Continue"**
5. On **Scopes** page, click **"Add or Remove Scopes"**
   - Add: `./auth/userinfo.email`
   - Add: `./auth/userinfo.profile`
6. Click **"Save and Continue"**
7. Add test users (your email addresses for testing)
8. Click **"Save and Continue"** → **"Back to Dashboard"**

### 4. Create OAuth 2.0 Credentials

1. Go to **"APIs & Services"** → **"Credentials"**
2. Click **"+ Create Credentials"** → **"OAuth client ID"**
3. Select **"Web application"**
4. Configure:
   - **Name:** Routa Web Client
   - **Authorized JavaScript origins:**
     - `http://localhost`
   - **Authorized redirect URIs:**
     - `http://localhost/Routa/php/google-callback.php`
5. Click **"Create"**
6. **Save your credentials:**
   - Copy the **Client ID** (ends with `.apps.googleusercontent.com`)
   - Copy the **Client Secret**

### 5. Update Configuration Files

#### A. Update `php/config.php`

Replace these values:
```php
define('GOOGLE_CLIENT_ID', 'YOUR_ACTUAL_CLIENT_ID.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'YOUR_ACTUAL_CLIENT_SECRET');
```

#### B. Update `register.php`

Find this line (around line 333):
```javascript
const googleClientId = 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com';
```

Replace with your actual Client ID:
```javascript
const googleClientId = 'YOUR_ACTUAL_CLIENT_ID.apps.googleusercontent.com';
```

#### C. Update `login.php`

Find this line:
```javascript
const googleClientId = 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com';
```

Replace with your actual Client ID.

### 6. Update Database

Run the SQL file to add Google OAuth support:

```sql
-- In phpMyAdmin or MySQL command line:
-- Select routa_db database, then run:

ALTER TABLE users 
ADD COLUMN google_id VARCHAR(255) NULL UNIQUE AFTER phone,
ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER google_id;

CREATE INDEX idx_google_id ON users(google_id);
CREATE INDEX idx_email_verified ON users(email_verified);
```

Or import the file: `add_google_oauth.sql`

### 7. Test the Integration

1. Open `http://localhost/Routa/register.php`
2. Click **"Continue with Google"** button
3. You should be redirected to Google login
4. After authorizing, you'll be redirected back to Routa
5. Your account will be created automatically

### 8. For Production Deployment

When deploying to a live server:

1. Update authorized redirect URIs in Google Console:
   - Add: `https://yourdomain.com/php/google-callback.php`

2. Update `php/config.php`:
   ```php
   define('GOOGLE_REDIRECT_URI', 'https://yourdomain.com/php/google-callback.php');
   define('BASE_URL', 'https://yourdomain.com');
   ```

3. Update JavaScript in `register.php` and `login.php`:
   ```javascript
   const redirectUri = 'https://yourdomain.com/php/google-callback.php';
   ```

4. Publish your OAuth consent screen in Google Console

## 🔧 Troubleshooting

### Issue: "Redirect URI mismatch"
**Solution:** Make sure the redirect URI in Google Console exactly matches the one in your code (including http/https and trailing slashes)

### Issue: "This app isn't verified"
**Solution:** This is normal for development. Click "Advanced" → "Go to Routa (unsafe)" for testing.

### Issue: "Access blocked: This app's request is invalid"
**Solution:** Check that you've enabled the required APIs and added the correct scopes in OAuth consent screen.

### Issue: User info not received
**Solution:** Ensure Google+ API or Google People API is enabled in your project.

## 📝 How It Works

1. **User clicks "Continue with Google"**
   - JavaScript redirects to Google OAuth URL
   - Includes client ID, redirect URI, and requested scopes

2. **Google authenticates user**
   - User logs in to their Google account
   - User authorizes Routa to access email and profile

3. **Google redirects back to callback**
   - Includes authorization code in URL
   - `php/google-callback.php` receives the code

4. **Backend exchanges code for tokens**
   - Sends code to Google to get access token
   - Uses access token to fetch user info

5. **Create or login user**
   - If email exists: Log them in
   - If new user: Create account and log them in
   - Redirect to user dashboard

## 🎯 Features

- ✅ Automatic account creation
- ✅ Instant login for existing users
- ✅ Email verification (Google emails are pre-verified)
- ✅ Secure password-less authentication
- ✅ User profile data from Google

## 🚀 You're Ready!

Once configured, users can:
- Register with one click
- Login instantly with Google
- No need to remember passwords
- Faster and more secure authentication

---

**Need Help?** Check the [Google OAuth 2.0 Documentation](https://developers.google.com/identity/protocols/oauth2)
