# KSP Lam Gabe Jaya - Development Guide

## 🚀 **Getting Started**

### **Prerequisites**
- PHP 8.x dengan extensions: mysqli, json, session, openssl
- MySQL/MariaDB 5.7+
- Web server (Apache/Nginx dengan mod_rewrite)
- Git untuk version control
- Modern web browser (Chrome, Firefox, Safari, Edge)

### **Quick Start**
```bash
# 1. Clone repository
git clone <repository-url>
cd mono-v2

# 2. Setup database
mysql -u root -p < database/gabe.sql

# 3. Configure
cp config/config.example.php config/config.php
# Edit config.php dengan database credentials

# 4. Set permissions
chmod 755 .
chmod 644 *.php
chmod 755 api/
chmod 755 pages/

# 5. Access application
http://localhost/mono-v2/
```

---

## 🏗️ **Architecture Overview**

### **System Architecture**
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Backend       │    │   Database      │
│                 │    │                 │    │                 │
│ • HTML5         │◄──►│ • PHP 8.x       │◄──►│ • MySQL/MariaDB │
│ • Bootstrap 5.3 │    │ • Session Auth  │    │ • InnoDB        │
│ • Font Awesome  │    │ • REST API      │    │ • UTF8MB4       │
│ • Vanilla JS    │    │ • OOP Patterns  │    │ • Foreign Keys  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### **Directory Structure**
```
mono-v2/
├── 📁 api/                    # REST API endpoints
│   ├── auth.php              # Authentication API
│   └── API_DOCUMENTATION.md  # API documentation
├── 📁 assets/                 # Static assets
│   ├── css/                  # Stylesheets
│   │   └── fontawesome-fallback.css
│   ├── js/                   # JavaScript files
│   └── images/               # Images and icons
├── 📁 config/                 # Configuration files
│   ├── constants.php         # Application constants
│   └── config.example.php    # Sample configuration
├── 📁 database/               # Database files
│   ├── gabe.sql              # Complete database schema
│   ├── gabe_v2.sql           # v2.0 schema
│   ├── update_gabe_v2.sql    # Migration script
│   └── DATABASE_DOCUMENTATION.md
├── 📁 pages/                  # Role-specific pages
│   ├── bos/                  # BOS management pages
│   ├── admin/                # Admin operational pages
│   ├── teller/               # Teller transaction pages
│   ├── collector/            # Collector field pages
│   └── nasabah/              # Nasabah customer pages
├── 📄 main.php                # Main application file
├── 📄 index.php               # Entry point
├── 📄 login.php               # Login page
├── 📄 README.md               # Project documentation
└── 📄 test-*.php              # Test files
```

---

## 🎭 **Role System**

### **User Roles & Permissions**
```php
// Role hierarchy: bos > admin > teller > collector > nasabah

$roles = [
    'bos' => [
        'name' => 'BOS (Owner)',
        'permissions' => ['*'], // Full access
        'dashboard' => 'management_overview',
        'pages' => ['dashboard', 'laporan', 'users', 'settings']
    ],
    'admin' => [
        'name' => 'Administrator',
        'permissions' => ['manage_users', 'approve_loans', 'reports'],
        'dashboard' => 'operational_overview',
        'pages' => ['dashboard', 'laporan', 'nasabah', 'transaksi']
    ],
    'teller' => [
        'name' => 'Teller',
        'permissions' => ['transactions', 'customer_service'],
        'dashboard' => 'transaction_overview',
        'pages' => ['dashboard', 'transaksi', 'nasabah']
    ],
    'collector' => [
        'name' => 'Collector',
        'permissions' => ['field_operations', 'collections'],
        'dashboard' => 'field_overview',
        'pages' => ['dashboard', 'kutipan', 'rute', 'gps_log']
    ],
    'nasabah' => [
        'name' => 'Nasabah (Customer)',
        'permissions' => ['personal_account'],
        'dashboard' => 'personal_overview',
        'pages' => ['dashboard', 'profil', 'transaksi']
    ]
];
```

