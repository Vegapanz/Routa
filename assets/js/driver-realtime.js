/**
 * Routa Real-time Integration for Driver Dashboard
 * Handles booking notifications, location tracking, and status updates
 */

let realtimeDriver = null;
let driverUserId = null;
let locationTracking = null;

function initDriverRealtime(userId) {
    driverUserId = userId;
    console.log('[Driver] Initializing real-time connection for user:', userId);
    realtimeDriver = new RoutaRealtime('ws://127.0.0.1:8080');
    
    // Connect as driver
    realtimeDriver.connect(userId, 'driver');
    
    // Setup event handlers
    realtimeDriver.on('authenticated', () => {
        console.log('Driver real-time connected');
        showToast('Real-time updates enabled', 'success');
        
        // Start location tracking if online
        const statusToggle = document.getElementById('statusToggle');
        if (statusToggle && statusToggle.checked) {
            startLocationTracking();
        }
    });
    
    realtimeDriver.on('new_booking', (data) => {
        console.log('New booking available:', data);
        
        // Play sound
        playNotificationSound();
        
        // Show booking modal
        showBookingModal(data);
    });
    
    realtimeDriver.on('booking_assigned', (data) => {
        console.log('Booking assigned to you:', data);
        
        showToast('New booking assigned! Refreshing...', 'success');
        playNotificationSound();
        
        // Reload page to show new booking
        // Use a longer delay to ensure notification is saved
        setTimeout(() => {
            window.location.reload(true); // Force reload from server
        }, 1500);
    });
    
    realtimeDriver.on('status_update', (data) => {
        console.log('Booking status updated:', data);
        updateBookingStatusInList(data.ride_id, data.status);
    });
    
    realtimeDriver.on('disconnected', () => {
        showToast('Connection lost. Reconnecting...', 'warning');
        stopLocationTracking();
    });
}

