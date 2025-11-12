<?php
require_once 'config.php';

echo "<h2>Fix Driver Passwords</h2>";

try {
    // Update all driver passwords to 'password123'
    $newPassword = 'password123';
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $drivers = [
        'pedro@driver.com' => 'Pedro Santos',
        'jose@driver.com' => 'Jose Reyes',
        'antonio@driver.com' => 'Antonio Cruz',
        'ricardo@driver.com' => 'Ricardo Lopez',
        'ramon@driver.com' => 'Ramon Silva'
    ];
    
    echo "<h3>Updating driver passwords...</h3>";
    
    foreach ($drivers as $email => $name) {
        $stmt = $pdo->prepare("UPDATE tricycle_drivers SET password = ? WHERE email = ?");
        $result = $stmt->execute([$hashedPassword, $email]);
        
        if ($result) {
            echo "<p style='color: green;'>✅ Updated password for " . htmlspecialchars($name) . " (" . htmlspecialchars($email) . ")</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to update " . htmlspecialchars($name) . "</p>";
        }
    }
    
    echo "<h3>Verification:</h3>";
    
    // Verify the updates
    $stmt = $pdo->query("SELECT name, email, password FROM tricycle_drivers");
    $allDrivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($allDrivers as $driver) {
        $matches = password_verify('password123', $driver['password']);
        if ($matches) {
            echo "<p style='color: green;'>✅ " . htmlspecialchars($driver['name']) . " - password verified!</p>";
        } else {
            echo "<p style='color: red;'>❌ " . htmlspecialchars($driver['name']) . " - password verification failed!</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>✅ All Done!</h3>";
    echo "<p>All driver passwords have been set to: <strong>password123</strong></p>";
    echo "<p><a href='../test_driver_login.html'>Test Driver Login</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database Error: " . $e->getMessage() . "</p>";
}
?>
