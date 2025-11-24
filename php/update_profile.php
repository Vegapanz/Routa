<?php
header('Content-Type: application/json');

require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$userId = $_SESSION['user_id'];
$name = trim($data['name'] ?? '');
$phone = trim($data['phone'] ?? '');
$currentPassword = $data['currentPassword'] ?? '';
$newPassword = $data['newPassword'] ?? '';
$confirmPassword = $data['confirmPassword'] ?? '';

// Validate inputs
$errors = [];

// Validate name
if (empty($name)) {
    $errors[] = 'Name is required';
} elseif (strlen($name) < 2 || strlen($name) > 100) {
    $errors[] = 'Name must be between 2 and 100 characters';
}

// Validate phone (optional but must be valid if provided)
if (!empty($phone)) {
    // Remove spaces and dashes
    $phone = preg_replace('/[\s\-]/', '', $phone);
    
    // Check Philippine mobile format: STRICTLY 09XXXXXXXXX (11 digits)
    if (!preg_match('/^09\d{9}$/', $phone)) {
        $errors[] = 'Invalid phone number format. Must be exactly 11 digits starting with 09 (e.g., 09123456789)';
    }
    
    // Check if phone number changed and verify OTP was completed
    if (!empty($_POST['phone'])) {
        // Get user's current phone
        $stmt = $pdo->prepare("SELECT phone FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If phone changed, verify OTP was completed
        if ($currentUser && $currentUser['phone'] !== $phone) {
            // Normalize phone to +63 format for comparison
            $normalizedNewPhone = preg_match('/^09\d{9}$/', $phone) ? '+63' . substr($phone, 1) : $phone;
            
            // Check if phone was verified in session (check both formats)
            $phoneVerifiedInSession = false;
            if (isset($_SESSION['phone_verified'])) {
                $sessionPhone = $_SESSION['phone_verified'];
                $sessionPhoneOriginal = $_SESSION['phone_verified_original'] ?? '';
                
                // Check if it matches either format
                if ($sessionPhone === $normalizedNewPhone || 
                    $sessionPhone === $phone || 
                    $sessionPhoneOriginal === $phone) {
                    $phoneVerifiedInSession = true;
                }
            }
            
            if (!$phoneVerifiedInSession) {
                $errors[] = 'Phone number must be verified with OTP before updating';
            }
        }
    }
}

// If there are validation errors, return them
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Handle password change if requested
    $updatePassword = false;
    if (!empty($currentPassword) && !empty($newPassword)) {
        // Validate new password
        if (strlen($newPassword) < 6) {
            throw new Exception('New password must be at least 6 characters');
        }
        
        if ($newPassword !== $confirmPassword) {
            throw new Exception('New passwords do not match');
        }
        
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        if (!password_verify($currentPassword, $user['password'])) {
            throw new Exception('Current password is incorrect');
        }
        
        $updatePassword = true;
    } elseif (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
        // If any password field is filled but not all
        throw new Exception('Please fill all password fields to change your password');
    }
    
    // Update profile
    if ($updatePassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $hashedPassword, $userId]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $userId]);
    }
    
    $pdo->commit();
    
    // Update session data
    $_SESSION['user_name'] = $name;
    
    // Clear phone verification session after successful update
    if (isset($_SESSION['phone_verified'])) {
        unset($_SESSION['phone_verified']);
        unset($_SESSION['otp_verified']);
    }
    
    $response = [
        'success' => true,
        'message' => $updatePassword ? 'Profile and password updated successfully!' : 'Profile updated successfully!',
        'data' => [
            'name' => $name,
            'phone' => $phone
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
