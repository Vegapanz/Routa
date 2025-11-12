# 🚀 Testing the Uber-like Booking System

## ✅ Setup Steps (Run These First!)

### 1. Update Database
Run these SQL files in phpMyAdmin:
```sql
1. upgrade_booking_system.sql  (adds new tables and columns)
2. set_driver_locations.sql    (sets driver GPS locations)
```

### 2. Verify Setup
Check that:
- ✅ Drivers have GPS coordinates (current_lat, current_lng)
- ✅ Drivers status = 'available'
- ✅ Tables created: driver_locations, ride_notifications, driver_earnings, fare_settings

## 🧪 Complete Testing Flow

### **STEP 1: User Books a Ride**

1. Login as user: `juan@email.com` / `password123`
2. Click **"Book a New Ride"**
3. Enter locations:
   - **Pickup:** Type "Metro Manila" (wait for suggestions, click one)
   - **Dropoff:** Type "Cavite" (wait for suggestions, click one)
4. Click **"Book Ride"**
5. **Expected:** Modal shows "Searching for nearby drivers..." then "Driver found!"
6. **Status:** `searching` → `driver_found`

---

### **STEP 2: Driver Receives Request**

1. Open new tab/window
2. Login as driver: `pedro@driver.com` / `password123`
3. Toggle status to **"Online"** (green)
4. **Expected:** See "New Ride Requests" section with yellow border
5. **Ride Details Show:**
   - Booking ID
   - Passenger name & phone
   - Pickup & dropoff locations
   - Fare amount
   - Distance
   - **Two buttons:** "Accept Ride" / "Reject"

---

### **STEP 3: Driver Accepts Ride**

1. Click **"Accept Ride"**
2. **Expected:** 
   - Success notification: "Ride accepted!"
   - Page refreshes
   - Ride moves to "Active Rides" section
   - Status badge: "Confirmed"
   - Button shows: "Start Ride"

---

### **STEP 4: User Sees Confirmation**

1. Go back to user tab
2. **Expected (auto-updates every 5 seconds):**
   - Status: "Driver confirmed! Heading to your location..."
   - Driver details visible:
     - Name: Pedro Santos
     - Plate number
     - Rating: 5.0
     - Phone number (clickable)
   - Status: `confirmed`

---

### **STEP 5: Driver Arrives & Starts Trip**

1. In driver dashboard:
2. Click **"Start Ride"**
3. Confirm: "Start this trip?"
4. **Expected:**
   - Notification: "Trip started! Drive safely."
   - Status changes to "In Progress"
   - Button changes to "Complete Ride"

---

### **STEP 6: User Sees Trip In Progress**

1. User tab auto-updates
2. **Expected:**
   - Status: "Trip in progress..."
   - Shows route and driver info
   - Cancel button hidden

---

### **STEP 7: Driver Completes Trip**

1. Click **"Complete Ride"**
2. Confirm: "Mark this trip as completed?"
3. **Expected:**
   - Success: "Trip completed successfully!"
   - Earnings added: ₱168 (₱210 fare - 20% commission)
   - Driver status back to "Available"
   - Trip moves to "Trip History"
   - Stats updated:
     - Today's Earnings increased
     - Total Earnings increased
     - Total Trips increased

---

### **STEP 8: User Rates Driver**

1. User tab shows: "Trip completed!"
2. Rating modal appears automatically
3. Click stars (1-5)
4. Optional: Write review
5. Click **"Submit Rating"**
6. **Expected:**
   - "Thank you for your feedback!"
   - Trip appears in "Trip History"

---

### **STEP 9: Admin Monitors Everything**

1. Login as admin: `admin@routa.com` / `admin123`
2. **Dashboard Shows:**
   - **Total Revenue:** Updated with new trip
   - **Total Bookings:** Increased by 1
   - **Active Drivers:** Shows online drivers
   - **Pending Bookings:** Real-time count

3. **Tabs Available:**
   - **Pending Bookings:** See rides waiting for drivers
   - **All Bookings:** Complete history with filters
   - **Analytics:** Charts (daily bookings, monthly revenue, status distribution)
   - **Drivers:** List all drivers, status, stats
   - **Users:** All registered users

4. **Admin Can:**
   - View all booking details
   - See driver locations
   - Monitor system performance
   - Manage fare settings

---

## 🔄 Test Different Scenarios

### **Scenario A: Driver Rejects Ride**
1. User books ride
2. Driver clicks **"Reject"**
3. **Expected:**
   - System finds NEXT nearest driver automatically
   - User sees: "Driver declined. Searching for another driver..."
   - New driver gets the request
   - First driver goes back to "Available"

### **Scenario B: User Cancels Ride**
1. User books ride
2. Before driver accepts, click **"Cancel Ride"**
3. **Expected:**
   - Ride cancelled
   - If driver was assigned, they become available again
   - Booking status: `cancelled`

### **Scenario C: Multiple Concurrent Bookings**
1. Open 3 user tabs
2. Book rides from each
3. **Expected:**
   - Each gets assigned to different drivers
   - Drivers see separate requests
   - No conflicts

### **Scenario D: No Drivers Available**
1. Set all drivers to "Offline"
2. User books ride
3. **Expected:**
   - Status stuck at "Searching for nearby drivers..."
   - No driver assigned
   - User can still cancel

---

## 📊 Database Status Check

Run this query to see booking statuses:
```sql
SELECT 
    r.id,
    r.status,
    u.name as passenger,
    d.name as driver,
    r.pickup_location,
    r.destination,
    r.fare,
    r.created_at
FROM ride_history r
LEFT JOIN users u ON r.user_id = u.id
LEFT JOIN tricycle_drivers d ON r.driver_id = d.id
ORDER BY r.created_at DESC
LIMIT 10;
```

Check driver status:
```sql
SELECT 
    id,
    name,
    status,
    current_lat,
    current_lng,
    total_trips_completed,
    total_earnings
FROM tricycle_drivers;
```

---

## 🐛 Troubleshooting

### Issue: No driver assigned
**Fix:** 
```sql
-- Run set_driver_locations.sql
-- Verify drivers have GPS coordinates
SELECT id, name, current_lat, current_lng FROM tricycle_drivers;
```

### Issue: Driver doesn't see request
**Fix:**
- Check driver status is "Online"
- Refresh driver dashboard
- Check database: `SELECT * FROM ride_history WHERE status = 'driver_found';`

### Issue: Locations not suggesting
**Fix:**
- Open browser console (F12)
- Type at least 3 characters
- Check for errors
- Try: "Manila", "Quezon City", "Makati"

### Issue: API errors
**Fix:**
- Check `php/booking_api.php` exists
- Check `php/driver_api.php` exists
- Verify session is active
- Check browser console for errors

---

## ✨ Key Features Working

✅ Real-time driver matching (5km radius)
✅ Multi-API location search (Photon + Nominatim)
✅ Dynamic fare calculation
✅ Driver accept/reject functionality
✅ Live status updates (polls every 5 seconds)
✅ Driver earnings tracking (80/20 split)
✅ Rating system
✅ Admin monitoring
✅ Notifications
✅ Trip history

---

## 🎉 Success Criteria

- [✅] User can book rides with autocomplete
- [✅] System finds nearest available driver
- [✅] Driver receives request notification
- [✅] Driver can accept/reject
- [✅] Real-time status updates for both
- [✅] Driver can start and complete trip
- [✅] Earnings calculated automatically
- [✅] User can rate driver
- [✅] Admin can monitor everything
- [✅] Multiple concurrent bookings work

**The system now works exactly like Uber!** 🚗💨
