# 🚀 WebSocket Quick Reference Card

## One-Page Guide for Developers

---

## 🏁 Quick Start (5 Minutes)

```bash
# 1. Install
cd websocket && composer install

# 2. Database
mysql -u root -p routa < database/websocket_tables.sql
php php/generate_ws_tokens.php

# 3. Start
php server.php start

# 4. Test
Open: http://localhost/Routa/websocket/test.html
```

---

## 📝 Integration Snippets

### Add to HTML Pages

```html
<!-- All Pages -->
<input type="hidden" id="[role]-user-id" value="<?= $_SESSION['user_id'] ?>">
<input type="hidden" id="ws-token" value="<?= $_SESSION['ws_token'] ?>">
<script src="/assets/js/websocket-client.js"></script>

<!-- Admin Page -->
<script src="/assets/js/admin-websocket.js"></script>

<!-- Driver Page -->
<script src="/assets/js/driver-websocket.js"></script>

<!-- Rider Page -->
<input type="hidden" id="booking-id" value="<?= $booking_id ?? '' ?>">
<script src="/assets/js/rider-websocket.js"></script>
```

### PHP Backend Actions

```php
require_once 'php/WebSocketClient.php';

// Assign driver
WebSocketHelper::assignDriver($booking_id, $driver_id, $rider_id, $pickup, $dropoff);

// Update status
WebSocketHelper::updateStatus($booking_id, 'on_way', 'Driver on the way');

// Custom notification
WebSocketHelper::notify($user_id, 'rider', 'custom_event', ['data' => 'value']);
```

### JavaScript Client Usage

```javascript
// Listen for messages
ws.onMessage('driver_assigned', (data) => {
    console.log('New assignment:', data);
});

// Send message
ws.send({
    type: 'status_update',
    booking_id: 123,
    status: 'on_way'
});

// Check connection
if (ws.isConnected()) {
    // Ready to send
}
```

---

## 📡 Message Types Reference

### Admin → Server

```javascript
// Assign Driver
{
    type: 'assign_driver',
    booking_id: 123,
    driver_id: 456,
    rider_id: 789,
    pickup_location: 'Location A',
    dropoff_location: 'Location B'
}
```

### Driver → Server

```javascript
// Accept/Decline
{
    type: 'driver_response',
    booking_id: 123,
    response: 'accepted' // or 'declined'
}

// Status Update
{
    type: 'status_update',
    booking_id: 123,
    status: 'on_way' // on_way, arrived, started, completed
}

// Location Update
{
    type: 'location_update',
    booking_id: 123,
    latitude: 14.5995,
    longitude: 120.9842
}
```

### Server → Client (All)

```javascript
// Status Update
{
    type: 'status_update',
    booking_id: 123,
    status: 'on_way',
    message: 'Driver is on the way',
    timestamp: 1699999999
}

// Driver Assignment (to driver)
{
    type: 'driver_assigned',
    booking_id: 123,
    pickup_location: 'Location A',
    dropoff_location: 'Location B',
    rider_id: 789
}

// Location Update (to rider)
{
    type: 'location_update',
    booking_id: 123,
    latitude: 14.5995,
    longitude: 120.9842
}
```

---

## 🎯 Common Tasks

### Task 1: Add Custom Event

**Server (server.php):**
```php
case 'my_custom_event':
    handleMyCustomEvent($connection, $message);
    break;

function handleMyCustomEvent($connection, $message) {
    // Your logic here
    broadcastToUser($user_id, $role, ['type' => 'custom_response']);
}
```

**Client (JavaScript):**
```javascript
// Send
ws.send({ type: 'my_custom_event', data: 'value' });

// Receive
ws.onMessage('custom_response', (data) => {
    console.log('Custom event received:', data);
});
```

### Task 2: Add Notification

```javascript
// JavaScript
function showNotification(message) {
    // Browser notification
    new Notification('Routa', { body: message });
    
    // In-page alert
    alert(`<div class="alert alert-info">${message}</div>`);
}

ws.onMessage('driver_assigned', (data) => {
    showNotification('New booking assigned!');
});
```

### Task 3: Add UI Element

```html
<!-- Status indicator -->
<span id="ws-status-indicator" class="badge badge-secondary">Offline</span>

<!-- Active booking panel -->
<div id="active-booking-panel" style="display:none;">
    <h3>Current Booking: <span id="current-booking-id"></span></h3>
    <button onclick="updateBookingStatus('on_way')">On Way</button>
    <button onclick="updateBookingStatus('arrived')">Arrived</button>
    <button onclick="updateBookingStatus('started')">Start Trip</button>
    <button onclick="updateBookingStatus('completed')">Complete</button>
</div>
```

---

## 🔧 Configuration Cheat Sheet

| What | Where | Line/Property |
|------|-------|---------------|
| Change Port | `server.php` | `new Worker("websocket://0.0.0.0:8282")` |
| Worker Count | `server.php` | `$ws_worker->count = 4;` |
| Heartbeat | `server.php` | `Timer::add(30, ...)` |
| Location Freq | `driver-websocket.js` | `setInterval(..., 5000)` |
| Reconnect | `websocket-client.js` | `reconnectInterval: 3000` |
| Max Reconnect | `websocket-client.js` | `maxReconnectAttempts: 10` |
| Token Expiry | `generate_ws_tokens.php` | `'+7 days'` |

