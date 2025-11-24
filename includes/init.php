<?php
/**
 * Application Bootstrap
 * Routa - Tricycle Booking System
 * 
 * This file initializes the application by loading all required configurations
 * Include this file at the top of every page that needs database/session access
 */

// Load constants first
require_once __DIR__ . '/config/constants.php';

// Load database connection
require_once __DIR__ . '/config/database.php';

// Load session management
require_once __DIR__ . '/config/session.php';

// Load utility functions (optional, only if needed)
// require_once __DIR__ . '/functions/auth.php';
// require_once __DIR__ . '/functions/validation.php';
// require_once __DIR__ . '/functions/email.php';
?>
