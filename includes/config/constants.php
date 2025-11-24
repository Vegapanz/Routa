<?php
/**
 * Application Constants
 * Routa - Tricycle Booking System
 */

// Timezone
date_default_timezone_set('Asia/Manila');

// Application URLs
define('BASE_URL', 'http://localhost/Routa');
define('ASSETS_URL', BASE_URL . '/assets');

// OAuth Configuration (Google)
define('GOOGLE_CLIENT_ID', '941913119965-kld04cl0a3ugka2b0est8l022ji6b8ur.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', BASE_URL . '/php/google-callback.php');

// Session Configuration
define('SESSION_LIFETIME', 86400); // 24 hours

// File Upload Configuration
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf']);
define('UPLOAD_DIR', __DIR__ . '/../../uploads/');

// Pagination
define('ITEMS_PER_PAGE', 20);
?>
