<?php

require_once 'config.php';

function getDashboardStats($pdo) {
    try {
        $stats = [];
        
        // Get current month's total revenue
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(fare), 0) as total_revenue FROM ride_history WHERE MONTH(created_at) = MONTH(CURDATE())");
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

        $pdo->commit();
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
    try {
        $stmt = $pdo->prepare("UPDATE ride_history SET status = 'rejected', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$bookingId]);
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