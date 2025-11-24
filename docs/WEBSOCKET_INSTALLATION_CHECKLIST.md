# ✅ WebSocket Installation Checklist

Use this checklist to ensure proper installation and configuration.

---

## 📋 Pre-Installation Requirements

- [ ] PHP 7.4 or higher installed
- [ ] Composer installed
- [ ] MySQL 5.7 or higher running
- [ ] XAMPP/Apache running (for development)
- [ ] Modern browser (Chrome, Firefox, Safari, Edge)

**Verify:**
```bash
php -v        # Should show PHP 7.4+
composer -v   # Should show Composer version
mysql -V      # Should show MySQL version
```

---

## 🚀 Installation Steps

### Step 1: Install Dependencies ✓
- [ ] Navigate to `websocket` directory
- [ ] Run setup script or manual install:

**Windows:**
```bash
cd d:\xampp\htdocs\Routa\websocket
setup.bat
```

**Linux/Mac:**
```bash
cd /path/to/Routa/websocket
chmod +x setup.sh
./setup.sh
```

**Manual:**
```bash
cd websocket
composer install
```

**Verify:**
- [ ] `vendor/` directory created
- [ ] `vendor/workerman/` exists
- [ ] No error messages

---

### Step 2: Database Setup ✓
- [ ] Import WebSocket tables:

```bash
mysql -u root -p routa < database/websocket_tables.sql
```

**Verify in MySQL:**
```sql
USE routa;
SHOW TABLES LIKE 'ws_%';
```

**Expected Output:**
- [ ] `ws_tokens`
- [ ] `ws_connections`
- [ ] `ws_message_queue`
- [ ] `ws_stats`

**Check columns added to users table:**
```sql
DESCRIBE users;
```

**Expected:**
- [ ] `ws_token`
- [ ] `ws_token_expires`
- [ ] `current_latitude`
- [ ] `current_longitude`
- [ ] `location_updated_at`

---

### Step 3: Generate Tokens ✓
- [ ] Run token generator:

```bash
php php/generate_ws_tokens.php
```

**Verify:**
- [ ] Output shows "Generated token for user X"
- [ ] Check in database:

```sql
SELECT id, email, ws_token, ws_token_expires FROM users LIMIT 5;
```

- [ ] All users have `ws_token` populated
- [ ] `ws_token_expires` is 7 days in future

---

### Step 4: Start WebSocket Server ✓
- [ ] Start server:

**Windows (Development):**
```bash
cd websocket
php server.php start
```

**Linux (Production):**
```bash
cd websocket
php server.php start -d
```

**Verify:**
- [ ] Output shows "WebSocket worker started on port 8282"
- [ ] No error messages
- [ ] Check if running:

**Windows:**
```powershell
tasklist | findstr php
```

**Linux:**
```bash
ps aux | grep server.php
```

- [ ] Should see 4 worker processes

---

### Step 5: Test Connection ✓
- [ ] Open test page in browser:

```
http://localhost/Routa/websocket/test.html
```

**In Test Page:**
- [ ] Enter your user ID (e.g., 1)
- [ ] Select role (admin/driver/rider)
- [ ] Enter ws_token from database
- [ ] Click "Connect"
- [ ] Status should show "Connected" (green)
- [ ] Console should show "Authenticated"

**Test Messages:**
- [ ] Try "Join Room" - should see "room_joined"
- [ ] Try sending custom message
- [ ] Check console log for responses

---

### Step 6: Integration into Pages ✓

#### Admin Page (admin.php)
- [ ] Add hidden inputs:

```html
<input type="hidden" id="admin-user-id" value="<?php echo $_SESSION['user_id']; ?>">
<input type="hidden" id="ws-token" value="<?php echo $_SESSION['ws_token']; ?>">
```

- [ ] Add scripts before `</body>`:

```html
<script src="/assets/js/websocket-client.js"></script>
<script src="/assets/js/admin-websocket.js"></script>
```

- [ ] Open admin page in browser
- [ ] Check browser console for:
  - [ ] "Admin WebSocket connected"
  - [ ] "Admin authenticated"

#### Driver Page (driver_dashboard.php)
- [ ] Add hidden inputs:

