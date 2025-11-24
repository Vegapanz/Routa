<?php
session_start();
require_once 'config.php';

// Check if we have an authorization code
if (!isset($_GET['code'])) {
    header('Location: ../register.php?error=google_auth_failed');
    exit();
}

$authCode = $_GET['code'];

// Exchange authorization code for access token
$tokenUrl = 'https://oauth2.googleapis.com/token';
$tokenData = [
    'code' => $authCode,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local development
$tokenResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Log the response for debugging
error_log("Google Token Response: " . $tokenResponse);
error_log("HTTP Code: " . $httpCode);
if ($curlError) {
    error_log("CURL Error: " . $curlError);
}

$tokenInfo = json_decode($tokenResponse, true);

if (!isset($tokenInfo['access_token'])) {
    // Log detailed error
    $errorMsg = isset($tokenInfo['error_description']) ? $tokenInfo['error_description'] : 'Unknown error';
    error_log("Google OAuth Token Error: " . $errorMsg);
    
    // Show detailed error in development
    echo "<h3>Failed to get access token</h3>";
    echo "<p>Error: " . htmlspecialchars($errorMsg) . "</p>";
    echo "<p>Response: " . htmlspecialchars($tokenResponse) . "</p>";
    echo "<p><a href='../register.php'>Back to Register</a></p>";
    exit();
}

$accessToken = $tokenInfo['access_token'];

// Get user info from Google
$userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
$ch = curl_init($userInfoUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);
$userInfoResponse = curl_exec($ch);
curl_close($ch);

$userInfo = json_decode($userInfoResponse, true);

if (!isset($userInfo['email'])) {
    header('Location: ../register.php?error=user_info_failed');
    exit();
}

try {
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$userInfo['email']]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        // User exists, log them in
        $_SESSION['user_id'] = $existingUser['id'];
        $_SESSION['user_email'] = $existingUser['email'];
        $_SESSION['user_name'] = $existingUser['name'];
        $_SESSION['is_admin'] = false;
        $_SESSION['is_driver'] = false;
        
        header('Location: ../userdashboard.php');
        exit();
    } else {
        // Create new user
        $name = $userInfo['name'] ?? ($userInfo['given_name'] . ' ' . $userInfo['family_name']);
        $email = $userInfo['email'];
        $googleId = $userInfo['id'];
        
        // Generate a random password (user won't need it for Google login)
        $randomPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, google_id, email_verified, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
        $stmt->execute([$name, $email, $randomPassword, null, $googleId]);
        
        $userId = $pdo->lastInsertId();
        
        // Log the user in
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $name;
        $_SESSION['is_admin'] = false;
        $_SESSION['is_driver'] = false;
        
        // Redirect to dashboard with welcome message
        header('Location: ../userdashboard.php?welcome=1');
        exit();
    }
} catch (PDOException $e) {
    error_log("Google OAuth error: " . $e->getMessage());
    header('Location: ../register.php?error=database_error');
    exit();
}
?>
