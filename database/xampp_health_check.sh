#!/bin/bash
# XAMPP Health Check Script

echo "=========================================="
echo "         XAMPP HEALTH CHECK REPORT        "
echo "=========================================="
echo ""

# Function to check status
check_status() {
    if [ $? -eq 0 ]; then
        echo "✅ $1"
        return 0
    else
        echo "❌ $1"
        return 1
    fi
}

echo "🔧 XAMPP Services Status:"
echo "8208" | sudo -S /opt/lampp/lampp status | grep -E "(Apache|MySQL|ProFTPD)" | while read line; do
    if echo "$line" | grep -q "running"; then
        echo "✅ $line"
    else
        echo "❌ $line"
    fi
done

echo ""
echo "🌐 Web Server Access:"
curl -s -o /dev/null -w "Main page: %{http_code}\n" http://localhost/ | grep -q "200\|302" && echo "✅ Main page accessible" || echo "❌ Main page not accessible"
curl -s -o /dev/null -w "phpMyAdmin: %{http_code}\n" http://localhost/phpmyadmin/ | grep -q "200" && echo "✅ phpMyAdmin accessible" || echo "❌ phpMyAdmin not accessible"
curl -s -o /dev/null -w "Application: %{http_code}\n" http://localhost/mono-v2/ | grep -q "200" && echo "✅ Application accessible" || echo "❌ Application not accessible"

echo ""
echo "🔌 Port Status:"
netstat -tlnp 2>/dev/null | grep -E ":80|:3306|:21" | while read line; do
    port=$(echo $line | awk '{print $4}' | cut -d':' -f2)
    service=$(echo $line | awk '{print $7}')
    case $port in
        80) echo "✅ Port 80 (Apache): $service" ;;
        3306) echo "✅ Port 3306 (MySQL): $service" ;;
        21) echo "✅ Port 21 (FTP): $service" ;;
    esac
done

echo ""
echo "💾 Database Connection:"
mysqladmin ping 2>/dev/null && echo "✅ MySQL responding" || echo "❌ MySQL not responding"

echo ""
echo "📊 Resource Usage:"
echo "Memory: $(free -h | grep Mem | awk '{print $3"/"$2" " ("$3"/"$2")}')"
echo "Disk: $(df -h /opt/lampp | tail -1 | awk '{print $3"/"$2" " ("$5")}')"

echo ""
echo "🔍 Issues Found:"
echo "8208" | sudo -S tail -5 /opt/lampp/var/mysql/*.err 2>/dev/null | grep -i error | head -3 | while read line; do
    echo "⚠️  MySQL: $line"
done

echo "8208" | sudo -S tail -5 /opt/lampp/logs/error_log 2>/dev/null | grep -i error | head -3 | while read line; do
    echo "⚠️  Apache: $line"
done

echo ""
echo "=========================================="
echo "           HEALTH CHECK COMPLETE           "
echo "=========================================="
echo ""

# Overall status summary
if curl -s -o /dev/null -w "%{http_code}" http://localhost/phpmyadmin/ | grep -q "200"; then
    echo "🎯 OVERALL STATUS: ✅ XAMPP is working with minor issues"
else
    echo "🚨 OVERALL STATUS: ❌ XAMPP has significant problems"
fi

echo ""
echo "📋 Recommendations:"
echo "1. If MySQL authentication fails: Use phpMyAdmin web interface"
echo "2. If SSL warnings appear: Ignore for development environment"
echo "3. If ports are blocked: Check firewall settings"
echo "4. If services fail to start: Check log files for details"
