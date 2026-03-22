<?php
/**
 * Simple Orang Integration API
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

try {
    $pdo_ksp = new PDO("mysql:host=localhost;unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=ksp_lamgabejaya_v2", "root", "root");
    $pdo_ksp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo_orang = new PDO("mysql:host=localhost;unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=orang", "root", "root");
    $pdo_orang->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $action = $_GET["action"] ?? "dashboard";
    
    switch ($action) {
        case "dashboard":
            $stmt = $pdo_orang->query("SELECT COUNT(*) as total FROM persons WHERE is_active = 1");
            $totalPersons = $stmt->fetchColumn();
            
            $stmt = $pdo_ksp->query("SELECT COUNT(*) as total FROM members WHERE status = 'Active'");
            $totalMembers = $stmt->fetchColumn();
            
            $stmt = $pdo_ksp->query("SELECT COUNT(*) as total FROM members WHERE person_id IS NOT NULL AND status = 'Active'");
            $linkedMembers = $stmt->fetchColumn();
            
            echo json_encode([
                "success" => true,
                "data" => [
                    "total_persons" => $totalPersons,
                    "total_members" => $totalMembers,
                    "linked_members" => $linkedMembers,
                    "integration_rate" => $totalMembers > 0 ? round(($linkedMembers / $totalMembers) * 100, 2) : 0
                ]
            ]);
            break;
            
        case "search_persons":
            $query = $_GET["query"] ?? "";
            if (empty($query)) {
                throw new Exception("Query required");
            }
            
            $stmt = $pdo_orang->prepare("
                SELECT id, full_name, phone, email 
                FROM persons 
                WHERE full_name LIKE ? AND is_active = 1 
                LIMIT 10
            ");
            $stmt->execute(["%{$query}%"]);
            $persons = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                "success" => true,
                "data" => $persons
            ]);
            break;
            
        default:
            throw new Exception("Invalid action");
    }
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>