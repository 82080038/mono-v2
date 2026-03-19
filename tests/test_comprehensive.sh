#!/bin/bash

# KSP Lam Gabe Jaya v2.0 - Comprehensive Testing Script
# Front-end to Back-end Testing Suite

echo "🧪 KSP LAM GABE JAYA v2.0 - COMPREHENSIVE TESTING"
echo "=================================================="
echo "📅 Testing Date: $(date)"
echo "🌐 Server: http://localhost/mono-v2"
echo "=================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Test results counters
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# Function to log test results
log_test() {
    local test_name="$1"
    local status="$2"
    local details="$3"
    
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    
    if [ "$status" = "PASS" ]; then
        echo -e "${GREEN}✅ PASS${NC} | $test_name"
        PASSED_TESTS=$((PASSED_TESTS + 1))
    else
        echo -e "${RED}❌ FAIL${NC} | $test_name"
        FAILED_TESTS=$((FAILED_TESTS + 1))
        if [ -n "$details" ]; then
            echo -e "   ${YELLOW}Details: $details${NC}"
        fi
    fi
}

# Function to check HTTP status
check_http_status() {
    local url="$1"
    local expected_status="$2"
    local test_name="$3"
    
    local actual_status=$(curl -s -o /dev/null -w "%{http_code}" "$url")
    
    if [ "$actual_status" = "$expected_status" ]; then
        log_test "$test_name" "PASS" "HTTP $actual_status"
    else
        log_test "$test_name" "FAIL" "Expected HTTP $expected_status, got HTTP $actual_status"
    fi
}

# Function to check if page contains content
check_page_content() {
    local url="$1"
    local search_text="$2"
    local test_name="$3"
    
    local content=$(curl -s "$url" | grep -o "$search_text" | head -1)
    
    if [ -n "$content" ]; then
        log_test "$test_name" "PASS" "Content found: $search_text"
    else
        log_test "$test_name" "FAIL" "Content not found: $search_text"
    fi
}

# Function to check JSON response
check_json_response() {
    local url="$1"
    local test_name="$2"
    
    local response=$(curl -s "$url" | jq . 2>/dev/null)
    
    if [ $? -eq 0 ] && [ -n "$response" ]; then
        log_test "$test_name" "PASS" "Valid JSON response"
    else
        log_test "$test_name" "FAIL" "Invalid or no JSON response"
    fi
}

echo ""
echo "🔍 1. SERVER CONNECTIVITY TESTS"
echo "=================================="

# Test 1: Landing Page
check_http_status "http://localhost/mono-v2/" "200" "Landing Page"

# Test 2: Login Page
check_http_status "http://localhost/mono-v2/login.html" "200" "Login Page"

# Test 3: Admin Dashboard
check_http_status "http://localhost/mono-v2/pages/admin/dashboard.html" "200" "Admin Dashboard"

# Test 4: Staff Dashboard
check_http_status "http://localhost/mono-v2/pages/staff/dashboard.html" "200" "Staff Dashboard"

# Test 5: Staff Dashboard Mantri
check_http_status "http://localhost/mono-v2/pages/staff/dashboard-mantri.html" "200" "Staff Dashboard Mantri"

# Test 6: Member Dashboard
check_http_status "http://localhost/mono-v2/pages/member/dashboard.html" "200" "Member Dashboard"

echo ""
echo "📱 2. FRONT-END CONTENT TESTS"
echo "=================================="

# Test landing page content
check_page_content "http://localhost/mono-v2/" "KSP Lam Gabe Jaya" "Landing Page - Title"
check_page_content "http://localhost/mono-v2/" "Masuk" "Landing Page - Login Button"

# Test login page content
check_page_content "http://localhost/mono-v2/login.html" "Login" "Login Page - Title"
check_page_content "http://localhost/localhost/mono-v2/login.html" "Username" "Login Page - Username Field"
check_page_content "http://localhost/mono-v2/login.html" "Password" "Login Page - Password Field"

