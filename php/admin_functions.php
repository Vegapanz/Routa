<?php

require_once 'config.php';

function getDashboardStats($pdo) {
    try {
        $stats = [];
        
        // Get current month's total revenue (only completed rides)
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(fare), 0) as total_revenue FROM ride_history WHERE MONTH(created_at) = MONTH(CURDATE()) AND status = 'completed'");
        $stmt->execute();
        $stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'];

        // Get total bookings count
        $stmt = $pdo->prepare("SELECT COUNT(*) as total_bookings FROM ride_history");
        $stmt->execute();
        $stats['total_bookings'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_bookings'];

        // Get active drivers count
        $stmt = $pdo->prepare("SELECT COUNT(*) as active_drivers FROM tricycle_drivers WHERE status = 'available'");
        $stmt->execute();
        $stats['active_drivers'] = $stmt->fetch(PDO::FETCH_ASSOC)['active_drivers'];

        // Get pending bookings count
        $stmt = $pdo->prepare("SELECT COUNT(*) as pending_bookings FROM ride_history WHERE status = 'pending'");
        $stmt->execute();
        $stats['pending_bookings'] = $stmt->fetch(PDO::FETCH_ASSOC)['pending_bookings'];

        return $stats;
    } catch (PDOException $e) {
        error_log("Error in getDashboardStats: " . $e->getMessage());
        return false;
    }
}

function getPendingBookings($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT 
            r.id as booking_id,
            r.user_id,
            r.pickup_location,
            r.destination,
            r.fare,
            r.created_at,
            u.name as rider_name,
            u.phone
            FROM ride_history r
            JOIN users u ON r.user_id = u.id
            WHERE r.status = 'pending'
            ORDER BY r.created_at DESC
            LIMIT 10");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in getPendingBookings: " . $e->getMessage());
        return false;
    }
}

function getAvailableDrivers($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id, name FROM tricycle_drivers WHERE status = 'available'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in getAvailableDrivers: " . $e->getMessage());
        return false;
    }
}

