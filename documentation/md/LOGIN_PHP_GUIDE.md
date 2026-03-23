# Login PHP - Authentication Page

## 🎯 Overview

Halaman `login.php` adalah dedicated authentication page dengan fitur lengkap dan security yang ditingkatkan.

## 🔐 Features

### ✅ Authentication Features
- **Multi-method Token Support** - JWT, session, bearer token
- **Role-based Login** - Redirect sesuai user role
- **Remember Me** - Persistent login cookie
- **Rate Limiting** - Protection dari brute force
- **Session Management** - Secure session handling
- **Auto-redirect** - Login check otomatis

### 🎨 UI/UX Features
- **Modern Design** - Gradient background dengan animasi
- **Responsive Layout** - Mobile-friendly interface
- **Password Toggle** - Show/hide password
- **Demo Credentials** - Quick access untuk testing
- **Loading States** - Visual feedback saat login
- **Error Handling** - User-friendly error messages
- **Keyboard Shortcuts** - Ctrl+Enter untuk submit

### 🛡️ Security Features
- **XSS Protection** - Input sanitization
- **CSRF Headers** - Security headers
- **Rate Limiting** - Max 5 attempts per IP
- **Token Expiration** - JWT dengan expiry
- **Secure Cookies** - HttpOnly, Secure flags
- **Input Validation** - Server-side validation

## 👥 Demo Users

### **Admin Account**
- **Username:** `admin`
- **Password:** `admin`
- **Role:** `admin`
- **Access:** Full system access

### **Member Account**
- **Username:** `member`
- **Password:** `member`
- **Role:** `member`
- **Access:** Member dashboard

### **Staff Account**
- **Username:** `staff`
- **Password:** `staff`
- **Role:** `staff`
- **Access:** Staff operations

## 🔄 Authentication Flow

### **Login Process**
```
1. User submit form → AJAX request
2. Server validate credentials
3. Generate JWT token (1 hour expiry)
4. Set session data
5. Store token in localStorage
6. Redirect to dashboard (role-based)
```

### **Token Types**
```php
// JWT Token (production)
$header = base64_encode(['alg' => 'HS256', 'typ' => 'JWT']);
$payload = base64_encode([
    'user_id' => $user['id'],
    'username' => $user['username'],
    'role' => $user['role'],
    'exp' => time() + 3600 // 1 hour
]);

// Demo Token (development)
'demo-token-' . time()
```

### **Session Storage**
```php
$_SESSION['user'] = [
    'id' => 1,
    'username' => 'admin',
    'name' => 'Administrator',
    'role' => 'admin'
];
```

## 📱 AJAX Endpoints

### **POST /login.php**
Headers: `X-Requested-With: XMLHttpRequest`

#### **Login Request**
```javascript
fetch('login.php', {
    method: 'POST',
    headers: {'X-Requested-With': 'XMLHttpRequest'},
    body: new FormData(loginForm)
})
```

**Response (Success):**
```json
{
    "success": true,
    "message": "Login berhasil",
    "user": {...},
    "token": "jwt-token",
    "redirect": "/dashboard"
}
```

**Response (Error):**
```json
{
    "success": false,
    "message": "Username atau password salah"
}
```

#### **Check Session Request**
```javascript
fetch('login.php', {
    method: 'POST',
    headers: {'X-Requested-With': 'XMLHttpRequest'},
    body: 'action=check_session'
})
```

#### **Logout Request**
```javascript
fetch('login.php', {
    method: 'POST',
    headers: {'X-Requested-With': 'XMLHttpRequest'},
    body: 'action=logout'
})
```

## 🎨 UI Components

### **Animated Background**
- CSS animations dengan floating elements
- Gradient background yang menarik
- Responsive design untuk semua screen sizes

### **Login Form**
- Floating labels design
- Icon integration
- Password visibility toggle
- Remember me checkbox

### **Demo Credentials**
- Quick fill buttons
- Visual feedback
- Multiple account types

### **Alert System**
- Auto-dismiss notifications
- Success/error states
- Smooth animations

## 🔧 Configuration

### **Security Settings**
```php
// Rate limiting
$maxAttempts = 5;
$blockDuration = 900; // 15 minutes

// Token expiration
$tokenExpiry = 3600; // 1 hour

// Cookie settings
setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
```

### **Redirect Rules**
```php
$redirects = [
    'admin' => '/dashboard',
    'super_admin' => '/dashboard',
    'member' => '/dashboard',
    'staff' => '/dashboard'
];
```

## 🚀 Integration

### **With Index.php**
```php
// Login check di index.php
$user = checkExistingAuth();
if ($user) {
    header('Location: /dashboard');
    exit;
}
```

### **With Unified Dashboard**
```php
// Token validation
$token = $_GET['token'] ?? $_POST['token'];
$userData = validateToken($token);
```

## 📋 Testing

### **Manual Testing**
1. Buka `http://localhost/mono-v2/login.php`
2. Klik badge demo credentials
3. Form akan terisi otomatis
4. Submit form
5. Verify redirect ke dashboard

### **API Testing**
```bash
# Test login endpoint
curl -X POST http://localhost/mono-v2/login.php \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "action=login&username=admin&password=admin"
```

## 🔍 Debug Mode

### **Enable Debug**
```php
// Di login.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### **Debug Information**
- Console logs untuk AJAX errors
- Network tab untuk request/response
- Session storage untuk token verification

## 📱 Mobile Features

### **Touch Optimized**
- Larger touch targets
- Mobile keyboard optimization
- Responsive form layout

### **PWA Ready**
- Manifest integration
- Theme color meta tags
- Apple touch icons

---

**Status:** ✅ **READY** - Login page siap digunakan dengan fitur lengkap!
