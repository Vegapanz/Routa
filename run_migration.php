<?php
require_once 'php/config.php';

try {
    // Add password column to driver_applications table
    $sql = "ALTER TABLE driver_applications ADD COLUMN password VARCHAR(255) NOT NULL AFTER email";
    $pdo->exec($sql);
    echo "✅ Password column added successfully to driver_applications table\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "ℹ️ Password column already exists\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}
?>
