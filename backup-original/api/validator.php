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

/**
 * Comprehensive Validation System
 * Advanced input validation for KSP Lam Gabe Jaya
 */

class Validator {
    private static $errors = [];
    private static $data = [];
    
    /**
     * Validate input data against rules
     */
    public static function validate($data, $rules) {
        self::$errors = [];
        self::$data = $data;
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            
            foreach ($fieldRules as $rule => $params) {
                if (is_numeric($rule)) {
                    // Simple rule without parameters
                    $rule = $params;
                    $params = null;
                }
                
                self::applyRule($field, $value, $rule, $params);
            }
        }
        
        return empty(self::$errors);
    }
    
    /**
     * Apply validation rule
     */
    private static function applyRule($field, $value, $rule, $params) {
        switch ($rule) {
            case 'required':
                if (is_null($value) || $value === '') {
                    self::addError($field, 'required', "$field is required");
                }
                break;
                
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    self::addError($field, 'email', "$field must be a valid email");
                }
                break;
                
            case 'min':
                if (!empty($value) && strlen($value) < $params) {
                    self::addError($field, 'min', "$field must be at least $params characters");
                }
                break;
                
            case 'max':
                if (!empty($value) && strlen($value) > $params) {
                    self::addError($field, 'max', "$field must not exceed $params characters");
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    self::addError($field, 'numeric', "$field must be a number");
                }
                break;
                
            case 'positive':
                if (!empty($value) && (!is_numeric($value) || $value <= 0)) {
                    self::addError($field, 'positive', "$field must be a positive number");
                }
                break;
                
            case 'phone':
                if (!empty($value) && !preg_match('/^[0-9+\-\s()]+$/', $value)) {
                    self::addError($field, 'phone', "$field must be a valid phone number");
                }
                break;
                
            case 'date':
                if (!empty($value) && !self::isValidDate($value)) {
                    self::addError($field, 'date', "$field must be a valid date");
                }
                break;
                
            case 'alpha':
                if (!empty($value) && !preg_match('/^[a-zA-Z\s]+$/', $value)) {
                    self::addError($field, 'alpha', "$field must contain only letters");
                }
                break;
                
            case 'alphanumeric':
                if (!empty($value) && !preg_match('/^[a-zA-Z0-9\s]+$/', $value)) {
                    self::addError($field, 'alphanumeric', "$field must contain only letters and numbers");
                }
                break;
                
            case 'in':
                if (!empty($value) && !in_array($value, $params)) {
                    self::addError($field, 'in', "$field must be one of: " . implode(', ', $params));
                }
                break;
                
            case 'unique':
                if (!empty($value) && !self::isUnique($field, $value, $params)) {
                    self::addError($field, 'unique', "$field already exists");
                }
                break;
                
            case 'password':
                if (!empty($value) && !self::isStrongPassword($value)) {
                    self::addError($field, 'password', "$field must be at least 8 characters with uppercase, lowercase, number, and special character");
                }
                break;
                
            case 'confirm':
                $confirmField = $params;
                $confirmValue = self::$data[$confirmField] ?? null;
                if ($value !== $confirmValue) {
                    self::addError($field, 'confirm', "$field must match $confirmField");
                }
                break;
        }
    }
    
    /**
     * Add validation error
     */
    private static function addError($field, $rule, $message) {
        if (!isset(self::$errors[$field])) {
            self::$errors[$field] = [];
        }
        self::$errors[$field][$rule] = $message;
    }
    
    /**
     * Get validation errors
     */
    public static function getErrors() {
        return self::$errors;
    }
    
    /**
     * Get first error for each field
     */
    public static function getFirstErrors() {
        $firstErrors = [];
        foreach (self::$errors as $field => $rules) {
            $firstErrors[$field] = reset($rules);
        }
        return $firstErrors;
    }
    
    /**
     * Check if date is valid
     */
    private static function isValidDate($date) {
        $format = 'Y-m-d';
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Check if value is unique in database
     */
    private static function isUnique($field, $value, $params) {
        if (!isset($params['table']) || !isset($params['column'])) {
            return true;
        }
        
        try {
            $pdo = new PDO("mysql:host=localhost;unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=ksp_lamgabejaya_v2", "root", "root");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = "SELECT COUNT(*) FROM {$params['table']} WHERE {$params['column']} = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$value]);
            $count = $stmt->fetchColumn();
            
            // Exclude current record for updates
            if (isset($params['exclude'])) {
                $sql .= " AND id != ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$value, $params['exclude']]);
                $count = $stmt->fetchColumn();
            }
            
            return $count == 0;
        } catch (Exception $e) {
            return true; // Assume unique if database fails
        }
    }
    
    /**
     * Check if password is strong
     */
    private static function isStrongPassword($password) {
        if (strlen($password) < 8) {
            return false;
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitize($data) {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Validate Indonesian phone number
     */
    public static function validateIndonesianPhone($phone) {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check Indonesian phone number formats
        $patterns = [
            '/^08[0-9]{8,12}$/',     // Mobile: 08xx-xxxx-xxxx
            '/^62[0-9]{8,12}$/',     // Mobile with country code: 62xx-xxxx-xxxx
            '/^0[2-9][0-9]{6,10}$/', // Landline: 0xx-xxxx-xxxx
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $phone)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Validate Indonesian ID (NIK)
     */
    public static function validateNIK($nik) {
        // Remove spaces and dashes
        $nik = preg_replace('/[\s\-]/', '', $nik);
        
        // Check if exactly 16 digits
        if (!preg_match('/^[0-9]{16}$/', $nik)) {
            return false;
        }
        
        // Additional validation can be added here
        return true;
    }
    
    /**
     * Validate Indonesian postal code
     */
    public static function validatePostalCode($postalCode) {
        return preg_match('/^[0-9]{5}$/', $postalCode);
    }
}

/**
 * Common validation rules for the application
 */
class ValidationRules {
    public static function user() {
        return [
            'username' => ['required', 'min:3', 'max:50', 'alphanumeric'],
            'email' => ['required', 'email', 'unique' => ['table' => 'users', 'column' => 'email']],
            'password' => ['required', 'min:8', 'password'],
            'confirm_password' => ['required', 'confirm' => 'password'],
            'full_name' => ['required', 'min:2', 'max:255', 'alpha'],
            'phone' => ['required', 'phone'],
            'role' => ['required', 'in' => ['admin', 'staff', 'member']]
        ];
    }
    
    public static function member() {
        return [
            'person_id' => ['required', 'numeric', 'positive'],
            'member_number' => ['required', 'alphanumeric', 'unique' => ['table' => 'members', 'column' => 'member_number']],
            'join_date' => ['required', 'date'],
            'status' => ['required', 'in' => ['Active', 'Inactive', 'Suspended']]
        ];
    }
    
    public static function loan() {
        return [
            'member_id' => ['required', 'numeric', 'positive'],
            'amount' => ['required', 'numeric', 'positive'],
            'interest_rate' => ['required', 'numeric', 'positive'],
            'term_months' => ['required', 'numeric', 'positive'],
            'purpose' => ['required', 'min:5', 'max:500'],
            'disbursement_date' => ['required', 'date']
        ];
    }
    
    public static function savings() {
        return [
            'member_id' => ['required', 'numeric', 'positive'],
            'amount' => ['required', 'numeric', 'positive'],
            'type' => ['required', 'in' => ['mandatory', 'voluntary']],
            'transaction_date' => ['required', 'date']
        ];
    }
}
?>
