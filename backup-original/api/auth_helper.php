<?php
/**
 * batch-update-legacy.php - Updated with Security
 * Auto-generated security update
 */

// Security headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token");

// Handle preflight requests
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    exit(0);
}

// Prevent direct access
if (!defined("KSP_API_ACCESS")) {
    header("HTTP/1.0 403 Forbidden");
    exit("Direct access to this file is not allowed.");
}

// Include required files
require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/DatabaseHelper.php";
require_once __DIR__ . "/Logger.php";
require_once __DIR__ . "/DataValidator.php";
require_once __DIR__ . "/SecurityLogger.php";
require_once __DIR__ . "/AuthHelper.php";
require_once __DIR__ . "/SecurityHelper.php";
require_once __DIR__ . "/SecurityMiddleware.php";

// Initialize services
Logger::initialize();
$securityLogger = SecurityLogger::getInstance();
$db = DatabaseHelper::getInstance();
$validator = new DataValidator();

// Initialize response
$response = [
    "success" => false,
    "message" => "",
    "data" => null,
    "errors" => [],
    "timestamp" => date("Y-m-d H:i:s")
];

// Database connection helper
function getDatabaseConnection() {
    try {
        $pdo = new PDO("mysql:host=localhost;unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=ksp_lamgabejaya_v2", "root", "root");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (Exception $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

// Authentication helper
function validateToken($token) {
    if (empty($token)) {
        return null;
    }
    
    try {
        $pdo = getDatabaseConnection();
        $tokenParts = explode(':', base64_decode($token));
        $userId = $tokenParts[0] ?? 0;
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user;
    } catch (Exception $e) {
        return null;
    }
}

// Role-based access check
function checkRoleAccess($userRole, $allowedRoles) {
    return in_array($userRole, $allowedRoles);
}
?>
