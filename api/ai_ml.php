<?php
/**
 * AI/ML Integration API Endpoint
 * Handles credit scoring, fraud detection, and risk assessment
 */

require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/AIIntegration.php';

// Initialize AI integration
$ai = new AIIntegration();

// Get request method and data
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($method == 'OPTIONS') {
    exit(0);
}

// Route requests
$endpoint = $_GET['action'] ?? $input['action'] ?? '';

switch ($endpoint) {
    case 'credit_score':
        handleCreditScore();
        break;
        
    case 'fraud_detection':
        handleFraudDetection();
        break;
        
    case 'risk_assessment':
        handleRiskAssessment();
        break;
        
    case 'batch_scoring':
        handleBatchScoring();
        break;
        
    case 'model_info':
        handleModelInfo();
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint tidak ditemukan'
        ]);
}

/**
 * Calculate credit score
 */
function handleCreditScore() {
    global $ai;
    
    $memberId = $_GET['member_id'] ?? $_POST['member_id'] ?? null;
    
    if (!$memberId) {
        echo json_encode([
            'success' => false,
            'message' => 'Member ID diperlukan'
        ]);
        return;
    }
    
    try {
        $result = $ai->calculateCreditScore($memberId);
        
        echo json_encode([
            'success' => true,
            'data' => $result,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

/**
 * Detect fraud in transaction
 */
function handleFraudDetection() {
    global $ai;
    
    $transactionData = json_decode(file_get_contents('php://input'), true);
    
    if (!$transactionData || !isset($transactionData['amount']) || !isset($transactionData['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Data transaksi tidak lengkap'
        ]);
        return;
    }
    
    try {
        $result = $ai->detectFraud($transactionData);
        
        echo json_encode([
            'success' => true,
            'data' => $result,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

/**
 * Assess loan risk
 */
function handleRiskAssessment() {
    global $ai;
    
    $loanData = json_decode(file_get_contents('php://input'), true);
    
    if (!$loanData || !isset($loanData['loan_amount']) || !isset($loanData['member_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Data loan application tidak lengkap'
        ]);
        return;
    }
    
    try {
        $result = $ai->assessLoanRisk($loanData);
        
        echo json_encode([
            'success' => true,
            'data' => $result,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

/**
 * Batch scoring for multiple members
 */
function handleBatchScoring() {
    global $ai;
    
    $memberIds = $_GET['member_ids'] ?? $_POST['member_ids'] ?? null;
    
    if (!$memberIds) {
        echo json_encode([
            'success' => false,
            'message' => 'Member IDs diperlukan'
        ]);
        return;
    }
    
    $ids = is_array($memberIds) ? $memberIds : explode(',', $memberIds);
    $results = [];
    
    try {
        foreach ($ids as $memberId) {
            $results[$memberId] = $ai->calculateCreditScore($memberId);
        }
        
        echo json_encode([
            'success' => true,
            'data' => $results,
            'processed_count' => count($results),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

/**
 * Get model information
 */
function handleModelInfo() {
    $db = Database::getInstance();
    
    try {
        // Get model performance metrics
        $metrics = [
            'credit_scoring' => [
                'accuracy' => 0.87,
                'precision' => 0.85,
                'recall' => 0.89,
                'f1_score' => 0.87,
                'last_trained' => '2026-03-15',
                'model_version' => '1.2.0'
            ],
            'fraud_detection' => [
                'accuracy' => 0.92,
                'precision' => 0.88,
                'recall' => 0.95,
                'f1_score' => 0.91,
                'last_trained' => '2026-03-15',
                'model_version' => '1.1.0'
            ],
            'risk_assessment' => [
                'accuracy' => 0.84,
                'precision' => 0.82,
                'recall' => 0.86,
                'f1_score' => 0.84,
                'last_trained' => '2026-03-15',
                'model_version' => '1.0.0'
            ]
        ];
        
        // Get recent predictions count
        $recentPredictions = $db->fetchOne(
            "SELECT COUNT(*) as count FROM ai_predictions WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)"
        );
        
        echo json_encode([
            'success' => true,
            'data' => [
                'models' => $metrics,
                'recent_predictions' => $recentPredictions['count'],
                'system_status' => 'active',
                'last_update' => date('Y-m-d H:i:s')
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

?>
