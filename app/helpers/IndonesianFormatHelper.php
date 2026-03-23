<?php

/**
 * Indonesian Format Helper
 * 
 * Helper class untuk formatting data khas Indonesia:
 * - Format mata uang Rupiah
 * - Format tanggal dan waktu bahasa Indonesia
 * - Validasi dan format NIK (Nomor Induk Kependudukan)
 * - Format nomor telepon Indonesia
 * - Format alamat lengkap
 * - Validasi kode pos
 * - Format nama lengkap
 * - Konversi angka terbilang bahasa Indonesia
 * 
 * @author KSP Lam Gabe Jaya Development Team
 * @version 1.0.0
 */

class IndonesianFormatHelper
{
    /**
     * Konstanta untuk format Indonesia
     */
    const CURRENCY_SYMBOL = 'Rp';
    const DECIMAL_SEPARATOR = ',';
    const THOUSANDS_SEPARATOR = '.';
    const COUNTRY_CODE = '+62';
    
    /**
     * Array nama hari dalam bahasa Indonesia
     */
    private static $days = [
        'Sunday'    => 'Minggu',
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu'
    ];
    
    /**
     * Array nama bulan dalam bahasa Indonesia
     */
    private static $months = [
        'January'   => 'Januari',
        'February'  => 'Februari',
        'March'     => 'Maret',
        'April'     => 'April',
        'May'       => 'Mei',
        'June'      => 'Juni',
        'July'      => 'Juli',
        'August'    => 'Agustus',
        'September' => 'September',
        'October'   => 'Oktober',
        'November'  => 'November',
        'December'  => 'Desember'
    ];
    
    /**
     * Array nama bulan singkatan
     */
    private static $monthsShort = [
        'Jan' => 'Jan',
        'Feb' => 'Feb',
        'Mar' => 'Mar',
        'Apr' => 'Apr',
        'May' => 'Mei',
        'Jun' => 'Jun',
        'Jul' => 'Jul',
        'Aug' => 'Agu',
        'Sep' => 'Sep',
        'Oct' => 'Okt',
        'Nov' => 'Nov',
        'Dec' => 'Des'
    ];
    
    /**
     * Format mata uang ke Rupiah
     * 
     * @param mixed $amount Jumlah uang
     * @param bool $withSymbol Tampilkan simbol Rp
     * @param int $decimals Jumlah desimal
     * @return string
     */
    public static function formatRupiah($amount, $withSymbol = true, $decimals = 2)
    {
        $amount = is_numeric($amount) ? $amount : 0;
        
        if ($decimals === 0) {
            $formatted = number_format($amount, 0, '', self::THOUSANDS_SEPARATOR);
        } else {
            $formatted = number_format($amount, $decimals, self::DECIMAL_SEPARATOR, self::THOUSANDS_SEPARATOR);
        }
        
        return $withSymbol ? self::CURRENCY_SYMBOL . ' ' . $formatted : $formatted;
    }
    
    /**
     * Format mata uang Rupiah tanpa desimal
     * 
     * @param mixed $amount Jumlah uang
     * @param bool $withSymbol Tampilkan simbol Rp
     * @return string
     */
    public static function formatRupiahSimple($amount, $withSymbol = true)
    {
        return self::formatRupiah($amount, $withSymbol, 0);
    }
    
    /**
     * Parse Rupiah string ke numeric
     * 
     * @param string $rupiahString String Rupiah
     * @return float
     */
    public static function parseRupiah($rupiahString)
    {
        // Hapus simbol Rp dan spasi
        $cleaned = str_replace([self::CURRENCY_SYMBOL, ' '], '', $rupiahString);
        
        // Ganti separator Indonesia ke standar
        $cleaned = str_replace([self::THOUSANDS_SEPARATOR, self::DECIMAL_SEPARATOR], ['', '.'], $cleaned);
        
        return is_numeric($cleaned) ? (float) $cleaned : 0;
    }
    
