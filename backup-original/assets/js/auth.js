/**
 * KSP Lam Gabe Jaya - Authentication Module
 * 100% English JavaScript Variables and Functions
 * Indonesian UI Text Only
 */

// Global authentication state
let authState = {
    isAuthenticated: false,
    currentUser: null,
    token: null,
    loginAttempts: 0,
    maxLoginAttempts: 3,
    lockoutTime: 15 * 60 * 1000, // 15 minutes
    lockoutUntil: null
};

// DOM elements cache
const domElements = {
    loginForm: null,
    emailInput: null,
    passwordInput: null,
    loginBtn: null,
    loginBtnText: null,
    togglePasswordBtn: null,
    passwordIcon: null,
    rememberMeCheckbox: null,
    alertContainer: null,
    forgotPasswordForm: null,
    resetEmailInput: null
};

// Update UI display name if element exists
function updateUserNameDisplay(user) {
    try {
        // Handle null/undefined user data safely
        if (!user) return;

        // Get display name with fallbacks
        const displayName = user.full_name || user.name || user.username || 'User';

        // Update all possible user name elements
        const userElements = ['userName', 'headerUserName', 'sidebarUserName'];
        userElements.forEach(elementId => {
            const el = document.getElementById(elementId);
            if (el) {
                el.textContent = displayName;
            }
        });

        // Update role elements
        const displayRole = user.role || 'Staff';
        const roleElements = ['headerUserRole', 'sidebarUserRole'];
        roleElements.forEach(elementId => {
            const el = document.getElementById(elementId);
            if (el) {
                el.textContent = displayRole;
            }
        });
    } catch (e) {
        console.warn('Error updating user display:', e);
    }
}

// Initialize authentication module
document.addEventListener('DOMContentLoaded', function () {
    initializeAuth();
    bindEventListeners();
    checkExistingSession();
});

/**
 * Initialize authentication system
 */
function initializeAuth() {
    // Cache DOM elements
    domElements.loginForm = document.getElementById('loginForm');
    domElements.emailInput = document.getElementById('emailInput');
    domElements.passwordInput = document.getElementById('passwordInput');
    domElements.loginBtn = document.getElementById('loginBtn');
    domElements.loginBtnText = document.getElementById('loginBtnText');
    domElements.togglePasswordBtn = document.getElementById('togglePassword');
    domElements.passwordIcon = document.getElementById('passwordIcon');
    domElements.rememberMeCheckbox = document.getElementById('rememberMe');
    domElements.alertContainer = document.querySelector('.alert-container');
    domElements.forgotPasswordForm = document.getElementById('forgotPasswordForm');
    domElements.resetEmailInput = document.getElementById('resetEmailInput');

    console.log('Authentication system initialized');
}

/**
 * Bind event listeners
 */
function bindEventListeners() {
    if (domElements.loginForm) {
        domElements.loginForm.addEventListener('submit', handleLogin);
    }

    if (domElements.togglePasswordBtn) {
        domElements.togglePasswordBtn.addEventListener('click', togglePasswordVisibility);
    }

    if (domElements.emailInput) {
        domElements.emailInput.addEventListener('input', clearFieldError);
    }

    if (domElements.passwordInput) {
        domElements.passwordInput.addEventListener('input', clearFieldError);
    }

    if (domElements.forgotPasswordForm) {
        domElements.forgotPasswordForm.addEventListener('submit', handleForgotPassword);
    }

    console.log('Event listeners bound');
}

/**
 * Check for existing session
 */
function checkExistingSession() {
    const token = localStorage.getItem('authToken') || sessionStorage.getItem('authToken');
    const userData = localStorage.getItem('userData') || sessionStorage.getItem('userData');

    if (token && userData) {
        try {
            // Validate token before setting auth state
            validateStoredToken(token).then(isValid => {
                if (isValid) {
                    authState.isAuthenticated = true;
                    authState.token = token;
                    authState.currentUser = JSON.parse(userData);

                    // Update display name
                    updateUserNameDisplay(authState.currentUser);

                    // Avoid redirect loop if already on a dashboard page
                    const currentPath = window.location.pathname;
                    if (currentPath.includes('/pages/') && currentPath.includes('dashboard')) {
                        return;
                    }

                    // Redirect to dashboard
                    redirectToDashboard();
                } else {
                    // Token invalid, clear stored session
                    clearStoredSession();
                    console.log('Stored token invalid, session cleared');
                }
            }).catch(error => {
                console.error('Token validation error:', error);
                clearStoredSession();
            });
        } catch (error) {
            console.error('Session check error:', error);
            clearStoredSession();
        }
    }
}

