<?php
/**
 * KSP Lam Gabe Jaya - Enhanced API Configuration
 * Complete configuration with all modern features
 */

return [
    // API Configuration
    'api' => [
        'version' => '2.0.0',
        'base_url' => $_ENV['API_BASE_URL'] ?? 'https://api.ksp-lamgabejaya.com',
        'timeout' => 30,
        'retry_attempts' => 3,
        'rate_limit' => [
            'requests_per_minute' => 100,
            'burst_limit' => 20
        ]
    ],
    
    // Authentication
    'auth' => [
        'jwt_secret' => $_ENV['JWT_SECRET'] ?? 'your-super-secret-jwt-key-change-in-production',
        'jwt_expiry' => 3600, // 1 hour
        'refresh_token_expiry' => 86400, // 24 hours
        'password_min_length' => 8,
        'session_timeout' => 1800 // 30 minutes
    ],
    
    // Database Configuration
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'name' => $_ENV['DB_NAME'] ?? 'ksp_lamgabejaya_v2',
        'username' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    ],
    
    // Security Configuration
    'security' => [
        'encryption_key' => $_ENV['ENCRYPTION_KEY'] ?? 'your-256-bit-encryption-key',
        'hash_algorithm' => 'sha256',
        'password_pepper' => $_ENV['PASSWORD_PEPPER'] ?? 'your-password-pepper',
        'csrf_token_expiry' => 3600,
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
        'allowed_origins' => [
            'http://localhost:3000',
            'https://ksp-lamgabejaya.com',
            'https://www.ksp-lamgabejaya.com'
        ]
    ],
    
    // Payment Gateway Configuration
    'payments' => [
        'enabled_methods' => ['cash', 'transfer', 'va', 'qr', 'ewallet'],
        'providers' => [
            'midtrans' => [
                'merchant_id' => $_ENV['MIDTRANS_MERCHANT_ID'] ?? '',
                'client_key' => $_ENV['MIDTRANS_CLIENT_KEY'] ?? '',
                'server_key' => $_ENV['MIDTRANS_SERVER_KEY'] ?? '',
                'sandbox' => $_ENV['MIDTRANS_SANDBOX'] ?? true
            ],
            'xendit' => [
                'api_key' => $_ENV['XENDIT_API_KEY'] ?? '',
                'secret_key' => $_ENV['XENDIT_SECRET_KEY'] ?? '',
                'sandbox' => $_ENV['XENDIT_SANDBOX'] ?? true
            ]
        ],
        'va' => [
            'bank_prefix' => '8877',
            'expiry_hours' => 24
        ],
        'qr' => [
            'provider' => 'qris',
            'expiry_minutes' => 15
        ]
    ],
    
    // GPS Configuration
    'gps' => [
        'default_radius' => 5, // 5km
        'update_interval' => 30, // 30 seconds
        'max_tracking_duration' => 86400, // 24 hours
        'geofence_check_interval' => 60, // 1 minute
        'accuracy_threshold' => 50 // 50 meters
    ],
    
    // Email Configuration
    'email' => [
        'driver' => $_ENV['MAIL_DRIVER'] ?? 'smtp',
        'host' => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
        'port' => $_ENV['MAIL_PORT'] ?? 587,
        'username' => $_ENV['MAIL_USERNAME'] ?? '',
        'password' => $_ENV['MAIL_PASSWORD'] ?? '',
        'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
        'from' => [
            'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@ksp-lamgabejaya.com',
            'name' => $_ENV['MAIL_FROM_NAME'] ?? 'KSP Lam Gabe Jaya'
        ]
    ],
    
    // SMS Configuration
    'sms' => [
        'provider' => $_ENV['SMS_PROVIDER'] ?? 'twilio',
        'twilio' => [
            'sid' => $_ENV['TWILIO_SID'] ?? '',
            'token' => $_ENV['TWILIO_TOKEN'] ?? '',
            'from' => $_ENV['TWILIO_FROM'] ?? ''
        ],
        'wa' => [
            'api_key' => $_ENV['WA_API_KEY'] ?? '',
            'api_url' => $_ENV['WA_API_URL'] ?? ''
        ]
    ],
    
    // File Upload Configuration
    'upload' => [
        'max_size' => 10 * 1024 * 1024, // 10MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'path' => $_ENV['UPLOAD_PATH'] ?? '/opt/lampp/htdocs/mono-v2/uploads/',
        'url_prefix' => $_ENV['UPLOAD_URL_PREFIX'] ?? '/uploads/'
    ],
    
    // Cache Configuration
    'cache' => [
        'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
        'redis' => [
            'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['REDIS_PORT'] ?? 6379,
            'password' => $_ENV['REDIS_PASSWORD'] ?? null,
            'database' => $_ENV['REDIS_DB'] ?? 0
        ],
        'memcached' => [
            'servers' => [
                ['host' => $_ENV['MEMCACHED_HOST'] ?? '127.0.0.1', 'port' => $_ENV['MEMCACHED_PORT'] ?? 11211]
            ]
        ]
    ],
    
    // Logging Configuration
    'logging' => [
        'level' => $_ENV['LOG_LEVEL'] ?? 'info',
        'path' => $_ENV['LOG_PATH'] ?? '/opt/lampp/htdocs/mono-v2/logs/',
        'max_files' => 30,
        'channels' => ['single', 'daily', 'slack', 'monolog'],
        'slack' => [
            'webhook_url' => $_ENV['SLACK_WEBHOOK_URL'] ?? '',
            'channel' => $_ENV['SLACK_CHANNEL'] ?? '#alerts'
        ]
    ],
    
    // Monitoring Configuration
    'monitoring' => [
        'enabled' => $_ENV['MONITORING_ENABLED'] ?? true,
        'metrics_endpoint' => '/metrics',
        'health_check_endpoint' => '/health',
        'alert_thresholds' => [
            'memory_usage' => 80, // 80%
            'cpu_usage' => 80, // 80%
            'disk_usage' => 85, // 85%
            'response_time' => 2000 // 2 seconds
        ]
    ],
    
    // Backup Configuration
    'backup' => [
        'enabled' => $_ENV['BACKUP_ENABLED'] ?? true,
        'schedule' => $_ENV['BACKUP_SCHEDULE'] ?? '0 2 * * *', // Daily at 2 AM
        'retention_days' => $_ENV['BACKUP_RETENTION_DAYS'] ?? 30,
        'storage' => [
            'type' => $_ENV['BACKUP_STORAGE_TYPE'] ?? 'local',
            'path' => $_ENV['BACKUP_PATH'] ?? '/opt/lampp/htdocs/mono-v2/backups/',
            's3' => [
                'bucket' => $_ENV['BACKUP_S3_BUCKET'] ?? '',
                'key' => $_ENV['BACKUP_S3_KEY'] ?? '',
                'secret' => $_ENV['BACKUP_S3_SECRET'] ?? '',
                'region' => $_ENV['BACKUP_S3_REGION'] ?? 'us-east-1'
            ]
        ]
    ],
    
    // Feature Flags
    'features' => [
        'ai_credit_scoring' => $_ENV['FEATURE_AI_SCORING'] ?? true,
        'gps_tracking' => $_ENV['FEATURE_GPS_TRACKING'] ?? true,
        'online_payments' => $_ENV['FEATURE_ONLINE_PAYMENTS'] ?? true,
        'mobile_app' => $_ENV['FEATURE_MOBILE_APP'] ?? true,
        'bi_analytics' => $_ENV['FEATURE_BI_ANALYTICS'] ?? true,
        'notifications' => $_ENV['FEATURE_NOTIFICATIONS'] ?? true,
        'audit_logging' => $_ENV['FEATURE_AUDIT_LOGGING'] ?? true,
        'api_v2' => $_ENV['FEATURE_API_V2'] ?? true
    ],
    
    // Business Rules
    'business' => [
        'loan' => [
            'min_amount' => 1000000, // 1 juta
            'max_amount' => 50000000, // 50 juta
            'min_term' => 1, // 1 bulan
            'max_term' => 36, // 36 bulan
            'interest_rate_min' => 1.5, // 1.5% per bulan
            'interest_rate_max' => 3.0 // 3% per bulan
        ],
        'savings' => [
            'min_deposit' => 10000, // 10 ribu
            'min_balance' => 50000, // 50 ribu
            'interest_rate' => 0.5 // 0.5% per bulan
        ],
        'member' => [
            'min_age' => 17,
            'max_age' => 65,
            'registration_fee' => 10000 // 10 ribu
        ]
    ],
    
    // System Configuration
    'system' => [
        'name' => 'KSP Lam Gabe Jaya',
        'version' => '2.0.0',
        'environment' => $_ENV['APP_ENV'] ?? 'production',
        'debug' => $_ENV['APP_DEBUG'] ?? false,
        'timezone' => 'Asia/Jakarta',
        'locale' => 'id_ID',
        'currency' => 'IDR',
        'date_format' => 'Y-m-d',
        'datetime_format' => 'Y-m-d H:i:s'
    ]
];
?>
