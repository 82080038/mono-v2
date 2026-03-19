<?php
/**
 * Single Cooperative Configuration
 * KSP Lam Gabe Jaya - Single Cooperative Setup
 */

class Config {
    // Database Configuration
    const DB_HOST = 'localhost';
    const DB_NAME = 'ksp_lamgabejaya_v2';
    const DB_USER = 'root';
    const DB_PASS = 'root';
    const DB_CHARSET = 'utf8mb4';
    const DB_SOCKET = '';
    
    // Application Configuration
    const APP_NAME = 'KSP Lam Gabe Jaya';
    const APP_VERSION = '2.0.0';
    const APP_URL = 'http://localhost/ksp-lamgabejaya';
    const APP_ENV = 'production'; // development, staging, production
    
    // Security Configuration
    const JWT_SECRET = 'your-super-secret-jwt-key-change-in-production';
    const JWT_EXPIRE_HOURS = 24;
    const PASSWORD_MIN_LENGTH = 8;
    const MAX_LOGIN_ATTEMPTS = 5;
    const SESSION_TIMEOUT_MINUTES = 480;
    
    // GPS & Location Configuration
    const GPS_TOLERANCE_METERS = 50;
    const GPS_MAX_ACCURACY = 100;
    const GEOFENCE_DEFAULT_RADIUS = 5000; // 5km
    const LOCATION_TRACKING_INTERVAL = 300; // 5 minutes
    
    // Fraud Detection Configuration
    const FRAUD_DETECTION_ENABLED = true;
    const FRAUD_RISK_THRESHOLD = 0.7;
    const FRAUD_BLOCK_THRESHOLD = 0.9;
    const FRAUD_REVIEW_THRESHOLD = 0.8;
    const ML_MODEL_PATH = __DIR__ . '/../models/fraud_detection/';
    
    // File Upload Configuration
    const UPLOAD_MAX_SIZE = 5242880; // 5MB
    const UPLOAD_ALLOWED_TYPES = ['jpg', 'jpeg', 'png', 'pdf'];
    const UPLOAD_PATH = __DIR__ . '/../uploads/';
    const AVATAR_PATH = __DIR__ . '/../uploads/avatars/';
    const DOCUMENT_PATH = __DIR__ . '/../uploads/documents/';
    
    // Notification Configuration
    const SMS_GATEWAY_URL = 'https://api.sms-provider.com/send';
    const SMS_GATEWAY_KEY = 'your-sms-api-key';
    const EMAIL_SMTP_HOST = 'smtp.gmail.com';
    const EMAIL_SMTP_PORT = 587;
    const EMAIL_SMTP_USERNAME = 'your-email@gmail.com';
    const EMAIL_SMTP_PASSWORD = 'your-email-password';
    const WHATSAPP_API_URL = 'https://api.whatsapp.com/send';
    
    // Payment Gateway Configuration
    const QRIS_MERCHANT_ID = 'your-qrism-merchant-id';
    const QRIS_API_KEY = 'your-qrism-api-key';
    const QRIS_API_URL = 'https://api.qrism.co.id';
    const BANK_API_URL = 'https://api.bank.co.id';
    
    // Cache Configuration
    const CACHE_DRIVER = 'file'; // file, redis, memcached
    const CACHE_PREFIX = 'ksp_';
    const CACHE_DEFAULT_TTL = 3600; // 1 hour
    
    // Logging Configuration
    const LOG_LEVEL = 'info'; // debug, info, warning, error
    const LOG_PATH = __DIR__ . '/../logs/';
    const LOG_MAX_FILES = 30;
    const AUDIT_LOG_ENABLED = true;
    
    // Backup Configuration
    const BACKUP_ENABLED = true;
    const BACKUP_PATH = __DIR__ . '/../backups/';
    const BACKUP_RETENTION_DAYS = 30;
    const BACKUP_SCHEDULE = '0 2 * * *'; // Daily at 2 AM
    
    // Performance Configuration
    const DB_QUERY_CACHE = true;
    const DB_QUERY_CACHE_SIZE = 256; // MB
    const API_RATE_LIMIT = 100; // requests per minute
    const API_CONCURRENT_LIMIT = 50;
    
    // Business Rules Configuration
    const MAX_DAILY_LOAN_AMOUNT = 5000000;
    const MAX_MONTHLY_LOAN_AMOUNT = 50000000;
    const DEFAULT_INTEREST_RATE_MONTHLY = 0.02; // 2%
    const DEFAULT_LATE_FEE_RATE = 0.001; // 0.1%
    const DEFAULT_ADMIN_FEE_RATE = 0.01; // 1%
    const MIN_CREDIT_SCORE_FOR_LOAN = 50;
    const MAX_LOAN_TERM_MONTHS = 60;
    
    // Mobile App Configuration
    const MOBILE_APP_VERSION = '2.0.0';
    const MOBILE_OFFLINE_SYNC_ENABLED = true;
    const MOBILE_OFFLINE_STORAGE_LIMIT = 100; // MB
    const MOBILE_BLUETOOTH_PRINTER_ENABLED = true;
    const MOBILE_QR_SCANNER_ENABLED = true;
    
    // Reporting Configuration
    const REPORT_GENERATION_SCHEDULE = '0 3 * * *'; // Daily at 3 AM
    const REPORT_RETENTION_DAYS = 365;
    const REPORT_EXPORT_FORMATS = ['pdf', 'excel', 'csv'];
    
