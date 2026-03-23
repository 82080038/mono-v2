<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setoran - KSP Lam Gabe Jaya</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/mono-v2/assets/css/dashboard.css">
    <link rel="stylesheet" href="/mono-v2/assets/css/ksp-responsive.css">
    
    <!-- Security Headers -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' 'unsafe-inline' 'unsafe-eval'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/; img-src 'self' data: https:; connect-src 'self' https://cdn.jsdelivr.net; base-uri 'self'; form-action 'self'">
    
    <!-- PWA Support -->
    <meta name="theme-color" content="#007bff">
    <link rel="manifest" href="/manifest.json">
    
    <style>
        /* Additional page-specific styles */
        .transaction-form {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .member-search-result {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .member-search-result:hover {
            background-color: #f8f9fa;
        }
        
        .receipt-preview {
            background: white;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            display: none;
        }
        
        .today-stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .search-loading {
            display: none;
            text-align: center;
            padding: 1rem;
        }
        
        .form-floating label {
            color: #6c757d;
        }
        
        .btn-primary {
            background: var(--primary-color, #007bff);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark, #0056b3);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,123,255,0.3);
        }
        
        .alert-success {
            border-left: 4px solid #28a745;
        }
        
        .transaction-table {
            font-size: 0.9rem;
        }
        
        .transaction-table th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }
        
        /* Mobile-specific enhancements */
        @media (max-width: 768px) {
            .transaction-form {
                padding: 1rem;
                margin-bottom: 1rem;
            }
            
            .today-stats {
                padding: 1rem;
            }
            
            .today-stats .row > div {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <div class="header-left">
                <button class="btn btn-link" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <a href="/mono-v2/?page=dashboard" class="brand">
                    <i class="fas fa-university"></i>
                    KSP Lam Gabe Jaya
                </a>
            </div>
            
            <div class="header-right">
                <div class="user-dropdown">
                    <div class="user-avatar" onclick="toggleUserMenu()">
                        <?php echo strtoupper(substr($userName, 0, 2)); ?>
                    </div>
                    
                    <div class="dropdown-menu dropdown-menu-end" id="userMenu" style="display: none;">
                        <div class="dropdown-item-text">
                            <strong><?php echo htmlspecialchars($userName); ?></strong>
                            <br>
                            <small class="text-muted"><?php echo htmlspecialchars(ucfirst($userRole)); ?></small>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" onclick="showProfile()">
                            <i class="fas fa-user me-2"></i>Profil
                        </a>
                        <a class="dropdown-item" href="#" onclick="showSettings()">
                            <i class="fas fa-cog me-2"></i>Pengaturan
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" onclick="logout()">
                            <i class="fas fa-sign-out-alt me-2"></i>Keluar
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Sidebar -->
        <aside class="app-sidebar" id="sidebar">
            <nav class="sidebar-menu">
                <a href="/mono-v2/?page=dashboard" class="menu-item">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="/mono-v2/?page=nasabah" class="menu-item">
                    <i class="fas fa-users"></i>
                    Nasabah
                </a>
                <a href="/mono-v2/?page=setoran" class="menu-item active">
                    <i class="fas fa-plus-circle"></i>
                    Setoran
                </a>
                <a href="/mono-v2/?page=penarikan" class="menu-item">
                    <i class="fas fa-minus-circle"></i>
                    Penarikan
                </a>
                <a href="/mono-v2/?page=pembayaran" class="menu-item">
                    <i class="fas fa-credit-card"></i>
                    Pembayaran
                </a>
                <a href="/mono-v2/?page=laporan_harian" class="menu-item">
                    <i class="fas fa-clipboard-list"></i>
                    Laporan Harian
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="app-main">
            <div class="dashboard-header">
                <h1>Setoran</h1>
                <p>Proses setoran simpanan anggota koperasi</p>
            </div>
            
            <!-- Today's Statistics -->
            <div class="today-stats">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 id="todayTransactions">0</h3>
                            <small>Transaksi Hari Ini</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 id="totalDeposits">Rp 0</h3>
                            <small>Total Setoran</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 id="totalWithdrawals">Rp 0</h3>
                            <small>Total Penarikan</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 id="totalAmount">Rp 0</h3>
                            <small>Nilai Transaksi</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Transaction Form -->
            <div class="transaction-form">
                <h5 class="mb-4">
                    <i class="fas fa-plus-circle me-2"></i>Form Setoran
                </h5>
                
                <form id="depositForm">
                    <!-- Member Search -->
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="memberSearch" placeholder="Cari anggota..." autocomplete="off">
                        <label for="memberSearch">Cari Anggota (Nama, No. Anggota, atau Telepon)</label>
                        <div class="search-loading">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Mencari...</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Search Results -->
                    <div id="searchResults" class="mb-3"></div>
                    
                    <!-- Selected Member Info -->
                    <div id="selectedMemberInfo" class="alert alert-info" style="display: none;">
                        <h6>Anggota Terpilih:</h6>
                        <div id="memberDetails"></div>
                    </div>
                    
                    <!-- Account Type -->
                    <div class="form-floating mb-3">
                        <select class="form-select" id="accountType" required>
                            <option value="">Pilih Jenis Simpanan</option>
                            <option value="simpanan">Simpanan Sukarela</option>
                            <option value="simpanan_wajib">Simpanan Wajib</option>
                            <option value="simpanan_pokok">Simpanan Pokok</option>
                        </select>
                        <label for="accountType">Jenis Simpanan</label>
                    </div>
                    
                    <!-- Amount -->
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="amount" placeholder="0" min="1000" step="1000" required>
                        <label for="amount">Jumlah Setoran (Rp)</label>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="form-floating mb-3">
                        <select class="form-select" id="paymentMethod" required>
                            <option value="cash">Tunai</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="bank_deposit">Setoran Bank</option>
                            <option value="digital_payment">Pembayaran Digital</option>
                        </select>
                        <label for="paymentMethod">Metode Pembayaran</label>
                    </div>
                    
                    <!-- Description -->
                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="description" placeholder="Catatan" style="height: 80px"></textarea>
                        <label for="description">Catatan (Opsional)</label>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="fas fa-save me-2"></i>Proses Setoran
                        </button>
                    </div>
                </form>
                
                <!-- Receipt Preview -->
                <div id="receiptPreview" class="receipt-preview">
                    <h6>Bukti Setoran</h6>
                    <div id="receiptContent"></div>
                    <div class="mt-3">
                        <button class="btn btn-success btn-sm" onclick="printReceipt()">
                            <i class="fas fa-print me-2"></i>Cetak Bukti
                        </button>
                        <button class="btn btn-outline-secondary btn-sm ms-2" onclick="newTransaction()">
                            <i class="fas fa-plus me-2"></i>Transaksi Baru
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Recent Transactions -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-history me-2"></i>Transaksi Terakhir</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped transaction-table">
                            <thead>
                                <tr>
                                    <th>Kode Transaksi</th>
                                    <th>Anggota</th>
                                    <th>Jenis</th>
                                    <th>Jumlah</th>
                                    <th>Metode</th>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="transactionsTable">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="fas fa-spinner fa-spin me-2"></i>Memuat data...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Enhanced JavaScript -->
    <script src="/mono-v2/assets/js/ksp-enhanced.js"></script>
    
    <script>
        // User menu functions (from existing code)
        function toggleUserMenu() {
            const userMenu = document.getElementById('userMenu');
            if (userMenu.style.display === 'none' || userMenu.style.display === '') {
                userMenu.style.display = 'block';
                userMenu.classList.add('show');
            } else {
                userMenu.style.display = 'none';
                userMenu.classList.remove('show');
            }
        }
        
        function showProfile() {
            // TODO: Implement profile modal
            alert('Fitur profil akan segera tersedia');
        }
        
        function showSettings() {
            // TODO: Implement settings modal
            alert('Fitur pengaturan akan segera tersedia');
        }
        
        function logout() {
            if (confirm('Apakah Anda yakin ingin keluar?')) {
                window.location.href = '/mono-v2/?action=logout';
            }
        }
        
        function printReceipt() {
            window.print();
        }
        
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });
        
        // Close user menu when clicking outside
        document.addEventListener('click', function(e) {
            const userMenu = document.getElementById('userMenu');
            const userDropdown = document.querySelector('.user-dropdown');
            
            if (userMenu && !e.target.closest('.user-dropdown')) {
                userMenu.style.display = 'none';
                userMenu.classList.remove('show');
            }
        });
        
        // Additional page-specific enhancements
        document.addEventListener('DOMContentLoaded', function() {
            // Add keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl+N for new transaction
                if (e.ctrlKey && e.key === 'n') {
                    e.preventDefault();
                    newTransaction();
                }
                
                // Ctrl+P for print receipt
                if (e.ctrlKey && e.key === 'p') {
                    e.preventDefault();
                    const receiptPreview = document.getElementById('receiptPreview');
                    if (receiptPreview.style.display === 'block') {
                        printReceipt();
                    }
                }
                
                // Escape to clear search
                if (e.key === 'Escape') {
                    const searchInput = document.getElementById('memberSearch');
                    if (document.activeElement === searchInput) {
                        searchInput.value = '';
                        searchInput.blur();
                    }
                }
            });
            
            // Add real-time validation feedback
            const amountInput = document.getElementById('amount');
            if (amountInput) {
                amountInput.addEventListener('input', function(e) {
                    const value = parseFloat(e.target.value);
                    const submitBtn = document.getElementById('submitBtn');
                    
                    if (value < 1000) {
                        e.target.classList.add('is-invalid');
                        submitBtn.disabled = true;
                    } else if (value > 100000000) {
                        e.target.classList.add('is-invalid');
                        submitBtn.disabled = true;
                    } else {
                        e.target.classList.remove('is-invalid');
                        submitBtn.disabled = false;
                    }
                });
            }
            
            // Add auto-save draft functionality
            let draftTimeout;
            const form = document.getElementById('depositForm');
            
            form.addEventListener('input', function() {
                clearTimeout(draftTimeout);
                draftTimeout = setTimeout(() => {
                    saveFormDraft();
                }, 2000);
            });
            
            function saveFormDraft() {
                const formData = {
                    memberSearch: document.getElementById('memberSearch').value,
                    accountType: document.getElementById('accountType').value,
                    amount: document.getElementById('amount').value,
                    paymentMethod: document.getElementById('paymentMethod').value,
                    description: document.getElementById('description').value
                };
                
                localStorage.setItem('depositDraft', JSON.stringify(formData));
                console.log('Draft saved');
            }
            
            function loadFormDraft() {
                const draft = localStorage.getItem('depositDraft');
                if (draft) {
                    try {
                        const formData = JSON.parse(draft);
                        
                        // Only restore if form is empty
                        if (!document.getElementById('memberSearch').value) {
                            document.getElementById('memberSearch').value = formData.memberSearch || '';
                            document.getElementById('accountType').value = formData.accountType || '';
                            document.getElementById('amount').value = formData.amount || '';
                            document.getElementById('paymentMethod').value = formData.paymentMethod || 'cash';
                            document.getElementById('description').value = formData.description || '';
                        }
                    } catch (e) {
                        console.error('Failed to load draft:', e);
                    }
                }
            }
            
            // Load draft on page load
            loadFormDraft();
            
            // Clear draft on successful submission
            const originalProcessDeposit = window.transactionProcessor?.processDeposit;
            if (originalProcessDeposit) {
                window.transactionProcessor.processDeposit = async function() {
                    const result = await originalProcessDeposit.call(this);
                    if (result) {
                        localStorage.removeItem('depositDraft');
                    }
                    return result;
                };
            }
        });
    </script>
</body>
</html>
