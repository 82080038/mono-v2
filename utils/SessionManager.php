<?php
/**
 * Session Management
 */
class SessionManager {
    public static function secureSession() {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Strict');
        session_start();
    }
    
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    public static function destroy() {
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
    }
}
?>