    /**
     * Format tanggal ke bahasa Indonesia
     * 
     * @param string $date Tanggal (Y-m-d atau format lain)
     * @param bool $withDay Tampilkan nama hari
     * @param bool $longFormat Format panjang (Januari vs Jan)
     * @return string
     */
    public static function formatDate($date, $withDay = false, $longFormat = true)
    {
        if (empty($date)) {
            return '';
        }
        
        try {
            $dateTime = new DateTime($date);
            
            if ($withDay) {
                $dayName = self::$days[$dateTime->format('l')] . ', ';
            } else {
                $dayName = '';
            }
            
            $day = $dateTime->format('d');
            $month = $longFormat ? 
                self::$months[$dateTime->format('F')] : 
                self::$monthsShort[$dateTime->format('M')];
            $year = $dateTime->format('Y');
            
            return $dayName . $day . ' ' . $month . ' ' . $year;
        } catch (Exception $e) {
            return $date; // Return original if parsing fails
        }
    }
    
    /**
     * Format tanggal dan waktu lengkap
     * 
     * @param string $datetime Tanggal dan waktu
     * @param bool $withDay Tampilkan nama hari
     * @param bool $longFormat Format panjang
     * @return string
     */
    public static function formatDateTime($datetime, $withDay = false, $longFormat = true)
    {
        if (empty($datetime)) {
            return '';
        }
        
        try {
            $dateTime = new DateTime($datetime);
            
            $datePart = self::formatDate($datetime, $withDay, $longFormat);
            $timePart = $dateTime->format('H:i');
            
            return $datePart . ' pukul ' . $timePart;
        } catch (Exception $e) {
            return $datetime;
        }
    }
    
    /**
     * Format waktu (jam:menit) Indonesia
     * 
     * @param string $time Waktu
     * @return string
     */
    public static function formatTime($time)
    {
        if (empty($time)) {
            return '';
        }
        
        try {
            $dateTime = new DateTime($time);
            return $dateTime->format('H:i');
        } catch (Exception $e) {
            return $time;
        }
    }
    