/**
 * Validate stored token
 */
async function validateStoredToken(token) {
    try {
        // Use absolute URL to avoid path resolution issues
        const baseUrl = window.location.origin + window.location.pathname.replace(/\/[^\/]+$/, '/');
        const response = await fetch(`${baseUrl}api/auth.php?action=validate&token=${encodeURIComponent(token)}`);
        const result = await response.json();
        return result.success;
    } catch (error) {
        console.error('Token validation API error:', error);
        return false;
    }
}

/**
 * Clear stored session
 */
function clearStoredSession() {
    localStorage.removeItem('authToken');
    localStorage.removeItem('userData');
    sessionStorage.removeItem('authToken');
    sessionStorage.removeItem('userData');

    // Reset auth state
    authState.isAuthenticated = false;
    authState.token = null;
    authState.currentUser = null;
}

/**
 * Handle login form submission
 */
async function handleLogin(event) {
    event.preventDefault();

    // Check if account is locked
    if (isAccountLocked()) {
        showAlert('danger', 'Akun terkunci. Silakan coba lagi dalam 15 menit.');
        return;
    }

    // Get form data
    const email = domElements.emailInput.value.trim();
    const password = domElements.passwordInput.value;
    const rememberMe = domElements.rememberMeCheckbox.checked;

    // Validate form
    if (!validateLoginForm(email, password)) {
        return;
    }

    // Show loading state
    setLoginLoading(true);

    try {
        // Simulate API call (replace with actual API)
        const response = await simulateLoginAPI(email, password);

        if (response.success) {
            // Login successful
            handleLoginSuccess(response.data, rememberMe);
        } else {
            // Login failed
            handleLoginFailure(response.message);
        }
    } catch (error) {
        console.error('Login error:', error);
        handleLoginFailure('Terjadi kesalahan. Silakan coba lagi.');
    } finally {
        setLoginLoading(false);
    }
}

/**
 * Validate login form
 */
function validateLoginForm(email, password) {
    let isValid = true;

    // Reset previous errors
    clearFormErrors();

    // Validate email/username
    if (!email) {
        showFieldError(domElements.emailInput, 'Email atau username harus diisi');
        isValid = false;
    } else if (email.includes('@') && !isValidEmail(email)) {
        // Only validate email format if it contains @ symbol
        showFieldError(domElements.emailInput, 'Email tidak valid');
        isValid = false;
    }

    // Validate password
    if (!password) {
        showFieldError(domElements.passwordInput, 'Kata sandi harus diisi');
        isValid = false;
    } else if (password.length < 6) {
        showFieldError(domElements.passwordInput, 'Kata sandi minimal 6 karakter');
        isValid = false;
    }

    return isValid;
}

/**
 * Handle successful login
 */
function handleLoginSuccess(data, rememberMe) {
    const user = data.user || {};

    // Ensure user object has required fields with fallbacks
    const processedUser = {
        id: user.id || 0,
        name: user.full_name || user.name || user.username || 'User',
        email: user.email || '',
        role: user.role || 'Staff',
        token: user.token || data.token || '',
        is_active: user.is_active ?? 1,
        permissions: user.permissions || null,
        last_login: user.last_login || null
    };

    // Reset login attempts
    authState.loginAttempts = 0;
    authState.lockoutUntil = null;

    // Set authentication state
    authState.isAuthenticated = true;
    authState.currentUser = processedUser;

    // Update display name
    updateUserNameDisplay(processedUser);

    // Set authentication token
    authState.token = processedUser.token;

    // Store session data
    const storage = rememberMe ? localStorage : sessionStorage;
    storage.setItem('authToken', processedUser.token);
    storage.setItem('userData', JSON.stringify(processedUser));

    // Show success message
    showAlert('success', 'Login berhasil! Mengalihkan ke dashboard...');

    // Redirect to dashboard after delay
    setTimeout(() => {
        redirectToDashboard();
    }, 1500);
}

/**
 * Handle login failure
 */
