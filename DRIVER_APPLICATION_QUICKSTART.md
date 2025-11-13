# 🚀 Quick Setup Guide - Driver Application System

## Step 1: Create Database Table

Visit this URL in your browser:
```
http://localhost/Routa/php/setup_driver_table.php
```

You should see:
```json
{"success":true,"message":"Driver applications table created successfully!"}
```

## Step 2: Test the Application Form

1. Go to: `http://localhost/Routa/driver-application.php`
2. Fill in all 4 steps
3. Upload documents (JPG, PNG, or PDF - max 5MB each)
4. Click "Submit Application"

## Step 3: View Submitted Applications

Go to: `http://localhost/Routa/view_driver_applications.php`

This page shows all submitted applications with:
- Applicant information
- Application status
- View details button

## 📁 Files Created

### Frontend:
- `driver-application.php` - Multi-step application form
- `assets/css/pages/driver-application.css` - Styling
- `assets/js/pages/driver-application.js` - Form logic

### Backend:
- `php/submit_driver_application.php` - Handles form submission
- `php/get_application_details.php` - Retrieves application data
- `php/setup_driver_table.php` - Database setup script

### Database:
- `database/driver_applications.sql` - Table schema
- `database/create_driver_applications_table.sql` - Setup script

### Admin:
- `view_driver_applications.php` - View all applications

### Documentation:
- `docs/DRIVER_APPLICATION_SETUP.md` - Detailed setup guide

## ✅ Features Implemented

1. **Multi-step Form** - 4 steps with progress indicator
2. **File Uploads** - Secure document upload system
3. **Validation** - Client and server-side validation
4. **Database Storage** - All data saved to MySQL
5. **Status Tracking** - pending, under_review, approved, rejected
6. **Admin View** - Simple interface to view applications

## 🔐 Security Features

- ✅ File type validation (JPG, PNG, PDF only)
- ✅ File size limits (5MB max)
- ✅ SQL injection protection
- ✅ XSS protection
- ✅ Duplicate email checking
- ✅ Prepared statements

## 📊 Database Table Structure

```
driver_applications
├── Personal Info (name, email, phone, address)
├── Driver Info (license, experience, emergency contact)
├── Vehicle Info (type, plate, franchise, make/model)
├── Documents (7 file paths)
└── Status (pending/under_review/approved/rejected)
```

## 🎯 Next Steps

To enhance the system:

1. **Admin Dashboard** - Full CRUD operations
2. **Email Notifications** - Auto-send on submission/approval
3. **Document Viewer** - Preview uploaded files
4. **Application Approval** - Workflow for reviewing applications
5. **Driver Account Creation** - Auto-create driver account on approval

## 🐛 Troubleshooting

### Upload folder not found?
The system auto-creates `uploads/driver_applications/` folder.
If issues persist, manually create it and set permissions to 777.

### Database connection error?
Check `php/config.php` - ensure database credentials are correct.

### Files not uploading?
Check `php.ini`:
- `upload_max_filesize = 10M`
- `post_max_size = 10M`

## 🎉 You're All Set!

Your driver application system is now fully functional with database integration and file upload capabilities!
