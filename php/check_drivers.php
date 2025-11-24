<?php
require_once 'config.php';

echo "<h2>Database Driver Check</h2>";

try {
    // Check if drivers table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'tricycle_drivers'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "<p style='color: red;'>❌ Table 'tricycle_drivers' does NOT exist!</p>";
        echo "<p>Please run database.sql to create the table.</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Table 'tricycle_drivers' exists</p>";
    
    // Check all drivers
    $stmt = $pdo->query("SELECT id, name, email, password, status FROM tricycle_drivers");
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($drivers)) {
        echo "<p style='color: red;'>❌ No drivers found in database!</p>";
        echo "<p>Please run database.sql to insert sample drivers.</p>";
        exit;
    }
    
    echo "<h3>Found " . count($drivers) . " drivers:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Password (Hashed)</th><th>Status</th><th>Test</th></tr>";
    
    foreach ($drivers as $driver) {
        $testPassword = 'password123';
        $matches = password_verify($testPassword, $driver['password']);
        $matchesPlain = ($testPassword === $driver['password']);
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($driver['id']) . "</td>";
        echo "<td>" . htmlspecialchars($driver['name']) . "</td>";
        echo "<td>" . htmlspecialchars($driver['email']) . "</td>";
        echo "<td style='font-size: 10px;'>" . substr($driver['password'], 0, 30) . "...</td>";
        echo "<td>" . htmlspecialchars($driver['status']) . "</td>";
        
        if ($matches) {
            echo "<td style='color: green;'>✅ Password hash matches 'password123'</td>";
        } elseif ($matchesPlain) {
            echo "<td style='color: orange;'>⚠️ Plain text 'password123'</td>";
        } else {
            echo "<td style='color: red;'>❌ Password doesn't match 'password123'</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // Test specific login
    echo "<h3>Test Login for pedro@driver.com:</h3>";
    $stmt = $pdo->prepare("SELECT * FROM tricycle_drivers WHERE email = ?");
    $stmt->execute(['pedro@driver.com']);
    $pedro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($pedro) {
        echo "<p>✅ Found Pedro Santos</p>";
        echo "<p>Email: " . htmlspecialchars($pedro['email']) . "</p>";
        echo "<p>Name: " . htmlspecialchars($pedro['name']) . "</p>";
        echo "<p>Password hash: " . substr($pedro['password'], 0, 50) . "...</p>";
        
        $testPass = 'password123';
        if (password_verify($testPass, $pedro['password'])) {
            echo "<p style='color: green; font-weight: bold;'>✅ 'password123' MATCHES the stored hash!</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ 'password123' does NOT match the stored hash!</p>";
            echo "<p>Trying to generate correct hash:</p>";
            $correctHash = password_hash($testPass, PASSWORD_DEFAULT);
            echo "<p>New hash: " . $correctHash . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Pedro Santos not found!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database Error: " . $e->getMessage() . "</p>";
}
?>
