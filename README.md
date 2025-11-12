# 🚖 ROUTA - Tricycle Booking System

A complete ride-hailing platform for tricycle services with real-time booking, GPS tracking, and admin management.

## 📁 Project Structure

```
Routa/
├── index.php                    # Landing page
├── login.php                    # Login page (User/Driver/Admin)
├── register.php                 # User registration
├── userdashboard.php            # User dashboard & booking
├── driver_dashboard.php         # Driver dashboard & trip management
├── admin.php                    # Admin dashboard & management
│
├── assets/                      # Frontend assets
│   ├── css/                     # Stylesheets
│   │   ├── style.css           # Global styles
│   │   ├── auth.css            # Login/Register styles
│   │   ├── admin.css           # Admin dashboard styles
│   │   ├── userdashboard-clean.css
│   │   ├── components/         # Component styles
│   │   └── pages/              # Page-specific styles
│   ├── images/                  # Images and icons
│   └── js/                      # JavaScript files
│       ├── main.js             # Global JS
│       ├── dashboard.js        # User dashboard JS
│       ├── admin.js            # Admin dashboard JS
│       └── pages/              # Page-specific JS
│           ├── home.js
│           ├── login.js
│           ├── register.js
│           └── driver-dashboard.js
│
├── php/                         # Backend PHP scripts
│   ├── config.php              # Database configuration
│   ├── login.php               # Login handler
│   ├── register.php            # Registration handler
│   ├── logout.php              # Logout handler
│   ├── book_ride.php           # Ride booking API
│   ├── booking_api.php         # Booking management API
│   ├── send_otp.php            # OTP sending
│   ├── verify_otp.php          # OTP verification
│   ├── admin_functions.php     # Admin utilities
│   ├── check_admin.php         # Admin authentication
│   └── includes/               # Reusable PHP components
│       ├── header.php
│       └── footer.php
│
├── database/                    # Database files
│   ├── routa_database.sql      # 🌟 MAIN DATABASE (Use this!)
│   ├── database.sql            # Original schema (reference)
│   └── seed.sql                # Additional seed data
│
├── docs/                        # Documentation
│   ├── QUICK_START.md          # Quick start guide
│   ├── API_QUICK_GUIDE.md      # API documentation
│   ├── SETUP_CHECKLIST.md      # Setup instructions
│   ├── FILE_STRUCTURE.md       # This file structure
│   └── [Other guides...]       # Feature-specific docs
│
├── tests/                       # Test files
│   ├── test_*.php              # PHP test scripts
│   └── test_*.html             # HTML test pages
│
└── _old_migrations/            # Archived SQL migrations
    ├── add_*.sql               # Old addition scripts
    ├── update_*.sql            # Old update scripts
    └── [Other migrations...]   # No longer needed
```

## 🚀 Quick Setup

### 1. Database Setup (IMPORTANT!)

**Use the clean consolidated database:**

```bash
# In phpMyAdmin or MySQL terminal:
1. Open: database/routa_database.sql
2. Execute the entire file
3. Done! ✓
```

This single file includes:
- ✅ All tables with proper structure
- ✅ Sample users, drivers, admin
- ✅ All features (OAuth, OTP, ratings, tracking)
- ✅ Proper indexes and foreign keys
- ✅ Clean, commented structure

**Login Credentials:**
- **User:** juan@email.com / password
- **Driver:** pedro@driver.com / password
- **Admin:** admin@routa.com / admin123

### 2. Configure Database Connection

Edit `php/config.php`:
```php
$host = 'localhost';
$dbname = 'routa_db';
$username = 'root';
$password = '';  // Your MySQL password
```

### 3. Start XAMPP

```bash
1. Start Apache
2. Start MySQL
3. Open: http://localhost/Routa
```

## 📊 Database Schema Overview

### Core Tables
- **users** - Passenger/customer accounts
- **tricycle_drivers** - Driver accounts with location
- **admins** - Admin accounts
- **ride_history** - All bookings and trips