```html
<input type="hidden" id="driver-user-id" value="<?php echo $_SESSION['user_id']; ?>">
<input type="hidden" id="ws-token" value="<?php echo $_SESSION['ws_token']; ?>">
```

- [ ] Add scripts:

```html
<script src="/assets/js/websocket-client.js"></script>
<script src="/assets/js/driver-websocket.js"></script>
```

- [ ] Open driver page
- [ ] Check console for connection

#### Rider Page (userdashboard.php)
- [ ] Add hidden inputs:

```html
<input type="hidden" id="rider-user-id" value="<?php echo $_SESSION['user_id']; ?>">
<input type="hidden" id="ws-token" value="<?php echo $_SESSION['ws_token']; ?>">
<input type="hidden" id="booking-id" value="<?php echo $current_booking_id ?? ''; ?>">
```

- [ ] Add scripts:

```html
<script src="/assets/js/websocket-client.js"></script>
<script src="/assets/js/rider-websocket.js"></script>
```

- [ ] Open rider page
- [ ] Check console for connection

---

### Step 7: Update Login System ✓
- [ ] Modify `php/login.php` to set `ws_token` in session:

```php
// After successful login
$_SESSION['ws_token'] = $user['ws_token'];
```

- [ ] Test login:
  - [ ] Login as admin
  - [ ] Check session has `ws_token`
  - [ ] Check admin page connects to WebSocket

---

### Step 8: Integrate Backend Actions ✓
- [ ] Add WebSocket notifications to booking actions

**Example: Driver Assignment**
- [ ] Open file where you assign drivers
- [ ] Add at top:

```php
require_once 'php/WebSocketClient.php';
```

- [ ] After database update, add:

```php
WebSocketHelper::assignDriver(
    $booking_id,
    $driver_id,
    $rider_id,
    $pickup_location,
    $dropoff_location
);
```

**Example: Status Update**
- [ ] In status update handler, add:

```php
WebSocketHelper::updateStatus($booking_id, $status, 'Status updated');
```

---

### Step 9: End-to-End Testing ✓

#### Test 1: Admin Assigns Driver
- [ ] Open 3 browsers/tabs:
  - Tab 1: Admin dashboard
  - Tab 2: Driver dashboard  
  - Tab 3: Rider dashboard
- [ ] Admin assigns driver to booking
- [ ] **Expected Results:**
  - [ ] Driver receives modal popup within 1 second
  - [ ] Rider sees "Driver assigned" status
  - [ ] Admin sees confirmation

#### Test 2: Driver Accepts Booking
- [ ] Driver clicks "Accept" button
- [ ] **Expected Results:**
  - [ ] Rider sees "Driver accepted" message
  - [ ] Driver info displayed to rider
  - [ ] Map initializes (if Google Maps enabled)
  - [ ] Admin dashboard updates

#### Test 3: Status Updates
- [ ] Driver clicks status buttons in order:
  - [ ] "On Way"
  - [ ] "Arrived"
  - [ ] "Started"
  - [ ] "Completed"
- [ ] **Expected Results:**
  - [ ] Rider sees each status update instantly
  - [ ] Progress bar/timeline updates
  - [ ] Notifications appear
  - [ ] Admin sees all updates

#### Test 4: Location Tracking
- [ ] After driver accepts booking
- [ ] Check rider's map
- [ ] **Expected Results:**
  - [ ] Driver marker appears on map
  - [ ] Position updates every 5 seconds
  - [ ] ETA displays (if implemented)

---

### Step 10: Performance Check ✓
- [ ] Monitor server resources:

**Windows:**
```powershell
# Check memory usage
Get-Process php | Select-Object ProcessName, WorkingSet

# Check CPU
Get-Process php | Select-Object ProcessName, CPU
```

**Linux:**
```bash
top -p $(pgrep -f server.php)
```

- [ ] Expected metrics:
  - [ ] Memory: ~40MB total (4 workers)
  - [ ] CPU: < 5% idle
  - [ ] Response time: < 100ms

---

### Step 11: Production Preparation (Optional) ✓

