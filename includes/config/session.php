<?php
/**
 * Session Management
 * Routa - Tricycle Booking System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure session settings
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    
    session_start();
}

// Initialize session
if (!isset($_SESSION['initiated'])) {
    $_SESSION['initiated'] = true;
    $_SESSION['init_time'] = time();
}

// Keep session alive
if (isset($_SESSION['user_id'])) {
    $_SESSION['last_activity'] = time();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && 
           ($_SESSION['is_admin'] === true || $_SESSION['is_admin'] === 1 || $_SESSION['is_admin'] === '1');
}

/**
 * Redirect if not logged in
 */
function requireLogin($redirectTo = 'login.php') {
    if (!isLoggedIn()) {
        header('Location: ' . $redirectTo);
        exit();
    }
}

/**
 * Redirect if not admin
 */
function requireAdmin($redirectTo = 'index.php') {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: ' . $redirectTo);
        exit();
    }
}
?>
