<?php
/**
 * Complete Data Validation System
 * Comprehensive input validation and sanitization
 */

class DataValidator {
    private static $errors = [];
    private static $sanitized_data = [];
    
    /**
     * Validate and sanitize input data
     */
    public static function validate($data, $rules = []) {
        self::$errors = [];
        self::$sanitized_data = [];
        
        foreach ($rules as $field => $field_rules) {
            $value = $data[$field] ?? '';
            
            // Apply validation rules
            foreach ($field_rules as $rule => $params) {
                switch ($rule) {
                    case 'required':
                        self::validateRequired($field, $value, $params);
                        break;
                    case 'email':
                        self::validateEmail($field, $value, $params);
                        break;
                    case 'phone':
                        self::validatePhone($field, $value, $params);
                        break;
                    case 'numeric':
                        self::validateNumeric($field, $value, $params);
                        break;
                    case 'min_length':
                        self::validateMinLength($field, $value, $params);
                        break;
                    case 'max_length':
                        self::validateMaxLength($field, $value, $params);
                        break;
                    case 'alphanumeric':
                        self::validateAlphanumeric($field, $value, $params);
                        break;
                    case 'safe_string':
                        self::validateSafeString($field, $value, $params);
                        break;
                    case 'amount':
                        self::validateAmount($field, $value, $params);
                        break;
                    case 'date':
                        self::validateDate($field, $value, $params);
                        break;
                }
            }
            
            // Sanitize and store
            self::$sanitized_data[$field] = self::sanitize($value, $field_rules);
        }
        
        return [
            'success' => empty(self::$errors),
            'errors' => self::$errors,
            'sanitized_data' => self::$sanitized_data
        ];
    }
    
    /**
     * Validate required field
     */
    private static function validateRequired($field, $value, $params) {
        if (empty($value) || trim($value) === '') {
            self::$errors[$field][] = "Field {$field} is required";
        }
    }
    
