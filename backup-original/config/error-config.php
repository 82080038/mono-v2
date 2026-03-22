<?php
/**
 * Error Reporting Configuration for API
 * Only enable error reporting for CLI, not for web requests
 */

declare(strict_types=1);

// Check if running from CLI
if (php_sapi_name() === 'cli') {
    
    // Enable all error reporting for CLI only
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
    
    // Set error handler for better error display
    set_error_handler(function($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return;
        }
        
        $error_types = [
            E_ERROR => 'Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated'
        ];
        
        $error_type = $error_types[$severity] ?? 'Unknown';
        
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 10px; border-radius: 4px;'>";
        echo "<strong>{$error_type}:</strong> {$message} in <strong>{$file}</strong> on line <strong>{$line}</strong>";
        echo "</div>";
        
        // Log to file
        error_log("[{$error_type}] {$message} in {$file} on line {$line}");
    });
    
    // Set exception handler
    set_exception_handler(function($exception) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 10px; border-radius: 4px;'>";
        echo "<strong>Fatal Error:</strong> " . $exception->getMessage() . " in <strong>" . $exception->getFile() . "</strong> on line <strong>" . $exception->getLine() . "</strong>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
        echo "</div>";
        
        error_log("Fatal Error: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    });
} else {
    // For web requests, only log errors but don't display them
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
}
?>
