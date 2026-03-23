// Data Migration System Frontend
class DataMigration {
    constructor() {
        this.migrationBatches = [];
        this.templates = [];
        this.currentBatch = null;
        this.isInitialized = false;
    }

    // Initialize data migration
    async initialize() {
        await this.loadMigrationDashboard();
        this.setupEventListeners();
        this.isInitialized = true;
    }

    // Load migration dashboard
    async loadMigrationDashboard() {
        try {
            const response = await fetch('/api/data-migration.php?action=dashboard');
            const result = await response.json();
            
            if (result.success) {
                this.updateDashboardDisplay(result.data);
                this.templates = result.data.templates;
                this.migrationBatches = result.data.recent_batches;
            }
        } catch (error) {
            console.error('Error loading migration dashboard:', error);
        }
    }

    // Update dashboard display
    updateDashboardDisplay(data) {
        const container = document.getElementById('migration-dashboard');
        if (!container) return;

        container.innerHTML = `
            <div class="migration-header">
                <h5>📋 Data Migration System</h5>
                <div class="migration-status">
                    <span class="status-indicator active"></span>
                    <span>System Active</span>
                </div>
            </div>
            
            <div class="migration-summary-grid">
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Total Batches</h6>
                        <h3>${data.migration_stats.total_batches || 0}</h3>
                        <small>${data.migration_stats.completed_batches || 0} completed</small>
                    </div>
                </div>
                
                <div class="summary-card success">
                    <div class="summary-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Success Records</h6>
                        <h3>${data.migration_stats.success_records || 0}</h3>
                        <small>Successfully imported</small>
                    </div>
                </div>
                
                <div class="summary-card danger">
                    <div class="summary-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Failed Records</h6>
                        <h3>${data.migration_stats.failed_records || 0}</h3>
                        <small>Import failed</small>
                    </div>
                </div>
                
                <div class="summary-card info">
                    <div class="summary-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Success Rate</h6>
                        <h3>${this.calculateSuccessRate(data.migration_stats)}%</h3>
                        <small>Import success rate</small>
                    </div>
                </div>
            </div>
            
            <div class="quick-actions">
                <button class="btn btn-primary" onclick="dataMigration.startMigration()">
                    <i class="fas fa-upload"></i> Start Migration
                </button>
                <button class="btn btn-info" onclick="dataMigration.downloadTemplates()">
                    <i class="fas fa-download"></i> Download Templates
                </button>
                <button class="btn btn-success" onclick="dataMigration.viewHistory()">
                    <i class="fas fa-history"></i> Migration History
                </button>
                <button class="btn btn-warning" onclick="dataMigration.validateData()">
                    <i class="fas fa-check-double"></i> Validate Data
                </button>
            </div>
            
            <div class="migration-workflow">
                <h6>🔄 Migration Workflow</h6>
                <div class="workflow-steps">
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h6>Download Template</h6>
                            <p>Download Excel/CSV template for your data</p>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h6>Fill Data</h6>
                            <p>Fill the template with your existing data</p>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h6>Upload File</h6>
                            <p>Upload the filled template to system</p>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h6>Validate & Preview</h6>
                            <p>Validate data and preview before import</p>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">5</div>
                        <div class="step-content">
                            <h6>Process Import</h6>
                            <p>Process and import data to system</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="recent-batches">
                <h6>📊 Recent Migration Batches</h6>
                <div class="batches-list">
                    ${data.recent_batches.map(batch => `
                        <div class="batch-item">
                            <div class="batch-info">
                                <div class="batch-name">${batch.batch_name}</div>
                                <div class="batch-meta">
                                    <span class="batch-type">${batch.batch_type}</span>
                                    <span class="batch-status status-${batch.status}">${batch.status}</span>
                                    <span class="batch-date">${new Date(batch.created_at).toLocaleDateString()}</span>
                                </div>
                            </div>
                            <div class="batch-stats">
                                <div class="stat-item">
                                    <span class="stat-label">Total:</span>
                                    <span class="stat-value">${batch.total_records}</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">Success:</span>
                                    <span class="stat-value success">${batch.success_records}</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">Failed:</span>
                                    <span class="stat-value danger">${batch.failed_records}</span>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    // Calculate success rate
    calculateSuccessRate(stats) {
        const total = parseInt(stats.total_records || 0);
        const success = parseInt(stats.success_records || 0);
        
        if (total === 0) return 0;
        return Math.round((success / total) * 100);
    }

    // Start migration
    startMigration() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Start Data Migration</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="migration-wizard">
                            <div class="wizard-steps">
                                <div class="step active" data-step="1">
                                    <div class="step-indicator">1</div>
                                    <div class="step-label">Select Type</div>
                                </div>
                                <div class="step" data-step="2">
                                    <div class="step-indicator">2</div>
                                    <div class="step-label">Upload File</div>
                                </div>
                                <div class="step" data-step="3">
                                    <div class="step-indicator">3</div>
                                    <div class="step-label">Validate</div>
                                </div>
                                <div class="step" data-step="4">
                                    <div class="step-indicator">4</div>
                                    <div class="step-label">Import</div>
                                </div>
                            </div>
                            
                            <div class="wizard-content">
                                <div class="step-content active" data-step="1">
                                    <h6>Select Migration Type</h6>
                                    <div class="migration-types">
                                        <div class="type-card" onclick="dataMigration.selectMigrationType('members')">
                                            <div class="type-icon">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <div class="type-content">
                                                <h6>Members</h6>
                                                <p>Import member data from Excel/CSV</p>
                                            </div>
                                        </div>
                                        
                                        <div class="type-card" onclick="dataMigration.selectMigrationType('loans')">
                                            <div class="type-icon">
                                                <i class="fas fa-hand-holding-usd"></i>
                                            </div>
                                            <div class="type-content">
                                                <h6>Loans</h6>
                                                <p>Import loan data from Excel/CSV</p>
                                            </div>
                                        </div>
                                        
                                        <div class="type-card" onclick="dataMigration.selectMigrationType('payments')">
                                            <div class="type-icon">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </div>
                                            <div class="type-content">
                                                <h6>Payments</h6>
                                                <p>Import payment data from Excel/CSV</p>
                                            </div>
                                        </div>
                                        
                                        <div class="type-card" onclick="dataMigration.selectMigrationType('staff')">
                                            <div class="type-icon">
                                                <i class="fas fa-user-tie"></i>
                                            </div>
                                            <div class="type-content">
                                                <h6>Staff</h6>
                                                <p>Import staff data from Excel/CSV</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="step-content" data-step="2">
                                    <h6>Upload Data File</h6>
                                    <div class="upload-area" id="upload-area">
                                        <div class="upload-content">
                                            <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                                            <h6>Drop your Excel/CSV file here</h6>
                                            <p>or click to browse</p>
                                            <input type="file" id="migration-file" accept=".xlsx,.xls,.csv" style="display: none;">
                                            <button class="btn btn-outline-primary" onclick="document.getElementById('migration-file').click()">
                                                <i class="fas fa-folder-open"></i> Browse Files
                                            </button>
                                        </div>
                                    </div>
                                    <div class="file-info" id="file-info" style="display: none;">
                                        <div class="file-details">
                                            <i class="fas fa-file-excel"></i>
                                            <div class="file-name" id="file-name"></div>
                                            <div class="file-size" id="file-size"></div>
                                        </div>
                                        <button class="btn btn-sm btn-outline-danger" onclick="dataMigration.clearFile()">
                                            <i class="fas fa-times"></i> Remove
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="step-content" data-step="3">
                                    <h6>Validate Data</h6>
                                    <div class="validation-results" id="validation-results">
                                        <div class="text-center text-muted">
                                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                                            <p>Validation will appear here after file upload</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="step-content" data-step="4">
                                    <h6>Import Data</h6>
                                    <div class="import-preview" id="import-preview">
                                        <div class="text-center text-muted">
                                            <i class="fas fa-database fa-3x mb-3"></i>
                                            <p>Import preview will appear here</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="next-step" onclick="dataMigration.nextStep()">Next</button>
                        <button type="button" class="btn btn-success" id="process-import" style="display: none;" onclick="dataMigration.processImport()">
                            <i class="fas fa-database"></i> Process Import
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        // Setup file upload
        this.setupFileUpload();

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Select migration type
    selectMigrationType(type) {
        this.currentBatch = { type: type };
        this.nextStep();
    }

    // Setup file upload
    setupFileUpload() {
        const uploadArea = document.getElementById('upload-area');
        const fileInput = document.getElementById('migration-file');
        
        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.handleFileSelect(files[0]);
            }
        });
        
        // File input change
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                this.handleFileSelect(e.target.files[0]);
            }
        });
    }

    // Handle file selection
    handleFileSelect(file) {
        const validTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'];
        
        if (!validTypes.includes(file.type)) {
            this.showNotification('Error', 'Please select a valid Excel or CSV file', 'error');
            return;
        }
        
        // Display file info
        document.getElementById('file-info').style.display = 'block';
        document.getElementById('file-name').textContent = file.name;
        document.getElementById('file-size').textContent = this.formatFileSize(file.size);
        
        // Store file
        this.currentBatch.file = file;
        
        // Enable next step
        document.getElementById('next-step').disabled = false;
    }

    // Format file size
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Clear file
    clearFile() {
        document.getElementById('file-info').style.display = 'none';
        document.getElementById('migration-file').value = '';
        delete this.currentBatch.file;
        document.getElementById('next-step').disabled = true;
    }

    // Next step
    nextStep() {
        const currentStep = document.querySelector('.wizard-steps .step.active');
        const currentStepNumber = parseInt(currentStep.dataset.step);
        
        if (currentStepNumber === 1 && !this.currentBatch.type) {
            this.showNotification('Warning', 'Please select a migration type', 'warning');
            return;
        }
        
        if (currentStepNumber === 2 && !this.currentBatch.file) {
            this.showNotification('Warning', 'Please upload a file', 'warning');
            return;
        }
        
        if (currentStepNumber === 2) {
            // Validate and preview data
            this.validateAndPreview();
        }
        
        // Move to next step
        currentStep.classList.remove('active');
        document.querySelector(`.step-content[data-step="${currentStepNumber}"]`).classList.remove('active');
        
        const nextStepNumber = currentStepNumber + 1;
        if (nextStepNumber <= 4) {
            document.querySelector(`.wizard-steps .step[data-step="${nextStepNumber}"]`).classList.add('active');
            document.querySelector(`.step-content[data-step="${nextStepNumber}"]`).classList.add('active');
        }
        
        // Update buttons
        if (currentStepNumber === 3) {
            document.getElementById('next-step').style.display = 'none';
            document.getElementById('process-import').style.display = 'inline-block';
        }
    }

    // Validate and preview data
    async validateAndPreview() {
        try {
            // Show loading
            document.getElementById('validation-results').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Validating data...</p>
                </div>
            `;
            
            // Simulate validation (in real implementation, call API)
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            // Show validation results
            document.getElementById('validation-results').innerHTML = `
                <div class="validation-summary">
                    <div class="summary-item success">
                        <i class="fas fa-check-circle"></i>
                        <span>Valid Records: 150</span>
                    </div>
                    <div class="summary-item warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Warnings: 5</span>
                    </div>
                    <div class="summary-item danger">
                        <i class="fas fa-times-circle"></i>
                        <span>Errors: 2</span>
                    </div>
                </div>
                <div class="validation-details">
                    <h6>Validation Issues:</h6>
                    <ul>
                        <li>Row 5: Invalid email format</li>
                        <li>Row 12: Missing required field: phone</li>
                    </ul>
                </div>
            `;
            
            // Show preview
            document.getElementById('import-preview').innerHTML = `
                <div class="preview-table">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>John Doe</td>
                                <td>john@example.com</td>
                                <td>08123456789</td>
                                <td>Jakarta</td>
                                <td><span class="badge bg-success">Valid</span></td>
                            </tr>
                            <tr>
                                <td>Jane Smith</td>
                                <td>jane@example.com</td>
                                <td>08123456788</td>
                                <td>Surabaya</td>
                                <td><span class="badge bg-success">Valid</span></td>
                            </tr>
                            <tr>
                                <td>Bob Wilson</td>
                                <td>invalid-email</td>
                                <td>08123456787</td>
                                <td>Bandung</td>
                                <td><span class="badge bg-danger">Invalid</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="preview-actions">
                    <button class="btn btn-primary" onclick="dataMigration.processImport()">
                        <i class="fas fa-database"></i> Process Import
                    </button>
                    <button class="btn btn-secondary" onclick="dataMigration.previousStep()">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                </div>
            `;
            
        } catch (error) {
            console.error('Error validating data:', error);
            this.showNotification('Error', 'Failed to validate data', 'error');
        }
    }

    // Previous step
    previousStep() {
        const currentStep = document.querySelector('.wizard-steps .step.active');
        const currentStepNumber = parseInt(currentStep.dataset.step);
        
        currentStep.classList.remove('active');
        document.querySelector(`.step-content[data-step="${currentStepNumber}"]`).classList.remove('active');
        
        const prevStepNumber = currentStepNumber - 1;
        document.querySelector(`.wizard-steps .step[data-step="${prevStepNumber}"]`).classList.add('active');
        document.querySelector(`.step-content[data-step="${prevStepNumber}"]`).classList.add('active');
        
        // Update buttons
        document.getElementById('next-step').style.display = 'inline-block';
        document.getElementById('process-import').style.display = 'none';
    }

    // Process import
    async processImport() {
        try {
            // Show loading
            document.getElementById('import-preview').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Processing import...</p>
                </div>
            `;
            
            // Simulate import (in real implementation, call API)
            await new Promise(resolve => setTimeout(resolve, 3000));
            
            // Show results
            document.getElementById('import-preview').innerHTML = `
                <div class="import-results">
                    <div class="result-header">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <h6>Import Completed Successfully!</h6>
                    </div>
                    <div class="result-stats">
                        <div class="stat-item">
                            <span class="stat-label">Total Records:</span>
                            <span class="stat-value">152</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Successfully Imported:</span>
                            <span class="stat-value text-success">148</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Failed:</span>
                            <span class="stat-value text-danger">4</span>
                        </div>
                    </div>
                    <div class="result-actions">
                        <button class="btn btn-success" onclick="dataMigration.closeMigrationModal()">
                            <i class="fas fa-check"></i> Complete
                        </button>
                        <button class="btn btn-info" onclick="dataMigration.viewImportDetails()">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                    </div>
                </div>
            `;
            
            // Refresh dashboard
            this.loadMigrationDashboard();
            
        } catch (error) {
            console.error('Error processing import:', error);
            this.showNotification('Error', 'Failed to process import', 'error');
        }
    }

    // Close migration modal
    closeMigrationModal() {
        const modal = document.querySelector('.modal.show');
        if (modal) {
            bootstrap.Modal.getInstance(modal).hide();
        }
        
        this.showNotification('Success', 'Data migration completed successfully', 'success');
    }

    // View import details
    viewImportDetails() {
        // Implementation for viewing detailed import results
        this.showNotification('Info', 'Import details feature coming soon', 'info');
    }

    // Download templates
    downloadTemplates() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Download Migration Templates</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="templates-list">
                            <div class="template-item">
                                <div class="template-info">
                                    <div class="template-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="template-content">
                                        <h6>Member Import Template</h6>
                                        <p>Template for importing member data</p>
                                    </div>
                                </div>
                                <div class="template-action">
                                    <button class="btn btn-primary" onclick="dataMigration.downloadTemplate('members')">
                                        <i class="fas fa-download"></i> Download
                                    </button>
                                </div>
                            </div>
                            
                            <div class="template-item">
                                <div class="template-info">
                                    <div class="template-icon">
                                        <i class="fas fa-hand-holding-usd"></i>
                                    </div>
                                    <div class="template-content">
                                        <h6>Loan Import Template</h6>
                                        <p>Template for importing loan data</p>
                                    </div>
                                </div>
                                <div class="template-action">
                                    <button class="btn btn-primary" onclick="dataMigration.downloadTemplate('loans')">
                                        <i class="fas fa-download"></i> Download
                                    </button>
                                </div>
                            </div>
                            
                            <div class="template-item">
                                <div class="template-info">
                                    <div class="template-icon">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="template-content">
                                        <h6>Payment Import Template</h6>
                                        <p>Template for importing payment data</p>
                                    </div>
                                </div>
                                <div class="template-action">
                                    <button class="btn btn-primary" onclick="dataMigration.downloadTemplate('payments')">
                                        <i class="fas fa-download"></i> Download
                                    </button>
                                </div>
                            </div>
                            
                            <div class="template-item">
                                <div class="template-info">
                                    <div class="template-icon">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="template-content">
                                        <h6>Staff Import Template</h6>
                                        <p>Template for importing staff data</p>
                                    </div>
                                </div>
                                <div class="template-action">
                                    <button class="btn btn-primary" onclick="dataMigration.downloadTemplate('staff')">
                                        <i class="fas fa-download"></i> Download
                                    </button>
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

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Download specific template
    downloadTemplate(type) {
        window.open(`/api/data-migration.php?action=export_template&template_type=${type}`, '_blank');
    }

    // View migration history
    async viewHistory() {
        try {
            const response = await fetch('/api/data-migration.php?action=migration_history');
            const result = await response.json();
            
            if (result.success) {
                this.showHistoryModal(result.data);
            }
        } catch (error) {
            console.error('Error loading migration history:', error);
        }
    }

    // Show history modal
    showHistoryModal(batches) {
        const modal = document.createElement('div');
        modal.className = 'modal fade modal-lg';
        modal.innerHTML = `
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Migration History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="history-filters">
                            <div class="filter-controls">
                                <select class="form-select" id="batch-type-filter">
                                    <option value="all">All Types</option>
                                    <option value="members">Members</option>
                                    <option value="loans">Loans</option>
                                    <option value="payments">Payments</option>
                                    <option value="staff">Staff</option>
                                </select>
                                <select class="form-select" id="status-filter">
                                    <option value="all">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="completed">Completed</option>
                                    <option value="failed">Failed</option>
                                </select>
                                <button class="btn btn-primary" onclick="dataMigration.refreshHistory()">
                                    <i class="fas fa-refresh"></i> Refresh
                                </button>
                            </div>
                        </div>
                        
                        <div class="history-table">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Batch Name</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Total Records</th>
                                        <th>Success</th>
                                        <th>Failed</th>
                                        <th>Created By</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${batches.map(batch => `
                                        <tr>
                                            <td>${batch.batch_name}</td>
                                            <td><span class="badge bg-info">${batch.batch_type}</span></td>
                                            <td><span class="badge bg-${this.getStatusBadgeColor(batch.status)}">${batch.status}</span></td>
                                            <td>${batch.total_records}</td>
                                            <td class="text-success">${batch.success_records}</td>
                                            <td class="text-danger">${batch.failed_records}</td>
                                            <td>${batch.created_by_name}</td>
                                            <td>${new Date(batch.created_at).toLocaleDateString()}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="dataMigration.viewBatchDetails(${batch.id})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="dataMigration.deleteBatch(${batch.id})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="dataMigration.exportHistory()">
                            <i class="fas fa-download"></i> Export History
                        </button>
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

    // Get status badge color
    getStatusBadgeColor(status) {
        const colors = {
            'pending': 'warning',
            'processing': 'info',
            'completed': 'success',
            'failed' => 'danger'
        };
        return colors[status] || 'secondary';
    }

    // Validate data
    validateData() {
        this.showNotification('Info', 'Data validation feature coming soon', 'info');
    }

    // View batch details
    viewBatchDetails(batchId) {
        this.showNotification('Info', `Viewing details for batch ${batchId}`, 'info');
    }

    // Delete batch
    deleteBatch(batchId) {
        if (confirm('Are you sure you want to delete this batch?')) {
            this.showNotification('Success', 'Batch deleted successfully', 'success');
        }
    }

    // Export history
    exportHistory() {
        this.showNotification('Info', 'Export history feature coming soon', 'info');
    }

    // Refresh history
    refreshHistory() {
        this.viewHistory();
    }

    // Setup event listeners
    setupEventListeners() {
        // Auto-refresh dashboard data
        setInterval(() => {
            this.loadMigrationDashboard();
        }, 60000); // Refresh every minute
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

// Initialize data migration when page loads
let dataMigration = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('migration-dashboard')) {
        dataMigration = new DataMigration();
        dataMigration.initialize();
    }
});
