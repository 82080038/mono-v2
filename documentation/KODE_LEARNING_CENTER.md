# 📚 Kode Learning Center - KSP Lam Gabe Jaya

## 🎯 **Tujuan Dokumentasi Ini**

Dokumentasi ini dirancang khusus untuk **belajar kode yang sebenarnya** dari aplikasi KSP Lam Gabe Jaya. Setiap bagian mencakup:
- **Kode asli** dari aplikasi
- **Penjelasan baris per baris**
- **Konsep programming** yang digunakan
- **Best practices** dan implementasi
- **Contoh praktis** yang bisa dicoba

---

## 🏗️ **Struktur Aplikasi**

### **📁 File Utama**
```
mono-v2/
├── 🎯 index.php          # Entry point & routing
├── 🔐 login.php          # Authentication system
├── 📊 main.php           # Dashboard & SPA logic
├── 🔌 api/auth.php       # REST API endpoints
├── 📄 pages/             # Role-specific pages
├── 🎨 assets/            # CSS, JS, images
└── 🗄️ database/          # Database files
```

---

## 🚀 **Mulai Belajar**

### **1. Authentication System** 🔐

#### **File: `login.php`**
```php
<?php
/**
 * KSP Lam Gabe Jaya - Authentication System
 * Multi-role authentication with session management
 */

// Start session for user state management
session_start();

// Include required files
require_once 'config/constants.php';
require_once 'core/Database.php';
require_once 'core/Auth.php';

// Handle login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Authenticate user
    $auth = new Auth();
    $result = $auth->login($username, $password);
    
    if ($result['success']) {
        // Set session variables
        $_SESSION['user_id'] = $result['user']['id'];
        $_SESSION['username'] = $result['user']['username'];
        $_SESSION['role'] = $result['user']['role'];
        
        // Redirect to dashboard
        header('Location: index.php?page=dashboard');
        exit;
    } else {
        $error = $result['message'];
    }
}
?>
```

**📝 Penjelasan:**
- **Session Management**: Menggunakan `session_start()` untuk maintain state user
- **Input Validation**: Menggunakan null coalescing operator (`??`) untuk safety
- **OOP Approach**: Menggunakan class `Auth` untuk logic authentication
- **Security**: Redirect setelah login untuk prevent refresh attacks

---

### **2. Dynamic Dashboard** 📊

#### **File: `main.php` - JavaScript Section**
```javascript
// Global variables from PHP
let currentUser = <?php echo json_encode($user); ?>;
let userRole = <?php echo json_encode($userRole); ?>;

// Dynamic content generation
function generateDashboardContent() {
    const dashboardContent = {
        'bos': `
            <div class="row">
                <div class="col-md-12">
                    <h1>Dashboard BOS</h1>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3>Total Anggota</h3>
                            <p class="stat-number">150</p>
                        </div>
                    </div>
                </div>
            </div>
        `,
        'admin': `
            <div class="row">
                <div class="col-md-12">
                    <h1>Dashboard Admin</h1>
                    <div class="admin-tools">
                        <button onclick="manageUsers()">Manage Users</button>
                    </div>
                </div>
            </div>
        `
    };
    
    return dashboardContent[userRole] || dashboardContent['bos'];
}

// SPA Navigation
function navigateTo(page, event) {
    if (event) event.preventDefault();
    
    // Update URL hash
    window.location.hash = page;
    
    // Load content dynamically
    loadPageContent(page);
}

function loadPageContent(page) {
    const content = generateContentForPage(page);
    document.getElementById('app-main').innerHTML = content;
    
    // Show notification
    showNotification(`Halaman ${page} dimuat`, 'success');
}
```

