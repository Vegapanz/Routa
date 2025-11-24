<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'php/config.php';
require_once 'php/admin_functions.php';

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
$isAjax = $isAjax || (isset($_POST['action']) && $_SERVER['REQUEST_METHOD'] === 'POST');

// Debug session state for troubleshooting
if ($isAjax) {
    error_log("=== AJAX Request Debug ===");
    error_log("Session ID: " . session_id());
    error_log("Session status: " . session_status());
    error_log("user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));
    error_log("is_admin: " . (isset($_SESSION['is_admin']) ? var_export($_SESSION['is_admin'], true) : 'NOT SET'));
    error_log("Action: " . ($_POST['action'] ?? 'none'));
    error_log("PHPSESSID cookie: " . ($_COOKIE['PHPSESSID'] ?? 'NOT SET'));
}

// Check if user is logged in and is admin - be more lenient with type checking
$hasUserId = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$isAdmin = isset($_SESSION['is_admin']) && ($_SESSION['is_admin'] === true || $_SESSION['is_admin'] === 1 || $_SESSION['is_admin'] === '1');

if (!$hasUserId || !$isAdmin) {
    error_log("Admin session check FAILED - user_id: " . ($hasUserId ? 'EXISTS' : 'MISSING') . 
              ", is_admin value: " . (isset($_SESSION['is_admin']) ? var_export($_SESSION['is_admin'], true) : 'NOT SET') .
              ", is_admin check: " . ($isAdmin ? 'PASSED' : 'FAILED'));
    
    // if ($isAjax) {
    //     header('Content-Type: application/json');
    //     echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.', 'redirect' => 'login.php']);
    //     exit();
    // }
    header('Location: login.php');
    exit();
}

// Verify admin exists in admins table
$admin_id = $_SESSION['user_id'];
$admin_data = null;

try {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin_data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error checking admin: " . $e->getMessage());
}

// If admin data not found, use cached session data instead of logging out
if (!$admin_data) {
    error_log("Admin data not retrieved from database for user_id: " . $admin_id . " - using session cache");
    
    // Create admin data from session to allow page to continue
    $admin_data = [
        'id' => $admin_id,
        'name' => $_SESSION['user_name'] ?? 'Admin',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['admin_role'] ?? 'admin'
    ];
    
    // Only log critical error if this persists
    if (!isset($_SESSION['admin_cache_start_time'])) {
        $_SESSION['admin_cache_start_time'] = time();
    } elseif ((time() - $_SESSION['admin_cache_start_time']) > 300) {
        // If using cache for more than 5 minutes, something is wrong
        error_log("CRITICAL: Admin data unavailable for 5+ minutes for user_id: " . $admin_id);
    }
} else {
    // Successfully retrieved admin data - clear cache timer
    unset($_SESSION['admin_cache_start_time']);
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Clear any previous output
    ob_clean();
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    try {
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
                    try {
                        if (approveDriverApplication($pdo, $_POST['application_id'])) {
                            $response['success'] = true;
                            $response['message'] = 'Application approved successfully. Driver added to system and approval email sent.';
                        } else {
                            $response['message'] = 'Failed to approve application. Please check error logs.';
                        }
                    } catch (Exception $e) {
                        $response['message'] = 'Error: ' . $e->getMessage();
                        error_log("Approval error: " . $e->getMessage());
                    }
                } else {
                    $response['message'] = 'Missing application_id';
                }
                break;

            case 'reject_application':
                if (isset($_POST['application_id'])) {
                    error_log("Attempting to reject application " . $_POST['application_id']);
                    try {
                        $reason = $_POST['rejection_reason'] ?? '';
                        if (rejectDriverApplication($pdo, $_POST['application_id'], $reason)) {
                            $response['success'] = true;
                            $response['message'] = 'Application rejected successfully and rejection email sent.';
                        } else {
                            $response['message'] = 'Failed to reject application. Please check error logs.';
                        }
                    } catch (Exception $e) {
                        $response['message'] = 'Error: ' . $e->getMessage();
                        error_log("Rejection error: " . $e->getMessage());
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

            case 'delete_driver':
                if (isset($_POST['driver_id'])) {
                    error_log("Attempting to archive driver " . $_POST['driver_id']);
                    try {
                        $stmt = $pdo->prepare("UPDATE tricycle_drivers SET status = 'archived', deleted_at = NOW() WHERE id = ?");
                        if ($stmt->execute([$_POST['driver_id']])) {
                            $response['success'] = true;
                            $response['message'] = 'Driver moved to trash';
                        } else {
                            $response['message'] = 'Failed to archive driver';
                        }
                    } catch (Exception $e) {
                        $response['message'] = 'Error: ' . $e->getMessage();
                        error_log("Archive driver error: " . $e->getMessage());
                    }
                } else {
                    $response['message'] = 'Missing driver_id';
                }
                break;

            case 'delete_user':
                if (isset($_POST['user_id'])) {
                    error_log("Attempting to archive user " . $_POST['user_id']);
                    try {
                        $stmt = $pdo->prepare("UPDATE users SET status = 'archived', deleted_at = NOW() WHERE id = ?");
                        if ($stmt->execute([$_POST['user_id']])) {
                            $response['success'] = true;
                            $response['message'] = 'User moved to trash';
                        } else {
                            $response['message'] = 'Failed to archive user';
                        }
                    } catch (Exception $e) {
                        $response['message'] = 'Error: ' . $e->getMessage();
                        error_log("Archive user error: " . $e->getMessage());
                    }
                } else {
                    $response['message'] = 'Missing user_id';
                }
                break;

            case 'get_dashboard_stats':
                $stats = getDashboardStats($pdo);
                if ($stats) {
                    $response['success'] = true;
                    $response['data'] = $stats;
                } else {
                    $response['message'] = 'Failed to fetch dashboard stats';
                }
                break;

            case 'get_pending_bookings':
                $bookings = getPendingBookings($pdo);
                if ($bookings !== false) {
                    $response['success'] = true;
                    $response['data'] = $bookings;
                } else {
                    $response['message'] = 'Failed to fetch pending bookings';
                }
                break;

            case 'get_all_bookings':
                $stmt = $pdo->prepare("SELECT r.*, u.name as rider_name, u.phone, d.name as driver_name 
                    FROM ride_history r 
                    LEFT JOIN users u ON r.user_id = u.id 
                    LEFT JOIN tricycle_drivers d ON r.driver_id = d.id 
                    ORDER BY r.created_at DESC");
                $stmt->execute();
                $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response['success'] = true;
                $response['data'] = $bookings;
                break;

            case 'get_drivers':
                $stmt = $pdo->prepare("SELECT d.*, COUNT(r.id) as total_trips 
                    FROM tricycle_drivers d 
                    LEFT JOIN ride_history r ON d.id = r.driver_id AND r.status = 'completed'
                    WHERE d.status != 'archived'
                    GROUP BY d.id
                    ORDER BY d.created_at DESC");
                $stmt->execute();
                $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response['success'] = true;
                $response['data'] = $drivers;
                break;

            case 'get_users':
                $stmt = $pdo->prepare("SELECT u.*, COUNT(r.id) as total_rides 
                    FROM users u 
                    LEFT JOIN ride_history r ON u.id = r.user_id 
                    WHERE (u.status IS NULL OR u.status != 'archived')
                    GROUP BY u.id
                    ORDER BY u.created_at DESC");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response['success'] = true;
                $response['data'] = $users;
                break;

            case 'get_applications':
                $stmt = $pdo->prepare("SELECT * FROM driver_applications ORDER BY created_at DESC");
                $stmt->execute();
                $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response['success'] = true;
                $response['data'] = $applications;
                break;

            case 'get_archived_drivers':
                $stmt = $pdo->prepare("SELECT d.*, COUNT(r.id) as total_trips 
                    FROM tricycle_drivers d 
                    LEFT JOIN ride_history r ON d.id = r.driver_id AND r.status = 'completed'
                    WHERE d.status = 'archived'
                    GROUP BY d.id
                    ORDER BY d.deleted_at DESC");
                $stmt->execute();
                $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response['success'] = true;
                $response['data'] = $drivers;
                break;

            case 'get_archived_users':
                $stmt = $pdo->prepare("SELECT u.*, COUNT(r.id) as total_rides 
                    FROM users u 
                    LEFT JOIN ride_history r ON u.id = r.user_id 
                    WHERE u.status = 'archived'
                    GROUP BY u.id
                    ORDER BY u.deleted_at DESC");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response['success'] = true;
                $response['data'] = $users;
                break;

            case 'restore_driver':
                if (isset($_POST['driver_id'])) {
                    error_log("Attempting to restore driver " . $_POST['driver_id']);
                    try {
                        $stmt = $pdo->prepare("UPDATE tricycle_drivers SET status = 'offline', deleted_at = NULL WHERE id = ?");
                        if ($stmt->execute([$_POST['driver_id']])) {
                            $response['success'] = true;
                            $response['message'] = 'Driver restored successfully';
                        } else {
                            $response['message'] = 'Failed to restore driver';
                        }
                    } catch (Exception $e) {
                        $response['message'] = 'Error: ' . $e->getMessage();
                        error_log("Restore driver error: " . $e->getMessage());
                    }
                } else {
                    $response['message'] = 'Missing driver_id';
                }
                break;

            case 'restore_user':
                if (isset($_POST['user_id'])) {
                    error_log("Attempting to restore user " . $_POST['user_id']);
                    try {
                        $stmt = $pdo->prepare("UPDATE users SET status = NULL, deleted_at = NULL WHERE id = ?");
                        if ($stmt->execute([$_POST['user_id']])) {
                            $response['success'] = true;
                            $response['message'] = 'User restored successfully';
                        } else {
                            $response['message'] = 'Failed to restore user';
                        }
                    } catch (Exception $e) {
                        $response['message'] = 'Error: ' . $e->getMessage();
                        error_log("Restore user error: " . $e->getMessage());
                    }
                } else {
                    $response['message'] = 'Missing user_id';
                }
                break;

            case 'permanent_delete_driver':
                if (isset($_POST['driver_id'])) {
                    error_log("Attempting to permanently delete driver " . $_POST['driver_id']);
                    try {
                        $stmt = $pdo->prepare("DELETE FROM tricycle_drivers WHERE id = ? AND status = 'archived'");
                        if ($stmt->execute([$_POST['driver_id']])) {
                            $response['success'] = true;
                            $response['message'] = 'Driver permanently deleted';
                        } else {
                            $response['message'] = 'Failed to delete driver';
                        }
                    } catch (Exception $e) {
                        $response['message'] = 'Error: ' . $e->getMessage();
                        error_log("Permanent delete driver error: " . $e->getMessage());
                    }
                } else {
                    $response['message'] = 'Missing driver_id';
                }
                break;

            case 'permanent_delete_user':
                if (isset($_POST['user_id'])) {
                    error_log("Attempting to permanently delete user " . $_POST['user_id']);
                    try {
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND status = 'archived'");
                        if ($stmt->execute([$_POST['user_id']])) {
                            $response['success'] = true;
                            $response['message'] = 'User permanently deleted';
                        } else {
                            $response['message'] = 'Failed to delete user';
                        }
                    } catch (Exception $e) {
                        $response['message'] = 'Error: ' . $e->getMessage();
                        error_log("Permanent delete user error: " . $e->getMessage());
                    }
                } else {
                    $response['message'] = 'Missing user_id';
                }
                break;
        }
    } catch (Exception $e) {
        error_log("Exception in AJAX handler: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $response['success'] = false;
        $response['message'] = 'Server error: ' . $e->getMessage();
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

// Fetch drivers (exclude archived)
$stmt = $pdo->prepare("SELECT d.*, COUNT(r.id) as total_trips 
    FROM tricycle_drivers d 
    LEFT JOIN ride_history r ON d.id = r.driver_id AND r.status = 'completed'
    WHERE d.status != 'archived'
    GROUP BY d.id 
    ORDER BY d.id");
$stmt->execute();
$drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch users (exclude archived)
$stmt = $pdo->prepare("SELECT u.*, COUNT(r.id) as total_trips 
    FROM users u 
    LEFT JOIN ride_history r ON u.id = r.user_id 
    WHERE (u.status IS NULL OR u.status != 'archived')
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .pagination-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding: 15px 0;
            border-top: 1px solid #e5e7eb;
        }
        .pagination-info {
            color: #6b7280;
            font-size: 14px;
        }
        .pagination-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .pagination-buttons button {
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            color: #374151;
            transition: all 0.2s;
        }
        .pagination-buttons button:hover:not(:disabled) {
            background: #f9fafb;
            border-color: #10b981;
            color: #10b981;
        }
        .pagination-buttons button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .pagination-buttons button.active {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }
        .entries-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        .entries-selector label {
            color: #374151;
            font-size: 14px;
            margin: 0;
        }
        .entries-selector select {
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            background: white;
        }
        .search-box {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        .search-box input {
            flex: 1;
            max-width: 300px;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }
        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
    </style>
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
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="trash-tab" data-bs-toggle="tab" data-bs-target="#trash" type="button" role="tab">
                    <i class="bi bi-trash"></i> Trash
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
                </div>

                <div class="table-controls">
                    <div class="entries-selector">
                        <label>Show</label>
                        <select id="bookingsEntries" onchange="updateTableEntries('bookingsTable', this.value)">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="-1">All</option>
                        </select>
                        <label>entries</label>
                    </div>
                    <div class="search-box">
                        <input type="text" placeholder="Search bookings..." id="bookingsSearch" oninput="searchTable('bookingsTable', this.value)" autocomplete="off">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table data-table" id="bookingsTable">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Rider</th>
                                <th>Driver</th>
                                <th>Route</th>
                                <th>Fare</th>
                                <th>Status</th>
                                <th>Time</th>
                                <th>Actions</th>
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
                <div class="pagination-controls" id="bookingsPagination"></div>
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
                <div class="section-header mb-4">
                    <h5 class="section-title">Driver Management</h5>
                    <p class="section-subtitle">Monitor and manage all registered drivers</p>
                </div>

                <div class="table-controls">
                    <div class="entries-selector">
                        <label>Show</label>
                        <select id="driversEntries" onchange="updateTableEntries('driversTable', this.value)">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="-1">All</option>
                        </select>
                        <label>entries</label>
                    </div>
                    <div class="search-box">
                        <input type="text" placeholder="Search drivers..." id="driversSearch" oninput="searchTable('driversTable', this.value)" autocomplete="off">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table data-table" id="driversTable">
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
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewDriverDetails(<?= $driver['id'] ?>)">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteDriver(<?= $driver['id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination-controls" id="driversPagination"></div>
            </div>

            <!-- Users Tab -->
            <div class="tab-pane fade" id="users" role="tabpanel">
                <div class="section-header mb-4">
                    <h5 class="section-title">User Management</h5>
                    <p class="section-subtitle">View and manage all registered users</p>
                </div>

                <div class="table-controls">
                    <div class="entries-selector">
                        <label>Show</label>
                        <select id="usersEntries" onchange="updateTableEntries('usersTable', this.value)">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="-1">All</option>
                        </select>
                        <label>entries</label>
                    </div>
                    <div class="search-box">
                        <input type="text" placeholder="Search users..." id="usersSearch" oninput="searchTable('usersTable', this.value)" autocomplete="off">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table data-table" id="usersTable" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="width: 10%; min-width: 90px;">User ID</th>
                                <th style="width: 30%; min-width: 250px;">Name</th>
                                <th style="width: 18%; min-width: 150px;">Phone</th>
                                <th style="width: 15%; min-width: 110px;">Total Rides</th>
                                <th style="width: 15%; min-width: 110px;">Joined</th>
                                <th style="width: 12%; min-width: 100px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="fw-semibold" style="width: 10%; min-width: 90px;">USR-<?= str_pad($user['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                <td style="width: 30%; min-width: 250px;">
                                    <div class="avatar-name">
                                        <span class="avatar"><?= strtoupper(substr($user['name'], 0, 2)) ?></span>
                                        <div>
                                            <div><?= htmlspecialchars($user['name']) ?></div>
                                            <div class="text-muted" style="font-size: 0.8125rem;"><?= htmlspecialchars($user['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 18%; min-width: 150px;"><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></td>
                                <td style="width: 15%; min-width: 110px;"><?= $user['total_trips'] ?> rides</td>
                                <td class="text-muted" style="width: 15%; min-width: 110px;"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                <td style="width: 12%; min-width: 100px;">
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?= $user['id'] ?>)">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination-controls" id="usersPagination"></div>
            </div>

            <!-- Driver Applications Tab -->
            <div class="tab-pane fade" id="applications" role="tabpanel">
                <div class="section-header mb-4">
                    <h5 class="section-title">Driver Applications</h5>
                    <p class="section-subtitle">Review and manage driver applications</p>
                </div>

                <?php if (empty($driver_applications)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox" style="font-size: 48px; color: #cbd5e1;"></i>
                        <p class="text-muted mt-3">No driver applications yet</p>
                    </div>
                <?php else: ?>
                    <div class="table-controls">
                        <div class="entries-selector">
                            <label>Show</label>
                            <select id="applicationsEntries" onchange="updateTableEntries('applicationsTable', this.value)">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="-1">All</option>
                            </select>
                            <label>entries</label>
                        </div>
                        <div class="search-box">
                            <input type="text" placeholder="Search applications..." id="applicationsSearch" oninput="searchTable('applicationsTable', this.value)" autocomplete="off">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table data-table" id="applicationsTable">
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
                    <div class="pagination-controls" id="applicationsPagination"></div>
                <?php endif; ?>
            </div>

            <!-- Trash Tab -->
            <div class="tab-pane fade" id="trash" role="tabpanel">
                <div class="section-header mb-4">
                    <h5 class="section-title"><i class="bi bi-trash"></i> Trash</h5>
                    <p class="section-subtitle">Archived users and drivers - Restore or permanently delete</p>
                </div>

                <!-- Archived Drivers Section -->
                <div class="mb-5">
                    <h6 class="fw-bold mb-3">Archived Drivers</h6>
                    <div class="table-controls">
                        <div class="entries-selector">
                            <label>Show</label>
                            <select id="trashDriversEntries" onchange="updateTableEntries('trashDriversTable', this.value)">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="-1">All</option>
                            </select>
                            <label>entries</label>
                        </div>
                        <div class="search-box">
                            <input type="text" placeholder="Search archived drivers..." id="trashDriversSearch" oninput="searchTable('trashDriversTable', this.value)" autocomplete="off">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table data-table" id="trashDriversTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Plate Number</th>
                                    <th>Total Trips</th>
                                    <th>Deleted At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Will be populated via AJAX -->
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination-controls" id="trashDriversPagination"></div>
                </div>

                <!-- Archived Users Section -->
                <div>
                    <h6 class="fw-bold mb-3">Archived Users</h6>
                    <div class="table-controls">
                        <div class="entries-selector">
                            <label>Show</label>
                            <select id="trashUsersEntries" onchange="updateTableEntries('trashUsersTable', this.value)">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="-1">All</option>
                            </select>
                            <label>entries</label>
                        </div>
                        <div class="search-box">
                            <input type="text" placeholder="Search archived users..." id="trashUsersSearch" oninput="searchTable('trashUsersTable', this.value)" autocomplete="off">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table data-table" id="trashUsersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Total Rides</th>
                                    <th>Deleted At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Will be populated via AJAX -->
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination-controls" id="trashUsersPagination"></div>
                </div>
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

        // Custom Pagination System - Global scope
        window.tablePagination = {};

        window.initializeTable = function(tableId) {
            console.log('🔧 Initializing:', tableId);
            const table = document.getElementById(tableId);
            if (!table) {
                console.log('❌ Table not found:', tableId);
                return;
            }
            
            const tbody = table.querySelector('tbody');
            if (!tbody) {
                console.log('❌ tbody not found');
                return;
            }
            
            const rows = Array.from(tbody.querySelectorAll('tr'));
            console.log('✅ Found rows:', rows.length);
            
            window.tablePagination[tableId] = {
                currentPage: 1,
                entriesPerPage: 10,
                allRows: rows,
                filteredRows: [...rows]
            };
            
            updateTableDisplay(tableId);
        }

        // Debounce timer for search
        let searchTimers = {};

        window.searchTable = function(tableId, searchTerm) {
            console.log('🔍 Searching:', tableId, '|' + searchTerm + '|');
            
            // Clear existing timer
            if (searchTimers[tableId]) {
                clearTimeout(searchTimers[tableId]);
            }
            
            // Debounce the search
            searchTimers[tableId] = setTimeout(() => {
                performSearch(tableId, searchTerm);
            }, 150);
        }

        function performSearch(tableId, searchTerm) {
            if (!window.tablePagination[tableId]) {
                console.log('⚠️ Table not initialized, initializing now...');
                window.initializeTable(tableId);
                // Try again after initialization
                setTimeout(() => performSearch(tableId, searchTerm), 100);
                return;
            }
            
            const data = window.tablePagination[tableId];
            if (!data || !data.allRows) {
                console.log('❌ No data or rows for table');
                return;
            }
            
            searchTerm = searchTerm.toLowerCase().trim();
            console.log('🔎 Performing search for:', searchTerm);
            
            if (searchTerm === '') {
                data.filteredRows = [...data.allRows];
                console.log('✅ Showing all', data.allRows.length, 'rows');
            } else {
                data.filteredRows = data.allRows.filter(row => {
                    // Get all text content from the row
                    const text = row.textContent.toLowerCase();
                    const match = text.includes(searchTerm);
                    
                    if (match) {
                        console.log('✅ Match found in:', text.substring(0, 50) + '...');
                    }
                    
                    return match;
                });
                console.log('🎯 Found', data.filteredRows.length, 'matches out of', data.allRows.length, 'rows');
            }
            
            data.currentPage = 1;
            updateTableDisplay(tableId);
        }

        window.updateTableEntries = function(tableId, entries) {
            console.log('📊 Update entries:', tableId, entries);
            
            if (!window.tablePagination[tableId]) {
                console.log('⚠️ Table not initialized, initializing now...');
                window.initializeTable(tableId);
            }
            
            const data = window.tablePagination[tableId];
            if (!data) {
                console.log('❌ No data for table');
                return;
            }
            
            const entriesValue = parseInt(entries);
            data.entriesPerPage = entriesValue === -1 ? data.allRows.length : entriesValue;
            data.currentPage = 1;
            
            console.log('✅ Set entries per page:', data.entriesPerPage);
            updateTableDisplay(tableId);
        }

        function updateTableDisplay(tableId) {
            const data = window.tablePagination[tableId];
            if (!data) {
                console.log('❌ No data in updateTableDisplay');
                return;
            }
            
            console.log('🔄 Updating display for', tableId);
            
            // Hide all rows first
            data.allRows.forEach(row => {
                row.style.display = 'none';
            });
            
            // Calculate pagination
            const totalRows = data.filteredRows.length;
            const entriesPerPage = data.entriesPerPage;
            const totalPages = Math.max(1, Math.ceil(totalRows / entriesPerPage));
            
            console.log('📈 Total rows:', totalRows, 'Per page:', entriesPerPage, 'Total pages:', totalPages);
            
            // Adjust current page
            if (data.currentPage > totalPages) {
                data.currentPage = totalPages;
            }
            
            // Show rows for current page
            const startIndex = (data.currentPage - 1) * entriesPerPage;
            const endIndex = Math.min(startIndex + entriesPerPage, totalRows);
            
            console.log('👁️ Showing rows', startIndex + 1, 'to', endIndex);
            
            for (let i = startIndex; i < endIndex; i++) {
                if (data.filteredRows[i]) {
                    data.filteredRows[i].style.display = '';
                }
            }
            
            // Update pagination controls
            updatePaginationControls(tableId, totalRows, startIndex, endIndex, totalPages);
        }

        function updatePaginationControls(tableId, totalRows, startIndex, endIndex, totalPages) {
            const data = window.tablePagination[tableId];
            const paginationId = tableId.replace('Table', 'Pagination');
            const paginationDiv = document.getElementById(paginationId);
            
            if (!paginationDiv) return;
            
            // Info text
            const infoText = totalRows === 0 
                ? 'Showing 0 entries'
                : `Showing ${startIndex + 1} to ${endIndex} of ${totalRows} entries`;
            
            // Generate buttons
            let buttonsHtml = '';
            
            // Previous
            buttonsHtml += `<button onclick="changePage('${tableId}', ${data.currentPage - 1})" ${data.currentPage === 1 ? 'disabled' : ''}>
                <i class="bi bi-chevron-left"></i> Previous
            </button>`;
            
            // Page numbers
            if (totalPages > 1) {
                const maxButtons = 5;
                let startPage = Math.max(1, data.currentPage - Math.floor(maxButtons / 2));
                let endPage = Math.min(totalPages, startPage + maxButtons - 1);
                
                if (endPage - startPage < maxButtons - 1) {
                    startPage = Math.max(1, endPage - maxButtons + 1);
                }
                
                if (startPage > 1) {
                    buttonsHtml += `<button onclick="changePage('${tableId}', 1)">1</button>`;
                    if (startPage > 2) {
                        buttonsHtml += `<button disabled>...</button>`;
                    }
                }
                
                for (let i = startPage; i <= endPage; i++) {
                    buttonsHtml += `<button onclick="changePage('${tableId}', ${i})" ${i === data.currentPage ? 'class="active"' : ''}>${i}</button>`;
                }
                
                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        buttonsHtml += `<button disabled>...</button>`;
                    }
                    buttonsHtml += `<button onclick="changePage('${tableId}', ${totalPages})">${totalPages}</button>`;
                }
            }
            
            // Next
            buttonsHtml += `<button onclick="changePage('${tableId}', ${data.currentPage + 1})" ${data.currentPage >= totalPages ? 'disabled' : ''}>
                Next <i class="bi bi-chevron-right"></i>
            </button>`;
            
            paginationDiv.innerHTML = `
                <div class="pagination-info">${infoText}</div>
                <div class="pagination-buttons">${buttonsHtml}</div>
            `;
        }

        window.changePage = function(tableId, page) {
            const data = window.tablePagination[tableId];
            if (!data) return;
            
            const totalRows = data.filteredRows.length;
            const totalPages = Math.max(1, Math.ceil(totalRows / data.entriesPerPage));
            
            if (page < 1 || page > totalPages) return;
            
            data.currentPage = page;
            updateTableDisplay(tableId);
        }

        // Initialize when ready
        function initializeTables() {
            console.log('🚀 Starting table initialization...');
            console.log('Document ready state:', document.readyState);
            
            // Try multiple times to ensure tabs are loaded
            let attempts = 0;
            const maxAttempts = 5;
            
            const tryInit = () => {
                attempts++;
                console.log(`⏳ Initialization attempt ${attempts}/${maxAttempts}`);
                
                window.initializeTable('bookingsTable');
                window.initializeTable('driversTable');
                window.initializeTable('usersTable');
                window.initializeTable('applicationsTable');
                
                // Check if any table was initialized
                const initialized = Object.keys(window.tablePagination).length;
                console.log('✨ Tables initialized:', initialized);
                
                if (initialized === 0 && attempts < maxAttempts) {
                    console.log('⏭️ Retrying in 300ms...');
                    setTimeout(tryInit, 300);
                }
            };
            
            setTimeout(tryInit, 100);
        }

        // Global function for WebSocket to call after updates
        window.reinitializeTables = function() {
            console.log('🔄 WebSocket update detected - Re-initializing tables...');
            
            // Store current state
            const states = {};
            Object.keys(window.tablePagination).forEach(tableId => {
                const data = window.tablePagination[tableId];
                states[tableId] = {
                    currentPage: data.currentPage,
                    entriesPerPage: data.entriesPerPage,
                    searchTerm: document.getElementById(tableId.replace('Table', 'Search'))?.value || ''
                };
            });
            
            // Re-initialize
            setTimeout(() => {
                Object.keys(states).forEach(tableId => {
                    window.initializeTable(tableId);
                    
                    // Restore state
                    const state = states[tableId];
                    if (window.tablePagination[tableId]) {
                        window.tablePagination[tableId].currentPage = state.currentPage;
                        window.tablePagination[tableId].entriesPerPage = state.entriesPerPage;
                        
                        // Reapply search if there was one
                        if (state.searchTerm) {
                            window.searchTable(tableId, state.searchTerm);
                        } else {
                            updateTableDisplay(tableId);
                        }
                    }
                });
            }, 100);
        };

        // Listen for tab changes to initialize tables when they become visible
        document.addEventListener('DOMContentLoaded', function() {
            initializeTables();
            
            // Re-initialize when switching tabs
            const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
            tabButtons.forEach(button => {
                button.addEventListener('shown.bs.tab', function(e) {
                    console.log('🔄 Tab switched, re-initializing tables...');
                    setTimeout(initializeTables, 100);
                });
            });
        });

        // Also try to initialize immediately if DOM is already loaded
        if (document.readyState !== 'loading') {
            initializeTables();
        }

        // Set up MutationObserver to detect when table rows change (from WebSocket updates)
        document.addEventListener('DOMContentLoaded', function() {
            const observeTables = () => {
                ['bookingsTable', 'driversTable', 'usersTable', 'applicationsTable'].forEach(tableId => {
                    const table = document.getElementById(tableId);
                    if (table) {
                        const tbody = table.querySelector('tbody');
                        if (tbody) {
                            const observer = new MutationObserver((mutations) => {
                                // Check if rows were added or removed
                                const hasChanges = mutations.some(mutation => 
                                    mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0
                                );
                                
                                if (hasChanges) {
                                    console.log('🔔 Table rows changed:', tableId);
                                    window.reinitializeTables();
                                }
                            });
                            
                            observer.observe(tbody, {
                                childList: true,
                                subtree: false
                            });
                            
                            console.log('👀 Observing', tableId, 'for changes');
                        }
                    }
                });
            };
            
            setTimeout(observeTables, 500);
        });

        // ===== Trash Management Functions =====
        
        function loadArchivedData() {
            // Load archived drivers
            fetch('admin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'action=get_archived_drivers'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateArchivedDrivers(data.data);
                }
            })
            .catch(error => console.error('Error loading archived drivers:', error));

            // Load archived users
            fetch('admin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'action=get_archived_users'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateArchivedUsers(data.data);
                }
            })
            .catch(error => console.error('Error loading archived users:', error));
        }

        function populateArchivedDrivers(drivers) {
            const tbody = document.querySelector('#trashDriversTable tbody');
            if (!tbody) return;

            if (drivers.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No archived drivers</td></tr>';
            } else {
                tbody.innerHTML = drivers.map(driver => `
                    <tr>
                        <td class="fw-semibold">DRV-${String(driver.id).padStart(3, '0')}</td>
                        <td>
                            <div class="avatar-name">
                                <span class="avatar">${driver.name.substring(0, 2).toUpperCase()}</span>
                                ${driver.name}
                            </div>
                        </td>
                        <td class="text-muted">${driver.email}</td>
                        <td>${driver.plate_number}</td>
                        <td>${driver.total_trips} trips</td>
                        <td class="text-muted">${driver.deleted_at ? new Date(driver.deleted_at).toLocaleDateString() : 'N/A'}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-success me-2" onclick="restoreDriver(${driver.id})">
                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="permanentDeleteDriver(${driver.id})">
                                <i class="bi bi-trash3"></i> Delete Permanently
                            </button>
                        </td>
                    </tr>
                `).join('');
            }

            window.initializeTable('trashDriversTable');
        }

        function populateArchivedUsers(users) {
            const tbody = document.querySelector('#trashUsersTable tbody');
            if (!tbody) return;

            if (users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No archived users</td></tr>';
            } else {
                tbody.innerHTML = users.map(user => `
                    <tr>
                        <td class="fw-semibold">USR-${String(user.id).padStart(3, '0')}</td>
                        <td>
                            <div class="avatar-name">
                                <span class="avatar">${user.name.substring(0, 2).toUpperCase()}</span>
                                ${user.name}
                            </div>
                        </td>
                        <td class="text-muted">${user.email}</td>
                        <td>${user.phone || 'N/A'}</td>
                        <td>${user.total_rides} rides</td>
                        <td class="text-muted">${user.deleted_at ? new Date(user.deleted_at).toLocaleDateString() : 'N/A'}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-success me-2" onclick="restoreUser(${user.id})">
                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="permanentDeleteUser(${user.id})">
                                <i class="bi bi-trash3"></i> Delete Permanently
                            </button>
                        </td>
                    </tr>
                `).join('');
            }

            window.initializeTable('trashUsersTable');
        }

        function restoreDriver(driverId) {
            Swal.fire({
                title: 'Restore Driver?',
                text: 'This driver will be able to login again and accept rides.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, restore it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('admin.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `action=restore_driver&driver_id=${driverId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Restored!', data.message, 'success');
                            loadArchivedData();
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Failed to restore driver', 'error');
                        console.error('Error:', error);
                    });
                }
            });
        }

        function restoreUser(userId) {
            Swal.fire({
                title: 'Restore User?',
                text: 'This user will be able to login again and book rides.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, restore it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('admin.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `action=restore_user&user_id=${userId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Restored!', data.message, 'success');
                            loadArchivedData();
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Failed to restore user', 'error');
                        console.error('Error:', error);
                    });
                }
            });
        }

        function permanentDeleteDriver(driverId) {
            Swal.fire({
                title: 'Permanently Delete?',
                text: 'This action cannot be undone! All driver data will be permanently removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete permanently!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('admin.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `action=permanent_delete_driver&driver_id=${driverId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Deleted!', data.message, 'success');
                            loadArchivedData();
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Failed to delete driver', 'error');
                        console.error('Error:', error);
                    });
                }
            });
        }

        function permanentDeleteUser(userId) {
            Swal.fire({
                title: 'Permanently Delete?',
                text: 'This action cannot be undone! All user data will be permanently removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete permanently!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('admin.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `action=permanent_delete_user&user_id=${userId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Deleted!', data.message, 'success');
                            loadArchivedData();
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Failed to delete user', 'error');
                        console.error('Error:', error);
                    });
                }
            });
        }

        // Load archived data when trash tab is shown
        document.addEventListener('DOMContentLoaded', function() {
            const trashTab = document.getElementById('trash-tab');
            if (trashTab) {
                trashTab.addEventListener('shown.bs.tab', function() {
                    loadArchivedData();
                });
            }
        });
    </script>

    <!-- Real-time WebSocket Integration -->
    <script src="assets/js/realtime-client.js"></script>
    <script src="assets/js/admin-realtime.js"></script>
    <script>
        // Initialize real-time updates
        initAdminRealtime(<?= $_SESSION['user_id'] ?>);
    </script>
</body>
</html>
