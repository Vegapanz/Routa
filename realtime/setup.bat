@echo off
echo ================================================
echo   Routa Real-time System - Setup Script
echo ================================================
echo.

REM Check if MySQL is running
echo [1/4] Checking MySQL...
D:\xampp\mysql\bin\mysql.exe -u root -e "SELECT 1" >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: MySQL is not running. Please start XAMPP MySQL first.
    pause
    exit /b 1
)
echo MySQL is running!

REM Import database schema
echo.
echo [2/4] Creating database tables...
D:\xampp\mysql\bin\mysql.exe -u root routa_db < database\realtime_system.sql
if %errorlevel% neq 0 (
    echo ERROR: Failed to import database schema
    pause
    exit /b 1
)
echo Database tables created successfully!

REM Check PHP version
echo.
echo [3/4] Checking PHP version...
php -v | findstr /C:"PHP 7" >nul 2>&1
if %errorlevel% equ 0 (
    echo PHP 7.x detected - OK
) else (
    php -v | findstr /C:"PHP 8" >nul 2>&1
    if %errorlevel% equ 0 (
        echo PHP 8.x detected - OK
    ) else (
        echo WARNING: PHP version might not be compatible
    )
)

REM Check if sockets extension is enabled
echo.
echo [4/4] Checking PHP sockets extension...
php -m | findstr /C:"sockets" >nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo WARNING: PHP sockets extension is NOT enabled!
    echo.
    echo To enable it:
    echo 1. Open D:\xampp\php\php.ini
    echo 2. Find the line: ;extension=sockets
    echo 3. Remove the semicolon to make it: extension=sockets
    echo 4. Restart Apache
    echo.
    pause
    exit /b 1
)
echo Sockets extension is enabled!

echo.
echo ================================================
echo   Setup Complete!
echo ================================================
echo.
echo To start the WebSocket server, run:
echo   php realtime\server.php
echo.
echo The server will run on: ws://localhost:8080
echo.
echo NOTE: Keep the server running in a separate terminal
echo       while your website is active.
echo.
pause
