<?php
/**
 * Payment Gateway Integration System
 * Supports QRIS, E-wallet, and Bank Transfers
 */

class PaymentGateway {
    private $db;
    private $config;
    
    public function __construct($database) {
        $this->db = $database;
        $this->config = $this->loadPaymentConfig();
    }
    
    private function loadPaymentConfig() {
        return [
            'qris' => [
                'enabled' => true,
                'merchant_id' => '1234567890',
                'api_key' => 'qris_api_key_here',
                'endpoint' => 'https://api.qris.id/v1.0'
            ],
            'gopay' => [
                'enabled' => true,
                'client_id' => 'gopay_client_id',
                'client_secret' => 'gopay_client_secret',
                'endpoint' => 'https://api.gopay.co.id'
            ],
            'ovo' => [
                'enabled' => true,
                'app_id' => 'ovo_app_id',
                'app_secret' => 'ovo_app_secret',
                'endpoint' => 'https://api.ovo.id'
            ],
            'dana' => [
                'enabled' => true,
                'merchant_id' => 'dana_merchant_id',
                'secret_key' => 'dana_secret_key',
                'endpoint' => 'https://api.dana.id'
            ]
        ];
    }
    
    /**
     * Create QRIS payment
     */
    public function createQRISPayment($order_data) {
        $qr_data = [
            'merchant_id' => $this->config['qris']['merchant_id'],
            'order_id' => $order_data['order_id'],
            'amount' => $order_data['amount'],
            'currency' => 'IDR',
            'description' => $order_data['description'] ?? 'Pembayaran Koperasi'
        ];
        
        // Generate QRIS QR code
        $qr_string = $this->generateQRISString($qr_data);
        $qr_image = $this->generateQRCode($qr_string);
        
        // Store payment record
        $stmt = $this->db->prepare("
            INSERT INTO payments (order_id, type, amount, qr_data, status, created_at)
            VALUES (?, 'qris', ?, ?, 'PENDING', NOW())
        ");
        $stmt->execute([$order_data['order_id'], $order_data['amount'], json_encode($qr_data)]);
        
        return [
            'payment_id' => $this->db->lastInsertId(),
            'qr_code' => base64_encode($qr_image),
            'qr_string' => $qr_string,
            'amount' => $order_data['amount']
        ];
    }
    
    /**
     * Create E-wallet payment
     */
    public function createEwalletPayment($order_data, $wallet_type) {
        $wallet_config = $this->config[$wallet_type];
        
        if (!$wallet_config['enabled']) {
            return ['error' => 'E-wallet not enabled'];
        }
        
        $payment_data = [
            'order_id' => $order_data['order_id'],
            'amount' => $order_data['amount'],
            'description' => $order_data['description'] ?? 'Pembayaran Koperasi'
        ];
        
        // Store payment record
        $stmt = $this->db->prepare("
            INSERT INTO payments (order_id, type, amount, wallet_data, status, created_at)
            VALUES (?, ?, ?, ?, 'PENDING', NOW())
        ");
        $stmt->execute([$order_data['order_id'], $wallet_type, $order_data['amount'], json_encode($payment_data)]);
        
        // Generate payment URL (simulate)
        $payment_url = $this->generateEwalletURL($wallet_type, $payment_data);
        
        return [
            'payment_id' => $this->db->lastInsertId(),
            'payment_url' => $payment_url,
            'wallet_type' => $wallet_type,
            'amount' => $order_data['amount']
        ];
    }
    
    /**
     * Generate QRIS string
     */
    private function generateQRISString($qr_data) {
        // Simplified QRIS generation (use actual QRIS library in production)
        return "00020101021226" . strlen($qr_data['merchant_id']) . $qr_data['merchant_id'] .
               "52" . strlen($qr_data['amount']) . $qr_data['amount'] .
               "53" . strlen($qr_data['currency']) . $qr_data['currency'];
    }
    
    /**
     * Generate QR code image
     */
    private function generateQRCode($qr_string) {
        // Use QR code library (phpqrcode, endroid/qr-code, etc.)
        // This is a placeholder implementation
        $image = imagecreate(200, 200);
        $bg_color = imagecolorallocate($image, 255, 255, 255);
        $fg_color = imagecolorallocate($image, 0, 0, 0);
        
        // Generate QR code pattern (simplified)
        for ($x = 0; $x < 200; $x += 10) {
            for ($y = 0; $y < 200; $y += 10) {
                if (rand(0, 1)) {
                    imagefilledrectangle($image, $x, $y, $x + 8, $y + 8, $fg_color);
                }
            }
        }
        
        ob_start();
        imagepng($image);
        $qr_image = ob_get_contents();
        imagedestroy($image);
        ob_end_clean();
        
        return $qr_image;
    }
    
    /**
     * Generate E-wallet URL
     */
    private function generateEwalletURL($wallet_type, $payment_data) {
        $base_url = $this->config[$wallet_type]['endpoint'];
        
        switch ($wallet_type) {
            case 'gopay':
                return $base_url . '/pay?data=' . base64_encode(json_encode($payment_data));
            case 'ovo':
                return $base_url . '/payment?data=' . base64_encode(json_encode($payment_data));
            case 'dana':
                return $base_url . '/pay?data=' . base64_encode(json_encode($payment_data));
            default:
                return '';
        }
    }
    
    /**
     * Get payment status
     */
    public function getPaymentStatus($payment_id) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->execute([$payment_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
