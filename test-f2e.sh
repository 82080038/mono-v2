#!/bin/bash

# F2E (Frontend-to-End) Testing Script for KSP Lam Gabe Jaya System
# Tests all frontend features with real API endpoints

echo "🧪 F2E Testing - KSP Lam Gabe Jaya System"
echo "=========================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Test results
TESTS_PASSED=0
TESTS_FAILED=0
TOTAL_TESTS=0

# Function to run test
run_test() {
    local test_name="$1"
    local test_command="$2"
    local expected_result="$3"
    
    echo -e "${BLUE}Testing: $test_name${NC}"
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    
    # Run test command
    result=$(eval "$test_command" 2>/dev/null)
    exit_code=$?
    
    # Check result
    if [[ $exit_code -eq 0 && "$result" == *"$expected_result"* ]]; then
        echo -e "${GREEN}✅ PASSED${NC}"
        TESTS_PASSED=$((TESTS_PASSED + 1))
    else
        echo -e "${RED}❌ FAILED${NC}"
        echo -e "${RED}   Expected: $expected_result${NC}"
        echo -e "${RED}   Got: $result${NC}"
        TESTS_FAILED=$((TESTS_FAILED + 1))
    fi
    echo ""
}

# Function to test API endpoint
test_api() {
    local test_name="$1"
    local api_url="$2"
    local expected_field="$3"
    local expected_value="$4"
    
    echo -e "${BLUE}Testing API: $test_name${NC}"
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    
    # Test API endpoint
    response=$(curl -s "$api_url")
    
    # Check if response contains expected field and value
    if [[ "$response" == *"$expected_field"* && "$response" == *"$expected_value"* ]]; then
        echo -e "${GREEN}✅ PASSED${NC}"
        TESTS_PASSED=$((TESTS_PASSED + 1))
    else
        echo -e "${RED}❌ FAILED${NC}"
        echo -e "${RED}   API URL: $api_url${NC}"
        echo -e "${RED}   Expected: $expected_field = $expected_value${NC}"
        echo -e "${RED}   Response: $response${NC}"
        TESTS_FAILED=$((TESTS_FAILED + 1))
    fi
    echo ""
}

# Function to test frontend page
test_frontend() {
    local test_name="$1"
    local page_url="$2"
    local expected_content="$3"
    
    echo -e "${BLUE}Testing Frontend: $test_name${NC}"
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    
    # Test frontend page
    response=$(curl -s "$page_url")
    
    # Check if page contains expected content
    if [[ "$response" == *"$expected_content"* ]]; then
        echo -e "${GREEN}✅ PASSED${NC}"
        TESTS_PASSED=$((TESTS_PASSED + 1))
    else
        echo -e "${RED}❌ FAILED${NC}"
        echo -e "${RED}   Page URL: $page_url${NC}"
        echo -e "${RED}   Expected content: $expected_content${NC}"
        TESTS_FAILED=$((TESTS_FAILED + 1))
    fi
    echo ""
}

echo -e "${YELLOW}Starting F2E Tests...${NC}"
echo ""

# Test 1: Admin Dashboard API
test_api "Admin Dashboard API" "http://localhost/mono-v2/api/dashboard.php?action=admin_stats" "success" "true"

# Test 2: Staff Dashboard API
test_api "Staff Dashboard API" "http://localhost/mono-v2/api/dashboard.php?action=staff_stats" "success" "true"

# Test 3: Member Dashboard API
test_api "Member Dashboard API" "http://localhost/mono-v2/api/dashboard.php?action=member_stats&member_id=1" "success" "true"

# Test 4: Orang Integration API
test_api "Orang Integration Dashboard" "http://localhost/mono-v2/api/orang-integration.php?action=dashboard" "success" "true"

# Test 5: Orang Search API
test_api "Orang Search API" "http://localhost/mono-v2/api/orang-integration.php?action=search_persons&query=John" "success" "true"

# Test 6: Guarantee Risk Management API
test_api "Guarantee Risk Dashboard" "http://localhost/mono-v2/api/guarantee-risk-management.php?action=dashboard" "success" "true"

# Test 7: Database Stats API
test_api "Database Stats API" "http://localhost/mono-v2/api/database-stats.php" "success" "true"

# Test 8: Database Activities API
test_api "Database Activities API" "http://localhost/mono-v2/api/database-activities.php" "success" "true"

# Test 9: Data Migration API
test_api "Data Migration API" "http://localhost/mono-v2/api/data-migration.php?action=dashboard" "success" "true"

# Test 10: Admin Dashboard Frontend
test_frontend "Admin Dashboard Page" "http://localhost/mono-v2/pages/admin/dashboard.html" "Dashboard Admin"

