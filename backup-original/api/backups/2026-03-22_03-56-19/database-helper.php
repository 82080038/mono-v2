<?php
// Helper functions for multiple database connections
// KSP Lam Gabe Jaya v2.0

function getDatabaseConnection() {
    // KSP Database (ksp_lamgabejaya_v2)
    static $ksp_pdo = null;
    if ($ksp_pdo === null) {
        try {
            $ksp_pdo = new PDO(
                "mysql:host=localhost;dbname=ksp_lamgabejaya_v2;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("KSP Database connection failed: " . $e->getMessage());
        }
    }
    return $ksp_pdo;
}

function getAlamatDbConnection() {
    // Alamat Database (alamat_db) - READ ONLY
    static $alamat_pdo = null;
    if ($alamat_pdo === null) {
        try {
            $alamat_pdo = new PDO(
                "mysql:host=localhost;dbname=alamat_db;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("Alamat Database connection failed: " . $e->getMessage());
        }
    }
    return $alamat_pdo;
}

function getOrangDbConnection() {
    // Orang Database (orang)
    static $orang_pdo = null;
    if ($orang_pdo === null) {
        try {
            $orang_pdo = new PDO(
                "mysql:host=localhost;dbname=orang;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("Orang Database connection failed: " . $e->getMessage());
        }
    }
    return $orang_pdo;
}

// Enhanced authentication helper with multiple database support
function validateToken($token) {
    try {
        $pdo = getDatabaseConnection();
        
        $stmt = $pdo->prepare("
            SELECT u.*, r.name as role_name 
            FROM users u 
            LEFT JOIN user_roles r ON u.role_id = r.id 
            WHERE u.token = ? AND u.status = 'Active' AND u.token_expiry > NOW()
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Update last activity
            $stmt = $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
        }
        
        return $user;
    } catch (Exception $e) {
        return false;
    }
}

// Function to get address hierarchy from alamat_db
function getAddressHierarchy($provinceId = null, $regencyId = null, $districtId = null, $villageId = null) {
    try {
        $alamat_db = getAlamatDbConnection();
        
        $sql = "
            SELECT 
                p.id as province_id, p.name as province_name,
                r.id as regency_id, r.name as regency_name,
                d.id as district_id, d.name as district_name,
                v.id as village_id, v.name as village_name
            FROM alamat_db.provinces p
            LEFT JOIN alamat_db.regencies r ON r.province_id = p.id
            LEFT JOIN alamat_db.districts d ON d.regency_id = r.id
            LEFT JOIN alamat_db.villages v ON v.district_id = d.id
            WHERE 1=1
        ";
        
        $params = [];
        if ($provinceId) {
            $sql .= " AND p.id = ?";
            $params[] = $provinceId;
        }
        if ($regencyId) {
            $sql .= " AND r.id = ?";
            $params[] = $regencyId;
        }
        if ($districtId) {
            $sql .= " AND d.id = ?";
            $params[] = $districtId;
        }
        if ($villageId) {
            $sql .= " AND v.id = ?";
            $params[] = $villageId;
        }
        
        $stmt = $alamat_db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch();
    } catch (Exception $e) {
        return false;
    }
}

// Function to validate identity number uniqueness across databases
function validateIdentityUniqueness($identityType, $identityNumber, $excludeId = null) {
    try {
        $ksp_db = getDatabaseConnection();
        $orang_db = getOrangDbConnection();
        
        // Check in member_identities
        $sql = "SELECT COUNT(*) as count FROM member_identities WHERE identity_type = ? AND identity_number = ? AND status = 'Active'";
        $params = [$identityType, $identityNumber];
        
        if ($excludeId) {
            $sql .= " AND member_id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $ksp_db->prepare($sql);
        $stmt->execute($params);
        $memberCount = $stmt->fetchColumn();
        
        // Check in person_identities (for KTP)
        if ($identityType === 'KTP') {
            $sql = "SELECT COUNT(*) as count FROM persons WHERE nik = ?";
            $stmt = $orang_db->prepare($sql);
            $stmt->execute([$identityNumber]);
            $personCount = $stmt->fetchColumn();
            
            return ($memberCount + $personCount) === 0;
        }
        
        return $memberCount === 0;
    } catch (Exception $e) {
        return false;
    }
}

// Function to generate unique member number
function generateMemberNumber() {
    try {
        $pdo = getDatabaseConnection();
        
        $prefix = 'M' . date('Ymd');
        
        do {
            $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
            $memberNumber = $prefix . $random;
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE member_number = ?");
            $stmt->execute([$memberNumber]);
            $exists = $stmt->fetchColumn();
        } while ($exists > 0);
        
        return $memberNumber;
    } catch (Exception $e) {
        return false;
    }
}

// Function to generate unique loan application number
function generateApplicationNumber() {
    try {
        $pdo = getDatabaseConnection();
        
        $prefix = 'LA' . date('Ymd');
        
        do {
            $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
            $applicationNumber = $prefix . $random;
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM loan_applicants WHERE application_number = ?");
            $stmt->execute([$applicationNumber]);
            $exists = $stmt->fetchColumn();
        } while ($exists > 0);
        
        return $applicationNumber;
    } catch (Exception $e) {
        return false;
    }
}

// Function to handle file uploads
function handleFileUpload($file, $directory, $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf']) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Validate file type
    if (!in_array($fileType, $allowedTypes)) {
        return false;
    }
    
    // Validate file size (max 5MB)
    if ($fileSize > 5 * 1024 * 1024) {
        return false;
    }
    
    // Generate unique filename
    $newFileName = uniqid() . '_' . time() . '.' . $fileType;
    $uploadPath = '../../' . $directory . '/' . $newFileName;
    
    // Create directory if not exists
    if (!is_dir('../../' . $directory)) {
        mkdir('../../' . $directory, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($fileTmpName, $uploadPath)) {
        return $directory . '/' . $newFileName;
    }
    
    return false;
}

// Function to format currency
function formatCurrency($amount, $currency = 'IDR') {
    return $currency . ' ' . number_format($amount, 0, ',', '.');
}

// Function to validate Indonesian phone number
function validateIndonesianPhone($phone) {
    // Remove all non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Check length (Indonesian phone numbers are 9-13 digits)
    if (strlen($phone) < 9 || strlen($phone) > 13) {
        return false;
    }
    
    // Check if starts with Indonesian country code or area code
    if (substr($phone, 0, 2) === '62') {
        // International format
        return true;
    } elseif (substr($phone, 0, 1) === '0') {
        // Local format
        return true;
    }
    
    return false;
}

// Function to calculate age from birth date
function calculateAge($birthDate) {
    if (!$birthDate) return null;
    
    $birth = new DateTime($birthDate);
    $today = new DateTime();
    $age = $today->diff($birth);
    
    return $age->y;
}

// Function to validate Indonesian ID number (KTP)
function validateIndonesianKTP($ktp) {
    // Remove spaces and dots
    $ktp = preg_replace('/[\s.]/', '', $ktp);
    
    // Check length (16 digits)
    if (strlen($ktp) !== 16) {
        return false;
    }
    
    // Check if all digits
    if (!ctype_digit($ktp)) {
        return false;
    }
    
    // Basic format validation (province code check)
    $provinceCode = substr($ktp, 0, 2);
    if ($provinceCode < 11 || $provinceCode > 94) {
        return false;
    }
    
    return true;
}
?>
