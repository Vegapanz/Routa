// Dashboard Initialization
console.log('Admin.js loaded successfully');

// Utility function to show alert modal
function showAlert(message, title = 'Notice', type = 'info') {
    const modal = new bootstrap.Modal(document.getElementById('alertModal'));
    document.getElementById('alertModalTitle').textContent = title;
    document.getElementById('alertModalBody').innerHTML = message;
    
    // Change button color based on type
    const okBtn = document.querySelector('#alertModal .btn-primary');
    okBtn.className = 'btn';
    if (type === 'success') {
        okBtn.classList.add('btn-success');
    } else if (type === 'error' || type === 'danger') {
        okBtn.classList.add('btn-danger');
    } else if (type === 'warning') {
        okBtn.classList.add('btn-warning');
    } else {
        okBtn.classList.add('btn-primary');
    }
    
    modal.show();
}

// Utility function to show confirm modal
function showConfirm(message, title = 'Confirm Action', onConfirm) {
    return new Promise((resolve, reject) => {
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        document.getElementById('confirmModalTitle').textContent = title;
        document.getElementById('confirmModalBody').innerHTML = message;
        
        const confirmBtn = document.getElementById('confirmModalBtn');
        
        // Remove old event listeners
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        // Add new event listener
        newConfirmBtn.addEventListener('click', function() {
            modal.hide();
            resolve(true);
            if (onConfirm) onConfirm();
        });
        
        // Handle modal dismiss
        document.getElementById('confirmModal').addEventListener('hidden.bs.modal', function() {
            resolve(false);
        }, { once: true });
        
        modal.show();
    });
}

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
                showAlert('Please select a driver first', 'Warning', 'warning');
                return;
            }

            assignBooking(bookingId, driverId);
        });
    });

    // Handle booking rejection
    document.querySelectorAll('.reject-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const bookingId = this.dataset.bookingId;
            const confirmed = await showConfirm(
                'Are you sure you want to reject this booking?',
                'Reject Booking'
            );
            if (confirmed) {
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

async function rejectBooking(bookingId) {
    console.log('rejectBooking called with ID:', bookingId);
    
    const confirmed = await showConfirm(
        'Are you sure you want to reject this booking?',
        'Reject Booking'
    );
    
    if (!confirmed) return;

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
            showAlert('Booking rejected successfully', 'Success', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'Error', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while rejecting the booking: ' + error.message, 'Error', 'error');
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
            showAlert('Please select a driver', 'Warning', 'warning');
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
            showAlert('Booking assigned successfully!', 'Success', 'success');
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('assignDriverModal'));
            if (modal) {
                modal.hide();
            }
            // Reload page to show updated data
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'Error', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while assigning the booking: ' + error.message, 'Error', 'error');
    });
}

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

