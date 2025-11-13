<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    $id = $_GET['id'] ?? 0;
    
    if (!$id) {
        throw new Exception('Invalid application ID');
    }
    
    $stmt = $pdo->prepare("SELECT * FROM driver_applications WHERE id = ?");
    $stmt->execute([$id]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        throw new Exception('Application not found');
    }
    
    echo json_encode([
        'success' => true,
        'application' => $application
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
