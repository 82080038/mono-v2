<?php
/**
 * KSP Lam Gabe Jaya - Base Controller
 * Base controller class for all controllers
 */

namespace App\Controllers;

use App\Models\Model;
use App\Services\Service;

abstract class Controller {
    protected $request;
    protected $response;
    
    public function __construct($request, $response) {
        $this->request = $request;
        $this->response = $response;
    }
    
    /**
     * Render view with data
     */
    protected function view($template, $data = []) {
        // Add common data
        $data['user'] = $this->getCurrentUser();
        $data['app_name'] = APP_NAME;
        $data['app_version'] = APP_VERSION;
        $data['base_url'] = BASE_URL;
        $data['assets_url'] = ASSETS_URL;
        
        return $this->response->view($template, $data);
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200) {
        return $this->response->json($data, $statusCode);
    }
    
    /**
     * Return success response
     */
    protected function success($message, $data = null) {
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return $this->json($response);
    }
    
    /**
     * Return error response
     */
    protected function error($message, $statusCode = 400, $data = null) {
        $response = [
            'success' => false,
            'message' => $message,
            'status_code' => $statusCode
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return $this->json($response, $statusCode);
    }
    
    /**
     * Get current user
     */
    protected function getCurrentUser() {
        if (isset($_SESSION['user'])) {
            return $_SESSION['user'];
        }
        return null;
    }
    
    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated() {
        return $this->getCurrentUser() !== null;
    }
    
    /**
     * Require authentication
     */
    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            if ($this->isAjaxRequest()) {
                return $this->error('Authentication required', 401);
            } else {
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
        }
    }
    
    /**
     * Check if user has specific role
     */
    protected function hasRole($role) {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Require specific role
     */
    protected function requireRole($role) {
        if (!$this->hasRole($role)) {
            if ($this->isAjaxRequest()) {
                return $this->error('Insufficient permissions', 403);
            } else {
                header('Location: ' . BASE_URL . '/unauthorized');
                exit;
            }
        }
    }
    
    /**
     * Check if request is AJAX
     */
    protected function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }
    
    /**
     * Get pagination parameters
     */
    protected function getPaginationParams($defaultLimit = 20, $maxLimit = 100) {
        $page = max(1, intval($this->request->get('page', 1)));
        $limit = min($maxLimit, max(1, intval($this->request->get('limit', $defaultLimit))));
        $offset = ($page - 1) * $limit;
        
        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset
        ];
    }
    
    /**
     * Build pagination response
     */
    protected function buildPaginationResponse($items, $total, $pagination) {
        return [
            'items' => $items,
            'pagination' => [
                'page' => $pagination['page'],
                'limit' => $pagination['limit'],
                'total' => $total,
                'pages' => ceil($total / $pagination['limit']),
                'has_next' => ($pagination['page'] * $pagination['limit']) < $total,
                'has_prev' => $pagination['page'] > 1
            ]
        ];
    }
    
    /**
     * Validate required parameters
     */
    protected function validateRequired($required) {
        $missing = [];
        foreach ($required as $field) {
            if (!$this->request->get($field)) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            return $this->error('Missing required parameters: ' . implode(', ', $missing));
        }
        
        return true;
    }
    
    /**
     * Sanitize input
     */
    protected function sanitize($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitize'], $input);
        }
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate email
     */
    protected function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate date
     */
    protected function validateDate($date, $format = 'Y-m-d') {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Validate numeric
     */
    protected function validateNumeric($value) {
        return is_numeric($value) && $value >= 0;
    }
    
    /**
     * Log activity
     */
    protected function logActivity($action, $details = null) {
        try {
            $logger = new \Core\Logger\Logger();
            $user = $this->getCurrentUser();
            
            $logger->info($action, [
                'user_id' => $user['id'] ?? null,
                'user_name' => $user['username'] ?? 'Guest',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'details' => $details
            ]);
        } catch (\Exception $e) {
            // Log error but don't break the application
            error_log("Activity log error: " . $e->getMessage());
        }
    }
    
    /**
     * Flash message helper
     */
    protected function flash($type, $message) {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Get flash messages
     */
    protected function getFlashMessages() {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }
    
    /**
     * Redirect helper
     */
    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Back helper
     */
    protected function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL;
        $this->redirect($referer);
    }
}
?>
