<?php
/**
 * API Response Standardization Helper
 * Provides consistent response format across all API endpoints
 */

class ApiResponse {
    /**
     * Success response
     */
    public static function success($data = null, $message = 'Operation successful') {
        header('Content-Type: application/json');
        http_response_code(200);
        
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $data
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit();
    }
    
    /**
     * Error response
     */
    public static function error($message = 'Operation failed', $code = 400, $data = null) {
        header('Content-Type: application/json');
        http_response_code($code);
        
        $response = [
            'success' => false,
            'error' => $message,
            'code' => $code,
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $data
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit();
    }
    
    /**
     * Validation error response
     */
    public static function validationError($errors = []) {
        header('Content-Type: application/json');
        http_response_code(422);
        
        $response = [
            'success' => false,
            'error' => 'Validation failed',
            'code' => 422,
            'timestamp' => date('Y-m-d H:i:s'),
            'validation_errors' => $errors
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit();
    }
    
    /**
     * Unauthorized response
     */
    public static function unauthorized($message = 'Unauthorized access') {
        self::error($message, 401);
    }
    
    /**
     * Forbidden response
     */
    public static function forbidden($message = 'Access forbidden') {
        self::error($message, 403);
    }
    
    /**
     * Not found response
     */
    public static function notFound($message = 'Resource not found') {
        self::error($message, 404);
    }
    
    /**
     * Server error response
     */
    public static function serverError($message = 'Internal server error') {
        self::error($message, 500);
    }
    
    /**
     * Paginated response
     */
    public static function paginated($data, $page, $limit, $total, $message = 'Data retrieved successfully') {
        header('Content-Type: application/json');
        http_response_code(200);
        
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit),
                'has_next' => ($page * $limit) < $total,
                'has_prev' => $page > 1
            ],
            'data' => $data
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit();
    }
}

/**
 * API Request Validator
 */
class ApiValidator {
    private static $errors = [];
    
    public static function required($fields, $data) {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                self::$errors[$field] = "$field is required";
            }
        }
        return self::hasErrors();
    }
    
    public static function email($field, $value) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            self::$errors[$field] = "Invalid email format";
        }
        return self::hasErrors();
    }
    
    public static function minLength($field, $value, $min) {
        if (strlen($value) < $min) {
            self::$errors[$field] = "$field must be at least $min characters";
        }
        return self::hasErrors();
    }
    
    public static function numeric($field, $value) {
        if (!is_numeric($value)) {
            self::$errors[$field] = "$field must be a number";
        }
        return self::hasErrors();
    }
    
    public static function getErrors() {
        return self::$errors;
    }
    
    private static function hasErrors() {
        return !empty(self::$errors);
    }
    
    public static function reset() {
        self::$errors = [];
    }
}

/**
 * API Logger
 */
class ApiLogger {
    public static function log($level, $message, $context = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        error_log(json_encode($logEntry));
    }
    
    public static function info($message, $context = []) {
        self::log('INFO', $message, $context);
    }
    
    public static function warning($message, $context = []) {
        self::log('WARNING', $message, $context);
    }
    
    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }
}
?>
