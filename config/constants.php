<?php
/**
 * KSP Lam Gabe Jaya - Application Constants
 * 100% English PHP Constants and Variables
 */

// Application Configuration
define('APP_NAME', 'KSP Lam Gabe Jaya');
define('APP_VERSION', '2.0.0');
define('APP_ENVIRONMENT', 'production');
define('APP_DEBUG', false);
define('APP_URL', 'http://localhost/mono-v2');
define('APP_TIMEZONE', 'Asia/Jakarta');

// Security Configuration
define('JWT_SECRET', 'your-super-secret-jwt-key-change-in-production');
define('JWT_ALGORITHM', 'HS256');
define('TOKEN_EXPIRY', 3600); // 1 hour in seconds
define('REFRESH_TOKEN_EXPIRY', 604800); // 7 days in seconds
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ksp_lamgabejaya_v2');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// File Upload Configuration
define('UPLOAD_MAX_SIZE', 5242880); // 5MB in bytes
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');

// Email Configuration
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'noreply@ksplamgabejaya.co.id');
define('MAIL_PASSWORD', 'your-mail-password');
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM_NAME', APP_NAME);

// Business Logic Constants
define('LOAN_INTEREST_RATE', 1.00); // 1% per month
define('LOAN_MAX_AMOUNT_MULTIPLIER', 10); // Max 10x savings
define('LOAN_MAX_TERM_MONTHS', 12);
define('MANDATORY_SAVINGS_AMOUNT', 10000); // Rp 10,000 per day
define('SAVINGS_INTEREST_RATE', 0.50); // 0.5% per month
define('LATE_PAYMENT_PENALTY_RATE', 0.10); // 10% penalty

