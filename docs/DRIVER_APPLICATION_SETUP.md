# Driver Application System - Setup Guide

## Database Setup

### Option 1: Automatic Setup (Recommended)
1. Open your browser and navigate to: `http://localhost/Routa/php/setup_driver_table.php`
2. This will automatically create the `driver_applications` table in your database

### Option 2: Manual Setup
1. Open phpMyAdmin or your MySQL client
2. Select the `routa_db` database
3. Run the SQL script located at: `database/create_driver_applications_table.sql`

## File Upload Directory

The system will automatically create the upload directory at:
- `uploads/driver_applications/`

Make sure your web server has write permissions to the `uploads` folder.

## How It Works

### 1. Application Form (driver-application.php)
- Multi-step form with 4 stages
- Client-side validation for each step
- File upload support for documents

### 2. Backend Processing (php/submit_driver_application.php)
- Validates all form data
- Checks for duplicate email addresses
- Handles file uploads (max 5MB per file)
- Accepts JPG, PNG, and PDF files
- Stores application data in database
- Returns JSON response

### 3. Database Table (driver_applications)
Stores:
- Personal information
- Driver credentials
- Emergency contact
- Vehicle details
- Document file paths
- Application status (pending, under_review, approved, rejected)

## Application Status Flow

1. **pending** - Initial submission
2. **under_review** - Admin is reviewing
3. **approved** - Application accepted
4. **rejected** - Application declined

## File Upload Specifications

- Maximum file size: 5MB per document
- Allowed formats: JPG, PNG, PDF
- Files are stored in: `uploads/driver_applications/`
- Filename format: `[type]_[timestamp]_[unique_id].[ext]`

## Required Documents

1. Valid Driver's License (front and back)
2. Valid Government ID
3. Vehicle Registration (OR/CR)
4. Franchise/TODA Permit
5. Insurance Documents
6. Barangay Clearance
7. 2x2 ID Photo

## Testing the Application

1. Navigate to: `http://localhost/Routa/driver-application.php`
2. Fill in all 4 steps of the form
3. Upload required documents
4. Submit the application
5. Check the database to verify the data was saved

## View Applications

To view submitted applications, you can query the database:

```sql
SELECT * FROM driver_applications ORDER BY application_date DESC;
```

## Security Features

- ✅ File type validation
- ✅ File size limits
- ✅ SQL injection protection (prepared statements)
- ✅ XSS protection (input sanitization)
- ✅ Duplicate email checking
- ✅ Server-side validation

## Troubleshooting

### Issue: "Failed to upload file"
- Check folder permissions for `uploads/` directory
- Ensure PHP upload_max_filesize is at least 5MB

### Issue: "Database error"
- Verify the database connection in `php/config.php`
- Make sure the `driver_applications` table exists

### Issue: "Application not submitting"
- Check browser console for JavaScript errors
- Verify the PHP backend file path is correct
- Check PHP error logs

## Next Steps

1. ✅ Create admin dashboard to view applications
2. ✅ Add email notifications
3. ✅ Implement application status updates
4. ✅ Add document preview functionality
