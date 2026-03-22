-- Final MySQL Fix Script - Web-based Solution
-- Run this in phpMyAdmin SQL tab after logging in with empty password

-- Step 1: Switch from unix_socket to mysql_native_password
ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('root');

-- Step 2: Create additional root user for 127.0.0.1
CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED BY 'root';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1 WITH GRANT OPTION;

-- Step 3: Update authentication strings (backup method)
UPDATE mysql.user SET authentication_string = PASSWORD('root') WHERE User = 'root' AND Host IN ('localhost', '127.0.0.1');

-- Step 4: Ensure plugin is set correctly
UPDATE mysql.user SET plugin = 'mysql_native_password' WHERE User = 'root' AND Host IN ('localhost', '127.0.0.1');

-- Step 5: Apply changes
FLUSH PRIVILEGES;

-- Step 6: Verify the changes
SELECT user, host, plugin, authentication_string FROM mysql.user WHERE user = 'root';

-- Step 7: Test connection
SELECT 'Authentication fix completed!' as Status, USER() as Current_User, NOW() as Timestamp;

-- Step 8: Create database 'gabe'
CREATE DATABASE IF NOT EXISTS `gabe` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Step 9: Verify database
SHOW DATABASES LIKE 'gabe';

-- Step 10: Success message
SELECT 'MySQL fix completed successfully!' as Result, 'Username: root, Password: root' as Credentials;