    /**
     * Validasi format NIK Indonesia
     * 
     * @param string $nik Nomor NIK
     * @return bool
     */
    public static function validateNIK($nik)
    {
        if (empty($nik)) {
            return false;
        }
        
        // Basic validation: 16 digits
        if (!preg_match('/^\d{16}$/', $nik)) {
            return false;
        }
        
        // Extract components
        $provinceCode = substr($nik, 0, 2);
        $regencyCode = substr($nik, 2, 2);
        $districtCode = substr($nik, 4, 2);
        $birthDate = substr($nik, 6, 6);
        $sequence = substr($nik, 12, 4);
        
        // Validate province code (11-94, excluding some ranges)
        $provinceValid = preg_match('/^(1[1-9]|21|[37][1-6]|5[1-3]|6[1-5]|[89][12])/', $provinceCode);
        
        // Validate birth date
        $day = (int) substr($birthDate, 0, 2);
        $month = (int) substr($birthDate, 2, 2);
        $year = (int) substr($birthDate, 4, 2);
        
        // Check month validity
        if ($month < 1 || $month > 12) {
            return false;
        }
        
        // Check day validity (considering female +40 adjustment)
        if ($day > 71) { // Female (40 + max day 31)
            $day -= 40;
        }
        
        if ($day < 1 || $day > 31) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Format NIK dengan pemisah
     * 
     * @param string $nik Nomor NIK
     * @param string $separator Pemisah
     * @return string
     */
    public static function formatNIK($nik, $separator = ' ')
    {
        if (!self::validateNIK($nik)) {
            return $nik;
        }
        
        $province = substr($nik, 0, 2);
        $regency = substr($nik, 2, 2);
        $district = substr($nik, 4, 2);
        $birthdate = substr($nik, 6, 6);
        $sequence = substr($nik, 12, 4);
        
        return $province . $separator . 
               $regency . $separator . 
               $district . $separator . 
               $birthdate . $separator . 
               $sequence;
    }
    
    /**
     * Extract informasi dari NIK
     * 
     * @param string $nik Nomor NIK
     * @return array
     */
    public static function extractNIKInfo($nik)
    {
        if (!self::validateNIK($nik)) {
            return [];
        }
        
        $day = (int) substr($nik, 6, 2);
        $month = (int) substr($nik, 8, 2);
        $year = (int) substr($nik, 10, 2);
        
        // Determine gender and actual birth day
        $isFemale = $day > 40;
        $actualDay = $isFemale ? $day - 40 : $day;
        
        // Determine century (assuming current year logic)
        $currentYear = (int) date('Y');
        $currentYear2Digit = $currentYear % 100;
        $century = ($year <= $currentYear2Digit) ? 2000 : 1900;
        $fullYear = $century + $year;
        
        return [
            'province_code' => substr($nik, 0, 2),
            'regency_code' => substr($nik, 2, 2),
            'district_code' => substr($nik, 4, 2),
            'birth_day' => $actualDay,
            'birth_month' => $month,
            'birth_year' => $fullYear,
            'gender' => $isFemale ? 'Perempuan' : 'Laki-laki',
            'sequence' => substr($nik, 12, 4)
        ];
    }
    
    /**
     * Format nomor telepon Indonesia
     * 
     * @param string $phone Nomor telepon
     * @param string $format Format: 'international', 'national', 'pretty'
     * @return string
     */
    public static function formatPhoneNumber($phone, $format = 'national')
    {
        if (empty($phone)) {
            return '';
        }
        
        // Clean phone number
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Remove leading 62 or 0
        if (substr($cleaned, 0, 2) === '62') {
            $cleaned = substr($cleaned, 2);
        } elseif (substr($cleaned, 0, 1) === '0') {
            $cleaned = substr($cleaned, 1);
        }
        
        // Validate length (Indonesia phone numbers are 9-13 digits)
        if (strlen($cleaned) < 9 || strlen($cleaned) > 13) {
            return $phone; // Return original if invalid
        }
        
        switch ($format) {
            case 'international':
                return self::COUNTRY_CODE . ' ' . $cleaned;
                
            case 'national':
                return '0' . $cleaned;
                
            case 'pretty':
                // Format with spaces for readability
                if (strlen($cleaned) <= 10) {
                    // Mobile: 08xx-xxxx-xxxx
                    return '0' . substr($cleaned, 0, 3) . '-' . substr($cleaned, 3, 4) . '-' . substr($cleaned, 7);
                } else {
                    // Landline: 0xxx-xxxx-xxxx
                    return '0' . substr($cleaned, 0, 3) . '-' . substr($cleaned, 3, 4) . '-' . substr($cleaned, 7);
                }
                
            default:
                return '0' . $cleaned;
        }
    }
    
    /**
     * Validasi nomor telepon Indonesia
     * 
     * @param string $phone Nomor telepon
     * @return bool
     */
    public static function validatePhoneNumber($phone)
    {
        if (empty($phone)) {
            return false;
        }
        
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if starts with 62 or 0, and valid length
        if (substr($cleaned, 0, 2) === '62') {
            $cleaned = substr($cleaned, 2);
        } elseif (substr($cleaned, 0, 1) === '0') {
            $cleaned = substr($cleaned, 1);
        }
        
        return strlen($cleaned) >= 9 && strlen($cleaned) <= 13;
    }
    
    /**
     * Format alamat lengkap Indonesia
     * 
     * @param array $addressData Data alamat
     * @return string
     */
    public static function formatAddress($addressData)
    {
        $parts = [];
        
        // Street address
        if (!empty($addressData['street'])) {
            $parts[] = trim($addressData['street']);
        }
        
        // RT/RW
        if (!empty($addressData['rt'])) {
            $parts[] = 'RT ' . trim($addressData['rt']);
        }
        
        if (!empty($addressData['rw'])) {
            $parts[] = 'RW ' . trim($addressData['rw']);
        }
        
        // Kelurahan/Desa
        if (!empty($addressData['village'])) {
            $parts[] = 'Kel. ' . trim($addressData['village']);
        }
        
        // Kecamatan
        if (!empty($addressData['district'])) {
            $parts[] = 'Kec. ' . trim($addressData['district']);
        }
        
        // Kota/Kabupaten
        if (!empty($addressData['city'])) {
            $parts[] = trim($addressData['city']);
        }
        
        // Provinsi
        if (!empty($addressData['province'])) {
            $parts[] = trim($addressData['province']);
        }
        
        // Kode Pos
        if (!empty($addressData['postal_code'])) {
            $parts[] = trim($addressData['postal_code']);
        }
        
        return implode(', ', $parts);
    }
    
    /**
     * Validasi kode pos Indonesia (5 digit)
     * 
     * @param string $postalCode Kode pos
     * @return bool
     */
    public static function validatePostalCode($postalCode)
    {
        return preg_match('/^\d{5}$/', $postalCode);
    }
    
    /**
     * Format nama lengkap (title case untuk bahasa Indonesia)
     * 
     * @param string $name Nama lengkap
     * @return string
     */
    public static function formatName($name)
    {
        if (empty($name)) {
            return '';
        }
        
        // Convert to lowercase then capitalize each word
        $name = strtolower(trim($name));
        
        // Handle special Indonesian name prefixes
        $prefixes = ['muhammad', 'mohammad', 'm.', 'dr.', 'ir.', 'h.'];
        foreach ($prefixes as $prefix) {
            if (strpos($name, $prefix) === 0) {
                $name = str_replace($prefix, ucfirst($prefix), $name);
                break;
            }
        }
        
        // Capitalize each word
        $words = explode(' ', $name);
        $formattedWords = [];
        
        foreach ($words as $word) {
            // Handle common Indonesian name patterns
            if (in_array(strtolower($word), ['bin', 'binti', 'putra', 'putri'])) {
                $formattedWords[] = strtolower($word);
            } else {
                $formattedWords[] = ucfirst($word);
            }
        }
        
        return implode(' ', $formattedWords);
    }
    
    /**
     * Konversi angka ke terbilang bahasa Indonesia
     * 
     * @param int $number Angka
     * @param bool $withCurrency Tambahkan "Rupiah"
     * @return string
     */
    public static function numberToWords($number, $withCurrency = false)
    {
        if (!is_numeric($number)) {
            return '';
        }
        
        if ($number == 0) {
            $result = 'nol';
        } else {
            $result = self::convertNumberToWords($number);
        }
        
        return $withCurrency ? $result . ' Rupiah' : $result;
    }
    
    /**
     * Helper function untuk konversi angka ke kata
     * 
     * @param int $number
     * @return string
     */
    private static function convertNumberToWords($number)
    {
        $units = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'
        ];
        
        if ($number < 12) {
            return $units[$number];
        } elseif ($number < 20) {
            return $units[$number - 10] . ' belas';
        } elseif ($number < 100) {
            $tens = floor($number / 10);
            $remainder = $number % 10;
            return ($tens == 1 ? 'sepuluh' : $units[$tens] . ' puluh') . 
                   ($remainder > 0 ? ' ' . $units[$remainder] : '');
        } elseif ($number < 200) {
            return 'seratus ' . self::convertNumberToWords($number - 100);
        } elseif ($number < 1000) {
            $hundreds = floor($number / 100);
            $remainder = $number % 100;
            return $units[$hundreds] . ' ratus' . 
                   ($remainder > 0 ? ' ' . self::convertNumberToWords($remainder) : '');
        } elseif ($number < 2000) {
            return 'seribu ' . self::convertNumberToWords($number - 1000);
        } elseif ($number < 1000000) {
            $thousands = floor($number / 1000);
            $remainder = $number % 1000;
            return self::convertNumberToWords($thousands) . ' ribu' . 
                   ($remainder > 0 ? ' ' . self::convertNumberToWords($remainder) : '');
        } elseif ($number < 1000000000) {
            $millions = floor($number / 1000000);
            $remainder = $number % 1000000;
            return self::convertNumberToWords($millions) . ' juta' . 
                   ($remainder > 0 ? ' ' . self::convertNumberToWords($remainder) : '');
        } elseif ($number < 1000000000000) {
            $billions = floor($number / 1000000000);
            $remainder = $number % 1000000000;
            return self::convertNumberToWords($billions) . ' miliar' . 
                   ($remainder > 0 ? ' ' . self::convertNumberToWords($remainder) : '');
        } else {
            return 'angka terlalu besar';
        }
    }
    
