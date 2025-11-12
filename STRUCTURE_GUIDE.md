# 🎯 Quick Reference - Clean File Structure

## ✅ What Changed

### Before (Messy):
```
Routa/
├── 15+ .md files scattered everywhere
├── 10+ .sql migration files
├── test_*.php and test_*.html mixed with production
├── Confusing structure
└── Hard to find anything
```

### After (Clean):
```
Routa/
├── Core files only in root
├── /docs - All documentation
├── /database - One clean SQL file
├── /tests - All test files
├── /_old_migrations - Archived (safe to delete)
└── README.md - Main guide
```

## 📂 Quick File Finder

### Need to...

**Setup the database?**
→ `database/routa_database.sql` (Use ONLY this file!)

**Configure database connection?**
→ `php/config.php`

**Read documentation?**
→ `docs/` folder (all .md files)

**Run tests?**
→ `tests/` folder

**Find old SQL migrations?**
→ `_old_migrations/` folder (archived, not needed)

## 🗄️ Database Files Explained

### ✅ USE THIS:
**`database/routa_database.sql`** - Complete consolidated database
- All tables with latest structure
- All features included (OAuth, OTP, ratings, etc.)
- Sample data for testing
- Proper indexes and foreign keys
- Clean, well-commented
- **This is the ONLY file you need!**

### ⚠️ REFERENCE ONLY:
**`database/database.sql`** - Original schema
- Kept for reference
- Don't use for new setups

**`database/seed.sql`** - Additional seed data
- Extra sample data if needed

### 🗑️ ARCHIVED (in `_old_migrations/`):
- `add_google_oauth.sql` - Merged into main file
- `add_facebook_oauth.sql` - Merged into main file
- `add_otp_verification.sql` - Merged into main file
- `fix_rating_columns.sql` - Merged into main file
- `update_ride_history.sql` - Merged into main file
- `upgrade_booking_system.sql` - Merged into main file
- `set_driver_locations.sql` - Merged into main file

**You can safely delete `_old_migrations/` folder!**

## 📝 Documentation Organization

All `.md` files now in `/docs`:

### Setup Guides:
- `QUICK_START.md` - Start here!
- `SETUP_CHECKLIST.md` - Step-by-step setup

### Feature Guides:
- `GOOGLE_OAUTH_SETUP.md` - Google login
- `FACEBOOK_OAUTH_SETUP.md` - Facebook login
- `OTP_SETUP_GUIDE.md` - Phone verification
- `COMPLETE_TRIP_FLOW.md` - Trip & rating system
- `UBER_LIKE_BOOKING_SYSTEM.md` - Booking flow

### API & Technical:
- `API_QUICK_GUIDE.md` - API endpoints
- `FILE_STRUCTURE.md` - File organization

### Troubleshooting:
- `VALIDATION_IMPROVEMENTS.md`
- `TESTING_GUIDE.md`

## 🧪 Tests Organization

All test files in `/tests`:
- `test_*.php` - PHP test scripts
- `test_*.html` - HTML test pages
- `fix_*.html` - Debug pages

**These are for development only - don't deploy to production!**

## 🚀 Fresh Install Steps

### 1. Import Database
```bash
# Option A: phpMyAdmin
1. Open phpMyAdmin
2. Go to Import tab
3. Choose: database/routa_database.sql
4. Click "Go"

# Option B: Command line
mysql -u root < database/routa_database.sql
```

### 2. Configure Connection
Edit `php/config.php`:
```php
$dbname = 'routa_db';
$username = 'root';
$password = '';  // Your password
```

### 3. Test It
```
http://localhost/Routa
```

**Login with:**
- User: `juan@email.com` / `password`
- Driver: `pedro@driver.com` / `password`
- Admin: `admin@routa.com` / `admin123`

## 🔄 Database Migration

### From Old Setup:

