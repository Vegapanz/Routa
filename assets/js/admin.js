// Dashboard Initialization
console.log('Admin.js loaded successfully');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    console.log('Available drivers:', window.availableDrivers);
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Handle driver selection
    document.querySelectorAll('.driver-select').forEach(select => {
        select.addEventListener('change', function() {
            const bookingId = this.dataset.bookingId;
            const assignBtn = document.querySelector(`.assign-btn[data-booking-id="${bookingId}"]`);
            assignBtn.disabled = !this.value;
        });
    });

    // Handle booking assignment
    document.querySelectorAll('.assign-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const bookingId = this.dataset.bookingId;
            const select = document.querySelector(`.driver-select[data-booking-id="${bookingId}"]`);
            const driverId = select.value;

            if (!driverId) {
                alert('Please select a driver first');
                return;
            }

            assignBooking(bookingId, driverId);
        });
    });

    // Handle booking rejection
    document.querySelectorAll('.reject-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const bookingId = this.dataset.bookingId;
            if (confirm('Are you sure you want to reject this booking?')) {
                rejectBooking(bookingId);
            }
        });
    });

    // Charts initialization removed (no canvas elements in current design)
    
    // Load Pending Bookings
    // loadPendingBookings(); // Commented out - bookings loaded from PHP
});

// Load Pending Bookings Data
function loadPendingBookings() {
    // Sample data - replace with actual API call
    const bookings = [
        {
            id: 'BK-004',
            rider: 'Anna Bautista',
            phone: '+63 945 678 9012',
            from: 'LRT Carriedo',
            to: 'Binondo',
            fare: 70,
            time: '11:15 AM'
        },
        {
            id: 'BK-005',
            rider: 'Miguel Torres',
            phone: '+63 956 789 0123',
            from: 'Manila City Hall',
            to: 'Intramuros',
            fare: 65,
            time: '11:30 AM'
        }
    ];

    const tbody = document.getElementById('pendingBookingsTable');
    tbody.innerHTML = bookings.map(booking => `
        <tr>
            <td>${booking.id}</td>
            <td>${booking.rider}</td>
            <td>${booking.from}</td>
            <td>${booking.to}</td>
            <td>₱${booking.fare}</td>
            <td>${booking.time}</td>
            <td>
                <button class="btn btn-sm btn-success" onclick="confirmBooking('${booking.id}')">
                    Confirm & Assign Driver
                </button>
                <button class="btn btn-sm btn-danger" onclick="rejectBooking('${booking.id}')">
                    Reject
                </button>
            </td>
        </tr>
    `).join('');
}

// Booking Actions
function confirmBooking(bookingId) {
    console.log('confirmBooking called with ID:', bookingId);
    // Show modal to select driver
    showDriverAssignmentModal(bookingId);
}

function rejectBooking(bookingId) {
    console.log('rejectBooking called with ID:', bookingId);
    
    if (!confirm('Are you sure you want to reject this booking?')) {
        return;
    }

    console.log('Sending reject request...');
    
    fetch('admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=reject_booking&booking_id=${bookingId}`
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert('Booking rejected successfully');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while rejecting the booking: ' + error.message);
    });
}

function showDriverAssignmentModal(bookingId) {
    console.log('showDriverAssignmentModal called with ID:', bookingId);
    
    // Create and show assignment modal
    const modalHTML = `
        <div class="modal fade" id="assignDriverModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">Assign Driver to Booking</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-4">Select a driver to confirm and assign to this booking</p>
                        <form id="assignDriverForm">
                            <input type="hidden" id="assignBookingId" value="${bookingId}">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Select Driver <span class="text-danger">*</span></label>
                                <select class="form-select" id="driverSelect" required>
                                    <option value="">Choose a driver</option>
                                </select>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success flex-fill">Confirm Assignment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById('assignDriverModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    console.log('Modal HTML added to page');

    // Load available drivers
    loadAvailableDrivers();

    // Show modal
    const modalElement = document.getElementById('assignDriverModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    console.log('Modal shown');

    // Handle form submission
    document.getElementById('assignDriverForm').addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted');
        const bookingId = document.getElementById('assignBookingId').value;
        const driverId = document.getElementById('driverSelect').value;

        console.log('Booking ID:', bookingId, 'Driver ID:', driverId);

        if (!driverId) {
            alert('Please select a driver');
            return;
        }

        assignBookingToDriver(bookingId, driverId);
    });
}

function loadAvailableDrivers() {
    console.log('loadAvailableDrivers called');
    console.log('Available drivers from window:', window.availableDrivers);
    
    // Use the drivers data from the page
    const driverData = window.availableDrivers || [];
    const select = document.getElementById('driverSelect');
    
    if (!select) {
        console.error('Driver select element not found!');
        return;
    }
    
    select.innerHTML = '<option value="">Choose a driver</option>';
    
    if (driverData.length === 0) {
        console.warn('No available drivers found');
        select.innerHTML += '<option value="" disabled>No drivers available</option>';
        return;
    }
    
    console.log('Loading', driverData.length, 'drivers');
    
    driverData.forEach(driver => {
        const option = document.createElement('option');
        option.value = driver.id;
        option.textContent = `${driver.name}`;
        select.appendChild(option);
        console.log('Added driver:', driver.name);
    });
}

function assignBookingToDriver(bookingId, driverId) {
    console.log('assignBookingToDriver called - Booking:', bookingId, 'Driver:', driverId);
    
    fetch('admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=assign_booking&booking_id=${bookingId}&driver_id=${driverId}`
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert('Booking assigned successfully!');
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('assignDriverModal'));
            if (modal) {
                modal.hide();
            }
            // Reload page to show updated data
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while assigning the booking: ' + error.message);
    });
}

// Driver Management
document.getElementById('addDriverForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    // Add driver creation logic here
    alert('Driver added successfully!');
    bootstrap.Modal.getInstance(document.getElementById('addDriverModal')).hide();
});

// Sidebar Navigation
document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});

// Search Functionality
function searchBookings(query) {
    // Add search logic here
}

// Export Functionality
function exportData(type) {
    // Add export logic here
}