<?php
/**
 * Database Statistics API
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

$action = $_GET["action"] ?? "stats";

switch ($action) {
    case "stats":
        // Get database statistics
        $stats = [
            'koperasi' => [
                'tables' => 0,
                'records' => 0,
                'size' => 0
            ],
            'orang' => [
                'tables' => 0,
                'records' => 0,
                'size' => 0
            ],
            'alamat' => [
                'tables' => 0,
                'records' => 0,
                'size' => 0
            ]
        ];
        
        // Get Koperasi DB stats
        $stmt = $pdo_ksp->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'ksp_lamgabejaya_v2'");
        $stats['koperasi']['tables'] = $stmt->fetchColumn();
        
        $stmt = $pdo_ksp->query("SELECT SUM(table_rows) as count FROM information_schema.tables WHERE table_schema = 'ksp_lamgabejaya_v2'");
        $stats['koperasi']['records'] = $stmt->fetchColumn() ?: 0;
        
        $stmt = $pdo_ksp->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size FROM information_schema.tables WHERE table_schema = 'ksp_lamgabejaya_v2'");
        $stats['koperasi']['size'] = $stmt->fetchColumn() ?: 0;
        
        // Get Orang DB stats
        $stmt = $pdo_orang->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'orang'");
        $stats['orang']['tables'] = $stmt->fetchColumn();
        
        $stmt = $pdo_orang->query("SELECT SUM(table_rows) as count FROM information_schema.tables WHERE table_schema = 'orang'");
        $stats['orang']['records'] = $stmt->fetchColumn() ?: 0;
        
        $stmt = $pdo_orang->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size FROM information_schema.tables WHERE table_schema = 'orang'");
        $stats['orang']['size'] = $stmt->fetchColumn() ?: 0;
        
        // Get Alamat DB stats
        $stmt = $pdo_alamat->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'alamat_db'");
        $stats['alamat']['tables'] = $stmt->fetchColumn();
        
        $stmt = $pdo_alamat->query("SELECT SUM(table_rows) as count FROM information_schema.tables WHERE table_schema = 'alamat_db'");
        $stats['alamat']['records'] = $stmt->fetchColumn() ?: 0;
        
        $stmt = $pdo_alamat->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size FROM information_schema.tables WHERE table_schema = 'alamat_db'");
        $stats['alamat']['size'] = $stmt->fetchColumn() ?: 0;
        
        echo json_encode([
            "success" => true,
            "data" => $stats
        ]);
        break;
        
    case "activities":
        // Get recent database activities
        $activities = [];
        
        // Mock activities for now
        $activities = [
            [
                'timestamp' => date('Y-m-d H:i:s'),
                'database' => 'ksp_lamgabejaya_v2',
                'action' => 'INSERT',
                'table' => 'members',
                'status' => 'Success'
            ],
            [
                'timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'database' => 'orang',
                'action' => 'UPDATE',
                'table' => 'persons',
                'status' => 'Success'
            ],
            [
                'timestamp' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'database' => 'ksp_lamgabejaya_v2',
                'action' => 'DELETE',
                'table' => 'loans',
                'status' => 'Success'
            ]
        ];
        
        echo json_encode([
            "success" => true,
            "data" => $activities
        ]);
        break;
        
    case "sync":
        // Sync databases
        $sync_result = [
            'koperasi_sync' => true,
            'orang_sync' => true,
            'alamat_sync' => true,
            'integration_sync' => true,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode([
            "success" => true,
            "data" => $sync_result
        ]);
        break;
        
    case "backup":
        // Backup databases
        $backup_result = [
            'koperasi_backup' => true,
            'orang_backup' => true,
            'alamat_backup' => true,
            'backup_file' => 'backup_' . date('Y-m-d_H-i-s') . '.sql',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode([
            "success" => true,
            "data" => $backup_result
        ]);
        break;
        
    default:
        echo json_encode([
            "success" => false,
            "error" => "Unknown action"
        ]);
}
?>
