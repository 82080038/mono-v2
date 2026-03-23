/**
 * Enhanced Frontend JavaScript for KSP System
 * Includes: Form validation, error handling, mobile responsiveness, user feedback
 */

// Global configuration
const KSP_CONFIG = {
    apiBaseUrl: '/mono-v2/api/business-logic-enhanced.php',
    refreshInterval: 30000, // 30 seconds
    maxRetries: 3,
    retryDelay: 1000,
    mobileBreakpoint: 768
};

// Utility functions
const Utils = {
    // Format numbers with Indonesian locale
    formatNumber: (num) => {
        return new Intl.NumberFormat('id-ID').format(num);
    },
    
    // Format dates with Indonesian locale
    formatDate: (dateString, options = {}) => {
        const date = new Date(dateString);
        return date.toLocaleString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            ...options
        });
    },
    
    // Debounce function for search
    debounce: (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // Check if mobile device
    isMobile: () => {
        return window.innerWidth <= KSP_CONFIG.mobileBreakpoint;
    },
    
    // Show loading state
    showLoading: (element, text = 'Loading...') => {
        if (element) {
            element.innerHTML = `
                <div class="text-center p-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">${text}</span>
                    </div>
                    <div class="mt-2">${text}</div>
                </div>
            `;
        }
    },
    
    // Show error state
    showError: (element, message) => {
        if (element) {
            element.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${message}
                </div>
            `;
        }
    },
    
    // Show success message
    showSuccess: (message, duration = 3000) => {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, duration);
    },
    
    // Show error message
    showErrorMessage: (message, duration = 5000) => {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, duration);
    }
};

// Enhanced API client with error handling and retries
class APIClient {
    constructor() {
        this.retryCount = 0;
    }
    
    async request(action, data = {}, method = 'POST') {
        const url = new URL(KSP_CONFIG.apiBaseUrl);
        url.searchParams.set('action', action);
        
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        if (method === 'POST' && Object.keys(data).length > 0) {
            options.body = new URLSearchParams(data);
        }
        
        try {
            const response = await fetch(url, options);
            
            // Check if response is OK
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            
            // Check if API call was successful
            if (!result.success) {
                throw new Error(result.message || 'API call failed');
            }
            
            // Reset retry count on success
            this.retryCount = 0;
            
            return result;
            
        } catch (error) {
            console.error('API Error:', error);
            
            // Retry logic
            if (this.retryCount < KSP_CONFIG.maxRetries) {
                this.retryCount++;
                console.log(`Retrying API call (attempt ${this.retryCount}/${KSP_CONFIG.maxRetries})`);
                
                await new Promise(resolve => setTimeout(resolve, KSP_CONFIG.retryDelay));
                return this.request(action, data, method);
            }
            
            // Reset retry count after max retries
            this.retryCount = 0;
            
            // Show user-friendly error
            Utils.showErrorMessage(`API Error: ${error.message}`);
            
            throw error;
        }
    }
    
    // Convenience methods
    async get(action, params = {}) {
        return this.request(action, params, 'GET');
    }
    
    async post(action, data = {}) {
        return this.request(action, data, 'POST');
    }
}

// Form validation system
class FormValidator {
    constructor(formElement) {
        this.form = formElement;
        this.rules = {};
        this.errors = {};
        this.setupValidation();
    }
    
    addRule(fieldName, rules) {
        this.rules[fieldName] = rules;
        return this;
    }
    
    setupValidation() {
        // Real-time validation on input
        this.form.addEventListener('input', (e) => {
            this.validateField(e.target.name);
        });
        
        // Validate on blur
        this.form.addEventListener('blur', (e) => {
            this.validateField(e.target.name);
        }, true);
    }
    
    validateField(fieldName) {
        const field = this.form.elements[fieldName];
        const value = field.value.trim();
        const rules = this.rules[fieldName];
        
        if (!rules) return true;
        
        const errors = [];
        
        // Required validation
        if (rules.required && !value) {
            errors.push(`${this.getFieldLabel(fieldName)} is required`);
        }
        
        // Skip further validation if field is empty and not required
        if (!value && !rules.required) {
            this.clearFieldError(fieldName);
            return true;
        }
        
        // Type validation
        if (rules.type) {
            switch (rules.type) {
                case 'email':
                    if (!this.isValidEmail(value)) {
                        errors.push('Please enter a valid email address');
                    }
                    break;
                case 'number':
                    if (!this.isValidNumber(value)) {
                        errors.push('Please enter a valid number');
                    }
                    break;
                case 'phone':
                    if (!this.isValidPhone(value)) {
                        errors.push('Please enter a valid phone number');
                    }
                    break;
            }
        }
        
        // Length validation
        if (rules.minLength && value.length < rules.minLength) {
            errors.push(`${this.getFieldLabel(fieldName)} must be at least ${rules.minLength} characters`);
        }
        
        if (rules.maxLength && value.length > rules.maxLength) {
            errors.push(`${this.getFieldLabel(fieldName)} must not exceed ${rules.maxLength} characters`);
        }
        
        // Range validation for numbers
        if (rules.min && this.isValidNumber(value) && parseFloat(value) < rules.min) {
            errors.push(`${this.getFieldLabel(fieldName)} must be at least ${rules.min}`);
        }
        
        if (rules.max && this.isValidNumber(value) && parseFloat(value) > rules.max) {
            errors.push(`${this.getFieldLabel(fieldName)} must not exceed ${rules.max}`);
        }
        
        // Custom validation
        if (rules.custom && typeof rules.custom === 'function') {
            const customError = rules.custom(value);
            if (customError) {
                errors.push(customError);
            }
        }
        
        if (errors.length > 0) {
            this.showFieldError(fieldName, errors[0]);
            return false;
        } else {
            this.clearFieldError(fieldName);
            return true;
        }
    }
    
    validateAll() {
        let isValid = true;
        this.errors = {};
        
        for (const fieldName in this.rules) {
            if (!this.validateField(fieldName)) {
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    getFieldLabel(fieldName) {
        const field = this.form.elements[fieldName];
        const label = this.form.querySelector(`label[for="${fieldName}"]`);
        return label ? label.textContent.replace('*', '').trim() : fieldName;
    }
    
    showFieldError(fieldName, message) {
        const field = this.form.elements[fieldName];
        field.classList.add('is-invalid');
        
        // Remove existing error message
        this.clearFieldError(fieldName);
        
        // Add new error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        errorDiv.id = `${fieldName}-error`;
        
        field.parentNode.appendChild(errorDiv);
        this.errors[fieldName] = message;
    }
    
    clearFieldError(fieldName) {
        const field = this.form.elements[fieldName];
        field.classList.remove('is-invalid');
        
        const errorDiv = document.getElementById(`${fieldName}-error`);
        if (errorDiv) {
            errorDiv.remove();
        }
        
        delete this.errors[fieldName];
    }
    
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    isValidNumber(value) {
        return !isNaN(value) && isFinite(value);
    }
    
    isValidPhone(phone) {
        return /^[0-9+\-\s()]+$/.test(phone) && phone.replace(/\D/g, '').length >= 10;
    }
}

// Mobile responsiveness handler
class MobileHandler {
    constructor() {
        this.isMobile = Utils.isMobile();
        this.setupEventListeners();
        this.adjustUI();
    }
    
    setupEventListeners() {
        // Handle window resize
        window.addEventListener('resize', Utils.debounce(() => {
            const wasMobile = this.isMobile;
            this.isMobile = Utils.isMobile();
            
            if (wasMobile !== this.isMobile) {
                this.adjustUI();
            }
        }, 250));
        
        // Handle orientation change
        window.addEventListener('orientationchange', () => {
            setTimeout(() => this.adjustUI(), 100);
        });
    }
    
    adjustUI() {
        if (this.isMobile) {
            this.enableMobileMode();
        } else {
            this.enableDesktopMode();
        }
    }
    
    enableMobileMode() {
        // Hide sidebar on mobile
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.add('mobile-hidden');
        }
        
        // Add mobile classes
        document.body.classList.add('mobile-mode');
        
        // Adjust table responsiveness
        this.makeTablesResponsive();
        
        // Optimize form layouts
        this.optimizeFormsForMobile();
    }
    
    enableDesktopMode() {
        // Show sidebar on desktop
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.remove('mobile-hidden');
        }
        
        // Remove mobile classes
        document.body.classList.remove('mobile-mode');
    }
    
    makeTablesResponsive() {
        const tables = document.querySelectorAll('.table');
        tables.forEach(table => {
            if (!table.closest('.table-responsive')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });
    }
    
    optimizeFormsForMobile() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            // Add mobile form classes
            form.classList.add('mobile-form');
            
            // Optimize button layouts
            const buttons = form.querySelectorAll('.btn-group');
            buttons.forEach(group => {
                group.classList.add('btn-group-vertical');
            });
        });
    }
}

// Enhanced transaction processor with validation
class TransactionProcessor {
    constructor() {
        this.api = new APIClient();
        this.validator = null;
        this.setupForm();
    }
    
    setupForm() {
        const form = document.getElementById('depositForm');
        if (!form) return;
        
        // Setup validation rules
        this.validator = new FormValidator(form)
            .addRule('member_id', { required: true, type: 'number', min: 1 })
            .addRule('amount', { required: true, type: 'number', min: 1000, max: 100000000 })
            .addRule('account_type', { required: true, minLength: 3 })
            .addRule('payment_method', { required: true, minLength: 2 })
            .addRule('description', { required: false, maxLength: 500 });
        
        // Handle form submission
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.processDeposit();
        });
    }
    
    async processDeposit() {
        if (!this.validator.validateAll()) {
            Utils.showErrorMessage('Please fix the errors in the form');
            return;
        }
        
        const formData = {
            member_id: parseInt(document.getElementById('member_id').value),
            amount: parseFloat(document.getElementById('amount').value),
            account_type: document.getElementById('account_type').value,
            payment_method: document.getElementById('payment_method').value,
            description: document.getElementById('description').value
        };
        
        try {
            this.setSubmitButtonState(true, 'Processing...');
            
            const response = await this.api.post('process_deposit', formData);
            
            if (response.success) {
                this.displayReceipt(response.data);
                await this.loadTodayData();
                Utils.showSuccess('Deposit processed successfully!');
            }
        } catch (error) {
            console.error('Deposit processing error:', error);
            Utils.showErrorMessage('Failed to process deposit. Please try again.');
        } finally {
            this.setSubmitButtonState(false, 'Process Deposit');
        }
    }
    
    setSubmitButtonState(loading, text) {
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.disabled = loading;
            submitBtn.innerHTML = loading ? 
                '<i class="fas fa-spinner fa-spin me-2"></i>' + text :
                '<i class="fas fa-save me-2"></i>' + text;
        }
    }
    
    displayReceipt(receiptData) {
        const receiptPreview = document.getElementById('receiptPreview');
        const receiptContent = document.getElementById('receiptContent');
        const depositForm = document.getElementById('depositForm');
        
        if (receiptContent) {
            receiptContent.innerHTML = `
                <div class="receipt-header">
                    <h5>Deposit Receipt</h5>
                    <p><strong>Transaction Code:</strong> ${receiptData.transaction_code}</p>
                    <p><strong>Date:</strong> ${Utils.formatDate(receiptData.timestamp)}</p>
                </div>
                <hr>
                <div class="receipt-body">
                    <p><strong>Member:</strong> ${receiptData.member_name}</p>
                    <p><strong>Account Type:</strong> ${receiptData.account_type}</p>
                    <p><strong>Amount:</strong> <span class="text-success">Rp ${Utils.formatNumber(receiptData.amount)}</span></p>
                    <p><strong>Payment Method:</strong> ${receiptData.payment_method}</p>
                    <p><strong>New Balance:</strong> <span class="text-primary">Rp ${Utils.formatNumber(receiptData.new_balance)}</span></p>
                    <p><strong>Teller:</strong> ${receiptData.teller}</p>
                </div>
                <div class="receipt-actions mt-3">
                    <button class="btn btn-success btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print Receipt
                    </button>
                    <button class="btn btn-outline-secondary btn-sm ms-2" onclick="transactionProcessor.newTransaction()">
                        <i class="fas fa-plus me-2"></i>New Transaction
                    </button>
                </div>
            `;
        }
        
        if (receiptPreview) receiptPreview.style.display = 'block';
        if (depositForm) depositForm.style.display = 'none';
    }
    
    newTransaction() {
        const receiptPreview = document.getElementById('receiptPreview');
        const depositForm = document.getElementById('depositForm');
        
        if (receiptPreview) receiptPreview.style.display = 'none';
        if (depositForm) {
            depositForm.style.display = 'block';
            depositForm.reset();
        }
        
        // Clear selected member
        this.clearSelectedMember();
    }
    
    async loadTodayData() {
        try {
            await Promise.all([
                this.loadTodaySummary(),
                this.loadTodayTransactions()
            ]);
        } catch (error) {
            console.error('Failed to load today\'s data:', error);
        }
    }
    
    async loadTodaySummary() {
        try {
            const response = await this.api.get('get_today_summary');
            const summary = response.data;
            
            // Update UI with summary data
            const elements = {
                'todayTransactions': summary.total_transactions,
                'totalDeposits': 'Rp ' + Utils.formatNumber(summary.total_deposits),
                'totalWithdrawals': 'Rp ' + Utils.formatNumber(summary.total_withdrawals),
                'totalAmount': 'Rp ' + Utils.formatNumber(summary.total_amount)
            };
            
            for (const [id, value] of Object.entries(elements)) {
                const element = document.getElementById(id);
                if (element) element.textContent = value;
            }
        } catch (error) {
            console.error('Failed to load today\'s summary:', error);
        }
    }
    
    async loadTodayTransactions() {
        try {
            const response = await this.api.get('get_today_transactions');
            this.displayTransactions(response.data);
        } catch (error) {
            console.error('Failed to load today\'s transactions:', error);
        }
    }
    
    displayTransactions(transactions) {
        const tbody = document.getElementById('transactionsTable');
        if (!tbody) return;
        
        if (transactions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No transactions today</td></tr>';
            return;
        }
        
        tbody.innerHTML = transactions.map(transaction => {
            const typeClass = transaction.transaction_type === 'credit' ? 'text-success' : 'text-danger';
            const typeIcon = transaction.transaction_type === 'credit' ? 'fa-arrow-down' : 'fa-arrow-up';
            const typeName = transaction.transaction_type === 'credit' ? 'Deposit' : 'Withdrawal';
            
            return `
                <tr>
                    <td><small>${transaction.transaction_code}</small></td>
                    <td>${transaction.member_name}</td>
                    <td><span class="${typeClass}"><i class="fas ${typeIcon} me-1"></i>${typeName}</span></td>
                    <td class="${typeClass}">Rp ${Utils.formatNumber(transaction.amount)}</td>
                    <td><small>${transaction.payment_method}</small></td>
                    <td><small>${Utils.formatDate(transaction.created_at)}</small></td>
                    <td><span class="badge bg-success">${transaction.status}</span></td>
                </tr>
            `;
        }).join('');
    }
    
    clearSelectedMember() {
        // Clear member selection
        document.getElementById('memberSearch').value = '';
        document.getElementById('searchResults').innerHTML = '';
        document.getElementById('selectedMemberInfo').style.display = 'none';
        
        // Reset account options
        const accountSelect = document.getElementById('accountType');
        if (accountSelect) {
            accountSelect.innerHTML = '<option value="">Select Account Type</option>';
        }
    }
}

// Member search with debouncing
class MemberSearch {
    constructor() {
        this.api = new APIClient();
        this.searchTimeout = null;
        this.selectedMember = null;
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        const searchInput = document.getElementById('memberSearch');
        if (searchInput) {
            searchInput.addEventListener('input', Utils.debounce((e) => {
                const searchTerm = e.target.value.trim();
                if (searchTerm.length >= 3) {
                    this.searchMembers(searchTerm);
                } else {
                    this.clearSearchResults();
                }
            }, 500));
            
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.clearSearch();
                }
            });
        }
    }
    
    async searchMembers(searchTerm) {
        try {
            this.showSearchLoading(true);
            
            const response = await this.api.get('search_member', { q: searchTerm });
            
            this.displaySearchResults(response.data);
        } catch (error) {
            console.error('Search error:', error);
            this.showSearchError('Failed to search members');
        } finally {
            this.showSearchLoading(false);
        }
    }
    
    displaySearchResults(members) {
        const resultsContainer = document.getElementById('searchResults');
        if (!resultsContainer) return;
        
        if (members.length === 0) {
            resultsContainer.innerHTML = '<div class="alert alert-warning">No members found</div>';
            return;
        }
        
        const resultsHTML = members.map(member => `
            <div class="list-group-item member-search-result clickable" onclick="memberSearch.selectMember(${member.id})">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${member.full_name}</h6>
                    <small>${member.member_number}</small>
                </div>
                <p class="mb-1">
                    <i class="fas fa-phone me-1"></i>${member.phone || '-'}
                    ${member.email ? `<br><i class="fas fa-envelope me-1"></i>${member.email}` : ''}
                </p>
                <small class="text-success">
                    <i class="fas fa-check-circle me-1"></i>Active
                </small>
            </div>
        `).join('');
        
        resultsContainer.innerHTML = `<div class="list-group">${resultsHTML}</div>`;
    }
    
    async selectMember(memberId) {
        try {
            const response = await this.api.get('get_member_accounts', { member_id: memberId });
            
            if (response.success && response.data.length > 0) {
                this.selectedMember = response.data[0];
                this.displaySelectedMember();
                this.loadAccountOptions();
            } else {
                Utils.showErrorMessage('No accounts found for this member');
            }
        } catch (error) {
            console.error('Select member error:', error);
            Utils.showErrorMessage('Failed to load member data');
        }
    }
    
    displaySelectedMember() {
        if (!this.selectedMember) return;
        
        document.getElementById('memberSearch').value = this.selectedMember.full_name;
        this.clearSearchResults();
        
        const memberInfo = document.getElementById('selectedMemberInfo');
        const memberDetails = document.getElementById('memberDetails');
        
        if (memberDetails) {
            memberDetails.innerHTML = `
                <strong>${this.selectedMember.full_name}</strong><br>
                Member Number: ${this.selectedMember.member_number || '-'}<br>
                Account Number: ${this.selectedMember.account_number}<br>
                Current Balance: <strong>Rp ${Utils.formatNumber(this.selectedMember.balance)}</strong>
            `;
        }
        
        if (memberInfo) memberInfo.style.display = 'block';
    }
    
    async loadAccountOptions() {
        try {
            const response = await this.api.get('get_member_accounts', { member_id: this.selectedMember.member_id });
            
            const accountSelect = document.getElementById('accountType');
            if (accountSelect) {
                accountSelect.innerHTML = '<option value="">Select Account Type</option>';
                
                response.data.forEach(account => {
                    accountSelect.innerHTML += `<option value="${account.account_type}">${account.account_name} (Balance: Rp ${Utils.formatNumber(account.balance)})</option>`;
                });
            }
        } catch (error) {
            console.error('Load accounts error:', error);
        }
    }
    
    showSearchLoading(show) {
        const loadingElement = document.querySelector('.search-loading');
        if (loadingElement) {
            loadingElement.style.display = show ? 'block' : 'none';
        }
    }
    
    showSearchError(message) {
        const resultsContainer = document.getElementById('searchResults');
        if (resultsContainer) {
            resultsContainer.innerHTML = `<div class="alert alert-danger">${message}</div>`;
        }
    }
    
    clearSearchResults() {
        const resultsContainer = document.getElementById('searchResults');
        if (resultsContainer) {
            resultsContainer.innerHTML = '';
        }
    }
    
    clearSearch() {
        document.getElementById('memberSearch').value = '';
        this.clearSearchResults();
        this.clearSelectedMember();
    }
    
    clearSelectedMember() {
        this.selectedMember = null;
        document.getElementById('selectedMemberInfo').style.display = 'none';
        
        const accountSelect = document.getElementById('accountType');
        if (accountSelect) {
            accountSelect.innerHTML = '<option value="">Select Account Type</option>';
        }
    }
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if mobile and apply class immediately
    if (window.innerWidth <= 768) {
        document.body.classList.add('mobile-mode');
    }
    
    // Initialize mobile handler
    window.mobileHandler = new MobileHandler();
    
    // Initialize transaction processor
    window.transactionProcessor = new TransactionProcessor();
    
    // Initialize member search
    window.memberSearch = new MemberSearch();
    
    // Load initial data
    if (window.transactionProcessor) {
        window.transactionProcessor.loadTodayData();
    }
    
    // Setup auto-refresh for dashboard data
    setInterval(() => {
        if (window.transactionProcessor) {
            window.transactionProcessor.loadTodayData();
        }
    }, KSP_CONFIG.refreshInterval);
    
    console.log('KSP Enhanced Frontend initialized');
});

// Export for global access
window.KSP = {
    Utils,
    APIClient,
    FormValidator,
    MobileHandler,
    TransactionProcessor,
    MemberSearch
};
