<?php
require_once 'config.php';

echo "<h2>OTP System Test</h2>";

// Check if otp_verifications table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'otp_verifications'");
    if ($stmt->rowCount() > 0) {
        echo "✓ otp_verifications table exists<br>";
        
        // Check table structure
        $stmt = $pdo->query("DESCRIBE otp_verifications");
        echo "<h3>Table Structure:</h3><ul>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<li>{$row['Field']} - {$row['Type']}</li>";
        }
        echo "</ul>";
    } else {
        echo "✗ otp_verifications table does NOT exist<br>";
        echo "<p><strong>Please run the SQL in add_otp_verification.sql</strong></p>";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Check if phone_verified column exists in users table
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'phone_verified'");
    if ($stmt->rowCount() > 0) {
        echo "<br>✓ phone_verified column exists in users table<br>";
    } else {
        echo "<br>✗ phone_verified column does NOT exist in users table<br>";
        echo "<p><strong>Please run the SQL in add_otp_verification.sql</strong></p>";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Test OTP generation
echo "<h3>Test OTP Generation:</h3>";
$testOtp = sprintf("%06d", mt_rand(0, 999999));
echo "Generated OTP: <strong>$testOtp</strong><br>";

// Test phone normalization
$testPhones = ['09123456789', '+639123456789', '9123456789', '639123456789'];
echo "<h3>Phone Normalization Test:</h3><ul>";
foreach ($testPhones as $phone) {
    $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);
    if (preg_match('/^09\d{9}$/', $cleaned)) {
        $normalized = '+63' . substr($cleaned, 1);
    } elseif (preg_match('/^9\d{9}$/', $cleaned)) {
        $normalized = '+63' . $cleaned;
    } elseif (preg_match('/^639\d{9}$/', $cleaned)) {
        $normalized = '+' . $cleaned;
    } else {
        $normalized = $cleaned;
    }
    echo "<li>$phone → $normalized</li>";
}
echo "</ul>";

echo "<h3>Session Status:</h3>";
echo "Session started: " . (session_status() === PHP_SESSION_ACTIVE ? "Yes" : "No") . "<br>";
echo "Session ID: " . session_id() . "<br>";
if (isset($_SESSION['otp_verified'])) {
    echo "OTP Verified: " . ($_SESSION['otp_verified'] ? "Yes" : "No") . "<br>";
    echo "Verified Phone: " . ($_SESSION['phone_verified'] ?? 'N/A') . "<br>";
} else {
    echo "No OTP verification in session<br>";
}
?>
