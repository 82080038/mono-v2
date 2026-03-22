<?php
/**
 * KSP Lam Gabe Jaya - Application Constants
 * Centralized configuration constants for the entire application
 */

// Prevent direct access (but allow from main application files and API)
if (!defined('IN_INDEX_PHP') && !defined('IN_LOGIN_PHP') && !defined('IN_MAIN_PHP') && !defined('ALLOW_DIRECT_ACCESS')) {
    die('Direct access not allowed');
}

// Prevent constant redefinition
if (defined('APP_NAME')) {
    return;
}

// ========================================
// APPLICATION CONSTANTS
// ========================================

// Application Info
define('APP_NAME', 'KSP Lam Gabe Jaya');
define('APP_VERSION', '4.0');
define('APP_DESCRIPTION', 'Koperasi Simpan Pinjam Digital Terpadu');
define('APP_AUTHOR', 'KSP Lam Gabe Jaya Development Team');
define('APP_COPYRIGHT', '© 2024 KSP Lam Gabe Jaya. All rights reserved.');

// Application Paths
define('APP_ROOT', __DIR__ . '/..');
define('APP_CONFIG', __DIR__);
define('APP_ASSETS', APP_ROOT . '/assets');
define('APP_PAGES', APP_ROOT . '/pages');
define('APP_API', APP_ROOT . '/api');
define('APP_UPLOADS', APP_ROOT . '/uploads');
define('APP_BACKUPS', APP_ROOT . '/backups');
define('APP_LOGS', APP_ROOT . '/logs');
define('APP_CACHE', APP_ROOT . '/cache');
define('APP_TEMP', APP_ROOT . '/temp');

// URL Constants
define('BASE_URL', (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);
define('APP_URL', BASE_URL . str_replace('/index.php', '', $_SERVER['PHP_SELF']));
define('ASSETS_URL', APP_URL . '/assets');
define('API_URL', APP_URL . '/api');
define('UPLOADS_URL', APP_URL . '/uploads');

// ========================================
// DATABASE CONSTANTS
// ========================================

// Primary Database (Koperasi)
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'gabe');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_PASSWORD', 'root');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// Multi-Database Configuration
define('DB_ORANG_HOST', 'localhost');
define('DB_ORANG_NAME', 'orang');
define('DB_ORANG_USER', 'root');
define('DB_ORANG_PASSWORD', 'root');

define('DB_ALAMAT_HOST', 'localhost');
define('DB_ALAMAT_NAME', 'alamat_db');
define('DB_ALAMAT_USER', 'root');
define('DB_ALAMAT_PASSWORD', 'root');

// Database Connection Settings
define('DB_PERSISTENT', true);
define('DB_EMULATE_PREPARES', false);
define('DB_FETCH_MODE', PDO::FETCH_ASSOC);
define('DB_ERROR_MODE', PDO::ERRMODE_EXCEPTION);

// ========================================
// AUTHENTICATION CONSTANTS
// ========================================

// JWT Settings
define('JWT_ALGORITHM', 'HS256');
define('JWT_SECRET_KEY', 'ksp-lamgabejaya-secret-key-2024');
define('JWT_EXPIRY', 3600); // 1 hour in seconds
define('JWT_REFRESH_EXPIRY', 604800); // 7 days in seconds

// Session Settings
define('SESSION_NAME', 'KSP_SESSION');
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_PATH', '/');
define('SESSION_DOMAIN', '');
define('SESSION_SECURE', false); // Set to true for HTTPS
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Lax');

// Password Settings
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_MAX_LENGTH', 128);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBER', true);
define('PASSWORD_REQUIRE_SPECIAL', false);

// Rate Limiting
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes in seconds
define('RATE_LIMIT_WINDOW', 3600); // 1 hour

// ========================================
// USER ROLES & PERMISSIONS
// ========================================

// User Roles (Hierarchy: 0=highest, 4=lowest)
define('ROLE_BOS', 0);           // Pemilik Koperasi
define('ROLE_ADMIN', 1);         // Administrator Sistem
define('ROLE_TELLER', 2);        // Petugas Kasir
define('ROLE_FIELD_COLLECTOR', 3); // Petugas Lapangan/Kutipan
define('ROLE_NASABAH', 4);       // Anggota/Nasabah

// Role Names
define('ROLE_NAMES', [
    ROLE_BOS => 'Bos',
    ROLE_ADMIN => 'Admin',
    ROLE_TELLER => 'Teller',
    ROLE_FIELD_COLLECTOR => 'Petugas Lapangan',
    ROLE_NASABAH => 'Nasabah'
]);

