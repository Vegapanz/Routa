# Quick Start - OTP Verification

## 🚀 Setup in 3 Steps

### Step 1: Update Database
Run this in phpMyAdmin:
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

### Step 2: Test It! 
Go to: `http://localhost/Routa/register.php`

1. Enter phone number: `09123456789`
2. Click **"Verify"** button
3. **An alert will show your OTP code** (for testing)
4. Enter the 6-digit code in the modal
5. Complete registration
6. See beautiful success modal! 🎉

### Step 3: For Production (Real SMS)
1. Sign up at https://semaphore.co/
2. Get API key
3. Edit `php/send_otp.php` line 48:
   ```php
   $apiKey = 'YOUR_ACTUAL_API_KEY';
   ```
4. Uncomment lines 55-75 (the curl code)
5. Remove `'debug_otp' => $otp` from line 82

## ✨ What's New

### Phone Verification
- ✅ Click "Verify" button next to phone field
- ✅ Receive 6-digit OTP via SMS
- ✅ Enter OTP in beautiful modal
- ✅ 5-minute expiration with countdown timer
- ✅ Resend option available
- ✅ Phone locked after verification

### Success Modal
- ✅ Beautiful animated checkmark
- ✅ Professional success message
- ✅ "Continue to Login" button
- ✅ NO MORE ALERTS! 🎊

## 📱 Development Mode

Currently configured for testing:
- OTP shown in alert popup
- OTP shown in console (F12)
- No SMS credits needed
- Perfect for development!

## 🎯 Ready to Test

Just run the SQL above and try registering!
The OTP will appear in an alert box for testing.

---

For detailed documentation, see `OTP_SETUP_GUIDE.md`
