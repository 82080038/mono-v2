<?php
/**
 * XSS Protection
 */
class XSSProtection {
    public static function clean($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::clean($value);
            }
        } else {
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            $data = strip_tags($data);
        }
        return $data;
    }
    
    public static function protectHeaders() {
        header("X-XSS-Protection: 1; mode=block");
        header("X-Content-Type-Options: nosniff");
    }
}
?>