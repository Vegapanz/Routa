# Uber-like Booking System - Complete Guide

## 🚀 Overview

This booking system works exactly like Uber with real-time driver matching, live tracking, and seamless ride management.

## 📋 Setup Instructions

### 1. Update Database Schema

Run the SQL migration to upgrade your database:

```bash
# Open phpMyAdmin at http://localhost/phpmyadmin
# Select routa_db database
# Go to SQL tab
# Import or paste the contents of upgrade_booking_system.sql
```

This will add:
- ✅ Extended ride_history table with tracking fields
- ✅ driver_locations table for real-time GPS tracking
- ✅ ride_notifications table for push notifications
- ✅ fare_settings table for dynamic pricing
- ✅ driver_earnings table for payout management
- ✅ Enhanced status workflow (pending → searching → driver_found → confirmed → arrived → in_progress → completed)

### 2. Test the System

1. **As a User (Passenger):**
   - Login to userdashboard.php
   - Click "Book a New Ride"
   - Enter pickup and dropoff locations (autocomplete powered by OpenStreetMap)
   - See real-time fare calculation
   - Submit booking
   - Watch as system finds nearby drivers
   - Track driver in real-time
   - Rate driver after completion

2. **As a Driver:**
   - Login to driver_dashboard.php
   - Go online (toggle status to "Available")
   - Receive ride requests
   - Accept or reject rides
   - Navigate to pickup location
   - Mark as "Arrived"
   - Start trip when passenger boards
   - Complete trip at destination
   - View earnings

3. **As an Admin:**
   - Login to admin.php
   - View all active bookings
   - Monitor driver locations
   - Manage fare settings
   - Process driver payouts
   - View analytics

## 🎯 Features

### For Passengers

**1. Smart Location Search**
- Powered by OpenStreetMap Nominatim (FREE, no API key needed!)
- Real-time autocomplete suggestions
- Philippine locations prioritized
- Accurate geocoding

**2. Real-time Fare Calculation**
- Base fare: ₱50
- Per kilometer: ₱15
- Per minute: ₱2
- Booking fee: ₱10
- Dynamic surge pricing support
- Minimum fare: ₱50

**3. Live Driver Tracking**
- See driver location in real-time
- ETA updates every 5 seconds
- Driver details (name, rating, plate number)
- Direct call to driver
- Status updates (confirmed, arrived, in progress)

**4. Ride Management**
- Cancel anytime before trip starts
- Trip history with all details
- Rate and review drivers
- Multiple payment methods (Cash, GCash, Card)

**5. Safety Features**
- Driver verification
- Trip sharing
- Emergency contact
- Ride receipts

### For Drivers

**1. Intelligent Ride Matching**
- Automatic assignment to nearest drivers
- Distance-based priority (within 5km radius)
- Rating-based ranking
- Accept/reject functionality

**2. Real-time Navigation**
- GPS location tracking
- Turn-by-turn directions
- Traffic updates
- Optimal route suggestions

**3. Trip Management**
- Accept new rides
- Navigate to pickup
- Mark arrival
- Start trip
- Complete trip
- Collect payment

**4. Earnings Dashboard**
- Daily earnings
- Total trips completed
- Average rating
- Acceptance rate
- Payout history

**5. Driver Tools**
- Online/offline toggle
- Ride history
- Customer ratings
- Performance metrics

### For Admins

**1. System Dashboard**
- Total bookings
- Active rides
- Revenue analytics
- Driver performance

**2. Booking Management**
- View all bookings
- Filter by status
- Cancel rides
- Refund management

**3. Driver Management**
- Verify drivers
- View locations
- Performance monitoring
- Suspend/activate accounts

**4. Fare Management**
- Set base fare
- Configure per-km rates
- Adjust surge pricing
- Minimum fare settings

**5. Financial Reports**
- Daily/monthly revenue
- Driver payouts
- Platform commission
- Payment method breakdown

## 🔄 Ride Workflow

### User Journey

```
1. User opens app
   ↓
2. Enters pickup & dropoff locations
   ↓
3. Sees estimated fare
   ↓
4. Confirms booking
   ↓
5. Status: "Searching for drivers..."
   ↓
6. System finds nearby available drivers
   ↓
7. Assigns nearest driver
   ↓
8. Status: "Driver found! Waiting for confirmation..."
   ↓
9. Driver accepts
   ↓
10. Status: "Driver confirmed! Heading to your location..."
    ↓
11. Live tracking shows driver approaching
    ↓
12. Status: "Driver has arrived"
    ↓
13. Driver starts trip
    ↓
14. Status: "Trip in progress..."
    ↓
15. Driver completes trip
    ↓
16. Status: "Trip completed!"
    ↓
17. Rate your driver
    ↓
18. Done!
```