#### For Production Deployment:
- [ ] Setup SSL certificate (Let's Encrypt)
- [ ] Configure Nginx reverse proxy
- [ ] Setup Supervisor for auto-restart
- [ ] Update JavaScript URLs to use WSS
- [ ] Configure firewall rules
- [ ] Enable error logging
- [ ] Setup monitoring

**See:** `docs/WEBSOCKET_SETUP_GUIDE.md` for details

---

## 🔍 Verification Commands

### Check Server Status
```bash
php websocket/server.php status
```

### Check Active Connections
```sql
SELECT role, COUNT(*) as connections 
FROM ws_connections 
WHERE last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE) 
GROUP BY role;
```

### Check Recent Messages
```sql
SELECT * FROM ws_message_queue 
ORDER BY created_at DESC 
LIMIT 10;
```

### Test Server Response
```bash
# Using websocat (if installed)
websocat ws://localhost:8282
```

---

## ❌ Troubleshooting

### Problem: Server won't start
**Check:**
- [ ] Port 8282 not already in use: `netstat -an | findstr 8282`
- [ ] PHP extensions enabled: `php -m | findstr sockets`
- [ ] Workerman installed: Check `vendor/workerman/` exists

### Problem: Can't connect from browser
**Check:**
- [ ] Server is running: `ps aux | grep server.php`
- [ ] WebSocket URL correct: `ws://localhost:8282`
- [ ] Token is valid: Check in database
- [ ] Browser console for errors

### Problem: Messages not received
**Check:**
- [ ] Authenticated successfully: Check console logs
- [ ] Joined booking room: Call `joinRoom(bookingId)`
- [ ] User ID matches: Verify `$_SESSION['user_id']`
- [ ] Server logs: Check for errors

### Problem: High CPU/Memory
**Check:**
- [ ] Reduce location update frequency (change from 5s to 10s)
- [ ] Check for connection leaks: Old connections not closing
- [ ] Reduce worker count if needed: Edit `server.php`

---

## 📝 Configuration Notes

### Default Settings

| Setting | Value | Location |
|---------|-------|----------|
| WebSocket Port | 8282 | `server.php` |
| Worker Processes | 4 | `server.php` |
| Heartbeat Interval | 30 seconds | `server.php` |
| Location Update | 5 seconds | `driver-websocket.js` |
| Token Expiry | 7 days | `generate_ws_tokens.php` |
| Auto Reconnect | 10 attempts | `websocket-client.js` |
| Reconnect Interval | 3 seconds | `websocket-client.js` |

### To Change Settings

**Change Port:**
- Edit `websocket/server.php`: Line with `new Worker("websocket://0.0.0.0:8282")`
- Edit all JavaScript files: Change `ws://localhost:8282`

**Change Worker Count:**
- Edit `websocket/server.php`: `$ws_worker->count = 4;`

**Change Location Frequency:**
- Edit `assets/js/driver-websocket.js`: Change interval in `startLocationUpdates()`

---

## ✅ Installation Complete!

When all checkboxes are marked:

✅ Dependencies installed
✅ Database configured
✅ Tokens generated
✅ Server running
✅ Test page works
✅ Pages integrated
✅ Backend actions added
✅ End-to-end tests pass
✅ Performance acceptable

**Your real-time booking system is live!** 🎉

---

## 📚 Next Steps

1. **Customize UI** - Update styling, notifications, messages
2. **Add Features** - Chat, cancel reasons, driver ratings
3. **Monitor** - Watch logs, track performance
4. **Optimize** - Based on real usage patterns
5. **Scale** - Add more servers if needed

---

## 📞 Support Resources

- **Setup Guide**: `docs/WEBSOCKET_SETUP_GUIDE.md`
- **API Reference**: `docs/WEBSOCKET_API_REFERENCE.md`
- **Architecture**: `docs/WEBSOCKET_ARCHITECTURE.md`
- **Security**: `docs/WEBSOCKET_SECURITY.md`
- **Performance**: `docs/WEBSOCKET_PERFORMANCE.md`

---

**Need Help?**
- Check browser console for errors
- Check server logs
- Review documentation
- Test with `websocket/test.html`

Good luck! 🚀
