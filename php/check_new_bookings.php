<?php
/**
 * Check for new pending bookings for the driver
 * Used for polling mechanism
 */

session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Check if driver is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_driver']) || !$_SESSION['is_driver']) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$driver_id = $_SESSION['user_id'];

try {
    // Count pending bookings (driver_found status means assigned but not yet accepted)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as pending_count 
        FROM ride_history 
        WHERE driver_id = ? AND status = 'driver_found'
    ");
    $stmt->execute([$driver_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get the pending bookings details
    $stmt = $pdo->prepare("
        SELECT r.id, r.pickup_location, r.destination, r.fare, r.created_at,
               u.name as rider_name, u.phone as rider_phone
        FROM ride_history r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.driver_id = ? AND r.status = 'driver_found'
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$driver_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'pending_count' => (int)$result['pending_count'],
        'bookings' => $bookings,
        'timestamp' => time()
    ]);
    
} catch (PDOException $e) {
    error_log("Error checking new bookings: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'pending_count' => 0
    ]);
}
