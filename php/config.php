<?php
// Only set session configuration if session hasn't started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$host = 'localhost';
$dbname = 'routa_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set timezone to Philippines (UTC+8) to avoid timezone issues
    // This ensures PHP and MySQL use the same timezone
    date_default_timezone_set('Asia/Manila');
    $pdo->exec("SET time_zone = '+08:00'");
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', '941913119965-kld04cl0a3ugka2b0est8l022ji6b8ur.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost/Routa/php/google-callback.php');

// Facebook OAuth Configuration
define('FACEBOOK_APP_ID', 'YOUR_FACEBOOK_APP_ID');
define('FACEBOOK_APP_SECRET', 'YOUR_FACEBOOK_APP_SECRET');
define('FACEBOOK_REDIRECT_URI', 'http://localhost/Routa/php/facebook-callback.php');

// Base URL
define('BASE_URL', 'http://localhost/Routa');
?>