### **Default Login Credentials**
```
BOS:       username=bos,       password=bos
Admin:     username=admin,     password=admin
Teller:    username=teller,    password=teller
Collector: username=collector, password=collector
Nasabah:   username=nasabah,   password=nasabah
```

---

## 🌐 **Dynamic Navigation System**

### **SPA-Like Navigation**
```javascript
// Hash-based navigation without page reload
function navigateTo(page, event) {
    if (event) event.preventDefault();
    
    // Update URL hash
    window.location.hash = page;
    
    // Load dynamic content
    loadPageContent(page);
    
    // Update active menu
    updateActiveMenu(page);
}

// Dynamic content loading
function loadPageContent(page) {
    const content = generateContent(page, userRole);
    document.querySelector('.app-main').innerHTML = content;
}
```

### **Content Generation System**
```javascript
// Role-specific content generators
const contentGenerators = {
    'dashboard': generateDashboardContent,
    'laporan': generateLaporanContent,
    'nasabah': generateNasabahContent,
    'transaksi': generateTransaksiContent,
    'profil': generateProfilContent,
    // ... more pages
};

// Dynamic content based on role
function generateContent(page, role) {
    const generator = contentGenerators[page];
    return generator ? generator(role) : 'Page not found';
}
```

### **Available Routes**
```javascript
const routes = {
    // Common routes
    'dashboard': 'All roles',
    'profil': 'All roles',
    
    // BOS routes
    'laporan': 'BOS, Admin',
    'users': 'BOS only',
    'settings': 'BOS only',
    
    // Admin routes  
    'nasabah': 'Admin, Teller',
    'transaksi': 'Admin, Teller',
    
    // Teller routes
    'setoran': 'Teller only',
    'penarikan': 'Teller only',
    
    // Collector routes
    'kutipan': 'Collector only',
    'rute': 'Collector only',
    'gps_log': 'Collector only'
};
```

---

## 🎨 **Frontend Development**

### **CSS Architecture**
```css
/* Bootstrap 5.3.0 base */
@import url('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');

/* Font Awesome 6.4.0 with fallback */
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');

/* Custom CSS variables */
:root {
    --primary-color: #007bff;
    --secondary-color: #6c757d;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --info-color: #17a2b8;
    --light-color: #f8f9fa;
    --dark-color: #343a40;
}

/* Responsive design */
@media (max-width: 768px) {
    .sidebar { width: 100%; }
    .main-content { margin-left: 0; }
}
```

### **JavaScript Patterns**
```javascript
// Module pattern for organization
const KSPApp = {
    // Navigation module
    navigation: {
        init() { /* Initialize navigation */ },
        navigate(page) { /* Handle navigation */ },
        updateMenu(page) { /* Update active menu */ }
    },
    
    // Auth module
    auth: {
        login(credentials) { /* Handle login */ },
        logout() { /* Handle logout */ },
        checkSession() { /* Check session validity */ }
    },
    
    // Content module
    content: {
        generate(page, role) { /* Generate content */ },
        load(page) { /* Load page content */ },
        update(page) { /* Update page content */ }
    }
};

// Initialize application
document.addEventListener('DOMContentLoaded', () => {
    KSPApp.navigation.init();
    KSPApp.auth.checkSession();
});
```

### **Component Templates**
```html
<!-- Dashboard card component -->
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <div>
                <h6 class="card-subtitle mb-2 text-muted">{{subtitle}}</h6>
                <h3 class="card-title">{{value}}</h3>
                <small class="text-muted">{{change}}</small>
            </div>
            <div class="align-self-center">
                <i class="{{icon}} fa-2x text-{{color}}"></i>
            </div>
        </div>
    </div>
</div>

<!-- Data table component -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>{{column1}}</th>
                <th>{{column2}}</th>
                <th>{{column3}}</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Dynamic rows -->
        </tbody>
    </table>
</div>
```

