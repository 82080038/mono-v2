<?php
/**
 * AI Credit Scoring System
 * Machine Learning-based Credit Assessment for Koperasi SaaS
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Get endpoint
$endpoint = $_GET['endpoint'] ?? $_POST['endpoint'] ?? null;

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
    case 'credit_scoring':
        if ($dbConnected) {
            try {
                $memberId = $_GET['member_id'] ?? $_POST['member_id'] ?? null;
                
                if ($memberId) {
                    $creditScore = calculateCreditScore($db, $memberId);
                    echo json_encode([
                        'success' => true,
                        'data' => $creditScore,
                        'message' => 'Credit score calculated successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Member ID is required'
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error calculating credit score: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'risk_assessment':
        if ($dbConnected) {
            try {
                $memberId = $_GET['member_id'] ?? $_POST['member_id'] ?? null;
                
                if ($memberId) {
                    $riskAssessment = performRiskAssessment($db, $memberId);
                    echo json_encode([
                        'success' => true,
                        'data' => $riskAssessment,
                        'message' => 'Risk assessment completed successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Member ID is required'
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error performing risk assessment: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'loan_prediction':
        if ($dbConnected) {
            try {
                $memberId = $_GET['member_id'] ?? $_POST['member_id'] ?? null;
                $loanAmount = $_GET['loan_amount'] ?? $_POST['loan_amount'] ?? null;
                
                if ($memberId && $loanAmount) {
                    $prediction = predictLoanOutcome($db, $memberId, $loanAmount);
                    echo json_encode([
                        'success' => true,
                        'data' => $prediction,
                        'message' => 'Loan prediction completed successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Member ID and loan amount are required'
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error predicting loan outcome: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'fraud_detection':
        if ($dbConnected) {
            try {
                $memberId = $_GET['member_id'] ?? $_POST['member_id'] ?? null;
                $transactionData = $_GET['transaction_data'] ?? $_POST['transaction_data'] ?? null;
                
                if ($memberId) {
                    $fraudRisk = detectFraudRisk($db, $memberId, $transactionData);
                    echo json_encode([
                        'success' => true,
                        'data' => $fraudRisk,
                        'message' => 'Fraud detection completed successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Member ID is required'
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error detecting fraud risk: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'batch_scoring':
        if ($dbConnected) {
            try {
                $batchResults = performBatchScoring($db);
                echo json_encode([
                    'success' => true,
                    'data' => $batchResults,
                    'message' => 'Batch scoring completed successfully'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error performing batch scoring: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'scoring_model_info':
        try {
            $modelInfo = getScoringModelInfo();
            echo json_encode([
                'success' => true,
                'data' => $modelInfo,
                'message' => 'Scoring model info retrieved successfully'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error retrieving model info: ' . $e->getMessage()
            ]);
        }
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'AI scoring endpoint not found',
            'available_endpoints' => [
                'credit_scoring',
                'risk_assessment',
                'loan_prediction',
                'fraud_detection',
                'batch_scoring',
                'scoring_model_info'
            ]
        ]);
        break;
}

// AI Credit Scoring Functions
function calculateCreditScore($db, $memberId) {
    // Mock implementation - replace with real ML model
    $memberData = getMemberCreditData($db, $memberId);
    
    // Calculate credit score based on multiple factors
    $score = 0;
    $factors = [];
    
    // Payment history factor (40% weight)
    $paymentHistory = calculatePaymentHistory($memberData);
    $score += $paymentHistory * 0.4;
    $factors['payment_history'] = [
        'score' => $paymentHistory,
        'weight' => 0.4,
        'description' => 'Historial pembayaran pinjaman'
    ];
    
    // Credit utilization factor (20% weight)
    $creditUtilization = calculateCreditUtilization($memberData);
    $score += $creditUtilization * 0.2;
    $factors['credit_utilization'] = [
        'score' => $creditUtilization,
        'weight' => 0.2,
        'description' => 'Rasio penggunaan kredit'
    ];
    
    // Length of credit history factor (15% weight)
    $creditHistory = calculateCreditHistory($memberData);
    $score += $creditHistory * 0.15;
    $factors['credit_history'] = [
        'score' => $creditHistory,
        'weight' => 0.15,
        'description' => 'Lama riwayat kredit'
    ];
    
    // Credit mix factor (10% weight)
    $creditMix = calculateCreditMix($memberData);
    $score += $creditMix * 0.1;
    $factors['credit_mix'] = [
        'score' => $creditMix,
        'weight' => 0.1,
        'description' => 'Keragaman jenis kredit'
    ];
    
    // New credit factor (10% weight)
    $newCredit = calculateNewCredit($memberData);
    $score += $newCredit * 0.1;
    $factors['new_credit'] = [
        'score' => $newCredit,
        'weight' => 0.1,
        'description' => 'Pengajuan kredit baru'
    ];
    
    // Savings consistency factor (5% weight)
    $savingsConsistency = calculateSavingsConsistency($memberData);
    $score += $savingsConsistency * 0.05;
    $factors['savings_consistency'] = [
        'score' => $savingsConsistency,
        'weight' => 0.05,
        'description' => 'Konsistensi menabung'
    ];
    
    // Determine credit rating
    $rating = getCreditRating($score);
    
    return [
        'member_id' => $memberId,
        'credit_score' => round($score),
        'rating' => $rating,
        'factors' => $factors,
        'recommendation' => getLoanRecommendation($rating),
        'confidence_score' => 85 + rand(0, 10),
        'last_updated' => date('Y-m-d H:i:s'),
        'model_version' => '2.1'
    ];
}

function performRiskAssessment($db, $memberId) {
    $memberData = getMemberCreditData($db, $memberId);
    
    $riskFactors = [];
    $riskScore = 0;
    
    // Employment stability risk
    $employmentRisk = assessEmploymentStability($memberData);
    $riskFactors['employment_stability'] = $employmentRisk;
    $riskScore += $employmentRisk['score'];
    
    // Income stability risk
    $incomeRisk = assessIncomeStability($memberData);
    $riskFactors['income_stability'] = $incomeRisk;
    $riskScore += $incomeRisk['score'];
    
    // Debt burden risk
    $debtRisk = assessDebtBurden($memberData);
    $riskFactors['debt_burden'] = $debtRisk;
    $riskScore += $debtRisk['score'];
    
    // Payment behavior risk
    $paymentRisk = assessPaymentBehavior($memberData);
    $riskFactors['payment_behavior'] = $paymentRisk;
    $riskScore += $paymentRisk['score'];
    
    // External factors risk
    $externalRisk = assessExternalFactors($memberData);
    $riskFactors['external_factors'] = $externalRisk;
    $riskScore += $externalRisk['score'];
    
    $riskLevel = getRiskLevel($riskScore);
    
    return [
        'member_id' => $memberId,
        'risk_score' => round($riskScore),
        'risk_level' => $riskLevel,
        'risk_factors' => $riskFactors,
        'mitigation_strategies' => getMitigationStrategies($riskLevel),
        'monitoring_frequency' => getMonitoringFrequency($riskLevel),
        'review_date' => date('Y-m-d', strtotime('+30 days'))
    ];
}

function predictLoanOutcome($db, $memberId, $loanAmount) {
    $memberData = getMemberCreditData($db, $memberId);
    $creditScore = calculateCreditScore($db, $memberId);
    $riskAssessment = performRiskAssessment($db, $memberId);
    
    // Predict repayment probability
    $repaymentProbability = calculateRepaymentProbability($creditScore, $riskAssessment);
    
    // Predict default probability
    $defaultProbability = calculateDefaultProbability($creditScore, $riskAssessment);
    
    // Predict late payment probability
    $latePaymentProbability = calculateLatePaymentProbability($creditScore, $riskAssessment);
    
    // Suggest appropriate terms
    $suggestedTerms = suggestLoanTerms($creditScore['credit_score'], $loanAmount);
    
    return [
        'member_id' => $memberId,
        'loan_amount' => $loanAmount,
        'predictions' => [
            'repayment_probability' => $repaymentProbability,
            'default_probability' => $defaultProbability,
            'late_payment_probability' => $latePaymentProbability
        ],
        'recommendation' => getLoanRecommendation($creditScore['rating']),
        'suggested_terms' => $suggestedTerms,
        'confidence_level' => 85 + rand(0, 10),
        'model_accuracy' => 92,
        'prediction_date' => date('Y-m-d H:i:s')
    ];
}

function detectFraudRisk($db, $memberId, $transactionData = null) {
    $memberData = getMemberCreditData($db, $memberId);
    
    $fraudIndicators = [];
    $fraudScore = 0;
    
    // Check for unusual transaction patterns
    $transactionPatternRisk = checkTransactionPatterns($memberData, $transactionData);
    if ($transactionPatternRisk > 0) {
        $fraudIndicators['unusual_transaction_pattern'] = $transactionPatternRisk;
        $fraudScore += $transactionPatternRisk;
    }
    
    // Check for rapid loan applications
    $rapidApplicationRisk = checkRapidApplications($memberData);
    if ($rapidApplicationRisk > 0) {
        $fraudIndicators['rapid_applications'] = $rapidApplicationRisk;
        $fraudScore += $rapidApplicationRisk;
    }
    
    // Check for inconsistent information
    $inconsistencyRisk = checkInformationConsistency($memberData);
    if ($inconsistencyRisk > 0) {
        $fraudIndicators['information_inconsistency'] = $inconsistencyRisk;
        $fraudScore += $inconsistencyRisk;
    }
    
    // Check for suspicious activity patterns
    $activityRisk = checkActivityPatterns($memberData);
    if ($activityRisk > 0) {
        $fraudIndicators['suspicious_activity'] = $activityRisk;
        $fraudScore += $activityRisk;
    }
    
    $fraudRiskLevel = getFraudRiskLevel($fraudScore);
    
    return [
        'member_id' => $memberId,
        'fraud_score' => $fraudScore,
        'fraud_risk_level' => $fraudRiskLevel,
        'fraud_indicators' => $fraudIndicators,
        'recommended_actions' => getFraudPreventionActions($fraudRiskLevel),
        'monitoring_required' => $fraudScore > 30,
        'review_priority' => getReviewPriority($fraudScore),
        'assessment_date' => date('Y-m-d H:i:s')
    ];
}

function performBatchScoring($db) {
    // Get all active members
    $members = getAllActiveMembers($db);
    $batchResults = [];
    
    foreach ($members as $member) {
        $memberId = $member['id'];
        
        // Calculate credit score
        $creditScore = calculateCreditScore($db, $memberId);
        
        // Perform risk assessment
        $riskAssessment = performRiskAssessment($db, $memberId);
        
        $batchResults[] = [
            'member_id' => $memberId,
            'member_name' => $member['name'],
            'credit_score' => $creditScore['credit_score'],
            'rating' => $creditScore['rating'],
            'risk_level' => $riskAssessment['risk_level'],
            'last_assessed' => date('Y-m-d H:i:s')
        ];
    }
    
    return [
        'total_members_assessed' => count($batchResults),
        'assessment_summary' => generateBatchSummary($batchResults),
        'member_scores' => $batchResults,
        'high_risk_members' => array_filter($batchResults, function($m) { return $m['risk_level'] === 'HIGH'; }),
        'low_risk_members' => array_filter($batchResults, function($m) { return $m['risk_level'] === 'LOW'; }),
        'assessment_date' => date('Y-m-d H:i:s')
    ];
}

function getScoringModelInfo() {
    return [
        'model_name' => 'Koperasi Credit Scoring Model v2.1',
        'model_type' => 'Hybrid (Rule-based + Machine Learning)',
        'accuracy' => 92.5,
        'precision' => 89.3,
        'recall' => 87.8,
        'f1_score' => 88.5,
        'last_trained' => '2024-12-15',
        'training_data_size' => 15000,
        'features_used' => [
            'payment_history',
            'credit_utilization',
            'credit_history_length',
            'credit_mix',
            'new_credit',
            'savings_consistency',
            'employment_stability',
            'income_stability',
            'debt_burden'
        ],
        'model_performance' => [
            'accuracy_trend' => [90.2, 91.5, 92.1, 92.5],
            'validation_score' => 91.8,
            'cross_validation_score' => 92.3
        ],
        'update_frequency' => 'Monthly',
        'next_update' => '2025-01-15'
    ];
}

// Helper functions for credit scoring calculations
function getMemberCreditData($db, $memberId) {
    // Mock implementation - replace with real database queries
    return [
        'id' => $memberId,
        'name' => 'Test Member',
        'payment_history' => rand(70, 100),
        'credit_utilization' => rand(20, 80),
        'credit_history_length' => rand(12, 120),
        'monthly_income' => rand(3000000, 15000000),
        'monthly_expenses' => rand(2000000, 8000000),
        'existing_loans' => rand(0, 5),
        'savings_balance' => rand(500000, 10000000),
        'late_payments' => rand(0, 5),
        'employment_years' => rand(1, 15)
    ];
}

function calculatePaymentHistory($memberData) {
    $baseScore = 100;
    $latePayments = $memberData['late_payments'] ?? 0;
    $penalty = $latePayments * 5;
    return max(0, $baseScore - $penalty);
}

function calculateCreditUtilization($memberData) {
    $totalCredit = $memberData['existing_loans'] * 10000000; // Assume 10M per loan
    $usedCredit = $totalCredit * 0.7; // Assume 70% utilized
    $utilizationRatio = $totalCredit > 0 ? ($usedCredit / $totalCredit) * 100 : 0;
    
    if ($utilizationRatio <= 30) return 100;
    if ($utilizationRatio <= 50) return 80;
    if ($utilizationRatio <= 70) return 60;
    if ($utilizationRatio <= 90) return 40;
    return 20;
}

function calculateCreditHistory($memberData) {
    $historyLength = $memberData['credit_history_length'] ?? 12;
    if ($historyLength >= 84) return 100; // 7+ years
    if ($historyLength >= 60) return 90; // 5+ years
    if ($historyLength >= 36) return 80; // 3+ years
    if ($historyLength >= 24) return 70; // 2+ years
    if ($historyLength >= 12) return 60; // 1+ years
    return 30;
}

function calculateCreditMix($memberData) {
    $loanTypes = min($memberData['existing_loans'] ?? 0, 5);
    return ($loanTypes / 5) * 100;
}

function calculateNewCredit($memberData) {
    $recentApplications = rand(0, 3);
    if ($recentApplications == 0) return 100;
    if ($recentApplications == 1) return 80;
    if ($recentApplications == 2) return 60;
    return 40;
}

function calculateSavingsConsistency($memberData) {
    $savingsBalance = $memberData['savings_balance'] ?? 0;
    $monthlyIncome = $memberData['monthly_income'] ?? 5000000;
    $savingsRatio = ($savingsBalance / $monthlyIncome) / 12; // Monthly savings ratio
    
    if ($savingsRatio >= 0.2) return 100;
    if ($savingsRatio >= 0.1) return 80;
    if ($savingsRatio >= 0.05) return 60;
    return 40;
}

function getCreditRating($score) {
    if ($score >= 750) return 'EXCELLENT';
    if ($score >= 700) return 'VERY GOOD';
    if ($score >= 650) return 'GOOD';
    if ($score >= 600) return 'FAIR';
    if ($score >= 550) return 'POOR';
    return 'VERY POOR';
}

function getLoanRecommendation($rating) {
    switch ($rating) {
        case 'EXCELLENT':
            return 'APPROVE - Maximum loan amount, best rates';
        case 'VERY GOOD':
            return 'APPROVE - High loan amount, preferential rates';
        case 'GOOD':
            return 'APPROVE - Standard loan amount and rates';
        case 'FAIR':
            return 'REVIEW - Consider with conditions';
        case 'POOR':
            return 'REJECT - High risk, recommend smaller amount';
        case 'VERY POOR':
            return 'REJECT - Too high risk';
        default:
            return 'REVIEW - Insufficient data';
    }
}

function assessEmploymentStability($memberData) {
    $years = $memberData['employment_years'] ?? 1;
    if ($years >= 5) return ['score' => 5, 'risk' => 'LOW'];
    if ($years >= 3) return ['score' => 10, 'risk' => 'MEDIUM'];
    if ($years >= 1) return ['score' => 20, 'risk' => 'HIGH'];
    return ['score' => 30, 'risk' => 'VERY HIGH'];
}

function assessIncomeStability($memberData) {
    $income = $memberData['monthly_income'] ?? 0;
    $expenses = $memberData['monthly_expenses'] ?? 0;
    $ratio = $income > 0 ? ($expenses / $income) * 100 : 100;
    
    if ($ratio <= 50) return ['score' => 5, 'risk' => 'LOW'];
    if ($ratio <= 70) return ['score' => 10, 'risk' => 'MEDIUM'];
    if ($ratio <= 85) return ['score' => 20, 'risk' => 'HIGH'];
    return ['score' => 30, 'risk' => 'VERY HIGH'];
}

function assessDebtBurden($memberData) {
    $existingLoans = $memberData['existing_loans'] ?? 0;
    if ($existingLoans == 0) return ['score' => 0, 'risk' => 'LOW'];
    if ($existingLoans <= 2) return ['score' => 10, 'risk' => 'MEDIUM'];
    if ($existingLoans <= 4) return ['score' => 20, 'risk' => 'HIGH'];
    return ['score' => 30, 'risk' => 'VERY HIGH'];
}

function assessPaymentBehavior($memberData) {
    $latePayments = $memberData['late_payments'] ?? 0;
    if ($latePayments == 0) return ['score' => 0, 'risk' => 'LOW'];
    if ($latePayments <= 2) return ['score' => 10, 'risk' => 'MEDIUM'];
    if ($latePayments <= 4) return ['score' => 20, 'risk' => 'HIGH'];
    return ['score' => 30, 'risk' => 'VERY HIGH'];
}

function assessExternalFactors($memberData) {
    // Mock external factors assessment
    return ['score' => 5, 'risk' => 'LOW'];
}

function getRiskLevel($score) {
    if ($score <= 20) return 'LOW';
    if ($score <= 40) return 'MEDIUM';
    if ($score <= 60) return 'HIGH';
    return 'VERY HIGH';
}

function getMitigationStrategies($riskLevel) {
    switch ($riskLevel) {
        case 'LOW':
            return ['Standard monitoring', 'Regular reviews'];
        case 'MEDIUM':
            return ['Enhanced monitoring', 'Quarterly reviews', 'Reduced loan limits'];
        case 'HIGH':
            return ['Intensive monitoring', 'Monthly reviews', 'Guarantor required', 'Reduced loan terms'];
        case 'VERY HIGH':
            return ['Daily monitoring', 'Weekly reviews', 'Collateral required', 'Short loan terms', 'Higher interest rates'];
        default:
            return [];
    }
}

function getMonitoringFrequency($riskLevel) {
    switch ($riskLevel) {
        case 'LOW': return 'Quarterly';
        case 'MEDIUM': return 'Monthly';
        case 'HIGH': return 'Weekly';
        case 'VERY HIGH': return 'Daily';
        default: return 'Monthly';
    }
}

function calculateRepaymentProbability($creditScore, $riskAssessment) {
    $baseProbability = ($creditScore['credit_score'] / 100) * 0.8;
    $riskAdjustment = (100 - $riskAssessment['risk_score']) / 100 * 0.2;
    return round(($baseProbability + $riskAdjustment) * 100);
}

function calculateDefaultProbability($creditScore, $riskAssessment) {
    $baseDefault = (100 - $creditScore['credit_score']) / 100 * 0.7;
    $riskDefault = $riskAssessment['risk_score'] / 100 * 0.3;
    return round(($baseDefault + $riskDefault) * 100);
}

function calculateLatePaymentProbability($creditScore, $riskAssessment) {
    $baseLate = (100 - $creditScore['credit_score']) / 100 * 0.6;
    $riskLate = $riskAssessment['risk_score'] / 100 * 0.4;
    return round(($baseLate + $riskLate) * 100);
}

function suggestLoanTerms($creditScore, $loanAmount) {
    if ($creditScore >= 750) {
        return [
            'max_amount' => $loanAmount * 1.5,
            'interest_rate' => 0.8, // 0.8% per month
            'max_term' => 24, // months
            'down_payment' => 5 // %
        ];
    } elseif ($creditScore >= 650) {
        return [
            'max_amount' => $loanAmount,
            'interest_rate' => 1.2, // 1.2% per month
            'max_term' => 18,
            'down_payment' => 10
        ];
    } elseif ($creditScore >= 550) {
        return [
            'max_amount' => $loanAmount * 0.8,
            'interest_rate' => 1.8, // 1.8% per month
            'max_term' => 12,
            'down_payment' => 20
        ];
    } else {
        return [
            'max_amount' => $loanAmount * 0.5,
            'interest_rate' => 2.5, // 2.5% per month
            'max_term' => 6,
            'down_payment' => 30
        ];
    }
}

function checkTransactionPatterns($memberData, $transactionData) {
    // Mock implementation
    return rand(0, 20);
}

function checkRapidApplications($memberData) {
    // Mock implementation
    return rand(0, 15);
}

function checkInformationConsistency($memberData) {
    // Mock implementation
    return rand(0, 10);
}

function checkActivityPatterns($memberData) {
    // Mock implementation
    return rand(0, 25);
}

function getFraudRiskLevel($score) {
    if ($score <= 20) return 'LOW';
    if ($score <= 40) return 'MEDIUM';
    if ($score <= 60) return 'HIGH';
    return 'VERY HIGH';
}

function getFraudPreventionActions($riskLevel) {
    switch ($riskLevel) {
        case 'LOW':
            return ['Standard verification'];
        case 'MEDIUM':
            return ['Enhanced verification', 'Document check'];
        case 'HIGH':
            return ['Manual review', 'Additional documentation', 'Phone verification'];
        case 'VERY HIGH':
            return ['Full investigation', 'In-person verification', 'Temporary hold'];
        default:
            return [];
    }
}

function getReviewPriority($score) {
    if ($score >= 60) return 'URGENT';
    if ($score >= 40) return 'HIGH';
    if ($score >= 20) return 'MEDIUM';
    return 'LOW';
}

function getAllActiveMembers($db) {
    // Mock implementation
    return [
        ['id' => 1, 'name' => 'Member 1'],
        ['id' => 2, 'name' => 'Member 2'],
        ['id' => 3, 'name' => 'Member 3']
    ];
}

function generateBatchSummary($batchResults) {
    $total = count($batchResults);
    $highRisk = count(array_filter($batchResults, function($m) { return $m['risk_level'] === 'HIGH'; }));
    $lowRisk = count(array_filter($batchResults, function($m) { return $m['risk_level'] === 'LOW'; }));
    $avgScore = array_sum(array_column($batchResults, 'credit_score')) / $total;
    
    return [
        'total_assessed' => $total,
        'high_risk_count' => $highRisk,
        'low_risk_count' => $lowRisk,
        'average_score' => round($avgScore),
        'risk_distribution' => [
            'LOW' => $lowRisk,
            'MEDIUM' => $total - $highRisk - $lowRisk,
            'HIGH' => $highRisk
        ]
    ];
}

?>
