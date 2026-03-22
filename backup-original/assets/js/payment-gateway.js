// Payment Gateway Integration
class PaymentGateway {
    constructor() {
        this.paymentMethods = [];
        this.currentTransaction = null;
        this.isProcessing = false;
    }

    // Initialize payment methods
    async initializePaymentMethods() {
        try {
            const response = await fetch('/api/payment-gateway.php?action=methods');
            const result = await response.json();
            
            if (result.success) {
                this.paymentMethods = result.data;
                this.renderPaymentMethods();
            }
        } catch (error) {
            console.error('Error loading payment methods:', error);
        }
    }

    // Render payment methods
    renderPaymentMethods() {
        const container = document.getElementById('payment-methods');
        if (!container) return;

        container.innerHTML = `
            <div class="payment-methods-grid">
                ${this.paymentMethods.map(method => `
                    <div class="payment-method-card" data-method="${method.id}">
                        <div class="method-icon">
                            <i class="${this.getMethodIcon(method.id)}"></i>
                        </div>
                        <div class="method-info">
                            <h6>${method.name}</h6>
                            <p>${method.description}</p>
                            <small class="text-muted">Fee: Rp ${method.fee.toLocaleString('id-ID')}</small>
                        </div>
                        <div class="method-action">
                            <button class="btn btn-primary btn-sm" onclick="paymentGateway.selectPaymentMethod('${method.id}')">
                                Pilih
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    // Get method icon
    getMethodIcon(methodId) {
        const icons = {
            'cash': 'fas fa-money-bill-wave',
            'transfer': 'fas fa-university',
            'ewallet': 'fas fa-wallet',
            'qris': 'fas fa-qrcode'
        };
        return icons[methodId] || 'fas fa-credit-card';
    }

    // Select payment method
    selectPaymentMethod(methodId) {
        this.currentTransaction = {
            methodId: methodId,
            method: this.paymentMethods.find(m => m.id === methodId)
        };

        // Update UI
        document.querySelectorAll('.payment-method-card').forEach(card => {
            card.classList.remove('selected');
        });
        document.querySelector(`[data-method="${methodId}"]`).classList.add('selected');

        // Show payment form
        this.showPaymentForm();
    }

    // Show payment form
    showPaymentForm() {
        const formContainer = document.getElementById('payment-form');
        if (!formContainer) return;

        const method = this.currentTransaction.method;
        
        formContainer.innerHTML = `
            <div class="payment-form">
                <h5>Bayar dengan ${method.name}</h5>
                <div class="form-group">
                    <label for="payment-amount">Jumlah Pembayaran</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" id="payment-amount" 
                               placeholder="0" min="1000" step="1000">
                        <span class="input-group-text">,-</span>
                    </div>
                </div>
                
                ${method.id === 'cash' ? this.renderCashForm() : ''}
                ${method.id === 'transfer' ? this.renderTransferForm() : ''}
                ${method.id === 'ewallet' ? this.renderEwalletForm() : ''}
                ${method.id === 'qris' ? this.renderQrisForm() : ''}
                
                <div class="form-group">
                    <label for="payment-description">Deskripsi (Opsional)</label>
                    <textarea class="form-control" id="payment-description" rows="2" 
                              placeholder="Masukkan deskripsi pembayaran"></textarea>
                </div>
                
                <div class="payment-summary">
                    <div class="summary-row">
                        <span>Amount:</span>
                        <span id="summary-amount">Rp 0,-</span>
                    </div>
                    <div class="summary-row">
                        <span>Fee:</span>
                        <span id="summary-fee">Rp ${method.fee.toLocaleString('id-ID')}</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span id="summary-total">Rp ${method.fee.toLocaleString('id-ID')}</span>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button class="btn btn-secondary" onclick="paymentGateway.cancelPayment()">
                        Batal
                    </button>
                    <button class="btn btn-primary" onclick="paymentGateway.processPayment()" id="process-payment-btn">
                        Proses Pembayaran
                    </button>
                </div>
            </div>
        `;

        // Setup event listeners
        this.setupPaymentFormListeners();
    }

    // Render cash payment form
    renderCashForm() {
        return `
            <div class="cash-payment">
                <div class="form-group">
                    <label for="cash-received">Uang Diterima</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" id="cash-received" 
                               placeholder="0" min="0" step="1000">
                        <span class="input-group-text">,-</span>
                    </div>
                </div>
                <div class="change-info">
                    <small class="text-muted">Kembalian: Rp <span id="cash-change">0</span></small>
                </div>
            </div>
        `;
    }

    // Render bank transfer form
    renderTransferForm() {
        return `
            <div class="transfer-payment">
                <div class="bank-info">
                    <h6>Informasi Transfer</h6>
                    <p>Bank: BCA</p>
                    <p>No. Rekening: 1234567890</p>
                    <p>Atas Nama: KSP Lam Gabe Jaya</p>
                </div>
                <div class="form-group">
                    <label for="transfer-proof">Bukti Transfer (Opsional)</label>
                    <input type="file" class="form-control" id="transfer-proof" accept="image/*">
                </div>
            </div>
        `;
    }

    // Render e-wallet form
    renderEwalletForm() {
        return `
            <div class="ewallet-payment">
                <div class="form-group">
                    <label for="ewallet-number">Nomor E-Wallet</label>
                    <input type="tel" class="form-control" id="ewallet-number" 
                           placeholder="08xxxxxxxxxx" maxlength="13">
                </div>
                <div class="ewallet-options">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="ewallet-type" id="gopay" value="gopay">
                        <label class="form-check-label" for="gopay">GoPay</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="ewallet-type" id="ovo" value="ovo">
                        <label class="form-check-label" for="ovo">OVO</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="ewallet-type" id="dana" value="dana">
                        <label class="form-check-label" for="dana">DANA</label>
                    </div>
                </div>
            </div>
        `;
    }

    // Render QRIS form
    renderQrisForm() {
        return `
            <div class="qris-payment">
                <div class="qrcode-container">
                    <div class="qrcode-placeholder">
                        <i class="fas fa-qrcode fa-3x"></i>
                        <p>Scan QR Code untuk pembayaran</p>
                    </div>
                </div>
                <div class="qris-info">
                    <small class="text-muted">QR Code akan muncul setelah klik "Proses Pembayaran"</small>
                </div>
            </div>
        `;
    }

    // Setup payment form listeners
    setupPaymentFormListeners() {
        const amountInput = document.getElementById('payment-amount');
        const cashReceivedInput = document.getElementById('cash-received');
        
        if (amountInput) {
            amountInput.addEventListener('input', () => this.updatePaymentSummary());
        }
        
        if (cashReceivedInput) {
            cashReceivedInput.addEventListener('input', () => this.calculateChange());
        }
    }

    // Update payment summary
    updatePaymentSummary() {
        const amount = parseFloat(document.getElementById('payment-amount').value) || 0;
        const fee = this.currentTransaction.method.fee;
        const total = amount + fee;

        document.getElementById('summary-amount').textContent = `Rp ${amount.toLocaleString('id-ID')}`;
        document.getElementById('summary-total').textContent = `Rp ${total.toLocaleString('id-ID')}`;
    }

    // Calculate change for cash payments
    calculateChange() {
        const amount = parseFloat(document.getElementById('payment-amount').value) || 0;
        const received = parseFloat(document.getElementById('cash-received').value) || 0;
        const fee = this.currentTransaction.method.fee;
        const total = amount + fee;
        const change = received - total;

        document.getElementById('cash-change').textContent = change.toLocaleString('id-ID');
    }

    // Process payment
    async processPayment() {
        if (this.isProcessing) return;
        
        this.isProcessing = true;
        const processBtn = document.getElementById('process-payment-btn');
        processBtn.disabled = true;
        processBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

        try {
            const amount = parseFloat(document.getElementById('payment-amount').value) || 0;
            const description = document.getElementById('payment-description').value || '';
            const currentUser = JSON.parse(localStorage.getItem('currentUser') || '{}');

            if (amount <= 0) {
                throw new Error('Jumlah pembayaran harus lebih dari 0');
            }

            const formData = new FormData();
            formData.append('member_id', currentUser.id);
            formData.append('amount', amount);
            formData.append('payment_type', this.currentTransaction.method.id);
            formData.append('payment_gateway', this.currentTransaction.method.id);
            formData.append('description', description);

            // Add method-specific data
            if (this.currentTransaction.method.id === 'cash') {
                const cashReceived = document.getElementById('cash-received').value;
                formData.append('cash_received', cashReceived);
            }

            const response = await fetch('/api/payment-gateway.php?action=process', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showPaymentSuccess(result);
            } else {
                throw new Error(result.error || 'Pembayaran gagal');
            }

        } catch (error) {
            this.showPaymentError(error.message);
        } finally {
            this.isProcessing = false;
            processBtn.disabled = false;
            processBtn.innerHTML = 'Proses Pembayaran';
        }
    }

    // Show payment success
    showPaymentSuccess(result) {
        const formContainer = document.getElementById('payment-form');
        formContainer.innerHTML = `
            <div class="payment-success text-center">
                <div class="success-icon">
                    <i class="fas fa-check-circle fa-4x text-success"></i>
                </div>
                <h5>Pembayaran Berhasil!</h5>
                <p>Transaksi ID: ${result.transaction_id}</p>
                <p>Jumlah: Rp ${result.amount.toLocaleString('id-ID')}</p>
                <p>Status: <span class="badge bg-success">${result.status}</span></p>
                <div class="mt-3">
                    <button class="btn btn-primary" onclick="paymentGateway.printReceipt('${result.transaction_id}')">
                        <i class="fas fa-print"></i> Cetak Struk
                    </button>
                    <button class="btn btn-secondary ms-2" onclick="paymentGateway.newPayment()">
                        Pembayaran Baru
                    </button>
                </div>
            </div>
        `;

        // Show notification
        this.showNotification('Pembayaran berhasil diproses', 'success');
        
        // Update balance display
        this.updateBalanceDisplay();
    }

    // Show payment error
    showPaymentError(error) {
        this.showNotification(error, 'error');
    }

    // Cancel payment
    cancelPayment() {
        this.currentTransaction = null;
        document.getElementById('payment-methods').innerHTML = '';
        document.getElementById('payment-form').innerHTML = '';
    }

    // New payment
    newPayment() {
        this.cancelPayment();
        this.initializePaymentMethods();
    }

    // Print receipt
    printReceipt(transactionId) {
        window.print();
    }

    // Update balance display
    async updateBalanceDisplay() {
        try {
            const currentUser = JSON.parse(localStorage.getItem('currentUser') || '{}');
            const response = await fetch(`/api/payment-gateway.php?action=history&member_id=${currentUser.id}&limit=1`);
            const result = await response.json();
            
            if (result.success && result.data.length > 0) {
                const balance = result.data[0].amount || 0;
                const balanceElement = document.getElementById('current-balance');
                if (balanceElement) {
                    balanceElement.textContent = `Rp ${balance.toLocaleString('id-ID')}`;
                }
            }
        } catch (error) {
            console.error('Error updating balance:', error);
        }
    }

    // Show notification
    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.innerHTML = `
            ${message}
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

// Initialize payment gateway when page loads
let paymentGateway = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('payment-container')) {
        paymentGateway = new PaymentGateway();
        paymentGateway.initializePaymentMethods();
    }
});
