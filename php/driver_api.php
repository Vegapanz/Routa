<?php
/**
 * Driver API - Handle driver-side ride management
 * Actions: Accept/Reject rides, Update location, Start/Complete trip, Go online/offline
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// Debug session state
error_log("=== Driver API Request Debug ===");
error_log("Session ID: " . session_id());
error_log("Action: " . ($_GET['action'] ?? $_POST['action'] ?? 'none'));
error_log("user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));
error_log("is_driver: " . (isset($_SESSION['is_driver']) ? var_export($_SESSION['is_driver'], true) : 'NOT SET'));
error_log("PHPSESSID cookie: " . ($_COOKIE['PHPSESSID'] ?? 'NOT SET'));

header('Content-Type: application/json');

// Check driver authentication - be lenient with type checking
$hasUserId = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$isDriver = isset($_SESSION['is_driver']) && ($_SESSION['is_driver'] === true || $_SESSION['is_driver'] === 1 || $_SESSION['is_driver'] === '1');

if (!$hasUserId || !$isDriver) {
    error_log("Driver authentication FAILED - user_id: " . ($hasUserId ? 'EXISTS' : 'MISSING') . 
              ", is_driver value: " . (isset($_SESSION['is_driver']) ? var_export($_SESSION['is_driver'], true) : 'NOT SET') .
              ", is_driver check: " . ($isDriver ? 'PASSED' : 'FAILED'));
    echo json_encode(['success' => false, 'message' => 'Driver authentication required', 'redirect' => 'login.php']);
    exit;
}

$driverId = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'update_location':
            updateDriverLocation($pdo, $driverId);
            break;
        
        case 'update_status':
            updateDriverStatus($pdo, $driverId);
            break;
        
        case 'accept_ride':
            acceptRide($pdo, $driverId);
            break;
        
        case 'reject_ride':
            rejectRide($pdo, $driverId);
            break;
        
        case 'arrived':
            markArrived($pdo, $driverId);
            break;
        
        case 'start_trip':
            startTrip($pdo, $driverId);
            break;
        
        case 'complete_trip':
            completeTrip($pdo, $driverId);
            break;
        
        case 'get_rides':
            getPendingRides($pdo, $driverId);
            break;
        
        case 'active_ride':
            getActiveRide($pdo, $driverId);
            break;
        
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Driver API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}

/**
 * Update driver's current location (for real-time tracking)
 */
function updateDriverLocation($pdo, $driverId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $lat = floatval($input['latitude'] ?? 0);
    $lng = floatval($input['longitude'] ?? 0);
    $heading = floatval($input['heading'] ?? 0);
    $speed = floatval($input['speed'] ?? 0);
    
    if ($lat == 0 || $lng == 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid coordinates']);
        return;
    }
    
    // Update driver's current location in main table
    $stmt = $pdo->prepare("
        UPDATE tricycle_drivers 
        SET current_lat = ?, current_lng = ?, last_location_update = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$lat, $lng, $driverId]);
    
    // Insert into location tracking table
    $stmt = $pdo->prepare("
        INSERT INTO driver_locations (driver_id, latitude, longitude, heading, speed) 
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            latitude = VALUES(latitude), 
            longitude = VALUES(longitude), 
            heading = VALUES(heading), 
            speed = VALUES(speed),
            updated_at = NOW()
    ");
    $stmt->execute([$driverId, $lat, $lng, $heading, $speed]);
    
    echo json_encode(['success' => true, 'message' => 'Location updated']);
}

/**
 * Update driver status (available, offline)
 */
