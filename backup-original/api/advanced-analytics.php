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
    
    $action = $_REQUEST["action"] ?? "dashboard";
    
    switch ($action) {
        case "dashboard":
            $period = $_REQUEST['period'] ?? '30'; // days
            $dashboard = getDashboardAnalytics($pdo, $period);
            
            SecurityMiddleware::sendJSONResponse($response);
            break;
            
        case "staff_performance":
            $period = $_REQUEST['period'] ?? '30';
            $staffId = $_REQUEST['staff_id'] ?? null;
            
            $performance = getStaffPerformanceAnalytics($pdo, $period, $staffId);
            
            SecurityMiddleware::sendJSONResponse($response);
            break;
            
        case "profitability_analysis":
            $period = $_REQUEST['period'] ?? '30';
            
            $profitability = getProfitabilityAnalysis($pdo, $period);
            
            SecurityMiddleware::sendJSONResponse($response);
            break;
            
        case "customer_lifetime_value":
            $memberId = $_REQUEST['member_id'] ?? null;
            
            $clvAnalysis = getCustomerLifetimeValue($pdo, $memberId);
            
            SecurityMiddleware::sendJSONResponse($response);
            break;
            
        case "collection_efficiency":
            $period = $_REQUEST['period'] ?? '30';
            
            $efficiency = getCollectionEfficiency($pdo, $period);
            
            SecurityMiddleware::sendJSONResponse($response);
            break;
            
        case "predictive_analytics":
            $period = $_REQUEST['period'] ?? '30';
            
            $predictions = getPredictiveAnalytics($pdo, $period);
            
            SecurityMiddleware::sendJSONResponse($response);
            break;
            
        case "market_analysis":
            $period = $_REQUEST['period'] ?? '30';
            
            $marketAnalysis = getMarketAnalysis($pdo, $period);
            
            SecurityMiddleware::sendJSONResponse($response);
            break;
            
        default:
            SecurityMiddleware::sendJSONResponse($response);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

// Advanced Analytics Functions
function getDashboardAnalytics($pdo, $period) {
    $startDate = date('Y-m-d', strtotime("-$period days"));
    
    // Basic metrics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT m.id) as total_members,
            COUNT(DISTINCT CASE WHEN m.status = 'Active' THEN m.id END) as active_members,
            COUNT(DISTINCT l.id) as total_loans,
            COUNT(DISTINCT CASE WHEN l.status = 'Active' THEN l.id END) as active_loans,
            SUM(l.amount) as total_loan_portfolio,
            SUM(CASE WHEN l.status = 'Active' THEN l.amount ELSE 0 END) as active_portfolio,
            SUM(l.outstanding_balance) as total_outstanding,
            AVG(l.amount) as avg_loan_size
        FROM members m
        LEFT JOIN loans l ON m.id = l.member_id
    ");
    $stmt->execute();
    $basicMetrics = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Period performance
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as new_loans,
            SUM(l.amount) as new_loan_amount,
            AVG(l.amount) as avg_new_loan,
            COUNT(CASE WHEN l.status = 'Approved' THEN 1 END) as approved_loans,
            SUM(CASE WHEN l.status = 'Approved' THEN l.amount ELSE 0 END) as approved_amount
        FROM loans l
        WHERE l.created_at >= ?
    ");
    $stmt->execute([$startDate]);
    $periodPerformance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Collection performance
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_collections,
            SUM(p.amount) as total_collected,
            AVG(p.amount) as avg_collection,
            COUNT(DISTINCT p.member_id) as unique_members_collected
        FROM payments p
        WHERE p.created_at >= ?
    ");
    $stmt->execute([$startDate]);
    $collectionPerformance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Risk metrics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_loans_period,
            COUNT(CASE WHEN status = 'Default' THEN 1 END) as defaults,
            COUNT(CASE WHEN status = 'Late' THEN 1 END) as late_payments,
            SUM(CASE WHEN status = 'Default' THEN amount ELSE 0 END) as default_amount
        FROM loans
        WHERE created_at >= ?
    ");
    $stmt->execute([$startDate]);
    $riskMetrics = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Growth metrics
    $stmt = $pdo->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as new_members
        FROM members
        WHERE created_at >= ?
        GROUP BY DATE(created_at)
        ORDER BY date DESC
        LIMIT 7
    ");
    $stmt->execute([$startDate]);
    $memberGrowth = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        "period" => $period,
        "basic_metrics" => $basicMetrics,
        "period_performance" => $periodPerformance,
        "collection_performance" => $collectionPerformance,
        "risk_metrics" => $riskMetrics,
        "member_growth" => $memberGrowth,
        "key_insights" => generateKeyInsights($basicMetrics, $periodPerformance, $riskMetrics)
    ];
}

