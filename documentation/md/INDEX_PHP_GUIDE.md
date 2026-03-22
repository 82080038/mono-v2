# Index PHP - Main Application Router

## 🎯 Overview

File `index.php` adalah **single entry point** untuk seluruh aplikasi KSP Lam Gabe Jaya. Semua request akan melalui file ini terlebih dahulu.

## 🔄 Routing Logic

### URL Pattern Handling
```
/                    → Landing page (redirect ke dashboard jika login)
/login               → Login page
/dashboard           → Unified dashboard
/admin               → Unified dashboard (admin role)
/member              → Unified dashboard (member role)
/staff               → Unified dashboard (staff role)
/logout              → Logout & redirect
/[any-other-page]    → Unified dashboard dengan page tersebut
```

### Request Type Detection
- **AJAX Requests** → JSON response untuk API calls
- **Page Requests** → HTML response untuk halaman web

## 🔐 Authentication Flow

### Token Support
- **Session Token** (server-side)
- **JWT Token** (client-side)
- **Simple Token** (localStorage simulation)
- **Bearer Token** (Authorization header)

### User Roles
- `admin` - Full access
- `member` - Limited access
- `staff` - Operational access
- `super_admin` - System admin

## 📋 Features

### ✅ Core Features
- **Single Entry Point** - Semua request melalui index.php
- **Dynamic Routing** - URL parsing otomatis
- **Authentication** - Multi-method token validation
- **Role-based Access** - Kontrol akses per role
- **AJAX Support** - API endpoint handling
- **Error Handling** - Graceful error pages
- **Fallback Pages** - Basic HTML jika file tidak ada

### 🛡️ Security
- **XSS Protection** - Input sanitization
- **CSRF Headers** - Security headers
- **Session Management** - Secure session handling
- **Error Logging** - Debug information

### 📱 Fallback System
Jika file spesifik tidak ada:
- **Landing Page** → Basic HTML Bootstrap
- **Login Page** → Simple form dengan demo login
- **Dashboard** → Basic layout per role

## 🚀 Usage Examples

### Basic Navigation
```php
// User访问根URL
GET /                    → Landing page atau dashboard

// Login
GET /login               → Login form
POST /login              → Authentication (AJAX)

// Dashboard (tergantung role)
GET /dashboard           → Unified dashboard
GET /admin               → Admin dashboard
GET /member              → Member dashboard
```

### AJAX Requests
```javascript
// Login via AJAX
fetch('/', {
    method: 'POST',
    headers: {'X-Requested-With': 'XMLHttpRequest'},
    body: 'action=login&username=admin&password=admin'
})

// Check authentication
fetch('/', {
    method: 'POST',
    headers: {'X-Requested-With': 'XMLHttpRequest'},
    body: 'action=check_auth'
})
```

## 🔧 Configuration

### Constants
```php
APP_NAME         -> "KSP Lam Gabe Jaya"
APP_VERSION      -> "4.0"
APP_ROOT         -> Application root directory
APP_URL          -> Base URL
ASSETS_URL       -> Assets directory URL
API_URL          -> API endpoint URL
```

### Session Configuration
```php
session_start();  // Server-side session
$_SESSION['user'] // User data storage
```

## 📁 File Dependencies

File ini akan mencoba load file-file berikut (jika ada):
- `config/error-config.php` - Error handling
- `config/constants.php` - Application constants  
- `api/auth.php` - Authentication system
- `pages/unified-dashboard.php` - Main dashboard
- `api/unified-router.php` - AJAX handler

## 🔄 Integration Points

### With Unified Dashboard
```php
// Route ke unified dashboard
$this->showUnifiedDashboard($page);
```

### With API Router
```php
// Forward AJAX requests
$this->forwardToUnifiedRouter($action);
```

## 🎯 Next Steps

1. **Extract config files** dari backup
2. **Create authentication system**
3. **Build unified dashboard**
4. **Implement API router**
5. **Add assets & styling**

---

**Status:** ✅ **READY** - Index router siap digunakan sebagai entry point utama aplikasi.
