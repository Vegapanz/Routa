<?php
/**
 * Quick Session Test - Shows session immediately after login
 */
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Test - Routa</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .info { color: #3b82f6; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 20px; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-success { background: #10b981; color: white; }
        .badge-error { background: #ef4444; color: white; }
        .badge-info { background: #3b82f6; color: white; }
    </style>
</head>
<body>
    <h1>🔍 Session Test After Login</h1>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="box">
            <h2>✅ Session Active</h2>
            
            <h3>Session Variables:</h3>
            <pre><?php print_r($_SESSION); ?></pre>
            
            <h3>Type Detection:</h3>
            <?php
            $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
            $isDriver = isset($_SESSION['is_driver']) && $_SESSION['is_driver'] === true;
            $isUser = !$isAdmin && !$isDriver;
            ?>
            
            <p>
                <strong>Is Admin:</strong> 
                <?php if ($isAdmin): ?>
                    <span class="badge badge-success">TRUE</span> → Should go to admin.php
                <?php else: ?>
                    <span class="badge badge-error">FALSE</span>
                <?php endif; ?>
            </p>
            
            <p>
                <strong>Is Driver:</strong> 
                <?php if ($isDriver): ?>
                    <span class="badge badge-success">TRUE</span> → Should go to driver_dashboard.php
                <?php else: ?>
                    <span class="badge badge-error">FALSE</span>
                <?php endif; ?>
            </p>
            
            <p>
                <strong>Is Regular User:</strong> 
                <?php if ($isUser): ?>
                    <span class="badge badge-success">TRUE</span> → Should go to userdashboard.php
                <?php else: ?>
                    <span class="badge badge-error">FALSE</span>
                <?php endif; ?>
            </p>
            
            <h3>Expected Dashboard:</h3>
            <?php if ($isAdmin): ?>
                <p class="info">→ <strong>admin.php</strong></p>
            <?php elseif ($isDriver): ?>
                <p class="info">→ <strong>driver_dashboard.php</strong></p>
            <?php else: ?>
                <p class="success">→ <strong>userdashboard.php</strong></p>
            <?php endif; ?>
            
            <h3>Test Navigation:</h3>
            <p><a href="userdashboard.php">Go to User Dashboard</a></p>
            <p><a href="driver_dashboard.php">Go to Driver Dashboard</a></p>
            <p><a href="admin.php">Go to Admin Dashboard</a></p>
            
            <hr>
            <p><a href="php/logout.php">Logout</a> | <a href="debug_session.php">Full Debug</a></p>
        </div>
    <?php else: ?>
        <div class="box">
            <h2 class="error">❌ No Session</h2>
            <p>You are not logged in.</p>
            <p><a href="login.php">Go to Login</a></p>
        </div>
    <?php endif; ?>
</body>
</html>
