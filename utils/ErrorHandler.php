<?php
/**
 * Error Handling System
 */
class ErrorHandler {
    public static function handleException($exception) {
        error_log("Exception: " . $exception->getMessage());
        self::logError($exception->getMessage(), $exception->getFile(), $exception->getLine());
        
        if (ini_get('display_errors')) {
            echo "Error: " . $exception->getMessage();
        } else {
            echo "Something went wrong. Please try again later.";
        }
    }
    
    public static function logError($message, $file = '', $line = 0) {
        $log_entry = date('Y-m-d H:i:s') . " - " . $message;
        if ($file) $log_entry .= " in " . $file;
        if ($line) $log_entry .= " on line " . $line;
        error_log($log_entry . "\n", 3, '/var/www/html/mono/logs/error.log');
    }
}
?>