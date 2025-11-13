<?php
require_once 'php/config.php';

try {
    // Add tricycle_number column
    $sql = "ALTER TABLE tricycle_drivers ADD COLUMN tricycle_number VARCHAR(20) UNIQUE AFTER plate_number";
    $pdo->exec($sql);
    echo "✅ tricycle_number column added successfully\n";
    
    // Add index
    $sql = "CREATE INDEX idx_tricycle_number ON tricycle_drivers(tricycle_number)";
    $pdo->exec($sql);
    echo "✅ Index created successfully\n";
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "ℹ️ tricycle_number column already exists\n";
    } elseif (strpos($e->getMessage(), 'Duplicate key name') !== false) {
        echo "ℹ️ Index already exists\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

// Update existing drivers with tricycle numbers if they don't have one
try {
    $stmt = $pdo->query("SELECT id FROM tricycle_drivers WHERE tricycle_number IS NULL ORDER BY id");
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($drivers) > 0) {
        echo "\n📝 Updating " . count($drivers) . " existing drivers with tricycle numbers...\n";
        
        $counter = 1;
        foreach ($drivers as $driver) {
            $tricycleNumber = 'TRY-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
            $updateStmt = $pdo->prepare("UPDATE tricycle_drivers SET tricycle_number = ? WHERE id = ?");
            $updateStmt->execute([$tricycleNumber, $driver['id']]);
            echo "   Driver ID {$driver['id']} -> $tricycleNumber\n";
            $counter++;
        }
        
        echo "✅ All existing drivers updated with tricycle numbers\n";
    } else {
        echo "ℹ️ No drivers need tricycle number updates\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error updating drivers: " . $e->getMessage() . "\n";
}

echo "\n✨ Migration complete!\n";
?>
