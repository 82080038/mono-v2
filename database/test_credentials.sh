#!/bin/bash
# Test and fix phpMyAdmin credentials

echo "=== phpMyAdmin Credentials Test ==="
echo ""

echo "1. Testing current MySQL connection..."
mysql -u root --password='root' -e "SELECT 'SUCCESS' as Status;" 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✅ Credentials root/root are WORKING"
    echo "phpMyAdmin login: Username: root, Password: root"
else
    echo "❌ Credentials root/root are NOT working"
    
    echo ""
    echo "2. Testing empty password..."
    mysql -u root -e "SELECT 'SUCCESS' as Status;" 2>/dev/null
    if [ $? -eq 0 ]; then
        echo "✅ Empty password is working"
        echo "Need to set password to 'root'"
        
        echo ""
        echo "3. Setting password to 'root'..."
        mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'root'; FLUSH PRIVILEGES;" 2>/dev/null
        if [ $? -eq 0 ]; then
            echo "✅ Password set successfully"
            echo "Now test new credentials..."
            mysql -u root --password='root' -e "SELECT 'SUCCESS' as Status;" 2>/dev/null
            if [ $? -eq 0 ]; then
                echo "✅ NEW credentials root/root are WORKING"
            else
                echo "❌ NEW credentials still failed"
            fi
        else
            echo "❌ Failed to set password"
        fi
    else
        echo "❌ Empty password not working either"
        echo "MySQL authentication issues detected"
    fi
fi

echo ""
echo "4. Testing phpMyAdmin web access..."
curl -s -o /dev/null -w "HTTP Status: %{http_code}\n" http://localhost/phpmyadmin/

echo ""
echo "5. Current MySQL status:"
echo "8208" | sudo -S /opt/lampp/lampp status mysql

echo ""
echo "=== Summary ==="
echo "If credentials are working:"
echo "- Open: http://localhost/phpmyadmin/"
echo "- Login: Username: root, Password: root"
echo ""
echo "If credentials are NOT working:"
echo "- Need manual setup via phpMyAdmin interface"
echo "- Follow the setup guide in database/phpmyadmin_setup_guide.sh"