### Driver Journey

```
1. Driver goes online
   ↓
2. System monitors location every 10 seconds
   ↓
3. New ride request arrives
   ↓
4. Notification: "New ride from [Location]"
   ↓
5. View ride details (pickup, dropoff, fare, distance)
   ↓
6. Accept or Reject
   ↓
7. If accepted: Navigate to pickup
   ↓
8. Mark "Arrived" when at pickup location
   ↓
9. Passenger boards
   ↓
10. Press "Start Trip"
    ↓
11. Navigate to dropoff location
    ↓
12. Passenger exits
    ↓
13. Press "Complete Trip"
    ↓
14. Collect payment
    ↓
15. Earnings added to account
    ↓
16. Back to available status
```

## 📡 API Endpoints

### Booking API (`php/booking_api.php`)

**Create Booking**
```javascript
POST /php/booking_api.php?action=create
Body: {
    pickup_location: "SM Manila",
    dropoff_location: "Divisoria",
    pickup_lat: 14.5995,
    pickup_lng: 120.9842,
    dropoff_lat: 14.6092,
    dropoff_lng: 120.9812,
    payment_method: "cash",
    distance: "5.2 km",
    duration: "15 mins"
}
Response: {
    success: true,
    booking_id: 123,
    status: "driver_found",
    fare: 128.00,
    driver: {...}
}
```

**Check Status**
```javascript
GET /php/booking_api.php?action=status&booking_id=123
Response: {
    success: true,
    booking: {...},
    driver: {...}
}
```

**Cancel Booking**
```javascript
POST /php/booking_api.php?action=cancel
Body: {
    booking_id: 123,
    reason: "Changed plans"
}
```

**Rate Driver**
```javascript
POST /php/booking_api.php?action=rate
Body: {
    booking_id: 123,
    rating: 5,
    review: "Great driver!"
}
```

**Get Active Booking**
```javascript
GET /php/booking_api.php?action=active
```

### Driver API (`php/driver_api.php`)

**Update Location**
```javascript
POST /php/driver_api.php?action=update_location
Body: {
    latitude: 14.5995,
    longitude: 120.9842,
    heading: 45.5,
    speed: 30
}
```

**Update Status**
```javascript
POST /php/driver_api.php?action=update_status
Body: {
    status: "available" // or "offline"
}
```

**Accept Ride**
```javascript
POST /php/driver_api.php?action=accept_ride
Body: {
    ride_id: 123
}
```

**Reject Ride**
```javascript
POST /php/driver_api.php?action=reject_ride
Body: {
    ride_id: 123,
    reason: "Too far"
}
```

**Mark Arrived**
```javascript
POST /php/driver_api.php?action=arrived
Body: {
    ride_id: 123
}
```

**Start Trip**
```javascript
POST /php/driver_api.php?action=start_trip
Body: {
    ride_id: 123
}
```

**Complete Trip**
```javascript
POST /php/driver_api.php?action=complete_trip
Body: {
    ride_id: 123,
    fare: 128.00 // optional, can adjust final fare
}
```

**Get Active Ride**
```javascript
GET /php/driver_api.php?action=active_ride
```

## 🗺️ Location Services

### OpenStreetMap Integration (FREE!)

**No API Key Required!**

The system uses:
- **Nominatim** for geocoding and address search
- **Leaflet** for maps and routing
- **OSRM** for navigation

**Features:**
- Real-time address autocomplete
- Reverse geocoding
- Distance calculation using Haversine formula
- Free worldwide coverage
- No request limits for reasonable use

**Usage Example:**
```javascript
// Search for location
fetch('https://nominatim.openstreetmap.org/search?format=json&q=SM Manila&countrycodes=ph&limit=5')

// Calculate distance
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Earth radius in km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c; // Distance in km
}
```

## 💰 Fare Calculation

The fare calculation formula:

```
Total Fare = Base Fare + (Distance × Per KM Rate) + (Duration × Per Minute Rate) × Surge Multiplier

If Total Fare < Minimum Fare:
    Total Fare = Minimum Fare

Platform Commission = Total Fare × 0.20 (20%)
Driver Earnings = Total Fare × 0.80 (80%)
```

**Example:**
- Base Fare: ₱50
- Distance: 5 km
- Duration: 15 minutes
- Per KM Rate: ₱15
- Per Minute Rate: ₱2
- Surge: 1.0x (normal)

