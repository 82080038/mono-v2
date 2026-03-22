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
    // Get database connections
    $ksp_db = getDatabaseConnection(); // ksp_lamgabejaya_v2
    $alamat_db = getAlamatDbConnection(); // alamat_db (read-only)
    $orang_db = getOrangDbConnection(); // orang
    
    $action = $_REQUEST["action"] ?? "list";
    
    switch ($action) {
        // ==================================================
        // MEMBER MANAGEMENT WITH MULTIPLE IDENTITIES
        // ==================================================
        
        case "list":
            $stmt = $ksp_db->query("
                SELECT m.*, mt.name as member_type_name,
                       (SELECT COUNT(*) FROM member_identities mi WHERE mi.member_id = m.id AND mi.status = 'Active') as identity_count,
                       (SELECT COUNT(*) FROM member_addresses ma WHERE ma.member_id = m.id AND ma.status = 'Active') as address_count
                FROM members m 
                LEFT JOIN member_types mt ON m.member_type_id = mt.id 
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
            
            $ksp_db->beginTransaction();
            
            try {
                // Check if person exists in orang database based on KTP
                $personId = null;
                if (!empty($data['identities'])) {
                    foreach ($data['identities'] as $identity) {
                        if ($identity['identity_type'] === 'KTP' && !empty($identity['identity_number'])) {
                            $stmt = $orang_db->prepare("SELECT id FROM persons WHERE nik = ?");
                            $stmt->execute([$identity['identity_number']]);
                            $personId = $stmt->fetchColumn();
                            break;
                        }
                    }
                }
                
                // Create new person in orang database if not exists
                if (!$personId) {
                    $stmt = $orang_db->prepare("
                        INSERT INTO persons (nik, full_name, place_of_birth, date_of_birth, gender, 
                            phone_number, email, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    
                    $primaryKtp = null;
                    foreach ($data['identities'] as $identity) {
                        if ($identity['identity_type'] === 'KTP') {
                            $primaryKtp = $identity['identity_number'];
                            break;
                        }
                    }
                    
                    $stmt->execute([
                        $primaryKtp,
                        $data['full_name'],
                        $data['birth_place'] ?? null,
                        $data['date_of_birth'] ?? null,
                        $data['gender'],
                        $data['phone_number'],
                        $data['email']
                    ]);
                    
                    $personId = $orang_db->lastInsertId();
                }
                
                // Insert member in ksp database
                $memberNumber = 'M' . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
                
                $stmt = $ksp_db->prepare("
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
                
                $memberId = $ksp_db->lastInsertId();
                
                // Insert identities in ksp database
                if (!empty($data['identities'])) {
                    foreach ($data['identities'] as $index => $identity) {
                        if (!empty($identity['identity_type']) && !empty($identity['identity_number'])) {
                            $stmt = $ksp_db->prepare("
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
                            
                            // Also insert in orang database if KTP
                            if ($identity['identity_type'] === 'KTP') {
                                $stmt = $orang_db->prepare("
                                    INSERT INTO person_identities (person_id, identity_type, identity_number, document_path, 
                                        issue_date, expiry_date, is_primary, notes) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                                ");
                                
                                $stmt->execute([
                                    $personId,
                                    'KTP',
                                    $identity['identity_number'],
                                    $identity['document_path'] ?? null,
                                    $identity['issue_date'] ?? null,
                                    $identity['expiry_date'] ?? null,
                                    1,
                                    $identity['notes'] ?? null
                                ]);
                            }
                        }
                    }
                }
                
                // Insert addresses in ksp database
                if (!empty($data['addresses'])) {
                    foreach ($data['addresses'] as $index => $address) {
                        if (!empty($address['province_name'])) {
                            // Get province/regency/district/village IDs from alamat_db
                            $provinceId = null;
                            $regencyId = null;
                            $districtId = null;
                            $villageId = null;
                            
                            if (!empty($address['province_name'])) {
                                $stmt = $alamat_db->prepare("SELECT id FROM provinces WHERE name = ?");
                                $stmt->execute([$address['province_name']]);
                                $provinceId = $stmt->fetchColumn();
                            }
                            
                            if (!empty($address['regency_name']) && $provinceId) {
                                $stmt = $alamat_db->prepare("SELECT id FROM regencies WHERE name = ? AND province_id = ?");
                                $stmt->execute([$address['regency_name'], $provinceId]);
                                $regencyId = $stmt->fetchColumn();
                            }
                            
                            if (!empty($address['district_name']) && $regencyId) {
                                $stmt = $alamat_db->prepare("SELECT id FROM districts WHERE name = ? AND regency_id = ?");
                                $stmt->execute([$address['district_name'], $regencyId]);
                                $districtId = $stmt->fetchColumn();
                            }
                            
                            if (!empty($address['village_name']) && $districtId) {
                                $stmt = $alamat_db->prepare("SELECT id FROM villages WHERE name = ? AND district_id = ?");
                                $stmt->execute([$address['village_name'], $districtId]);
                                $villageId = $stmt->fetchColumn();
                            }
                            
                            // Insert in ksp database
                            $stmt = $ksp_db->prepare("
                                INSERT INTO member_addresses (member_id, address_type, province_id, province_name, 
                                    regency_id, regency_name, district_id, district_name, village_id, village_name, 
                                    rt, rw, full_address, postal_code, latitude, longitude, is_primary) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            
                            $stmt->execute([
                                $memberId,
                                $address['address_type'] ?? 'Residence',
                                $provinceId,
                                $address['province_name'],
                                $regencyId,
                                $address['regency_name'],
                                $districtId,
                                $address['district_name'],
                                $villageId,
                                $address['village_name'],
                                $address['rt'] ?? null,
                                $address['rw'] ?? null,
                                $address['full_address'],
                                $address['postal_code'] ?? null,
                                $address['latitude'] ?? null,
                                $address['longitude'] ?? null,
                                $index == 0 ? 1 : 0
                            ]);
                            
                            // Also insert in orang database
                            $stmt = $orang_db->prepare("
                                INSERT INTO person_addresses (person_id, address_type, village_id, district_id, 
                                    regency_id, province_id, postal_code, address_line, address_line2, 
                                    latitude, longitude, is_primary) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            
                            $stmt->execute([
                                $personId,
                                'home',
                                $villageId,
                                $districtId,
                                $regencyId,
                                $provinceId,
                                $address['postal_code'] ?? null,
                                $address['full_address'],
                                null,
                                $address['latitude'] ?? null,
                                $address['longitude'] ?? null,
                                $index == 0 ? 1 : 0
                            ]);
                        }
                    }
                }
                
                // Handle photo upload
                if (!empty($data['photo_base64'])) {
                    $photoData = base64_decode($data['photo_base64']);
                    $photoPath = 'uploads/avatars/members/' . $memberId . '_' . time() . '.jpg';
                    
                    if (file_put_contents('../../' . $photoPath, $photoData)) {
                        $stmt = $ksp_db->prepare("UPDATE members SET photo_path = ? WHERE id = ?");
                        $stmt->execute([$photoPath, $memberId]);
                    }
                }
                
                $ksp_db->commit();
                
                echo json_encode([
                    "success" => true, 
                    "member_id" => $memberId, 
                    "person_id" => $personId,
                    "member_number" => $memberNumber,
                    "message" => "Member created successfully with multiple identities and addresses"
                ]);
                
            } catch (Exception $e) {
                $ksp_db->rollBack();
                throw $e;
            }
            break;
            
        case "get":
            $memberId = $_REQUEST['id'] ?? 0;
            
            // Get member basic info
            $stmt = $ksp_db->prepare("
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
            $stmt = $ksp_db->prepare("
                SELECT * FROM member_identities 
                WHERE member_id = ? AND status = 'Active'
                ORDER BY is_primary DESC, created_at ASC
            ");
            $stmt->execute([$memberId]);
            $member['identities'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get addresses with alamat_db integration
            $stmt = $ksp_db->prepare("
                SELECT ma.*, 
                       p.name as province_db_name,
                       r.name as regency_db_name,
                       d.name as district_db_name,
                       v.name as village_db_name
                FROM member_addresses ma
                LEFT JOIN alamat_db.provinces p ON ma.province_id = p.id
                LEFT JOIN alamat_db.regencies r ON ma.regency_id = r.id
                LEFT JOIN alamat_db.districts d ON ma.district_id = d.id
                LEFT JOIN alamat_db.villages v ON ma.village_id = v.id
                WHERE ma.member_id = ? AND ma.status = 'Active'
                ORDER BY ma.is_primary DESC, ma.created_at ASC
            ");
            $stmt->execute([$memberId]);
            $member['addresses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(["success" => true, "data" => $member]);
            break;
            
        // ==================================================
        // ADDRESS DATABASE INTEGRATION (READ-ONLY)
        // ==================================================
        
        case "get_provinces":
            $stmt = $alamat_db->query("SELECT id, code, name FROM provinces ORDER BY name");
            $provinces = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["success" => true, "data" => $provinces]);
            break;
            
        case "get_regencies":
            $provinceId = $_REQUEST['province_id'] ?? 0;
            if (!$provinceId) {
                echo json_encode(["success" => false, "error" => "Province ID required"]);
                break;
            }
            
            $stmt = $alamat_db->prepare("SELECT id, code, name FROM regencies WHERE province_id = ? ORDER BY name");
            $stmt->execute([$provinceId]);
            $regencies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["success" => true, "data" => $regencies]);
            break;
            
        case "get_districts":
            $regencyId = $_REQUEST['regency_id'] ?? 0;
            if (!$regencyId) {
                echo json_encode(["success" => false, "error" => "Regency ID required"]);
                break;
            }
            
            $stmt = $alamat_db->prepare("SELECT id, code, name FROM districts WHERE regency_id = ? ORDER BY name");
            $stmt->execute([$regencyId]);
            $districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["success" => true, "data" => $districts]);
            break;
            
        case "get_villages":
            $districtId = $_REQUEST['district_id'] ?? 0;
            if (!$districtId) {
                echo json_encode(["success" => false, "error" => "District ID required"]);
                break;
            }
            
            $stmt = $alamat_db->prepare("SELECT id, code, name FROM villages WHERE district_id = ? ORDER BY name");
            $stmt->execute([$districtId]);
            $villages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["success" => true, "data" => $villages]);
            break;
            
        // ==================================================
        // LOAN APPLICANT MANAGEMENT
        // ==================================================
        
        case "create_loan_applicant":
            if (!in_array($user['role'], ['Super Admin', 'Admin', 'Manager', 'Owner'])) {
                echo json_encode(["success" => false, "error" => "Access denied"]);
                break;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $ksp_db->beginTransaction();
            
            try {
                // Check if person exists in orang database
                $personId = null;
                if (!empty($data['identities'])) {
                    foreach ($data['identities'] as $identity) {
                        if ($identity['identity_type'] === 'KTP' && !empty($identity['identity_number'])) {
                            $stmt = $orang_db->prepare("SELECT id FROM persons WHERE nik = ?");
                            $stmt->execute([$identity['identity_number']]);
                            $personId = $stmt->fetchColumn();
                            break;
                        }
                    }
                }
                
                // Create new person if not exists
                if (!$personId) {
                    $stmt = $orang_db->prepare("
                        INSERT INTO persons (nik, full_name, date_of_birth, gender, 
                            phone_number, email, profession, monthly_income, marital_status, 
                            spouse_name, dependents_count, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    
                    $primaryKtp = null;
                    foreach ($data['identities'] as $identity) {
                        if ($identity['identity_type'] === 'KTP') {
                            $primaryKtp = $identity['identity_number'];
                            break;
                        }
                    }
                    
                    $stmt->execute([
                        $primaryKtp,
                        $data['full_name'],
                        $data['birth_date'] ?? null,
                        $data['gender'],
                        $data['phone_number'],
                        $data['email'],
                        $data['occupation'],
                        $data['monthly_income'] ?? null,
                        $data['marital_status'] ?? null,
                        $data['spouse_name'] ?? null,
                        $data['dependents'] ?? 0
                    ]);
                    
                    $personId = $orang_db->lastInsertId();
                }
                
                // Create loan applicant
                $applicationNumber = 'LA' . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
                
                $stmt = $ksp_db->prepare("
                    INSERT INTO loan_applicants (application_number, person_id, full_name, birth_date, 
                        gender, phone_number, email, occupation, monthly_income, marital_status, 
                        dependents, reference_name, reference_phone, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
                ");
                
                $stmt->execute([
                    $applicationNumber,
                    $personId,
                    $data['full_name'],
                    $data['birth_date'] ?? null,
                    $data['gender'],
                    $data['phone_number'],
                    $data['email'],
                    $data['occupation'],
                    $data['monthly_income'] ?? null,
                    $data['marital_status'] ?? null,
                    $data['dependents'] ?? 0,
                    $data['reference_name'] ?? null,
                    $data['reference_phone'] ?? null
                ]);
                
                $applicantId = $ksp_db->lastInsertId();
                
                // Insert identities
                if (!empty($data['identities'])) {
                    foreach ($data['identities'] as $index => $identity) {
                        if (!empty($identity['identity_type']) && !empty($identity['identity_number'])) {
                            // Insert in ksp database
                            $stmt = $ksp_db->prepare("
                                INSERT INTO loan_applicant_identities (applicant_id, identity_type, identity_number, 
                                    document_path, issue_date, expiry_date, is_primary, notes) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            
                            $stmt->execute([
                                $applicantId,
                                $identity['identity_type'],
                                $identity['identity_number'],
                                $identity['document_path'] ?? null,
                                $identity['issue_date'] ?? null,
                                $identity['expiry_date'] ?? null,
                                $index == 0 ? 1 : 0,
                                $identity['notes'] ?? null
                            ]);
                            
                            // Insert in orang database
                            $stmt = $orang_db->prepare("
                                INSERT INTO person_identities (person_id, identity_type, identity_number, document_path, 
                                    issue_date, expiry_date, is_primary, notes) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            
                            $stmt->execute([
                                $personId,
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
                
                // Insert addresses
                if (!empty($data['addresses'])) {
                    foreach ($data['addresses'] as $index => $address) {
                        if (!empty($address['province_name'])) {
                            // Get IDs from alamat_db
                            $provinceId = null;
                            $regencyId = null;
                            $districtId = null;
                            $villageId = null;
                            
                            if (!empty($address['province_name'])) {
                                $stmt = $alamat_db->prepare("SELECT id FROM provinces WHERE name = ?");
                                $stmt->execute([$address['province_name']]);
                                $provinceId = $stmt->fetchColumn();
                            }
                            
                            if (!empty($address['regency_name']) && $provinceId) {
                                $stmt = $alamat_db->prepare("SELECT id FROM regencies WHERE name = ? AND province_id = ?");
                                $stmt->execute([$address['regency_name'], $provinceId]);
                                $regencyId = $stmt->fetchColumn();
                            }
                            
                            if (!empty($address['district_name']) && $regencyId) {
                                $stmt = $alamat_db->prepare("SELECT id FROM districts WHERE name = ? AND regency_id = ?");
                                $stmt->execute([$address['district_name'], $regencyId]);
                                $districtId = $stmt->fetchColumn();
                            }
                            
                            if (!empty($address['village_name']) && $districtId) {
                                $stmt = $alamat_db->prepare("SELECT id FROM villages WHERE name = ? AND district_id = ?");
                                $stmt->execute([$address['village_name'], $districtId]);
                                $villageId = $stmt->fetchColumn();
                            }
                            
                            // Insert in ksp database
                            $stmt = $ksp_db->prepare("
                                INSERT INTO loan_applicant_addresses (applicant_id, address_type, province_id, province_name, 
                                    regency_id, regency_name, district_id, district_name, village_id, village_name, 
                                    rt, rw, full_address, postal_code, latitude, longitude, is_primary) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            
                            $stmt->execute([
                                $applicantId,
                                $address['address_type'] ?? 'Residence',
                                $provinceId,
                                $address['province_name'],
                                $regencyId,
                                $address['regency_name'],
                                $districtId,
                                $address['district_name'],
                                $villageId,
                                $address['village_name'],
                                $address['rt'] ?? null,
                                $address['rw'] ?? null,
                                $address['full_address'],
                                $address['postal_code'] ?? null,
                                $address['latitude'] ?? null,
                                $address['longitude'] ?? null,
                                $index == 0 ? 1 : 0
                            ]);
                            
                            // Insert in orang database
                            $stmt = $orang_db->prepare("
                                INSERT INTO person_addresses (person_id, address_type, village_id, district_id, 
                                    regency_id, province_id, postal_code, address_line, address_line2, 
                                    latitude, longitude, is_primary) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            
                            $stmt->execute([
                                $personId,
                                'home',
                                $villageId,
                                $districtId,
                                $regencyId,
                                $provinceId,
                                $address['postal_code'] ?? null,
                                $address['full_address'],
                                null,
                                $address['latitude'] ?? null,
                                $address['longitude'] ?? null,
                                $index == 0 ? 1 : 0
                            ]);
                        }
                    }
                }
                
                $ksp_db->commit();
                
                echo json_encode([
                    "success" => true, 
                    "applicant_id" => $applicantId,
                    "person_id" => $personId,
                    "application_number" => $applicationNumber,
                    "message" => "Loan applicant created successfully"
                ]);
                
            } catch (Exception $e) {
                $ksp_db->rollBack();
                throw $e;
            }
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
