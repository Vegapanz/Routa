<?php
header('Content-Type: application/json');

require_once 'config.php';

try {
    $conn = new mysqli('localhost', 'root', '', 'routa_db');
    
    // Get pending notifications
    $result = $conn->query("SELECT * FROM realtime_notifications WHERE status = 'pending' ORDER BY created_at DESC LIMIT 10");
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($notifications),
        'notifications' => $notifications
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
