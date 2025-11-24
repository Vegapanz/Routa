// Dashboard Initialization
console.log('Admin.js loaded successfully');

// Real-time update intervals
let dashboardStatsInterval;
let pendingBookingsInterval;
let allBookingsInterval;
let driversInterval;
let usersInterval;
let applicationsInterval;

// Utility function to show alert modal
function showAlert(message, title = 'Notice', type = 'info') {
    const modalElement = document.getElementById('alertModal');
    const titleElement = document.getElementById('alertModalTitle');
    const bodyElement = document.getElementById('alertModalBody');
    
    if (!modalElement || !titleElement || !bodyElement) {
        console.error('Alert modal elements not found');
        // Fallback to SweetAlert2
        Swal.fire({
            title: title,
            html: message,
            icon: type === 'error' || type === 'danger' ? 'error' : type === 'success' ? 'success' : 'info',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    const modal = new bootstrap.Modal(modalElement);
    titleElement.textContent = title;
    bodyElement.innerHTML = message;
    
    // Change button color based on type
    const okBtn = modalElement.querySelector('.btn-primary');
    if (okBtn) {
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

    // Start real-time updates for dashboard stats
    startDashboardStatsUpdate();
    
    // Start real-time updates for pending bookings (default active tab)
    startPendingBookingsUpdate();
    
    // Handle tab changes
    const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function(event) {
            const targetId = event.target.getAttribute('data-bs-target');
            handleTabSwitch(targetId);
        });
    });
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
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        credentials: 'same-origin',
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
        credentials: 'same-origin', // Include cookies for session
        body: `action=assign_booking&booking_id=${bookingId}&driver_id=${driverId}`
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response content type:', response.headers.get('content-type'));
        
        // Clone the response to read it as text first
        return response.text().then(text => {
            console.log('Raw response:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response was:', text.substring(0, 500));
                throw new Error('Server returned invalid JSON. Check PHP error logs.');
            }
        });
    })
    .then(data => {
        console.log('Response data:', data);
        
        // Check if session expired
        // if (data.redirect) {
        //     showAlert(data.message || 'Session expired. Please login again.', 'Session Expired', 'warning');
        //     setTimeout(() => {
        //         window.location.href = data.redirect;
        //     }, 2000);
        //     return;
        // }
        
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
            showAlert(data.message || 'Failed to assign booking', 'Error', 'error');
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

// Delete Driver
async function deleteDriver(driverId) {
    const confirmed = await showConfirm(
        'Are you sure you want to delete this driver? This action cannot be undone.',
        'Delete Driver'
    );
    
    if (!confirmed) return;
    
    fetch('admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        credentials: 'same-origin',
        body: `action=delete_driver&driver_id=${driverId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'Success', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'Error', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while deleting the driver', 'Error', 'error');
    });
}

// Delete User
async function deleteUser(userId) {
    const confirmed = await showConfirm(
        'Are you sure you want to delete this user? This action cannot be undone.',
        'Delete User'
    );
    
    if (!confirmed) return;
    
    fetch('admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        credentials: 'same-origin',
        body: `action=delete_user&user_id=${userId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'Success', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'Error', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while deleting the user', 'Error', 'error');
    });
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
        credentials: 'same-origin',
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
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        credentials: 'same-origin',
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
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        credentials: 'same-origin',
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
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        credentials: 'same-origin',
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

// ========== REAL-TIME UPDATE FUNCTIONS ==========

// Handle tab switching - start/stop appropriate intervals
function handleTabSwitch(targetId) {
    // Stop all intervals first
    stopAllIntervals();
    
    // Start appropriate interval based on active tab
    switch(targetId) {
        case '#pending':
            startPendingBookingsUpdate();
            break;
        case '#all-bookings':
            startAllBookingsUpdate();
            break;
        case '#drivers':
            startDriversUpdate();
            break;
        case '#users':
            startUsersUpdate();
            break;
        case '#applications':
            startApplicationsUpdate();
            break;
        case '#analytics':
            // Analytics doesn't need frequent updates
            break;
    }
}

// Stop all update intervals
function stopAllIntervals() {
    if (pendingBookingsInterval) clearInterval(pendingBookingsInterval);
    if (allBookingsInterval) clearInterval(allBookingsInterval);
    if (driversInterval) clearInterval(driversInterval);
    if (usersInterval) clearInterval(usersInterval);
    if (applicationsInterval) clearInterval(applicationsInterval);
}

// Dashboard Stats Update (runs continuously)
function startDashboardStatsUpdate() {
    updateDashboardStats(); // Initial load
    dashboardStatsInterval = setInterval(updateDashboardStats, 5000); // Every 5 seconds
}

function updateDashboardStats() {
    fetch('admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'same-origin',
        body: 'action=get_dashboard_stats'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const stats = data.data;
            
            // Update stat cards
            const revenueEl = document.querySelector('.stat-card h3');
            const bookingsEl = document.querySelectorAll('.stat-card h3')[1];
            const driversEl = document.querySelectorAll('.stat-card h3')[2];
            const pendingEl = document.querySelectorAll('.stat-card h3')[3];
            
            if (revenueEl) revenueEl.textContent = '₱' + parseFloat(stats.total_revenue || 0).toLocaleString();
            if (bookingsEl) bookingsEl.textContent = stats.total_bookings || 0;
            if (driversEl) driversEl.textContent = stats.active_drivers || 0;
            if (pendingEl) pendingEl.textContent = stats.pending_bookings || 0;
        }
    })
    .catch(error => console.error('Error updating dashboard stats:', error));
}

// Pending Bookings Update
function startPendingBookingsUpdate() {
    updatePendingBookings(); // Initial load
    pendingBookingsInterval = setInterval(updatePendingBookings, 3000); // Every 3 seconds
}

function updatePendingBookings() {
    fetch('admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'same-origin',
        body: 'action=get_pending_bookings'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const tbody = document.querySelector('#pending tbody');
            if (!tbody) return;
            
            const bookings = data.data;
            
            if (bookings.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                            <p class="text-muted mt-2">No pending bookings</p>
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = bookings.map(booking => `
                <tr>
                    <td><strong>#${booking.id}</strong></td>
                    <td>
                        <div class="fw-semibold">${booking.rider_name || 'Unknown'}</div>
                        <small class="text-muted">${booking.phone || 'N/A'}</small>
                    </td>
                    <td>${booking.pickup_location || 'N/A'}</td>
                    <td>${booking.dropoff_location || 'N/A'}</td>
                    <td><strong>₱${parseFloat(booking.fare || 0).toFixed(2)}</strong></td>
                    <td><small>${formatDateTime(booking.created_at)}</small></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-success assign-btn-inline" data-booking-id="${booking.id}" onclick="confirmBooking(${booking.id})">
                                <i class="bi bi-check-circle"></i> Assign
                            </button>
                            <button class="btn btn-danger" onclick="rejectBooking(${booking.id})">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
    })
    .catch(error => console.error('Error updating pending bookings:', error));
}

// All Bookings Update
function startAllBookingsUpdate() {
    updateAllBookings(); // Initial load
    allBookingsInterval = setInterval(updateAllBookings, 5000); // Every 5 seconds
}

function updateAllBookings() {
    fetch('admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'same-origin',
        body: 'action=get_all_bookings'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const tbody = document.querySelector('#all-bookings tbody');
            if (!tbody) return;
            
            const bookings = data.data;
            
            if (bookings.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                            <p class="text-muted mt-2">No bookings found</p>
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = bookings.map(booking => `
                <tr>
                    <td class="fw-semibold">BK-${String(booking.id).padStart(3, '0')}</td>
                    <td><i class="bi bi-person"></i> ${booking.rider_name || 'Unknown'}</td>
                    <td>${booking.driver_name ? '<i class="bi bi-bicycle"></i> ' + booking.driver_name : '<span class="text-muted">Not assigned</span>'}</td>
                    <td class="small">
                        <div><i class="bi bi-geo-alt-fill text-success"></i> ${(booking.pickup_location || 'N/A').substring(0, 30)}...</div>
                        <div><i class="bi bi-geo-alt text-danger"></i> ${(booking.destination || booking.dropoff_location || 'N/A').substring(0, 30)}...</div>
                    </td>
                    <td class="fw-semibold">₱${parseFloat(booking.fare || 0).toFixed(0)}</td>
                    <td><span class="badge ${getStatusBadgeClass(booking.status)}">${getStatusText(booking.status)}</span></td>
                    <td class="text-muted small"><i class="bi bi-clock"></i> ${formatTime(booking.created_at)}</td>
                    <td><button class="btn btn-sm btn-link text-muted"><i class="bi bi-three-dots-vertical"></i></button></td>
                </tr>
            `).join('');
        }
    })
    .catch(error => console.error('Error updating all bookings:', error));
}

// Drivers Update
function startDriversUpdate() {
    updateDrivers(); // Initial load
    driversInterval = setInterval(updateDrivers, 5000); // Every 5 seconds
}

function updateDrivers() {
    fetch('admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'same-origin',
        body: 'action=get_drivers'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const tbody = document.querySelector('#drivers tbody');
            if (!tbody) return;
            
            const drivers = data.data;
            
            if (drivers.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                            <p class="text-muted mt-2">No drivers found</p>
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = drivers.map(driver => `
                <tr>
                    <td class="fw-semibold">DRV-${String(driver.id).padStart(3, '0')}</td>
                    <td>
                        <div class="avatar-name">
                            <span class="avatar">${(driver.name || 'UN').substring(0, 2).toUpperCase()}</span>
                            ${driver.name || 'Unknown'}
                        </div>
                    </td>
                    <td>${driver.tricycle_number || 'TRY-' + String(driver.id).padStart(3, '0')}</td>
                    <td><i class="bi bi-star-fill text-warning"></i> ${driver.rating || '4.8'}</td>
                    <td>${driver.total_trips || 0} trips</td>
                    <td><span class="badge ${driver.status === 'available' ? 'bg-success' : 'bg-secondary'}">${driver.status === 'available' ? 'Active' : 'Offline'}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="viewDriverDetails(${driver.id})">
                                <i class="bi bi-eye"></i> View
                            </button>
                            <button class="btn btn-outline-danger" onclick="deleteDriver(${driver.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
    })
    .catch(error => console.error('Error updating drivers:', error));
}

// Users Update
function startUsersUpdate() {
    updateUsers(); // Initial load
    usersInterval = setInterval(updateUsers, 5000); // Every 5 seconds
}

function updateUsers() {
    fetch('admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'same-origin',
        body: 'action=get_users'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const tbody = document.querySelector('#users tbody');
            if (!tbody) return;
            
            const users = data.data;
            
            if (users.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                            <p class="text-muted mt-2">No users found</p>
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = users.map(user => `
                <tr>
                    <td><strong>USR-${String(user.id).padStart(3, '0')}</strong></td>
                    <td>
                        <div class="fw-semibold">${user.name || 'Unknown'}</div>
                        <small class="text-muted">${user.email || 'N/A'}</small>
                    </td>
                    <td>${user.phone || 'N/A'}</td>
                    <td>${user.total_rides || 0} rides</td>
                    <td><small>${formatDate(user.created_at)}</small></td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(${user.id})">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </td>
                </tr>
            `).join('');
        }
    })
    .catch(error => console.error('Error updating users:', error));
}

// Applications Update
function startApplicationsUpdate() {
    updateApplications(); // Initial load
    applicationsInterval = setInterval(updateApplications, 5000); // Every 5 seconds
}

function updateApplications() {
    fetch('admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'same-origin',
        body: 'action=get_applications'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const tbody = document.querySelector('#applications tbody');
            if (!tbody) return;
            
            const applications = data.data;
            
            if (applications.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                            <p class="text-muted mt-2">No applications found</p>
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = applications.map(app => `
                <tr>
                    <td class="fw-semibold">APP-${String(app.id).padStart(3, '0')}</td>
                    <td>
                        <div class="avatar-name">
                            <span class="avatar">${(app.first_name.substring(0, 1) + app.last_name.substring(0, 1)).toUpperCase()}</span>
                            ${app.first_name} ${app.last_name}
                        </div>
                    </td>
                    <td class="text-muted">${app.email}</td>
                    <td>${app.phone}</td>
                    <td>${app.license_number}</td>
                    <td>${(app.vehicle_make || '') + ' ' + (app.vehicle_model || '')}</td>
                    <td><span class="badge ${getApplicationStatusBadge(app.status)}">${getApplicationStatusText(app.status)}</span></td>
                    <td class="text-muted">${formatDate(app.application_date || app.created_at)}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewApplicationDetails(${app.id})">
                            <i class="bi bi-eye"></i> View
                        </button>
                    </td>
                </tr>
            `).join('');
        }
    })
    .catch(error => console.error('Error updating applications:', error));
}

