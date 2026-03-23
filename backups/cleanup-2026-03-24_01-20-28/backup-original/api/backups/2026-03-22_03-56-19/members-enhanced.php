<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if (!isset($_SERVER["REQUEST_METHOD"])) {
    $_SERVER["REQUEST_METHOD"] = "GET";
}

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

require_once 'auth_helper.php';

// Check authentication
$token = $_REQUEST['token'] ?? '';
if (empty($token)) {
    echo json_encode(["success" => false, "error" => "Token required"]);
    exit();
}

// Validate token
$user = validateToken($token);

if (!$user) {
    echo json_encode(["success" => false, "error" => "Invalid token"]);
    exit();
}

try {
    $pdo = getDatabaseConnection();
    
    $action = $_REQUEST["action"] ?? "list";
    
    switch ($action) {
        case "list":
            $stmt = $pdo->query("
                SELECT m.*, mt.name as member_type_name,
                       GROUP_CONCAT(DISTINCT CONCAT(mi.identity_type, ':', mi.identity_number) SEPARATOR ';') as identities,
                       (SELECT COUNT(*) FROM member_addresses ma WHERE ma.member_id = m.id AND ma.is_primary = 1) as has_primary_address
                FROM members m 
                LEFT JOIN member_types mt ON m.member_type_id = mt.id 
                LEFT JOIN member_identities mi ON m.id = mi.member_id AND mi.status = 'Active'
                GROUP BY m.id
                ORDER BY m.created_at DESC
            ");
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["success" => true, "data" => $members]);
            break;
            
        case "create":
            if (!in_array($user['role'], ['Super Admin', 'Admin', 'Manager', 'Owner'])) {
                echo json_encode(["success" => false, "error" => "Access denied"]);
                break;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $pdo->beginTransaction();
            
            try {
                // Insert member
                $memberNumber = 'M' . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
                
                $stmt = $pdo->prepare("
                    INSERT INTO members (member_number, member_type_id, title, full_name, birth_place, date_of_birth, gender, 
                        phone_number, mobile_number, email, occupation, company_name, monthly_income, 
                        marital_status, spouse_name, spouse_phone, emergency_contact_name, emergency_contact_phone, 
                        emergency_contact_relation, rt, rw, latitude, longitude, registration_date, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $memberNumber,
                    $data['member_type_id'] ?? 1,
                    $data['title'] ?? null,
                    $data['full_name'],
                    $data['birth_place'] ?? null,
                    $data['date_of_birth'] ?? null,
                    $data['gender'],
                    $data['phone_number'],
                    $data['mobile_number'] ?? $data['phone_number'],
                    $data['email'],
                    $data['occupation'],
                    $data['company_name'] ?? null,
                    $data['monthly_income'] ?? null,
                    $data['marital_status'] ?? null,
                    $data['spouse_name'] ?? null,
                    $data['spouse_phone'] ?? null,
                    $data['emergency_contact_name'] ?? null,
                    $data['emergency_contact_phone'] ?? null,
                    $data['emergency_contact_relation'] ?? null,
                    $data['rt'] ?? null,
                    $data['rw'] ?? null,
                    $data['latitude'] ?? null,
                    $data['longitude'] ?? null,
                    $data['registration_date'] ?? date('Y-m-d'),
                    $user['id']
                ]);
                
                $memberId = $pdo->lastInsertId();
                
                // Insert identities
                if (!empty($data['identities'])) {
                    foreach ($data['identities'] as $index => $identity) {
                        if (!empty($identity['identity_type']) && !empty($identity['identity_number'])) {
                            $stmt = $pdo->prepare("
                                INSERT INTO member_identities (member_id, identity_type, identity_number, document_path, 
                                    issue_date, expiry_date, is_primary, notes) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            
                            $stmt->execute([
                                $memberId,
                                $identity['identity_type'],
                                $identity['identity_number'],
                                $identity['document_path'] ?? null,
                                $identity['issue_date'] ?? null,
                                $identity['expiry_date'] ?? null,
                                $index == 0 ? 1 : 0, // First identity is primary
                                $identity['notes'] ?? null
                            ]);
                        }
                    }
                }
                
                // Insert addresses
                if (!empty($data['addresses'])) {
                    foreach ($data['addresses'] as $index => $address) {
                        if (!empty($address['province_name'])) {
                            $stmt = $pdo->prepare("
                                INSERT INTO member_addresses (member_id, address_type, province_id, province_name, 
                                    regency_id, regency_name, district_id, district_name, village_id, village_name, 
                                    rt, rw, full_address, postal_code, latitude, longitude, is_primary) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            
                            $stmt->execute([
                                $memberId,
                                $address['address_type'] ?? 'Residence',
                                $address['province_id'] ?? null,
                                $address['province_name'],
                                $address['regency_id'] ?? null,
                                $address['regency_name'],
                                $address['district_id'] ?? null,
                                $address['district_name'],
                                $address['village_id'] ?? null,
                                $address['village_name'],
                                $address['rt'] ?? null,
                                $address['rw'] ?? null,
                                $address['full_address'],
                                $address['postal_code'] ?? null,
                                $address['latitude'] ?? null,
                                $address['longitude'] ?? null,
                                $index == 0 ? 1 : 0 // First address is primary
                            ]);
                        }
                    }
                }
                
                // Handle photo upload
                if (!empty($data['photo_base64'])) {
                    $photoData = base64_decode($data['photo_base64']);
                    $photoPath = 'uploads/avatars/members/' . $memberId . '_' . time() . '.jpg';
                    
                    if (file_put_contents('../../' . $photoPath, $photoData)) {
                        $stmt = $pdo->prepare("UPDATE members SET photo_path = ? WHERE id = ?");
                        $stmt->execute([$photoPath, $memberId]);
                    }
                }
                
                $pdo->commit();
                
                echo json_encode([
                    "success" => true, 
                    "member_id" => $memberId, 
                    "member_number" => $memberNumber,
                    "message" => "Member created successfully with multiple identities and addresses"
                ]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case "get":
            $memberId = $_REQUEST['id'] ?? 0;
            
            // Get member basic info
            $stmt = $pdo->prepare("
                SELECT m.*, mt.name as member_type_name 
                FROM members m 
                LEFT JOIN member_types mt ON m.member_type_id = mt.id 
                WHERE m.id = ?
            ");
            $stmt->execute([$memberId]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$member) {
                echo json_encode(["success" => false, "error" => "Member not found"]);
                break;
            }
            
            // Get identities
            $stmt = $pdo->prepare("
                SELECT * FROM member_identities 
                WHERE member_id = ? AND status = 'Active'
                ORDER BY is_primary DESC, created_at ASC
            ");
            $stmt->execute([$memberId]);
            $member['identities'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get addresses
            $stmt = $pdo->prepare("
                SELECT * FROM member_addresses 
                WHERE member_id = ? AND status = 'Active'
                ORDER BY is_primary DESC, created_at ASC
            ");
            $stmt->execute([$memberId]);
            $member['addresses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get documents
            $stmt = $pdo->prepare("
                SELECT * FROM member_documents 
                WHERE member_id = ?
                ORDER BY created_at ASC
            ");
            $stmt->execute([$memberId]);
            $member['documents'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(["success" => true, "data" => $member]);
            break;
            
        case "update":
            if (!in_array($user['role'], ['Super Admin', 'Admin', 'Manager', 'Owner'])) {
                echo json_encode(["success" => false, "error" => "Access denied"]);
                break;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $memberId = $data['id'] ?? 0;
            
            if (!$memberId) {
                echo json_encode(["success" => false, "error" => "Member ID required"]);
                break;
            }
            
            $pdo->beginTransaction();
            
            try {
                // Update member basic info
                $stmt = $pdo->prepare("
                    UPDATE members SET title = ?, full_name = ?, birth_place = ?, date_of_birth = ?, gender = ?, 
                        phone_number = ?, mobile_number = ?, email = ?, occupation = ?, company_name = ?, 
                        monthly_income = ?, marital_status = ?, spouse_name = ?, spouse_phone = ?, 
                        emergency_contact_name = ?, emergency_contact_phone = ?, emergency_contact_relation = ?, 
                        rt = ?, rw = ?, latitude = ?, longitude = ?, updated_by = ?
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $data['title'] ?? null,
                    $data['full_name'],
                    $data['birth_place'] ?? null,
                    $data['date_of_birth'] ?? null,
                    $data['gender'],
                    $data['phone_number'],
                    $data['mobile_number'] ?? $data['phone_number'],
                    $data['email'],
                    $data['occupation'],
                    $data['company_name'] ?? null,
                    $data['monthly_income'] ?? null,
                    $data['marital_status'] ?? null,
                    $data['spouse_name'] ?? null,
                    $data['spouse_phone'] ?? null,
                    $data['emergency_contact_name'] ?? null,
                    $data['emergency_contact_phone'] ?? null,
                    $data['emergency_contact_relation'] ?? null,
                    $data['rt'] ?? null,
                    $data['rw'] ?? null,
                    $data['latitude'] ?? null,
                    $data['longitude'] ?? null,
                    $user['id'],
                    $memberId
                ]);
                
                // Update identities (delete old ones and insert new)
                if (isset($data['identities'])) {
                    $stmt = $pdo->prepare("DELETE FROM member_identities WHERE member_id = ?");
                    $stmt->execute([$memberId]);
                    
                    foreach ($data['identities'] as $index => $identity) {
                        if (!empty($identity['identity_type']) && !empty($identity['identity_number'])) {
                            $stmt = $pdo->prepare("
                                INSERT INTO member_identities (member_id, identity_type, identity_number, document_path, 
                                    issue_date, expiry_date, is_primary, notes) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            
                            $stmt->execute([
                                $memberId,
                                $identity['identity_type'],
                                $identity['identity_number'],
                                $identity['document_path'] ?? null,
                                $identity['issue_date'] ?? null,
                                $identity['expiry_date'] ?? null,
                                $index == 0 ? 1 : 0,
                                $identity['notes'] ?? null
                            ]);
                        }
                    }
                }
                
                // Update addresses
                if (isset($data['addresses'])) {
                    $stmt = $pdo->prepare("DELETE FROM member_addresses WHERE member_id = ?");
                    $stmt->execute([$memberId]);
                    
                    foreach ($data['addresses'] as $index => $address) {
                        if (!empty($address['province_name'])) {
                            $stmt = $pdo->prepare("
                                INSERT INTO member_addresses (member_id, address_type, province_id, province_name, 
                                    regency_id, regency_name, district_id, district_name, village_id, village_name, 
                                    rt, rw, full_address, postal_code, latitude, longitude, is_primary) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            
                            $stmt->execute([
                                $memberId,
                                $address['address_type'] ?? 'Residence',
                                $address['province_id'] ?? null,
                                $address['province_name'],
                                $address['regency_id'] ?? null,
                                $address['regency_name'],
                                $address['district_id'] ?? null,
                                $address['district_name'],
                                $address['village_id'] ?? null,
                                $address['village_name'],
                                $address['rt'] ?? null,
                                $address['rw'] ?? null,
                                $address['full_address'],
                                $address['postal_code'] ?? null,
                                $address['latitude'] ?? null,
                                $address['longitude'] ?? null,
                                $index == 0 ? 1 : 0
                            ]);
                        }
                    }
                }
                
                // Handle photo update
                if (!empty($data['photo_base64'])) {
                    $photoData = base64_decode($data['photo_base64']);
                    $photoPath = 'uploads/avatars/members/' . $memberId . '_' . time() . '.jpg';
                    
                    if (file_put_contents('../../' . $photoPath, $photoData)) {
                        $stmt = $pdo->prepare("UPDATE members SET photo_path = ? WHERE id = ?");
                        $stmt->execute([$photoPath, $memberId]);
                    }
                }
                
                $pdo->commit();
                
                echo json_encode([
                    "success" => true,
                    "message" => "Member updated successfully"
                ]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case "delete":
            if (!in_array($user['role'], ['Super Admin', 'Admin', 'Owner'])) {
                echo json_encode(["success" => false, "error" => "Access denied"]);
                break;
            }
            
            $memberId = $_REQUEST['id'] ?? 0;
            
            $pdo->beginTransaction();
            
            try {
                // Delete related records (foreign keys will handle cascade)
                $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
                $stmt->execute([$memberId]);
                
                $pdo->commit();
                
                echo json_encode(["success" => true, "message" => "Member deleted successfully"]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case "search":
            $query = $_REQUEST['q'] ?? '';
            $limit = $_REQUEST['limit'] ?? 10;
            
            $stmt = $pdo->prepare("
                SELECT m.id, m.member_number, m.full_name, m.phone_number, m.email,
                       mt.name as member_type_name
                FROM members m 
                LEFT JOIN member_types mt ON m.member_type_id = mt.id 
                WHERE (m.full_name LIKE ? OR m.member_number LIKE ? OR m.phone_number LIKE ?)
                AND m.status = 'Active'
                ORDER BY m.full_name
                LIMIT ?
            ");
            
            $searchTerm = "%$query%";
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit]);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(["success" => true, "data" => $members]);
            break;
            
        default:
            echo json_encode(["success" => false, "error" => "Unknown action: $action"]);
            break;
    }
    
} catch (PDOException $e) {
    echo json_encode([
        "success" => false, 
        "error" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false, 
        "error" => $e->getMessage()
    ]);
}
?>
