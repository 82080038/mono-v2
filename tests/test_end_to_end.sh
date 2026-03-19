#!/bin/bash

# KSP Lam Gabe Jaya v2.0 - End-to-End Testing Script
# Complete workflow testing with real API calls

echo "🧪 KSP LAM GABE JAYA v2.0 - END-TO-END TESTING"
echo "=============================================="
echo "📅 Testing Date: $(date)"
echo "🌐 Server: http://localhost/mono-v2"
echo "=============================================="

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

# Function to test API response
test_api_response() {
    local url="$1"
    local method="$2"
    local data="$3"
    local test_name="$4"
    local expected_field="$5"
    local expected_value="$6"
    
    local response
    if [ "$method" = "POST" ]; then
        response=$(curl -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "$data" "$url" 2>/dev/null)
    else
        response=$(curl -s -X GET "$url" 2>/dev/null)
    fi
    
    # Check if response is valid JSON
    if echo "$response" | jq . >/dev/null 2>&1; then
        if [ -n "$expected_field" ]; then
            local actual_value=$(echo "$response" | jq -r ".$expected_field" 2>/dev/null)
            if [ "$actual_value" = "$expected_value" ]; then
                log_test "$test_name" "PASS" "Expected: $expected_value, Got: $actual_value"
            else
                log_test "$test_name" "FAIL" "Expected: $expected_value, Got: $actual_value"
            fi
        else
            log_test "$test_name" "PASS" "Valid JSON response"
        fi
    else
        log_test "$test_name" "FAIL" "Invalid JSON response"
        if [ -n "$response" ]; then
            echo -e "   ${YELLOW}Response: $response${NC}"
        fi
    fi
}

echo ""
echo "🌐 1. API ENDPOINT TESTING"
echo "=================================="

# Test 1: Auth API GET request
test_api_response "http://localhost/mono-v2/api/auth.php" "GET" "" "Auth API GET Request" "" ""

# Test 2: Auth API POST login (valid credentials)
test_api_response "http://localhost/mono-v2/api/auth.php" "POST" "action=login&username=Admin User&password=password" "Login API - Valid Credentials" "success" "true"

# Test 3: Auth API POST login (invalid credentials)
test_api_response "http://localhost/mono-v2/api/auth.php" "POST" "action=login&username=invalid&password=invalid" "Login API - Invalid Credentials" "success" "false"

echo ""
echo "📱 2. FRONT-END TO BACK-END INTEGRATION"
echo "=================================="

# Test 4: Landing page loads correctly
landing_response=$(curl -s "http://localhost/mono-v2/" 2>/dev/null)
if echo "$landing_response" | grep -q "KSP Lam Gabe Jaya"; then
    log_test "Landing Page Integration" "PASS" "Contains app title"
else
    log_test "Landing Page Integration" "FAIL" "Missing app title"
fi

# Test 5: Login page loads correctly
login_response=$(curl -s "http://localhost/mono-v2/login.html" 2>/dev/null)
if echo "$login_response" | grep -q "Masuk ke Sistem"; then
    log_test "Login Page Integration" "PASS" "Contains login title"
else
    log_test "Login Page Integration" "FAIL" "Missing login title"
fi

# Test 6: Login page has required fields
if echo "$login_response" | grep -q "username"; then
    log_test "Login Form - Username Field" "PASS" "Username field present"
else
    log_test "Login Form - Username Field" "FAIL" "Username field missing"
fi

if echo "$login_response" | grep -q "password"; then
    log_test "Login Form - Password Field" "PASS" "Password field present"
else
    log_test "Login Form - Password Field" "FAIL" "Password field missing"
fi

echo ""
echo "👤 3. USER WORKFLOW TESTING"
echo "=================================="

# Test 7: Complete login workflow simulation
echo "Testing complete login workflow..."

# Step 1: Access landing page
landing_status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/")
if [ "$landing_status" = "200" ]; then
    log_test "Login Workflow - Step 1" "PASS" "Access landing page"
else
    log_test "Login Workflow - Step 1" "FAIL" "Failed to access landing page"
fi

# Step 2: Navigate to login page
login_status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/login.html")
if [ "$login_status" = "200" ]; then
    log_test "Login Workflow - Step 2" "PASS" "Navigate to login page"
else
    log_test "Login Workflow - Step 2" "FAIL" "Failed to access login page"
fi

# Step 3: Submit login form
login_result=$(curl -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "action=login&username=Admin User&password=password" "http://localhost/mono-v2/api/auth.php")
if echo "$login_result" | jq -r ".success" 2>/dev/null | grep -q "true"; then
    log_test "Login Workflow - Step 3" "PASS" "Login successful"
    user_role=$(echo "$login_result" | jq -r ".data.user.role" 2>/dev/null)
    echo -e "   ${YELLOW}User Role: $user_role${NC}"
