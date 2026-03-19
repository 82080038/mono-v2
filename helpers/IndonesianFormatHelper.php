<?php
/**
 * Indonesian Format Helper
 * Helper khusus untuk format Bahasa Indonesia
 */

class IndonesianFormatHelper {
    
    /**
     * Format angka dengan style Indonesia
     * @param int|float $number
     * @return string
     */
    public static function formatNumber($number) {
        return number_format($number, 0, ',', '.');
    }
    
    /**
     * Format uang dengan style Indonesia
     * @param int|float $amount
     * @param bool $withSymbol
     * @return string
     */
    public static function formatMoney($amount, $withSymbol = true) {
        $formatted = number_format($amount, 0, ',', '.');
        return $withSymbol ? 'Rp ' . $formatted : $formatted;
    }
    
    /**
     * Format tanggal dengan style Indonesia
     * @param string $date
     * @param bool $withDay
     * @param bool $withTime
     * @return string
     */
    public static function formatDate($date, $withDay = false, $withTime = false) {
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        $dayName = $days[date('w', $timestamp)];
        $day = date('d', $timestamp);
        $month = $months[date('n', $timestamp)];
        $year = date('Y', $timestamp);
        $time = date('H:i', $timestamp);
        
        $result = $withDay ? "$dayName, " : "";
        $result .= "$day $month $year";
        
        if ($withTime) {
            $result .= " pukul $time WIB";
        }
        
        return $result;
    }
    
    /**
     * Format tanggal singkat
     * @param string $date
     * @return string
     */
    public static function formatDateShort($date) {
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        return date('d/m/Y', $timestamp);
    }
    
    /**
     * Format persentase dengan style Indonesia
     * @param float $value
     * @param int $decimals
     * @return string
     */
    public static function formatPercentage($value, $decimals = 2) {
        return number_format($value, $decimals, ',', '.') . '%';
    }
    
    /**
     * Format telepon Indonesia
     * @param string $phone
     * @return string
     */
    public static function formatPhone($phone) {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Format based on length
        if (strlen($phone) <= 9) {
            return $phone;
        } elseif (strlen($phone) == 10) {
            return substr($phone, 0, 3) . '-' . substr($phone, 3);
        } elseif (strlen($phone) == 11) {
            return substr($phone, 0, 4) . '-' . substr($phone, 4);
        } elseif (strlen($phone) == 12) {
            return substr($phone, 0, 4) . '-' . substr($phone, 4, 4) . '-' . substr($phone, 8);
        } else {
            return $phone;
        }
    }
    
    /**
     * Format NIK Indonesia
     * @param string $nik
     * @return string
     */
    public static function formatNIK($nik) {
        // Remove spaces and dashes
        $nik = preg_replace('/[\s\-]/', '', $nik);
        
        // Format: XX.XX.XX.XXXX.XXXX.XXXX
        if (strlen($nik) == 16) {
            return substr($nik, 0, 2) . '.' . 
                   substr($nik, 2, 2) . '.' . 
                   substr($nik, 4, 2) . '.' . 
                   substr($nik, 6, 4) . '.' . 
                   substr($nik, 10, 4) . '.' . 
                   substr($nik, 14, 4);
        }
        
        return $nik;
    }
    
    /**
     * Terbilang angka dalam Bahasa Indonesia
     * @param int $number
     * @return string
     */
    public static function toWords($number) {
        $number = abs($number);
        $words = "";
        
        $units = [
            "", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"
        ];
        
        $levels = ["", "ribu", "juta", "miliar", "triliun"];
        
        if ($number < 12) {
            $words = $units[$number];
        } elseif ($number < 20) {
            $words = $units[$number - 10] . " belas";
        } elseif ($number < 100) {
            $words = $units[floor($number / 10)] . " puluh " . $units[$number % 10];
        } elseif ($number < 200) {
            $words = "seratus " . self::toWords($number - 100);
        } elseif ($number < 1000) {
            $words = $units[floor($number / 100)] . " ratus " . self::toWords($number % 100);
        } else {
            $level = 0;
            while ($number >= 1000) {
                $number = floor($number / 1000);
                $level++;
            }
            $words = self::toWords($number) . " " . $levels[$level];
            if ($number % 1000 != 0) {
                $words .= " " . self::toWords($number % 1000);
            }
        }
        
        return $words;
    }
    
    /**
     * Terbilang uang dalam Bahasa Indonesia
     * @param int|float $amount
     * @return string
     */
    public static function moneyToWords($amount) {
        $words = self::toWords($amount);
        return ucwords($words) . " Rupiah";
    }
}

// JavaScript Version untuk frontend
?>
<script>
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
</script>
