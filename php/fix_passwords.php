<?php
require_once 'config.php';

echo "=== FIXING PASSWORDS ===\n\n";

try {
    // Generate proper password hashes
    $userPasswordHash = password_hash('password', PASSWORD_DEFAULT);
    $adminPasswordHash = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Update admin password
    $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE email = 'admin@routa.com'");
    $stmt->execute([$adminPasswordHash]);
    echo "✓ Admin password updated (admin123)\n";
    
    // Update all user passwords
    $stmt = $pdo->prepare("UPDATE users SET password = ?");
    $stmt->execute([$userPasswordHash]);
    $affected = $stmt->rowCount();
    echo "✓ Updated $affected user passwords (password)\n";
    
    // Update all driver passwords
    $stmt = $pdo->prepare("UPDATE tricycle_drivers SET password = ?");
    $stmt->execute([$userPasswordHash]);
    $affected = $stmt->rowCount();
    echo "✓ Updated $affected driver passwords (password)\n";
    
    echo "\n=== VERIFICATION ===\n";
    
    // Verify admin
    $stmt = $pdo->prepare("SELECT password FROM admins WHERE email = 'admin@routa.com'");
    $stmt->execute();
    $hash = $stmt->fetchColumn();
    echo "Admin (admin123): " . (password_verify('admin123', $hash) ? "✓ WORKS" : "✗ FAILED") . "\n";
    
    // Verify a user
    $stmt = $pdo->prepare("SELECT password FROM users WHERE email = 'juan@email.com'");
    $stmt->execute();
    $hash = $stmt->fetchColumn();
    echo "User (password): " . (password_verify('password', $hash) ? "✓ WORKS" : "✗ FAILED") . "\n";
    
    // Verify a driver
    $stmt = $pdo->prepare("SELECT password FROM tricycle_drivers WHERE email = 'pedro@driver.com'");
    $stmt->execute();
    $hash = $stmt->fetchColumn();
    echo "Driver (password): " . (password_verify('password', $hash) ? "✓ WORKS" : "✗ FAILED") . "\n";
    
    echo "\n=== LOGIN CREDENTIALS ===\n";
    echo "Admin: admin@routa.com / admin123\n";
    echo "User: juan@email.com / password\n";
    echo "Driver: pedro@driver.com / password\n";
    echo "\n✓ All passwords fixed! Login should work now.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
