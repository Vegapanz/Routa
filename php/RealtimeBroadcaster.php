<?php
/**
 * Routa Real-time Broadcaster
 * Use this from your API endpoints to send real-time notifications
 * WITHOUT blocking the API response
 * 
 * Usage:
 *   RealtimeBroadcaster::notifyUser($userId, ['type' => 'new_booking', ...]);
 *   RealtimeBroadcaster::notifyRole('driver', ['type' => 'new_ride', ...]);
 */

class RealtimeBroadcaster {
    private static $db = null;
    
    /**
     * Initialize database connection
     */
    private static function getDB() {
        if (self::$db === null) {
            self::$db = new mysqli('localhost', 'root', '', 'routa_db');
            self::$db->set_charset('utf8mb4');
        }
        return self::$db;
    }
    
    /**
     * Send notification to a specific user
     * 
     * @param int $userId User ID to send to
     * @param array $data Message data (will be JSON encoded)
     * @return bool Success status
     */
    public static function notifyUser($userId, $data) {
        $db = self::getDB();
        
        $stmt = $db->prepare("INSERT INTO realtime_notifications (target_type, target_id, data, status) VALUES ('user', ?, ?, 'pending')");
        $jsonData = json_encode($data);
        $stmt->bind_param("ss", $userId, $jsonData);
        
        return $stmt->execute();
    }
    
    /**
     * Send notification to all users with a specific role
     * 
     * @param string $role Role name (admin, driver, rider)
     * @param array $data Message data (will be JSON encoded)
     * @return bool Success status
     */
    public static function notifyRole($role, $data) {
        $db = self::getDB();
        
        $stmt = $db->prepare("INSERT INTO realtime_notifications (target_type, target_id, data, status) VALUES ('role', ?, ?, 'pending')");
        $jsonData = json_encode($data);
        $stmt->bind_param("ss", $role, $jsonData);
        
        return $stmt->execute();
    }
    
    /**
     * Broadcast to all admins (for dashboard updates)
     * 
     * @param array $data Message data (will be JSON encoded)
     * @return bool Success status
     */
    public static function broadcast($data) {
        return self::notifyRole('admin', $data);
    }
    
    /**
     * Notify about new booking to all available drivers
     */
    public static function notifyNewBooking($bookingId, $pickupLat, $pickupLng, $dropoffLat, $dropoffLng, $fare) {
        return self::notifyRole('driver', [
            'type' => 'new_booking',
            'booking_id' => $bookingId,
            'pickup' => ['lat' => $pickupLat, 'lng' => $pickupLng],
            'dropoff' => ['lat' => $dropoffLat, 'lng' => $dropoffLng],
            'fare' => $fare,
            'timestamp' => time()
        ]);
    }
    
    /**
     * Notify rider that driver has been assigned
     */
    public static function notifyBookingAssigned($riderId, $bookingId, $driverId, $driverName, $tricycleNumber) {
        return self::notifyUser($riderId, [
            'type' => 'booking_assigned',
            'booking_id' => $bookingId,
            'driver_id' => $driverId,
            'driver_name' => $driverName,
            'tricycle_number' => $tricycleNumber,
            'timestamp' => time()
        ]);
    }
    
    /**
     * Notify that driver accepted the ride
     */
    public static function notifyDriverAccepted($riderId, $bookingId, $driverId) {
        return self::notifyUser($riderId, [
            'type' => 'driver_accepted',
            'booking_id' => $bookingId,
            'driver_id' => $driverId,
            'timestamp' => time()
        ]);
    }
    
    /**
     * Notify about ride status change
     */
    public static function notifyStatusChange($userId, $bookingId, $status) {
        return self::notifyUser($userId, [
            'type' => 'status_update',
            'booking_id' => $bookingId,
            'status' => $status,
            'timestamp' => time()
        ]);
    }
    
    /**
     * Notify ride completion (for rating)
     */
    public static function notifyRideCompleted($riderId, $bookingId, $fare) {
        return self::notifyUser($riderId, [
            'type' => 'ride_completed',
            'booking_id' => $bookingId,
            'fare' => $fare,
            'timestamp' => time()
        ]);
    }
    
    /**
     * Check if user is currently connected
     */
    public static function isUserOnline($userId) {
        $db = self::getDB();
        
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM realtime_connections WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] > 0;
    }
    
    /**
     * Get all online users with specific role
     */
    public static function getOnlineUsers($role = null) {
        $db = self::getDB();
        
        if ($role) {
            $stmt = $db->prepare("SELECT user_id FROM realtime_connections WHERE role = ?");
            $stmt->bind_param("s", $role);
        } else {
            $stmt = $db->prepare("SELECT user_id FROM realtime_connections");
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row['user_id'];
        }
        
        return $users;
    }
    
    /**
     * Clean old notifications (call this periodically or in cron)
     */
    public static function cleanOldNotifications($hoursOld = 24) {
        $db = self::getDB();
        
        $stmt = $db->prepare("DELETE FROM realtime_notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? HOUR)");
        $stmt->bind_param("i", $hoursOld);
        
        return $stmt->execute();
    }
}

// Example usage in your booking API:
/*
// When new booking is created
RealtimeBroadcaster::notifyNewBooking(
    $bookingId, 
    $pickupLat, $pickupLng, 
    $dropoffLat, $dropoffLng, 
    $fare
);

// When admin assigns driver
RealtimeBroadcaster::notifyBookingAssigned(
    $riderId, 
    $bookingId, 
    $driverId, 
    $driverName, 
    $tricycleNumber
);

// When driver accepts
RealtimeBroadcaster::notifyDriverAccepted($riderId, $bookingId, $driverId);

// When status changes
RealtimeBroadcaster::notifyStatusChange($userId, $bookingId, 'started');

// When ride completes
RealtimeBroadcaster::notifyRideCompleted($riderId, $bookingId, $fare);
*/
