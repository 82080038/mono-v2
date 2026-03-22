<?php
/**
 * KSP Lam Gabe Jaya - Enhanced Local Development Configuration
 * Complete local development setup
 */

return [
    // Local Development Settings
    'environment' => 'local',
    'debug' => true,
    'log_level' => 'debug',
    
    // Local Database
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'ksp_lamgabejaya_v2',
        'username' => 'root',
        'password' => '',
        'socket' => '/opt/lampp/var/mysql/mysql.sock',
        'charset' => 'utf8mb4'
    ],
    
    // Local API Configuration
    'api' => [
        'base_url' => 'http://localhost/mono-v2/api',
        'version' => '2.0.0',
        'timeout' => 60,
        'debug_mode' => true
    ],
    
    // Local Authentication
    'auth' => [
        'jwt_secret' => 'local-dev-jwt-secret-key-not-for-production',
        'jwt_expiry' => 86400, // 24 hours for development
        'refresh_token_expiry' => 604800, // 7 days for development
        'password_min_length' => 6, // Lower for development
        'session_timeout' => 7200 // 2 hours for development
    ],
    
    // Local Security (Relaxed for Development)
    'security' => [
        'encryption_key' => 'local-dev-encryption-key-32-chars',
        'hash_algorithm' => 'sha256',
        'csrf_token_expiry' => 7200, // 2 hours for development
        'max_login_attempts' => 10, // More attempts for development
        'lockout_duration' => 300, // 5 minutes for development
        'allowed_origins' => [
            'http://localhost',
            'http://localhost:3000',
            'http://localhost:8080',
            'http://127.0.0.1',
            'http://127.0.0.1:3000',
            'http://127.0.0.1:8080'
        ],
        'bypass_auth' => true // Allow bypassing auth for development
    ],
    
    // Local Payment Gateway (Test Mode)
    'payments' => [
        'test_mode' => true,
        'enabled_methods' => ['cash', 'transfer', 'va', 'qr', 'ewallet'],
        'providers' => [
            'midtrans' => [
                'merchant_id' => 'test-merchant-id',
                'client_key' => 'test-client-key',
                'server_key' => 'test-server-key',
                'sandbox' => true
            ],
            'xendit' => [
                'api_key' => 'test-api-key',
                'secret_key' => 'test-secret-key',
                'sandbox' => true
            ]
        ],
        'va' => [
            'bank_prefix' => '8877',
            'expiry_hours' => 1 // 1 hour for development
        ],
        'qr' => [
            'provider' => 'qris-test',
            'expiry_minutes' => 5 // 5 minutes for development
        ]
    ],
    
    // Local GPS Configuration
    'gps' => [
        'default_radius' => 1, // 1km for development
        'update_interval' => 10, // 10 seconds for development
        'max_tracking_duration' => 3600, // 1 hour for development
        'geofence_check_interval' => 30, // 30 seconds for development
        'accuracy_threshold' => 100, // 100 meters for development
        'mock_location' => true // Allow mock GPS for development
    ],
    
    // Local Email Configuration
    'email' => [
        'driver' => 'log', // Log emails instead of sending
        'host' => 'localhost',
        'port' => 1025, // Mailhog port
        'username' => '',
        'password' => '',
        'encryption' => null,
        'from' => [
            'address' => 'dev@ksp-lamgabejaya.local',
            'name' => 'KSP Lam Gabe Jaya (Dev)'
        ],
        'pretend' => true // Don't actually send emails
    ],
    
    // Local SMS Configuration
    'sms' => [
        'provider' => 'log', // Log SMS instead of sending
        'pretend' => true, // Don't actually send SMS
        'mock_number' => '+6281234567890' // Mock number for development
    ],
    
    // Local File Upload Configuration
    'upload' => [
        'max_size' => 5 * 1024 * 1024, // 5MB for development
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'],
        'path' => '/opt/lampp/htdocs/mono-v2/uploads/dev/',
        'url_prefix' => '/uploads/dev/',
        'public' => true // Make uploads publicly accessible
    ],
    
    // Local Cache Configuration
    'cache' => [
        'driver' => 'file', // Use file cache for development
        'path' => '/opt/lampp/htdocs/mono-v2/cache/dev/',
        'prefix' => 'dev_'
    ],
    
    // Local Logging Configuration
    'logging' => [
        'level' => 'debug',
        'path' => '/opt/lampp/htdocs/mono-v2/logs/dev/',
        'max_files' => 7, // Keep 7 days of logs for development
        'channels' => ['single', 'daily'],
        'console' => true, // Log to console
        'verbose' => true // Verbose logging for development
    ],
    
    // Local Monitoring Configuration
    'monitoring' => [
        'enabled' => true,
        'metrics_endpoint' => '/dev/metrics',
        'health_check_endpoint' => '/dev/health',
        'debug_bar' => true, // Show debug bar
        'profiler' => true, // Enable profiler
        'query_log' => true // Log all queries
    ],
    
    // Local Backup Configuration
    'backup' => [
        'enabled' => false, // Disable backup in development
        'schedule' => '0 */6 * * *', // Every 6 hours for development
        'retention_days' => 3, // Keep only 3 days in development
        'storage' => [
            'type' => 'local',
            'path' => '/opt/lampp/htdocs/mono-v2/backups/dev/'
        ]
    ],
    
    // Local Feature Flags (All Enabled)
    'features' => [
        'ai_credit_scoring' => true,
        'gps_tracking' => true,
        'online_payments' => true,
        'mobile_app' => true,
        'bi_analytics' => true,
        'notifications' => true,
        'audit_logging' => true,
        'api_v2' => true,
        'debug_mode' => true,
        'mock_data' => true, // Use mock data for development
        'seed_data' => true, // Auto-seed data
        'auto_migrate' => true // Auto-migrate database
    ],
    
    // Local Business Rules (Relaxed)
    'business' => [
        'loan' => [
            'min_amount' => 100000, // 100 ribu for development
            'max_amount' => 10000000, // 10 juta for development
            'min_term' => 1,
            'max_term' => 12, // 12 months for development
            'interest_rate_min' => 1.0, // Lower for development
            'interest_rate_max' => 2.0 // Lower for development
        ],
        'savings' => [
            'min_deposit' => 1000, // 1 ribu for development
            'min_balance' => 5000, // 5 ribu for development
            'interest_rate' => 1.0 // Higher for development
        ],
        'member' => [
            'min_age' => 16, // Lower for development
            'max_age' => 70,
            'registration_fee' => 0 // Free for development
        ]
    ],
    
    // Local System Configuration
    'system' => [
        'name' => 'KSP Lam Gabe Jaya (Development)',
        'version' => '2.0.0-dev',
        'environment' => 'local',
        'debug' => true,
        'timezone' => 'Asia/Jakarta',
        'locale' => 'id_ID',
        'currency' => 'IDR',
        'date_format' => 'Y-m-d',
        'datetime_format' => 'Y-m-d H:i:s',
        'maintenance_mode' => false,
        'dev_bar' => true, // Show development bar
        'auto_reload' => true // Auto-reload on changes
    ],
    
    // Local Development Tools
    'dev_tools' => [
        'api_documentation' => true,
        'query_inspector' => true,
        'route_list' => true,
        'cache_clear' => true,
        'config_show' => true,
        'migrate_fresh' => true,
        'seed_data' => true,
        'test_data' => true,
        'debug_queries' => true,
        'performance_monitor' => true
    ],
    
    // Local Testing Configuration
    'testing' => [
        'database' => [
            'name' => 'ksp_lamgabejaya_v2_test',
            'seed' => true,
            'migrate' => true
        ],
        'mock_services' => [
            'email' => true,
            'sms' => true,
            'payments' => true,
            'gps' => true
        ],
        'test_data' => [
            'members' => 10,
            'accounts' => 20,
            'loans' => 15,
            'transactions' => 50
        ]
    ],
    
    // Local Development URLs
    'urls' => [
        'frontend' => 'http://localhost/mono-v2',
        'api' => 'http://localhost/mono-v2/api',
        'admin' => 'http://localhost/mono-v2/pages/admin',
        'staff' => 'http://localhost/mono-v2/pages/staff',
        'member' => 'http://localhost/mono-v2/pages/member',
        'docs' => 'http://localhost/mono-v2/docs',
        'metrics' => 'http://localhost/mono-v2/dev/metrics',
        'health' => 'http://localhost/mono-v2/dev/health'
    ],
    
    // Local Development Commands
    'commands' => [
        'serve' => 'php -S localhost:8000',
        'migrate' => 'php migrate.php',
        'seed' => 'php seed.php',
        'test' => 'php test.php',
        'clear_cache' => 'php clear_cache.php',
        'backup' => 'php backup.php',
        'logs' => 'tail -f logs/app.log',
        'db_reset' => 'php db_reset.php'
    ]
];
?>
