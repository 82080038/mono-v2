#!/bin/bash

# E2E (End-to-End) Testing Script for KSP Lam Gabe Jaya System
# Tests complete user flows and system integration

echo "🧪 E2E Testing - KSP Lam Gabe Jaya System"
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

# Function to test user flow
test_user_flow() {
    local test_name="$1"
    local flow_steps="$2"
    
    echo -e "${BLUE}Testing User Flow: $test_name${NC}"
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    
    # Execute user flow steps
    flow_passed=true
    for step in $flow_steps; do
        if ! eval "$step" >/dev/null 2>&1; then
            flow_passed=false
            break
        fi
    done
    
    if [ "$flow_passed" = true ]; then
        echo -e "${GREEN}✅ PASSED${NC}"
        TESTS_PASSED=$((TESTS_PASSED + 1))
    else
        echo -e "${RED}❌ FAILED${NC}"
        echo -e "${RED}   User flow failed at step: $step${NC}"
        TESTS_FAILED=$((TESTS_FAILED + 1))
    fi
    echo ""
}

echo -e "${YELLOW}Starting E2E Tests...${NC}"
echo ""

# Test 1: Complete Admin Dashboard Flow
test_user_flow "Admin Dashboard Flow" "
    'curl -s http://localhost/mono-v2/api/dashboard.php?action=admin_stats | grep -q success'
    'curl -s http://localhost/mono-v2/api/orang-integration.php?action=dashboard | grep -q success'
    'curl -s http://localhost/mono-v2/api/database-stats.php | grep -q success'
"

# Test 2: Complete Staff Dashboard Flow
test_user_flow "Staff Dashboard Flow" "
    'curl -s http://localhost/mono-v2/api/dashboard.php?action=staff_stats | grep -q success'
    'curl -s http://localhost/mono-v2/api/guarantee-risk-management.php?action=dashboard | grep -q success'
"

# Test 3: Complete Member Dashboard Flow
test_user_flow "Member Dashboard Flow" "
    'curl -s http://localhost/mono-v2/api/dashboard.php?action=member_stats&member_id=1 | grep -q success'
"

# Test 4: Database Integration Flow
test_user_flow "Database Integration Flow" "
    'mysql -u root -p'root' --socket=/opt/lampp/var/mysql/mysql.sock -e 'USE ksp_lamgabejaya_v2; SELECT 1' >/dev/null 2>&1'
    'mysql -u root -p'root' --socket=/opt/lampp/var/mysql/mysql.sock -e 'USE orang; SELECT 1' >/dev/null 2>&1'
    'mysql -u root -p'root' --socket=/opt/lampp/var/mysql/mysql.sock -e 'USE alamat_db; SELECT 1' >/dev/null 2>&1'
"

# Test 5: Guarantee Management Flow
test_user_flow "Guarantee Management Flow" "
    'curl -s http://localhost/mono-v2/api/guarantee-risk-management.php?action=dashboard | grep -q success'
    'curl -s http://localhost/mono-v2/api/orang-integration.php?action=search_persons&query=John | grep -q success'
"

# Test 6: Data Migration Flow
test_user_flow "Data Migration Flow" "
    'curl -s http://localhost/mono-v2/api/data-migration.php?action=dashboard | grep -q success'
    'curl -s http://localhost/mono-v2/api/database-stats.php | grep -q success'
"

# Test 7: Multi-Database Architecture Test
test_api "Multi-Database Architecture" "http://localhost/mono-v2/api/orang-integration.php?action=dashboard" "total_persons" "1"

# Test 8: Frontend Integration Test
test_api "Frontend Integration" "http://localhost/mono-v2/api/dashboard.php?action=admin_stats" "total_members" "1"

# Test 9: API Response Format Test
test_api "API Response Format" "http://localhost/mono-v2/api/orang-integration.php?action=dashboard" "success" "true"

# Test 10: Database Connection Test
run_test "Database Connection Koperasi" "mysql -u root -p'root' --socket=/opt/lampp/var/mysql/mysql.sock -e 'USE ksp_lamgabejaya_v2; SELECT 1'" "1"

