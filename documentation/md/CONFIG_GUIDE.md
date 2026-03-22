# Configuration Files Guide

## 🎯 Overview

File konfigurasi sistem yang telah disiapkan untuk aplikasi KSP Lam Gabe Jaya.

## 📁 File Structure

```
config/
├── constants.php       # Application constants & settings
├── error-config.php   # Error handling & logging system
└── (future configs)   # Additional config files

api/
├── auth.php           # Authentication system
├── logout.php         # Logout handler
└── (future APIs)      # Additional API endpoints
```

## 🔧 Constants Configuration

### **Application Constants**
```php
define('APP_NAME', 'KSP Lam Gabe Jaya');
define('APP_VERSION', '4.0');
define('APP_URL', 'http://localhost/mono-v2');
```

### **Database Configuration**
```php
// Primary Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'ksp_lamgabejaya_v2');
define('DB_USER', 'root');
define('DB_PASSWORD', 'root');

// Multi-Database Support
define('DB_ORANG_NAME', 'orang');
define('DB_ALAMAT_NAME', 'alamat_db');
```

### **Authentication Settings**
```php
define('JWT_SECRET_KEY', 'ksp-lamgabejaya-secret-key-2024');
define('JWT_EXPIRY', 3600); // 1 hour
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes
```

### **Business Logic Constants**
```php
define('LOAN_MIN_AMOUNT', 100000);
define('LOAN_MAX_AMOUNT', 50000000);
define('SAVINGS_MIN_DEPOSIT', 10000);
define('PASSWORD_MIN_LENGTH', 8);
```

### **User Roles Hierarchy**
```php
define('ROLE_CREATOR', 0);        // Highest privilege
define('ROLE_OWNER', 1);
define('ROLE_SUPER_ADMIN', 2);
define('ROLE_ADMIN', 3);
define('ROLE_MANAGER', 4);
define('ROLE_SUPERVISOR', 5);
define('ROLE_TELLER', 6);
define('ROLE_STAFF', 7);
define('ROLE_MEMBER', 8);         // Lowest privilege
```

## 🛠️ Error Configuration

### **Error Handling Features**
- **Comprehensive Logging** - File-based error logging
- **Error Rotation** - Automatic log file rotation
- **Exception Classes** - Custom exception types
- **Production Mode** - Safe error display
- **Email Notifications** - Error alerts (optional)

### **Custom Exception Classes**
```php
AuthenticationException  // Auth failures
AuthorizationException   // Permission denied
ValidationException      // Input validation errors
DatabaseException        // Database errors
RateLimitException       // Rate limiting
FileNotFoundException     // Missing files
```

### **Error Logging**
```php
// Log levels: DEBUG, INFO, WARNING, ERROR
logInfo("User logged in");
logError("Database connection failed");
logDebug("Variable value: " . $var);
```

## 🔐 Authentication System

### **AuthSystem Class Features**
- **Multi-method Authentication** - JWT, session, tokens
- **Password Security** - Argon2ID hashing
- **Rate Limiting** - Brute force protection
- **Token Management** - JWT generation & validation
- **Session Management** - Secure session handling
- **Permission System** - Role-based access control

### **Token Types**
```php
// Access Token (1 hour expiry)
$accessToken = $auth->authenticate($username, $password);

// Refresh Token (7 days expiry)
$refreshToken = $tokens['refresh_token'];

// Token Validation
$user = $auth->validateToken($token);
```

### **Permission Checking**
```php
// Require authentication
$user = requireAuth();

// Require specific role
$admin = requireRole(ROLE_ADMIN);

// Check permissions
if (hasPermission('members', PERMISSION_WRITE)) {
    // Allow action
}
```

## 🚪 Logout System

### **Logout Features**
- **Token Blacklisting** - Secure token invalidation
- **Session Cleanup** - Complete session destruction
- **Cookie Cleanup** - Remove all auth cookies
- **Multi-device Logout** - Logout from all devices
- **Emergency Cleanup** - Fallback cleanup methods

