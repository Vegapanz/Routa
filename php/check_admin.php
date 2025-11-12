<?php
require_once 'config.php';

// Create a test password hash
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Testing password hash:\n";
echo "Password: $password\n";
echo "Hash: $hash\n";

// Check if admin exists
try {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute(['admin@routa.com']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "\nAdmin found:\n";
        echo "ID: " . $admin['id'] . "\n";
        echo "Email: " . $admin['email'] . "\n";
        echo "Current password hash: " . $admin['password'] . "\n";
        
        // Test if the password 'admin123' works with current hash
        if (password_verify('admin123', $admin['password'])) {
            echo "\nCurrent password hash is valid for 'admin123'\n";
        } else {
            echo "\nCurrent password hash is NOT valid for 'admin123'\n";
            
            // Update the password
            $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE email = ?");
            if ($stmt->execute([$hash, 'admin@routa.com'])) {
                echo "Password has been updated with new hash\n";
            } else {
                echo "Failed to update password\n";
            }
        }
    } else {
        echo "\nAdmin not found. Creating admin account...\n";
        
        // Create the admin account
        $stmt = $pdo->prepare("INSERT INTO admins (name, email, password, role) VALUES (?, ?, ?, ?)");
        if ($stmt->execute(['Admin User', 'admin@routa.com', $hash, 'superadmin'])) {
            echo "Admin account created successfully\n";
        } else {
            echo "Failed to create admin account\n";
        }
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>