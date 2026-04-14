/**
 * Indonesian Format Helper - JavaScript Only
 * Helper khusus untuk format Bahasa Indonesia untuk frontend
 */

class IndonesianFormatHelper {
    
    // Format angka dengan style Indonesia
    static formatNumber(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }
    
    // Format uang dengan style Indonesia
    static formatMoney(amount, withSymbol = true) {
        const formatted = new Intl.NumberFormat('id-ID').format(amount);
        return withSymbol ? 'Rp ' + formatted : formatted;
    }
    
    // Format tanggal dengan style Indonesia
    static formatDate(date, withDay = false, withTime = false) {
        const options = {
            weekday: withDay ? 'long' : undefined,
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: withTime ? '2-digit' : undefined,
            minute: withTime ? '2-digit' : undefined,
            timeZone: 'Asia/Jakarta'
        };
        
        return new Date(date).toLocaleDateString('id-ID', options);
    }
    
    // Format tanggal singkat
    static formatDateShort(date) {
        return new Date(date).toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
    
    // Format persentase dengan style Indonesia
    static formatPercentage(value, decimals = 2) {
        return value.toLocaleString('id-ID', {
            style: 'percent',
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }
    
    // Format telepon Indonesia
    static formatPhone(phone) {
        // Remove all non-numeric characters
        phone = phone.replace(/[^0-9]/g, '');
        
        // Format based on length
        if (phone.length <= 9) {
            return phone;
        } else if (phone.length == 10) {
            return phone.slice(0, 3) + '-' + phone.slice(3);
        } else if (phone.length == 11) {
            return phone.slice(0, 4) + '-' + phone.slice(4);
        } else if (phone.length == 12) {
            return phone.slice(0, 4) + '-' + phone.slice(4, 8) + '-' + phone.slice(8);
        }
        
        return phone;
    }
    
    // Format NIK Indonesia
    static formatNIK(nik) {
        // Remove spaces and dashes
        nik = nik.replace(/[\s\-]/g, '');
        
        // Format: XX.XX.XX.XXXX.XXXX.XXXX
        if (nik.length == 16) {
            return nik.slice(0, 2) + '.' + 
                   nik.slice(2, 4) + '.' + 
                   nik.slice(4, 6) + '.' + 
                   nik.slice(6, 10) + '.' + 
                   nik.slice(10, 14) + '.' + 
                   nik.slice(14, 18);
        }
        
        return nik;
    }
    
    // Terbilang angka dalam Bahasa Indonesia (sederhana)
    static toWords(number) {
        // Implementasi sederhana - bisa dikembangkan
        const units = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        // ... implementasi lengkap
        
        return number.toString(); // Placeholder
    }
    
    // Terbilang uang dalam Bahasa Indonesia
    static moneyToWords(amount) {
        // Implementasi sederhana
        return 'Rp ' + this.formatMoney(amount) + ' rupiah'; // Placeholder
    }
}

// Global helper untuk kemudahan penggunaan
window.formatIndonesian = IndonesianFormatHelper.formatNumber;
window.formatUang = IndonesianFormatHelper.formatMoney;
window.formatTanggal = IndonesianFormatHelper.formatDate;
window.formatTanggalShort = IndonesianFormatHelper.formatDateShort;
window.formatPersentase = IndonesianFormatHelper.formatPercentage;
window.formatTelepon = IndonesianFormatHelper.formatPhone;
window.formatNIK = IndonesianFormatHelper.formatNIK;
