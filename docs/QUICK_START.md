# 🎉 FREE Booking System - 2 Step Setup

## NO API KEY NEEDED! 

I've switched to **OpenStreetMap** (free alternative to Google Maps)

---

## ✅ Step 1: Update Database

1. Open: `http://localhost/phpmyadmin`
2. Click: `routa_db` database
3. Click: `SQL` tab
4. Open file: `update_ride_history.sql`
5. Copy all contents
6. Paste into SQL box
7. Click: `Go`

**Done!** ✅

---

## ✅ Step 2: Test It!

1. Go to: `http://localhost/Routa/userdashboard.php`
2. Click: **"Book a New Ride"**
3. Type in Pickup: `Manila City Hall`
4. Wait for suggestions → Click one
5. Type in Drop-off: `SM Mall of Asia`
6. Wait for suggestions → Click one
7. See fare calculate automatically!
8. Click: **"Book Ride"**

**It works!** 🎊

---

## 🌟 Features:

✅ **Free Forever** - No API key, no credit card
✅ **Autocomplete** - Type and get suggestions
✅ **Auto Calculate** - Distance and fare
✅ **Philippines** - Optimized for PH addresses
✅ **No Limits** - Unlimited bookings

---

## 💰 Fare Calculation:

```
₱40 base + ₱15 per kilometer

Example:
- 5 km = ₱40 + (5 × ₱15) = ₱115
- 10 km = ₱40 + (10 × ₱15) = ₱190
```

---

## 🎯 How Users Book:

1. Click "Book a New Ride"
2. Type pickup location (wait for suggestions)
3. Click on correct address
4. Type drop-off location (wait for suggestions)  
5. Click on correct address
6. Review fare (shows automatically)
7. Select payment method
8. Click "Book Ride"
9. Success! ✅

---

## 🔧 Customize Fare:

Edit: `assets/js/dashboard.js` (lines 126-127)

```javascript
const baseFare = 40;      // Change base fare
const perKmRate = 15;     // Change per km rate
```

---

## ⚡ That's It!

**No complicated setup!**
**No API keys!**
**No credit card!**
**Just works!** 🚀

Read `FREE_BOOKING_SETUP.md` for detailed info.

---

**Ready to test now! Just update the database and try booking! 🎉**