function handleLoginFailure(message) {
    // Increment login attempts
    authState.loginAttempts++;

    // Check if should lock account
    if (authState.loginAttempts >= authState.maxLoginAttempts) {
        authState.lockoutUntil = Date.now() + authState.lockoutTime;
        showAlert('danger', 'Terlalu banyak percobaan login. Akun terkunci selama 15 menit.');
    } else {
        const remainingAttempts = authState.maxLoginAttempts - authState.loginAttempts;
        showAlert('danger', `${message}. Sisa percobaan: ${remainingAttempts}`);
    }

    // Clear password field
    domElements.passwordInput.value = '';
    domElements.passwordInput.focus();
}

/**
 * Toggle password visibility
 */
function togglePasswordVisibility() {
    const passwordField = domElements.passwordInput;
    const currentType = passwordField.getAttribute('type');
    const newType = currentType === 'password' ? 'text' : 'password';

    passwordField.setAttribute('type', newType);

    // Update icon
    if (domElements.passwordIcon) {
        const iconClass = newType === 'password' ? 'fa-eye' : 'fa-eye-slash';
        domElements.passwordIcon.className = `fas ${iconClass}`;
    }
}

/**
 * Set login button loading state
 */
function setLoginLoading(isLoading) {
    if (isLoading) {
        domElements.loginBtn.disabled = true;
        domElements.loginBtn.classList.add('loading');
        domElements.loginBtnText.textContent = 'Memproses...';
    } else {
        domElements.loginBtn.disabled = false;
        domElements.loginBtn.classList.remove('loading');
        domElements.loginBtnText.textContent = 'Masuk';
    }
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    field.classList.add('is-invalid');

    // Find or create error message element
    let errorElement = field.parentNode.querySelector('.invalid-feedback');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'invalid-feedback';
        field.parentNode.appendChild(errorElement);
    }

    errorElement.textContent = message;
}

/**
 * Clear field error
 */
function clearFieldError(event) {
    const field = event.target;
    field.classList.remove('is-invalid');
}

/**
 * Clear all form errors
 */
function clearFormErrors() {
    const invalidFields = document.querySelectorAll('.is-invalid');
    invalidFields.forEach(field => {
        field.classList.remove('is-invalid');
    });
}

/**
 * Check if account is locked
 */
function isAccountLocked() {
    return authState.lockoutUntil && Date.now() < authState.lockoutUntil;
}

/**
 * Validate email format
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Global API error handler
 */
function handleApiError(response, error) {
    if (response.status === 401) {
        // Token expired or invalid
        console.warn('Authentication failed - clearing session');
        clearAuthSession();
        showAlert('danger', 'Session expired. Please login again.');
        
        // Redirect to login after delay
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 2000);
        
        return true; // Error handled
    }
    
    if (response.status === 403) {
        showAlert('danger', 'Access denied. You do not have permission for this action.');
        return true;
    }
    
    if (response.status >= 500) {
        showAlert('danger', 'Server error. Please try again later.');
        return true;
    }
    
    return false; // Not handled
}

/**
 * Enhanced fetch with error handling
 */
async function secureFetch(url, options = {}) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            }
        });
        
        // Check for authentication errors
        if (response.status === 401) {
            handleApiError(response, { message: 'Authentication failed' });
            throw new Error('Authentication failed');
        }
        
        if (response.status === 403) {
            handleApiError(response, { message: 'Access denied' });
            throw new Error('Access denied');
        }
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}
function showAlert(type, message) {
    // Create alert container if it doesn't exist
    if (!domElements.alertContainer) {
        domElements.alertContainer = document.createElement('div');
        domElements.alertContainer.className = 'alert-container';
        domElements.alertContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
        document.body.appendChild(domElements.alertContainer);
    }

    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show custom-alert" role="alert">
            <i class="fas fa-${getAlertIcon(type)} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    domElements.alertContainer.insertAdjacentHTML('beforeend', alertHtml);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alerts = domElements.alertContainer.querySelectorAll('.alert');
        if (alerts.length > 0) {
            alerts[0].remove();
        }
    }, 5000);
}

/**
 * Get alert icon based on type
 */
