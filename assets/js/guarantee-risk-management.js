// Guarantee & Risk Management System Frontend
class GuaranteeRiskManagement {
    constructor() {
        this.guarantees = [];
        this.riskAssessments = [];
        this.relationships = [];
        this.isInitialized = false;
    }

    // Initialize guarantee risk management
    async initialize() {
        await this.loadGuaranteeDashboard();
        this.setupEventListeners();
        this.isInitialized = true;
    }

    // Load guarantee dashboard
    async loadGuaranteeDashboard() {
        try {
            const response = await fetch('/api/guarantee-risk-management.php?action=dashboard');
            const result = await response.json();
            
            if (result.success) {
                this.updateDashboardDisplay(result.data);
                this.guarantees = result.data.recent_guarantees;
            }
        } catch (error) {
            console.error('Error loading guarantee dashboard:', error);
        }
    }

    // Update dashboard display
    updateDashboardDisplay(data) {
        const container = document.getElementById('guarantee-dashboard');
        if (!container) return;

        container.innerHTML = `
            <div class="guarantee-header">
                <h5>🛡️ Guarantee & Risk Management</h5>
                <div class="guarantee-status">
                    <span class="status-indicator active"></span>
                    <span>System Active</span>
                </div>
            </div>
            
            <div class="guarantee-summary-grid">
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Total Guarantees</h6>
                        <h3>${data.guarantee_stats.total_guarantees || 0}</h3>
                        <small>${data.guarantee_stats.active_guarantees || 0} active</small>
                    </div>
                </div>
                
                <div class="summary-card warning">
                    <div class="summary-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Defaulted</h6>
                        <h3>${data.guarantee_stats.defaulted_guarantees || 0}</h3>
                        <small>Need attention</small>
                    </div>
                </div>
                
                <div class="summary-card success">
                    <div class="summary-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Settled</h6>
                        <h3>${data.guarantee_stats.settled_guarantees || 0}</h3>
                        <small>Completed</small>
                    </div>
                </div>
                
                <div class="summary-card danger">
                    <div class="summary-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Avg Risk Score</h6>
                        <h3>${Math.round(data.guarantee_stats.average_risk_score || 0)}%</h3>
                        <small>Risk assessment</small>
                    </div>
                </div>
            </div>
            
            <div class="quick-actions">
                <button class="btn btn-primary" onclick="guaranteeRiskManagement.createGuarantee()">
                    <i class="fas fa-plus"></i> Create Guarantee
                </button>
                <button class="btn btn-warning" onclick="guaranteeRiskManagement.assessCollectiveRisk()">
                    <i class="fas fa-chart-pie"></i> Risk Assessment
                </button>
                <button class="btn btn-info" onclick="guaranteeRiskManagement.analyzeRelationships()">
                    <i class="fas fa-project-diagram"></i> Relationship Analysis
                </button>
                <button class="btn btn-success" onclick="guaranteeRiskManagement.viewReports()">
                    <i class="fas fa-file-alt"></i> Reports
                </button>
            </div>
            
            <div class="risk-distribution">
                <h6>📊 Risk Distribution</h6>
                <div class="risk-bars">
                    ${data.risk_stats.map(risk => `
                        <div class="risk-bar">
                            <div class="risk-label">${risk.risk_level}</div>
                            <div class="risk-progress">
                                <div class="progress">
                                    <div class="progress-bar bg-${this.getRiskColor(risk.risk_level)}" 
                                         style="width: ${risk.count * 10}%">
                                        ${risk.count} (${Math.round(risk.avg_score)}%)
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div class="guarantee-types">
                <h6>📋 Guarantee Types</h6>
                <div class="type-cards">
                    ${data.type_distribution.map(type => `
                        <div class="type-card">
                            <div class="type-icon">
                                <i class="fas ${this.getTypeIcon(type.guarantee_type)}"></i>
                            </div>
                            <div class="type-content">
                                <h6>${type.guarantee_type}</h6>
                                <p>${type.count} guarantees</p>
                                <small>Rp ${this.formatCurrency(type.total_amount)}</small>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div class="recent-guarantees">
                <h6>📋 Recent Guarantees</h6>
                <div class="guarantees-list">
                    ${data.recent_guarantees.map(guarantee => `
                        <div class="guarantee-item ${guarantee.guarantee_status}">
                            <div class="guarantee-info">
                                <div class="guarantee-amount">Rp ${this.formatCurrency(guarantee.guarantee_amount)}</div>
                                <div class="guarantee-parties">
                                    <span class="borrower">${guarantee.borrower_name}</span>
                                    <i class="fas fa-arrow-right"></i>
                                    <span class="guarantor">${guarantee.guarantor_name}</span>
                                </div>
                                <div class="guarantee-meta">
                                    <span class="loan-number">${guarantee.loan_number}</span>
                                    <span class="guarantee-status status-${guarantee.guarantee_status}">${guarantee.guarantee_status}</span>
                                    <span class="risk-score">Risk: ${Math.round(guarantee.risk_score)}%</span>
                                </div>
                            </div>
                            <div class="guarantee-actions">
                                <button class="btn btn-sm btn-outline-primary" onclick="guaranteeRiskManagement.viewGuaranteeDetails(${guarantee.id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" onclick="guaranteeRiskManagement.editGuarantee(${guarantee.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    // Create guarantee
    createGuarantee() {
        const modal = document.createElement('div');
        modal.className = 'modal fade modal-lg';
        modal.innerHTML = `
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Guarantee</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="create-guarantee-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="loan-select">Select Loan</label>
                                        <select class="form-select" id="loan-select" required onchange="guaranteeRiskManagement.loadLoanDetails(this.value)">
                                            <option value="">Select Loan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="guarantor-select">Select Guarantor</label>
                                        <select class="form-select" id="guarantor-select" required onchange="guaranteeRiskManagement.loadGuarantorDetails(this.value)">
                                            <option value="">Select Guarantor</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="guarantee-type">Guarantee Type</label>
                                        <select class="form-select" id="guarantee-type" required>
                                            <option value="">Select Type</option>
                                            <option value="personal">Personal</option>
                                            <option value="corporate">Corporate</option>
                                            <option value="collateral">Collateral</option>
                                            <option value="social">Social</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="guarantee-amount">Guarantee Amount</label>
                                        <input type="number" class="form-control" id="guarantee-amount" required min="0" step="0.01">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="relationship-type">Relationship Type</label>
                                        <select class="form-select" id="relationship-type" required>
                                            <option value="">Select Relationship</option>
                                            <option value="family">Family</option>
                                            <option value="friend">Friend</option>
                                            <option value="colleague">Colleague</option>
                                            <option value="neighbor">Neighbor</option>
                                            <option value="business">Business Partner</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="relationship-strength">Relationship Strength</label>
                                        <select class="form-select" id="relationship-strength" required>
                                            <option value="">Select Strength</option>
                                            <option value="strong">Strong</option>
                                            <option value="moderate">Moderate</option>
                                            <option value="weak">Weak</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="guarantee-documents">Guarantee Documents</label>
                                <textarea class="form-control" id="guarantee-documents" rows="3" placeholder="List of guarantee documents (KTP, KK, etc.)"></textarea>
                            </div>
                            
                            <div class="loan-details" id="loan-details" style="display: none;">
                                <h6>Loan Details</h6>
                                <div class="loan-info">
                                    <div class="info-item">
                                        <span class="info-label">Borrower:</span>
                                        <span class="info-value" id="loan-borrower"></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Loan Amount:</span>
                                        <span class="info-value" id="loan-amount"></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Loan Date:</span>
                                        <span class="info-value" id="loan-date"></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Due Date:</span>
                                        <span class="info-value" id="loan-due-date"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="guarantor-details" id="guarantor-details" style="display: none;">
                                <h6>Guarantor Details</h6>
                                <div class="guarantor-info">
                                    <div class="info-item">
                                        <span class="info-label">Name:</span>
                                        <span class="info-value" id="guarantor-name"></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Phone:</span>
                                        <span class="info-value" id="guarantor-phone"></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Address:</span>
                                        <span class="info-value" id="guarantor-address"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="risk-preview" id="risk-preview" style="display: none;">
                                <h6>📊 Risk Assessment Preview</h6>
                                <div class="risk-metrics">
                                    <div class="metric-item">
                                        <span class="metric-label">Risk Score:</span>
                                        <span class="metric-value" id="preview-risk-score">0%</span>
                                    </div>
                                    <div class="metric-item">
                                        <span class="metric-label">Risk Level:</span>
                                        <span class="metric-value" id="preview-risk-level">Medium</span>
                                    </div>
                                    <div class="metric-item">
                                        <span class="metric-label">Recommendation:</span>
                                        <span class="metric-value" id="preview-recommendation">Proceed with caution</span>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="guaranteeRiskManagement.saveGuarantee()">Create Guarantee</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        // Load data
        this.loadLoans();
        this.loadMembers();

        // Setup form validation
        this.setupGuaranteeFormValidation();

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Load loans for selection
    async loadLoans() {
        try {
            const response = await fetch('/api/loans.php?action=get_loans&status=active');
            const result = await response.json();
            
            if (result.success) {
                const loanSelect = document.getElementById('loan-select');
                if (loanSelect) {
                    loanSelect.innerHTML = '<option value="">Select Loan</option>' + 
                        result.data.map(loan => 
                            `<option value="${loan.id}">${loan.loan_number} - ${loan.member_name} (Rp ${this.formatCurrency(loan.amount)})</option>`
                        ).join('');
                }
            }
        } catch (error) {
            console.error('Error loading loans:', error);
        }
    }

    // Load members for guarantor selection
    async loadMembers() {
        try {
            const response = await fetch('/api/members.php?action=get_members');
            const result = await response.json();
            
            if (result.success) {
                const guarantorSelect = document.getElementById('guarantor-select');
                if (guarantorSelect) {
                    guarantorSelect.innerHTML = '<option value="">Select Guarantor</option>' + 
                        result.data.map(member => 
                            `<option value="${member.id}">${member.name} (${member.phone})</option>`
                        ).join('');
                }
            }
        } catch (error) {
            console.error('Error loading members:', error);
        }
    }

    // Load loan details
    async loadLoanDetails(loanId) {
        if (!loanId) {
            document.getElementById('loan-details').style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`/api/loans.php?action=loan_details&loan_id=${loanId}`);
            const result = await response.json();
            
            if (result.success) {
                const loan = result.data.loan;
                document.getElementById('loan-borrower').textContent = loan.member_name;
                document.getElementById('loan-amount').textContent = `Rp ${this.formatCurrency(loan.amount)}`;
                document.getElementById('loan-date').textContent = loan.loan_date;
                document.getElementById('loan-due-date').textContent = loan.due_date;
                document.getElementById('loan-details').style.display = 'block';
                
                // Update guarantee amount to loan amount
                document.getElementById('guarantee-amount').value = loan.amount;
                
                // Update risk preview
                this.updateRiskPreview();
            }
        } catch (error) {
            console.error('Error loading loan details:', error);
        }
    }

    // Load guarantor details
    async loadGuarantorDetails(guarantorId) {
        if (!guarantorId) {
            document.getElementById('guarantor-details').style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`/api/members.php?action=member_details&member_id=${guarantorId}`);
            const result = await response.json();
            
            if (result.success) {
                const member = result.data.member;
                document.getElementById('guarantor-name').textContent = member.name;
                document.getElementById('guarantor-phone').textContent = member.phone;
                document.getElementById('guarantor-address').textContent = member.address;
                document.getElementById('guarantor-details').style.display = 'block';
                
                // Update risk preview
                this.updateRiskPreview();
            }
        } catch (error) {
            console.error('Error loading guarantor details:', error);
        }
    }

    // Setup guarantee form validation
    setupGuaranteeFormValidation() {
        const form = document.getElementById('create-guarantee-form');
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('change', () => {
                this.updateRiskPreview();
            });
        });
    }

    // Update risk preview
    updateRiskPreview() {
        const loanId = document.getElementById('loan-select').value;
        const guarantorId = document.getElementById('guarantor-select').value;
        const relationshipStrength = document.getElementById('relationship-strength').value;
        const guaranteeType = document.getElementById('guarantee-type').value;
        
        if (!loanId || !guarantorId || !relationshipStrength || !guaranteeType) {
            document.getElementById('risk-preview').style.display = 'none';
            return;
        }
        
        // Calculate risk score (simplified)
        let riskScore = 50; // Base score
        
        // Adjust based on relationship strength
        switch (relationshipStrength) {
            case 'strong':
                riskScore -= 20;
                break;
            case 'moderate':
                riskScore += 0;
                break;
            case 'weak':
                riskScore += 30;
                break;
        }
        
        // Adjust based on guarantee type
        switch (guaranteeType) {
            case 'personal':
                riskScore += 10;
                break;
            case 'corporate':
                riskScore -= 10;
                break;
            case 'collateral':
                riskScore -= 20;
                break;
            case 'social':
                riskScore += 20;
                break;
        }
        
        // Ensure score is within bounds
        riskScore = Math.max(0, Math.min(100, riskScore));
        
        // Determine risk level
        let riskLevel = 'Low';
        if (riskScore > 70) {
            riskLevel = 'High';
        } else if (riskScore > 40) {
            riskLevel = 'Medium';
        }
        
        // Determine recommendation
        let recommendation = 'Proceed with caution';
        if (riskScore < 30) {
            recommendation = 'Recommended';
        } else if (riskScore > 70) {
            recommendation = 'High risk - reconsider';
        }
        
        // Update preview
        document.getElementById('preview-risk-score').textContent = `${riskScore}%`;
        document.getElementById('preview-risk-level').textContent = riskLevel;
        document.getElementById('preview-risk-level').className = `metric-value text-${this.getRiskColorClass(riskLevel)}`;
        document.getElementById('preview-recommendation').textContent = recommendation;
        document.getElementById('risk-preview').style.display = 'block';
    }

    // Save guarantee
    async saveGuarantee() {
        try {
            const formData = new FormData();
            formData.append('loan_id', document.getElementById('loan-select').value);
            formData.append('guarantor_id', document.getElementById('guarantor-select').value);
            formData.append('guarantee_type', document.getElementById('guarantee-type').value);
            formData.append('guarantee_amount', document.getElementById('guarantee-amount').value);
            formData.append('guarantee_relationship', document.getElementById('relationship-type').value);
            formData.append('relationship_strength', document.getElementById('relationship-strength').value);
            formData.append('guarantee_documents', document.getElementById('guarantee-documents').value);
            formData.append('created_by', 1); // Current user ID

            const response = await fetch('/api/guarantee-risk-management.php?action=create_guarantee', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Success', 'Guarantee created successfully', 'success');
                this.loadGuaranteeDashboard();
                
                // Close modal
                const modal = document.querySelector('.modal.show');
                if (modal) {
                    bootstrap.Modal.getInstance(modal).hide();
                }
            } else {
                throw new Error(result.error || 'Failed to create guarantee');
            }
        } catch (error) {
            console.error('Error saving guarantee:', error);
            this.showNotification('Error', 'Failed to create guarantee', 'error');
        }
    }

    // Assess collective risk
    async assessCollectiveRisk() {
        const modal = document.createElement('div');
        modal.className = 'modal fade modal-lg';
        modal.innerHTML = `
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Collective Risk Assessment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="risk-assessment-form">
                            <div class="form-group">
                                <label for="risk-loan-select">Select Loan</label>
                                <select class="form-select" id="risk-loan-select" required onchange="guaranteeRiskManagement.loadCollectiveRisk(this.value)">
                                    <option value="">Select Loan</option>
                                </select>
                            </div>
                            
                            <div class="risk-results" id="risk-results">
                                <div class="text-center text-muted">
                                    <i class="fas fa-chart-pie fa-3x mb-3"></i>
                                    <p>Select a loan to assess collective risk</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="guaranteeRiskManagement.generateRiskReport()" id="generate-report-btn" style="display: none;">
                            <i class="fas fa-file-alt"></i> Generate Report
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        // Load loans
        this.loadLoansForRiskAssessment();

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Load loans for risk assessment
    async loadLoansForRiskAssessment() {
        try {
            const response = await fetch('/api/loans.php?action=get_loans&status=active');
            const result = await response.json();
            
            if (result.success) {
                const loanSelect = document.getElementById('risk-loan-select');
                if (loanSelect) {
                    loanSelect.innerHTML = '<option value="">Select Loan</option>' + 
                        result.data.map(loan => 
                            `<option value="${loan.id}">${loan.loan_number} - ${loan.member_name}</option>`
                        ).join('');
                }
            }
        } catch (error) {
            console.error('Error loading loans:', error);
        }
    }

    // Load collective risk assessment
    async loadCollectiveRisk(loanId) {
        if (!loanId) {
            document.getElementById('risk-results').innerHTML = `
                <div class="text-center text-muted">
                    <i class="fas fa-chart-pie fa-3x mb-3"></i>
                    <p>Select a loan to assess collective risk</p>
                </div>
            `;
            document.getElementById('generate-report-btn').style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`/api/guarantee-risk-management.php?action=collective_risk_assessment&loan_id=${loanId}`);
            const result = await response.json();
            
            if (result.success) {
                this.displayCollectiveRiskResults(result.data);
                document.getElementById('generate-report-btn').style.display = 'inline-block';
            }
        } catch (error) {
            console.error('Error loading collective risk:', error);
        }
    }

    // Display collective risk results
    displayCollectiveRiskResults(data) {
        const container = document.getElementById('risk-results');
        
        container.innerHTML = `
            <div class="risk-overview">
                <div class="risk-score-card">
                    <div class="risk-score-circle">
                        <div class="score-value">${data.collective_risk_score}%</div>
                        <div class="score-label">Risk Score</div>
                    </div>
                    <div class="risk-level-badge bg-${this.getRiskColorClass(data.risk_level)}">
                        ${data.risk_level.toUpperCase()}
                    </div>
                </div>
                
                <div class="risk-metrics">
                    <div class="metric-item">
                        <div class="metric-value">${data.guarantee_count}</div>
                        <div class="metric-label">Guarantees</div>
                    </div>
                    <div class="metric-item">
                        <div class="metric-value">Rp ${this.formatCurrency(data.total_guaranteed_amount)}</div>
                        <div class="metric-label">Total Guaranteed</div>
                    </div>
                </div>
            </div>
            
            <div class="guarantees-list">
                <h6>📋 Individual Guarantees</h6>
                <div class="guarantee-cards">
                    ${data.guarantees.map(guarantee => `
                        <div class="guarantee-card">
                            <div class="card-header">
                                <div class="guarantor-info">
                                    <h6>${guarantee.guarantor_name}</h6>
                                    <span class="relationship-type">${guarantee.guarantee_relationship}</span>
                                </div>
                                <div class="guarantee-amount">
                                    Rp ${this.formatCurrency(guarantee.guarantee_amount)}
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="risk-indicators">
                                    <div class="indicator">
                                        <span class="indicator-label">Risk Score:</span>
                                        <span class="indicator-value">${Math.round(guarantee.risk_score)}%</span>
                                    </div>
                                    <div class="indicator">
                                        <span class="indicator-label">Relationship:</span>
                                        <span class="indicator-value">${guarantee.relationship_strength}</span>
                                    </div>
                                    <div class="indicator">
                                        <span class="indicator-label">Trust Level:</span>
                                        <span class="indicator-value">${Math.round(guarantee.trust_level)}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div class="risk-analysis">
                <div class="risk-factors">
                    <h6>⚠️ Risk Factors</h6>
                    <ul class="risk-list">
                        ${data.risk_factors.map(factor => `
                            <li class="risk-item">${factor}</li>
                        `).join('')}
                    </ul>
                </div>
                
                <div class="mitigation-strategies">
                    <h6>🛡️ Mitigation Strategies</h6>
                    <ul class="strategy-list">
                        ${data.mitigation_strategies.map(strategy => `
                            <li class="strategy-item">${strategy}</li>
                        `).join('')}
                    </ul>
                </div>
            </div>
        `;
    }

    // Analyze relationships
    async analyzeRelationships() {
        const modal = document.createElement('div');
        modal.className = 'modal fade modal-lg';
        modal.innerHTML = `
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Relationship Analysis</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="relationship-analysis-form">
                            <div class="form-group">
                                <label for="member-select">Select Member</label>
                                <select class="form-select" id="member-select" required onchange="guaranteeRiskManagement.loadRelationshipAnalysis(this.value)">
                                    <option value="">Select Member</option>
                                </select>
                            </div>
                            
                            <div class="relationship-results" id="relationship-results">
                                <div class="text-center text-muted">
                                    <i class="fas fa-project-diagram fa-3x mb-3"></i>
                                    <p>Select a member to analyze relationships</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="guaranteeRiskManagement.exportRelationshipAnalysis()" id="export-analysis-btn" style="display: none;">
                            <i class="fas fa-download"></i> Export Analysis
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        // Load members
        this.loadMembersForRelationshipAnalysis();

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Load members for relationship analysis
    async loadMembersForRelationshipAnalysis() {
        try {
            const response = await fetch('/api/members.php?action=get_members');
            const result = await response.json();
            
            if (result.success) {
                const memberSelect = document.getElementById('member-select');
                if (memberSelect) {
                    memberSelect.innerHTML = '<option value="">Select Member</option>' + 
                        result.data.map(member => 
                            `<option value="${member.id}">${member.name} (${member.phone})</option>`
                        ).join('');
                }
            }
        } catch (error) {
            console.error('Error loading members:', error);
        }
    }

    // Load relationship analysis
    async loadRelationshipAnalysis(memberId) {
        if (!memberId) {
            document.getElementById('relationship-results').innerHTML = `
                <div class="text-center text-muted">
                    <i class="fas fa-project-diagram fa-3x mb-3"></i>
                    <p>Select a member to analyze relationships</p>
                </div>
            `;
            document.getElementById('export-analysis-btn').style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`/api/guarantee-risk-management.php?action=guarantee_relationship_analysis&member_id=${memberId}`);
            const result = await response.json();
            
            if (result.success) {
                this.displayRelationshipAnalysis(result.data);
                document.getElementById('export-analysis-btn').style.display = 'inline-block';
            }
        } catch (error) {
            console.error('Error loading relationship analysis:', error);
        }
    }

    // Display relationship analysis
    displayRelationshipAnalysis(data) {
        const container = document.getElementById('relationship-results');
        const analysis = data.analysis;
        
        container.innerHTML = `
            <div class="relationship-overview">
                <div class="overview-card">
                    <h6>📊 Network Overview</h6>
                    <div class="overview-stats">
                        <div class="stat-item">
                            <div class="stat-value">${analysis.total_connections}</div>
                            <div class="stat-label">Total Connections</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">${analysis.strong_connections}</div>
                            <div class="stat-label">Strong Connections</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">${analysis.high_trust_connections}</div>
                            <div class="stat-label">High Trust</div>
                        </div>
                    </div>
                </div>
                
                <div class="connection-types">
                    <h6>🔗 Connection Types</h6>
                    <div class="type-bars">
                        <div class="type-bar">
                            <span class="type-label">Family:</span>
                            <span class="type-value">${analysis.family_connections}</span>
                        </div>
                        <div class="type-bar">
                            <span class="type-label">Friends:</span>
                            <span class="type-value">${analysis.friend_connections}</span>
                        </div>
                        <div class="type-bar">
                            <span class="type-label">Colleagues:</span>
                            <span class="type-value">${analysis.colleague_connections}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="relationships-list">
                <h6>👥 Individual Relationships</h6>
                <div class="relationship-cards">
                    ${data.relationships.map(rel => `
                        <div class="relationship-card">
                            <div class="card-header">
                                <div class="person-info">
                                    <h6>${rel.related_member_name}</h6>
                                    <span class="relationship-role">${rel.relationship_role}</span>
                                </div>
                                <div class="relationship-type">${rel.relationship_type}</div>
                            </div>
                            <div class="card-body">
                                <div class="relationship-metrics">
                                    <div class="metric">
                                        <span class="metric-label">Strength:</span>
                                        <span class="metric-value">${rel.relationship_strength}</span>
                                    </div>
                                    <div class="metric">
                                        <span class="metric-label">Trust:</span>
                                        <span class="metric-value">${Math.round(rel.trust_level)}%</span>
                                    </div>
                                    <div class="metric">
                                        <span class="metric-label">Financial Dep:</span>
                                        <span class="metric-value">${rel.financial_dependency}</span>
                                    </div>
                                    <div class="metric">
                                        <span class="metric-label">Social Influence:</span>
                                        <span class="metric-value">${rel.social_influence}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    // View guarantee details
    async viewGuaranteeDetails(guaranteeId) {
        try {
            const response = await fetch(`/api/guarantee-risk-management.php?action=get_guarantee_details&guarantee_id=${guaranteeId}`);
            const result = await response.json();
            
            if (result.success) {
                this.showGuaranteeDetailsModal(result.data);
            }
        } catch (error) {
            console.error('Error loading guarantee details:', error);
        }
    }

    // Show guarantee details modal
    showGuaranteeDetailsModal(data) {
        const modal = document.createElement('div');
        modal.className = 'modal fade modal-lg';
        modal.innerHTML = `
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Guarantee Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="guarantee-details">
                            <div class="detail-section">
                                <h6>📋 Guarantee Information</h6>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <span class="detail-label">Guarantee Amount:</span>
                                        <span class="detail-value">Rp ${this.formatCurrency(data.guarantee.guarantee_amount)}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Guarantee Type:</span>
                                        <span class="detail-value">${data.guarantee.guarantee_type}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Relationship:</span>
                                        <span class="detail-value">${data.guarantee.guarantee_relationship}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Status:</span>
                                        <span class="detail-value">${data.guarantee.guarantee_status}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Risk Score:</span>
                                        <span class="detail-value">${Math.round(data.guarantee.risk_score)}%</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Created:</span>
                                        <span class="detail-value">${new Date(data.guarantee.created_at).toLocaleDateString()}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h6>👥 Parties Involved</h6>
                                <div class="parties-grid">
                                    <div class="party-card">
                                        <h6>Borrower</h6>
                                        <div class="party-info">
                                            <div class="info-item">
                                                <span class="info-label">Name:</span>
                                                <span class="info-value">${data.guarantee.borrower_name}</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">Phone:</span>
                                                <span class="info-value">${data.guarantee.borrower_phone}</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">Address:</span>
                                                <span class="info-value">${data.guarantee.borrower_address}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="party-card">
                                        <h6>Guarantor</h6>
                                        <div class="party-info">
                                            <div class="info-item">
                                                <span class="info-label">Name:</span>
                                                <span class="info-value">${data.guarantee.guarantor_name}</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">Phone:</span>
                                                <span class="info-value">${data.guarantee.guarantor_phone}</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">Address:</span>
                                                <span class="info-value">${data.guarantee.guarantor_address}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h6>🔗 Relationship Analysis</h6>
                                <div class="relationship-metrics">
                                    <div class="metric-item">
                                        <span class="metric-label">Relationship Strength:</span>
                                        <span class="metric-value">${data.guarantee.relationship_strength}</span>
                                    </div>
                                    <div class="metric-item">
                                        <span class="metric-label">Trust Level:</span>
                                        <span class="metric-value">${Math.round(data.guarantee.trust_level)}%</span>
                                    </div>
                                    <div class="metric-item">
                                        <span class="metric-label">Financial Dependency:</span>
                                        <span class="metric-value">${data.guarantee.financial_dependency}</span>
                                    </div>
                                    <div class="metric-item">
                                        <span class="metric-label">Social Influence:</span>
                                        <span class="metric-value">${data.guarantee.social_influence}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h6>📊 Risk Assessments</h6>
                                <div class="risk-assessments-list">
                                    ${data.risk_assessments.map(assessment => `
                                        <div class="assessment-item">
                                            <div class="assessment-header">
                                                <span class="assessment-date">${assessment.assessment_date}</span>
                                                <span class="assessment-level bg-${this.getRiskColorClass(assessment.risk_level)}">${assessment.risk_level}</span>
                                            </div>
                                            <div class="assessment-score">Risk Score: ${assessment.risk_score}%</div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h6>💳 Payment Tracking</h6>
                                <div class="payments-list">
                                    ${data.payment_tracking.map(payment => `
                                        <div class="payment-item">
                                            <div class="payment-header">
                                                <span class="payment-date">${payment.payment_date}</span>
                                                <span class="payment-source">${payment.payment_source}</span>
                                            </div>
                                            <div class="payment-amount">Rp ${this.formatCurrency(payment.payment_amount)}</div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="guaranteeRiskManagement.editGuarantee(${data.guarantee.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button type="button" class="btn btn-warning" onclick="guaranteeRiskManagement.initiateCollection(${data.guarantee.id})">
                            <i class="fas fa-hand-holding-usd"></i> Initiate Collection
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

    // View reports
    viewReports() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Guarantee Reports</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="reports-grid">
                            <div class="report-card" onclick="guaranteeRiskManagement.generateReport('summary')">
                                <div class="report-icon">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <div class="report-content">
                                    <h6>Summary Report</h6>
                                    <p>Overall guarantee statistics and summary</p>
                                </div>
                            </div>
                            
                            <div class="report-card" onclick="guaranteeRiskManagement.generateReport('risk')">
                                <div class="report-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="report-content">
                                    <h6>Risk Report</h6>
                                    <p>Detailed risk assessment and analysis</p>
                                </div>
                            </div>
                            
                            <div class="report-card" onclick="guaranteeRiskManagement.generateReport('payment')">
                                <div class="report-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="report-content">
                                    <h6>Payment Report</h6>
                                    <p>Payment tracking and collection history</p>
                                </div>
                            </div>
                            
                            <div class="report-card" onclick="guaranteeRiskManagement.generateReport('relationship')">
                                <div class="report-icon">
                                    <i class="fas fa-project-diagram"></i>
                                </div>
                                <div class="report-content">
                                    <h6>Relationship Report</h6>
                                    <p>Network analysis and relationship patterns</p>
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

    // Generate report
    generateReport(reportType) {
        const startDate = prompt('Enter start date (YYYY-MM-DD):', new Date().toISOString().split('T')[0]);
        const endDate = prompt('Enter end date (YYYY-MM-DD):', new Date().toISOString().split('T')[0]);
        
        if (startDate && endDate) {
            window.open(`/api/guarantee-risk-management.php?action=guarantee_report&report_type=${reportType}&start_date=${startDate}&end_date=${endDate}`, '_blank');
        }
    }

    // Helper functions
    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID').format(amount);
    }

    getRiskColor(riskLevel) {
        const colors = {
            'low': 'success',
            'medium': 'warning',
            'high': 'danger',
            'critical': 'dark'
        };
        return colors[riskLevel] || 'secondary';
    }

    getRiskColorClass(riskLevel) {
        const colors = {
            'Low': 'success',
            'Medium': 'warning',
            'High': 'danger',
            'Critical': 'dark'
        };
        return colors[riskLevel] || 'secondary';
    }

    getTypeIcon(type) {
        const icons = {
            'personal': 'fa-user',
            'corporate': 'fa-building',
            'collateral': 'fa-home',
            'social': 'fa-users'
        };
        return icons[type] || 'fa-shield-alt';
    }

    // Setup event listeners
    setupEventListeners() {
        // Auto-refresh dashboard data
        setInterval(() => {
            this.loadGuaranteeDashboard();
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

    // Placeholder methods for additional functionality
    editGuarantee(guaranteeId) {
        this.showNotification('Info', `Edit guarantee ${guaranteeId} - Coming soon`, 'info');
    }

    initiateCollection(guaranteeId) {
        this.showNotification('Info', `Initiate collection for guarantee ${guaranteeId} - Coming soon`, 'info');
    }

    generateRiskReport() {
        this.showNotification('Info', 'Generate risk report - Coming soon', 'info');
    }

    exportRelationshipAnalysis() {
        this.showNotification('Info', 'Export relationship analysis - Coming soon', 'info');
    }
}

// Initialize guarantee risk management when page loads
let guaranteeRiskManagement = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('guarantee-dashboard')) {
        guaranteeRiskManagement = new GuaranteeRiskManagement();
        guaranteeRiskManagement.initialize();
    }
});
