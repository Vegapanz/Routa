<?php
require_once 'php/config.php';

echo "=== TRACING BOOKING ID 45 ===\n\n";

// Get booking details
$stmt = $pdo->prepare("SELECT * FROM ride_history WHERE id = 45");
$stmt->execute();
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if ($booking) {
    echo "Booking found:\n";
    print_r($booking);
    echo "\n";
} else {
    echo "Booking 45 not found!\n";
    exit;
}

// Check if notification was created for this booking
$stmt = $pdo->query("SELECT * FROM realtime_notifications WHERE data LIKE '%\"booking_id\":\"45\"%' OR data LIKE '%\"booking_id\":45%'");
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n=== NOTIFICATIONS FOR BOOKING 45 ===\n\n";

if (empty($notifications)) {
    echo "NO NOTIFICATION FOUND FOR BOOKING 45!\n";
    echo "This means book_ride.php did not send the notification.\n";
} else {
    foreach ($notifications as $notif) {
        echo "Notification ID: " . $notif['id'] . "\n";
        echo "Status: " . $notif['status'] . "\n";
        echo "Data: " . $notif['data'] . "\n";
        echo "Created: " . $notif['created_at'] . "\n";
        echo "---\n";
    }
}

// Check all notifications after ID 14
echo "\n=== ALL NOTIFICATIONS AFTER ID 14 ===\n\n";
$stmt = $pdo->query("SELECT id, target_type, target_id, status, created_at, SUBSTRING(data, 1, 150) as data_preview FROM realtime_notifications WHERE id > 14 ORDER BY id DESC");
$allNew = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($allNew)) {
    echo "No notifications created after ID 14.\n";
} else {
    foreach ($allNew as $notif) {
        echo "ID: " . $notif['id'] . " | Target: " . $notif['target_type'] . "/" . $notif['target_id'] . " | Status: " . $notif['status'] . " | Created: " . $notif['created_at'] . "\n";
        echo "Data: " . $notif['data_preview'] . "...\n\n";
    }
}
