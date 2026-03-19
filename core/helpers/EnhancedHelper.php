<?php
/**
 * Enhanced Helper Library for Koperasi SaaS Application
 * Based on internet best practices and industry standards
 */

class EnhancedHelper {
    
    // ==================== STRING HELPERS ====================
    
    /**
     * Create URL-friendly slug from string
     * Based on best practices from SwissHelper
     */
    public static function slug($text) {
        // Convert to lowercase and remove accents
        $text = strtolower($text);
        $text = self::removeAccents($text);
        
        // Replace non-alphanumeric characters with hyphens
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        
        // Remove leading/trailing hyphens
        return trim($text, '-');
    }
    
    /**
     * Remove accents from string
     */
    public static function removeAccents($text) {
        $accents = [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o', 'ø' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'ç' => 'c', 'ñ' => 'n',
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Ã' => 'A', 'Å' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Ö' => 'O', 'Õ' => 'O', 'Ø' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y', 'Ÿ' => 'Y',
            'Ç' => 'C', 'Ñ' => 'N'
        ];
        
        return strtr($text, $accents);
    }
    
    /**
     * Extract only numbers from string
     */
    public static function onlyNumbers($text) {
        return preg_replace('/[^0-9]/', '', $text);
    }
    
    /**
     * Apply mask to string (CPF, phone, etc.)
     */
    public static function mask($text, $mask) {
        $result = '';
        $textIndex = 0;
        
        for ($i = 0; $i < strlen($mask); $i++) {
            if ($mask[$i] === '#') {
                if (isset($text[$textIndex])) {
                    $result .= $text[$textIndex];
                    $textIndex++;
                }
            } else {
                $result .= $mask[$i];
            }
        }
        
        return $result;
    }
    
    // ==================== DATETIME HELPERS ====================
    
    /**
     * Enhanced DateTime helper
     * Based on SwissHelper now() function
     */
    public static function now($format = null) {
        $now = new DateTime();
        
        if ($format) {
            return $now->format($format);
        }
        
        return $now;
    }
    
    /**
     * Indonesian date format
     */
    public static function indoDate($date, $format = 'l, d F Y') {
        $months = [
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
            'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
            'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
            'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
        ];
        
        $days = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu'
        ];
        
        $dateObj = new DateTime($date);
        $formatted = $dateObj->format($format);
        
        // Replace English with Indonesian
        foreach ($months as $en => $id) {
            $formatted = str_replace($en, $id, $formatted);
        }
        
        foreach ($days as $en => $id) {
            $formatted = str_replace($en, $id, $formatted);
        }
        
