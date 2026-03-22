// Enhanced GPS Tracking for Koperasi Keliling
class GPSTracker {
    constructor() {
        this.watchId = null;
        this.currentPosition = null;
        this.isTracking = false;
        this.locations = [];
    }

    // Start GPS tracking
    startTracking(staffId) {
        if (navigator.geolocation) {
            this.isTracking = true;
            
            this.watchId = navigator.geolocation.watchPosition(
                (position) => {
                    this.currentPosition = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                        timestamp: new Date().toISOString()
                    };
                    
                    // Send location to server
                    this.sendLocationToServer(staffId, this.currentPosition);
                    
                    // Add to locations array
                    this.locations.push(this.currentPosition);
                    
                    // Update UI
                    this.updateLocationDisplay(this.currentPosition);
                },
                (error) => {
                    console.error('GPS Error:', error);
                    this.handleGPSError(error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
            
            console.log('GPS tracking started for staff:', staffId);
        } else {
            alert('GPS tidak didukung di browser ini');
        }
    }

    // Stop GPS tracking
    stopTracking() {
        if (this.watchId) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
            this.isTracking = false;
            console.log('GPS tracking stopped');
        }
    }

    // Send location to server
    async sendLocationToServer(staffId, position) {
        try {
            const response = await fetch('/api/gps-tracking-enhanced.php?action=track', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    staff_id: staffId,
                    latitude: position.latitude,
                    longitude: position.longitude,
                    address: await this.getAddressFromCoords(position.latitude, position.longitude),
                    visit_type: this.getVisitType()
                })
            });
            
