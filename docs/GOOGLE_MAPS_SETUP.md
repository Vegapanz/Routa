# Google Maps API Setup Guide

## Step-by-Step Instructions to Get Your Google Maps API Key

### Step 1: Create a Google Cloud Account
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Sign in with your Google account (or create one if you don't have one)
3. If this is your first time, you may need to accept the Terms of Service

### Step 2: Create a New Project
1. Click on the project dropdown at the top of the page (next to "Google Cloud")
2. Click "NEW PROJECT" button
3. Enter a project name (e.g., "Routa Booking System")
4. Click "CREATE"
5. Wait for the project to be created (this may take a few seconds)
6. Select your newly created project from the dropdown

### Step 3: Enable Billing (Required for Google Maps)
**Note:** Google offers $200 free credit per month, which is usually enough for development/small apps
1. Click on the menu (☰) in the top-left corner
2. Go to "Billing"
3. Click "LINK A BILLING ACCOUNT" or "CREATE BILLING ACCOUNT"
4. Follow the prompts to enter your payment information
   - You won't be charged until you exceed the free $200 monthly credit
   - Most small to medium apps stay within the free tier

### Step 4: Enable Required APIs
1. In the Google Cloud Console, click the menu (☰)
2. Navigate to "APIs & Services" > "Library"
3. Search for and enable the following APIs (you need BOTH):
   
   **a) Maps JavaScript API**
   - Search for "Maps JavaScript API"
   - Click on it
   - Click "ENABLE"
   
   **b) Places API**
   - Search for "Places API"
   - Click on it
   - Click "ENABLE"

### Step 5: Create API Credentials
1. Go to "APIs & Services" > "Credentials"
2. Click "+ CREATE CREDENTIALS" at the top
3. Select "API key"
4. Your API key will be generated and displayed
5. **IMPORTANT:** Copy this key immediately!

### Step 6: Restrict Your API Key (IMPORTANT for Security)
1. After creating the key, click "RESTRICT KEY" or click on the key name
2. Under "Application restrictions":
   - Select "HTTP referrers (web sites)"
   - Click "ADD AN ITEM"
   - Add your website URLs:
     - For local development: `http://localhost/*`
     - For production: `https://yourdomain.com/*` (replace with your actual domain)
3. Under "API restrictions":
   - Select "Restrict key"
   - Check only:
     - ✅ Maps JavaScript API
     - ✅ Places API
4. Click "SAVE"

### Step 7: Add API Key to Your Project
1. Open the file: `d:\xampp\htdocs\Routa\userdashboard.php`
2. Find this line (near the bottom):
   ```html
   <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places&callback=initAutocomplete" async defer></script>
   ```
3. Replace `YOUR_API_KEY` with your actual API key:
   ```html
   <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyXXXXXXXXXXXXXXXXXXXX&libraries=places&callback=initAutocomplete" async defer></script>
   ```

### Step 8: Update Database
1. Open phpMyAdmin or your MySQL client
2. Run the SQL file: `update_ride_history.sql`
   - This adds necessary columns for storing location coordinates

### Step 9: Test Your Implementation
1. Start your XAMPP server (Apache and MySQL)
2. Go to: `http://localhost/Routa/userdashboard.php`
3. Click "Book a New Ride" button
4. Start typing a location in Philippines
5. You should see autocomplete suggestions!
6. Fill in both pickup and drop-off locations
7. The system will calculate distance and fare automatically
8. Click "Book Ride" to complete the booking

## Pricing Information

### Google Maps API Free Tier
- **$200 free credit per month** (equivalent to approximately):
  - 28,000+ autocomplete requests
  - 40,000+ maps loads
  - 200,000+ directions requests

### Typical Usage for Your App
- Each booking uses approximately:
  - 2 autocomplete sessions (pickup + dropoff) = $0.017
  - 1 directions request = $0.005
  - **Total per booking: ~$0.022**
  
- With $200 free credit, you can handle **~9,000 bookings per month** for free!

## Troubleshooting

### Common Issues:

**1. "This page can't load Google Maps correctly"**
- Solution: Make sure billing is enabled on your Google Cloud account
- Check that both Maps JavaScript API and Places API are enabled

**2. "RefererNotAllowedMapError"**
- Solution: Add your website URL to the API key restrictions
- For local development, add: `http://localhost/*`

**3. Autocomplete not showing**
- Check browser console for errors (F12)
- Verify API key is correctly inserted in the script tag
- Make sure Places API is enabled

**4. "API key not valid"**
- Wait a few minutes after creating the key (can take 5-10 minutes to activate)
- Check that you copied the complete API key

## Features Implemented

✅ Google Maps Autocomplete (Philippines only)
✅ Real-time distance calculation
✅ Automatic fare calculation (₱40 base + ₱15/km)
✅ Multiple payment methods (Cash, GCash, Card)
✅ Booking history tracking
✅ Responsive modal design
✅ Success confirmation

## Fare Calculation Formula
```
Base Fare: ₱40
Rate per KM: ₱15
Total Fare = ₱40 + (Distance in KM × ₱15)
```

You can adjust these rates in `assets/js/dashboard.js` (lines 67-69)

## Security Notes

1. **Never commit your API key to public repositories**
2. Always restrict your API key by:
   - HTTP referrer (website URL)
   - API restrictions (only enable needed APIs)
3. Monitor usage in Google Cloud Console
4. Set up usage alerts to prevent unexpected charges

## Need Help?

If you encounter any issues:
1. Check the browser console (F12) for JavaScript errors
2. Verify all APIs are enabled in Google Cloud
3. Ensure database columns are added (run the SQL file)
4. Make sure XAMPP Apache and MySQL are running

---

**Your booking system is now ready to use! 🚀**
