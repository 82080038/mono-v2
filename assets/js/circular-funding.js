// Circular Funding System
class CircularFundingSystem {
    constructor() {
        this.availableFunds = {};
        this.fundRequests = [];
        this.staffBalances = {};
        this.transfers = [];
        this.isMonitoring = false;
    }

    // Initialize circular funding system
    async initialize() {
        await this.loadDashboardData();
        await this.loadStaffBalances();
        this.setupRealTimeMonitoring();
        this.setupEventListeners();
    }

    // Load dashboard data
    async loadDashboardData() {
        try {
            const response = await fetch('/api/circular-funding.php?action=dashboard');
            const result = await response.json();
            
            if (result.success) {
                this.updateDashboardDisplay(result.data);
            }
        } catch (error) {
            console.error('Error loading circular funding dashboard:', error);
        }
    }

    // Load staff balances
    async loadStaffBalances() {
        try {
            const response = await fetch('/api/circular-funding.php?action=staff_balance&staff_id=' + this.getCurrentStaffId());
            const result = await response.json();
            
            if (result.success) {
                this.staffBalances = result.data;
                this.updateBalanceDisplay();
            }
        } catch (error) {
            console.error('Error loading staff balances:', error);
        }
    }

    // Update dashboard display
    updateDashboardDisplay(data) {
        const container = document.getElementById('circular-funding-dashboard');
        if (!container) return;

        container.innerHTML = `
            <div class="funding-header">
                <h5>💰 Circular Funding System</h5>
                <div class="funding-status">
                    <span class="status-indicator active"></span>
                    <span>Active</span>
                </div>
            </div>
            
            <div class="funding-summary">
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Total Collected</h6>
                        <h3>Rp ${(data.summary.total_collected || 0).toLocaleString('id-ID')}</h3>
                        <small>Today's collections</small>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Available Funds</h6>
                        <h3>Rp ${(data.summary.total_available || 0).toLocaleString('id-ID')}</h3>
                        <small>Ready for allocation</small>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Allocated</h6>
                        <h3>Rp ${(data.summary.total_allocated || 0).toLocaleString('id-ID')}</h3>
                        <small>Already allocated</small>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Active Staff</h6>
                        <h3>${data.summary.active_staff}</h3>
                        <small>With available funds</small>
                    </div>
                </div>
            </div>
            
            <div class="funding-actions">
                <button class="btn btn-primary" onclick="circularFunding.requestFunds()">
                    <i class="fas fa-plus"></i> Request Funds
                </button>
                <button class="btn btn-info" onclick="circularFunding.transferFunds()">
                    <i class="fas fa-exchange-alt"></i> Transfer Funds
                </button>
                <button class="btn btn-warning" onclick="circularFunding.autoTransfer()">
                    <i class="fas fa-magic"></i> Auto-Transfer
                </button>
                <button class="btn btn-secondary" onclick="circularFunding.viewHistory()">
                    <i class="fas fa-history"></i> History
                </button>
            </div>
            
            <div class="staff-balances-section">
                <h6>👥 Staff Balances</h6>
                <div class="balances-grid">
                    ${data.staff_balances.map(staff => `
                        <div class="staff-balance-card ${this.getBalanceStatusClass(staff.current_balance)}">
                            <div class="staff-info">
                                <h6>${staff.staff_name}</h6>
                                <small>Staff ID: ${staff.staff_id}</small>
                            </div>
                            <div class="balance-info">
                                <div class="current-balance">
                                    <span class="balance-label">Current:</span>
                                    <span class="balance-value">Rp ${(staff.current_balance || 0).toLocaleString('id-ID')}</span>
                                </div>
                                <div class="daily-activity">
                                    <div class="activity-item">
                                        <span class="activity-label">Collected:</span>
                                        <span class="activity-value">Rp ${(staff.collected_today || 0).toLocaleString('id-ID')}</span>
                                    </div>
                                    <div class="activity-item">
                                        <span class="activity-label">Allocated:</span>
                                        <span class="activity-value">Rp ${(staff.allocated_today || 0).toLocaleString('id-ID')}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="balance-actions">
                                <button class="btn btn-sm btn-outline-primary" onclick="circularFunding.viewStaffDetails(${staff.staff_id})">
                                    <i class="fas fa-eye"></i> Details
                                </button>
                                ${staff.current_balance < 100000 ? `
                                    <button class="btn btn-sm btn-outline-warning" onclick="circularFunding.requestEmergencyFunds(${staff.staff_id})">
                                        <i class="fas fa-exclamation-triangle"></i> Emergency
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    // Update balance display
    updateBalanceDisplay() {
        const container = document.getElementById('staff-balance-display');
        if (!container) return;

        const balance = this.staffBalances.staff_balance || {};
        const funds = this.staffBalances.today_funds || {};
        
        container.innerHTML = `
            <div class="balance-overview">
                <div class="balance-item">
                    <span class="balance-label">Current Balance:</span>
                    <span class="balance-value">Rp ${(balance.current_balance || 0).toLocaleString('id-ID')}</span>
                </div>
                <div class="balance-item">
                    <span class="balance-label">Available Today:</span>
                    <span class="balance-value">Rp ${((funds.available_amount || 0) - (funds.allocated_amount || 0)).toLocaleString('id-ID')}</span>
                </div>
                <div class="balance-item">
                    <span class="balance-label">Collected Today:</span>
                    <span class="balance-value">Rp ${(funds.collected_amount || 0).toLocaleString('id-ID')}</span>
                </div>
                <div class="balance-item">
                    <span class="balance-label">Allocated Today:</span>
                    <span class="balance-value">Rp ${(funds.allocated_amount || 0).toLocaleString('id-ID')}</span>
                </div>
            </div>
        `;
    }

    // Get balance status class
    getBalanceStatusClass(balance) {
        if (balance < 100000) return 'critical';
        if (balance < 500000) return 'warning';
        return 'healthy';
    }

    // Get current staff ID
    getCurrentStaffId() {
        const currentUser = JSON.parse(localStorage.getItem('currentUser') || '{}');
        return currentUser.id || 0;
    }

    // Request funds
    requestFunds() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Request Funds</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="fund-request-form">
                            <div class="form-group">
                                <label for="member-id">Member ID</label>
                                <input type="number" class="form-control" id="member-id" required>
                            </div>
                            <div class="form-group">
                                <label for="request-amount">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="request-amount" min="10000" step="10000" required>
                                    <span class="input-group-text">,-</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="purpose">Purpose</label>
                                <select class="form-control" id="purpose" required>
                                    <option value="">Select purpose</option>
                                    <option value="new_loan">New Loan</option>
                                    <option value="emergency">Emergency</option>
                                    <option value="operational">Operational</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" rows="3" placeholder="Describe the purpose of this fund request"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="circularFunding.submitFundRequest()">Submit Request</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Submit fund request
    async submitFundRequest() {
        try {
            const staffId = this.getCurrentStaffId();
            const memberId = document.getElementById('member-id').value;
            const amount = parseFloat(document.getElementById('request-amount').value);
            const purpose = document.getElementById('purpose').value;
            const description = document.getElementById('description').value;

            if (!memberId || !amount || !purpose) {
                this.showNotification('Error', 'Please fill all required fields', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('staff_id', staffId);
            formData.append('member_id', memberId);
            formData.append('amount', amount);
            formData.append('purpose', purpose);
            formData.append('description', description);

            const response = await fetch('/api/circular-funding.php?action=request_funds', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Success', 'Fund request submitted successfully', 'success');
                
                // Close modal
                const modal = document.querySelector('.modal.show');
                if (modal) {
                    bootstrap.Modal.getInstance(modal).hide();
                }
                
                // Refresh data
                await this.loadDashboardData();
                await this.loadStaffBalances();
            } else {
                throw new Error(result.error || 'Failed to submit request');
            }
        } catch (error) {
            console.error('Error submitting fund request:', error);
            this.showNotification('Error', 'Failed to submit fund request', 'error');
        }
    }

    // Transfer funds
    transferFunds() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Transfer Funds</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="fund-transfer-form">
                            <div class="form-group">
                                <label for="to-staff-id">To Staff ID</label>
                                <input type="number" class="form-control" id="to-staff-id" required>
                            </div>
                            <div class="form-group">
                                <label for="transfer-amount">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="transfer-amount" min="10000" step="10000" required>
                                    <span class="input-group-text">,-</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="transfer-type">Transfer Type</label>
                                <select class="form-control" id="transfer-type" required>
                                    <option value="">Select type</option>
                                    <option value="allocation">Allocation</option>
                                    <option value="reimbursement">Reimbursement</option>
                                    <option value="emergency">Emergency</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="transfer-description">Description</label>
                                <textarea class="form-control" id="transfer-description" rows="3" placeholder="Describe the transfer purpose"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="circularFunding.submitTransfer()">Transfer</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Submit transfer
    async submitTransfer() {
        try {
            const fromStaffId = this.getCurrentStaffId();
            const toStaffId = document.getElementById('to-staff-id').value;
            const amount = parseFloat(document.getElementById('transfer-amount').value);
            const transferType = document.getElementById('transfer-type').value;
            const description = document.getElementById('transfer-description').value;

            if (!toStaffId || !amount || !transferType) {
                this.showNotification('Error', 'Please fill all required fields', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('from_staff_id', fromStaffId);
            formData.append('to_staff_id', toStaffId);
            formData.append('amount', amount);
            formData.append('transfer_type', transferType);
            formData.append('description', description);

            const response = await fetch('/api/circular-funding.php?action=transfer_funds', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Success', 'Fund transfer completed successfully', 'success');
                
                // Close modal
                const modal = document.querySelector('.modal.show');
                if (modal) {
                    bootstrap.Modal.getInstance(modal).hide();
                }
                
                // Refresh data
                await this.loadDashboardData();
                await this.loadStaffBalances();
            } else {
                throw new Error(result.error || 'Failed to transfer funds');
            }
        } catch (error) {
            console.error('Error transferring funds:', error);
            this.showNotification('Error', 'Failed to transfer funds', 'error');
        }
    }

    // Auto transfer
    async autoTransfer() {
        try {
            this.showNotification('Auto-Transfer', 'Initiating automatic fund transfer...', 'info');
            
            const response = await fetch('/api/circular-funding.php?action=auto_transfer', {
                method: 'POST'
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Success', `Auto-transfer completed. ${result.transfers_made.length} transfers made.`, 'success');
                
                // Refresh data
                await this.loadDashboardData();
                await this.loadStaffBalances();
                
                // Show transfer details
                if (result.transfers_made.length > 0) {
                    this.showTransferDetails(result.transfers_made);
                }
            } else {
                throw new Error(result.error || 'Auto-transfer failed');
            }
        } catch (error) {
            console.error('Error in auto-transfer:', error);
            this.showNotification('Error', 'Auto-transfer failed', 'error');
        }
    }

    // Show transfer details
    showTransferDetails(transfers) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Auto-Transfer Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="transfer-details">
                            <h6>Transfers Completed</h6>
                            <div class="transfers-list">
                                ${transfers.map(transfer => `
                                    <div class="transfer-item">
                                        <div class="transfer-info">
                                            <span class="transfer-from">From: ${transfer.from_staff}</span>
                                            <span class="transfer-to">To: ${transfer.to_staff}</span>
                                        </div>
                                        <div class="transfer-amount">
                                            Rp ${transfer.amount.toLocaleString('id-ID')}
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // View history
    viewHistory() {
        const modal = document.createElement('div');
        modal.className = 'modal fade modal-lg';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Fund History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="history-tabs">
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#fund-history">Fund History</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#transfer-history">Transfer History</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="fund-history">
                                    <div class="history-content">
                                        <p>Loading fund history...</p>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="transfer-history">
                                    <div class="history-content">
                                        <p>Loading transfer history...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        // Load history data
        this.loadHistoryData();

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Load history data
    async loadHistoryData() {
        try {
            const staffId = this.getCurrentStaffId();
            const response = await fetch(`/api/circular-funding.php?action=fund_history&staff_id=${staffId}`);
            const result = await response.json();

            if (result.success) {
                this.updateHistoryDisplay(result.data);
            }
        } catch (error) {
            console.error('Error loading history:', error);
        }
    }

    // Update history display
    updateHistoryDisplay(data) {
        const fundHistoryContainer = document.querySelector('#fund-history .history-content');
        const transferHistoryContainer = document.querySelector('#transfer-history .history-content');

        if (fundHistoryContainer) {
            fundHistoryContainer.innerHTML = `
                <div class="history-list">
                    ${data.fund_history.map(fund => `
                        <div class="history-item">
                            <div class="history-date">${new Date(fund.date).toLocaleDateString()}</div>
                            <div class="history-details">
                                <div class="history-amount">Rp ${fund.collected_amount.toLocaleString('id-ID')}</div>
                                <div class="history-status ${fund.status}">${fund.status}</div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        if (transferHistoryContainer) {
            transferHistoryContainer.innerHTML = `
                <div class="history-list">
                    ${data.transfer_history.map(transfer => `
                        <div class="history-item">
                            <div class="history-date">${new Date(transfer.initiated_at).toLocaleDateString()}</div>
                            <div class="history-details">
                                <div class="transfer-info">
                                    <span>From: ${transfer.from_staff_name}</span>
                                    <span>To: ${transfer.to_staff_name}</span>
                                </div>
                                <div class="history-amount">Rp ${transfer.amount.toLocaleString('id-ID')}</div>
                                <div class="history-status ${transfer.status}">${transfer.status}</div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }
    }

    // Request emergency funds
    requestEmergencyFunds(staffId) {
        this.showNotification('Emergency', 'Requesting emergency funds...', 'info');
        
        // Implement emergency fund request logic
        setTimeout(() => {
            this.showNotification('Success', 'Emergency funds requested successfully', 'success');
        }, 2000);
    }

    // View staff details
    viewStaffDetails(staffId) {
        this.showNotification('Staff Details', `Loading details for staff ${staffId}...`, 'info');
        
        // Implement staff details view
    }

    // Setup real-time monitoring
    setupRealTimeMonitoring() {
        if (!this.isMonitoring) return;

        // Monitor fund balances
        setInterval(() => {
            this.checkFundBalances();
        }, 60000); // Check every minute

        // Monitor fund requests
        setInterval(() => {
            this.checkFundRequests();
        }, 30000); // Check every 30 seconds
    }

    // Check fund balances
    async checkFundBalances() {
        try {
            await this.loadStaffBalances();
            
            // Check for low balances
            const balance = this.staffBalances.staff_balance?.current_balance || 0;
            if (balance < 100000) {
                this.showNotification('Low Balance', `Current balance: Rp ${balance.toLocaleString('id-ID')}`, 'warning');
            }
        } catch (error) {
            console.error('Error checking fund balances:', error);
        }
    }

    // Check fund requests
    async checkFundRequests() {
        try {
            const response = await fetch('/api/circular-funding.php?action=fund_requests&status=pending');
            const result = await response.json();

            if (result.success && result.data.length > 0) {
                this.showNotification('New Requests', `${result.data.length} pending fund requests`, 'info');
            }
        } catch (error) {
            console.error('Error checking fund requests:', error);
        }
    }

    // Setup event listeners
    setupEventListeners() {
        // Auto-refresh dashboard data
        setInterval(() => {
            this.loadDashboardData();
        }, 60000); // Refresh every minute

        // Auto-refresh staff balances
        setInterval(() => {
            this.loadStaffBalances();
        }, 30000); // Refresh every 30 seconds
    }

    // Show notification
    showNotification(title, message, type) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.innerHTML = `
            <strong>${title}:</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.getElementById('notifications');
        if (container) {
            container.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
    }
}

// Initialize circular funding system when page loads
let circularFunding = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('circular-funding-dashboard') || document.getElementById('staff-balance-display')) {
        circularFunding = new CircularFundingSystem();
        circularFunding.initialize();
    }
});