            const result = await response.json();
            if (result.success) {
                console.log('Location sent successfully');
            } else {
                console.error('Failed to send location:', result.error);
            }
        } catch (error) {
            console.error('Error sending location:', error);
        }
    }

    // Get address from coordinates (reverse geocoding)
    async getAddressFromCoords(lat, lng) {
        try {
            // Using Nominatim (OpenStreetMap) for reverse geocoding
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
            const data = await response.json();
            return data.display_name || 'Lokasi tidak diketahui';
        } catch (error) {
            console.error('Error getting address:', error);
            return 'Lokasi tidak diketahui';
        }
    }

    // Get visit type based on time and location
    getVisitType() {
        const hour = new Date().getHours();
        if (hour >= 8 && hour <= 9) return 'start';
        if (hour >= 16 && hour <= 17) return 'end';
        return 'visit';
    }

    // Update location display
    updateLocationDisplay(position) {
        const locationElement = document.getElementById('current-location');
        if (locationElement) {
            locationElement.innerHTML = `
                <div class="location-info">
                    <i class="fas fa-map-marker-alt text-success"></i>
                    <span class="coordinates">${position.latitude.toFixed(6)}, ${position.longitude.toFixed(6)}</span>
                    <span class="accuracy">Akurasi: ${position.accuracy.toFixed(0)}m</span>
                    <span class="timestamp">${new Date(position.timestamp).toLocaleTimeString()}</span>
                </div>
            `;
        }
    }

    // Handle GPS errors
    handleGPSError(error) {
        let errorMessage = '';
        switch (error.code) {
            case error.PERMISSION_DENIED:
                errorMessage = 'Akses GPS ditolak. Silakan aktifkan GPS.';
                break;
            case error.POSITION_UNAVAILABLE:
                errorMessage = 'Informasi lokasi tidak tersedia.';
                break;
            case error.TIMEOUT:
                errorMessage = 'Waktu habis untuk mendapatkan lokasi.';
                break;
            default:
                errorMessage = 'Terjadi kesalahan GPS.';
                break;
        }
        
        const errorElement = document.getElementById('gps-error');
        if (errorElement) {
            errorElement.innerHTML = `<div class="alert alert-warning">${errorMessage}</div>`;
        }
    }

    // Get today's location history
    async getLocationHistory(staffId) {
        try {
            const response = await fetch(`/api/gps-tracking-enhanced.php?action=history&staff_id=${staffId}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data;
            } else {
                throw new Error(result.error);
            }
        } catch (error) {
            console.error('Error getting location history:', error);
            return [];
        }
    }

    // Calculate route distance
    calculateRouteDistance(locations) {
        if (locations.length < 2) return 0;
        
        let totalDistance = 0;
        for (let i = 1; i < locations.length; i++) {
            totalDistance += this.calculateDistance(
                locations[i-1].latitude, locations[i-1].longitude,
                locations[i].latitude, locations[i].longitude
            );
        }
        
        return totalDistance;
    }

    // Calculate distance between two points
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Earth's radius in kilometers
        const dLat = this.toRad(lat2 - lat1);
        const dLon = this.toRad(lon2 - lon1);
        
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(this.toRad(lat1)) * Math.cos(this.toRad(lat2)) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    toRad(deg) {
        return deg * (Math.PI/180);
    }
}

// Route Optimization
class RouteOptimizer {
    constructor() {
        this.customers = [];
        this.optimizedRoute = [];
    }

    // Add customer to route
    addCustomer(customer) {
        this.customers.push(customer);
    }

    // Optimize route using nearest neighbor algorithm
    optimizeRoute() {
        if (this.customers.length === 0) return [];

        const route = [];
        const unvisited = [...this.customers];
        let current = unvisited.shift();
        route.push(current);

        while (unvisited.length > 0) {
            let nearestIndex = 0;
            let minDistance = Infinity;

            for (let i = 0; i < unvisited.length; i++) {
                const distance = this.calculateDistance(
                    current.latitude, current.longitude,
                    unvisited[i].latitude, unvisited[i].longitude
                );

                if (distance < minDistance) {
                    minDistance = distance;
                    nearestIndex = i;
                }
            }

            current = unvisited[nearestIndex];
            route.push(current);
            unvisited.splice(nearestIndex, 1);
        }

        this.optimizedRoute = route;
        return route;
    }

    // Calculate distance between two points
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Earth's radius in kilometers
        const dLat = this.toRad(lat2 - lat1);
        const dLon = this.toRad(lon2 - lon1);
        
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(this.toRad(lat1)) * Math.cos(this.toRad(lat2)) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    toRad(deg) {
        return deg * (Math.PI/180);
    }

    // Send optimized route to server
    async sendOptimizedRoute(staffId) {
        try {
            const response = await fetch('/api/gps-tracking-enhanced.php?action=route', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    staff_id: staffId,
                    customers: JSON.stringify(this.customers)
                })
            });
            
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Error sending route:', error);
            return { success: false, error: error.message };
        }
    }
}

// Initialize GPS tracking when page loads
let gpsTracker = null;
let routeOptimizer = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('gps-controls')) {
        gpsTracker = new GPSTracker();
        routeOptimizer = new RouteOptimizer();
        
        // Get current user info
        const currentUser = JSON.parse(localStorage.getItem('currentUser') || '{}');
        
        // Setup GPS controls
        setupGPSControls(currentUser.id);
        
        // Load today's route
        loadTodayRoute(currentUser.id);
    }
});

function setupGPSControls(staffId) {
    const startBtn = document.getElementById('start-tracking');
    const stopBtn = document.getElementById('stop-tracking');
    const optimizeBtn = document.getElementById('optimize-route');
    
    if (startBtn) {
        startBtn.addEventListener('click', () => {
            gpsTracker.startTracking(staffId);
            startBtn.disabled = true;
            stopBtn.disabled = false;
        });
    }
    
    if (stopBtn) {
        stopBtn.addEventListener('click', () => {
            gpsTracker.stopTracking();
            startBtn.disabled = false;
            stopBtn.disabled = true;
        });
    }
    
    if (optimizeBtn) {
        optimizeBtn.addEventListener('click', async () => {
            const customers = await getTodayCustomers();
            customers.forEach(customer => routeOptimizer.addCustomer(customer));
            
            const optimizedRoute = routeOptimizer.optimizeRoute();
            const result = await routeOptimizer.sendOptimizedRoute(staffId);
            
            if (result.success) {
                showRouteOnMap(optimizedRoute);
                showNotification('Rute berhasil dioptimasi', 'success');
            } else {
                showNotification('Gagal mengoptimasi rute', 'error');
            }
        });
    }
}

async function loadTodayRoute(staffId) {
    try {
        const response = await fetch(`/api/analytics-engine.php?action=route_efficiency&date=${new Date().toISOString().split('T')[0]}`);
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            const routeData = result.data[0];
            updateRouteDisplay(routeData);
        }
    } catch (error) {
        console.error('Error loading route:', error);
    }
}

function showRouteOnMap(route) {
    // This would integrate with a map library like Leaflet or Google Maps
    console.log('Showing optimized route on map:', route);
    
    const routeElement = document.getElementById('route-display');
    if (routeElement) {
        routeElement.innerHTML = `
            <div class="route-summary">
                <h6>Rute Dioptimasi (${route.length} titik)</h6>
                <div class="route-list">
                    ${route.map((customer, index) => `
                        <div class="route-point">
                            <span class="point-number">${index + 1}</span>
                            <span class="customer-name">${customer.name}</span>
                            <span class="customer-address">${customer.address}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
}

function updateRouteDisplay(routeData) {
    const routeElement = document.getElementById('route-summary');
    if (routeElement) {
        routeElement.innerHTML = `
            <div class="route-stats">
                <div class="stat-item">
                    <span class="stat-label">Kunjungan</span>
                    <span class="stat-value">${routeData.visit_count || 0}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Jarak</span>
                    <span class="stat-value">${(routeData.total_distance || 0).toFixed(1)} km</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Efisiensi</span>
                    <span class="stat-value">${(routeData.efficiency_score || 0).toFixed(1)}%</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Penagihan</span>
                    <span class="stat-value">Rp ${(routeData.collection_amount || 0).toLocaleString('id-ID')}</span>
                </div>
            </div>
        `;
    }
}

async function getTodayCustomers() {
    // This would get today's customer list from the API
    return [
        { id: 1, name: 'Budi Santoso', address: 'Jl. Merdeka No. 123', latitude: -6.2088, longitude: 106.8456 },
        { id: 2, name: 'Siti Nurhaliza', address: 'Jl. Sudirman No. 456', latitude: -6.1751, longitude: 106.8650 },
        { id: 3, name: 'Ahmad Fauzi', address: 'Jl. Gatot Subroto No. 789', latitude: -6.1944, longitude: 106.8229 }
    ];
}

function showNotification(message, type) {
    // Show notification to user
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    
    const container = document.getElementById('notifications');
    if (container) {
        container.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
}
