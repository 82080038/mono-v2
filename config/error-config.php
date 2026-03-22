<?php
/**
 * KSP Lam Gabe Jaya - Error Configuration
 * Centralized error handling and logging system
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__ . '/..');
}

// ========================================
// ERROR HANDLING CONFIGURATION
// ========================================

// Error reporting levels
define('ERROR_REPORTING_ALL', E_ALL);
define('ERROR_REPORTING_PRODUCTION', 0);
define('ERROR_REPORTING_DEVELOPMENT', E_ALL);

// Error display settings
define('DISPLAY_ERRORS_DEVELOPMENT', true);
define('DISPLAY_ERRORS_PRODUCTION', false);
define('DISPLAY_ERRORS_STAGING', false);

// Error logging settings
define('LOG_ERRORS_ENABLED', true);
define('LOG_ERRORS_PATH', APP_ROOT . '/logs');
define('LOG_ERRORS_FILE', 'error.log');
define('LOG_ERRORS_MAX_SIZE', 10485760); // 10MB
define('LOG_ERRORS_FILES', 5);

// Exception handling
define('EXCEPTION_HANDLING_ENABLED', true);
define('EXCEPTION_EMAIL_ENABLED', false);
define('EXCEPTION_EMAIL_RECIPIENT', 'admin@ksplamgabejaya.co.id');

// ========================================
// ERROR HANDLING CLASS
// ========================================

class ErrorHandler {
    private static $instance = null;
    private $logPath;
    private $logFile;
    private $errorTypes = [
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
        E_STRICT => 'Strict Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated'
    ];
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->logPath = LOG_ERRORS_PATH;
        $this->logFile = $this->logPath . '/' . LOG_ERRORS_FILE;
        
        // Create log directory if it doesn't exist
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
        
        // Set error handlers
        if (EXCEPTION_HANDLING_ENABLED) {
            set_error_handler([$this, 'handleError']);
            set_exception_handler([$this, 'handleException']);
            register_shutdown_function([$this, 'handleShutdown']);
        }
    }
    
    /**
     * Handle PHP errors
     */
    public function handleError($errno, $errstr, $errfile, $errline) {
        // Don't handle errors suppressed with @
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        $errorType = $this->errorTypes[$errno] ?? 'Unknown Error';
        $message = sprintf(
            "[%s] %s: %s in %s on line %d\n",
            date('Y-m-d H:i:s'),
            $errorType,
            $errstr,
            $errfile,
            $errline
        );
        
        // Log error
        $this->logError($message);
        
        // Display error if enabled
        if (defined('DISPLAY_ERRORS_DEVELOPMENT') && DISPLAY_ERRORS_DEVELOPMENT) {
            $this->displayError($errno, $errstr, $errfile, $errline);
        }
        
        // Don't execute PHP internal error handler
        return true;
    }
    
    /**
     * Handle uncaught exceptions
     */
    public function handleException($exception) {
        $message = sprintf(
            "[%s] Uncaught Exception: %s in %s on line %d\nStack trace:\n%s\n",
            date('Y-m-d H:i:s'),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        
        // Log exception
        $this->logError($message);
        
        // Send email if enabled
        if (EXCEPTION_EMAIL_ENABLED && defined('EXCEPTION_EMAIL_RECIPIENT')) {
            $this->sendExceptionEmail($exception);
        }
        
        // Display error page
        $this->displayException($exception);
    }
    
    /**
     * Handle fatal errors on shutdown
     */
    public function handleShutdown() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $message = sprintf(
                "[%s] Fatal Error: %s in %s on line %d\n",
                date('Y-m-d H:i:s'),
                $error['message'],
                $error['file'],
                $error['line']
            );
            
            $this->logError($message);
            
            if (defined('DISPLAY_ERRORS_DEVELOPMENT') && DISPLAY_ERRORS_DEVELOPMENT) {
                $this->displayFatalError($error);
            }
        }
    }
    
    /**
     * Log error to file
     */
    private function logError($message) {
        if (!LOG_ERRORS_ENABLED) {
            return;
        }
        
        // Rotate log if too large
        if (file_exists($this->logFile) && filesize($this->logFile) > LOG_ERRORS_MAX_SIZE) {
            $this->rotateLog();
        }
        
        // Write to log file
        file_put_contents($this->logFile, $message, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Rotate log files
     */
    private function rotateLog() {
        // Move existing logs
        for ($i = LOG_ERRORS_FILES - 1; $i > 0; $i--) {
            $oldFile = $this->logFile . '.' . $i;
            $newFile = $this->logFile . '.' . ($i + 1);
            
            if (file_exists($oldFile)) {
                if ($i == LOG_ERRORS_FILES - 1) {
                    unlink($oldFile); // Delete oldest
                } else {
                    rename($oldFile, $newFile);
                }
            }
        }
        
        // Move current log
        rename($this->logFile, $this->logFile . '.1');
    }
    
    /**
     * Display error (development only)
     */
    private function displayError($errno, $errstr, $errfile, $errline) {
        $errorType = $this->errorTypes[$errno] ?? 'Unknown Error';
        
        echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 10px; border-radius: 4px;">';
        echo '<strong>' . htmlspecialchars($errorType) . '</strong>: ' . htmlspecialchars($errstr) . '<br>';
        echo 'File: ' . htmlspecialchars($errfile) . '<br>';
        echo 'Line: ' . htmlspecialchars($errline);
        echo '</div>';
    }
    
    /**
     * Display exception (development only)
     */
    private function displayException($exception) {
        if (defined('DISPLAY_ERRORS_DEVELOPMENT') && DISPLAY_ERRORS_DEVELOPMENT) {
            echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 10px; border-radius: 4px;">';
            echo '<strong>Uncaught Exception:</strong> ' . htmlspecialchars($exception->getMessage()) . '<br>';
            echo 'File: ' . htmlspecialchars($exception->getFile()) . '<br>';
            echo 'Line: ' . htmlspecialchars($exception->getLine()) . '<br>';
            echo '<strong>Stack Trace:</strong><br>';
            echo '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
            echo '</div>';
        } else {
            // Production error page
            $this->showErrorPage('Terjadi kesalahan pada sistem. Silakan coba lagi nanti.');
        }
    }
    
    /**
     * Display fatal error (development only)
     */
    private function displayFatalError($error) {
        if (defined('DISPLAY_ERRORS_DEVELOPMENT') && DISPLAY_ERRORS_DEVELOPMENT) {
            echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 10px; border-radius: 4px;">';
            echo '<strong>Fatal Error:</strong> ' . htmlspecialchars($error['message']) . '<br>';
            echo 'File: ' . htmlspecialchars($error['file']) . '<br>';
            echo 'Line: ' . htmlspecialchars($error['line']);
            echo '</div>';
        } else {
            $this->showErrorPage('Sistem sedang dalam perbaikan. Silakan coba lagi nanti.');
        }
    }
    
    /**
     * Show generic error page
     */
    private function showErrorPage($message) {
        // Check if we're in an AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $message
            ]);
        } else {
            // Show HTML error page
            echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h4>Terjadi Kesalahan</h4>
                    </div>
                    <div class="card-body">
                        <p>' . htmlspecialchars($message) . '</p>
                        <div class="mt-3">
                            <a href="/" class="btn btn-primary">Kembali ke Beranda</a>
                            <button onclick="history.back()" class="btn btn-secondary">Kembali</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';
        }
        exit;
    }
    
    /**
     * Send exception email
     */
    private function sendExceptionEmail($exception) {
        if (!defined('EXCEPTION_EMAIL_RECIPIENT') || !EXCEPTION_EMAIL_RECIPIENT) {
            return;
        }
        
        $subject = 'Exception Alert - ' . APP_NAME;
        $message = sprintf(
            "Exception occurred:\n\nMessage: %s\nFile: %s\nLine: %d\n\nStack Trace:\n%s\n\nTime: %s\nIP: %s\nURL: %s",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString(),
            date('Y-m-d H:i:s'),
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            $_SERVER['REQUEST_URI'] ?? 'Unknown'
        );
        
        $headers = [
            'From: noreply@ksplamgabejaya.co.id',
            'Content-Type: text/plain; charset=UTF-8'
        ];
        
        mail(EXCEPTION_EMAIL_RECIPIENT, $subject, $message, implode("\r\n", $headers));
    }
    
    /**
     * Get recent errors
     */
    public function getRecentErrors($limit = 50) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $errors = array_reverse(array_slice($lines, -$limit));
        
        $recentErrors = [];
        foreach ($errors as $line) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (.+)/', $line, $matches)) {
                $recentErrors[] = [
                    'timestamp' => $matches[1],
                    'message' => $matches[2]
                ];
            }
        }
        
        return $recentErrors;
    }
    
    /**
     * Clear error log
     */
    public function clearErrorLog() {
        if (file_exists($this->logFile)) {
            return unlink($this->logFile);
        }
        return true;
    }
    
    /**
     * Get error statistics
     */
    public function getErrorStats() {
        if (!file_exists($this->logFile)) {
            return [
                'total_errors' => 0,
                'file_size' => 0,
                'last_error' => null
            ];
        }
        
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lastError = null;
        
        if (!empty($lines)) {
            $lastLine = end($lines);
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $lastLine, $matches)) {
                $lastError = $matches[1];
            }
        }
        
        return [
            'total_errors' => count($lines),
            'file_size' => filesize($this->logFile),
            'last_error' => $lastError
        ];
    }
}

