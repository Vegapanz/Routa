/**
 * Routa Real-time Integration for Admin Dashboard
 * Auto-updates bookings, notifications, and statistics
 */

// Initialize real-time connection
let realtimeAdmin = null;
let adminUserId = null;

function initAdminRealtime(userId) {
    adminUserId = userId;
        realtimeAdmin = new RoutaRealtime('ws://127.0.0.1:8080');
    
    // Connect as admin
    realtimeAdmin.connect(userId, 'admin');
    
    // Setup event handlers
    realtimeAdmin.on('authenticated', () => {
                showToast('Real-time updates enabled', 'success');
    });
    
    realtimeAdmin.on('new_booking', (data) => {
                
        // Show notification
        showToast(`New booking from ${data.user_name || 'User'}`, 'info');
        
        // Play sound (optional)
        playNotificationSound();
        
        // Add new booking to pending list
        addNewBookingToList(data);
        
        // Update counter
        updatePendingBookingsCount();
    });
    
    realtimeAdmin.on('status_update', (data) => {
                
        // Update booking status in table
        updateBookingStatus(data.ride_id, data.status);
    });
    
    realtimeAdmin.on('driver_rejected', (data) => {
                
        // Show notification to admin
        showToast(`Driver rejected booking #${data.booking_id}. Booking returned to pending.`, 'warning');
        playNotificationSound();
        
        // Reload the page to show updated pending bookings
        setTimeout(() => {
            location.reload();
        }, 2000);
    });
    
    realtimeAdmin.on('disconnected', () => {
        showToast('Real-time connection lost. Reconnecting...', 'warning');
    });
    
    realtimeAdmin.on('error', () => {
            });
}

function addNewBookingToList(data) {
    const pendingSection = document.querySelector('#pending');
    if (!pendingSection) return;
    
    // Remove empty state if exists
    const emptyState = pendingSection.querySelector('.empty-state');
    if (emptyState) {
        emptyState.remove();
    }
    
    // Format the current time
    const now = new Date();
    const timeStr = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    
    // Create booking card matching original design
    const bookingCard = document.createElement('div');
    bookingCard.className = 'booking-card mb-3 booking-card-new';
    bookingCard.id = `booking-${data.booking_id}`;
    bookingCard.innerHTML = `
        <div class="booking-card-content">
            <div class="booking-header-row">
                <div class="booking-id">Booking ID: BK-${String(data.booking_id).padStart(3, '0')}</div>
                <span class="badge bg-warning text-dark px-3 py-2">Pending</span>
            </div>
            
            <div class="booking-info-grid">
                <div class="info-item">
                    <i class="bi bi-person-fill"></i>
                    <span class="info-label">Rider:</span>
                    <span class="info-value">${data.user_name || 'N/A'}</span>
                </div>
                <div class="info-item">
                    <i class="bi bi-telephone-fill"></i>
                    <span>${data.user_phone || 'N/A'}</span>
                </div>
            </div>

            <div class="booking-locations">
                <div class="location-item">
                    <i class="bi bi-geo-alt-fill text-success"></i>
                    <div>
                        <div class="location-label">FROM:</div>
                        <div class="location-value">${data.pickup.address || 'N/A'}</div>
                    </div>
                </div>
                <div class="location-item">
                    <i class="bi bi-geo-alt-fill text-danger"></i>
                    <div>
                        <div class="location-label">TO:</div>
                        <div class="location-value">${data.dropoff.address || 'N/A'}</div>
                    </div>
                </div>
            </div>

            <div class="booking-footer-row">
                <div class="booking-fare-large">₱${Math.round(parseFloat(data.fare))}</div>
                <div class="booking-time-info">
                    <i class="bi bi-clock"></i>
                    <span>Requested: ${timeStr}</span>
                </div>
            </div>
        </div>

        <div class="booking-actions">
            <button class="btn btn-confirm" onclick="confirmBooking('${data.booking_id}')">
                <i class="bi bi-check-circle-fill me-2"></i> Confirm & Assign Driver
            </button>
            <button class="btn btn-reject" onclick="rejectBooking('${data.booking_id}')">
                <i class="bi bi-x-circle-fill me-2"></i> Reject Booking
            </button>
        </div>
    `;
    
    // Insert at the top after section header
    const sectionHeader = pendingSection.querySelector('.section-header');
    if (sectionHeader && sectionHeader.nextSibling) {
        pendingSection.insertBefore(bookingCard, sectionHeader.nextSibling);
    } else {
        pendingSection.appendChild(bookingCard);
    }
    
    // Remove animation class after animation completes
    setTimeout(() => {
        bookingCard.classList.remove('booking-card-new');
    }, 1000);
}

