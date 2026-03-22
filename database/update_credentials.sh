#!/bin/bash
# Update MySQL Credentials Script
# Target: Username: root, Password: root

echo "============================================"
echo "      UPDATE MYSQL CREDENTIALS SCRIPT"
echo "============================================"
echo ""

echo "🎯 Target Credentials:"
echo "Username: root"
echo "Password: root"
echo ""

echo "🌐 Step 1: Access phpMyAdmin"
echo "Open browser: http://localhost/phpmyadmin/"
echo "Login with current credentials (try empty password first)"
echo ""

echo "📝 Step 2: Update Credentials SQL"
echo "After logging in, click 'SQL' tab and run:"
echo ""
echo "-- Main credentials update"
echo "ALTER USER 'root'@'localhost' IDENTIFIED BY 'root';"
echo ""
echo "-- Additional user for compatibility"
echo "CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED BY 'root';"
echo "GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION;"
echo ""
echo "-- Apply changes"
echo "FLUSH PRIVILEGES;"
echo ""

echo "✅ Step 3: Create Database 'gabe'"
echo "After credentials update, run:"
echo "CREATE DATABASE IF NOT EXISTS \`gabe\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo ""

echo "🧪 Step 4: Test New Credentials"
echo "1. Logout from phpMyAdmin"
echo "2. Login with: Username: root, Password: root"
echo "3. Verify database 'gabe' exists"
echo ""

echo "📋 Step 5: Command Line Test"
echo "Test in terminal:"
echo "mysql -u root --password='root' -e 'SELECT \"SUCCESS! Credentials updated!\";'"
echo ""

echo "🔧 Step 6: Application Configuration"
echo "Update application config files:"
echo "config/Config.php"
echo "helpers/DatabaseHelper.php"
echo "Any other database connection files"
echo ""

echo "============================================"
echo "        CREDENTIALS UPDATE GUIDE"
echo "============================================"
echo ""

echo "📂 Quick SQL Commands (copy-paste):"
echo "ALTER USER 'root'@'localhost' IDENTIFIED BY 'root';"
echo "CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED BY 'root';"
echo "GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION;"
echo "FLUSH PRIVILEGES;"
echo "CREATE DATABASE IF NOT EXISTS \`gabe\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo ""

echo "🎯 Success Criteria:"
echo "✅ phpMyAdmin login: root/root"
echo "✅ Command line access: mysql -u root -p'root'"
echo "✅ Database 'gabe' created"
echo "✅ Application can connect"
