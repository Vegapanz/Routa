<?php
require_once 'config.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get form data
    $firstName = trim($_POST['firstName'] ?? '');
    $middleName = trim($_POST['middleName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $dateOfBirth = trim($_POST['dateOfBirth'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $zipCode = trim($_POST['zipCode'] ?? '');
    
    $licenseNumber = trim($_POST['licenseNumber'] ?? '');
    $licenseExpiry = trim($_POST['licenseExpiry'] ?? '');
    $drivingExperience = trim($_POST['drivingExperience'] ?? '');
    
    $emergencyName = trim($_POST['emergencyName'] ?? '');
    $emergencyPhone = trim($_POST['emergencyPhone'] ?? '');
    $relationship = trim($_POST['relationship'] ?? '');
    $previousExperience = trim($_POST['previousExperience'] ?? '');
    
    $vehicleType = trim($_POST['vehicleType'] ?? '');
    $plateNumber = trim($_POST['plateNumber'] ?? '');
    $franchiseNumber = trim($_POST['franchiseNumber'] ?? '');
    $make = trim($_POST['make'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = trim($_POST['year'] ?? '');

    // Validate required fields
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password)) {
        throw new Exception('Please fill in all required fields');
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Validate password length
    if (strlen($password) < 8) {
        throw new Exception('Password must be at least 8 characters long');
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $checkStmt = $pdo->prepare("SELECT id FROM driver_applications WHERE email = ?");
    $checkStmt->execute([$email]);
    if ($checkStmt->fetch()) {
        throw new Exception('An application with this email already exists');
    }

    // Create upload directory if it doesn't exist
    $uploadDir = '../uploads/driver_applications/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Function to handle file upload
    function uploadFile($file, $uploadDir, $prefix) {
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }

        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('File size exceeds 5MB limit');
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and PDF files are allowed');
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $prefix . '_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to upload file');
        }

        return 'uploads/driver_applications/' . $filename;
    }

    // Handle file uploads
    $licenseDoc = uploadFile($_FILES['license'] ?? null, $uploadDir, 'license');
    $govIdDoc = uploadFile($_FILES['governmentId'] ?? null, $uploadDir, 'govid');
    $registrationDoc = uploadFile($_FILES['registration'] ?? null, $uploadDir, 'registration');
    $franchiseDoc = uploadFile($_FILES['franchise'] ?? null, $uploadDir, 'franchise');
    $insuranceDoc = uploadFile($_FILES['insurance'] ?? null, $uploadDir, 'insurance');
    $clearanceDoc = uploadFile($_FILES['clearance'] ?? null, $uploadDir, 'clearance');
    $photoDoc = uploadFile($_FILES['photo'] ?? null, $uploadDir, 'photo');

    // Convert date formats - now expecting Y-m-d from HTML5 date input
    function formatDate($dateString) {
        if (empty($dateString)) return null;
        // HTML5 date input sends Y-m-d format
        $date = DateTime::createFromFormat('Y-m-d', $dateString);
        if (!$date) {
            // Fallback to other formats
            $date = DateTime::createFromFormat('d-m-Y', $dateString);
        }
        if (!$date) {
            $date = DateTime::createFromFormat('m/d/Y', $dateString);
        }
        return $date ? $date->format('Y-m-d') : null;
    }

    $dobFormatted = formatDate($dateOfBirth);
    $licenseExpiryFormatted = formatDate($licenseExpiry);
    
    // Validate dates
    if (!$dobFormatted) {
        throw new Exception('Invalid date of birth format');
    }
    if (!$licenseExpiryFormatted) {
        throw new Exception('Invalid license expiry date format');
    }
    
    // Check if applicant is at least 18 years old
    $today = new DateTime();
    $dob = new DateTime($dobFormatted);
    $age = $today->diff($dob)->y;
    if ($age < 18) {
        throw new Exception('Applicant must be at least 18 years old');
    }
    
    // Check if license is not expired
    $licenseExp = new DateTime($licenseExpiryFormatted);
    if ($licenseExp < $today) {
        throw new Exception('Driver license has expired. Please renew before applying.');
    }

    // Insert into database
    $sql = "INSERT INTO driver_applications (
        first_name, middle_name, last_name, date_of_birth, phone, email, password,
        address, barangay, city, zip_code,
        license_number, license_expiry, driving_experience,
        emergency_name, emergency_phone, relationship, previous_experience,
        vehicle_type, plate_number, franchise_number, vehicle_make, vehicle_model, vehicle_year,
        license_document, government_id_document, registration_document, 
        franchise_document, insurance_document, clearance_document, photo_document,
        status, application_date
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?, ?,
        'pending', NOW()
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $firstName, $middleName, $lastName, $dobFormatted, $phone, $email, $hashedPassword,
        $address, $barangay, $city, $zipCode,
        $licenseNumber, $licenseExpiryFormatted, $drivingExperience,
        $emergencyName, $emergencyPhone, $relationship, $previousExperience,
        $vehicleType, $plateNumber, $franchiseNumber, $make, $model, $year,
        $licenseDoc, $govIdDoc, $registrationDoc,
        $franchiseDoc, $insuranceDoc, $clearanceDoc, $photoDoc
    ]);

    $applicationId = $pdo->lastInsertId();

    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Application submitted successfully! We will review your application and get back to you within 2-3 business days.',
        'application_id' => $applicationId
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
