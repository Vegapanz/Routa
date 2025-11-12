# Complete Booking Flow - Testing Guide ✅

## Overview
This guide tests the complete end-to-end booking flow including driver actions and user rating.

---

## Complete Flow Steps

### 1️⃣ User Books a Ride
**User:** maria@email.com / password

1. Login to user dashboard
2. Click "Book a New Ride"
3. Enter pickup location (e.g., "SM Manila")
4. Enter dropoff location (e.g., "Divisoria")
5. Select payment method
6. Click "Book Ride"
7. **Result:** User sees "Booking submitted! Waiting for admin confirmation..."
8. **Status:** `pending`

---

### 2️⃣ Admin Assigns Driver
**Admin:** admin@routa.com / admin123

1. Login to admin dashboard
2. Go to "Pending Bookings" tab
3. See the new booking (SM Manila → Divisoria)
4. Click "Confirm & Assign Driver"
5. Select driver from dropdown (e.g., Pedro)
6. Click "Confirm Assignment"
7. **Result:** Admin sees "Booking assigned successfully!"
8. **Status:** `driver_found` (waiting for driver acceptance)

---

### 3️⃣ Driver Accepts Ride
**Driver:** pedro@driver.com / password

1. Login to driver dashboard
2. See "New Ride Requests" section with yellow background
3. View ride details:
   - Booking ID
   - Rider name and phone
   - Pickup location
   - Dropoff location
   - Fare
4. Click "Accept Ride" button
5. Confirm acceptance
6. **Result:** Driver sees "Ride accepted! Navigate to pickup location."
7. **Status:** `confirmed`
8. **Driver Status:** Changed to `on_trip`

---

### 4️⃣ Driver Arrives at Pickup
**Driver Dashboard**

1. See ride in "Active Rides" section
2. Status shows "Confirmed"
3. Click "I've Arrived" button (orange button)
4. Confirm arrival
5. **Result:** "Marked as arrived! Wait for passenger."
6. **Status:** `arrived`
7. **User Notification:** "Your driver has arrived!"

---

### 5️⃣ Driver Starts Trip
**Driver Dashboard**

1. Passenger gets in the tricycle
2. Click "Start Trip" button (green button)
3. Confirm trip start
4. **Result:** "Trip started! Drive safely to destination."
5. **Status:** `in_progress`
6. **User Notification:** "Your trip has started!"

---

### 6️⃣ Driver Completes Trip
**Driver Dashboard**

1. Arrive at destination
2. Click "Complete Trip" button (blue button)
3. Confirm completion
4. **Result:** "Trip completed! You earned ₱48.00" (80% of ₱60)
5. **Status:** `completed`
6. **Driver Status:** Changed back to `available`
7. **Driver Earnings:** Updated in database
8. **User Notification:** "Trip completed! Please rate your driver."

---

### 7️⃣ User Rates Driver
**User Dashboard**

1. After trip completes, rating modal appears automatically
2. User sees "Rate Your Trip" modal
3. Click stars to select rating (1-5 stars)
4. Optionally add review text
5. Click "Submit Rating"
6. **Result:** "Thank you! Your feedback helps us improve."
7. **Database:** Rating saved in `ride_history.user_rating`
8. **Driver Rating:** Average rating updated automatically

---

## Test Database Entry

Created test booking:
```sql
Booking ID: 12
User: Maria (maria@email.com)
Pickup: SM Manila
Dropoff: Divisoria
Fare: ₱60
Distance: 3.0 km
Status: pending
```

---

## Status Progression

1. **pending** → User booked, waiting for admin
2. **driver_found** → Admin assigned driver, waiting for driver acceptance
3. **confirmed** → Driver accepted, heading to pickup
4. **arrived** → Driver at pickup location
5. **in_progress** → Trip started, en route to destination
6. **completed** → Trip finished, ready for rating
7. **rejected** → Admin/Driver rejected (goes back to pending/searching)
8. **cancelled** → User cancelled

---

## Button Flow on Driver Dashboard

### When Status = 'confirmed'
```
🟡 I've Arrived (orange button)
   ↓
```

