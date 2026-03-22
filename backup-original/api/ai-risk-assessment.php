<?php
/**
 * batch-update-legacy.php - Updated with Security
 * Auto-generated security update
 */

// Security headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token");

// Handle preflight requests
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    exit(0);
}

// Prevent direct access
if (!defined("KSP_API_ACCESS")) {
    header("HTTP/1.0 403 Forbidden");
    exit("Direct access to this file is not allowed.");
}

// Include required files
require_once __DIR__ . "/../config/Config.php";
require_once __DIR__ . "/DatabaseHelper.php";
require_once __DIR__ . "/Logger.php";
require_once __DIR__ . "/DataValidator.php";
require_once __DIR__ . "/SecurityLogger.php";
require_once __DIR__ . "/AuthHelper.php";
require_once __DIR__ . "/SecurityHelper.php";
require_once __DIR__ . "/SecurityMiddleware.php";

// Initialize services
Logger::initialize();
$securityLogger = SecurityLogger::getInstance();
$db = DatabaseHelper::getInstance();
$validator = new DataValidator();

// Initialize response
$response = [
    "success" => false,
    "message" => "",
    "data" => null,
    "errors" => [],
    "timestamp" => date("Y-m-d H:i:s")
];

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

// Check authentication
$token = $_REQUEST['token'] ?? '';
if (empty($token)) {
    SecurityMiddleware::sendJSONResponse($response);
    exit();
}

$user = validateToken($token);
if (!$user) {
    SecurityMiddleware::sendJSONResponse($response);
    exit();
}

