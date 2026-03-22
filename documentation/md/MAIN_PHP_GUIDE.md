# Main PHP - Dashboard Utama

## 🎯 Overview

Halaman `main.php` adalah dashboard utama yang ditampilkan setelah user berhasil login. Halaman ini dirancang sebagai single-page application dengan widget-based layout yang adaptif berdasarkan role pengguna.

## 🏗️ Struktur Halaman

### **Layout Components**
```
┌─────────────────────────────────────────────────┐
│ Header (Fixed)                                    │
│ - Brand Logo - User Menu - Refresh - Logout       │
├─────────────────────────────────────────────────┤
│ Sidebar (Fixed)                                   │
│ - Role-based Navigation Menu                      │
├─────────────────────────────────────────────────┤
│ Main Content Area                                │
│ - Dashboard Header                               │
│ - Widget Grid (Responsive)                       │
│ - Dynamic Content                                │
└─────────────────────────────────────────────────┘
```

## 👥 Role-Based Dashboard

### **Creator/Owner Dashboard**
```php
$menuItems = [
    ['key' => 'dashboard', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt'],
    ['key' => 'system', 'title' => 'System', 'icon' => 'fas fa-cogs'],
    ['key' => 'database', 'title' => 'Database', 'icon' => 'fas fa-database'],
    ['key' => 'users', 'title' => 'Users', 'icon' => 'fas fa-users'],
    ['key' => 'analytics', 'title' => 'Analytics', 'icon' => 'fas fa-chart-line'],
    ['key' => 'logs', 'title' => 'Logs', 'icon' => 'fas fa-file-alt']
];
```

### **Admin Dashboard**
```php
$menuItems = [
    ['key' => 'dashboard', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt'],
    ['key' => 'members', 'title' => 'Anggota', 'icon' => 'fas fa-users'],
    ['key' => 'loans', 'title' => 'Pinjaman', 'icon' => 'fas fa-hand-holding-usd'],
    ['key' => 'savings', 'title' => 'Simpanan', 'icon' => 'fas fa-piggy-bank'],
    ['key' => 'transactions', 'title' => 'Transaksi', 'icon' => 'fas fa-exchange-alt'],
    ['key' => 'reports', 'title' => 'Laporan', 'icon' => 'fas fa-chart-bar'],
    ['key' => 'settings', 'title' => 'Pengaturan', 'icon' => 'fas fa-cog']
];
```

### **Member Dashboard**
```php
$menuItems = [
    ['key' => 'dashboard', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt'],
    ['key' => 'profile', 'title' => 'Profil', 'icon' => 'fas fa-user'],
    ['key' => 'savings', 'title' => 'Simpanan', 'icon' => 'fas fa-piggy-bank'],
    ['key' => 'loans', 'title' => 'Pinjaman', 'icon' => 'fas fa-hand-holding-usd'],
    ['key' => 'transactions', 'title' => 'Riwayat', 'icon' => 'fas fa-history'],
    ['key' => 'payments', 'title' => 'Pembayaran', 'icon' => 'fas fa-credit-card']
];
```

## 🎨 Widget System

### **Widget Types**

#### **1. Stats Widget**
```php
// Overview Statistics
[
    'total_members' => 150,
    'active_loans' => 45,
    'total_savings' => 250000000,
    'monthly_growth' => 12
]

// Account Summary
[
    'savings_balance' => 5000000,
    'active_loan' => 10000000,
    'monthly_payment' => 500000,
    'next_payment_date' => '2024-02-01'
]
```

#### **2. Activity Widget**
```php
$activities = [
    ['type' => 'member', 'title' => 'Anggota baru ditambahkan', 'user' => 'John Doe', 'time' => '2 jam yang lalu'],
    ['type' => 'loan', 'title' => 'Pinjaman disetujui', 'user' => 'Jane Smith', 'time' => '5 jam yang lalu'],
    ['type' => 'payment', 'title' => 'Pembayaran diterima', 'user' => 'Robert Johnson', 'time' => '1 hari yang lalu']
];
```

#### **3. Quick Actions Widget**
```php
// Admin Actions
[
    ['icon' => 'fas fa-user-plus', 'label' => 'Tambah Anggota'],
    ['icon' => 'fas fa-hand-holding-usd', 'label' => 'Ajukan Pinjaman'],
    ['icon' => 'fas fa-plus', 'label' => 'Setoran Baru'],
    ['icon' => 'fas fa-chart-bar', 'label' => 'Lihat Laporan']
]

// Member Actions
[
    ['icon' => 'fas fa-plus-circle', 'label' => 'Ajukan Pinjaman'],
    ['icon' => 'fas fa-piggy-bank', 'label' => 'Tambah Simpanan'],
    ['icon' => 'fas fa-credit-card', 'label' => 'Bayar Cicilan'],
    ['icon' => 'fas fa-download', 'label' => 'Download Laporan']
]
```

#### **4. Notifications Widget**
```php
$notifications = [
    ['title' => 'Pengajuan pinjaman baru', 'time' => '30 menit yang lalu', 'unread' => true],
    ['title' => 'Jadwal pembayaran cicilan', 'time' => '2 jam yang lalu', 'unread' => true],
    ['title' => 'Update sistem', 'time' => '1 hari yang lalu', 'unread' => false]
];
```

## 🔐 Authentication Integration