function getStaffPerformanceAnalytics($pdo, $period, $staffId = null) {
    $startDate = date('Y-m-d', strtotime("-$period days"));
    
    $staffFilter = $staffId ? "AND cs.staff_id = $staffId" : "";
    
    // Staff performance metrics
    $stmt = $pdo->prepare("
        SELECT 
            u.id as staff_id,
            u.full_name as staff_name,
            COUNT(DISTINCT cs.id) as total_schedules,
            COUNT(DISTINCT CASE WHEN cs.status = 'collected' THEN cs.id END) as collections,
            COUNT(DISTINCT CASE WHEN cs.status = 'missed' THEN cs.id END) as missed,
            SUM(cs.expected_amount) as total_expected,
            SUM(cs.actual_amount) as total_collected,
            ROUND(AVG(cs.actual_amount / cs.expected_amount) * 100, 2) as collection_rate,
            COUNT(DISTINCT cs.member_id) as unique_members
        FROM users u
        LEFT JOIN collection_schedules cs ON u.id = cs.staff_id AND cs.collection_date >= ?
        WHERE u.role IN ('Staff', 'Teller') $staffFilter
        GROUP BY u.id, u.full_name
        ORDER BY total_collected DESC
    ");
    $stmt->execute([$startDate]);
    $staffPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Top performers
    $topPerformers = array_slice($staffPerformance, 0, 5);
    
    // Performance trends
    $stmt = $pdo->prepare("
        SELECT 
            DATE(cs.collection_date) as date,
            COUNT(*) as total_schedules,
            SUM(cs.actual_amount) as total_collected,
            COUNT(DISTINCT cs.staff_id) as active_staff
        FROM collection_schedules cs
        WHERE cs.collection_date >= ?
        GROUP BY DATE(cs.collection_date)
        ORDER BY date DESC
        LIMIT 7
    ");
    $stmt->execute([$startDate]);
    $performanceTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        "period" => $period,
        "staff_performance" => $staffPerformance,
        "top_performers" => $topPerformers,
        "performance_trends" => $performanceTrends,
        "performance_summary" => [
            "total_staff" => count($staffPerformance),
            "avg_collection_rate" => count($staffPerformance) > 0 ? 
                round(array_sum(array_column($staffPerformance, 'collection_rate')) / count($staffPerformance), 2) : 0,
            "total_collected" => array_sum(array_column($staffPerformance, 'total_collected'))
        ]
    ];
}

function getProfitabilityAnalysis($pdo, $period) {
    $startDate = date('Y-m-d', strtotime("-$period days"));
    
    // Revenue analysis
    $stmt = $pdo->prepare("
        SELECT 
            SUM(l.amount) as total_disbursed,
            SUM(l.total_interest) as total_interest_earned,
            SUM(l.total_payment) as total_received,
            SUM(l.admin_fee) as total_fees,
            COUNT(*) as total_loans
        FROM loans l
        WHERE l.created_at >= ?
    ");
    $stmt->execute([$startDate]);
    $revenueAnalysis = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Collection revenue
    $stmt = $pdo->prepare("
        SELECT 
            SUM(p.amount) as total_collected,
            COUNT(*) as total_transactions,
            AVG(p.amount) as avg_transaction,
            COUNT(DISTINCT p.member_id) as unique_payers
        FROM payments p
        WHERE p.created_at >= ?
    ");
    $stmt->execute([$startDate]);
    $collectionRevenue = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Profitability by loan type
    $stmt = $pdo->prepare("
        SELECT 
            lt.name as loan_type,
            COUNT(l.id) as loan_count,
            SUM(l.amount) as total_amount,
            SUM(l.total_interest) as total_interest,
            AVG(l.interest_rate) as avg_rate,
            ROUND((SUM(l.total_interest) / SUM(l.amount)) * 100, 2) as effective_rate
        FROM loans l
        LEFT JOIN loan_types lt ON l.loan_type_id = lt.id
        WHERE l.created_at >= ?
        GROUP BY lt.id, lt.name
        ORDER BY total_amount DESC
    ");
    $stmt->execute([$startDate]);
    $profitabilityByType = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Monthly profit trend
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            SUM(total_interest) as monthly_interest,
            SUM(admin_fee) as monthly_fees,
            SUM(amount) as monthly_disbursed,
            COUNT(*) as monthly_loans
        FROM loans
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
        LIMIT 6
    ");
    $stmt->execute();
    $monthlyTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate profitability metrics
    $totalRevenue = ($revenueAnalysis['total_interest_earned'] ?: 0) + ($revenueAnalysis['total_fees'] ?: 0);
    $totalDisbursed = $revenueAnalysis['total_disbursed'] ?: 0;
    $profitMargin = $totalDisbursed > 0 ? round(($totalRevenue / $totalDisbursed) * 100, 2) : 0;
    
    return [
        "period" => $period,
        "revenue_analysis" => $revenueAnalysis,
        "collection_revenue" => $collectionRevenue,
        "profitability_by_type" => $profitabilityByType,
        "monthly_trend" => $monthlyTrend,
        "profitability_metrics" => [
            "total_revenue" => $totalRevenue,
            "total_disbursed" => $totalDisbursed,
            "profit_margin" => $profitMargin,
            "avg_loan_size" => $revenueAnalysis['total_loans'] > 0 ? 
                round($totalDisbursed / $revenueAnalysis['total_loans'], 2) : 0,
            "revenue_per_loan" => $revenueAnalysis['total_loans'] > 0 ? 
                round($totalRevenue / $revenueAnalysis['total_loans'], 2) : 0
        ]
    ];
}

function getCustomerLifetimeValue($pdo, $memberId = null) {
    if ($memberId) {
        // Single member CLV
        return getSingleMemberCLV($pdo, $memberId);
    } else {
        // Aggregate CLV analysis
        return getAggregateCLVAnalysis($pdo);
    }
}

function getSingleMemberCLV($pdo, $memberId) {
    $stmt = $pdo->prepare("
        SELECT 
            m.full_name,
            m.created_at as member_since,
            COUNT(l.id) as total_loans,
            SUM(l.amount) as total_borrowed,
            SUM(l.total_interest) as total_interest_paid,
            SUM(l.total_payment) as total_repaid,
            AVG(l.amount) as avg_loan_size,
            DATEDIFF(NOW(), m.created_at) as days_as_member
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
    
    // Calculate CLV metrics
    $totalRevenue = $member['total_interest_paid'] ?: 0;
    $daysAsMember = $member['days_as_member'];
    $monthsAsMember = $daysAsMember / 30.44;
    
    $monthlyRevenue = $monthsAsMember > 0 ? $totalRevenue / $monthsAsMember : 0;
    $projectedAnnualRevenue = $monthlyRevenue * 12;
    
    // Predict future value based on current behavior
    $avgLoanSize = $member['avg_loan_size'] ?: 0;
    $avgInterestRate = 0.03; // 3% monthly
    $avgMonthlyInterest = $avgLoanSize * $avgInterestRate;
    $projectedMonthlyCLV = $avgMonthlyInterest * 6; // Assuming 6 loans per year
    
    return [
        "member_id" => $memberId,
        "member_name" => $member['full_name'],
        "member_since" => $member['member_since'],
        "days_as_member" => $daysAsMember,
        "historical_clv" => [
            "total_revenue" => $totalRevenue,
            "total_loans" => $member['total_loans'],
            "avg_loan_size" => $avgLoanSize,
            "monthly_revenue" => round($monthlyRevenue, 2)
        ],
        "projected_clv" => [
            "projected_annual_revenue" => round($projectedAnnualRevenue, 2),
            "projected_monthly_clv" => round($projectedMonthlyCLV, 2),
            "projected_3_year_clv" => round($projectedAnnualRevenue * 3, 2)
        ],
        "clv_score" => calculateCLVScore($totalRevenue, $monthsAsMember, $member['total_loans'])
    ];
}

function getAggregateCLVAnalysis($pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_members,
            COUNT(CASE WHEN m.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_members,
            COUNT(CASE WHEN m.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY) THEN 1 END) as recent_members,
            AVG(DATEDIFF(NOW(), m.created_at)) as avg_member_age_days
        FROM members m
        WHERE m.status = 'Active'
    ");
    $stmt->execute();
    $memberStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // CLV by member segments
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN DATEDIFF(NOW(), m.created_at) <= 90 THEN 'New'
                WHEN DATEDIFF(NOW(), m.created_at) <= 365 THEN 'Regular'
                ELSE 'Loyal'
            END as segment,
            COUNT(*) as member_count,
            AVG(COALESCE(total_revenue, 0)) as avg_clv,
            SUM(COALESCE(total_revenue, 0)) as total_segment_clv
        FROM members m
        LEFT JOIN (
            SELECT 
                l.member_id,
                SUM(l.total_interest) as total_revenue
            FROM loans l
            GROUP BY l.member_id
        ) revenue ON m.id = revenue.member_id
        WHERE m.status = 'Active'
        GROUP BY 
            CASE 
                WHEN DATEDIFF(NOW(), m.created_at) <= 90 THEN 'New'
                WHEN DATEDIFF(NOW(), m.created_at) <= 365 THEN 'Regular'
                ELSE 'Loyal'
            END
    ");
    $stmt->execute();
    $clvBySegment = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        "member_statistics" => $memberStats,
        "clv_by_segment" => $clvBySegment,
        "insights" => generateCLVInsights($memberStats, $clvBySegment)
    ];
}

function getCollectionEfficiency($pdo, $period) {
    $startDate = date('Y-m-d', strtotime("-$period days"));
    
    // Collection efficiency metrics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_schedules,
            COUNT(CASE WHEN status = 'collected' THEN 1 END) as collected,
            COUNT(CASE WHEN status = 'missed' THEN 1 END) as missed,
            COUNT(CASE WHEN status = 'postponed' THEN 1 END) as postponed,
            SUM(expected_amount) as total_expected,
            SUM(actual_amount) as total_collected,
            ROUND(SUM(actual_amount) / SUM(expected_amount) * 100, 2) as collection_rate,
            COUNT(DISTINCT staff_id) as active_collectors,
            COUNT(DISTINCT member_id) as unique_members
        FROM collection_schedules
        WHERE collection_date >= ?
    ");
    $stmt->execute([$startDate]);
    $efficiencyMetrics = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Daily efficiency trend
    $stmt = $pdo->prepare("
        SELECT 
            collection_date,
            COUNT(*) as schedules,
            COUNT(CASE WHEN status = 'collected' THEN 1 END) as collected,
            SUM(expected_amount) as expected,
            SUM(actual_amount) as collected,
            ROUND(SUM(actual_amount) / SUM(expected_amount) * 100, 2) as daily_rate
        FROM collection_schedules
        WHERE collection_date >= ?
        GROUP BY collection_date
        ORDER BY collection_date DESC
        LIMIT 7
    ");
    $stmt->execute([$startDate]);
    $dailyTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Staff efficiency ranking
    $stmt = $pdo->prepare("
        SELECT 
            u.full_name,
            COUNT(*) as total_schedules,
            COUNT(CASE WHEN cs.status = 'collected' THEN 1 END) as collections,
            SUM(cs.expected_amount) as expected,
            SUM(cs.actual_amount) as collected,
            ROUND(SUM(cs.actual_amount) / SUM(cs.expected_amount) * 100, 2) as efficiency_rate
        FROM users u
        JOIN collection_schedules cs ON u.id = cs.staff_id
        WHERE cs.collection_date >= ?
        GROUP BY u.id, u.full_name
        HAVING total_schedules >= 5
        ORDER BY efficiency_rate DESC
        LIMIT 10
    ");
    $stmt->execute([$startDate]);
    $staffRanking = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        "period" => $period,
        "efficiency_metrics" => $efficiencyMetrics,
        "daily_trend" => $dailyTrend,
        "staff_ranking" => $staffRanking,
        "efficiency_score" => calculateEfficiencyScore($efficiencyMetrics)
    ];
}

function getPredictiveAnalytics($pdo, $period) {
    // Predict next month's performance
    $nextMonthPredictions = predictNextMonthPerformance($pdo);
    
    // Member churn prediction
    $churnPrediction = predictMemberChurn($pdo);
    
    // Loan demand prediction
    $demandPrediction = predictLoanDemand($pdo);
    
    // Collection prediction
    $collectionPrediction = predictCollectionPerformance($pdo);
    
    return [
        "period" => $period,
        "next_month_predictions" => $nextMonthPredictions,
        "churn_prediction" => $churnPrediction,
        "demand_prediction" => $demandPrediction,
        "collection_prediction" => $collectionPrediction,
        "confidence_level" => 0.75 // 75% confidence for demo
    ];
}

function getMarketAnalysis($pdo, $period) {
    // Geographic distribution
    $stmt = $pdo->prepare("
        SELECT 
            city,
            COUNT(*) as member_count,
            SUM(COALESCE(l.amount, 0)) as total_loans,
            AVG(COALESCE(l.amount, 0)) as avg_loan_size
        FROM members m
        LEFT JOIN loans l ON m.id = l.member_id
        WHERE m.status = 'Active'
        GROUP BY city
        ORDER BY member_count DESC
        LIMIT 10
    ");
    $stmt->execute();
    $geographicDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Occupation analysis
    $stmt = $pdo->prepare("
        SELECT 
            occupation,
            COUNT(*) as member_count,
            AVG(monthly_income) as avg_income,
            AVG(COALESCE(l.amount, 0)) as avg_loan_size,
            COUNT(CASE WHEN l.status = 'Default' THEN 1 END) as defaults
        FROM members m
        LEFT JOIN loans l ON m.id = l.member_id
        WHERE m.status = 'Active' AND occupation IS NOT NULL
        GROUP BY occupation
        HAVING member_count >= 3
        ORDER BY member_count DESC
        LIMIT 10
    ");
    $stmt->execute();
    $occupationAnalysis = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Income segment analysis
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN monthly_income < 3000000 THEN 'Low Income'
                WHEN monthly_income < 7000000 THEN 'Middle Income'
                ELSE 'High Income'
            END as income_segment,
            COUNT(*) as member_count,
            AVG(monthly_income) as avg_segment_income,
            SUM(COALESCE(l.amount, 0)) as total_loans,
            COUNT(CASE WHEN l.status = 'Default' THEN 1 END) as defaults
        FROM members m
        LEFT JOIN loans l ON m.id = l.member_id
        WHERE m.status = 'Active' AND monthly_income > 0
        GROUP BY 
            CASE 
                WHEN monthly_income < 3000000 THEN 'Low Income'
                WHEN monthly_income < 7000000 THEN 'Middle Income'
                ELSE 'High Income'
            END
    ");
    $stmt->execute();
    $incomeSegments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        "period" => $period,
        "geographic_distribution" => $geographicDistribution,
        "occupation_analysis" => $occupationAnalysis,
        "income_segments" => $incomeSegments,
        "market_insights" => generateMarketInsights($geographicDistribution, $occupationAnalysis, $incomeSegments)
    ];
}

// Helper functions for calculations
function generateKeyInsights($basic, $performance, $risk) {
    $insights = [];
    
    if ($performance['new_loans'] > 0) {
        $approvalRate = $performance['approved_loans'] / $performance['new_loans'] * 100;
        if ($approvalRate > 80) {
            $insights[] = "High approval rate (" . round($approvalRate, 1) . "%) indicates good demand";
        }
    }
    
    if ($risk['total_loans_period'] > 0) {
        $defaultRate = ($risk['defaults'] / $risk['total_loans_period']) * 100;
        if ($defaultRate > 5) {
            $insights[] = "High default rate (" . round($defaultRate, 1) . "%) requires attention";
        }
    }
    
    if ($basic['active_loans'] > 0) {
        $avgOutstanding = $basic['total_outstanding'] / $basic['active_loans'];
        if ($avgOutstanding > 5000000) {
            $insights[] = "High average outstanding balance per loan";
        }
    }
    
    return $insights;
}

function calculateCLVScore($totalRevenue, $monthsAsMember, $totalLoans) {
    // Simple CLV scoring (0-100)
    $revenueScore = min(100, ($totalRevenue / 1000000) * 100); // Revenue in millions
    $loyaltyScore = min(100, ($monthsAsMember / 12) * 100); // Years as member
    $frequencyScore = min(100, ($totalLoans / 5) * 100); // Loan frequency
    
    return round(($revenueScore + $loyaltyScore + $frequencyScore) / 3, 2);
}

function generateCLVInsights($stats, $segments) {
    $insights = [];
    
    $newMemberRatio = $stats['recent_members'] / $stats['total_members'] * 100;
    if ($newMemberRatio > 20) {
        $insights[] = "Strong new member acquisition (" . round($newMemberRatio, 1) . "% in last 90 days)";
    }
    
    foreach ($segments as $segment) {
        if ($segment['segment'] === 'Loyal' && $segment['avg_clv'] > 1000000) {
            $insights[] = "Loyal members show high CLV (Rp " . number_format($segment['avg_clv']) . ")";
        }
    }
    
    return $insights;
}

function calculateEfficiencyScore($metrics) {
    if ($metrics['total_schedules'] == 0) return 0;
    
    $collectionRate = $metrics['collection_rate'];
    $coverageRate = ($metrics['unique_members'] / $metrics['total_schedules']) * 100;
    
    return round(($collectionRate + $coverageRate) / 2, 2);
}

function predictNextMonthPerformance($pdo) {
    // Simple prediction based on recent trends
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as current_month_loans,
            SUM(amount) as current_month_amount
        FROM loans
        WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
    ");
    $stmt->execute();
    $currentMonth = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $growthRate = 0.1; // Assume 10% growth
    $predictedLoans = round($currentMonth['current_month_loans'] * (1 + $growthRate));
    $predictedAmount = round($currentMonth['current_month_amount'] * (1 + $growthRate));
    
    return [
        "predicted_loans" => $predictedLoans,
        "predicted_amount" => $predictedAmount,
        "growth_rate" => $growthRate * 100
    ];
}

function predictMemberChurn($pdo) {
    // Since we don't have last_login, use loan activity as proxy
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT m.id) as total_active,
            COUNT(DISTINCT CASE WHEN last_loan_date < DATE_SUB(NOW(), INTERVAL 30 DAY) THEN m.id END) as inactive
        FROM members m
        LEFT JOIN (
            SELECT member_id, MAX(created_at) as last_loan_date
            FROM loans
            GROUP BY member_id
        ) recent_loans ON m.id = recent_loans.member_id
        WHERE m.status = 'Active'
    ");
    $stmt->execute();
    $loanActivity = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $totalActive = $loanActivity['total_active'] ?: 0;
    $inactiveMembers = $loanActivity['inactive'] ?: 0;
    $churnRate = $totalActive > 0 ? ($inactiveMembers / $totalActive) * 100 : 0;
    
    return [
        "current_active" => $totalActive,
        "inactive_members" => $inactiveMembers,
        "churn_rate" => round($churnRate, 2),
        "predicted_churn_next_month" => round($churnRate * 1.1, 2) // Assume 10% increase
    ];
}

function predictLoanDemand($pdo) {
    // Predict based on seasonal patterns and recent trends
    $stmt = $pdo->prepare("
        SELECT 
            AVG(amount) as avg_loan_size,
            COUNT(*) as recent_applications
        FROM loans
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute();
    $recent = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        "predicted_avg_loan_size" => $recent['avg_loan_size'] * 1.05, // 5% increase
        "predicted_applications" => round($recent['recent_applications'] * 1.15), // 15% increase
        "confidence" => "Medium"
    ];
}

function predictCollectionPerformance($pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            AVG(actual_amount / expected_amount) as avg_collection_rate
        FROM collection_schedules
        WHERE collection_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        AND expected_amount > 0
    ");
    $stmt->execute();
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $predictedRate = ($current['avg_collection_rate'] ?: 0) * 0.95; // Assume 5% decrease
    
    return [
        "current_rate" => round(($current['avg_collection_rate'] ?: 0) * 100, 2),
        "predicted_rate" => round($predictedRate * 100, 2),
        "trend" => "Slightly decreasing"
    ];
}

function generateMarketInsights($geo, $occupation, $income) {
    $insights = [];
    
    // Top city insight
    if (!empty($geo) && $geo[0]['member_count'] > 10) {
        $insights[] = $geo[0]['city'] . " is the top market with " . $geo[0]['member_count'] . " members";
    }
    
    // Occupation insight
    foreach ($occupation as $occ) {
        if ($occ['defaults'] == 0 && $occ['member_count'] >= 5) {
            $insights[] = $occ['occupation'] . " segment shows zero defaults";
        }
    }
    
    // Income segment insight
    foreach ($income as $seg) {
        if ($seg['income_segment'] === 'Middle Income' && $seg['member_count'] > 5) {
            $insights[] = "Middle income segment is the largest with " . $seg['member_count'] . " members";
        }
    }
    
    return $insights;
}
?>
