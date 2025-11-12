<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'] ?? '';
    
    if (empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'Phone number is required']);
        exit;
    }
    
    // Validate Philippine phone number
    $cleanedPhone = preg_replace('/[\s\-\(\)]/', '', $phone);
    $validPatterns = [
        '/^\+639\d{9}$/',
        '/^639\d{9}$/',
        '/^09\d{9}$/',
        '/^9\d{9}$/'
    ];
    
    $isValidPhone = false;
    foreach ($validPatterns as $pattern) {
        if (preg_match($pattern, $cleanedPhone)) {
            $isValidPhone = true;
            break;
        }
    }
    
    if (!$isValidPhone) {
        echo json_encode(['success' => false, 'message' => 'Invalid Philippine mobile number']);
        exit;
    }
    
    // Normalize phone number
    if (preg_match('/^09\d{9}$/', $cleanedPhone)) {
        $normalizedPhone = '+63' . substr($cleanedPhone, 1);
    } elseif (preg_match('/^9\d{9}$/', $cleanedPhone)) {
        $normalizedPhone = '+63' . $cleanedPhone;
    } elseif (preg_match('/^639\d{9}$/', $cleanedPhone)) {
        $normalizedPhone = '+' . $cleanedPhone;
    } else {
        $normalizedPhone = $cleanedPhone;
    }
    
    try {
        // Check if table exists first
        $checkTable = $pdo->query("SHOW TABLES LIKE 'otp_verifications'");
        if ($checkTable->rowCount() === 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Database not configured. Please run add_otp_verification.sql',
                'error' => 'otp_verifications table does not exist'
            ]);
            exit;
        }
        
        // Generate 6-digit OTP
        $otp = sprintf("%06d", mt_rand(0, 999999));
        
        // Set expiration time (5 minutes from now)
        // Use time() + 300 to avoid timezone issues
        $expiresAt = date('Y-m-d H:i:s', time() + 300); // 300 seconds = 5 minutes
        
        // Delete any existing OTP for this phone
        $stmt = $pdo->prepare("DELETE FROM otp_verifications WHERE phone = ?");
        $stmt->execute([$normalizedPhone]);
        
        // Insert new OTP
        $stmt = $pdo->prepare("INSERT INTO otp_verifications (phone, otp_code, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$normalizedPhone, $otp, $expiresAt]);
        
        // Send SMS using Semaphore API
        $apiKey = 'YOUR_SEMAPHORE_API_KEY'; // Replace with your actual API key
        
        // For development/testing, you can skip actual SMS sending
        // Comment out the following block and uncomment the success response
        
        /*
        $message = "Your Routa verification code is: $otp. Valid for 5 minutes.";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.semaphore.co/api/v4/messages');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'apikey' => $apiKey,
            'number' => $normalizedPhone,
            'message' => $message,
            'sendername' => 'Routa'
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            echo json_encode(['success' => false, 'message' => 'Failed to send OTP. Please try again.']);
            exit;
        }
        */
        
        // For development: Return OTP in response (REMOVE IN PRODUCTION!)
        echo json_encode([
            'success' => true, 
            'message' => 'OTP sent successfully',
            'debug_otp' => $otp, // REMOVE THIS IN PRODUCTION!
            'phone' => $normalizedPhone,
            'debug_info' => [ // REMOVE IN PRODUCTION
                'current_time' => date('Y-m-d H:i:s'),
                'expires_at' => $expiresAt,
                'server_time' => time(),
                'expiry_timestamp' => time() + 300
            ]
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Database error occurred',
            'error' => $e->getMessage() // Shows the actual error for debugging
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
