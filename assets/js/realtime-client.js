/**
 * Routa Real-time WebSocket Client
 * Lightweight JavaScript client for real-time updates
 * Auto-reconnection, heartbeat, and event handling
 */

class RoutaRealtime {
    constructor(url = 'ws://localhost:8080') {
        this.url = url;
        this.ws = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 10;
        this.reconnectDelay = 2000;
        this.heartbeatInterval = null;
        this.authenticated = false;
        this.eventHandlers = {};
        this.userId = null;
        this.userRole = null;
    }

    /**
     * Connect to WebSocket server
     */
    connect(userId, userRole) {
        this.userId = userId;
        this.userRole = userRole;

        try {
            this.ws = new WebSocket(this.url);
            
            this.ws.onopen = () => {
                                this.reconnectAttempts = 0;
                this.authenticate();
                this.startHeartbeat();
                this.trigger('connected');
            };

            this.ws.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    this.handleMessage(data);
                } catch (e) {
                                    }
            };

            this.ws.onerror = (error) => {
                                this.trigger('error', error);
            };

            this.ws.onclose = () => {
                                this.authenticated = false;
                this.stopHeartbeat();
                this.trigger('disconnected');
                this.reconnect();
            };

        } catch (error) {
                        this.reconnect();
        }
    }

    /**
     * Authenticate with server
     */
    authenticate() {
        this.send({
            type: 'auth',
            user_id: this.userId,
            role: this.userRole
        });
    }

    /**
     * Reconnect logic
     */
    reconnect() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
                        this.trigger('max_reconnect_reached');
            return;
        }

        this.reconnectAttempts++;
        const delay = this.reconnectDelay * this.reconnectAttempts;
        
        `);
        
        setTimeout(() => {
            this.connect(this.userId, this.userRole);
        }, delay);
    }

    /**
     * Start heartbeat to keep connection alive
     */
    startHeartbeat() {
        this.heartbeatInterval = setInterval(() => {
            if (this.ws && this.ws.readyState === WebSocket.OPEN) {
                this.send({ type: 'ping' });
            }
        }, 25000); // Every 25 seconds
    }

    /**
     * Stop heartbeat
     */
    stopHeartbeat() {
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
            this.heartbeatInterval = null;
        }
    }

    /**
     * Handle incoming messages
     */
    handleMessage(data) {
        switch (data.type) {
            case 'auth_success':
                this.authenticated = true;
                                this.trigger('authenticated');
                break;

            case 'auth_error':
                                this.trigger('auth_error', data);
                break;

            case 'ping':
                this.send({ type: 'pong' });
                break;

            case 'pong':
                // Heartbeat response
                break;

            case 'new_booking':
                this.trigger('new_booking', data);
                break;

            case 'booking_assigned':
                this.trigger('booking_assigned', data);
                break;

            case 'driver_accepted':
                this.trigger('driver_accepted', data);
                break;

            case 'driver_location':
                this.trigger('driver_location', data);
                break;

            case 'status_update':
                this.trigger('status_update', data);
                break;

            case 'ride_completed':
                this.trigger('ride_completed', data);
                break;

            default:
                this.trigger(data.type, data);
        }
    }

    /**
     * Send message to server
     */
    send(data) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(data));
        } else {
                    }
    }

    /**
     * Update location (for drivers)
     */
    updateLocation(lat, lng) {
        this.send({
            type: 'location_update',
            lat: lat,
            lng: lng
        });
    }

    /**
     * Update ride status
     */
    updateStatus(rideId, status) {
        this.send({
            type: 'status_update',
            ride_id: rideId,
            status: status
        });
    }

    /**
     * Register event handler
     */
    on(event, callback) {
        if (!this.eventHandlers[event]) {
            this.eventHandlers[event] = [];
        }
        this.eventHandlers[event].push(callback);
    }

    /**
     * Trigger event
     */
    trigger(event, data) {
        if (this.eventHandlers[event]) {
            this.eventHandlers[event].forEach(callback => callback(data));
        }
    }

    /**
     * Disconnect
     */
    disconnect() {
        this.stopHeartbeat();
        if (this.ws) {
            this.ws.close();
            this.ws = null;
        }
    }

    /**
     * Check connection status
     */
    isConnected() {
        return this.ws && this.ws.readyState === WebSocket.OPEN && this.authenticated;
    }
}

// Export for use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RoutaRealtime;
}
