/**
 * Fixed Authentication Script
 * KSP Lam Gabe Jaya v2.0
 */

// Authentication state
const authState = {
    isAuthenticated: false,
    token: null,
    currentUser: null,
    loginAttempts: 0,
    maxLoginAttempts: 5,
    lockoutTime: 15 * 60 * 1000, // 15 minutes
    lockoutUntil: null
};

// DOM elements
const domElements = {};

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
    domElements.togglePasswordBtn = document.getElementById('togglePassword');
    domElements.rememberMeCheckbox = document.getElementById('rememberMe');
    domElements.loginBtn = document.getElementById('loginBtn');
    domElements.forgotPasswordLink = document.getElementById('forgotPasswordLink');
    domElements.passwordIcon = domElements.togglePasswordBtn ? domElements.togglePasswordBtn.querySelector('i') : null;
    domElements.alertContainer = document.querySelector('.alert-container');
    domElements.forgotPasswordForm = document.getElementById('forgotPasswordForm');
    domElements.resetEmailInput = document.getElementById('resetEmailInput');

    console.log('Fixed authentication system initialized');
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

    if (domElements.forgotPasswordLink) {
        domElements.forgotPasswordLink.addEventListener('click', showForgotPassword);
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
        console.log('Found existing session, validating...');

        // For now, just clear it to avoid issues
        clearStoredSession();
        console.log('Session cleared to avoid conflicts');
    }
}

/**
 * Handle login form submission
 */
