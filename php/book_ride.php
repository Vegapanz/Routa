<?php
session_start();
require_once 'config.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Prevent drivers and admins from booking rides as users
if (isset($_SESSION['is_driver']) && $_SESSION['is_driver'] === true) {
    echo json_encode(['success' => false, 'message' => 'Drivers cannot book rides as users']);
    exit;
}

if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    echo json_encode(['success' => false, 'message' => 'Admins cannot book rides as users']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['pickup_location']) || !isset($input['dropoff_location'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Extract data
    $userId = $_SESSION['user_id'];
    $pickupLocation = $input['pickup_location'];
    $dropoffLocation = $input['dropoff_location'];
    $pickupLat = $input['pickup_lat'] ?? null;
    $pickupLng = $input['pickup_lng'] ?? null;
    $dropoffLat = $input['dropoff_lat'] ?? null;
    $dropoffLng = $input['dropoff_lng'] ?? null;
    $paymentMethod = $input['payment_method'] ?? 'cash';
    $fare = $input['fare'] ?? 0;
    $distance = $input['distance'] ?? 'N/A';

    // Insert ride into database
    $stmt = $pdo->prepare("
        INSERT INTO ride_history 
        (user_id, pickup_location, destination, pickup_lat, pickup_lng, 
         dropoff_lat, dropoff_lng, payment_method, fare, distance, status, created_at) 
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");

    $stmt->execute([
        $userId,
        $pickupLocation,
        $dropoffLocation,
        $pickupLat,
        $pickupLng,
        $dropoffLat,
        $dropoffLng,
        $paymentMethod,
        $fare,
        $distance
    ]);

    $bookingId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Ride booked successfully',
        'booking_id' => $bookingId
    ]);

} catch (PDOException $e) {
    error_log('Booking error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