// Pagination Configuration
define('DEFAULT_PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);

// Date/Time Formats
define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd F Y');
define('DISPLAY_DATETIME_FORMAT', 'd F Y H:i:s');

// Currency Configuration
define('CURRENCY_CODE', 'IDR');
define('CURRENCY_SYMBOL', 'Rp');
define('CURRENCY_DECIMAL_PLACES', 0);

// Status Options
define('USER_ROLES', ['super_admin', 'admin', 'mantri', 'member']);
define('LOAN_STATUSES', ['pending', 'approved', 'rejected', 'disbursed', 'completed', 'defaulted']);
define('TRANSACTION_TYPES', ['deposit', 'withdrawal', 'loan_payment', 'loan_disbursement', 'interest_payment']);
define('TRANSACTION_STATUSES', ['pending', 'completed', 'failed', 'cancelled']);
define('SAVINGS_TYPES', ['mandatory', 'voluntary', 'fixed_deposit']);

// API Configuration
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // requests per hour
define('API_TIMEOUT', 30); // seconds

// Cache Configuration
define('CACHE_DRIVER', 'file');
define('CACHE_PREFIX', 'ksp_');
define('CACHE_DEFAULT_TTL', 3600); // 1 hour

// Logging Configuration
define('LOG_LEVEL', 'error');
define('LOG_PATH', __DIR__ . '/../logs/');
define('LOG_MAX_FILES', 30);

// Validation Rules
define('VALIDATION_RULES', [
    'name' => [
        'required' => true,
        'min_length' => 3,
        'max_length' => 255,
        'pattern' => '/^[a-zA-Z\s\-\.,]+$/'
    ],
    'email' => [
        'required' => true,
        'max_length' => 255,
        'pattern' => '/^[^\s@]+@[^\s@]+\.[^\s@]+$/'
    ],
    'phone' => [
        'required' => true,
        'min_length' => 10,
        'max_length' => 15,
        'pattern' => '/^[0-9\-\+\(\)]+$/'
    ],
    'password' => [
        'required' => true,
        'min_length' => 6,
        'max_length' => 255,
        'pattern' => '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{6,}$/'
    ],
    'amount' => [
        'required' => true,
        'min' => 0,
        'max' => 999999999.99,
        'pattern' => '/^\d+(\.\d{1,2})?$/'
    ],
    'id_number' => [
        'required' => true,
        'min_length' => 16,
        'max_length' => 16,
        'pattern' => '/^[0-9]+$/'
    ]
]);

// Error Messages (Indonesian)
define('ERROR_MESSAGES', [
    'general' => 'Terjadi kesalahan. Silakan coba lagi.',
    'validation' => 'Input tidak valid. Silakan periksa kembali.',
    'unauthorized' => 'Anda tidak memiliki akses ke halaman ini.',
    'forbidden' => 'Akses ditolak.',
    'not_found' => 'Data tidak ditemukan.',
    'server_error' => 'Terjadi kesalahan server.',
    'database_error' => 'Terjadi kesalahan database.',
    'file_upload_error' => 'Gagal mengunggah file.',
    'invalid_file_type' => 'Tipe file tidak diizinkan.',
    'file_too_large' => 'Ukuran file terlalu besar.',
    'email_already_exists' => 'Email sudah terdaftar.',
    'invalid_credentials' => 'Email atau kata sandi salah.',
    'account_locked' => 'Akun terkunci. Silakan coba lagi dalam 15 menit.',
    'insufficient_balance' => 'Saldo tidak mencukupi.',
    'loan_already_approved' => 'Pinjaman sudah disetujui.',
    'loan_not_found' => 'Pinjaman tidak ditemukan.',
    'member_not_found' => 'Anggota tidak ditemukan.',
    'transaction_failed' => 'Transaksi gagal.',
    'duplicate_transaction' => 'Transaksi duplikat terdeteksi.',
    'network_error' => 'Terjadi kesalahan jaringan.',
    'timeout' => 'Request timeout. Silakan coba lagi.',
    'maintenance' => 'Sistem sedang dalam pemeliharaan.'
]);

// Success Messages (Indonesian)
define('SUCCESS_MESSAGES', [
    'login' => 'Login berhasil.',
    'logout' => 'Logout berhasil.',
    'register' => 'Registrasi berhasil.',
    'profile_updated' => 'Profil berhasil diperbarui.',
    'password_changed' => 'Kata sandi berhasil diubah.',
    'password_reset_sent' => 'Link reset kata sandi telah dikirim.',
    'member_created' => 'Anggota berhasil ditambahkan.',
    'member_updated' => 'Data anggota berhasil diperbarui.',
    'member_deleted' => 'Anggota berhasil dihapus.',
    'loan_created' => 'Pengajuan pinjaman berhasil dibuat.',
    'loan_approved' => 'Pinjaman berhasil disetujui.',
    'loan_rejected' => 'Pinjaman berhasil ditolak.',
    'loan_disbursed' => 'Pinjaman berhasil dicairkan.',
    'savings_created' => 'Rekening simpanan berhasil dibuat.',
    'savings_updated' => 'Data simpanan berhasil diperbarui.',
    'deposit_successful' => 'Setoran berhasil.',
    'withdrawal_successful' => 'Penarikan berhasil.',
    'payment_successful' => 'Pembayaran berhasil.',
    'transaction_completed' => 'Transaksi berhasil.',
    'data_exported' => 'Data berhasil diekspor.',
    'data_imported' => 'Data berhasil diimpor.',
    'settings_updated' => 'Pengaturan berhasil diperbarui.',
    'email_sent' => 'Email berhasil dikirim.',
    'file_uploaded' => 'File berhasil diunggah.',
    'backup_created' => 'Backup berhasil dibuat.',
    'backup_restored' => 'Backup berhasil dipulihkan.'
]);

// Notification Messages (Indonesian)
define('NOTIFICATION_MESSAGES', [
    'new_loan_application' => 'Ada pengajuan pinjaman baru yang menunggu persetujuan.',
    'loan_approved' => 'Pinjaman Anda telah disetujui.',
    'loan_rejected' => 'Pinjaman Anda ditolak.',
    'loan_disbursed' => 'Pinjaman telah dicairkan.',
    'payment_due' => 'Pembayaran pinjaman Anda jatuh tempo.',
    'payment_overdue' => 'Pembayaran pinjaman Anda terlambat.',
    'low_balance' => 'Saldo rekening Anda rendah.',
    'new_member' => 'Anggota baru telah terdaftar.',
    'system_maintenance' => 'Sistem akan melakukan pemeliharaan.',
    'security_alert' => 'Aktivitas mencurigakan terdeteksi pada akun Anda.'
]);

// System Limits
define('SYSTEM_LIMITS', [
    'max_members_per_page' => 50,
    'max_loans_per_page' => 50,
    'max_transactions_per_page' => 100,
    'max_file_size' => 5242880, // 5MB
    'max_upload_files' => 10,
    'session_timeout' => 3600, // 1 hour
    'password_min_length' => 6,
    'password_max_length' => 255,
    'name_min_length' => 3,
    'name_max_length' => 255,
    'phone_min_length' => 10,
    'phone_max_length' => 15,
    'id_number_length' => 16,
    'loan_min_amount' => 100000,
    'loan_max_amount' => 100000000,
    'deposit_min_amount' => 10000,
    'withdrawal_min_amount' => 10000
]);

// Feature Flags
define('FEATURE_FLAGS', [
    'enable_sms_notifications' => false,
    'enable_email_notifications' => true,
    'enable_file_uploads' => true,
    'enable_data_export' => true,
    'enable_data_import' => false,
    'enable_backup_restore' => true,
    'enable_audit_logs' => true,
    'enable_two_factor_auth' => false,
    'enable_api_access' => true,
    'enable_mobile_app' => false
]);

// API Endpoints
define('API_ENDPOINTS', [
    'base' => APP_URL . '/api/' . API_VERSION,
    'auth' => '/auth',
    'users' => '/users',
    'members' => '/members',
    'loans' => '/loans',
    'savings' => '/savings',
    'transactions' => '/transactions',
    'reports' => '/reports',
    'notifications' => '/notifications',
    'settings' => '/settings'
]);

// Database Tables
define('DB_TABLES', [
    'users' => 'users',
    'members' => 'members',
    'loans' => 'loans',
    'savings' => 'savings',
    'transactions' => 'transactions',
    'loan_payments' => 'loan_payments',
    'savings_deposits' => 'savings_deposits',
    'notifications' => 'notifications',
    'audit_logs' => 'audit_logs',
    'settings' => 'settings',
    'login_attempts' => 'login_attempts',
    'password_resets' => 'password_resets',
    'migrations' => 'migrations'
]);

// Report Types
define('REPORT_TYPES', [
    'daily' => 'Harian',
    'weekly' => 'Mingguan',
    'monthly' => 'Bulanan',
    'yearly' => 'Tahunan',
    'custom' => 'Kustom'
]);

// Export Formats
define('EXPORT_FORMATS', [
    'pdf' => 'PDF',
    'excel' => 'Excel',
    'csv' => 'CSV',
    'json' => 'JSON'
]);

// Time Periods
define('TIME_PERIODS', [
    'today' => 'Hari Ini',
    'yesterday' => 'Kemarin',
    'this_week' => 'Minggu Ini',
    'last_week' => 'Minggu Lalu',
    'this_month' => 'Bulan Ini',
    'last_month' => 'Bulan Lalu',
    'this_year' => 'Tahun Ini',
    'last_year' => 'Tahun Lalu',
    'custom' => 'Kustom'
]);

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Set error reporting based on environment
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', 0);
}