    // Integration Configuration
    const OJK_REPORTING_ENABLED = true;
    const OJK_API_URL = 'https://api.ojk.go.id';
    const TAX_REPORTING_ENABLED = true;
    const DIGITAL_SIGNATURE_ENABLED = true;
    
    // Development Configuration
    const DEBUG_MODE = false;
    const ERROR_REPORTING = true;
    const PERFORMANCE_MONITORING = true;
    const API_DOCUMENTATION_ENABLED = true;
    
    /**
     * Get database connection
     */
    public static function getDatabase() {
        static $db = null;
        
        if ($db === null) {
            try {
                $dsn = "mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME . ";charset=" . self::DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ];
                
                $db = new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
                
                // Set charset after connection
                $db->exec("SET NAMES utf8mb4");
                
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new Exception("Database connection failed");
            }
        }
        
        return $db;
    }
    
    /**
     * Get application URL
     */
    public static function getAppUrl($path = '') {
        return rtrim(self::APP_URL, '/') . '/' . ltrim($path, '/');
    }
    
    /**
     * Get upload URL
     */
    public static function getUploadUrl($filename = '') {
        return self::getAppUrl('uploads/' . $filename);
    }
    
    /**
     * Get API URL
     */
    public static function getApiUrl($endpoint = '') {
        return self::getAppUrl('api/' . $endpoint);
    }
    
    /**
     * Check if feature is enabled
     */
    public static function isFeatureEnabled($feature) {
        $features = [
            'fraud_detection' => self::FRAUD_DETECTION_ENABLED,
            'offline_sync' => self::MOBILE_OFFLINE_SYNC_ENABLED,
            'bluetooth_printer' => self::MOBILE_BLUETOOTH_PRINTER_ENABLED,
            'qr_scanner' => self::MOBILE_QR_SCANNER_ENABLED,
            'backup' => self::BACKUP_ENABLED,
            'audit_log' => self::AUDIT_LOG_ENABLED,
            'ojk_reporting' => self::OJK_REPORTING_ENABLED,
            'tax_reporting' => self::TAX_REPORTING_ENABLED,
            'digital_signature' => self::DIGITAL_SIGNATURE_ENABLED
        ];
        
        return isset($features[$feature]) ? $features[$feature] : false;
    }
    
    /**
     * Get business rule value
     */
    public static function getBusinessRule($rule) {
        $rules = [
            'max_daily_loan_amount' => self::MAX_DAILY_LOAN_AMOUNT,
            'max_monthly_loan_amount' => self::MAX_MONTHLY_LOAN_AMOUNT,
            'default_interest_rate' => self::DEFAULT_INTEREST_RATE_MONTHLY,
            'default_late_fee_rate' => self::DEFAULT_LATE_FEE_RATE,
            'default_admin_fee_rate' => self::DEFAULT_ADMIN_FEE_RATE,
            'min_credit_score' => self::MIN_CREDIT_SCORE_FOR_LOAN,
            'max_loan_term' => self::MAX_LOAN_TERM_MONTHS,
            'gps_tolerance' => self::GPS_TOLERANCE_METERS,
            'max_login_attempts' => self::MAX_LOGIN_ATTEMPTS,
            'session_timeout' => self::SESSION_TIMEOUT_MINUTES
        ];
        
        return isset($rules[$rule]) ? $rules[$rule] : null;
    }
    
    /**
     * Validate configuration
     */
    public static function validate() {
        $errors = [];
        
        // Check required directories
        $required_dirs = [
            self::UPLOAD_PATH,
            self::AVATAR_PATH,
            self::DOCUMENT_PATH,
            self::LOG_PATH,
            self::BACKUP_PATH,
            self::ML_MODEL_PATH
        ];
        
        foreach ($required_dirs as $dir) {
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    $errors[] = "Cannot create directory: $dir";
                }
            }
        }
        
        // Check database connection
        try {
            self::getDatabase();
        } catch (Exception $e) {
            $errors[] = "Database connection failed: " . $e->getMessage();
        }
        
        // Check writable directories
        $writable_dirs = [
            self::UPLOAD_PATH,
            self::LOG_PATH,
            self::BACKUP_PATH
        ];
        
        foreach ($writable_dirs as $dir) {
            if (!is_writable($dir)) {
                $errors[] = "Directory not writable: $dir";
            }
        }
        
        return $errors;
    }
    
    /**
     * Get environment-specific configuration
     */
    public static function getEnvironmentConfig() {
        $env_configs = [
            'development' => [
                'debug' => true,
                'log_level' => 'debug',
                'cache_enabled' => false,
                'error_reporting' => true
            ],
            'staging' => [
                'debug' => true,
                'log_level' => 'info',
                'cache_enabled' => true,
                'error_reporting' => true
            ],
            'production' => [
                'debug' => false,
                'log_level' => 'error',
                'cache_enabled' => true,
                'error_reporting' => false
            ]
        ];
        
        return $env_configs[self::APP_ENV] ?? $env_configs['production'];
    }
}

// Validate configuration on load
$config_errors = Config::validate();
if (!empty($config_errors)) {
    error_log("Configuration errors: " . implode(', ', $config_errors));
}
