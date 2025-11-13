<?php
// Script to create driver_applications table
require_once 'config.php';

try {
    $sql = file_get_contents('../database/create_driver_applications_table.sql');
    
    // Remove the USE database statement as we're already connected
    $sql = preg_replace('/USE.*?;/', '', $sql);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Driver applications table created successfully!'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error creating table: ' . $e->getMessage()
    ]);
}
?>
