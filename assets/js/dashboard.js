/**
 * ROUTA USER DASHBOARD - Complete Ride Booking System
 * 
 * Features:
 * 1. Location search with FREE OpenStreetMap APIs (Photon + Nominatim)
 * 2. Real-time ride tracking with status updates
 * 3. Driver assignment and acceptance flow
 * 4. Trip completion with drop-off confirmation
 * 5. 5-star rating system with reviews
 * 6. Complete trip history with ratings
 * 
 * Flow:
 * User Books → Driver Accepts → Trip Starts → Trip Completes → User Rates → Logs to History
 */

// Dashboard functionality - Using FREE OpenStreetMap (No API Key Needed!)
let pickupSelected = false;
let dropoffSelected = false;
let pickupSuggestions = [];
let dropoffSuggestions = [];

// Calculate distance between two coordinates using Haversine formula
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Radius of Earth in kilometers
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

// Search location using multiple APIs with fallback
async function searchLocation(query, isPickup) {
    if (query.length < 3) return;
    
    console.log('Searching location:', query, 'isPickup:', isPickup);
    
    const type = isPickup ? 'pickup' : 'dropoff';
    
    // Add Philippines to search query for better results
    const searchQuery = query.includes('Philippines') ? query : `${query}, Philippines`;
    
    // Try multiple APIs in order (fastest to slowest)
    const apis = [
        // 1. Photon API - Very fast, no rate limits
        {
            name: 'Photon',
            url: `https://photon.komoot.io/api/?q=${encodeURIComponent(searchQuery)}&limit=8&lat=14.5995&lon=120.9842`,
            parseResults: (data) => {
                if (!data.features || data.features.length === 0) return [];
                return data.features.map(f => ({
                    display_name: formatPhotonAddress(f.properties),
                    lat: f.geometry.coordinates[1],
                    lon: f.geometry.coordinates[0],
                    name: f.properties.name || f.properties.street || 'Unknown'
                }));
            }
        },
        // 2. Nominatim - Reliable backup
        {
            name: 'Nominatim',
            url: `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}&countrycodes=ph&limit=8&addressdetails=1`,
            parseResults: (data) => data
        }
    ];
    
    for (const api of apis) {
        try {
            console.log(`Trying ${api.name} API:`, api.url);
            
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout
            
            const response = await fetch(api.url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                },
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                console.log(`${api.name} returned status:`, response.status);
                continue; // Try next API
            }
            
            const data = await response.json();
            const results = api.parseResults(data);
            
            console.log(`${api.name} results:`, results);
            
            if (results && results.length > 0) {
                if (isPickup) {
                    pickupSuggestions = results;
                    showSuggestions(results, 'pickup');
                } else {
                    dropoffSuggestions = results;
                    showSuggestions(results, 'dropoff');
                }
                return; // Success, stop trying other APIs
            }
        } catch (error) {
            console.log(`${api.name} failed:`, error.message);
            continue; // Try next API
        }
    }
    
    // All APIs failed
    console.error('All geocoding APIs failed');
    showErrorMessage(type, 'Unable to search locations. Please try again.');
}

// Format Photon API address for display
function formatPhotonAddress(props) {
    const parts = [];
    
    if (props.name) parts.push(props.name);
    if (props.street) parts.push(props.street);
    if (props.city || props.locality) parts.push(props.city || props.locality);
    if (props.district) parts.push(props.district);
    if (props.state) parts.push(props.state);
    if (props.country) parts.push(props.country);
    
    return parts.filter(p => p).join(', ') || 'Unknown location';
}

