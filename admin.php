<?php
session_start();
require_once 'php/config.php';
require_once 'php/admin_functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit();
}

// Verify admin exists in admins table
$admin_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin_data) {
    // Admin not found - invalid session
    session_destroy();
    header('Location: login.php');
    exit();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    switch ($_POST['action']) {
        case 'assign_booking':
            if (isset($_POST['booking_id']) && isset($_POST['driver_id'])) {
                error_log("Attempting to assign booking " . $_POST['booking_id'] . " to driver " . $_POST['driver_id']);
                if (assignBooking($pdo, $_POST['booking_id'], $_POST['driver_id'])) {
                    $response['success'] = true;
                    $response['message'] = 'Booking assigned successfully';
                } else {
                    $response['message'] = 'Failed to assign booking. Please check error logs.';
                    error_log("assignBooking returned false for booking " . $_POST['booking_id']);
                }
            } else {
                $response['message'] = 'Missing booking_id or driver_id';
                error_log("Missing parameters: booking_id=" . (isset($_POST['booking_id']) ? 'set' : 'missing') . 
                         ", driver_id=" . (isset($_POST['driver_id']) ? 'set' : 'missing'));
            }
            break;

        case 'reject_booking':
            if (isset($_POST['booking_id'])) {
                error_log("Attempting to reject booking " . $_POST['booking_id']);
                if (rejectBooking($pdo, $_POST['booking_id'])) {
                    $response['success'] = true;
                    $response['message'] = 'Booking rejected successfully';
                } else {
                    $response['message'] = 'Failed to reject booking. Please check error logs.';
                    error_log("rejectBooking returned false for booking " . $_POST['booking_id']);
                }
            } else {
                $response['message'] = 'Missing booking_id';
            }
            break;

        case 'approve_application':
            if (isset($_POST['application_id'])) {
                error_log("Attempting to approve application " . $_POST['application_id']);
                if (approveDriverApplication($pdo, $_POST['application_id'])) {
                    $response['success'] = true;
                    $response['message'] = 'Application approved successfully. Driver added to system.';
                } else {
                    $response['message'] = 'Failed to approve application. Please check error logs.';
                }
            } else {
                $response['message'] = 'Missing application_id';
            }
            break;

        case 'reject_application':
            if (isset($_POST['application_id'])) {
                error_log("Attempting to reject application " . $_POST['application_id']);
                if (rejectDriverApplication($pdo, $_POST['application_id'])) {
                    $response['success'] = true;
                    $response['message'] = 'Application rejected successfully.';
                } else {
                    $response['message'] = 'Failed to reject application. Please check error logs.';
                }
            } else {
                $response['message'] = 'Missing application_id';
            }
            break;

        case 'get_application_details':
            if (isset($_POST['application_id'])) {
                $details = getApplicationDetails($pdo, $_POST['application_id']);
                if ($details) {
                    $response['success'] = true;
                    $response['data'] = $details;
                } else {
                    $response['message'] = 'Application not found';
                }
            } else {
                $response['message'] = 'Missing application_id';
            }
            break;

        case 'get_driver_details':
            if (isset($_POST['driver_id'])) {
                $stmt = $pdo->prepare("SELECT d.*, COUNT(r.id) as total_trips 
                    FROM tricycle_drivers d 
                    LEFT JOIN ride_history r ON d.id = r.driver_id AND r.status = 'completed'
                    WHERE d.id = ?
                    GROUP BY d.id");
                $stmt->execute([$_POST['driver_id']]);
                $driver = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($driver) {
                    $response['success'] = true;
                    $response['data'] = $driver;
                } else {
                    $response['message'] = 'Driver not found';
                }
            } else {
                $response['message'] = 'Missing driver_id';
            }
            break;
    }

    echo json_encode($response);
    exit();
}

// Fetch dashboard stats
$dashboard = getDashboardStats($pdo);
if (!$dashboard) {
    $dashboard = [
        'total_revenue' => 0,
        'total_bookings' => 0,
        'active_drivers' => 0,
        'pending_bookings' => 0
    ];
}

// Fetch pending bookings
$pending_bookings = getPendingBookings($pdo) ?: [];

// Fetch all bookings
$stmt = $pdo->prepare("SELECT r.*, u.name as rider_name, u.phone, d.name as driver_name 
    FROM ride_history r 
    LEFT JOIN users u ON r.user_id = u.id 
    LEFT JOIN tricycle_drivers d ON r.driver_id = d.id 
    ORDER BY r.created_at DESC LIMIT 20");
$stmt->execute();
$all_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch drivers
$stmt = $pdo->prepare("SELECT d.*, COUNT(r.id) as total_trips 
    FROM tricycle_drivers d 
    LEFT JOIN ride_history r ON d.id = r.driver_id AND r.status = 'completed'
    GROUP BY d.id 
    ORDER BY d.id");
$stmt->execute();
$drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch users
$stmt = $pdo->prepare("SELECT u.*, COUNT(r.id) as total_trips 
    FROM users u 
    LEFT JOIN ride_history r ON u.id = r.user_id 
    GROUP BY u.id 
    ORDER BY u.created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch driver applications
$stmt = $pdo->prepare("SELECT * FROM driver_applications ORDER BY application_date DESC");
$stmt->execute();
$driver_applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch available drivers for assignment
$available_drivers = getAvailableDrivers($pdo) ?: [];

// Fetch analytics data for charts
// Daily bookings for the past 7 days
$stmt = $pdo->prepare("
    SELECT DATE(created_at) as date, COUNT(*) as count 
    FROM ride_history 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute();
$daily_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Monthly revenue for the past 6 months
$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(fare) as revenue 
    FROM ride_history 
    WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");
$stmt->execute();
$monthly_revenue = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Booking status distribution
$stmt = $pdo->prepare("
    SELECT status, COUNT(*) as count 
    FROM ride_history 
    GROUP BY status
");
$stmt->execute();
$status_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate key metrics
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM ride_history WHERE status = 'completed'");
$stmt->execute();
$completed_rides = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM ride_history WHERE status IN ('confirmed', 'in-progress')");
$stmt->execute();
$active_rides = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM ride_history WHERE status = 'pending'");
$stmt->execute();
$pending_confirmations = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->prepare("SELECT AVG(fare) as avg_fare FROM ride_history WHERE status = 'completed'");
$stmt->execute();
$avg_fare = $stmt->fetch(PDO::FETCH_ASSOC)['avg_fare'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Routa Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="shortcut icon" href="assets/images/Logo.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-light bg-white border-bottom shadow-sm">
        <div class="container-fluid px-4 py-3">
            <a class="navbar-brand d-flex align-items-center" href="admin.php">
                <img src="assets/images/Logo.png" alt="Routa" height="32" class="me-2">
                <span class="fw-bold fs-5" style="color: #10b981;">Routa</span>
                <span class="text-muted ms-2" style="font-size: 0.875rem;">Admin</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted">Admin User</span>
                <a href="php/logout.php" class="btn btn-sm" style="border: 1px solid #e5e7eb; color: #6b7280;">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid px-4 py-4" style="max-width: 1400px; margin: 0 auto;">
        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-label">Total Revenue</div>
                        <i class="bi bi-currency-dollar stat-icon" style="color: #6366f1;"></i>
                    </div>
                    <div class="stat-value">₱<?= number_format($dashboard['total_revenue'] ?? 135, 0) ?></div>
                    <div class="stat-change positive">+20.1% from last month</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-label">Total Bookings</div>
                        <i class="bi bi-arrow-repeat stat-icon" style="color: #10b981;"></i>
                    </div>
                    <div class="stat-value"><?= $dashboard['total_bookings'] ?? 6 ?></div>
                    <div class="stat-change positive">+15.3% from last month</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-label">Active Drivers</div>
                        <i class="bi bi-person-check stat-icon" style="color: #06b6d4;"></i>
                    </div>
                    <div class="stat-value"><?= $dashboard['active_drivers'] ?? 3 ?></div>
                    <div class="stat-meta">5 total drivers</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-label">Pending Bookings</div>
                        <i class="bi bi-clock-history stat-icon" style="color: #f59e0b;"></i>
                    </div>
                    <div class="stat-value"><?= $dashboard['pending_bookings'] ?? 2 ?></div>
                    <div class="stat-meta">Awaiting confirmation</div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs admin-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                    Pending Bookings
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="all-bookings-tab" data-bs-toggle="tab" data-bs-target="#all-bookings" type="button" role="tab">
                    All Bookings
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="analytics-tab" data-bs-toggle="tab" data-bs-target="#analytics" type="button" role="tab">
                    Analytics
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="drivers-tab" data-bs-toggle="tab" data-bs-target="#drivers" type="button" role="tab">
                    Drivers
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                    Users
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="applications-tab" data-bs-toggle="tab" data-bs-target="#applications" type="button" role="tab">
                    Driver Applications
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="adminTabsContent">
            
            <!-- Pending Bookings Tab -->
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                <div class="section-header mb-4">
                    <h5 class="section-title">Pending Bookings</h5>
                    <p class="section-subtitle">Review and confirm incoming booking requests</p>
                </div>

                <?php if (empty($pending_bookings)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox" style="font-size: 48px; color: #cbd5e1;"></i>
                        <p class="text-muted mt-3">No pending bookings at the moment</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pending_bookings as $booking): ?>
                    <div class="booking-card mb-3">
                        <div class="booking-card-content">
                            <div class="booking-header-row">
                                <div class="booking-id">Booking ID: BK-<?= str_pad($booking['booking_id'], 3, '0', STR_PAD_LEFT) ?></div>
                                <span class="badge bg-warning text-dark px-3 py-2">Pending</span>
                            </div>
                            
                            <div class="booking-info-grid">
                                <div class="info-item">
                                    <i class="bi bi-person-fill"></i>
                                    <span class="info-label">Rider:</span>
                                    <span class="info-value"><?= htmlspecialchars($booking['rider_name']) ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="bi bi-telephone-fill"></i>
                                    <span><?= htmlspecialchars($booking['phone']) ?></span>
                                </div>
                            </div>

                            <div class="booking-locations">
                                <div class="location-item">
                                    <i class="bi bi-geo-alt-fill text-success"></i>
                                    <div>
                                        <div class="location-label">From:</div>
                                        <div class="location-value"><?= htmlspecialchars($booking['pickup_location']) ?></div>
                                    </div>
                                </div>
                                <div class="location-item">
                                    <i class="bi bi-geo-alt-fill text-danger"></i>
                                    <div>
                                        <div class="location-label">To:</div>
                                        <div class="location-value"><?= htmlspecialchars($booking['destination']) ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="booking-footer-row">
                                <div class="booking-fare-large">₱<?= number_format($booking['fare'], 0) ?></div>
                                <div class="booking-time-info">
                                    <i class="bi bi-clock"></i>
                                    <span>Requested: <?= date('g:i A', strtotime($booking['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="booking-actions">
                            <button class="btn btn-confirm" onclick="confirmBooking('<?= $booking['booking_id'] ?>')">
                                <i class="bi bi-check-circle-fill me-2"></i> Confirm & Assign Driver
                            </button>
                            <button class="btn btn-reject" onclick="rejectBooking('<?= $booking['booking_id'] ?>')">
                                <i class="bi bi-x-circle-fill me-2"></i> Reject Booking
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- All Bookings Tab -->
            <div class="tab-pane fade" id="all-bookings" role="tabpanel">
                <div class="section-header mb-4">
                    <h5 class="section-title">Recent Bookings</h5>
                    <p class="section-subtitle">Manage and monitor all ride bookings</p>
                    <input type="text" class="form-control search-input" placeholder="Search bookings..." id="searchBookings">
                </div>

                <div class="table-responsive">
                    <table class="table data-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Rider</th>
                                <th>Driver</th>
                                <th>Route</th>
                                <th>Fare</th>
                                <th>Status</th>
                                <th>Time</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_bookings as $booking): ?>
                            <tr>
                                <td class="fw-semibold">BK-<?= str_pad($booking['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <i class="bi bi-person"></i> <?= htmlspecialchars($booking['rider_name']) ?>
                                </td>
                                <td>
                                    <?php if ($booking['driver_name']): ?>
                                        <i class="bi bi-bicycle"></i> <?= htmlspecialchars($booking['driver_name']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small">
                                    <div><i class="bi bi-geo-alt-fill text-success"></i> <?= substr(htmlspecialchars($booking['pickup_location']), 0, 30) ?>...</div>
                                    <div><i class="bi bi-geo-alt text-danger"></i> <?= substr(htmlspecialchars($booking['destination']), 0, 30) ?>...</div>
                                </td>
                                <td class="fw-semibold">₱<?= number_format($booking['fare'], 0) ?></td>
                                <td>
                                    <?php 
                                    $statusClass = [
                                        'completed' => 'success',
                                        'pending' => 'warning',
                                        'in-progress' => 'info',
                                        'confirmed' => 'primary'
                                    ];
                                    $class = $statusClass[$booking['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $class ?>"><?= htmlspecialchars($booking['status']) ?></span>
                                </td>
                                <td class="text-muted small">
                                    <i class="bi bi-clock"></i> <?= date('g:i A', strtotime($booking['created_at'])) ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-link text-muted"><i class="bi bi-three-dots-vertical"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Analytics Tab -->
            <div class="tab-pane fade" id="analytics" role="tabpanel">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="analytics-card">
                            <h6 class="analytics-title">Daily Bookings</h6>
                            <p class="analytics-subtitle">Booking trends over the week</p>
                            <div style="height: 300px; padding: 20px;">
                                <canvas id="dailyBookingsChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="analytics-card">
                            <h6 class="analytics-title">Monthly Revenue</h6>
                            <p class="analytics-subtitle">Revenue trends over 6 months</p>
                            <div style="height: 300px; padding: 20px;">
                                <canvas id="monthlyRevenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="analytics-card">
                            <h6 class="analytics-title">Booking Status Distribution</h6>
                            <p class="analytics-subtitle">Current status of all bookings</p>
                            <div style="height: 300px; padding: 20px;">
                                <canvas id="statusDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="analytics-card">
                            <h6 class="analytics-title">Key Metrics</h6>
                            <p class="analytics-subtitle">Performance overview</p>
                            <div class="key-metrics">
                                <div class="metric-item">
                                    <div class="metric-value text-success"><?= $completed_rides ?></div>
                                    <div class="metric-label">Completed Rides</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value text-info"><?= $active_rides ?></div>
                                    <div class="metric-label">Active Rides</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value text-warning"><?= $pending_confirmations ?></div>
                                    <div class="metric-label">Pending Confirmation</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value text-primary">₱<?= number_format($avg_fare, 0) ?></div>
                                    <div class="metric-label">Average Fare</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Drivers Tab -->
            <div class="tab-pane fade" id="drivers" role="tabpanel">
                <div class="section-header mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="section-title">Driver Management</h5>
                        <p class="section-subtitle">Monitor and manage all registered drivers</p>
                    </div>
                    <div>
                        <input type="text" class="form-control search-input d-inline-block me-2" placeholder="Search drivers..." style="width: 250px;" id="searchDrivers">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table data-table">
                        <thead>
                            <tr>
                                <th>Driver ID</th>
                                <th>Name</th>
                                <th>Tricycle No.</th>
                                <th>Rating</th>
                                <th>Total Trips</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($drivers as $driver): ?>
                            <tr>
                                <td class="fw-semibold">DRV-<?= str_pad($driver['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <div class="avatar-name">
                                        <span class="avatar"><?= strtoupper(substr($driver['name'], 0, 2)) ?></span>
                                        <?= htmlspecialchars($driver['name']) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($driver['tricycle_number'] ?? 'TRY-123') ?></td>
                                <td>
                                    <i class="bi bi-star-fill text-warning"></i> <?= number_format(4.5 + (rand(0, 4) / 10), 1) ?>
                                </td>
                                <td><?= $driver['total_trips'] ?> trips</td>
                                <td>
                                    <?php if ($driver['status'] == 'available'): ?>
                                        <span class="badge bg-success">active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">offline</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewDriverDetails(<?= $driver['id'] ?>)">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Users Tab -->
            <div class="tab-pane fade" id="users" role="tabpanel">
                <div class="section-header mb-4">
                    <h5 class="section-title">User Management</h5>
                    <p class="section-subtitle">View and manage all registered users</p>
                    <input type="text" class="form-control search-input" placeholder="Search users..." id="searchUsers">
                </div>

                <div class="table-responsive">
                    <table class="table data-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Total Trips</th>
                                <th>Joined</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="fw-semibold">USR-<?= str_pad($user['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <div class="avatar-name">
                                        <span class="avatar"><?= strtoupper(substr($user['name'], 0, 2)) ?></span>
                                        <?= htmlspecialchars($user['name']) ?>
                                    </div>
                                </td>
                                <td class="text-muted"><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['phone'] ?? '+63 912 345 6789') ?></td>
                                <td><?= $user['total_trips'] ?> trips</td>
                                <td class="text-muted"><?= date('M Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-link text-muted"><i class="bi bi-three-dots-vertical"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Driver Applications Tab -->
            <div class="tab-pane fade" id="applications" role="tabpanel">
                <div class="section-header mb-4">
                    <h5 class="section-title">Driver Applications</h5>
                    <p class="section-subtitle">Review and manage driver applications</p>
                    <input type="text" class="form-control search-input" placeholder="Search applications..." id="searchApplications">
                </div>

                <?php if (empty($driver_applications)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox" style="font-size: 48px; color: #cbd5e1;"></i>
                        <p class="text-muted mt-3">No driver applications yet</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>License #</th>
                                    <th>Vehicle</th>
                                    <th>Status</th>
                                    <th>Applied</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($driver_applications as $app): ?>
                                <tr>
                                    <td class="fw-semibold">APP-<?= str_pad($app['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                    <td>
                                        <div class="avatar-name">
                                            <span class="avatar"><?= strtoupper(substr($app['first_name'], 0, 1) . substr($app['last_name'], 0, 1)) ?></span>
                                            <?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?>
                                        </div>
                                    </td>
                                    <td class="text-muted"><?= htmlspecialchars($app['email']) ?></td>
                                    <td><?= htmlspecialchars($app['phone']) ?></td>
                                    <td><?= htmlspecialchars($app['license_number']) ?></td>
                                    <td><?= htmlspecialchars($app['vehicle_make'] . ' ' . $app['vehicle_model']) ?></td>
                                    <td>
                                        <?php 
                                        $statusClass = '';
                                        $statusText = ucfirst(str_replace('_', ' ', $app['status']));
                                        switch($app['status']) {
                                            case 'pending':
                                                $statusClass = 'bg-warning text-dark';
                                                break;
                                            case 'under_review':
                                                $statusClass = 'bg-info text-white';
                                                break;
                                            case 'approved':
                                                $statusClass = 'bg-success';
                                                break;
                                            case 'rejected':
                                                $statusClass = 'bg-danger';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $statusClass ?> px-3 py-2"><?= $statusText ?></span>
                                    </td>
                                    <td class="text-muted"><?= date('M d, Y', strtotime($app['application_date'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewApplicationDetails(<?= $app['id'] ?>)">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- View Application Details Modal -->
    <div class="modal fade" id="viewApplicationModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Application Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="applicationDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="rejectApplicationBtn">Reject</button>
                    <button type="button" class="btn btn-success" id="approveApplicationBtn">Approve</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Driver Details Modal -->
    <div class="modal fade" id="viewDriverModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Driver Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="driverDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Modal -->
    <div class="modal fade" id="alertModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold" id="alertModalTitle">Notice</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="alertModalBody">
                    Message here
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold" id="confirmModalTitle">Confirm Action</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="confirmModalBody">
                    Are you sure?
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmModalBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Make available drivers accessible to JavaScript
        window.availableDrivers = <?= json_encode($available_drivers) ?>;
        
        // Analytics data
        window.analyticsData = {
            dailyBookings: <?= json_encode($daily_bookings) ?>,
            monthlyRevenue: <?= json_encode($monthly_revenue) ?>,
            statusDistribution: <?= json_encode($status_distribution) ?>
        };
    </script>
    <script src="assets/js/admin.js"></script>
    <script>
        // Initialize charts when Analytics tab is shown
        document.addEventListener('DOMContentLoaded', function() {
            const analyticsTab = document.getElementById('analytics-tab');
            let chartsInitialized = false;

            analyticsTab.addEventListener('shown.bs.tab', function() {
                if (!chartsInitialized) {
                    initializeCharts();
                    chartsInitialized = true;
                }
            });
        });

        function initializeCharts() {
            // Daily Bookings Chart
            const dailyCtx = document.getElementById('dailyBookingsChart').getContext('2d');
            const dailyData = window.analyticsData.dailyBookings;
            
            // Fill in missing days with 0
            const last7Days = [];
            const today = new Date();
            for (let i = 6; i >= 0; i--) {
                const date = new Date(today);
                date.setDate(date.getDate() - i);
                last7Days.push(date.toISOString().split('T')[0]);
            }
            
            const dailyCounts = last7Days.map(date => {
                const found = dailyData.find(d => d.date === date);
                return found ? parseInt(found.count) : 0;
            });
            
            const dailyLabels = last7Days.map(date => {
                const d = new Date(date);
                return d.toLocaleDateString('en-US', { weekday: 'short' });
            });

            new Chart(dailyCtx, {
                type: 'bar',
                data: {
                    labels: dailyLabels,
                    datasets: [{
                        label: 'Bookings',
                        data: dailyCounts,
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });

            // Monthly Revenue Chart
            const revenueCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
            const revenueData = window.analyticsData.monthlyRevenue;
            
            const revenueLabels = revenueData.map(d => {
                const [year, month] = d.month.split('-');
                const date = new Date(year, month - 1);
                return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            });
            
            const revenueValues = revenueData.map(d => parseFloat(d.revenue));

            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: revenueLabels,
                    datasets: [{
                        label: 'Revenue (₱)',
                        data: revenueValues,
                        borderColor: 'rgba(99, 102, 241, 1)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: 'rgba(99, 102, 241, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '₱' + context.parsed.y.toFixed(2);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value;
                                }
                            }
                        }
                    }
                }
            });

            // Status Distribution Chart
            const statusCtx = document.getElementById('statusDistributionChart').getContext('2d');
            const statusData = window.analyticsData.statusDistribution;
            
            const statusLabels = statusData.map(d => {
                return d.status.charAt(0).toUpperCase() + d.status.slice(1);
            });
            const statusCounts = statusData.map(d => parseInt(d.count));
            
            const statusColors = {
                'Completed': 'rgba(16, 185, 129, 0.8)',
                'Pending': 'rgba(245, 158, 11, 0.8)',
                'Confirmed': 'rgba(59, 130, 246, 0.8)',
                'In-progress': 'rgba(6, 182, 212, 0.8)',
                'Cancelled': 'rgba(239, 68, 68, 0.8)'
            };
            
            const backgroundColors = statusLabels.map(label => 
                statusColors[label] || 'rgba(156, 163, 175, 0.8)'
            );

            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusCounts,
                        backgroundColor: backgroundColors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
        }

        // View application details function
        function viewApplicationDetails(id) {
            const modal = new bootstrap.Modal(document.getElementById('viewApplicationModal'));
            const contentDiv = document.getElementById('applicationDetailsContent');
            
            // Show loading
            contentDiv.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Store application ID for approve/reject buttons
            document.getElementById('approveApplicationBtn').dataset.applicationId = id;
            document.getElementById('rejectApplicationBtn').dataset.applicationId = id;
            
            modal.show();
            
            // Fetch application details using admin.php endpoint
            fetch('admin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_application_details&application_id=${id}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayApplicationDetails(data.data);
                    } else {
                        contentDiv.innerHTML = 
                            '<div class="alert alert-danger">Failed to load application details: ' + (data.message || 'Unknown error') + '</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    contentDiv.innerHTML = 
                        '<div class="alert alert-danger">Error loading application details</div>';
                });
        }

        function displayApplicationDetails(app) {
            const html = `
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3"><i class="bi bi-person me-2"></i>Personal Information</h6>
                        <p class="mb-2"><strong>Name:</strong> ${app.first_name} ${app.middle_name || ''} ${app.last_name}</p>
                        <p class="mb-2"><strong>Date of Birth:</strong> ${app.date_of_birth}</p>
                        <p class="mb-2"><strong>Email:</strong> ${app.email}</p>
                        <p class="mb-2"><strong>Phone:</strong> ${app.phone}</p>
                        <p class="mb-2"><strong>Address:</strong> ${app.address}, ${app.barangay}, ${app.city} ${app.zip_code}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3"><i class="bi bi-card-text me-2"></i>Driver Information</h6>
                        <p class="mb-2"><strong>License #:</strong> ${app.license_number}</p>
                        <p class="mb-2"><strong>License Expiry:</strong> ${app.license_expiry}</p>
                        <p class="mb-2"><strong>Driving Experience:</strong> ${app.driving_experience}</p>
                        <p class="mb-2"><strong>Emergency Contact:</strong> ${app.emergency_name} (${app.emergency_phone})</p>
                        <p class="mb-2"><strong>Relationship:</strong> ${app.relationship}</p>
                    </div>
                    <div class="col-12">
                        <hr>
                        <h6 class="fw-bold mb-3"><i class="bi bi-truck me-2"></i>Vehicle Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Type:</strong> ${app.vehicle_type}</p>
                                <p class="mb-2"><strong>Plate #:</strong> ${app.plate_number}</p>
                                <p class="mb-2"><strong>Franchise #:</strong> ${app.franchise_number}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Make:</strong> ${app.vehicle_make}</p>
                                <p class="mb-2"><strong>Model:</strong> ${app.vehicle_model}</p>
                                <p class="mb-2"><strong>Year:</strong> ${app.vehicle_year}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <hr>
                        <h6 class="fw-bold mb-3"><i class="bi bi-file-earmark-text me-2"></i>Documents</h6>
                        <div class="row g-2">
                            ${app.license_document ? `<div class="col-md-4"><a href="${app.license_document}" target="_blank" class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-file-pdf me-1"></i> Driver's License</a></div>` : ''}
                            ${app.government_id_document ? `<div class="col-md-4"><a href="${app.government_id_document}" target="_blank" class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-file-pdf me-1"></i> Government ID</a></div>` : ''}
                            ${app.registration_document ? `<div class="col-md-4"><a href="${app.registration_document}" target="_blank" class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-file-pdf me-1"></i> Vehicle Registration</a></div>` : ''}
                            ${app.franchise_document ? `<div class="col-md-4"><a href="${app.franchise_document}" target="_blank" class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-file-pdf me-1"></i> Franchise Permit</a></div>` : ''}
                            ${app.insurance_document ? `<div class="col-md-4"><a href="${app.insurance_document}" target="_blank" class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-file-pdf me-1"></i> Insurance</a></div>` : ''}
                            ${app.clearance_document ? `<div class="col-md-4"><a href="${app.clearance_document}" target="_blank" class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-file-pdf me-1"></i> Barangay Clearance</a></div>` : ''}
                            ${app.photo_document ? `<div class="col-md-4"><a href="${app.photo_document}" target="_blank" class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-image me-1"></i> ID Photo</a></div>` : ''}
                        </div>
                    </div>
                    ${app.previous_experience ? `
                    <div class="col-12">
                        <hr>
                        <h6 class="fw-bold mb-3"><i class="bi bi-chat-left-text me-2"></i>Previous Experience</h6>
                        <p class="text-muted">${app.previous_experience}</p>
                    </div>
                    ` : ''}
                </div>
            `;
            document.getElementById('applicationDetailsContent').innerHTML = html;
            
            // Enable/disable buttons based on application status
            const approveBtn = document.getElementById('approveApplicationBtn');
            const rejectBtn = document.getElementById('rejectApplicationBtn');
            
            if (app.status !== 'pending') {
                approveBtn.disabled = true;
                rejectBtn.disabled = true;
                approveBtn.textContent = 'Already ' + app.status.charAt(0).toUpperCase() + app.status.slice(1);
            } else {
                approveBtn.disabled = false;
                rejectBtn.disabled = false;
                approveBtn.textContent = 'Approve';
            }
        }

        // Search functionality - Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality for bookings
            const searchBookings = document.getElementById('searchBookings');
            if (searchBookings) {
                searchBookings.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const rows = document.querySelectorAll('#all-bookings table tbody tr');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            }

            // Search functionality for drivers
            const searchDrivers = document.getElementById('searchDrivers');
            if (searchDrivers) {
                searchDrivers.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const rows = document.querySelectorAll('#drivers table tbody tr');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            }

            // Search functionality for users
            const searchUsers = document.getElementById('searchUsers');
            if (searchUsers) {
                searchUsers.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const rows = document.querySelectorAll('#users table tbody tr');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            }

            // Search functionality for applications
            const searchApplications = document.getElementById('searchApplications');
            if (searchApplications) {
                searchApplications.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const rows = document.querySelectorAll('#applications table tbody tr');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            }
        });
    </script>
</body>
</html>
