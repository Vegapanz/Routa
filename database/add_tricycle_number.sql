-- Add tricycle_number column to tricycle_drivers table
ALTER TABLE tricycle_drivers 
ADD COLUMN tricycle_number VARCHAR(20) UNIQUE AFTER plate_number;

-- Create index for tricycle_number
CREATE INDEX idx_tricycle_number ON tricycle_drivers(tricycle_number);