// View Driver Details
function viewDriverDetails(driverId) {
    const modal = new bootstrap.Modal(document.getElementById('viewDriverModal'));
    const contentDiv = document.getElementById('driverDetailsContent');
    
    // Show loading
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Fetch driver details
    fetch('admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=get_driver_details&driver_id=${driverId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const driver = data.data;
            contentDiv.innerHTML = `
                <div class="row g-4">
                    <div class="col-12">
                        <div class="text-center mb-4">
                            <div class="avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                                ${driver.name.substring(0, 2).toUpperCase()}
                            </div>
                            <h4 class="mb-1">${driver.name}</h4>
                            <p class="text-muted">DRV-${String(driver.id).padStart(3, '0')}</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3"><i class="bi bi-person-circle me-2"></i>Personal Information</h6>
                        <div class="mb-2">
                            <strong>Email:</strong><br>
                            <span class="text-muted">${driver.email || 'N/A'}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Phone:</strong><br>
                            <span class="text-muted">${driver.phone || 'N/A'}</span>
                        </div>
                        <div class="mb-2">
                            <strong>License Number:</strong><br>
                            <span class="text-muted">${driver.license_number || 'N/A'}</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3"><i class="bi bi-car-front me-2"></i>Vehicle Information</h6>
                        <div class="mb-2">
                            <strong>Tricycle Number:</strong><br>
                            <span class="text-muted">${driver.tricycle_number || 'N/A'}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Status:</strong><br>
                            <span class="badge ${driver.status === 'available' ? 'bg-success' : 'bg-secondary'}">
                                ${driver.status === 'available' ? 'Active' : 'Offline'}
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <h6 class="fw-bold mb-3"><i class="bi bi-graph-up me-2"></i>Statistics</h6>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="p-3 bg-light rounded">
                                    <h4 class="mb-0 text-primary">${driver.total_trips || 0}</h4>
                                    <small class="text-muted">Total Trips</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 bg-light rounded">
                                    <h4 class="mb-0 text-warning">
                                        <i class="bi bi-star-fill"></i> ${driver.rating || '0.0'}
                                    </h4>
                                    <small class="text-muted">Rating</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 bg-light rounded">
                                    <h4 class="mb-0 text-success">
                                        ${new Date(driver.created_at).toLocaleDateString()}
                                    </h4>
                                    <small class="text-muted">Joined</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else {
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    ${data.message || 'Failed to load driver details'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        contentDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                An error occurred while loading driver details
            </div>
        `;
    });
}