    /**
     * Format persentase Indonesia
     * 
     * @param float $number Angka
     * @param int $decimals Jumlah desimal
     * @return string
     */
    public static function formatPercentage($number, $decimals = 2)
    {
        $formatted = number_format($number, $decimals, self::DECIMAL_SEPARATOR, self::THOUSANDS_SEPARATOR);
        return $formatted . '%';
    }
    
    /**
     * Format ukuran file (bytes ke KB, MB, GB)
     * 
     * @param int $bytes Ukuran dalam bytes
     * @param int $decimals Jumlah desimal
     * @return string
     */
    public static function formatFileSize($bytes, $decimals = 2)
    {
        if ($bytes >= 1073741824) {
            $formatted = number_format($bytes / 1073741824, $decimals, self::DECIMAL_SEPARATOR, self::THOUSANDS_SEPARATOR);
            return $formatted . ' GB';
        } elseif ($bytes >= 1048576) {
            $formatted = number_format($bytes / 1048576, $decimals, self::DECIMAL_SEPARATOR, self::THOUSANDS_SEPARATOR);
            return $formatted . ' MB';
        } elseif ($bytes >= 1024) {
            $formatted = number_format($bytes / 1024, $decimals, self::DECIMAL_SEPARATOR, self::THOUSANDS_SEPARATOR);
            return $formatted . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    /**
     * Format singkatan gelar akademik Indonesia
     * 
     * @param string $name Nama dengan gelar
     * @return string
     */
    public static function formatAcademicTitle($name)
    {
        $titleMappings = [
            'S.T.' => 'Sarjana Teknik',
            'S.Kom.' => 'Sarjana Komputer',
            'S.E.' => 'Sarjana Ekonomi',
            'S.H.' => 'Sarjana Hukum',
            'S.Pd.' => 'Sarjana Pendidikan',
            'S.Psi.' => 'Sarjana Psikologi',
            'M.T.' => 'Magister Teknik',
            'M.Kom.' => 'Magister Komputer',
            'M.E.' => 'Magister Ekonomi',
            'M.H.' => 'Magister Hukum',
            'Dr.' => 'Doktor',
            'Ir.' => 'Insinyur'
        ];
        
        foreach ($titleMappings as $abbreviation => $fullTitle) {
            if (strpos($name, $abbreviation) !== false) {
                return str_replace($abbreviation, $fullTitle, $name);
            }
        }
        
        return $name;
    }
    
    /**
     * Generate format data tabel Indonesia
     * 
     * @param array $data Data array
     * @return array
     */
    public static function formatTableData($data)
    {
        $formatted = [];
        
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'amount':
                case 'price':
                case 'total':
                case 'balance':
                    $formatted[$key] = self::formatRupiah($value);
                    break;
                    
                case 'date':
                case 'created_at':
                case 'updated_at':
                    $formatted[$key] = self::formatDate($value);
                    break;
                    
                case 'datetime':
                case 'transaction_date':
                    $formatted[$key] = self::formatDateTime($value);
                    break;
                    
                case 'phone':
                case 'mobile':
                case 'telephone':
                    $formatted[$key] = self::formatPhoneNumber($value, 'pretty');
                    break;
                    
                case 'nik':
                    $formatted[$key] = self::formatNIK($value);
                    break;
                    
                case 'postal_code':
                    $formatted[$key] = $value; // Keep as is for validation
                    break;
                    
                default:
                    $formatted[$key] = $value;
            }
        }
        
        return $formatted;
    }
    