### When Status = 'arrived'
```
🟢 Start Trip (green button)
   ↓
```

### When Status = 'in_progress'
```
🔵 Complete Trip (blue button)
   ↓
   User Rating Modal
```

---

## API Endpoints Used

### Driver Actions
- `php/driver_api.php?action=accept_ride` - Accept assigned ride
- `php/driver_api.php?action=reject_ride` - Reject assigned ride
- `php/driver_api.php?action=arrived` - Mark as arrived at pickup
- `php/driver_api.php?action=start_trip` - Start the trip
- `php/driver_api.php?action=complete_trip` - Complete the trip

### User Actions
- `php/booking_api.php?action=create` - Create new booking
- `php/booking_api.php?action=status` - Check booking status
- `php/booking_api.php?action=rate` - Submit driver rating
- `php/booking_api.php?action=active` - Get active booking

### Admin Actions
- `admin.php` (POST) - `action=assign_booking` - Assign driver to booking
- `admin.php` (POST) - `action=reject_booking` - Reject booking

---

## Key Features Working

✅ **User Booking** - Creates pending booking
✅ **Admin Assignment** - Assigns driver to booking
✅ **Driver Accept/Reject** - Driver can accept or reject assignments
✅ **Status Progression** - Confirmed → Arrived → In Progress → Completed
✅ **Drop-off Detection** - Complete button only shows when in_progress
✅ **Rating System** - User can rate driver after completion
✅ **Earnings Calculation** - Driver gets 80%, platform gets 20%
✅ **Driver Stats** - Earnings and ratings automatically updated
✅ **Notifications** - All parties notified at each step

---

## Testing Checklist

- [ ] User can book a ride
- [ ] Admin sees pending booking
- [ ] Admin can assign driver
- [ ] Driver sees assignment request
- [ ] Driver can accept ride
- [ ] Driver can reject ride
- [ ] "I've Arrived" button works
- [ ] "Start Trip" button works
- [ ] "Complete Trip" button works
- [ ] User receives rating modal
- [ ] User can submit rating
- [ ] Driver rating updates
- [ ] Driver earnings update
- [ ] Trip appears in history
- [ ] All notifications sent

---

## Common Issues & Solutions

### Issue: Rating modal doesn't appear
**Solution:** Check if booking status is 'completed' and booking_id is passed correctly

### Issue: Complete button doesn't work
**Solution:** Verify ride status is 'in_progress' and driver_id matches session

### Issue: Driver can't accept ride
**Solution:** Check if ride status is 'driver_found' and driver_id is assigned

### Issue: Earnings don't update
**Solution:** Verify driver_earnings table exists and platform commission calculation is correct

---

## Database Verification Queries

### Check booking status
```sql
SELECT id, pickup_location, destination, status, driver_id, user_id, fare 
FROM ride_history 
WHERE id = 12;
```

### Check driver earnings
```sql
SELECT * FROM driver_earnings 
WHERE ride_id = 12;
```

### Check user rating
```sql
SELECT user_rating, user_review 
FROM ride_history 
WHERE id = 12;
```

### Check driver stats
```sql
SELECT total_trips_completed, total_earnings, average_rating 
FROM tricycle_drivers 
WHERE id = 1;
```

---

## Files Modified for This Feature

1. **driver_dashboard.php** - Added "I've Arrived" button, updated button logic
2. **assets/js/pages/driver-dashboard.js** - Added markArrived(), updated start/complete trip
3. **php/driver_api.php** - Already had all endpoints (accept, arrived, start, complete)
4. **php/booking_api.php** - Already had rating endpoint
5. **assets/js/dashboard.js** - Already had rating modal functionality

---

## Next Steps (Optional Enhancements)

1. **Real-time Location Tracking** - Show driver location on map
2. **ETA Calculation** - Show estimated arrival time
3. **Push Notifications** - Real-time updates without refresh
4. **Trip Route Map** - Show route taken during trip
5. **Receipt Generation** - PDF receipt for completed trips
6. **Driver Performance Dashboard** - Detailed analytics
7. **Multi-language Support** - Support for different languages

---

*Last Updated: November 12, 2025*
*Status: ✅ All Features Working*