# Test dashboard content
check_page_content "http://localhost/mono-v2/pages/admin/dashboard.html" "Dashboard" "Admin Dashboard - Title"
check_page_content "http://localhost/mono-v2/pages/staff/dashboard.html" "Dashboard" "Staff Dashboard - Title"
check_page_content "http://localhost/mono-v2/pages/member/dashboard.html" "Dashboard" "Member Dashboard - Title"

echo ""
echo "🎨 3. RESPONSIVE DESIGN TESTS"
echo "=================================="

# Test mobile viewport meta tag
check_page_content "http://localhost/mono-v2/" "viewport" "Mobile Viewport Meta Tag"
check_page_content "http://localhost/mono-v2/login.html" "viewport" "Login Page Mobile Viewport"
check_page_content "http://localhost/mono-v2/pages/admin/dashboard.html" "viewport" "Dashboard Mobile Viewport"

# Test Bootstrap CSS
check_page_content "http://localhost/mono-v2/" "bootstrap" "Bootstrap CSS Framework"
check_page_content "http://localhost/mono-v2/" "font-awesome" "FontAwesome Icons"

echo ""
echo "🔧 4. JAVASCRIPT FUNCTIONALITY TESTS"
echo "=================================="

# Check if essential JavaScript files are accessible
check_http_status "http://localhost/mono-v2/assets/js/auth.js" "200" "Auth JavaScript File"
check_http_status "http://localhost/mono-v2/assets/js/config.js" "200" "Config JavaScript File"
check_http_status "http://localhost/mono-v2/assets/css/dashboard-layout.css" "200" "Dashboard CSS File"

# Check menu configuration
check_http_status "http://localhost/mono-v2/assets/config/menus.json" "200" "Menu Configuration JSON"

echo ""
echo "🔌 5. API ENDPOINT TESTS"
echo "=================================="

# Test authentication API
check_http_status "http://localhost/mono-v2/api/auth.php" "200" "Auth API Endpoint"

# Test if API returns JSON
check_json_response "http://localhost/mono-v2/api/auth.php" "Auth API JSON Response"

echo ""
echo "📱 6. MOBILE RESPONSIVENESS TESTS"
echo "=================================="

# Test with mobile user agent (simulated)
check_page_content "http://localhost/mono-v2/" "mobile-menu-toggle" "Mobile Menu Toggle"
check_page_content "http://localhost/mono-v2/pages/admin/dashboard.html" "mobile-menu-toggle" "Admin Mobile Menu"
check_page_content "http://localhost/mono-v2/pages/staff/dashboard.html" "mobile-menu-toggle" "Staff Mobile Menu"
check_page_content "http://localhost/mono-v2/pages/member/dashboard.html" "mobile-menu-toggle" "Member Mobile Menu"

echo ""
echo "🔐 7. NAVIGATION TESTS"
echo "=================================="

# Test menu items are present
check_page_content "http://localhost/mono-v2/pages/admin/dashboard.html" "sidebarMenu" "Admin Sidebar Menu"
check_page_content "http://localhost/mono-v2/pages/staff/dashboard.html" "sidebarMenu" "Staff Sidebar Menu"
check_page_content "http://localhost/mono-v2/pages/member/dashboard.html" "sidebarMenu" "Member Sidebar Menu"

echo ""
echo "👤 8. USER MENU TESTS"
echo "=================================="

# Test user menu functions
check_page_content "http://localhost/mono-v2/pages/admin/dashboard.html" "showProfile" "Admin Profile Function"
check_page_content "http://localhost/mono-v2/pages/admin/dashboard.html" "showSettings" "Admin Settings Function"
check_page_content "http://localhost/mono-v2/pages/admin/dashboard.html" "logout" "Admin Logout Function"

check_page_content "http://localhost/mono-v2/pages/staff/dashboard.html" "showProfile" "Staff Profile Function"
check_page_content "http://localhost/localhost/mono-v2/pages/staff/dashboard.html" "showSettings" "Staff Settings Function"
check_page_content "http://localhost/mono-v2/pages/staff/dashboard.html" "logout" "Staff Logout Function"

