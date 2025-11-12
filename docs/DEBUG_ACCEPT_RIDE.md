# Debugging Accept Ride Issue

## Steps to Debug:

### 1. Check PHP Error Logs

The error logs are located at:
- **XAMPP**: `d:\xampp\apache\logs\error.log`
- **PHP Errors**: `d:\xampp\php\logs\php_error_log`

Open these files to see any PHP errors when clicking Accept.

### 2. Use the Test Page

1. Login as a driver: http://localhost/Routa/login.php
   - Username: `pedro@driver.com`
   - Password: `password123`

2. Open test page: http://localhost/Routa/test_accept_ride.php

This will show:
- Your session info
- Pending rides
- Driver status
- Test buttons with detailed error messages

### 3. Check Browser Console

1. Open driver dashboard: http://localhost/Routa/driver_dashboard.php
2. Press F12 to open Developer Tools
3. Go to Console tab
4. Click "Accept Ride" button
5. Look for console.log messages showing:
   - `acceptRide called with bookingId: X`
   - `Sending accept request...`
   - `Response status: X`
   - `Response data: {...}`

### 4. Common Issues & Fixes

#### Issue 1: Session Not Set
**Symptom**: API returns "Driver authentication required"
**Fix**: Make sure you're logged in as a driver (not regular user)

#### Issue 2: Ride Not Found
**Symptom**: API returns "Ride not found or already accepted"
**Fix**: Check if ride status is actually 'driver_found'

Run this SQL:
```sql
SELECT id, driver_id, status, pickup_location 
FROM ride_history 
WHERE status = 'driver_found' 
ORDER BY created_at DESC 
LIMIT 5;
```

#### Issue 3: Driver ID Mismatch
**Symptom**: API returns "Ride not found"
**Fix**: Verify the driver_id in ride_history matches your session user_id

Run this SQL (replace X with your driver ID):
```sql
SELECT * FROM ride_history WHERE driver_id = X AND status = 'driver_found';
```

#### Issue 4: JavaScript Not Loading
**Symptom**: Nothing happens when clicking button
**Fix**: Check if JavaScript file is loaded correctly

In browser console, type:
```javascript
typeof DriverDashboard
```
Should return: "object"

#### Issue 5: PHP Errors
**Symptom**: Network error or blank response
**Fix**: Check error logs (see step 1)

### 5. Manual Test via SQL

If Accept button doesn't work, you can manually accept a ride:

```sql
-- Find a pending ride
SELECT id, driver_id, user_id, status FROM ride_history WHERE status = 'driver_found' LIMIT 1;

-- Accept it manually (replace X with ride id)
UPDATE ride_history SET status = 'confirmed', driver_arrival_time = NOW() WHERE id = X;

-- Update driver status (replace Y with driver id)
UPDATE tricycle_drivers SET status = 'on_trip' WHERE id = Y;
```

Then refresh the dashboard to see if it moves to "Active Rides".

### 6. Verify Database Setup

Make sure you've run these SQL files:
1. `upgrade_booking_system.sql` - Creates necessary tables and columns
2. `set_driver_locations.sql` - Sets driver GPS coordinates

Check if tables exist:
```sql
SHOW TABLES LIKE 'driver_locations';
SHOW TABLES LIKE 'ride_notifications';

-- Check ride_history structure
DESCRIBE ride_history;
```

### 7. Network Tab Inspection

1. Open Developer Tools (F12)
2. Go to "Network" tab
3. Click "Accept Ride"
4. Click on the "driver_api.php" request
5. Check:
   - **Request Headers**: Should show Content-Type: application/json
   - **Request Payload**: Should show `{"ride_id":X}`
   - **Response**: Should show JSON with success/failure

### 8. Quick Fix Commands

If nothing works, try these in PowerShell:

```powershell
# Restart XAMPP Apache
d:\xampp\apache\bin\httpd.exe -k restart

# Clear PHP opcache (if enabled)
# Or just restart Apache

# Check if files are being served correctly
Invoke-WebRequest -Uri "http://localhost/Routa/php/driver_api.php" -Method GET
```

### 9. Check File Permissions

Make sure these files are readable:
- `d:\xampp\htdocs\Routa\php\driver_api.php`
- `d:\xampp\htdocs\Routa\assets\js\pages\driver-dashboard.js`

### 10. Expected Behavior

When you click "Accept Ride":
1. Browser shows confirmation dialog
2. Console logs: "acceptRide called with bookingId: X"
3. Console logs: "Sending accept request..."
4. Console logs: "Response status: 200"
5. Console logs: "Response data: {success: true, ...}"
6. Green notification appears: "Ride accepted! Navigate to pickup location."
7. Page reloads after 1.5 seconds
8. Ride moves from "New Ride Requests" to "Active Rides"
9. Rider sees status change to "confirmed"

## Let Me Know:

After checking these, please tell me:
1. What you see in the browser console (F12 → Console)
2. What you see on the test page (test_accept_ride.php)
3. Any errors in PHP error logs
4. The exact error message if any

This will help me identify the exact issue!
