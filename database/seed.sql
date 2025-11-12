-- Use the database
USE routa_db;

-- Disable foreign key checks temporarily to avoid import order issues
SET FOREIGN_KEY_CHECKS = 0;

-- Clear existing data
TRUNCATE TABLE ride_history;
TRUNCATE TABLE sessions;
TRUNCATE TABLE tricycle_drivers;
TRUNCATE TABLE users;
TRUNCATE TABLE admins;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Seed Users
INSERT INTO users (id, name, email, password, created_at) VALUES
(1, 'Juan Dela Cruz', 'juan@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', DATE_SUB(NOW(), INTERVAL 2 MONTH)),
(2, 'Maria Garcia', 'maria@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', DATE_SUB(NOW(), INTERVAL 1 MONTH)),
(3, 'Carlos Mendoza', 'carlos@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', DATE_SUB(NOW(), INTERVAL 25 DAY)),
(4, 'Anna Bautista', 'anna@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(5, 'Miguel Torres', 'miguel@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', DATE_SUB(NOW(), INTERVAL 5 DAY));

-- Seed Tricycle Drivers
INSERT INTO tricycle_drivers (id, name, email, password, phone, plate_number, tricycle_model, license_number, is_verified, rating, status, created_at) VALUES
(1, 'Pedro Santos', 'pedro@driver.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+63 912 345 6789', 'TRY-123', 'Honda TMX', 'LIC-001', 1, 4.8, 'available', DATE_SUB(NOW(), INTERVAL 3 MONTH)),
(2, 'Jose Reyes', 'jose@driver.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+63 923 456 7890', 'TRY-456', 'Kawasaki Barako', 'LIC-002', 1, 4.6, 'available', DATE_SUB(NOW(), INTERVAL 2 MONTH)),
(3, 'Antonio Cruz', 'antonio@driver.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+63 934 567 8901', 'TRY-789', 'Yamaha RS100', 'LIC-003', 1, 4.9, 'offline', DATE_SUB(NOW(), INTERVAL 45 DAY)),
(4, 'Ricardo Lopez', 'ricardo@driver.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+63 945 678 9012', 'TRY-321', 'Honda TMX Supreme', 'LIC-004', 1, 4.7, 'available', DATE_SUB(NOW(), INTERVAL 30 DAY)),
(5, 'Ramon Silva', 'ramon@driver.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+63 956 789 0123', 'TRY-654', 'Kawasaki CT100', 'LIC-005', 1, 4.5, 'offline', DATE_SUB(NOW(), INTERVAL 20 DAY));

-- Seed Recent Completed Rides (Last 24 hours)
INSERT INTO ride_history (user_id, driver_id, pickup_location, destination, driver_name, fare, status, created_at, completed_at) VALUES
(1, 1, 'SM City Manila', 'Divisoria', 'Pedro Santos', 85.00, 'completed', DATE_SUB(NOW(), INTERVAL 1 HOUR), DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(2, 2, 'Quiapo Church', 'Recto', 'Jose Reyes', 60.00, 'in_progress', DATE_SUB(NOW(), INTERVAL 45 MINUTE), NULL),
(3, 3, 'UST', 'España', 'Antonio Cruz', 50.00, 'completed', DATE_SUB(NOW(), INTERVAL 2 HOUR), DATE_SUB(NOW(), INTERVAL 1 HOUR 30 MINUTE));

-- Seed Pending Bookings
INSERT INTO ride_history (user_id, driver_id, pickup_location, destination, driver_name, fare, status, created_at) VALUES
(4, NULL, 'LRT Carriedo', 'Binondo', '', 70.00, 'pending', DATE_SUB(NOW(), INTERVAL 15 MINUTE)),
(5, NULL, 'Manila City Hall', 'Intramuros', '', 65.00, 'pending', DATE_SUB(NOW(), INTERVAL 10 MINUTE));

-- Seed Historical Rides (Past week)
INSERT INTO ride_history (user_id, driver_id, pickup_location, destination, driver_name, fare, status, created_at, completed_at) VALUES
(1, 2, 'Robinsons Place Manila', 'Intramuros', 'Jose Reyes', 75.00, 'completed', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 1, 'Manila City Hall', 'Quiapo Church', 'Pedro Santos', 55.00, 'completed', DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 4, 'SM San Lazaro', 'Bambang', 'Ricardo Lopez', 90.00, 'completed', DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),
(4, 3, 'Binondo Church', 'Lucky Chinatown', 'Antonio Cruz', 45.00, 'completed', DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(5, 5, 'Divisoria Mall', 'Tutuban Center', 'Ramon Silva', 40.00, 'completed', DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 6 DAY));

-- Seed Admin Account
INSERT INTO admins (id, name, email, password, role, created_at) VALUES
(1, 'Admin User', 'admin@routa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', DATE_SUB(NOW(), INTERVAL 6 MONTH));

-- Add some cancelled rides for statistics
INSERT INTO ride_history (user_id, driver_id, pickup_location, destination, driver_name, fare, status, created_at) VALUES
(2, NULL, 'Lawton', 'Quiapo', '', 55.00, 'cancelled', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, NULL, 'Recto', 'Divisoria', '', 45.00, 'cancelled', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(4, NULL, 'Binondo', 'Escolta', '', 50.00, 'cancelled', DATE_SUB(NOW(), INTERVAL 3 DAY));

/*
Test Account Credentials:
- Admin: admin@routa.com / password123
- Users: any email (e.g., juan@email.com) / password123
- Drivers: any driver email (e.g., pedro@driver.com) / password123

Note: All passwords are hashed versions of 'password123' for testing
*/