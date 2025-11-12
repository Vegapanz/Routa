<?php
/**
 * Session Debug Tool
 * Use this to check what's in your current session
 */
session_start();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Debug - Routa</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            padding: 20px;
            background: #1e1e1e;
            color: #d4d4d4;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #252526;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        h1 {
            color: #4ec9b0;
            border-bottom: 2px solid #4ec9b0;
            padding-bottom: 10px;
        }
        h2 {
            color: #569cd6;
            margin-top: 30px;
        }
        .session-data {
            background: #1e1e1e;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #4ec9b0;
            margin: 15px 0;
        }
        .key {
            color: #9cdcfe;
            font-weight: bold;
        }
        .value {
            color: #ce9178;
        }
        .type {
            color: #4ec9b0;
            font-size: 0.9em;
        }
        .null {
            color: #569cd6;
        }
        .boolean {
            color: #569cd6;
        }
        .warning {
            background: #3c3c3c;
            border-left: 4px solid #d7ba7d;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .error {
            background: #3c3c3c;
            border-left: 4px solid #f48771;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background: #3c3c3c;
            border-left: 4px solid #4ec9b0;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .btn {
            background: #0e639c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn:hover {
            background: #1177bb;
        }
        .btn-danger {
            background: #c72e2e;
        }
        .btn-danger:hover {
            background: #e03e3e;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Routa Session Debug Tool</h1>
        
        <?php if (empty($_SESSION)): ?>
            <div class="warning">
                ⚠️ <strong>No active session</strong> - You are not logged in
            </div>
        <?php else: ?>
            <div class="success">
                ✅ <strong>Active Session Found</strong>
            </div>
            
            <h2>Session Data:</h2>
            <div class="session-data">
                <?php foreach ($_SESSION as $key => $value): ?>
                    <div style="margin: 10px 0;">
                        <span class="key"><?= htmlspecialchars($key) ?></span>: 
                        <?php if (is_bool($value)): ?>
                            <span class="boolean"><?= $value ? 'true' : 'false' ?></span>
                        <?php elseif (is_null($value)): ?>
                            <span class="null">null</span>
                        <?php else: ?>
                            <span class="value"><?= htmlspecialchars($value) ?></span>
                        <?php endif; ?>
                        <span class="type">(<?= gettype($value) ?>)</span>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <h2>User Type Detection:</h2>
            <div class="session-data">
                <?php
                $userType = 'Unknown';
                $userTable = 'Unknown';
                
                if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
                    $userType = 'Admin';
                    $userTable = 'admins';
                } elseif (isset($_SESSION['is_driver']) && $_SESSION['is_driver']) {
                    $userType = 'Driver';
                    $userTable = 'tricycle_drivers';
                } elseif (isset($_SESSION['user_id'])) {
                    $userType = 'Regular User';
                    $userTable = 'users';
                }
                ?>
                <strong>Detected Type:</strong> <span class="value"><?= $userType ?></span><br>
                <strong>Should Query Table:</strong> <span class="value"><?= $userTable ?></span>
            </div>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <h2>Database Verification:</h2>
                <?php
                require_once 'php/config.php';
                
                $userId = $_SESSION['user_id'];
                
                // Check all three tables
                $tables = [
                    'users' => 'Regular User',
                    'tricycle_drivers' => 'Driver',
                    'admins' => 'Admin'
                ];
                
                foreach ($tables as $table => $label) {
                    $nameField = $table === 'admins' ? 'email' : 'name';
                    $stmt = $pdo->prepare("SELECT id, $nameField as name, email FROM $table WHERE id = ?");
                    $stmt->execute([$userId]);
                    $record = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($record) {
                        echo "<div class='session-data'>";
                        echo "<strong>✓ Found in '$table':</strong><br>";
                        echo "ID: <span class='value'>{$record['id']}</span><br>";
                        echo "Name: <span class='value'>" . htmlspecialchars($record['name']) . "</span><br>";
                        echo "Email: <span class='value'>" . htmlspecialchars($record['email']) . "</span>";
                        
                        if ($table === $userTable) {
                            echo "<br><strong style='color: #4ec9b0;'>✓ Correct table for session type</strong>";
                        } else {
                            echo "<br><strong style='color: #f48771;'>⚠ WARNING: ID exists in wrong table!</strong>";
                        }
                        echo "</div>";
                    }
                }
                ?>
                
                <?php if ($userType === 'Unknown'): ?>
                    <div class="error">
                        ❌ <strong>Session Corruption Detected!</strong><br>
                        Session has user_id but no type flags (is_admin, is_driver).<br>
                        This can cause data to be pulled from the wrong table.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
        
        <h2>Actions:</h2>
        <a href="index.php" class="btn">← Back to Home</a>
        <?php if (!empty($_SESSION)): ?>
            <a href="php/logout.php" class="btn btn-danger">Logout & Clear Session</a>
        <?php endif; ?>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #3c3c3c; color: #858585; font-size: 0.9em;">
            <strong>Debug Info:</strong><br>
            Session ID: <?= session_id() ?><br>
            Time: <?= date('Y-m-d H:i:s') ?>
        </div>
    </div>
</body>
</html>
