<?php
/**
 * Complete Database Seed Data
 * Sample data untuk testing dan development
 */

require_once __DIR__ . '/../config/Config.php';

class DatabaseSeeder {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function seedAllData() {
        echo "🌱 Starting database seeding...\n";
        
        try {
            // 1. Seed Users
            $this->seedUsers();
            
            // 2. Seed Member Profiles
            $this->seedMemberProfiles();
            
            // 3. Seed Loans
            $this->seedLoans();
            
            // 4. Seed Savings
            $this->seedSavings();
            
            // 5. Seed Transactions
            $this->seedTransactions();
            
            // 6. Seed Reports
            $this->seedReports();
            
            echo "✅ Database seeding completed successfully!\n";
            return true;
            
        } catch (Exception $e) {
            echo "❌ Error in database seeding: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function seedUsers() {
        echo "📱 Seeding users...\n";
        
        $users = [
            [
                'name' => 'Admin Utama',
                'email' => 'admin@koperasi.co.id',
                'phone' => '08123456789',
                'role' => 'super_admin',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Administrator',
                'email' => 'administrator@koperasi.co.id',
                'phone' => '08123456788',
                'role' => 'admin',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@koperasi.co.id',
                'phone' => '08123456790',
                'role' => 'member',
                'password' => password_hash('member123', PASSWORD_DEFAULT),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti@koperasi.co.id',
                'phone' => '08123456791',
                'role' => 'member',
                'password' => password_hash('member123', PASSWORD_DEFAULT),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad@koperasi.co.id',
                'phone' => '08123456792',
                'role' => 'mantri',
                'password' => password_hash('mantri123', PASSWORD_DEFAULT),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Dewi Kartika',
                'email' => 'dewi@koperasi.co.id',
                'phone' => '08123456793',
                'role' => 'kasir',
                'password' => password_hash('kasir123', PASSWORD_DEFAULT),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Rizki Ramadhan',
                'email' => 'rizki@koperasi.co.id',
                'phone' => '08123456794',
                'role' => 'teller',
                'password' => password_hash('teller123', PASSWORD_DEFAULT),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Maya Sari',
                'email' => 'maya@koperasi.co.id',
                'phone' => '08123456795',
                'role' => 'surveyor',
                'password' => password_hash('surveyor123', PASSWORD_DEFAULT),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'eko@koperasi.co.id',
                'phone' => '08123456796',
                'role' => 'collector',
                'password' => password_hash('collector123', PASSWORD_DEFAULT),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($users as $user) {
            // Check if user already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$user['email']]);
            
            if ($stmt->rowCount() == 0) {
                $stmt = $this->db->prepare("
                    INSERT INTO users (name, email, phone, role, password, is_active, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $user['name'],
                    $user['email'],
                    $user['phone'],
                    $user['role'],
                    $user['password'],
                    $user['is_active'],
                    $user['created_at']
                ]);
                echo "   ✅ Created user: " . $user['name'] . "\n";
            } else {
                echo "   ⚠️ User already exists: " . $user['name'] . "\n";
            }
        }
    }
    
    private function seedMemberProfiles() {
        echo "👥 Seeding member profiles...\n";
        
        // Get member users
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'member'");
        $stmt->execute();
        $member_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($member_ids as $user_id) {
            // Check if profile already exists
            $stmt = $this->db->prepare("SELECT id FROM member_profiles WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            if ($stmt->rowCount() == 0) {
                $profile = [
                    'user_id' => $user_id,
                    'income' => rand(3000000, 15000000),
                    'employment_status' => ['permanent', 'contract', 'freelance'][rand(0, 2)],
                    'employment_duration' => rand(6, 60),
                    'marital_status' => ['single', 'married', 'divorced'][rand(0, 2)],
                    'dependents' => rand(0, 4),
                    'residence_type' => ['own', 'rent', 'family'][rand(0, 2)],
                    'education_level' => ['senior_high', 'bachelor', 'master'][rand(0, 2)],
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $stmt = $this->db->prepare("
                    INSERT INTO member_profiles (user_id, income, employment_status, employment_duration, 
                                            marital_status, dependents, residence_type, education_level, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $profile['user_id'],
                    $profile['income'],
                    $profile['employment_status'],
                    $profile['employment_duration'],
                    $profile['marital_status'],
                    $profile['dependents'],
                    $profile['residence_type'],
                    $profile['education_level'],
                    $profile['created_at']
                ]);
                echo "   ✅ Created profile for user ID: " . $user_id . "\n";
            }
        }
    }
    
    private function seedLoans() {
        echo "💰 Seeding loans...\n";
        
        // Get member users
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'member'");
        $stmt->execute();
        $member_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $loan_statuses = ['pending', 'approved', 'active', 'completed', 'rejected'];
        $loan_types = ['personal', 'business', 'emergency', 'education'];
        
        foreach ($member_ids as $user_id) {
            // Create 2-3 loans per member
            for ($i = 0; $i < rand(2, 3); $i++) {
                $loan = [
                    'user_id' => $user_id,
                    'loan_type' => $loan_types[rand(0, 3)],
                    'amount' => rand(1000000, 5000000),
                    'interest_rate' => rand(5, 15),
                    'duration_months' => rand(6, 24),
                    'status' => $loan_statuses[rand(0, 4)],
                    'purpose' => 'Sample loan purpose',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'))
                ];
                
                $stmt = $this->db->prepare("
                    INSERT INTO loans (user_id, loan_type, amount, interest_rate, duration_months, 
                                 status, purpose, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $loan['user_id'],
                    $loan['loan_type'],
                    $loan['amount'],
                    $loan['interest_rate'],
                    $loan['duration_months'],
                    $loan['status'],
                    $loan['purpose'],
                    $loan['created_at']
                ]);
                echo "   ✅ Created loan: " . $loan['loan_type'] . " - Rp " . number_format($loan['amount'], 0, ',', '.') . "\n";
            }
        }
    }
    
    private function seedSavings() {
        echo "🏦 Seeding savings...\n";
        
        // Get member users
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'member'");
        $stmt->execute();
        $member_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $savings_types = ['regular', 'mandatory', 'voluntary'];
        
        foreach ($member_ids as $user_id) {
            // Create 1-2 savings per member
            for ($i = 0; $i < rand(1, 2); $i++) {
                $savings = [
                    'user_id' => $user_id,
                    'type' => $savings_types[rand(0, 2)],
                    'balance' => rand(100000, 5000000),
                    'interest_rate' => rand(3, 8),
                    'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 90) . ' days'))
                ];
                
                $stmt = $this->db->prepare("
                    INSERT INTO savings (user_id, type, balance, interest_rate, created_at)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $savings['user_id'],
                    $savings['type'],
                    $savings['balance'],
                    $savings['interest_rate'],
                    $savings['created_at']
                ]);
                echo "   ✅ Created savings: " . $savings['type'] . " - Rp " . number_format($savings['balance'], 0, ',', '.') . "\n";
            }
        }
    }
    
    private function seedTransactions() {
        echo "📊 Seeding transactions...\n";
        
        // Get member users
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'member'");
        $stmt->execute();
        $member_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $transaction_types = ['deposit', 'withdrawal', 'transfer', 'loan_payment'];
        
        foreach ($member_ids as $user_id) {
            // Create 5-10 transactions per member
            for ($i = 0; $i < rand(5, 10); $i++) {
                $transaction = [
                    'user_id' => $user_id,
                    'type' => $transaction_types[rand(0, 3)],
                    'amount' => rand(50000, 2000000),
                    'description' => 'Sample transaction',
                    'status' => 'completed',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 60) . ' days'))
                ];
                
                $stmt = $this->db->prepare("
                    INSERT INTO transactions (user_id, type, amount, description, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $transaction['user_id'],
                    $transaction['type'],
                    $transaction['amount'],
                    $transaction['description'],
                    $transaction['status'],
                    $transaction['created_at']
                ]);
                echo "   ✅ Created transaction: " . $transaction['type'] . " - Rp " . number_format($transaction['amount'], 0, ',', '.') . "\n";
            }
        }
    }
    
    private function seedReports() {
        echo "📋 Seeding reports...\n";
        
        $report_types = ['monthly', 'quarterly', 'annual'];
        $report_statuses = ['draft', 'published', 'archived'];
        
        foreach ($report_types as $report_type) {
            foreach ($report_statuses as $status) {
                $report = [
                    'type' => $report_type,
                    'title' => 'Sample ' . ucfirst($report_type) . ' Report',
                    'description' => 'Sample report description',
                    'status' => $status,
                    'generated_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'))
                ];
                
                $stmt = $this->db->prepare("
                    INSERT INTO reports (type, title, description, status, generated_at)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $report['type'],
                    $report['title'],
                    $report['description'],
                    $report['status'],
                    $report['generated_at']
                ]);
                echo "   ✅ Created report: " . $report['title'] . "\n";
            }
        }
    }
}

// Run seeder if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $seeder = new DatabaseSeeder();
    $seeder->seedAllData();
}
?>