/**
 * Routa Real-time Integration for Rider Dashboard
 * Handles booking updates, driver tracking, and notifications
 */

let realtimeRider = null;
let riderUserId = null;
let driverMarker = null;
let rideMap = null;

function initRiderRealtime(userId) {
    riderUserId = userId;
        realtimeRider = new RoutaRealtime('ws://127.0.0.1:8080');
    
    // Connect as rider
    realtimeRider.connect(userId, 'rider');
    
    // Setup event handlers
    realtimeRider.on('authenticated', () => {
                showToast('Real-time updates enabled', 'success');
    });
    
    realtimeRider.on('booking_assigned', (data) => {
                
        showToast(`Driver ${data.driver_name} assigned!`, 'success');
        playNotificationSound();
        
        // Update booking card
        updateBookingCard(data);
    });
    
    realtimeRider.on('driver_accepted', (data) => {
                
        showToast('Driver accepted your booking!', 'success');
        playNotificationSound();
        
        // Update status
        updateRideStatus('confirmed');
    });
    
    realtimeRider.on('driver_location', (data) => {
                
        // Update driver marker on map
        updateDriverLocationOnMap(data.lat, data.lng);
    });
    
    realtimeRider.on('status_update', (data) => {
                
        updateRideStatus(data.status);
        
        // Show appropriate notification
        const statusMessages = {
            'confirmed': 'Driver is on the way!',
            'arrived': 'Driver has arrived at pickup location!',
            'in_progress': 'Your trip has started!',
            'completed': 'Ride completed! Please rate your driver'
        };
        
        if (statusMessages[data.status]) {
            showToast(statusMessages[data.status], 'info');
            playNotificationSound();
        }
        
        // Handle cancel button based on status
        const cancelBtn = document.querySelector('#cancelRideBtn, #cancelBookingBtn, .btn-cancel-booking');
        if (cancelBtn) {
            if (data.status === 'in_progress') {
                cancelBtn.disabled = true;
                cancelBtn.innerHTML = '<i class="bi bi-x-circle me-2"></i>Cannot Cancel (Ongoing)';
                cancelBtn.classList.add('btn-secondary');
                cancelBtn.classList.remove('btn-danger');
            } else if (data.status === 'completed') {
                cancelBtn.disabled = true;
                cancelBtn.style.display = 'none';
            }
        }
        
        // Update modal status text
        const modal = document.getElementById('rideTrackingModal');
        if (modal) {
            const statusText = modal.querySelector('#statusText');
            if (statusText) {
                const statusTexts = {
                    'confirmed': 'Driver is on the way to pick you up',
                    'arrived': 'Driver has arrived at your location',
                    'in_progress': 'Trip in progress - Enjoy your ride!',
                    'completed': 'Trip completed successfully'
                };
                if (statusTexts[data.status]) {
                    statusText.textContent = statusTexts[data.status];
                }
            }
        }
        
        // Show rating modal when completed
        if (data.status === 'completed') {
            setTimeout(() => {
                showRatingModal(data.ride_id);
            }, 2000);
        }
    });
    
    realtimeRider.on('ride_completed', (data) => {
                
        showToast('Ride completed! Total: ₱' + data.fare, 'success');
        
        // Re-enable "Book a Ride" button
        const bookRideBtn = document.querySelector('[data-action="book-ride"]');
        if (bookRideBtn) {
            bookRideBtn.disabled = false;
            bookRideBtn.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Book a New Ride';
        }
        
        // Show rating modal - use ride_id from the event
        setTimeout(() => {
            showRatingModal(data.ride_id);
        }, 2000);
    });
    
    realtimeRider.on('booking_rejected', (data) => {
                
        showToast(data.message || 'Your booking has been rejected', 'error');
        playNotificationSound();
        
        // Close the ride tracking modal if open
        const modal = document.getElementById('rideTrackingModal');
        if (modal) {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
        
        // Re-enable "Book a Ride" button
        const bookRideBtn = document.querySelector('.btn-book-ride, [data-action="book-ride"]');
        if (bookRideBtn) {
            bookRideBtn.disabled = false;
            bookRideBtn.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Book a New Ride';
        }
        
        // Reload page after a delay to show updated status
        setTimeout(() => {
            location.reload();
        }, 3000);
    });
    
    realtimeRider.on('driver_rejected', (data) => {
                
        showToast(data.message || 'Driver declined your booking. Admin will assign another driver shortly...', 'warning');
        playNotificationSound();
        
        // Update status text in modal if open
        const modal = document.getElementById('rideTrackingModal');
        if (modal) {
            const statusText = modal.querySelector('#statusText');
            if (statusText) {
                statusText.textContent = 'Driver declined. Admin is assigning another driver...';
            }
            
            // Hide driver info and show pending status
            const driverInfo = modal.querySelector('#driverInfo');
            const rideStatus = modal.querySelector('#rideStatus');
            
            if (driverInfo) {
                driverInfo.classList.add('d-none');
            }
            
            if (rideStatus) {
                rideStatus.classList.remove('d-none');
                const statusTextEl = rideStatus.querySelector('.text-muted');
                if (statusTextEl) {
                    statusTextEl.textContent = 'Waiting for admin to assign a new driver...';
                }
            }
        }
        
        // Update status badge to pending
        updateRideStatus('pending');
        
        // Reload page after delay to show new driver assignment
        setTimeout(() => {
            location.reload();
        }, 4000);
    });
    
    realtimeRider.on('disconnected', () => {
        showToast('Connection lost. Reconnecting...', 'warning');
    });
}

function updateBookingCard(data) {
        
    // Find the "Your Ride" modal
    const modal = document.getElementById('rideTrackingModal');
    if (!modal) {
                // Refresh the page to show the updated booking
        setTimeout(() => location.reload(), 1500);
        return;
    }
    
    // Update driver name in modal
    const driverNameEl = modal.querySelector('#driverName');
    if (driverNameEl) {
        driverNameEl.textContent = data.driver_name || 'N/A';
            }
    
    // Update driver rating
    const driverRatingEl = modal.querySelector('#driverRating');
    if (driverRatingEl) {
        driverRatingEl.textContent = data.driver_rating || 'N/A';
    }
    
    // Update driver plate/tricycle number
    const driverPlateEl = modal.querySelector('#driverPlate');
    if (driverPlateEl) {
        driverPlateEl.textContent = data.tricycle_number || data.plate_number || 'N/A';
    }
    
    // Update phone link
    const driverPhoneEl = modal.querySelector('#driverPhone');
    if (driverPhoneEl && data.driver_phone) {
        driverPhoneEl.href = 'tel:' + data.driver_phone;
    }
    
    // Show driver info section and hide loading
    const rideStatus = modal.querySelector('#rideStatus');
    const driverInfo = modal.querySelector('#driverInfo');
    
    if (rideStatus) {
        rideStatus.classList.add('d-none');
    }
    
    if (driverInfo) {
        driverInfo.classList.remove('d-none');
        driverInfo.classList.add('fade-in');
    }
    
    // Update status text
    const statusText = modal.querySelector('#statusText');
    if (statusText) {
        statusText.textContent = 'Driver assigned! Preparing for pickup...';
    }
    
    // Disable "Book a Ride" button if exists
    const bookRideBtn = document.querySelector('.btn-book-ride');
    if (bookRideBtn) {
        bookRideBtn.disabled = true;
        bookRideBtn.innerHTML = '<i class="bi bi-clock me-2"></i>Booking in Progress';
    }
    
    }

function updateRideStatus(status) {
    const statusBadge = document.querySelector('.ride-status-badge');
    if (!statusBadge) return;
    
    const statusMap = {
        'pending': { text: 'Pending', class: 'bg-warning', icon: 'clock' },
        'searching': { text: 'Finding Driver', class: 'bg-info', icon: 'search' },
        'driver_found': { text: 'Driver Found', class: 'bg-info', icon: 'person-check' },
        'confirmed': { text: 'On The Way', class: 'bg-success', icon: 'truck' },
        'arrived': { text: 'Driver Arrived', class: 'bg-primary', icon: 'geo-alt-fill' },
        'in_progress': { text: 'Ongoing', class: 'bg-danger', icon: 'arrow-right-circle-fill' },
        'completed': { text: 'Completed', class: 'bg-secondary', icon: 'check-circle-fill' }
    };
    
    const statusInfo = statusMap[status] || { text: status, class: 'bg-secondary', icon: 'info-circle' };
    
    statusBadge.className = `badge ${statusInfo.class} ride-status-badge`;
    statusBadge.innerHTML = `<i class="bi bi-${statusInfo.icon} me-1"></i>${statusInfo.text}`;
    
    // Update timeline
    updateStatusTimeline(status);
    
    // Add pulse animation
    statusBadge.classList.add('status-pulse');
    setTimeout(() => statusBadge.classList.remove('status-pulse'), 1000);
}

function updateStatusTimeline(status) {
    const timeline = document.querySelector('.status-timeline');
    if (!timeline) return;
    
    const steps = ['pending', 'driver_found', 'confirmed', 'arrived', 'in_progress', 'completed'];
    const currentIndex = steps.indexOf(status);
    
    if (currentIndex === -1) return; // Status not found in timeline
    
    steps.forEach((step, index) => {
        const stepEl = timeline.querySelector(`[data-step="${step}"]`);
        if (!stepEl) return;
        
        if (index <= currentIndex) {
            stepEl.classList.add('completed');
            stepEl.classList.remove('active');
        } else if (index === currentIndex + 1) {
            stepEl.classList.add('active');
            stepEl.classList.remove('completed');
        } else {
            stepEl.classList.remove('completed', 'active');
        }
    });
}

function updateDriverLocationOnMap(lat, lng) {
    // If map doesn't exist, try to initialize it
    if (!rideMap) {
        const mapElement = document.getElementById('rideMap');
        if (!mapElement) return;
        
        // Initialize map centered on driver location
        rideMap = new google.maps.Map(mapElement, {
            center: { lat: parseFloat(lat), lng: parseFloat(lng) },
            zoom: 15
        });
    }
    
    // Update or create driver marker
    if (!driverMarker) {
        driverMarker = new google.maps.Marker({
            position: { lat: parseFloat(lat), lng: parseFloat(lng) },
            map: rideMap,
            title: 'Driver Location',
            icon: {
                url: 'assets/images/tricycle-marker.png',
                scaledSize: new google.maps.Size(40, 40)
            }
        });
    } else {
        // Animate marker movement
        const newPos = { lat: parseFloat(lat), lng: parseFloat(lng) };
        
        // Smooth transition
        const currentPos = driverMarker.getPosition();
        animateMarker(driverMarker, currentPos, newPos);
    }
    
    // Pan map to keep driver in view
    rideMap.panTo({ lat: parseFloat(lat), lng: parseFloat(lng) });
}

function animateMarker(marker, from, to) {
    const frames = 60;
    const duration = 1000; // 1 second
    let frame = 0;
    
    const animate = () => {
        frame++;
        const progress = frame / frames;
        
        const lat = from.lat() + (to.lat - from.lat()) * progress;
        const lng = from.lng() + (to.lng - from.lng()) * progress;
        
        marker.setPosition({ lat, lng });
        
        if (frame < frames) {
            requestAnimationFrame(animate);
        }
    };
    
    animate();
}

function showRatingModal(rideId) {
    const modalHTML = `
        <div class="modal fade" id="ratingModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Rate Your Driver</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p class="mb-3">How was your ride?</p>
                        <div class="star-rating mb-3">
                            <i class="bi bi-star rating-star" data-rating="1"></i>
                            <i class="bi bi-star rating-star" data-rating="2"></i>
                            <i class="bi bi-star rating-star" data-rating="3"></i>
                            <i class="bi bi-star rating-star" data-rating="4"></i>
                            <i class="bi bi-star rating-star" data-rating="5"></i>
                        </div>
                        <textarea class="form-control" id="ratingComment" placeholder="Add a comment (optional)" rows="3"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Skip</button>
                        <button type="button" class="btn btn-primary" onclick="submitRating(${rideId})">Submit Rating</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal
    const existingModal = document.getElementById('ratingModal');
    if (existingModal) existingModal.remove();
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Setup star rating interaction
    const stars = document.querySelectorAll('.rating-star');
    let selectedRating = 0;
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            selectedRating = parseInt(this.dataset.rating);
            updateStars(selectedRating);
        });
        
        star.addEventListener('mouseenter', function() {
            updateStars(parseInt(this.dataset.rating));
        });
    });
    
    document.querySelector('.star-rating').addEventListener('mouseleave', () => {
        updateStars(selectedRating);
    });
    
    function updateStars(rating) {
        stars.forEach(star => {
            const starRating = parseInt(star.dataset.rating);
            if (starRating <= rating) {
                star.classList.remove('bi-star');
                star.classList.add('bi-star-fill', 'text-warning');
            } else {
                star.classList.remove('bi-star-fill', 'text-warning');
                star.classList.add('bi-star');
            }
        });
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('ratingModal'));
    modal.show();
}

function submitRating(rideId) {
    const rating = document.querySelectorAll('.rating-star.bi-star-fill').length;
    const comment = document.getElementById('ratingComment').value;
    
    if (rating === 0) {
        showToast('Please select a rating', 'warning');
        return;
    }
    
    // Submit via AJAX to booking_api.php
    fetch('php/booking_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'rate',
            booking_id: rideId,
            rating: rating,
            review: comment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Thank you for your rating!', 'success');
            const ratingModal = document.getElementById('ratingModal');
            if (ratingModal) {
                const modalInstance = bootstrap.Modal.getInstance(ratingModal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }
            // Refresh the page to show updated trip history
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to submit rating', 'danger');
        }
    })
    .catch(error => {
                showToast('Network error. Please try again.', 'danger');
    });
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
        
        oscillator.frequency.value = 1000;
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.3);
    } catch (e) {
            }
}

// Add CSS for rider realtime
if (!document.getElementById('rider-realtime-styles')) {
    const riderStyles = document.createElement('style');
    riderStyles.id = 'rider-realtime-styles';
    riderStyles.textContent = `
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .status-pulse {
            animation: pulse 0.5s ease-in-out;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .rating-star {
            font-size: 2rem;
            cursor: pointer;
            transition: all 0.2s;
            margin: 0 5px;
        }
        
        .rating-star:hover {
            transform: scale(1.2);
        }
        
        .status-timeline .completed {
            background: #198754;
            color: white;
        }
        
        .status-timeline .active {
            background: #0d6efd;
            color: white;
            animation: pulseBg 2s infinite;
        }
        
        @keyframes pulseBg {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    `;
    document.head.appendChild(riderStyles);
}