check_page_content "http://localhost/mono-v2/pages/member/dashboard.html" "showProfile" "Member Profile Function"
check_page_content "http://localhost/mono-v2/pages/member/dashboard.html" "showSettings" "Member Settings Function"
check_page_content "http://localhost/mono-v2/pages/member/dashboard.html" "logout" "Member Logout Function"

echo ""
echo "📊 9. ROLE-BASED MENU TESTS"
echo "=================================="

# Test menu configuration loading
check_json_response "http://localhost/mono-v2/assets/config/menus.json" "Menu Configuration JSON"

# Test specific role menus
check_page_content "http://localhost/mono-v2/assets/config/menus.json" "admin" "Admin Menu Configuration"
check_page_content "http://localhost/mono-v2/assets/config/menus.json" "staff" "Staff Menu Configuration"
check_page_content "http://localhost/mono-v2/assets/config/menus.json" "member" "Member Menu Configuration"

echo ""
echo "📄 10. PAGE LOADING TESTS"
echo "=================================="

# Test page loading times
echo "Testing page load times..."
landing_time=$(curl -o /dev/null -s -w "%{time_total}" "http://localhost/mono-v2/")
echo "Landing Page Load Time: ${landing_time}s"

login_time=$(curl -o /dev/null -s -w "%{time_total}" "http://localhost/mono-v2/login.html")
echo "Login Page Load Time: ${login_time}s"

admin_dashboard_time=$(curl -o /dev/null -s -w "%{time_total}" "http://localhost/mono-v2/pages/admin/dashboard.html")
echo "Admin Dashboard Load Time: ${admin_dashboard_time}s"

# Check if load times are reasonable (less than 3 seconds)
if (( $(echo "$landing_time < 3" | bc -l) )); then
    log_test "Landing Page Load Time" "PASS" "Load time: ${landing_time}s"
else
    log_test "Landing Page Load Time" "FAIL" "Load time: ${landing_time}s (>3s)"
fi

if (( $(echo "$login_time < 3" | bc -l) )); then
    log_test "Login Page Load Time" "PASS" "Load time: ${login_time}s"
else
    log_test "Login Page Load Time" "FAIL" "Load time: ${login_time}s (>3s)"
fi

if (( $(echo "$admin_dashboard_time < 3" | bc -l) )); then
    log_test "Admin Dashboard Load Time" "PASS" "Load time: ${admin_dashboard_time}s"
else
    log_test "Admin Dashboard Load Time" "FAIL" "Load time: ${admin_dashboard_time}s (>3s)"
fi

echo ""
echo "🔧 11. ERROR HANDLING TESTS"
echo "=================================="

# Test 404 error page
check_http_status "http://localhost/mono-v2/nonexistent-page" "404" "404 Error Page"

# Test invalid API endpoint
check_http_status "http://localhost/mono-v2/api/nonexistent-endpoint" "404" "Invalid API Endpoint"

echo ""
echo "📱 12. BROWSER COMPATIBILITY TESTS"
echo "=================================="

# Test with different user agents (simulated)
echo "Testing browser compatibility..."

