<?php
/**
 * Input Validation System
 */
class InputValidator {
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public static function validateNumeric($value) {
        return is_numeric($value) && $value >= 0;
    }
    
    public static function validateRequired($data, $required_fields) {
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }
    
    public static function sanitize($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}
?>