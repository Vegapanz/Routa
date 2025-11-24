<?php
/**
 * Test script to verify registration functionality
 * This script checks the database connection and table structure
 */

require_once 'config.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test connection
    echo "✓ Database connection successful<br>";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Users table exists<br>";
        
        // Check table structure
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h3>Users table structure:</h3>";
        echo "<ul>";
        foreach ($columns as $column) {
            echo "<li>$column</li>";
        }
        echo "</ul>";
        
        // Check for required columns
        $requiredColumns = ['id', 'name', 'email', 'password', 'phone'];
        $missingColumns = array_diff($requiredColumns, $columns);
        
        if (empty($missingColumns)) {
            echo "✓ All required columns are present<br>";
        } else {
            echo "✗ Missing columns: " . implode(', ', $missingColumns) . "<br>";
            echo "<p><strong>Please run the update_users_table.sql script to add missing columns.</strong></p>";
        }
        
    } else {
        echo "✗ Users table does not exist<br>";
        echo "<p><strong>Please run the database.sql script to create the database structure.</strong></p>";
    }
    
    // Test password hashing
    echo "<h3>Password Hashing Test:</h3>";
    $testPassword = "test123456";
    $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
    echo "Original password: $testPassword<br>";
    echo "Hashed password: $hashedPassword<br>";
    
    if (password_verify($testPassword, $hashedPassword)) {
        echo "✓ Password hashing and verification working correctly<br>";
    } else {
        echo "✗ Password verification failed<br>";
    }
    
} catch(PDOException $e) {
    echo "✗ Error: " . $e->getMessage();
}
?>
