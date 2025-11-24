<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'php/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// CRITICAL: Prevent session hijacking - verify the user is not a driver
// If someone is logged in as driver in another tab, don't let that affect this session
if (isset($_SESSION['is_driver']) && $_SESSION['is_driver'] === true && 
    !isset($_SESSION['is_admin'])) {
    // Pure driver trying to access user dashboard - this shouldn't happen
    error_log("SECURITY: Driver attempting to access user dashboard - user_id: " . $_SESSION['user_id']);
    session_destroy();
    header('Location: login.php');
    exit;
}

// Redirect if driver is trying to access user dashboard
if (isset($_SESSION['is_driver']) && $_SESSION['is_driver'] === true) {
    header('Location: driver_dashboard.php');
    exit;
}

// Allow admins to access user dashboard for testing purposes
// They won't be redirected, but we'll use their session properly

// Get user data
$userId = $_SESSION['user_id'];
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$isDriver = isset($_SESSION['is_driver']) && $_SESSION['is_driver'] === true;

// Debug logging
error_log("=== USERDASHBOARD ACCESS ===");
error_log("Session User ID: " . $userId);
error_log("Is Admin: " . ($isAdmin ? 'YES' : 'NO'));
error_log("Is Driver: " . ($isDriver ? 'YES' : 'NO'));
error_log("Session ID: " . session_id());

// CRITICAL FIX: If user was not admin/driver when they loaded this page initially,
// lock their session to prevent switching to admin mid-session
if (!isset($_SESSION['userdash_locked'])) {
    // First time loading - lock the session identity
    $_SESSION['userdash_locked'] = true;
    $_SESSION['userdash_initial_user_id'] = $userId;
    $_SESSION['userdash_is_admin'] = $isAdmin;
    $_SESSION['userdash_is_driver'] = $isDriver;
    error_log("Session locked to user_id: $userId, admin: " . ($isAdmin ? 'YES' : 'NO'));
} else {
    // Check if session has been hijacked/switched
    if ($_SESSION['userdash_initial_user_id'] != $userId) {
        error_log("CRITICAL: Session hijacking detected! Original user: " . $_SESSION['userdash_initial_user_id'] . ", Current: $userId");
        // Restore original session
        $_SESSION['user_id'] = $_SESSION['userdash_initial_user_id'];
        $_SESSION['is_admin'] = $_SESSION['userdash_is_admin'];
        $_SESSION['is_driver'] = $_SESSION['userdash_is_driver'];
        $userId = $_SESSION['user_id'];
        $isAdmin = $_SESSION['is_admin'];
        $isDriver = $_SESSION['is_driver'];
        error_log("Session restored to user_id: $userId");
    }
}

// If admin, get data from admins table; otherwise from users table
if ($isAdmin) {
    error_log("Querying admins table for user_id: " . $userId);
    $stmt = $pdo->prepare("SELECT id, name, email, '' as phone FROM admins WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Ensure admin user data is properly set
    if ($user) {
        $user['phone'] = ''; // Admins don't have phone numbers
        error_log("Admin found: " . $user['name'] . " (" . $user['email'] . ")");
    } else {
        error_log("ERROR: Admin not found with id: " . $userId);
    }
} else {
    error_log("Querying users table for user_id: " . $userId);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        error_log("User found: " . $user['name'] . " (" . $user['email'] . ")");
        
        // Check if user account is archived
        if (isset($user['status']) && $user['status'] === 'archived') {
            error_log("User account is archived, logging out");
            session_destroy();
            header('Location: login.php?error=account_deactivated');
            exit();
        }
    } else {
        error_log("ERROR: User not found with id: " . $userId);
    }
}

// If user not found in appropriate table, session is invalid
if (!$user) {
    error_log("CRITICAL: No user data found, destroying session and redirecting to login");
    session_destroy();
    header('Location: login.php');
    exit;
}

// Get user statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_trips FROM ride_history WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$totalTrips = $stmt->fetch(PDO::FETCH_ASSOC)['total_trips'];

