<?php
/**
 * Production Environment Configuration
 * 
 * Copy this file to includes/config/production.php
 * Enable it by uncommenting the require line in init.php
 */

// Error reporting (OFF for production)
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Log errors instead of displaying them
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

// Security headers
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Remove PHP version from headers
header_remove("X-Powered-By");

// Production database configuration override
// Uncomment and update with production values
/*
define('DB_HOST', 'your-production-host');
define('DB_NAME', 'your-production-db');
define('DB_USER', 'your-production-user');
define('DB_PASS', 'your-production-pass');
*/

// Production URL override
// Uncomment and update with production URL
/*
define('BASE_URL', 'https://your-domain.com');
*/

// Force HTTPS in production
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    if (!headers_sent()) {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit();
    }
}
?>
