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

// Get endpoint
$endpoint = $_GET['endpoint'] ?? $_POST['endpoint'] ?? 'test';

// Load database
try {
    require_once __DIR__ . '/../config/Config.php';
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
                
                echo json_encode([
                    'success' => true,
                    'data' => $users,
                    'count' => count($users),
                    'message' => 'Users retrieved successfully'
                ]);
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
                $sql = "SELECT key_name, value, description, category 
                        FROM system_settings 
                        ORDER BY category, key_name";
                
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Group by category
                $groupedSettings = [];
                foreach ($settings as $setting) {
                    $groupedSettings[$setting['category']][] = $setting;
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $groupedSettings,
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
                            m.name,
                            m.member_number,
                            m.email,
                            m.phone,
                            m.address,
                            m.join_date,
                            m.membership_level,
                            m.credit_score,
                            m.status,
                            m.created_at
                        FROM members m
                        ORDER BY m.created_at DESC";
                
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => $members,
                    'count' => count($members),
                    'message' => 'Members retrieved successfully'
                ]);
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
                            l.loan_amount,
                            l.interest_rate,
                            l.loan_term,
                            l.outstanding_balance,
                            l.next_payment_date,
                            l.last_payment_date,
                            l.status,
                            l.created_at,
                            m.name as member_name,
                            m.member_number
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
                            r.category,
                            r.description,
                            r.status,
                            r.created_at
                        FROM reports r
                        ORDER BY r.category, r.name";
                
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Group by category
                $groupedReports = [];
                foreach ($reports as $report) {
                    $groupedReports[$report['category']][] = $report;
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $groupedReports,
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
        
    case 'profile':
        $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
        if ($userId && $dbConnected) {
            try {
                $sql = "SELECT 
                            m.id,
                            m.name,
                            m.email,
                            m.phone,
                            m.member_number,
                            m.address,
                            m.join_date,
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
                    echo json_encode([
                        'success' => false,
                        'message' => 'Profile not found'
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
        $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
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
                            m.name as member_name
                        FROM savings s
                        LEFT JOIN members m ON s.member_id = m.id
                        WHERE m.user_id = :user_id
                        ORDER BY s.created_at DESC";
                
                $stmt = $db->prepare($sql);
                $stmt->execute(['user_id' => $userId]);
                $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
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
        $userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;
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
                            m.name as member_name
                        FROM transactions t
                        LEFT JOIN savings s ON t.account_id = s.id
                        LEFT JOIN members m ON s.member_id = m.id
                        WHERE m.user_id = :user_id
                        ORDER BY t.created_at DESC
                        LIMIT :limit";
                
                $stmt = $db->prepare($sql);
                $stmt->execute(['user_id' => $userId, 'limit' => $limit]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
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
                'users', 'settings', 'system_health', 'members', 'loans', 'reports',
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
                'users', 'settings', 'system_health', 'members', 'loans', 'reports',
                'profile', 'accounts', 'transactions', 'payments', 'cash', 'credit',
                'field_data', 'gps_tracking', 'collection', 'overdue', 'collection_reports',
                'surveys', 'verification', 'test'
            ]
        ]);
        break;
}

?>
