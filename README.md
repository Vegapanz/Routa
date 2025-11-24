# Routa - Tricycle Booking System

A modern, responsive web-based tricycle booking system built with PHP, MySQL, and Bootstrap.

## 🚀 Features

- **User Dashboard** - Book rides, view history, rate drivers
- **Driver Dashboard** - Accept rides, manage trips, view earnings
- **Admin Dashboard** - Manage users, drivers, bookings, and analytics
- **Real-time Updates** - AJAX polling for live booking status
- **Responsive Design** - Works on desktop, tablet, and mobile
- **Email Notifications** - PHPMailer integration
- **OAuth Login** - Google and Facebook authentication
- **Rating System** - Rate drivers after completed trips
- **Secure** - PDO prepared statements, password hashing, session management

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- Composer (for dependencies)

## 🛠️ Installation

### 1. Clone Repository

```bash
git clone https://github.com/yourusername/routa.git
cd routa
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Database Setup

1. Create a MySQL database
2. Import `database/routa_database.sql`
3. Configure database connection in `includes/config/database.php`

### 4. Configuration

1. Copy `includes/config/production.example.php` to `includes/config/production.php`
2. Update configuration:
   - Database credentials
   - Base URL
   - Email settings (PHPMailer)
   - OAuth credentials (Google/Facebook)

### 5. File Permissions

```bash
chmod 755 uploads/
chmod 755 logs/
```

## 📁 Project Structure

```
routa/
├── assets/           # CSS, JS, images
├── components/       # Reusable components
├── database/         # SQL files
├── includes/         # Configuration & functions
│   ├── config/      # Database, constants, session
│   ├── functions/   # Validation, helpers
│   └── handlers/    # Request handlers
├── php/             # Backend logic
├── uploads/         # User uploaded files
├── vendor/          # Composer dependencies
└── *.php            # Main pages
```

## 🔧 Configuration Files

- `includes/config/database.php` - Database connection
- `includes/config/constants.php` - Application constants
- `includes/config/session.php` - Session management
- `includes/config/production.php` - Production settings

## 📖 Usage

### For Users
1. Register/Login
2. Book a ride by entering pickup and destination
3. Wait for driver acceptance
4. Track ride status
5. Rate driver after completion

### For Drivers
1. Apply through driver application form
2. Login with approved credentials
3. View available ride requests
4. Accept and complete rides
5. View earnings and statistics

### For Admins
1. Login with admin credentials
2. Manage users and drivers
3. View all bookings and analytics
4. Approve/reject driver applications

## 🔒 Security Features

- Password hashing with `password_hash()`
- PDO prepared statements for SQL injection prevention
- Session security (httpOnly, SameSite)
- Input validation and sanitization
- CSRF protection
- File upload validation

## 📧 Email Configuration

Configure PHPMailer in `includes/config/constants.php`:
- SMTP settings
- Email credentials
- From address

## 🗺️ Google Maps Integration

1. Get API key from Google Cloud Console
2. Update `GOOGLE_MAPS_API_KEY` in `includes/config/constants.php`

## 🔑 OAuth Setup

### Google OAuth
1. Create project in Google Cloud Console
2. Configure OAuth consent screen
3. Update `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET`

### Facebook OAuth
1. Create app in Facebook Developers
2. Update `FACEBOOK_APP_ID` and `FACEBOOK_APP_SECRET`

## 🐛 Troubleshooting

- **Database connection errors**: Check credentials in `includes/config/database.php`
- **Email not sending**: Verify SMTP settings and credentials
- **Session issues**: Check PHP session configuration
- **Upload errors**: Verify folder permissions (755 or 777)

## 📝 License

MIT License - See LICENSE file

## 👨‍💻 Author

Your Name - [@Vegapanz](https://github.com/Vegapanz)

## 🤝 Contributing

Contributions welcome! Please open an issue or submit a pull request.

## 📞 Support

For issues and questions:
- GitHub Issues: https://github.com/yourusername/routa/issues
- Email: monroyojohnpatrick@gmail.com

