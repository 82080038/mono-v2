<?php
/**
 * Reward Points API
 * Handles reward points system for members
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Include required files
require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/DatabaseHelper.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/DataValidator.php';
require_once __DIR__ . '/SecurityLogger.php';

// Initialize services
Logger::initialize();
$securityLogger = SecurityLogger::getInstance();
$db = DatabaseHelper::getInstance();
$validator = new DataValidator();

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'data' => null,
    'errors' => [],
    'timestamp' => date('Y-m-d H:i:s')
];

// Authentication middleware
function requireAuth($role = null) {
    global $db;
    
    $token = getTokenFromRequest();
    if (!$token) {
        throw new Exception('Authentication required');
    }
    
    $tokenData = validateJWTToken($token);
    if (!$tokenData) {
        throw new Exception('Invalid token');
    }
    
    $user = $db->fetchOne(
        "SELECT * FROM users WHERE id = ? AND is_active = 1",
        [$tokenData['user_id']]
    );
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    if ($role && $user['role'] !== $role && $user['role'] !== 'admin') {
        throw new Exception('Insufficient privileges');
    }
    
    return array_merge($user, $tokenData);
}

function getTokenFromRequest() {
    $headers = getallheaders();
    return $headers['Authorization'] ?? $headers['authorization'] ?? $_REQUEST['token'] ?? null;
}

function validateJWTToken($token) {
    if (!$token) {
        return null;
    }
    
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }
    
    $payload = base64_decode($parts[1]);
    $payloadData = json_decode($payload, true);
    
    if (!$payloadData || $payloadData['exp'] < time()) {
        return null;
    }
    
    return $payloadData;
}

// Handle API requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($action, $db, $validator);
            break;
        case 'POST':
            handlePostRequest($action, $db, $validator);
            break;
        case 'PUT':
            handlePutRequest($action, $db, $validator);
            break;
        default:
            $response['message'] = 'Method not allowed';
            echo json_encode($response);
            break;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    echo json_encode($response);
}

function handleGetRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'balance':
            handleGetPointsBalance($db, $validator);
            break;
        case 'history':
            handleGetPointsHistory($db, $validator);
            break;
        case 'rewards':
            handleGetAvailableRewards($db, $validator);
            break;
        case 'redemptions':
            handleGetRedemptions($db, $validator);
            break;
        case 'leaderboard':
            handleGetLeaderboard($db, $validator);
            break;
        case 'rules':
            handleGetPointsRules($db, $validator);
            break;
        case 'statistics':
            handleGetPointsStatistics($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handlePostRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'earn':
            handleEarnPoints($db, $validator);
            break;
        case 'redeem':
            handleRedeemReward($db, $validator);
            break;
        case 'transfer':
            handleTransferPoints($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handlePutRequest($action, $db, $validator) {
    global $response;
    
    switch ($action) {
        case 'adjust':
            handleAdjustPoints($db, $validator);
            break;
        case 'expire':
            handleExpirePoints($db, $validator);
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
}

function handleGetPointsBalance($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    // Get member information
    $member = $db->fetchOne("SELECT id, member_number, full_name FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Get current points balance
    $balance = $db->fetchOne(
        "SELECT COALESCE(SUM(points), 0) as current_balance 
         FROM reward_points 
         WHERE member_id = ? AND expires_at > CURDATE() AND is_active = 1",
        [$member['id']]
    )['current_balance'];
    
    // Get points breakdown by category
    $breakdown = $db->fetchAll(
        "SELECT category, COALESCE(SUM(points), 0) as total_points, COUNT(*) as transaction_count
         FROM reward_points 
         WHERE member_id = ? AND expires_at > CURDATE() AND is_active = 1
         GROUP BY category",
        [$member['id']]
    );
    
    // Get points about to expire
    $expiringSoon = $db->fetchAll(
        "SELECT SUM(points) as points, expires_at
         FROM reward_points 
         WHERE member_id = ? AND expires_at BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND is_active = 1
         GROUP BY expires_at
         ORDER BY expires_at ASC",
        [$member['id']]
    );
    
    // Get member level and benefits
    $memberLevel = getMemberLevel($db, $member['id']);
    
    $pointsData = [
        'member' => $member,
        'current_balance' => $balance,
        'breakdown' => $breakdown,
        'expiring_soon' => $expiringSoon,
        'member_level' => $memberLevel,
        'next_level' => getNextLevel($memberLevel),
        'points_to_next_level' => getPointsToNextLevel($balance, $memberLevel),
        'earning_rate' => getEarningRate($member['id']),
        'last_updated' => date('Y-m-d H:i:s')
    ];
    
    $response['success'] = true;
    $response['message'] = 'Points balance retrieved successfully';
    $response['data'] = $pointsData;
    
    echo json_encode($response);
}

function handleGetPointsHistory($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $category = $_GET['category'] ?? '';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = ["member_id = ?"];
    $params = [$member['id']];
    
    if (!empty($category)) {
        $whereConditions[] = "category = ?";
        $params[] = $category;
    }
    
    if (!empty($dateFrom)) {
        $whereConditions[] = "created_at >= ?";
        $params[] = $dateFrom . ' 00:00:00';
    }
    
    if (!empty($dateTo)) {
        $whereConditions[] = "created_at <= ?";
        $params[] = $dateTo . ' 23:59:59';
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM reward_points $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get points history
    $sql = "SELECT rp.*, 
                    CASE 
                        WHEN rp.points > 0 THEN 'Earned'
                        ELSE 'Spent'
                    END as transaction_type,
                    DATEDIFF(rp.expires_at, CURDATE()) as days_until_expiry
             FROM reward_points rp 
             $whereClause
             ORDER BY rp.created_at DESC 
             LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $history = $db->fetchAll($sql, $params);
    
    foreach ($history as &$item) {
        $item['category_display'] = getCategoryDisplay($item['category']);
        $item['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($item['created_at']));
        $item['expires_at_formatted'] = date('Y-m-d', strtotime($item['expires_at']));
        $item['is_expiring_soon'] = $item['days_until_expiry'] <= 30 && $item['days_until_expiry'] >= 0;
        
        // Get related data if available
        if ($item['reference_id']) {
            $item['related_data'] = getRelatedPointsData($item['reference_id'], $item['category']);
        }
    }
    
    $response['success'] = true;
    $response['message'] = 'Points history retrieved successfully';
    $response['data'] = [
        'history' => $history,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
    
    echo json_encode($response);
}

function handleGetAvailableRewards($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    // Get member information and points balance
    $member = $db->fetchOne("SELECT id, member_number FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    $currentBalance = $db->fetchOne(
        "SELECT COALESCE(SUM(points), 0) as balance 
         FROM reward_points 
         WHERE member_id = ? AND expires_at > CURDATE() AND is_active = 1",
        [$member['id']]
    )['balance'];
    
    $memberLevel = getMemberLevel($db, $member['id']);
    
    // Get available rewards
    $rewards = $db->fetchAll(
        "SELECT r.*, 
                CASE 
                    WHEN r.points_required <= ? THEN 'Available'
                    ELSE 'Not Available'
                END as availability,
                (r.stock_quantity > 0) as in_stock
         FROM rewards r 
         WHERE r.is_active = 1 AND r.available_from <= CURDATE() AND r.available_until >= CURDATE()
         ORDER BY r.points_required ASC",
        [$currentBalance]
    );
    
    foreach ($rewards as &$reward) {
        $reward['points_required_formatted'] = number_format($reward['points_required']);
        $reward['can_redeem'] = $reward['points_required'] <= $currentBalance && $reward['in_stock'];
        $reward['member_eligible'] = isMemberEligibleForReward($member['id'], $reward['id'], $memberLevel);
        $reward['category_display'] = getRewardCategoryDisplay($reward['category']);
        $reward['value_display'] = formatRewardValue($reward['reward_type'], $reward['reward_value']);
        
        // Get redemption statistics
        $stats = $db->fetchOne(
            "SELECT COUNT(*) as total_redemptions, COUNT(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as recent_redemptions
             FROM reward_redemptions 
             WHERE reward_id = ?",
            [$reward['id']]
        );
        $reward['redemption_stats'] = $stats;
    }
    
    $response['success'] = true;
    $response['message'] = 'Available rewards retrieved successfully';
    $response['data'] = [
        'rewards' => $rewards,
        'member_points' => $currentBalance,
        'member_level' => $memberLevel,
        'categories' => getRewardCategories($db)
    ];
    
    echo json_encode($response);
}

function handleGetRedemptions($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $status = $_GET['status'] ?? '';
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    $offset = ($page - 1) * $limit;
    
    $whereConditions = ["rr.member_id = ?"];
    $params = [$member['id']];
    
    if (!empty($status)) {
        $whereConditions[] = "rr.status = ?";
        $params[] = $status;
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM reward_redemptions rr $whereClause";
    $total = $db->fetchOne($countSql, $params)['total'];
    
    // Get redemptions
    $sql = "SELECT rr.*, r.name as reward_name, r.reward_type, r.reward_value
         FROM reward_redemptions rr 
         LEFT JOIN rewards r ON rr.reward_id = r.id 
         $whereClause
         ORDER BY rr.created_at DESC 
         LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $redemptions = $db->fetchAll($sql, $params);
    
    foreach ($redemptions as &$redemption) {
        $redemption['status_display'] = ucfirst($redemption['status']);
        $redemption['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($redemption['created_at']));
        $redemption['value_display'] = formatRewardValue($redemption['reward_type'], $redemption['reward_value']);
        
        if ($redemption['processed_at']) {
            $redemption['processed_at_formatted'] = date('Y-m-d H:i:s', strtotime($redemption['processed_at']));
        }
    }
    
    $response['success'] = true;
    $response['message'] = 'Reward redemptions retrieved successfully';
    $response['data'] = [
        'redemptions' => $redemptions,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
    
    echo json_encode($response);
}

function handleGetLeaderboard($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    $period = $_GET['period'] ?? 'month';
    $limit = (int)($_GET['limit'] ?? 10);
    
    // Get current member's position
    $currentMember = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$currentMember) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    $dateCondition = getDateCondition($period);
    
    // Get top members
    $leaderboard = $db->fetchAll(
        "SELECT m.id, m.full_name, m.member_number,
                COALESCE(SUM(rp.points), 0) as total_points,
                COUNT(rp.id) as transactions_count
         FROM members m 
         LEFT JOIN reward_points rp ON m.id = rp.member_id AND $dateCondition AND rp.is_active = 1
         WHERE m.status = 'Active'
         GROUP BY m.id 
         HAVING total_points > 0
         ORDER BY total_points DESC, m.full_name ASC 
         LIMIT ?",
        [$limit]
    );
    
    // Add rankings
    foreach ($leaderboard as $index => &$member) {
        $member['rank'] = $index + 1;
        $member['points_formatted'] = number_format($member['total_points']);
        $member['member_level'] = getMemberLevel($db, $member['id']);
        
        // Check if this is the current user
        if ($member['id'] === $currentMember['id']) {
            $member['is_current_user'] = true;
        }
    }
    
    // Get current user's rank if not in top list
    $currentUserRank = null;
    $foundInTop = false;
    
    foreach ($leaderboard as $member) {
        if ($member['id'] === $currentMember['id']) {
            $foundInTop = true;
            break;
        }
    }
    
    if (!$foundInTop) {
        $currentUserPoints = $db->fetchOne(
            "SELECT COALESCE(SUM(rp.points), 0) as total_points
             FROM reward_points rp 
             WHERE rp.member_id = ? AND $dateCondition AND rp.is_active = 1",
            [$currentMember['id']]
        )['total_points'];
        
        if ($currentUserPoints > 0) {
            $currentUserRank = $db->fetchOne(
                "SELECT COUNT(*) + 1 as rank
                 FROM (
                     SELECT m.id, COALESCE(SUM(rp.points), 0) as total_points
                     FROM members m 
                     LEFT JOIN reward_points rp ON m.id = rp.member_id AND $dateCondition AND rp.is_active = 1
                     WHERE m.status = 'Active'
                     GROUP BY m.id 
                     HAVING total_points > ?
                 ) ranked_members",
                [$currentUserPoints]
            )['rank'];
        }
    }
    
    $response['success'] = true;
    $response['message'] = 'Leaderboard retrieved successfully';
    $response['data'] = [
        'leaderboard' => $leaderboard,
        'current_user_rank' => $currentUserRank,
        'period' => $period,
        'total_participants' => $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE status = 'Active'")['count']
    ];
    
    echo json_encode($response);
}

function handleGetPointsRules($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    // Get points earning rules
    $earningRules = $db->fetchAll(
        "SELECT * FROM points_rules WHERE is_active = 1 AND rule_type = 'earning' ORDER BY points_value DESC"
    );
    
    // Get points spending rules
    $spendingRules = $db->fetchAll(
        "SELECT * FROM points_rules WHERE is_active = 1 AND rule_type = 'spending' ORDER BY points_value ASC"
    );
    
    // Get member level benefits
    $levelBenefits = $db->fetchAll(
        "SELECT mlb.*, ml.level_name
         FROM member_level_benefits mlb
         JOIN member_levels ml ON mlb.level_id = ml.id
         WHERE ml.is_active = 1
         ORDER BY ml.level_order ASC"
    );
    
    // Get current member's level for context
    $currentMember = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    $currentLevel = $currentMember ? getMemberLevel($db, $currentMember['id']) : null;
    
    $response['success'] = true;
    $response['message'] = 'Points rules retrieved successfully';
    $response['data'] = [
        'earning_rules' => $earningRules,
        'spending_rules' => $spendingRules,
        'level_benefits' => $levelBenefits,
        'current_member_level' => $currentLevel,
        'general_info' => [
            'points_expiry_days' => 365,
            'minimum_redemption' => 100,
            'transfer_fee_percentage' => 2,
            'maximum_transfer_per_day' => 1000
        ]
    ];
    
    echo json_encode($response);
}

function handleGetPointsStatistics($db, $validator) {
    global $response;
    
    $user = requireAuth();
    
    // Get member information
    $member = $db->fetchOne("SELECT id, join_date FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    $period = $_GET['period'] ?? 'year';
    $dateCondition = getDateCondition($period);
    
    $statistics = [
        'overview' => [
            'total_earned' => $db->fetchOne("SELECT COALESCE(SUM(points), 0) as total FROM reward_points WHERE member_id = ? AND points > 0 AND is_active = 1", [$member['id']])['total'],
            'total_spent' => abs($db->fetchOne("SELECT COALESCE(SUM(points), 0) as total FROM reward_points WHERE member_id = ? AND points < 0 AND is_active = 1", [$member['id']])['total']),
            'current_balance' => $db->fetchOne("SELECT COALESCE(SUM(points), 0) as total FROM reward_points WHERE member_id = ? AND expires_at > CURDATE() AND is_active = 1", [$member['id']])['total'],
            'total_transactions' => $db->fetchOne("SELECT COUNT(*) as count FROM reward_points WHERE member_id = ? AND is_active = 1", [$member['id']])['count']
        ],
        'earning_breakdown' => $db->fetchAll(
            "SELECT category, COALESCE(SUM(points), 0) as total_points, COUNT(*) as transaction_count
             FROM reward_points 
             WHERE member_id = ? AND points > 0 AND is_active = 1
             GROUP BY category
             ORDER BY total_points DESC",
            [$member['id']]
        ),
        'spending_breakdown' => $db->fetchAll(
            "SELECT category, COALESCE(ABS(SUM(points)), 0) as total_points, COUNT(*) as transaction_count
             FROM reward_points 
             WHERE member_id = ? AND points < 0 AND is_active = 1
             GROUP BY category
             ORDER BY total_points DESC",
            [$member['id']]
        ),
        'monthly_trends' => $db->fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                    COALESCE(SUM(CASE WHEN points > 0 THEN points ELSE 0 END), 0) as earned,
                    COALESCE(ABS(SUM(CASE WHEN points < 0 THEN points ELSE 0 END)), 0) as spent
             FROM reward_points 
             WHERE member_id = ? AND is_active = 1 AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY month",
            [$member['id']]
        ),
        'achievements' => [
            'total_redemptions' => $db->fetchOne("SELECT COUNT(*) as count FROM reward_redemptions WHERE member_id = ?", [$member['id']])['count'],
            'favorite_category' => $db->fetchOne(
                "SELECT category, COUNT(*) as count 
                 FROM reward_redemptions 
                 WHERE member_id = ? 
                 GROUP BY category 
                 ORDER BY count DESC 
                 LIMIT 1",
                [$member['id']]
            ),
            'highest_single_redemption' => $db->fetchOne(
                "SELECT MAX(points_used) as max_points 
                 FROM reward_redemptions 
                 WHERE member_id = ?",
                [$member['id']]
            )['max_points']
        ],
        'membership_stats' => [
            'membership_days' => (strtotime(date('Y-m-d')) - strtotime($member['join_date'])) / 86400,
            'average_points_per_day' => 0,
            'points_per_month' => $db->fetchAll(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COALESCE(SUM(points), 0) as points
                 FROM reward_points 
                 WHERE member_id = ? AND is_active = 1 AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                 ORDER BY month",
                [$member['id']]
            )
        ]
    ];
    
    // Calculate average points per day
    if ($statistics['overview']['total_transactions'] > 0) {
        $statistics['membership_stats']['average_points_per_day'] = round($statistics['overview']['total_earned'] / $statistics['membership_stats']['membership_days'], 2);
    }
    
    $response['success'] = true;
    $response['message'] = 'Points statistics retrieved successfully';
    $response['data'] = $statistics;
    
    echo json_encode($response);
}

function handleEarnPoints($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'category' => 'required|in:loan_payment,savings_deposit,referral,login,milestone,bonus,penalty',
        'points' => 'required|integer|min:1',
        'description' => 'required|string|min:5',
        'reference_id' => 'integer',
        'expires_at' => 'date'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Validate points earning rules
    if (!validatePointsEarning($db, $member['id'], $input['category'], $input['points'], $input['reference_id'])) {
        $response['message'] = 'Points earning validation failed';
        echo json_encode($response);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Create points record
        $pointsData = [
            'member_id' => $member['id'],
            'points' => $input['points'],
            'category' => $input['category'],
            'description' => $input['description'],
            'reference_id' => $input['reference_id'] ?? null,
            'expires_at' => $input['expires_at'] ?? date('Y-m-d', strtotime('+1 year')),
            'is_active' => true,
            'created_by' => $user['id'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $pointsId = $db->insert('reward_points', $pointsData);
        
        // Update member statistics
        updateMemberPointsStats($db, $member['id']);
        
        // Check for level upgrade
        checkMemberLevelUpgrade($db, $member['id']);
        
        // Create notification
        $db->insert('notifications', [
            'user_id' => $user['id'],
            'title' => 'Points Earned!',
            'message' => "You've earned {$input['points']} points for {$input['category']}",
            'type' => 'success',
            'reference_id' => $pointsId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $db->commit();
        
        $response['success'] = true;
        $response['message'] = 'Points earned successfully';
        $response['data'] = [
            'points_id' => $pointsId,
            'points_earned' => $input['points'],
            'new_balance' => getCurrentPointsBalance($db, $member['id'])
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleRedeemReward($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'reward_id' => 'required|integer',
        'quantity' => 'required|integer|min:1',
        'delivery_address' => 'string',
        'notes' => 'string'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Get member information
    $member = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$member) {
        $response['message'] = 'Member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Get reward details
    $reward = $db->fetchOne("SELECT * FROM rewards WHERE id = ? AND is_active = 1", [$input['reward_id']]);
    
    if (!$reward) {
        $response['message'] = 'Reward not found';
        echo json_encode($response);
        return;
    }
    
    // Check member eligibility
    $memberLevel = getMemberLevel($db, $member['id']);
    if (!isMemberEligibleForReward($member['id'], $reward['id'], $memberLevel)) {
        $response['message'] = 'Not eligible for this reward';
        echo json_encode($response);
        return;
    }
    
    // Check points balance
    $currentBalance = getCurrentPointsBalance($db, $member['id']);
    $requiredPoints = $reward['points_required'] * $input['quantity'];
    
    if ($currentBalance < $requiredPoints) {
        $response['message'] = 'Insufficient points balance';
        SecurityMiddleware::sendJSONResponse($response);
        return;
    }
    
    // Check stock availability
    if ($reward['stock_quantity'] < $input['quantity']) {
        $response['message'] = 'Insufficient stock';
        SecurityMiddleware::sendJSONResponse($response);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Create redemption record
        $redemptionData = [
            'member_id' => $member['id'],
            'reward_id' => $input['reward_id'],
            'quantity' => $input['quantity'],
            'points_used' => $requiredPoints,
            'status' => 'pending',
            'delivery_address' => $input['delivery_address'] ?? '',
            'notes' => $input['notes'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $redemptionId = $db->insert('reward_redemptions', $redemptionData);
        
        // Deduct points
        $pointsDeductionData = [
            'member_id' => $member['id'],
            'points' => -$requiredPoints,
            'category' => 'reward_redemption',
            'description' => "Redeemed {$reward['name']} (x{$input['quantity']})",
            'reference_id' => $redemptionId,
            'expires_at' => date('Y-m-d', strtotime('+1 year')),
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $db->insert('reward_points', $pointsDeductionData);
        
        // Update reward stock
        $db->update('rewards', ['stock_quantity' => $reward['stock_quantity'] - $input['quantity']], 'id = ?', [$input['reward_id']]);
        
        // Create notification
        $db->insert('notifications', [
            'user_id' => $user['id'],
            'title' => 'Reward Redeemed!',
            'message' => "You've successfully redeemed {$reward['name']} (x{$input['quantity']})",
            'type' => 'success',
            'reference_id' => $redemptionId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $db->commit();
        
        $response['success'] = true;
        $response['message'] = 'Reward redeemed successfully';
        $response['data'] = [
            'redemption_id' => $redemptionId,
            'points_used' => $requiredPoints,
            'new_balance' => getCurrentPointsBalance($db, $member['id']),
            'reward' => $reward
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleTransferPoints($db, $validator) {
    global $response;
    
    $user = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'to_member_number' => 'required|string',
        'points' => 'required|integer|min:1',
        'message' => 'string|min:5'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Get sender member information
    $senderMember = $db->fetchOne("SELECT id FROM members WHERE user_id = ?", [$user['id']]);
    
    if (!$senderMember) {
        $response['message'] = 'Sender member profile not found';
        echo json_encode($response);
        return;
    }
    
    // Get recipient member information
    $recipientMember = $db->fetchOne("SELECT * FROM members WHERE member_number = ? AND status = 'Active'", [$input['to_member_number']]);
    
    if (!$recipientMember) {
        $response['message'] = 'Recipient member not found';
        echo json_encode($response);
        return;
    }
    
    // Check if transferring to self
    if ($senderMember['id'] === $recipientMember['id']) {
        $response['message'] = 'Cannot transfer points to yourself';
        echo json_encode($response);
        return;
    }
    
    // Check sender's points balance
    $currentBalance = getCurrentPointsBalance($db, $senderMember['id']);
    $transferFee = round($input['points'] * 0.02); // 2% transfer fee
    $totalRequired = $input['points'] + $transferFee;
    
    if ($currentBalance < $totalRequired) {
        $response['message'] = 'Insufficient points balance (including transfer fee)';
        echo json_encode($response);
        return;
    }
    
    // Check daily transfer limit
    $todayTransfers = $db->fetchOne(
        "SELECT COALESCE(SUM(points), 0) as total 
         FROM reward_points 
         WHERE member_id = ? AND category = 'points_transfer' AND DATE(created_at) = CURDATE() AND points < 0",
        [$senderMember['id']]
    )['total'];
    
    if (abs($todayTransfers) + $input['points'] > 1000) {
        $response['message'] = 'Daily transfer limit exceeded';
        echo json_encode($response);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Deduct points from sender
        $senderDeductionData = [
            'member_id' => $senderMember['id'],
            'points' => -$totalRequired,
            'category' => 'points_transfer',
            'description' => "Points transfer to {$input['to_member_number']} (fee: {$transferFee} points)",
            'reference_id' => $recipientMember['id'],
            'expires_at' => date('Y-m-d', strtotime('+1 year')),
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $db->insert('reward_points', $senderDeductionData);
        
        // Add points to recipient
        $recipientAdditionData = [
            'member_id' => $recipientMember['id'],
            'points' => $input['points'],
            'category' => 'points_transfer',
            'description' => "Points received from {$senderMember['member_number']}",
            'reference_id' => $senderMember['id'],
            'expires_at' => date('Y-m-d', strtotime('+1 year')),
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $db->insert('reward_points', $recipientAdditionData);
        
        // Create notifications
        $db->insert('notifications', [
            'user_id' => $user['id'],
            'title' => 'Points Transferred',
            'message' => "You've transferred {$input['points']} points to {$input['to_member_number']}",
            'type' => 'info',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $recipientUser = $db->fetchOne("SELECT id FROM users WHERE id = ?", [$recipientMember['user_id']]);
        if ($recipientUser) {
            $db->insert('notifications', [
                'user_id' => $recipientUser['id'],
                'title' => 'Points Received',
                'message' => "You've received {$input['points']} points from {$senderMember['member_number']}",
                'type' => 'success',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        $db->commit();
        
        $response['success'] = true;
        $response['message'] = 'Points transferred successfully';
        $response['data'] = [
            'points_transferred' => $input['points'],
            'transfer_fee' => $transferFee,
            'total_deducted' => $totalRequired,
            'new_balance' => getCurrentPointsBalance($db, $senderMember['id']),
            'recipient' => [
                'member_number' => $recipientMember['member_number'],
                'full_name' => $recipientMember['full_name']
            ]
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleAdjustPoints($db, $validator) {
    global $response;
    
    $user = requireAuth('admin');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rules = [
        'member_id' => 'required|integer',
        'points' => 'required|integer',
        'category' => 'required|in:loan_payment,savings_deposit,referral,login,milestone,bonus,penalty,adjustment',
        'description' => 'required|string|min:5',
        'reason' => 'required|string|min:10'
    ];
    
    if (!$validator->validate($input, $rules)) {
        $response['errors'] = $validator->getErrors();
        $response['message'] = 'Validation failed';
        echo json_encode($response);
        return;
    }
    
    // Verify member exists
    $member = $db->fetchOne("SELECT * FROM members WHERE id = ?", [$input['member_id']]);
    
    if (!$member) {
        $response['message'] = 'Member not found';
        echo json_encode($response);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Create points adjustment record
        $pointsData = [
            'member_id' => $input['member_id'],
            'points' => $input['points'],
            'category' => $input['category'],
            'description' => $input['description'],
            'expires_at' => date('Y-m-d', strtotime('+1 year')),
            'is_active' => true,
            'created_by' => $user['id'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $pointsId = $db->insert('reward_points', $pointsData);
        
        // Log adjustment
        $db->insert('audit_logs', [
            'user_id' => $user['id'],
            'action' => 'points_adjustment',
            'table_name' => 'reward_points',
            'record_id' => $pointsId,
            'old_values' => json_encode(['points' => 0]),
            'new_values' => json_encode(['points' => $input['points']]),
            'description' => $input['reason'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Create notification for member
        $memberUser = $db->fetchOne("SELECT id FROM users WHERE id = ?", [$member['user_id']]);
        if ($memberUser) {
            $action = $input['points'] > 0 ? 'credited' : 'debited';
            $db->insert('notifications', [
                'user_id' => $memberUser['id'],
                'title' => 'Points Adjustment',
                'message' => "Your points have been {$action} by " . abs($input['points']) . " points",
                'type' => $input['points'] > 0 ? 'success' : 'warning',
                'reference_id' => $pointsId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        $db->commit();
        
        $response['success'] = true;
        $response['message'] = 'Points adjusted successfully';
        $response['data'] = [
            'points_id' => $pointsId,
            'member' => $member,
            'points_adjusted' => $input['points'],
            'new_balance' => getCurrentPointsBalance($db, $input['member_id'])
        ];
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
    echo json_encode($response);
}

function handleExpirePoints($db, $validator) {
    global $response;
    
    $user = requireAuth('admin');
    
    // Get expired points
    $expiredPoints = $db->fetchAll(
        "SELECT * FROM reward_points 
         WHERE expires_at < CURDATE() AND is_active = 1"
    );
    
    $expiredCount = 0;
    
    foreach ($expiredPoints as $points) {
        // Mark as expired
        $db->update('reward_points', ['is_active' => false], 'id = ?', [$points['id']]);
        
        // Create notification
        $member = $db->fetchOne("SELECT user_id FROM members WHERE id = ?", [$points['member_id']]);
        if ($member) {
            $db->insert('notifications', [
                'user_id' => $member['user_id'],
                'title' => 'Points Expired',
                'message' => abs($points['points']) . " points have expired",
                'type' => 'warning',
                'reference_id' => $points['id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        $expiredCount++;
    }
    
    $response['success'] = true;
    $response['message'] = 'Points expiration processed';
    $response['data'] = ['expired_count' => $expiredCount];
    
    echo json_encode($response);
}

// Helper functions
function getDateCondition($period) {
    switch ($period) {
        case 'day':
            return 'DATE(rp.created_at) = CURDATE()';
        case 'week':
            return 'rp.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
        case 'month':
            return 'rp.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
        case 'quarter':
            return 'rp.created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)';
        case 'year':
            return 'rp.created_at >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)';
        default:
            return 'rp.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
    }
}

function getCategoryDisplay($category) {
    $displays = [
        'loan_payment' => 'Loan Payment',
        'savings_deposit' => 'Savings Deposit',
        'referral' => 'Referral',
        'login' => 'Daily Login',
        'milestone' => 'Milestone',
        'bonus' => 'Bonus',
        'penalty' => 'Penalty',
        'reward_redemption' => 'Reward Redemption',
        'points_transfer' => 'Points Transfer',
        'adjustment' => 'Adjustment'
    ];
    
    return $displays[$category] ?? $category;
}

function getRelatedPointsData($referenceId, $category) {
    global $db;
    
    switch ($category) {
        case 'loan_payment':
            return $db->fetchOne("SELECT loan_number, amount FROM payment_transactions WHERE id = ?", [$referenceId]);
        case 'savings_deposit':
            return $db->fetchOne("SELECT account_number, amount FROM payment_transactions WHERE id = ?", [$referenceId]);
        case 'reward_redemption':
            return $db->fetchOne("SELECT name, quantity FROM reward_redemptions WHERE id = ?", [$referenceId]);
        case 'points_transfer':
            return $db->fetchOne("SELECT full_name, member_number FROM members WHERE id = ?", [$referenceId]);
        default:
            return null;
    }
}

function getMemberLevel($db, $memberId) {
    $totalPoints = $db->fetchOne(
        "SELECT COALESCE(SUM(points), 0) as total 
         FROM reward_points 
         WHERE member_id = ? AND expires_at > CURDATE() AND is_active = 1",
        [$memberId]
    )['total'];
    
    // Simple level calculation based on points
    if ($totalPoints >= 10000) {
        return ['level' => 'Platinum', 'order' => 4];
    } elseif ($totalPoints >= 5000) {
        return ['level' => 'Gold', 'order' => 3];
    } elseif ($totalPoints >= 2000) {
        return ['level' => 'Silver', 'order' => 2];
    } else {
        return ['level' => 'Bronze', 'order' => 1];
    }
}

function getNextLevel($currentLevel) {
    $levels = ['Bronze' => 1, 'Silver' => 2, 'Gold' => 3, 'Platinum' => 4];
    $currentOrder = $levels[$currentLevel['level']] ?? 1;
    
    if ($currentOrder < 4) {
        $nextLevelName = array_search($currentOrder + 1, $levels);
        return ['level' => $nextLevelName, 'order' => $currentOrder + 1];
    }
    
    return null;
}

function getPointsToNextLevel($currentBalance, $currentLevel) {
    $levelThresholds = [
        'Bronze' => 0,
        'Silver' => 2000,
        'Gold' => 5000,
        'Platinum' => 10000
    ];
    
    $nextLevel = getNextLevel($currentLevel);
    if ($nextLevel) {
        return $levelThresholds[$nextLevel['level']] - $currentBalance;
    }
    
    return 0;
}

function getEarningRate($memberId) {
    // Calculate points earning rate based on member activity
    global $db;
    
    $lastMonthPoints = $db->fetchOne(
        "SELECT COALESCE(SUM(points), 0) as total 
         FROM reward_points 
         WHERE member_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND points > 0",
        [$memberId]
    )['total'];
    
    return $lastMonthPoints; // Points earned last month
}

function getRewardCategoryDisplay($category) {
    $displays = [
        'voucher' => 'Voucher',
        'discount' => 'Discount',
        'product' => 'Product',
        'service' => 'Service',
        'cashback' => 'Cashback',
        'experience' => 'Experience'
    ];
    
    return $displays[$category] ?? $category;
}

function formatRewardValue($type, $value) {
    switch ($type) {
        case 'voucher':
        case 'discount':
            return 'Rp ' . number_format($value, 2, ',', '.');
        case 'cashback':
            return 'Rp ' . number_format($value, 2, ',', '.');
        case 'product':
            return $value;
        case 'service':
            return $value;
        default:
            return $value;
    }
}

function getRewardCategories($db) {
    return $db->fetchAll("SELECT DISTINCT category, COUNT(*) as count FROM rewards WHERE is_active = 1 GROUP BY category");
}

function isMemberEligibleForReward($memberId, $rewardId, $memberLevel) {
    global $db;
    
    // Check if reward has level restrictions
    $reward = $db->fetchOne("SELECT * FROM rewards WHERE id = ?", [$rewardId]);
    
    if (!$reward) {
        return false;
    }
    
    // Check if member level meets requirements
    if ($reward['required_level'] && $memberLevel['order'] < $reward['required_level']) {
        return false;
    }
    
    // Check other eligibility criteria
    return true;
}

function getCurrentPointsBalance($db, $memberId) {
    return $db->fetchOne(
        "SELECT COALESCE(SUM(points), 0) as balance 
         FROM reward_points 
         WHERE member_id = ? AND expires_at > CURDATE() AND is_active = 1",
        [$memberId]
    )['balance'];
}

function validatePointsEarning($db, $memberId, $category, $points, $referenceId) {
    // Validate against duplicate earning rules
    if ($category === 'loan_payment' && $referenceId) {
        $existing = $db->fetchOne(
            "SELECT COUNT(*) as count 
             FROM reward_points 
             WHERE member_id = ? AND category = ? AND reference_id = ? AND is_active = 1",
            [$memberId, $category, $referenceId]
        )['count'];
        
        if ($existing > 0) {
            return false; // Already earned points for this reference
        }
    }
    
    return true;
}

function updateMemberPointsStats($db, $memberId) {
    // This would update member statistics table if it exists
    // For now, it's a placeholder
}

function checkMemberLevelUpgrade($db, $memberId) {
    // Check if member should be upgraded to next level
    $currentLevel = getMemberLevel($db, $memberId);
    $nextLevel = getNextLevel($currentLevel);
    
    if ($nextLevel) {
        // Create notification for level upgrade
        $member = $db->fetchOne("SELECT user_id FROM members WHERE id = ?", [$memberId]);
        if ($member) {
            $db->insert('notifications', [
                'user_id' => $member['user_id'],
                'title' => 'Level Upgraded!',
                'message' => "Congratulations! You've reached {$nextLevel['level']} level!",
                'type' => 'success',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}
?>
