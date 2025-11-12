# OTP Expiration Fix

## Problem
OTP was expiring immediately due to timezone mismatch between PHP and MySQL.

## Solution Applied
Set both PHP and MySQL to use Philippine timezone (Asia/Manila, UTC+8) in `php/config.php`.

## Testing

### 1. Check Timezone Setup
Open: `http://localhost/Routa/php/check_timezone.php`

This will show:
- PHP current time
- MySQL current time
- Time difference
- Whether times are in sync

### 2. Test OTP Flow Again
Open: `http://localhost/Routa/test_otp.html`

1. Click "Send OTP" 
2. You should see the OTP code and expiry time
3. Immediately click "Verify OTP"
4. It should now work!

### 3. Expected Behavior
- OTP is valid for 5 minutes from creation
- Shows countdown timer in modal
- Can be used immediately after receiving
- Expires after 5 minutes

## What Was Fixed

1. **config.php** - Set timezone to Asia/Manila
2. **send_otp.php** - Use time() + 300 for expiration
3. **verify_otp.php** - Better time comparison
4. **Added debug info** - Shows creation and expiry times

## If Still Having Issues

If timezone check shows time difference > 60 seconds:
1. Restart Apache in XAMPP
2. Restart MySQL in XAMPP
3. Clear browser cache
4. Try again

The timezone is now set to Philippine time (UTC+8) which should match your local time!
