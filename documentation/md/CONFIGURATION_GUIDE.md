# Configuration Guide

## 🎯 Overview

Dokumentasi lengkap untuk konfigurasi aplikasi KSP Lam Gabe Jaya. Guide ini mencakup setup environment, konfigurasi database, pengaturan aplikasi, dan optimasi performa.

## 📋 Table of Contents

- [Environment Setup](#environment-setup)
- [Database Configuration](#database-configuration)
- [Application Configuration](#application-configuration)
- [Security Configuration](#security-configuration)
- [Server Configuration](#server-configuration)
- [Performance Optimization](#performance-optimization)
- [Logging Configuration](#logging-configuration)
- [Development vs Production](#development-vs-production)
- [Troubleshooting](#troubleshooting)

---

## 🌍 Environment Setup

### **Environment Variables**

#### **.env File Structure**
```bash
# Environment Configuration
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=Asia/Jakarta

# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ksp_lamgabejaya_v2
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Security Configuration
APP_KEY=base64:YourSecretKeyHere32CharsLong
JWT_SECRET=your-jwt-secret-key-here
HASH_COST=12

# Email Configuration
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@ksp-lamgabejaya.com
MAIL_FROM_NAME=KSP Lam Gabe Jaya

# SMS Configuration
SMS_PROVIDER=twilio
TWILIO_SID=your-twilio-sid
TWILIO_TOKEN=your-twilio-token
TWILIO_FROM=+628123456789

# File Storage
FILESYSTEM_DRIVER=local
UPLOAD_PATH=uploads/
MAX_FILE_SIZE=5242880
ALLOWED_EXTENSIONS=jpg,jpeg,png,pdf,doc,docx

# Cache Configuration
CACHE_DRIVER=file
CACHE_PREFIX=ksp_
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=

# API Configuration
API_RATE_LIMIT=60
API_TIMEOUT=30
API_VERSION=v1

# Backup Configuration
BACKUP_ENABLED=true
BACKUP_PATH=backups/
BACKUP_SCHEDULE=daily
BACKUP_RETENTION=30

# Logging Configuration
LOG_CHANNEL=stack
LOG_LEVEL=debug
LOG_PATH=logs/
LOG_MAX_FILES=30
```

#### **Environment Class**
```php
<?php
// config/Environment.php
class Environment {
    private static $variables = [];
    
    public static function load($file = '.env') {
        if (!file_exists($file)) {
            throw new Exception("Environment file not found: $file");
        }
        
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue; // Skip comments
            }
            
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                $value = trim($value, '"\'');
                
                // Set environment variable
                $_ENV[$key] = $value;
                putenv("$key=$value");
                self::$variables[$key] = $value;
            }
        }
    }
    
    public static function get($key, $default = null) {
        return self::$variables[$key] ?? $_ENV[$key] ?? $default;
    }
    
    public static function isProduction() {
        return self::get('APP_ENV') === 'production';
    }
    
    public static function isDevelopment() {
        return self::get('APP_ENV') === 'development';
    }
    
    public static function isDebug() {
        return filter_var(self::get('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN);
    }
}

// Load environment
Environment::load();
?>
```

---

## 🗄️ Database Configuration

### **Database Connection**

#### **MySQL Configuration**
```php
<?php
// config/Database.php
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $this->connection = new PDO(
            "mysql:host=" . Environment::get('DB_HOST') . 
            ";port=" . Environment::get('DB_PORT') . 
            ";dbname=" . Environment::get('DB_DATABASE') . 
            ";charset=" . Environment::get('DB_CHARSET'),
            Environment::get('DB_USERNAME'),
            Environment::get('DB_PASSWORD'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . Environment::get('DB_CHARSET')
            ]
        );
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
}
?>
```

#### **Database Configuration File**
```php
<?php
// config/database.php
return [
    'default' => 'mysql',
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => Environment::get('DB_HOST', 'localhost'),
            'port' => Environment::get('DB_PORT', '3306'),
            'database' => Environment::get('DB_DATABASE'),
            'username' => Environment::get('DB_USERNAME'),
            'password' => Environment::get('DB_PASSWORD'),
            'charset' => Environment::get('DB_CHARSET', 'utf8mb4'),
            'collation' => Environment::get('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'strict' => true,
            'engine' => 'InnoDB',
            'options' => [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_TIMEOUT => 30,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
            ]
        ],
        
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
            'prefix' => '',
        ],
    ],
    
    'migrations' => 'migrations',
    'seed' => 'seed'
];
?>
```

### **Database Migration**

#### **Migration Structure**
```php
<?php
// database/migrations/001_create_members_table.php
class CreateMembersTable {
    public function up() {
        $sql = "
            CREATE TABLE IF NOT EXISTS members (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nik VARCHAR(16) UNIQUE NOT NULL,
                name VARCHAR(255) NOT NULL,
                phone VARCHAR(20),
                email VARCHAR(255) UNIQUE,
                birth_date DATE,
                gender ENUM('Laki-laki', 'Perempuan'),
                address TEXT,
                village VARCHAR(100),
                district VARCHAR(100),
                city VARCHAR(100),
                province VARCHAR(100),
                postal_code VARCHAR(5),
                status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
                balance DECIMAL(15,2) DEFAULT 0.00,
                loan_limit DECIMAL(15,2) DEFAULT 0.00,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                INDEX idx_nik (nik),
                INDEX idx_email (email),
                INDEX idx_status (status),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        Database::getInstance()->query($sql);
    }
    
    public function down() {
        $sql = "DROP TABLE IF EXISTS members";
        Database::getInstance()->query($sql);
    }
}
?>
```

#### **Migration Runner**
```php
<?php
// scripts/migrate.php
class MigrationRunner {
    private $migrationsPath;
    private $appliedMigrations = [];
    
    public function __construct($migrationsPath) {
        $this->migrationsPath = $migrationsPath;
        $this->loadAppliedMigrations();
    }
    
    public function migrate() {
        $migrationFiles = glob($this->migrationsPath . '*.php');
        
        foreach ($migrationFiles as $file) {
            $className = $this->getClassNameFromFile($file);
            $migration = new $className();
            
            if (!in_array($className, $this->appliedMigrations)) {
                echo "Running migration: $className\n";
                $migration->up();
                $this->logMigration($className);
            }
        }
    }
    
    public function rollback($steps = 1) {
        $migrationFiles = glob($this->migrationsPath . '*.php');
        $migrationFiles = array_reverse($migrationFiles);
        
        $count = 0;
        foreach ($migrationFiles as $file) {
            if ($count >= $steps) break;
            
            $className = $this->getClassNameFromFile($file);
            
            if (in_array($className, $this->appliedMigrations)) {
                echo "Rolling back migration: $className\n";
                $migration = new $className();
                $migration->down();
                $this->removeMigration($className);
                $count++;
            }
        }
    }
    
    private function getClassNameFromFile($file) {
        $filename = basename($file, '.php');
        return str_replace('_', '', ucwords($filename, '_'));
    }
    
    private function loadAppliedMigrations() {
        $sql = "SELECT migration FROM migrations ORDER BY id DESC";
        $result = Database::getInstance()->query($sql);
        
        while ($row = $result->fetch()) {
            $this->appliedMigrations[] = $row['migration'];
        }
    }
    
    private function logMigration($migration) {
        $sql = "INSERT INTO migrations (migration) VALUES (?)";
        Database::getInstance()->query($sql, [$migration]);
    }
    
    private function removeMigration($migration) {
        $sql = "DELETE FROM migrations WHERE migration = ?";
        Database::getInstance()->query($sql, [$migration]);
    }
}

// Run migrations
$migrationRunner = new MigrationRunner('database/migrations/');
$migrationRunner->migrate();
?>
```

---

## ⚙️ Application Configuration

### **Core Configuration**

#### **App Configuration**
```php
<?php
// config/app.php
return [
    'name' => 'KSP Lam Gabe Jaya',
    'version' => '1.0.0',
    'env' => Environment::get('APP_ENV', 'production'),
    'debug' => Environment::isDebug(),
    'url' => Environment::get('APP_URL', 'http://localhost'),
    'timezone' => Environment::get('APP_TIMEZONE', 'Asia/Jakarta'),
    'locale' => 'id',
    'fallback_locale' => 'en',
    
    'currency' => [
        'code' => 'IDR',
        'symbol' => 'Rp',
        'precision' => 2,
        'decimal_separator' => ',',
        'thousands_separator' => '.'
    ],
    
    'features' => [
        'online_payments' => true,
        'sms_notifications' => true,
        'email_notifications' => true,
        'auto_backup' => true,
        'api_access' => true,
        'mobile_app' => false
    ],
    
    'limits' => [
        'max_loan_amount' => 500000000,
        'min_loan_amount' => 1000000,
        'max_daily_withdrawal' => 10000000,
        'min_deposit_amount' => 100000,
        'max_file_upload_size' => 5242880, // 5MB
        'session_lifetime' => 7200 // 2 hours
    ],
    
    'business' => [
        'interest_rate' => 0.15, // 15% per year
        'late_fee_rate' => 0.02, // 2% per month
        'admin_fee_rate' => 0.01, // 1% of loan amount
        'working_days' => [1, 2, 3, 4, 5], // Monday to Friday
        'working_hours' => [
            'start' => '08:00',
            'end' => '17:00'
        ]
    ]
];
?>
```

#### **Session Configuration**
```php
<?php
// config/session.php
return [
    'driver' => Environment::get('SESSION_DRIVER', 'file'),
    'lifetime' => Environment::get('SESSION_LIFETIME', 120), // minutes
    'expire_on_close' => false,
    'encrypt' => filter_var(Environment::get('SESSION_ENCRYPT', false), FILTER_VALIDATE_BOOLEAN),
    'files' => session_save_path(),
    'connection' => null,
    'table' => 'sessions',
    'store' => null,
    'lottery' => [2, 100],
    'cookie' => 'ksp_session',
    'path' => Environment::get('SESSION_PATH', '/'),
    'domain' => Environment::get('SESSION_DOMAIN', ''),
    'secure' => Environment::isProduction(),
    'http_only' => true,
    'same_site' => 'lax'
];
?>
```

#### **Cache Configuration**
```php
<?php
// config/cache.php
return [
    'default' => Environment::get('CACHE_DRIVER', 'file'),
    
    'stores' => [
        'apc' => [
            'driver' => 'apc',
        ],
        
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
        
        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
        ],
        
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],
        
        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],
        
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],
        
        'dynamodb' => [
            'driver' => 'dynamodb',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'table' => env('DYNAMODB_CACHE_TABLE', 'cache'),
        ],
    ],
    
    'prefix' => Environment::get('CACHE_PREFIX', 'ksp_'),
];
?>
```

### **Email Configuration**

#### **Mail Configuration**
```php
<?php
// config/mail.php
return [
    'default' => Environment::get('MAIL_DRIVER', 'smtp'),
    
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => Environment::get('MAIL_HOST'),
            'port' => Environment::get('MAIL_PORT', 587),
            'encryption' => Environment::get('MAIL_ENCRYPTION', 'tls'),
            'username' => Environment::get('MAIL_USERNAME'),
            'password' => Environment::get('MAIL_PASSWORD'),
            'timeout' => null,
            'auth_mode' => null,
        ],
        
        'ses' => [
            'transport' => 'ses',
        ],
        
        'mailgun' => [
            'transport' => 'mailgun',
        ],
    ],
    
    'from' => [
        'address' => Environment::get('MAIL_FROM_ADDRESS', 'noreply@ksp-lamgabejaya.com'),
        'name' => Environment::get('MAIL_FROM_NAME', 'KSP Lam Gabe Jaya'),
    ],
    
    'markdown' => [
        'theme' => 'default',
        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],
];
?>
```

#### **Email Templates**
```php
<?php
// templates/email/welcome.php
class WelcomeEmail {
    private $member;
    
    public function __construct($member) {
        $this->member = $member;
    }
    
    public function build() {
        $subject = 'Selamat Datang di KSP Lam Gabe Jaya';
        $view = 'emails.welcome';
        $data = [
            'member' => $this->member,
            'app_name' => config('app.name'),
            'login_url' => config('app.url') . '/login'
        ];
        
        return [
            'subject' => $subject,
            'view' => $view,
            'data' => $data
        ];
    }
}
?>
```

---

## 🔒 Security Configuration

### **Authentication Security**

#### **JWT Configuration**
```php
<?php
// config/jwt.php
return [
    'secret' => Environment::get('JWT_SECRET'),
    'algo' => 'HS256',
    'expires_in' => 3600, // 1 hour
    'refresh_expires_in' => 604800, // 7 days
    'issuer' => config('app.url'),
    'audience' => config('app.url'),
    'leeway' => 0
];
?>
```

#### **Password Configuration**
```php
<?php
// config/password.php
return [
    'driver' => 'bcrypt',
    'bcrypt_cost' => Environment::get('HASH_COST', 12),
    'argon_memory' => 65536,
    'argon_threads' => 1,
    'argon_time' => 4,
    'min_length' => 8,
    'require_uppercase' => true,
    'require_lowercase' => true,
    'require_numbers' => true,
    'require_symbols' => true
];
?>
```

### **CSRF Protection**

#### **CSRF Configuration**
```php
<?php
// config/csrf.php
return [
    'token_name' => '_token',
    'header_name' => 'X-CSRF-TOKEN',
    'cookie_name' => 'XSRF-TOKEN',
    'expires' => 7200, // 2 hours
    'regenerate' => true,
    'http_only' => true,
    'secure' => Environment::isProduction(),
    'same_site' => 'lax'
];
?>
```

#### **CSRF Middleware**
```php
<?php
// middleware/CSRFProtection.php
class CSRFProtection {
    public static function generateToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_expires'] = time() + config('csrf.expires');
        }
        
        return $_SESSION['csrf_token'];
    }
    
    public static function validateToken($token) {
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_expires']) ||
            time() > $_SESSION['csrf_token_expires']) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public static function getTokenField() {
        $token = self::generateToken();
        return '<input type="hidden" name="' . config('csrf.token_name') . '" value="' . $token . '">';
    }
    
    public static function getHeader() {
        return config('csrf.header_name') . ': ' . self::generateToken();
    }
}
?>
```

---

## 🖥️ Server Configuration

### **Apache Configuration**

#### **.htaccess Configuration**
```apache
# Enable rewrite engine
RewriteEngine On

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self'"

# Hide server signature
ServerSignature Off
ServerTokens Prod

# Disable directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "\.(env|log|md|txt|json|xml)$">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect configuration files
<FilesMatch "^(config|composer|package|\.env).*">
    Order allow,deny
    Deny from all
</FilesMatch>

# URL rewriting
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Cache static files
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/ico "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType application/pdf "access plus 1 month"
</IfModule>

# Compress static files
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# PHP settings
<IfModule mod_php7.c>
    php_flag display_errors Off
    php_value max_execution_time 300
    php_value memory_limit 256M
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_input_vars 3000
</IfModule>
```

### **Nginx Configuration**

#### **Nginx Server Block**
```nginx
server {
    listen 80;
    server_name localhost ksp-lamgabejaya.com;
    root /var/www/ksp-lamgabejaya/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options DENY always;
    add_header X-Content-Type-Options nosniff always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self'" always;

    # Hide server version
    server_tokens off;

    # Disable directory listing
    autoindex off;

    # Protect sensitive files
    location ~ /\.(env|log|md|txt|json|xml)$ {
        deny all;
        return 404;
    }

    location ~ ^/(config|composer|package|\.env).* {
        deny all;
        return 404;
    }

    # PHP processing
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        # PHP settings
        fastcgi_param PHP_VALUE "display_errors=Off";
        fastcgi_param PHP_VALUE "max_execution_time=300";
        fastcgi_param PHP_VALUE "memory_limit=256M";
        fastcgi_param PHP_VALUE "upload_max_filesize=10M";
        fastcgi_param PHP_VALUE "post_max_size=10M";
    }

    # URL rewriting
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Cache static files
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        add_header Vary Accept-Encoding;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/javascript
        application/xml+rss
        application/json;
}
```

---

## ⚡ Performance Optimization

### **Database Optimization**

#### **MySQL Configuration**
```ini
# /etc/mysql/mysql.conf.d/mysqld.cnf
[mysqld]
# General
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Connection
max_connections = 100
max_connect_errors = 1000
wait_timeout = 60
interactive_timeout = 60

# Query cache (MySQL 5.7 and below)
query_cache_type = 1
query_cache_size = 64M
query_cache_limit = 2M

# Slow query log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Binary log
log_bin = /var/log/mysql/mysql-bin.log
binlog_format = ROW
expire_logs_days = 7

# Character set
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# Performance schema
performance_schema = ON
```

#### **Database Indexing**
```sql
-- Create indexes for better performance
CREATE INDEX idx_members_nik ON members(nik);
CREATE INDEX idx_members_email ON members(email);
CREATE INDEX idx_members_status ON members(status);
CREATE INDEX idx_members_created_at ON members(created_at);

CREATE INDEX idx_transactions_member_id ON transactions(member_id);
CREATE INDEX idx_transactions_type ON transactions(type);
CREATE INDEX idx_transactions_status ON transactions(status);
CREATE INDEX idx_transactions_created_at ON transactions(created_at);

CREATE INDEX idx_loan_member_id ON loans(member_id);
CREATE INDEX idx_loan_status ON loans(status);
CREATE INDEX idx_loan_created_at ON loans(created_at);

-- Composite indexes for common queries
CREATE INDEX idx_transactions_member_type ON transactions(member_id, type);
CREATE INDEX idx_transactions_date_status ON transactions(created_at, status);
```

### **Caching Strategy**

#### **Redis Configuration**
```ini
# /etc/redis/redis.conf
# Memory
maxmemory 512mb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000

# Security
requirepass your_redis_password
bind 127.0.0.1

# Performance
tcp-keepalive 300
timeout 0
```

#### **Cache Implementation**
```php
<?php
// services/CacheService.php
class CacheService {
    private static $instance = null;
    private $redis;
    
    private function __construct() {
        $this->redis = new Redis();
        $this->redis->connect(
            Environment::get('REDIS_HOST', '127.0.0.1'),
            Environment::get('REDIS_PORT', 6379)
        );
        
        if (Environment::get('REDIS_PASSWORD')) {
            $this->redis->auth(Environment::get('REDIS_PASSWORD'));
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function remember($key, $callback, $ttl = 3600) {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }
    
    public function get($key) {
        $data = $this->redis->get($key);
        return $data ? json_decode($data, true) : null;
    }
    
    public function set($key, $value, $ttl = 3600) {
        return $this->redis->setex($key, $ttl, json_encode($value));
    }
    
    public function delete($key) {
        return $this->redis->del($key);
    }
    
    public function clear($pattern = '*') {
        $keys = $this->redis->keys($pattern);
        return $this->redis->del($keys);
    }
}
?>
```

---

## 📝 Logging Configuration

### **Log Configuration**

#### **Logging Setup**
```php
<?php
// config/logging.php
return [
    'default' => Environment::get('LOG_CHANNEL', 'stack'),
    
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'daily'],
            'ignore_exceptions' => false,
        ],
        
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/app.log'),
            'level' => Environment::get('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],
        
        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/app.log'),
            'level' => Environment::get('LOG_LEVEL', 'debug'),
            'days' => Environment::get('LOG_MAX_FILES', 30),
        ],
        
        'errorlog' => [
            'driver' => 'errorlog',
            'level' => Environment::get('LOG_LEVEL', 'debug'),
            'type' => Logger::ERROR,
        ],
        
        'syslog' => [
            'driver' => 'syslog',
            'level' => Environment::get('LOG_LEVEL', 'debug'),
            'facility' => LOG_USER,
        ],
        
        'monitor' => [
            'driver' => 'single',
            'path' => storage_path('logs/monitor.log'),
            'level' => 'info',
            'replace_placeholders' => true,
        ],
        
        'security' => [
            'driver' => 'daily',
            'path' => storage_path('logs/security.log'),
            'level' => 'warning',
            'days' => 90,
        ],
        
        'audit' => [
            'driver' => 'daily',
            'path' => storage_path('logs/audit.log'),
            'level' => 'info',
            'days' => 365,
        ],
    ],
];
?>
```

#### **Logger Implementation**
```php
<?php
// services/Logger.php
class Logger {
    private static $instance = null;
    private $loggers = [];
    
    private function __construct() {
        $this->setupLoggers();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function setupLoggers() {
        $config = require 'config/logging.php';
        
        foreach ($config['channels'] as $name => $channel) {
            $this->loggers[$name] = new ChannelLogger($channel);
        }
    }
    
    public function debug($message, array $context = [], $channel = 'default') {
        $this->loggers[$channel]->debug($message, $context);
    }
    
    public function info($message, array $context = [], $channel = 'default') {
        $this->loggers[$channel]->info($message, $context);
    }
    
    public function warning($message, array $context = [], $channel = 'default') {
        $this->loggers[$channel]->warning($message, $context);
    }
    
    public function error($message, array $context = [], $channel = 'default') {
        $this->loggers[$channel]->error($message, $context);
    }
    
    public function critical($message, array $context = [], $channel = 'default') {
        $this->loggers[$channel]->critical($message, $context);
    }
    
    public function audit($action, $userId, $details = []) {
        $context = [
            'action' => $action,
            'user_id' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->info("AUDIT: $action", $context, 'audit');
    }
    
    public function security($event, $details = []) {
        $context = [
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->warning("SECURITY: $event", $context, 'security');
    }
}
?>
```

---

## 🔄 Development vs Production

### **Development Configuration**

#### **Development .env**
```bash
# Development Environment
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost

# Database (Development)
DB_HOST=localhost
DB_DATABASE=ksp_lamgabejaya_dev
DB_USERNAME=root
DB_PASSWORD=

# Logging (Development)
LOG_LEVEL=debug
LOG_CHANNEL=stack

# Cache (Development)
CACHE_DRIVER=array

# Session (Development)
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Email (Development)
MAIL_DRIVER=log
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=

# Security (Development)
APP_KEY=base64:dev-key-here
JWT_SECRET=dev-jwt-secret-here
```

### **Production Configuration**

#### **Production .env**
```bash
# Production Environment
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ksp-lamgabejaya.com

# Database (Production)
DB_HOST=production-db-host
DB_DATABASE=ksp_lamgabejaya_prod
DB_USERNAME=app_user
DB_PASSWORD=secure_password_here

# Logging (Production)
LOG_LEVEL=warning
LOG_CHANNEL=daily

# Cache (Production)
CACHE_DRIVER=redis
REDIS_HOST=redis-server
REDIS_PASSWORD=redis_password

# Session (Production)
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true

# Email (Production)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreply@ksp-lamgabejaya.com
MAIL_PASSWORD=secure_app_password

# Security (Production)
APP_KEY=base64:production-key-here-32-chars
JWT_SECRET=production-jwt-secret-here-64-chars

# Performance (Production)
OPCACHE_ENABLE=1
OPCACHE_MEMORY_CONSUMPTION=256
OPCACHE_MAX_ACCELERATED_FILES=4000
```

---

## 🔧 Troubleshooting

### **Common Issues**

#### **Database Connection Issues**
```bash
# Check MySQL service
sudo systemctl status mysql

# Check MySQL socket
ls -la /var/run/mysqld/mysqld.sock

# Test connection
mysql -h localhost -u root -p

# Check database exists
mysql -u root -p -e "SHOW DATABASES;"
```

#### **Permission Issues**
```bash
# Fix file permissions
sudo chown -R www-data:www-data /var/www/ksp-lamgabejaya
sudo chmod -R 755 /var/www/ksp-lamgabejaya
sudo chmod -R 777 /var/www/ksp-lamgabejaya/storage
sudo chmod -R 777 /var/www/ksp-lamgabejaya/logs
```

#### **Cache Issues**
```bash
# Clear Redis cache
redis-cli FLUSHALL

# Clear application cache
rm -rf storage/cache/*
rm -rf storage/framework/cache/*

# Restart services
sudo systemctl restart redis
sudo systemctl restart php7.4-fpm
sudo systemctl restart nginx
```

#### **Log Analysis**
```bash
# Check error logs
tail -f logs/error.log

# Check access logs
tail -f /var/log/nginx/access.log

# Check MySQL slow queries
tail -f /var/log/mysql/slow.log

# Check system logs
sudo journalctl -u nginx -f
```

### **Performance Monitoring**

#### **System Monitoring Script**
```bash
#!/bin/bash
# scripts/monitor.sh

echo "=== System Status ==="
echo "CPU Usage: $(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | awk -F'%' '{print $1}')"
echo "Memory Usage: $(free -m | awk 'NR==2{printf "%.2f%%", $3*100/$2}')"
echo "Disk Usage: $(df -h / | awk 'NR==2{print $5}')"

echo ""
echo "=== Service Status ==="
echo "Nginx: $(systemctl is-active nginx)"
echo "PHP-FPM: $(systemctl is-active php7.4-fpm)"
echo "MySQL: $(systemctl is-active mysql)"
echo "Redis: $(systemctl is-active redis)"

echo ""
echo "=== Application Status ==="
echo "Cache: $(redis-cli ping 2>/dev/null || echo 'DOWN')"
echo "Database: $(mysqladmin ping 2>/dev/null | grep -c 'alive' || echo 'DOWN')"
echo "Disk Space: $(df -h /var/www/ksp-lamgabejaya | awk 'NR==2{print $4}')"
```

---

## 📚 Configuration Checklist

### **Pre-Deployment Checklist**

#### **Security**
- [ ] Change default passwords
- [ ] Generate secure APP_KEY and JWT_SECRET
- [ ] Configure HTTPS certificates
- [ ] Set proper file permissions
- [ ] Enable security headers
- [ ] Configure firewall rules

#### **Performance**
- [ ] Enable OPcache
- [ ] Configure Redis caching
- [ ] Optimize database indexes
- [ ] Enable gzip compression
- [ ] Configure CDN for static assets
- [ ] Set up monitoring

#### **Backup**
- [ ] Configure automated database backups
- [ ] Set up file backup strategy
- [ ] Test backup restoration
- [ ] Configure backup retention policy
- [ ] Set up off-site backup

#### **Logging**
- [ ] Configure log rotation
- [ ] Set up error monitoring
- [ ] Configure audit logging
- [ ] Set up log analysis tools
- [ ] Configure alerting

---

**🎯 **Configuration Guide ini menyediakan panduan lengkap untuk setup dan konfigurasi aplikasi KSP Lam Gabe Jaya dengan best practices untuk keamanan, performa, dan maintainability!**
