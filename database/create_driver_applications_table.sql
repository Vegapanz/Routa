-- Run this script to create the driver_applications table

USE routa_db;

-- Driver Applications Table
CREATE TABLE IF NOT EXISTS driver_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Personal Information
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    barangay VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    zip_code VARCHAR(10) NOT NULL,
    
    -- Driver Information
    license_number VARCHAR(50) NOT NULL,
    license_expiry DATE NOT NULL,
    driving_experience VARCHAR(20) NOT NULL,
    
    -- Emergency Contact
    emergency_name VARCHAR(100) NOT NULL,
    emergency_phone VARCHAR(20) NOT NULL,
    relationship VARCHAR(50) NOT NULL,
    previous_experience TEXT,
    
    -- Vehicle Information
    vehicle_type VARCHAR(50) NOT NULL,
    plate_number VARCHAR(20) NOT NULL,
    franchise_number VARCHAR(50) NOT NULL,
    vehicle_make VARCHAR(50) NOT NULL,
    vehicle_model VARCHAR(50) NOT NULL,
    vehicle_year VARCHAR(10) NOT NULL,
    
    -- Document Paths
    license_document VARCHAR(255),
    government_id_document VARCHAR(255),
    registration_document VARCHAR(255),
    franchise_document VARCHAR(255),
    insurance_document VARCHAR(255),
    clearance_document VARCHAR(255),
    photo_document VARCHAR(255),
    
    -- Application Status
    status ENUM('pending', 'under_review', 'approved', 'rejected') DEFAULT 'pending',
    application_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    reviewed_date DATETIME,
    reviewed_by INT,
    rejection_reason TEXT,
    
    -- Additional Fields
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_application_date (application_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Check if table was created successfully
SELECT 'Driver applications table created successfully!' as message;
