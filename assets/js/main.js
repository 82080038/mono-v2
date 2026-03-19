/**
 * KSP Lam Gabe Jaya - Main JavaScript Module
 * 100% English JavaScript Variables and Functions
 * Indonesian UI Text Only
 */

// Application state
const appState = {
    currentPage: 'home',
    userRole: null,
    systemConfig: {
        apiBaseUrl: '/api',
        version: '2.0.0',
        environment: 'production'
    }
};

// Initialize application
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
    bindGlobalEventListeners();
    initializeAnimations();
});

/**
 * Initialize application
 */
function initializeApp() {
    console.log('KSP Lam Gabe Jaya v2.0 - Application initialized');
    
    // Detect current page
    detectCurrentPage();
    
    // Check authentication status
    checkAuthenticationStatus();
    
    // Initialize tooltips
    initializeTooltips();
    
    console.log('Application ready');
}

/**
 * Detect current page
 */
function detectCurrentPage() {
    const path = window.location.pathname;
    const filename = path.split('/').pop();
    
    if (filename === 'index.html' || filename === '') {
        appState.currentPage = 'home';
    } else if (filename === 'login.html') {
        appState.currentPage = 'login';
    } else if (filename.includes('dashboard')) {
        appState.currentPage = 'dashboard';
    } else {
        appState.currentPage = 'other';
    }
    
    console.log('Current page detected:', appState.currentPage);
}

/**
 * Check authentication status
 */
function checkAuthenticationStatus() {
    const token = localStorage.getItem('authToken') || sessionStorage.getItem('authToken');
    const userData = localStorage.getItem('userData') || sessionStorage.getItem('userData');
    
    if (token && userData) {
        try {
            const user = JSON.parse(userData);
            appState.userRole = user.role;
            console.log('User authenticated:', user.role);
        } catch (error) {
            console.error('Error parsing user data:', error);
        }
    } else {
        console.log('User not authenticated');
    }
}

/**
 * Bind global event listeners
 */
function bindGlobalEventListeners() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(event) {
            event.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                smoothScrollTo(targetElement);
            }
        });
    });
    
    // Form validation for all forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.hasAttribute('data-no-validate')) {
                if (!validateForm(form)) {
                    event.preventDefault();
                }
            }
        });
    });
    
    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    });
    
    console.log('Global event listeners bound');
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize animations
 */
function initializeAnimations() {
    // Animate elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe elements with animation classes
    document.querySelectorAll('.animate-on-scroll').forEach(element => {
        observer.observe(element);
    });
}

/**
 * Smooth scroll to element
 */
function smoothScrollTo(targetElement) {
    const targetPosition = targetElement.offsetTop - 70; // Account for fixed navbar
    const startPosition = window.pageYOffset;
    const distance = targetPosition - startPosition;
    const duration = 800;
    let start = null;
    
    function animation(currentTime) {
        if (start === null) start = currentTime;
        const timeElapsed = currentTime - start;
        const run = easeInOutQuad(timeElapsed, startPosition, distance, duration);
        window.scrollTo(0, run);
        
        if (timeElapsed < duration) {
            requestAnimationFrame(animation);
        }
    }
    
    requestAnimationFrame(animation);
}

/**
 * Easing function for smooth scrolling
 */
function easeInOutQuad(t, b, c, d) {
    t /= d/2;
    if (t < 1) return c/2*t*t + b;
    t--;
    return -c/2 * (t*(t-2) - 1) + b;
}

/**
 * Validate form
 */
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        if (input.hasAttribute('required') && !input.value.trim()) {
            showFieldError(input, 'Field ini harus diisi');
            isValid = false;
        } else if (input.type === 'email' && input.value && !isValidEmail(input.value)) {
            showFieldError(input, 'Email tidak valid');
            isValid = false;
        } else {
            clearFieldError(input);
        }
    });
    
    return isValid;
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    field.classList.add('is-invalid');
    
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
function clearFieldError(field) {
    field.classList.remove('is-invalid');
}

/**
 * Validate email format
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Show loading state
 */
function showLoading(element, text = 'Memproses...') {
    element.disabled = true;
    element.dataset.originalText = element.textContent;
    element.textContent = text;
    element.classList.add('loading');
}

/**
 * Hide loading state
 */
function hideLoading(element) {
    element.disabled = false;
    element.textContent = element.dataset.originalText || element.textContent;
    element.classList.remove('loading');
    delete element.dataset.originalText;
}

/**
 * Show notification
 */
