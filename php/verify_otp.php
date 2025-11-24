<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'] ?? '';
    $otp = $_POST['otp'] ?? '';
    
    if (empty($phone) || empty($otp)) {
        echo json_encode(['success' => false, 'message' => 'Phone number and OTP are required']);
        exit;
    }
    
    try {
        // Check if table exists first
        $checkTable = $pdo->query("SHOW TABLES LIKE 'otp_verifications'");
        if ($checkTable->rowCount() === 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Database not configured. Please run add_otp_verification.sql'
            ]);
            exit;
        }
        
        // Normalize phone number to match what was stored
        $normalizedPhone = $phone;
        if (preg_match('/^09\d{9}$/', $phone)) {
            $normalizedPhone = '+63' . substr($phone, 1);
        } elseif (preg_match('/^9\d{9}$/', $phone)) {
            $normalizedPhone = '+63' . $phone;
        } elseif (preg_match('/^639\d{9}$/', $phone)) {
            $normalizedPhone = '+' . $phone;
        }
        
        // Check if OTP exists and is not expired
        // Get current timestamp
        $currentTime = date('Y-m-d H:i:s');
        
        $stmt = $pdo->prepare("
            SELECT *, 
                   expires_at as expiry,
                   ? as current_check_time,
                   (expires_at > ?) as is_valid
            FROM otp_verifications 
            WHERE phone = ? AND otp_code = ? AND is_verified = 0
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$currentTime, $currentTime, $normalizedPhone, $otp]);
        $otpRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$otpRecord || $otpRecord['is_valid'] == 0) {
            // Check if OTP exists but is expired or already used
            if ($otpRecord) {
                if ($otpRecord['is_verified'] == 1) {
                    echo json_encode(['success' => false, 'message' => 'This OTP has already been used']);
                } else {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'OTP has expired. Please request a new one',
                        'debug' => [
                            'current_time' => $otpRecord['current_check_time'],
                            'expiry_time' => $otpRecord['expiry']
                        ]
                    ]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid OTP code']);
            }
            exit;
        }
        
        // Mark OTP as verified
        $stmt = $pdo->prepare("UPDATE otp_verifications SET is_verified = 1 WHERE id = ?");
        $stmt->execute([$otpRecord['id']]);
        
        // Store verification status in session (store both formats for compatibility)
        $_SESSION['phone_verified'] = $normalizedPhone;
        $_SESSION['phone_verified_original'] = $phone;
        $_SESSION['otp_verified'] = true;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Phone verified successfully',
            'phone' => $normalizedPhone, // Return normalized phone to JavaScript
            'debug' => [
                'phone_sent' => $phone,
                'phone_normalized' => $normalizedPhone,
                'phone_in_db' => $otpRecord['phone']
            ]
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Database error occurred',
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
