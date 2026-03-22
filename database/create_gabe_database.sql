-- Create database 'gabe' for KSP Lam Gabe Jaya
-- Run this script in phpMyAdmin: http://localhost/phpmyadmin/

CREATE DATABASE IF NOT EXISTS `gabe` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Verify database creation
SHOW DATABASES LIKE 'gabe';

-- Use the database
USE `gabe`;

-- Create a test table to verify database is working
CREATE TABLE IF NOT EXISTS `test_table` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `message` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert test data
INSERT INTO `test_table` (`message`) VALUES 
('Database gabe created successfully'),
('KSP Lam Gabe Jaya - Ready for development');

-- Verify test data
SELECT * FROM `test_table`;

-- Show database info
SELECT 
    SCHEMA_NAME as 'Database',
    DEFAULT_CHARACTER_SET_NAME as 'Charset',
    DEFAULT_COLLATION_NAME as 'Collation'
FROM information_schema.SCHEMATA 
WHERE SCHEMA_NAME = 'gabe';

-- Success message
SELECT 'Database "gabe" created and ready for use!' as Status;
