<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'php/config.php';

// Check if user is logged in and is a driver
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_driver']) || !$_SESSION['is_driver']) {
    header('Location: login.php');
    exit();
}

$driver_id = $_SESSION['user_id'];

// Fetch driver data from tricycle_drivers table to ensure correct data
$driver_data = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM tricycle_drivers WHERE id = ?");
    $stmt->execute([$driver_id]);
    $driver_data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error checking driver: " . $e->getMessage());
}

// If driver data not found, use cached session data instead of logging out
if (!$driver_data) {
    error_log("Driver data not retrieved from database for user_id: " . $driver_id . " - using session cache");
    
    // Create driver data from session to allow page to continue
    $driver_data = [
        'id' => $driver_id,
        'name' => $_SESSION['user_name'] ?? 'Driver',
        'email' => $_SESSION['user_email'] ?? '',
        'status' => 'online'
    ];
    
    // Only log critical error if this persists (check if we've been using cache too long)
    if (!isset($_SESSION['cache_start_time'])) {
        $_SESSION['cache_start_time'] = time();
    } elseif ((time() - $_SESSION['cache_start_time']) > 300) {
        // If using cache for more than 5 minutes, something is wrong
        error_log("CRITICAL: Driver data unavailable for 5+ minutes for user_id: " . $driver_id);
    }
} else {
    // Successfully retrieved driver data - clear cache timer
    unset($_SESSION['cache_start_time']);
}

$driver_name = $driver_data['name'];
$_SESSION['user_name'] = $driver_name; // Update session to ensure consistency

// Handle AJAX requests for status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    try {
        switch ($_POST['action']) {
            case 'update_status':
                $new_status = $_POST['status'] ?? 'offline';
                $stmt = $pdo->prepare("UPDATE tricycle_drivers SET status = ? WHERE id = ?");
                if ($stmt->execute([$new_status, $driver_id])) {
                    $response['success'] = true;
                    $response['message'] = 'Status updated successfully';
                } else {
                    $response['message'] = 'Failed to update status';
                }
                break;

            case 'start_trip':
                $booking_id = $_POST['booking_id'] ?? 0;
                $stmt = $pdo->prepare("UPDATE ride_history SET status = 'in-progress' WHERE id = ? AND driver_id = ?");
                if ($stmt->execute([$booking_id, $driver_id])) {
                    $response['success'] = true;
                    $response['message'] = 'Trip started';
                } else {
                    $response['message'] = 'Failed to start trip';
                }
                break;

            case 'complete_trip':
                $booking_id = $_POST['booking_id'] ?? 0;
                $pdo->beginTransaction();
                
                // Update ride status to completed
                $stmt = $pdo->prepare("UPDATE ride_history SET status = 'completed' WHERE id = ? AND driver_id = ?");
                $stmt->execute([$booking_id, $driver_id]);
                
                // Set driver status back to available
                $stmt = $pdo->prepare("UPDATE tricycle_drivers SET status = 'available' WHERE id = ?");
                $stmt->execute([$driver_id]);
                
                $pdo->commit();
                $response['success'] = true;
                $response['message'] = 'Trip completed';
                break;
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log("Driver dashboard error: " . $e->getMessage());
    }

    echo json_encode($response);
    exit();
}

