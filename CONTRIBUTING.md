# Contributing to Routa

Thank you for your interest in contributing to Routa Tricycle Booking System! 

## Code of Conduct

Be respectful, inclusive, and constructive in all interactions.

## How to Contribute

### Reporting Bugs

1. Check if the bug has already been reported in Issues
2. Use the bug report template
3. Include:
   - Clear description
   - Steps to reproduce
   - Expected vs actual behavior
   - Screenshots (if applicable)
   - Environment details (PHP version, browser, etc.)

### Suggesting Features

1. Check if the feature has been suggested
2. Open a new issue with `[Feature Request]` prefix
3. Describe:
   - The problem it solves
   - Proposed solution
   - Alternative solutions considered

### Pull Requests

1. **Fork the repository**
2. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```
3. **Make your changes**
4. **Follow coding standards** (see below)
5. **Test thoroughly**
6. **Commit with clear messages**
   ```bash
   git commit -m "Add feature: description"
   ```
7. **Push to your fork**
   ```bash
   git push origin feature/your-feature-name
   ```
8. **Create Pull Request**

## Coding Standards

### PHP

- Use PSR-12 coding style
- Document functions with PHPDoc
- Use type hints where possible
- Validate all inputs
- Use prepared statements for database queries

```php
/**
 * Get user by ID
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @return array|null User data or null
 */
function getUserById(PDO $pdo, int $userId): ?array {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}
```

### JavaScript

- Use ES6+ syntax
- Use `const` and `let` (not `var`)
- Document complex functions
- Use meaningful variable names
- Handle errors properly

```javascript
/**
 * Fetch bookings from server
 * @returns {Promise<Array>} Array of bookings
 */
async function fetchBookings() {
    try {
        const response = await fetch('php/get_bookings.php');
        const data = await response.json();
        return data.bookings || [];
    } catch (error) {
        console.error('Error fetching bookings:', error);
        return [];
    }
}
```

### CSS

- Use meaningful class names
- Follow BEM methodology where appropriate
- Keep selectors simple
- Group related properties
- Use comments for sections

```css
/* User Dashboard Cards */
.dashboard-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.dashboard-card__title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 10px;
}
```

### SQL

- Use descriptive table/column names
- Add comments for complex queries
- Include indexes for frequently queried columns
- Use proper data types
- Include foreign key constraints

```sql
-- Create bookings table with proper relationships
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    driver_id INT,
    status ENUM('pending', 'accepted', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL,
    
    INDEX idx_status (status),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Testing

- Test all features manually
- Verify responsive design on multiple devices
- Check browser compatibility (Chrome, Firefox, Safari, Edge)
- Test with different user roles (user, driver, admin)
- Verify database queries work correctly

## Documentation

- Update README.md if needed
- Update API documentation for new endpoints
- Add inline comments for complex logic
- Update CHANGELOG.md

## Git Commit Messages

Use clear, descriptive commit messages:

- `feat: Add booking cancellation feature`
- `fix: Resolve session timeout issue`
- `docs: Update installation guide`
- `style: Format admin dashboard CSS`
- `refactor: Simplify database connection code`
- `test: Add booking validation tests`
- `chore: Update dependencies`

## Review Process

1. Code will be reviewed by maintainers
2. Address any feedback/changes requested
3. Once approved, PR will be merged
4. Your contribution will be credited

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

## Questions?

Open an issue or contact the maintainers.

Thank you for contributing! 🎉
