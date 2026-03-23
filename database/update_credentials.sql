-- Update MySQL Credentials to root/root
-- Run this in phpMyAdmin SQL tab

-- Step 1: Update root password for localhost
ALTER USER 'root'@'localhost' IDENTIFIED BY 'root';

-- Step 2: Create/update root user for 127.0.0.1
CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED BY 'root';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION;

-- Step 3: Apply changes
FLUSH PRIVILEGES;

-- Step 4: Verify the update
SELECT user, host, plugin FROM mysql.user WHERE user = 'root';

-- Step 5: Test connection
SELECT 'Credentials updated successfully!' as Status, USER() as Current_User, NOW() as Timestamp;

-- Step 6: Create database 'gabe'
CREATE DATABASE IF NOT EXISTS `gabe` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Step 7: Verify database
SHOW DATABASES LIKE 'gabe';

-- Step 8: Final success message
SELECT 'MySQL credentials updated to root/root!' as Result, 'Database gabe created!' as Database_Status;