try {
    $pdo = getDatabaseConnection();
    
    $action = $_REQUEST["action"] ?? "assess_loan";
    
    switch ($action) {
        case "assess_loan":
            if (!in_array($user['role'], ['Super Admin', 'Admin', 'Manager', 'Owner'])) {
                SecurityMiddleware::sendJSONResponse($response);
                break;
            }
            
            $memberId = $_REQUEST['member_id'] ?? 0;
            $loanAmount = $_REQUEST['loan_amount'] ?? 0;
            $loanTerm = $_REQUEST['loan_term'] ?? 12;
            
            $riskAssessment = assessLoanRisk($pdo, $memberId, $loanAmount, $loanTerm);
            
            SecurityMiddleware::sendJSONResponse($response);
            break;
            
        case "predict_default":
            $memberId = $_REQUEST['member_id'] ?? 0;
            
            $defaultPrediction = predictDefaultRisk($pdo, $memberId);
            
            SecurityMiddleware::sendJSONResponse($response);
            break;
            
        case "member_risk_profile":
            $memberId = $_REQUEST['member_id'] ?? 0;
            
            $riskProfile = getMemberRiskProfile($pdo, $memberId);
            
            SecurityMiddleware::sendJSONResponse($response);
            break;
            
        case "batch_risk_assessment":
            if (!in_array($user['role'], ['Super Admin', 'Admin', 'Manager', 'Owner'])) {
                SecurityMiddleware::sendJSONResponse($response);
                break;
            }
            
            $limit = $_REQUEST['limit'] ?? 50;
            
            $batchResults = batchRiskAssessment($pdo, $limit);
            
            SecurityMiddleware::sendJSONResponse($response);
            break;
            
        case "risk_trends":
            $period = $_REQUEST['period'] ?? '30'; // days
            
            $trends = getRiskTrends($pdo, $period);
            
            SecurityMiddleware::sendJSONResponse($response);
            break;
            
        default:
            SecurityMiddleware::sendJSONResponse($response);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

// AI Risk Assessment Functions
function assessLoanRisk($pdo, $memberId, $loanAmount, $loanTerm) {
    // Get member data
    $stmt = $pdo->prepare("
        SELECT m.*, 
               COUNT(l.id) as total_loans,
               SUM(CASE WHEN l.status = 'Active' THEN 1 ELSE 0 END) as active_loans,
               SUM(CASE WHEN l.status = 'Default' THEN 1 ELSE 0 END) as defaulted_loans,
               SUM(l.amount) as total_borrowed,
               SUM(CASE WHEN l.status = 'Paid Off' THEN l.total_payment ELSE 0 END) as total_repaid
        FROM members m
        LEFT JOIN loans l ON m.id = l.member_id
        WHERE m.id = ?
        GROUP BY m.id
    ");
    $stmt->execute([$memberId]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        return ["error" => "Member not found"];
    }
    
    // Calculate risk factors
    $riskFactors = calculateRiskFactors($member, $loanAmount, $loanTerm);
    
    // Calculate risk score (0-100, higher = riskier)
    $riskScore = calculateRiskScore($riskFactors);
    
    // Determine risk category
    $riskCategory = getRiskCategory($riskScore);
    
    // Generate recommendations
    $recommendations = generateRecommendations($riskFactors, $riskScore);
    
    // Calculate interest rate based on risk
    $recommendedInterestRate = calculateInterestRate($riskScore);
    
    // Predict monthly payment capability
    $paymentCapability = predictPaymentCapability($member, $loanAmount, $loanTerm);
    
    return [
        "member_id" => $memberId,
        "member_name" => $member['full_name'],
        "loan_amount" => $loanAmount,
        "loan_term" => $loanTerm,
        "risk_score" => $riskScore,
        "risk_category" => $riskCategory,
        "risk_factors" => $riskFactors,
        "recommendations" => $recommendations,
        "recommended_interest_rate" => $recommendedInterestRate,
        "payment_capability" => $paymentCapability,
        "approval_probability" => calculateApprovalProbability($riskScore),
        "assessment_date" => date('Y-m-d H:i:s')
    ];
}

function calculateRiskFactors($member, $loanAmount, $loanTerm) {
    $factors = [];
    
    // Payment history factor (30%)
    $totalLoans = $member['total_loans'] ?: 0;
    $defaultedLoans = $member['defaulted_loans'] ?: 0;
    $paymentHistoryScore = $totalLoans > 0 ? max(0, 100 - ($defaultedLoans / $totalLoans * 100)) : 70;
    $factors['payment_history'] = [
        "score" => $paymentHistoryScore,
        "weight" => 0.30,
        "details" => [
            "total_loans" => $totalLoans,
            "defaulted_loans" => $defaultedLoans,
            "default_rate" => $totalLoans > 0 ? round($defaultedLoans / $totalLoans * 100, 2) : 0
        ]
    ];
    
    // Debt-to-income ratio (25%)
    $monthlyIncome = $member['monthly_income'] ?: 0;
    $activeLoans = $member['active_loans'] ?: 0;
    $monthlyDebt = $activeLoans * 1000000; // Estimated average monthly payment
    $debtToIncomeRatio = $monthlyIncome > 0 ? ($monthlyDebt / $monthlyIncome) * 100 : 0;
    $debtRatioScore = max(0, 100 - $debtToIncomeRatio);
    $factors['debt_to_income'] = [
        "score" => $debtRatioScore,
        "weight" => 0.25,
        "details" => [
            "monthly_income" => $monthlyIncome,
            "estimated_monthly_debt" => $monthlyDebt,
            "debt_to_income_ratio" => round($debtToIncomeRatio, 2)
        ]
    ];
    
    // Loan amount to income ratio (20%)
    $monthlyPayment = calculateMonthlyPayment($loanAmount, 0.015, $loanTerm);
    $paymentToIncomeRatio = $monthlyIncome > 0 ? ($monthlyPayment / $monthlyIncome) * 100 : 100;
    $loanAmountScore = max(0, 100 - $paymentToIncomeRatio);
    $factors['loan_amount_ratio'] = [
        "score" => $loanAmountScore,
        "weight" => 0.20,
        "details" => [
            "loan_amount" => $loanAmount,
            "monthly_payment" => $monthlyPayment,
            "payment_to_income_ratio" => round($paymentToIncomeRatio, 2)
        ]
    ];
    
    // Employment stability (15%)
    $employmentScore = 70; // Base score
    if ($member['occupation'] && strtolower($member['occupation']) !== 'pengangguran') {
        $employmentScore += 20;
    }
    if ($member['marital_status'] === 'Married') {
        $employmentScore += 10;
    }
    $factors['employment_stability'] = [
        "score" => min(100, $employmentScore),
        "weight" => 0.15,
        "details" => [
            "occupation" => $member['occupation'],
            "marital_status" => $member['marital_status']
        ]
    ];
    
    // Credit history (10%)
    $creditScore = 75; // Base score for new members
    if ($totalLoans > 0) {
        $repaymentRate = ($member['total_repaid'] ?: 0) / ($member['total_borrowed'] ?: 1);
        $creditScore = min(100, 50 + ($repaymentRate * 50));
    }
    $factors['credit_history'] = [
        "score" => $creditScore,
        "weight" => 0.10,
        "details" => [
            "total_borrowed" => $member['total_borrowed'] ?: 0,
            "total_repaid" => $member['total_repaid'] ?: 0,
            "repayment_rate" => isset($repaymentRate) ? round($repaymentRate * 100, 2) : 0
        ]
    ];
    
    return $factors;
}

function calculateRiskScore($riskFactors) {
    $totalScore = 0;
    $totalWeight = 0;
    
    foreach ($riskFactors as $factor) {
        $totalScore += $factor['score'] * $factor['weight'];
        $totalWeight += $factor['weight'];
    }
    
    return round($totalScore / $totalWeight, 2);
}

function getRiskCategory($riskScore) {
    if ($riskScore >= 80) return "LOW";
    if ($riskScore >= 60) return "MEDIUM";
    if ($riskScore >= 40) return "HIGH";
    return "VERY_HIGH";
}

function generateRecommendations($riskFactors, $riskScore) {
    $recommendations = [];
    
    if ($riskFactors['payment_history']['score'] < 70) {
        $recommendations[] = "Consider smaller loan amount due to payment history";
    }
    
    if ($riskFactors['debt_to_income']['score'] < 60) {
        $recommendations[] = "High debt-to-income ratio - recommend debt consolidation first";
    }
    
    if ($riskFactors['loan_amount_ratio']['score'] < 50) {
        $recommendations[] = "Loan amount may be too high for income level";
    }
    
    if ($riskScore < 40) {
        $recommendations[] = "High risk - recommend collateral or guarantor";
        $recommendations[] = "Consider shorter loan term to reduce risk";
    }
    
    if ($riskScore >= 70) {
        $recommendations[] = "Good risk profile - eligible for preferential rates";
    }
    
    return $recommendations;
}

function calculateInterestRate($riskScore) {
    // Base rate for rentenir model
    $baseRate = 0.03; // 3% per month (36% per year)
    
    // Risk adjustment
    if ($riskScore >= 80) return $baseRate - 0.005; // 2.5% for low risk
    if ($riskScore >= 60) return $baseRate; // 3% for medium risk
    if ($riskScore >= 40) return $baseRate + 0.01; // 4% for high risk
    return $baseRate + 0.02; // 5% for very high risk
}

function predictPaymentCapability($member, $loanAmount, $loanTerm) {
    $monthlyIncome = $member['monthly_income'] ?: 0;
    $monthlyPayment = calculateMonthlyPayment($loanAmount, 0.015, $loanTerm);
    
    $estimatedLivingCost = $monthlyIncome * 0.4; // 40% for living expenses
    $availableForPayment = $monthlyIncome - $estimatedLivingCost;
    
    return [
        "monthly_income" => $monthlyIncome,
        "monthly_payment" => $monthlyPayment,
        "available_for_payment" => $availableForPayment,
        "payment_gap" => $monthlyPayment - $availableForPayment,
        "can_afford" => $availableForPayment >= $monthlyPayment
    ];
}

function calculateApprovalProbability($riskScore) {
    // Approval probability based on risk score for rentenir model
    if ($riskScore >= 80) return 0.95; // 95% approval
    if ($riskScore >= 60) return 0.80; // 80% approval
    if ($riskScore >= 40) return 0.60; // 60% approval
    return 0.30; // 30% approval for very high risk
}

function calculateMonthlyPayment($principal, $monthlyRate, $months) {
    if ($monthlyRate == 0) return $principal / $months;
    
    $r = $monthlyRate;
    $n = $months;
    
    return $principal * ($r * pow(1 + $r, $n)) / (pow(1 + $r, $n) - 1);
}

function predictDefaultRisk($pdo, $memberId) {
    // Get member's loan history
    $stmt = $pdo->prepare("
        SELECT l.*, DATEDIFF(l.updated_at, l.created_at) as loan_duration_days
        FROM loans l
        WHERE l.member_id = ?
        ORDER BY l.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$memberId]);
    $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($loans)) {
        return [
            "default_probability" => 0.15, // 15% for new members
            "confidence_level" => 0.5,
            "factors" => ["No credit history available"]
        ];
    }
    
    // Calculate default probability based on history
    $totalLoans = count($loans);
    $defaultedLoans = 0;
    $latePayments = 0;
    
    foreach ($loans as $loan) {
        if ($loan['status'] === 'Default') $defaultedLoans++;
        if ($loan['status'] === 'Late') $latePayments++;
    }
    
    $defaultRate = $totalLoans > 0 ? $defaultedLoans / $totalLoans : 0;
    $lateRate = $totalLoans > 0 ? $latePayments / $totalLoans : 0;
    
    // Adjust for rentenir model (more lenient)
    $baseProbability = $defaultRate * 0.7 + $lateRate * 0.3;
    $adjustedProbability = max(0.05, min(0.8, $baseProbability));
    
    return [
        "default_probability" => round($adjustedProbability, 3),
        "confidence_level" => min(0.9, $totalLoans / 10),
        "historical_data" => [
            "total_loans" => $totalLoans,
            "defaulted_loans" => $defaultedLoans,
            "late_payments" => $latePayments,
            "default_rate" => round($defaultRate * 100, 2),
            "late_rate" => round($lateRate * 100, 2)
        ]
    ];
}

function getMemberRiskProfile($pdo, $memberId) {
    $riskAssessment = assessLoanRisk($pdo, $memberId, 5000000, 12);
    $defaultPrediction = predictDefaultRisk($pdo, $memberId);
    
    return [
        "member_id" => $memberId,
        "risk_score" => $riskAssessment['risk_score'] ?? 0,
        "risk_category" => $riskAssessment['risk_category'] ?? 'UNKNOWN',
        "default_probability" => $defaultPrediction['default_probability'],
        "approval_probability" => $riskAssessment['approval_probability'] ?? 0,
        "recommended_max_loan" => calculateMaxLoanAmount($riskAssessment['risk_score'] ?? 0),
        "recommended_interest_rate" => $riskAssessment['recommended_interest_rate'] ?? 0.03,
        "last_updated" => date('Y-m-d H:i:s')
    ];
}

function batchRiskAssessment($pdo, $limit) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT m.id, m.full_name
        FROM members m
        LEFT JOIN loans l ON m.id = l.member_id AND l.status = 'Active'
        WHERE m.status = 'Active'
        ORDER BY m.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $results = [];
    foreach ($members as $member) {
        $riskProfile = getMemberRiskProfile($pdo, $member['id']);
        $results[] = [
            "member_id" => $member['id'],
            "member_name" => $member['full_name'],
            "risk_score" => $riskProfile['risk_score'],
            "risk_category" => $riskProfile['risk_category'],
            "default_probability" => $riskProfile['default_probability']
        ];
    }
    
    return $results;
}

