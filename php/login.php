<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log("Login attempt started");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    error_log("Login attempt - Email: " . $email . ", Password: " . $password);

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
        exit;
    }

    try {
        // Check admin table first
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            error_log("Admin found, comparing passwords...");
            error_log("Stored password in DB: " . $admin['password']);
            error_log("Entered password: " . $password);
            
            // Use password_verify for hashed admin password
            if (password_verify($password, $admin['password'])) {
                error_log("Admin password match successful");
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['user_email'] = $admin['email'];
                $_SESSION['is_admin'] = true;
                $_SESSION['is_driver'] = false;
                $_SESSION['admin_role'] = $admin['role'];
                
                echo json_encode([
                    'success' => true, 
                    'redirect' => 'admin.php'
                ]);
                exit;
            }
        }

        // Check tricycle_drivers table
        $stmt = $pdo->prepare("SELECT * FROM tricycle_drivers WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($driver) {
            error_log("Driver found, comparing passwords...");
            error_log("Stored password in DB: " . $driver['password']);
            error_log("Entered password: " . $password);
            error_log("Driver status value: " . ($driver['status'] ?? 'NULL/NOT SET'));
            error_log("Status isset: " . (isset($driver['status']) ? 'YES' : 'NO'));
            error_log("Status equals archived: " . (($driver['status'] ?? '') === 'archived' ? 'YES' : 'NO'));
            
            // Check if driver is archived
            if (isset($driver['status']) && $driver['status'] === 'archived') {
                error_log("❌ BLOCKING LOGIN - Driver account is archived");
                echo json_encode([
                    'success' => false,
                    'message' => 'This account has been deactivated. Please contact support.'
                ]);
                exit;
            }
            
            // Check both hashed and plain text passwords
            if (password_verify($password, $driver['password']) || $password === $driver['password']) {
                error_log("Driver password match successful");
                $_SESSION['user_id'] = $driver['id'];
                $_SESSION['user_email'] = $driver['email'];
                $_SESSION['user_name'] = $driver['name'];
                $_SESSION['is_driver'] = true;
                $_SESSION['is_admin'] = false;
                
                echo json_encode([
                    'success' => true, 
                    'redirect' => 'driver_dashboard.php'
                ]);
                exit;
            }
        }

        // If not driver, check users table
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            error_log("User found, comparing passwords...");
            error_log("Stored password in DB: " . $user['password']);
            error_log("Entered password: " . $password);
            
            // Check if user is archived
            if (isset($user['status']) && $user['status'] === 'archived') {
                error_log("User account is archived");
                echo json_encode([
                    'success' => false,
                    'message' => 'This account has been deactivated. Please contact support.'
                ]);
                exit;
            }
            
            // Check both hashed and plain text passwords
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                error_log("Password match successful");
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['is_admin'] = false;
                $_SESSION['is_driver'] = false;
                
                echo json_encode([
                    'success' => true, 
                    'redirect' => 'userdashboard.php'
                ]);
                exit;
            }
        }
        
        // If we get here, authentication failed
        error_log("Authentication failed - no matching credentials found");
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid email or password'
        ]);
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'An error occurred. Please try again.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method'
    ]);
}
?>