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

/**
 * Database Sync API
 */

// Database connections
$pdo_ksp = new PDO("mysql:host=localhost;unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=ksp_lamgabejaya_v2", "root", "root");
$pdo_ksp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo_orang = new PDO("mysql:host=localhost;unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=orang", "root", "root");
$pdo_orang->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo_alamat = new PDO("mysql:host=localhost;unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=alamat_db", "root", "root");
$pdo_alamat->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST["action"] ?? "sync";
    
    switch ($action) {
        case "sync":
            // Sync all databases
            $sync_result = [
                'koperasi_sync' => false,
                'orang_sync' => false,
                'alamat_sync' => false,
                'integration_sync' => false,
                'timestamp' => date('Y-m-d H:i:s'),
                'details' => []
            ];
            
            try {
                // Test Koperasi DB connection
                $stmt = $pdo_ksp->query("SELECT 1");
                $sync_result['koperasi_sync'] = true;
                $sync_result['details'][] = "Koperasi database connection: OK";
            } catch (Exception $e) {
                $sync_result['details'][] = "Koperasi database connection: Failed - " . $e->getMessage();
            }
            
            try {
                // Test Orang DB connection
                $stmt = $pdo_orang->query("SELECT 1");
                $sync_result['orang_sync'] = true;
                $sync_result['details'][] = "Orang database connection: OK";
            } catch (Exception $e) {
                $sync_result['details'][] = "Orang database connection: Failed - " . $e->getMessage();
            }
            
            try {
                // Test Alamat DB connection
                $stmt = $pdo_alamat->query("SELECT 1");
                $sync_result['alamat_sync'] = true;
                $sync_result['details'][] = "Alamat database connection: OK";
            } catch (Exception $e) {
                $sync_result['details'][] = "Alamat database connection: Failed - " . $e->getMessage();
            }
            
            try {
                // Test integration (foreign key constraints)
                $stmt = $pdo_ksp->query("SELECT COUNT(*) as count FROM information_schema.table_constraints WHERE table_schema = 'ksp_lamgabejaya_v2' AND constraint_type = 'FOREIGN KEY'");
                $fk_count = $stmt->fetchColumn();
                $sync_result['integration_sync'] = true;
                $sync_result['details'][] = "Integration foreign keys: $fk_count constraints found";
            } catch (Exception $e) {
                $sync_result['details'][] = "Integration check: Failed - " . $e->getMessage();
            }
            
            SecurityMiddleware::sendJSONResponse($response);
            break;
            
        default:
            SecurityMiddleware::sendJSONResponse($response);
    }
} else {
    SecurityMiddleware::sendJSONResponse($response);
}
?>
