<?php
/**
 * Document Management API Endpoints
 */

// Include required files
require_once 'DocumentsController.php';

// Initialize controller
$documentsController = new DocumentsController($db);

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
    case 'DELETE':
        handleDeleteRequest();
        break;
    default:
        sendJsonResponse(['error' => 'Method not allowed'], 405);
}

function handleGetRequest() {
    global $documentsController;
    
    $path = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
    $endpoint = $path[0] ?? '';
    
    switch ($endpoint) {
        case 'documents':
            if (isset($path[1]) && is_numeric($path[1])) {
                // Get single document
                $document = $documentsController->getDocument($path[1]);
                if ($document) {
                    sendJsonResponse($document);
                } else {
                    sendJsonResponse(['error' => 'Document not found'], 404);
                }
            } else {
                // Get documents with filters
                $filters = [
                    'type' => $_GET['type'] ?? '',
                    'member_id' => $_GET['member_id'] ?? '',
                    'search' => $_GET['search'] ?? '',
                    'date_from' => $_GET['date_from'] ?? '',
                    'date_to' => $_GET['date_to'] ?? '',
                    'limit' => $_GET['limit'] ?? 50
                ];
                
                $documents = $documentsController->getDocuments($filters);
                sendJsonResponse($documents);
            }
            break;
            
        case 'templates':
            if (isset($path[1]) && is_numeric($path[1])) {
                // Get single template
                $template = $documentsController->getTemplate($path[1]);
                if ($template) {
                    sendJsonResponse($template);
                } else {
                    sendJsonResponse(['error' => 'Template not found'], 404);
                }
            } else {
                // Get all templates
                $templates = $documentsController->getTemplates();
                sendJsonResponse($templates);
            }
            break;
            
        case 'stats':
            $stats = $documentsController->getDocumentStats();
            sendJsonResponse($stats);
            break;
            
        case 'search':
            $searchTerm = $_GET['q'] ?? '';
            $filters = [
                'type' => $_GET['type'] ?? '',
                'member_id' => $_GET['member_id'] ?? ''
            ];
            
            $results = $documentsController->searchDocuments($searchTerm, $filters);
            sendJsonResponse($results);
            break;
            
        default:
            sendJsonResponse(['error' => 'Endpoint not found'], 404);
    }
}

function handlePostRequest() {
    global $documentsController;
    
    $path = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
    $endpoint = $path[0] ?? '';
    
    switch ($endpoint) {
        case 'documents':
            // Upload document
            if (!isset($_FILES['document'])) {
                sendJsonResponse(['error' => 'No file uploaded'], 400);
            }
            
            $data = [
                'type' => $_POST['type'] ?? 'other',
                'member_id' => $_POST['member_id'] ?? null,
                'description' => $_POST['description'] ?? '',
                'uploaded_by' => $_POST['uploaded_by'] ?? 1
            ];
            
            $result = $documentsController->uploadDocument($_FILES['document'], $data);
            sendJsonResponse($result);
            break;
            
        case 'templates':
            // Create template
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                sendJsonResponse(['error' => 'Invalid JSON'], 400);
            }
            
            $data = [
                'name' => $input['name'] ?? '',
                'type' => $input['type'] ?? 'other',
                'content' => $input['content'] ?? '',
                'variables' => $input['variables'] ?? '',
                'created_by' => $input['created_by'] ?? 1
            ];
            
            $result = $documentsController->createTemplate($data);
            sendJsonResponse($result);
            break;
            
        case 'generate':
            // Generate document from template
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                sendJsonResponse(['error' => 'Invalid JSON'], 400);
            }
            
            $data = [
                'template_id' => $input['template_id'] ?? 0,
                'member_id' => $input['member_id'] ?? null,
                'uploaded_by' => $input['uploaded_by'] ?? 1
            ];
            
            // Add template variables
            if (isset($input['variables']) && is_array($input['variables'])) {
                foreach ($input['variables'] as $key => $value) {
                    $data[$key] = $value;
                }
            }
            
            $result = $documentsController->generateFromTemplate($data['template_id'], $data);
            sendJsonResponse($result);
            break;
            
        default:
            sendJsonResponse(['error' => 'Endpoint not found'], 404);
    }
}

function handlePutRequest() {
    global $documentsController;
    
    $path = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
    $endpoint = $path[0] ?? '';
    
    switch ($endpoint) {
        case 'documents':
            if (!isset($path[1]) || !is_numeric($path[1])) {
                sendJsonResponse(['error' => 'Document ID required'], 400);
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                sendJsonResponse(['error' => 'Invalid JSON'], 400);
            }
            
            // Update document (mock implementation)
            $result = ['success' => true, 'message' => 'Document updated successfully'];
            sendJsonResponse($result);
            break;
            
        default:
            sendJsonResponse(['error' => 'Endpoint not found'], 404);
    }
}

function handleDeleteRequest() {
    global $documentsController;
    
    $path = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
    $endpoint = $path[0] ?? '';
    
    switch ($endpoint) {
        case 'documents':
            if (!isset($path[1]) || !is_numeric($path[1])) {
                sendJsonResponse(['error' => 'Document ID required'], 400);
            }
            
            $result = $documentsController->deleteDocument($path[1]);
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