// Show error message
function showErrorMessage(type, message) {
    const inputId = type === 'pickup' ? 'pickupLocation' : 'dropoffLocation';
    const input = document.getElementById(inputId);
    
    if (!input) return;
    
    // Remove existing suggestions
    let existingSuggestions = document.getElementById(`${type}-suggestions`);
    if (existingSuggestions) {
        existingSuggestions.remove();
    }
    
    // Create error message
    const errorDiv = document.createElement('div');
    errorDiv.id = `${type}-suggestions`;
    errorDiv.className = 'location-suggestions';
    errorDiv.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        z-index: 1000;
        margin-top: 4px;
        padding: 12px 16px;
        color: #dc2626;
        font-size: 0.875rem;
    `;
    errorDiv.textContent = message;
    
    input.parentElement.parentElement.style.position = 'relative';
    input.parentElement.parentElement.appendChild(errorDiv);
    
    // Remove error after 3 seconds
    setTimeout(() => {
        if (errorDiv.parentElement) {
            errorDiv.remove();
        }
    }, 3000);
}

// Show autocomplete suggestions
function showSuggestions(results, type) {
    const inputId = type === 'pickup' ? 'pickupLocation' : 'dropoffLocation';
    const input = document.getElementById(inputId);
    
    // Remove existing suggestions
    let existingSuggestions = document.getElementById(`${type}-suggestions`);
    if (existingSuggestions) {
        existingSuggestions.remove();
    }
    
    if (results.length === 0) return;
    
    console.log('Showing suggestions for', type, ':', results); // Debug log
    
    // Create suggestions dropdown
    const suggestionsDiv = document.createElement('div');
    suggestionsDiv.id = `${type}-suggestions`;
    suggestionsDiv.className = 'location-suggestions';
    suggestionsDiv.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #10b981;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        max-height: 300px;
        overflow-y: auto;
        z-index: 9999;
        margin-top: 4px;
    `;
    
    results.forEach((result, index) => {
        const item = document.createElement('div');
        item.className = 'suggestion-item';
        item.style.cssText = `
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.875rem;
        `;
        item.innerHTML = `
            <div style="color: #0f172a; font-weight: 500;">${result.display_name.split(',')[0]}</div>
            <div style="color: #64748b; font-size: 0.75rem;">${result.display_name}</div>
        `;
        
        item.addEventListener('mouseenter', () => {
            item.style.backgroundColor = '#f8fafc';
        });
        
        item.addEventListener('mouseleave', () => {
            item.style.backgroundColor = 'white';
        });
        
        item.addEventListener('click', (e) => {
            e.stopPropagation();
            selectLocation(result, type);
            suggestionsDiv.remove();
        });
        
        suggestionsDiv.appendChild(item);
    });
    
    // Find the form group container
    let container = input.parentElement;
    while (container && !container.classList.contains('mb-3')) {
        container = container.parentElement;
    }
    
    if (!container) {
        container = input.parentElement.parentElement;
    }
    
    container.style.position = 'relative';
    container.appendChild(suggestionsDiv);
    
    console.log('Suggestions dropdown appended to:', container); // Debug log
}

// Select a location from suggestions
function selectLocation(result, type) {
    if (type === 'pickup') {
        document.getElementById('pickupLocation').value = result.display_name;
        document.getElementById('pickupLat').value = result.lat;
        document.getElementById('pickupLng').value = result.lon;
        pickupSelected = true;
    } else {
        document.getElementById('dropoffLocation').value = result.display_name;
        document.getElementById('dropoffLat').value = result.lat;
        document.getElementById('dropoffLng').value = result.lon;
        dropoffSelected = true;
    }
    
    calculateRoute();
}

// Calculate route and fare
function calculateRoute() {
    const pickupLat = parseFloat(document.getElementById('pickupLat').value);
    const pickupLng = parseFloat(document.getElementById('pickupLng').value);
    const dropoffLat = parseFloat(document.getElementById('dropoffLat').value);
    const dropoffLng = parseFloat(document.getElementById('dropoffLng').value);

    if (pickupLat && pickupLng && dropoffLat && dropoffLng) {
        // Calculate distance using Haversine formula
        const distance = calculateDistance(pickupLat, pickupLng, dropoffLat, dropoffLng);
        const distanceText = distance.toFixed(2) + ' km';
        
        // Calculate fare (Base fare ₱40 + ₱15 per km)
        const baseFare = 40;
        const perKmRate = 15;
        const fare = Math.ceil(baseFare + (distance * perKmRate));

        // Display fare and distance
        document.getElementById('distanceText').textContent = distanceText;
        document.getElementById('fareText').textContent = '₱' + fare.toLocaleString();
        document.getElementById('fareDisplay').classList.remove('d-none');
    }
}

