<?php
/**
 * Test Accept Ride - Debugging Tool
 * This file helps test the accept ride functionality
 */

session_start();
require_once 'php/config.php';

// Check if driver is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_driver'])) {
    die('ERROR: Not logged in as driver. Session data: ' . json_encode($_SESSION));
}

echo "<h2>Driver Session Check</h2>";
echo "<pre>";
echo "User ID: " . $_SESSION['user_id'] . "\n";
echo "Is Driver: " . ($_SESSION['is_driver'] ? 'Yes' : 'No') . "\n";
echo "User Name: " . ($_SESSION['user_name'] ?? 'Not set') . "\n";
echo "</pre>";

// Get pending rides for this driver
$driverId = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT r.*, u.name as rider_name 
    FROM ride_history r 
    LEFT JOIN users u ON r.user_id = u.id 
    WHERE r.driver_id = ? AND r.status = 'driver_found'
");
$stmt->execute([$driverId]);
$pendingRides = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Pending Rides (driver_found status)</h2>";
if (empty($pendingRides)) {
    echo "<p>No pending rides found.</p>";
    
    // Check all rides for this driver
    $stmt = $pdo->prepare("
        SELECT id, status, pickup_location, destination, created_at 
        FROM ride_history 
        WHERE driver_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$driverId]);
    $allRides = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Recent rides for this driver:</h3>";
    echo "<pre>";
    print_r($allRides);
    echo "</pre>";
} else {
    echo "<pre>";
    print_r($pendingRides);
    echo "</pre>";
    
    echo "<h2>Test Accept Ride</h2>";
    foreach ($pendingRides as $ride) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
        echo "<p><strong>Booking ID:</strong> BK-" . str_pad($ride['id'], 3, '0', STR_PAD_LEFT) . "</p>";
        echo "<p><strong>Rider:</strong> " . htmlspecialchars($ride['rider_name']) . "</p>";
        echo "<p><strong>From:</strong> " . htmlspecialchars($ride['pickup_location']) . "</p>";
        echo "<p><strong>To:</strong> " . htmlspecialchars($ride['destination']) . "</p>";
        echo "<button onclick='testAcceptRide(" . $ride['id'] . ")'>Test Accept Ride</button>";
        echo "<div id='result-" . $ride['id'] . "' style='margin-top: 10px; padding: 10px; background: #f0f0f0;'></div>";
        echo "</div>";
    }
}

// Check driver status
$stmt = $pdo->prepare("SELECT status, current_lat, current_lng FROM tricycle_drivers WHERE id = ?");
$stmt->execute([$driverId]);
$driverInfo = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h2>Driver Status</h2>";
echo "<pre>";
print_r($driverInfo);
echo "</pre>";

?>

<script>
async function testAcceptRide(bookingId) {
    console.log('Testing accept ride for booking:', bookingId);
    const resultDiv = document.getElementById('result-' + bookingId);
    resultDiv.innerHTML = 'Sending request...';
    
    try {
        const response = await fetch('php/driver_api.php?action=accept_ride', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ride_id: bookingId })
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        const text = await response.text();
        console.log('Raw response:', text);
        
        try {
            const data = JSON.parse(text);
            console.log('Parsed data:', data);
            
            resultDiv.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            
            if (data.success) {
                resultDiv.style.background = '#d4edda';
                alert('Success! The page will reload to show updated status.');
                setTimeout(() => window.location.reload(), 2000);
            } else {
                resultDiv.style.background = '#f8d7da';
            }
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            resultDiv.innerHTML = '<strong>Error parsing response:</strong><br><pre>' + text + '</pre>';
            resultDiv.style.background = '#f8d7da';
        }
    } catch (error) {
        console.error('Error:', error);
        resultDiv.innerHTML = '<strong>Network Error:</strong> ' + error.message;
        resultDiv.style.background = '#f8d7da';
    }
}
</script>

<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }
    button {
        background: #10b981;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
    }
    button:hover {
        background: #059669;
    }
</style>
