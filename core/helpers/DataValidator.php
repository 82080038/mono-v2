<?php
/**
 * Data Validation and Null Handling Helper
 * Comprehensive null data prevention and handling
 */

class DataValidator {
    
    /**
     * Validate and sanitize data, replace null with default values
     */
    public static function validateData($data, $defaults = []) {
        if (!is_array($data)) {
            return $data;
        }
        
        foreach ($data as $key => $value) {
            // Handle null values
            if ($value === null) {
                $data[$key] = $defaults[$key] ?? self::getDefaultValue($key);
            }
            // Handle empty strings
            elseif ($value === '') {
                $data[$key] = $defaults[$key] ?? self::getDefaultValue($key);
            }
            // Recursively handle nested arrays
            elseif (is_array($value)) {
                $data[$key] = self::validateData($value, $defaults[$key] ?? []);
            }
        }
        
        return $data;
    }
    
    /**
     * Get default value for common fields
     */
    private static function getDefaultValue($field) {
        $defaults = [
            // Numeric fields
            'id' => 0,
            'user_id' => 0,
            'member_id' => 0,
            'loan_id' => 0,
            'amount' => 0,
            'balance' => 0,
            'interest_rate' => 0,
            'limit' => 50,
            'offset' => 0,
            'count' => 0,
            'total' => 0,
            
            // String fields
            'name' => 'Unknown',
            'email' => 'unknown@example.com',
            'phone' => '0000000000',
            'address' => 'Address not provided',
            'description' => 'No description',
            'notes' => 'No notes',
            'status' => 'active',
            'type' => 'general',
            'category' => 'general',
            'action' => 'unknown',
            'account_number' => '0000000000',
            'account_type' => 'savings',
            
            // Date fields
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'last_login' => date('Y-m-d H:i:s'),
            'date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            
            // Boolean fields
            'is_active' => true,
            'deleted_at' => null,
            'verified' => false,
            'approved' => false,
            
            // Array fields
            'data' => [],
            'errors' => [],
            'documents' => [],
            'features' => [],
            
            // JSON fields
            'old_values' => '{}',
            'new_values' => '{}',
            'metadata' => '{}',
            'settings' => '{}'
        ];
        
        return $defaults[$field] ?? null;
    }
    