// Setup location search with debouncing
let pickupTimeout, dropoffTimeout;

function initializeLocationSearch() {
    const pickupInput = document.getElementById('pickupLocation');
    const dropoffInput = document.getElementById('dropoffLocation');
    
    if (pickupInput) {
        // Remove any existing listeners
        const newPickupInput = pickupInput.cloneNode(true);
        pickupInput.parentNode.replaceChild(newPickupInput, pickupInput);
        
        newPickupInput.addEventListener('input', function(e) {
            console.log('Pickup input:', this.value); // Debug log
            clearTimeout(pickupTimeout);
            pickupSelected = false;
            
            const latInput = document.getElementById('pickupLat');
            const lngInput = document.getElementById('pickupLng');
            if (latInput) latInput.value = '';
            if (lngInput) lngInput.value = '';
            
            // Remove existing suggestions
            const existingSuggestions = document.getElementById('pickup-suggestions');
            if (existingSuggestions) existingSuggestions.remove();
            
            pickupTimeout = setTimeout(() => {
                if (this.value.length >= 3) {
                    console.log('Searching for:', this.value); // Debug log
                    searchLocation(this.value, true);
                }
            }, 500);
        });
        
        // Prevent form submission on enter
        newPickupInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
    }
    
    if (dropoffInput) {
        // Remove any existing listeners
        const newDropoffInput = dropoffInput.cloneNode(true);
        dropoffInput.parentNode.replaceChild(newDropoffInput, dropoffInput);
        
        newDropoffInput.addEventListener('input', function() {
            console.log('Dropoff input:', this.value); // Debug log
            clearTimeout(dropoffTimeout);
            dropoffSelected = false;
            
            const latInput = document.getElementById('dropoffLat');
            const lngInput = document.getElementById('dropoffLng');
            if (latInput) latInput.value = '';
            if (lngInput) lngInput.value = '';
            
            // Remove existing suggestions
            const existingSuggestions = document.getElementById('dropoff-suggestions');
            if (existingSuggestions) existingSuggestions.remove();
            
            dropoffTimeout = setTimeout(() => {
                if (this.value.length >= 3) {
                    console.log('Searching for:', this.value); // Debug log
                    searchLocation(this.value, false);
                }
            }, 500);
        });
        
        // Prevent form submission on enter
        newDropoffInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
    }
}

