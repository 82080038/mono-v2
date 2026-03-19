<?php
/**
 * Security Hardening
 */
class SecurityHardening {
    public static function hardenHeaders() {
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
        header("X-Frame-Options: DENY");
        header("X-Content-Type-Options: nosniff");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
    }
    
    public static function validatePassword($password) {
        return strlen($password) >= 8 && 
               preg_match('/[A-Z]/', $password) && 
               preg_match('/[a-z]/', $password) && 
               preg_match('/[0-9]/', $password);
    }
    
    public static function generateSecureToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
}
?>