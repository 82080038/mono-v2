#!/bin/bash
# Script to set MySQL root password to 'root' for phpMyAdmin

echo "=== Set phpMyAdmin Credentials ==="
echo "Username: root"
echo "Password: root"
echo ""

echo "Step 1: Stop MySQL service"
echo "8208" | sudo -S /opt/lampp/lampp stop mysql
sleep 3

echo ""
echo "Step 2: Start MySQL in safe mode (skip grant tables)"
echo "8208" | sudo -S /opt/lampp/sbin/mysqld --datadir=/opt/lampp/var/mysql --user=mysql --skip-grant-tables --skip-networking &
sleep 5

echo ""
echo "Step 3: Update root password"
echo "8208" | sudo -S mysql --socket=/opt/lampp/var/mysql/mysql.sock -u root -e "USE mysql; UPDATE user SET authentication_string=PASSWORD('root') WHERE User='root'; UPDATE user SET plugin='mysql_native_password' WHERE User='root'; FLUSH PRIVILEGES;" 2>/dev/null

echo ""
echo "Step 4: Stop MySQL safe mode"
echo "8208" | sudo -S pkill mysqld
sleep 3

echo ""
echo "Step 5: Start MySQL normally"
echo "8208" | sudo -S /opt/lampp/lampp start mysql
sleep 5

echo ""
echo "Step 6: Test new credentials"
echo "8208" | sudo -S mysql -u root --password='root' -e "SELECT 'Password updated successfully!' as Status;" 2>/dev/null && echo "✅ Credentials updated successfully" || echo "❌ Failed to update credentials"

echo ""
echo "=== phpMyAdmin Login Information ==="
echo "URL: http://localhost/phpmyadmin/"
echo "Username: root"
echo "Password: root"
echo ""

echo "=== Test phpMyAdmin Access ==="
echo "Open browser and navigate to: http://localhost/phpmyadmin/"
echo "Login with: root / root"
