<?php
session_start();
require_once 'config.php';

// Check if we have an authorization code
if (!isset($_GET['code'])) {
    header('Location: ../register.php?error=facebook_auth_failed');
    exit();
}

$code = $_GET['code'];

// Exchange authorization code for access token
$tokenUrl = 'https://graph.facebook.com/v18.0/oauth/access_token';
$params = [
    'client_id' => FACEBOOK_APP_ID,
    'client_secret' => FACEBOOK_APP_SECRET,
    'redirect_uri' => FACEBOOK_REDIRECT_URI,
    'code' => $code
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tokenUrl . '?' . http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($response, true);

if (!isset($tokenData['access_token'])) {
    header('Location: ../register.php?error=token_failed');
    exit();
}

$accessToken = $tokenData['access_token'];

// Get user information from Facebook
$userInfoUrl = 'https://graph.facebook.com/v18.0/me?fields=id,name,email,picture&access_token=' . $accessToken;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$userInfoResponse = curl_exec($ch);
curl_close($ch);

$userInfo = json_decode($userInfoResponse, true);

if (!isset($userInfo['id'])) {
    header('Location: ../register.php?error=user_info_failed');
    exit();
}

$facebookId = $userInfo['id'];
$email = isset($userInfo['email']) ? $userInfo['email'] : null;
$name = $userInfo['name'];

// If email is not provided by Facebook, create a placeholder
if (!$email) {
    $email = $facebookId . '@facebook.user';
}

try {
    // Check if user already exists with this Facebook ID
    $stmt = $pdo->prepare("SELECT * FROM users WHERE facebook_id = ?");
    $stmt->execute([$facebookId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // User exists, log them in
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['is_admin'] = $user['is_admin'];
        $_SESSION['is_driver'] = $user['is_driver'];
        
        header('Location: ../userdashboard.php');
        exit();
    }

    // Check if user exists with this email (from regular registration or Google)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        // Link Facebook account to existing user
        $stmt = $pdo->prepare("UPDATE users SET facebook_id = ?, email_verified = 1 WHERE id = ?");
        $stmt->execute([$facebookId, $existingUser['id']]);

        $_SESSION['user_id'] = $existingUser['id'];
        $_SESSION['user_email'] = $existingUser['email'];
        $_SESSION['user_name'] = $existingUser['name'];
        $_SESSION['is_admin'] = $existingUser['is_admin'];
        $_SESSION['is_driver'] = $existingUser['is_driver'];
        
        header('Location: ../userdashboard.php');
        exit();
    }

    // Create new user
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, phone, facebook_id, email_verified, is_admin, is_driver) 
        VALUES (?, ?, ?, ?, ?, 1, 0, 0)
    ");
    
    // Generate a random password (user won't need it since they login via Facebook)
    $randomPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $phone = ''; // Facebook doesn't provide phone number
    
    $stmt->execute([$name, $email, $randomPassword, $phone, $facebookId]);
    $userId = $pdo->lastInsertId();

    // Log the user in
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $name;
    $_SESSION['is_admin'] = 0;
    $_SESSION['is_driver'] = 0;

    header('Location: ../userdashboard.php');
    exit();

} catch (PDOException $e) {
    error_log("Facebook OAuth Error: " . $e->getMessage());
    header('Location: ../register.php?error=database_error');
    exit();
}
?>