// ========== UTILITY FUNCTIONS ==========

function formatDateTime(datetime) {
    if (!datetime) return 'N/A';
    const date = new Date(datetime);
    return date.toLocaleString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric',
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

function formatDate(datetime) {
    if (!datetime) return 'N/A';
    const date = new Date(datetime);
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric'
    });
}

function formatTime(datetime) {
    if (!datetime) return 'N/A';
    const date = new Date(datetime);
    return date.toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit',
        hour12: true
    });
}

function getStatusBadgeClass(status) {
    const classes = {
        'pending': 'bg-warning',
        'accepted': 'bg-info',
        'picked_up': 'bg-primary',
        'in_progress': 'bg-primary',
        'completed': 'bg-success',
        'cancelled': 'bg-danger',
        'rejected': 'bg-danger'
    };
    return classes[status] || 'bg-secondary';
}

function getStatusText(status) {
    const texts = {
        'pending': 'Pending',
        'accepted': 'Accepted',
        'picked_up': 'Picked Up',
        'in_progress': 'In Progress',
        'completed': 'Completed',
        'cancelled': 'Cancelled',
        'rejected': 'Rejected'
    };
    return texts[status] || status;
}

function getApplicationStatusBadge(status) {
    const classes = {
        'pending': 'bg-warning',
        'approved': 'bg-success',
        'rejected': 'bg-danger'
    };
    return classes[status] || 'bg-secondary';
}

function getApplicationStatusText(status) {
    const texts = {
        'pending': 'Pending',
        'approved': 'Approved',
        'rejected': 'Rejected'
    };
    return texts[status] || status;
}