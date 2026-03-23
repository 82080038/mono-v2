-- GPS Tracking Database Schema for KSP Lam Gabe Jaya

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS ksp_lamgabejaya_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ksp_lamgabejaya_v2;

-- GPS Logs Table
CREATE TABLE IF NOT EXISTS gps_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    accuracy DECIMAL(8, 2) DEFAULT NULL,
    altitude DECIMAL(8, 2) DEFAULT NULL,
    speed DECIMAL(6, 2) DEFAULT NULL,
    heading DECIMAL(5, 2) DEFAULT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_staff_timestamp (staff_id, timestamp),
    INDEX idx_coordinates (latitude, longitude),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE
);

-- GPS Tracking Sessions Table
CREATE TABLE IF NOT EXISTS gps_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    member_id INT DEFAULT NULL,
    purpose VARCHAR(255) DEFAULT NULL,
    route_plan TEXT DEFAULT NULL,
    status ENUM('active', 'completed', 'paused', 'cancelled') DEFAULT 'active',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP DEFAULT NULL,
    start_latitude DECIMAL(10, 8) DEFAULT NULL,
    start_longitude DECIMAL(11, 8) DEFAULT NULL,
    end_latitude DECIMAL(10, 8) DEFAULT NULL,
    end_longitude DECIMAL(11, 8) DEFAULT NULL,
    distance_km DECIMAL(8, 3) DEFAULT 0,
    duration_minutes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_staff_status (staff_id, status),
    INDEX idx_started_at (started_at),
    
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL
);

-- Geofence Areas Table
CREATE TABLE IF NOT EXISTS geofence_areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    radius DECIMAL(8, 2) NOT NULL DEFAULT 100, -- in meters
    type ENUM('office', 'branch', 'member_area', 'restricted', 'safe_zone') DEFAULT 'office',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_coordinates (latitude, longitude),
    INDEX idx_type_active (type, is_active)
);

-- Geofence Logs Table
CREATE TABLE IF NOT EXISTS geofence_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    geofence_id INT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    action ENUM('entry', 'exit') NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_staff_timestamp (staff_id, timestamp),
    INDEX idx_geofence_action (geofence_id, action),
    
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (geofence_id) REFERENCES geofence_areas(id) ON DELETE CASCADE
);

-- GPS Routes Table (for predefined routes)
CREATE TABLE IF NOT EXISTS gps_routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    staff_id INT DEFAULT NULL,
    waypoints JSON DEFAULT NULL, -- Array of {lat, lng, name} objects
    distance_km DECIMAL(8, 3) DEFAULT 0,
    estimated_duration_minutes INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_staff_active (staff_id, is_active),
    
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE SET NULL
);

-- GPS Route Points Table (for route tracking)
CREATE TABLE IF NOT EXISTS gps_route_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_id INT NOT NULL,
    point_order INT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    name VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    is_checkpoint BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_route_order (route_id, point_order),
    
    FOREIGN KEY (route_id) REFERENCES gps_routes(id) ON DELETE CASCADE
);

-- Users Table (if not exists)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'member') DEFAULT 'member',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_role_active (role, is_active),
    INDEX idx_username (username),
    INDEX idx_email (email)
);

-- Members Table (if not exists)
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    member_number VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    latitude DECIMAL(10, 8) DEFAULT NULL,
    longitude DECIMAL(11, 8) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_member_number (member_number),
    INDEX idx_coordinates (latitude, longitude),
    INDEX idx_active (is_active),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert sample data for testing
INSERT IGNORE INTO users (id, username, email, password_hash, full_name, role, is_active) VALUES
(1, 'admin', 'admin@ksp-lamgabe.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', TRUE),
(2, 'staff1', 'staff1@ksp-lamgabe.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff One', 'staff', TRUE),
(3, 'member1', 'member1@ksp-lamgabe.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Member One', 'member', TRUE);

INSERT IGNORE INTO members (id, user_id, member_number, full_name, email, phone, latitude, longitude) VALUES
(1, 3, 'M001', 'Member One', 'member1@ksp-lamgabe.com', '08123456789', -6.2088, 106.8456),
(2, NULL, 'M002', 'Member Two', 'member2@ksp-lamgabe.com', '08234567890', -6.1751, 106.8650),
(3, NULL, 'M003', 'Member Three', 'member3@ksp-lamgabe.com', '08345678901', -6.2297, 106.8295);

INSERT IGNORE INTO geofence_areas (id, name, description, latitude, longitude, radius, type, is_active) VALUES
(1, 'KSP Main Office', 'Main office location', -6.2088, 106.8456, 200, 'office', TRUE),
(2, 'Branch Office 1', 'First branch office', -6.1751, 106.8650, 150, 'branch', TRUE),
(3, 'Restricted Area', 'Restricted zone for staff', -6.2297, 106.8295, 100, 'restricted', TRUE);

-- Create indexes for performance optimization
CREATE INDEX IF NOT EXISTS idx_gps_logs_staff_time ON gps_logs(staff_id, created_at);
CREATE INDEX IF NOT EXISTS idx_gps_tracking_staff_status ON gps_tracking(staff_id, status);
CREATE INDEX IF NOT EXISTS idx_geofence_logs_staff_time ON geofence_logs(staff_id, timestamp);

-- Create view for GPS tracking summary
CREATE OR REPLACE VIEW gps_tracking_summary AS
SELECT 
    gt.id,
    gt.staff_id,
    u.username,
    u.full_name as staff_name,
    gt.member_id,
    m.full_name as member_name,
    gt.purpose,
    gt.status,
    gt.started_at,
    gt.ended_at,
    gt.distance_km,
    gt.duration_minutes,
    COUNT(gl.id) as location_points,
    MAX(gl.timestamp) as last_location_time
FROM gps_tracking gt
LEFT JOIN users u ON gt.staff_id = u.id
LEFT JOIN members m ON gt.member_id = m.id
LEFT JOIN gps_logs gl ON gt.id = gl.tracking_id
GROUP BY gt.id;

-- Create trigger for updating GPS tracking duration and distance
DELIMITER //
CREATE TRIGGER IF NOT EXISTS update_tracking_stats 
    AFTER INSERT ON gps_logs
    FOR EACH ROW
BEGIN
    UPDATE gps_tracking 
    SET 
        duration_minutes = TIMESTAMPDIFF(MINUTE, started_at, COALESCE(ended_at, NOW())),
        distance_km = (
            SELECT COALESCE(SUM(
                6371 * acos(
                    cos(radians(NEW.latitude)) * cos(radians(gl2.latitude)) * 
                    cos(radians(gl2.longitude) - radians(NEW.longitude)) + 
                    sin(radians(NEW.latitude)) * sin(radians(gl2.latitude))
                )
            ), 0)
            FROM gps_logs gl2 
            WHERE gl2.tracking_id = NEW.tracking_id 
            AND gl2.timestamp <= NEW.timestamp
        )
    WHERE id = NEW.tracking_id;
END//
DELIMITER ;