function updateBookingStatus(bookingId, status) {
    const row = document.querySelector(`#booking-${bookingId}`);
    if (!row) return;
    
    const statusBadge = row.querySelector('td:nth-child(6) span');
    if (!statusBadge) return;
    
    // Update badge
    const statusMap = {
        'pending': { text: 'Pending', class: 'bg-warning' },
        'searching': { text: 'Searching', class: 'bg-info' },
        'driver_found': { text: 'Driver Found', class: 'bg-primary' },
        'confirmed': { text: 'Confirmed', class: 'bg-success' },
        'arrived': { text: 'Arrived', class: 'bg-success' },
        'in_progress': { text: 'In Progress', class: 'bg-primary' },
        'completed': { text: 'Completed', class: 'bg-secondary' },
        'cancelled': { text: 'Cancelled', class: 'bg-danger' },
        'rejected': { text: 'Rejected', class: 'bg-danger' }
    };
    
    const statusInfo = statusMap[status] || { text: status, class: 'bg-secondary' };
    statusBadge.className = `badge ${statusInfo.class}`;
    statusBadge.textContent = statusInfo.text;
    
    // Add highlight animation
    row.classList.add('table-row-highlight');
    setTimeout(() => {
        row.classList.remove('table-row-highlight');
    }, 2000);
    
    // Move to appropriate tab if completed or cancelled
    if (status === 'completed' || status === 'cancelled') {
        setTimeout(() => {
            row.remove();
            updatePendingBookingsCount();
        }, 3000);
    }
}

function updatePendingBookingsCount() {
    const count = document.querySelectorAll('#pending .booking-card').length;
    
    // Update "Pending Bookings" stat card (4th stat card)
    const statCards = document.querySelectorAll('.stat-card');
    if (statCards.length >= 4) {
        const pendingCountEl = statCards[3].querySelector('.stat-value');
        if (pendingCountEl) {
            pendingCountEl.textContent = count;
            
            // Add pulse animation
            pendingCountEl.classList.add('stat-pulse');
            setTimeout(() => {
                pendingCountEl.classList.remove('stat-pulse');
            }, 500);
        }
    }
}

function showToast(message, type = 'info') {
    // Create toast container if doesn't exist
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
        document.body.appendChild(container);
    }
    
    // Create toast
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show toast-notification`;
    toast.style.cssText = 'min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 10px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    container.appendChild(toast);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 150);
    }, 5000);
}

function playNotificationSound() {
    // Simple beep using Web Audio API
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
            }
}

// Add CSS for admin realtime animations
if (!document.getElementById('admin-realtime-styles')) {
    const adminStyles = document.createElement('style');
    adminStyles.id = 'admin-realtime-styles';
    adminStyles.textContent = `
        .booking-card-new {
            animation: slideInFromTop 0.5s ease-out;
        }
        
        .booking-card-new .booking-card-content {
            background-color: #e7f3ff !important;
            border: 2px solid #0d6efd;
        }
        
        @keyframes slideInFromTop {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .table-row-highlight {
            animation: highlightPulse 2s ease-in-out;
        }
        
        @keyframes highlightPulse {
            0%, 100% { background-color: transparent; }
            50% { background-color: #fff3cd; }
        }
        
        .stat-pulse {
            animation: pulse 0.5s ease-in-out;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); color: #0d6efd; }
        }
        
        .toast-notification {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    `;
    document.head.appendChild(adminStyles);
}
