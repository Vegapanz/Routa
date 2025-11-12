# Google Maps API - Quick Visual Guide

## 🔗 Direct Links You'll Need:

1. **Google Cloud Console**: https://console.cloud.google.com/
2. **APIs Library**: https://console.cloud.google.com/apis/library
3. **Credentials**: https://console.cloud.google.com/apis/credentials

---

## 📋 Step-by-Step with Screenshots Guide:

### STEP 1: Go to Google Cloud Console
```
🌐 Visit: https://console.cloud.google.com/
→ Sign in with your Google account
```

### STEP 2: Create New Project
```
1. Click dropdown at top (says "Select a project")
2. Click "NEW PROJECT"
3. Name it: "Routa Booking"
4. Click "CREATE"
5. Wait 10 seconds
6. Select your new project from dropdown
```

### STEP 3: Enable Billing
```
⚠️ IMPORTANT: Required but FREE for most apps
($200 free credit per month = ~9,000 bookings)

1. Click ☰ menu (top left)
2. Click "Billing"
3. Click "LINK A BILLING ACCOUNT"
4. Enter payment info (won't be charged unless you exceed $200/month)
5. Click "SET ACCOUNT AND BUDGET"
```

### STEP 4: Enable APIs (Need BOTH!)
```
API #1 - Maps JavaScript API:
📍 https://console.cloud.google.com/apis/library/maps-backend.googleapis.com
→ Click "ENABLE"
→ Wait for "API enabled" message

API #2 - Places API:
📍 https://console.cloud.google.com/apis/library/places-backend.googleapis.com
→ Click "ENABLE"
→ Wait for "API enabled" message
```

### STEP 5: Create API Key
```
1. Go to: https://console.cloud.google.com/apis/credentials
2. Click "+ CREATE CREDENTIALS" (top center)
3. Click "API key"
4. 🔑 COPY YOUR KEY NOW! (looks like: AIzaSyXXXXXXXXXXXX...)
5. Click "RESTRICT KEY"
```

### STEP 6: Restrict API Key (Security)
```
Under "Application restrictions":
→ Select "HTTP referrers (web sites)"
→ Click "+ ADD AN ITEM"
→ Enter: http://localhost/*
→ Click "DONE"

Under "API restrictions":
→ Select "Restrict key"
→ Check ONLY these two:
   ✅ Maps JavaScript API
   ✅ Places API
→ Click "SAVE"

⏱️ Wait 5-10 minutes for key to activate
```

---

## 💻 Add Key to Your Code:

### Open File: `userdashboard.php`

**Find this line (near bottom):**
```html
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places&callback=initAutocomplete" async defer></script>
```

**Replace YOUR_API_KEY with your actual key:**
```html
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyXXXXXXXXXXXX&libraries=places&callback=initAutocomplete" async defer></script>
```

**Save the file!**

---

## 🗄️ Update Database:

### Open phpMyAdmin:
```
1. Go to: http://localhost/phpmyadmin
2. Click "routa_db" database (left sidebar)
3. Click "SQL" tab (top menu)
4. Open file: update_ride_history.sql
5. Copy all contents
6. Paste into SQL box
7. Click "Go" button
8. You should see "Query executed successfully"
```

---

## ✅ Test Your Booking System:

### Step-by-Step Test:
```
1. Open: http://localhost/Routa/userdashboard.php
2. Click "Book a New Ride" button
3. In "Pickup Location" field, type: "Manila"
   → You should see autocomplete suggestions appear!
4. Click on a suggestion
5. In "Drop-off Location", type: "Quezon City"
   → Select a suggestion
6. You should see:
   - Distance calculated (e.g., "8.5 km")
   - Fare calculated (e.g., "₱168")
7. Select payment method
8. Click "Book Ride"
9. Success modal should appear!
10. Check "Trip History" tab - your booking should be there
```

---

## 🎯 Quick Troubleshooting:

### ❌ "This page can't load Google Maps correctly"
**Solution:**
- Make sure billing is enabled
- Check both APIs are enabled (Maps JavaScript + Places)
- Wait 10 minutes after creating key

### ❌ "RefererNotAllowedMapError"
**Solution:**
- Go to API Key restrictions
- Add: `http://localhost/*` to HTTP referrers
- Save and wait 5 minutes

### ❌ Autocomplete not showing suggestions
**Solution:**
- Check browser console (press F12)
- Look for any red errors
- Verify API key is correct in userdashboard.php
- Make sure Places API is enabled

### ❌ "API key not valid"
**Solution:**
- Wait 5-10 minutes after creating key
- Check you copied the complete key (starts with AIza...)
- Verify no extra spaces when pasting

---

## 📊 Monitor Your Usage:

**Check usage at:**
https://console.cloud.google.com/google/maps-apis/metrics

**You get FREE:**
- $200 credit per month
- ~28,000 autocomplete sessions
- ~9,000 complete bookings

**You'll be notified before any charges**

---

## 🎉 All Done!

Your booking system features:
✅ Real-time location autocomplete
✅ Automatic distance calculation
✅ Automatic fare calculation
✅ Multiple payment methods
✅ Booking history
✅ Beautiful UI

**Next time someone books a ride, it will:**
1. Save to database
2. Show in trip history
3. Display pickup/dropoff locations
4. Show fare and distance
5. Track payment method

---

## 💡 Pro Tips:

1. **For Production:** Replace `http://localhost/*` with your actual domain
2. **Customize Rates:** Edit `assets/js/dashboard.js` lines 68-69
3. **Add More Features:** Distance matrix, real-time tracking, driver assignment
4. **Set Usage Alerts:** In Google Cloud, set up budget alerts

---

## 🆘 Need More Help?

- Google Maps Documentation: https://developers.google.com/maps/documentation
- Places API Docs: https://developers.google.com/maps/documentation/places/web-service
- Stack Overflow: Tag questions with `google-maps-api-3`

**You're ready to go! 🚀**
