// Error Handling Wrapper
(function() {
    /**
 * Indonesian Language Helper for Enum Values
 * Translates English enum values to Indonesian for display
 */

class IndonesianTranslator {
    constructor() {
        this.translations = {
            // User Roles
            userRoles: {
                'Super Admin': 'Super Admin',
                'Admin': 'Administrator',
                'Manager': 'Manajer', 
                'Teller': 'Teller',
                'Staff': 'Staf',
                'Owner': 'Pemilik'
            },
            
            // Loan Status
            loanStatus: {
                'Applied': 'Diajukan',
                'Approved': 'Disetujui',
                'Rejected': 'Ditolak',
                'Disbursed': 'Dicairkan',
                'Active': 'Aktif',
                'Late': 'Terlambat',
                'Default': 'Gagal Bayar',
                'Restructured': 'Restrukturisasi',
                'Paid Off': 'Lunas'
            },
            
            // Payment Types
            paymentTypes: {
                'loan_payment': 'Pembayaran Pinjaman',
                'deposit': 'Simpanan',
                'registration_fee': 'Biaya Pendaftaran',
                'other': 'Lainnya'
            },
            
            // Payment Status
            paymentStatus: {
                'pending': 'Menunggu',
                'paid': 'Dibayar',
                'failed': 'Gagal',
                'expired': 'Kadaluarsa',
                'cancelled': 'Dibatalkan'
            },
            
            // Payment Gateways
            paymentGateways: {
                'qris': 'QRIS',
                'bank_transfer': 'Transfer Bank',
                'ewallet': 'E-Wallet',
                'cash': 'Tunai'
            },
            
            // Collection Types
            collectionTypes: {
                'loan_payment': 'Pembayaran Pinjaman',
                'savings_deposit': 'Setoran Simpanan',
                'fee_payment': 'Pembayaran Biaya'
            },
            
            // Collection Status
            collectionStatus: {
                'scheduled': 'Dijadwalkan',
                'in_progress': 'Sedang Berlangsung',
                'collected': 'Terkumpul',
                'missed': 'Terlewat',
                'postponed': 'Ditunda',
                'cancelled': 'Dibatalkan'
            },
            
            // Member Status
            memberStatus: {
                'Active': 'Aktif',
                'Inactive': 'Tidak Aktif',
                'Pending': 'Menunggu',
                'Suspended': 'Ditangguhkan'
            },
            
            // Member Types
            memberTypes: {
                'Regular': 'Regular',
                'Premium': 'Premium',
                'Board': 'Pengurus',
                'Honorary': 'Kehormatan',
                'Associate': 'Associate'
            },
            
            // Account Types
            accountTypes: {
                'SA_POKOK': 'Simpanan Pokok',
                'SA_WAJIB': 'Simpanan Wajib',
                'SA_SUKARELA': 'Simpanan Sukarela',
                'SA_BERJANGKA': 'Simpanan Berjangka',
                'SA_HARI_RAYA': 'Simpanan Hari Raya'
            },
            
            // Calculation Methods
            calculationMethods: {
                'Flat': 'Flat',
                'Effective': 'Efektif',
                'Anuitas': 'Anuitas'
            },
            
            // Gender
            gender: {
                'L': 'Laki-laki',
                'P': 'Perempuan',
                'Male': 'Laki-laki',
                'Female': 'Perempuan'
            },
            
            // Marital Status
            maritalStatus: {
                'Single': 'Belum Menikah',
                'Married': 'Menikah',
                'Divorced': 'Cerai',
                'Widowed': 'Duda/Janda'
            },
            
            // Common Actions
            actions: {
                'create': 'Buat',
                'edit': 'Edit',
                'delete': 'Hapus',
                'view': 'Lihat',
                'save': 'Simpan',
                'cancel': 'Batal',
                'submit': 'Kirim',
                'approve': 'Setujui',
                'reject': 'Tolak',
                'search': 'Cari',
                'filter': 'Filter',
                'export': 'Export',
                'import': 'Import',
                'print': 'Cetak',
                'download': 'Unduh',
                'upload': 'Unggah'
            },
            
            // Messages
            messages: {
                'success': 'Berhasil',
                'error': 'Error',
                'warning': 'Peringatan',
                'info': 'Informasi',
                'loading': 'Memuat...',
                'saving': 'Menyimpan...',
                'deleting': 'Menghapus...',
                'processing': 'Memproses...',
                'confirm': 'Konfirmasi',
                'are_you_sure': 'Apakah Anda yakin?',
                'no_data_found': 'Tidak ada data ditemukan',
                'operation_successful': 'Operasi berhasil',
                'operation_failed': 'Operasi gagal'
            }
        };
    }
    
    /**
     * Translate enum value to Indonesian
     */
    translate(category, value) {
        if (this.translations[category] && this.translations[category][value]) {
            return this.translations[category][value];
        }
        return value; // Return original if no translation found
    }
    
    /**
     * Translate user role
     */
    translateUserRole(role) {
        return this.translate('userRoles', role);
    }
    
    /**
     * Translate loan status
     */
    translateLoanStatus(status) {
        return this.translate('loanStatus', status);
    }
    
    /**
     * Translate payment type
     */
    translatePaymentType(type) {
        return this.translate('paymentTypes', type);
    }
    
    /**
     * Translate payment status
     */
    translatePaymentStatus(status) {
        return this.translate('paymentStatus', status);
    }
    
    /**
     * Translate collection status
     */
    translateCollectionStatus(status) {
        return this.translate('collectionStatus', status);
    }
    
    /**
     * Translate member status
     */
    translateMemberStatus(status) {
        return this.translate('memberStatus', status);
    }
    
    /**
     * Translate gender
     */
    translateGender(gender) {
        return this.translate('gender', gender);
    }
    
    /**
     * Translate marital status
     */
    translateMaritalStatus(status) {
        return this.translate('maritalStatus', status);
    }
    
    /**
     * Format currency with Indonesian locale
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }
    
    /**
     * Format date with Indonesian locale
     */
    formatDate(date, options = {}) {
        const defaultOptions = {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        };
        
        return new Date(date).toLocaleDateString('id-ID', { ...defaultOptions, ...options });
    }
    
    /**
     * Format date time with Indonesian locale
     */
    formatDateTime(date, options = {}) {
        const defaultOptions = {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        
        return new Date(date).toLocaleString('id-ID', { ...defaultOptions, ...options });
    }
    
    /**
     * Format number with Indonesian locale
     */
    formatNumber(number) {
        return number.toLocaleString('id-ID');
    }
    
    /**
     * Get status badge color class
     */
    getStatusBadgeClass(status, category = 'default') {
        const colorMaps = {
            loanStatus: {
                'Applied': 'warning',
                'Approved': 'success',
                'Rejected': 'danger',
                'Disbursed': 'info',
                'Active': 'primary',
                'Late': 'warning',
                'Default': 'danger',
                'Restructured': 'secondary',
                'Paid Off': 'success'
            },
            paymentStatus: {
                'pending': 'warning',
                'paid': 'success',
                'failed': 'danger',
                'expired': 'secondary',
                'cancelled': 'dark'
            },
            collectionStatus: {
                'scheduled': 'info',
                'in_progress': 'primary',
                'collected': 'success',
                'missed': 'danger',
                'postponed': 'warning',
                'cancelled': 'secondary'
            },
            memberStatus: {
                'Active': 'success',
                'Inactive': 'secondary',
                'Pending': 'warning',
                'Suspended': 'danger'
            }
        };
        
        const colorMap = colorMaps[category] || colorMaps['default'];
        return colorMap[status] || 'secondary';
    }
    
    /**
     * Create status badge HTML
     */
    createStatusBadge(status, category = 'default') {
        const badgeClass = this.getStatusBadgeClass(status, category);
        const translatedStatus = this.translate(category.replace('Status', 'Status'), status);
        
        return `<span class="badge bg-${badgeClass}">${translatedStatus}</span>`;
    }
    
    /**
     * Translate and format data array
     */
    translateDataArray(data, mappings) {
        return data.map(item => {
            const translatedItem = { ...item };
            
            Object.keys(mappings).forEach(field => {
                const mapping = mappings[field];
                if (item[field] && mapping.category) {
                    translatedItem[field + '_translated'] = this.translate(mapping.category, item[field]);
                    translatedItem[field + '_badge'] = this.createStatusBadge(item[field], mapping.category);
                }
            });
            
            return translatedItem;
        });
    }
}

// Global instance
window.indonesianTranslator = new IndonesianTranslator();

// Helper functions for easy access
window.translateRole = (role) => window.indonesianTranslator.translateUserRole(role);
window.translateStatus = (status, category) => window.indonesianTranslator.translate(category + 'Status', status);
window.formatCurrency = (amount) => window.indonesianTranslator.formatCurrency(amount);
window.formatDate = (date, options) => window.indonesianTranslator.formatDate(date, options);
window.formatDateTime = (date, options) => window.indonesianTranslator.formatDateTime(date, options);

// Auto-translate function for common elements
function autoTranslateIndonesian() {
    // Translate status badges
    document.querySelectorAll('[data-translate-status]').forEach(element => {
        const status = element.dataset.translateStatus;
        const category = element.dataset.translateCategory || 'loan';
        element.innerHTML = window.indonesianTranslator.createStatusBadge(status, category);
    });
    
    // Translate currency values
    document.querySelectorAll('[data-format-currency]').forEach(element => {
        const amount = parseFloat(element.dataset.formatCurrency);
        element.textContent = window.indonesianTranslator.formatCurrency(amount);
    });
    
    // Translate dates
    document.querySelectorAll('[data-format-date]').forEach(element => {
        const date = element.dataset.formatDate;
        element.textContent = window.indonesianTranslator.formatDate(date);
    });
    
    // Translate date times
    document.querySelectorAll('[data-format-datetime]').forEach(element => {
        const datetime = element.dataset.formatDatetime;
        element.textContent = window.indonesianTranslator.formatDateTime(datetime);
    });
}

// Auto-run when DOM is ready
document.addEventListener('DOMContentLoaded', autoTranslateIndonesian);

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = IndonesianTranslator;
}

})();