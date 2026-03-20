<?php
/**
 * Database Backup API
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
    $action = $_POST["action"] ?? "backup";
    
    switch ($action) {
        case "backup":
            // Backup all databases
            $backup_result = [
                'koperasi_backup' => false,
                'orang_backup' => false,
                'alamat_backup' => false,
                'backup_file' => 'backup_' . date('Y-m-d_H-i-s') . '.sql',
                'timestamp' => date('Y-m-d H:i:s'),
                'details' => []
            ];
            
            try {
                // Backup Koperasi DB
                $backup_file = '/opt/lampp/htdocs/mono-v2/backups/koperasi_' . date('Y-m-d_H-i-s') . '.sql';
                $command = "mysqldump --single-transaction --routines --triggers ksp_lamgabejaya_v2 > $backup_file";
                exec($command, $output, $return_var);
                
                if ($return_var === 0) {
                    $backup_result['koperasi_backup'] = true;
                    $backup_result['details'][] = "Koperasi database backup: Success";
                } else {
                    $backup_result['details'][] = "Koperasi database backup: Failed";
                }
            } catch (Exception $e) {
                $backup_result['details'][] = "Koperasi backup error: " . $e->getMessage();
            }
            
            try {
                // Backup Orang DB
                $backup_file = '/opt/lampp/htdocs/mono-v2/backups/orang_' . date('Y-m-d_H-i-s') . '.sql';
                $command = "mysqldump --single-transaction --routines --triggers orang > $backup_file";
                exec($command, $output, $return_var);
                
                if ($return_var === 0) {
                    $backup_result['orang_backup'] = true;
                    $backup_result['details'][] = "Orang database backup: Success";
                } else {
                    $backup_result['details'][] = "Orang database backup: Failed";
                }
            } catch (Exception $e) {
                $backup_result['details'][] = "Orang backup error: " . $e->getMessage();
            }
            
            try {
                // Backup Alamat DB
                $backup_file = '/opt/lampp/htdocs/mono-v2/backups/alamat_' . date('Y-m-d_H-i-s') . '.sql';
                $command = "mysqldump --single-transaction --routines --triggers alamat_db > $backup_file";
                exec($command, $output, $return_var);
                
                if ($return_var === 0) {
                    $backup_result['alamat_backup'] = true;
                    $backup_result['details'][] = "Alamat database backup: Success";
                } else {
                    $backup_result['details'][] = "Alamat database backup: Failed";
                }
            } catch (Exception $e) {
                $backup_result['details'][] = "Alamat backup error: " . $e->getMessage();
            }
            
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
} else {
    echo json_encode([
        "success" => false,
        "error" => "Invalid request method"
    ]);
}
?>