// Permission Levels
define('PERMISSION_READ', 'read');
define('PERMISSION_WRITE', 'write');
define('PERMISSION_ADMIN', 'admin');

// ========================================
// BUSINESS CONSTANTS
// ========================================

// Loan Settings
define('LOAN_MIN_AMOUNT', 100000);
define('LOAN_MAX_AMOUNT', 50000000);
define('LOAN_DEFAULT_INTEREST_RATE', 2.5); // Percentage per month
define('LOAN_MIN_TERM', 1); // Minimum term in months
define('LOAN_MAX_TERM', 36); // Maximum term in months
define('LOAN_LATE_FEE_RATE', 0.5); // Percentage per month

// Savings Settings
define('SAVINGS_MIN_DEPOSIT', 10000);
define('SAVINGS_MIN_WITHDRAWAL', 10000);
define('SAVINGS_DEFAULT_INTEREST_RATE', 0.5); // Percentage per month

// Transaction Settings
define('TRANSACTION_FEE_AMOUNT', 2500);
define('TRANSACTION_MIN_AMOUNT', 1000);
define('TRANSACTION_MAX_AMOUNT', 10000000);

// ========================================
// SYSTEM CONSTANTS
// ========================================

// File Upload Settings
define('UPLOAD_MAX_SIZE', 5242880); // 5MB in bytes
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);
define('UPLOAD_PATH_IMAGES', APP_UPLOADS . '/images');
define('UPLOAD_PATH_DOCUMENTS', APP_UPLOADS . '/documents');
define('UPLOAD_PATH_AVATARS', APP_UPLOADS . '/avatars');

// Pagination Settings
define('PAGINATION_DEFAULT_LIMIT', 20);
define('PAGINATION_MAX_LIMIT', 100);
define('PAGINATION_OFFSET', 0);

// Cache Settings
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour
define('CACHE_PATH', APP_CACHE);

// Backup Settings
define('BACKUP_ENABLED', true);
define('BACKUP_SCHEDULE', 'daily');
define('BACKUP_RETENTION_DAYS', 30);
define('BACKUP_PATH', APP_BACKUPS);

// Logging Settings
define('LOG_ENABLED', true);
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
define('LOG_PATH', APP_LOGS);
define('LOG_MAX_SIZE', 10485760); // 10MB
define('LOG_FILES', 5);

// ========================================
// PWA & MOBILE CONSTANTS
// ========================================

// PWA Settings
define('PWA_NAME', APP_NAME);
define('PWA_SHORT_NAME', 'KSP');
define('PWA_THEME_COLOR', '#007bff');
define('PWA_BACKGROUND_COLOR', '#ffffff');
define('PWA_DISPLAY', 'standalone');
define('PWA_ORIENTATION', 'portrait');

// Mobile Settings
define('MOBILE_BREAKPOINT', 768); // pixels
define('GPS_TRACKING_ENABLED', true);
define('OFFLINE_MODE_ENABLED', true);
define('PUSH_NOTIFICATIONS_ENABLED', true);

// ========================================
// EXTERNAL INTEGRATION CONSTANTS
// ========================================

// WhatsApp API
define('WHATSAPP_ENABLED', false);
define('WHATSAPP_API_URL', 'https://api.whatsapp.com/v1');
define('WHATSAPP_TOKEN', '');

// Email Settings
define('EMAIL_ENABLED', true);
define('EMAIL_FROM', 'noreply@ksplamgabejaya.co.id');
define('EMAIL_FROM_NAME', APP_NAME);
define('EMAIL_SMTP_HOST', 'localhost');
define('EMAIL_SMTP_PORT', 587);
define('EMAIL_SMTP_USERNAME', '');
define('EMAIL_SMTP_PASSWORD', '');
define('EMAIL_SMTP_ENCRYPTION', 'tls');

// SMS Gateway
define('SMS_ENABLED', false);
define('SMS_GATEWAY_URL', '');
define('SMS_API_KEY', '');
define('SMS_SENDER', 'KSP');

// Payment Gateway
define('PAYMENT_ENABLED', false);
define('PAYMENT_GATEWAY_URL', '');
define('PAYMENT_API_KEY', '');
define('PAYMENT_MERCHANT_ID', '');

// ========================================
// DEVELOPMENT & DEBUG CONSTANTS
// ========================================

// Environment
define('ENVIRONMENT', 'development'); // development, staging, production
define('DEBUG_MODE', true);
define('SHOW_ERRORS', true);
define('LOG_ERRORS', true);

// API Settings
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // requests per hour
define('API_TIMEOUT', 30); // seconds

