<?php
/**
 * GPS Integration System for Web Application
 * Handles real-time and offline GPS tracking for web app
 */

class GPSIntegration {
    private $db;
    private $offlineStorage = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get current GPS location
     */
    public function getCurrentLocation($userId) {
        $sql = "SELECT * FROM gps_tracking 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 1";
        
        $location = $this->db->fetchOne($sql, [$userId]);
        
        if ($location) {
            return [
                'success' => true,
                'data' => [
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'address' => $location['address'],
                    'accuracy' => $location['accuracy'],
                    'timestamp' => $location['created_at'],
                    'is_real_time' => true
                ]
            ];
        }
        
        return [
            'success' => false,
            'message' => 'No GPS data available'
        ];
    }
    
    /**
     * Update GPS location (from web app)
     */
    public function updateLocation($userId, $latitude, $longitude, $accuracy = null, $address = null) {
        // Get address from coordinates if not provided
        if (!$address) {
            $address = $this->getAddressFromCoordinates($latitude, $longitude);
        }
        
        $locationData = [
            'user_id' => $userId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => $address,
            'accuracy' => $accuracy ?: 10,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $this->db->insert('gps_tracking', $locationData);
            
            return [
                'success' => true,
                'message' => 'Location updated successfully',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            // Store offline if database fails
            $this->storeOfflineLocation($userId, $locationData);
            
            return [
                'success' => false,
                'message' => 'Stored offline (no connection)',
                'offline' => true
            ];
        }
    }
    
    /**
     * Get location history
     */
    public function getLocationHistory($userId, $dateFrom = null, $dateTo = null) {
        $sql = "SELECT * FROM gps_tracking 
                WHERE user_id = ?";
        
        $params = [$userId];
        
        if ($dateFrom) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $dateTo;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $locations = $this->db->fetchAll($sql, $params);
        
        return [
            'success' => true,
            'data' => $locations,
            'count' => count($locations)
        ];
    }
    
    /**
     * Check if user is within geofence
     */
    public function checkGeofence($userId, $targetLat, $targetLng, $radius = 100) {
        $current = $this->getCurrentLocation($userId);
        
        if (!$current['success']) {
            return [
                'success' => false,
                'message' => 'No current location available'
            ];
        }
        
        $distance = $this->calculateDistance(
            $current['data']['latitude'],
            $current['data']['longitude'],
            $targetLat,
            $targetLng
        );
        
        $withinGeofence = $distance <= $radius;
        
        return [
            'success' => true,
            'data' => [
                'within_geofence' => $withinGeofence,
                'distance' => $distance,
                'radius' => $radius,
                'current_location' => $current['data'],
                'target_location' => [
                    'latitude' => $targetLat,
                    'longitude' => $targetLng
                ]
            ]
        ];
    }
    
    /**
     * Get nearby members or locations
     */
    public function getNearbyLocations($userId, $radius = 1000) {
        $current = $this->getCurrentLocation($userId);
        
        if (!$current['success']) {
            return [
                'success' => false,
                'message' => 'No current location available'
            ];
        }
        
        $lat = $current['data']['latitude'];
        $lng = $current['data']['longitude'];
        
        // Get nearby members
        $sql = "SELECT m.id, m.name, m.member_number, m.address, m.phone,
                       gt.latitude, gt.longitude, gt.created_at as last_seen,
                       (6371 * acos(cos(radians(?)) * cos(radians(gt.latitude)) * 
                        cos(radians(gt.longitude) - radians(?)) + 
                        sin(radians(?)) * sin(radians(gt.latitude)))) AS distance
                FROM members m
                LEFT JOIN gps_tracking gt ON m.user_id = gt.user_id
                WHERE gt.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                HAVING distance <= ?
                ORDER BY distance ASC";
        
        $nearby = $this->db->fetchAll($sql, [$lat, $lng, $lat, $radius / 1000]);
        
        return [
            'success' => true,
            'data' => $nearby,
            'current_location' => $current['data'],
            'search_radius' => $radius
        ];
    }
    
    /**
     * Get route for field operations
     */
    public function getRoute($userId, $memberIds) {
        $route = [];
        
        foreach ($memberIds as $memberId) {
            $sql = "SELECT m.id, m.name, m.member_number, m.address,
                           gt.latitude, gt.longitude
                    FROM members m
                    LEFT JOIN gps_tracking gt ON m.id = gt.member_id
                    WHERE m.id = ?";
            
            $member = $this->db->fetchOne($sql, [$memberId]);
            
            if ($member) {
                $route[] = $member;
            }
        }
        
        // Optimize route (simplified nearest neighbor)
        $optimizedRoute = $this->optimizeRoute($route);
        
        return [
            'success' => true,
            'data' => $optimizedRoute,
            'total_distance' => $this->calculateTotalDistance($optimizedRoute),
            'estimated_time' => $this->estimateRouteTime($optimizedRoute)
        ];
    }
    
    /**
     * Store offline location data
     */
    private function storeOfflineLocation($userId, $locationData) {
        if (!isset($this->offlineStorage[$userId])) {
            $this->offlineStorage[$userId] = [];
        }
        
        $this->offlineStorage[$userId][] = $locationData;
        
        // Limit offline storage to 100 items per user
        if (count($this->offlineStorage[$userId]) > 100) {
            array_shift($this->offlineStorage[$userId]);
        }
    }
    
    /**
     * Sync offline GPS data
     */
    public function syncOfflineData($userId) {
        if (!isset($this->offlineStorage[$userId]) || empty($this->offlineStorage[$userId])) {
            return [
                'success' => true,
                'message' => 'No offline data to sync'
            ];
        }
        
        $synced = 0;
        $failed = 0;
        
        foreach ($this->offlineStorage[$userId] as $location) {
            try {
                $this->db->insert('gps_tracking', $location);
                $synced++;
            } catch (Exception $e) {
                $failed++;
            }
        }
        
        // Clear offline storage after sync
        $this->offlineStorage[$userId] = [];
        
        return [
            'success' => true,
            'data' => [
                'synced' => $synced,
                'failed' => $failed,
                'total' => $synced + $failed
            ],
            'message' => "Synced {$synced} locations"
        ];
    }
    
    /**
     * Get address from coordinates (geocoding)
     */
    private function getAddressFromCoordinates($latitude, $longitude) {
        // Simplified address generation (would use real geocoding API)
        return "Lat: {$latitude}, Lng: {$longitude}";
    }
    
    /**
     * Calculate distance between two points (Haversine formula)
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2) {
        $earthRadius = 6371000; // Earth's radius in meters
        
        $latDiff = deg2rad($lat2 - $lat1);
        $lngDiff = deg2rad($lng2 - $lng1);
        
        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDiff / 2) * sin($lngDiff / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c; // Distance in meters
    }
    
    /**
     * Optimize route using nearest neighbor algorithm
     */
    private function optimizeRoute($locations) {
        if (empty($locations)) {
            return [];
        }
        
        $optimized = [];
        $remaining = $locations;
        $current = array_shift($remaining);
        $optimized[] = $current;
        
        while (!empty($remaining)) {
            $nearest = null;
            $minDistance = PHP_FLOAT_MAX;
            
            foreach ($remaining as $index => $location) {
                $distance = $this->calculateDistance(
                    $current['latitude'], $current['longitude'],
                    $location['latitude'], $location['longitude']
                );
                
                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $nearest = $index;
                }
            }
            
            if ($nearest !== null) {
                $current = $remaining[$nearest];
                $optimized[] = $current;
                unset($remaining[$nearest]);
            } else {
                break;
            }
        }
        
        return $optimized;
    }
    
    /**
     * Calculate total route distance
     */
    private function calculateTotalDistance($route) {
        $totalDistance = 0;
        
        for ($i = 0; $i < count($route) - 1; $i++) {
            $totalDistance += $this->calculateDistance(
                $route[$i]['latitude'], $route[$i]['longitude'],
                $route[$i + 1]['latitude'], $route[$i + 1]['longitude']
            );
        }
        
        return $totalDistance;
    }
    
    /**
     * Estimate route time (simplified)
     */
    private function estimateRouteTime($route) {
        $totalDistance = $this->calculateTotalDistance($route);
        $averageSpeed = 30; // km/h in city traffic
        $travelTime = ($totalDistance / 1000) / $averageSpeed * 60; // minutes
        $visitTime = count($route) * 15; // 15 minutes per visit
        
        return [
            'travel_time' => round($travelTime),
            'visit_time' => $visitTime,
            'total_time' => round($travelTime + $visitTime)
        ];
    }
}

?>
