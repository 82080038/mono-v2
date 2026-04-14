<?php
// Database export script
$host = 'localhost';
$user = 'root';
$pass = 'root';
$dbname = 'ksp_lamgabejaya_v2';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $sql = "-- MariaDB dump\n";
    $sql .= "-- Host: localhost    Database: $dbname\n";
    $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    
    foreach ($tables as $table) {
        // Get CREATE TABLE
        $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $sql .= $row['Create Table'] . ";\n\n";
        
        // Get data
        $stmt = $pdo->query("SELECT * FROM `$table`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            $columns = array_keys($rows[0]);
            $sql .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES\n";
            
            $values = [];
            foreach ($rows as $row) {
                $vals = [];
                foreach ($row as $val) {
                    if ($val === null) {
                        $vals[] = 'NULL';
                    } elseif (is_numeric($val)) {
                        $vals[] = $val;
                    } else {
                        $vals[] = "'" . addslashes($val) . "'";
                    }
                }
                $values[] = '(' . implode(', ', $vals) . ')';
            }
            
            $sql .= implode(",\n", $values) . ";\n\n";
        }
    }
    
    // Save to file
    $outputFile = __DIR__ . '/database/ksp_lamgabejaya_v2_exported.sql';
    file_put_contents($outputFile, $sql);
    
    echo "Database exported successfully to: $outputFile\n";
    echo "Tables exported: " . count($tables) . "\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
