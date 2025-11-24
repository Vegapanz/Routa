<?php
/**
 * Legacy Config File - Backward Compatibility
 * 
 * This file maintains backward compatibility for existing code.
 * New code should use: require_once 'includes/init.php';
 * 
 * For production deployment, you can either:
 * 1. Keep this file for backward compatibility (recommended)
 * 2. Update all files to use new structure and remove this
 */

// Load new modular structure
require_once __DIR__ . '/../includes/init.php';

// Legacy variable support (if needed)
// $host, $dbname, etc. are no longer exposed
// Use $pdo directly which is already initialized
?>