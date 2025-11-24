<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['fullName'] ?? $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $verifiedPhone = $_POST['verified_phone'] ?? ''; // Get the verified phone from hidden field

    if (empty($name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    // Validate password length
    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
        exit;
    }
    
    // Validate Philippine phone number (if provided)
    if (!empty($phone)) {
        $cleanedPhone = preg_replace('/[\s\-\(\)]/', '', $phone);
        $validPatterns = [
            '/^\+639\d{9}$/',   // +639XXXXXXXXX
            '/^639\d{9}$/',     // 639XXXXXXXXX
            '/^09\d{9}$/',      // 09XXXXXXXXX
            '/^9\d{9}$/'        // 9XXXXXXXXX
        ];
        
        $isValidPhone = false;
        foreach ($validPatterns as $pattern) {
            if (preg_match($pattern, $cleanedPhone)) {
                $isValidPhone = true;
                break;
            }
        }
        
        if (!$isValidPhone) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid Philippine mobile number']);
            exit;
        }
        
        // Normalize phone number to +63 format
        if (preg_match('/^09\d{9}$/', $cleanedPhone)) {
            $phone = '+63' . substr($cleanedPhone, 1);
        } elseif (preg_match('/^9\d{9}$/', $cleanedPhone)) {
            $phone = '+63' . $cleanedPhone;
        } elseif (preg_match('/^639\d{9}$/', $cleanedPhone)) {
            $phone = '+' . $cleanedPhone;
        } else {
            $phone = $cleanedPhone;
        }
    }

    try {
        // Check if phone was verified (stored in session)
        if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
            echo json_encode(['success' => false, 'message' => 'Please verify your phone number first']);
            exit;
        }
        
        // Use the verified phone from hidden field if available, otherwise normalize the submitted phone
        $phoneToUse = !empty($verifiedPhone) ? $verifiedPhone : $phone;
        
        // Function to normalize phone to +639XXXXXXXXX format
        function normalizePhoneNumber($phone) {
            // Remove all spaces, dashes, parentheses
            $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);
            
            // Convert to +639XXXXXXXXX format
            if (preg_match('/^\+639\d{9}$/', $cleaned)) {
                return $cleaned; // Already in correct format
            } elseif (preg_match('/^639\d{9}$/', $cleaned)) {
                return '+' . $cleaned; // Add +
            } elseif (preg_match('/^09\d{9}$/', $cleaned)) {
                return '+63' . substr($cleaned, 1); // Replace 0 with +63
            } elseif (preg_match('/^9\d{9}$/', $cleaned)) {
                return '+63' . $cleaned; // Add +63
            }
            
            return $cleaned; // Return as-is if no pattern matches
        }
        
        // Normalize both phones for comparison
        $normalizedSubmittedPhone = normalizePhoneNumber($phoneToUse);
        $normalizedVerifiedPhone = normalizePhoneNumber($_SESSION['phone_verified']);
        
        // Verify the phone matches the verified one
        if ($normalizedVerifiedPhone !== $normalizedSubmittedPhone) {
            echo json_encode([
                'success' => false, 
                'message' => 'Phone number does not match verified number',
                'debug' => [
                    'submitted' => $normalizedSubmittedPhone,
                    'verified' => $normalizedVerifiedPhone,
                    'original_submitted' => $phoneToUse,
                    'original_verified' => $_SESSION['phone_verified']
                ]
            ]);
            exit;
        }
        
        // Use the verified phone for database storage
        $phone = $_SESSION['phone_verified'];
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already registered']);
            exit;
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user with phone verified
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, phone_verified) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$name, $email, $hashedPassword, $phone]);

        // Clear OTP verification session
        unset($_SESSION['otp_verified']);
        unset($_SESSION['phone_verified']);

        echo json_encode(['success' => true, 'message' => 'Registration successful']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'An error occurred']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>