function assignBooking($pdo, $bookingId, $driverId) {
    require_once __DIR__ . '/RealtimeBroadcaster.php';
    
    try {
        $pdo->beginTransaction();

        // Get driver and booking details
        $stmt = $pdo->prepare("SELECT name, email FROM tricycle_drivers WHERE id = ?");
        $stmt->execute([$driverId]);
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$driver) {
            error_log("Driver not found: " . $driverId);
            $pdo->rollBack();
            return false;
        }

        // Get booking details for notification
        $stmt = $pdo->prepare("SELECT pickup_location, destination, user_id FROM ride_history WHERE id = ?");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking) {
            error_log("Booking not found: " . $bookingId);
            $pdo->rollBack();
            return false;
        }

        // Update ride history - set to 'driver_found' (waiting for driver to accept)
        $stmt = $pdo->prepare("UPDATE ride_history SET 
            driver_id = ?,
            driver_name = ?,
            status = 'driver_found',
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ?");
        $result = $stmt->execute([$driverId, $driver['name'], $bookingId]);
        
        if (!$result) {
            error_log("Failed to update ride_history for booking: " . $bookingId);
            $pdo->rollBack();
            return false;
        }

        // Check if row was actually updated
        if ($stmt->rowCount() === 0) {
            error_log("No rows updated for booking: " . $bookingId);
            $pdo->rollBack();
            return false;
        }

        // Create notification for driver (they need to accept/reject)
        $stmt = $pdo->prepare("
            INSERT INTO ride_notifications 
            (ride_id, recipient_id, recipient_type, notification_type, message, created_at) 
            VALUES (?, ?, 'driver', 'new_ride', ?, NOW())
        ");
        $stmt->execute([
            $bookingId, 
            $driverId, 
            "New ride assigned: {$booking['pickup_location']} to {$booking['destination']}"
        ]);

        // Create notification for user
        $stmt = $pdo->prepare("
            INSERT INTO ride_notifications 
            (ride_id, recipient_id, recipient_type, notification_type, message, created_at) 
            VALUES (?, ?, 'user', 'driver_assigned', ?, NOW())
        ");
        $stmt->execute([
            $bookingId, 
            $booking['user_id'], 
            "Driver {$driver['name']} has been assigned. Waiting for driver confirmation..."
        ]);

        // Get driver details for real-time notification
        $driverStmt = $pdo->prepare("SELECT name, phone, tricycle_number FROM tricycle_drivers WHERE id = ?");
        $driverStmt->execute([$driverId]);
        $driverData = $driverStmt->fetch(PDO::FETCH_ASSOC);

        // Get full booking details
        $bookingStmt = $pdo->prepare("SELECT * FROM ride_history WHERE id = ?");
        $bookingStmt->execute([$bookingId]);
        $bookingData = $bookingStmt->fetch(PDO::FETCH_ASSOC);

        $pdo->commit();
        
        // Send real-time notification to driver
        RealtimeBroadcaster::notifyUser($driverId, [
            'type' => 'booking_assigned',
            'booking_id' => $bookingId,
            'pickup' => [
                'address' => $bookingData['pickup_location'],
                'lat' => $bookingData['pickup_lat'],
                'lng' => $bookingData['pickup_lng']
            ],
            'dropoff' => [
                'address' => $bookingData['destination'],
                'lat' => $bookingData['dropoff_lat'],
                'lng' => $bookingData['dropoff_lng']
            ],
            'fare' => $bookingData['fare'],
            'distance' => $bookingData['distance'],
            'timestamp' => time()
        ]);

        // Send real-time notification to rider
        RealtimeBroadcaster::notifyUser($booking['user_id'], [
            'type' => 'booking_assigned',
            'booking_id' => $bookingId,
            'driver_id' => $driverId,
            'driver_name' => $driverData['name'],
            'driver_phone' => $driverData['phone'] ?? 'N/A',
            'tricycle_number' => $driverData['tricycle_number'] ?? 'N/A',
            'timestamp' => time()
        ]);
        
        error_log("Successfully assigned driver $driverId to booking $bookingId - status: driver_found (waiting for driver acceptance)");
        return true;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error in assignBooking: " . $e->getMessage());
        error_log("Booking ID: $bookingId, Driver ID: $driverId");
        return false;
    }
}

function rejectBooking($pdo, $bookingId) {
    require_once __DIR__ . '/RealtimeBroadcaster.php';
    
    try {
        // Get booking details before rejecting
        $stmt = $pdo->prepare("SELECT user_id, pickup_location, destination FROM ride_history WHERE id = ?");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking) {
            error_log("Booking not found: " . $bookingId);
            return false;
        }
        
        // Update booking status to rejected
        $stmt = $pdo->prepare("UPDATE ride_history SET status = 'rejected', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $result = $stmt->execute([$bookingId]);
        
        if ($result) {
            // Send real-time notification to user
            RealtimeBroadcaster::notifyUser($booking['user_id'], [
                'type' => 'booking_rejected',
                'booking_id' => $bookingId,
                'message' => 'Your booking request has been rejected. Please try booking again.',
                'timestamp' => time()
            ]);
            
            // Broadcast to admin dashboard for real-time update
            RealtimeBroadcaster::broadcast([
                'type' => 'booking_rejected',
                'booking_id' => $bookingId,
                'status' => 'rejected',
                'timestamp' => time()
            ]);
            
            error_log("Booking rejected and user notified: " . $bookingId);
        }
        
        return $result;
    } catch (PDOException $e) {
        error_log("Error in rejectBooking: " . $e->getMessage());
        return false;
    }
}

function getBookingHistory($pdo, $limit = 10) {
    try {
        $stmt = $pdo->prepare("SELECT 
            r.id,
            r.pickup_location,
            r.destination,
            r.fare,
            r.status,
            r.created_at,
            u.name as rider_name,
            d.name as driver_name
            FROM ride_history r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN tricycle_drivers d ON r.driver_id = d.id
            ORDER BY r.created_at DESC
            LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in getBookingHistory: " . $e->getMessage());
        return false;
    }
}

function generateUniqueTricycleNumber($pdo) {
    $maxAttempts = 100;
    $attempt = 0;
    
    while ($attempt < $maxAttempts) {
        // Get the highest existing tricycle number
        $stmt = $pdo->prepare("SELECT tricycle_number FROM tricycle_drivers WHERE tricycle_number LIKE 'TRY-%' ORDER BY CAST(SUBSTRING(tricycle_number, 5) AS UNSIGNED) DESC LIMIT 1");
        $stmt->execute();
        $lastTricycle = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lastTricycle && preg_match('/TRY-(\d+)/', $lastTricycle['tricycle_number'], $matches)) {
            // Increment from last number
            $nextNumber = intval($matches[1]) + 1;
        } else {
            // Start from 001 if no tricycles exist
            $nextNumber = 1;
        }
        
        // Format as TRY-XXX (3 digits)
        $tricycleNumber = 'TRY-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        
        // Check if this number already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tricycle_drivers WHERE tricycle_number = ?");
        $stmt->execute([$tricycleNumber]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
        
        if (!$exists) {
            return $tricycleNumber;
        }
        
        $attempt++;
    }
    
    // Fallback: use timestamp-based unique number if all attempts fail
    return 'TRY-' . substr(time(), -6);
}

function approveDriverApplication($pdo, $applicationId) {
    try {
        $pdo->beginTransaction();
        
        // Get application details
        $stmt = $pdo->prepare("SELECT * FROM driver_applications WHERE id = ? AND status = 'pending'");
        $stmt->execute([$applicationId]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$application) {
            error_log("Application not found or not pending: " . $applicationId);
            $pdo->rollBack();
            throw new Exception("Application not found or already processed");
        }
        
        // Check if driver with same email, phone, license number, or plate number already exists
        $stmt = $pdo->prepare("
            SELECT id, email, phone, license_number, plate_number 
            FROM tricycle_drivers 
            WHERE email = ? OR phone = ? OR license_number = ? OR plate_number = ?
        ");
        $stmt->execute([
            $application['email'], 
            $application['phone'], 
            $application['license_number'], 
            $application['plate_number']
        ]);
        $existingDriver = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingDriver) {
            // Determine which field is duplicate for better error message
            $duplicateField = '';
            if ($existingDriver['email'] === $application['email']) {
                $duplicateField = 'email address';
            } elseif ($existingDriver['phone'] === $application['phone']) {
                $duplicateField = 'phone number';
            } elseif ($existingDriver['license_number'] === $application['license_number']) {
                $duplicateField = 'license number';
            } elseif ($existingDriver['plate_number'] === $application['plate_number']) {
                $duplicateField = 'plate number';
            }
            
            error_log("Driver with duplicate {$duplicateField} already exists");
            $pdo->rollBack();
            throw new Exception("A driver with this {$duplicateField} already exists in the system");
        }
        
        // Generate unique tricycle number
        $tricycleNumber = generateUniqueTricycleNumber($pdo);
        
        // Combine first, middle, and last name
        $fullName = trim($application['first_name'] . ' ' . ($application['middle_name'] ? $application['middle_name'] . ' ' : '') . $application['last_name']);
        
        // Insert into tricycle_drivers table with password
        $stmt = $pdo->prepare("
            INSERT INTO tricycle_drivers 
            (name, email, password, phone, license_number, plate_number, tricycle_number, status, rating, average_rating, total_trips_completed, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'offline', 5.00, 5.00, 0, NOW())
        ");
        $result = $stmt->execute([
            $fullName,
            $application['email'],
            $application['password'], // Transfer hashed password
            $application['phone'],
            $application['license_number'],
            $application['plate_number'],
            $tricycleNumber
        ]);
        
        if (!$result) {
            error_log("Failed to insert driver");
            $pdo->rollBack();
            throw new Exception("Failed to create driver account");
        }
        
        // Update application status to approved
        $stmt = $pdo->prepare("UPDATE driver_applications SET status = 'approved', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$applicationId]);
        
        $pdo->commit();
        error_log("Successfully approved application $applicationId and created driver account");
        
        // Send approval email
        try {
            require_once __DIR__ . '/email_helper.php';
            $emailSent = sendDriverApprovalEmail(
                $application['email'],
                $application['first_name'],
                $application['last_name'],
                $tricycleNumber,
                $application['plate_number']
            );
            
            if ($emailSent) {
                error_log("Approval email sent to: {$application['email']}");
            } else {
                error_log("Failed to send approval email to: {$application['email']}");
            }
        } catch (Exception $emailError) {
            error_log("Error sending approval email: " . $emailError->getMessage());
        }
        
        return true;
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error in approveDriverApplication: " . $e->getMessage());
        
        // Provide user-friendly error messages
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            if (strpos($e->getMessage(), 'plate_number') !== false) {
                throw new Exception("This plate number is already registered in the system");
            } elseif (strpos($e->getMessage(), 'email') !== false) {
                throw new Exception("This email address is already registered in the system");
            } elseif (strpos($e->getMessage(), 'phone') !== false) {
                throw new Exception("This phone number is already registered in the system");
            } elseif (strpos($e->getMessage(), 'license_number') !== false) {
                throw new Exception("This license number is already registered in the system");
            }
        }
        
        throw new Exception("Database error: Unable to approve application. Please try again.");
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e; // Re-throw the exception to be caught by admin.php
    }
}

function rejectDriverApplication($pdo, $applicationId, $rejectionReason = '') {
    try {
        // Get application details first
        $stmt = $pdo->prepare("SELECT * FROM driver_applications WHERE id = ? AND status = 'pending'");
        $stmt->execute([$applicationId]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$application) {
            error_log("No application found or not pending: $applicationId");
            return false;
        }
        
        // Update status to rejected
        $stmt = $pdo->prepare("UPDATE driver_applications SET status = 'rejected', updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$applicationId]);
        
        if ($stmt->rowCount() > 0) {
            error_log("Successfully rejected application $applicationId");
            
            // Send rejection email
            try {
                require_once __DIR__ . '/email_helper.php';
                $emailSent = sendDriverRejectionEmail(
                    $application['email'],
                    $application['first_name'],
                    $application['last_name'],
                    $rejectionReason
                );
                
                if ($emailSent) {
                    error_log("Rejection email sent to: {$application['email']}");
                } else {
                    error_log("Failed to send rejection email to: {$application['email']}");
                }
            } catch (Exception $emailError) {
                error_log("Error sending rejection email: " . $emailError->getMessage());
            }
            
            return true;
        }
        
        error_log("Failed to update application status for: $applicationId");
        return false;
        
    } catch (PDOException $e) {
        error_log("Error in rejectDriverApplication: " . $e->getMessage());
        return false;
    }
}

function getApplicationDetails($pdo, $applicationId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM driver_applications WHERE id = ?");
        $stmt->execute([$applicationId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in getApplicationDetails: " . $e->getMessage());
        return false;
    }
}