# Test 11: Staff Dashboard Frontend
test_frontend "Staff Dashboard Page" "http://localhost/mono-v2/pages/staff/dashboard.html" "Dashboard Staff"

# Test 12: Member Dashboard Frontend
test_frontend "Member Dashboard Page" "http://localhost/mono-v2/pages/member/dashboard.html" "Dashboard Member"

# Test 13: Guarantee Management Frontend
test_frontend "Guarantee Management Page" "http://localhost/mono-v2/pages/admin/guarantee-management.html" "Guarantee Management"

# Test 14: Database Management Frontend
test_frontend "Database Management Page" "http://localhost/mono-v2/pages/admin/database-management.html" "Database Management"

# Test 15: Admin Login Page
test_frontend "Admin Login Page" "http://localhost/mono-v2/pages/admin/login.html" "Login Admin"

# Test 16: Staff Login Page
test_frontend "Staff Login Page" "http://localhost/mono-v2/pages/staff/login.html" "Login Staff"

# Test 17: Member Login Page
test_frontend "Member Login Page" "http://localhost/mono-v2/pages/member/login.html" "Login Member"

# Test 18: Main Index Page
test_frontend "Main Index Page" "http://localhost/mono-v2/index.html" "KSP Lam Gabe Jaya"

# Test 19: CSS Files Exist
run_test "Dashboard CSS File" "test -f /opt/lampp/htdocs/mono-v2/assets/css/dashboard.css" ""

# Test 20: Guarantee Risk CSS File
run_test "Guarantee Risk CSS File" "test -f /opt/lampp/htdocs/mono-v2/assets/css/guarantee-risk-management.css" ""

# Test 21: Data Migration CSS File
run_test "Data Migration CSS File" "test -f /opt/lampp/htdocs/mono-v2/assets/css/data-migration.css" ""

# Test 22: Dashboard JS File
run_test "Dashboard JS File" "test -f /opt/lampp/htdocs/mono-v2/assets/js/dashboard.js" ""

# Test 23: Guarantee Risk JS File
run_test "Guarantee Risk JS File" "test -f /opt/lampp/htdocs/mono-v2/assets/js/guarantee-risk-management.js" ""

# Test 24: Data Migration JS File
run_test "Data Migration JS File" "test -f /opt/lampp/htdocs/mono-v2/assets/js/data-migration.js" ""

# Test 25: Database Connection Test
run_test "Database Connection" "mysql -u root -p'root' --socket=/opt/lampp/var/mysql/mysql.sock -e 'SELECT 1'" "1"

# Test 26: Koperasi Database Exists
run_test "Koperasi Database" "mysql -u root -p'root' --socket=/opt/lampp/var/mysql/mysql.sock -e 'USE ksp_lamgabejaya_v2; SELECT 1'" "1"

# Test 27: Orang Database Exists
run_test "Orang Database" "mysql -u root -p'root' --socket=/opt/lampp/var/mysql/mysql.sock -e 'USE orang; SELECT 1'" "1"

# Test 28: Alamat Database Exists
run_test "Alamat Database" "mysql -u root -p'root' --socket=/opt/lampp/var/mysql/mysql.sock -e 'USE alamat_db; SELECT 1'" "1"

# Test 29: Apache Running
run_test "Apache Service" "ps aux | grep -v grep | grep apache" "apache"

# Test 30: MySQL Running
run_test "MySQL Service" "ps aux | grep -v grep | grep mysql" "mysql"

echo -e "${YELLOW}Test Results:${NC}"
echo -e "${GREEN}Tests Passed: $TESTS_PASSED${NC}"
echo -e "${RED}Tests Failed: $TESTS_FAILED${NC}"
echo -e "${BLUE}Total Tests: $TOTAL_TESTS${NC}"
echo ""

# Calculate success rate
if [ $TOTAL_TESTS -gt 0 ]; then
    SUCCESS_RATE=$((TESTS_PASSED * 100 / TOTAL_TESTS))
    echo -e "${YELLOW}Success Rate: $SUCCESS_RATE%${NC}"
    
    if [ $SUCCESS_RATE -ge 80 ]; then
        echo -e "${GREEN}🎉 F2E Testing COMPLETED SUCCESSFULLY!${NC}"
        echo -e "${GREEN}✅ Frontend integration is working properly${NC}"
    else
        echo -e "${RED}⚠️  F2E Testing completed with issues${NC}"
        echo -e "${RED}❌ Some frontend features may not work correctly${NC}"
    fi
else
    echo -e "${RED}❌ No tests were executed${NC}"
fi

echo ""
echo -e "${BLUE}==========================================${NC}"
echo -e "${YELLOW}F2E Testing Complete${NC}"
echo -e "${BLUE}==========================================${NC}"