// Set session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', APP_ENVIRONMENT === 'production');
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Set upload configuration
ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '6M');
ini_set('max_execution_time', 300);

// Set memory limit
ini_set('memory_limit', '256M');

// Log configuration
ini_set('log_errors', 1);
ini_set('error_log', LOG_PATH . 'error.log');

// Return constants array for easy access
return [
    'app' => [
        'name' => APP_NAME,
        'version' => APP_VERSION,
        'environment' => APP_ENVIRONMENT,
        'debug' => APP_DEBUG,
        'url' => APP_URL,
        'timezone' => APP_TIMEZONE
    ],
    'security' => [
        'jwt_secret' => JWT_SECRET,
        'jwt_algorithm' => JWT_ALGORITHM,
        'token_expiry' => TOKEN_EXPIRY,
        'refresh_token_expiry' => REFRESH_TOKEN_EXPIRY,
        'session_lifetime' => SESSION_LIFETIME,
        'max_login_attempts' => MAX_LOGIN_ATTEMPTS,
        'login_lockout_time' => LOGIN_LOCKOUT_TIME
    ],
    'database' => [
        'host' => DB_HOST,
        'name' => DB_NAME,
        'user' => DB_USER,
        'password' => DB_PASS,
        'charset' => DB_CHARSET,
        'collation' => DB_COLLATION
    ],
    'business' => [
        'loan_interest_rate' => LOAN_INTEREST_RATE,
        'loan_max_amount_multiplier' => LOAN_MAX_AMOUNT_MULTIPLIER,
        'loan_max_term_months' => LOAN_MAX_TERM_MONTHS,
        'mandatory_savings_amount' => MANDATORY_SAVINGS_AMOUNT,
        'savings_interest_rate' => SAVINGS_INTEREST_RATE,
        'late_payment_penalty_rate' => LATE_PAYMENT_PENALTY_RATE
    ],
    'limits' => SYSTEM_LIMITS,
    'features' => FEATURE_FLAGS,
    'messages' => [
        'errors' => ERROR_MESSAGES,
        'success' => SUCCESS_MESSAGES,
        'notifications' => NOTIFICATION_MESSAGES
    ]
];
?>
