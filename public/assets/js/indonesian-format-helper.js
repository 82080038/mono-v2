/**
 * Indonesian Format Helper - Frontend JavaScript
 * 
 * Helper class untuk formatting data khas Indonesia di frontend:
 * - Format mata uang Rupiah
 * - Format tanggal dan waktu bahasa Indonesia
 * - Validasi dan format NIK
 * - Format nomor telepon Indonesia
 * - Format alamat lengkap
 * - Konversi angka terbilang
 * - Validasi form Indonesia
 * 
 * @author KSP Lam Gabe Jaya Development Team
 * @version 1.0.0
 */

class IndonesianFormatHelper {
    
    /**
     * Konstanta untuk format Indonesia
     */
    static CURRENCY_SYMBOL = 'Rp';
    static DECIMAL_SEPARATOR = ',';
    static THOUSANDS_SEPARATOR = '.';
    static COUNTRY_CODE = '+62';
    
    /**
     * Array nama hari dalam bahasa Indonesia
     */
    static DAYS = [
        'Sunday'    : 'Minggu',
        'Monday'    : 'Senin',
        'Tuesday'   : 'Selasa',
        'Wednesday' : 'Rabu',
        'Thursday'  : 'Kamis',
        'Friday'    : 'Jumat',
        'Saturday'  : 'Sabtu'
    ];
    
    /**
     * Array nama bulan dalam bahasa Indonesia
     */
    static MONTHS = [
        'January'   : 'Januari',
        'February'  : 'Februari',
        'March'     : 'Maret',
        'April'     : 'April',
        'May'       : 'Mei',
        'June'      : 'Juni',
        'July'      : 'Juli',
        'August'    : 'Agustus',
        'September' : 'September',
        'October'   : 'Oktober',
        'November'  : 'November',
        'December'  : 'Desember'
    ];
    