**If you have existing data:**
```sql
-- 1. Backup your current data
mysqldump -u root routa_db > backup.sql

-- 2. Import new clean database
mysql -u root < database/routa_database.sql

-- 3. Restore your data (if needed)
-- Import specific data from backup.sql
```

**Fresh install (no existing data):**
```sql
-- Just import the clean database
mysql -u root < database/routa_database.sql
```

## 📊 Database Tables Quick Reference

### User Management
- `users` - Passenger accounts
- `tricycle_drivers` - Driver accounts
- `admins` - Admin accounts
- `sessions` - Login sessions

### Booking & Trips
- `ride_history` - All rides/bookings (MAIN TABLE)
- `driver_locations` - GPS tracking
- `ride_notifications` - Notifications

### Financial
- `driver_earnings` - Driver payouts
- `fare_settings` - Pricing config

### Security
- `otp_verifications` - Phone OTP codes

### Utility
- `active_rides` - VIEW for quick queries

## 🎨 Frontend Structure

```
assets/
├── css/
│   ├── style.css           # Global styles
│   ├── auth.css            # Login/Register
│   ├── admin.css           # Admin dashboard
│   └── pages/              # Page-specific CSS
│
├── js/
│   ├── main.js             # Global JS
│   ├── dashboard.js        # User dashboard
│   ├── admin.js            # Admin dashboard
│   └── pages/              # Page-specific JS
│
└── images/                  # All images
```

## 🔌 Backend Structure

```
php/
├── config.php              # Database config (CONFIGURE THIS!)
├── login.php               # Login handler
├── register.php            # Registration handler
├── book_ride.php           # Booking API
├── booking_api.php         # Booking management
├── admin_functions.php     # Admin utilities
├── send_otp.php            # OTP sending
├── verify_otp.php          # OTP verification
└── includes/               # Reusable components
    ├── header.php
    └── footer.php
```

## 🎯 Main Entry Points

```
index.php               → Homepage/Landing
login.php               → Login page (all roles)
register.php            → User registration
userdashboard.php       → User interface
driver_dashboard.php    → Driver interface
admin.php               → Admin interface
```

## 🔍 Quick Search Guide

**Looking for...**

- 🔐 **Authentication code?** → `php/login.php`, `php/register.php`
- 📍 **Booking logic?** → `php/book_ride.php`, `php/booking_api.php`
- 👤 **User dashboard?** → `userdashboard.php`, `assets/js/dashboard.js`
- 🚗 **Driver dashboard?** → `driver_dashboard.php`, `assets/js/pages/driver-dashboard.js`
- 👨‍💼 **Admin panel?** → `admin.php`, `assets/js/admin.js`
- 🗄️ **Database structure?** → `database/routa_database.sql`
- 📖 **Documentation?** → `docs/` folder
- 🧪 **Test files?** → `tests/` folder

## ✨ Benefits of New Structure

✅ **One database file** instead of 10+ migrations
✅ **All docs organized** in `/docs` folder
✅ **Tests separated** from production code
✅ **Clear structure** - easy to find anything
✅ **Clean root** - only essential files
✅ **README** explains everything
✅ **Archived old files** instead of deleting (safe)
✅ **Professional organization** for team collaboration

## 🗑️ Safe to Delete

You can safely delete these folders if you want:
- `_old_migrations/` - Old SQL files (already merged)

Keep these:
- `docs/` - Documentation (reference)
- `tests/` - Test files (for development)
- `database/` - Database files (needed)

## 🎓 Learning Path

1. **Start:** `README.md` (main guide)
2. **Setup:** `docs/QUICK_START.md`
3. **Database:** Import `database/routa_database.sql`
4. **Features:** Read specific guides in `docs/`
5. **API:** `docs/API_QUICK_GUIDE.md`
6. **Testing:** Use files in `tests/`

---

**Everything is now clean and organized!** 🎉

**Questions?**
- Check `README.md` for overview
- Check `docs/` for detailed guides
- Check `database/routa_database.sql` comments for database info
