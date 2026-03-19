#!/bin/bash

# KSP Lam Gabe Jaya v2.0 - Role Testing Script
# Test all user roles and credentials

echo "🧪 KSP LAM GABE JAYA v2.0 - ROLE TESTING"
echo "====================================="
echo "📅 $(date)"
echo "🌐 http://localhost/mono-v2"
echo "====================================="

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

# Function to test login
test_login() {
    local username="$1"
    local password="$2"
    local expected_role="$3"
    local test_name="$4"
    
    local result=$(curl -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "action=login&username=$username&password=$password" "http://localhost/mono-v2/api/auth.php" 2>/dev/null)
    
    if echo "$result" | jq -r ".success" 2>/dev/null | grep -q "true"; then
        local actual_role=$(echo "$result" | jq -r ".data.user.role" 2>/dev/null)
        if [ "$actual_role" = "$expected_role" ]; then
            log_test "$test_name" "PASS" "Role: $actual_role"
        else
            log_test "$test_name" "FAIL" "Expected role: $expected_role, Got: $actual_role"
        fi
    else
        local error_msg=$(echo "$result" | jq -r ".message" 2>/dev/null)
        log_test "$test_name" "FAIL" "$error_msg"
    fi
}

echo ""
echo "👤 TESTING ALL USER ROLES"
echo "======================="

# Test Admin role
echo ""
echo "🔧 Admin Role Testing"
echo "-------------------"
test_login "Admin User" "password" "admin" "Admin Login (Name)"
test_login "test_admin@lamabejaya.coop" "password" "admin" "Admin Login (Email)"

# Test Staff roles
echo ""
echo "👨‍💼 Staff Role Testing"
echo "--------------------"
test_login "Mantri User" "password" "mantri" "Mantri Login (Name)"
test_login "test_mantri@lamabejaya.coop" "password" "mantri" "Mantri Login (Email)"
test_login "Kasir User" "password" "kasir" "Kasir Login (Name)"
test_login "Teller User" "password" "teller" "Teller Login (Name)"
test_login "Surveyor User" "password" "surveyor" "Surveyor Login (Name)"
test_login "Collector User" "password" "collector" "Collector Login (Name)"

# Test Member role
echo ""
echo "👥 Member Role Testing"
echo "-------------------"
test_login "Member User" "password" "member" "Member Login (Name)"
test_login "test_member@lamabejaya.coop" "password" "member" "Member Login (Email)"

# Test invalid credentials
echo ""
echo "🚫 Invalid Credentials Testing"
echo "---------------------------"
test_login "invalid" "password" "" "Invalid Username"
test_login "Admin User" "wrongpassword" "" "Invalid Password"
test_login "" "" "" "Empty Credentials"

# Test dashboard access
echo ""
echo "📱 Dashboard Access Testing"
echo "------------------------"

# Test dashboard accessibility
dashboard_pages=(
    "pages/admin/dashboard.html:Admin Dashboard"
    "pages/staff/dashboard.html:Staff Dashboard"
    "pages/staff/dashboard-mantri.html:Mantri Dashboard"
    "pages/staff/dashboard-kasir.html:Kasir Dashboard"
    "pages/staff/dashboard-teller.html:Teller Dashboard"
    "pages/staff/dashboard-surveyor.html:Surveyor Dashboard"
    "pages/staff/dashboard-collector.html:Collector Dashboard"
    "pages/member/dashboard.html:Member Dashboard"
)

for page_info in "${dashboard_pages[@]}"; do
    page_path=$(echo "$page_info" | cut -d':' -f1)
    page_name=$(echo "$page_info" | cut -d':' -f2)
    
    status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/$page_path")
    if [ "$status" = "200" ]; then
        log_test "$page_name Access" "PASS" "HTTP 200"
    else
        log_test "$page_name Access" "FAIL" "HTTP $status"
    fi
done

echo ""
echo "📊 ROLE TESTING RESULTS"
echo "===================="
echo "Total Tests: $TOTAL_TESTS"
echo -e "Passed: ${GREEN}$PASSED_TESTS${NC}"
echo -e "Failed: ${RED}$FAILED_TESTS${NC}"

# Calculate success rate
if [ $TOTAL_TESTS -gt 0 ]; then
    success_rate=$(echo "scale=1; $PASSED_TESTS * 100 / $TOTAL_TESTS" | bc 2>/dev/null)
    echo "Success Rate: ${success_rate}%"
fi

if [ $FAILED_TESTS -eq 0 ]; then
    echo -e "${GREEN}🎉 ALL ROLE TESTS PASSED!${NC}"
    echo ""
    echo "🔑 Valid Login Credentials:"
    echo "  Admin: Admin User / password"
    echo "  Admin: test_admin@lamabejaya.coop / password"
    echo "  Mantri: Mantri User / password"
    echo "  Mantri: test_mantri@lamabejaya.coop / password"
    echo "  Kasir: Kasir User / password"
    echo "  Teller: Teller User / password"
    echo "  Surveyor: Surveyor User / password"
    echo "  Collector: Collector User / password"
    echo "  Member: Member User / password"
    echo "  Member: test_member@lamabejaya.coop / password"
    echo ""
    echo "🌐 Dashboard URLs:"
    echo "  Admin: http://localhost/mono-v2/pages/admin/dashboard.html"
    echo "  Staff: http://localhost/mono-v2/pages/staff/dashboard.html"
    echo "  Member: http://localhost/mono-v2/pages/member/dashboard.html"
else
    echo -e "${RED}❌ SOME ROLE TESTS FAILED!${NC}"
    echo "Please review the failed tests and fix the issues."
fi

echo ""
echo "📚 For comprehensive testing, run:"
echo "  ./tests/test_comprehensive.sh"
echo "  ./tests/test_end_to_end.sh"