    /**
     * Validate email
     */
    private static function validateEmail($field, $value, $params) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            self::$errors[$field][] = "Invalid email format for {$field}";
        }
    }
    
    /**
     * Validate phone number
     */
    private static function validatePhone($field, $value, $params) {
        if (!empty($value)) {
            // Remove non-numeric characters
            $phone = preg_replace('/[^0-9]/', '', $value);
            
            // Check if valid Indonesian phone number
            if (strlen($phone) < 10 || strlen($phone) > 13) {
                self::$errors[$field][] = "Invalid phone number format for {$field}";
            }
        }
    }
    
    /**
     * Validate numeric
     */
    private static function validateNumeric($field, $value, $params) {
        if (!empty($value) && !is_numeric($value)) {
            self::$errors[$field][] = "Field {$field} must be numeric";
        }
    }
    
    /**
     * Validate minimum length
     */
    private static function validateMinLength($field, $value, $params) {
        $min_length = $params['min'] ?? 3;
        
        if (strlen($value) < $min_length) {
            self::$errors[$field][] = "Field {$field} must be at least {$min_length} characters";
        }
    }
    
    /**
     * Validate maximum length
     */
    private static function validateMaxLength($field, $value, $params) {
        $max_length = $params['max'] ?? 255;
        
        if (strlen($value) > $max_length) {
            self::$errors[$field][] = "Field {$field} must not exceed {$max_length} characters";
        }
    }
    
    /**
     * Validate alphanumeric
     */
    private static function validateAlphanumeric($field, $value, $params) {
        if (!empty($value) && !preg_match('/^[a-zA-Z0-9]+$/', $value)) {
            self::$errors[$field][] = "Field {$field} must contain only alphanumeric characters";
        }
    }
    
    /**
     * Validate safe string
     */
    private static function validateSafeString($field, $value, $params) {
        if (!empty($value)) {
            // Check for dangerous characters
            $dangerous_chars = ['<', '>', '"', "'", '\\', '/', ';', ':', '(', ')', '{', '}'];
            
            foreach ($dangerous_chars as $char) {
                if (strpos($value, $char) !== false) {
                    self::$errors[$field][] = "Field {$field} contains unsafe characters";
                    break;
                }
            }
            
            // Check for SQL injection patterns
            $sql_patterns = ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'UNION', 'SCRIPT'];
            
            foreach ($sql_patterns as $pattern) {
                if (stripos($value, $pattern) !== false) {
                    self::$errors[$field][] = "Field {$field} contains potentially dangerous content";
                    break;
                }
            }
        }
    }
    
    /**
     * Validate amount
     */
    private static function validateAmount($field, $value, $params) {
        if (!empty($value)) {
            // Check if numeric
            if (!is_numeric($value)) {
                self::$errors[$field][] = "Field {$field} must be a valid amount";
                return;
            }
            
            $amount = floatval($value);
            $min_amount = $params['min'] ?? 0;
            $max_amount = $params['max'] ?? 999999999;
            
            if ($amount < $min_amount) {
                self::$errors[$field][] = "Field {$field} must be at least {$min_amount}";
            }
            
            if ($amount > $max_amount) {
                self::$errors[$field][] = "Field {$field} must not exceed {$max_amount}";
            }
        }
    }
    
    /**
     * Validate date
     */
    private static function validateDate($field, $value, $params) {
        if (!empty($value)) {
            $date_format = $params['format'] ?? 'Y-m-d';
            $date = DateTime::createFromFormat($date_format, $value);
            
            if (!$date) {
                self::$errors[$field][] = "Field {$field} must be a valid date ({$date_format})";
            }
        }
    }
    
    /**
     * Sanitize input data
     */
    private static function sanitize($value, $rules) {
        if (empty($value)) {
            return $value;
        }
        
        // Trim whitespace
        $sanitized = trim($value);
        
        // Apply specific sanitization based on field type
        if (isset($rules['email'])) {
            $sanitized = filter_var($sanitized, FILTER_SANITIZE_EMAIL);
        } elseif (isset($rules['phone'])) {
            $sanitized = preg_replace('/[^0-9]/', '', $sanitized);
        } elseif (isset($rules['numeric'])) {
            $sanitized = filter_var($sanitized, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        } elseif (isset($rules['alphanumeric'])) {
            $sanitized = preg_replace('/[^a-zA-Z0-9]/', '', $sanitized);
        } else {
            // Default sanitization
            $sanitized = filter_var($sanitized, FILTER_SANITIZE_STRING);
            $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
        }
        
        return $sanitized;
    }
    
    /**
     * Validate API request data
     */
    public static function validateAPIRequest($request_data, $endpoint_rules = []) {
        $validation_rules = [
            'user_id' => ['required', 'numeric'],
            'email' => ['email', 'max_length' => ['max' => 255]],
            'phone' => ['phone'],
            'name' => ['required', 'safe_string', 'min_length' => ['min' => 2], 'max_length' => ['max' => 100]],
            'amount' => ['required', 'amount', 'min' => ['min' => 0], 'max' => ['max' => 999999999]],
            'description' => ['safe_string', 'max_length' => ['max' => 500]],
            'date' => ['date', 'format' => ['format' => 'Y-m-d']],
            'status' => ['safe_string', 'max_length' => ['max' => 50]]
        ];
        
        // Merge with endpoint-specific rules
        $rules = array_merge($validation_rules, $endpoint_rules);
        
        return self::validate($request_data, $rules);
    }
    
    /**
     * Validate user registration data
     */
    public static function validateUserRegistration($data) {
        $rules = [
            'name' => ['required', 'safe_string', 'min_length' => ['min' => 2], 'max_length' => ['max' => 100]],
            'email' => ['required', 'email', 'max_length' => ['max' => 255]],
            'phone' => ['required', 'phone'],
            'password' => ['required', 'min_length' => ['min' => 8], 'max_length' => ['max' => 255]],
            'role' => ['required', 'safe_string', 'max_length' => ['max' => 20]]
        ];
        
        return self::validate($data, $rules);
    }
    
    /**
     * Validate loan application data
     */
    public static function validateLoanApplication($data) {
        $rules = [
            'user_id' => ['required', 'numeric'],
            'loan_type' => ['required', 'safe_string', 'max_length' => ['max' => 50]],
            'amount' => ['required', 'amount', 'min' => ['min' => 100000], 'max' => ['max' => 50000000]],
            'duration_months' => ['required', 'numeric', 'min' => ['min' => 1], 'max' => ['max' => 360]],
            'purpose' => ['required', 'safe_string', 'max_length' => ['max' => 500]],
            'income' => ['required', 'amount', 'min' => ['min' => 0]],
            'employment_status' => ['required', 'safe_string', 'max_length' => ['max' => 50]]
        ];
        
        return self::validate($data, $rules);
    }
    
    /**
     * Validate transaction data
     */
    public static function validateTransaction($data) {
        $rules = [
            'user_id' => ['required', 'numeric'],
            'type' => ['required', 'safe_string', 'max_length' => ['max' => 20]],
            'amount' => ['required', 'amount', 'min' => ['min' => 1000], 'max' => ['max' => 10000000]],
            'description' => ['safe_string', 'max_length' => ['max' => 500]],
            'recipient_id' => ['numeric'] // For transfers
        ];
        
        return self::validate($data, $rules);
    }
    
    /**
     * Get validation errors
     */
    public static function getErrors() {
        return self::$errors;
    }
    
    /**
     * Get sanitized data
     */
    public static function getSanitizedData() {
        return self::$sanitized_data;
    }
    
    /**
     * Clear validation state
     */
    public static function clear() {
        self::$errors = [];
        self::$sanitized_data = [];
    }
}
?>