---

## 🔧 **Backend Development**

### **PHP Architecture**
```php
// Session management
session_start();
require_once 'config/constants.php';

// Database connection
class Database {
    private $host = DB_HOST;
    private $dbname = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    
    public function connect() {
        // PDO connection with error handling
    }
}

// Authentication class
class Auth {
    public function login($username, $password) {
        // Validate credentials
        // Create session
        // Log attempt
    }
    
    public function logout() {
        // Destroy session
        // Log logout
    }
    
    public function checkRole($requiredRole) {
        // Check user role
        // Return boolean
    }
}
```

### **API Endpoints**
```php
// Authentication API
POST /api/auth.php
Content-Type: application/x-www-form-urlencoded

action=login&username={username}&password={password}

// Response format
{
    "success": true,
    "user": {
        "id": 1,
        "username": "bos",
        "role": "bos",
        "name": "Bos KSP"
    },
    "message": "Login successful"
}
```

### **Security Best Practices**
```php
// Input validation
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// SQL injection prevention
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);

// XSS prevention
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');

// CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
}
```

---

## 🗄️ **Database Development**

### **Schema Design**
```sql
-- Core tables
users          -- User management with role system
members        -- Member information
accounts       -- Account management
transactions   -- Transaction tracking
savings        -- Savings management
loans          -- Loan management
loan_payments  -- Loan payment tracking

-- Security tables
login_attempts -- Login attempt tracking
audit_logs     -- Audit trail with JSON

-- Configuration
system_config  -- System settings

-- Views for reporting
daily_transactions   -- Daily transaction summary
loan_performance     -- Loan performance metrics
member_summary       -- Member dashboard data
```

### **Migration System**
```sql
-- Version control for database
CREATE TABLE schema_migrations (
    version VARCHAR(255) PRIMARY KEY,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Migration files
database/migrations/001_create_users_table.sql
database/migrations/002_add_role_system.sql
database/migrations/003_enhance_security.sql
```

### **Query Optimization**
```sql
-- Indexes for performance
CREATE INDEX idx_transactions_date ON transactions(transaction_date);
CREATE INDEX idx_loans_status ON loans(status);
CREATE INDEX idx_users_role ON users(role);

-- Efficient joins
SELECT m.full_name, a.balance 
FROM members m
LEFT JOIN accounts a ON m.id = a.member_id
WHERE a.status = 'active';
```

---

## 🧪 **Testing System**

### **Test Categories**
```bash
# Authentication tests
php test-login-all-roles.php          # Test all role logins
php test-session-management.php       # Test session handling

# Dynamic content tests
php test-dynamic-navigation.php      # Test SPA navigation
php test-dynamic-content-pages.php   # Test content generation

# JavaScript tests
php test-javascript-syntax.php        # Test JS syntax
php test-comprehensive-errors.php     # Test all errors

# Role-based tests
php test-role-based-statistics.php   # Test role statistics
php test-all-roles-consistency.php   # Test role consistency
```

### **Test Structure**
```php
// Test class structure
class TestCase {
    protected $testName;
    protected $expected;
    protected $actual;
    
    public function run() {
        // Run test
        // Assert results
        // Report results
    }
    
    public function assert($condition, $message) {
        if (!$condition) {
            throw new Exception("Test failed: $message");
        }
    }
}
```

### **Continuous Testing**
```bash
# Run all tests
php run-all-tests.php

# Test specific feature
php test-feature.php --feature=authentication

# Generate test report
php generate-test-report.php
```

---

## 🚀 **Deployment Guide**

### **Development Environment**
```bash
# Local development setup
git clone <repository>
cd mono-v2
composer install
npm install

# Start development server
php -S localhost:8000
npm run dev
```

### **Production Environment**
```bash
# Production deployment
git clone <repository>
cd mono-v2

# Configure environment
cp .env.example .env
# Edit .env with production values

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install --production

# Set permissions
chmod 755 .
chmod 644 *.php
chmod 755 api/ pages/

# Configure web server
# Apache: .htaccess for mod_rewrite
# Nginx: server block configuration
```