else
    log_test "Login Workflow - Step 3" "FAIL" "Login failed"
    echo -e "   ${YELLOW}Response: $login_result${NC}"
fi

echo ""
echo "📱 4. DASHBOARD ACCESS TESTING"
echo "=================================="

# Test 8: Admin dashboard access
admin_status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/pages/admin/dashboard.html")
if [ "$admin_status" = "200" ]; then
    log_test "Admin Dashboard Access" "PASS" "Admin dashboard accessible"
else
    log_test "Admin Dashboard Access" "FAIL" "Admin dashboard not accessible"
fi

# Test 9: Staff dashboard access
staff_status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/pages/staff/dashboard.html")
if [ "$staff_status" = "200" ]; then
    log_test "Staff Dashboard Access" "PASS" "Staff dashboard accessible"
else
    log_test "Staff Dashboard Access" "FAIL" "Staff dashboard not accessible"
fi

# Test 10: Member dashboard access
member_status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/pages/member/dashboard.html")
if [ "$member_status" = "200" ]; then
    log_test "Member Dashboard Access" "PASS" "Member dashboard accessible"
else
    log_test "Member Dashboard Access" "FAIL" "Member dashboard not accessible"
fi

echo ""
echo "🎨 5. RESPONSIVE DESIGN TESTING"
echo "=================================="

# Test 11: Mobile viewport meta tag
if curl -s "http://localhost/mono-v2/" | grep -q "viewport"; then
    log_test "Mobile Viewport Meta Tag" "PASS" "Viewport meta tag present"
else
    log_test "Mobile Viewport Meta Tag" "FAIL" "Viewport meta tag missing"
fi

# Test 12: Bootstrap CSS framework
if curl -s "http://localhost/mono-v2/" | grep -q "bootstrap"; then
    log_test "Bootstrap CSS Framework" "PASS" "Bootstrap CSS loaded"
else
    log_test "Bootstrap CSS Framework" "FAIL" "Bootstrap CSS not loaded"
fi

# Test 13: FontAwesome icons
if curl -s "http://localhost/mono-v2/" | grep -q "font-awesome"; then
    log_test "FontAwesome Icons" "PASS" "FontAwesome loaded"
else
    log_test "FontAwesome Icons" "FAIL" "FontAwesome not loaded"
fi

echo ""
echo "🔧 6. JAVASCRIPT FUNCTIONALITY TESTING"
echo "=================================="

# Test 14: Essential JavaScript files
auth_js_status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/assets/js/auth.js")
if [ "$auth_js_status" = "200" ]; then
    log_test "Auth JavaScript File" "PASS" "Auth.js accessible"
else
    log_test "Auth JavaScript File" "FAIL" "Auth.js not accessible"
fi

config_js_status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/assets/js/config.js")
if [ "$config_js_status" = "200" ]; then
    log_test "Config JavaScript File" "PASS" "Config.js accessible"
else
    log_test "Config JavaScript File" "FAIL" "Config.js not accessible"
fi

# Test 15: Menu configuration
menu_config=$(curl -s "http://localhost/mono-v2/assets/config/menus.json" 2>/dev/null)
if echo "$menu_config" | jq . >/dev/null 2>&1; then
    log_test "Menu Configuration JSON" "PASS" "Valid JSON menu config"
    admin_menu_count=$(echo "$menu_config" | jq ".admin | length" 2>/dev/null)
    staff_menu_count=$(echo "$menu_config" | jq ".staff | length" 2>/dev/null)
    member_menu_count=$(echo "$menu_config" | jq ".member | length" 2>/dev/null)
    echo -e "   ${YELLOW}Admin menu items: $admin_menu_count${NC}"
    echo -e "   ${YELLOW}Staff menu items: $staff_menu_count${NC}"
    echo -e "   ${YELLOW}Member menu items: $member_menu_count${NC}"
else
    log_test "Menu Configuration JSON" "FAIL" "Invalid JSON menu config"
fi

echo ""
echo "🔐 7. USER MENU FUNCTIONALITY TESTING"
echo "=================================="

# Test 16: User menu functions in admin dashboard
admin_dashboard=$(curl -s "http://localhost/mono-v2/pages/admin/dashboard.html")
if echo "$admin_dashboard" | grep -q "showProfile"; then
    log_test "Admin Profile Function" "PASS" "showProfile function present"
else
    log_test "Admin Profile Function" "FAIL" "showProfile function missing"
fi

if echo "$admin_dashboard" | grep -q "showSettings"; then
    log_test "Admin Settings Function" "PASS" "showSettings function present"
else
    log_test "Admin Settings Function" "FAIL" "showSettings function missing"
fi

if echo "$admin_dashboard" | grep -q "logout"; then
    log_test "Admin Logout Function" "PASS" "logout function present"
else
    log_test "Admin Logout Function" "FAIL" "logout function missing"
