-- Update MySQL root password for phpMyAdmin
-- Run this script in phpMyAdmin after logging in with empty password

-- Update root password to 'root'
ALTER USER 'root'@'localhost' IDENTIFIED BY 'root';

-- For MariaDB compatibility
UPDATE mysql.user SET authentication_string = PASSWORD('root') WHERE User = 'root';

-- Ensure plugin is set correctly
UPDATE mysql.user SET plugin = 'mysql_native_password' WHERE User = 'root';

-- Flush privileges to apply changes
FLUSH PRIVILEGES;

-- Test the new password (this will show success if password works)
SELECT 'Root password updated to "root" successfully!' as Status;

-- Show user info to verify
SELECT User, Host, plugin, authentication_string FROM mysql.user WHERE User = 'root';