$stmt = $pdo->prepare("SELECT SUM(fare) as total_spent FROM ride_history WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$totalSpent = $stmt->fetch(PDO::FETCH_ASSOC)['total_spent'] ?? 0;

// Calculate trips this week
$stmt = $pdo->prepare("SELECT COUNT(*) as week_trips FROM ride_history WHERE user_id = ? AND status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stmt->execute([$userId]);
$weekTrips = $stmt->fetch(PDO::FETCH_ASSOC)['week_trips'];

// Calculate user's actual average rating given to drivers
$stmt = $pdo->prepare("
    SELECT AVG(user_rating) as avg_rating, COUNT(user_rating) as rating_count 
    FROM ride_history 
    WHERE user_id = ? AND user_rating IS NOT NULL
");
$stmt->execute([$userId]);
$ratingData = $stmt->fetch(PDO::FETCH_ASSOC);
$avgRating = $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 1) : 0;
$ratingCount = $ratingData['rating_count'] ?? 0;

// Get user's favorite destinations (most frequent dropoff locations)
$stmt = $pdo->prepare("
    SELECT destination, COUNT(*) as visit_count 
    FROM ride_history 
    WHERE user_id = ? AND status = 'completed' AND destination IS NOT NULL
    GROUP BY destination 
    ORDER BY visit_count DESC 
    LIMIT 3
");
$stmt->execute([$userId]);
$favoriteDestinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate average fare per trip
$avgFarePerTrip = $totalTrips > 0 ? round($totalSpent / $totalTrips, 2) : 0;

// Get account age
$accountAge = 'New Member';
if (!empty($user['created_at'])) {
    $accountDate = new DateTime($user['created_at']);
    $now = new DateTime();
    $diff = $accountDate->diff($now);
    
    if ($diff->y > 0) {
        $accountAge = $diff->y . ' year' . ($diff->y > 1 ? 's' : '');
    } elseif ($diff->m > 0) {
        $accountAge = $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
    } elseif ($diff->d > 0) {
        $accountAge = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
    } else {
        $accountAge = 'Today';
    }
}

// Get recent trips with driver information
$stmt = $pdo->prepare("
    SELECT r.*, 
           d.name as driver_name,
           d.phone as driver_phone,
           d.plate_number,
           d.rating as driver_rating
    FROM ride_history r 
    LEFT JOIN tricycle_drivers d ON r.driver_id = d.id
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC 
    LIMIT 10
");
$stmt->execute([$userId]);
$recentTrips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check for active/pending booking
$stmt = $pdo->prepare("
    SELECT * FROM ride_history 
    WHERE user_id = ? 
    AND status IN ('pending', 'driver_found', 'searching', 'confirmed', 'arrived', 'in_progress')
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt->execute([$userId]);
$activeBooking = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Routa - User Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/userdashboard-clean.css">
    <link rel="shortcut icon" href="assets/images/Logo.png" type="image/x-icon">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-light bg-white border-bottom">
        <div class="container-fluid px-4 py-3">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="assets/images/Logo.png" alt="Routa Logo" height="32" class="me-2">
                <span class="fw-bold fs-5" style="color: #10b981;">Routa</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted">Welcome, <?php echo htmlspecialchars($user['name'] ?? 'User'); ?></span>
                <a href="php/logout.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 py-4" style="max-width: 1400px; margin: 0 auto;">
        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stats-label">Total Trips</div>
                        <div class="stats-icon">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                    </div>
                    <div class="stats-value"><?php echo $totalTrips; ?></div>
                    <div class="stats-meta <?php echo $weekTrips > 0 ? 'text-success' : 'text-muted'; ?>">
                        +<?php echo $weekTrips; ?> this week
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stats-label">Total Spent</div>
                        <div class="stats-icon">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                    <div class="stats-value">₱<?php echo number_format($totalSpent, 0); ?></div>
                    <div class="stats-meta">
                        Average ₱<?php echo $totalTrips > 0 ? number_format($totalSpent / $totalTrips, 0) : '0'; ?> per trip
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="stats-label">Avg Rating Given</div>
                        <div class="stats-icon">
                            <i class="bi bi-star"></i>
                        </div>
                    </div>
                    <div class="stats-value">
                        <?php 
                        if ($avgRating > 0) {
                            echo number_format($avgRating, 1);
                        } else {
                            echo '<span class="text-muted" style="font-size: 1.5rem;">N/A</span>';
                        }
                        ?>
                    </div>
                    <div class="stats-meta">
                        <?php 
                        if ($ratingCount > 0) {
                            echo 'From ' . $ratingCount . ' review' . ($ratingCount > 1 ? 's' : '');
                        } else {
                            echo 'No ratings yet';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Book Ride Button -->
        <div class="mb-4">
            <button class="btn btn-book-ride" data-bs-toggle="modal" data-bs-target="#bookRideModal">
                <i class="bi bi-plus-circle me-2"></i>
                Book a New Ride
            </button>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs custom-tabs mb-4" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="trip-history-tab" data-bs-toggle="tab" data-bs-target="#trip-history" type="button" role="tab">
                    Trip History
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                    Profile
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="dashboardTabsContent">
            <!-- Trip History Tab -->
            <div class="tab-pane fade show active" id="trip-history" role="tabpanel">
                <div class="content-section">
                    <h5 class="section-title mb-3">Recent Trips</h5>
                    <p class="section-subtitle text-muted mb-4">Your booking history and past rides</p>
                    
                    <div class="trip-list">
                        <?php if (empty($recentTrips)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 48px; color: #cbd5e1;"></i>
                                <p class="text-muted mt-3">No trips yet. Book your first ride!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recentTrips as $trip): ?>
                                <div class="trip-card">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="trip-id">BK-<?php echo str_pad($trip['id'], 3, '0', STR_PAD_LEFT); ?></div>
                                        <div class="trip-status status-<?php echo $trip['status']; ?>">
                                            <?php 
                                                $statusLabels = [
                                                    'searching' => 'Searching Driver',
                                                    'driver_found' => 'Driver Found',
                                                    'confirmed' => 'Confirmed',
                                                    'arrived' => 'Driver Arrived',
                                                    'in_progress' => 'In Progress',
                                                    'completed' => 'Completed',
                                                    'cancelled' => 'Cancelled'
                                                ];
                                                echo $statusLabels[$trip['status']] ?? ucfirst($trip['status']);
                                            ?>
                                        </div>
                                    </div>
                                    <div class="trip-route mb-3">
                                        <div class="trip-location">
                                            <i class="bi bi-geo-alt-fill text-success"></i>
                                            <span><?php echo htmlspecialchars($trip['pickup_location']); ?></span>
                                        </div>
                                        <div class="trip-location destination">
                                            <i class="bi bi-geo-alt text-danger"></i>
                                            <span><?php echo htmlspecialchars($trip['destination']); ?></span>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($trip['distance'])): ?>
                                        <div class="mb-2 text-muted small">
                                            <i class="bi bi-pin-map-fill me-1"></i>
                                            Distance: <?php echo htmlspecialchars($trip['distance']); ?>
                                            <?php if (!empty($trip['estimated_duration'])): ?>
                                                • Duration: <?php echo htmlspecialchars($trip['estimated_duration']); ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <hr class="my-3">
                                    <div class="d-flex justify-content-between align-items-end">
                                        <div class="trip-info">
                                            <div class="trip-date">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                <?php 
                                                    $date = new DateTime($trip['created_at']);
                                                    echo $date->format('M d, Y') . ' • ' . $date->format('g:i A');
                                                ?>
                                            </div>
                                            <?php if (!empty($trip['driver_name']) && $trip['status'] === 'completed'): ?>
                                                <div class="trip-driver mt-1">
                                                    <i class="bi bi-person-circle me-1"></i>
                                                    Driver: <?php echo htmlspecialchars($trip['driver_name']); ?>
                                                    <?php if (!empty($trip['plate_number'])): ?>
                                                        • <?php echo htmlspecialchars($trip['plate_number']); ?>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($trip['user_rating'])): ?>
                                                    <div class="trip-rating mt-1">
                                                        <i class="bi bi-star-fill text-warning me-1"></i>
                                                        <?php 
                                                        $userRating = intval($trip['user_rating']);
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            echo $i <= $userRating ? '★' : '☆';
                                                        }
                                                        echo ' You rated: ' . $userRating . '/5';
                                                        ?>
                                                    </div>
                                                <?php elseif ($trip['status'] === 'completed'): ?>
                                                    <div class="mt-1">
                                                        <button class="btn btn-sm btn-outline-warning" onclick="showRatingModal(<?php echo $trip['id']; ?>)">
                                                            <i class="bi bi-star me-1"></i>Rate Trip
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($trip['user_review'])): ?>
                                                    <div class="mt-2 p-2 bg-light rounded">
                                                        <small class="text-muted">Your review:</small>
                                                        <p class="mb-0 small"><?php echo htmlspecialchars($trip['user_review']); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="trip-fare">₱<?php echo number_format($trip['fare'], 0); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Profile Tab -->
            <div class="tab-pane fade" id="profile" role="tabpanel">
                <div class="profile-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="profile-section-title mb-1">Profile Information</h5>
                            <p class="profile-section-subtitle mb-0">Your personal details and riding statistics</p>
                        </div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="bi bi-pencil-square me-1"></i>Edit Profile
                        </button>
                    </div>
                    
                    <!-- Profile Card -->
                    <div class="profile-card mb-4">
                        <div class="profile-avatar-section">
                            <div class="profile-avatar-circle">
                                <span style="font-size: 2.5rem; font-weight: 700;">
                                    <?php 
                                        // Get user initials
                                        $nameParts = explode(' ', $user['name'] ?? 'U');
                                        $initials = '';
                                        foreach ($nameParts as $part) {
                                            $initials .= strtoupper(substr($part, 0, 1));
                                            if (strlen($initials) >= 2) break;
                                        }
                                        echo $initials ?: 'U';
                                    ?>
                                </span>
                            </div>
                            <div class="profile-user-info">
                                <h4 class="profile-username"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></h4>
                                <p class="profile-user-role">
                                    <i class="bi bi-person-badge me-1"></i> Routa Rider
                                    <span class="badge bg-success ms-2">Active</span>
                                </p>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-calendar-check me-1"></i> 
                                    Member for <?php echo $accountAge; ?>
                                </p>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-info-circle me-2"></i>Personal Information
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="profile-field-group">
                                    <label class="profile-field-label">
                                        <i class="bi bi-person me-1"></i> Full Name
                                    </label>
                                    <input type="text" class="profile-field-input" value="<?php echo htmlspecialchars($user['name'] ?? 'Not set'); ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="profile-field-group">
                                    <label class="profile-field-label">
                                        <i class="bi bi-envelope me-1"></i> Email Address
                                    </label>
                                    <input type="text" class="profile-field-input" value="<?php echo htmlspecialchars($user['email'] ?? 'Not set'); ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="profile-field-group">
                                    <label class="profile-field-label">
                                        <i class="bi bi-telephone me-1"></i> Phone Number
                                    </label>
                                    <input type="text" class="profile-field-input" value="<?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="profile-field-group">
                                    <label class="profile-field-label">
                                        <i class="bi bi-calendar-event me-1"></i> Member Since
                                    </label>
                                    <input type="text" class="profile-field-input" value="<?php 
                                        if (!empty($user['created_at'])) {
                                            $memberDate = new DateTime($user['created_at']);
                                            echo $memberDate->format('F d, Y');
                                        } else {
                                            echo 'Recently joined';
                                        }
                                    ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Riding Statistics Card -->
                    <div class="profile-card mb-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-graph-up me-2"></i>Riding Statistics
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="text-success fs-2 fw-bold"><?php echo $totalTrips; ?></div>
                                    <div class="text-muted small">Total Trips</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="text-primary fs-2 fw-bold">₱<?php echo number_format($totalSpent, 0); ?></div>
                                    <div class="text-muted small">Total Spent</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="text-warning fs-2 fw-bold">
                                        <?php echo $avgRating > 0 ? number_format($avgRating, 1) : 'N/A'; ?>
                                    </div>
                                    <div class="text-muted small">Avg Rating Given</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="text-info fs-2 fw-bold">₱<?php echo number_format($avgFarePerTrip, 0); ?></div>
                                    <div class="text-muted small">Avg Fare/Trip</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <div class="p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="text-muted small">This Week</div>
                                            <div class="fs-4 fw-bold text-success"><?php echo $weekTrips; ?> trips</div>
                                        </div>
                                        <i class="bi bi-calendar-week fs-1 text-success opacity-25"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="text-muted small">Ratings Given</div>
                                            <div class="fs-4 fw-bold text-warning"><?php echo $ratingCount; ?> reviews</div>
                                        </div>
                                        <i class="bi bi-star-fill fs-1 text-warning opacity-25"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Favorite Destinations Card -->
                    <?php if (!empty($favoriteDestinations)): ?>
                    <div class="profile-card">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-geo-alt-fill me-2"></i>Favorite Destinations
                        </h6>
                        
                        <div class="list-group list-group-flush">
                            <?php foreach ($favoriteDestinations as $index => $dest): ?>
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-trophy-fill me-2 <?php 
                                        echo $index === 0 ? 'text-warning' : ($index === 1 ? 'text-secondary' : 'text-bronze'); 
                                    ?>"></i>
                                    <span class="fw-semibold"><?php echo htmlspecialchars($dest['destination']); ?></span>
                                </div>
                                <span class="badge bg-primary rounded-pill"><?php echo $dest['visit_count']; ?> visits</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Book Ride Modal -->
    <div class="modal fade" id="bookRideModal" tabindex="-1" aria-labelledby="bookRideModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="bookRideModalLabel">Book a New Ride</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="text-muted small mb-3">Enter your pickup and drop-off locations</p>
                    
                    <!-- Map Container -->
                    <div id="bookingMap" style="height: 350px; width: 100%; border-radius: 8px; margin-bottom: 20px; border: 2px solid #e2e8f0;"></div>
                    
                    <form id="bookRideForm">
                        <!-- Pickup Location -->
                        <div class="mb-3">
                            <label for="pickupLocation" class="form-label fw-semibold">
                                Pickup Location <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-geo-alt-fill text-success"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-0" id="pickupLocation" 
                                       placeholder="Select pickup location" required autocomplete="off">
                            </div>
                            <input type="hidden" id="pickupLat">
                            <input type="hidden" id="pickupLng">
                        </div>

                        <!-- Drop-off Location -->
                        <div class="mb-3">
                            <label for="dropoffLocation" class="form-label fw-semibold">
                                Drop-off Location <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-geo-alt text-warning"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-0" id="dropoffLocation" 
                                       placeholder="Select drop-off location" required autocomplete="off">
                            </div>
                            <input type="hidden" id="dropoffLat">
                            <input type="hidden" id="dropoffLng">
                        </div>

                        <!-- Distance & Fare Display -->
                        <div id="fareDisplay" class="alert alert-info d-none mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block">Estimated Distance</small>
                                    <strong id="distanceText">-</strong>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block">Estimated Fare</small>
                                    <strong class="text-success fs-5" id="fareText">₱0</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-4">
                            <label for="paymentMethod" class="form-label fw-semibold">Payment Method</label>
                            <select class="form-select" id="paymentMethod" required>
                                <option value="cash" selected>Cash</option>
                                <option value="gcash">GCash</option>
                                <option value="card">Credit/Debit Card</option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-success flex-fill">
                                <i class="bi bi-check-circle me-1"></i>
                                Book Ride
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-body p-5">
                    <div class="success-checkmark mb-3">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Ride Booked Successfully!</h4>
                    <p class="text-muted mb-4">Your ride has been confirmed. A driver will be assigned shortly.</p>
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editProfileModalLabel">
                        <i class="bi bi-person-circle me-2"></i>Edit Profile
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="editProfileAlert" class="alert d-none" role="alert"></div>
                    
                    <form id="editProfileForm">
                        <!-- Full Name -->
                        <div class="mb-3">
                            <label for="editName" class="form-label fw-semibold">
                                <i class="bi bi-person me-1"></i> Full Name
                            </label>
                            <input type="text" class="form-control" id="editName" 
                                   value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" 
                                   required minlength="2" maxlength="100">
                            <div class="invalid-feedback">Please enter your full name (2-100 characters).</div>
                        </div>

                        <!-- Phone Number -->
                        <div class="mb-3">
                            <label for="editPhone" class="form-label fw-semibold">
                                <i class="bi bi-telephone me-1"></i> Phone Number
                            </label>
                            <div class="input-group">
                                <input type="tel" class="form-control" id="editPhone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                       placeholder="09123456789" 
                                       pattern="^09\d{9}$" 
                                       maxlength="11"
                                       data-original-phone="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                       style="border-right: 0;">
                                <button type="button" class="btn btn-outline-success" id="sendOtpEditBtn" 
                                        disabled>
                                    <i class="bi bi-shield-check me-1"></i>Send OTP
                                </button>
                            </div>
                            <div class="form-text">Format: 09XXXXXXXXX (11 digits only)</div>
                            <div id="phoneEditVerificationStatus" style="font-size: 13px; margin-top: 8px; display: none;">
                                <i class="bi bi-check-circle-fill text-success me-1"></i>
                                <span class="text-success fw-semibold">Phone number verified</span>
                            </div>
                            <div class="invalid-feedback">Please enter exactly 11 digits starting with 09.</div>
                        </div>

                        <!-- Email (Read-only) -->
                        <div class="mb-3">
                            <label for="editEmail" class="form-label fw-semibold">
                                <i class="bi bi-envelope me-1"></i> Email Address
                            </label>
                            <input type="email" class="form-control" id="editEmail" 
                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                                   readonly disabled>
                            <div class="form-text text-muted">Email cannot be changed</div>
                        </div>

                        <!-- Change Password Section -->
                        <hr class="my-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-lock me-2"></i>Change Password (Optional)</h6>
                        
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label fw-semibold">Current Password</label>
                            <input type="password" class="form-control" id="currentPassword" 
                                   placeholder="Leave blank to keep current password">
                        </div>

                        <div class="mb-3">
                            <label for="newPassword" class="form-label fw-semibold">New Password</label>
                            <input type="password" class="form-control" id="newPassword" 
                                   placeholder="Enter new password" 
                                   minlength="6">
                            <div class="invalid-feedback">Password must be at least 6 characters.</div>
                        </div>

                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label fw-semibold">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirmPassword" 
                                   placeholder="Confirm new password">
                            <div class="invalid-feedback">Passwords do not match.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveProfileBtn">
                        <i class="bi bi-check-circle me-1"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- OTP Verification Modal for Profile Edit -->
    <div class="modal fade" id="otpEditModal" tabindex="-1" aria-labelledby="otpEditModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="otpEditModalLabel">
                        <i class="bi bi-phone text-success me-2"></i>Verify Your Phone
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center px-4 py-4">
                    <div class="mb-4">
                        <div class="otp-icon mb-3">
                            <i class="bi bi-shield-check" style="font-size: 64px; color: #10b981;"></i>
                        </div>
                        <p class="text-muted mb-1">
                            We've sent a 6-digit verification code to
                        </p>
                        <p class="fw-bold fs-5" id="displayEditPhone" style="color: #10b981;"></p>
                        <input type="hidden" id="normalizedEditPhone">
                    </div>
                    
                    <div class="otp-input-group mb-3">
                        <input type="text" class="otp-input" maxlength="1" id="otpEdit1" inputmode="numeric" autocomplete="off" />
                        <input type="text" class="otp-input" maxlength="1" id="otpEdit2" inputmode="numeric" autocomplete="off" />
                        <input type="text" class="otp-input" maxlength="1" id="otpEdit3" inputmode="numeric" autocomplete="off" />
                        <input type="text" class="otp-input" maxlength="1" id="otpEdit4" inputmode="numeric" autocomplete="off" />
                        <input type="text" class="otp-input" maxlength="1" id="otpEdit5" inputmode="numeric" autocomplete="off" />
                        <input type="text" class="otp-input" maxlength="1" id="otpEdit6" inputmode="numeric" autocomplete="off" />
                    </div>
                    
                    <div id="otpEditError" class="alert alert-danger" role="alert" style="display: none; padding: 10px; font-size: 14px;"></div>
                    
                    <button type="button" class="btn btn-success w-100 mb-3 py-2" id="verifyOtpEditBtn">
                        <i class="bi bi-check-circle me-2"></i>Verify Code
                    </button>
                    
                    <div class="text-center">
                        <small class="text-muted">
                            Didn't receive the code? 
                            <a href="#" id="resendOtpEditBtn" class="text-success fw-bold text-decoration-none">Resend OTP</a>
                        </small>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted" id="otpEditTimer"></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OTP Input Styles -->
    <style>
        .otp-input-group {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .otp-input {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            outline: none;
            transition: all 0.2s;
        }
        
        .otp-input:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        
        .otp-input.error {
            border-color: #ef4444;
            animation: shake 0.3s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    </style>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Leaflet CSS for Maps (Free, No API Key Needed!) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Leaflet Control Geocoder (Free Address Search) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.css" />
    <script src="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.js"></script>
    
    <script src="assets/js/dashboard.js"></script>
    
    <!-- Real-time WebSocket Integration -->
    <script src="assets/js/realtime-client.js"></script>
    <script src="assets/js/rider-realtime.js"></script>
    <script>
        // Initialize real-time updates
        initRiderRealtime(<?= $_SESSION['user_id'] ?>);
    </script>
</body>
</html>