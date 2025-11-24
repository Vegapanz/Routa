<?php
/**
 * Advanced Booking API - Uber-like Ride Booking System
 * Handles: Creating bookings, Finding drivers, Real-time matching
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';
require_once 'RealtimeBroadcaster.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Session integrity check - if called from userdashboard, verify user hasn't switched
if (isset($_SESSION['userdash_initial_user_id']) && 
    $_SESSION['userdash_initial_user_id'] != $_SESSION['user_id']) {
    error_log("BOOKING API: Session mismatch detected - restoring user_id from " . $_SESSION['user_id'] . " to " . $_SESSION['userdash_initial_user_id']);
    $_SESSION['user_id'] = $_SESSION['userdash_initial_user_id'];
    $_SESSION['is_admin'] = $_SESSION['userdash_is_admin'] ?? false;
    $_SESSION['is_driver'] = $_SESSION['userdash_is_driver'] ?? false;
}

// Only prevent drivers from using user booking API
// (Admins can test the system, and regular users should always be able to book)
if (isset($_SESSION['is_driver']) && $_SESSION['is_driver'] === true && 
    !isset($_SESSION['userdash_locked'])) {
    // Pure driver (not testing from userdashboard) cannot use booking API
    echo json_encode(['success' => false, 'message' => 'Drivers cannot access user booking functions']);
    exit;
}

$userId = $_SESSION['user_id'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Handle JSON input
$jsonInput = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? $_POST['action'] ?? ($jsonInput['action'] ?? 'create');

try {
    switch ($action) {
        case 'create':
            createBooking($pdo, $userId);
            break;
        
        case 'cancel':
            cancelBooking($pdo, $userId);
            break;
        
        case 'status':
            getBookingStatus($pdo, $userId);
            break;
        
        case 'rate':
            rateDriver($pdo, $userId);
            break;
        
        case 'active':
            getActiveBooking($pdo, $userId);
            break;
        
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Booking API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}

/**
 * Create a new booking and find nearby drivers
 */
