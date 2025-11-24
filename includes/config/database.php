<?php
/**
 * Database Configuration
 * Routa - Tricycle Booking System
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'routa_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create PDO connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    // Set timezone for MySQL
    $pdo->exec("SET time_zone = '+08:00'");
    
} catch(PDOException $e) {
    // Log error (in production, don't display details)
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
?>