// Development Settings
define('DEV_DEMO_MODE', true);
define('DEV_FAKE_DATA', true);
define('DEV_LOG_QUERIES', false);

// ========================================
// SECURITY CONSTANTS
// ========================================

// Security Headers
define('SECURITY_XSS_PROTECTION', '1; mode=block');
define('SECURITY_CONTENT_TYPE_OPTIONS', 'nosniff');
define('SECURITY_FRAME_OPTIONS', 'DENY');
define('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin');

// Encryption
define('ENCRYPTION_METHOD', 'AES-256-CBC');
define('ENCRYPTION_KEY', 'ksp-lamgabejaya-encryption-key-2024');
define('ENCRYPTION_IV', 'ksp-lamgabejaya-iv-2024');

// CSRF Protection
define('CSRF_ENABLED', true);
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour

// ========================================
// BUSINESS LOGIC CONSTANTS
// ========================================

// Member Status
define('MEMBER_STATUS_ACTIVE', 'active');
define('MEMBER_STATUS_INACTIVE', 'inactive');
define('MEMBER_STATUS_SUSPENDED', 'suspended');
define('MEMBER_STATUS_CLOSED', 'closed');

// Loan Status
define('LOAN_STATUS_PENDING', 'pending');
define('LOAN_STATUS_APPROVED', 'approved');
define('LOAN_STATUS_ACTIVE', 'active');
define('LOAN_STATUS_COMPLETED', 'completed');
define('LOAN_STATUS_DEFAULTED', 'defaulted');
define('LOAN_STATUS_REJECTED', 'rejected');

// Transaction Types
define('TRANSACTION_TYPE_DEPOSIT', 'deposit');
define('TRANSACTION_TYPE_WITHDRAWAL', 'withdrawal');
define('TRANSACTION_TYPE_LOAN_PAYMENT', 'loan_payment');
define('TRANSACTION_TYPE_LOAN_DISBURSEMENT', 'loan_disbursement');
define('TRANSACTION_TYPE_FEE', 'fee');
define('TRANSACTION_TYPE_TRANSFER', 'transfer');

// Guarantee Types
define('GUARANTEE_TYPE_PERSONAL', 'personal');
define('GUARANTEE_TYPE_COLLATERAL', 'collateral');
define('GUARANTEE_TYPE_INSURANCE', 'insurance');

// Risk Levels
define('RISK_LEVEL_LOW', 'low');
define('RISK_LEVEL_MEDIUM', 'medium');
define('RISK_LEVEL_HIGH', 'high');
define('RISK_LEVEL_VERY_HIGH', 'very_high');

// ========================================
// HELPER FUNCTIONS
// ========================================

/**
 * Get role name by level
 */
function getRoleName($roleLevel) {
    return ROLE_NAMES[$roleLevel] ?? 'Unknown';
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'IDR') {
    if ($currency === 'IDR') {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
    return number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

/**
 * Generate unique ID
 */
function generateUniqueId($prefix = '') {
    return $prefix . uniqid() . '-' . time();
}

/**
 * Check if environment is production
 */
function isProduction() {
    return ENVIRONMENT === 'production';
}

/**
 * Check if environment is development
 */
function isDevelopment() {
    return ENVIRONMENT === 'development';
}

/**
 * Get current timestamp
 */
function getCurrentTimestamp() {
    return date('Y-m-d H:i:s');
}

/**
 * Calculate age from birth date
 */
function calculateAge($birthDate) {
    $birthDate = new DateTime($birthDate);
    $today = new DateTime();
    return $birthDate->diff($today)->y;
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitize input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate random password
 */
function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($chars), 0, $length);
}

/**
 * Check if user has permission
 */
if (!function_exists('hasPermission')) {
    function hasPermission($userRole, $requiredRole) {
        // Role hierarchy: admin > manager > staff > member
        $roleHierarchy = [
            'admin' => 4,
            'manager' => 3,
            'staff' => 2,
            'member' => 1
        ];
        
        if (!isset($roleHierarchy[$userRole]) || !isset($roleHierarchy[$requiredRole])) {
            return false;
        }
        
        return $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
    }
}

/**
 * Get file size in human readable format
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

// ========================================
// AUTO-LOADER CONFIGURATION
// ========================================

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// Set error reporting based on environment
if (isProduction()) {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Set default charset
mb_internal_encoding('UTF-8');

// Set locale for currency/date formatting
setlocale(LC_ALL, 'id_ID.UTF-8', 'id_ID');

// ========================================
// END OF CONSTANTS
// ========================================

?>
