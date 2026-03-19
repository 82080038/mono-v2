#!/bin/bash

# KSP Lam Gabe Jaya v2.0 - Test Suite Launcher
# Main script to run all testing scripts

echo "🧪 KSP LAM GABE JAYA v2.0 - TEST SUITE LAUNCHER"
echo "======================================"
echo "📅 $(date)"
echo "🌐 http://localhost/mono-v2"
echo "======================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Function to display menu
show_menu() {
    echo ""
    echo "🧪 Select Test Type:"
    echo "1. Quick Test (7 tests) - Fast basic checks"
    echo "2. Comprehensive Test (71 tests) - Complete system validation"
    echo "3. End-to-End Test (29 tests) - Critical workflows"
    echo "4. Database Setup - Create test database"
    echo "5. Run All Tests - Execute all test suites"
    echo "6. Exit"
    echo ""
}

# Function to run quick test
run_quick_test() {
    echo "🚀 Running Quick Test..."
    echo "===================="
    cd /opt/lampp/htdocs/mono-v2/tests
    ./quick_test.sh
}

# Function to run comprehensive test
run_comprehensive_test() {
    echo "🔧 Running Comprehensive Test..."
    echo "========================="
    cd /opt/lampp/htdocs/mono-v2/tests
    ./test_comprehensive.sh
}

# Function to run end-to-end test
run_end_to_end_test() {
    echo "🔄 Running End-to-End Test..."
    echo "========================="
    cd /opt/lampp/htdocs/mono-v2/tests
    ./test_end_to_end.sh
}

# Function to setup database
setup_database() {
    echo "🗄️ Setting Up Database..."
    echo "====================="
    cd /opt/lampp/htdocs/mono-v2/tests
    ./setup_database.sh
}

# Function to run all tests
run_all_tests() {
    echo "🧪 Running All Test Suites..."
    echo "========================="
    
    echo ""
    echo "1/4: Quick Test"
    run_quick_test
    echo ""
    echo "2/4: Comprehensive Test"
    run_comprehensive_test
    echo ""
    echo "3/4: End-to-End Test"
    run_end_to_end_test
    echo ""
    echo "4/4: Database Setup"
    setup_database
    echo ""
    echo "📊 All Tests Completed!"
}

# Function to display help
show_help() {
    echo ""
    echo "📚 Test Suite Help"
    echo "=================="
    echo ""
    echo "Available Options:"
    echo "  1  - Quick Test (7 tests)"
    echo "  2  - Comprehensive Test (71 tests)"
    echo "  3  - End-to-End Test (29 tests)"
    echo " 4  - Database Setup"
    echo "   5  - Run All Tests"
    echo "  6  - Exit"
    echo ""
    echo "Usage Examples:"
    echo "  ./test_runner.sh          # Show menu"
    echo "  ./test_runner.sh 1        # Quick test"
    echo "  echo '2' | ./test_runner.sh # Comprehensive test"
    echo "  ./test_runner.sh all        # Run all tests"
    echo ""
    echo "📚 Documentation: tests/README.md"
    echo ""
    echo "🔧 Requirements:"
    echo "  - Apache/Nginx web server"
    echo "  - PHP 8.2+"
    echo "  - MySQL/MariaDB"
    echo "  - curl, jq, bc, mysql-client"
    echo ""
    echo "🔑 Testing Credentials:"
    echo "  - Admin: Admin User / password"
    echo "  - Staff: test_mantri@lamabejaya.coop / password"
    echo "  - Member: test_member@lamabejaya.coop / password"
}

# Main execution logic
case "${1:-menu}" in
    "1"|"quick")
        run_quick_test
        ;;
    "2"|"comprehensive")
        run_comprehensive_test
        ;;
    "3"|"end-to-end"|"e2e")
        run_end_to_end_test
        ;;
    "4"|"database"|"db"|"setup")
        setup_database
        ;;
    "5"|"all")
        run_all_tests
        ;;
    "6"|"exit"|"quit"|"q")
        echo "👋 Exiting Test Suite"
        exit 0
        ;;
    "help"|"-h"|"--help")
        show_help
        ;;
    *)
        show_menu
        ;;
esac
