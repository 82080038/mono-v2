<?php
/**
 * Database Seeder for Koperasi SaaS Application
 * Creates sample data for testing and demonstration
 */

require_once __DIR__ . '/utils/Database.php';

class DatabaseSeeder {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function seedAll() {
        echo "Starting database seeding...\n";
        
        try {
            $this->seedCooperatives();
            $this->seedRoles();
            $this->seedUsers();
            $this->seedMembers();
            $this->seedLoans();
            $this->seedSavings();
            $this->seedTransactions();
            $this->seedCollectionQueue();
            $this->seedFieldData();
            $this->seedGpsTracking();
            
            echo "Database seeding completed successfully!\n";
            
        } catch (Exception $e) {
            echo "Seeding failed: " . $e->getMessage() . "\n";
        }
    }
    
    private function seedCooperatives() {
        $this->db->delete("cooperatives", "1=1");
        
        $cooperatives = [
            [
                'name' => 'KSP Lam Gabe Jaya',
                'code' => 'KSP-LGJ-001',
                'address' => 'Jl. Lam Gabe Jaya No. 123, Jakarta Pusat',
                'phone' => '021-12345678',
                'email' => 'info@lamgabejaya.coop',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($cooperatives as $coop) {
            $this->db->insert('cooperatives', $coop);
        }
        
        echo "✅ Cooperatives seeded\n";
    }
    
    private function seedRoles() {
        $this->db->delete("roles", "1=1");
        
        $roles = [
            ['name' => 'super_admin', 'description' => 'Super Administrator', 'permissions' => 'all'],
            ['name' => 'admin', 'description' => 'Administrator', 'permissions' => 'users,members,loans,reports,settings'],
            ['name' => 'mantri', 'description' => 'Field Officer', 'permissions' => 'field_data,gps_tracking,collection,verification'],
            ['name' => 'member', 'description' => 'Member', 'permissions' => 'profile,accounts,transactions,applications'],
            ['name' => 'kasir', 'description' => 'Cashier', 'permissions' => 'payments,cash_management'],
            ['name' => 'teller', 'description' => 'Teller', 'permissions' => 'accounts,loans,credit'],
            ['name' => 'surveyor', 'description' => 'Surveyor', 'permissions' => 'surveys,verification,field_data'],
            ['name' => 'collector', 'description' => 'Collector', 'permissions' => 'collection,overdue,reports']
        ];
        
        foreach ($roles as $role) {
            $this->db->insert('roles', $role);
        }
        
        echo "✅ Roles seeded\n";
    }
    
    private function seedUsers() {
        $this->db->delete("users", "1=1");
        
        $users = [
            [
                'cooperative_id' => 5,
                'username' => 'superadmin',
                'email' => 'test_super_admin@lamabejaya.coop',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'full_name' => 'Super Admin',
                'role' => 'admin',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'cooperative_id' => 5,
                'username' => 'admin',
                'email' => 'test_admin@lamabejaya.coop',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'full_name' => 'Admin User',
                'role' => 'admin',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'cooperative_id' => 5,
                'username' => 'mantri',
                'email' => 'test_mantri@lamabejaya.coop',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'full_name' => 'Mantri User',
                'role' => 'collector',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'cooperative_id' => 5,
                'username' => 'member',
                'email' => 'test_member@lamabejaya.coop',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'full_name' => 'Member User',
                'role' => 'member',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'cooperative_id' => 5,
                'username' => 'kasir',
                'email' => 'test_kasir@lamabejaya.coop',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'full_name' => 'Kasir User',
                'role' => 'staff',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'cooperative_id' => 5,
                'username' => 'teller',
                'email' => 'test_teller@lamabejaya.coop',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'full_name' => 'Teller User',
                'role' => 'staff',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'cooperative_id' => 5,
                'username' => 'surveyor',
                'email' => 'test_surveyor@lamabejaya.coop',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'full_name' => 'Surveyor User',
                'role' => 'staff',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'cooperative_id' => 5,
                'username' => 'collector',
                'email' => 'test_collector@lamabejaya.coop',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'role' => 'collector',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($users as $user) {
            $this->db->insert('users', $user);
        }
        
        echo "✅ Users seeded\n";
    }
    
    private function seedMembers() {
        $this->db->delete("members", "1=1");
        
        // Get actual user IDs
        $users = $this->db->fetchAll("SELECT id, role FROM users WHERE role = 'member' LIMIT 10");
        $memberUserIds = array_column($users, 'id');
        
        if (empty($memberUserIds)) {
            echo "⚠️ No member users found, skipping member seeding\n";
            return;
        }
        
        $members = [
            [
                'user_id' => $memberUserIds[0] ?? 4,
                'name' => 'Budi Santoso',
                'member_number' => 'M001',
                'email' => 'budi.santoso@email.com',
                'phone' => '08123456789',
                'address' => 'Jl. Merdeka No. 45, Jakarta Pusat',
                'join_date' => '2023-01-15',
                'membership_level' => 'gold',
                'credit_score' => 750,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'user_id' => $memberUserIds[0] ?? 4,
                'name' => 'Siti Rahayu',
                'member_number' => 'M002',
                'email' => 'siti.rahayu@email.com',
                'phone' => '08123456790',
                'address' => 'Jl. Sudirman No. 67, Jakarta Selatan',
                'join_date' => '2023-02-20',
                'membership_level' => 'silver',
                'credit_score' => 680,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'user_id' => 4,
                'name' => 'Ahmad Wijaya',
                'member_number' => 'M003',
                'email' => 'ahmad.wijaya@email.com',
                'phone' => '08123456791',
                'address' => 'Jl. Thamrin No. 89, Jakarta Pusat',
                'join_date' => '2023-03-10',
                'membership_level' => 'bronze',
                'credit_score' => 620,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($members as $member) {
            $this->db->insert('members', $member);
        }
        
        echo "✅ Members seeded\n";
    }
    
    private function seedLoans() {
        $this->db->delete("loans", "1=1");
        
        $loans = [
            [
                'member_id' => 1,
                'loan_number' => 'LN-2023-001',
                'loan_amount' => 5000000,
                'interest_rate' => 0.02,
                'loan_term' => 12,
                'outstanding_balance' => 3500000,
                'next_payment_date' => date('Y-m-d', strtotime('-5 days')),
                'last_payment_date' => date('Y-m-d', strtotime('-30 days')),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'member_id' => 2,
                'loan_number' => 'LN-2023-002',
                'loan_amount' => 3000000,
                'interest_rate' => 0.025,
                'loan_term' => 6,
                'outstanding_balance' => 1200000,
                'next_payment_date' => date('Y-m-d', strtotime('-2 days')),
                'last_payment_date' => date('Y-m-d', strtotime('-15 days')),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($loans as $loan) {
            $this->db->insert('loans', $loan);
        }
        
        echo "✅ Loans seeded\n";
    }
    
    private function seedSavings() {
        $this->db->delete("savings", "1=1");
        
        $savings = [
            [
                'member_id' => 1,
                'account_number' => 'SA001',
                'account_type' => 'savings',
                'balance' => 10000000,
                'interest_rate' => 0.05,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'member_id' => 2,
                'account_number' => 'SA002',
                'account_type' => 'savings',
                'balance' => 7500000,
                'interest_rate' => 0.05,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($savings as $saving) {
            $this->db->insert('savings', $saving);
        }
        
        echo "✅ Savings seeded\n";
    }
    
    private function seedTransactions() {
        $this->db->delete("transactions", "1=1");
        
        $transactions = [
            [
                'account_id' => 1,
                'transaction_type' => 'deposit',
                'amount' => 1000000,
                'description' => 'Monthly savings',
                'transaction_date' => date('Y-m-d'),
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'account_id' => 1,
                'transaction_type' => 'withdrawal',
                'amount' => 500000,
                'description' => 'Cash withdrawal',
                'transaction_date' => date('Y-m-d', strtotime('-1 day')),
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'account_id' => 1,
                'transaction_type' => 'loan_payment',
                'amount' => 1500000,
                'description' => 'Monthly installment',
                'transaction_date' => date('Y-m-d', strtotime('-2 days')),
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($transactions as $transaction) {
            $this->db->insert('transactions', $transaction);
        }
        
        echo "✅ Transactions seeded\n";
    }
    
    private function seedCollectionQueue() {
        $this->db->delete("collection_queue", "1=1");
        
        $queue = [
            [
                'member_id' => 1,
                'assigned_collector_id' => 8,
                'amount_due' => 1500000,
                'due_date' => date('Y-m-d', strtotime('-5 days')),
                'next_attempt_date' => date('Y-m-d'),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'member_id' => 2,
                'assigned_collector_id' => 8,
                'amount_due' => 750000,
                'due_date' => date('Y-m-d', strtotime('-2 days')),
                'next_attempt_date' => date('Y-m-d', strtotime('+1 day')),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($queue as $item) {
            $this->db->insert('collection_queue', $item);
        }
        
        echo "✅ Collection queue seeded\n";
    }
    
    private function seedFieldData() {
        $this->db->delete("field_data", "1=1");
        
        $fieldData = [
            [
                'member_id' => 1,
                'mantri_id' => 3,
                'location' => 'Jakarta Pusat',
                'gps_coordinates' => '-6.2088, 106.8456',
                'visit_date' => date('Y-m-d'),
                'visit_time' => '09:30:00',
                'purpose' => 'collection',
                'status' => 'completed',
                'notes' => 'Member paid on time',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'member_id' => 2,
                'mantri_id' => 3,
                'location' => 'Jakarta Utara',
                'gps_coordinates' => '-6.1384, 106.8759',
                'visit_date' => date('Y-m-d'),
                'visit_time' => '11:00:00',
                'purpose' => 'survey',
                'status' => 'pending',
                'notes' => 'New loan application',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($fieldData as $data) {
            $this->db->insert('field_data', $data);
        }
        
        echo "✅ Field data seeded\n";
    }
    
    private function seedGpsTracking() {
        $this->db->delete("gps_tracking", "1=1");
        
        $gpsData = [
            [
                'user_id' => 3,
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'address' => 'Jakarta Pusat, Indonesia',
                'accuracy' => 10,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'user_id' => 3,
                'latitude' => -6.1384,
                'longitude' => 106.8759,
                'address' => 'Jakarta Utara, Indonesia',
                'accuracy' => 15,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
            ]
        ];
        
        foreach ($gpsData as $data) {
            $this->db->insert('gps_tracking', $data);
        }
        
        echo "✅ GPS tracking seeded\n";
    }
}

// Run seeder
if (php_sapi_name() === 'cli') {
    $seeder = new DatabaseSeeder();
    $seeder->seedAll();
} else {
    echo "This script must be run from command line.\n";
}

?>