**📝 Penjelasan:**
- **PHP to JavaScript Bridge**: Menggunakan `json_encode()` untuk passing data
- **Template Literals**: Menggunakan backticks (`) untuk HTML strings
- **SPA Pattern**: Single Page Application dengan hash navigation
- **Dynamic Content**: Content generation based on user role

---

### **3. REST API** 🔌

#### **File: `api/auth.php`**
```php
<?php
/**
 * REST API for Authentication
 * Supports JSON responses and proper HTTP methods
 */

// Set headers for REST API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Route requests
switch ($method) {
    case 'POST':
        handleLogin($input);
        break;
    case 'GET':
        handleSessionCheck();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

function handleLogin($data) {
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    
    // Validate input
    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Username and password required']);
        return;
    }
    
    // Authenticate
    $auth = new Auth();
    $result = $auth->login($username, $password);
    
    echo json_encode($result);
}

function handleSessionCheck() {
    $user = $_SESSION['user'] ?? null;
    
    echo json_encode([
        'authenticated' => ($user !== null),
        'user' => $user
    ]);
}
?>
```

**📝 Penjelasan:**
- **REST Principles**: Proper HTTP methods and status codes
- **JSON API**: Menggunakan `json_encode()` untuk responses
- **CORS Support**: Headers untuk cross-origin requests
- **Error Handling**: Proper HTTP status codes dan error messages

---

### **4. Database Operations** 🗄️

#### **File: `core/Database.php`**
```php
<?php
/**
 * Database Connection and Operations
 * Uses PDO for secure database interactions
 */

class Database {
    private $pdo;
    private $host = DB_HOST;
    private $name = DB_NAME;
    private $user = DB_USER;
    private $pass = DB_PASSWORD;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        
        return $this->pdo->lastInsertId();
    }
}
?>
```

**📝 Penjelasan:**
- **PDO Usage**: Database abstraction layer dengan prepared statements
- **Security**: SQL injection prevention dengan parameter binding
- **Error Handling**: Exception handling untuk database errors
- **CRUD Operations**: Methods untuk insert, select, update, delete

---

### **5. Role-Based Access Control** 🎭

#### **File: `core/Auth.php`**
```php
<?php
/**
 * Authentication and Authorization System
 * Multi-role access control with permissions
 */

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function login($username, $password) {
        // Validate credentials
        $sql = "SELECT * FROM users WHERE username = ? AND status = 'active'";
        $user = $this->db->fetchOne($sql, [$username]);
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid password'];
        }
        
        // Update last login
        $this->updateLastLogin($user['id']);
        
        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'name' => $user['name']
            ]
        ];
    }
    
    public function hasPermission($role, $permission) {
        $permissions = $this->getRolePermissions($role);
        return in_array($permission, $permissions);
    }
    
    public function getRolePermissions($role) {
        $rolePermissions = [
            'bos' => ['all'],
            'admin' => ['manage_users', 'view_reports', 'manage_data'],
            'teller' => ['process_transactions', 'view_customers'],
            'collector' => ['manage_collections', 'view_routes'],
            'nasabah' => ['view_own_data', 'make_transactions']
        ];
        
        return $rolePermissions[$role] ?? [];
    }
    
    private function updateLastLogin($userId) {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $this->db->query($sql, [$userId]);
    }
}
?>
```

**📝 Penjelasan:**
- **Password Security**: Menggunakan `password_verify()` untuk hashed passwords
- **Role Hierarchy**: 5 roles dengan permissions berbeda
- **Permission System**: Method untuk checking akses permission
- **Audit Trail**: Update last login untuk tracking

---

## 🎨 **Frontend Implementation**

### **Bootstrap 5 + Custom CSS**
```css
/* Custom styles for KSP Lam Gabe Jaya */
.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 10px;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #007bff;
}

/* Responsive design */
@media (max-width: 768px) {
    .stat-card {
        margin-bottom: 1rem;
    }
}
```

### **JavaScript Utilities**
```javascript
// Notification System
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    
    // Add to page
    document.getElementById('notifications').appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Form Validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required]');
    
    let isValid = true;
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// AJAX Helper
function ajaxRequest(url, method, data, callback) {
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => callback(data))
    .catch(error => console.error('AJAX Error:', error));
}
```

---

## 🧪 **Testing & Debugging**

### **Unit Testing Example**
```php
<?php
/**
 * Simple Unit Test for Auth Class
 */

class AuthTest {
    private $auth;
    
    public function __construct() {
        $this->auth = new Auth();
    }
    
    public function testValidLogin() {
        $result = $this->auth->login('bos', 'bos');
        
        if ($result['success']) {
            echo "✅ Valid login test passed\n";
        } else {
            echo "❌ Valid login test failed\n";
        }
    }
    
    public function testInvalidLogin() {
        $result = $this->auth->login('invalid', 'wrong');
        
        if (!$result['success']) {
            echo "✅ Invalid login test passed\n";
        } else {
            echo "❌ Invalid login test failed\n";
        }
    }
    
    public function runAllTests() {
        echo "🧪 Running Auth Tests...\n";
        $this->testValidLogin();
        $this->testInvalidLogin();
        echo "🏁 Tests completed\n";
    }
}

// Run tests
$test = new AuthTest();
$test->runAllTests();
?>
```

### **JavaScript Testing**
```javascript
// Test Suite for Dashboard Functions
class DashboardTest {
    testContentGeneration() {
        const content = generateDashboardContent();
        
        if (content.includes('Dashboard')) {
            console.log('✅ Content generation test passed');
        } else {
            console.log('❌ Content generation test failed');
        }
    }
    
    testNavigation() {
        // Mock DOM
        document.body.innerHTML = '<div id="app-main"></div>';
        
        navigateTo('dashboard');
        
        const mainContent = document.getElementById('app-main').innerHTML;
        
        if (mainContent.length > 0) {
            console.log('✅ Navigation test passed');
        } else {
            console.log('❌ Navigation test failed');
        }
    }
    
    runAllTests() {
        console.log('🧪 Running Dashboard Tests...');
        this.testContentGeneration();
        this.testNavigation();
        console.log('🏁 Tests completed');
    }
}