### **Environment Variables**
```bash
# .env file
DB_HOST=localhost
DB_NAME=gabe
DB_USER=root
DB_PASS=password

APP_ENV=production
APP_DEBUG=false
APP_URL=https://ksp-lamgabejaya.com

SESSION_TIMEOUT=1800
MAX_LOGIN_ATTEMPTS=5
LOCKOUT_DURATION=900
```

---

## 🔄 **Development Workflow**

### **Git Workflow**
```bash
# Feature development
git checkout -b feature/new-feature
# Make changes
git add .
git commit -m "feat: add new feature"
git push origin feature/new-feature

# Create pull request
# Code review
# Merge to main

# Release
git tag v2.1.0
git push origin v2.1.0
```

### **Code Standards**
```php
// PSR-12 coding standards
<?php
declare(strict_types=1);

namespace KSP\Controllers;

use KSP\Models\User;

class AuthController
{
    public function login(string $username, string $password): bool
    {
        // Implementation
    }
}
```

### **Documentation Standards**
```php
/**
 * Authenticate user with credentials
 *
 * @param string $username User username
 * @param string $password User password
 * @return array Authentication result
 * @throws InvalidCredentialsException If credentials are invalid
 */
public function authenticate(string $username, string $password): array
{
    // Implementation
}
```

---

## 🐛 **Debugging Guide**

### **Common Issues**
```php
// Debug session issues
error_log("Session data: " . print_r($_SESSION, true));

// Debug database issues
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Debug JavaScript errors
console.log("Current page: " + page);
console.log("User role: " + userRole);
```

### **Logging System**
```php
// Application logging
function logMessage($level, $message) {
    $logFile = 'logs/app_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
```

### **Performance Monitoring**
```php
// Query performance
$start = microtime(true);
// Database query
$end = microtime(true);
$executionTime = $end - $start;
logMessage('INFO', "Query executed in {$executionTime} seconds");
```

---

## 📚 **API Documentation**

### **Authentication API**
```http
POST /api/auth.php
Content-Type: application/x-www-form-urlencoded

action=login&username=bos&password=bos
```

### **Response Format**
```json
{
    "success": true,
    "user": {
        "id": 1,
        "username": "bos",
        "role": "bos",
        "name": "Bos KSP"
    },
    "message": "Login successful"
}
```

### **Error Handling**
```json
{
    "success": false,
    "error": {
        "code": "AUTH_001",
        "message": "Invalid credentials"
    }
}
```

---

## 🎯 **Future Development**

### **Planned Features**
- [ ] Real-time notifications
- [ ] Mobile app API
- [ ] Advanced reporting
- [ ] Document management
- [ ] SMS notifications
- [ ] Payment gateway integration

### **Technical Debt**
- [ ] Implement proper MVC pattern
- [ ] Add unit tests
- [ ] Optimize database queries
- [ ] Implement caching
- [ ] Add API rate limiting

### **Performance Improvements**
- [ ] Database indexing
- [ ] CSS/JS minification
- [ ] Image optimization
- [ ] Lazy loading
- [ ] CDN integration

---

## 📞 **Support & Contact**

### **Development Team**
- **Lead Developer**: [Contact Info]
- **Backend Developer**: [Contact Info]
- **Frontend Developer**: [Contact Info]
- **Database Admin**: [Contact Info]

### **Resources**
- **Documentation**: `/docs/`
- **API Reference**: `/api/docs`
- **Test Suite**: `test-*.php`
- **Database Schema**: `/database/`

### **Getting Help**
1. Check documentation
2. Review existing issues
3. Create new issue with detailed description
4. Contact development team

---

**KSP Lam Gabe Jaya Development Guide v2.0** 🏦✨

*This guide helps new developers understand and contribute to the KSP Lam Gabe Jaya cooperative management system.*