```
Calculation:
₱50 + (5 × ₱15) + (15 × ₱2) × 1.0
= ₱50 + ₱75 + ₱30
= ₱155

Driver earns: ₱124 (80%)
Platform takes: ₱31 (20%)
```

## 🔔 Notifications System

The system sends notifications for:

**For Users:**
- Driver assigned
- Driver confirmed
- Driver arrived
- Trip started
- Trip completed
- Ride cancelled

**For Drivers:**
- New ride request
- Ride cancelled by user
- Rating received
- Payout processed

Implementation in `ride_notifications` table tracks all events.

## 📊 Database Schema

### Key Tables

**ride_history**
- Complete booking information
- Pickup/dropoff locations with coordinates
- Status tracking through lifecycle
- Payment details
- Driver ratings

**driver_locations**
- Real-time GPS coordinates
- Heading and speed
- Timestamp for freshness
- Used for driver matching

**ride_notifications**
- Push notification queue
- User and driver alerts
- Read/unread status

**fare_settings**
- Configurable pricing
- Surge multiplier
- Commission rates

**driver_earnings**
- Trip-by-trip earnings
- Payout tracking
- Commission breakdown

## 🚦 Status Flow

```
pending → searching → driver_found → confirmed → arrived → in_progress → completed
                                                                ↓
                                                          cancelled
```

**Status Descriptions:**
- `pending`: Just created, not yet processed
- `searching`: System looking for available drivers
- `driver_found`: Driver assigned, waiting for acceptance
- `confirmed`: Driver accepted, heading to pickup
- `arrived`: Driver at pickup location
- `in_progress`: Trip started, passenger on board
- `completed`: Trip finished successfully
- `cancelled`: Cancelled by user, driver, or system

## 🔧 Configuration

### Fare Settings

Edit in database or admin panel:
```sql
UPDATE fare_settings SET 
    base_fare = 50.00,
    per_km_rate = 15.00,
    per_minute_rate = 2.00,
    minimum_fare = 50.00,
    booking_fee = 10.00,
    surge_multiplier = 1.00
WHERE active = 1;
```

### Driver Search Radius

In `booking_api.php`:
```php
$nearbyDrivers = findNearbyDrivers($pdo, $pickupLat, $pickupLng, 5); // 5km radius
```

### Polling Interval

In `dashboard.js`:
```javascript
statusPollingInterval = setInterval(async () => {
    // Poll for updates
}, 5000); // Every 5 seconds
```

## 🎨 Frontend Components

### Ride Booking Modal
- Location inputs with autocomplete
- Fare display
- Payment method selection
- Real-time validation

### Ride Tracking Modal
- Driver information card
- Live status updates
- Route visualization
- Cancel button
- ETA display

### Rating Modal
- 5-star rating system
- Optional review text
- Submit/skip options

## 🔐 Security Features

1. **Session-based Authentication**
   - All API endpoints check session
   - User ID verification
   - Role-based access control

2. **Input Validation**
   - Required fields checked
   - Coordinate validation
   - SQL injection prevention (PDO prepared statements)

3. **Authorization**
   - Users can only cancel their own rides
   - Drivers can only manage assigned rides
   - Admins have full access

4. **Error Handling**
   - Try-catch blocks
   - Error logging
   - User-friendly messages

## 📱 Mobile Responsive

The system works on:
- ✅ Desktop browsers
- ✅ Mobile phones
- ✅ Tablets
- ✅ Progressive Web App ready

## 🚀 Production Deployment

### Pre-launch Checklist

1. **Database:**
   - [ ] Run upgrade_booking_system.sql
   - [ ] Set production fare settings
   - [ ] Create admin accounts
   - [ ] Verify driver accounts

2. **Configuration:**
   - [ ] Update base URL in config.php
   - [ ] Set production database credentials
   - [ ] Enable error logging
   - [ ] Disable debug mode

3. **Testing:**
   - [ ] Test complete booking flow
   - [ ] Test driver acceptance/rejection
   - [ ] Test cancellations
   - [ ] Test payment processing
   - [ ] Load test with multiple concurrent users

4. **Monitoring:**
   - [ ] Set up error monitoring
   - [ ] Database backup schedule
   - [ ] Performance monitoring
   - [ ] User analytics

## 🎉 You're Ready!

Your Uber-like booking system is now complete with:
- ✅ Real-time driver matching
- ✅ Live GPS tracking
- ✅ Automated notifications
- ✅ Dynamic fare calculation
- ✅ Rating system
- ✅ Earnings management
- ✅ Full admin control

**Start testing by booking your first ride!** 🚗💨
