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

// Map variables
let bookingMap = null;
let pickupMarker = null;
let dropoffMarker = null;
let routeLine = null;

// Initialize booking map
function initBookingMap() {
    // Default center: Manila, Philippines
    const manilaCoords = [14.5995, 120.9842];
    
    // Create map if it doesn't exist
    if (!bookingMap) {
        bookingMap = L.map('bookingMap').setView(manilaCoords, 13);
        
        // Add OpenStreetMap tiles (free, no API key needed)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(bookingMap);
        
        // Custom icons for markers
        const pickupIcon = L.divIcon({
            html: '<div style="background-color: #10b981; color: white; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 18px; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">📍</div>',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            className: 'custom-marker'
        });
        
        const dropoffIcon = L.divIcon({
            html: '<div style="background-color: #ef4444; color: white; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 18px; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">📍</div>',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            className: 'custom-marker'
        });
        
        // Store icons for later use
        bookingMap.pickupIcon = pickupIcon;
        bookingMap.dropoffIcon = dropoffIcon;
        
        // Map click handler to set locations
        bookingMap.on('click', async function(e) {
            const { lat, lng } = e.latlng;
            
            // Reverse geocode to get address
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                const data = await response.json();
                const address = data.display_name;
                
                // Check which field is empty or last focused
                const pickupInput = document.getElementById('pickupLocation');
                const dropoffInput = document.getElementById('dropoffLocation');
                
                if (!pickupInput.value || document.activeElement === pickupInput || (!dropoffInput.value)) {
                    // Set as pickup if pickup is empty or focused
                    if (!pickupInput.value) {
                        selectLocation({ display_name: address, lat: lat, lon: lng }, 'pickup');
                        addMapMarker(lat, lng, 'pickup');
                    } else if (!dropoffInput.value) {
                        // Set as dropoff if pickup is already set
                        selectLocation({ display_name: address, lat: lat, lon: lng }, 'dropoff');
                        addMapMarker(lat, lng, 'dropoff');
                    }
                }
            } catch (error) {
                console.error('Error reverse geocoding:', error);
            }
        });
    } else {
        // If map exists, invalidate size to fix display issues
        setTimeout(() => bookingMap.invalidateSize(), 100);
    }
    
    return bookingMap;
}

// Add marker to map
function addMapMarker(lat, lng, type) {
    if (!bookingMap) return;
    
    const icon = type === 'pickup' ? bookingMap.pickupIcon : bookingMap.dropoffIcon;
    
    if (type === 'pickup') {
        // Remove existing pickup marker
        if (pickupMarker) {
            bookingMap.removeLayer(pickupMarker);
        }
        // Add new pickup marker
        pickupMarker = L.marker([lat, lng], { icon: icon }).addTo(bookingMap);
        pickupMarker.bindPopup('<b>Pickup Location</b>').openPopup();
    } else {
        // Remove existing dropoff marker
        if (dropoffMarker) {
            bookingMap.removeLayer(dropoffMarker);
        }
        // Add new dropoff marker
        dropoffMarker = L.marker([lat, lng], { icon: icon }).addTo(bookingMap);
        dropoffMarker.bindPopup('<b>Drop-off Location</b>').openPopup();
    }
    
    // Draw route if both markers exist
    drawRoute();
}