### **Logout Endpoints**
```php
// Standard logout
POST /api/logout.php?action=logout

// Session cleanup only
POST /api/logout.php?action=cleanup

// Logout from all devices
POST /api/logout.php?action=logout_all
```

## 📋 Usage Examples

### **Basic Authentication**
```php
// Include config
require_once 'config/constants.php';
require_once 'api/auth.php';

// Initialize auth
$auth = new AuthSystem();

// Authenticate user
$result = $auth->authenticate($username, $password);

// Validate token
$user = $auth->validateToken($token);
```

### **Permission Checking**
```php
// In your page/controller
$user = requireAuth();

// Check role-based access
if ($user['role'] <= ROLE_ADMIN) {
    // Admin-only content
}

// Check specific permission
if (hasPermission('loans', PERMISSION_WRITE)) {
    // Can create/edit loans
}
```

### **Error Handling**
```php
try {
    // Your code here
    $result = riskyOperation();
} catch (DatabaseException $e) {
    logError("Database error: " . $e->getMessage());
    showErrorPage("Database temporarily unavailable");
} catch (AuthenticationException $e) {
    redirectToLogin();
}
```

## 🔧 Configuration Customization

### **Environment Settings**
```php
// Development
define('ENVIRONMENT', 'development');
define('DEBUG_MODE', true);
define('SHOW_ERRORS', true);

// Production
define('ENVIRONMENT', 'production');
define('DEBUG_MODE', false);
define('SHOW_ERRORS', false);
```

### **Database Settings**
```php
// For production
define('DB_HOST', 'production-db-host');
define('DB_PASSWORD', 'secure-password');
define('DB_SSL_MODE', true);
```

### **Security Settings**
```php
// JWT settings
define('JWT_SECRET_KEY', 'your-very-secure-secret-key');
define('JWT_EXPIRY', 1800); // 30 minutes for higher security

// Rate limiting
define('LOGIN_MAX_ATTEMPTS', 3); // Stricter for production
define('LOGIN_LOCKOUT_TIME', 1800); // 30 minutes
```

## 🚀 Integration Points

### **With Index.php**
```php
// At the top of index.php
require_once 'config/constants.php';
require_once 'config/error-config.php';
require_once 'api/auth.php';

// Check authentication
$user = getCurrentUser();
if (!$user && $currentRoute !== '/login') {
    redirectToLogin();
}
```

### **With Login.php**
```php
// In login form handler
$auth = new AuthSystem();
$result = $auth->authenticate($_POST['username'], $_POST['password']);

if ($result['success']) {
    // Set tokens and redirect
    $_SESSION['access_token'] = $result['access_token'];
    header('Location: /dashboard');
}
```

### **With Unified Dashboard**
```php
// In dashboard initialization
$user = requireAuth();

// Load content based on role
if ($user['role'] <= ROLE_ADMIN) {
    // Load admin content
} elseif ($user['role'] === ROLE_MEMBER) {
    // Load member content
}
```

## 📁 File Dependencies

### **Required Files**
- ✅ `config/constants.php` - Application constants
- ✅ `config/error-config.php` - Error handling
- ✅ `api/auth.php` - Authentication system
- ✅ `api/logout.php` - Logout handler

### **Optional Files (to be added)**
- 📄 `config/database.php` - Database configuration
- 📄 `config/email.php` - Email settings
- 📄 `config/sms.php` - SMS gateway settings
- 📄 `config/payment.php` - Payment gateway config

## 🔍 Testing Configuration

### **Test Constants**
```php
// Test database connection
$db = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASSWORD);

// Test authentication
$auth = new AuthSystem();
$user = $auth->authenticate('admin', 'admin');

// Test error handling
logInfo("Test log message");
```

### **Validate Setup**
```bash
# Check PHP syntax
php -l config/constants.php
php -l api/auth.php

# Test error logging
echo "Test error" | php -a
```

---

**Status:** ✅ **READY** - Configuration system complete and ready for integration!
