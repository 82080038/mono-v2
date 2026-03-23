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
    
    <!-- Security Headers -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' 'unsafe-inline' 'unsafe-eval'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/; img-src 'self' data: https:; connect-src 'self' https://cdn.jsdelivr.net; base-uri 'self'; form-action 'self'">
    
    <style>
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
    
    <script>
        // Global variables
        let selectedMember = null;
        let searchTimeout = null;
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadTodaySummary();
            loadTodayTransactions();
            initializeEventListeners();
        });
        
        // Initialize event listeners
        function initializeEventListeners() {
            // Member search
            document.getElementById('memberSearch').addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                const searchTerm = e.target.value.trim();
                
                if (searchTerm.length >= 3) {
                    searchTimeout = setTimeout(() => searchMembers(searchTerm), 500);
                } else {
                    document.getElementById('searchResults').innerHTML = '';
                }
            });
            
            // Form submission
            document.getElementById('depositForm').addEventListener('submit', function(e) {
                e.preventDefault();
                processDeposit();
            });
            
            // Clear search on escape
            document.getElementById('memberSearch').addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    document.getElementById('searchResults').innerHTML = '';
                    clearSelectedMember();
                }
            });
        }
        
        // Search members
        async function searchMembers(searchTerm) {
            try {
                document.querySelector('.search-loading').style.display = 'block';
                
                const response = await fetch(`/mono-v2/api/business-logic.php?action=search_member&q=${encodeURIComponent(searchTerm)}`);
                const result = await response.json();
                
                document.querySelector('.search-loading').style.display = 'none';
                
                if (result.success) {
                    displaySearchResults(result.data);
                } else {
                    console.error('Search failed:', result.message);
                }
            } catch (error) {
                console.error('Search error:', error);
                document.querySelector('.search-loading').style.display = 'none';
            }
        }
        
        // Display search results
        function displaySearchResults(members) {
            const resultsContainer = document.getElementById('searchResults');
            
            if (members.length === 0) {
                resultsContainer.innerHTML = '<div class="alert alert-warning">Tidak ada anggota yang ditemukan</div>';
                return;
            }
            
            let html = '<div class="list-group">';
            members.forEach(member => {
                html += `
                    <div class="list-group-item member-search-result" onclick="selectMember(${member.id})">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${member.full_name}</h6>
                            <small>${member.member_number}</small>
                        </div>
                        <p class="mb-1">
                            <i class="fas fa-phone me-1"></i>${member.phone || '-'}
                            ${member.email ? `<br><i class="fas fa-envelope me-1"></i>${member.email}` : ''}
                        </p>
                        <small class="text-success">
                            <i class="fas fa-check-circle me-1"></i>Aktif
                        </small>
                    </div>
                `;
            });
            html += '</div>';
            
            resultsContainer.innerHTML = html;
        }
        
        // Select member
        async function selectMember(memberId) {
            try {
                const response = await fetch(`/mono-v2/api/business-logic.php?action=get_member_accounts&member_id=${memberId}`);
                const result = await response.json();
                
                if (result.success) {
                    selectedMember = result.data[0]; // Get first account
                    displaySelectedMember();
                    loadAccountOptions();
                } else {
                    alert('Gagal memuat data anggota: ' + result.message);
                }
            } catch (error) {
                console.error('Select member error:', error);
                alert('Terjadi kesalahan saat memilih anggota');
            }
        }
        
        // Display selected member
        function displaySelectedMember() {
            if (!selectedMember) return;
            
            document.getElementById('memberSearch').value = selectedMember.full_name;
            document.getElementById('searchResults').innerHTML = '';
            
            const memberInfo = document.getElementById('selectedMemberInfo');
            const memberDetails = document.getElementById('memberDetails');
            
            memberDetails.innerHTML = `
                <strong>${selectedMember.full_name}</strong><br>
                No. Anggota: ${selectedMember.member_number || '-'}<br>
                No. Rekening: ${selectedMember.account_number}<br>
                Saldo Saat Ini: <strong>Rp ${formatNumber(selectedMember.balance)}</strong>
            `;
            
            memberInfo.style.display = 'block';
        }
        
        // Load account options
        async function loadAccountOptions() {
            try {
                const response = await fetch(`/mono-v2/api/business-logic.php?action=get_member_accounts&member_id=${selectedMember.member_id}`);
                const result = await response.json();
                
                if (result.success) {
                    const accountSelect = document.getElementById('accountType');
                    accountSelect.innerHTML = '<option value="">Pilih Jenis Simpanan</option>';
                    
                    result.data.forEach(account => {
                        accountSelect.innerHTML += `<option value="${account.account_type}">${account.account_name} (Saldo: Rp ${formatNumber(account.balance)})</option>`;
                    });
                }
            } catch (error) {
                console.error('Load accounts error:', error);
            }
        }
        
        // Process deposit
        async function processDeposit() {
            if (!selectedMember) {
                alert('Silakan pilih anggota terlebih dahulu');
                return;
            }
            
            const formData = {
                member_id: selectedMember.member_id,
                account_type: document.getElementById('accountType').value,
                amount: parseFloat(document.getElementById('amount').value),
                payment_method: document.getElementById('paymentMethod').value,
                description: document.getElementById('description').value
            };
            
            // Validate
            if (!formData.account_type) {
                alert('Silakan pilih jenis simpanan');
                return;
            }
            
            if (formData.amount < 1000) {
                alert('Minimal setoran adalah Rp 1.000');
                return;
            }
            
            try {
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
                
                const response = await fetch('/mono-v2/api/business-logic.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'process_deposit',
                        ...formData
                    })
                });
                
                const result = await response.json();
                
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Proses Setoran';
                
                if (result.success) {
                    displayReceipt(result.data);
                    loadTodaySummary();
                    loadTodayTransactions();
                    
                    // Show success message
                    showAlert('Setoran berhasil diproses!', 'success');
                } else {
                    alert('Gagal memproses setoran: ' + result.message);
                }
            } catch (error) {
                console.error('Process deposit error:', error);
                alert('Terjadi kesalahan saat memproses setoran');
                
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Proses Setoran';
            }
        }
        
        // Display receipt
        function displayReceipt(receiptData) {
            const receiptContent = document.getElementById('receiptContent');
            receiptContent.innerHTML = `
                <div class="row">
                    <div class="col-6">
                        <strong>Kode Transaksi:</strong><br>
                        <span>${receiptData.transaction_code}</span>
                    </div>
                    <div class="col-6 text-end">
                        <strong>Tanggal:</strong><br>
                        <span>${receiptData.timestamp}</span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <strong>Anggota:</strong><br>
                        ${receiptData.member_name}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-6">
                        <strong>Jenis Simpanan:</strong><br>
                        ${receiptData.account_type}
                    </div>
                    <div class="col-6 text-end">
                        <strong>Jumlah:</strong><br>
                        <span class="text-success">Rp ${formatNumber(receiptData.amount)}</span>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-6">
                        <strong>Metode:</strong><br>
                        ${receiptData.payment_method}
                    </div>
                    <div class="col-6 text-end">
                        <strong>Saldo Baru:</strong><br>
                        <span class="text-primary">Rp ${formatNumber(receiptData.new_balance)}</span>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <small>Teller: ${receiptData.teller}</small>
                </div>
            `;
            
            document.getElementById('receiptPreview').style.display = 'block';
            document.getElementById('depositForm').style.display = 'none';
        }
        
        // Load today's summary
        async function loadTodaySummary() {
            try {
                const response = await fetch('/mono-v2/api/business-logic.php?action=get_today_summary');
                const result = await response.json();
                
                if (result.success) {
                    const summary = result.data;
                    document.getElementById('todayTransactions').textContent = summary.total_transactions;
                    document.getElementById('totalDeposits').textContent = 'Rp ' + formatNumber(summary.total_deposits);
                    document.getElementById('totalWithdrawals').textContent = 'Rp ' + formatNumber(summary.total_withdrawals);
                    document.getElementById('totalAmount').textContent = 'Rp ' + formatNumber(summary.total_amount);
                }
            } catch (error) {
                console.error('Load summary error:', error);
            }
        }
        
        // Load today's transactions
        async function loadTodayTransactions() {
            try {
                const response = await fetch('/mono-v2/api/business-logic.php?action=get_today_transactions');
                const result = await response.json();
                
                if (result.success) {
                    displayTransactions(result.data);
                }
            } catch (error) {
                console.error('Load transactions error:', error);
            }
        }
        
        // Display transactions
        function displayTransactions(transactions) {
            const tbody = document.getElementById('transactionsTable');
            
            if (transactions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Belum ada transaksi hari ini</td></tr>';
                return;
            }
            
            let html = '';
            transactions.forEach(transaction => {
                const typeClass = transaction.transaction_type === 'credit' ? 'text-success' : 'text-danger';
                const typeIcon = transaction.transaction_type === 'credit' ? 'fa-arrow-down' : 'fa-arrow-up';
                const typeName = transaction.transaction_type === 'credit' ? 'Setoran' : 'Penarikan';
                
                html += `
                    <tr>
                        <td><small>${transaction.transaction_code}</small></td>
                        <td>${transaction.member_name}</td>
                        <td><span class="${typeClass}"><i class="fas ${typeIcon} me-1"></i>${typeName}</span></td>
                        <td class="${typeClass}">Rp ${formatNumber(transaction.amount)}</td>
                        <td><small>${transaction.payment_method}</small></td>
                        <td><small>${formatDateTime(transaction.created_at)}</small></td>
                        <td><span class="badge bg-success">${transaction.status}</span></td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
        }
        
        // New transaction
        function newTransaction() {
            document.getElementById('depositForm').reset();
            document.getElementById('depositForm').style.display = 'block';
            document.getElementById('receiptPreview').style.display = 'none';
            document.getElementById('selectedMemberInfo').style.display = 'none';
            document.getElementById('searchResults').innerHTML = '';
            selectedMember = null;
        }
        
        // Print receipt
        function printReceipt() {
            window.print();
        }
        
        // Clear selected member
        function clearSelectedMember() {
            selectedMember = null;
            document.getElementById('selectedMemberInfo').style.display = 'none';
            document.getElementById('accountType').innerHTML = '<option value="">Pilih Jenis Simpanan</option>';
        }
        
        // Show alert
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }
        
        // Utility functions
        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }
        
        function formatDateTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('id-ID', {
                day: '2-digit',
                month: '2-digit', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
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
        
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
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
    </script>
</body>
</html>