async function handleLogin(event) {
    // Make event parameter optional
    if (event && event.preventDefault) {
        event.preventDefault();
    }

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
        // Use fixed login API
        const response = await simulateLoginAPIFixed(email, password);

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
 * Fixed login API call
 */
async function simulateLoginAPIFixed(email, password) {
    try {
        console.log('Using FIXED login API call...');

        const formData = new FormData();
        formData.append('action', 'login');
        formData.append('username', email);
        formData.append('password', password);

        // Use BASE_PATH configuration properly - try simple API
        let apiUrl;
        if (window.buildApiUrl && window.APP_CONFIG?.API?.AUTH_SIMPLE) {
            apiUrl = window.buildApiUrl('AUTH_SIMPLE');
        } else if (window.APP_CONFIG?.BASE_PATH) {
            // Fallback with BASE_PATH
            apiUrl = window.location.origin + window.APP_CONFIG.BASE_PATH + '/api/auth_simple.php';
        } else {
            // Final fallback
            apiUrl = window.location.origin + '/mono-v2/api/auth_simple.php';
        }
        console.log('Calling API:', apiUrl);

        const response = await fetch(apiUrl, {
            method: 'POST',
            body: formData
        });

        console.log('Response status:', response.status);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();
        console.log('Response data:', result);

        return result;
    } catch (error) {
        console.error('Fixed API Error:', error);
        return {
            success: false,
            message: 'Koneksi gagal: ' + error.message
        };
    }
}

/**
 * Validate stored token
 */
async function validateStoredToken(token) {
    try {
        // Use BASE_PATH with fallback to simple API
        let apiUrl;
        if (window.buildApiUrlWithFallback) {
            apiUrl = window.buildApiUrlWithFallback('AUTH', 'AUTH_SIMPLE') + '?action=validate&token=' + encodeURIComponent(token);
        } else {
            apiUrl = window.location.origin + '/mono-v2/api/auth_simple.php?action=validate&token=' + encodeURIComponent(token);
        }

        console.log('Validating token at:', apiUrl);

        const response = await fetch(apiUrl);
        const result = await response.json();

        // For simple API, just check if response is successful
        return result.success || (result.message && result.message.includes('Login berhasil'));
    } catch (error) {
        console.error('Token validation error:', error);
        return false;
    }
}

/**
 * Handle successful login
 */
function handleLoginSuccess(data, rememberMe) {
    console.log('Login success received:', data);

    const user = data.user;

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

    console.log('Processed user:', processedUser);

    // Reset login attempts
    authState.loginAttempts = 0;
    authState.lockoutUntil = null;

    // Set authentication state
    authState.isAuthenticated = true;
    authState.currentUser = processedUser;
    authState.token = processedUser.token;

    console.log('Auth state updated:', authState);

    // Update display name
    updateUserNameDisplay(processedUser);

    // Store session data
    const storage = rememberMe ? localStorage : sessionStorage;
    storage.setItem('authToken', processedUser.token);
    storage.setItem('userData', JSON.stringify(processedUser));

    console.log('Session stored in:', rememberMe ? 'localStorage' : 'sessionStorage');

    // Show success message
    showAlert('success', 'Login berhasil! Mengalihkan ke dashboard...');

    // Redirect to dashboard after delay
    console.log('Preparing to redirect to dashboard...');
    setTimeout(() => {
        console.log('Redirecting now...');
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
    if (domElements.passwordInput) {
        domElements.passwordInput.value = '';
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
 * Redirect to dashboard based on user role
 */
function redirectToDashboard() {
    const userRole = authState.currentUser.role;
    console.log('Redirecting user with role:', userRole);

    // Use BASE_PATH configuration or relative paths
    let dashboardPath;

    switch (userRole) {
        case 'Super Admin':
        case 'Admin':
        case 'Manager':
            if (window.buildUrl) {
                dashboardPath = window.buildUrl('pages/admin/dashboard.html');
            } else if (window.APP_CONFIG?.BASE_PATH) {
                dashboardPath = window.location.origin + window.APP_CONFIG.BASE_PATH + '/pages/admin/dashboard.html';
            } else {
                dashboardPath = 'pages/admin/dashboard.html';
            }
            break;
        case 'Teller':
            if (window.buildUrl) {
                dashboardPath = window.buildUrl('pages/staff/dashboard-teller-complete.html');
            } else if (window.APP_CONFIG?.BASE_PATH) {
                dashboardPath = window.location.origin + window.APP_CONFIG.BASE_PATH + '/pages/staff/dashboard-teller-complete.html';
            } else {
                dashboardPath = 'pages/staff/dashboard-teller-complete.html';
            }
            break;
        case 'Staff':
            if (window.buildUrl) {
                dashboardPath = window.buildUrl('pages/staff/dashboard-complete.html');
            } else if (window.APP_CONFIG?.BASE_PATH) {
                dashboardPath = window.location.origin + window.APP_CONFIG.BASE_PATH + '/pages/staff/dashboard-complete.html';
            } else {
                dashboardPath = 'pages/staff/dashboard-complete.html';
            }
            break;
        case 'mantri':
            if (window.buildUrl) {
                dashboardPath = window.buildUrl('pages/staff/dashboard-mantri.html');
            } else if (window.APP_CONFIG?.BASE_PATH) {
                dashboardPath = window.location.origin + window.APP_CONFIG.BASE_PATH + '/pages/staff/dashboard-mantri.html';
            } else {
                dashboardPath = 'pages/staff/dashboard-mantri.html';
            }
            break;
        case 'kasir':
            if (window.buildUrl) {
                dashboardPath = window.buildUrl('pages/staff/dashboard-kasir.html');
            } else if (window.APP_CONFIG?.BASE_PATH) {
                dashboardPath = window.location.origin + window.APP_CONFIG.BASE_PATH + '/pages/staff/dashboard-kasir.html';
            } else {
                dashboardPath = 'pages/staff/dashboard-kasir.html';
            }
            break;
        case 'surveyor':
            if (window.buildUrl) {
                dashboardPath = window.buildUrl('pages/staff/dashboard-surveyor.html');
            } else if (window.APP_CONFIG?.BASE_PATH) {
                dashboardPath = window.location.origin + window.APP_CONFIG.BASE_PATH + '/pages/staff/dashboard-surveyor.html';
            } else {
                dashboardPath = 'pages/staff/dashboard-surveyor.html';
            }
            break;
        case 'collector':
            if (window.buildUrl) {
                dashboardPath = window.buildUrl('pages/staff/dashboard-collector.html');
            } else if (window.APP_CONFIG?.BASE_PATH) {
                dashboardPath = window.location.origin + window.APP_CONFIG.BASE_PATH + '/pages/staff/dashboard-collector.html';
            } else {
                dashboardPath = 'pages/staff/dashboard-collector.html';
            }
            break;
        case 'member':
            if (window.buildUrl) {
                dashboardPath = window.buildUrl('pages/member/dashboard.html');
            } else if (window.APP_CONFIG?.BASE_PATH) {
                dashboardPath = window.location.origin + window.APP_CONFIG.BASE_PATH + '/pages/member/dashboard.html';
            } else {
                dashboardPath = 'pages/member/dashboard.html';
            }
            break;
        default:
            if (window.buildUrl) {
                dashboardPath = window.buildUrl('pages/staff/dashboard-complete.html');
            } else if (window.APP_CONFIG?.BASE_PATH) {
                dashboardPath = window.location.origin + window.APP_CONFIG.BASE_PATH + '/pages/staff/dashboard-complete.html';
            } else {
                dashboardPath = 'pages/staff/dashboard-complete.html';
            }
    }

    console.log('Redirecting to:', dashboardPath);
    console.log('Current location:', window.location.href);

    // Add small delay and redirect
    setTimeout(() => {
        window.location.href = dashboardPath;
    }, 100);
}

/**
 * Update UI display name
 */
function updateUserNameDisplay(user) {
    try {
        if (!user) return;

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

/**
 * Utility functions
 */
function isAccountLocked() {
    return authState.lockoutUntil && Date.now() < authState.lockoutUntil;
}

function setLoginLoading(loading) {
    if (domElements.loginBtn) {
        domElements.loginBtn.disabled = loading;
        if (loading) {
            domElements.loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
        } else {
            domElements.loginBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Masuk';
        }
    }
}

function togglePasswordVisibility() {
    if (domElements.passwordInput.type === 'password') {
        domElements.passwordInput.type = 'text';
        if (domElements.passwordIcon) {
            domElements.passwordIcon.classList.remove('fa-eye');
            domElements.passwordIcon.classList.add('fa-eye-slash');
        }
    } else {
        domElements.passwordInput.type = 'password';
        if (domElements.passwordIcon) {
            domElements.passwordIcon.classList.remove('fa-eye-slash');
            domElements.passwordIcon.classList.add('fa-eye');
        }
    }
}

function showAlert(type, message) {
    if (!domElements.alertContainer) return;

    const alertId = 'alert-' + Date.now();
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" id="${alertId}" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    domElements.alertContainer.insertAdjacentHTML('beforeend', alertHtml);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

function showFieldError(field, message) {
    if (!field) return;

    field.classList.add('is-invalid');

    let feedback = field.parentNode.querySelector('.invalid-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        field.parentNode.appendChild(feedback);
    }
    feedback.textContent = message;
}

function clearFormErrors() {
    const invalidFields = document.querySelectorAll('.is-invalid');
    invalidFields.forEach(field => {
        field.classList.remove('is-invalid');
    });

    const feedbackElements = document.querySelectorAll('.invalid-feedback');
    feedbackElements.forEach(element => {
        element.remove();
    });
}

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

function showForgotPassword() {
    showAlert('info', 'Halaman lupa kata sandi akan segera tersedia');
}

function handleForgotPassword(event) {
    event.preventDefault();
    showAlert('info', 'Fitur lupa kata sandi dalam pengembangan');
}

// Export functions for global access
window.authState = authState;
window.clearStoredSession = clearStoredSession;
window.handleLogin = handleLogin;
window.logout = function () {
    if (confirm('Apakah Anda yakin ingin keluar?')) {
        clearStoredSession();
        window.location.href = '/mono-v2/login.html';
    }
};
