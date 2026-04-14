<?php
/**
 * KSP Lam Gabe Jaya - Auth Middleware (Phase 2)
 */
require_once __DIR__ . '/../config/Config.php';

class Middleware {

    private static $currentUser = null;

    public static function requireAuth(): array {
        $token = self::extractToken();
        if (!$token) self::sendError(401, 'Token autentikasi diperlukan');

        $payload = self::validateJWT($token);
        if (!$payload) self::sendError(401, 'Token tidak valid atau kadaluarsa');

        self::$currentUser = $payload;
        return $payload;
    }

    public static function requireRole(array $roles): void {
        if (!self::$currentUser) self::sendError(401, 'Autentikasi diperlukan');
        
        // Get all roles for user (main role + additional roles from user_roles)
        $userRoles = self::getUserRoles();
        $allowed = array_map('strtolower', $roles);
        
        // Super admin bypasses all role checks
        if (in_array('super_admin', $userRoles)) return;
        
        // Check if any of user's roles match allowed roles
        $hasAccess = false;
        foreach ($userRoles as $role) {
            if (in_array($role, $allowed)) {
                $hasAccess = true;
                break;
            }
        }
        
        if (!$hasAccess) {
            self::sendError(403, 'Akses ditolak. Role yang dibutuhkan: ' . implode(', ', $roles));
        }
    }

    /**
     * Get all roles for current user (main role + additional roles from user_roles table)
     * Returns array of normalized role codes
     */
    public static function getUserRoles(): array {
        if (!self::$currentUser) return [];
        
        $roles = [];
        
        // Add main role from users table
        $mainRole = self::normalizeRole(self::$currentUser['role'] ?? '');
        if ($mainRole) $roles[] = $mainRole;
        
        // Add additional roles from user_roles table
        try {
            $pdo = Config::getDatabase();
            $userId = self::getCurrentUserId();
            
            if ($userId) {
                $stmt = $pdo->prepare("
                    SELECT r.role_code 
                    FROM user_roles ur
                    JOIN roles r ON r.id = ur.role_id
                    WHERE ur.user_id = ? AND ur.is_active = 1 AND r.is_active = 1
                ");
                $stmt->execute([$userId]);
                $additionalRoles = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($additionalRoles as $roleCode) {
                    $roles[] = strtolower($roleCode);
                }
            }
        } catch (Exception $e) {
            // If query fails, return main role only
        }
        
        return array_unique($roles);
    }

    /**
     * Normalize DB role string to a simple lowercase key.
     * e.g. "Super Admin" → "super_admin", "Teller" → "teller"
     */
    public static function normalizeRole(string $role): string {
        $map = [
            'super admin' => 'super_admin',
            'superadmin'  => 'super_admin',
            'admin'       => 'admin',
            'manager'     => 'manager',
            'manajer'     => 'manager',
            'teller'      => 'teller',
            'kasir'       => 'kasir',
            'mantri'      => 'mantri',
            'collector'   => 'collector',
            'surveyor'    => 'surveyor',
            'member'      => 'member',
            'akuntansi'   => 'akuntansi',
        ];
        return $map[strtolower($role)] ?? strtolower(str_replace(' ', '_', $role));
    }

    public static function getCurrentUser(): ?array {
        return self::$currentUser;
    }

    public static function getCurrentUserId(): ?int {
        return isset(self::$currentUser['user_id']) ? (int)self::$currentUser['user_id'] : null;
    }

    public static function getCurrentRole(): ?string {
        return self::$currentUser['role'] ?? null;
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private static function extractToken(): ?string {
        $headers = getallheaders();
        $auth    = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        if ($auth && str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }
        return $_GET['token'] ?? $_POST['token'] ?? null;
    }

    private static function validateJWT(string $token): ?array {
        try {
            if (self::isBlacklisted($token)) return null;

            $parts = explode('.', $token);
            if (count($parts) !== 3) return null;

            [$header, $payload, $signature] = $parts;
            $expected = str_replace(['+','/','='], ['-','_',''],
                base64_encode(hash_hmac('sha256', "$header.$payload", Config::JWT_SECRET, true))
            );
            if (!hash_equals($expected, $signature)) return null;

            $decoded = json_decode(
                base64_decode(str_replace(['-','_'], ['+','/'], $payload)), true
            );
            if (!$decoded || ($decoded['exp'] ?? 0) < time()) return null;

            return $decoded;
        } catch (Exception $e) {
            return null;
        }
    }

    private static function isBlacklisted(string $token): bool {
        try {
            $pdo  = Config::getDatabase();
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM token_blacklist WHERE token = ? AND (expires_at IS NULL OR expires_at > NOW())"
            );
            $stmt->execute([$token]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private static function sendError(int $code, string $message): void {
        http_response_code($code);
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}