    /**
     * Format mata uang ke Rupiah
     * 
     * @param {number} amount - Jumlah uang
     * @param {boolean} withSymbol - Tampilkan simbol Rp
     * @param {number} decimals - Jumlah desimal
     * @return {string}
     */
    static formatRupiah(amount, withSymbol = true, decimals = 2) {
        if (isNaN(amount) || amount === null || amount === '') {
            amount = 0;
        }
        
        const num = parseFloat(amount);
        
        if (decimals === 0) {
            const formatted = num.toLocaleString('id-ID');
            return withSymbol ? `${this.CURRENCY_SYMBOL} ${formatted}` : formatted;
        } else {
            const formatted = num.toLocaleString('id-ID', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
            return withSymbol ? `${this.CURRENCY_SYMBOL} ${formatted}` : formatted;
        }
    }
    
    /**
     * Format mata uang Rupiah tanpa desimal
     * 
     * @param {number} amount - Jumlah uang
     * @param {boolean} withSymbol - Tampilkan simbol Rp
     * @return {string}
     */
    static formatRupiahSimple(amount, withSymbol = true) {
        return this.formatRupiah(amount, withSymbol, 0);
    }
    
    /**
     * Parse Rupiah string ke numeric
     * 
     * @param {string} rupiahString - String Rupiah
     * @return {number}
     */
    static parseRupiah(rupiahString) {
        if (!rupiahString || typeof rupiahString !== 'string') {
            return 0;
        }
        
        // Hapus simbol Rp dan spasi
        let cleaned = rupiahString.replace(new RegExp(this.CURRENCY_SYMBOL.replace('.', '\\.') + '\\s*', 'g'), '');
        
        // Ganti separator Indonesia ke standar
        cleaned = cleaned.replace(new RegExp('\\' + this.THOUSANDS_SEPARATOR, 'g'), '');
        cleaned = cleaned.replace(new RegExp('\\' + this.DECIMAL_SEPARATOR, 'g'), '.');
        
        const parsed = parseFloat(cleaned);
        return isNaN(parsed) ? 0 : parsed;
    }
    
    /**
     * Format tanggal ke bahasa Indonesia
     * 
     * @param {string|Date} date - Tanggal
     * @param {boolean} withDay - Tampilkan nama hari
     * @param {boolean} longFormat - Format panjang
     * @return {string}
     */
    static formatDate(date, withDay = false, longFormat = true) {
        if (!date) {
            return '';
        }
        
        try {
            const dateObj = new Date(date);
            if (isNaN(dateObj.getTime())) {
                return date;
            }
            
            let result = '';
            
            // Add day name if requested
            if (withDay) {
                const dayName = this.DAYS[dateObj.toLocaleDateString('en-US', { weekday: 'long' })];
                result += dayName + ', ';
            }
            
            // Format date
            const day = dateObj.getDate();
            const monthKey = dateObj.toLocaleDateString('en-US', { month: 'long' });
            const month = longFormat ? 
                this.MONTHS[monthKey] : 
                this.MONTHS[monthKey].substring(0, 3);
            const year = dateObj.getFullYear();
            
            result += `${day} ${month} ${year}`;
            return result;
            
        } catch (error) {
            console.error('Date formatting error:', error);
            return date;
        }
    }
    
    /**
     * Format tanggal dan waktu lengkap
     * 
     * @param {string|Date} datetime - Tanggal dan waktu
     * @param {boolean} withDay - Tampilkan nama hari
     * @param {boolean} longFormat - Format panjang
     * @return {string}
     */
    static formatDateTime(datetime, withDay = false, longFormat = true) {
        if (!datetime) {
            return '';
        }
        
        try {
            const dateObj = new Date(datetime);
            if (isNaN(dateObj.getTime())) {
                return datetime;
            }
            
            const datePart = this.formatDate(datetime, withDay, longFormat);
            const timePart = dateObj.toLocaleTimeString('id-ID', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            return `${datePart} pukul ${timePart}`;
            
        } catch (error) {
            console.error('DateTime formatting error:', error);
            return datetime;
        }
    }
    
    /**
     * Validasi format NIK Indonesia
     * 
     * @param {string} nik - Nomor NIK
     * @return {boolean}
     */
    static validateNIK(nik) {
        if (!nik || typeof nik !== 'string') {
            return false;
        }
        
        // Basic validation: 16 digits
        if (!/^\d{16}$/.test(nik)) {
            return false;
        }
        
        // Extract components
        const provinceCode = nik.substring(0, 2);
        const regencyCode = nik.substring(2, 2);
        const districtCode = nik.substring(4, 2);
        const birthDate = nik.substring(6, 6);
        const sequence = nik.substring(12, 4);
        
        // Validate province code
        const provinceValid = /^(1[1-9]|21|[37][1-6]|5[1-3]|6[1-5]|[89][12])/.test(provinceCode);
        
        // Validate birth date
        const day = parseInt(birthDate.substring(0, 2));
        const month = parseInt(birthDate.substring(2, 2));
        const year = parseInt(birthDate.substring(4, 2));
        
        // Check month validity
        if (month < 1 || month > 12) {
            return false;
        }
        
        // Check day validity (considering female +40 adjustment)
        let actualDay = day;
        if (day > 71) { // Female (40 + max day 31)
            actualDay = day - 40;
        }
        
        if (actualDay < 1 || actualDay > 31) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Format NIK dengan pemisah
     * 
     * @param {string} nik - Nomor NIK
     * @param {string} separator - Pemisah
     * @return {string}
     */
    static formatNIK(nik, separator = ' ') {
        if (!this.validateNIK(nik)) {
            return nik;
        }
        
        const province = nik.substring(0, 2);
        const regency = nik.substring(2, 2);
        const district = nik.substring(4, 2);
        const birthdate = nik.substring(6, 6);
        const sequence = nik.substring(12, 4);
        
        return `${province}${separator}${regency}${separator}${district}${separator}${birthdate}${separator}${sequence}`;
    }
    
    /**
     * Extract informasi dari NIK
     * 
     * @param {string} nik - Nomor NIK
     * @return {object}
     */
    static extractNIKInfo(nik) {
        if (!this.validateNIK(nik)) {
            return {};
        }
        
        const day = parseInt(nik.substring(6, 2));
        const month = parseInt(nik.substring(8, 2));
        const year = parseInt(nik.substring(10, 2));
        
        // Determine gender and actual birth day
        const isFemale = day > 40;
        const actualDay = isFemale ? day - 40 : day;
        
        // Determine century
        const currentYear = new Date().getFullYear();
        const currentYear2Digit = currentYear % 100;
        const century = (year <= currentYear2Digit) ? 2000 : 1900;
        const fullYear = century + year;
        
        return {
            province_code: nik.substring(0, 2),
            regency_code: nik.substring(2, 2),
            district_code: nik.substring(4, 2),
            birth_day: actualDay,
            birth_month: month,
            birth_year: fullYear,
            gender: isFemale ? 'Perempuan' : 'Laki-laki',
            sequence: nik.substring(12, 4)
        };
    }
    
    /**
     * Format nomor telepon Indonesia
     * 
     * @param {string} phone - Nomor telepon
     * @param {string} format - Format: 'international', 'national', 'pretty'
     * @return {string}
     */
    static formatPhoneNumber(phone, format = 'national') {
        if (!phone || typeof phone !== 'string') {
            return '';
        }
        
        // Clean phone number
        let cleaned = phone.replace(/[^\d]/g, '');
        
        // Remove leading 62 or 0
        if (cleaned.startsWith('62')) {
            cleaned = cleaned.substring(2);
        } else if (cleaned.startsWith('0')) {
            cleaned = cleaned.substring(1);
        }
        
        // Validate length
        if (cleaned.length < 9 || cleaned.length > 13) {
            return phone;
        }
        
        switch (format) {
            case 'international':
                return `${this.COUNTRY_CODE} ${cleaned}`;
                
            case 'national':
                return `0${cleaned}`;
                
            case 'pretty':
                // Format with spaces for readability
                if (cleaned.length <= 10) {
                    // Mobile: 08xx-xxxx-xxxx
                    return `0${cleaned.substring(0, 3)}-${cleaned.substring(3, 7)}-${cleaned.substring(7)}`;
                } else {
                    // Landline: 0xxx-xxxx-xxxx
                    return `0${cleaned.substring(0, 3)}-${cleaned.substring(3, 7)}-${cleaned.substring(7)}`;
                }
                
            default:
                return `0${cleaned}`;
        }
    }
    
    /**
     * Validasi nomor telepon Indonesia
     * 
     * @param {string} phone - Nomor telepon
     * @return {boolean}
     */
    static validatePhoneNumber(phone) {
        if (!phone || typeof phone !== 'string') {
            return false;
        }
        
        const cleaned = phone.replace(/[^\d]/g, '');
        
        // Check if starts with 62 or 0, and valid length
        let processed = cleaned;
        if (processed.startsWith('62')) {
            processed = processed.substring(2);
        } else if (processed.startsWith('0')) {
            processed = processed.substring(1);
        }
        
        return processed.length >= 9 && processed.length <= 13;
    }
    
    /**
     * Format alamat lengkap Indonesia
     * 
     * @param {object} addressData - Data alamat
     * @return {string}
     */
    static formatAddress(addressData) {
        if (!addressData || typeof addressData !== 'object') {
            return '';
        }
        
        const parts = [];
        
        // Street address
        if (addressData.street) {
            parts.push(addressData.street.trim());
        }
        
        // RT/RW
        if (addressData.rt) {
            parts.push(`RT ${addressData.rt.trim()}`);
        }
        
        if (addressData.rw) {
            parts.push(`RW ${addressData.rw.trim()}`);
        }
        
        // Kelurahan/Desa
        if (addressData.village) {
            parts.push(`Kel. ${addressData.village.trim()}`);
        }
        
        // Kecamatan
        if (addressData.district) {
            parts.push(`Kec. ${addressData.district.trim()}`);
        }
        
        // Kota/Kabupaten
        if (addressData.city) {
            parts.push(addressData.city.trim());
        }
        
        // Provinsi
        if (addressData.province) {
            parts.push(addressData.province.trim());
        }
        
        // Kode Pos
        if (addressData.postal_code) {
            parts.push(addressData.postal_code.trim());
        }
        
        return parts.join(', ');
    }
    
    /**
     * Validasi kode pos Indonesia (5 digit)
     * 
     * @param {string} postalCode - Kode pos
     * @return {boolean}
     */
    static validatePostalCode(postalCode) {
        return /^\d{5}$/.test(postalCode);
    }
    
    /**
     * Format nama lengkap (title case untuk bahasa Indonesia)
     * 
     * @param {string} name - Nama lengkap
     * @return {string}
     */
    static formatName(name) {
        if (!name || typeof name !== 'string') {
            return '';
        }
        
        // Convert to lowercase then capitalize each word
        let formatted = name.toLowerCase().trim();
        
        // Handle special Indonesian name prefixes
        const prefixes = ['muhammad', 'mohammad', 'm.', 'dr.', 'ir.', 'h.'];
        for (const prefix of prefixes) {
            if (formatted.startsWith(prefix)) {
                formatted = formatted.replace(prefix, prefix.charAt(0).toUpperCase() + prefix.substring(1));
                break;
            }
        }
        
        // Capitalize each word
        const words = formatted.split(' ');
        const formattedWords = words.map(word => {
            // Handle common Indonesian name patterns
            if (['bin', 'binti', 'putra', 'putri'].includes(word.toLowerCase())) {
                return word.toLowerCase();
            } else {
                return word.charAt(0).toUpperCase() + word.substring(1);
            }
        });
        
        return formattedWords.join(' ');
    }
    
    /**
     * Konversi angka ke terbilang bahasa Indonesia
     * 
     * @param {number} number - Angka
     * @param {boolean} withCurrency - Tambahkan "Rupiah"
     * @return {string}
     */
    static numberToWords(number, withCurrency = false) {
        if (isNaN(number) || number === null || number === '') {
            return '';
        }
        
        if (number === 0) {
            const result = 'nol';
            return withCurrency ? `${result} Rupiah` : result;
        }
        
        const result = this.convertNumberToWords(Math.floor(number));
        return withCurrency ? `${result} Rupiah` : result;
    }
    
    /**
     * Helper function untuk konversi angka ke kata
     * 
     * @param {number} number
     * @return {string}
     */
    static convertNumberToWords(number) {
        const units = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'
        ];
        
        if (number < 12) {
            return units[number];
        } else if (number < 20) {
            return units[number - 10] + ' belas';
        } else if (number < 100) {
            const tens = Math.floor(number / 10);
            const remainder = number % 10;
            return (tens === 1 ? 'sepuluh' : units[tens] + ' puluh') + 
                   (remainder > 0 ? ' ' + units[remainder] : '');
        } else if (number < 200) {
            return 'seratus ' + this.convertNumberToWords(number - 100);
        } else if (number < 1000) {
            const hundreds = Math.floor(number / 100);
            const remainder = number % 100;
            return units[hundreds] + ' ratus' + 
                   (remainder > 0 ? ' ' + this.convertNumberToWords(remainder) : '');
        } else if (number < 2000) {
            return 'seribu ' + this.convertNumberToWords(number - 1000);
        } else if (number < 1000000) {
            const thousands = Math.floor(number / 1000);
            const remainder = number % 1000;
            return this.convertNumberToWords(thousands) + ' ribu' + 
                   (remainder > 0 ? ' ' + this.convertNumberToWords(remainder) : '');
        } else if (number < 1000000000) {
            const millions = Math.floor(number / 1000000);
            const remainder = number % 1000000;
            return this.convertNumberToWords(millions) + ' juta' + 
                   (remainder > 0 ? ' ' + this.convertNumberToWords(remainder) : '');
        } else if (number < 1000000000000) {
            const billions = Math.floor(number / 1000000000);
            const remainder = number % 1000000000;
            return this.convertNumberToWords(billions) + ' miliar' + 
                   (remainder > 0 ? ' ' + this.convertNumberToWords(remainder) : '');
        } else {
            return 'angka terlalu besar';
        }
    }
    
    /**
     * Format persentase Indonesia
     * 
     * @param {number} number - Angka
     * @param {number} decimals - Jumlah desimal
     * @return {string}
     */
    static formatPercentage(number, decimals = 2) {
        if (isNaN(number) || number === null || number === '') {
            number = 0;
        }
        
        const num = parseFloat(number);
        const formatted = num.toLocaleString('id-ID', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
        
        return `${formatted}%`;
    }
    
    /**
     * Format ukuran file (bytes ke KB, MB, GB)
     * 
     * @param {number} bytes - Ukuran dalam bytes
     * @param {number} decimals - Jumlah desimal
     * @return {string}
     */
    static formatFileSize(bytes, decimals = 2) {
        if (isNaN(bytes) || bytes === null || bytes === '') {
            bytes = 0;
        }
        
        const num = parseFloat(bytes);
        
        if (num >= 1073741824) {
            const formatted = (num / 1073741824).toLocaleString('id-ID', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
            return `${formatted} GB`;
        } else if (num >= 1048576) {
            const formatted = (num / 1048576).toLocaleString('id-ID', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
            return `${formatted} MB`;
        } else if (num >= 1024) {
            const formatted = (num / 1024).toLocaleString('id-ID', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
            return `${formatted} KB`;
        } else {
            return `${num} bytes`;
        }
    }
    
    /**
     * Validasi data lengkap pengguna Indonesia
     * 
     * @param {object} userData - Data pengguna
     * @return {object} Validation result
     */
    static validateUserData(userData) {
        const errors = {};
        
        // Validate NIK
        if (userData.nik && !this.validateNIK(userData.nik)) {
            errors.nik = 'Format NIK tidak valid';
        }
        
        // Validate phone
        if (userData.phone && !this.validatePhoneNumber(userData.phone)) {
            errors.phone = 'Format nomor telepon tidak valid';
        }
        
        // Validate postal code
        if (userData.postal_code && !this.validatePostalCode(userData.postal_code)) {
            errors.postal_code = 'Format kode pos tidak valid';
        }
        
        // Validate email (basic)
        if (userData.email && !this.validateEmail(userData.email)) {
            errors.email = 'Format email tidak valid';
        }
        
        return {
            valid: Object.keys(errors).length === 0,
            errors: errors
        };
    }
    
    /**
     * Validasi email (basic)
     * 
     * @param {string} email - Email address
     * @return {boolean}
     */
    static validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    /**
     * Format data tabel Indonesia
     * 
     * @param {object} data - Data object
     * @return {object} Formatted data
     */
    static formatTableData(data) {
        const formatted = {};
        
        for (const [key, value] of Object.entries(data)) {
            switch (key) {
                case 'amount':
                case 'price':
                case 'total':
                case 'balance':
                    formatted[key] = this.formatRupiah(value);
                    break;
                    
                case 'date':
                case 'created_at':
                case 'updated_at':
                    formatted[key] = this.formatDate(value);
                    break;
                    
                case 'datetime':
                case 'transaction_date':
                    formatted[key] = this.formatDateTime(value);
                    break;
                    
                case 'phone':
                case 'mobile':
                case 'telephone':
                    formatted[key] = this.formatPhoneNumber(value, 'pretty');
                    break;
                    
                case 'nik':
                    formatted[key] = this.formatNIK(value);
                    break;
                    
                default:
                    formatted[key] = value;
            }
        }
        
        return formatted;
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Auto-format rupiah inputs
    const rupiahInputs = document.querySelectorAll('input[data-format="rupiah"]');
    rupiahInputs.forEach(input => {
        input.addEventListener('input', function() {
            const value = IndonesianFormatHelper.parseRupiah(this.value);
            this.value = IndonesianFormatHelper.formatRupiah(value, true, 0);
        });
        
        input.addEventListener('blur', function() {
            const value = IndonesianFormatHelper.parseRupiah(this.value);
            this.value = IndonesianFormatHelper.formatRupiah(value, true, 0);
        });
    });
    
    // Auto-format phone inputs
    const phoneInputs = document.querySelectorAll('input[data-format="phone"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = IndonesianFormatHelper.formatPhoneNumber(this.value, 'national');
        });
    });
    
    // Auto-format NIK inputs
    const nikInputs = document.querySelectorAll('input[data-format="nik"]');
    nikInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Only allow numbers
            this.value = this.value.replace(/\D/g, '');
            
            // Limit to 16 digits
            if (this.value.length > 16) {
                this.value = this.value.substring(0, 16);
            }
        });
    });
    
    // Auto-format postal code inputs
    const postalInputs = document.querySelectorAll('input[data-format="postal"]');
    postalInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Only allow numbers
            this.value = this.value.replace(/\D/g, '');
            
            // Limit to 5 digits
            if (this.value.length > 5) {
                this.value = this.value.substring(0, 5);
            }
        });
    });
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = IndonesianFormatHelper;
}

// Global assignment for browser
if (typeof window !== 'undefined') {
    window.IndonesianFormatHelper = IndonesianFormatHelper;
    
    // Shortcut functions
    window.formatRupiah = (amount, withSymbol, decimals) => IndonesianFormatHelper.formatRupiah(amount, withSymbol, decimals);
    window.formatTanggal = (date, withDay, longFormat) => IndonesianFormatHelper.formatDate(date, withDay, longFormat);
    window.formatTelepon = (phone, format) => IndonesianFormatHelper.formatPhoneNumber(phone, format);
    window.validateNIK = (nik) => IndonesianFormatHelper.validateNIK(nik);
    window.angkaTerbilang = (number, withCurrency) => IndonesianFormatHelper.numberToWords(number, withCurrency);
}
