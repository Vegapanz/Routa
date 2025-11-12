<?php
session_start();
require_once 'php/config.php';

// Check if user is logged in and is a driver
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_driver']) || !$_SESSION['is_driver']) {
    header('Location: login.php');
    exit();
}

$driver_id = $_SESSION['user_id'];

// Fetch driver data from tricycle_drivers table to ensure correct data
$stmt = $pdo->prepare("SELECT * FROM tricycle_drivers WHERE id = ?");
$stmt->execute([$driver_id]);
$driver_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$driver_data) {
    // Driver not found in drivers table - invalid session
    session_destroy();
    header('Location: login.php');
    exit();
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
                
                <div class="ride-card">
                    <div class="empty-state">
                        <i class="bi bi-person-circle empty-state-icon"></i>
                        <p class="empty-state-title">Profile information coming soon</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/pages/driver-dashboard.js"></script>
</body>
</html>
