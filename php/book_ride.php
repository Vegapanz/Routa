<?php
session_start();
require_once 'config.php';
require_once 'RealtimeBroadcaster.php';

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

    // Check if user already has an active or pending booking
    $checkStmt = $pdo->prepare("
        SELECT id, status FROM ride_history 
        WHERE user_id = ? 
        AND status IN ('pending', 'driver_found', 'searching')
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $checkStmt->execute([$userId]);
    $existingBooking = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingBooking) {
        echo json_encode([
            'success' => false,
            'message' => 'You already have a pending booking. Please wait for it to be completed or cancelled.',
            'existing_booking_id' => $existingBooking['id'],
            'existing_status' => $existingBooking['status']
        ]);
        exit;
    }

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

    // Get user details for notification
    $userStmt = $pdo->prepare("SELECT name, phone, email FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);

    // Send real-time notification to admin
    RealtimeBroadcaster::notifyRole('admin', [
        'type' => 'new_booking',
        'booking_id' => $bookingId,
        'user_id' => $userId,
        'user_name' => $userData['name'] ?? 'Unknown',
        'user_phone' => $userData['phone'] ?? 'N/A',
        'user_email' => $userData['email'] ?? 'N/A',
        'pickup' => [
            'address' => $pickupLocation,
            'lat' => $pickupLat,
            'lng' => $pickupLng
        ],
        'dropoff' => [
            'address' => $dropoffLocation,
            'lat' => $dropoffLat,
            'lng' => $dropoffLng
        ],
        'fare' => $fare,
        'distance' => $distance,
        'payment_method' => $paymentMethod,
        'timestamp' => time()
    ]);

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
