<?php

class Config {
    // Database Configuration
    const DB_HOST = 'localhost';
    const DB_SOCKET = '/opt/lampp/var/mysql/mysql.sock';
    const DB_NAME = 'ksp_lamgabejaya_v2';
    const DB_USER = 'root';
    const DB_PASSWORD = 'root';
    const DB_CHARSET = 'utf8mb4';
    
    // Application Configuration
    const APP_NAME = 'KSP Lam Gabe Jaya';
    const APP_VERSION = '4.0';
    const APP_ENV = 'production';
    const DEBUG = false;
    
    // Security Configuration
    const JWT_SECRET = 'ksp-lamgabejaya-secret-key-2026-secure-hash-with-salt';
    const JWT_ALGORITHM = 'HS256';
    const JWT_EXPIRY = 86400; // 24 hours
    const JWT_REFRESH_EXPIRY = 604800; // 7 days
    const PASSWORD_MIN_LENGTH = 8;
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOGIN_LOCKOUT_TIME = 900; // 15 minutes
    const CSRF_TOKEN_LENGTH = 32;
    const SESSION_TIMEOUT = 3600; // 1 hour
    const ENCRYPTION_KEY = 'ksp-encryption-key-2026-secure';
    
    // API Configuration
    const API_RATE_LIMIT = 100; // requests per hour
    const API_TIMEOUT = 30; // seconds
    const API_MAX_FILE_SIZE = 10485760; // 10MB
    const API_VERSION = 'v1';
    const API_BASE_URL = 'http://localhost/mono-v2/api';
    const CORS_ORIGINS = ['http://localhost', 'https://localhost'];
    const CORS_METHODS = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
    const CORS_HEADERS = ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-Token'];
    
    // GPS Configuration
    const GPS_UPDATE_INTERVAL = 30; // seconds
    const GPS_ACCURACY_THRESHOLD = 50; // meters
    const GPS_SPEED_LIMIT = 120; // km/h
    const GEOFENCE_CHECK_INTERVAL = 60; // seconds
    
    // Cache Configuration
    const CACHE_ENABLED = true;
    const CACHE_TTL = 300; // 5 minutes
    const CACHE_PATH = __DIR__ . '/../cache/';
    
    // Logging Configuration
    const LOG_ENABLED = true;
    const LOG_LEVEL = 'INFO';
    const LOG_PATH = __DIR__ . '/../logs/';
    const LOG_MAX_SIZE = 10485760; // 10MB
    const LOG_ROTATION = true;
    const LOG_RETENTION_DAYS = 30;
    
    // Email Configuration
    const SMTP_HOST = 'localhost';
    const SMTP_PORT = 587;
    const SMTP_USERNAME = '';
    const SMTP_PASSWORD = '';
    const SMTP_ENCRYPTION = 'tls';
    
    // File Upload Configuration
    const UPLOAD_PATH = __DIR__ . '/../uploads/';
    const UPLOAD_MAX_SIZE = 5242880; // 5MB
    const UPLOAD_ALLOWED_TYPES = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    
    // Backup Configuration
    const BACKUP_ENABLED = true;
    const BACKUP_PATH = __DIR__ . '/../backups/';
    const BACKUP_SCHEDULE = 'daily';
    const BACKUP_RETENTION = 30; // days
    
    // WhatsApp Integration (if available)
    const WHATSAPP_ENABLED = false;
    const WHATSAPP_API_URL = '';
    const WHATSAPP_TOKEN = '';
    
    // QRIS Payment Configuration
    const QRIS_ENABLED = false;
    const QRIS_MERCHANT_ID = '';
    const QRIS_API_KEY = '';
    
    // System Limits
    const MAX_MEMBERS_PER_PAGE = 50;
    const MAX_TRANSACTIONS_PER_PAGE = 100;
    const MAX_GPS_LOGS_PER_PAGE = 200;
    