function updateDriverStatus($pdo, $driverId) {
    $input = json_decode(file_get_contents('php://input'), true);
    $status = $input['status'] ?? 'offline';
    
    $validStatuses = ['available', 'offline'];
    if (!in_array($status, $validStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        return;
    }
    
    // Check if driver has active rides
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as active_count 
        FROM ride_history 
        WHERE driver_id = ? AND status IN ('confirmed', 'arrived', 'in_progress')
    ");
    $stmt->execute([$driverId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['active_count'] > 0 && $status == 'offline') {
        echo json_encode(['success' => false, 'message' => 'Cannot go offline with active rides']);
        return;
    }
    
    $stmt = $pdo->prepare("UPDATE tricycle_drivers SET status = ? WHERE id = ?");
    $stmt->execute([$status, $driverId]);
    
    echo json_encode(['success' => true, 'message' => "Status updated to {$status}"]);
}

/**
 * Accept a ride request
 */
function acceptRide($pdo, $driverId) {
    require_once __DIR__ . '/RealtimeBroadcaster.php';
    
    error_log("acceptRide called - Driver ID: $driverId");
    
    $input = json_decode(file_get_contents('php://input'), true);
    error_log("Input data: " . json_encode($input));
    
    $rideId = $input['ride_id'] ?? 0;
    error_log("Ride ID: $rideId");
    
    if ($rideId == 0) {
        error_log("Invalid ride ID");
        echo json_encode(['success' => false, 'message' => 'Invalid ride ID']);
        return;
    }
    
    // Verify ride is assigned to this driver and pending acceptance
    $stmt = $pdo->prepare("
        SELECT * FROM ride_history 
        WHERE id = ? AND driver_id = ? AND status = 'driver_found'
    ");
    $stmt->execute([$rideId, $driverId]);
    $ride = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Ride found: " . ($ride ? 'Yes' : 'No'));
    
    if (!$ride) {
        // Check if ride exists with different status
        $stmt = $pdo->prepare("SELECT id, driver_id, status FROM ride_history WHERE id = ?");
        $stmt->execute([$rideId]);
        $existingRide = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("Existing ride: " . json_encode($existingRide));
        
        echo json_encode(['success' => false, 'message' => 'Ride not found or already accepted']);
        return;
    }
    
    // Accept the ride
    $stmt = $pdo->prepare("
        UPDATE ride_history 
        SET status = 'confirmed', driver_arrival_time = NOW(), updated_at = NOW() 
        WHERE id = ?
    ");
    $result = $stmt->execute([$rideId]);
    error_log("Update result: " . ($result ? 'Success' : 'Failed'));
    
    // Update driver status
    $stmt = $pdo->prepare("UPDATE tricycle_drivers SET status = 'on_trip' WHERE id = ?");
    $stmt->execute([$driverId]);
    
    // Notify user via database notification
    createNotification($pdo, $rideId, $ride['user_id'], 'user', 'driver_confirmed', 
        'Your driver is on the way!');
    
    // Send real-time notification to user
    RealtimeBroadcaster::notifyUser($ride['user_id'], [
        'type' => 'status_update',
        'ride_id' => $rideId,
        'status' => 'confirmed',
        'message' => 'Driver is on the way!',
        'timestamp' => time()
    ]);
    
    error_log("Ride accepted successfully");
    
    echo json_encode([
        'success' => true,
        'message' => 'Ride accepted successfully',
        'ride' => $ride
    ]);
}

/**
 * Reject a ride request
 */
function rejectRide($pdo, $driverId) {
    require_once __DIR__ . '/RealtimeBroadcaster.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    $rideId = $input['ride_id'] ?? 0;
    $reason = $input['reason'] ?? 'Driver declined';
    
    // Verify ride
    $stmt = $pdo->prepare("
        SELECT * FROM ride_history 
        WHERE id = ? AND driver_id = ? AND status = 'driver_found'
    ");
    $stmt->execute([$rideId, $driverId]);
    $ride = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ride) {
        echo json_encode(['success' => false, 'message' => 'Ride not found']);
        return;
    }
    
    // Unassign driver and set back to pending for admin reassignment
    $stmt = $pdo->prepare("
        UPDATE ride_history 
        SET driver_id = NULL, status = 'pending', updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$rideId]);
    
    // Make driver available again
    $stmt = $pdo->prepare("UPDATE tricycle_drivers SET status = 'available' WHERE id = ?");
    $stmt->execute([$driverId]);
    
    // Update driver cancellation rate
    updateDriverCancellationRate($pdo, $driverId);
    
    // Notify user via database
    createNotification($pdo, $rideId, $ride['user_id'], 'user', 'driver_declined', 
        'Driver declined. Your booking is being reassigned...');
    
    // Send real-time notification to user
    RealtimeBroadcaster::notifyUser($ride['user_id'], [
        'type' => 'driver_rejected',
        'ride_id' => $rideId,
        'message' => 'Driver declined your booking. Admin will assign another driver shortly...',
        'reason' => $reason,
        'timestamp' => time()
    ]);
    
    // Send real-time notification to all admins about the rejected booking
    try {
        $stmt = $pdo->prepare("SELECT id FROM admins");
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($admins as $admin) {
            RealtimeBroadcaster::notifyUser($admin['id'], [
                'type' => 'driver_rejected',
                'booking_id' => $rideId,
                'driver_id' => $driverId,
                'message' => "Driver rejected booking #{$rideId}. Booking returned to pending.",
                'reason' => $reason,
                'timestamp' => time()
            ]);
        }
    } catch (Exception $e) {
        error_log("Failed to notify admins: " . $e->getMessage());
    }
    
    error_log("Booking {$rideId} rejected by driver {$driverId} - returned to pending for admin reassignment");
    
    echo json_encode(['success' => true, 'message' => 'Ride rejected and returned to admin for reassignment']);
}

/**
 * Mark driver as arrived at pickup location
 */
function markArrived($pdo, $driverId) {
    require_once __DIR__ . '/RealtimeBroadcaster.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    $rideId = $input['ride_id'] ?? 0;
    
    $stmt = $pdo->prepare("
        SELECT * FROM ride_history 
        WHERE id = ? AND driver_id = ? AND status = 'confirmed'
    ");
    $stmt->execute([$rideId, $driverId]);
    $ride = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ride) {
        echo json_encode(['success' => false, 'message' => 'Ride not found']);
        return;
    }
    
    $stmt = $pdo->prepare("
        UPDATE ride_history 
        SET status = 'arrived', updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$rideId]);
    
    // Notify user via database notification
    createNotification($pdo, $rideId, $ride['user_id'], 'user', 'driver_arrived', 
        'Your driver has arrived!');
    
    // Send real-time notification to user
    RealtimeBroadcaster::notifyUser($ride['user_id'], [
        'type' => 'status_update',
        'ride_id' => $rideId,
        'status' => 'arrived',
        'message' => 'Your driver has arrived!',
        'timestamp' => time()
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Marked as arrived']);
}

/**
 * Start the trip
 */
function startTrip($pdo, $driverId) {
    require_once __DIR__ . '/RealtimeBroadcaster.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    $rideId = $input['ride_id'] ?? 0;
    
    $stmt = $pdo->prepare("
        SELECT * FROM ride_history 
        WHERE id = ? AND driver_id = ? AND status IN ('confirmed', 'arrived')
    ");
    $stmt->execute([$rideId, $driverId]);
    $ride = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ride) {
        echo json_encode(['success' => false, 'message' => 'Ride not found']);
        return;
    }
    
    $stmt = $pdo->prepare("
        UPDATE ride_history 
        SET status = 'in_progress', trip_start_time = NOW(), updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$rideId]);
    
    // Notify user via database notification
    createNotification($pdo, $rideId, $ride['user_id'], 'user', 'trip_started', 
        'Your trip has started!');
    
    // Send real-time notification to user
    RealtimeBroadcaster::notifyUser($ride['user_id'], [
        'type' => 'status_update',
        'ride_id' => $rideId,
        'status' => 'in_progress',
        'message' => 'Your trip has started!',
        'timestamp' => time()
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Trip started']);
}

/**
 * Complete the trip
 */
function completeTrip($pdo, $driverId) {
    $input = json_decode(file_get_contents('php://input'), true);
    $rideId = $input['ride_id'] ?? 0;
    $actualFare = $input['fare'] ?? null;
    
    $stmt = $pdo->prepare("
        SELECT * FROM ride_history 
        WHERE id = ? AND driver_id = ? AND status = 'in_progress'
    ");
    $stmt->execute([$rideId, $driverId]);
    $ride = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ride) {
        echo json_encode(['success' => false, 'message' => 'Ride not found or not in progress']);
        return;
    }
    
    // Update actual fare if provided
    $finalFare = $actualFare ?? $ride['fare'];
    
    $stmt = $pdo->prepare("
        UPDATE ride_history 
        SET status = 'completed', 
            fare = ?, 
            trip_end_time = NOW(), 
            completed_at = NOW(),
            updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$finalFare, $rideId]);
    
    // Calculate driver earnings (assuming 80% goes to driver, 20% platform fee)
    $platformCommission = $finalFare * 0.20;
    $netEarnings = $finalFare * 0.80;
    
    // Record earnings
    $stmt = $pdo->prepare("
        INSERT INTO driver_earnings 
        (driver_id, ride_id, gross_fare, platform_commission, net_earnings) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$driverId, $rideId, $finalFare, $platformCommission, $netEarnings]);
    
    // Update driver status and stats
    $stmt = $pdo->prepare("
        UPDATE tricycle_drivers 
        SET status = 'available', 
            total_trips_completed = total_trips_completed + 1,
            total_earnings = total_earnings + ?
        WHERE id = ?
    ");
    $stmt->execute([$netEarnings, $driverId]);
    
    // Notify user via database notification
    createNotification($pdo, $rideId, $ride['user_id'], 'user', 'trip_completed', 
        'Trip completed! Please rate your driver.');
    
    // Send real-time notification to user
    require_once __DIR__ . '/RealtimeBroadcaster.php';
    RealtimeBroadcaster::notifyUser($ride['user_id'], [
        'type' => 'ride_completed',
        'ride_id' => $rideId,
        'status' => 'completed',
        'fare' => $finalFare,
        'message' => 'Trip completed! Please rate your driver.',
        'timestamp' => time()
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Trip completed successfully',
        'earnings' => $netEarnings,
        'fare' => $finalFare
    ]);
}

/**
 * Get pending ride requests for this driver
 */
function getPendingRides($pdo, $driverId) {
    $stmt = $pdo->prepare("
        SELECT r.*, u.name as user_name, u.phone as user_phone
        FROM ride_history r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.driver_id = ? AND r.status = 'driver_found'
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$driverId]);
    $rides = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'rides' => $rides
    ]);
}

/**
 * Get driver's active ride (if any)
 */
function getActiveRide($pdo, $driverId) {
    $stmt = $pdo->prepare("
        SELECT r.*, u.name as user_name, u.phone as user_phone, u.email as user_email
        FROM ride_history r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.driver_id = ? 
        AND r.status IN ('driver_found', 'confirmed', 'arrived', 'in_progress')
        ORDER BY r.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$driverId]);
    $ride = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ride) {
        echo json_encode(['success' => false, 'message' => 'No active ride']);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'ride' => $ride
    ]);
}

