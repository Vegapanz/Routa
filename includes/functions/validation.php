<?php
/**
 * Validation Functions
 * Routa - Tricycle Booking System
 */

/**
 * Sanitize input
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Philippine format)
 */
function isValidPhone($phone) {
    // Remove spaces and special characters
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    
    // Check if it matches Philippine phone patterns
    $patterns = [
        '/^09[0-9]{9}$/',           // 09XXXXXXXXX
        '/^\+639[0-9]{9}$/',        // +639XXXXXXXXX
        '/^639[0-9]{9}$/',          // 639XXXXXXXXX
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $phone)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Validate password strength
 */
function isValidPassword($password) {
    // At least 8 characters
    return strlen($password) >= 8;
}

/**
 * Validate file upload
 */
function isValidFile($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Check file size
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return false;
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    return in_array($mimeType, ALLOWED_FILE_TYPES);
}
?>