function showNotification(type, title, message, duration = 5000) {
    const notificationHtml = `
        <div class="notification notification-${type} animate-in">
            <div class="notification-header">
                <i class="fas fa-${getNotificationIcon(type)} me-2"></i>
                <strong>${title}</strong>
                <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
            <div class="notification-body">
                ${message}
            </div>
        </div>
    `;
    
    // Create notification container if it doesn't exist
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.className = 'notification-container';
        document.body.appendChild(container);
    }
    
    container.insertAdjacentHTML('beforeend', notificationHtml);
    
    // Auto-remove after duration
    setTimeout(() => {
        const notifications = container.querySelectorAll('.notification');
        if (notifications.length > 0) {
            notifications[0].classList.add('animate-out');
            setTimeout(() => {
                if (notifications[0].parentNode) {
                    notifications[0].remove();
                }
            }, 300);
        }
    }, duration);
}

/**
 * Get notification icon based on type
 */
function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-triangle',
        warning: 'exclamation-circle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

/**
 * Format currency
 */
function formatCurrency(amount, currency = 'IDR') {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

/**
 * Format date
 */
function formatDate(date, options = {}) {
    const defaultOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        locale: 'id-ID'
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    if (typeof date === 'string') {
        date = new Date(date);
    }
    
    return date.toLocaleDateString(finalOptions.locale, {
        year: finalOptions.year,
        month: finalOptions.month,
        day: finalOptions.day
    });
}

/**
 * Format date time
 */
function formatDateTime(date, options = {}) {
    const defaultOptions = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        locale: 'id-ID'
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    if (typeof date === 'string') {
        date = new Date(date);
    }
    
    return date.toLocaleString(finalOptions.locale, {
        year: finalOptions.year,
        month: finalOptions.month,
        day: finalOptions.day,
        hour: finalOptions.hour,
        minute: finalOptions.minute
    });
}

/**
 * Debounce function
 */
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

/**
 * Throttle function
 */
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Copy text to clipboard
 */
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showNotification('success', 'Berhasil', 'Teks berhasil disalin');
    } catch (err) {
        console.error('Failed to copy text: ', err);
        showNotification('error', 'Gagal', 'Tidak bisa menyalin teks');
    }
}

/**
 * Download data as file
 */
function downloadFile(data, filename, type = 'text/plain') {
    const blob = new Blob([data], { type: type });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', filename);
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

/**
 * Export data to CSV
 */
function exportToCSV(data, filename = 'export.csv') {
    if (!Array.isArray(data) || data.length === 0) {
        showNotification('warning', 'Peringatan', 'Tidak ada data untuk diekspor');
        return;
    }
    
    // Get headers from first object
    const headers = Object.keys(data[0]);
    
    // Create CSV content
    const csvContent = [
        headers.join(','),
        ...data.map(row => 
            headers.map(header => {
                const value = row[header];
                // Escape quotes and wrap in quotes if contains comma
                return typeof value === 'string' && value.includes(',') 
                    ? `"${value.replace(/"/g, '""')}"` 
                    : value;
            }).join(',')
        )
    ].join('\n');
    
    downloadFile(csvContent, filename, 'text/csv');
    showNotification('success', 'Berhasil', 'Data berhasil diekspor');
}

/**
 * Print element
 */
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) {
        showNotification('error', 'Error', 'Elemen tidak ditemukan');
        return;
    }
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
            <head>
                <title>Print</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { padding: 20px; }
                    @media print { .no-print { display: none !important; } }
                </style>
            </head>
            <body>
                ${element.outerHTML}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
    printWindow.close();
}

/**
 * Handle keyboard shortcuts
 */
document.addEventListener('keydown', function(event) {
    // Ctrl+P for print
    if (event.ctrlKey && event.key === 'p') {
        event.preventDefault();
        const printElement = document.querySelector('[data-print-target]');
        if (printElement) {
            printElement(printElement.dataset.printTarget);
        }
    }
    
    // Escape to close modals
    if (event.key === 'Escape') {
        const openModal = document.querySelector('.modal.show');
        if (openModal) {
            const modal = bootstrap.Modal.getInstance(openModal);
            if (modal) {
                modal.hide();
            }
        }
    }
});

// Export functions for global access
window.appFunctions = {
    showNotification,
    formatCurrency,
    formatDate,
    formatDateTime,
    copyToClipboard,
    downloadFile,
    exportToCSV,
    printElement,
    showLoading,
    hideLoading,
    debounce,
    throttle
};

console.log('Main application module loaded');