// Run tests
const test = new DashboardTest();
test.runAllTests();
```

---

## 🔒 **Security Best Practices**

### **Input Validation**
```php
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateNumber($input, $min = 0, $max = PHP_INT_MAX) {
    $num = filter_var($input, FILTER_VALIDATE_INT);
    return $num !== false && $num >= $min && $num <= $max;
}
```

### **SQL Injection Prevention**
```php
// ❌ BAD: Vulnerable to SQL injection
$sql = "SELECT * FROM users WHERE username = '$username'";

// ✅ GOOD: Using prepared statements
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username]);
```

### **XSS Prevention**
```php
// ❌ BAD: Direct output
echo $userInput;

// ✅ GOOD: Escaped output
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
```

---

## 📈 **Performance Optimization**

### **Database Optimization**
```php
// Use indexes for frequently queried columns
$sql = "CREATE INDEX idx_users_username ON users(username)";

// Use LIMIT for pagination
$sql = "SELECT * FROM transactions LIMIT 10 OFFSET 0";

// Cache frequently accessed data
$cacheKey = 'user_stats_' . $userId;
$cached = apcu_fetch($cacheKey);

if ($cached === false) {
    $stats = calculateUserStats($userId);
    apcu_store($cacheKey, $stats, 300); // Cache for 5 minutes
}
```

### **Frontend Optimization**
```javascript
// Debounce search input
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Lazy load images
const imageObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src;
            imageObserver.unobserve(img);
        }
    });
});
```

---

## 🎯 **Exercises & Challenges**

### **Beginner Level**
1. **Buat halaman profil user** dengan data dari database
2. **Implementasi form validation** untuk registration
3. **Tambahkan AJAX** untuk submit form tanpa refresh

### **Intermediate Level**
1. **Buat reporting system** dengan charts
2. **Implementasi file upload** untuk document management
3. **Tambahkan real-time notifications** dengan WebSocket

### **Advanced Level**
1. **Buat mobile app** dengan React Native
2. **Implementasi microservices architecture**
3. **Tambahkan machine learning** untuk fraud detection

---

## 🔗 **Resources & References**

### **Documentation Links**
- [PHP Manual](https://www.php.net/docs.php)
- [JavaScript MDN](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
- [Bootstrap Documentation](https://getbootstrap.com/docs/)
- [MySQL Reference](https://dev.mysql.com/doc/)

### **Online Courses**
- [PHP for Beginners](https://www.php.net/manual/en/getting-started.php)
- [JavaScript Modern Tutorial](https://javascript.info/)
- [Database Design Course](https://www.coursera.org/learn/database-design)

### **Practice Platforms**
- [Codewars](https://www.codewars.com/)
- [HackerRank](https://www.hackerrank.com/)
- [LeetCode](https://leetcode.com/)

---

## 🏆 **Project Completion Checklist**

- [ ] **Authentication System** - Multi-role login
- [ ] **Dashboard** - Dynamic content per role
- [ ] **Database Operations** - CRUD dengan prepared statements
- [ ] **API Integration** - RESTful API endpoints
- [ ] **Frontend Interactivity** - JavaScript SPA navigation
- [ ] **Security** - Input validation & XSS prevention
- [ ] **Testing** - Unit tests for critical functions
- [ ] **Documentation** - Code comments & README

---

## 🎓 **Learning Path**

### **Phase 1: Foundation (1-2 weeks)**
1. PHP basics & syntax
2. MySQL database fundamentals
3. HTML5 & CSS3
4. JavaScript fundamentals

### **Phase 2: Application Development (2-3 weeks)**
1. OOP in PHP
2. Database design & normalization
3. Bootstrap 5 & responsive design
4. AJAX & API integration

### **Phase 3: Advanced Features (2-3 weeks)**
1. Security best practices
2. Performance optimization
3. Testing & debugging
4. Deployment & maintenance

### **Phase 4: Specialization (1-2 weeks)**
1. Choose specialization (frontend, backend, full-stack)
2. Advanced topics in chosen area
3. Portfolio project development
4. Code review & optimization

---

## 🤝 **Contribution Guidelines**

### **Code Style**
- Follow PSR-12 coding standards
- Use meaningful variable names
- Add comments for complex logic
- Keep functions small and focused

### **Git Workflow**
```bash
# Create feature branch
git checkout -b feature/new-feature

# Make changes
git add .
git commit -m "feat: add new feature"

# Push and create pull request
git push origin feature/new-feature
```

### **Testing Requirements**
- Write unit tests for new features
- Ensure all tests pass before merge
- Add documentation for new functions

---

**🚀 Selamat Belajar!**

Dokumentasi ini dirancang untuk membantu Anda memahami kode aplikasi KSP Lam Gabe Jaya dari dasar hingga advanced. Pelajari setiap bagian secara bertahap, praktikkan kode yang ada, dan jangan ragu untuk bereksperimen dengan fitur baru.

**Happy Coding!** 💻✨
