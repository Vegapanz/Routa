-- Create test admin account
INSERT INTO admins (email, password, role) 
VALUES ('admin@routa.com', 'admin123', 'admin');

-- Show current admin accounts
SELECT * FROM admins;