-- Add password column to driver_applications table
ALTER TABLE driver_applications 
ADD COLUMN password VARCHAR(255) NOT NULL AFTER email;
