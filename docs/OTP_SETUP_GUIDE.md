# OTP Verification & Success Modal Setup Guide

## Features Added

### 1. SMS OTP Verification ✅
- Users must verify their phone number before registering
- 6-digit OTP code sent via SMS
- 5-minute expiration time
- Resend OTP functionality
- Real-time countdown timer

### 2. Beautiful Success Modal ✅
- Animated checkmark on successful registration
- Professional modal instead of alert
- Smooth animations
- "Continue to Login" button

## Database Setup

### Step 1: Run the SQL Script
Execute `add_otp_verification.sql` in phpMyAdmin or MySQL:

```sql
-- This creates:
-- 1. otp_verifications table
-- 2. phone_verified column in users table
```

Or manually run these commands:
```sql
USE routa_db;

CREATE TABLE IF NOT EXISTS otp_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(25) NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_otp_code (otp_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE users 
ADD COLUMN phone_verified TINYINT(1) DEFAULT 0 AFTER phone;
```

## SMS Gateway Setup (Semaphore)

### Option A: For Production (Real SMS)

1. **Sign up for Semaphore**
   - Go to https://semaphore.co/
   - Create an account
   - Load credits (approximately ₱0.50 per SMS)

2. **Get API Key**
   - Go to API Settings
   - Copy your API Key

3. **Update `php/send_otp.php`**
   ```php
   // Replace this line:
   $apiKey = 'YOUR_SEMAPHORE_API_KEY';
   
   // With your actual API key:
   $apiKey = 'your-actual-api-key-here';
   ```

4. **Uncomment the SMS sending code**
   In `php/send_otp.php`, uncomment this block:
   ```php
   // UNCOMMENT FROM LINE ~55 to ~75
   $message = "Your Routa verification code is: $otp. Valid for 5 minutes.";
   
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, 'https://api.semaphore.co/api/v4/messages');
   // ... rest of the curl code
   ```

5. **Remove debug output**
   Comment out or remove:
   ```php
   'debug_otp' => $otp, // REMOVE THIS IN PRODUCTION!
   ```

### Option B: For Development/Testing (No Real SMS)

The system is already configured for testing!
- OTP codes are displayed in:
  - Console log (F12 → Console)
  - Alert popup
- No SMS credits needed
- Perfect for development

## How It Works

### User Flow

1. **User enters phone number**
   - Format: +63 912 345 6789 or 09123456789
   - Must be valid Philippine mobile number

2. **Click "Verify" button**
   - OTP sent to phone via SMS
   - Modal opens for OTP entry
   - 5-minute countdown timer starts

3. **Enter 6-digit OTP**
   - Type or paste the code
   - Auto-focuses next input
   - Can resend if not received

4. **Verify OTP**
   - System validates the code
   - Phone marked as verified
   - Phone input is disabled (locked)
   - Green checkmark appears

5. **Complete registration**
   - Fill remaining fields
   - Submit form
   - Success modal appears with animation

6. **Success modal**
   - Animated checkmark
   - "Continue to Login" button
   - Redirects to login page

### Technical Flow

```
User enters phone
    ↓
Click "Verify"
    ↓
php/send_otp.php
    - Validates phone format
    - Generates 6-digit OTP
    - Stores in database with expiry
    - Sends SMS via Semaphore
    - Returns success
    ↓
OTP Modal opens
    ↓
User enters OTP
    ↓
Click "Verify Code"
    ↓
php/verify_otp.php
    - Checks OTP in database
    - Validates not expired
    - Marks as verified
    - Stores in session
    ↓
Phone verified ✅
    ↓
User completes form
    ↓
Submit registration
    ↓
php/register.php
    - Checks phone verification in session
    - Validates all fields
    - Creates user account
    - Sets phone_verified = 1
    ↓
Success Modal 🎉
```

## Files Created/Modified

### New Files
1. **add_otp_verification.sql** - Database schema
2. **php/send_otp.php** - Sends OTP via SMS
3. **php/verify_otp.php** - Verifies OTP code

### Modified Files
1. **register.php**
   - Added OTP modal UI
   - Added success modal UI
   - Added verify button next to phone input
   - Added custom styles for modals

2. **assets/js/pages/register.js**
   - OTP sending logic
   - OTP verification logic
   - Modal handling
   - Timer countdown
   - Success modal display
   - Form validation with phone verification check

3. **php/register.php**
   - Check for phone verification in session
   - Store phone_verified flag in database

## UI Components