function showBookingModal(data) {
    // Create modal HTML
    const modalHTML = `
        <div class="modal fade" id="newBookingModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-bell-fill me-2"></i>New Booking Available
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="booking-details">
                            <div class="mb-3">
                                <strong>Pickup:</strong>
                                <p class="mb-1">${data.pickup.address || 'N/A'}</p>
                                <small class="text-muted">Lat: ${data.pickup.lat}, Lng: ${data.pickup.lng}</small>
                            </div>
                            <div class="mb-3">
                                <strong>Dropoff:</strong>
                                <p class="mb-1">${data.dropoff.address || 'N/A'}</p>
                                <small class="text-muted">Lat: ${data.dropoff.lat}, Lng: ${data.dropoff.lng}</small>
                            </div>
                            <div class="mb-3">
                                <strong>Fare:</strong>
                                <h4 class="text-success mb-0">₱${parseFloat(data.fare).toFixed(2)}</h4>
                            </div>
                            <div class="mb-3">
                                <strong>Distance:</strong>
                                <p class="mb-0">${calculateDistance(data.pickup, data.dropoff)} km (approx)</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Decline</button>
                        <button type="button" class="btn btn-success" onclick="acceptBooking(${data.booking_id})">
                            <i class="bi bi-check-circle me-1"></i>Accept Booking
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('newBookingModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('newBookingModal'));
    modal.show();
}

function acceptBooking(bookingId) {
    // Send acceptance to server via AJAX
    fetch('php/driver_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({
            action: 'accept_ride',
            ride_id: bookingId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Booking accepted!', 'success');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('newBookingModal'));
            modal.hide();
            
            // Refresh page to show new booking
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Failed to accept booking', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error. Please try again.', 'danger');
    });
}

function startLocationTracking() {
    if (!realtimeDriver || !realtimeDriver.isConnected()) {
        console.log('Cannot start tracking - not connected');
        return;
    }
    
    if (locationTracking) {
        console.log('Location tracking already active');
        return;
    }
    
    // Check if geolocation is available
    if (!navigator.geolocation) {
        showToast('Geolocation not supported', 'warning');
        return;
    }
    
    console.log('Starting location tracking...');
    
    // Track location every 10 seconds
    locationTracking = setInterval(() => {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // Send location to WebSocket server
                realtimeDriver.updateLocation(lat, lng);
                
                console.log(`Location updated: ${lat}, ${lng}`);
                
                // Update UI indicator
                updateLocationIndicator(true);
            },
            (error) => {
                console.error('Geolocation error:', error);
                updateLocationIndicator(false);
            },
            {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            }
        );
    }, 10000);
    
    // Send initial location immediately
    navigator.geolocation.getCurrentPosition(
        (position) => {
            realtimeDriver.updateLocation(position.coords.latitude, position.coords.longitude);
            updateLocationIndicator(true);
        },
        (error) => {
            console.error('Initial location error:', error);
        }
    );
}

function stopLocationTracking() {
    if (locationTracking) {
        clearInterval(locationTracking);
        locationTracking = null;
        updateLocationIndicator(false);
        console.log('Location tracking stopped');
    }
}

function updateLocationIndicator(active) {
    let indicator = document.getElementById('locationIndicator');
    
    if (!indicator) {
        // Create indicator if doesn't exist
        indicator = document.createElement('div');
        indicator.id = 'locationIndicator';
        indicator.style.cssText = 'position: fixed; bottom: 20px; right: 20px; z-index: 1000;';
        document.body.appendChild(indicator);
    }
    
    if (active) {
        indicator.innerHTML = `
            <div class="badge bg-success" style="font-size: 14px; padding: 10px 15px;">
                <i class="bi bi-geo-alt-fill me-1"></i>
                <span class="pulse-dot"></span>
                Location Tracking Active
            </div>
        `;
    } else {
        indicator.innerHTML = `
            <div class="badge bg-secondary" style="font-size: 14px; padding: 10px 15px;">
                <i class="bi bi-geo-alt me-1"></i>
                Location Tracking Off
            </div>
        `;
    }
}

function updateBookingStatusInList(bookingId, status) {
    const row = document.querySelector(`tr[data-booking-id="${bookingId}"]`);
    if (!row) return;
    
    const statusBadge = row.querySelector('.status-badge');
    if (!statusBadge) return;
    
    const statusMap = {
        'confirmed': { text: 'Confirmed', class: 'bg-success' },
        'arrived': { text: 'Arrived', class: 'bg-info' },
        'in_progress': { text: 'In Progress', class: 'bg-primary' },
        'completed': { text: 'Completed', class: 'bg-secondary' }
    };
    
    const statusInfo = statusMap[status] || { text: status, class: 'bg-secondary' };
    statusBadge.className = `badge ${statusInfo.class} status-badge`;
    statusBadge.textContent = statusInfo.text;
    
    // Highlight row
    row.classList.add('table-row-highlight');
    setTimeout(() => row.classList.remove('table-row-highlight'), 2000);
}

function calculateDistance(pickup, dropoff) {
    // Simple Haversine formula
    const R = 6371; // Earth radius in km
    const dLat = (dropoff.lat - pickup.lat) * Math.PI / 180;
    const dLng = (dropoff.lng - pickup.lng) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(pickup.lat * Math.PI / 180) * Math.cos(dropoff.lat * Math.PI / 180) *
              Math.sin(dLng/2) * Math.sin(dLng/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return (R * c).toFixed(2);
}

function showToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show`;
    toast.style.cssText = 'min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 10px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 150);
    }, 5000);
}

function playNotificationSound() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.5);
    } catch (e) {
        console.log('Audio not supported');
    }
}

// Listen for status toggle changes
document.addEventListener('DOMContentLoaded', () => {
    const statusToggle = document.getElementById('statusToggle');
    if (statusToggle) {
        statusToggle.addEventListener('change', function() {
            if (this.checked) {
                startLocationTracking();
            } else {
                stopLocationTracking();
            }
        });
    }
});

// Add CSS for driver realtime
if (!document.getElementById('driver-realtime-styles')) {
    const driverStyles = document.createElement('style');
    driverStyles.id = 'driver-realtime-styles';
    driverStyles.textContent = `
        .pulse-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #fff;
            border-radius: 50%;
            animation: pulse 2s infinite;
            margin-left: 5px;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        
        .table-row-highlight {
            animation: highlightPulse 2s ease-in-out;
        }
        
        @keyframes highlightPulse {
            0%, 100% { background-color: transparent; }
            50% { background-color: #fff3cd; }
        }
    `;
    document.head.appendChild(driverStyles);
}

// Function to fetch and update bookings without full page reload
function fetchAndUpdateBookings() {
    fetch('php/driver_api.php?action=get_rides', {
        method: 'GET',
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.rides) {
                // Find the bookings container
                const bookingsContainer = document.querySelector('.bookings-list, .ride-requests, tbody');
                if (bookingsContainer) {
                    // If there are new rides, reload to show them properly
                    // This is safer than trying to inject HTML dynamically
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            }
        })
        .catch(error => {
            console.error('Error fetching bookings:', error);
            // Fallback to reload on error
            setTimeout(() => {
                location.reload();
            }, 2000);
        });
}