    /**
     * Ensure array has minimum structure
     */
    public static function ensureArrayStructure($array, $requiredFields = []) {
        if (!is_array($array)) {
            return [];
        }
        
        $result = [];
        foreach ($requiredFields as $field) {
            $result[$field] = $array[$field] ?? self::getDefaultValue($field);
        }
        
        // Add any additional fields from original array
        foreach ($array as $key => $value) {
            if (!isset($result[$key])) {
                $result[$key] = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * Validate API response structure
     */
    public static function validateApiResponse($response, $expectedStructure = []) {
        $defaults = [
            'success' => false,
            'data' => [],
            'message' => 'No message',
            'errors' => [],
            'count' => 0,
            'pagination' => [
                'total' => 0,
                'limit' => 50,
                'offset' => 0,
                'has_more' => false
            ]
        ];
        
        return self::ensureArrayStructure($response, array_keys($defaults));
    }
    
    /**
     * Handle empty database results
     */
    public static function handleEmptyResult($endpoint, $params = []) {
        $mockData = self::getMockData($endpoint, $params);
        
        return [
            'success' => true,
            'data' => $mockData,
            'count' => count($mockData),
            'message' => "Mock data returned for {$endpoint}",
            'is_mock' => true
        ];
    }
    
    /**
     * Get mock data for empty results
     */
    private static function getMockData($endpoint, $params = []) {
        $mockData = [
            'users' => [
                [
                    'id' => 1,
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'is_active' => true,
                    'last_login' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ],
            'members' => [
                [
                    'id' => 1,
                    'name' => 'Test Member',
                    'email' => 'member@example.com',
                    'phone' => '08123456789',
                    'address' => 'Test Address',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ],
            'loans' => [
                [
                    'id' => 1,
                    'member_id' => 1,
                    'amount' => 10000000,
                    'interest_rate' => 12,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ],
            'savings' => [
                [
                    'id' => 1,
                    'member_id' => 1,
                    'account_number' => 'SA001',
                    'balance' => 5000000,
                    'account_type' => 'savings',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ],
            'accounts' => [
                [
                    'id' => 1,
                    'user_id' => $params['user_id'] ?? 1,
                    'account_number' => 'ACC001',
                    'balance' => 1000000,
                    'account_type' => 'savings',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ],
            'transactions' => [
                [
                    'id' => 1,
                    'user_id' => $params['user_id'] ?? 1,
                    'type' => 'deposit',
                    'amount' => 1000000,
                    'description' => 'Initial deposit',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ],
            'reports' => [
                [
                    'id' => 1,
                    'type' => 'monthly',
                    'title' => 'Monthly Report',
                    'data' => '{"total": 1000000}',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ],
            'audit_logs' => [
                [
                    'id' => 1,
                    'user_id' => 1,
                    'user_name' => 'System',
                    'action' => 'SYSTEM_CHECK',
                    'table_name' => 'system',
                    'record_id' => 0,
                    'old_values' => null,
                    'new_values' => '{"status": "checked"}',
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'System',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]
        ];
        
        return $mockData[$endpoint] ?? [];
    }
    
    /**
     * Validate input parameters
     */
    public static function validateInput($input, $rules = []) {
        $errors = [];
        $validated = [];
        
        foreach ($rules as $field => $rule) {
            $value = $input[$field] ?? null;
            
            // Required validation
            if (isset($rule['required']) && $rule['required'] && ($value === null || $value === '')) {
                $errors[$field] = "{$field} is required";
                continue;
            }
            
            // Type validation
            if ($value !== null && isset($rule['type'])) {
                switch ($rule['type']) {
                    case 'int':
                        if (!is_numeric($value)) {
                            $errors[$field] = "{$field} must be a number";
                            continue;
                        }
                        $validated[$field] = (int)$value;
                        break;
                    case 'float':
                        if (!is_numeric($value)) {
                            $errors[$field] = "{$field} must be a number";
                            continue;
                        }
                        $validated[$field] = (float)$value;
                        break;
                    case 'string':
                        $validated[$field] = (string)$value;
                        break;
                    case 'bool':
                        $validated[$field] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                        break;
                    case 'array':
                        $validated[$field] = is_array($value) ? $value : [];
                        break;
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field] = "{$field} must be a valid email";
                            continue;
                        }
                        $validated[$field] = $value;
                        break;
                }
            } else {
                $validated[$field] = $value;
            }
            
            // Min/Max validation
            if (isset($rule['min']) && $validated[$field] < $rule['min']) {
                $errors[$field] = "{$field} must be at least {$rule['min']}";
            }
            
            if (isset($rule['max']) && $validated[$field] > $rule['max']) {
                $errors[$field] = "{$field} must be at most {$rule['max']}";
            }
        }
        
        return [
            'success' => empty($errors),
            'data' => $validated,
            'errors' => $errors
        ];
    }
    
    /**
     * Sanitize output data
     */
    public static function sanitizeOutput($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitizeOutput($value);
            }
        } elseif (is_string($data)) {
            // Remove any potential XSS
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            // Remove any null bytes
            $data = str_replace("\0", '', $data);
        }
        
        return $data;
    }
    
    /**
     * Check if data is empty or null
     */
    public static function isEmpty($data) {
        if ($data === null || $data === '') {
            return true;
        }
        
        if (is_array($data)) {
            return empty($data);
        }
        
        return false;
    }
    
    /**
     * Get safe value from array
     */
    public static function getValue($array, $key, $default = null) {
        return isset($array[$key]) && !self::isEmpty($array[$key]) ? $array[$key] : $default;
    }
    
    /**
     * Validate date format
     */
    public static function validateDate($date, $format = 'Y-m-d H:i:s') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Format currency safely
     */
    public static function formatCurrency($amount, $currency = 'IDR') {
        if (!is_numeric($amount)) {
            $amount = 0;
        }
        
        return number_format($amount, 2, '.', ',');
    }
    
    /**
     * Validate phone number
     */
    public static function validatePhone($phone) {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it's a valid Indonesian phone number
        return (strlen($phone) >= 10 && strlen($phone) <= 13) && 
               (substr($phone, 0, 2) === '08' || substr($phone, 0, 3) === '+62');
    }
    
    /**
     * Generate safe ID
     */
    public static function generateId($prefix = '') {
        return $prefix . date('YmdHis') . '_' . rand(1000, 9999);
    }
}

?>
