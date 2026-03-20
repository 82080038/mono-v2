<?php
/**
 * Simple Database Seeder for Koperasi SaaS Application
 * Uses direct MySQL connection for XAMPP compatibility
 */

$host = '127.0.0.1';
$port = '3306';
$dbname = 'ksp_lamgabejaya_v2';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connected successfully!\n";
    
    // Seed basic data if tables are empty
    seedBasicData($pdo);
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}

function seedBasicData($pdo) {
    echo "Starting database seeding...\n";
    
    try {
        // Check and seed users if empty
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $userCount = $stmt->fetchColumn();
        
        if ($userCount < 5) {
            echo "Seeding users...\n";
            
            // Add sample users
            $users = [
                ['admin', 'admin123', 'Administrator', 'admin@ksp.com', 'Admin'],
                ['manager', 'manager123', 'Branch Manager', 'manager@ksp.com', 'Manager'],
                ['teller', 'teller123', 'Teller', 'teller@ksp.com', 'Teller'],
                ['field1', 'field123', 'Field Officer 1', 'field1@ksp.com', 'Staff'],
                ['field2', 'field123', 'Field Officer 2', 'field2@ksp.com', 'Staff']
            ];
            
            foreach ($users as $user) {
                $hashedPassword = password_hash($user[1], PASSWORD_DEFAULT);
                $sql = "INSERT IGNORE INTO users (username, password, full_name, email, role, is_active, created_at) 
                        VALUES (?, ?, ?, ?, ?, 1, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user[0], $hashedPassword, $user[2], $user[3], $user[4]]);
            }
        }
        
        // Seed members if empty
        $stmt = $pdo->query("SELECT COUNT(*) FROM members");
        $memberCount = $stmt->fetchColumn();
        
        if ($memberCount == 0) {
            echo "Seeding members...\n";
            
            $members = [
                ['MEM-001', 'Ahmad Wijaya', 'Jl. Merdeka No. 1', '08123456789', 'ahmad@email.com', '3171051203890001'],
                ['MEM-002', 'Siti Nurhaliza', 'Jl. Sudirman No. 2', '08234567890', 'siti@email.com', '3271054505900002'],
                ['MEM-003', 'Budi Santoso', 'Jl. Gatotkaca No. 3', '08345678901', 'budi@email.com', '3371051203910003'],
                ['MEM-004', 'Dewi Lestari', 'Jl. Pancasila No. 4', '08456789012', 'dewi@email.com', '3471056704920004'],
                ['MEM-005', 'Eko Prasetyo', 'Jl. Pahlawan No. 5', '08567890123', 'eko@email.com', '3571058805930005']
            ];
            
            foreach ($members as $member) {
                $sql = "INSERT INTO members (member_number, full_name, address, phone_number, email, id_number, member_type_id, registration_date, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, 5, CURDATE(), NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($member);
            }
        }
        
        // Seed accounts if empty
        $stmt = $pdo->query("SELECT COUNT(*) FROM accounts");
        $accountCount = $stmt->fetchColumn();
        
        if ($accountCount == 0) {
            echo "Seeding accounts...\n";
            
            $sql = "INSERT INTO accounts (account_number, member_id, account_type_id, account_name, balance, opening_date, status, created_at) 
                    SELECT CONCAT('ACC-', LPAD(m.id, 4, '0')), m.id, at.id, CONCAT('Rekening ', m.full_name), 1000000.00, CURDATE(), 'Active', NOW()
                    FROM members m, account_types at 
                    WHERE at.name = 'Simpans' 
                    LIMIT 5";
            $pdo->exec($sql);
        }
        
        // Seed loan types if empty
        $stmt = $pdo->query("SELECT COUNT(*) FROM loan_types");
        $loanTypeCount = $stmt->fetchColumn();
        
        if ($loanTypeCount == 0) {
            echo "Seeding loan types...\n";
            
            $loanTypes = [
                ['Pinjaman Regular', 0.02, 12, 5000000],
                ['Pinjaman Express', 0.025, 6, 3000000],
                ['Pinjaman Produktif', 0.015, 24, 10000000],
                ['Pinjaman Darurat', 0.03, 3, 2000000]
            ];
            
            foreach ($loanTypes as $type) {
                $sql = "INSERT INTO loan_types (name, interest_rate, max_term_months, max_amount, created_at) 
                        VALUES (?, ?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($type);
            }
        }
        
        echo "Database seeding completed successfully!\n";
        
    } catch(Exception $e) {
        echo "Seeding failed: " . $e->getMessage() . "\n";
    }
}

?>
