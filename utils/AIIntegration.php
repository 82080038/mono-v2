<?php
/**
 * AI/ML Integration System
 * Implements machine learning for credit scoring and fraud detection
 */

class AIIntegration {
    private $db;
    private $models = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->loadModels();
    }
    
    /**
     * Load ML models
     */
    private function loadModels() {
        // Credit Scoring Model
        $this->models['credit_scoring'] = [
            'features' => [
                'age' => ['weight' => 0.1, 'type' => 'numeric'],
                'income' => ['weight' => 0.25, 'type' => 'numeric'],
                'employment_years' => ['weight' => 0.15, 'type' => 'numeric'],
                'existing_loans' => ['weight' => 0.2, 'type' => 'numeric'],
                'payment_history' => ['weight' => 0.3, 'type' => 'categorical'],
                'savings_balance' => ['weight' => 0.15, 'type' => 'numeric'],
                'credit_history_length' => ['weight' => 0.1, 'type' => 'numeric']
            ],
            'algorithm' => 'weighted_logistic_regression',
            'threshold' => 0.65
        ];
        
        // Fraud Detection Model
        $this->models['fraud_detection'] = [
            'features' => [
                'transaction_amount' => ['weight' => 0.2, 'type' => 'numeric'],
                'transaction_frequency' => ['weight' => 0.15, 'type' => 'numeric'],
                'location_consistency' => ['weight' => 0.25, 'type' => 'numeric'],
                'device_fingerprint' => ['weight' => 0.1, 'type' => 'categorical'],
                'time_pattern' => ['weight' => 0.15, 'type' => 'numeric'],
                'amount_pattern' => ['weight' => 0.15, 'type' => 'numeric']
            ],
            'algorithm' => 'anomaly_detection',
            'threshold' => 0.8
        ];
        
        // Risk Assessment Model
        $this->models['risk_assessment'] = [
            'features' => [
                'debt_to_income_ratio' => ['weight' => 0.3, 'type' => 'numeric'],
                'loan_to_value_ratio' => ['weight' => 0.25, 'type' => 'numeric'],
                'payment_history_score' => ['weight' => 0.2, 'type' => 'numeric'],
                'employment_stability' => ['weight' => 0.15, 'type' => 'categorical'],
                'collateral_value' => ['weight' => 0.1, 'type' => 'numeric']
            ],
            'algorithm' => 'decision_tree',
            'threshold' => 0.7
        ];
    }
    
    /**
     * Calculate credit score
     */
    public function calculateCreditScore($memberId) {
        $memberData = $this->getMemberData($memberId);
        $model = $this->models['credit_scoring'];
        
        $score = 0;
        $totalWeight = 0;
        
        foreach ($model['features'] as $feature => $config) {
            $value = $this->getFeatureValue($feature, $memberData);
            $normalizedValue = $this->normalizeFeature($feature, $value, $config['type']);
            
            $score += $normalizedValue * $config['weight'];
            $totalWeight += $config['weight'];
        }
        
        $finalScore = ($score / $totalWeight) * 1000;
        
        // Save score to database
        $this->saveCreditScore($memberId, $finalScore);
        
        return [
            'score' => round($finalScore),
            'grade' => $this->getCreditGrade($finalScore),
            'recommendation' => $this->getLoanRecommendation($finalScore),
            'factors' => $this->getScoreFactors($memberData)
        ];
    }
    
    /**
     * Detect fraud in transaction
     */
    public function detectFraud($transactionData) {
        $model = $this->models['fraud_detection'];
        $riskScore = 0;
        $totalWeight = 0;
        
        foreach ($model['features'] as $feature => $config) {
            $value = $this->getTransactionFeature($feature, $transactionData);
            $riskValue = $this->calculateRiskValue($feature, $value, $config['type']);
            
            $riskScore += $riskValue * $config['weight'];
            $totalWeight += $config['weight'];
        }
        
        $finalRiskScore = $riskScore / $totalWeight;
        $isFraud = $finalRiskScore > $model['threshold'];
        
        // Log fraud detection
        $this->logFraudDetection($transactionData, $finalRiskScore, $isFraud);
        
        return [
            'risk_score' => round($finalRiskScore, 3),
            'is_fraud' => $isFraud,
            'risk_level' => $this->getRiskLevel($finalRiskScore),
            'recommendations' => $this->getFraudRecommendations($finalRiskScore)
        ];
    }
    
    /**
     * Assess loan risk
     */
    public function assessLoanRisk($loanApplication) {
        $model = $this->models['risk_assessment'];
        $riskScore = 0;
        $totalWeight = 0;
        
        foreach ($model['features'] as $feature => $config) {
            $value = $this->getLoanFeature($feature, $loanApplication);
            $riskValue = $this->calculateRiskValue($feature, $value, $config['type']);
            
            $riskScore += $riskValue * $config['weight'];
            $totalWeight += $config['weight'];
        }
        
        $finalRiskScore = $riskScore / $totalWeight;
        
        return [
            'risk_score' => round($finalRiskScore, 3),
            'risk_level' => $this->getRiskLevel($finalRiskScore),
            'approval_probability' => $this->getApprovalProbability($finalRiskScore),
            'recommended_amount' => $this->getRecommendedAmount($loanApplication, $finalRiskScore),
            'recommended_terms' => $this->getRecommendedTerms($finalRiskScore),
            'risk_factors' => $this->getRiskFactors($loanApplication)
        ];
    }
    
    /**
     * Get member data for ML models
     */
    private function getMemberData($memberId) {
        $sql = "SELECT 
                    m.*,
                    (SELECT COUNT(*) FROM loans WHERE member_id = m.id AND status = 'active') as active_loans,
                    (SELECT COALESCE(SUM(outstanding_balance), 0) FROM loans WHERE member_id = m.id AND status = 'active') as total_debt,
                    (SELECT COALESCE(SUM(balance), 0) FROM savings WHERE member_id = m.id) as total_savings,
                    (SELECT COUNT(*) FROM transactions WHERE member_id = m.id AND transaction_date >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)) as transaction_count,
                    (SELECT AVG(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) FROM loans WHERE member_id = m.id) as payment_history
                FROM members m 
                WHERE m.id = ?";
        
        return $this->db->fetchOne($sql, [$memberId]);
    }
    
    /**
     * Get feature value
     */
    private function getFeatureValue($feature, $memberData) {
        switch ($feature) {
            case 'age':
                return $this->calculateAge($memberData['birth_date']);
            case 'income':
                return $memberData['monthly_income'] ?? 0;
            case 'employment_years':
                return $memberData['employment_years'] ?? 0;
            case 'existing_loans':
                return $memberData['active_loans'] ?? 0;
            case 'payment_history':
                return $memberData['payment_history'] ?? 0.5;
            case 'savings_balance':
                return $memberData['total_savings'] ?? 0;
            case 'credit_history_length':
                return $memberData['credit_history_months'] ?? 0;
            default:
                return 0;
        }
    }
    
    /**
     * Normalize feature value
     */
    private function normalizeFeature($feature, $value, $type) {
        if ($type == 'numeric') {
            switch ($feature) {
                case 'age':
                    return min($value / 60, 1); // Normalize to 0-1 (max age 60)
                case 'income':
                    return min($value / 20000000, 1); // Normalize to 0-1 (max income 20M)
                case 'employment_years':
                    return min($value / 30, 1); // Normalize to 0-1 (max 30 years)
                case 'existing_loans':
                    return max(0, 1 - ($value / 5)); // Inverse normalize (more loans = lower score)
                case 'savings_balance':
                    return min($value / 50000000, 1); // Normalize to 0-1 (max 50M)
                case 'credit_history_length':
                    return min($value / 120, 1); // Normalize to 0-1 (max 10 years)
                default:
                    return 0.5;
            }
        } else {
            // Categorical features
            return max(0, min(1, $value)); // Ensure 0-1 range
        }
    }
    
    /**
     * Calculate age from birth date
     */
    private function calculateAge($birthDate) {
        if (!$birthDate) return 25; // Default age
        
        $birth = new DateTime($birthDate);
        $today = new DateTime();
        return $today->diff($birth)->y;
    }
    
    /**
     * Get credit grade
     */
    private function getCreditGrade($score) {
        if ($score >= 800) return 'AAA';
        if ($score >= 700) return 'AA';
        if ($score >= 600) return 'A';
        if ($score >= 500) return 'B';
        if ($score >= 400) return 'C';
        if ($score >= 300) return 'D';
        return 'E';
    }
    
    /**
     * Get loan recommendation
     */
    private function getLoanRecommendation($score) {
        if ($score >= 700) return 'Approved - High confidence';
        if ($score >= 600) return 'Approved - Medium confidence';
        if ($score >= 500) return 'Consider with conditions';
        if ($score >= 400) return 'Requires additional collateral';
        return 'Not recommended';
    }
    
    /**
     * Get score factors
     */
    private function getScoreFactors($memberData) {
        $factors = [];
        
        if ($memberData['payment_history'] > 0.8) {
            $factors[] = 'Excellent payment history';
        } elseif ($memberData['payment_history'] < 0.5) {
            $factors[] = 'Poor payment history';
        }
        
        if ($memberData['total_savings'] > 10000000) {
            $factors[] = 'Strong savings balance';
        }
        
        if ($memberData['active_loans'] > 3) {
            $factors[] = 'Multiple existing loans';
        }
        
        return $factors;
    }
    
    /**
     * Get transaction feature
     */
    private function getTransactionFeature($feature, $transactionData) {
        switch ($feature) {
            case 'transaction_amount':
                return $transactionData['amount'] ?? 0;
            case 'transaction_frequency':
                return $this->getTransactionFrequency($transactionData['user_id']);
            case 'location_consistency':
                return $this->getLocationConsistency($transactionData);
            case 'device_fingerprint':
                return $this->getDeviceFingerprintScore($transactionData);
            case 'time_pattern':
                return $this->getTimePatternScore($transactionData);
            case 'amount_pattern':
                return $this->getAmountPatternScore($transactionData);
            default:
                return 0;
        }
    }
    
    /**
     * Calculate risk value
     */
    private function calculateRiskValue($feature, $value, $type) {
        if ($type == 'numeric') {
            switch ($feature) {
                case 'transaction_amount':
                    return min($value / 10000000, 1); // Higher amount = higher risk
                case 'transaction_frequency':
                    return min($value / 100, 1); // Higher frequency = higher risk
                case 'location_consistency':
                    return max(0, 1 - $value); // Lower consistency = higher risk
                case 'time_pattern':
                    return max(0, 1 - $value); // Unusual time = higher risk
                case 'amount_pattern':
                    return max(0, 1 - $value); // Unusual amount = higher risk
                default:
                    return 0.5;
            }
        } else {
            return max(0, min(1, $value));
        }
    }
    
    /**
     * Get transaction frequency
     */
    private function getTransactionFrequency($userId) {
        $sql = "SELECT COUNT(*) as count FROM transactions 
                WHERE user_id = ? AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)";
        $result = $this->db->fetchOne($sql, [$userId]);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get location consistency
     */
    private function getLocationConsistency($transactionData) {
        // Simplified location consistency check
        $sql = "SELECT COUNT(DISTINCT location) as unique_locations 
                FROM gps_tracking 
                WHERE user_id = ? AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)";
        $result = $this->db->fetchOne($sql, [$transactionData['user_id']]);
        
        $uniqueLocations = $result['unique_locations'] ?? 1;
        return max(0, 1 - ($uniqueLocations / 10)); // More locations = lower consistency
    }
    
    /**
     * Get device fingerprint score
     */
    private function getDeviceFingerprintScore($transactionData) {
        // Simplified device fingerprint check
        return 0.8; // Default score (would implement actual device fingerprinting)
    }
    
    /**
     * Get time pattern score
     */
    private function getTimePatternScore($transactionData) {
        $hour = date('H', strtotime($transactionData['created_at']));
        
        // Normal business hours (8 AM - 6 PM)
        if ($hour >= 8 && $hour <= 18) {
            return 0.9;
        } elseif ($hour >= 6 && $hour <= 22) {
            return 0.7;
        } else {
            return 0.3; // Unusual hours
        }
    }
    
    /**
     * Get amount pattern score
     */
    private function getAmountPatternScore($transactionData) {
        $amount = $transactionData['amount'];
        $userId = $transactionData['user_id'];
        
        // Get average transaction amount for user
        $sql = "SELECT AVG(amount) as avg_amount FROM transactions 
                WHERE user_id = ? AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)";
        $result = $this->db->fetchOne($sql, [$userId]);
        
        $avgAmount = $result['avg_amount'] ?? $amount;
        
        if ($amount > $avgAmount * 3) {
            return 0.2; // Unusually high amount
        } elseif ($amount > $avgAmount * 2) {
            return 0.5; // Higher than usual
        } else {
            return 0.9; // Normal amount
        }
    }
    
    /**
     * Get risk level
     */
    private function getRiskLevel($riskScore) {
        if ($riskScore >= 0.8) return 'Very High';
        if ($riskScore >= 0.6) return 'High';
        if ($riskScore >= 0.4) return 'Medium';
        if ($riskScore >= 0.2) return 'Low';
        return 'Very Low';
    }
    
    /**
     * Get fraud recommendations
     */
    private function getFraudRecommendations($riskScore) {
        $recommendations = [];
        
        if ($riskScore >= 0.8) {
            $recommendations[] = 'Block transaction immediately';
            $recommendations[] = 'Require additional verification';
            $recommendations[] = 'Report to security team';
        } elseif ($riskScore >= 0.6) {
            $recommendations[] = 'Require manual review';
            $recommendations[] = 'Request additional documentation';
        } elseif ($riskScore >= 0.4) {
            $recommendations[] = 'Monitor for suspicious activity';
        }
        
        return $recommendations;
    }
    
    /**
     * Get loan feature
     */
    private function getLoanFeature($feature, $loanApplication) {
        switch ($feature) {
            case 'debt_to_income_ratio':
                return $this->calculateDebtToIncomeRatio($loanApplication);
            case 'loan_to_value_ratio':
                return $this->calculateLoanToValueRatio($loanApplication);
            case 'payment_history_score':
                return $loanApplication['payment_history_score'] ?? 0.5;
            case 'employment_stability':
                return $this->getEmploymentStability($loanApplication);
            case 'collateral_value':
                return $loanApplication['collateral_value'] ?? 0;
            default:
                return 0;
        }
    }
    
    /**
     * Calculate debt to income ratio
     */
    private function calculateDebtToIncomeRatio($loanApplication) {
        $monthlyIncome = $loanApplication['monthly_income'] ?? 1;
        $monthlyDebt = $loanApplication['existing_monthly_debt'] ?? 0;
        $proposedPayment = $this->calculateMonthlyPayment($loanApplication);
        
        return ($monthlyDebt + $proposedPayment) / $monthlyIncome;
    }
    
    /**
     * Calculate loan to value ratio
     */
    private function calculateLoanToValueRatio($loanApplication) {
        $loanAmount = $loanApplication['loan_amount'] ?? 0;
        $collateralValue = $loanApplication['collateral_value'] ?? $loanAmount;
        
        return $loanAmount / $collateralValue;
    }
    
    /**
     * Calculate monthly payment
     */
    private function calculateMonthlyPayment($loanApplication) {
        $principal = $loanApplication['loan_amount'] ?? 0;
        $rate = ($loanApplication['interest_rate'] ?? 0.02) / 12;
        $term = $loanApplication['loan_term'] ?? 12;
        
        if ($rate == 0) return $principal / $term;
        
        return $principal * ($rate * pow(1 + $rate, $term)) / (pow(1 + $rate, $term) - 1);
    }
    
    /**
     * Get employment stability
     */
    private function getEmploymentStability($loanApplication) {
        $years = $loanApplication['employment_years'] ?? 0;
        
        if ($years >= 5) return 0.9;
        if ($years >= 3) return 0.7;
        if ($years >= 1) return 0.5;
        return 0.3;
    }
    
    /**
     * Get approval probability
     */
    private function getApprovalProbability($riskScore) {
        return max(0, min(1, 1 - $riskScore));
    }
    
    /**
     * Get recommended amount
     */
    private function getRecommendedAmount($loanApplication, $riskScore) {
        $requestedAmount = $loanApplication['loan_amount'] ?? 0;
        
        if ($riskScore < 0.3) return $requestedAmount;
        if ($riskScore < 0.5) return $requestedAmount * 0.8;
        if ($riskScore < 0.7) return $requestedAmount * 0.6;
        return $requestedAmount * 0.4;
    }
    
    /**
     * Get recommended terms
     */
    private function getRecommendedTerms($riskScore) {
        if ($riskScore < 0.3) return ['term' => 24, 'interest_rate' => 0.015];
        if ($riskScore < 0.5) return ['term' => 18, 'interest_rate' => 0.02];
        if ($riskScore < 0.7) return ['term' => 12, 'interest_rate' => 0.025];
        return ['term' => 6, 'interest_rate' => 0.03];
    }
    
    /**
     * Get risk factors
     */
    private function getRiskFactors($loanApplication) {
        $factors = [];
        
        $dti = $this->calculateDebtToIncomeRatio($loanApplication);
        if ($dti > 0.5) {
            $factors[] = 'High debt-to-income ratio';
        }
        
        $ltv = $this->calculateLoanToValueRatio($loanApplication);
        if ($ltv > 0.8) {
            $factors[] = 'High loan-to-value ratio';
        }
        
        if ($loanApplication['payment_history_score'] < 0.5) {
            $factors[] = 'Poor payment history';
        }
        
        return $factors;
    }
    
    /**
     * Save credit score
     */
    private function saveCreditScore($memberId, $score) {
        $this->db->update('members', [
            'credit_score' => round($score),
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$memberId]);
    }
    
    /**
     * Log fraud detection
     */
    private function logFraudDetection($transactionData, $riskScore, $isFraud) {
        $this->db->insert('fraud_detection_logs', [
            'transaction_id' => $transactionData['id'],
            'user_id' => $transactionData['user_id'],
            'risk_score' => $riskScore,
            'is_fraud' => $isFraud ? 1 : 0,
            'transaction_data' => json_encode($transactionData),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}

?>