function getRiskTrends($pdo, $period) {
    $stmt = $pdo->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as total_loans,
            SUM(CASE WHEN status = 'Default' THEN 1 ELSE 0 END) as defaults,
            SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late_payments
        FROM loans 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ");
    $stmt->execute([$period]);
    $dailyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate trends
    $totalLoans = array_sum(array_column($dailyData, 'total_loans'));
    $totalDefaults = array_sum(array_column($dailyData, 'defaults'));
    $totalLate = array_sum(array_column($dailyData, 'late_payments'));
    
    $defaultRate = $totalLoans > 0 ? ($totalDefaults / $totalLoans) * 100 : 0;
    $lateRate = $totalLoans > 0 ? ($totalLate / $totalLoans) * 100 : 0;
    
    return [
        "period_days" => $period,
        "summary" => [
            "total_loans" => $totalLoans,
            "total_defaults" => $totalDefaults,
            "total_late_payments" => $totalLate,
            "default_rate" => round($defaultRate, 2),
            "late_rate" => round($lateRate, 2)
        ],
        "daily_breakdown" => $dailyData
    ];
}

function calculateMaxLoanAmount($riskScore) {
    $baseMultiplier = 5; // 5x monthly income base
    
    if ($riskScore >= 80) return $baseMultiplier * 1.5; // 7.5x income
    if ($riskScore >= 60) return $baseMultiplier; // 5x income
    if ($riskScore >= 40) return $baseMultiplier * 0.7; // 3.5x income
    return $baseMultiplier * 0.5; // 2.5x income
}
?>
