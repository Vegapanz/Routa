# Xdebug Setup Guide for Routa Project

## Current Setup
- **PHP Version**: 8.2.12 (XAMPP)
- **PHP Config**: `D:\xampp\php\php.ini`
- **Xdebug Port**: 9003 (default for Xdebug 3.x)

## Installation Steps

### 1. Download Xdebug
1. Go to https://xdebug.org/download
2. Download **PHP 8.2 VC15 TS (64 bit)** version
3. Or use direct link: https://xdebug.org/files/php_xdebug-3.3.1-8.2-vs16-x86_64.dll

### 2. Install Xdebug DLL
1. Save the downloaded file as `php_xdebug.dll`
2. Copy it to: `D:\xampp\php\ext\`

### 3. Configure php.ini
1. Open `D:\xampp\php\php.ini` in a text editor (as Administrator)
2. Scroll to the bottom of the file
3. Add the following configuration:

```ini
[XDebug]
zend_extension = "D:\xampp\php\ext\php_xdebug.dll"
xdebug.mode = debug
xdebug.start_with_request = yes
xdebug.client_port = 9003
xdebug.client_host = 127.0.0.1
xdebug.idekey = VSCODE
xdebug.log = "D:\xampp\tmp\xdebug.log"
xdebug.log_level = 7
```

### 4. Restart Apache
1. Open XAMPP Control Panel
2. Stop Apache
3. Start Apache
4. Check if Xdebug is loaded: Open http://localhost/Routa/phpinfo.php

### 5. Install VS Code Extension
1. Open VS Code
2. Go to Extensions (Ctrl+Shift+X)
3. Search for "PHP Debug" by Xdebug
4. Install the extension by Felix Becker

### 6. Verify Installation
Run in PowerShell:
```powershell
php -v
```
You should see "with Xdebug v3.x.x" in the output.

Or run:
```powershell
php -m | Select-String xdebug
```

## VS Code Configuration

The `.vscode/launch.json` file has been created with these debug configurations:

### 1. Listen for Xdebug
- Listens on port 9003
- Use this for debugging web requests from browser
- Start debugging (F5), then visit your site in browser

### 2. Launch currently open script
- Debugs the PHP file currently open in VS Code
- Good for testing individual PHP files

### 3. Debug Admin Dashboard
- Pre-configured to debug admin.php
- Launches with Xdebug automatically

### 4. Debug User Dashboard
- Pre-configured to debug userdashboard.php
- Launches with Xdebug automatically

## How to Debug

### Method 1: Browser Debugging (Recommended for Web Apps)
1. In VS Code, press **F5** or click "Run and Debug"
2. Select "Listen for Xdebug"
3. Set breakpoints in your PHP files (click left of line numbers)
4. Open your browser and navigate to: http://localhost/Routa/admin.php
5. Execution will pause at your breakpoints

### Method 2: CLI Debugging
1. Set breakpoints in your PHP file
2. Press **F5**
3. Select "Launch currently open script"
4. The script will run and pause at breakpoints

## Setting Breakpoints

1. Open any PHP file (e.g., admin.php)
2. Click to the left of a line number - a red dot will appear
3. Start debugging (F5)
4. When code reaches that line, execution pauses
5. You can:
   - Inspect variables (hover over them)
   - Check the Debug Console
   - Step through code line by line (F10)
   - Step into functions (F11)
   - Continue execution (F5)

## Debugging Tips

### Common Breakpoint Locations:
- **admin.php**: Line 90+ (after session check)
- **userdashboard.php**: Line 40+ (after user data fetch)
- **php/booking_api.php**: Line 30+ (in API handlers)
- **php/admin_functions.php**: Inside functions

### Debug Actions:
- **F5**: Continue/Start debugging
- **F10**: Step Over (next line)
- **F11**: Step Into (enter function)
- **Shift+F11**: Step Out (exit function)
- **Ctrl+Shift+F5**: Restart debugging
- **Shift+F5**: Stop debugging

### Debug Panels:
- **Variables**: Shows all variables in current scope
- **Watch**: Add expressions to monitor
- **Call Stack**: Shows function call hierarchy
- **Breakpoints**: Manage all breakpoints
- **Debug Console**: Execute PHP code during debugging

## Troubleshooting

### Xdebug not working?
1. Check if extension is loaded:
   ```powershell
   php -m | Select-String xdebug
   ```

2. Check Apache error log:
   ```powershell
   Get-Content D:\xampp\apache\logs\error.log -Tail 20
   ```

3. Check Xdebug log:
   ```powershell
   Get-Content D:\xampp\tmp\xdebug.log -Tail 20
   ```

### Breakpoints not hitting?
1. Make sure "Listen for Xdebug" is running (F5)
2. Check that port 9003 is not blocked by firewall
3. Verify `xdebug.start_with_request = yes` in php.ini
4. Clear browser cache
5. Add `?XDEBUG_SESSION_START=VSCODE` to URL

### Performance slow with Xdebug?
1. Disable when not debugging:
   - Comment out the `zend_extension` line in php.ini
   - Restart Apache

2. Or use:
   ```ini
   xdebug.start_with_request = trigger
   ```
   Then add `?XDEBUG_SESSION_START=1` to URL only when debugging

## Quick Start Commands

### Check if Xdebug is installed:
```powershell
php -v
php -m | Select-String xdebug
```

### Create phpinfo.php for detailed info:
```powershell
echo "<?php phpinfo(); ?>" > phpinfo.php
```
Then visit: http://localhost/Routa/phpinfo.php

### Restart Apache:
```powershell
cd D:\xampp
.\apache_stop.bat
.\apache_start.bat
```

## Browser Extension (Optional)
Install "Xdebug Helper" browser extension:
- Chrome: https://chrome.google.com/webstore - search "Xdebug Helper"
- Firefox: https://addons.mozilla.org - search "Xdebug Helper"

This lets you toggle debugging on/off with a button instead of URL parameters.

## Summary
1. ✅ Download Xdebug DLL for PHP 8.2
2. ✅ Copy to `D:\xampp\php\ext\`
3. ✅ Add config to `php.ini`
4. ✅ Restart Apache
5. ✅ Install VS Code PHP Debug extension
6. ✅ Press F5 in VS Code
7. ✅ Set breakpoints
8. ✅ Debug!

Need help? Check the Xdebug log at: `D:\xampp\tmp\xdebug.log`
