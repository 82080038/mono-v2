// Accounting System Frontend
class AccountingSystem {
    constructor() {
        this.journalEntries = [];
        this.chartOfAccounts = [];
        this.financialReports = {};
        this.isInitialized = false;
    }

    // Initialize accounting system
    async initialize() {
        await this.loadAccountingDashboard();
        await this.loadChartOfAccounts();
        this.setupEventListeners();
        this.isInitialized = true;
    }

    // Load accounting dashboard
    async loadAccountingDashboard() {
        try {
            const response = await fetch('/api/accounting-system.php?action=dashboard');
            const result = await response.json();
            
            if (result.success) {
                this.updateDashboardDisplay(result.data);
            }
        } catch (error) {
            console.error('Error loading accounting dashboard:', error);
        }
    }

    // Load chart of accounts
    async loadChartOfAccounts() {
        try {
            const response = await fetch('/api/accounting-system.php?action=chart_of_accounts');
            const result = await response.json();
            
            if (result.success) {
                this.chartOfAccounts = result.data;
                this.updateAccountsDisplay();
            }
        } catch (error) {
            console.error('Error loading chart of accounts:', error);
        }
    }

    // Update dashboard display
    updateDashboardDisplay(data) {
        const container = document.getElementById('accounting-dashboard');
        if (!container) return;

        container.innerHTML = `
            <div class="accounting-header">
                <h5>📊 Sistem Akuntansi</h5>
                <div class="accounting-status">
                    <span class="status-indicator active"></span>
                    <span>System Active</span>
                </div>
            </div>
            
            <div class="accounting-summary-grid">
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Total Akun</h6>
                        <h3>${data.account_summary.total_accounts}</h3>
                        <small>${data.account_summary.active_accounts} aktif</small>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Jurnal</h6>
                        <h3>${data.journal_summary.total_entries}</h3>
                        <small>${data.journal_summary.posted_entries} posted</small>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Neraca</h6>
                        <h3>Rp ${(data.balance_summary.total_assets || 0).toLocaleString('id-ID')}</h3>
                        <small>Total Aset</small>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="summary-content">
                        <h6>Periode</h6>
                        <h3>${data.current_period.month}/${data.current_period.year}</h6>
                        <small>Periode aktif</small>
                    </div>
                </div>
            </div>
            
            <div class="quick-actions">
                <button class="btn btn-primary" onclick="accountingSystem.createJournalEntry()">
                    <i class="fas fa-plus"></i> Buat Jurnal
                </button>
                <button class="btn btn-info" onclick="accountingSystem.viewGeneralLedger()">
                    <i class="fas fa-book"></i> Buku Besar
                </button>
                <button class="btn btn-success" onclick="accountingSystem.viewTrialBalance()">
                    <i class="fas fa-balance-scale"></i> Neraca Saldo
                </button>
                <button class="btn btn-warning" onclick="accountingSystem.viewBalanceSheet()">
                    <i class="fas fa-chart-pie"></i> Neraca
                </button>
                <button class="btn btn-danger" onclick="accountingSystem.viewIncomeStatement()">
                    <i class="fas fa-chart-line"></i> Laba Rugi
                </button>
            </div>
            
            <div class="accounting-reports">
                <h6>📈 Laporan Keuangan</h6>
                <div class="reports-grid">
                    <div class="report-card" onclick="accountingSystem.viewMonthlyReport()">
                        <div class="report-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="report-content">
                            <h6>Laporan Bulanan</h6>
                            <p>Laporan keuangan bulan ini</p>
                        </div>
                    </div>
                    
                    <div class="report-card" onclick="accountingSystem.viewAnnualReport()">
                        <div class="report-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="report-content">
                            <h6>Laporan Tahunan</h6>
                            <p>Laporan keuangan tahun ini</p>
                        </div>
                    </div>
                    
                    <div class="report-card" onclick="accountingSystem.viewSHUReport()">
                        <div class="report-icon">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="report-content">
                            <h6>Laporan SHU</h6>
                            <p>Laporan Sisa Hasil Usaha</p>
                        </div>
                    </div>
                    
                    <div class="report-card" onclick="accountingSystem.viewCashFlow()">
                        <div class="report-icon">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="report-content">
                            <h6>Arus Kas</h6>
                            <p>Laporan arus kas</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Update accounts display
    updateAccountsDisplay() {
        const container = document.getElementById('accounts-display');
        if (!container) return;

        container.innerHTML = `
            <div class="accounts-header">
                <h6>📊 Buku Besar Akun</h6>
                <div class="account-filters">
                    <select class="form-select" onchange="accountingSystem.filterAccounts(this.value)">
                        <option value="all">Semua Akun</option>
                        <option value="asset">Aset</option>
                        <option value="liability">Kewajiban</option>
                        <option value="equity">Ekuitas</option>
                        <option value="revenue">Pendapatan</option>
                        <option value="expense">Beban</option>
                    </select>
                </div>
            </div>
            
            <div class="accounts-list">
                ${this.chartOfAccounts.map(account => `
                    <div class="account-item ${account.account_type}">
                        <div class="account-info">
                            <span class="account-code">${account.account_code}</span>
                            <span class="account-name">${account.account_name}</span>
                            <span class="account-type">${account.account_type}</span>
                        </div>
                        <div class="account-actions">
                            <button class="btn btn-sm btn-outline-primary" onclick="accountingSystem.viewAccountLedger(${account.id})">
                                <i class="fas fa-book"></i> Ledger
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    // Create journal entry
    createJournalEntry() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Buat Jurnal Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="journal-entry-form">
                            <div class="form-group">
                                <label for="entry-date">Tanggal</label>
                                <input type="date" class="form-control" id="entry-date" required>
                            </div>
                            <div class="form-group">
                                <label for="entry-description">Deskripsi</label>
                                <textarea class="form-control" id="entry-description" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="reference-type">Tipe Referensi</label>
                                <select class="form-control" id="reference-type">
                                    <option value="">Pilih tipe referensi</option>
                                    <option value="loan">Pinjaman</option>
                                    <option value="savings">Simpanan</option>
                                    <option value="payment">Pembayaran</option>
                                    <option value="expense">Beban</option>
                                    <option value="revenue">Pendapatan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="reference-id">ID Referensi</label>
                                <input type="number" class="form-control" id="reference-id">
                            </div>
                            
                            <div class="journal-lines">
                                <h6>Detail Jurnal</h6>
                                <div id="journal-lines-container">
                                    <div class="journal-line">
                                        <div class="line-header">
                                            <span>Baris 1</span>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="accountingSystem.removeJournalLine(this)">Hapus</button>
                                        </div>
                                        <div class="line-fields">
                                            <div class="form-group">
                                                <label>Akun</label>
                                                <select class="form-control account-select" required>
                                                    <option value="">Pilih akun</option>
                                                    ${this.chartOfAccounts.map(account => `
                                                        <option value="${account.id}">${account.account_code} - ${account.account_name}</option>
                                                    `).join('')}
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Deskripsi</label>
                                                <input type="text" class="form-control line-description">
                                            </div>
                                            <div class="form-group">
                                                <label>Debit</label>
                                                <input type="number" class="form-control line-debit" step="0.01" min="0">
                                            </div>
                                            <div class="form-group">
                                                <label>Kredit</label>
                                                <input type="number" class="form-control line-credit" step="0.01" min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-outline-primary" onclick="accountingSystem.addJournalLine()">
                                <i class="fas fa-plus"></i> Tambah Baris
                            </button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" onclick="accountingSystem.saveJournalEntry()">Simpan Draft</button>
                        <button type="button" class="btn btn-success" onclick="accountingSystem.postJournalEntry()">Post Jurnal</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        // Set default date
        document.getElementById('entry-date').value = new Date().toISOString().split('T')[0];

        // Cleanup on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    // Add journal line
    addJournalLine() {
        const container = document.getElementById('journal-lines-container');
        const lineCount = container.children.length + 1;
        
        const lineDiv = document.createElement('div');
        lineDiv.className = 'journal-line';
        lineDiv.innerHTML = `
            <div class="line-header">
                <span>Baris ${lineCount}</span>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="accountingSystem.removeJournalLine(this)">Hapus</button>
            </div>
            <div class="line-fields">
                <div class="form-group">
                    <label>Akun</label>
                    <select class="form-control account-select" required>
                        <option value="">Pilih akun</option>
                        ${this.chartOfAccounts.map(account => `
                            <option value="${account.id}">${account.account_code} - ${account.account_name}</option>
                        `).join('')}
                    </select>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <input type="text" class="form-control line-description">
                </div>
                <div class="form-group">
                    <label>Debit</label>
                    <input type="number" class="form-control line-debit" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label>Kredit</label>
                    <input type="number" class="form-control line-credit" step="0.01" min="0">
                </div>
            </div>
        `;
        
        container.appendChild(lineDiv);
    }

    // Remove journal line
    removeJournalLine(button) {
        const line = button.closest('.journal-line');
        line.remove();
        
        // Update line numbers
        const container = document.getElementById('journal-lines-container');
        const lines = container.querySelectorAll('.journal-line');
        lines.forEach((line, index) => {
            line.querySelector('.line-header span').textContent = `Baris ${index + 1}`;
        });
    }

    // Save journal entry
    async saveJournalEntry() {
        try {
            const formData = this.collectJournalFormData();
            
            const response = await fetch('/api/accounting-system.php?action=create_journal', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Success', 'Jurnal berhasil disimpan sebagai draft', 'success');
                this.loadAccountingDashboard();
                
                // Close modal
                const modal = document.querySelector('.modal.show');
                if (modal) {
                    bootstrap.Modal.getInstance(modal).hide();
                }
            } else {
                throw new Error(result.error || 'Failed to save journal');
            }
        } catch (error) {
            console.error('Error saving journal entry:', error);
            this.showNotification('Error', 'Gagal menyimpan jurnal', 'error');
        }
    }

    // Post journal entry
    async postJournalEntry() {
        try {
            const formData = this.collectJournalFormData();
            
            // First save as draft
            const saveResponse = await fetch('/api/accounting-system.php?action=create_journal', {
                method: 'POST',
                body: formData
            });
            
            const saveResult = await saveResponse.json();
            
            if (saveResult.success) {
                // Then post the journal
                const postFormData = new FormData();
                postFormData.append('journal_id', saveResult.journal_id);
                postFormData.append('posted_by', getCurrentUserId());
                
                const postResponse = await fetch('/api/accounting-system.php?action=post_journal', {
                    method: 'POST',
                    body: postFormData
                });
                
                const postResult = await postResponse.json();
                
                if (postResult.success) {
                    this.showNotification('Success', 'Jurnal berhasil diposting', 'success');
                    this.loadAccountingDashboard();
                    
                    // Close modal
                    const modal = document.querySelector('.modal.show');
                    if (modal) {
                        bootstrap.Modal.getInstance(modal).hide();
                    }
                } else {
                    throw new Error(postResult.error || 'Failed to post journal');
                }
            } else {
                throw new Error(saveResult.error || 'Failed to save journal');
            }
        } catch (error) {
            console.error('Error posting journal entry:', error);
            this.showNotification('Error', 'Gagal memposting jurnal', 'error');
        }
    }

    // Collect journal form data
    collectJournalFormData() {
        const formData = new FormData();
        
        formData.append('entry_date', document.getElementById('entry-date').value);
        formData.append('description', document.getElementById('entry-description').value);
        formData.append('reference_type', document.getElementById('reference-type').value);
        formData.append('reference_id', document.getElementById('reference-id').value);
        formData.append('created_by', getCurrentUserId());
        
        // Collect journal lines
        const lines = [];
        const journalLines = document.querySelectorAll('.journal-line');
        
        journalLines.forEach(line => {
            const accountId = line.querySelector('.account-select').value;
            const description = line.querySelector('.line-description').value;
            const debit = line.querySelector('.line-debit').value || 0;
            const credit = line.querySelector('.line-credit').value || 0;
            
            if (accountId && (debit > 0 || credit > 0)) {
                lines.push({
                    account_id: accountId,
                    description: description,
                    debit_amount: debit,
                    credit_amount: credit
                });
            }
        });
        
        formData.append('lines', JSON.stringify(lines));
        
        return formData;
    }

    // View general ledger
    async viewGeneralLedger() {
        try {
            const response = await fetch('/api/accounting-system.php?action=general_ledger&account_id=1');
            const result = await response.json();
            
            if (result.success) {
                this.showLedgerModal(result.data);
            }
        } catch (error) {
            console.error('Error loading general ledger:', error);
        }
    }

    // View trial balance
    async viewTrialBalance() {
        try {
            const response = await fetch('/api/accounting-system.php?action=trial_balance');
            const result = await response.json();
            
            if (result.success) {
                this.showTrialBalanceModal(result.data);
            }
        } catch (error) {
            console.error('Error loading trial balance:', error);
        }
    }

    // View balance sheet
    async viewBalanceSheet() {
        try {
            const response = await fetch('/api/accounting-system.php?action=balance_sheet');
            const result = await response.json();
            
            if (result.success) {
                this.showBalanceSheetModal(result.data);
            }
        } catch (error) {
            console.error('Error loading balance sheet:', error);
        }
    }

    // View income statement
    async viewIncomeStatement() {
        try {
            const response = await fetch('/api/accounting-system.php?action=income_statement');
            const result = await response.json();
            
            if (result.success) {
                this.showIncomeStatementModal(result.data);
            }
        } catch (error) {
            console.error('Error loading income statement:', error);
        }
    }

    // Show ledger modal
    showLedgerModal(data) {
        const modal = document.createElement('div');
        modal.className = 'modal fade modal-lg';
        modal.innerHTML = `
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Buku Besar - ${data.account.account_name}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="ledger-info">
                            <h6>${data.account.account_code} - ${data.account.account_name}</h6>
                            <p>Tipe: ${data.account.account_type} | Saldo Akhir: Rp ${data.final_balance.toLocaleString('id-ID')}</p>
                        </div>
                        
                        <div class="ledger-table">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>No. Jurnal</th>
                                        <th>Deskripsi</th>
                                        <th>Debit</th>
                                        <th>Kredit</th>
                                        <th>Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.ledger_entries.map(entry => `
                                        <tr>
                                            <td>${entry.entry_date}</td>
                                            <td>${entry.entry_number}</td>
                                            <td>${entry.description}</td>
                                            <td>Rp ${entry.debit_amount.toLocaleString('id-ID')}</td>
                                            <td>Rp ${entry.credit_amount.toLocaleString('id-ID')}</td>
                                            <td>Rp ${entry.running_balance.toLocaleString('id-ID')}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" onclick="accountingSystem.exportLedger(${data.account.id})">
                            <i class="fas fa-download"></i> Export
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

    // Show trial balance modal
    showTrialBalanceModal(data) {
        const modal = document.createElement('div');
        modal.className = 'modal fade modal-lg';
        modal.innerHTML = `
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Neraca Saldo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="trial-balance-info">
                            <h6>Periode: ${new Date().toLocaleDateString('id-ID')}</h6>
                            <p>Status: ${data.is_balanced ? 'Seimbang ✅' : 'Tidak Seimbang ❌'}</p>
                        </div>
                        
                        <div class="trial-balance-table">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Kode Akun</th>
                                        <th>Nama Akun</th>
                                        <th>Debit</th>
                                        <th>Kredit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.debits.map(account => `
                                        <tr>
                                            <td>${account.account_code}</td>
                                            <td>${account.account_name}</td>
                                            <td>Rp ${account.balance.toLocaleString('id-ID')}</td>
                                            <td>-</td>
                                        </tr>
                                    `).join('')}
                                    ${data.credits.map(account => `
                                        <tr>
                                            <td>${account.account_code}</td>
                                            <td>${account.account_name}</td>
                                            <td>-</td>
                                            <td>Rp ${account.balance.toLocaleString('id-ID')}</td>
                                        </tr>
                                    `).join('')}
                                    <tr class="total-row">
                                        <td colspan="2"><strong>Total</strong></td>
                                        <td><strong>Rp ${data.total_debits.toLocaleString('id-ID')}</strong></td>
                                        <td><strong>Rp ${data.total_credits.toLocaleString('id-ID')}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" onclick="accountingSystem.exportTrialBalance()">
                            <i class="fas fa-download"></i> Export
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

    // Show balance sheet modal
    showBalanceSheetModal(data) {
        const modal = document.createElement('div');
        modal.className = 'modal fade modal-lg';
        modal.innerHTML = `
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Neraca</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="balance-sheet-info">
                            <h6>Periode: ${new Date().toLocaleDateString('id-ID')}</h6>
                            <p>Status: ${data.is_balanced ? 'Seimbang ✅' : 'Tidak Seimbang ❌'}</p>
                        </div>
                        
                        <div class="balance-sheet-content">
                            <div class="balance-sheet-section">
                                <h6>Aset</h6>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama Akun</th>
                                            <th>Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.assets.accounts.map(account => `
                                            <tr>
                                                <td>${account.account_code}</td>
                                                <td>${account.account_name}</td>
                                                <td>Rp ${account.balance.toLocaleString('id-ID')}</td>
                                            </tr>
                                        `).join('')}
                                        <tr class="total-row">
                                            <td colspan="2"><strong>Total Aset</strong></td>
                                            <td><strong>Rp ${data.assets.total.toLocaleString('id-ID')}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="balance-sheet-section">
                                <h6>Kewajiban</h6>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama Akun</th>
                                            <th>Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.liabilities.accounts.map(account => `
                                            <tr>
                                                <td>${account.account_code}</td>
                                                <td>${account.account_name}</td>
                                                <td>Rp ${account.balance.toLocaleString('id-ID')}</td>
                                            </tr>
                                        `).join('')}
                                        <tr class="total-row">
                                            <td colspan="2"><strong>Total Kewajiban</strong></td>
                                            <td><strong>Rp ${data.liabilities.total.toLocaleString('id-ID')}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="balance-sheet-section">
                                <h6>Ekuitas</h6>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama Akun</th>
                                            <th>Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.equity.accounts.map(account => `
                                            <tr>
                                                <td>${account.account_code}</td>
                                                <td>${account.account_name}</td>
                                                <td>Rp ${account.balance.toLocaleString('id-ID')}</td>
                                            </tr>
                                        `).join('')}
                                        <tr class="total-row">
                                            <td colspan="2"><strong>Total Ekuitas</strong></td>
                                            <td><strong>Rp ${data.equity.total.toLocaleString('id-ID')}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="balance-sheet-summary">
                                <table class="table">
                                    <tr>
                                        <td><strong>Total Aset</strong></td>
                                        <td>Rp ${data.assets.total.toLocaleString('id-ID')}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Kewajiban + Ekuitas</strong></td>
                                        <td>Rp ${data.total_liabilities_equity.toLocaleString('id-ID')}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" onclick="accountingSystem.exportBalanceSheet()">
                            <i class="fas fa-download"></i> Export
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

    // Show income statement modal
    showIncomeStatementModal(data) {
        const modal = document.createElement('div');
        modal.className = 'modal fade modal-lg';
        modal.innerHTML = `
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Laporan Laba Rugi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="income-statement-info">
                            <h6>Periode: ${new Date().toLocaleDateString('id-ID')}</h6>
                        </div>
                        
                        <div class="income-statement-content">
                            <div class="income-statement-section">
                                <h6>Pendapatan</h6>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama Akun</th>
                                            <th>Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.revenues.accounts.map(account => `
                                            <tr>
                                                <td>${account.account_code}</td>
                                                <td>${account.account_name}</td>
                                                <td>Rp ${account.balance.toLocaleString('id-ID')}</td>
                                            </tr>
                                        `).join('')}
                                        <tr class="total-row">
                                            <td colspan="2"><strong>Total Pendapatan</strong></td>
                                            <td><strong>Rp ${data.revenues.total.toLocaleString('id-ID')}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="income-statement-section">
                                <h6>Beban</h6>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama Akun</th>
                                            <th>Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.expenses.accounts.map(account => `
                                            <tr>
                                                <td>${account.account_code}</td>
                                                <td>${account.account_name}</td>
                                                <td>Rp ${account.balance.toLocaleString('id-ID')}</td>
                                            </tr>
                                        `).join('')}
                                        <tr class="total-row">
                                            <td colspan="2"><strong>Total Beban</strong></td>
                                            <td><strong>Rp ${data.expenses.total.toLocaleString('id-ID')}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="income-statement-summary">
                                <table class="table">
                                    <tr>
                                        <td><strong>Total Pendapatan</strong></td>
                                        <td>Rp ${data.revenues.total.toLocaleString('id-ID')}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Beban</strong></td>
                                        <td>Rp ${data.expenses.total.toLocaleString('id-ID')}</td>
                                    </tr>
                                    <tr class="net-income-row">
                                        <td><strong>Laba Bersih</strong></td>
                                        <td><strong>Rp ${data.net_income.toLocaleString('id-ID')}</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" onclick="accountingSystem.exportIncomeStatement()">
                            <i class="fas fa-download"></i> Export
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

    // Setup event listeners
    setupEventListeners() {
        // Auto-refresh dashboard data
        setInterval(() => {
            this.loadAccountingDashboard();
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

// Initialize accounting system when page loads
let accountingSystem = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('accounting-dashboard')) {
        accountingSystem = new AccountingSystem();
        accountingSystem.initialize();
    }
});

// Helper function to get current user ID
function getCurrentUserId() {
    const currentUser = JSON.parse(localStorage.getItem('currentUser') || '{}');
    return currentUser.id || 1;
}