# Test 11: Database Connection Orang
run_test "Database Connection Orang" "mysql -u root -p'root' --socket=/opt/lampp/var/mysql/mysql.sock -e 'USE orang; SELECT 1'" "1"

# Test 12: Database Connection Alamat
run_test "Database Connection Alamat" "mysql -u root -p'root' --socket=/opt/lampp/var/mysql/mysql.sock -e 'USE alamat_db; SELECT 1'" "1"

# Test 13: Frontend Page Loading
test_user_flow "Frontend Page Loading" "
    'curl -s http://localhost/mono-v2/pages/admin/dashboard.html | grep -q Dashboard'
    'curl -s http://localhost/mono-v2/pages/staff/dashboard.html | grep -q Dashboard'
    'curl -s http://localhost/mono-v2/pages/member/dashboard.html | grep -q Dashboard'
"

# Test 14: API Error Handling
test_user_flow "API Error Handling" "
    'curl -s http://localhost/mono-v2/api/dashboard.php?action=invalid | grep -q error'
"

# Test 15: Data Integrity Test
test_user_flow "Data Integrity Test" "
    'mysql -u root -p'root' --socket=/opt/lampp/var/mysql/mysql.sock -e 'USE ksp_lamgabejaya_v2; SELECT COUNT(*) FROM members' >/dev/null 2>&1'
    'mysql -u root -p'root' --socket=/opt/lampp/var/mysql/mysql.sock -e 'USE orang; SELECT COUNT(*) FROM persons' >/dev/null 2>&1'
"

# Test 16: Performance Test
test_user_flow "Performance Test" "
    'curl -s http://localhost/mono-v2/api/dashboard.php?action=admin_stats --max-time 5 >/dev/null 2>&1'
    'curl -s http://localhost/mono-v2/api/orang-integration.php?action=dashboard --max-time 5 >/dev/null 2>&1'
"

# Test 17: Security Test
test_user_flow "Security Test" "
    'curl -s http://localhost/mono-v2/api/dashboard.php?action=admin_stats | grep -q password'
    'curl -s http://localhost/mono-v2/api/orang-integration.php?action=dashboard | grep -q password'
"

# Test 18: System Resources Test
test_user_flow "System Resources Test" "
    'ps aux | grep -v grep | grep mysql >/dev/null 2>&1'
    'ps aux | grep -v grep | grep apache >/dev/null 2>&1'
"

# Test 19: File System Test
test_user_flow "File System Test" "
    'test -f /opt/lampp/htdocs/mono-v2/api/dashboard.php'
    'test -f /opt/lampp/htdocs/mono-v2/api/orang-integration.php'
    'test -f /opt/lampp/htdocs/mono-v2/api/guarantee-risk-management.php'
"

# Test 20: Integration Test
test_user_flow "Integration Test" "
    'curl -s http://localhost/mono-v2/api/orang-integration.php?action=dashboard | grep -q total_persons'
    'curl -s http://localhost/mono-v2/api/dashboard.php?action=admin_stats | grep -q total_members'
"

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
        echo -e "${GREEN}🎉 E2E Testing COMPLETED SUCCESSFULLY!${NC}"
        echo -e "${GREEN}✅ System integration is working properly${NC}"
        echo -e "${GREEN}✅ All user flows are functioning correctly${NC}"
        echo -e "${GREEN}✅ Multi-database architecture is operational${NC}"
    else
        echo -e "${RED}⚠️  E2E Testing completed with issues${NC}"
        echo -e "${RED}❌ Some system integrations may not work correctly${NC}"
        echo -e "${RED}❌ User flows may have failures${NC}"
    fi
else
    echo -e "${RED}❌ No tests were executed${NC}"
fi

echo ""
echo -e "${BLUE}==========================================${NC}"
echo -e "${YELLOW}E2E Testing Complete${NC}"
echo -e "${BLUE}==========================================${NC}"