        return $formatted;
    }
    
    /**
     * Calculate age from birth date
     */
    public static function calculateAge($birthDate) {
        $birth = new DateTime($birthDate);
        $today = new DateTime();
        
        return $birth->diff($today)->y;
    }
    
    /**
     * Add working days to date (excluding weekends)
     */
    public static function addWorkingDays($date, $days) {
        $date = new DateTime($date);
        
        while ($days > 0) {
            $date->add(new DateInterval('P1D'));
            
            // Skip weekends
            if ($date->format('N') < 6) {
                $days--;
            }
        }
        
        return $date->format('Y-m-d');
    }
    
    // ==================== FINANCIAL HELPERS ====================
    
    /**
     * Calculate loan payment (PMT)
     * Based on math-php Finance class
     */
    public static function calculatePMT($rate, $periods, $presentValue, $futureValue = 0, $beginning = false) {
        if ($rate == 0) {
            return -($presentValue + $futureValue) / $periods;
        }
        
        $when = $beginning ? 1 : 0;
        $factor = pow(1 + $rate, $periods);
        
        if ($beginning) {
            return ($presentValue * $factor + $futureValue) * $rate / (1 + $rate) / ($factor - 1);
        } else {
            return ($presentValue * $factor + $futureValue) * $rate / ($factor - 1);
        }
    }
    
    /**
     * Calculate interest portion of payment (IPMT)
     */
    public static function calculateIPMT($rate, $period, $periods, $presentValue, $futureValue = 0, $beginning = false) {
        if ($period < 1 || $period > $periods || $rate == 0) {
            return 0;
        }
        
        if ($beginning && $period == 1) {
            return 0;
        }
        
        $payment = self::calculatePMT($rate, $periods, $presentValue, $futureValue, $beginning);
        
        if ($beginning) {
            $interest = (self::calculateFV($rate, $period - 2, $payment, $presentValue, $beginning) - $payment) * $rate;
        } else {
            $interest = self::calculateFV($rate, $period - 1, $payment, $presentValue, $beginning) * $rate;
        }
        
        return $interest;
    }
    
    /**
     * Calculate principal portion of payment (PPMT)
     */
    public static function calculatePPMT($rate, $period, $periods, $presentValue, $futureValue = 0, $beginning = false) {
        $payment = self::calculatePMT($rate, $periods, $presentValue, $futureValue, $beginning);
        $interest = self::calculateIPMT($rate, $period, $periods, $presentValue, $futureValue, $beginning);
        
        return $payment - $interest;
    }
    
    /**
     * Calculate future value (FV)
     */
    public static function calculateFV($rate, $periods, $payment, $presentValue, $beginning = false) {
        if ($rate == 0) {
            return -($presentValue + $payment * $periods);
        }
        
        $when = $beginning ? 1 : 0;
        $factor = pow(1 + $rate, $periods);
        
        return -($presentValue * $factor) - $payment * (1 + $rate * $when) * ($factor - 1) / $rate;
    }
    
    /**
     * Calculate Annual Equivalent Rate (AER)
     */
    public static function calculateAER($nominal, $periods) {
        if ($periods == 1) {
            return $nominal;
        }
        
        return pow(1 + ($nominal / $periods), $periods) - 1;
    }
    
    /**
     * Format currency with Indonesian format
     */
    public static function formatCurrency($amount, $currency = 'IDR') {
        if (!is_numeric($amount)) {
            $amount = 0;
        }
        
        $formatted = number_format($amount, 2, ',', '.');
        
        switch ($currency) {
            case 'IDR':
                return 'Rp ' . $formatted;
            case 'USD':
                return '$' . $formatted;
            default:
                return $formatted . ' ' . $currency;
        }
    }
    
    /**
     * Calculate loan amortization schedule
     */
    public static function loanAmortization($principal, $annualRate, $years, $paymentFrequency = 12) {
        $periodicRate = $annualRate / $paymentFrequency;
        $totalPeriods = $years * $paymentFrequency;
        
        $payment = abs(self::calculatePMT($periodicRate, $totalPeriods, $principal));
        $balance = $principal;
        $schedule = [];
        
        for ($period = 1; $period <= $totalPeriods; $period++) {
            $interest = $balance * $periodicRate;
            $principalPayment = $payment - $interest;
            $balance -= $principalPayment;
            
            $schedule[] = [
                'period' => $period,
                'payment' => $payment,
                'interest' => $interest,
                'principal' => $principalPayment,
                'balance' => max(0, $balance)
            ];
        }
        
        return $schedule;
    }
    
    // ==================== VALIDATION HELPERS ====================
    
    /**
     * Enhanced email validation
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (Indonesian format)
     */
    public static function validatePhone($phone) {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check Indonesian phone format
        return (strlen($phone) >= 10 && strlen($phone) <= 13) && 
               (substr($phone, 0, 2) === '08' || substr($phone, 0, 3) === '+62');
    }
    
    /**
     * Validate Indonesian ID (NIK)
     */
    public static function validateNIK($nik) {
        // Remove spaces and hyphens
        $nik = preg_replace('/[\s-]/', '', $nik);
        
        // Check if 16 digits
        return preg_match('/^[0-9]{16}$/', $nik);
    }
    
    /**
     * Validate credit card number
     */
    public static function validateCreditCard($number) {
        // Remove spaces and hyphens
        $number = preg_replace('/[\s-]/', '', $number);
        
        // Check if numeric and valid length
        if (!is_numeric($number) || strlen($number) < 13 || strlen($number) > 19) {
            return false;
        }
        
        // Luhn algorithm
        $sum = 0;
        $alternate = false;
        
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $digit = $number[$i];
            
            if ($alternate) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
            $alternate = !$alternate;
        }
        
        return $sum % 10 === 0;
    }
    
    /**
     * Validate password strength
     */
    public static function validatePassword($password, $minLength = 8, $requireUppercase = true, $requireLowercase = true, $requireNumbers = true, $requireSymbols = true) {
        $errors = [];
        
        if (strlen($password) < $minLength) {
            $errors[] = "Password must be at least {$minLength} characters";
        }
        
        if ($requireUppercase && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if ($requireLowercase && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        if ($requireNumbers && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        if ($requireSymbols && !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = "Password must contain at least one symbol";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validate date range
     */
    public static function validateDateRange($date, $minAge = null, $maxAge = null) {
        $dateObj = new DateTime($date);
        $today = new DateTime();
        $age = $dateObj->diff($today)->y;
        
        if ($minAge && $age < $minAge) {
            return false;
        }
        
        if ($maxAge && $age > $maxAge) {
            return false;
        }
        
        return true;
    }
    
    // ==================== SECURITY HELPERS ====================
    
    /**
     * Generate secure random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = self::generateToken();
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generate CSRF hidden field
     */
    public static function csrfField() {
        $token = self::generateCSRFToken();
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Hash password securely
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Sanitize input
     */
    public static function sanitize($input, $type = 'string') {
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'string':
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
    
    // ==================== FILE UPLOAD HELPERS ====================
    
    /**
     * Secure file upload validation
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) {
        $errors = [];
        
        // Check if file was uploaded
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload error: ' . $file['error'];
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $errors[] = 'File size exceeds maximum allowed size';
        }
        
        // Check file type
        if (!empty($allowedTypes)) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $errors[] = 'File type not allowed';
            }
        }
        
        // Check file extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'File extension not allowed';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Process image upload with security
     */
    public static function processImageUpload($file, $uploadDir, $maxWidth = 1920, $maxHeight = 1080, $quality = 85) {
        $validation = self::validateFileUpload($file, ['image/jpeg', 'image/png', 'image/gif']);
        
        if (!$validation['valid']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }
        
        // Create upload directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $uploadPath = $uploadDir . '/' . $filename;
        
        // Process image based on type
        $imageInfo = getimagesize($file['tmp_name']);
        
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($file['tmp_name']);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($file['tmp_name']);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($file['tmp_name']);
                break;
            default:
                return ['success' => false, 'errors' => ['Unsupported image type']];
        }
        
        // Calculate new dimensions
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = round($width * $ratio);
            $newHeight = round($height * $ratio);
            
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            imagedestroy($source);
            $source = $newImage;
        }
        
        // Save processed image
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($source, $uploadPath, $quality);
                break;
            case 'png':
                imagepng($source, $uploadPath, round($quality / 11));
                break;
            case 'gif':
                imagegif($source, $uploadPath);
                break;
        }
        
        imagedestroy($source);
        
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $uploadPath,
            'size' => filesize($uploadPath)
        ];
    }
    
    // ==================== ARRAY HELPERS ====================
    
    /**
     * Get nested array value with dot notation
     */
    public static function arrayGet($array, $key, $default = null) {
        $keys = explode('.', $key);
        $value = $array;
        
        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    /**
     * Get only specified keys from array
     */
    public static function arrayOnly($array, $keys) {
        return array_intersect_key($array, array_flip($keys));
    }
    
    /**
     * Get all except specified keys from array
     */
    public static function arrayExcept($array, $keys) {
        return array_diff_key($array, array_flip($keys));
    }
    
    // ==================== URL HELPERS ====================
    
    /**
     * Get current URL
     */
    public static function currentUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
    
    /**
     * Get base URL
     */
    public static function baseUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'];
    }
    
    /**
     * Generate URL to specific path
     */
    public static function urlTo($path) {
        return self::baseUrl() . '/' . ltrim($path, '/');
    }
    
    // ==================== LOGGING HELPERS ====================
    
    /**
     * Log message to file
     */
    public static function log($message, $level = 'INFO', $file = null) {
        $logFile = $file ?: __DIR__ . '/../logs/app.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] {$level}: {$message}" . PHP_EOL;
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log error
     */
    public static function logError($message, $context = []) {
        $contextStr = !empty($context) ? ' Context: ' . json_encode($context) : '';
        self::log($message . $contextStr, 'ERROR');
    }
    
    /**
     * Log info
     */
    public static function logInfo($message) {
        self::log($message, 'INFO');
    }
    
    /**
     * Log debug
     */
    public static function logDebug($message) {
        self::log($message, 'DEBUG');
    }
    
    // ==================== SESSION HELPERS ====================
    
    /**
     * Set session value
     */
    public static function sessionPut($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session value
     */
    public static function sessionGet($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session key exists
     */
    public static function sessionHas($key) {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session value
     */
    public static function sessionForget($key) {
        unset($_SESSION[$key]);
    }
    
    /**
     * Get all session data
     */
    public static function sessionAll() {
        return $_SESSION;
    }
    
    /**
     * Destroy session
     */
    public static function sessionDestroy() {
        session_destroy();
    }
    
    // ==================== MISCELLANEOUS HELPERS ====================
    
    /**
     * Generate random string
     */
    public static function randomString($length = 16, $type = 'alnum') {
        $characters = '';
        
        switch ($type) {
            case 'alpha':
                $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'numeric':
                $characters = '0123456789';
                break;
            case 'alnum':
            default:
                $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                break;
        }
        
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
    
    /**
     * Generate UUID v4
     */
    public static function generateUUID() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Check if request is AJAX
     */
    public static function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Get client IP address
     */
    public static function getClientIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Convert bytes to human readable format
     */
    public static function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Create pagination
     */
    public static function pagination($totalItems, $itemsPerPage, $currentPage = 1) {
        $totalPages = ceil($totalItems / $itemsPerPage);
        $currentPage = max(1, min($currentPage, $totalPages));
        $offset = ($currentPage - 1) * $itemsPerPage;
        
        return [
            'total_items' => $totalItems,
            'items_per_page' => $itemsPerPage,
            'total_pages' => $totalPages,
            'current_page' => $currentPage,
            'offset' => $offset,
            'has_next' => $currentPage < $totalPages,
            'has_prev' => $currentPage > 1,
            'next_page' => $currentPage + 1,
            'prev_page' => $currentPage - 1
        ];
    }
}

?>