### **Session Management**
```php
// Check authentication
$auth = new AuthSystem();
$user = $auth->getCurrentUser();

if (!$user) {
    header('Location: /login.php');
    exit;
}

// Update last activity
$_SESSION['last_activity'] = time();
```

### **Role-Based Access**
```php
// Get user role and permissions
$userRole = $user['role'];
$userName = $user['name'] ?? $user['username'];

// Determine dashboard layout
$dashboardLayout = getDashboardLayout($userRole);
$menuItems = getMenuItems($userRole);
$widgets = getDashboardWidgets($userRole);
```

## 🎨 UI/UX Features

### **Responsive Design**
- **Desktop Layout** - Fixed sidebar + main content
- **Mobile Layout** - Collapsible sidebar + full-width content
- **Tablet Layout** - Adaptive sidebar behavior

### **Interactive Elements**
- **Hover Effects** - Smooth transitions on widgets and buttons
- **Loading States** - Spinners during data loading
- **Active States** - Visual feedback for navigation
- **Notifications** - Toast-style alerts

### **Keyboard Shortcuts**
```javascript
// Ctrl+R - Refresh dashboard
// Ctrl+L - Logout
// Tab navigation - Full keyboard accessibility
```

## 📱 Mobile Features

### **Responsive Sidebar**
```css
@media (max-width: 768px) {
    .app-sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .app-sidebar.show {
        transform: translateX(0);
    }
}
```

### **Touch-Friendly**
- **Larger touch targets** - Minimum 44px
- **Swipe gestures** - Sidebar toggle
- **Mobile menu** - Hamburger menu button

## 🔄 Dynamic Content Loading

### **Widget Generation**
```javascript
function loadDashboardWidgets() {
    const widgetsContainer = document.getElementById('dashboardWidgets');
    widgetsContainer.innerHTML = '<div class="text-center"><div class="loading-spinner"></div></div>';
    
    setTimeout(() => {
        const widgets = <?php echo json_encode($widgets); ?>;
        let widgetsHTML = '';
        
        for (const [key, widget] of Object.entries(widgets)) {
            widgetsHTML += generateWidgetHTML(key, widget);
        }
        
        widgetsContainer.innerHTML = widgetsHTML;
    }, 500);
}
```

### **AJAX Navigation**
```javascript
function navigateTo(page) {
    // Update active menu
    document.querySelectorAll('.menu-item').forEach(item => {
        item.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Load page content (future implementation)
    console.log('Navigating to:', page);
    showNotification('Navigasi ke ' + page, 'info');
}
```

## 🛡️ Security Features

### **Session Timeout**
```javascript
function checkSessionTimeout() {
    const sessionTimeout = 3600000; // 1 hour
    setInterval(() => {
        const now = Date.now();
        if (now - lastActivity > sessionTimeout) {
            showNotification('Session expired. Please login again.', 'warning');
            setTimeout(() => {
                window.location.href = '/login.php';
            }, 3000);
        }
    }, 60000); // Check every minute
}
```

### **CSRF Protection**
```php
// Headers for security
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
```

## 📊 Performance Optimization

### **Lazy Loading**
- **Widgets** - Load on demand
- **Images** - Lazy load with intersection observer
- **Data** - AJAX-based content loading

### **Caching Strategy**
- **Static Assets** - Browser cache headers
- **Widget Data** - Session-based caching
- **Menu Items** - Pre-computed per role

## 🎯 Integration Points

### **With Authentication System**
```php
// Require authentication
$user = requireAuth();

// Check role permissions
if ($user['role'] <= ROLE_ADMIN) {
    // Load admin widgets
}
```

### **With API Endpoints**
```javascript
// Refresh dashboard data
fetch('/api/dashboard.php?action=refresh', {
    method: 'POST',
    headers: {'X-Requested-With': 'XMLHttpRequest'}
})
.then(response => response.json())
.then(data => {
    updateWidgets(data);
});
```

### **With Logout System**
```javascript
function logout() {
    fetch('/api/logout.php', {
        method: 'POST',
        body: 'action=logout'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            localStorage.removeItem('authToken');
            window.location.href = '/login.php';
        }
    });
}
```

## 📋 Usage Examples

### **Basic Access**
```bash
# Direct access
http://localhost/mono-v2/main.php

# With authentication token
http://localhost/mono-v2/main.php?token=jwt-token
```

### **Role Testing**
```php
// Test different roles by modifying user data
$_SESSION['user']['role'] = ROLE_ADMIN;  // Admin dashboard
$_SESSION['user']['role'] = ROLE_MEMBER; // Member dashboard
```

### **Widget Customization**
```php
// Add custom widget
$widgets['custom_widget'] = [
    'title' => 'Custom Widget',
    'type' => 'custom',
    'data' => $customData
];
```

## 🚀 Future Enhancements

### **Planned Features**
- **Real-time Updates** - WebSocket integration
- **Customizable Layout** - Drag-and-drop widgets
- **Advanced Analytics** - Chart.js integration
- **Offline Support** - Service worker implementation
- **Push Notifications** - Browser notifications

### **Performance Improvements**
- **Widget Caching** - Server-side caching
- **Data Preloading** - Predictive loading
- **Image Optimization** - WebP format
- **Code Splitting** - Lazy load components

---

**Status:** ✅ **READY** - Main dashboard page complete with role-based layout and responsive design!
