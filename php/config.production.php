<?php
/**
 * PRODUCTION CONFIGURATION FILE FOR INFINITYFREE
 * 
 * Instructions:
 * 1. Fill in your InfinityFree database details below
 * 2. Update your domain URL
 * 3. Rename this file to config.php when uploading to server
 * 4. DO NOT upload your local config.php with localhost settings
 */

// Only set session configuration if session hasn't started
if (session_status() === PHP_SESSION_NONE) {
    // Fix session path for InfinityFree hosting
    ini_set('session.save_path', getcwd() . '/../sessions');
    if (!is_dir('../sessions')) {
        @mkdir('../sessions', 0777);
    }
    session_start();
}

// ========================================
// DATABASE CONFIGURATION - UPDATE THESE!
// ========================================

// Get these details from your InfinityFree cPanel
$host = 'sqlXXX.infinityfreeapp.com';  // Your MySQL hostname (e.g., sql123.infinityfreeapp.com)
$dbname = 'epiz_XXXXXXXX_routadb';     // Your full database name
$username = 'epiz_XXXXXXXX_routauser'; // Your database username
$password = 'YOUR_DATABASE_PASSWORD';   // Your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Set timezone to Philippines (UTC+8)
    date_default_timezone_set('Asia/Manila');
    $pdo->exec("SET time_zone = '+08:00'");
    
} catch(PDOException $e) {
    // Production error handling - don't show detailed errors to users
    error_log("Database Connection Error: " . $e->getMessage());
    die("We're experiencing technical difficulties. Please try again later.");
}

// ========================================
// PRODUCTION SETTINGS
// ========================================

// Disable error display in production (log errors instead)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set error log location
ini_set('error_log', __DIR__ . '/../error_log.txt');

// ========================================
// BASE URL CONFIGURATION - UPDATE THIS!
// ========================================

// Replace with your actual InfinityFree domain
// Examples: 
//   http://yoursite.rf.gd
//   http://yoursite.42web.io
//   http://yoursite.epizy.com
//   https://yourdomain.com (if using custom domain with SSL)
define('BASE_URL', 'http://yoursite.rf.gd');

// ========================================
// GOOGLE OAUTH CONFIGURATION
// ========================================

// Get these from: https://console.cloud.google.com
define('GOOGLE_CLIENT_ID', '941913119965-kld04cl0a3ugka2b0est8l022ji6b8ur.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');

// Update this with your actual domain
define('GOOGLE_REDIRECT_URI', BASE_URL . '/php/google-callback.php');

// ========================================
// FACEBOOK OAUTH CONFIGURATION
// ========================================

// Get these from: https://developers.facebook.com
define('FACEBOOK_APP_ID', 'YOUR_FACEBOOK_APP_ID');
define('FACEBOOK_APP_SECRET', 'YOUR_FACEBOOK_APP_SECRET');

// Update this with your actual domain
define('FACEBOOK_REDIRECT_URI', BASE_URL . '/php/facebook-callback.php');

// ========================================
// GOOGLE MAPS API KEY (if using)
// ========================================

// Get from: https://console.cloud.google.com
define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY');

// ========================================
// FILE UPLOAD SETTINGS
// ========================================

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB (InfinityFree limit)
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf']);

// ========================================
// SECURITY SETTINGS
// ========================================

// Password hashing options
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_COST', 12);

// Session security
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');

// ========================================
// APPLICATION SETTINGS
// ========================================

define('SITE_NAME', 'Routa');
define('SUPPORT_EMAIL', 'support@yoursite.rf.gd');
define('ADMIN_EMAIL', 'admin@yoursite.rf.gd');

// ========================================
// TIMEZONE SETTINGS
// ========================================

define('TIMEZONE', 'Asia/Manila');
date_default_timezone_set(TIMEZONE);

// ========================================
// HELPER FUNCTIONS
// ========================================

/**
 * Get full URL for a path
 */
function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . '/' . $path;
}

/**
 * Redirect to a URL
 */
function redirect($path) {
    header('Location: ' . url($path));
    exit;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user type
 */
function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

// ========================================
// END OF CONFIGURATION
// ========================================
?>
