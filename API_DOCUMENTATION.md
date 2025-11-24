# API Documentation

Backend API endpoints for Routa Tricycle Booking System.

## Base URL

```
http://localhost/routa/php/
```

## Authentication

Most endpoints require authentication via PHP sessions. Include session cookies in requests.

## Response Format

All endpoints return JSON responses:

```json
{
    "success": true|false,
    "message": "Response message",
    "data": {...}  // Optional data object
}
```

---

## User Endpoints

### Register User

**Endpoint:** `POST /register.php`

**Request Body:**
```json
{
    "full_name": "John Doe",
    "email": "john@example.com",
    "phone": "09123456789",
    "password": "password123",
    "confirm_password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Registration successful!"
}
```

### Login

**Endpoint:** `POST /login.php`

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "redirect": "userdashboard.php"
}
```

### Logout

**Endpoint:** `POST /logout.php`

**Response:**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

---

## Booking Endpoints

### Create Booking

**Endpoint:** `POST /create_booking.php`

**Authentication:** Required

**Request Body:**
```json
{
    "pickup_location": "123 Main St",
    "dropoff_location": "456 Oak Ave",
    "pickup_lat": 14.5995,
    "pickup_lng": 120.9842,
    "dropoff_lat": 14.6091,
    "dropoff_lng": 121.0223,
    "estimated_fare": 150.00
}
```

**Response:**
```json
{
    "success": true,
    "message": "Booking created successfully",
    "booking_id": 123
}
```

### Get User Bookings

**Endpoint:** `GET /get_bookings.php`

**Authentication:** Required

**Query Parameters:**
- `status` (optional): Filter by status (pending, accepted, completed, cancelled)

**Response:**
```json
{
    "success": true,
    "bookings": [
        {
            "id": 1,
            "pickup_location": "123 Main St",
            "dropoff_location": "456 Oak Ave",
            "status": "completed",
            "fare": 150.00,
            "driver_name": "Juan Cruz",
            "created_at": "2024-01-15 14:30:00"
        }
    ]
}
```

### Cancel Booking

**Endpoint:** `POST /cancel_booking.php`

**Authentication:** Required

**Request Body:**
```json
{
    "booking_id": 123
}
```

**Response:**
```json
{
    "success": true,
    "message": "Booking cancelled successfully"
}
```

---

## Driver Endpoints

### Apply as Driver

**Endpoint:** `POST /driver_application.php`

**Request Body:**
```json
{
    "full_name": "Juan Cruz",
    "email": "juan@example.com",
    "phone": "09123456789",
    "license_number": "N01-12-345678",
    "tricycle_number": "ABC-1234",
    "password": "password123"
}
```

**Files:**
- `license_image` - Driver's license photo
- `tricycle_image` - Tricycle photo

**Response:**
```json
{
    "success": true,
    "message": "Application submitted successfully"
}
```

### Accept Ride

**Endpoint:** `POST /accept_ride.php`

**Authentication:** Required (Driver)

**Request Body:**
```json
{
    "booking_id": 123
}
```

**Response:**
```json
{
    "success": true,
    "message": "Ride accepted"
}
```

### Complete Ride

**Endpoint:** `POST /complete_ride.php`

**Authentication:** Required (Driver)

**Request Body:**
```json
{
    "booking_id": 123,
    "final_fare": 150.00
}
```

**Response:**
```json
{
    "success": true,
    "message": "Ride completed"
}
```

### Get Available Rides

**Endpoint:** `GET /get_available_rides.php`

**Authentication:** Required (Driver)

**Response:**
```json
{
    "success": true,
    "rides": [
        {
            "id": 123,
            "pickup_location": "123 Main St",
            "dropoff_location": "456 Oak Ave",
            "estimated_fare": 150.00,
            "passenger_name": "John Doe",
            "created_at": "2024-01-15 14:30:00"
        }
    ]
}
```

---

## Rating Endpoints

### Submit Rating

**Endpoint:** `POST /submit_rating.php`

**Authentication:** Required

**Request Body:**
```json
{
    "booking_id": 123,
    "rating": 5,
    "comment": "Great driver!"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Rating submitted successfully"
}
```

### Get Driver Rating

**Endpoint:** `GET /get_driver_rating.php?driver_id=1`

**Response:**
```json
{
    "success": true,
    "driver_id": 1,
    "average_rating": 4.8,
    "total_ratings": 25
}
```

---

## Admin Endpoints

### Get All Bookings

**Endpoint:** `GET /admin/get_all_bookings.php`

**Authentication:** Required (Admin)

**Response:**
```json
{
    "success": true,
    "bookings": [...]
}
```

### Get Statistics

**Endpoint:** `GET /admin/get_statistics.php`

**Authentication:** Required (Admin)

**Response:**
```json
{
    "success": true,
    "stats": {
        "total_users": 150,
        "total_drivers": 25,
        "total_bookings": 500,
        "pending_applications": 5,
        "active_rides": 3
    }
}
```

### Approve Driver

**Endpoint:** `POST /admin/approve_driver.php`

**Authentication:** Required (Admin)

**Request Body:**
```json
{
    "application_id": 123
}
```

**Response:**
```json
{
    "success": true,
    "message": "Driver approved successfully"
}
```

### Delete User

**Endpoint:** `POST /admin/delete_user.php`

**Authentication:** Required (Admin)

**Request Body:**
```json
{
    "user_id": 123
}
```

**Response:**
```json
{
    "success": true,
    "message": "User deleted successfully"
}
```

---

## Error Responses

### 400 Bad Request
```json
{
    "success": false,
    "message": "Invalid input data"
}
```

### 401 Unauthorized
```json
{
    "success": false,
    "message": "Authentication required"
}
```

### 403 Forbidden
```json
{
    "success": false,
    "message": "Access denied"
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "Resource not found"
}
```

### 500 Internal Server Error
```json
{
    "success": false,
    "message": "Server error occurred"
}
```

---

## Rate Limiting

Currently no rate limiting implemented. Consider implementing for production.

## CORS

CORS headers should be configured for API access from different domains.

## Security

- All endpoints use PDO prepared statements
- Passwords are hashed with `password_hash()`
- Session-based authentication
- Input validation and sanitization
- File upload validation

## Testing

Use Postman, Insomnia, or cURL to test endpoints:

```bash
# Login example
curl -X POST http://localhost/routa/php/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'
```

## Webhooks (Future)

Webhook support for real-time notifications may be added in future versions.
