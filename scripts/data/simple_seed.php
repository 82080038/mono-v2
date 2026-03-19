<?php
/**
 * Simple Database Seeder
 * Creates basic tables and sample data for testing
 */

// Database configuration
$host = 'localhost';
$dbname = 'ksp_lamgabejaya';
$username = 'root';
$password = '';
$socket = '/opt/lampp/var/mysql/mysql.sock';

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=localhost;unix_socket=$socket", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to MySQL\n";
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    echo "Database created/verified\n";
    
    // Select database
    $pdo->exec("USE $dbname");
    
    // Create tables
    $createTables = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(50) NOT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            name VARCHAR(255) NOT NULL,
            member_number VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(255),
            phone VARCHAR(20),
            address TEXT,
            join_date DATE,
            membership_level ENUM('bronze', 'silver', 'gold') DEFAULT 'bronze',
            credit_score INT DEFAULT 500,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS loans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            member_id INT,
            loan_number VARCHAR(50) UNIQUE NOT NULL,
            loan_amount DECIMAL(12,2) NOT NULL,
            interest_rate DECIMAL(5,4) NOT NULL,
            loan_term INT NOT NULL,
            outstanding_balance DECIMAL(12,2) NOT NULL,
            next_payment_date DATE,
            last_payment_date DATE,
            status ENUM('active', 'paid', 'defaulted') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS savings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            member_id INT,
            account_number VARCHAR(50) UNIQUE NOT NULL,
            account_type ENUM('savings', 'time_deposit') DEFAULT 'savings',
            balance DECIMAL(12,2) DEFAULT 0,
            interest_rate DECIMAL(5,4) DEFAULT 0.05,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            account_id INT,
            transaction_type ENUM('deposit', 'withdrawal', 'loan_payment') NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            description TEXT,
            transaction_date DATE NOT NULL,
            status ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS collection_queue (
            id INT AUTO_INCREMENT PRIMARY KEY,
            member_id INT,
            assigned_collector_id INT,
            amount_due DECIMAL(12,2) NOT NULL,
            due_date DATE NOT NULL,
            next_attempt_date DATE,
            status ENUM('pending', 'completed', 'skipped') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS field_data (
            id INT AUTO_INCREMENT PRIMARY KEY,
            member_id INT,
            mantri_id INT,
            location VARCHAR(255),
            gps_coordinates VARCHAR(100),
            visit_date DATE NOT NULL,
            visit_time TIME NOT NULL,
            purpose ENUM('collection', 'survey', 'verification') NOT NULL,
            status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS gps_tracking (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            latitude DECIMAL(10,8) NOT NULL,
            longitude DECIMAL(11,8) NOT NULL,
            address VARCHAR(255),
            accuracy INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];
    
    foreach ($createTables as $sql) {
        $pdo->exec($sql);
        echo "Table created: " . substr($sql, 0, 30) . "...\n";
    }
    
    // Insert sample data
    $insertUsers = [
        "INSERT INTO users (name, email, password, role) VALUES 
        ('Super Admin', 'test_super_admin@lamabejaya.coop', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'super_admin'),
        ('Admin User', 'test_admin@lamabejaya.coop', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'admin'),
        ('Mantri User', 'test_mantri@lamabejaya.coop', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'mantri'),
        ('Member User', 'test_member@lamabejaya.coop', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'member'),
        ('Kasir User', 'test_kasir@lamabejaya.coop', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'kasir'),
        ('Teller User', 'test_teller@lamabejaya.coop', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'teller'),
        ('Surveyor User', 'test_surveyor@lamabejaya.coop', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'surveyor'),
        ('Collector User', 'test_collector@lamabejaya.coop', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'collector')"
    ];
    
    foreach ($insertUsers as $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            // Ignore duplicate entries
            if (strpos($e->getMessage(), 'Duplicate') === false) {
                echo "Error inserting user: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "Users seeded\n";
    
    // Insert sample members
    $insertMembers = [
        "INSERT INTO members (user_id, name, member_number, email, phone, address, join_date, membership_level, credit_score) VALUES 
        (4, 'Budi Santoso', 'M001', 'budi.santoso@email.com', '08123456789', 'Jl. Merdeka No. 45, Jakarta Pusat', '2023-01-15', 'gold', 750),
        (4, 'Siti Rahayu', 'M002', 'siti.rahayu@email.com', '08123456790', 'Jl. Sudirman No. 67, Jakarta Selatan', '2023-02-20', 'silver', 680),
        (4, 'Ahmad Wijaya', 'M003', 'ahmad.wijaya@email.com', '08123456791', 'Jl. Thamrin No. 89, Jakarta Pusat', '2023-03-10', 'bronze', 620)"
    ];
    
    foreach ($insertMembers as $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') === false) {
                echo "Error inserting member: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "Members seeded\n";
    
    // Insert sample loans
    $insertLoans = [
        "INSERT INTO loans (member_id, loan_number, loan_amount, interest_rate, loan_term, outstanding_balance, next_payment_date, last_payment_date, status) VALUES 
        (1, 'LN-2023-001', 5000000, 0.02, 12, 3500000, DATE_SUB(CURRENT_DATE, INTERVAL 5 DAY), DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY), 'active'),
        (2, 'LN-2023-002', 3000000, 0.025, 6, 1200000, DATE_SUB(CURRENT_DATE, INTERVAL 2 DAY), DATE_SUB(CURRENT_DATE, INTERVAL 15 DAY), 'active')"
    ];
    
    foreach ($insertLoans as $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') === false) {
                echo "Error inserting loan: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "Loans seeded\n";
    
    // Insert sample savings
    $insertSavings = [
        "INSERT INTO savings (member_id, account_number, account_type, balance, interest_rate, status) VALUES 
        (1, 'SA001', 'savings', 10000000, 0.05, 'active'),
        (2, 'SA002', 'savings', 7500000, 0.05, 'active')"
    ];
    
    foreach ($insertSavings as $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') === false) {
                echo "Error inserting savings: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "Savings seeded\n";
    
    // Insert sample transactions
    $insertTransactions = [
        "INSERT INTO transactions (account_id, transaction_type, amount, description, transaction_date, status) VALUES 
        (1, 'deposit', 1000000, 'Monthly savings', CURRENT_DATE, 'completed'),
        (1, 'withdrawal', 500000, 'Cash withdrawal', DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY), 'completed'),
        (1, 'loan_payment', 1500000, 'Monthly installment', DATE_SUB(CURRENT_DATE, INTERVAL 2 DAY), 'completed')"
    ];
    
    foreach ($insertTransactions as $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') === false) {
                echo "Error inserting transaction: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "Transactions seeded\n";
    
    // Insert sample collection queue
    $insertCollectionQueue = [
        "INSERT INTO collection_queue (member_id, assigned_collector_id, amount_due, due_date, next_attempt_date, status) VALUES 
        (1, 8, 1500000, DATE_SUB(CURRENT_DATE, INTERVAL 5 DAY), CURRENT_DATE, 'pending'),
        (2, 8, 750000, DATE_SUB(CURRENT_DATE, INTERVAL 2 DAY), DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY), 'pending')"
    ];
    
    foreach ($insertCollectionQueue as $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') === false) {
                echo "Error inserting collection queue: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "Collection queue seeded\n";
    
    // Insert sample field data
    $insertFieldData = [
        "INSERT INTO field_data (member_id, mantri_id, location, gps_coordinates, visit_date, visit_time, purpose, status, notes) VALUES 
        (1, 3, 'Jakarta Pusat', '-6.2088, 106.8456', CURRENT_DATE, '09:30:00', 'collection', 'completed', 'Member paid on time'),
        (2, 3, 'Jakarta Utara', '-6.1384, 106.8759', CURRENT_DATE, '11:00:00', 'survey', 'pending', 'New loan application')"
    ];
    
    foreach ($insertFieldData as $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') === false) {
                echo "Error inserting field data: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "Field data seeded\n";
    
    // Insert sample GPS tracking
    $insertGpsTracking = [
        "INSERT INTO gps_tracking (user_id, latitude, longitude, address, accuracy) VALUES 
        (3, -6.2088, 106.8456, 'Jakarta Pusat, Indonesia', 10),
        (3, -6.1384, 106.8759, 'Jakarta Utara, Indonesia', 15)"
    ];
    
    foreach ($insertGpsTracking as $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') === false) {
                echo "Error inserting GPS tracking: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "GPS tracking seeded\n";
    
    echo "\n✅ Database seeding completed successfully!\n";
    echo "📊 Sample data created for testing\n";
    echo "🔑 Test users ready for login\n";
    echo "🌐 Access application at: http://localhost/mono\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

?>
