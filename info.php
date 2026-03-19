<?php
/**
 * Development Environment Information
 * This file provides debugging and system information for development
 */

// Disable in production
if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
    die('Access denied in production environment');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Development Info - KSP Lam Gabe Jaya</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .info-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .info-section h3 { margin-top: 0; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f5f5f5; }
        .status-ok { color: green; }
        .status-error { color: red; }
        .status-warning { color: orange; }
    </style>
</head>
<body>
    <h1>🔧 Development Environment Information</h1>
    
    <div class="info-section">
        <h3>🖥️ System Information</h3>
        <table>
            <tr><th>PHP Version</th><td><?php echo phpversion(); ?></td></tr>
            <tr><th>Server Software</th><td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td></tr>
            <tr><th>Document Root</th><td><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></td></tr>
            <tr><th>Current Directory</th><td><?php echo __DIR__; ?></td></tr>
            <tr><th>Server Time</th><td><?php echo date('Y-m-d H:i:s'); ?></td></tr>
        </table>
    </div>
    
    <div class="info-section">
        <h3>🗄️ Database Connection</h3>
        <?php
        try {
            require_once '../config/Config.php';
            $config = Config::getInstance();
            $db = $config->getDatabase();
            
            $pdo = new PDO(
                "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4",
                $db['user'],
                $db['password']
            );
            
            echo '<p class="status-ok">✅ Database connection successful</p>';
            
            // Check tables
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo '<p><strong>Tables found:</strong> ' . implode(', ', $tables) . '</p>';
            
        } catch (Exception $e) {
            echo '<p class="status-error">❌ Database connection failed: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>
    
    <div class="info-section">
        <h3>📁 Directory Permissions</h3>
        <?php
        $directories = ['logs', 'uploads', 'uploads/avatars', 'uploads/documents', 'backups'];
        foreach ($directories as $dir) {
            $path = "../$dir";
            if (is_dir($path)) {
                $writable = is_writable($path);
                $status = $writable ? 'status-ok' : 'status-error';
                $icon = $writable ? '✅' : '❌';
                echo "<p class='$status'>$icon $dir: " . ($writable ? 'Writable' : 'Not writable') . "</p>";
            } else {
                echo "<p class='status-warning'>⚠️ $dir: Directory does not exist</p>";
            }
        }
        ?>
    </div>
    
    <div class="info-section">
        <h3>⚙️ Configuration</h3>
        <?php
        $configFiles = ['.env.example', '../config/Config.php'];
        foreach ($configFiles as $file) {
            if (file_exists($file)) {
                echo "<p class='status-ok'>✅ $file exists</p>";
            } else {
                echo "<p class='status-error'>❌ $file missing</p>";
            }
        }
        ?>
    </div>
    
    <div class="info-section">
        <h3>🔗 API Endpoints</h3>
        <p>Available API endpoints:</p>
        <ul>
            <li><a href="../api/members.php?action=get_member_types">Member Types</a></li>
            <li><a href="../api/savings.php?action=get_account_types">Account Types</a></li>
            <li><a href="../api/loans.php?action=get_loan_types">Loan Types</a></li>
        </ul>
    </div>
    
    <div class="info-section">
        <h3>📝 Development Notes</h3>
        <ul>
            <li>Remember to copy <code>.env.example</code> to <code>.env</code> and update with your database credentials</li>
            <li>Ensure all directories in <code>uploads/</code> are writable</li>
            <li>Check database connection before running the application</li>
            <li>Use this file for debugging during development</li>
        </ul>
    </div>
    
</body>
</html>
