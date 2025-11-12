-- ============================================================
-- ROUTA - Tricycle Booking System Database
-- Complete Schema with All Features
-- Version: 2.0 (Consolidated & Clean)
-- ============================================================

-- Drop and recreate database
DROP DATABASE IF EXISTS routa_db;
CREATE DATABASE routa_db
    DEFAULT CHARACTER SET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;

USE routa_db;

-- ============================================================
-- CORE TABLES
-- ============================================================

-- Users table (passengers/customers)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(25) NULL,
    phone_verified TINYINT(1) DEFAULT 0,
    google_id VARCHAR(255) NULL UNIQUE,
    facebook_id VARCHAR(255) NULL UNIQUE,
    email_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_google_id (google_id),
    INDEX idx_facebook_id (facebook_id),
    INDEX idx_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Passenger/Customer accounts';

-- Tricycle drivers table
CREATE TABLE tricycle_drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(25) NULL,
    plate_number VARCHAR(50) NOT NULL UNIQUE,
    tricycle_model VARCHAR(100) NULL,
    license_number VARCHAR(100) NULL,
    is_verified TINYINT(1) DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 5.00,
    average_rating DECIMAL(3,2) DEFAULT 5.00,
    total_ratings INT DEFAULT 0,
    current_lat DECIMAL(10,7) NULL,
    current_lng DECIMAL(10,7) NULL,
    last_location_update TIMESTAMP NULL,
    status ENUM('available', 'on_trip', 'offline') DEFAULT 'offline',
    total_trips_completed INT DEFAULT 0,
    total_earnings DECIMAL(10,2) DEFAULT 0.00,
    acceptance_rate DECIMAL(5,2) DEFAULT 100.00,
    cancellation_rate DECIMAL(5,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_status_location (status, current_lat, current_lng),
    INDEX idx_rating (average_rating),
    INDEX idx_plate (plate_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tricycle driver accounts';

-- Admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'superadmin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Admin accounts';

-- ============================================================
-- BOOKING & RIDE MANAGEMENT
-- ============================================================

-- Ride history / Bookings table
CREATE TABLE ride_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    driver_id INT NULL,
    driver_name VARCHAR(100) NULL,
    pickup_location VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    pickup_lat DECIMAL(10,7) NULL,
    pickup_lng DECIMAL(10,7) NULL,
    dropoff_lat DECIMAL(10,7) NULL,
    dropoff_lng DECIMAL(10,7) NULL,
    fare DECIMAL(10,2) NOT NULL,
    distance VARCHAR(50) NULL,
    payment_method VARCHAR(50) DEFAULT 'cash',
    estimated_duration VARCHAR(50) NULL,
    status ENUM(
        'pending',           -- User created booking, waiting for admin
        'searching',         -- System searching for driver
        'driver_found',      -- Driver found, waiting for acceptance
        'confirmed',         -- Driver accepted, heading to pickup
        'arrived',           -- Driver arrived at pickup
        'in_progress',       -- Trip in progress
        'completed',         -- Trip completed
        'cancelled',         -- Cancelled by user/driver
        'rejected'           -- Rejected by admin
    ) NOT NULL DEFAULT 'pending',
    user_rating INT NULL CHECK (user_rating BETWEEN 1 AND 5),
    user_review TEXT NULL,
    driver_rating INT NULL CHECK (driver_rating BETWEEN 1 AND 5),
    driver_review TEXT NULL,
    driver_arrival_time TIMESTAMP NULL,
    trip_start_time TIMESTAMP NULL,
    trip_end_time TIMESTAMP NULL,
    cancelled_by ENUM('user', 'driver', 'admin', 'system') NULL,
    cancel_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES tricycle_drivers(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_driver_id (driver_id),
    INDEX idx_status (status),
    INDEX idx_status_driver (status, driver_id),
    INDEX idx_user_status (user_id, status),
    INDEX idx_created (created_at DESC),
    INDEX idx_user_rating (user_rating),
    INDEX idx_driver_rating (driver_rating),
    INDEX idx_completed_status (status, completed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='All ride bookings and history';

-- ============================================================
-- FINANCIAL MANAGEMENT
-- ============================================================

-- Fare settings table
CREATE TABLE fare_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    base_fare DECIMAL(10,2) DEFAULT 50.00,
    per_km_rate DECIMAL(10,2) DEFAULT 15.00,
    per_minute_rate DECIMAL(10,2) DEFAULT 2.00,
    minimum_fare DECIMAL(10,2) DEFAULT 50.00,
    booking_fee DECIMAL(10,2) DEFAULT 10.00,
    surge_multiplier DECIMAL(3,2) DEFAULT 1.00,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Pricing and fare configuration';

-- Driver earnings table
CREATE TABLE driver_earnings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    ride_id INT NOT NULL,
    gross_fare DECIMAL(10,2) NOT NULL,
    platform_commission DECIMAL(10,2) DEFAULT 0.00,
    net_earnings DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    payout_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES tricycle_drivers(id) ON DELETE CASCADE,
    FOREIGN KEY (ride_id) REFERENCES ride_history(id) ON DELETE CASCADE,
    INDEX idx_driver_id (driver_id),
    INDEX idx_ride_id (ride_id),
    INDEX idx_driver_status (driver_id, payment_status),
    INDEX idx_payout_date (payout_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Driver earnings and commission tracking';

-- ============================================================
-- AUTHENTICATION & SECURITY
-- ============================================================

-- User sessions table
CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_expires (user_id, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Active user sessions';

-- OTP verifications table
CREATE TABLE otp_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(25) NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_otp_code (otp_code),
    INDEX idx_phone_verified (phone, is_verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='OTP codes for phone verification';

-- ============================================================
-- REAL-TIME TRACKING & NOTIFICATIONS
-- ============================================================

-- Driver location tracking
CREATE TABLE driver_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    latitude DECIMAL(10,7) NOT NULL,
    longitude DECIMAL(10,7) NOT NULL,
    heading DECIMAL(5,2) NULL,
    speed DECIMAL(5,2) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES tricycle_drivers(id) ON DELETE CASCADE,
    INDEX idx_driver_id (driver_id),
    INDEX idx_driver_updated (driver_id, updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Real-time driver GPS locations';

-- Ride notifications
CREATE TABLE ride_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ride_id INT NOT NULL,
    recipient_id INT NOT NULL,
    recipient_type ENUM('user', 'driver', 'admin') NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ride_id) REFERENCES ride_history(id) ON DELETE CASCADE,
    INDEX idx_ride_id (ride_id),
    INDEX idx_recipient (recipient_id, recipient_type, is_read),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Push notifications for rides';

-- ============================================================
-- VIEWS FOR EASY QUERYING
-- ============================================================

-- Active rides view
CREATE OR REPLACE VIEW active_rides AS
SELECT 
    r.id,
    r.user_id,
    r.driver_id,
    r.pickup_location,
    r.destination,
    r.pickup_lat,
    r.pickup_lng,
    r.dropoff_lat,
    r.dropoff_lng,
    r.fare,
    r.status,
    r.payment_method,
    r.distance,
    r.created_at,
    r.updated_at,
    u.name as user_name,
    u.phone as user_phone,
    u.email as user_email,
    d.name as driver_name,
    d.phone as driver_phone,
    d.plate_number,
    d.current_lat as driver_lat,
    d.current_lng as driver_lng,
    d.rating as driver_rating
FROM ride_history r
LEFT JOIN users u ON r.user_id = u.id
LEFT JOIN tricycle_drivers d ON r.driver_id = d.id
WHERE r.status IN ('pending', 'searching', 'driver_found', 'confirmed', 'arrived', 'in_progress');

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Sample users (password for all: "password")
INSERT INTO users (name, email, password, phone, phone_verified, email_verified) VALUES
('Juan Dela Cruz', 'juan@email.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.', '+63 912 345 6789', 1, 1),
('Maria Garcia', 'maria@email.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.', '+63 923 456 7890', 1, 1),
('Carlos Mendoza', 'carlos@email.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.', '+63 934 567 8901', 1, 1),
('Anna Bautista', 'anna@email.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.', '+63 945 678 9012', 1, 1),
('Miguel Torres', 'miguel@email.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.', '+63 956 789 0123', 1, 1);

-- Sample drivers (password for all: "password")
INSERT INTO tricycle_drivers (name, email, password, phone, plate_number, tricycle_model, license_number, is_verified, status, rating, average_rating, current_lat, current_lng) VALUES
('Pedro Santos', 'pedro@driver.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.', '+63 917 111 2222', 'TRY-123', 'Honda TMX', 'LIC-001', 1, 'available', 4.80, 4.80, 14.5995, 120.9842),
('Jose Reyes', 'jose@driver.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.', '+63 917 222 3333', 'TRY-456', 'Kawasaki', 'LIC-002', 1, 'available', 4.90, 4.90, 14.6042, 120.9822),
('Antonio Cruz', 'antonio@driver.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.', '+63 917 333 4444', 'TRY-789', 'Yamaha', 'LIC-003', 1, 'offline', 4.70, 4.70, 14.5896, 120.9812),
('Ricardo Lopez', 'ricardo@driver.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.', '+63 917 444 5555', 'TRY-321', 'Honda', 'LIC-004', 1, 'available', 5.00, 5.00, 14.5933, 120.9771),
('Ramon Silva', 'ramon@driver.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.', '+63 917 555 6666', 'TRY-654', 'Kawasaki', 'LIC-005', 1, 'offline', 4.85, 4.85, 14.6091, 120.9895);

-- Admin account (email: admin@routa.com, password: admin123)
INSERT INTO admins (name, email, password, role) VALUES
('Admin User', 'admin@routa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

-- Sample ride history
INSERT INTO ride_history (user_id, driver_id, driver_name, pickup_location, destination, pickup_lat, pickup_lng, dropoff_lat, dropoff_lng, fare, distance, payment_method, status, user_rating, user_review, completed_at, created_at) VALUES
(1, 1, 'Pedro Santos', 'SM City Manila', 'Divisoria', 14.5995, 120.9842, 14.6042, 120.9822, 85.00, '2.5 km', 'cash', 'completed', 5, 'Excellent service! Very friendly driver.', DATE_SUB(NOW(), INTERVAL 2 HOUR), DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(2, 2, 'Jose Reyes', 'Quiapo Church', 'Recto Avenue', 14.5989, 120.9831, 14.6026, 120.9831, 60.00, '1.8 km', 'cash', 'completed', 4, 'Good driver, arrived on time.', DATE_SUB(NOW(), INTERVAL 5 HOUR), DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(3, 3, 'Antonio Cruz', 'UST Main Building', 'España Boulevard', 14.6091, 120.9895, 14.6052, 120.9921, 50.00, '1.2 km', 'cash', 'completed', 5, 'Very safe driver!', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 4, 'Ricardo Lopez', 'Binondo Church', 'Lucky Chinatown', 14.5975, 120.9739, 14.5965, 120.9785, 55.00, '1.5 km', 'cash', 'completed', 5, 'Highly recommended!', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(4, 1, 'Pedro Santos', 'Intramuros', 'Rizal Park', 14.5897, 120.9752, 14.5833, 120.9778, 50.00, '1.0 km', 'cash', 'completed', 4, 'Nice ride.', DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Sample pending bookings
INSERT INTO ride_history (user_id, pickup_location, destination, pickup_lat, pickup_lng, dropoff_lat, dropoff_lng, fare, distance, payment_method, status) VALUES
(2, 'Manila City Hall', 'San Miguel Church', 14.5919, 120.9799, 14.5901, 120.9734, 65.00, '1.8 km', 'cash', 'pending'),
(5, 'LRT Carriedo Station', 'Divisoria Mall', 14.5991, 120.9815, 14.6045, 120.9801, 70.00, '2.0 km', 'cash', 'pending');

-- Default fare settings
INSERT INTO fare_settings (base_fare, per_km_rate, per_minute_rate, minimum_fare, booking_fee, surge_multiplier, active) VALUES
(50.00, 15.00, 2.00, 50.00, 10.00, 1.00, 1);

-- Initialize driver locations
INSERT INTO driver_locations (driver_id, latitude, longitude, heading, speed)
SELECT id, current_lat, current_lng, 0, 0 
FROM tricycle_drivers 
WHERE current_lat IS NOT NULL AND current_lng IS NOT NULL;

-- ============================================================
-- COMPLETION MESSAGE
-- ============================================================

SELECT '✓ Database created successfully!' as status;
SELECT '✓ All tables created' as status;
SELECT '✓ Sample data inserted' as status;
SELECT '✓ Views and indexes created' as status;
SELECT '' as '';
SELECT 'Login Credentials:' as info;
SELECT '  User: juan@email.com / password' as user_login;
SELECT '  Driver: pedro@driver.com / password' as driver_login;
SELECT '  Admin: admin@routa.com / admin123' as admin_login;
SELECT '' as '';
SELECT CONCAT('Total Users: ', COUNT(*)) as count FROM users;
SELECT CONCAT('Total Drivers: ', COUNT(*)) as count FROM tricycle_drivers;
SELECT CONCAT('Total Rides: ', COUNT(*)) as count FROM ride_history;
SELECT CONCAT('Pending Bookings: ', COUNT(*)) as count FROM ride_history WHERE status = 'pending';
