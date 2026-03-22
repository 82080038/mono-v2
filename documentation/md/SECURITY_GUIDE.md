# Security Guide

## 🎯 Overview

Dokumentasi lengkap untuk keamanan aplikasi KSP Lam Gabe Jaya. Guide ini mencakup best practices keamanan, implementasi autentikasi, proteksi data, audit trail, dan compliance dengan standar keamanan.

## 📋 Table of Contents

- [Security Principles](#security-principles)
- [Authentication & Authorization](#authentication--authorization)
- [Data Protection](#data-protection)
- [Input Validation](#input-validation)
- [Session Security](#session-security)
- [API Security](#api-security)
- [Database Security](#database-security)
- [File Upload Security](#file-upload-security)
- [Cross-Site Scripting (XSS)](#cross-site-scripting-xss)
- [Cross-Site Request Forgery (CSRF)](#cross-site-request-forgery-csrf)
- [SQL Injection Protection](#sql-injection-protection)
- [Audit & Logging](#audit--logging)
- [Security Headers](#security-headers)
- [Compliance & Standards](#compliance--standards)
- [Security Monitoring](#security-monitoring)

---

## 🛡️ Security Principles

### **Core Security Principles**

#### **Principle of Least Privilege**
```php
<?php
// Only grant necessary permissions
class RoleBasedAccess {
    private static $permissions = [
        'admin' => ['create', 'read', 'update', 'delete', 'manage_users', 'manage_system'],
        'manager' => ['create', 'read', 'update', 'approve_loans', 'view_reports'],
        'staff' => ['read', 'create_transaction', 'update_member'],
        'member' => ['read_own', 'update_own']
    ];
    
    public static function hasPermission($role, $permission) {
        return in_array($permission, self::$permissions[$role] ?? []);
    }
    
    public static function checkPermission($role, $permission) {
        if (!self::hasPermission($role, $permission)) {
            throw new SecurityException("Insufficient permissions for action: $permission");
        }
    }
}
?>
```

#### **Defense in Depth**
```php
<?php
// Multiple layers of security
class SecurityLayer {
    public static function authenticate($credentials) {
        // Layer 1: Input validation
        self::validateCredentials($credentials);
        
        // Layer 2: Rate limiting
        self::checkRateLimit($credentials['username']);
        
        // Layer 3: Account lockout
        self::checkAccountLockout($credentials['username']);
        
        // Layer 4: Password verification
        return self::verifyPassword($credentials);
    }
    
    private static function validateCredentials($credentials) {
        if (empty($credentials['username']) || empty($credentials['password'])) {
            throw new SecurityException('Username and password required');
        }
        
        if (strlen($credentials['username']) > 50) {
            throw new SecurityException('Invalid username format');
        }
    }
}
?>
```

#### **Zero Trust Architecture**
```php
<?php
// Never trust, always verify
class ZeroTrust {
    public static function verifyRequest($request) {
        // Verify every request, even from authenticated users
        self::verifyToken($request->token);
        self::verifyIP($request->ip);
        self::verifyUserAgent($request->userAgent);
        self::verifySession($request->sessionId);
        self::verifyPermissions($request->userId, $request->action);
    }
    
    private static function verifyToken($token) {
        if (!JWT::validate($token)) {
            throw new SecurityException('Invalid or expired token');
        }
    }
}
?>
```

---

## 🔐 Authentication & Authorization

### **Password Security**

#### **Password Hashing**
```php
<?php
class PasswordSecurity {
    private static $algo = PASSWORD_ARGON2ID;
    private static $options = [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ];
    
    public static function hash($password) {
        if (strlen($password) < 8) {
            throw new SecurityException('Password must be at least 8 characters');
        }
        
        return password_hash($password, self::$algo, self::$options);
    }
    
    public static function verify($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public static function needsRehash($hash) {
        return password_needs_rehash($hash, self::$algo, self::$options);
    }
    
    public static function generateStrongPassword($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle($chars), 0, $length);
    }
}
?>
```

#### **Password Policy**
```php
<?php
class PasswordPolicy {
    private static $requirements = [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => true,
        'forbidden_patterns' => ['password', '123456', 'qwerty'],
        'max_consecutive_chars' => 3
    ];
    
    public static function validate($password) {
        $errors = [];
        
        // Length check
        if (strlen($password) < self::$requirements['min_length']) {
            $errors[] = "Password must be at least " . self::$requirements['min_length'] . " characters";
        }
        
        // Uppercase check
        if (self::$requirements['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        // Lowercase check
        if (self::$requirements['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        // Numbers check
        if (self::$requirements['require_numbers'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        // Symbols check
        if (self::$requirements['require_symbols'] && !preg_match('/[!@#$%^&*]/', $password)) {
            $errors[] = "Password must contain at least one symbol";
        }
        
        // Forbidden patterns
        foreach (self::$requirements['forbidden_patterns'] as $pattern) {
            if (stripos($password, $pattern) !== false) {
                $errors[] = "Password cannot contain common patterns like '$pattern'";
            }
        }
        
        // Consecutive characters
        if (preg_match('/(.)\1{' . (self::$requirements['max_consecutive_chars'] - 1) . ',}/', $password)) {
            $errors[] = "Password cannot contain more than " . self::$requirements['max_consecutive_chars'] . " consecutive identical characters";
        }
        
        return $errors;
    }
}
?>
```

### **JWT Authentication**

#### **JWT Implementation**
```php
<?php
class JWTAuth {
    private static $secretKey;
    private static $algorithm = 'HS256';
    private static $issuer = 'ksp-lamgabejaya';
    private static $audience = 'ksp-lamgabejaya-app';
    
    public static function generateToken($payload) {
        $header = [
            'alg' => self::$algorithm,
            'typ' => 'JWT'
        ];
        
        $payload['iss'] = self::$issuer;
        $payload['aud'] = self::$audience;
        $payload['iat'] = time();
        $payload['exp'] = time() + 3600; // 1 hour
        $payload['jti'] = bin2hex(random_bytes(16)); // JWT ID
        
        return self::encode($header, $payload);
    }
    
    public static function validateToken($token) {
        try {
            $payload = self::decode($token);
            
            // Check expiration
            if (time() > $payload['exp']) {
                throw new SecurityException('Token expired');
            }
            
            // Check issuer and audience
            if ($payload['iss'] !== self::$issuer || $payload['aud'] !== self::$audience) {
                throw new SecurityException('Invalid token issuer or audience');
            }
            
            // Check if token is blacklisted
            if (self::isTokenBlacklisted($payload['jti'])) {
                throw new SecurityException('Token has been revoked');
            }
            
            return $payload;
            
        } catch (Exception $e) {
            throw new SecurityException('Invalid token: ' . $e->getMessage());
        }
    }
    
    public static function refreshToken($token) {
        $payload = self::validateToken($token);
        
        // Check if token is in refresh window (last 15 minutes)
        if (time() < $payload['exp'] - 900) {
            throw new SecurityException('Token not eligible for refresh');
        }
        
        // Blacklist old token
        self::blacklistToken($payload['jti']);
        
        // Generate new token
        unset($payload['iat'], $payload['exp'], $payload['jti']);
        return self::generateToken($payload);
    }
    
    private static function encode($header, $payload) {
        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", self::$secretKey, true);
        $signatureEncoded = self::base64UrlEncode($signature);
        
        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }
    
    private static function decode($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }
        
        $header = json_decode(self::base64UrlDecode($parts[0]), true);
        $payload = json_decode(self::base64UrlDecode($parts[1]), true);
        $signature = self::base64UrlDecode($parts[2]);
        
        // Verify signature
        $expectedSignature = hash_hmac('sha256', "$parts[0].$parts[1]", self::$secretKey, true);
        if (!hash_equals($signature, $expectedSignature)) {
            throw new Exception('Invalid signature');
        }
        
        return $payload;
    }
    
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
    
    private static function isTokenBlacklisted($jti) {
        // Check Redis or database for blacklisted tokens
        return false; // Implement based on your storage
    }
    
    private static function blacklistToken($jti) {
        // Add token to blacklist with expiration time
        // Implement based on your storage
    }
}
?>
```

### **Two-Factor Authentication**

#### **2FA Implementation**
```php
<?php
class TwoFactorAuth {
    private static $issuer = 'KSP Lam Gabe Jaya';
    
    public static function generateSecret($user) {
        $secret = random_bytes(16);
        $encodedSecret = self::base32Encode($secret);
        
        // Store secret for user
        self::storeUserSecret($user['id'], $encodedSecret);
        
        return $encodedSecret;
    }
    
    public static function generateQRCode($user, $secret) {
        $appName = urlencode(self::$issuer);
        $userName = urlencode($user['email']);
        $issuer = urlencode(self::$issuer);
        
        $qrData = "otpauth://totp/{$appName}:{$userName}?secret={$secret}&issuer={$issuer}";
        
        return self::generateQRCodeImage($qrData);
    }
    
    public static function verifyCode($user, $code) {
        $secret = self::getUserSecret($user['id']);
        
        if (!$secret) {
            throw new SecurityException('2FA not configured for user');
        }
        
        // Verify TOTP code
        return self::verifyTOTP($secret, $code);
    }
    
    private static function verifyTOTP($secret, $code) {
        $time = floor(time() / 30);
        
        // Check current and adjacent time windows (±1)
        for ($i = -1; $i <= 1; $i++) {
            $calculatedCode = self::generateTOTP($secret, $time + $i);
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }
        
        return false;
    }
    
    private static function generateTOTP($secret, $time) {
        $binarySecret = self::base32Decode($secret);
        $binaryTime = pack('N*', $time);
        $binaryTime = str_pad($binaryTime, 8, "\x00", STR_PAD_LEFT);
        
        $hash = hash_hmac('sha1', $binaryTime, $binarySecret, true);
        $offset = ord($hash[19]) & 0x0f;
        
        $code = (
            ((ord($hash[$offset + 0]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;
        
        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }
    
    private static function base32Encode($data) {
        $base32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        
        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }
        
        $binary = str_pad($binary, strlen($binary) + ((5 - strlen($binary) % 5) % 5), '0', STR_PAD_RIGHT);
        $encoded = '';
        
        for ($i = 0; $i < strlen($binary); $i += 5) {
            $chunk = substr($binary, $i, 5);
            $index = bindec($chunk);
            $encoded .= $base32[$index];
        }
        
        return $encoded;
    }
    
    private static function base32Decode($data) {
        $base32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        
        foreach (str_split($data) as $char) {
            $index = strpos($base32, $char);
            $binary .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }
        
        $binary = substr($binary, 0, - (strlen($binary) % 8));
        $decoded = '';
        
        for ($i = 0; $i < strlen($binary); $i += 8) {
            $chunk = substr($binary, $i, 8);
            $decoded .= chr(bindec($chunk));
        }
        
        return $decoded;
    }
}
?>
```

---

## 🔒 Data Protection

### **Encryption**

#### **Data Encryption Class**
```php
<?php
class DataEncryption {
    private static $method = 'AES-256-GCM';
    private static $key;
    
    public static function init() {
        self::$key = hash('sha256', Environment::get('ENCRYPTION_KEY'), true);
    }
    
    public static function encrypt($data) {
        if (!self::$key) {
            self::init();
        }
        
        $iv = random_bytes(openssl_cipher_iv_length(self::$method));
        $tag = '';
        
        $encrypted = openssl_encrypt(
            $data,
            self::$method,
            self::$key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            16
        );
        
        return base64_encode($iv . $tag . $encrypted);
    }
    
    public static function decrypt($encryptedData) {
        if (!self::$key) {
            self::init();
        }
        
        $data = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length(self::$method);
        $iv = substr($data, 0, $ivLength);
        $tag = substr($data, $ivLength, 16);
        $encrypted = substr($data, $ivLength + 16);
        
        $decrypted = openssl_decrypt(
            $encrypted,
            self::$method,
            self::$key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        if ($decrypted === false) {
            throw new SecurityException('Decryption failed');
        }
        
        return $decrypted;
    }
    
    public static function encryptField($value) {
        return empty($value) ? null : self::encrypt($value);
    }
    
    public static function decryptField($encryptedValue) {
        return empty($encryptedValue) ? null : self::decrypt($encryptedValue);
    }
}
?>
```

#### **Sensitive Data Handling**
```php
<?php
class SensitiveDataHandler {
    private static $sensitiveFields = [
        'nik',
        'phone',
        'email',
        'bank_account',
        'credit_card'
    ];
    
    public static function encryptSensitiveData($data) {
        foreach ($data as $key => $value) {
            if (in_array($key, self::$sensitiveFields)) {
                $data[$key] = DataEncryption::encrypt($value);
            }
        }
        
        return $data;
    }
    
    public static function decryptSensitiveData($data) {
        foreach ($data as $key => $value) {
            if (in_array($key, self::$sensitiveFields)) {
                $data[$key] = DataEncryption::decrypt($value);
            }
        }
        
        return $data;
    }
    
    public static function maskData($data, $field) {
        if (!isset($data[$field])) {
            return $data;
        }
        
        $value = $data[$field];
        $length = strlen($value);
        
        switch ($field) {
            case 'nik':
                $data[$field] = substr($value, 0, 6) . str_repeat('*', 10);
                break;
            case 'phone':
                $data[$field] = substr($value, 0, 3) . str_repeat('*', $length - 6) . substr($value, -3);
                break;
            case 'email':
                $parts = explode('@', $value);
                $username = $parts[0];
                $domain = $parts[1];
                $data[$field] = substr($username, 0, 2) . str_repeat('*', strlen($username) - 2) . '@' . $domain;
                break;
            default:
                $data[$field] = str_repeat('*', $length);
        }
        
        return $data;
    }
}
?>
```

---

## ✅ Input Validation

### **Comprehensive Validation**

#### **Input Validator Class**
```php
<?php
class InputValidator {
    private static $rules = [
        'name' => [
            'required' => true,
            'max_length' => 255,
            'pattern' => '/^[a-zA-Z\s\.]+$/'
        ],
        'email' => [
            'required' => true,
            'max_length' => 255,
            'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
        ],
        'phone' => [
            'required' => false,
            'pattern' => '/^[0-9+\-\s()]+$/',
            'min_length' => 10,
            'max_length' => 20
        ],
        'nik' => [
            'required' => true,
            'pattern' => '/^[0-9]{16}$/'
        ],
        'amount' => [
            'required' => true,
            'type' => 'numeric',
            'min' => 0,
            'max' => 999999999999
        ]
    ];
    
    public static function validate($data, $rules = null) {
        $errors = [];
        $rules = $rules ?? self::$rules;
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            
            // Required validation
            if ($fieldRules['required'] && empty($value)) {
                $errors[$field] = "$field is required";
                continue;
            }
            
            if (empty($value)) {
                continue; // Skip other validations for empty optional fields
            }
            
            // Type validation
            if (isset($fieldRules['type'])) {
                if ($fieldRules['type'] === 'numeric' && !is_numeric($value)) {
                    $errors[$field] = "$field must be numeric";
                }
            }
            
            // Length validation
            if (isset($fieldRules['min_length']) && strlen($value) < $fieldRules['min_length']) {
                $errors[$field] = "$field must be at least {$fieldRules['min_length']} characters";
            }
            
            if (isset($fieldRules['max_length']) && strlen($value) > $fieldRules['max_length']) {
                $errors[$field] = "$field must not exceed {$fieldRules['max_length']} characters";
            }
            
            // Pattern validation
            if (isset($fieldRules['pattern']) && !preg_match($fieldRules['pattern'], $value)) {
                $errors[$field] = "$field format is invalid";
            }
            
            // Range validation
            if (isset($fieldRules['min']) && is_numeric($value) && $value < $fieldRules['min']) {
                $errors[$field] = "$field must be at least {$fieldRules['min']}";
            }
            
            if (isset($fieldRules['max']) && is_numeric($value) && $value > $fieldRules['max']) {
                $errors[$field] = "$field must not exceed {$fieldRules['max']}";
            }
        }
        
        return $errors;
    }
    
    public static function sanitize($data) {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
                $data[$key] = stripslashes($value);
                $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        
        return $data;
    }
    
    public static function validateFile($file, $allowedTypes = [], $maxSize = 5242880) {
        $errors = [];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['file'] = self::getUploadErrorMessage($file['error']);
            return $errors;
        }
        
        // File size validation
        if ($file['size'] > $maxSize) {
            $errors['file'] = "File size must not exceed " . ($maxSize / 1024 / 1024) . "MB";
        }
        
        // File type validation
        if (!empty($allowedTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $errors['file'] = "File type not allowed";
            }
        }
        
        return $errors;
    }
    
    private static function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return "File exceeds upload_max_filesize directive";
            case UPLOAD_ERR_FORM_SIZE:
                return "File exceeds MAX_FILE_SIZE directive";
            case UPLOAD_ERR_PARTIAL:
                return "File was only partially uploaded";
            case UPLOAD_ERR_NO_FILE:
                return "No file was uploaded";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Missing temporary folder";
            case UPLOAD_ERR_CANT_WRITE:
                return "Failed to write file to disk";
            case UPLOAD_ERR_EXTENSION:
                return "File upload stopped by extension";
            default:
                return "Unknown upload error";
        }
    }
}
?>
```

---

## 🔄 Session Security

### **Secure Session Management**

#### **Session Security Class**
```php
<?php
class SecureSession {
    private static $sessionName = 'ksp_session';
    private static $sessionTimeout = 7200; // 2 hours
    private static $regenerateInterval = 300; // 5 minutes
    
    public static function start() {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', Environment::isProduction());
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        ini_set('session.gc_maxlifetime', self::$sessionTimeout);
        
        session_name(self::$sessionName);
        session_start();
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > self::$regenerateInterval) {
            self::regenerate();
        }
        
        // Validate session
        self::validateSession();
    }
    
    public static function regenerate() {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    }
    
    public static function validateSession() {
        if (!isset($_SESSION['ip_address']) || !isset($_SESSION['user_agent'])) {
            self::destroy();
            throw new SecurityException('Invalid session');
        }
        
        if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
            self::destroy();
            throw new SecurityException('Session IP address mismatch');
        }
        
        if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            self::destroy();
            throw new SecurityException('Session user agent mismatch');
        }
        
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity']) > self::$sessionTimeout) {
            self::destroy();
            throw new SecurityException('Session expired');
        }
        
        $_SESSION['last_activity'] = time();
    }
    
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    public static function destroy() {
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        session_destroy();
    }
    
    public static function isExpired() {
        return isset($_SESSION['last_activity']) && 
               (time() - $_SESSION['last_activity']) > self::$sessionTimeout;
    }
}
?>
```

---

## 🔐 API Security

### **API Security Implementation**

#### **API Authentication Middleware**
```php
<?php
class APISecurity {
    private static $rateLimitWindow = 60; // 1 minute
    private static $rateLimitMax = 100; // 100 requests per minute
    
    public static function authenticate($request) {
        // Check API key
        $apiKey = $request->getHeader('X-API-Key');
        if (!self::validateAPIKey($apiKey)) {
            throw new SecurityException('Invalid API key');
        }
        
        // Check JWT token
        $token = $request->getHeader('Authorization');
        if (!$token) {
            throw new SecurityException('Authorization header required');
        }
        
        $token = str_replace('Bearer ', '', $token);
        $payload = JWTAuth::validateToken($token);
        
        return $payload;
    }
    
    public static function rateLimit($clientId) {
        $key = "rate_limit:$clientId";
        $current = Redis::get($key) ?? 0;
        
        if ($current >= self::$rateLimitMax) {
            throw new SecurityException('Rate limit exceeded');
        }
        
        Redis::setex($key, self::$rateLimitWindow, $current + 1);
        
        // Set rate limit headers
        header('X-RateLimit-Limit: ' . self::$rateLimitMax);
        header('X-RateLimit-Remaining: ' . (self::$rateLimitMax - $current - 1));
        header('X-RateLimit-Reset: ' . (time() + self::$rateLimitWindow));
    }
    
    public static function validateInput($data) {
        // Remove potential XSS
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        
        return $data;
    }
    
    public static function logAPIRequest($request, $response, $userId = null) {
        $logData = [
            'method' => $request->getMethod(),
            'endpoint' => $request->getEndpoint(),
            'ip_address' => $request->getIP(),
            'user_agent' => $request->getUserAgent(),
            'user_id' => $userId,
            'status_code' => $response->getStatusCode(),
            'response_time' => $response->getResponseTime(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        Logger::info('API Request', $logData, 'api');
    }
    
    private static function validateAPIKey($apiKey) {
        // Implement API key validation
        return true;
    }
}
?>
```

---

## 🗄️ Database Security

### **Database Security Measures**

#### **Secure Database Class**
```php
<?php
class SecureDatabase {
    private $connection;
    private $preparedStatements = [];
    
    public function __construct() {
        $this->connection = new PDO(
            "mysql:host=" . Environment::get('DB_HOST') . 
            ";dbname=" . Environment::get('DB_DATABASE'),
            Environment::get('DB_USERNAME'),
            Environment::get('DB_PASSWORD'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_SSL => Environment::get('DB_SSL', false),
                PDO::MYSQL_ATTR_MULTI_STATEMENTS => false
            ]
        );
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // Log error without exposing sensitive information
            Logger::error('Database query failed', [
                'error_code' => $e->getCode(),
                'sql' => $this->sanitizeSQL($sql)
            ], 'database');
            
            throw new DatabaseException('Query failed');
        }
    }
    
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $values = array_values($data);
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->query($sql, $values);
        
        return $this->connection->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        $values = [];
        
        foreach ($data as $column => $value) {
            $setParts[] = "$column = ?";
            $values[] = $value;
        }
        
        $setClause = implode(', ', $setParts);
        $values = array_merge($values, $whereParams);
        
        $sql = "UPDATE $table SET $setClause WHERE $where";
        $this->query($sql, $values);
        
        return $stmt->rowCount();
    }
    
    private function sanitizeSQL($sql) {
        // Remove sensitive data from SQL for logging
        return preg_replace('/(\'[^\']*\')/', "'*****'", $sql);
    }
}
?>
```

---

## 📁 File Upload Security

### **Secure File Upload**

#### **File Upload Security Class**
```php
<?php
class SecureFileUpload {
    private static $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    private static $allowedExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'
    ];
    
    private static $maxFileSize = 5242880; // 5MB
    
    public static function upload($file, $destination, $allowedTypes = null) {
        $errors = [];
        
        // Basic validation
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = self::getUploadErrorMessage($file['error']);
            return ['success' => false, 'errors' => $errors];
        }
        
        // File size validation
        if ($file['size'] > self::$maxFileSize) {
            $errors[] = 'File size exceeds maximum limit';
        }
        
        // MIME type validation
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = $allowedTypes ?? self::$allowedMimeTypes;
        if (!in_array($mimeType, $allowedMimes)) {
            $errors[] = 'File type not allowed';
        }
        
        // Extension validation
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::$allowedExtensions)) {
            $errors[] = 'File extension not allowed';
        }
        
        // Validate that MIME type matches extension
        if (!self::validateMimeExtensionMatch($mimeType, $extension)) {
            $errors[] = 'File extension does not match file type';
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Generate secure filename
        $filename = self::generateSecureFilename($file['name']);
        $filepath = $destination . '/' . $filename;
        
        // Move file to destination
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $errors[] = 'Failed to move uploaded file';
            return ['success' => false, 'errors' => $errors];
        }
        
        // Set secure permissions
        chmod($filepath, 0644);
        
        // Scan for malware
        if (self::scanForMalware($filepath)) {
            unlink($filepath);
            $errors[] = 'File contains malicious content';
            return ['success' => false, 'errors' => $errors];
        }
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'mime_type' => $mimeType,
            'size' => $file['size']
        ];
    }
    
    private static function generateSecureFilename($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Sanitize filename
        $basename = preg_replace('/[^a-zA-Z0-9]/', '_', $basename);
        $basename = substr($basename, 0, 50); // Limit length
        
        // Add random suffix
        $random = bin2hex(random_bytes(8));
        
        return $basename . '_' . $random . '.' . $extension;
    }
    
    private static function validateMimeExtensionMatch($mimeType, $extension) {
        $mimeMap = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif'],
            'application/pdf' => ['pdf'],
            'application/msword' => ['doc'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx']
        ];
        
        return in_array($extension, $mimeMap[$mimeType] ?? []);
    }
    
    private static function scanForMalware($filepath) {
        // Basic malware scanning - implement based on your security solution
        $content = file_get_contents($filepath);
        
        // Check for suspicious patterns
        $suspiciousPatterns = [
            '/<\?php/i',
            '/<script/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload=/i',
            '/onerror=/i'
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    private static function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File exceeds upload_max_filesize directive';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds MAX_FILE_SIZE directive';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
}
?>
```

---

## 🛡️ Cross-Site Scripting (XSS)

### **XSS Protection**

#### **XSS Protection Class**
```php
<?php
class XSSProtection {
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        
        // Remove potentially dangerous characters
        $input = trim($input);
        
        // Convert special characters to HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Remove any remaining script tags
        $input = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $input);
        
        // Remove event handlers
        $input = preg_replace('/on\w+\s*=\s*["\']?[^"\']*["\']?/i', '', $input);
        
        // Remove javascript: protocol
        $input = preg_replace('/javascript\s*:/i', '', $input);
        
        // Remove data: protocol
        $input = preg_replace('/data\s*:/i', '', $input);
        
        return $input;
    }
    
    public static function cleanHTML($html) {
        // Allow only safe HTML tags
        $allowedTags = '<p><br><strong><em><u><ol><ul><li><a><h1><h2><h3><h4><h5><h6>';
        
        // Strip dangerous tags
        $html = strip_tags($html, $allowedTags);
        
        // Remove dangerous attributes
        $html = preg_replace('/\s*on\w+\s*=\s*["\']?[^"\']*["\']?/i', '', $html);
        
        // Remove javascript: and data: protocols
        $html = preg_replace('/(javascript|data)\s*:/i', '', $html);
        
        return $html;
    }
    
    public static function output($data) {
        if (is_array($data) || is_object($data)) {
            header('Content-Type: application/json');
            echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP);
        } else {
            echo self::sanitize($data);
        }
    }
    
    public static function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
?>
```

---

## 🔒 Cross-Site Request Forgery (CSRF)

### **CSRF Protection**

#### **CSRF Protection Class**
```php
<?php
class CSRFProtection {
    private static $tokenName = '_token';
    private static $tokenLength = 32;
    private static $tokenExpiry = 7200; // 2 hours
    
    public static function generateToken() {
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_expires']) ||
            time() > $_SESSION['csrf_token_expires']) {
            
            $_SESSION['csrf_token'] = bin2hex(random_bytes(self::$tokenLength));
            $_SESSION['csrf_token_expires'] = time() + self::$tokenExpiry;
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
        return '<input type="hidden" name="' . self::$tokenName . '" value="' . $token . '">';
    }
    
    public static function getHeader() {
        $token = self::generateToken();
        return 'X-CSRF-Token: ' . $token;
    }
    
    public static function validateRequest($request) {
        // Check POST token
        if ($request->getMethod() === 'POST') {
            $token = $request->getPost(self::$tokenName);
            if (!self::validateToken($token)) {
                throw new SecurityException('CSRF token validation failed');
            }
        }
        
        // Check header token
        $headerToken = $request->getHeader('X-CSRF-Token');
        if ($headerToken && !self::validateToken($headerToken)) {
            throw new SecurityException('CSRF header token validation failed');
        }
        
        // Regenerate token after successful validation
        self::regenerateToken();
    }
    
    public static function regenerateToken() {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_expires']);
    }
    
    public static function middleware($request, $next) {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            self::validateRequest($request);
        }
        
        return $next($request);
    }
}
?>
```

---

## 💉 SQL Injection Protection

### **SQL Injection Prevention**

#### **SQL Injection Protection Class**
```php
<?php
class SQLInjectionProtection {
    public static function escape($value) {
        if (is_numeric($value)) {
            return $value;
        }
        
        return addslashes($value);
    }
    
    public static function validateQuery($sql) {
        // Check for dangerous SQL patterns
        $dangerousPatterns = [
            '/\b(DROP|DELETE|UPDATE|INSERT|CREATE|ALTER|EXECUTE|UNION|SELECT)\b/i',
            '/\b(OR|AND)\s+\d+\s*=\s*\d+/i',
            '/\b(OR|AND)\s+["\'][^"\']*["\']\s*=\s*["\'][^"\']*["\']/i',
            '/\b(OR|AND)\s+\w+\s*=\s*\w+/i',
            '/--/',
            '/\/\*/',
            '/\*\//',
            '/;\s*(DROP|DELETE|UPDATE|INSERT|CREATE|ALTER|EXECUTE)/i'
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $sql)) {
                Logger::security('SQL Injection attempt detected', [
                    'query' => $sql,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
                
                throw new SecurityException('Potential SQL injection detected');
            }
        }
        
        return true;
    }
    
    public static function parameterize($query, $params) {
        // Ensure all parameters are properly escaped
        foreach ($params as $key => $value) {
            if (is_string($value)) {
                $params[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        
        return $params;
    }
    
    public static function buildQuery($table, $conditions = [], $orderBy = '', $limit = '') {
        $sql = "SELECT * FROM $table";
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                if (is_numeric($value)) {
                    $whereClause[] = "$column = $value";
                } else {
                    $escapedValue = self::escape($value);
                    $whereClause[] = "$column = '$escapedValue'";
                }
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        if (!empty($orderBy)) {
            $sql .= " ORDER BY $orderBy";
        }
        
        if (!empty($limit)) {
            $sql .= " LIMIT $limit";
        }
        
        return $sql;
    }
}
?>
```

---

## 📝 Audit & Logging

### **Security Audit System**

#### **Audit Logger Class**
```php
<?php
class SecurityAuditLogger {
    private static $logFile = 'logs/security.log';
    
    public static function logAuthentication($event, $userId, $details = []) {
        $logData = [
            'event' => $event,
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s'),
            'details' => $details
        ];
        
        self::writeLog('AUTHENTICATION', $logData);
    }
    
    public static function logAuthorization($event, $userId, $resource, $details = []) {
        $logData = [
            'event' => $event,
            'user_id' => $userId,
            'resource' => $resource,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s'),
            'details' => $details
        ];
        
        self::writeLog('AUTHORIZATION', $logData);
    }
    
    public static function logDataAccess($event, $userId, $resource, $details = []) {
        $logData = [
            'event' => $event,
            'user_id' => $userId,
            'resource' => $resource,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s'),
            'details' => $details
        ];
        
        self::writeLog('DATA_ACCESS', $logData);
    }
    
    public static function logSecurityIncident($incident, $severity, $details = []) {
        $logData = [
            'incident' => $incident,
            'severity' => $severity,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s'),
            'details' => $details
        ];
        
        self::writeLog('SECURITY_INCIDENT', $logData);
    }
    
    public static function logConfigurationChange($event, $userId, $config, $oldValue, $newValue) {
        $logData = [
            'event' => $event,
            'user_id' => $userId,
            'config' => $config,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        self::writeLog('CONFIG_CHANGE', $logData);
    }
    
    private static function writeLog($category, $data) {
        $logEntry = [
            'category' => $category,
            'data' => $data
        ];
        
        $logLine = json_encode($logEntry) . PHP_EOL;
        
        file_put_contents(self::$logFile, $logLine, FILE_APPEND | LOCK_EX);
        
        // Also write to system log for critical events
        if ($category === 'SECURITY_INCIDENT') {
            error_log("SECURITY: " . json_encode($logData));
        }
    }
    
    public static function getSecurityLogs($startDate = null, $endDate = null, $category = null) {
        $logs = [];
        $lines = file(self::$logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $logEntry = json_decode($line, true);
            
            if (!$logEntry) continue;
            
            $logDate = $logEntry['data']['timestamp'];
            
            // Filter by date range
            if ($startDate && $logDate < $startDate) continue;
            if ($endDate && $logDate > $endDate) continue;
            
            // Filter by category
            if ($category && $logEntry['category'] !== $category) continue;
            
            $logs[] = $logEntry;
        }
        
        return $logs;
    }
}
?>
```

---

## 🛡️ Security Headers

### **Security Headers Implementation**

#### **Security Headers Class**
```php
<?php
class SecurityHeaders {
    public static function setAll() {
        self::setXSSProtection();
        self::setContentTypeOptions();
        self::setFrameOptions();
        self::setStrictTransportSecurity();
        self::setContentSecurityPolicy();
        self::setReferrerPolicy();
        self::setPermissionsPolicy();
    }
    
    public static function setXSSProtection() {
        header('X-XSS-Protection: 1; mode=block');
    }
    
    public static function setContentTypeOptions() {
        header('X-Content-Type-Options: nosniff');
    }
    
    public static function setFrameOptions() {
        header('X-Frame-Options: DENY');
    }
    
    public static function setStrictTransportSecurity() {
        if (Environment::isProduction()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }
    
    public static function setContentSecurityPolicy() {
        $policy = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: https:",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'"
        ];
        
        header('Content-Security-Policy: ' . implode('; ', $policy));
    }
    
    public static function setReferrerPolicy() {
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
    
    public static function setPermissionsPolicy() {
        $policy = [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'accelerometer=()'
        ];
        
        header('Permissions-Policy: ' . implode(', ', $policy));
    }
    
    public static function setCustomHeaders() {
        // Hide server information
        header('Server: KSP-Lamgabejaya');
        header('X-Powered-By: PHP');
        
        // Remove PHP version
        header_remove('X-Powered-By');
        
        // Set cache control for dynamic content
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}
?>
```

---

## 📋 Compliance & Standards

### **Security Compliance**

#### **GDPR Compliance**
```php
<?php
class GDPRCompliance {
    public static function anonymizePersonalData($data) {
        $personalFields = ['name', 'email', 'phone', 'address', 'nik'];
        
        foreach ($personalFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = self::anonymizeField($data[$field], $field);
            }
        }
        
        return $data;
    }
    
    public static function anonymizeField($value, $field) {
        $length = strlen($value);
        
        switch ($field) {
            case 'name':
                return substr($value, 0, 1) . str_repeat('*', $length - 1);
            case 'email':
                $parts = explode('@', $value);
                return str_repeat('*', strlen($parts[0])) . '@' . $parts[1];
            case 'phone':
                return substr($value, 0, 3) . str_repeat('*', $length - 6) . substr($value, -3);
            default:
                return str_repeat('*', $length);
        }
    }
    
    public static function logDataProcessing($activity, $userId, $dataTypes, $legalBasis) {
        $logData = [
            'activity' => $activity,
            'user_id' => $userId,
            'data_types' => $dataTypes,
            'legal_basis' => $legalBasis,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        Logger::info('GDPR Data Processing', $logData, 'gdpr');
    }
    
    public static function exportUserData($userId) {
        // Implement user data export functionality
        $userData = [
            'personal_info' => self::getUserPersonalInfo($userId),
            'transactions' => self::getUserTransactions($userId),
            'accounts' => self::getUserAccounts($userId),
            'loans' => self::getUserLoans($userId),
            'audit_logs' => self::getUserAuditLogs($userId)
        ];
        
        return $userData;
    }
    
    public static function deleteUserData($userId) {
        // Implement right to be forgotten
        try {
            // Anonymize instead of delete for audit purposes
            self::anonymizeUserData($userId);
            
            // Log deletion
            self::logDataProcessing('DELETE_USER_DATA', $userId, 
                ['all_personal_data'], 'user_request');
            
            return true;
        } catch (Exception $e) {
            Logger::error('Failed to delete user data', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ], 'gdpr');
            
            return false;
        }
    }
}
?>
```

---

## 🔍 Security Monitoring

### **Security Monitoring System**

#### **Security Monitor Class**
```php
<?php
class SecurityMonitor {
    private static $thresholds = [
        'failed_login_attempts' => 5,
        'suspicious_requests' => 10,
        'file_upload_failures' => 3,
        'sql_injection_attempts' => 1,
        'xss_attempts' => 1
    ];
    
    public static function monitorFailedLogin($username, $ip) {
        $key = "failed_login:$ip:$username";
        $count = Redis::incr($key);
        Redis::expire($key, 300); // 5 minutes
        
        if ($count >= self::$thresholds['failed_login_attempts']) {
            self::triggerAlert('BRUTE_FORCE_ATTACK', [
                'username' => $username,
                'ip' => $ip,
                'attempts' => $count
            ]);
            
            // Block IP temporarily
            self::blockIP($ip, 300);
        }
    }
    
    public static function monitorSuspiciousRequest($request) {
        $key = "suspicious:$request->ip";
        $count = Redis::incr($key);
        Redis::expire($key, 3600); // 1 hour
        
        if ($count >= self::$thresholds['suspicious_requests']) {
            self::triggerAlert('SUSPICIOUS_ACTIVITY', [
                'ip' => $request->ip,
                'user_agent' => $request->userAgent,
                'endpoint' => $request->endpoint,
                'count' => $count
            ]);
        }
    }
    
    public static function monitorFileUpload($file, $result) {
        if (!$result['success']) {
            $key = "upload_fail:" . $_SERVER['REMOTE_ADDR'];
            $count = Redis::incr($key);
            Redis::expire($key, 3600);
            
            if ($count >= self::$thresholds['file_upload_failures']) {
                self::triggerAlert('MALICIOUS_UPLOAD_ATTEMPT', [
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'file' => $file['name'],
                    'errors' => $result['errors'],
                    'count' => $count
                ]);
            }
        }
    }
    
    public static function monitorSQLInjection($query, $ip) {
        self::triggerAlert('SQL_INJECTION_ATTEMPT', [
            'query' => $query,
            'ip' => $ip,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        // Block IP immediately
        self::blockIP($ip, 3600);
    }
    
    public static function monitorXSSAttempt($input, $ip) {
        self::triggerAlert('XSS_ATTEMPT', [
            'input' => $input,
            'ip' => $ip,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        // Block IP temporarily
        self::blockIP($ip, 600);
    }
    
    public static function triggerAlert($type, $data) {
        $alert = [
            'type' => $type,
            'severity' => self::getSeverity($type),
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Log alert
        Logger::warning('Security Alert', $alert, 'security');
        
        // Send notification for critical alerts
        if ($alert['severity'] === 'critical') {
            self::sendNotification($alert);
        }
    }
    
    public static function blockIP($ip, $duration) {
        $key = "blocked_ip:$ip";
        Redis::setex($key, $duration, time());
        
        // Log blocking
        Logger::info('IP Blocked', [
            'ip' => $ip,
            'duration' => $duration,
            'reason' => 'Security violation'
        ], 'security');
    }
    
    public static function isIPBlocked($ip) {
        $key = "blocked_ip:$ip";
        return Redis::exists($key);
    }
    
    private static function getSeverity($type) {
        $severities = [
            'BRUTE_FORCE_ATTACK' => 'high',
            'SUSPICIOUS_ACTIVITY' => 'medium',
            'MALICIOUS_UPLOAD_ATTEMPT' => 'high',
            'SQL_INJECTION_ATTEMPT' => 'critical',
            'XSS_ATTEMPT' => 'critical'
        ];
        
        return $severities[$type] ?? 'medium';
    }
    
    private static function sendNotification($alert) {
        // Implement notification system (email, Slack, etc.)
        // For now, just log it
        Logger::critical('Critical Security Alert', $alert, 'security');
    }
}
?>
```

---

## 📚 Security Checklist

### **Pre-Deployment Security Checklist**

#### **Authentication & Authorization**
- [ ] Password hashing implemented (Argon2ID)
- [ ] Password policy enforced
- [ ] Two-factor authentication available
- [ ] Session security implemented
- [ ] Rate limiting on auth endpoints
- [ ] Account lockout after failed attempts
- [ ] JWT token validation
- [ ] Role-based access control

#### **Data Protection**
- [ ] Data encryption at rest
- [ ] Data encryption in transit
- [ ] Sensitive data masking
- [ ] GDPR compliance implemented
- [ ] Data retention policy
- [ ] Secure backup procedures

#### **Input Validation**
- [ ] Server-side validation for all inputs
- [ ] XSS protection implemented
- [ ] SQL injection prevention
- [ ] CSRF protection implemented
- [ ] File upload security
- [ ] Input sanitization

#### **API Security**
- [ ] API authentication
- [ ] API rate limiting
- [ ] Input validation
- [ ] Output encoding
- [ ] API security headers
- [ ] API audit logging

#### **Infrastructure Security**
- [ ] HTTPS implemented
- [ ] Security headers configured
- [ ] Database security configured
- [ ] File permissions set correctly
- [ ] Firewall rules configured
- [ ] Regular security updates

#### **Monitoring & Logging**
- [ ] Security event logging
- [ ] Intrusion detection
- [ ] Security monitoring
- [ ] Alert system
- [ ] Log analysis
- [ ] Regular security audits

---

**🎯 **Security Guide ini menyediakan panduan lengkap untuk implementasi keamanan aplikasi KSP Lam Gabe Jaya dengan best practices untuk melindungi data dan mencegah serangan keamanan!**
