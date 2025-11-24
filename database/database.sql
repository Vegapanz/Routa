-- Drop (if present) and create the database with explicit charset/collation for MySQL
DROP DATABASE IF EXISTS routa_db;
CREATE DATABASE IF NOT EXISTS routa_db
    DEFAULT CHARACTER SET = utf8mb4
    DEFAULT COLLATE = utf8mb4_unicode_ci;
USE routa_db;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(25),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create sessions table for managing user sessions
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create ride_history table for tracking rides
CREATE TABLE IF NOT EXISTS ride_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    driver_id INT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    driver_name VARCHAR(100) NOT NULL,
    fare DECIMAL(10,2) NOT NULL,
    status ENUM('completed', 'cancelled', 'in_progress') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create tricycle_drivers table for driver accounts
CREATE TABLE IF NOT EXISTS tricycle_drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(25),
    plate_number VARCHAR(50) NOT NULL UNIQUE,
    tricycle_model VARCHAR(100),
    license_number VARCHAR(100),
    is_verified TINYINT(1) DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 5.00,
    current_lat DECIMAL(10,7) NULL,
    current_lng DECIMAL(10,7) NULL,
    status ENUM('available','on_trip','offline') DEFAULT 'offline',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create admins table for application administrators
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'superadmin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample user data
INSERT INTO users (name, email, password) VALUES
('Juan Dela Cruz', 'juan@email.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.'),
('Maria Garcia', 'maria@email.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.'),
('Carlos Mendoza', 'carlos@email.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.'),
('Anna Bautista', 'anna@email.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.'),
('Miguel Torres', 'miguel@email.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.');

-- Sample tricycle drivers data
INSERT INTO tricycle_drivers (name, email, password, phone, plate_number, tricycle_model, license_number, is_verified, status) VALUES
('Pedro Santos', 'pedro@driver.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.', '+63 912 345 6789', 'TRY-123', 'Honda TMX', 'LIC-001', 1, 'available'),
('Jose Reyes', 'jose@driver.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.', '+63 923 456 7890', 'TRY-456', 'Kawasaki', 'LIC-002', 1, 'available'),
('Antonio Cruz', 'antonio@driver.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.', '+63 934 567 8901', 'TRY-789', 'Yamaha', 'LIC-003', 1, 'offline'),
('Ricardo Lopez', 'ricardo@driver.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.', '+63 945 678 9012', 'TRY-321', 'Honda', 'LIC-004', 1, 'available'),
('Ramon Silva', 'ramon@driver.com', '$2y$10$EYuVge3ocsAxkfK4.npACeRvKjP9h3YeJUWM0QzUoUN0mQh.W87E.', '+63 956 789 0123', 'TRY-654', 'Kawasaki', 'LIC-005', 1, 'offline');

-- Sample ride history data
INSERT INTO ride_history (user_id, driver_id, pickup_location, destination, driver_name, fare, status, created_at) VALUES
(1, 1, 'SM City Manila', 'Divisoria', 'Pedro Santos', 85.00, 'completed', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(2, 2, 'Quiapo Church', 'Recto', 'Jose Reyes', 60.00, 'in_progress', DATE_SUB(NOW(), INTERVAL 45 MINUTE)),
(3, 3, 'UST', 'España', 'Antonio Cruz', 50.00, 'completed', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(4, NULL, 'LRT Carriedo', 'Binondo', '', 70.00, 'pending', DATE_SUB(NOW(), INTERVAL 15 MINUTE)),
(5, NULL, 'Manila City Hall', 'Intramuros', '', 65.00, 'pending', DATE_SUB(NOW(), INTERVAL 10 MINUTE));

-- Admin account with password: admin123 (hashed)
INSERT INTO admins (name, email, password, role)
VALUES ('Admin User', 'admin@routa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

-- Notes:
-- 1) Passwords in sample inserts are placeholders. Use a secure hashing function (password_hash in PHP) before inserting real accounts.
-- 2) If you prefer a single auth table with roles, you can instead add a `role` column to `users` and remove the separate `admins` table.

-- Add foreign key to ride_history.driver_id once tricycle_drivers table exists
-- This is done as an ALTER TABLE to avoid forward-reference errors during import
ALTER TABLE ride_history
    ADD INDEX idx_ride_driver_id (driver_id),
    ADD CONSTRAINT fk_ride_driver
        FOREIGN KEY (driver_id) REFERENCES tricycle_drivers(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE;