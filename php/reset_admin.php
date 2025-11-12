<?php
require_once 'config.php';

try {
    // Hash the password 'admin123'
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Update admin password
    $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE email = 'admin@routa.com'");
    $result = $stmt->execute([$password]);
    
    if ($result) {
        echo "Admin password has been reset successfully!";
    } else {
        echo "Failed to reset admin password.";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>