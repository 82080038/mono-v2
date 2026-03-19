<?php
/**
 * Multi-Factor Authentication System
 * Supports SMS OTP, Email OTP, and Authenticator Apps
 */

class MFA_System {
    private $db;
    private $config;
    
    public function __construct($database) {
        $this->db = $database;
        $this->config = $this->loadConfig();
    }
    
    private function loadConfig() {
        return [
            'sms_enabled' => true,
            'email_enabled' => true,
            'authenticator_enabled' => true,
            'otp_length' => 6,
            'otp_expiry' => 300, // 5 minutes
            'max_attempts' => 3
        ];
    }
    
    /**
     * Generate OTP for user
     */
    public function generateOTP($user_id, $type = 'sms') {
        $otp = $this->generateRandomOTP();
        $expiry = time() + $this->config['otp_expiry'];
        
        // Store OTP in database
        $stmt = $this->db->prepare("
            INSERT INTO mfa_tokens (user_id, token, type, expiry, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $otp, $type, $expiry]);
        
        // Send OTP based on type
        switch ($type) {
            case 'sms':
                return $this->sendSMSOTP($user_id, $otp);
            case 'email':
                return $this->sendEmailOTP($user_id, $otp);
            case 'authenticator':
                return $this->generateAuthenticatorQR($user_id);
        }
        
        return false;
    }
    
    /**
     * Verify OTP
     */
    public function verifyOTP($user_id, $otp, $type = 'sms') {
        $stmt = $this->db->prepare("
            SELECT * FROM mfa_tokens 
            WHERE user_id = ? AND token = ? AND type = ? AND expiry > ? AND used = 0
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$user_id, $otp, $type, time()]);
        
        $token = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($token) {
            // Mark token as used
            $update = $this->db->prepare("UPDATE mfa_tokens SET used = 1 WHERE id = ?");
            $update->execute([$token['id']]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Generate random OTP
     */
    private function generateRandomOTP() {
        return str_pad(random_int(0, 999999), $this->config['otp_length'], '0', STR_PAD_LEFT);
    }
    
    /**
     * Send SMS OTP
     */
    private function sendSMSOTP($user_id, $otp) {
        // Get user phone number
        $stmt = $this->db->prepare("SELECT phone FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && $user['phone']) {
            // Integrate with SMS gateway (Twilio, etc.)
            $message = "Koperasi SaaS: Kode OTP Anda adalah " . $otp . ". Berlaku 5 menit.";
            
            // Simulate SMS sending (replace with actual SMS API)
            error_log("SMS to {$user['phone']}: $message");
            return true;
        }
        
        return false;
    }
    
    /**
     * Send Email OTP
     */
    private function sendEmailOTP($user_id, $otp) {
        // Get user email
        $stmt = $this->db->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && $user['email']) {
            $subject = "Koperasi SaaS - Kode OTP";
            $message = "
                <h2>Kode OTP Anda</h2>
                <p>Kode One-Time Password (OTP) Anda adalah:</p>
                <h1 style='font-size: 24px; color: #3498db;'>" . $otp . "</h1>
                <p>Kode ini berlaku selama 5 menit.</p>
                <p>Jika Anda tidak meminta kode ini, abaikan email ini.</p>
            ";
            
            // Send email using PHPMailer or similar
            error_log("Email to {$user['email']}: $subject");
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user has MFA enabled
     */
    public function isMFAEnabled($user_id) {
        $stmt = $this->db->prepare("
            SELECT mfa_enabled FROM users WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user && $user['mfa_enabled'] == 1;
    }
    
    /**
     * Enable MFA for user
     */
    public function enableMFA($user_id, $methods = ['sms', 'email']) {
        $methods_json = json_encode($methods);
        
        $stmt = $this->db->prepare("
            UPDATE users SET mfa_enabled = 1, mfa_methods = ? WHERE id = ?
        ");
        return $stmt->execute([$methods_json, $user_id]);
    }
}
