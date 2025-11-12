# Quick Setup Checklist for Book a Ride Feature

## ✅ Setup Steps (Do these in order):

### 1. Update Database
- [ ] Open phpMyAdmin (http://localhost/phpmyadmin)
- [ ] Select your `routa_db` database
- [ ] Go to SQL tab
- [ ] Copy and paste contents from `update_ride_history.sql`
- [ ] Click "Go" to execute

### 2. Get Google Maps API Key
- [ ] Follow steps in `GOOGLE_MAPS_SETUP.md`
- [ ] Create Google Cloud account
- [ ] Enable billing (free $200/month credit)
- [ ] Enable Maps JavaScript API
- [ ] Enable Places API
- [ ] Create and copy API key
- [ ] Restrict API key for security

### 3. Add API Key to Project
- [ ] Open `userdashboard.php`
- [ ] Find line with `YOUR_API_KEY`
- [ ] Replace with your actual API key
- [ ] Save file

### 4. Test the Feature
- [ ] Start XAMPP (Apache + MySQL)
- [ ] Login to your account
- [ ] Go to dashboard
- [ ] Click "Book a New Ride"
- [ ] Test autocomplete by typing a location
- [ ] Complete a test booking
- [ ] Check if booking appears in Trip History

## 📁 Files Modified/Created:

### Created Files:
✅ `php/book_ride.php` - Backend booking handler
✅ `update_ride_history.sql` - Database updates
✅ `GOOGLE_MAPS_SETUP.md` - Detailed setup guide
✅ `SETUP_CHECKLIST.md` - This file

### Modified Files:
✅ `userdashboard.php` - Added booking modal
✅ `assets/js/dashboard.js` - Added Google Maps integration
✅ `assets/css/userdashboard-clean.css` - Added modal styling

## 🎨 Features:

### What Works Now:
- ✅ Click "Book a New Ride" button opens modal
- ✅ Autocomplete for pickup location (Philippines)
- ✅ Autocomplete for drop-off location (Philippines)
- ✅ Automatic distance calculation
- ✅ Automatic fare calculation (₱40 base + ₱15/km)
- ✅ Payment method selection (Cash, GCash, Card)
- ✅ Booking saved to database
- ✅ Success notification
- ✅ Trip appears in history after booking

### Fare Formula:
```
Base Fare: ₱40
Per Kilometer: ₱15
Total = ₱40 + (Distance × ₱15)
```

## 🔧 Customization Options:

### Change Fare Rates:
Edit `assets/js/dashboard.js` around line 68:
```javascript
const baseFare = 40;      // Change base fare here
const perKmRate = 15;     // Change per-km rate here
```

### Change Country Restriction:
Edit `assets/js/dashboard.js` line 11:
```javascript
componentRestrictions: { country: 'ph' }  // Change 'ph' to other country code
```

### Add More Payment Methods:
Edit `userdashboard.php` in the payment method dropdown:
```html
<option value="newmethod">New Method</option>
```

## 🚨 Common Issues:

**Problem:** Autocomplete not working
**Solution:** 
- Check if API key is added correctly
- Wait 5-10 minutes after creating API key
- Check browser console for errors (F12)

**Problem:** "This page can't load Google Maps"
**Solution:**
- Enable billing in Google Cloud
- Enable both required APIs (Maps JavaScript + Places)

**Problem:** Bookings not saving
**Solution:**
- Run the SQL update file
- Check database connection in `php/config.php`
- Check browser console for errors

## 📊 Monitor Usage:

Check your Google Maps API usage at:
https://console.cloud.google.com/google/maps-apis/metrics

Free tier includes $200/month credit (≈9,000 bookings)

## 🎉 You're All Set!

Once you complete the checklist above, your booking system will be fully functional!
