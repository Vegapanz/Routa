<?php
require_once 'config.php';

echo "<h2>Driver Applications Table Setup & Test</h2>";

// Step 1: Create table if it doesn't exist
echo "<h3>Step 1: Creating Table</h3>";
try {
    $createTableSQL = "CREATE TABLE IF NOT EXISTS driver_applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        middle_name VARCHAR(100) NULL DEFAULT NULL,
        last_name VARCHAR(100) NOT NULL,
        date_of_birth DATE NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL,
        address TEXT NOT NULL,
        barangay VARCHAR(100) NOT NULL,
        city VARCHAR(100) NOT NULL,
        zip_code VARCHAR(10) NOT NULL,
        license_number VARCHAR(50) NOT NULL,
        license_expiry DATE NOT NULL,
        driving_experience INT NOT NULL,
        emergency_name VARCHAR(100) NOT NULL,
        emergency_phone VARCHAR(20) NOT NULL,
        relationship VARCHAR(50) NOT NULL,
        previous_experience TEXT NULL DEFAULT NULL,
        vehicle_type ENUM('tricycle', 'motorcycle', 'car', 'van') NOT NULL,
        plate_number VARCHAR(20) NOT NULL,
        franchise_number VARCHAR(50) NULL DEFAULT NULL,
        make VARCHAR(50) NOT NULL,
        model VARCHAR(50) NOT NULL,
        year INT NOT NULL,
        license_photo VARCHAR(255) NULL DEFAULT NULL,
        vehicle_photo VARCHAR(255) NULL DEFAULT NULL,
        or_cr_photo VARCHAR(255) NULL DEFAULT NULL,
        nbi_clearance VARCHAR(255) NULL DEFAULT NULL,
        barangay_clearance VARCHAR(255) NULL DEFAULT NULL,
        vehicle_insurance VARCHAR(255) NULL DEFAULT NULL,
        franchise_certificate VARCHAR(255) NULL DEFAULT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        reviewed_date TIMESTAMP NULL DEFAULT NULL,
        admin_notes TEXT NULL DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($createTableSQL);
    echo "<p style='color: green;'>✓ Table created successfully or already exists</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error creating table: " . $e->getMessage() . "</p>";
    die();
}

// Step 2: Check if table exists and show structure
echo "<h3>Step 2: Verifying Table Structure</h3>";
try {
    $stmt = $pdo->query("DESCRIBE driver_applications");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p style='color: green;'>✓ Table exists with " . count($columns) . " columns</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error checking table: " . $e->getMessage() . "</p>";
    die();
}

// Step 3: Check if submit handler file exists
echo "<h3>Step 3: Checking Submit Handler</h3>";
$submitFile = __DIR__ . '/submit_driver_application.php';
if (file_exists($submitFile)) {
    echo "<p style='color: green;'>✓ submit_driver_application.php exists</p>";
} else {
    echo "<p style='color: red;'>✗ submit_driver_application.php NOT FOUND</p>";
}

// Step 4: Check upload directory
echo "<h3>Step 4: Checking Upload Directory</h3>";
$uploadDir = __DIR__ . '/../uploads/driver_documents';
if (!file_exists($uploadDir)) {
    if (mkdir($uploadDir, 0777, true)) {
        echo "<p style='color: green;'>✓ Upload directory created: $uploadDir</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Could not create upload directory: $uploadDir</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Upload directory exists: $uploadDir</p>";
    if (is_writable($uploadDir)) {
        echo "<p style='color: green;'>✓ Upload directory is writable</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Upload directory is NOT writable</p>";
    }
}

// Step 5: Test database connection
echo "<h3>Step 5: Testing Database Connection</h3>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM driver_applications");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p style='color: green;'>✓ Database connection working</p>";
    echo "<p>Current applications in database: " . $result['count'] . "</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Summary</h3>";
echo "<p>If all checks above passed (green ✓), your driver application system is ready.</p>";
echo "<p>You can now test the form at: <a href='../driver-application.php'>driver-application.php</a></p>";
?>