### Features
- **sessions** - User session management
- **otp_verifications** - Phone verification
- **driver_locations** - Real-time GPS tracking
- **driver_earnings** - Financial tracking
- **fare_settings** - Pricing configuration
- **ride_notifications** - Push notifications

### Views
- **active_rides** - Quick access to ongoing trips

## 🎯 Key Features

### User Features
- ✅ Book rides with pickup/destination
- ✅ Real-time driver tracking
- ✅ Rate drivers after trip
- ✅ View trip history
- ✅ OAuth login (Google/Facebook)
- ✅ Phone verification with OTP

### Driver Features
- ✅ Accept/reject ride requests
- ✅ Start/complete trips
- ✅ View earnings and statistics
- ✅ Online/offline status
- ✅ Trip history

### Admin Features
- ✅ View all bookings
- ✅ Assign drivers to pending bookings
- ✅ Monitor active rides
- ✅ View analytics and statistics
- ✅ Manage users and drivers
- ✅ Configure fare settings

## 📝 Important Files

### Must Configure
1. `php/config.php` - Database connection
2. `database/routa_database.sql` - Main database file

### Main Entry Points
- `index.php` - Homepage
- `login.php` - Universal login
- `userdashboard.php` - User interface
- `driver_dashboard.php` - Driver interface
- `admin.php` - Admin interface

### API Endpoints
- `php/book_ride.php` - Create booking
- `php/booking_api.php` - Booking management
- `php/send_otp.php` - Send OTP code
- `php/verify_otp.php` - Verify OTP

## 🗂️ File Organization

### Clean Structure Benefits
✅ All documentation in `/docs`
✅ All tests in `/tests`
✅ One main database file in `/database`
✅ Old migrations archived in `/_old_migrations`
✅ Easy to navigate and maintain

### What Got Cleaned Up
- 🗑️ 10+ SQL migration files → 1 clean database file
- 🗑️ 15+ MD documentation files → Organized in `/docs`
- 🗑️ Test files → Moved to `/tests`
- 🗑️ Debug files → Archived

## 🔧 Development

### Adding New Features
1. Database changes: Update `database/routa_database.sql`
2. Backend: Add PHP files in `php/`
3. Frontend: Add JS in `assets/js/`, CSS in `assets/css/`
4. Document: Add guide in `docs/`

### Testing
1. Use files in `/tests` folder
2. Or create new test files there
3. Never commit test files to production

## 📖 Documentation

All guides are in `/docs`:
- `QUICK_START.md` - Get started quickly
- `API_QUICK_GUIDE.md` - API reference
- `GOOGLE_OAUTH_SETUP.md` - OAuth setup
- `OTP_SETUP_GUIDE.md` - OTP configuration
- `COMPLETE_TRIP_FLOW.md` - Trip completion flow
- And more...

## 🆘 Troubleshooting

**Database won't import?**
- Use `database/routa_database.sql` (the clean one)
- Make sure MySQL is running
- Check for existing `routa_db` database (it will be dropped)

**Login doesn't work?**
- Verify database is imported
- Check `php/config.php` credentials
- Ensure Apache and MySQL are running

**Missing tables?**
- Re-import `database/routa_database.sql`
- Don't use old migration files from `_old_migrations/`

## 📦 Deployment Checklist

- [ ] Import `database/routa_database.sql`
- [ ] Configure `php/config.php`
- [ ] Set proper file permissions
- [ ] Enable error logging
- [ ] Test all login types (user/driver/admin)
- [ ] Test booking flow
- [ ] Verify OTP (if using)
- [ ] Test OAuth (if using)

## 🎓 Learning Resources

Check `/docs` folder for detailed guides on:
- Setting up OAuth
- Implementing OTP
- Understanding the booking flow
- API documentation
- Database schema details

## 📄 License

[Your License Here]

## 👥 Credits

Developed for tricycle booking services

---

**Version:** 2.0 (Cleaned & Organized)
**Last Updated:** November 2025

🎉 **Everything is now organized and clean!**
