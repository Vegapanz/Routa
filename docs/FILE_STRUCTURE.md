# Routa Project - File Structure Documentation

## Overview
This document describes the organized file structure of the Routa project. Each page now has its own dedicated CSS and JavaScript files for better maintainability and modularity.

## Directory Structure

```
Routa/
├── assets/
│   ├── css/
│   │   ├── pages/                    # Page-specific CSS files
│   │   │   ├── home.css             # Homepage styles
│   │   │   ├── login.css            # Login page styles
│   │   │   └── register.css         # Registration page styles
│   │   ├── auth.css                 # Shared authentication styles
│   │   ├── auth2.css                # Additional auth styles
│   │   └── style.css                # Global styles
│   ├── js/
│   │   ├── pages/                    # Page-specific JavaScript files
│   │   │   ├── home.js              # Homepage functionality
│   │   │   ├── login.js             # Login page functionality
│   │   │   └── register.js          # Registration page functionality
│   │   └── main.js                   # Global JavaScript
│   └── images/                       # Image assets
├── php/                              # PHP backend files
├── index.php                         # Homepage
├── login.php                         # Login page
├── register.php                      # Registration page
└── database.sql                      # Database schema
```

## File Linking Structure

### Homepage (index.php)
**CSS Files:**
- `assets/css/style.css` - Global styles
- `assets/css/pages/home.css` - Homepage-specific styles

**JavaScript Files:**
- `assets/js/main.js` - Global JavaScript
- `assets/js/pages/home.js` - Homepage-specific functionality

### Login Page (login.php)
**CSS Files:**
- `assets/css/auth.css` - Shared authentication styles
- `assets/css/pages/login.css` - Login-specific styles

**JavaScript Files:**
- `assets/js/pages/login.js` - Login functionality

### Registration Page (register.php)
**CSS Files:**
- `assets/css/auth2.css` - Shared authentication styles
- `assets/css/pages/register.css` - Registration-specific styles

**JavaScript Files:**
- `assets/js/pages/register.js` - Registration functionality

## Features by Page

### Home Page (home.js)
- Smooth scrolling for anchor links
- Navbar scroll effects
- Testimonial carousel
- Animation on scroll
- Back to top button
- Mobile menu toggle
- Contact form handling
- Newsletter subscription

### Login Page (login.js)
- Form validation
- Email validation
- Password visibility toggle
- Social login integration
- Forgot password functionality
- Remember me feature
- Loading states
- Error handling

### Registration Page (register.js)
- Multi-step form navigation
- Form validation per step
- Password strength checker
- Password visibility toggle
- Email validation
- Phone number validation
- Terms and conditions checkbox
- Social registration integration
- Progress indicator
- Loading states

## Benefits of This Structure

1. **Modularity**: Each page has its own CSS and JavaScript files
2. **Maintainability**: Easy to find and update page-specific code
3. **Performance**: Can load only the resources needed for each page
4. **Scalability**: Easy to add new pages with their own styles and scripts
5. **Organization**: Clear separation of concerns
6. **Debugging**: Easier to isolate and fix issues
7. **Collaboration**: Multiple developers can work on different pages without conflicts

## Best Practices

1. **Global Styles**: Keep common styles in `style.css`
2. **Page-Specific Styles**: Put unique styles in page-specific CSS files
3. **Reusable Components**: Create shared components in global files
4. **Code Comments**: Document complex functionality
5. **Naming Conventions**: Use clear, descriptive names for classes and functions
6. **File Organization**: Keep related files together in appropriate directories

## Future Enhancements

Consider adding:
- A `components/` directory for reusable UI components
- A `utils/` directory for utility functions
- A `config/` directory for configuration files
- Minified versions of CSS and JS files for production
- A build process for optimization

## Notes

- All external libraries (Bootstrap, jQuery, Font Awesome) are loaded via CDN
- Custom fonts are loaded from Google Fonts
- Images are stored in `assets/images/`
- PHP backend files are in the `php/` directory
- Database schema is in `database.sql`

---

**Last Updated**: 2025-11-04
**Version**: 1.0