    /**
     * Validasi data lengkap pengguna Indonesia
     * 
     * @param array $userData Data pengguna
     * @return array Validation result
     */
    public static function validateUserData($userData)
    {
        $errors = [];
        
        // Validate NIK
        if (!empty($userData['nik']) && !self::validateNIK($userData['nik'])) {
            $errors['nik'] = 'Format NIK tidak valid';
        }
        
        // Validate phone
        if (!empty($userData['phone']) && !self::validatePhoneNumber($userData['phone'])) {
            $errors['phone'] = 'Format nomor telepon tidak valid';
        }
        
        // Validate postal code
        if (!empty($userData['postal_code']) && !self::validatePostalCode($userData['postal_code'])) {
            $errors['postal_code'] = 'Format kode pos tidak valid';
        }
        
        // Validate email (basic)
        if (!empty($userData['email']) && !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}

/**
 * Shortcut functions untuk kemudahan penggunaan
 */

if (!function_exists('format_rupiah')) {
    function format_rupiah($amount, $withSymbol = true, $decimals = 2)
    {
        return IndonesianFormatHelper::formatRupiah($amount, $withSymbol, $decimals);
    }
}

if (!function_exists('format_tanggal')) {
    function format_tanggal($date, $withDay = false, $longFormat = true)
    {
        return IndonesianFormatHelper::formatDate($date, $withDay, $longFormat);
    }
}

if (!function_exists('format_telepon')) {
    function format_telepon($phone, $format = 'national')
    {
        return IndonesianFormatHelper::formatPhoneNumber($phone, $format);
    }
}

if (!function_exists('validate_nik')) {
    function validate_nik($nik)
    {
        return IndonesianFormatHelper::validateNIK($nik);
    }
}

if (!function_exists('angka_terbilang')) {
    function angka_terbilang($number, $withCurrency = false)
    {
        return IndonesianFormatHelper::numberToWords($number, $withCurrency);
    }
}

?>
