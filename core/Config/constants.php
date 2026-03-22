<?php
/**
 * KSP Lam Gabe Jaya - Application Constants
 * Centralized configuration constants
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    die('Direct access not allowed');
}

// Prevent constant redefinition
if (defined('APP_NAME')) {
    return;
}

// Application constants
define('APP_NAME', 'KSP Lam Gabe Jaya');
define('APP_VERSION', '2.0.0');
define('APP_ENV', defined('APP_MODE') ? APP_MODE : 'production');
define('APP_DEBUG', APP_ENV === 'development');
define('APP_TIMEZONE', 'Asia/Jakarta');

// URL constants
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $host);
define('APP_URL', BASE_URL);
define('API_URL', BASE_URL . '/api');
define('ASSETS_URL', BASE_URL . '/public/assets');

// Path constants
define('PUBLIC_PATH', APP_ROOT . '/public');
define('APP_PATH', APP_ROOT . '/app');
define('CORE_PATH', APP_ROOT . '/core');
define('STORAGE_PATH', APP_ROOT . '/storage');
define('CONFIG_PATH', CORE_PATH . '/Config');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// Database constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'gabe');
define('DB_USER', 'root');
define('DB_PASSWORD', 'root');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');
define('DB_SOCKET', '/opt/lampp/var/mysql/mysql.sock');

// Session constants
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_PATH', STORAGE_PATH . '/sessions');
define('COOKIE_SECURE', false);
define('COOKIE_HTTPONLY', true);
define('COOKIE_SAMESITE', 'Strict');

// Cache constants
define('CACHE_DRIVER', 'file');
define('CACHE_PATH', STORAGE_PATH . '/cache');
define('CACHE_LIFETIME', 3600); // 1 hour

// Logging constants
define('LOG_DRIVER', 'file');
define('LOG_PATH', STORAGE_PATH . '/logs');
define('LOG_LEVEL', 'debug');
define('LOG_MAX_FILES', 30);

// Security constants
define('HASH_ALGO', PASSWORD_DEFAULT);
define('HASH_COST', 12);
define('TOKEN_LENGTH', 32);
define('CSRF_TOKEN_LENGTH', 32);

// Pagination constants
define('DEFAULT_PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);

// File upload constants
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Email constants
define('MAIL_DRIVER', 'smtp');
define('MAIL_HOST', 'localhost');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', '');
define('MAIL_PASSWORD', '');
define('MAIL_ENCRYPTION', 'tls');

// API constants
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // requests per hour
define('API_TOKEN_LIFETIME', 3600); // 1 hour

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_MANAGER', 'manager');
define('ROLE_STAFF', 'staff');
define('ROLE_MEMBER', 'member');

// Account types
define('ACCOUNT_SAVINGS', 'simpanan');
define('ACCOUNT_LOAN', 'pinjaman');

// Transaction types
define('TRANSACTION_CREDIT', 'credit');
define('TRANSACTION_DEBIT', 'debit');

// Loan statuses
define('LOAN_STATUS_PENDING', 'pending');
define('LOAN_STATUS_APPROVED', 'approved');
define('LOAN_STATUS_ACTIVE', 'active');
define('LOAN_STATUS_COMPLETED', 'completed');
define('LOAN_STATUS_DEFAULTED', 'defaulted');
define('LOAN_STATUS_REJECTED', 'rejected');

// Member statuses
define('MEMBER_STATUS_ACTIVE', 'active');
define('MEMBER_STATUS_INACTIVE', 'inactive');
define('MEMBER_STATUS_SUSPENDED', 'suspended');

// Account statuses
define('ACCOUNT_STATUS_ACTIVE', 'active');
define('ACCOUNT_STATUS_INACTIVE', 'inactive');
define('ACCOUNT_STATUS_CLOSED', 'closed');

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Set session cookie parameters only if headers not sent
if (!headers_sent()) {
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
        'secure' => COOKIE_SECURE,
        'httponly' => COOKIE_HTTPONLY,
        'samesite' => COOKIE_SAMESITE
    ]);
}

// Error reporting
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

// Set default charset
mb_internal_encoding('UTF-8');

// Set locale
setlocale(LC_ALL, 'id_ID.UTF-8', 'id_ID.UTF8', 'id_ID');
?>