    public static function getDatabaseConfig() {
        return [
            'host' => self::DB_HOST,
            'socket' => self::DB_SOCKET,
            'dbname' => self::DB_NAME,
            'username' => self::DB_USER,
            'password' => self::DB_PASSWORD,
            'charset' => self::DB_CHARSET,
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . self::DB_CHARSET . " COLLATE " . self::DB_CHARSET . "_unicode_ci, SESSION sql_mode='STRICT_TRANS_TABLES'",
                PDO::ATTR_TIMEOUT => self::API_TIMEOUT,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
            ]
        ];
    }
    
    public static function getJWTConfig() {
        return [
            'secret' => self::JWT_SECRET,
            'expiry' => self::JWT_EXPIRY
        ];
    }
    
    public static function getGPSConfig() {
        return [
            'update_interval' => self::GPS_UPDATE_INTERVAL,
            'accuracy_threshold' => self::GPS_ACCURACY_THRESHOLD,
            'speed_limit' => self::GPS_SPEED_LIMIT,
            'geofence_check_interval' => self::GEOFENCE_CHECK_INTERVAL
        ];
    }
    
    public static function getAPIConfig() {
        return [
            'rate_limit' => self::API_RATE_LIMIT,
            'timeout' => self::API_TIMEOUT,
            'max_file_size' => self::API_MAX_FILE_SIZE
        ];
    }
    
    public static function getCacheConfig() {
        return [
            'enabled' => self::CACHE_ENABLED,
            'ttl' => self::CACHE_TTL,
            'path' => self::CACHE_PATH
        ];
    }
    
    public static function getLogConfig() {
        return [
            'enabled' => self::LOG_ENABLED,
            'level' => self::LOG_LEVEL,
            'path' => self::LOG_PATH,
            'max_size' => self::LOG_MAX_SIZE
        ];
    }
    
    public static function getUploadConfig() {
        return [
            'path' => self::UPLOAD_PATH,
            'max_size' => self::UPLOAD_MAX_SIZE,
            'allowed_types' => self::UPLOAD_ALLOWED_TYPES
        ];
    }
    
    public static function getBackupConfig() {
        return [
            'enabled' => self::BACKUP_ENABLED,
            'path' => self::BACKUP_PATH,
            'schedule' => self::BACKUP_SCHEDULE,
            'retention' => self::BACKUP_RETENTION
        ];
    }
    
    public static function isDebugMode() {
        return self::DEBUG;
    }
    
    public static function getEnvironment() {
        return self::APP_ENV;
    }
    
    public static function getAppInfo() {
        return [
            'name' => self::APP_NAME,
            'version' => self::APP_VERSION,
            'environment' => self::APP_ENV
        ];
    }
    
    public static function ensureDirectories() {
        $directories = [
            self::CACHE_PATH,
            self::LOG_PATH,
            self::UPLOAD_PATH,
            self::BACKUP_PATH
        ];
        
        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
        }
    }
    
    public static function validateEnvironment() {
        $errors = [];
        
        // Check required PHP extensions
        $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'curl'];
        foreach ($requiredExtensions as $extension) {
            if (!extension_loaded($extension)) {
                $errors[] = "Required PHP extension '$extension' is not loaded";
            }
        }
        
        // Check directory permissions
        $writableDirectories = [self::LOG_PATH, self::UPLOAD_PATH, self::BACKUP_PATH];
        foreach ($writableDirectories as $directory) {
            if (is_dir($directory) && !is_writable($directory)) {
                $errors[] = "Directory '$directory' is not writable";
            }
        }
        
        // Check database connection
        try {
            $config = self::getDatabaseConfig();
            $dsn = "mysql:unix_socket={$config['socket']};dbname={$config['dbname']};charset={$config['charset']}";
            new PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (PDOException $e) {
            $errors[] = "Database connection failed: " . $e->getMessage();
        }
        
        return $errors;
    }
}
