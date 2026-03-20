<?php
/**
 * Input Validation Class for API Security
 * KSP Lam Gabe Jaya v2.0
 */

class InputValidator {
    
    /**
     * Validate and sanitize input data
     */
    public static function validate($data, $type = 'string', $options = []) {
        switch ($type) {
            case 'int':
                return self::validateInteger($data, $options);
            case 'float':
                return self::validateFloat($data, $options);
            case 'email':
                return self::validateEmail($data);
            case 'string':
                return self::validateString($data, $options);
            case 'alpha':
                return self::validateAlpha($data);
            case 'alphanum':
                return self::validateAlphanum($data);
            case 'phone':
                return self::validatePhone($data);
            case 'date':
                return self::validateDate($data);
            case 'enum':
                return self::validateEnum($data, $options['allowed'] ?? []);
            default:
                return self::validateString($data, $options);
        }
    }
    
    /**
     * Validate integer
     */
    private static function validateInteger($data, $options = []) {
        $value = filter_var($data, FILTER_VALIDATE_INT);
        
        if ($value === false) return false;
        
        $min = $options['min'] ?? null;
        $max = $options['max'] ?? null;
        
        if ($min !== null && $value < $min) return false;
        if ($max !== null && $value > $max) return false;
        
        return $value;
    }
    
    /**
     * Validate float
     */
    private static function validateFloat($data, $options = []) {
        $value = filter_var($data, FILTER_VALIDATE_FLOAT);
        
        if ($value === false) return false;
        
        $min = $options['min'] ?? null;
        $max = $options['max'] ?? null;
        
        if ($min !== null && $value < $min) return false;
        if ($max !== null && $value > $max) return false;
        
        return $value;
    }
    
    /**
     * Validate email
     */
    private static function validateEmail($data) {
        return filter_var(trim($data), FILTER_VALIDATE_EMAIL);
    }
    
    /**
     * Validate string
     */
    private static function validateString($data, $options = []) {
        $value = trim($data);
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        
        $minLength = $options['min_length'] ?? null;
        $maxLength = $options['max_length'] ?? null;
        
        if ($minLength !== null && strlen($value) < $minLength) return false;
        if ($maxLength !== null && strlen($value) > $maxLength) return false;
        
        return $value;
    }
    
    /**
     * Validate alphabetic string
     */
    private static function validateAlpha($data) {
        return preg_match('/^[a-zA-Z]+$/', trim($data)) ? trim($data) : false;
    }
    
    /**
     * Validate alphanumeric string
     */
    private static function validateAlphanum($data) {
        return preg_match('/^[a-zA-Z0-9]+$/', trim($data)) ? trim($data) : false;
    }
    
    /**
     * Validate phone number
     */
    private static function validatePhone($data) {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $data);
        
        // Check if it's a valid Indonesian phone number
        if (preg_match('/^(08[0-9]{8,12}|628[0-9]{8,12})$/', $phone)) {
            return $phone;
        }
        
        return false;
    }
    
    /**
     * Validate date
     */
    private static function validateDate($data) {
        $date = trim($data);
        $formats = ['Y-m-d', 'd-m-Y', 'Y/m/d', 'd/m/Y'];
        
        foreach ($formats as $format) {
            $dateObj = DateTime::createFromFormat($format, $date);
            if ($dateObj && $dateObj->format($format) === $date) {
                return $dateObj->format('Y-m-d');
            }
        }
        
        return false;
    }
    
    /**
     * Validate enum values
     */
    private static function validateEnum($data, $allowed = []) {
        $value = trim($data);
        return in_array($value, $allowed) ? $value : false;
    }
    
    /**
     * Validate request method
     */
    public static function validateRequestMethod($allowedMethods = ['GET', 'POST']) {
        $method = $_SERVER['REQUEST_METHOD'];
        return in_array($method, $allowedMethods);
    }
    
    /**
     * Sanitize array of inputs
     */
    public static function sanitizeArray($data, $rules = []) {
        $sanitized = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if ($value === null) {
                $sanitized[$field] = $rule['default'] ?? null;
                continue;
            }
            
            $type = $rule['type'] ?? 'string';
            $options = $rule['options'] ?? [];
            $required = $rule['required'] ?? false;
            
            $validated = self::validate($value, $type, $options);
            
            if ($validated === false && $required) {
                throw new InvalidArgumentException("Invalid value for field: $field");
            }
            
            $sanitized[$field] = $validated;
        }
        
        return $sanitized;
    }
    
    /**
     * Check for SQL injection patterns
     */
    public static function detectSqlInjection($input) {
        $patterns = [
            '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\b)/i',
            '/(\b(OR|AND)\s+\d+\s*=\s*\d+)/i',
            '/(\-\-|\#|\/\*|\*\/)/',
            '/(\b(UNION|SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|SCRIPT)\s)/i',
            '/(\'\s*(OR|AND)\s*\d+\s*=\s*\d+)/i',
            '/(\'\s*(OR|AND)\s*\'\w+\'\s*=\s*\'\w+\')/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Validate file upload
     */
    public static function validateFile($file, $options = []) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        $allowedTypes = $options['allowed_types'] ?? [];
        $maxSize = $options['max_size'] ?? 5 * 1024 * 1024; // 5MB default
        
        // Check file size
        if ($file['size'] > $maxSize) {
            return false;
        }
        
        // Check file type
        if (!empty($allowedTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                return false;
            }
        }
        
        return true;
    }
}
