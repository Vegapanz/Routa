# 🎯 Quick Reference Card - Input Formats

## Personal Info
```
First/Last Name    → Letters only (Juan Dela Cruz)
Phone             → 09123456789 or +639123456789
Email             → juan@email.com (auto-lowercase)
Date of Birth     → Date picker (must be 18+)
Zip Code          → 1000 (4 digits only)
```

## Driver Info
```
License Number    → N01-12-345678 (auto-formatted)
License Expiry    → Date picker (must not be expired)
Experience        → Dropdown selection
```

## Vehicle Info
```
Plate Number      → ABC1234 (auto-uppercase)
Franchise Number  → FR-2024-12345 (optional, auto-formatted)
Make              → Honda, Yamaha (letters only)
Model             → TMX 155 (alphanumeric)
Year              → 2000-2025 (number range)
```

## Documents
```
File Types        → JPG, PNG, PDF only
Max Size          → 5MB per file
Required Docs     → 7 documents total
Checkboxes        → Both must be checked
```

## Test Pages
```
1. Demo:          http://localhost/Routa/demo_validation.html
2. Setup DB:      http://localhost/Routa/php/setup_and_test_driver_table.php
3. System Test:   http://localhost/Routa/test_driver_system.html
4. Application:   http://localhost/Routa/driver-application.php
```

## Common Errors Fixed
- ✅ Date of birth cannot be null → Use HTML5 date input
- ✅ Phone format → Auto-formats to Philippine format
- ✅ License format → Auto-adds hyphens
- ✅ Name validation → Auto-removes invalid characters
- ✅ File uploads → Size and type validation

## Status: ✅ ALL RESTRICTIONS IMPLEMENTED
