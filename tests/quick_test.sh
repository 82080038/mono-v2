#!/bin/bash

# KSP Lam Gabe Jaya v2.0 - Quick Test Runner
# Quick testing script for daily use

echo "🧪 KSP LAM GABE JAYA v2.0 - QUICK TEST RUNNER"
echo "==================================="
echo "📅 $(date)"
echo "🌐 http://localhost/mono-v2"
echo "=================================="

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

# Quick server check
echo "🔍 Quick Server Check"
echo "-------------------"

# Test 1: Landing page
landing_status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/")
if [ "$landing_status" = "200" ]; then
    log_test "Landing Page" "PASS" "HTTP 200"
else
    log_test "Landing Page" "FAIL" "HTTP $landing_status"
fi

# Test 2: Login page
login_status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/login.html")
if [ "$login_status" = "200" ]; then
    log_test "Login Page" "PASS" "HTTP 200"
else
    log_test "Login Page" "FAIL" "HTTP $login_status"
fi

# Test 3: Admin dashboard
admin_status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/pages/admin/dashboard.html")
if [ "$admin_status" = "200" ]; then
    log_test "Admin Dashboard" "PASS" "HTTP 200"
else
    log_test "Admin Dashboard" "FAIL" "HTTP $admin_status"
fi

# Test 4: Staff dashboard
staff_status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/pages/staff/dashboard.html")
if [ "$staff_status" = "200" ]; then
    log_test "Staff Dashboard" "PASS" "HTTP 200"
else
    log_test "Staff Dashboard" "FAIL" "HTTP $staff_status"
fi

# Test 5: Member dashboard
member_status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/pages/member/dashboard.html")
if [ "$member_status" = "200" ]; then
    log_test "Member Dashboard" "PASS" "HTTP 200"
else
    log_test "Member Dashboard" "FAIL" "HTTP $member_status"
fi

# Test 6: API endpoint
api_status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/api/auth.php")
if [ "$api_status" = "200" ]; then
    log_test "Auth API" "PASS" "HTTP 200"
else
    log_test "Auth API" "FAIL" "HTTP $api_status"
fi

# Test 7: Login API
login_api_result=$(curl -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "action=login&username=Admin User&password=password" "http://localhost/mono-v2/api/auth.php" 2>/dev/null)
if echo "$login_api_result" | jq -r ".success" 2>/dev/null | grep -q "true"; then
    log_test "Login API" "PASS" "Login successful"
else
    log_test "Login API" "FAIL" "Login failed"
fi

echo ""
echo "📊 Quick Test Results"
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
    echo -e "${GREEN}🎉 ALL QUICK TESTS PASSED!${NC}"
    echo "Application is running normally."
    echo ""
    echo "🔧 For comprehensive testing, run:"
    echo "  ./tests/test_comprehensive.sh"
    echo "  ./tests/test_end_to_end.sh"
else
    echo -e "${RED}❌ SOME QUICK TESTS FAILED!${NC}"
    echo "Please check the failed components."
    echo ""
    echo "🔧 For detailed testing and fixes, run:"
    echo "  ./tests/test_comprehensive.sh"
    echo "  ./tests/test_end_to_end.sh"
fi

echo ""
echo "📚 For testing documentation, see: tests/README.md"