function createBooking($pdo, $userId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['pickup_location', 'dropoff_location', 'pickup_lat', 'pickup_lng', 'dropoff_lat', 'dropoff_lng'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            echo json_encode(['success' => false, 'message' => "Missing field: $field"]);
            return;
        }
    }
    
    // Extract booking details
    $pickupLocation = $input['pickup_location'];
    $dropoffLocation = $input['dropoff_location'];
    $pickupLat = floatval($input['pickup_lat']);
    $pickupLng = floatval($input['pickup_lng']);
    $dropoffLat = floatval($input['dropoff_lat']);
    $dropoffLng = floatval($input['dropoff_lng']);
    $paymentMethod = $input['payment_method'] ?? 'cash';
    $distance = $input['distance'] ?? null;
    $duration = $input['duration'] ?? null;
    
    // Calculate fare
    $fare = calculateFare($pdo, $distance, $duration);
    
    // Create booking with 'pending' status - wait for admin to assign driver
    $stmt = $pdo->prepare("
        INSERT INTO ride_history 
        (user_id, pickup_location, destination, pickup_lat, pickup_lng, 
         dropoff_lat, dropoff_lng, payment_method, fare, distance, 
         estimated_duration, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
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
        $distance,
        $duration
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
    
    // Create notification for admins about new booking request (old system)
    $stmt = $pdo->query("SELECT id FROM admins WHERE role IN ('admin', 'superadmin')");
    $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($admins as $adminId) {
        createNotification($pdo, $bookingId, $adminId, 'admin', 'new_booking', 
            "New booking request from {$pickupLocation} to {$dropoffLocation}");
    }
    
    // Booking is pending admin approval
    echo json_encode([
        'success' => true,
        'booking_id' => $bookingId,
        'status' => 'pending',
        'message' => 'Booking submitted! Waiting for admin confirmation...',
        'fare' => $fare,
        'booking' => [
            'pickup_location' => $pickupLocation,
            'dropoff_location' => $dropoffLocation,
            'distance' => $distance,
            'fare' => $fare
        ]
    ]);
}

/**
 * Cancel an active booking
 */
function cancelBooking($pdo, $userId) {
    $input = json_decode(file_get_contents('php://input'), true);
    $bookingId = $input['booking_id'] ?? 0;
    $reason = $input['reason'] ?? 'User cancelled';
    
    // Verify booking belongs to user
    $stmt = $pdo->prepare("
        SELECT * FROM ride_history 
        WHERE id = ? AND user_id = ? AND status NOT IN ('completed', 'cancelled')
    ");
    $stmt->execute([$bookingId, $userId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found or already completed']);
        return;
    }
    
    // Cancel booking
    $stmt = $pdo->prepare("
        UPDATE ride_history 
        SET status = 'cancelled', cancelled_by = 'user', cancel_reason = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$reason, $bookingId]);
    
    // If driver was assigned, make them available again
    if ($booking['driver_id']) {
        $stmt = $pdo->prepare("UPDATE tricycle_drivers SET status = 'available' WHERE id = ?");
        $stmt->execute([$booking['driver_id']]);
        
        // Notify driver
        createNotification($pdo, $bookingId, $booking['driver_id'], 'driver', 'ride_cancelled', 
            "Ride cancelled by passenger");
    }
    
    // Broadcast to admin dashboard for real-time update
    RealtimeBroadcaster::broadcast([
        'type' => 'booking_cancelled',
        'booking_id' => $bookingId,
        'status' => 'cancelled',
        'cancelled_by' => 'user',
        'timestamp' => time()
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Booking cancelled successfully'
    ]);
}

/**
 * Get booking status with real-time updates
 */
function getBookingStatus($pdo, $userId) {
    $bookingId = $_GET['booking_id'] ?? 0;
    
    $stmt = $pdo->prepare("
        SELECT r.*, 
               d.name as driver_name, 
               d.phone as driver_phone, 
               d.plate_number, 
               d.rating as driver_rating,
               d.current_lat as driver_lat,
               d.current_lng as driver_lng,
               d.status as driver_status
        FROM ride_history r
        LEFT JOIN tricycle_drivers d ON r.driver_id = d.id
        WHERE r.id = ? AND r.user_id = ?
    ");
    $stmt->execute([$bookingId, $userId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        return;
    }
    
    // Get driver location if available
    $driverLocation = null;
    if ($booking['driver_id']) {
        $stmt = $pdo->prepare("
            SELECT latitude, longitude, heading, speed, updated_at 
            FROM driver_locations 
            WHERE driver_id = ? 
            ORDER BY updated_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$booking['driver_id']]);
        $driverLocation = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'booking' => [
            'id' => $booking['id'],
            'status' => $booking['status'],
            'pickup_location' => $booking['pickup_location'],
            'dropoff_location' => $booking['destination'],
            'pickup_lat' => $booking['pickup_lat'],
            'pickup_lng' => $booking['pickup_lng'],
            'dropoff_lat' => $booking['dropoff_lat'],
            'dropoff_lng' => $booking['dropoff_lng'],
            'fare' => $booking['fare'],
            'payment_method' => $booking['payment_method'],
            'distance' => $booking['distance'],
            'created_at' => $booking['created_at']
        ],
        'driver' => $booking['driver_id'] ? [
            'id' => $booking['driver_id'],
            'name' => $booking['driver_name'],
            'phone' => $booking['driver_phone'],
            'plate_number' => $booking['plate_number'],
            'rating' => $booking['driver_rating'],
            'current_location' => $driverLocation
        ] : null
    ]);
}

/**
 * Rate driver after trip completion
 */
function rateDriver($pdo, $userId) {
    $input = json_decode(file_get_contents('php://input'), true);
    $bookingId = intval($input['booking_id'] ?? 0);
    $rating = intval($input['rating'] ?? 0);
    $review = $input['review'] ?? '';
    
    error_log("Rating driver - Booking ID: $bookingId, Rating: $rating, User ID: $userId");
    error_log("Full input data: " . json_encode($input));
    
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
        return;
    }
    
    // Verify booking - allow re-rating even if already rated
    // For admins testing, check if they created the booking
    $stmt = $pdo->prepare("
        SELECT * FROM ride_history 
        WHERE id = ?
    ");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        error_log("Booking ID $bookingId does not exist in database");
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        return;
    }
    
    // Check if current user can rate this booking
    // Allow if: 1) user_id matches OR 2) admin is testing and booking exists
    $canRate = ($booking['user_id'] == $userId);
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        $canRate = true; // Admins can rate any booking for testing
    }
    
    if (!$canRate) {
        error_log("User $userId cannot rate booking {$bookingId} (belongs to user {$booking['user_id']})");
        echo json_encode(['success' => false, 'message' => 'You cannot rate this booking']);
        return;
    }
    
    if ($booking['status'] !== 'completed') {
        error_log("Booking status is not completed. Current status: " . $booking['status']);
        echo json_encode(['success' => false, 'message' => 'Can only rate completed trips']);
        return;
    }
    
    if (!$booking['driver_id']) {
        error_log("No driver assigned to this booking");
        echo json_encode(['success' => false, 'message' => 'No driver was assigned to this trip']);
        return;
    }
    
    // Save user's rating of the driver (user_rating column stores passenger's rating)
    $stmt = $pdo->prepare("
        UPDATE ride_history 
        SET user_rating = ?, user_review = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $result = $stmt->execute([$rating, $review, $bookingId]);
    
    if (!$result) {
        error_log("Failed to update rating in database");
        echo json_encode(['success' => false, 'message' => 'Failed to save rating']);
        return;
    }
    
    error_log("Rating saved successfully");
    
    // Update driver's average rating and total ratings count
    updateDriverRating($pdo, $booking['driver_id']);
    
    // Notify driver about the rating
    createNotification($pdo, $bookingId, $booking['driver_id'], 'driver', 'rating_received', 
        "You received a {$rating}-star rating from a passenger!");
    
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your rating!',
        'rating' => $rating
    ]);
}

/**
 * Get user's active booking (if any)
 */
