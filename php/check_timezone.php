<?php
require_once 'config.php';

echo "<h2>Timezone & Time Check</h2>";

// PHP Time
echo "<h3>PHP Time Settings:</h3>";
echo "PHP Timezone: " . date_default_timezone_get() . "<br>";
echo "PHP Current Time: " . date('Y-m-d H:i:s') . "<br>";
echo "PHP Timestamp: " . time() . "<br>";
echo "PHP +5 minutes: " . date('Y-m-d H:i:s', time() + 300) . "<br>";

// MySQL Time
echo "<h3>MySQL Time Settings:</h3>";
try {
    $stmt = $pdo->query("SELECT NOW() as mysql_now, UNIX_TIMESTAMP() as mysql_timestamp, @@session.time_zone as session_tz, @@global.time_zone as global_tz");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "MySQL NOW(): " . $result['mysql_now'] . "<br>";
    echo "MySQL Timestamp: " . $result['mysql_timestamp'] . "<br>";
    echo "MySQL Session Timezone: " . $result['session_tz'] . "<br>";
    echo "MySQL Global Timezone: " . $result['global_tz'] . "<br>";
    
    // Compare
    echo "<h3>Time Comparison:</h3>";
    $phpTime = time();
    $mysqlTime = $result['mysql_timestamp'];
    $diff = abs($phpTime - $mysqlTime);
    
    echo "Time Difference: " . $diff . " seconds<br>";
    
    if ($diff > 60) {
        echo "<div style='color: red;'><strong>⚠ WARNING: Times are out of sync by more than 1 minute!</strong></div>";
        echo "<p>This will cause OTP expiration issues.</p>";
        echo "<p><strong>Solution:</strong> Set MySQL timezone in config.php:</p>";
        echo "<pre>// Add after PDO connection:
\$pdo->exec(\"SET time_zone = '+00:00'\"); // or your timezone like '+08:00' for Philippines</pre>";
    } else {
        echo "<div style='color: green;'><strong>✓ Times are in sync</strong></div>";
    }
    
    // Test OTP flow
    echo "<h3>Test OTP Expiration:</h3>";
    $testExpiry = date('Y-m-d H:i:s', time() + 300);
    echo "If OTP is created now, it expires at: " . $testExpiry . "<br>";
    
    $stmt = $pdo->prepare("SELECT ? > NOW() as is_future");
    $stmt->execute([$testExpiry]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['is_future']) {
        echo "<div style='color: green;'>✓ Expiry time is correctly in the future</div>";
    } else {
        echo "<div style='color: red;'>✗ Expiry time appears to be in the past! This will cause immediate expiration.</div>";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