### OTP Modal
- Clean, centered modal
- 6 input boxes for OTP digits
- Auto-focus and auto-advance
- Paste support (paste 6-digit code)
- Error display
- Countdown timer
- Resend button

### Success Modal
- Animated green checkmark
- Professional success message
- Fade-in animation
- "Continue to Login" button
- Auto-redirects on button click

## Testing

### Test the OTP Flow

1. **Start XAMPP** (Apache + MySQL)

2. **Navigate to registration page**
   ```
   http://localhost/Routa/register.php
   ```

3. **Fill in the form**
   - Full Name: Test User
   - Email: test@example.com
   - Phone: 09123456789

4. **Click "Verify" button**
   - In development mode, an alert will show the OTP
   - Check console (F12) for the OTP code
   - Modal will open

5. **Enter the OTP**
   - Type the 6-digit code shown in alert/console
   - Or paste it
   - Click "Verify Code"

6. **Complete registration**
   - Phone field should now be locked with green checkmark
   - Fill in password fields
   - Check terms checkbox
   - Click "Create Account"

7. **Success!**
   - Beautiful modal appears with animation
   - Click "Continue to Login"
   - Redirects to login page

## Security Features

✅ **OTP Expiration** - 5-minute validity
✅ **One-time use** - OTP marked as used after verification
✅ **Session-based** - Verification stored in session
✅ **Server-side validation** - Cannot bypass on client side
✅ **Phone format validation** - Only Philippine numbers
✅ **Rate limiting ready** - Can add IP-based limits
✅ **Secure storage** - OTP stored in database, not exposed

## Customization

### Change OTP Length
In `php/send_otp.php`:
```php
// Change from 6 to 4 digits:
$otp = sprintf("%04d", mt_rand(0, 9999));

// Also update the input fields in register.php
```

### Change Expiry Time
In `php/send_otp.php`:
```php
// Change from 5 to 10 minutes:
$expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// Also update timer in register.js:
otpExpiryTime = Date.now() + (10 * 60 * 1000);
```

### Customize SMS Message
In `php/send_otp.php`:
```php
$message = "Your Routa verification code is: $otp. Valid for 5 minutes.";

// Change to:
$message = "Welcome to Routa! Your code is $otp. Don't share this code.";
```

### Style the Modals
Edit the `<style>` section in `register.php`:
- Change colors
- Adjust animations
- Modify sizes
- Update fonts

## Troubleshooting

### OTP not sent
- Check Semaphore API key
- Verify credits available
- Check phone format
- Check database connection
- Look at PHP error logs

### OTP expired immediately
- Check server timezone
- Verify database timezone
- Check system time

### Modal not showing
- Check browser console for errors
- Verify Bootstrap JS is loaded
- Clear browser cache
- Check modal initialization

### Phone already verified but can't register
- Clear session: `session_destroy()`
- Check database for existing verification
- Verify session is started in all PHP files

## Cost Estimate (Production)

### Semaphore SMS Pricing
- ₱0.50 per SMS
- 1000 users = ₱500
- 10,000 users = ₱5,000

### Tips to Save Costs
1. Only send OTP on valid phone numbers
2. Implement rate limiting (max 3 OTPs per hour)
3. Cache valid numbers to reduce resends
4. Use email verification as alternative

## Alternative SMS Providers

### Other Philippine SMS Gateways
1. **Semaphore** (Recommended)
   - https://semaphore.co/
   - ₱0.50/SMS
   - Easy API

2. **Twilio**
   - https://www.twilio.com/
   - $0.02/SMS (~₱1.00)
   - International support

3. **Vonage (Nexmo)**
   - https://www.vonage.com/
   - Similar to Twilio

4. **M360**
   - https://m360.com.ph/
   - Local provider

## Production Checklist

Before going live:

- [ ] Sign up for SMS gateway
- [ ] Add API key to send_otp.php
- [ ] Uncomment SMS sending code
- [ ] Remove debug_otp from response
- [ ] Remove console.log statements
- [ ] Remove alert() for OTP display
- [ ] Test with real phone number
- [ ] Add rate limiting
- [ ] Set up error logging
- [ ] Configure backup SMS provider
- [ ] Test on mobile devices
- [ ] Monitor SMS delivery rates

## Support

For issues or questions:
1. Check browser console for errors
2. Check PHP error logs
3. Verify database structure
4. Test with development mode first
5. Check Semaphore dashboard for SMS status

Enjoy your new OTP verification system! 🎉📱