/**
 * Helper functions
 */

function findNearbyDrivers($pdo, $lat, $lng, $radiusKm = 5, $excludeDriverId = null) {
    $excludeClause = $excludeDriverId ? "AND id != ?" : "";
    $params = [$lat, $lng, $lat, $radiusKm];
    if ($excludeDriverId) {
        $params[] = $excludeDriverId;
    }
    
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
        {$excludeClause}
        HAVING distance < ?
        ORDER BY distance ASC, rating DESC
        LIMIT 10
    ");
    
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createNotification($pdo, $rideId, $recipientId, $recipientType, $type, $message) {
    $stmt = $pdo->prepare("
        INSERT INTO ride_notifications 
        (ride_id, recipient_id, recipient_type, notification_type, message, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$rideId, $recipientId, $recipientType, $type, $message]);
}

function updateDriverCancellationRate($pdo, $driverId) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN cancelled_by = 'driver' THEN 1 ELSE 0 END) as cancelled
        FROM ride_history
        WHERE driver_id = ?
    ");
    $stmt->execute([$driverId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['total'] > 0) {
        $cancellationRate = ($result['cancelled'] / $result['total']) * 100;
        $stmt = $pdo->prepare("UPDATE tricycle_drivers SET cancellation_rate = ? WHERE id = ?");
        $stmt->execute([$cancellationRate, $driverId]);
    }
}
?>