// ========================================
// CUSTOM EXCEPTION CLASSES
// ========================================

class KSPException extends Exception {
    protected $context;
    
    public function __construct($message, $code = 0, $previous = null, $context = []) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }
    
    public function getContext() {
        return $this->context;
    }
}

class AuthenticationException extends KSPException {
    public function __construct($message, $context = []) {
        parent::__construct($message, 401, null, $context);
    }
}

class AuthorizationException extends KSPException {
    public function __construct($message, $context = []) {
        parent::__construct($message, 403, null, $context);
    }
}

class ValidationException extends KSPException {
    protected $errors;
    
    public function __construct($message, $errors = [], $context = []) {
        parent::__construct($message, 400, null, $context);
        $this->errors = $errors;
    }
    
    public function getErrors() {
        return $this->errors;
    }
}

class DatabaseException extends KSPException {
    public function __construct($message, $context = []) {
        parent::__construct($message, 500, null, $context);
    }
}

class FileNotFoundException extends KSPException {
    public function __construct($filename, $context = []) {
        parent::__construct("File not found: {$filename}", 404, null, $context);
    }
}

class RateLimitException extends KSPException {
    protected $retryAfter;
    
    public function __construct($message, $retryAfter = 0, $context = []) {
        parent::__construct($message, 429, null, $context);
        $this->retryAfter = $retryAfter;
    }
    
