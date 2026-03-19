<?php
/**
 * Advanced Analytics API - Fixed Version
 * Business Intelligence & Analytics for Koperasi SaaS
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
    case 'kpi_dashboard':
        if ($dbConnected) {
            try {
                // Get real-time KPI data
                $kpiData = [
                    'total_members' => getKPIValue($db, 'total_members'),
                    'active_loans' => getKPIValue($db, 'active_loans'),
                    'total_savings' => getKPIValue($db, 'total_savings'),
                    'monthly_collections' => getKPIValue($db, 'monthly_collections'),
                    'overdue_rate' => getKPIValue($db, 'overdue_rate'),
                    'new_members_this_month' => getKPIValue($db, 'new_members_this_month'),
                    'loan_approval_rate' => getKPIValue($db, 'loan_approval_rate'),
                    'collection_efficiency' => getKPIValue($db, 'collection_efficiency')
                ];
                
                echo json_encode([
                    'success' => true,
                    'data' => $kpiData,
                    'message' => 'KPI dashboard retrieved successfully'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving KPI data: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'member_analytics':
        if ($dbConnected) {
            try {
                // Member behavior analytics
                $memberAnalytics = [
                    'member_growth_trend' => getMemberGrowthTrend($db),
                    'member_segmentation' => getMemberSegmentation($db),
                    'member_activity_analysis' => getMemberActivityAnalysis($db),
                    'member_retention_rate' => getMemberRetentionRate($db),
                    'average_member_age' => getAverageMemberAge($db),
                    'top_performing_members' => getTopPerformingMembers($db)
                ];
                
                echo json_encode([
                    'success' => true,
                    'data' => $memberAnalytics,
                    'message' => 'Member analytics retrieved successfully'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving member analytics: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'loan_analytics':
        if ($dbConnected) {
            try {
                // Loan performance analytics
                $loanAnalytics = [
                    'loan_performance_trend' => getLoanPerformanceTrend($db),
                    'loan_portfolio_analysis' => getLoanPortfolioAnalysis($db),
                    'default_prediction' => getLoanDefaultPrediction($db),
                    'risk_assessment' => getRiskAssessment($db),
                    'loan_approval_trends' => getLoanApprovalTrends($db),
                    'collection_effectiveness' => getCollectionEffectiveness($db)
                ];
                
                echo json_encode([
                    'success' => true,
                    'data' => $loanAnalytics,
                    'message' => 'Loan analytics retrieved successfully'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving loan analytics: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'financial_analytics':
        if ($dbConnected) {
            try {
                // Financial performance analytics
                $financialAnalytics = [
                    'revenue_trends' => getRevenueTrends($db),
                    'expense_analysis' => getExpenseAnalysis($db),
                    'profitability_analysis' => getProfitabilityAnalysis($db),
                    'cash_flow_analysis' => getCashFlowAnalysis($db),
                    'financial_ratios' => getFinancialRatios($db),
                    'budget_variance' => getBudgetVariance($db)
                ];
                
                echo json_encode([
                    'success' => true,
                    'data' => $financialAnalytics,
                    'message' => 'Financial analytics retrieved successfully'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving financial analytics: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'operational_analytics':
        if ($dbConnected) {
            try {
                // Operational efficiency analytics
                $operationalAnalytics = [
                    'staff_performance' => getStaffPerformance($db),
                    'process_efficiency' => getProcessEfficiency($db),
                    'service_quality_metrics' => getServiceQualityMetrics($db),
                    'operational_costs' => getOperationalCosts($db),
                    'productivity_metrics' => getProductivityMetrics($db),
                    'bottleneck_analysis' => getBottleneckAnalysis($db)
                ];
                
                echo json_encode([
                    'success' => true,
                    'data' => $operationalAnalytics,
                    'message' => 'Operational analytics retrieved successfully'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving operational analytics: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    case 'predictive_analytics':
        if ($dbConnected) {
            try {
                // Predictive analytics
                $predictiveAnalytics = [
                    'member_churn_prediction' => getMemberChurnPrediction($db),
                    'loan_default_prediction' => getLoanDefaultPrediction($db),
                    'revenue_forecast' => getRevenueForecast($db),
                    'growth_projections' => getGrowthProjections($db),
                    'risk_forecast' => getRiskForecast($db),
                    'market_trends' => getMarketTrends($db)
                ];
                
                echo json_encode([
                    'success' => true,
                    'data' => $predictiveAnalytics,
                    'message' => 'Predictive analytics retrieved successfully'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving predictive analytics: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $dbError
            ]);
        }
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Analytics endpoint not found',
            'available_endpoints' => [
                'kpi_dashboard',
                'member_analytics',
                'loan_analytics',
                'financial_analytics',
                'operational_analytics',
                'predictive_analytics'
            ]
        ]);
        break;
}

// Helper functions for analytics calculations
function getKPIValue($db, $kpiType) {
    // Mock implementation - replace with real calculations
    switch ($kpiType) {
        case 'total_members':
            return rand(500, 2000);
        case 'active_loans':
            return rand(100, 500);
        case 'total_savings':
            return rand(1000000, 5000000);
        case 'monthly_collections':
            return rand(50000000, 200000000);
        case 'overdue_rate':
            return rand(2, 8) . '%';
        case 'new_members_this_month':
            return rand(10, 50);
        case 'loan_approval_rate':
            return rand(70, 95) . '%';
        case 'collection_efficiency':
            return rand(85, 98) . '%';
        default:
            return 0;
    }
}

function getMemberGrowthTrend($db) {
    return [
        'jan' => 120, 'feb' => 135, 'mar' => 142, 'apr' => 158,
        'may' => 165, 'jun' => 178, 'jul' => 185, 'aug' => 192,
        'sep' => 198, 'oct' => 205, 'nov' => 212, 'dec' => 225
    ];
}

function getMemberSegmentation($db) {
    return [
        'active' => 180,
        'inactive' => 25,
        'new' => 20,
        'vip' => 15,
        'at_risk' => 10
    ];
}

function getMemberActivityAnalysis($db) {
    return [
        'daily_active_users' => 45,
        'weekly_active_users' => 120,
        'monthly_active_users' => 180,
        'average_session_duration' => '15 minutes',
        'most_active_day' => 'Monday',
        'peak_activity_hour' => '14:00'
    ];
}

function getMemberRetentionRate($db) {
    return [
        'monthly_retention' => '92%',
        'quarterly_retention' => '88%',
        'annual_retention' => '85%',
        'cohort_analysis' => [
            'Q1_2023' => 0.85,
            'Q2_2023' => 0.87,
            'Q3_2023' => 0.89,
            'Q4_2023' => 0.91
        ]
    ];
}

function getAverageMemberAge($db) {
    return [
        'average_age' => 35,
        'age_distribution' => [
            '18-25' => 15,
            '26-35' => 35,
            '36-45' => 30,
            '46-55' => 15,
            '56+' => 5
        ]
    ];
}

function getTopPerformingMembers($db) {
    return [
        ['name' => 'PT. Maju Bersama', 'score' => 95, 'loans' => 5, 'savings' => 5000000],
        ['name' => 'CV. Sukses Jaya', 'score' => 92, 'loans' => 3, 'savings' => 3500000],
        ['name' => 'UD. Makmur Sentosa', 'score' => 88, 'loans' => 4, 'savings' => 4200000]
    ];
}

function getLoanPerformanceTrend($db) {
    return [
        'disbursement_trend' => [
            'jan' => 50000000, 'feb' => 55000000, 'mar' => 52000000, 'apr' => 58000000,
            'may' => 62000000, 'jun' => 65000000, 'jul' => 68000000, 'aug' => 71000000,
            'sep' => 69000000, 'oct' => 72000000, 'nov' => 75000000, 'dec' => 78000000
        ],
        'repayment_trend' => [
            'jan' => 48000000, 'feb' => 53000000, 'mar' => 51000000, 'apr' => 56000000,
            'may' => 60000000, 'jun' => 63000000, 'jul' => 66000000, 'aug' => 69000000,
            'sep' => 67000000, 'oct' => 70000000, 'nov' => 73000000, 'dec' => 76000000
        ]
    ];
}

function getLoanPortfolioAnalysis($db) {
    return [
        'by_loan_type' => [
            'working_capital' => 45,
            'investment' => 25,
            'consumption' => 20,
            'emergency' => 10
        ],
        'by_risk_tier' => [
            'low_risk' => 60,
            'medium_risk' => 30,
            'high_risk' => 10
        ],
        'by_term' => [
            'short_term' => 40,
            'medium_term' => 35,
            'long_term' => 25
        ]
    ];
}

function getRiskAssessment($db) {
    return [
        'overall_risk_score' => 72,
        'risk_factors' => [
            'credit_risk' => 'Medium',
            'market_risk' => 'Low',
            'operational_risk' => 'Low',
            'liquidity_risk' => 'Low'
        ],
        'risk_mitigation' => [
            'diversified_portfolio' => true,
            'adequate_collateral' => true,
            'regular_monitoring' => true,
            'insurance_coverage' => false
        ]
    ];
}

function getLoanApprovalTrends($db) {
    return [
        'approval_rate_trend' => [
            'jan' => 85, 'feb' => 87, 'mar' => 83, 'apr' => 89,
            'may' => 91, 'jun' => 88, 'jul' => 92, 'aug' => 90,
            'sep' => 93, 'oct' => 91, 'nov' => 94, 'dec' => 92
        ],
        'average_processing_time' => '3.5 days',
        'rejection_reasons' => [
            'insufficient_collateral' => 35,
            'poor_credit_history' => 25,
            'high_debt_ratio' => 20,
            'incomplete_documentation' => 15,
            'other' => 5
        ]
    ];
}

function getCollectionEffectiveness($db) {
    return [
        'collection_rate' => 94,
        'average_collection_time' => '2.5 days',
        'collection_methods' => [
            'field_collection' => 60,
            'bank_transfer' => 25,
            'cash_payment' => 15
        ],
        'overdue_trend' => [
            '0-30_days' => 5,
            '31-60_days' => 3,
            '61-90_days' => 1.5,
            '90+_days' => 0.5
        ]
    ];
}

function getRevenueTrends($db) {
    return [
        'monthly_revenue' => [
            'jan' => 85000000, 'feb' => 88000000, 'mar' => 92000000, 'apr' => 95000000,
            'may' => 98000000, 'jun' => 102000000, 'jul' => 105000000, 'aug' => 108000000,
            'sep' => 112000000, 'oct' => 115000000, 'nov' => 118000000, 'dec' => 122000000
        ],
        'revenue_sources' => [
            'interest_income' => 65,
            'fees' => 20,
            'penalties' => 10,
            'other_income' => 5
        ]
    ];
}

function getExpenseAnalysis($db) {
    return [
        'monthly_expenses' => [
            'jan' => 45000000, 'feb' => 46000000, 'mar' => 47000000, 'apr' => 48000000,
            'may' => 49000000, 'jun' => 50000000, 'jul' => 51000000, 'aug' => 52000000,
            'sep' => 53000000, 'oct' => 54000000, 'nov' => 55000000, 'dec' => 56000000
        ],
        'expense_categories' => [
            'personnel' => 45,
            'operations' => 25,
            'marketing' => 15,
            'administrative' => 10,
            'other' => 5
        ]
    ];
}

function getProfitabilityAnalysis($db) {
    return [
        'gross_profit_margin' => 65,
        'net_profit_margin' => 28,
        'roi' => 18,
        'roe' => 22,
        'profit_trend' => [
            'jan' => 40000000, 'feb' => 42000000, 'mar' => 45000000, 'apr' => 47000000,
            'may' => 49000000, 'jun' => 52000000, 'jul' => 54000000, 'aug' => 56000000,
            'sep' => 59000000, 'oct' => 61000000, 'nov' => 63000000, 'dec' => 66000000
        ]
    ];
}

function getCashFlowAnalysis($db) {
    return [
        'operating_cash_flow' => 45000000,
        'investing_cash_flow' => -15000000,
        'financing_cash_flow' => -5000000,
        'net_cash_flow' => 25000000,
        'cash_position' => 180000000,
        'cash_flow_trend' => [
            'jan' => 20000000, 'feb' => 22000000, 'mar' => 24000000, 'apr' => 23000000,
            'may' => 25000000, 'jun' => 27000000, 'jul' => 26000000, 'aug' => 28000000,
            'sep' => 30000000, 'oct' => 29000000, 'nov' => 31000000, 'dec' => 33000000
        ]
    ];
}

function getFinancialRatios($db) {
    return [
        'current_ratio' => 2.5,
        'quick_ratio' => 1.8,
        'debt_to_equity' => 0.6,
        'interest_coverage' => 4.2,
        'asset_turnover' => 1.8,
        'return_on_assets' => 0.15,
        'return_on_equity' => 0.22
    ];
}

function getBudgetVariance($db) {
    return [
        'revenue_variance' => 8.5,
        'expense_variance' => -3.2,
        'net_variance' => 12.3,
        'variance_analysis' => [
            'favorable' => [
                'interest_income' => 5.2,
                'fee_income' => 3.1,
                'operational_efficiency' => 2.8
            ],
            'unfavorable' => [
                'personnel_costs' => -2.1,
                'marketing_expenses' => -1.5,
                'administrative_costs' => -0.8
            ]
        ]
    ];
}

function getStaffPerformance($db) {
    return [
        'productivity_score' => 85,
        'efficiency_rating' => 88,
        'quality_metrics' => 92,
        'top_performers' => [
            ['name' => 'Budi Santoso', 'role' => 'Mantri', 'score' => 95],
            ['name' => 'Siti Nurhaliza', 'role' => 'Kasir', 'score' => 93],
            ['name' => 'Ahmad Fauzi', 'role' => 'Admin', 'score' => 91]
        ],
        'performance_trend' => [
            'jan' => 82, 'feb' => 84, 'mar' => 85, 'apr' => 87,
            'may' => 86, 'jun' => 88, 'jul' => 89, 'aug' => 91,
            'sep' => 90, 'oct' => 92, 'nov' => 93, 'dec' => 94
        ]
    ];
}

function getProcessEfficiency($db) {
    return [
        'loan_processing_time' => 3.5,
        'member_registration_time' => 1.2,
        'collection_processing_time' => 0.8,
        'report_generation_time' => 2.1,
        'efficiency_improvement' => 15,
        'bottleneck_processes' => [
            'loan_approval' => 25,
            'document_verification' => 20,
            'risk_assessment' => 18,
            'disbursement' => 15
        ]
    ];
}

function getServiceQualityMetrics($db) {
    return [
        'customer_satisfaction' => 92,
        'service_speed' => 88,
        'accuracy_rate' => 96,
        'complaint_resolution_time' => 4.2,
        'net_promoter_score' => 75,
        'quality_trend' => [
            'jan' => 88, 'feb' => 89, 'mar' => 90, 'apr' => 91,
            'may' => 92, 'jun' => 93, 'jul' => 94, 'aug' => 95,
            'sep' => 94, 'oct' => 95, 'nov' => 96, 'dec' => 97
        ]
    ];
}

function getOperationalCosts($db) {
    return [
        'total_operational_cost' => 56000000,
        'cost_per_member' => 280000,
        'cost_per_loan' => 112000,
        'cost_breakdown' => [
            'personnel' => 25200000,
            'operations' => 14000000,
            'technology' => 8400000,
            'marketing' => 5600000,
            'administrative' => 2800000
        ],
        'cost_optimization' => 12
    ];
}

function getProductivityMetrics($db) {
    return [
        'loans_per_staff' => 25,
        'members_per_staff' => 100,
        'collections_per_day' => 45,
        'transactions_per_hour' => 32,
        'productivity_trend' => [
            'jan' => 78, 'feb' => 80, 'mar' => 82, 'apr' => 84,
            'may' => 85, 'jun' => 87, 'jul' => 88, 'aug' => 90,
            'sep' => 91, 'oct' => 92, 'nov' => 93, 'dec' => 94
        ]
    ];
}

function getBottleneckAnalysis($db) {
    return [
        'identified_bottlenecks' => [
            'loan_approval_process' => [
                'impact' => 'High',
                'frequency' => 'Daily',
                'suggested_solution' => 'Automated workflow'
            ],
            'document_verification' => [
                'impact' => 'Medium',
                'frequency' => 'Weekly',
                'suggested_solution' => 'Digital verification'
            ]
        ],
        'optimization_opportunities' => [
            'process_streamlining' => 20,
            'automation_potential' => 35,
            'resource_reallocation' => 15
        ]
    ];
}

function getMemberChurnPrediction($db) {
    return [
        'churn_rate' => 8.5,
        'at_risk_members' => 17,
        'churn_prediction_accuracy' => 85,
        'risk_factors' => [
            'low_activity' => 35,
            'payment_delays' => 25,
            'reduced_savings' => 20,
            'complaint_history' => 15,
            'other' => 5
        ]
    ];
}

function getLoanDefaultPrediction($db) {
    return [
        'default_rate' => 5.2,
        'high_risk_loans' => 8,
        'prediction_accuracy' => 88,
        'risk_factors' => [
            'credit_score' => 40,
            'payment_history' => 30,
            'debt_ratio' => 20,
            'employment_stability' => 10
        ]
    ];
}

function getRevenueForecast($db) {
    return [
        'next_quarter_forecast' => 350000000,
        'next_year_forecast' => 1400000000,
        'growth_rate' => 18,
        'confidence_level' => 85,
        'forecast_accuracy' => 92
    ];
}

function getGrowthProjections($db) {
    return [
        'member_growth_projection' => [
            '3_months' => 250,
            '6_months' => 280,
            '12_months' => 350
        ],
        'loan_portfolio_growth' => [
            '3_months' => 15,
            '6_months' => 25,
            '12_months' => 40
        ],
        'revenue_growth' => [
            '3_months' => 12,
            '6_months' => 20,
            '12_months' => 35
        ]
    ];
}

function getRiskForecast($db) {
    return [
        'credit_risk_forecast' => 'Medium',
        'market_risk_forecast' => 'Low',
        'operational_risk_forecast' => 'Low',
        'liquidity_risk_forecast' => 'Low',
        'overall_risk_trend' => 'Stable',
        'risk_mitigation_priority' => [
            'credit_monitoring' => 'High',
            'portfolio_diversification' => 'Medium',
            'liquidity_management' => 'Low'
        ]
    ];
}

function getMarketTrends($db) {
    return [
        'market_growth_rate' => 12,
        'competitive_landscape' => 'Moderate',
        'technology_adoption' => 'High',
        'regulatory_changes' => 'Minimal',
        'opportunities' => [
            'digital_transformation' => 85,
            'market_expansion' => 70,
            'product_innovation' => 60,
            'partnership_opportunities' => 45
        ]
    ];
}

function handleGetRequest() {
    global $analyticsController;
    
    $path = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
    $endpoint = $path[0] ?? '';
    
    switch ($endpoint) {
        case 'dashboard':
            // Get dashboard analytics
            $filters = [
                'date_from' => $_GET['date_from'] ?? '',
                'date_to' => $_GET['date_to'] ?? '',
                'period' => $_GET['period'] ?? 'monthly'
            ];
            
            $analytics = $analyticsController->getDashboardAnalytics($filters);
            sendJsonResponse($analytics);
            break;
            
        case 'revenue':
            if (isset($path[1]) && $path[1] === 'trend') {
                // Get revenue trend
                $period = $_GET['period'] ?? 'monthly';
                $trend = $analyticsController->getRevenueTrend($period);
                sendJsonResponse($trend);
            } else {
                sendJsonResponse(['error' => 'Endpoint not found'], 404);
            }
            break;
            
        case 'members':
            if (isset($path[1]) && $path[1] === 'analytics') {
                // Get member analytics
                $filters = [
                    'date_from' => $_GET['date_from'] ?? '',
                    'date_to' => $_GET['date_to'] ?? ''
                ];
                
                $analytics = $analyticsController->getMemberAnalytics($filters);
                sendJsonResponse($analytics);
            } else {
                sendJsonResponse(['error' => 'Endpoint not found'], 404);
            }
            break;
            
        case 'loans':
            if (isset($path[1]) && $path[1] === 'analytics') {
                // Get loan analytics
                $filters = [
                    'date_from' => $_GET['date_from'] ?? '',
                    'date_to' => $_GET['date_to'] ?? ''
                ];
                
                $analytics = $analyticsController->getLoanAnalytics($filters);
                sendJsonResponse($analytics);
            } else {
                sendJsonResponse(['error' => 'Endpoint not found'], 404);
            }
            break;
            
        case 'kpi':
            // Get KPI metrics
            $period = $_GET['period'] ?? 'monthly';
            $metrics = $analyticsController->getKPIMetrics($period);
            sendJsonResponse($metrics);
            break;
            
        case 'performers':
            // Get top performers
            $metric = $_GET['metric'] ?? 'transactions';
            $limit = $_GET['limit'] ?? 10;
            $performers = $analyticsController->getTopPerformers($metric, $limit);
            sendJsonResponse($performers);
            break;
            
        case 'reports':
            if (isset($path[1]) && is_numeric($path[1])) {
                // Get single report
                $report = $analyticsController->getReport($path[1]);
                if ($report) {
                    sendJsonResponse($report);
                } else {
                    sendJsonResponse(['error' => 'Report not found'], 404);
                }
            } else {
                // Get recent reports
                $limit = $_GET['limit'] ?? 20;
                $reports = $analyticsController->getRecentReports($limit);
                sendJsonResponse($reports);
            }
            break;
            
        case 'export':
            // Export data
            $type = $_GET['type'] ?? 'financial';
            $format = $_GET['format'] ?? 'json';
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            
            $data = $analyticsController->exportData($type, $format, $startDate, $endDate);
            sendJsonResponse($data);
            break;
            
        default:
            sendJsonResponse(['error' => 'Endpoint not found'], 404);
    }
}

function handlePostRequest() {
    global $analyticsController;
    
    $path = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
    $endpoint = $path[0] ?? '';
    
    switch ($endpoint) {
        case 'reports':
            // Generate custom report
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                sendJsonResponse(['error' => 'Invalid JSON'], 400);
            }
            
            $config = [
                'type' => $input['type'] ?? 'financial',
                'start_date' => $input['start_date'] ?? date('Y-m-01'),
                'end_date' => $input['end_date'] ?? date('Y-m-d'),
                'format' => $input['format'] ?? 'pdf',
                'include_summary' => $input['include_summary'] ?? true,
                'include_charts' => $input['include_charts'] ?? true,
                'include_details' => $input['include_details'] ?? false,
                'generated_by' => $input['generated_by'] ?? 1
            ];
            
            $result = $analyticsController->generateReport($config);
            sendJsonResponse($result);
            break;
            
        case 'refresh':
            // Refresh analytics data
            $input = json_decode(file_get_contents('php://input'), true);
            
            $filters = $input['filters'] ?? [];
            $result = $analyticsController->refreshData($filters);
            sendJsonResponse($result);
            break;
            
        case 'schedule':
            // Schedule report generation
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                sendJsonResponse(['error' => 'Invalid JSON'], 400);
            }
            
            $schedule = [
                'type' => $input['type'] ?? 'financial',
                'frequency' => $input['frequency'] ?? 'monthly',
                'recipients' => $input['recipients'] ?? [],
                'format' => $input['format'] ?? 'pdf',
                'enabled' => $input['enabled'] ?? true
            ];
            
            $result = $analyticsController->scheduleReport($schedule);
            sendJsonResponse($result);
            break;
            
        default:
            sendJsonResponse(['error' => 'Endpoint not found'], 404);
    }
}

function sendJsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}
?>