// Close suggestions when clicking outside
document.addEventListener('click', function(e) {
    const pickupInput = document.getElementById('pickupLocation');
    const dropoffInput = document.getElementById('dropoffLocation');
    
    if (pickupInput && !pickupInput.contains(e.target)) {
        const suggestions = document.getElementById('pickup-suggestions');
        if (suggestions && !suggestions.contains(e.target)) {
            suggestions.remove();
        }
    }
    
    if (dropoffInput && !dropoffInput.contains(e.target)) {
        const suggestions = document.getElementById('dropoff-suggestions');
        if (suggestions && !suggestions.contains(e.target)) {
            suggestions.remove();
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Initialize location search
    initializeLocationSearch();
    
    // Reinitialize when modal is shown
    const bookModal = document.getElementById('bookRideModal');
    if (bookModal) {
        bookModal.addEventListener('shown.bs.modal', function() {
            console.log('Modal shown, initializing location search'); // Debug log
            initializeLocationSearch();
        });
    }
    
    // Handle book ride form submission
    const bookRideForm = document.getElementById('bookRideForm');
    if (bookRideForm) {
        bookRideForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const pickupLocation = document.getElementById('pickupLocation').value;
            const dropoffLocation = document.getElementById('dropoffLocation').value;
            const pickupLat = document.getElementById('pickupLat').value;
            const pickupLng = document.getElementById('pickupLng').value;
            const dropoffLat = document.getElementById('dropoffLat').value;
            const dropoffLng = document.getElementById('dropoffLng').value;
            const paymentMethod = document.getElementById('paymentMethod').value;
            const fareText = document.getElementById('fareText').textContent.replace('₱', '').replace(',', '');
            const distanceText = document.getElementById('distanceText').textContent;

            // Validate locations are selected
            if (!pickupLat || !dropoffLat) {
                alert('Please select valid pickup and drop-off locations from the suggestions.');
                return;
            }

            // Submit booking to server using new API
            try {
                // Calculate estimated duration (assuming 20 km/h average speed)
                const distance = parseFloat(distanceText.replace(' km', ''));
                const durationMinutes = Math.round((distance / 20) * 60);
                const duration = `${durationMinutes} mins`;

                const response = await fetch('php/booking_api.php?action=create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        pickup_location: pickupLocation,
                        dropoff_location: dropoffLocation,
                        pickup_lat: pickupLat,
                        pickup_lng: pickupLng,
                        dropoff_lat: dropoffLat,
                        dropoff_lng: dropoffLng,
                        payment_method: paymentMethod,
                        distance: distanceText,
                        duration: duration
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Hide booking modal
                    const bookingModal = bootstrap.Modal.getInstance(document.getElementById('bookRideModal'));
                    bookingModal.hide();

                    // Reset form
                    bookRideForm.reset();
                    document.getElementById('fareDisplay').classList.add('d-none');
                    document.getElementById('pickupLat').value = '';
                    document.getElementById('pickupLng').value = '';
                    document.getElementById('dropoffLat').value = '';
                    document.getElementById('dropoffLng').value = '';

                    // Show ride tracking modal
                    showRideTrackingModal(data);

                } else {
                    alert('Error booking ride: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while booking the ride. Please try again.');
            }
        });
    }

    // Reset form when modal is closed
    const bookRideModal = document.getElementById('bookRideModal');
    if (bookRideModal) {
        bookRideModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('bookRideForm').reset();
            document.getElementById('fareDisplay').classList.add('d-none');
            document.getElementById('pickupLat').value = '';
            document.getElementById('pickupLng').value = '';
            document.getElementById('dropoffLat').value = '';
            document.getElementById('dropoffLng').value = '';
        });
    }

    // Check for active booking on page load
    checkActiveBooking();
});