// Fetch driver details
$stmt = $pdo->prepare("SELECT * FROM tricycle_drivers WHERE id = ?");
$stmt->execute([$driver_id]);
$driver = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch today's earnings
$stmt = $pdo->prepare("SELECT COALESCE(SUM(fare), 0) as today_earnings, COUNT(*) as today_trips 
    FROM ride_history 
    WHERE driver_id = ? AND DATE(created_at) = CURDATE() AND status = 'completed'");
$stmt->execute([$driver_id]);
$today_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch total earnings
$stmt = $pdo->prepare("SELECT COALESCE(SUM(fare), 0) as total_earnings, COUNT(*) as total_trips 
    FROM ride_history 
    WHERE driver_id = ? AND status = 'completed'");
$stmt->execute([$driver_id]);
$total_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch pending ride requests (waiting for driver acceptance)
$stmt = $pdo->prepare("SELECT r.*, u.name as rider_name, u.phone, u.email as rider_email
    FROM ride_history r 
    LEFT JOIN users u ON r.user_id = u.id 
    WHERE r.driver_id = ? AND r.status = 'driver_found'
    ORDER BY r.created_at DESC");
$stmt->execute([$driver_id]);
$pending_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch assigned/confirmed rides (accepted rides in progress)
$stmt = $pdo->prepare("SELECT r.*, u.name as rider_name, u.phone 
    FROM ride_history r 
    LEFT JOIN users u ON r.user_id = u.id 
    WHERE r.driver_id = ? AND r.status IN ('confirmed', 'arrived', 'in_progress')
    ORDER BY r.created_at DESC");
$stmt->execute([$driver_id]);
$assigned_rides = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch trip history
$stmt = $pdo->prepare("SELECT r.*, u.name as rider_name, u.phone 
    FROM ride_history r 
    LEFT JOIN users u ON r.user_id = u.id 
    WHERE r.driver_id = ? AND r.status = 'completed'
    ORDER BY r.created_at DESC 
    LIMIT 10");
$stmt->execute([$driver_id]);
$completed_trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check for active ride
$stmt = $pdo->prepare("
    SELECT * FROM ride_history 
    WHERE driver_id = ? 
    AND status IN ('driver_found', 'confirmed', 'arrived', 'in_progress')
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt->execute([$driver_id]);
$activeRide = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch driver application information using email
$stmt = $pdo->prepare("SELECT * FROM driver_applications WHERE email = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$driver['email']]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard - Routa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="assets/images/Logo.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/pages/driver-dashboard.css">
    
    <!-- Leaflet CSS for Maps (Free, No API Key Needed!) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-light bg-white border-bottom">
        <div class="container-fluid px-4 py-3">
            <a class="navbar-brand d-flex align-items-center" href="driver_dashboard.php">
                <img src="assets/images/Logo.png" alt="Routa Logo" height="32" class="me-2">
                <span class="fw-bold fs-5" style="color: #10b981;">Routa</span>
                <span class="badge bg-success-subtle text-success ms-2" style="font-size: 0.75rem; font-weight: 500;">Driver</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <div class="status-toggle">
                    <span class="status-label <?= $driver['status'] === 'available' ? 'online' : '' ?>">
                        <?= $driver['status'] === 'available' ? 'Online' : 'Offline' ?>
                    </span>
                    <div class="status-toggle-switch <?= $driver['status'] === 'available' ? 'online' : '' ?>">
                        <div class="toggle-slider"></div>
                    </div>
                </div>
                <a href="php/logout.php" class="btn-logout-custom">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid px-4 py-4" style="max-width: 1400px; margin: 0 auto;">
        <!-- Welcome Section -->
        <div class="mb-4">
            <h4 class="fw-bold mb-1">Welcome back, <?= htmlspecialchars($driver_name) ?>!</h4>
            <p class="text-muted mb-0">You're <?= $driver['status'] === 'available' ? 'online' : 'offline' ?> and ready to accept rides</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-label">
                        <i class="bi bi-currency-dollar"></i>
                        Today's Earnings
                    </div>
                    <i class="bi bi-currency-dollar stat-icon" style="color: #10b981;"></i>
                </div>
                <div class="stat-value">₱<?= number_format($today_stats['today_earnings'], 0) ?></div>
                <div class="stat-meta"><?= $today_stats['today_trips'] ?> trips today</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-label">
                        <i class="bi bi-graph-up-arrow"></i>
                        Total Earnings
                    </div>
                    <i class="bi bi-graph-up-arrow stat-icon" style="color: #10b981;"></i>
                </div>
                <div class="stat-value">₱<?= number_format($total_stats['total_earnings'], 0) ?></div>
                <div class="stat-meta">All time earnings</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-label">
                        <i class="bi bi-arrow-repeat"></i>
                        Total Trips
                    </div>
                    <i class="bi bi-arrow-repeat stat-icon" style="color: #10b981;"></i>
                </div>
                <div class="stat-value"><?= $total_stats['total_trips'] ?></div>
                <div class="stat-meta">Completed rides</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-label">
                        <i class="bi bi-star"></i>
                        Rating
                    </div>
                    <i class="bi bi-star stat-icon" style="color: #fbbf24;"></i>
                </div>
                <div class="stat-value"><?= number_format($driver['rating'], 1) ?></div>
                <div class="stat-meta stat-rating">
                    <i class="bi bi-star-fill star-filled"></i>
                    <i class="bi bi-star-fill star-filled"></i>
                    <i class="bi bi-star-fill star-filled"></i>
                    <i class="bi bi-star-fill star-filled"></i>
                    <i class="bi bi-star-half star-filled"></i>
                    <span>Average rating</span>
                </div>
            </div>
        </div>

        <!-- Map Section -->
        <?php if (!empty($pending_requests) || !empty($assigned_rides)): ?>
        <div class="map-section mb-4">
            <div class="section-header mb-3">
                <div>
                    <div class="section-title">
                        <i class="bi bi-map"></i>
                        Ride Locations
                    </div>
                    <div class="section-subtitle">View pickup and drop-off locations on the map</div>
                </div>
            </div>
            <div id="driverMap" style="height: 400px; width: 100%; border-radius: 12px; border: 2px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"></div>
        </div>
        <?php endif; ?>

        <!-- Pending Ride Requests Section (NEW!) -->
        <?php if (!empty($pending_requests)): ?>
        <div class="pending-requests-section mb-4">
            <div class="section-header">
                <div>
                    <div class="section-title" style="color: #f59e0b;">
                        <i class="bi bi-bell-fill"></i>
                        New Ride Requests
                    </div>
                    <div class="section-subtitle">You have <?= count($pending_requests) ?> new ride request(s)</div>
                </div>
            </div>
            
            <?php foreach ($pending_requests as $request): ?>
            <div class="ride-card" style="border: 2px solid #f59e0b; background: #fffbeb;">
                <div class="ride-card-header">
                    <div class="ride-id-section">
                        <div class="booking-id">Booking ID: BK-<?= str_pad($request['id'], 3, '0', STR_PAD_LEFT) ?></div>
                        <span class="status-badge" style="background: #fbbf24; color: #78350f;">
                            <i class="bi bi-clock-fill"></i> Waiting for Response
                        </span>
                    </div>
                    <div class="ride-fare" style="color: #f59e0b;">₱<?= number_format($request['fare'], 0) ?></div>
                </div>

                <div class="rider-info">
                    <i class="bi bi-person-fill"></i>
                    <span class="rider-name">Rider: <?= htmlspecialchars($request['rider_name']) ?></span>
                    <div class="rider-phone">
                        <i class="bi bi-telephone-fill"></i>
                        <span><?= htmlspecialchars($request['phone']) ?></span>
                    </div>
                </div>

                <div class="location-item">
                    <i class="bi bi-geo-alt-fill location-icon from"></i>
                    <div class="location-content">
                        <div class="location-label">Pickup</div>
                        <div class="location-value"><?= htmlspecialchars($request['pickup_location']) ?></div>
                    </div>
                </div>

                <div class="location-item">
                    <i class="bi bi-geo-alt-fill location-icon to"></i>
                    <div class="location-content">
                        <div class="location-label">Dropoff</div>
                        <div class="location-value"><?= htmlspecialchars($request['destination']) ?></div>
                    </div>
                </div>

                <?php if ($request['distance']): ?>
                <div class="ride-info-row">
                    <div class="info-item">
                        <i class="bi bi-pin-map"></i>
                        <span><?= htmlspecialchars($request['distance']) ?></span>
                    </div>
                    <div class="info-item">
                        <i class="bi bi-cash"></i>
                        <span><?= htmlspecialchars($request['payment_method'] ?? 'Cash') ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="ride-actions" style="display: flex; gap: 10px; margin-top: 15px;">
                    <button class="btn-accept" onclick="acceptRide(<?= $request['id'] ?>)" style="flex: 1; background: #10b981; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        <i class="bi bi-check-circle-fill"></i> Accept Ride
                    </button>
                    <button class="btn-reject" onclick="rejectRide(<?= $request['id'] ?>)" style="flex: 1; background: #ef4444; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        <i class="bi bi-x-circle-fill"></i> Reject
                    </button>
                </div>
                <button class="btn view-map-btn" 
                    data-ride-id="<?= $request['id'] ?>"
                    data-pickup-location="<?= htmlspecialchars($request['pickup_location']) ?>"
                    data-dropoff-location="<?= htmlspecialchars($request['destination']) ?>"
                    data-pickup-lat="<?= $request['pickup_lat'] ?? '' ?>"
                    data-pickup-lng="<?= $request['pickup_lng'] ?? '' ?>"
                    data-dropoff-lat="<?= $request['dropoff_lat'] ?? '' ?>"
                    data-dropoff-lng="<?= $request['dropoff_lng'] ?? '' ?>"
                    style="width: 100%; margin-top: 10px; background: #091133; color: white; border: none; padding: 10px; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    <i class="bi bi-map"></i> View on Map
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Assigned Rides Section -->
        <div class="assigned-rides-section">
            <div class="section-header">
                <div>
                    <div class="section-title">
                        <i class="bi bi-info-circle"></i>
                        Active Rides
                    </div>
                    <div class="section-subtitle">You have <?= count($assigned_rides) ?> active ride(s)</div>
                </div>
            </div>
            
            <?php if (empty($assigned_rides) && empty($pending_requests)): ?>
                <div class="ride-card">
                    <div class="empty-state">
                        <i class="bi bi-inbox empty-state-icon"></i>
                        <p class="empty-state-desc">When you get assigned a ride, it will appear here</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($assigned_rides)): ?>
                <?php foreach ($assigned_rides as $ride): ?>
                <div class="ride-card">
                    <div class="ride-card-header">
                        <div class="ride-id-section">
                            <div class="booking-id">Booking ID: BK-<?= str_pad($ride['id'], 3, '0', STR_PAD_LEFT) ?></div>
                            <span class="status-badge <?= $ride['status'] === 'in-progress' ? 'in-progress' : 'confirmed' ?>">
                                <?= $ride['status'] === 'in-progress' ? 'In Progress' : 'Confirmed' ?>
                            </span>
                        </div>
                        <div class="ride-fare">₱<?= number_format($ride['fare'], 0) ?></div>
                    </div>

                    <div class="rider-info">
                        <i class="bi bi-person-fill"></i>
                        <span class="rider-name">Rider: <?= htmlspecialchars($ride['rider_name']) ?></span>
                        <div class="rider-phone">
                            <i class="bi bi-telephone-fill"></i>
                            <span><?= htmlspecialchars($ride['phone']) ?></span>
                        </div>
                    </div>

                    <div class="location-item">
                        <i class="bi bi-geo-alt-fill location-icon from"></i>
                        <div class="location-content">
                            <div class="location-label">From</div>
                            <div class="location-value"><?= htmlspecialchars($ride['pickup_location']) ?></div>
                        </div>
                    </div>

                    <div class="location-item">
                        <i class="bi bi-geo-alt-fill location-icon to"></i>
                        <div class="location-content">
                            <div class="location-label">To</div>
                            <div class="location-value"><?= htmlspecialchars($ride['destination']) ?></div>
                        </div>
                    </div>

                    <div class="ride-footer">
                        <div class="ride-time">
                            <i class="bi bi-clock"></i>
                            <span>Requested: <?= date('g:i A', strtotime($ride['created_at'])) ?></span>
                        </div>
                        
                        <?php if ($ride['status'] === 'confirmed' || $ride['status'] === 'arrived'): ?>
                            <button class="btn-start-ride" data-action="start-ride" data-booking-id="<?= $ride['id'] ?>">
                                <i class="bi bi-play-circle-fill"></i>
                                Start Ride
                            </button>
                        <?php else: ?>
                            <button class="btn-start-ride" data-action="complete-ride" data-booking-id="<?= $ride['id'] ?>" style="background: #ef4444;">
                                <i class="bi bi-geo-alt-fill"></i>
                                Drop Off
                            </button>
                        <?php endif; ?>
                    </div>
                    <button class="btn view-map-btn"
                        data-ride-id="<?= $ride['id'] ?>"
                        data-pickup-location="<?= htmlspecialchars($ride['pickup_location']) ?>"
                        data-dropoff-location="<?= htmlspecialchars($ride['destination']) ?>"
                        data-pickup-lat="<?= $ride['pickup_lat'] ?? '' ?>"
                        data-pickup-lng="<?= $ride['pickup_lng'] ?? '' ?>"
                        data-dropoff-lat="<?= $ride['dropoff_lat'] ?? '' ?>"
                        data-dropoff-lng="<?= $ride['dropoff_lng'] ?? '' ?>"
                        style="width: 100%; margin-top: 10px; background: #091133; color: white; border: none; padding: 10px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.875rem;">
                        <i class="bi bi-map"></i> View Route on Map
                    </button>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <button class="tab-button active" data-tab="history">Trip History</button>
            <button class="tab-button" data-tab="profile">Profile</button>
        </div>

        <!-- Trip History Tab -->
        <div class="tab-content" data-tab-content="history">
            <div class="trip-history-section">
                <div class="trip-history-header">
                    <div class="trip-history-title">
                        <i class="bi bi-clock-history"></i>
                        Driver Profile
                    </div>
                    <div class="trip-history-desc">Your information and vehicle details</div>
                </div>

                <?php if (empty($completed_trips)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox empty-state-icon"></i>
                        <p class="empty-state-title">No completed trips yet</p>
                    </div>
                <?php else: ?>
                    <div style="display: grid; gap: 1rem;">
                        <?php foreach ($completed_trips as $trip): ?>
                        <div class="ride-card">
                            <div class="ride-card-header">
                                <div class="ride-id-section">
                                    <div class="booking-id">Booking ID: BK-<?= str_pad($trip['id'], 3, '0', STR_PAD_LEFT) ?></div>
                                    <div style="font-size: 0.8125rem; color: #64748b;">
                                        <i class="bi bi-person"></i> <?= htmlspecialchars($trip['rider_name']) ?>
                                    </div>
                                </div>
                                <div class="ride-fare">₱<?= number_format($trip['fare'], 0) ?></div>
                            </div>

                            <div class="location-item">
                                <i class="bi bi-geo-alt-fill location-icon from"></i>
                                <div class="location-content">
                                    <div class="location-label">From</div>
                                    <div class="location-value"><?= htmlspecialchars($trip['pickup_location']) ?></div>
                                </div>
                            </div>

                            <div class="location-item">
                                <i class="bi bi-geo-alt-fill location-icon to"></i>
                                <div class="location-content">
                                    <div class="location-label">To</div>
                                    <div class="location-value"><?= htmlspecialchars($trip['destination']) ?></div>
                                </div>
                            </div>

                            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; font-size: 0.875rem; color: #64748b;">
                                <i class="bi bi-calendar3"></i> <?= date('M d, Y', strtotime($trip['created_at'])) ?> • 
                                <i class="bi bi-clock"></i> <?= date('g:i A', strtotime($trip['created_at'])) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Profile Tab -->
        <div class="tab-content" data-tab-content="profile" style="display: none;">
            <div class="profile-section">
                <div class="profile-section-title">Driver Profile</div>
                <div class="profile-section-desc">Your information and vehicle details</div>
                
                <!-- Personal Information Card -->
                <div class="ride-card mb-4">
                    <div style="display: flex; align-items: start; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #10b981 0%, #059669 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: 700; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                            <?php 
                                $nameParts = explode(' ', $driver_name);
                                $initials = '';
                                foreach ($nameParts as $part) {
                                    $initials .= strtoupper(substr($part, 0, 1));
                                    if (strlen($initials) >= 2) break;
                                }
                                echo $initials ?: 'D';
                            ?>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="margin: 0 0 0.5rem 0; font-weight: 700; color: #0f172a;"><?= htmlspecialchars($driver_name) ?></h4>
                            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                                <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.25rem 0.75rem; background: #dcfce7; color: #166534; border-radius: 20px; font-size: 0.875rem; font-weight: 600;">
                                    <i class="bi bi-patch-check-fill"></i> Verified Driver
                                </span>
                                <span style="display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: #64748b;">
                                    <i class="bi bi-star-fill" style="color: #fbbf24;"></i>
                                    <strong style="color: #0f172a;"><?= number_format($driver['rating'], 1) ?></strong> Rating
                                </span>
                                <span style="display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: #64748b;">
                                    <i class="bi bi-trophy-fill" style="color: #f59e0b;"></i>
                                    <strong style="color: #0f172a;"><?= $total_stats['total_trips'] ?></strong> Trips
                                </span>
                            </div>
                        </div>
                    </div>

                    <hr style="margin: 1.5rem 0; border-color: #e2e8f0;">

                    <h6 style="font-weight: 700; margin-bottom: 1rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-person-badge" style="color: #10b981;"></i>
                        Personal Information
                    </h6>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Full Name</label>
                            <div style="padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #0f172a; font-weight: 500;">
                                <?= htmlspecialchars($driver['name']) ?>
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Email Address</label>
                            <div style="padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #0f172a; font-weight: 500;">
                                <?= htmlspecialchars($driver['email'] ?? 'Not set') ?>
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Phone Number</label>
                            <div style="padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #0f172a; font-weight: 500;">
                                <?= htmlspecialchars($driver['phone'] ?? 'Not set') ?>
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Status</label>
                            <div style="padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-weight: 600;">
                                <?php if ($driver['status'] === 'available'): ?>
                                    <span style="color: #10b981;"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Available</span>
                                <?php else: ?>
                                    <span style="color: #64748b;"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Offline</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Information Card -->
                <div class="ride-card mb-4">
                    <h6 style="font-weight: 700; margin-bottom: 1rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-truck" style="color: #10b981;"></i>
                        Vehicle Information
                    </h6>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Plate Number</label>
                            <div style="padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #0f172a; font-weight: 600; font-family: monospace; font-size: 1.125rem; text-align: center; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #fbbf24;">
                                <?= htmlspecialchars($driver['plate_number'] ?? 'N/A') ?>
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Vehicle Type</label>
                            <div style="padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #0f172a; font-weight: 500;">
                                <i class="bi bi-bicycle" style="color: #10b981;"></i> Tricycle
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Tricycle Number</label>
                            <div style="padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #0f172a; font-weight: 500;">
                                <?= htmlspecialchars($driver['tricycle_number'] ?? 'Not set') ?>
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">License Number</label>
                            <div style="padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #0f172a; font-weight: 500;">
                                <?= htmlspecialchars($driver['license_number'] ?? 'Not set') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Statistics Card -->
                <div class="ride-card mb-4">
                    <h6 style="font-weight: 700; margin-bottom: 1rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-graph-up-arrow" style="color: #10b981;"></i>
                        Performance Statistics
                    </h6>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div style="text-align: center; padding: 1.5rem; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                            <div style="font-size: 2rem; font-weight: 700; color: #0f172a; margin-bottom: 0.5rem;">
                                <?= $total_stats['total_trips'] ?>
                            </div>
                            <div style="font-size: 0.875rem; color: #64748b; font-weight: 600;">Total Trips</div>
                            <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem;">All time</div>
                        </div>
                        
                        <div style="text-align: center; padding: 1.5rem; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                            <div style="font-size: 2rem; font-weight: 700; color: #0f172a; margin-bottom: 0.5rem;">
                                <?= number_format($driver['rating'], 1) ?>
                            </div>
                            <div style="font-size: 0.875rem; color: #64748b; font-weight: 600;">Average Rating</div>
                            <div style="font-size: 0.75rem; color: #fbbf24; margin-top: 0.25rem;">
                                <?php 
                                    $stars = round($driver['rating']);
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $stars ? '★' : '☆';
                                    }
                                ?>
                            </div>
                        </div>
                        
                        <div style="text-align: center; padding: 1.5rem; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                            <div style="font-size: 2rem; font-weight: 700; color: #0f172a; margin-bottom: 0.5rem;">
                                ₱<?= number_format($total_stats['total_earnings'], 0) ?>
                            </div>
                            <div style="font-size: 0.875rem; color: #64748b; font-weight: 600;">Total Earnings</div>
                            <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem;">Lifetime income</div>
                        </div>
                        
                        <div style="text-align: center; padding: 1.5rem; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                            <div style="font-size: 2rem; font-weight: 700; color: #0f172a; margin-bottom: 0.5rem;">
                                ₱<?= number_format($today_stats['today_earnings'], 0) ?>
                            </div>
                            <div style="font-size: 0.875rem; color: #64748b; font-weight: 600;">Today's Earnings</div>
                            <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem;"><?= $today_stats['today_trips'] ?> trips today</div>
                        </div>
                    </div>
                </div>

                <!-- Account Details Card -->
                <div class="ride-card mb-4">
                    <h6 style="font-weight: 700; margin-bottom: 1rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-info-circle" style="color: #10b981;"></i>
                        Account Details
                    </h6>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Driver ID</label>
                            <div style="padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #0f172a; font-weight: 500; font-family: monospace;">
                                #DRV-<?= str_pad($driver['id'], 4, '0', STR_PAD_LEFT) ?>
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Member Since</label>
                            <div style="padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #0f172a; font-weight: 500;">
                                <?php 
                                    if (!empty($driver['created_at'])) {
                                        $memberDate = new DateTime($driver['created_at']);
                                        echo $memberDate->format('F d, Y');
                                    } else {
                                        echo 'Recently joined';
                                    }
                                ?>
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Average Fare</label>
                            <div style="padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #0f172a; font-weight: 500;">
                                ₱<?= $total_stats['total_trips'] > 0 ? number_format($total_stats['total_earnings'] / $total_stats['total_trips'], 0) : '0' ?> per trip
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Account Status</label>
                            <div style="padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                                <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.25rem 0.75rem; background: #dcfce7; color: #166534; border-radius: 20px; font-size: 0.875rem; font-weight: 600;">
                                    <i class="bi bi-check-circle-fill"></i> Active
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Application Information Card -->
                <?php if ($application): ?>
                <div class="ride-card">
                    <h6 style="font-weight: 700; margin-bottom: 1rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-file-earmark-text" style="color: #10b981;"></i>
                        Application Information
                    </h6>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Application Date</label>
                            <div style="padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #0f172a; font-weight: 500;">
                                <?php 
                                    $appDate = new DateTime($application['created_at']);
                                    echo $appDate->format('F d, Y g:i A');
                                ?>
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Application Status</label>
                            <div style="padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                                <?php
                                    $statusColors = [
                                        'pending' => ['bg' => '#fef3c7', 'text' => '#92400e', 'icon' => 'clock-fill'],
                                        'approved' => ['bg' => '#dcfce7', 'text' => '#166534', 'icon' => 'check-circle-fill'],
                                        'rejected' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'icon' => 'x-circle-fill']
                                    ];
                                    $status = $application['status'] ?? 'pending';
                                    $statusStyle = $statusColors[$status] ?? $statusColors['pending'];
                                ?>
                                <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.25rem 0.75rem; background: <?= $statusStyle['bg'] ?>; color: <?= $statusStyle['text'] ?>; border-radius: 20px; font-size: 0.875rem; font-weight: 600;">
                                    <i class="bi bi-<?= $statusStyle['icon'] ?>"></i> <?= ucfirst($status) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Contact Number</label>
                            <div style="padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #0f172a; font-weight: 500;">
                                <?= htmlspecialchars($application['phone'] ?? 'N/A') ?>
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Address</label>
                            <div style="padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #0f172a; font-weight: 500;">
                                <?= htmlspecialchars($application['address'] ?? 'N/A') ?>
                            </div>
                        </div>
                    </div>

                    <hr style="margin: 1.5rem 0; border-color: #e2e8f0;">

                    <h6 style="font-weight: 700; margin-bottom: 1rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="bi bi-folder2-open" style="color: #10b981;"></i>
                        Submitted Documents
                    </h6>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
                        <!-- Driver's License -->
                        <div style="padding: 1rem; background: #ffffff; border: 2px solid #e2e8f0; border-radius: 8px;">
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                                <div style="width: 40px; height: 40px; background: #ecfdf5; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-credit-card-2-front" style="font-size: 1.25rem; color: #10b981;"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #0f172a; font-size: 0.875rem;">Driver's License</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">License Number: <?= htmlspecialchars($application['license_number'] ?? 'N/A') ?></div>
                                </div>
                            </div>
                            <?php if (!empty($application['license_document'])): ?>
                                <a href="<?= htmlspecialchars($application['license_document']) ?>" target="_blank" style="display: block; padding: 0.5rem 1rem; background: #10b981; color: white; text-align: center; border-radius: 6px; text-decoration: none; font-size: 0.875rem; font-weight: 600;">
                                    <i class="bi bi-eye"></i> View Document
                                </a>
                            <?php else: ?>
                                <div style="padding: 0.5rem 1rem; background: #f1f5f9; color: #64748b; text-align: center; border-radius: 6px; font-size: 0.875rem;">
                                    No document uploaded
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Barangay Clearance -->
                        <div style="padding: 1rem; background: #ffffff; border: 2px solid #e2e8f0; border-radius: 8px;">
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                                <div style="width: 40px; height: 40px; background: #ecfdf5; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-file-earmark-check" style="font-size: 1.25rem; color: #10b981;"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #0f172a; font-size: 0.875rem;">Barangay Clearance</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">Residence verification</div>
                                </div>
                            </div>
                            <?php if (!empty($application['clearance_document'])): ?>
                                <a href="<?= htmlspecialchars($application['clearance_document']) ?>" target="_blank" style="display: block; padding: 0.5rem 1rem; background: #10b981; color: white; text-align: center; border-radius: 6px; text-decoration: none; font-size: 0.875rem; font-weight: 600;">
                                    <i class="bi bi-eye"></i> View Document
                                </a>
                            <?php else: ?>
                                <div style="padding: 0.5rem 1rem; background: #f1f5f9; color: #64748b; text-align: center; border-radius: 6px; font-size: 0.875rem;">
                                    No document uploaded
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Police Clearance -->
                        <div style="padding: 1rem; background: #ffffff; border: 2px solid #e2e8f0; border-radius: 8px;">
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                                <div style="width: 40px; height: 40px; background: #ecfdf5; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-shield-check" style="font-size: 1.25rem; color: #10b981;"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #0f172a; font-size: 0.875rem;">Police Clearance</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">Background verification</div>
                                </div>
                            </div>
                            <?php if (!empty($application['clearance_document'])): ?>
                                <a href="<?= htmlspecialchars($application['clearance_document']) ?>" target="_blank" style="display: block; padding: 0.5rem 1rem; background: #10b981; color: white; text-align: center; border-radius: 6px; text-decoration: none; font-size: 0.875rem; font-weight: 600;">
                                    <i class="bi bi-eye"></i> View Document
                                </a>
                            <?php else: ?>
                                <div style="padding: 0.5rem 1rem; background: #f1f5f9; color: #64748b; text-align: center; border-radius: 6px; font-size: 0.875rem;">
                                    No document uploaded
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Vehicle OR/CR -->
                        <div style="padding: 1rem; background: #ffffff; border: 2px solid #e2e8f0; border-radius: 8px;">
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                                <div style="width: 40px; height: 40px; background: #ecfdf5; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-file-text" style="font-size: 1.25rem; color: #10b981;"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #0f172a; font-size: 0.875rem;">Vehicle OR/CR</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">Registration documents</div>
                                </div>
                            </div>
                            <?php if (!empty($application['registration_document'])): ?>
                                <a href="<?= htmlspecialchars($application['registration_document']) ?>" target="_blank" style="display: block; padding: 0.5rem 1rem; background: #10b981; color: white; text-align: center; border-radius: 6px; text-decoration: none; font-size: 0.875rem; font-weight: 600;">
                                    <i class="bi bi-eye"></i> View Document
                                </a>
                            <?php else: ?>
                                <div style="padding: 0.5rem 1rem; background: #f1f5f9; color: #64748b; text-align: center; border-radius: 6px; font-size: 0.875rem;">
                                    No document uploaded
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Profile Photo -->
                        <div style="padding: 1rem; background: #ffffff; border: 2px solid #e2e8f0; border-radius: 8px;">
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                                <div style="width: 40px; height: 40px; background: #ecfdf5; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-person-badge" style="font-size: 1.25rem; color: #10b981;"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #0f172a; font-size: 0.875rem;">Profile Photo</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">2x2 ID picture</div>
                                </div>
                            </div>
                            <?php if (!empty($application['photo_document'])): ?>
                                <a href="<?= htmlspecialchars($application['photo_document']) ?>" target="_blank" style="display: block; padding: 0.5rem 1rem; background: #10b981; color: white; text-align: center; border-radius: 6px; text-decoration: none; font-size: 0.875rem; font-weight: 600;">
                                    <i class="bi bi-eye"></i> View Photo
                                </a>
                            <?php else: ?>
                                <div style="padding: 0.5rem 1rem; background: #f1f5f9; color: #64748b; text-align: center; border-radius: 6px; font-size: 0.875rem;">
                                    No photo uploaded
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tricycle Photo -->
                        <div style="padding: 1rem; background: #ffffff; border: 2px solid #e2e8f0; border-radius: 8px;">
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                                <div style="width: 40px; height: 40px; background: #ecfdf5; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-bicycle" style="font-size: 1.25rem; color: #10b981;"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #0f172a; font-size: 0.875rem;">Tricycle Photo</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">Vehicle image</div>
                                </div>
                            </div>
                            <?php if (!empty($application['photo_document'])): ?>
                                <a href="<?= htmlspecialchars($application['photo_document']) ?>" target="_blank" style="display: block; padding: 0.5rem 1rem; background: #10b981; color: white; text-align: center; border-radius: 6px; text-decoration: none; font-size: 0.875rem; font-weight: 600;">
                                    <i class="bi bi-eye"></i> View Photo
                                </a>
                            <?php else: ?>
                                <div style="padding: 0.5rem 1rem; background: #f1f5f9; color: #64748b; text-align: center; border-radius: 6px; font-size: 0.875rem;">
                                    No photo uploaded
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($application['notes']) || !empty($application['rejection_reason'])): ?>
                    <div style="margin-top: 1.5rem; padding: 1rem; background: #fffbeb; border: 1px solid #fbbf24; border-radius: 8px;">
                        <div style="display: flex; align-items: start; gap: 0.75rem;">
                            <i class="bi bi-info-circle-fill" style="color: #f59e0b; font-size: 1.25rem;"></i>
                            <div>
                                <div style="font-weight: 600; color: #92400e; margin-bottom: 0.25rem;">Admin Notes:</div>
                                <div style="color: #78350f;"><?= nl2br(htmlspecialchars($application['notes'] ?? $application['rejection_reason'] ?? '')) ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="ride-card">
                    <div class="empty-state">
                        <i class="bi bi-file-earmark-x empty-state-icon"></i>
                        <p class="empty-state-title">No application found</p>
                        <p style="color: #64748b; font-size: 0.875rem;">Your driver application information will appear here once submitted.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script src="assets/js/pages/driver-dashboard.js"></script>
    
    <!-- Real-time WebSocket Integration -->
    <script src="assets/js/realtime-client.js"></script>
    <script src="assets/js/driver-realtime.js"></script>
    <script>
        // Initialize real-time updates
        initDriverRealtime(<?= $_SESSION['user_id'] ?>);
    </script>
</body>
</html>
