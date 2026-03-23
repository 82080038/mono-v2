<?php
/**
 * Database Sync API
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

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
            
            echo json_encode([
                "success" => true,
                "data" => $sync_result
            ]);
            break;
            
        default:
            echo json_encode([
                "success" => false,
                "error" => "Unknown action"
            ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "error" => "Invalid request method"
    ]);
}
?>
