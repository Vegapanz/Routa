-- Real-time System Database Tables
-- Lightweight schema for notification queue system

-- Table for tracking active WebSocket connections
CREATE TABLE IF NOT EXISTS realtime_connections (
    user_id INT PRIMARY KEY,
    role ENUM('admin', 'driver', 'rider') NOT NULL,
    connected_at DATETIME NOT NULL,
    INDEX idx_role (role),
    INDEX idx_connected (connected_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for notification queue
-- Your API writes to this table, WebSocket server reads and broadcasts
CREATE TABLE IF NOT EXISTS realtime_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    target_type ENUM('user', 'role') NOT NULL,
    target_id VARCHAR(50) NOT NULL,
    data JSON NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    sent_at DATETIME NULL,
    INDEX idx_status (status, created_at),
    INDEX idx_target (target_type, target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add columns to existing tables for real-time features
-- These store the last known location for drivers

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS current_latitude DECIMAL(10, 8) NULL,
ADD COLUMN IF NOT EXISTS current_longitude DECIMAL(11, 8) NULL,
ADD COLUMN IF NOT EXISTS location_updated_at DATETIME NULL;

ALTER TABLE tricycle_drivers
ADD COLUMN IF NOT EXISTS current_latitude DECIMAL(10, 8) NULL,
ADD COLUMN IF NOT EXISTS current_longitude DECIMAL(11, 8) NULL,
ADD COLUMN IF NOT EXISTS location_updated_at DATETIME NULL,
ADD COLUMN IF NOT EXISTS is_online TINYINT(1) DEFAULT 0;

-- Add indexes for performance
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_location (current_latitude, current_longitude);
ALTER TABLE tricycle_drivers ADD INDEX IF NOT EXISTS idx_online (is_online);

-- Clean up old notifications (run periodically via cron or in server)
-- DELETE FROM realtime_notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR);