---

## 🐛 Debug Commands

```bash
# Check if server running
ps aux | grep server.php

# Check port
netstat -an | grep 8282

# View logs (if logging enabled)
tail -f /var/log/routa-websocket.log

# Check memory
top -p $(pgrep -f server.php)

# Test connection
websocat ws://localhost:8282
```

**Browser Console:**
```javascript
// Enable debug mode
ws.config.debug = true;

// Check connection
console.log('Connected:', ws.isConnected());

// Manual send
ws.send({ type: 'status_update', booking_id: 1, status: 'on_way' });
```

**SQL Queries:**
```sql
-- Check active connections
SELECT * FROM ws_connections WHERE last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE);

-- Check tokens
SELECT id, email, ws_token, ws_token_expires FROM users;

-- Check message queue
SELECT * FROM ws_message_queue WHERE is_delivered = 0;
```

---

## ⚡ Performance Tips

1. **Reduce Location Updates**
   ```javascript
   // Change from 5s to 10s
   setInterval(() => sendLocation(), 10000);
   ```

2. **Minimize Message Size**
   ```javascript
   // Use short keys
   { t: 'loc', b: 123, lat: 14.5, lng: 120.9 }
   ```

3. **Cache Database Queries**
   ```php
   // Add caching in server.php
   if (isset($cache[$booking_id])) return $cache[$booking_id];
   ```

4. **Batch Updates**
   ```javascript
   // Send multiple updates together
   ws.send({ type: 'batch', updates: [...] });
   ```

---

## 🔒 Security Checklist

- [ ] Use WSS (not WS) in production
- [ ] Validate all input on server
- [ ] Check token expiration
- [ ] Implement rate limiting
- [ ] Use HTTPS for web pages
- [ ] Sanitize output in JavaScript
- [ ] Log security events
- [ ] Use environment variables for secrets

---

## 📊 Status Values

| Status | Meaning | Who Can Set |
|--------|---------|-------------|
| `pending` | No driver assigned | System |
| `assigned` | Driver assigned, waiting | Admin |
| `accepted` | Driver accepted | Driver |
| `declined` | Driver declined | Driver |
| `on_way` | Driver heading to pickup | Driver |
| `arrived` | Driver at pickup | Driver |
| `started` | Trip started | Driver |
| `completed` | Trip finished | Driver |
| `cancelled` | Trip cancelled | Admin/Driver |

---

## 🎨 UI Helper Functions

```javascript
// Format status
function formatStatus(status) {
    const map = {
        'on_way': 'On the Way',
        'arrived': 'Arrived',
        'started': 'Trip Started',
        'completed': 'Completed'
    };
    return map[status] || status;
}

// Get badge color
function getBadgeClass(status) {
    const map = {
        'pending': 'secondary',
        'accepted': 'success',
        'on_way': 'primary',
        'completed': 'success'
    };
    return 'badge badge-' + (map[status] || 'secondary');
}

// Show toast
function showToast(message, type = 'info') {
    const toast = $(`<div class="alert alert-${type}">${message}</div>`);
    $('#notifications').append(toast);
    setTimeout(() => toast.remove(), 3000);
}
```

---

## 🔗 Quick Links

- **Test Tool**: `/websocket/test.html`
- **Setup Guide**: `/docs/WEBSOCKET_SETUP_GUIDE.md`
- **API Docs**: `/docs/WEBSOCKET_API_REFERENCE.md`
- **Architecture**: `/docs/WEBSOCKET_ARCHITECTURE.md`

---

## 💡 Pro Tips

1. **Always check `isConnected()` before sending**
2. **Use `onMessage()` for specific events**
3. **Implement reconnection logic**
4. **Log all WebSocket events in debug mode**
5. **Test with multiple browsers/tabs**
6. **Monitor server resources**
7. **Keep messages small and efficient**
8. **Use rooms for targeted messaging**

---

## 🆘 Common Errors

**"Not authenticated"**
→ Send `auth` message first

**"Invalid token"**
→ Check token in database matches

**"Connection refused"**
→ Check server is running on port 8282

**"Missing required field"**
→ Include all required message fields

**"Rate limit exceeded"**
→ Reduce message frequency

---

## 📞 Emergency Commands

```bash
# Stop server
php server.php stop

# Restart server
php server.php restart

# Check status
php server.php status

# Force kill (Windows)
taskkill /F /IM php.exe

# Force kill (Linux)
killall -9 php
```

---

## ✅ Quick Test

```javascript
// 1. Open browser console on any page
// 2. Paste this code:

const testWS = new RoutaWebSocket({
    url: 'ws://localhost:8282',
    userId: 1,
    role: 'admin',
    token: 'your_token_here'
});

testWS.on('authenticated', () => {
    console.log('✅ WebSocket working!');
    testWS.send({ type: 'status_update', booking_id: 1, status: 'on_way' });
});

// If you see "✅ WebSocket working!" - all good!
```

---

**Print this page for quick reference! 📄**