function getActiveBooking($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT r.*, 
               d.name as driver_name, 
               d.phone as driver_phone, 
               d.plate_number, 
               d.rating as driver_rating,
               d.current_lat as driver_lat,
               d.current_lng as driver_lng
        FROM ride_history r
        LEFT JOIN tricycle_drivers d ON r.driver_id = d.id
        WHERE r.user_id = ? 
        AND r.status IN ('searching', 'driver_found', 'confirmed', 'arrived', 'in_progress')
        ORDER BY r.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'No active booking']);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'booking' => $booking
    ]);
}

/**
 * Find nearby available drivers using Haversine formula
 */
function findNearbyDrivers($pdo, $lat, $lng, $radiusKm = 5) {
    $stmt = $pdo->prepare("
        SELECT 
            id, name, phone, plate_number, rating, current_lat, current_lng,
            (6371 * acos(cos(radians(?)) * cos(radians(current_lat)) * 
            cos(radians(current_lng) - radians(?)) + sin(radians(?)) * 
            sin(radians(current_lat)))) AS distance
        FROM tricycle_drivers
        WHERE status = 'available'
        AND is_verified = 1
        AND current_lat IS NOT NULL
        AND current_lng IS NOT NULL
        HAVING distance < ?
        ORDER BY distance ASC, rating DESC
        LIMIT 10
    ");
    
    $stmt->execute([$lat, $lng, $lat, $radiusKm]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Assign driver to booking
 */
function assignDriverToBooking($pdo, $bookingId, $driverId) {
    $stmt = $pdo->prepare("
        UPDATE ride_history 
        SET driver_id = ?, status = 'driver_found', updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$driverId, $bookingId]);
    
    // Update driver status
    $stmt = $pdo->prepare("UPDATE tricycle_drivers SET status = 'on_trip' WHERE id = ?");
    $stmt->execute([$driverId]);
}

/**
 * Calculate fare based on distance and time
 */
function calculateFare($pdo, $distanceStr, $durationStr) {
    // Get fare settings
    $stmt = $pdo->query("SELECT * FROM fare_settings WHERE active = 1 ORDER BY id DESC LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$settings) {
        // Default values if no settings found
        $settings = [
            'base_fare' => 50.00,
            'per_km_rate' => 15.00,
            'per_minute_rate' => 2.00,
            'minimum_fare' => 50.00,
            'surge_multiplier' => 1.00
        ];
    }
    
    // Parse distance (e.g., "5.2 km" -> 5.2)
    $distance = 0;
    if (preg_match('/[\d.]+/', $distanceStr, $matches)) {
        $distance = floatval($matches[0]);
    }
    
    // Parse duration (e.g., "15 mins" -> 15)
    $duration = 0;
    if (preg_match('/\d+/', $durationStr, $matches)) {
        $duration = intval($matches[0]);
    }
    
    // Calculate fare
    $fare = $settings['base_fare'];
    $fare += ($distance * $settings['per_km_rate']);
    $fare += ($duration * $settings['per_minute_rate']);
    $fare *= $settings['surge_multiplier'];
    
    // Apply minimum fare
    if ($fare < $settings['minimum_fare']) {
        $fare = $settings['minimum_fare'];
    }
    
    return round($fare, 2);
}

/**
 * Estimate arrival time based on distance
 */
function estimateArrival($distanceKm) {
    // Average speed in urban areas: 20 km/h
    $minutes = ($distanceKm / 20) * 60;
    
    if ($minutes < 1) {
        return "Less than 1 minute";
    } elseif ($minutes < 60) {
        return round($minutes) . " minutes";
    } else {
        $hours = floor($minutes / 60);
        $mins = round($minutes % 60);
        return "{$hours}h {$mins}m";
    }
}

/**
 * Create notification
 */
function createNotification($pdo, $rideId, $recipientId, $recipientType, $type, $message) {
    $stmt = $pdo->prepare("
        INSERT INTO ride_notifications 
        (ride_id, recipient_id, recipient_type, notification_type, message, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$rideId, $recipientId, $recipientType, $type, $message]);
}

/**
 * Update driver's average rating
 */
function updateDriverRating($pdo, $driverId) {
    // user_rating column stores the passenger's rating of the driver
    $stmt = $pdo->prepare("
        SELECT AVG(user_rating) as avg_rating, COUNT(*) as total_ratings
        FROM ride_history
        WHERE driver_id = ? AND user_rating IS NOT NULL AND status = 'completed'
    ");
    $stmt->execute([$driverId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Updating driver rating for driver $driverId: avg={$result['avg_rating']}, total={$result['total_ratings']}");
    
    if ($result && $result['total_ratings'] > 0) {
        $avgRating = round($result['avg_rating'], 2);
        
        $stmt = $pdo->prepare("
            UPDATE tricycle_drivers 
            SET average_rating = ?, total_ratings = ?, rating = ?
            WHERE id = ?
        ");
        $stmt->execute([$avgRating, $result['total_ratings'], $avgRating, $driverId]);
        
        error_log("Driver rating updated: $avgRating with {$result['total_ratings']} ratings");
    }
}
?>