function getAlertIcon(type) {
    const icons = {
        success: 'check-circle',
        danger: 'exclamation-triangle',
        warning: 'exclamation-circle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

/**
 * Redirect to dashboard based on user role
 */
function redirectToDashboard() {
    const userRole = authState.currentUser.role;

    // Use dynamic routing with BASE_PATH
    let dashboardPath;

    switch (userRole) {
        case 'Super Admin':
        case 'Admin':
        case 'Manager':
            dashboardPath = 'pages/admin/dashboard.html';
            break;
        case 'Teller':
            dashboardPath = 'pages/staff/dashboard-teller-complete.html';
            break;
        case 'Staff':
            dashboardPath = 'pages/staff/dashboard-complete.html';
            break;
        case 'mantri':
            dashboardPath = 'pages/staff/dashboard-mantri.html';
            break;
        case 'kasir':
            dashboardPath = 'pages/staff/dashboard-kasir.html';
            break;
        case 'surveyor':
            dashboardPath = 'pages/staff/dashboard-surveyor.html';
            break;
        case 'collector':
            dashboardPath = 'pages/staff/dashboard-collector.html';
            break;
        case 'member':
            dashboardPath = 'pages/member/dashboard.html';
            break;
        default:
            dashboardPath = 'pages/staff/dashboard.html';
    }

    // Build URL with dynamic base path
    const dashboardUrl = window.buildUrl ? window.buildUrl(dashboardPath) : dashboardPath;
    window.location.href = dashboardUrl;
}

/**
 * Actual login API call
 */
async function simulateLoginAPI(email, password) {
    try {
        const formData = new FormData();
        formData.append('action', 'login');
        formData.append('username', email); // Changed from email to username
        formData.append('password', password);

        // Build absolute API URL to avoid path resolution issues
        const baseUrl = window.location.origin + window.location.pathname.replace(/\/[^\/]+$/, '/');
        const apiUrl = window.buildApiUrl ? window.buildApiUrl('AUTH') : `${baseUrl}api/auth.php`;

        const response = await fetch(apiUrl, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API Error:', error);
        return {
            success: false,
            message: 'Terjadi kesalahan koneksi ke server'
        };
    }
}

/**
 * Handle forgot password
 */
function showForgotPassword() {
    const modal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
    modal.show();
}

/**
 * Send reset password link
 */
async function sendResetLink() {
    const email = domElements.resetEmailInput.value.trim();

    if (!email || !isValidEmail(email)) {
        showAlert('danger', 'Email tidak valid');
        return;
    }

    try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1000));

        showAlert('success', 'Link reset kata sandi telah dikirim ke email Anda');

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
        modal.hide();

        // Clear form
        domElements.resetEmailInput.value = '';
    } catch (error) {
        console.error('Reset password error:', error);
        showAlert('danger', 'Terjadi kesalahan. Silakan coba lagi.');
    }
}

/**
 * Handle forgot password form submission
 */
function handleForgotPassword(event) {
    event.preventDefault();
    sendResetLink();
}

/**
 * Show register page (placeholder)
 */
function showRegister() {
    showAlert('info', 'Halaman pendaftaran akan segera tersedia');
}

/**
 * Show service detail (placeholder)
 */
function showServiceDetail(service) {
    showAlert('info', `Detail layanan ${service} akan segera tersedia`);
}

/**
 * Show contact form (placeholder)
 */
function showContactForm() {
    showAlert('info', 'Formulir kontak akan segera tersedia');
}

/**
 * Logout function
 */
function logout() {
    // Show confirmation dialog
    if (confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
        // Clear session data
        localStorage.removeItem('authToken');
        localStorage.removeItem('userData');
        sessionStorage.removeItem('authToken');
        sessionStorage.removeItem('userData');

        // Reset auth state
        authState = {
            isAuthenticated: false,
            currentUser: null,
            token: null,
            loginAttempts: 0,
            maxLoginAttempts: 3,
            lockoutTime: 15 * 60 * 1000,
            lockoutUntil: null
        };

        // Show logout message
        if (typeof showAlert === 'function') {
            showAlert('info', 'Anda telah keluar dari sistem. Mengalihkan ke halaman login...');
        }

        // Redirect to login after short delay
        setTimeout(() => {
            // Use dynamic base path for redirect
            const loginUrl = window.buildUrl ? window.buildUrl('login.html') : 'login.html';
            window.location.href = loginUrl;
        }, 1000);
    }
}

// Export functions for global access
window.authFunctions = {
    logout,
    showAlert,
    showForgotPassword,
    showRegister
};

// Also export logout directly for global access
window.logout = logout;