// Show ride tracking modal with real-time updates
function showRideTrackingModal(bookingData) {
    // Create modal HTML if it doesn't exist
    let trackingModal = document.getElementById('rideTrackingModal');
    if (!trackingModal) {
        const modalHTML = `
            <div class="modal fade" id="rideTrackingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold">Your Ride</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="return confirmCancelRide()"></button>
                        </div>
                        <div class="modal-body">
                            <div id="rideStatus" class="text-center mb-4">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-muted" id="statusText">Searching for nearby drivers...</p>
                            </div>
                            
                            <div id="driverInfo" class="d-none">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-3 text-muted">Driver Details</h6>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="bi bi-person-fill text-white fs-3"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1" id="driverName">-</h5>
                                                <div class="text-muted small">
                                                    <span id="driverRating">-</span> ★ • <span id="driverPlate">-</span>
                                                </div>
                                                <div class="text-muted small" id="driverETA">-</div>
                                            </div>
                                            <a href="#" id="driverPhone" class="btn btn-outline-success btn-sm">
                                                <i class="bi bi-telephone-fill"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-2">
                                        <i class="bi bi-geo-alt-fill text-success me-2 mt-1"></i>
                                        <div class="flex-grow-1">
                                            <small class="text-muted">Pickup</small>
                                            <div id="ridePickup">-</div>
                                        </div>
                                    </div>
                                    <div class="border-start border-2 border-secondary ms-2 my-2" style="height: 20px;"></div>
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-geo-alt text-danger me-2 mt-1"></i>
                                        <div class="flex-grow-1">
                                            <small class="text-muted">Drop-off</small>
                                            <div id="rideDropoff">-</div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <small class="text-muted">Distance</small>
                                            <div class="fw-semibold" id="rideDistance">-</div>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted">Fare</small>
                                            <div class="fw-semibold text-success fs-5" id="rideFare">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button type="button" class="btn btn-danger w-100" id="cancelRideBtn" onclick="cancelRide()">
                                    Cancel Ride
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        trackingModal = document.getElementById('rideTrackingModal');
    }
    
    // Update modal with booking data
    updateRideTrackingModal(bookingData);
    
    // Show modal
    const modal = new bootstrap.Modal(trackingModal);
    modal.show();
    
    // Start polling for updates
    startRideStatusPolling(bookingData.booking_id);
}

// Update ride tracking modal with current data
function updateRideTrackingModal(data) {
    const statusMessages = {
        'pending': 'Booking submitted! Waiting for admin confirmation...',
        'searching': 'Admin is looking for available drivers...',
        'driver_found': 'Driver found! Waiting for driver confirmation...',
        'confirmed': 'Driver confirmed! Heading to your location...',
        'arrived': 'Driver has arrived at pickup location',
        'in_progress': 'Trip in progress to destination...',
        'completed': 'Trip completed! Thank you for riding with us!'
    };
    
    // Store booking ID in modal for later use
    const modal = document.getElementById('rideTrackingModal');
    if (modal && data.booking_id) {
        modal.dataset.bookingId = data.booking_id;
    }
    
    document.getElementById('statusText').textContent = statusMessages[data.status] || data.message;
    document.getElementById('ridePickup').textContent = data.booking?.pickup_location || 'Loading...';
    document.getElementById('rideDropoff').textContent = data.booking?.dropoff_location || 'Loading...';
    document.getElementById('rideDistance').textContent = data.booking?.distance || '-';
    document.getElementById('rideFare').textContent = '₱' + (data.fare || data.booking?.fare || '0');
    
    // Show/hide driver info based on status
    if (data.driver && data.status !== 'searching' && data.status !== 'pending') {
        document.getElementById('driverInfo').classList.remove('d-none');
        document.getElementById('rideStatus').classList.add('d-none');
        document.getElementById('driverName').textContent = data.driver.name;
        document.getElementById('driverRating').textContent = data.driver.rating || '5.0';
        document.getElementById('driverPlate').textContent = data.driver.plate_number;
        
        // Update ETA based on status
        if (data.status === 'confirmed') {
            document.getElementById('driverETA').textContent = data.driver.eta ? `Arriving in ${data.driver.eta}` : 'On the way';
        } else if (data.status === 'arrived') {
            document.getElementById('driverETA').textContent = 'Arrived at pickup';
        } else if (data.status === 'in_progress') {
            document.getElementById('driverETA').textContent = 'Trip in progress';
        }
        
        document.getElementById('driverPhone').href = `tel:${data.driver.phone}`;
    } else if (data.status === 'pending' || data.status === 'searching' || data.status === 'driver_found') {
        document.getElementById('driverInfo').classList.add('d-none');
        document.getElementById('rideStatus').classList.remove('d-none');
    }
    
    // Hide cancel button if trip started or completed
    if (data.status === 'in_progress' || data.status === 'completed') {
        document.getElementById('cancelRideBtn').classList.add('d-none');
    }
    
    // Show completion message and rating modal
    if (data.status === 'completed') {
        document.getElementById('rideStatus').classList.remove('d-none');
        document.getElementById('rideStatus').innerHTML = `
            <div class="text-success mb-3">
                <i class="bi bi-check-circle-fill" style="font-size: 4rem;"></i>
            </div>
            <h5 class="fw-bold text-success">Trip Completed!</h5>
            <p class="text-muted" id="statusText">Thank you for riding with us!</p>
        `;
        
        setTimeout(() => {
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('rideTrackingModal'));
            if (modalInstance) {
                modalInstance.hide();
            }
            // Show rating modal with booking_id from data
            showRatingModal(data.booking_id || data.booking?.id);
        }, 2000);
    }
}

// Poll for ride status updates
let statusPollingInterval = null;
function startRideStatusPolling(bookingId) {
    // Clear any existing polling
    if (statusPollingInterval) {
        clearInterval(statusPollingInterval);
    }
    
    // Poll every 5 seconds
    statusPollingInterval = setInterval(async () => {
        try {
            const response = await fetch(`php/booking_api.php?action=status&booking_id=${bookingId}`);
            const data = await response.json();
            
            if (data.success) {
                updateRideTrackingModal(data);
                
                // Stop polling if ride is completed or cancelled
                if (data.booking.status === 'completed' || data.booking.status === 'cancelled') {
                    clearInterval(statusPollingInterval);
                }
            }
        } catch (error) {
            console.error('Error polling ride status:', error);
        }
    }, 5000);
}

// Check for active booking
async function checkActiveBooking() {
    try {
        const response = await fetch('php/booking_api.php?action=active');
        const data = await response.json();
        
        if (data.success && data.booking) {
            // Show tracking modal for active booking
            showRideTrackingModal({
                success: true,
                booking_id: data.booking.id,
                status: data.booking.status,
                booking: data.booking,
                driver: data.booking.driver_id ? {
                    name: data.booking.driver_name,
                    phone: data.booking.driver_phone,
                    plate_number: data.booking.plate_number,
                    rating: data.booking.driver_rating
                } : null,
                fare: data.booking.fare
            });
        }
    } catch (error) {
        console.error('Error checking active booking:', error);
    }
}

// Cancel ride
async function cancelRide() {
    if (!confirm('Are you sure you want to cancel this ride?')) {
        return;
    }
    
    const bookingId = document.getElementById('rideTrackingModal').dataset.bookingId;
    
    try {
        const response = await fetch('php/booking_api.php?action=cancel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                booking_id: bookingId,
                reason: 'User cancelled'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            clearInterval(statusPollingInterval);
            bootstrap.Modal.getInstance(document.getElementById('rideTrackingModal')).hide();
            alert('Ride cancelled successfully');
            location.reload();
        } else {
            alert('Error cancelling ride: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while cancelling the ride');
    }
}

// Confirm cancel ride
function confirmCancelRide() {
    return confirm('Are you sure you want to close? Your ride will be cancelled.');
}

// Show rating modal after trip completion
function showRatingModal(bookingId) {
    console.log('Showing rating modal for booking:', bookingId);
    
    if (!bookingId) {
        console.error('No booking ID provided for rating');
        location.reload();
        return;
    }
    
    // Remove existing rating modal if any
    const existingModal = document.getElementById('ratingModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    const modalHTML = `
        <div class="modal fade" id="ratingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">Rate Your Trip</h5>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <p class="text-muted mb-4">How was your experience?</p>
                        <div class="mb-4">
                            <div class="rating-stars d-flex justify-content-center gap-2" id="ratingStars">
                                ${[1,2,3,4,5].map(i => `<i class="bi bi-star fs-1 text-muted rating-star" data-rating="${i}" style="cursor: pointer; transition: all 0.2s;"></i>`).join('')}
                            </div>
                            <p class="mt-2 mb-0 text-muted small" id="ratingLabel">Select a rating</p>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" id="ratingReview" rows="3" placeholder="Share your experience (optional)"></textarea>
                        </div>
                        <button class="btn btn-success w-100 mb-2" id="submitRatingBtn" onclick="submitRating(${bookingId})" disabled>
                            <i class="bi bi-star-fill me-2"></i>Submit Rating
                        </button>
                        <button class="btn btn-link text-muted w-100" onclick="skipRating()">Skip for now</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    const modal = new bootstrap.Modal(document.getElementById('ratingModal'));
    modal.show();
    
    // Rating labels
    const ratingLabels = {
        1: 'Poor',
        2: 'Fair',
        3: 'Good',
        4: 'Very Good',
        5: 'Excellent'
    };
    
    // Handle star clicks
    let selectedRating = 0;
    document.querySelectorAll('.rating-star').forEach(star => {
        // Hover effect
        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            document.querySelectorAll('.rating-star').forEach((s, i) => {
                if (i < rating) {
                    s.classList.add('bi-star-fill', 'text-warning');
                    s.classList.remove('bi-star', 'text-muted');
                }
            });
        });
        
        star.addEventListener('mouseleave', function() {
            document.querySelectorAll('.rating-star').forEach((s, i) => {
                if (i < selectedRating) {
                    s.classList.add('bi-star-fill', 'text-warning');
                    s.classList.remove('bi-star', 'text-muted');
                } else {
                    s.classList.remove('bi-star-fill', 'text-warning');
                    s.classList.add('bi-star', 'text-muted');
                }
            });
        });
        
        // Click to select
        star.addEventListener('click', function() {
            selectedRating = parseInt(this.dataset.rating);
            document.querySelectorAll('.rating-star').forEach((s, i) => {
                if (i < selectedRating) {
                    s.classList.remove('bi-star', 'text-muted');
                    s.classList.add('bi-star-fill', 'text-warning');
                } else {
                    s.classList.remove('bi-star-fill', 'text-warning');
                    s.classList.add('bi-star', 'text-muted');
                }
            });
            
            // Update label and enable submit button
            document.getElementById('ratingLabel').textContent = ratingLabels[selectedRating];
            document.getElementById('submitRatingBtn').disabled = false;
        });
    });
    
    // Store selected rating for submit function
    window.currentRating = 0;
    modal._element.addEventListener('hidden.bs.modal', () => {
        modal._element.remove();
        location.reload();
    });
}