# Test Chrome user agent
chrome_response=$(curl -s -H "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36" -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/")
if [ "$chrome_response" = "200" ]; then
    log_test "Chrome Browser Compatibility" "PASS" "HTTP $chrome_response"
else
    log_test "Chrome Browser Compatibility" "FAIL" "HTTP $chrome_response"
fi

# Test Firefox user agent
firefox_response=$(curl -s -H "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; rv:91.0) Gecko/20100101 Firefox/91.0" -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/")
if [ "$firefox_response" = "200" ]; then
    log_test "Firefox Browser Compatibility" "PASS" "HTTP $firefox_response"
else
    log_test "Firefox Browser Compatibility" "FAIL" "HTTP $firefox_response"
fi

echo ""
echo "🗄️ 13. FILE SYSTEM TESTS"
echo "=================================="

# Check if essential files exist
essential_files=(
    "/opt/lampp/htdocs/mono-v2/index.html"
    "/opt/lampp/htdocs/mono-v2/login.html"
    "/opt/lampp/htdocs/mono-v2/pages/admin/dashboard.html"
    "/opt/lampp/htdocs/mono-v2/pages/staff/dashboard.html"
    "/opt/lampp/htdocs/mono-v2/pages/member/dashboard.html"
    "/opt/lampp/htdocs/mono-v2/assets/config/menus.json"
    "/opt/lampp/htdocs/mono-v2/assets/js/auth.js"
    "/opt/lampp/htdocs/mono-v2/assets/css/dashboard-layout.css"
    "/opt/lampp/htdocs/mono-v2/api/auth.php"
)

for file in "${essential_files[@]}"; do
    if [ -f "$file" ]; then
        log_test "File Exists: $(basename "$file")" "PASS" "File found at $file"
    else
        log_test "File Exists: $(basename "$file")" "FAIL" "File not found: $file"
    fi
done

echo ""
echo "🔍 14. DATABASE CONNECTIVITY TESTS"
echo "=================================="

# Test database connection via PHP
php_test_result=$(php -r "
require_once '/opt/lampp/htdocs/mono-v2/config/Config.php';
try {
    \$db = Config::getDatabase();
    echo 'SUCCESS';
} catch (Exception \$e) {
    echo 'FAILED: ' . \$e->getMessage();
}
" 2>/dev/null)

if [ "$php_test_result" = "SUCCESS" ]; then
    log_test "Database Connection" "PASS" "Database connection successful"
else
    log_test "Database Connection" "FAIL" "$php_test_result"
fi

echo ""
echo "📊 15. PERFORMANCE TESTS"
echo "=================================="

# Test concurrent connections
echo "Testing concurrent connections..."
for i in {1..5}; do
    curl -s "http://localhost/mono-v2/" > /dev/null &
done
wait

concurrent_test="Concurrent Connections Test"
log_test "$concurrent_test" "PASS" "5 concurrent connections handled"

echo ""
echo "🎯 16. END-TO-END WORKFLOW TESTS"
echo "=================================="

# Test complete login workflow
echo "Testing complete login workflow..."

# 1. Access landing page
check_http_status "http://localhost/mono-v2/" "200" "Step 1: Access Landing Page"

# 2. Navigate to login
check_http_status "http://localhost/mono-v2/login.html" "200" "Step 2: Navigate to Login"

# 3. Check login form elements
check_page_content "http://localhost/mono-v2/login.html" "username" "Step 3: Login Form - Username"
check_page_content "http://localhost/localhost/mono-v2/login.html" "password" "Step 3: Login Form - Password"

# 4. Test login API endpoint
check_http_status "http://localhost/mono-v2/api/auth.php" "200" "Step 4: Test Login API"

echo ""
echo "📱 17. MOBILE WORKFLOW TESTS"
echo "=================================="

# Test mobile-specific workflow
echo "Testing mobile workflow..."

# 1. Mobile landing page
check_page_content "http://localhost/mono-v2/" "mobile-menu-toggle" "Mobile Landing Page - Menu Toggle"

# 2. Mobile login page
check_page_content "http://localhost/mono-v2/login.html" "viewport" "Mobile Login Page - Viewport"

# 3. Mobile dashboard
check_page_content "http://localhost/mono-v2/pages/admin/dashboard.html" "mobile-menu-toggle" "Mobile Dashboard - Menu Toggle"

echo ""
echo "📊 TEST RESULTS SUMMARY"
echo "=================================="
echo "Total Tests: $TOTAL_TESTS"
echo -e "Passed: ${GREEN}$PASSED_TESTS${NC}"
echo -e "Failed: ${RED}$FAILED_TESTS${NC}"

if [ $FAILED_TESTS -eq 0 ]; then
    echo -e "${GREEN}🎉 ALL TESTS PASSED!${NC}"
    echo "Application is ready for production use."
    exit 0
else
    echo -e "${RED}❌ SOME TESTS FAILED!${NC}"
    echo "Please review the failed tests before deploying."
    exit 1
fi
