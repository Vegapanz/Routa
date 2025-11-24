<?php
// Configure session settings for better reliability (only if not already configured)
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie parameters BEFORE starting session
    ini_set('session.cookie_lifetime', 86400); // 24 hours
    ini_set('session.gc_maxlifetime', 86400);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', 0); // Disable strict mode to allow session reuse
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    
    // Use default session save path (more reliable)
    // Don't set custom path unless necessary as it can cause permission issues
    
    session_start();
    
    // Verify session started successfully
    if (session_status() !== PHP_SESSION_ACTIVE) {
        error_log("CRITICAL: Session failed to start!");
    }
}

// Mark session as initiated only once - avoid regeneration during normal operations
if (!isset($_SESSION['initiated'])) {
    $_SESSION['initiated'] = true;
    $_SESSION['init_time'] = time();
}

// Keep session alive
if (isset($_SESSION['user_id'])) {
    $_SESSION['last_activity'] = time();
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