    public function getRetryAfter() {
        return $this->retryAfter;
    }
}

// ========================================
// ERROR HANDLING INITIALIZATION
// ========================================

// Initialize error handler
if (defined('EXCEPTION_HANDLING_ENABLED') && EXCEPTION_HANDLING_ENABLED) {
    ErrorHandler::getInstance();
}

// ========================================
// HELPER FUNCTIONS
// ========================================

/**
 * Log custom message
 */
function logMessage($message, $level = 'INFO') {
    $errorHandler = ErrorHandler::getInstance();
    $logMessage = sprintf(
        "[%s] %s: %s\n",
        date('Y-m-d H:i:s'),
        strtoupper($level),
        $message
    );
    
    $reflection = new ReflectionClass($errorHandler);
    $method = $reflection->getMethod('logError');
    $method->setAccessible(true);
    $method->invoke($errorHandler, $logMessage);
}

/**
 * Log debug message
 */
function logDebug($message) {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        logMessage($message, 'DEBUG');
    }
}

/**
 * Log info message
 */
function logInfo($message) {
    logMessage($message, 'INFO');
}

/**
 * Log warning message
 */
function logWarning($message) {
    logMessage($message, 'WARNING');
}

/**
 * Log error message
 */
function logError($message) {
    logMessage($message, 'ERROR');
}

/**
 * Handle fatal error gracefully
 */
function handleFatalError($message) {
    $errorHandler = ErrorHandler::getInstance();
    $reflection = new ReflectionClass($errorHandler);
    $method = $reflection->getMethod('showErrorPage');
    $method->setAccessible(true);
    $method->invoke($errorHandler, $message);
}

// ========================================
// END OF ERROR CONFIGURATION
// ========================================

?>