// Submit rating
async function submitRating(bookingId) {
    const rating = document.querySelectorAll('#ratingStars i.bi-star-fill').length;
    const review = document.getElementById('ratingReview').value.trim();
    
    console.log('Submitting rating:', {bookingId, rating, review});
    
    if (rating === 0) {
        alert('Please select a rating');
        return;
    }
    
    // Disable button to prevent double submission
    const submitBtn = document.getElementById('submitRatingBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
    
    try {
        const response = await fetch('php/booking_api.php?action=rate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                booking_id: bookingId,
                rating: rating,
                review: review
            })
        });
        
        const data = await response.json();
        console.log('Rating response:', data);
        
        if (data.success) {
            // Show success message
            const modalBody = document.querySelector('#ratingModal .modal-body');
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 mb-2">Thank You!</h5>
                    <p class="text-muted">Your feedback helps us improve our service.</p>
                </div>
            `;
            
            // Close modal and reload after 2 seconds
            setTimeout(() => {
                const modalInstance = bootstrap.Modal.getInstance(document.getElementById('ratingModal'));
                if (modalInstance) {
                    modalInstance.hide();
                }
                location.reload();
            }, 2000);
        } else {
            alert('Error submitting rating: ' + data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-star-fill me-2"></i>Submit Rating';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while submitting your rating. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-star-fill me-2"></i>Submit Rating';
    }
}

// Skip rating
function skipRating() {
    if (confirm('Are you sure you want to skip rating? You can rate later from your trip history.')) {
        const modalInstance = bootstrap.Modal.getInstance(document.getElementById('ratingModal'));
        if (modalInstance) {
            modalInstance.hide();
        }
        location.reload();
    }
}