// Draw route between pickup and dropoff
async function drawRoute() {
    if (!bookingMap || !pickupMarker || !dropoffMarker) return;
    
    // Remove existing route
    if (routeLine) {
        bookingMap.removeLayer(routeLine);
    }
    
    const pickupLatLng = pickupMarker.getLatLng();
    const dropoffLatLng = dropoffMarker.getLatLng();
    
    try {
        // Use OSRM (Open Source Routing Machine) for actual road routing
        const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${pickupLatLng.lng},${pickupLatLng.lat};${dropoffLatLng.lng},${dropoffLatLng.lat}?overview=full&geometries=geojson&steps=true`;
        
        const response = await fetch(osrmUrl);
        const data = await response.json();
        
        if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
            const route = data.routes[0];
            const coordinates = route.geometry.coordinates;
            
            // Convert coordinates from [lng, lat] to [lat, lng] for Leaflet
            const latlngs = coordinates.map(coord => [coord[1], coord[0]]);
            
            // Draw the actual road route
            routeLine = L.polyline(latlngs, {
                color: '#10b981',
                weight: 5,
                opacity: 0.8,
                lineJoin: 'round'
            }).addTo(bookingMap);
            
            // Update distance and duration with real road data
            const distanceKm = (route.distance / 1000).toFixed(2);
            const durationMin = Math.round(route.duration / 60);
            
            document.getElementById('distanceText').textContent = distanceKm + ' km';
            
            // Recalculate fare with actual distance
            const baseFare = 40;
            const perKmRate = 15;
            const fare = Math.ceil(baseFare + (parseFloat(distanceKm) * perKmRate));
            document.getElementById('fareText').textContent = '₱' + fare.toLocaleString();
            
            // Show route info tooltip
            const midPoint = Math.floor(latlngs.length / 2);
            const popup = L.popup({
                closeButton: false,
                autoClose: false,
                closeOnClick: false,
                className: 'route-info-popup'
            })
            .setLatLng(latlngs[midPoint])
            .setContent(`
                <div style="text-align: center; font-size: 12px;">
                    <div style="font-weight: bold; color: #10b981;">🚗 Fastest Route</div>
                    <div style="margin-top: 4px;">📏 ${distanceKm} km</div>
                    <div>⏱️ ~${durationMin} mins</div>
                </div>
            `)
            .addTo(bookingMap);
            
            // Store popup reference to remove later
            if (bookingMap.routePopup) {
                bookingMap.removeLayer(bookingMap.routePopup);
            }
            bookingMap.routePopup = popup;
            
            // Fit map to show the entire route
            bookingMap.fitBounds(routeLine.getBounds(), { padding: [50, 50] });
            
            console.log(`Route calculated: ${distanceKm} km, ~${durationMin} mins, Fare: ₱${fare}`);
        } else {
            // Fallback to straight line if routing fails
            console.log('OSRM routing failed, using straight line');
            drawStraightLineRoute(pickupLatLng, dropoffLatLng);
        }
    } catch (error) {
        console.error('Error fetching route:', error);
        // Fallback to straight line on error
        drawStraightLineRoute(pickupLatLng, dropoffLatLng);
    }
}

// Fallback function to draw straight line
function drawStraightLineRoute(pickupLatLng, dropoffLatLng) {
    routeLine = L.polyline([pickupLatLng, dropoffLatLng], {
        color: '#10b981',
        weight: 4,
        opacity: 0.7,
        dashArray: '10, 10'
    }).addTo(bookingMap);
    
    // Fit map to show both markers
    const bounds = L.latLngBounds([pickupLatLng, dropoffLatLng]);
    bookingMap.fitBounds(bounds, { padding: [50, 50] });
}

// Clear map markers and route
function clearMapMarkers() {
    if (!bookingMap) return;
    
    if (pickupMarker) {
        bookingMap.removeLayer(pickupMarker);
        pickupMarker = null;
    }
    if (dropoffMarker) {
        bookingMap.removeLayer(dropoffMarker);
        dropoffMarker = null;
    }
    if (routeLine) {
        bookingMap.removeLayer(routeLine);
        routeLine = null;
    }
    if (bookingMap.routePopup) {
        bookingMap.removeLayer(bookingMap.routePopup);
        bookingMap.routePopup = null;
    }
    
    // Reset map view to Manila
    bookingMap.setView([14.5995, 120.9842], 13);
}

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
        // Add marker to map
        addMapMarker(result.lat, result.lon, 'pickup');
    } else {
        document.getElementById('dropoffLocation').value = result.display_name;
        document.getElementById('dropoffLat').value = result.lat;
        document.getElementById('dropoffLng').value = result.lon;
        dropoffSelected = true;
        // Add marker to map
        addMapMarker(result.lat, result.lon, 'dropoff');
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
        // Calculate distance using Haversine formula (as fallback)
        const distance = calculateDistance(pickupLat, pickupLng, dropoffLat, dropoffLng);
        const distanceText = distance.toFixed(2) + ' km';
        
        // Calculate fare (Base fare ₱40 + ₱15 per km)
        const baseFare = 40;
        const perKmRate = 15;
        const fare = Math.ceil(baseFare + (distance * perKmRate));

        // Display initial fare and distance (will be updated by drawRoute with actual road distance)
        document.getElementById('distanceText').textContent = distanceText;
        document.getElementById('fareText').textContent = '₱' + fare.toLocaleString();
        document.getElementById('fareDisplay').classList.remove('d-none');
        
        // Note: drawRoute() will be called separately and will update these values with actual road data
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
            console.log('Modal shown, initializing location search and map'); // Debug log
            initializeLocationSearch();
            // Initialize map when modal is shown
            initBookingMap();
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
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Locations',
                    text: 'Please select valid pickup and drop-off locations from the suggestions.',
                    confirmButtonColor: '#10b981'
                });
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
                    
                    // Clear map markers
                    clearMapMarkers();

                    // Show ride tracking modal
                    showRideTrackingModal(data);

                } else {
                    Swal.fire({
                        title: 'Booking Error',
                        text: 'Error booking ride: ' + (data.message || 'Unknown error'),
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'An error occurred while booking the ride. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
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
            // Clear map markers and route
            clearMapMarkers();
        });
    }

    // Check for active booking on page load
    // DISABLED: Using real-time WebSocket updates instead of polling
    // checkActiveBooking();
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
    
    // DISABLED: Using real-time WebSocket updates instead of polling
    // Real-time updates are handled by rider-realtime.js
    // No need to poll the API every 5 seconds anymore
    
    /* OLD POLLING CODE - REPLACED BY WEBSOCKET
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
    */
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
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Cancel Ride?',
        text: 'Are you sure you want to cancel this ride?',
        showCancelButton: true,
        confirmButtonText: 'Yes, cancel it',
        cancelButtonText: 'No, keep it',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280'
    });
    
    if (!result.isConfirmed) {
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
            Swal.fire({
                icon: 'success',
                title: 'Cancelled',
                text: 'Ride cancelled successfully',
                confirmButtonColor: '#10b981',
                timer: 2000
            });
            location.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error cancelling ride: ' + data.message,
                confirmButtonColor: '#10b981'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while cancelling the ride',
            confirmButtonColor: '#10b981'
        });
    }
}

// Confirm cancel ride
async function confirmCancelRide() {
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Close Modal?',
        text: 'Are you sure you want to close? Your ride will be cancelled.',
        showCancelButton: true,
        confirmButtonText: 'Yes, close',
        cancelButtonText: 'No, stay',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280'
    });
    return result.isConfirmed;
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
        Swal.fire({
            icon: 'warning',
            title: 'Rating Required',
            text: 'Please select a rating',
            confirmButtonColor: '#10b981'
        });
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
            Swal.fire({
                icon: 'error',
                title: 'Submission Failed',
                text: data.message || 'Error submitting rating',
                confirmButtonColor: '#10b981'
            });
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-star-fill me-2"></i>Submit Rating';
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while submitting your rating. Please try again.',
            confirmButtonColor: '#10b981'
        });
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-star-fill me-2"></i>Submit Rating';
    }
}

// Skip rating
async function skipRating() {
    const result = await Swal.fire({
        icon: 'question',
        title: 'Skip Rating?',
        text: 'You can rate later from your trip history.',
        showCancelButton: true,
        confirmButtonText: 'Yes, skip',
        cancelButtonText: 'No, I\'ll rate now',
        confirmButtonColor: '#6b7280',
        cancelButtonColor: '#10b981'
    });
    
    if (result.isConfirmed) {
        const modalInstance = bootstrap.Modal.getInstance(document.getElementById('ratingModal'));
        if (modalInstance) {
            modalInstance.hide();
        }
        location.reload();
    }
}

// ============================================
// EDIT PROFILE FUNCTIONALITY
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const saveProfileBtn = document.getElementById('saveProfileBtn');
    const editProfileForm = document.getElementById('editProfileForm');
    const editProfileModal = document.getElementById('editProfileModal');
    const editPhoneInput = document.getElementById('editPhone');
    const sendOtpEditBtn = document.getElementById('sendOtpEditBtn');
    let phoneVerified = false;
    let originalPhone = '';
    
    if (!saveProfileBtn || !editProfileForm) return;
    
    // Store original phone number
    if (editPhoneInput) {
        originalPhone = editPhoneInput.getAttribute('data-original-phone') || '';
    }
    
    // Phone number validation - strict 09XXXXXXXXX format
    if (editPhoneInput) {
        editPhoneInput.addEventListener('input', function(e) {
            // Only allow digits
            let value = e.target.value.replace(/\D/g, '');
            
            // Ensure it starts with 09
            if (value.length > 0 && !value.startsWith('09')) {
                value = '09' + value.replace(/^0+/, '');
            }
            
            // Limit to 11 digits
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            e.target.value = value;
            
            // Check if phone changed from original
            const phoneChanged = value !== originalPhone;
            
            // Enable Send OTP button only if valid and changed
            if (value.length === 11 && value.match(/^09\d{9}$/)) {
                editPhoneInput.setCustomValidity('');
                editPhoneInput.classList.remove('is-invalid');
                editPhoneInput.classList.add('is-valid');
                
                if (phoneChanged) {
                    sendOtpEditBtn.disabled = false;
                    phoneVerified = false;
                    document.getElementById('phoneEditVerificationStatus').style.display = 'none';
                } else {
                    // Phone same as original, no need to verify
                    sendOtpEditBtn.disabled = true;
                    phoneVerified = true;
                }
            } else {
                editPhoneInput.classList.remove('is-valid');
                if (value.length > 0) {
                    editPhoneInput.setCustomValidity('Invalid phone number');
                    editPhoneInput.classList.add('is-invalid');
                }
                sendOtpEditBtn.disabled = true;
                phoneVerified = phoneChanged ? false : true;
            }
        });
        
        // Trigger validation on load
        editPhoneInput.dispatchEvent(new Event('input'));
    }
    
    // Send OTP for phone verification
    if (sendOtpEditBtn) {
        sendOtpEditBtn.addEventListener('click', async function() {
            const phone = editPhoneInput.value;
            
            if (!phone.match(/^09\d{9}$/)) {
                showAlert('Please enter a valid 11-digit phone number starting with 09', 'danger');
                return;
            }
            
            // Disable button and show loading
            sendOtpEditBtn.disabled = true;
            sendOtpEditBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';
            
            try {
                const formData = new FormData();
                formData.append('phone', phone);
                
                const response = await fetch('php/send_otp.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                console.log('Send OTP response:', data);
                
                if (data.success) {
                    // Store normalized phone for verification
                    const normalizedPhone = data.phone || phone;
                    document.getElementById('normalizedEditPhone').value = normalizedPhone;
                    
                    // Show OTP modal
                    document.getElementById('displayEditPhone').textContent = phone;
                    const otpEditModal = new bootstrap.Modal(document.getElementById('otpEditModal'));
                    otpEditModal.show();
                    
                    // Setup OTP input handlers
                    setupOTPEditInputs();
                    
                    // If debug OTP is present (development), show it prominently
                    if (data.debug_otp) {
                        console.log('%c OTP CODE: ' + data.debug_otp, 'background: #10b981; color: white; font-size: 20px; padding: 10px; font-weight: bold;');
                        showAlert('OTP sent! Check console for code (dev mode)', 'success');
                    } else {
                        showAlert('OTP sent successfully to your phone', 'success');
                    }
                } else {
                    showAlert(data.message || 'Failed to send OTP', 'danger');
                }
            } catch (error) {
                console.error('Error sending OTP:', error);
                showAlert('An error occurred. Please try again.', 'danger');
            } finally {
                sendOtpEditBtn.disabled = false;
                sendOtpEditBtn.innerHTML = '<i class="bi bi-shield-check me-1"></i>Send OTP';
            }
        });
    }
    
    // Password validation
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    
    // Real-time password match validation
    if (confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            if (newPassword.value && confirmPassword.value) {
                if (newPassword.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                    confirmPassword.classList.add('is-invalid');
                } else {
                    confirmPassword.setCustomValidity('');
                    confirmPassword.classList.remove('is-invalid');
                    confirmPassword.classList.add('is-valid');
                }
            }
        });
    }
    
    // Setup OTP input fields for edit profile
    function setupOTPEditInputs() {
        const inputs = ['otpEdit1', 'otpEdit2', 'otpEdit3', 'otpEdit4', 'otpEdit5', 'otpEdit6'];
        
        inputs.forEach((inputId, index) => {
            const input = document.getElementById(inputId);
            if (!input) return;
            
            input.value = '';
            input.classList.remove('error');
            
            // Auto-focus first input
            if (index === 0) {
                setTimeout(() => input.focus(), 100);
            }
            
            input.addEventListener('input', function(e) {
                // Only allow numbers
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Move to next input
                if (this.value.length === 1 && index < inputs.length - 1) {
                    document.getElementById(inputs[index + 1]).focus();
                }
            });
            
            input.addEventListener('keydown', function(e) {
                // Move to previous input on backspace
                if (e.key === 'Backspace' && this.value === '' && index > 0) {
                    document.getElementById(inputs[index - 1]).focus();
                }
            });
        });
    }
    
    // Verify OTP for edit profile
    if (document.getElementById('verifyOtpEditBtn')) {
        document.getElementById('verifyOtpEditBtn').addEventListener('click', async function() {
            const otp = ['otpEdit1', 'otpEdit2', 'otpEdit3', 'otpEdit4', 'otpEdit5', 'otpEdit6']
                .map(id => document.getElementById(id).value)
                .join('');
            
            if (otp.length !== 6) {
                const errorDiv = document.getElementById('otpEditError');
                errorDiv.textContent = 'Please enter all 6 digits';
                errorDiv.style.display = 'block';
                return;
            }
            
            const verifyBtn = document.getElementById('verifyOtpEditBtn');
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';
            
            // Hide previous errors
            document.getElementById('otpEditError').style.display = 'none';
            
            try {
                // Use normalized phone from send_otp response
                const normalizedPhone = document.getElementById('normalizedEditPhone').value || editPhoneInput.value;
                
                const formData = new FormData();
                formData.append('phone', normalizedPhone);
                formData.append('otp', otp);
                
                console.log('Verifying OTP:', { phone: normalizedPhone, otp: otp });
                
                const response = await fetch('php/verify_otp.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                console.log('Verify OTP response:', data);
                
                if (data.success) {
                    phoneVerified = true;
                    
                    // Close OTP modal
                    const otpModalEl = document.getElementById('otpEditModal');
                    const otpModalInstance = bootstrap.Modal.getInstance(otpModalEl);
                    if (otpModalInstance) {
                        otpModalInstance.hide();
                    }
                    
                    // Show verification status
                    document.getElementById('phoneEditVerificationStatus').style.display = 'block';
                    sendOtpEditBtn.disabled = true;
                    sendOtpEditBtn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Verified';
                    sendOtpEditBtn.classList.remove('btn-outline-success');
                    sendOtpEditBtn.classList.add('btn-success');
                    editPhoneInput.readOnly = true;
                    
                    showAlert('Phone number verified successfully!', 'success');
                } else {
                    const errorDiv = document.getElementById('otpEditError');
                    errorDiv.textContent = data.message || 'Invalid OTP code';
                    errorDiv.style.display = 'block';
                    
                    // Add error animation
                    ['otpEdit1', 'otpEdit2', 'otpEdit3', 'otpEdit4', 'otpEdit5', 'otpEdit6'].forEach(id => {
                        const input = document.getElementById(id);
                        input.classList.add('error');
                        setTimeout(() => input.classList.remove('error'), 500);
                    });
                }
            } catch (error) {
                console.error('Error verifying OTP:', error);
                const errorDiv = document.getElementById('otpEditError');
                errorDiv.textContent = 'An error occurred. Please try again.';
                errorDiv.style.display = 'block';
            } finally {
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Verify Code';
            }
        });
    }
    
    // Resend OTP for edit profile
    if (document.getElementById('resendOtpEditBtn')) {
        document.getElementById('resendOtpEditBtn').addEventListener('click', async function(e) {
            e.preventDefault();
            
            const phone = editPhoneInput.value;
            const originalText = this.textContent;
            this.textContent = 'Sending...';
            
            try {
                const formData = new FormData();
                formData.append('phone', phone);
                
                const response = await fetch('php/send_otp.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                console.log('Resend OTP response:', data);
                
                if (data.success) {
                    // Update normalized phone
                    const normalizedPhone = data.phone || phone;
                    document.getElementById('normalizedEditPhone').value = normalizedPhone;
                    
                    this.textContent = '✓ OTP Sent!';
                    
                    // Hide error message
                    document.getElementById('otpEditError').style.display = 'none';
                    
                    // Clear OTP inputs
                    ['otpEdit1', 'otpEdit2', 'otpEdit3', 'otpEdit4', 'otpEdit5', 'otpEdit6'].forEach(id => {
                        const input = document.getElementById(id);
                        input.value = '';
                        input.classList.remove('error');
                    });
                    document.getElementById('otpEdit1').focus();
                    
                    if (data.debug_otp) {
                        console.log('%c NEW OTP CODE: ' + data.debug_otp, 'background: #10b981; color: white; font-size: 20px; padding: 10px; font-weight: bold;');
                    }
                    
                    setTimeout(() => {
                        this.textContent = originalText;
                    }, 3000);
                } else {
                    this.textContent = originalText;
                    const errorDiv = document.getElementById('otpEditError');
                    errorDiv.textContent = data.message || 'Failed to resend OTP';
                    errorDiv.style.display = 'block';
                }
            } catch (error) {
                console.error('Error resending OTP:', error);
                this.textContent = originalText;
                const errorDiv = document.getElementById('otpEditError');
                errorDiv.textContent = 'An error occurred. Please try again.';
                errorDiv.style.display = 'block';
            }
        });
    }
    
    // Save profile button click handler
    saveProfileBtn.addEventListener('click', async function() {
        // Validate form
        if (!editProfileForm.checkValidity()) {
            editProfileForm.classList.add('was-validated');
            return;
        }
        
        // Get form values
        const name = document.getElementById('editName').value.trim();
        const phone = document.getElementById('editPhone').value.trim();
        const currentPassword = document.getElementById('currentPassword').value;
        const newPasswordValue = document.getElementById('newPassword').value;
        const confirmPasswordValue = document.getElementById('confirmPassword').value;
        
        // Check if phone changed and needs verification
        if (phone !== originalPhone && !phoneVerified) {
            showAlert('Please verify your new phone number first', 'danger');
            return;
        }
        
        // Validate password fields
        if ((currentPassword || newPasswordValue || confirmPasswordValue)) {
            if (!currentPassword) {
                showAlert('Please enter your current password to change password', 'danger');
                return;
            }
            if (!newPasswordValue) {
                showAlert('Please enter a new password', 'danger');
                return;
            }
            if (newPasswordValue !== confirmPasswordValue) {
                showAlert('New passwords do not match', 'danger');
                return;
            }
            if (newPasswordValue.length < 6) {
                showAlert('New password must be at least 6 characters', 'danger');
                return;
            }
        }
        
        // Disable button and show loading state
        saveProfileBtn.disabled = true;
        saveProfileBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        
        try {
            const response = await fetch('php/update_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    phone: phone,
                    currentPassword: currentPassword,
                    newPassword: newPasswordValue,
                    confirmPassword: confirmPasswordValue
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert(data.message, 'success');
                
                // Update the profile display
                updateProfileDisplay(data.data);
                
                // Reset form
                editProfileForm.classList.remove('was-validated');
                document.getElementById('currentPassword').value = '';
                document.getElementById('newPassword').value = '';
                document.getElementById('confirmPassword').value = '';
                
                // Close modal after 1.5 seconds
                setTimeout(() => {
                    const modalInstance = bootstrap.Modal.getInstance(editProfileModal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    location.reload(); // Reload to update all profile instances
                }, 1500);
            } else {
                showAlert(data.message || 'Failed to update profile', 'danger');
            }
        } catch (error) {
            console.error('Error updating profile:', error);
            showAlert('An error occurred while updating your profile. Please try again.', 'danger');
        } finally {
            saveProfileBtn.disabled = false;
            saveProfileBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Save Changes';
        }
    });
    
    // Show alert in modal
    function showAlert(message, type) {
        const alertDiv = document.getElementById('editProfileAlert');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        alertDiv.classList.remove('d-none');
        
        // Auto-hide success messages
        if (type === 'success') {
            setTimeout(() => {
                alertDiv.classList.add('d-none');
            }, 3000);
        }
    }
    
    // Update profile display in the page
    function updateProfileDisplay(data) {
        // Update name in profile card
        const profileUsernames = document.querySelectorAll('.profile-username');
        profileUsernames.forEach(elem => {
            elem.textContent = data.name;
        });
        
        // Update phone in profile fields
        const phoneInputs = document.querySelectorAll('.profile-field-input[value*="09"], .profile-field-input[value*="+63"]');
        phoneInputs.forEach(elem => {
            if (elem.previousElementSibling && elem.previousElementSibling.textContent.includes('Phone')) {
                elem.value = data.phone || 'Not set';
            }
        });
    }
    
    // Clear validation on modal close
    if (editProfileModal) {
        editProfileModal.addEventListener('hidden.bs.modal', function() {
            editProfileForm.classList.remove('was-validated');
            document.getElementById('editProfileAlert').classList.add('d-none');
            
            // Clear password fields
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
            confirmPassword.classList.remove('is-invalid', 'is-valid');
            
            // Reset phone verification state
            phoneVerified = false;
            editPhoneInput.readOnly = false;
            editPhoneInput.value = originalPhone;
            document.getElementById('phoneEditVerificationStatus').style.display = 'none';
            sendOtpEditBtn.textContent = 'Send OTP';
            editPhoneInput.dispatchEvent(new Event('input'));
        });
    }
    
    // Clear OTP modal on close
    const otpEditModalEl = document.getElementById('otpEditModal');
    if (otpEditModalEl) {
        otpEditModalEl.addEventListener('hidden.bs.modal', function() {
            ['otpEdit1', 'otpEdit2', 'otpEdit3', 'otpEdit4', 'otpEdit5', 'otpEdit6'].forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    input.value = '';
                    input.classList.remove('error');
                }
            });
            document.getElementById('otpEditError').style.display = 'none';
        });
    }
});