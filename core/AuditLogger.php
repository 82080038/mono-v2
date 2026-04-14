<?php
/**
 * KSP Lam Gabe Jaya - Audit Logger (Phase 2)
 * Logs all CREATE / UPDATE / DELETE operations to audit_logs table.
 */
require_once __DIR__ . '/../config/Config.php';

class AuditLogger {

    /**
     * Log an action.
     *
     * @param string   $action      'CREATE' | 'UPDATE' | 'DELETE' | 'APPROVE' | 'REJECT' | 'LOGIN' | 'LOGOUT'
     * @param string   $tableName   Affected table, e.g. 'loans', 'savings_accounts'
     * @param int|null $recordId    Primary key of affected row
     * @param array    $oldValues   Values before change (empty for CREATE)
     * @param array    $newValues   Values after change  (empty for DELETE)
     * @param int|null $userId      Acting user id (null = system)
     * @param string   $description Optional human-readable description
     */
    public static function log(
        string $action,
        string $tableName,
        ?int   $recordId   = null,
        array  $oldValues  = [],
        array  $newValues  = [],
        ?int   $userId     = null,
        string $description = ''
    ): void {
        try {
            $pdo = Config::getDatabase();
            $stmt = $pdo->prepare("
                INSERT INTO audit_logs
                    (user_id, action, table_name, record_id, old_values, new_values,
                     ip_address, user_agent, description, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $userId,
                strtoupper($action),
                $tableName,
                $recordId,
                $oldValues  ? json_encode($oldValues,  JSON_UNESCAPED_UNICODE) : null,
                $newValues  ? json_encode($newValues,  JSON_UNESCAPED_UNICODE) : null,
                self::getClientIp(),
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $description ?: null,
            ]);
        } catch (Exception $e) {
            error_log('[AuditLogger] Failed to write audit log: ' . $e->getMessage());
        }
    }

    /** Shorthand helpers */
    public static function logCreate(string $table, int $recordId, array $data, ?int $userId = null, string $desc = ''): void {
        self::log('CREATE', $table, $recordId, [], $data, $userId, $desc);
    }

    public static function logUpdate(string $table, int $recordId, array $old, array $new, ?int $userId = null, string $desc = ''): void {
        self::log('UPDATE', $table, $recordId, $old, $new, $userId, $desc);
    }

    public static function logDelete(string $table, int $recordId, array $old, ?int $userId = null, string $desc = ''): void {
        self::log('DELETE', $table, $recordId, $old, [], $userId, $desc);
    }

    public static function logApprove(string $table, int $recordId, ?int $userId = null, string $desc = ''): void {
        self::log('APPROVE', $table, $recordId, [], [], $userId, $desc);
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private static function getClientIp(): string {
        foreach (['HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                return explode(',', $_SERVER[$key])[0];
            }
        }
        return '0.0.0.0';
    }
}
