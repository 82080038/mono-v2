<?php
/**
 * Compliance API Endpoints
 */

// Include required files
require_once 'ComplianceController.php';

// Initialize controller
$complianceController = new ComplianceController($db);

// Handle API requests
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        handleGetRequest();
        break;
    case 'POST':
        handlePostRequest();
        break;
    case 'PUT':
        handlePutRequest();
        break;
    default:
        sendJsonResponse(['error' => 'Method not allowed'], 405);
}

function handleGetRequest() {
    global $complianceController;
    
    $path = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
    $endpoint = $path[0] ?? '';
    
    switch ($endpoint) {
        case 'status':
            // Get compliance status
            $status = $complianceController->getComplianceStatus();
            sendJsonResponse($status);
            break;
            
        case 'audit':
            // Get audit trail
            $filters = [
                'user_id' => $_GET['user_id'] ?? '',
                'action' => $_GET['action'] ?? '',
                'date_from' => $_GET['date_from'] ?? '',
                'date_to' => $_GET['date_to'] ?? '',
                'search' => $_GET['search'] ?? '',
                'limit' => $_GET['limit'] ?? 50
            ];
            
            $auditTrail = $complianceController->getAuditTrail($filters);
            sendJsonResponse($auditTrail);
            break;
            
        case 'reports':
            if (isset($path[1]) && is_numeric($path[1])) {
                // Get single report
                $report = $complianceController->getComplianceReport($path[1]);
                if ($report) {
                    sendJsonResponse($report);
                } else {
                    sendJsonResponse(['error' => 'Report not found'], 404);
                }
            } else {
                // Get compliance reports
                $filters = [
                    'type' => $_GET['type'] ?? '',
                    'status' => $_GET['status'] ?? '',
                    'date_from' => $_GET['date_from'] ?? '',
                    'date_to' => $_GET['date_to'] ?? '',
                    'limit' => $_GET['limit'] ?? 20
                ];
                
                $reports = $complianceController->getComplianceReports($filters);
                sendJsonResponse($reports);
            }
            break;
            
        case 'alerts':
            if (isset($path[1]) && is_numeric($path[1])) {
                // Get single alert
                $alert = $complianceController->getAlert($path[1]);
                if ($alert) {
                    sendJsonResponse($alert);
                } else {
                    sendJsonResponse(['error' => 'Alert not found'], 404);
                }
            } else {
                // Get active alerts
                $alerts = $complianceController->getActiveAlerts();
                sendJsonResponse($alerts);
            }
            break;
            
        case 'checks':
            // Get recent compliance checks
            $limit = $_GET['limit'] ?? 10;
            $checks = $complianceController->getRecentComplianceChecks($limit);
            sendJsonResponse($checks);
            break;
            
        case 'risk':
            // Get risk indicators
            $indicators = $complianceController->getRiskIndicators();
            sendJsonResponse($indicators);
            break;
            
        case 'areas':
            // Get compliance areas
            $areas = $complianceController->getComplianceAreas();
            sendJsonResponse($areas);
            break;
            
        default:
            sendJsonResponse(['error' => 'Endpoint not found'], 404);
    }
}

function handlePostRequest() {
    global $complianceController;
    
    $path = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
    $endpoint = $path[0] ?? '';
    
    switch ($endpoint) {
        case 'check':
            // Run compliance check
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                sendJsonResponse(['error' => 'Invalid JSON'], 400);
            }
            
            $checkType = $input['type'] ?? 'full';
            $result = $complianceController->runComplianceCheck($checkType);
            sendJsonResponse($result);
            break;
            
        case 'reports':
            // Generate compliance report
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                sendJsonResponse(['error' => 'Invalid JSON'], 400);
            }
            
            $config = [
                'type' => $input['type'] ?? 'full',
                'start_date' => $input['start_date'] ?? date('Y-m-01'),
                'end_date' => $input['end_date'] ?? date('Y-m-d'),
                'format' => $input['format'] ?? 'pdf',
                'generated_by' => $input['generated_by'] ?? 1
            ];
            
            $result = $complianceController->generateComplianceReport($config);
            sendJsonResponse($result);
            break;
            
        case 'audit':
            // Log audit trail
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                sendJsonResponse(['error' => 'Invalid JSON'], 400);
            }
            
            $data = [
                'user_id' => $input['user_id'] ?? 1,
                'action' => $input['action'] ?? '',
                'details' => $input['details'] ?? '',
                'level' => $input['level'] ?? 'normal'
            ];
            
            $result = $complianceController->logAuditTrail($data['user_id'], $data['action'], $data['details'], $data['level']);
            
            if ($result) {
                sendJsonResponse(['success' => true, 'message' => 'Audit trail logged successfully']);
            } else {
                sendJsonResponse(['success' => false, 'message' => 'Failed to log audit trail'], 500);
            }
            break;
            
        case 'alerts':
            // Create compliance alert
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                sendJsonResponse(['error' => 'Invalid JSON'], 400);
            }
            
            $data = [
                'area' => $input['area'] ?? '',
                'severity' => $input['severity'] ?? 'medium',
                'message' => $input['message'] ?? '',
                'details' => $input['details'] ?? ''
            ];
            
            $result = $complianceController->createAlert($data);
            sendJsonResponse($result);
            break;
            
        default:
            sendJsonResponse(['error' => 'Endpoint not found'], 404);
    }
}

function handlePutRequest() {
    global $complianceController;
    
    $path = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
    $endpoint = $path[0] ?? '';
    
    switch ($endpoint) {
        case 'alerts':
            if (!isset($path[1]) || !is_numeric($path[1])) {
                sendJsonResponse(['error' => 'Alert ID required'], 400);
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                sendJsonResponse(['error' => 'Invalid JSON'], 400);
            }
            
            $action = $input['action'] ?? '';
            $result = $complianceController->handleAlert($path[1], $action);
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
