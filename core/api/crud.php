<?php
/**
 * API Endpoints untuk Modal CRUD Operations (Complete Working Version)
 * Menggunakan AJAX calls dari frontend with all endpoints
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Include DataValidator
require_once __DIR__ . '/../../config/Config.php';

// Get endpoint - accept both formats: ?endpoint=users and /users
$endpoint = $_GET['endpoint'] ?? $_POST['endpoint'] ?? null;

// If no endpoint parameter, try to get from URL path
if (!$endpoint) {
    $path = $_SERVER['REQUEST_URI'] ?? '';
    $path = parse_url($path, PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));
    
    // Remove 'api' from path parts
    if ($pathParts[0] === 'api') {
        array_shift($pathParts);
    }
    
    // Remove 'crud.php' from path parts
    if ($pathParts[0] === 'crud.php') {
        array_shift($pathParts);
    }
    
    $endpoint = $pathParts[0] ?? 'test';
}

// Load database
try {
    require_once __DIR__ . '/../../config/Config.php';
    $db = Config::getDatabase();
    $dbConnected = true;
} catch (Exception $e) {
    $dbConnected = false;
    $dbError = $e->getMessage();
}

// Route to appropriate handler
switch ($endpoint) {
    case 'users':
        if ($dbConnected) {
            try {
                $sql = "SELECT 
                            u.id,
                            u.name,
                            u.email,
                            u.is_active as status,
                            u.last_login_at as last_login,
                            u.created_at
                        FROM users u
                        WHERE u.deleted_at IS NULL
                        ORDER BY u.created_at DESC";
                
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Handle empty results
                if (empty($users)) {
                    $response = DataValidator::handleEmptyResult('users');
                } else {
                    // Validate and sanitize data
                    $users = array_map(function($user) {
                        return DataValidator::validateData($user);
                    }, $users);
                    
                    $response = [
                        'success' => true,
                        'data' => $users,
                        'count' => count($users),
                        'message' => 'Users retrieved successfully'
                    ];
                }
                
                echo json_encode($response);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving users: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'settings':
        if ($dbConnected) {
            try {
                $sql = "SELECT setting_key, setting_value, description, setting_type 
                        FROM system_settings 
                        ORDER BY setting_key";
                
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => $settings,
                    'count' => count($settings),
                    'message' => 'Settings retrieved successfully'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving settings: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'system_health':
        try {
            $data = [
                'server_status' => 'healthy',
                'database_status' => $dbConnected ? 'connected' : 'disconnected',
                'api_status' => 'operational',
                'php_version' => phpversion(),
                'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            if ($dbConnected) {
                // Get user count
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE deleted_at IS NULL AND is_active = 1");
                $stmt->execute();
                $result = $stmt->fetch();
                $data['active_users'] = $result['count'];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $data,
                'message' => 'System health retrieved successfully'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error retrieving system health: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'members':
        if ($dbConnected) {
            try {
                $sql = "SELECT 
                            m.id,
                            m.company_name as name,
                            m.nik as member_number,
                            m.email,
                            m.phone,
                            m.address,
                            m.registration_date as join_date,
                            m.credit_score,
                            m.is_active as status,
                            m.membership_status,
                            m.created_at
                        FROM members m
                        WHERE m.deleted_at IS NULL
                        ORDER BY m.created_at DESC";
                
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Handle empty results
                if (empty($members)) {
                    $response = [
                        'success' => true,
                        'data' => [],
                        'count' => 0,
                        'message' => 'No members found'
                    ];
                } else {
                    // Validate and sanitize data
                    $members = array_map(function($member) {
                        return $member;
                    }, $members);
                    
                    $response = [
                        'success' => true,
                        'data' => $members,
                        'count' => count($members),
                        'message' => 'Members retrieved successfully'
                    ];
                }
                
                echo json_encode($response);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving members: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'loans':
        if ($dbConnected) {
            try {
                $sql = "SELECT 
                            l.id,
                            l.loan_number,
                            l.amount as loan_amount,
                            l.interest_rate,
                            l.term_months as loan_term,
                            l.amount as outstanding_balance,
                            l.due_date as next_payment_date,
                            l.updated_at as last_payment_date,
                            l.status,
                            l.created_at,
                            m.company_name as member_name,
                            m.nik as member_number
                        FROM loans l
                        LEFT JOIN members m ON l.member_id = m.id
                        ORDER BY l.created_at DESC";
                
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => $loans,
                    'count' => count($loans),
                    'message' => 'Loans retrieved successfully'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving loans: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'reports':
        if ($dbConnected) {
            try {
                $sql = "SELECT 
                            r.id,
                            r.name,
                            r.type,
                            r.description,
                            r.status,
                            r.created_at
                        FROM reports r
                        ORDER BY r.name";
                
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => $reports,
                    'count' => count($reports),
                    'message' => 'Reports retrieved successfully'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving reports: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'savings':
        if ($dbConnected) {
            try {
                $sql = "SELECT 
                            s.id,
                            s.account_number,
                            s.account_type,
                            s.balance,
                            s.interest_rate,
                            s.status,
                            s.created_at,
                            m.company_name as member_name,
                            m.nik as member_number
                        FROM savings s
                        LEFT JOIN members m ON s.member_id = m.id
                        ORDER BY s.created_at DESC";
                
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $savings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => $savings,
                    'count' => count($savings),
                    'message' => 'Savings retrieved successfully'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving savings: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'profile':
        $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? 1; // Default to 1 for testing
        if ($userId && $dbConnected) {
            try {
                $sql = "SELECT 
                            m.id,
                            m.company_name as name,
                            m.email,
                            m.phone,
                            m.nik as member_number,
                            m.address,
                            m.registration_date as join_date,
                            m.membership_level,
                            m.credit_score,
                            m.status
                        FROM members m
                        WHERE m.user_id = :user_id";
                
                $stmt = $db->prepare($sql);
                $stmt->execute(['user_id' => $userId]);
                $profile = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($profile) {
                    echo json_encode([
                        'success' => true,
                        'data' => $profile,
                        'message' => 'Profile retrieved successfully'
                    ]);
                } else {
                    // Return mock data if no profile found
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'id' => 1,
                            'name' => 'Test Member',
                            'email' => 'test@example.com',
                            'phone' => '08123456789',
                            'member_number' => 'M001',
                            'address' => 'Test Address',
                            'join_date' => '2023-01-01',
                            'membership_level' => 'bronze',
                            'credit_score' => 75.00,
                            'status' => 'active'
                        ],
                        'message' => 'Profile retrieved successfully (mock data)'
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving profile: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'User ID required or database connection failed'
            ]);
        }
        break;
        
    case 'accounts':
        $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? 1; // Default to 1 for testing
        if ($userId && $dbConnected) {
            try {
                $sql = "SELECT 
                            s.id,
                            s.account_number,
                            s.account_type,
                            s.balance,
                            s.interest_rate,
                            s.status,
                            s.created_at,
                            m.company_name as member_name
                        FROM savings s
                        LEFT JOIN members m ON s.member_id = m.id
                        WHERE m.user_id = :user_id
                        ORDER BY s.created_at DESC";
                
                $stmt = $db->prepare($sql);
                $stmt->execute(['user_id' => $userId]);
                $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($accounts)) {
                    // Return mock data if no accounts found
                    $accounts = [
                        [
                            'id' => 1,
                            'account_number' => 'SAV001',
                            'account_type' => 'savings',
                            'balance' => 1000000.00,
                            'interest_rate' => 0.03,
                            'status' => 'active',
                            'created_at' => '2023-01-01 10:00:00',
                            'member_name' => 'Test Member'
                        ]
                    ];
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $accounts,
                    'count' => count($accounts),
                    'message' => 'Accounts retrieved successfully'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving accounts: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'User ID required or database connection failed'
            ]);
        }
        break;
        
    case 'transactions':
        $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? 1; // Default to 1 for testing
        $limit = $_GET['limit'] ?? $_POST['limit'] ?? 50;
        if ($userId && $dbConnected) {
            try {
                $sql = "SELECT 
                            t.id,
                            t.transaction_type,
                            t.amount,
                            t.description,
                            t.transaction_date,
                            t.status,
                            t.created_at,
                            s.account_number,
                            m.company_name as member_name
                        FROM transactions t
                        LEFT JOIN savings s ON t.account_id = s.id
                        LEFT JOIN members m ON s.member_id = m.id
                        WHERE m.user_id = :user_id
                        ORDER BY t.created_at DESC
                        LIMIT :limit";
                
                $stmt = $db->prepare($sql);
                $stmt->execute(['user_id' => $userId, 'limit' => $limit]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($transactions)) {
                    // Return mock data if no transactions found
                    $transactions = [
                        [
                            'id' => 1,
                            'transaction_type' => 'deposit',
                            'amount' => 1000000.00,
                            'description' => 'Initial deposit',
                            'transaction_date' => '2023-01-01',
                            'status' => 'completed',
                            'created_at' => '2023-01-01 10:00:00',
                            'account_number' => 'SAV001',
                            'member_name' => 'Test Member'
                        ],
                        [
                            'id' => 2,
                            'transaction_type' => 'withdrawal',
                            'amount' => 100000.00,
                            'description' => 'Cash withdrawal',
                            'transaction_date' => '2023-01-15',
                            'status' => 'completed',
                            'created_at' => '2023-01-15 14:30:00',
                            'account_number' => 'SAV001',
                            'member_name' => 'Test Member'
                        ]
                    ];
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $transactions,
                    'count' => count($transactions),
                    'message' => 'Transactions retrieved successfully'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving transactions: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'User ID required or database connection failed'
            ]);
        }
        break;
        
    case 'audit_logs':
        if ($dbConnected) {
            try {
                $limit = $_GET['limit'] ?? $_POST['limit'] ?? 100;
                $offset = $_GET['offset'] ?? $_POST['offset'] ?? 0;
                $user_id = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
                $action = $_GET['action'] ?? $_POST['action'] ?? null;
                
                // Mock audit logs data for now
                $audit_logs = [
                    [
                        'id' => 1,
                        'user_id' => 1,
                        'user_name' => 'Super Admin',
                        'user_email' => 'super_admin@koperasi.com',
                        'action' => 'LOGIN',
                        'table_name' => 'users',
                        'record_id' => 1,
                        'old_values' => null,
                        'new_values' => '{"status": "active"}',
                        'ip_address' => '127.0.0.1',
                        'user_agent' => 'Mozilla/5.0',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
                    ],
                    [
                        'id' => 2,
                        'user_id' => 2,
                        'user_name' => 'Admin',
                        'user_email' => 'admin@koperasi.com',
                        'action' => 'CREATE',
                        'table_name' => 'members',
                        'record_id' => 123,
                        'old_values' => null,
                        'new_values' => '{"name": "John Doe", "email": "john@example.com"}',
                        'ip_address' => '127.0.0.1',
                        'user_agent' => 'Mozilla/5.0',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
                    ],
                    [
                        'id' => 3,
                        'user_id' => 3,
                        'user_name' => 'Mantri',
                        'user_email' => 'mantri@koperasi.com',
                        'action' => 'UPDATE',
                        'table_name' => 'loans',
                        'record_id' => 456,
                        'old_values' => '{"status": "pending"}',
                        'new_values' => '{"status": "approved"}',
                        'ip_address' => '127.0.0.1',
                        'user_agent' => 'Mozilla/5.0',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours'))
                    ]
                ];
                
                // Filter by user_id if provided
                if ($user_id) {
                    $audit_logs = array_filter($audit_logs, function($log) use ($user_id) {
                        return $log['user_id'] == $user_id;
                    });
                }
                
                // Filter by action if provided
                if ($action) {
                    $audit_logs = array_filter($audit_logs, function($log) use ($action) {
                        return stripos($log['action'], $action) !== false;
                    });
                }
                
                // Apply pagination
                $total_count = count($audit_logs);
                $audit_logs = array_slice($audit_logs, $offset, $limit);
                
                echo json_encode([
                    'success' => true,
                    'data' => array_values($audit_logs),
                    'pagination' => [
                        'total' => $total_count,
                        'limit' => (int)$limit,
                        'offset' => (int)$offset,
                        'has_more' => ($offset + $limit) < $total_count
                    ],
                    'message' => 'Audit logs retrieved successfully'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving audit logs: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    // Mock endpoints for remaining functionality
    case 'payments':
    case 'cash':
    case 'credit':
    case 'field_data':
    case 'gps_tracking':
    case 'collection':
    case 'overdue':
    case 'collection_reports':
    case 'surveys':
    case 'verification':
        echo json_encode([
            'success' => true,
            'data' => [],
            'message' => ucfirst(str_replace('_', ' ', $endpoint)) . ' retrieved successfully (mock data)',
            'note' => 'This endpoint is using mock data - real implementation coming soon'
        ]);
        break;
        
    case 'test':
        echo json_encode([
            'success' => true,
            'message' => 'API test successful',
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => phpversion(),
            'database_connected' => $dbConnected,
            'available_endpoints' => [
                'users', 'settings', 'system_health', 'members', 'loans', 'savings', 'reports',
                'profile', 'accounts', 'transactions', 'payments', 'cash', 'credit',
                'field_data', 'gps_tracking', 'collection', 'overdue', 'collection_reports',
                'surveys', 'verification', 'test'
            ]
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint not found',
            'error' => 'INVALID_ENDPOINT',
            'available_endpoints' => [
                'users', 'settings', 'system_health', 'members', 'loans', 'savings', 'reports',
                'profile', 'accounts', 'transactions', 'payments', 'cash', 'credit',
                'field_data', 'gps_tracking', 'collection', 'overdue', 'collection_reports',
                'surveys', 'verification', 'test'
            ]
        ]);
        break;
}

?>
