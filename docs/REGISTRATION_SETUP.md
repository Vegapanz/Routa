# Registration Setup Instructions

## Changes Made

### 1. Updated `php/register.php`
- ✅ Now accepts `fullName` field from the form
- ✅ Includes password hashing using `password_hash()` with `PASSWORD_DEFAULT`
- ✅ Validates email format
- ✅ Validates password length (minimum 8 characters)
- ✅ Checks for duplicate emails
- ✅ Inserts user data into database with phone number
- ✅ Returns JSON responses for success/error

### 2. Updated `assets/js/pages/register.js`
- ✅ Form now sends data to `php/register.php` using fetch API
- ✅ Handles JSON responses from backend
- ✅ Shows appropriate error/success messages
- ✅ Redirects to login page after successful registration

### 3. Updated Database Schema
- ✅ Added `phone` field to users table in `database.sql`
- ✅ Created `update_users_table.sql` for existing databases

## Setup Instructions

### Step 1: Update Database Structure

If you haven't created the database yet:
```sql
-- Run the complete database.sql file in phpMyAdmin or MySQL
source database.sql;
```

If you already have the database created:
```sql
-- Run the update script to add the phone column
source update_users_table.sql;
```

Or manually in phpMyAdmin:
1. Open phpMyAdmin
2. Select the `routa_db` database
3. Click on the `users` table
4. Click "Structure" tab
5. Click "Add column"
6. Add a column named `phone` with type VARCHAR(25), allow NULL

### Step 2: Test Database Connection

1. Open your browser and navigate to:
   ```
   http://localhost/Routa/php/test_registration.php
   ```

2. This will verify:
   - Database connection is working
   - Users table exists
   - All required columns are present
   - Password hashing is working

### Step 3: Test Registration

1. Navigate to the registration page:
   ```
   http://localhost/Routa/register.php
   ```

2. Fill in the form:
   - Full Name: Test User
   - Email: test@example.com
   - Phone: +63 912 345 6789
   - Password: testpass123
   - Confirm Password: testpass123
   - Check the Terms checkbox

3. Click "Create Account"

4. If successful, you'll be redirected to the login page

### Step 4: Verify in Database

1. Open phpMyAdmin
2. Select `routa_db` database
3. Click on `users` table
4. You should see your new user with:
   - Name, email, phone fields filled
   - Password is hashed (long string starting with $2y$)

## How Password Hashing Works

The system uses PHP's `password_hash()` function with the following features:

1. **Hashing**: When a user registers, the password is hashed using `PASSWORD_DEFAULT` (currently bcrypt)
   ```php
   $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
   ```

2. **Storage**: The hashed password is stored in the database (not the plain text)

3. **Verification**: When logging in, use `password_verify()` to check passwords:
   ```php
   if (password_verify($inputPassword, $hashedPassword)) {
       // Password is correct
   }
   ```

## Troubleshooting

### Error: "Connection failed"
- Make sure XAMPP MySQL is running
- Check database credentials in `php/config.php`
- Database name should be `routa_db`

### Error: "Email already registered"
- The email is already in the database
- Use a different email or delete the existing record

### Error: "An error occurred"
- Check PHP error logs in XAMPP
- Make sure all required columns exist in the users table
- Run the test script to diagnose issues

### Form doesn't submit
- Open browser console (F12) to check for JavaScript errors
- Make sure the path to `php/register.php` is correct
- Check network tab to see the actual request/response

## Security Features Implemented

✅ **Password Hashing**: Passwords are hashed with bcrypt  
✅ **SQL Injection Prevention**: Using prepared statements with PDO  
✅ **Email Validation**: Server-side email format validation  
✅ **Duplicate Prevention**: Checks for existing emails  
✅ **Input Validation**: Required fields validation  
✅ **JSON Responses**: Secure API-style responses  

## Next Steps

To complete the authentication system, you should also update the login functionality to use `password_verify()` for checking hashed passwords.

Would you like me to update the login functionality as well?