// View Application Details
function viewApplicationDetails(applicationId) {
    const modal = new bootstrap.Modal(document.getElementById('viewApplicationModal'));
    const contentDiv = document.getElementById('applicationDetailsContent');
    
    // Show loading
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Store application ID for approve/reject buttons
    document.getElementById('approveApplicationBtn').dataset.applicationId = applicationId;
    document.getElementById('rejectApplicationBtn').dataset.applicationId = applicationId;
    
    // Fetch application details
    fetch('admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=get_application_details&application_id=${applicationId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const app = data.data;
            contentDiv.innerHTML = `
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3"><i class="bi bi-person-circle me-2"></i>Personal Information</h6>
                        <div class="mb-2"><strong>Name:</strong> ${app.first_name} ${app.middle_name || ''} ${app.last_name}</div>
                        <div class="mb-2"><strong>Date of Birth:</strong> ${app.date_of_birth}</div>
                        <div class="mb-2"><strong>Email:</strong> ${app.email}</div>
                        <div class="mb-2"><strong>Phone:</strong> ${app.phone}</div>
                        <div class="mb-2"><strong>Address:</strong> ${app.address}, ${app.barangay}, ${app.city} ${app.zip_code}</div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3"><i class="bi bi-file-text me-2"></i>Driver Information</h6>
                        <div class="mb-2"><strong>License #:</strong> ${app.license_number}</div>
                        <div class="mb-2"><strong>License Expiry:</strong> ${app.license_expiry}</div>
                        <div class="mb-2"><strong>Driving Experience:</strong> ${app.driving_experience} years</div>
                        <div class="mb-2"><strong>Emergency Contact:</strong> ${app.emergency_name} (${app.emergency_phone})</div>
                        <div class="mb-2"><strong>Relationship:</strong> ${app.relationship}</div>
                    </div>
                    
                    <div class="col-12">
                        <h6 class="fw-bold mb-3"><i class="bi bi-car-front me-2"></i>Vehicle Information</h6>
                        <div class="row">
                            <div class="col-md-3 mb-2"><strong>Type:</strong> ${app.vehicle_type}</div>
                            <div class="col-md-3 mb-2"><strong>Make:</strong> ${app.vehicle_make}</div>
                            <div class="col-md-3 mb-2"><strong>Model:</strong> ${app.vehicle_model}</div>
                            <div class="col-md-3 mb-2"><strong>Year:</strong> ${app.vehicle_year}</div>
                            <div class="col-md-4 mb-2"><strong>Plate #:</strong> ${app.plate_number}</div>
                            <div class="col-md-4 mb-2"><strong>Franchise #:</strong> ${app.franchise_number || 'N/A'}</div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <h6 class="fw-bold mb-3"><i class="bi bi-file-earmark me-2"></i>Documents</h6>
                        <div class="row g-2">
                            ${generateDocumentButtons(app)}
                        </div>
                    </div>
                </div>
            `;
            
            // Enable/disable buttons based on status
            const approveBtn = document.getElementById('approveApplicationBtn');
            const rejectBtn = document.getElementById('rejectApplicationBtn');
            
            if (app.status !== 'pending') {
                approveBtn.disabled = true;
                rejectBtn.disabled = true;
            } else {
                approveBtn.disabled = false;
                rejectBtn.disabled = false;
            }
        } else {
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    ${data.message || 'Failed to load application details'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        contentDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                An error occurred while loading application details
            </div>
        `;
    });
}

function generateDocumentButtons(app) {
    const documents = [
        { name: "Driver's License", file: app.license_document, icon: 'bi-card-heading' },
        { name: "Government ID", file: app.government_id_document, icon: 'bi-person-badge' },
        { name: "Vehicle Registration", file: app.registration_document, icon: 'bi-file-text' },
        { name: "Franchise Permit", file: app.franchise_document, icon: 'bi-file-earmark-check' },
        { name: "Insurance", file: app.insurance_document, icon: 'bi-shield-check' },
        { name: "Barangay Clearance", file: app.clearance_document, icon: 'bi-file-earmark-text' },
        { name: "ID Photo", file: app.photo_document, icon: 'bi-image' }
    ];
    
    return documents.map(doc => {
        if (doc.file) {
            return `
                <div class="col-md-4">
                    <a href="${doc.file}" target="_blank" class="btn btn-outline-primary btn-sm w-100">
                        <i class="${doc.icon} me-2"></i>${doc.name}
                    </a>
                </div>
            `;
        }
        return '';
    }).join('');
}

// Approve Application
document.getElementById('approveApplicationBtn')?.addEventListener('click', async function() {
    const applicationId = this.dataset.applicationId;
    
    console.log('Approve button clicked, applicationId:', applicationId);
    
    if (!applicationId || applicationId === 'undefined') {
        showAlert('No application ID found. Please close and reopen the application details.', 'Error', 'error');
        return;
    }
    
    const confirmed = await showConfirm(
        'Are you sure you want to approve this application? The driver will be added to the system.',
        'Approve Application'
    );
    
    if (!confirmed) return;
    
    fetch('admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=approve_application&application_id=${applicationId}`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Approve response:', data);
        if (data.success) {
            showAlert(data.message, 'Success', 'success');
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('viewApplicationModal'));
                if (modal) modal.hide();
                location.reload();
            }, 1500);
        } else {
            showAlert(data.message, 'Error', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while approving the application', 'Error', 'error');
    });
});

// Reject Application
document.getElementById('rejectApplicationBtn')?.addEventListener('click', async function() {
    const applicationId = this.dataset.applicationId;
    
    console.log('Reject button clicked, applicationId:', applicationId);
    
    if (!applicationId || applicationId === 'undefined') {
        showAlert('No application ID found. Please close and reopen the application details.', 'Error', 'error');
        return;
    }
    
    const confirmed = await showConfirm(
        'Are you sure you want to reject this application? This action cannot be undone.',
        'Reject Application'
    );
    
    if (!confirmed) return;
    
    fetch('admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=reject_application&application_id=${applicationId}`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Reject response:', data);
        if (data.success) {
            showAlert(data.message, 'Success', 'success');
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('viewApplicationModal'));
                if (modal) modal.hide();
                location.reload();
            }, 1500);
        } else {
            showAlert(data.message, 'Error', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while rejecting the application', 'Error', 'error');
    });
});

// Export Functionality
function exportData(type) {
    // Add export logic here
}