fi

echo ""
echo "📊 8. PERFORMANCE TESTING"
echo "=================================="

# Test 17: Page loading times
echo "Testing page load times..."
landing_time=$(curl -o /dev/null -s -w "%{time_total}" "http://localhost/mono-v2/")
login_time=$(curl -o /dev/null -s -w "%{time_total}" "http://localhost/mono-v2/login.html")
admin_time=$(curl -o /dev/null -s -w "%{time_total}" "http://localhost/mono-v2/pages/admin/dashboard.html")

echo "Landing Page: ${landing_time}s"
echo "Login Page: ${login_time}s"
echo "Admin Dashboard: ${admin_time}s"

# Check if load times are reasonable (less than 2 seconds)
if (( $(echo "$landing_time < 2" | bc -l) )); then
    log_test "Landing Page Load Time" "PASS" "Load time: ${landing_time}s"
else
    log_test "Landing Page Load Time" "FAIL" "Load time: ${landing_time}s (>2s)"
fi

if (( $(echo "$login_time < 2" | bc -l) )); then
    log_test "Login Page Load Time" "PASS" "Load time: ${login_time}s"
else
    log_test "Login Page Load Time" "FAIL" "Load time: ${login_time}s (>2s)"
fi

if (( $(echo "$admin_time < 2" | bc -l) )); then
    log_test "Admin Dashboard Load Time" "PASS" "Load time: ${admin_time}s"
else
    log_test "Admin Dashboard Load Time" "FAIL" "Load time: ${admin_time}s (>2s)"
fi

echo ""
echo "🔧 9. ERROR HANDLING TESTING"
echo "=================================="

# Test 18: 404 error handling
not_found_status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/nonexistent-page")
if [ "$not_found_status" = "404" ]; then
    log_test "404 Error Handling" "PASS" "Proper 404 response"
else
    log_test "404 Error Handling" "FAIL" "Got HTTP $not_found_status instead of 404"
fi

# Test 19: Invalid API endpoint
invalid_api_status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/api/nonexistent-endpoint")
if [ "$invalid_api_status" = "404" ]; then
    log_test "Invalid API Endpoint" "PASS" "Proper 404 response"
else
    log_test "Invalid API Endpoint" "FAIL" "Got HTTP $invalid_api_status instead of 404"
fi

echo ""
echo "📱 10. MOBILE COMPATIBILITY TESTING"
echo "=================================="

# Test 20: Mobile user agent compatibility
mobile_response=$(curl -s -H "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15" -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/")
if [ "$mobile_response" = "200" ]; then
    log_test "Mobile User Agent Compatibility" "PASS" "Mobile user agent works"
else
    log_test "Mobile User Agent Compatibility" "FAIL" "Mobile user agent failed with HTTP $mobile_response"
fi

echo ""
echo "🎯 11. DATABASE INTEGRATION TESTING"
echo "=================================="

# Test 21: Database connection via API
db_test_result=$(curl -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "action=login&username=Admin User&password=password" "http://localhost/mono-v2/api/auth.php" | jq -r ".success" 2>/dev/null)
if [ "$db_test_result" = "true" ]; then
    log_test "Database Integration" "PASS" "Database connection working"
else
    log_test "Database Integration" "FAIL" "Database connection failed"
fi

echo ""
echo "📊 TEST RESULTS SUMMARY"
echo "=================================="
echo "Total Tests: $TOTAL_TESTS"
echo -e "Passed: ${GREEN}$PASSED_TESTS${NC}"
echo -e "Failed: ${RED}$FAILED_TESTS${NC}"

# Calculate success rate
success_rate=$(echo "scale=1; $PASSED_TESTS * 100 / $TOTAL_TESTS" | bc 2>/dev/null)
echo "Success Rate: ${success_rate}%"

if [ $FAILED_TESTS -eq 0 ]; then
    echo -e "${GREEN}🎉 ALL TESTS PASSED!${NC}"
    echo "Application is ready for production use."
    echo ""
    echo "🔑 Testing Credentials:"
    echo "Admin: Admin User / password"
    echo "Staff: test_mantri@lamabejaya.coop / password"
    echo "Member: test_member@lamabejaya.coop / password"
    echo ""
    echo "🌐 Application URLs:"
    echo "Landing: http://localhost/mono-v2/"
    echo "Login: http://localhost/mono-v2/login.html"
    echo "Admin Dashboard: http://localhost/mono-v2/pages/admin/dashboard.html"
    echo "Staff Dashboard: http://localhost/mono-v2/pages/staff/dashboard.html"
    echo "Member Dashboard: http://localhost/mono-v2/pages/member/dashboard.html"
    exit 0
else
    echo -e "${RED}❌ SOME TESTS FAILED!${NC}"
    echo "Please review the failed tests before deploying."
    exit 1
fi
