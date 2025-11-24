/**
 * Driver Dashboard JavaScript Module
 * Handles all driver dashboard interactions and AJAX requests
 */

const DriverDashboard = {
    driverMap: null,
    currentMarkers: [],
    currentRoute: null,
    
    /**
     * Initialize the driver dashboard
     */
    init() {
        this.bindEvents();
        this.setupAutoRefresh();
        this.initializeMap();
        console.log('Driver Dashboard initialized');
    },

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Status toggle
        const statusToggle = document.querySelector('.status-toggle-switch');
        if (statusToggle) {
            statusToggle.addEventListener('click', () => this.toggleStatus());
        }

        // View on Map buttons
        document.querySelectorAll('.view-map-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const btn = e.currentTarget;
                const rideId = btn.dataset.rideId;
                const pickupLocation = btn.dataset.pickupLocation;
                const dropoffLocation = btn.dataset.dropoffLocation;
                const pickupLat = btn.dataset.pickupLat;
                const pickupLng = btn.dataset.pickupLng;
                const dropoffLat = btn.dataset.dropoffLat;
                const dropoffLng = btn.dataset.dropoffLng;
                
                console.log('View Map clicked:', { rideId, pickupLat, pickupLng, dropoffLat, dropoffLng });
                
                this.showRideOnMap(rideId, pickupLocation, dropoffLocation, pickupLat, pickupLng, dropoffLat, dropoffLng);
            });
        });

        // Arrived buttons
        document.querySelectorAll('[data-action="arrived"]').forEach(button => {
            button.addEventListener('click', (e) => {
                const bookingId = e.currentTarget.dataset.bookingId;
                this.markArrived(bookingId);
            });
        });

        // Start ride buttons
        document.querySelectorAll('[data-action="start-ride"]').forEach(button => {
            button.addEventListener('click', (e) => {
                const bookingId = e.currentTarget.dataset.bookingId;
                this.startTrip(bookingId);  
            });
        });

        // Complete ride buttons
        document.querySelectorAll('[data-action="complete-ride"]').forEach(button => {
            button.addEventListener('click', (e) => {
                const bookingId = e.currentTarget.dataset.bookingId;
                this.completeTrip(bookingId);
            });
        });

        // Tab navigation
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', (e) => {
                this.switchTab(e.currentTarget);
            });
        });
    },

    /**
     * Toggle driver online/offline status
     */
    toggleStatus() {
        const toggle = document.querySelector('.status-toggle-switch');
        const statusLabel = document.querySelector('.status-label');
        const currentStatus = toggle.classList.contains('online');
        const newStatus = currentStatus ? 'offline' : 'available';

        this.updateStatus(newStatus)
            .then(success => {
                if (success) {
                    toggle.classList.toggle('online');
                    statusLabel.classList.toggle('online');
                    statusLabel.textContent = currentStatus ? 'Offline' : 'Online';
                    
                    // Update header subtitle
                    const subtitle = document.querySelector('.driver-subtitle');
                    if (subtitle) {
                        subtitle.textContent = currentStatus 
                            ? "You're offline" 
                            : "You're online and ready to accept rides";
                    }
                }
            });
    },

    /**
     * Update driver status via AJAX
     */
    async updateStatus(status) {
        try {
            const response = await fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_status&status=${status}`
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Status updated successfully', 'success');
                return true;
            } else {
                this.showNotification('Failed to update status: ' + data.message, 'error');
                return false;
            }
        } catch (error) {
            console.error('Error updating status:', error);
            this.showNotification('Network error. Please try again.', 'error');
            return false;
        }
    },

    /**
     * Accept a ride request
     */
    async acceptRide(bookingId) {
        console.log('acceptRide called with bookingId:', bookingId);
        
        const confirmed = await this.showConfirmModal(
            'Accept Ride',
            'Do you want to accept this ride request?',
            'Accept',
            'Cancel'
        );
        
        if (!confirmed) {
            console.log('User cancelled confirmation');
            return;
        }

        try {
            console.log('Sending accept request...');
            const response = await fetch('php/driver_api.php?action=accept_ride', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ride_id: bookingId })
            });

            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Response data:', data);
            
            if (data.success) {
                this.showNotification('Ride accepted! Navigate to pickup location.', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                this.showNotification('Failed to accept ride: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error accepting ride:', error);
            this.showNotification('Network error. Please try again.', 'error');
        }
    },

    /**
     * Reject a ride request
     */
    async rejectRide(bookingId) {
        console.log('rejectRide called with bookingId:', bookingId);
        
        const confirmed = await this.showConfirmModal(
            'Reject Ride',
            'Are you sure you want to reject this ride request?',
            'Reject',
            'Cancel',
            'danger'
        );
        
        if (!confirmed) {
            console.log('User cancelled rejection');
            return;
        }
        
        const reason = 'Driver declined';

        try {
            console.log('Sending reject request...');
            const response = await fetch('php/driver_api.php?action=reject_ride', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    ride_id: bookingId,
                    reason: reason
                })
            });

            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Response data:', data);
            
            if (data.success) {
                this.showNotification('Ride rejected.', 'info');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                this.showNotification('Failed to reject ride: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error rejecting ride:', error);
            this.showNotification('Network error. Please try again.', 'error');
        }
    },

    /**
     * Mark as arrived at pickup location
     */
    async markArrived(bookingId) {
        const confirmed = await this.showConfirmModal(
            'Mark as Arrived',
            'Have you arrived at the pickup location?',
            'Yes, I\'ve Arrived',
            'Not Yet'
        );
        
        if (!confirmed) return;

        try {
            const response = await fetch('php/driver_api.php?action=arrived', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ride_id: bookingId })
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Marked as arrived! Wait for passenger.', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                this.showNotification('Failed to update status: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error marking as arrived:', error);
            this.showNotification('Network error. Please try again.', 'error');
        }
    },

    /**
     * Start a trip
     */
    async startTrip(bookingId) {
        const confirmed = await this.showConfirmModal(
            'Start Ride',
            'Is the passenger on board? Ready to start the trip?',
            'Start Ride',
            'Cancel'
        );
        
        if (!confirmed) return;

        try {
            const response = await fetch('php/driver_api.php?action=start_trip', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ride_id: bookingId })
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Trip started! Drive safely to destination.', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                this.showNotification('Failed to start trip: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error starting trip:', error);
            this.showNotification('Network error. Please try again.', 'error');
        }
    },

    /**
     * Complete a trip
     */
    async completeTrip(bookingId) {
        const confirmed = await this.showConfirmModal(
            'Drop Off Complete',
            'Have you reached the destination? Mark this trip as completed?',
            'Drop Off',
            'Cancel'
        );
        
        if (!confirmed) return;

        try {
            const response = await fetch('php/driver_api.php?action=complete_trip', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ride_id: bookingId })
            });

            const data = await response.json();
            
            if (data.success) {
                const earnings = data.earnings || 0;
                this.showNotification(`Trip completed! You earned ₱${earnings.toFixed(2)}`, 'success');
                setTimeout(() => window.location.reload(), 2000);
            } else {
                this.showNotification('Failed to complete trip: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error completing trip:', error);
            this.showNotification('Network error. Please try again.', 'error');
        }
    },

    /**
     * Switch between tabs
     */
    switchTab(clickedTab) {
        // Remove active class from all tabs
        document.querySelectorAll('.tab-button').forEach(tab => {
            tab.classList.remove('active');
        });

        // Add active class to clicked tab
        clickedTab.classList.add('active');

        // Get tab name
        const tabName = clickedTab.dataset.tab;

        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.style.display = 'none';
        });

        // Show selected tab content
        const targetContent = document.querySelector(`[data-tab-content="${tabName}"]`);
        if (targetContent) {
            targetContent.style.display = 'block';
        }
    },

    /**
     * Show notification message
     */
    showNotification(message, type = 'info') {
        // Remove existing notifications
        const existing = document.querySelector('.notification-toast');
        if (existing) {
            existing.remove();
        }

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification-toast notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="bi bi-${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
        `;

        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
        `;

        document.body.appendChild(notification);

        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    },

    /**
     * Get icon for notification type
     */
    getNotificationIcon(type) {
        switch(type) {
            case 'success': return 'check-circle-fill';
            case 'error': return 'x-circle-fill';
            case 'warning': return 'exclamation-triangle-fill';
            default: return 'info-circle-fill';
        }
    },

    /**
     * Setup auto-refresh for new ride assignments
     */
    setupAutoRefresh() {
        // Check for new rides every 30 seconds
        setInterval(() => {
            this.checkForNewRides();
        }, 30000);
    },

    /**
     * Check for new ride assignments
     */
    async checkForNewRides() {
        try {
            const response = await fetch(window.location.href + '?check_rides=1');
            const data = await response.json();
            
            if (data.newRides > 0) {
                this.showNotification(`You have ${data.newRides} new ride assignment(s)!`, 'info');
                // Optionally play a sound or show a more prominent notification
                this.playNotificationSound();
            }
        } catch (error) {
            console.error('Error checking for new rides:', error);
        }
    },

    /**
     * Play notification sound
     */
    playNotificationSound() {
        // Create audio element for notification sound
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZQA0PVa7n77BdGAg+ltryxnMpBSh+zPLaizsIGGS57OihUBELTKXh8bllHAU2jdXzz3swBSJ0xO/glEILElyx6OyrWBUIOpvY88p5LQUZD');
        audio.volume = 0.3;
        audio.play().catch(err => console.log('Audio play failed:', err));
    },

    /**
     * Format currency
     */
    formatCurrency(amount) {
        return '₱' + Number(amount).toLocaleString('en-PH', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    },

    /**
     * Format time ago
     */
    timeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);

        const intervals = {
            year: 31536000,
            month: 2592000,
            week: 604800,
            day: 86400,
            hour: 3600,
            minute: 60,
            second: 1
        };

        for (const [unit, secondsInUnit] of Object.entries(intervals)) {
            const interval = Math.floor(seconds / secondsInUnit);
            if (interval >= 1) {
                return interval === 1 ? `1 ${unit} ago` : `${interval} ${unit}s ago`;
            }
        }

        return 'just now';
    },

    /**
     * Initialize map
     */
    initializeMap() {
        const mapElement = document.getElementById('driverMap');
        if (!mapElement) {
            console.log('Map element not found - no active rides');
            return;
        }

        // Default center: Manila, Philippines
        const manilaCoords = [14.5995, 120.9842];
        
        // Create map
        this.driverMap = L.map('driverMap').setView(manilaCoords, 12);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(this.driverMap);
        
        // Fix map display issues
        setTimeout(() => {
            this.driverMap.invalidateSize();
        }, 100);
        
        // Load all active rides onto map
        this.loadAllRidesOnMap();
        
        console.log('Driver map initialized');
    },

    /**
     * Load all active rides onto the map
     */
    loadAllRidesOnMap() {
        if (!this.driverMap) return;
        
        // Get all view map buttons
        const viewMapButtons = document.querySelectorAll('.view-map-btn');
        const bounds = [];
        
        console.log(`Found ${viewMapButtons.length} rides to display on map`);
        
        viewMapButtons.forEach(btn => {
            try {
                const pickupLat = btn.dataset.pickupLat;
                const pickupLng = btn.dataset.pickupLng;
                const dropoffLat = btn.dataset.dropoffLat;
                const dropoffLng = btn.dataset.dropoffLng;
                const pickupLocation = btn.dataset.pickupLocation;
                const dropoffLocation = btn.dataset.dropoffLocation;
                
                // Validate coordinates
                const validPickupLat = pickupLat && pickupLat !== '' && !isNaN(parseFloat(pickupLat)) ? parseFloat(pickupLat) : null;
                const validPickupLng = pickupLng && pickupLng !== '' && !isNaN(parseFloat(pickupLng)) ? parseFloat(pickupLng) : null;
                const validDropoffLat = dropoffLat && dropoffLat !== '' && !isNaN(parseFloat(dropoffLat)) ? parseFloat(dropoffLat) : null;
                const validDropoffLng = dropoffLng && dropoffLng !== '' && !isNaN(parseFloat(dropoffLng)) ? parseFloat(dropoffLng) : null;
                
                if (validPickupLat && validPickupLng) {
                    bounds.push([validPickupLat, validPickupLng]);
                    
                    // Add pickup marker
                    const pickupMarker = L.marker([validPickupLat, validPickupLng], {
                        icon: L.divIcon({
                            html: '<div style="background-color: #10b981; color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-size: 16px; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">📍</div>',
                            iconSize: [28, 28],
                            iconAnchor: [14, 14],
                            className: 'custom-marker'
                        })
                    }).addTo(this.driverMap);
                    
                    pickupMarker.bindPopup(`<b>Pickup</b><br>${pickupLocation || 'Pickup Location'}`);
                    this.currentMarkers.push(pickupMarker);
                }
                
                if (validDropoffLat && validDropoffLng) {
                    bounds.push([validDropoffLat, validDropoffLng]);
                    
                    // Add dropoff marker
                    const dropoffMarker = L.marker([validDropoffLat, validDropoffLng], {
                        icon: L.divIcon({
                            html: '<div style="background-color: #ef4444; color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-size: 16px; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">🎯</div>',
                            iconSize: [28, 28],
                            iconAnchor: [14, 14],
                            className: 'custom-marker'
                        })
                    }).addTo(this.driverMap);
                    
                    dropoffMarker.bindPopup(`<b>Drop-off</b><br>${dropoffLocation || 'Drop-off Location'}`);
                    this.currentMarkers.push(dropoffMarker);
                }
            } catch (error) {
                console.error('Error adding marker:', error);
            }
        });
        
        // Fit map to show all markers
        if (bounds.length > 0) {
            this.driverMap.fitBounds(bounds, { padding: [50, 50] });
            console.log(`Map fitted to ${bounds.length} locations`);
        } else {
            console.log('No valid coordinates found - rides may not have location data');
        }
    },

    /**
     * Show specific ride on map
     */
    async showRideOnMap(rideId, pickupLocation, dropoffLocation, pickupLat, pickupLng, dropoffLat, dropoffLng) {
        console.log('showRideOnMap called with data:', { 
            rideId, 
            pickupLocation, 
            dropoffLocation,
            pickupLat, 
            pickupLng, 
            dropoffLat, 
            dropoffLng 
        });
        
        // Validate and convert coordinates
        const validPickupLat = pickupLat && pickupLat !== '' && pickupLat !== 'null' && !isNaN(parseFloat(pickupLat)) ? parseFloat(pickupLat) : null;
        const validPickupLng = pickupLng && pickupLng !== '' && pickupLng !== 'null' && !isNaN(parseFloat(pickupLng)) ? parseFloat(pickupLng) : null;
        const validDropoffLat = dropoffLat && dropoffLat !== '' && dropoffLat !== 'null' && !isNaN(parseFloat(dropoffLat)) ? parseFloat(dropoffLat) : null;
        const validDropoffLng = dropoffLng && dropoffLng !== '' && dropoffLng !== 'null' && !isNaN(parseFloat(dropoffLng)) ? parseFloat(dropoffLng) : null;
        
        console.log('Validated coordinates:', { validPickupLat, validPickupLng, validDropoffLat, validDropoffLng });
        
        if (!validPickupLat || !validPickupLng || !validDropoffLat || !validDropoffLng) {
            this.showNotification('Location coordinates are missing. Please ensure locations were selected from the map when booking.', 'warning');
            return;
        }
        
        // Initialize map if not already done
        if (!this.driverMap) {
            console.log('Map not initialized, initializing now...');
            this.initializeMap();
            // Wait for map to be ready
            await new Promise(resolve => setTimeout(resolve, 300));
        }
        
        if (!this.driverMap) {
            console.error('Failed to initialize map');
            this.showNotification('Map failed to load', 'error');
            return;
        }
        
        // Clear existing markers and route
        this.clearMapMarkers();
        
        // Add pickup marker
        const pickupMarker = L.marker([validPickupLat, validPickupLng], {
            icon: L.divIcon({
                html: '<div style="background-color: #10b981; color: white; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 3px solid white; box-shadow: 0 3px 10px rgba(0,0,0,0.4);">📍</div>',
                iconSize: [36, 36],
                iconAnchor: [18, 18],
                className: 'custom-marker'
            })
        }).addTo(this.driverMap);
        
        pickupMarker.bindPopup(`<b>Pickup</b><br>${pickupLocation || 'Pickup Location'}`).openPopup();
        this.currentMarkers.push(pickupMarker);
        
        // Add dropoff marker
        const dropoffMarker = L.marker([validDropoffLat, validDropoffLng], {
            icon: L.divIcon({
                html: '<div style="background-color: #ef4444; color: white; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 3px solid white; box-shadow: 0 3px 10px rgba(0,0,0,0.4);">🎯</div>',
                iconSize: [36, 36],
                iconAnchor: [18, 18],
                className: 'custom-marker'
            })
        }).addTo(this.driverMap);
        
        dropoffMarker.bindPopup(`<b>Drop-off</b><br>${dropoffLocation || 'Drop-off Location'}`);
        this.currentMarkers.push(dropoffMarker);
        
        // Draw route
        await this.drawRoute(validPickupLat, validPickupLng, validDropoffLat, validDropoffLng);
        
        // Fit map to show both markers
        const group = L.featureGroup(this.currentMarkers);
        this.driverMap.fitBounds(group.getBounds(), { padding: [50, 50] });
        
        // Scroll to map smoothly
        const mapElement = document.getElementById('driverMap');
        if (mapElement) {
            mapElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        this.showNotification('Route displayed on map', 'success');
    },

    /**
     * Draw route between two points using OSRM
     */
    async drawRoute(pickupLat, pickupLng, dropoffLat, dropoffLng) {
        try {
            // Use OSRM for routing
            const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${pickupLng},${pickupLat};${dropoffLng},${dropoffLat}?overview=full&geometries=geojson&steps=true`;
            
            const response = await fetch(osrmUrl);
            const data = await response.json();
            
            if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
                const route = data.routes[0];
                const coordinates = route.geometry.coordinates;
                
                // Convert coordinates from [lng, lat] to [lat, lng]
                const latlngs = coordinates.map(coord => [coord[1], coord[0]]);
                
                // Draw the route
                this.currentRoute = L.polyline(latlngs, {
                    color: '#10b981',
                    weight: 5,
                    opacity: 0.8,
                    lineJoin: 'round'
                }).addTo(this.driverMap);
                
                // Calculate route info
                const distanceKm = (route.distance / 1000).toFixed(2);
                const durationMin = Math.round(route.duration / 60);
                
                // Add route info popup
                const midPoint = Math.floor(latlngs.length / 2);
                const popup = L.popup({
                    closeButton: false,
                    autoClose: false,
                    closeOnClick: false,
                    className: 'route-info-popup'
                })
                .setLatLng(latlngs[midPoint])
                .setContent(`
                    <div style="text-align: center; font-size: 12px; font-weight: 600;">
                        <div style="color: #10b981; margin-bottom: 4px;">🚗 Fastest Route</div>
                        <div>📏 ${distanceKm} km</div>
                        <div>⏱️ ~${durationMin} mins</div>
                    </div>
                `)
                .addTo(this.driverMap);
                
                console.log(`Route drawn: ${distanceKm} km, ~${durationMin} mins`);
            }
        } catch (error) {
            console.error('Error drawing route:', error);
            // Draw straight line as fallback
            this.currentRoute = L.polyline([
                [pickupLat, pickupLng],
                [dropoffLat, dropoffLng]
            ], {
                color: '#10b981',
                weight: 4,
                opacity: 0.7,
                dashArray: '10, 10'
            }).addTo(this.driverMap);
        }
    },

    /**
     * Clear map markers and route
     */
    clearMapMarkers() {
        if (!this.driverMap) return;
        
        // Remove markers
        this.currentMarkers.forEach(marker => {
            this.driverMap.removeLayer(marker);
        });
        this.currentMarkers = [];
        
        // Remove route
        if (this.currentRoute) {
            this.driverMap.removeLayer(this.currentRoute);
            this.currentRoute = null;
        }
    },

    /**
     * Show custom confirmation modal
     */
    showConfirmModal(title, message, confirmText = 'Confirm', cancelText = 'Cancel', style = 'primary') {
        return new Promise((resolve) => {
            // Remove any existing modal
            const existingModal = document.getElementById('confirmModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Create modal HTML
            const modalHTML = `
                <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="confirmModalLabel">${title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ${message}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${cancelText}</button>
                                <button type="button" class="btn btn-${style}" id="confirmButton">${confirmText}</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Add modal to page
            document.body.insertAdjacentHTML('beforeend', modalHTML);

            // Get modal element
            const modalElement = document.getElementById('confirmModal');
            const modal = new bootstrap.Modal(modalElement);
            
            let resolved = false;

            // Handle confirm button click
            document.getElementById('confirmButton').addEventListener('click', () => {
                resolved = true;
                modal.hide();
                setTimeout(() => {
                    resolve(true);
                }, 100);
            });

            // Handle cancel/close
            modalElement.addEventListener('hidden.bs.modal', () => {
                setTimeout(() => {
                    modalElement.remove();
                    if (!resolved) {
                        resolve(false);
                    }
                }, 100);
            }, { once: true });

            // Show modal
            modal.show();
        });
    }
};

// Add CSS animations and modal styling
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    .notification-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .notification-content i {
        font-size: 1.25rem;
    }

    /* Custom Modal Styling - Match Routa Branding */
    #confirmModal .modal-content {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        overflow: hidden;
    }

    #confirmModal .modal-header {
        background: linear-gradient(135deg, #00b96b 0%, #009d5a 100%);
        color: white;
        border: none;
        padding: 1.5rem;
    }

    #confirmModal .modal-title {
        font-family: 'Sora', sans-serif;
        font-weight: 700;
        font-size: 1.25rem;
        color: white;
    }

    #confirmModal .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.8;
    }

    #confirmModal .btn-close:hover {
        opacity: 1;
    }

    #confirmModal .modal-body {
        padding: 2rem 1.5rem;
        font-family: 'DM Sans', sans-serif;
        font-size: 1rem;
        color: #091133;
        line-height: 1.6;
    }

    #confirmModal .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 1.25rem 1.5rem;
        gap: 0.75rem;
    }

    #confirmModal .btn {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        border-radius: 50px;
        padding: 0.625rem 1.75rem;
        border: none;
        transition: all 0.3s ease;
        min-width: 100px;
    }

    #confirmModal .btn-primary {
        background: linear-gradient(135deg, #00b96b 0%, #009d5a 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(0, 185, 107, 0.3);
    }

    #confirmModal .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 185, 107, 0.4);
        background: linear-gradient(135deg, #009d5a 0%, #008a4d 100%);
    }

    #confirmModal .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    #confirmModal .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    }

    #confirmModal .btn-secondary {
        background-color: #f3f4f6;
        color: #6b7280;
        border: 1px solid #e5e7eb;
    }

    #confirmModal .btn-secondary:hover {
        background-color: #e5e7eb;
        color: #4b5563;
        transform: translateY(-1px);
    }

    /* Modal animation */
    #confirmModal.fade .modal-dialog {
        transition: transform 0.3s ease-out;
    }

    #confirmModal.show .modal-dialog {
        transform: scale(1);
    }
`;
document.head.appendChild(style);

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => DriverDashboard.init());
} else {
    DriverDashboard.init();
}

// Export for use in other modules
window.DriverDashboard = DriverDashboard;

// Global functions for onclick handlers
window.acceptRide = (bookingId) => DriverDashboard.acceptRide(bookingId);
window.rejectRide = (bookingId) => DriverDashboard.rejectRide(bookingId);
window.showRideOnMap = (rideId, pickupLocation, dropoffLocation, pickupLat, pickupLng, dropoffLat, dropoffLng) => 
    DriverDashboard.showRideOnMap(rideId, pickupLocation, dropoffLocation, pickupLat, pickupLng, dropoffLat, dropoffLng);
