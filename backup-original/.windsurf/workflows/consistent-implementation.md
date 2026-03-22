---
description: Ensure Consistent Implementation Across All Roles and Components

This workflow ensures that all upgrades, fixes, and implementations are applied consistently across all roles (admin, staff, member) and components in the KSP Lam Gabe Jaya application.

---

## 🎯 **Implementation Standards**

### **📱 Mobile-First Responsive Design**
- **Breakpoint**: 992px for mobile menu toggle
- **Auto-close**: Sidebar auto-closes on mobile after menu click
- **Touch-friendly**: Proper tap targets and spacing
- **Viewport**: All pages have proper viewport meta tag

### **🔧 JavaScript Functions (Required in ALL dashboards)**
```javascript
// User menu functions
window.showProfile = function() {
    alert('Fitur Profil akan segera tersedia');
};

window.showSettings = function() {
    alert('Fitur Pengaturan akan segera tersedia');
};

window.logout = function() {
    if (confirm('Apakah Anda yakin ingin keluar?')) {
        localStorage.removeItem('userData');
        sessionStorage.removeItem('userData');
        window.location.href = '../../login.html';
    }
};

// Sidebar functions
window.toggleSidebar = function() {
    const sidebar = document.getElementById('dashboardSidebar');
    sidebar.classList.toggle('show');
};

window.toggleSidebarCollapse = function() {
    const sidebar = document.getElementById('dashboardSidebar');
    const icon = document.getElementById('sidebarToggleIcon');
    sidebar.classList.toggle('collapsed');
    icon.className = sidebar.classList.contains('collapsed') ? 'fas fa-indent' : 'fas fa-outdent';
};
```

### **📱 Mobile Auto-Close Functionality**
```javascript
// Add to bindMenuEvents() in ALL dashboards
if (window.innerWidth < 992) {
    const sidebar = document.getElementById('dashboardSidebar');
    sidebar.classList.remove('show');
}
```

### **🎨 CSS Standards**
- **Bootstrap 5.3**: Consistent framework usage
- **FontAwesome 6.4**: Consistent icon library
- **Responsive**: Mobile-first approach
- **Touch Targets**: Minimum 44px for mobile

---

## 📋 **Checklist for New Implementations**

### **🔧 Before Adding New Features**
- [ ] Check if similar feature exists in other roles
- [ ] Ensure consistent naming conventions
- [ ] Verify mobile compatibility
- [ ] Test on all breakpoints

### **📱 Mobile Compatibility**
- [ ] Auto-close sidebar on menu click
- [ ] Proper hamburger menu toggle
- [ ] Touch-friendly interface
- [ ] Responsive layout at ≤355px

### **🎨 UI Consistency**
- [ ] Same color scheme across roles
- [ ] Consistent card layouts
- [ ] Same button styles
- [ ] Consistent typography

### **🔧 JavaScript Consistency**
- [ ] All required functions present
- [ ] Window scope for global functions
- [ ] Error handling implemented
- [ ] Session management consistent

---

## 🔄 **Upgrade Process**

### **1. Identify Changes**
- List all files that need modification
- Group by role (admin, staff, member)
- Note any breaking changes

### **2. Apply Changes Systematically**
- **Admin Role**: Apply to all admin dashboards
- **Staff Role**: Apply to all staff dashboards  
- **Member Role**: Apply to member dashboard
- **Shared Components**: Apply to shared files

### **3. Verify Implementation**
- Test each role separately
- Verify mobile functionality
- Check for JavaScript errors
- Ensure responsive behavior

### **4. Cross-Role Testing**
- Test login/logout flow
- Verify menu functionality
- Check navigation between pages
- Validate responsive design

---

## 📁 **File Structure Reference**

### **🔧 Admin Dashboards**
- `/pages/admin/dashboard.html` (main)
- `/pages/admin/dashboard-simple.html`
- `/pages/admin/dashboard-clean.html`
- `/pages/admin/dashboard-new.html`

### **👤 Staff Dashboards**  
- `/pages/staff/dashboard.html` (main)
- `/pages/staff/dashboard-mantri.html`
- `/pages/staff/dashboard-kasir.html`
- `/pages/staff/dashboard-teller.html`
- `/pages/staff/dashboard-surveyor.html`
- `/pages/staff/dashboard-collector.html`

### **👤 Member Dashboard**
- `/pages/member/dashboard.html` (main)

### **🎨 Shared Resources**
- `/assets/css/dashboard-layout.css`
- `/assets/config/menus.json`
- `/assets/js/auth.js`
- `/assets/js/config.js`

---

## ⚠️ **Common Issues to Avoid**

### **❌ Inconsistent Functions**
- Missing `showProfile()`, `showSettings()`, `logout()`
- Functions not in window scope
- Different function signatures

### **❌ Mobile Issues**
- Sidebar doesn't auto-close on mobile
- No hamburger menu toggle
- Responsive breakpoints inconsistent
- Touch targets too small

### **❌ Navigation Issues**
- Different menu structures per role
- Missing menu items in some roles
- Inconsistent navigation behavior
- Broken page routing

---

## 🎯 **Quality Assurance**

### **✅ Pre-Deployment Checklist**
- [ ] All JavaScript functions present
- [ ] Mobile auto-close sidebar working
- [ ] Responsive design tested
- [ ] No console errors
- [ ] All menu items functional
- [ ] Login/logout flow working

### **✅ Cross-Browser Testing**
- [ ] Chrome/Chromium
- [ ] Firefox
- [ ] Safari (if available)
- [ ] Mobile browsers

### **✅ Performance**
- [ ] Fast page loads
- [ ] Smooth animations
- [ ] No memory leaks
- [ ] Efficient JavaScript

---

## 🔄 **Maintenance**

### **📅 Regular Tasks**
- Check for JavaScript console errors
- Verify mobile functionality
- Test new browser updates
- Update dependencies as needed

### **🔍 Monitoring**
- Watch for broken functionality
- Monitor performance metrics
- Check responsive behavior
- Validate user experience

---

**Remember**: Consistency is key to a professional user experience. Always test across all roles and devices before deploying changes.
