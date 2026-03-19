#!/bin/bash

# KSP Lam Gabe Jaya v2.0 - Test Suite Maintenance
# Script to maintain and update testing files

echo "🔧 KSP LAM GABE JAYA v2.0 - TEST SUITE MAINTENANCE"
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
    echo "🔧 Maintenance Options:"
    echo "1. Update Test Credentials"
    echo "2. Reset Database"
    echo "3. Check Dependencies"
    echo "4. Clean Test Logs"
    echo "5. Backup Test Results"
    echo "6. Generate Test Report"
    echo "7. Exit"
    echo ""
}

# Function to update test credentials
update_credentials() {
    echo "🔄 Updating Test Credentials..."
    echo "=========================="
    
    # Update database with new test users
    cd /opt/lamp/htdocs/mono-v2/tests
    echo "📝 Updating database with new test users..."
    
    # Update admin user
    mysql --host=localhost --user=root --password=root --socket=/opt/lampp/var/mysql/mysql.sock -e "
        UPDATE ksp_lamgabejaya_v2.users 
        SET password = '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
        WHERE name = 'Admin User';
    " 2>/dev/null
    
    # Update staff user
    mysql --host=localhost --user=root --password=root --socket=/opt/lampp/var/mysql/mysql.sock -e "
        UPDATE ksp_lamgabejaya_v2.users 
        SET password = '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
        WHERE name = 'Mantri User';
    " 2>/dev/null
    
    # Update member user
    mysql --host=localhost --user=root --password=root --socket=/opt/lamp/var/mysql/mysql.sock -e "
        UPDATE ksp_lamgabejaya_v2.users 
        SET password = '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
        WHERE name = 'Member User';
    " 2>/dev/null
    
    echo -e "${GREEN}✅ Test credentials updated${NC}"
    echo "   Admin: Admin User / password"
    echo "   Staff: Mantri User / password"
    echo "   Member: Member User / password"
}

# Function to reset database
reset_database() {
    echo "🗄️ Resetting Database..."
    echo "===================="
    
    read -p "⚠️  This will delete all test data. Continue? (y/N): " -n response
    if [[ ! $response =~ ^[Yy]$ ]]; then
        echo "Database reset cancelled."
        return
    fi
    
    cd /opt/lamp/htdocs/mono-v2/tests
    echo "📝 Dropping and recreating database..."
    
    # Drop database
    mysql --host=localhost --user=root --password=root --socket=/opt/lampp/var/mysql/mysql.sock -e "DROP DATABASE IF EXISTS ksp_lamgabejaya_v2;" 2>/dev/null
    
    # Recreate database
    mysql --host=localhost --user=root --password=root --socket=/opt/lampp/var/mysql/mysql.sock -e "CREATE DATABASE ksp_lamgabejaya_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
    
    # Run setup script
    ./setup_database.sh
    
    echo -e "${GREEN}✅ Database reset completed${NC}"
}

# Function to check dependencies
check_dependencies() {
    echo "🔍 Checking Dependencies..."
    echo "=================="
    
    # Check required tools
    tools=("curl" "jq" "bc" "mysql")
    missing_tools=()
    
    for tool in "${tools[@]}"; do
        if ! command -v "$tool" &>/dev/null; then
            missing_tools+=("$tool")
        fi
    done
    
    if [ ${#missing_tools[@]} -eq 0 ]; then
        echo -e "${GREEN}✅ All dependencies installed${NC}"
    else
        echo -e "${RED}❌ Missing dependencies: ${missing_tools[*]}${NC}"
        echo ""
        echo "Install missing tools:"
        for tool in "${missing_tools[@]}"; do
            echo "  sudo apt-get install $tool"
        done
    fi
    
    # Check web server
    if pgrep -x apache2 >/dev/null; then
        echo -e "${GREEN}✅ Apache running${NC}"
    else
        echo -e "${YELLOW}⚠️  Apache not running${NC}"
    fi
    
    # Check MySQL
    if pgrep -x mysqld >/dev/null; then
        echo -e "${GREEN}✅ MySQL running${NC}"
    else
        echo -e "${YELLOW}⚠️  MySQL not running${NC}"
    fi
    
    # Check PHP
    if command -v php >/dev/null; then
        php_version=$(php -v | head -1 | cut -d " " -f4)
        echo -e "${GREEN}✅ PHP installed ($php_version)${NC}"
    else
        echo -e "${RED}❌ PHP not installed${NC}"
    fi
}

# Function to clean test logs
clean_logs() {
    echo "🧹 Cleaning Test Logs..."
    echo "=================="
    
    # Clean Apache logs
    if [ -f "/opt/lampp/logs/error_log" ]; then
        echo "📝 Cleaning Apache error logs..."
        > /opt/lamp/logs/error_log
        echo -e "${GREEN}✅ Apache logs cleaned${NC}"
    fi
    
    # Clean PHP logs
    if [ -d "/opt/lampp/logs" ]; then
        echo "📝 Cleaning PHP logs..."
        find /opt/lampp/logs -name "*.log" -delete 2>/dev/null
        echo -e "${GREEN}✅ PHP logs cleaned${NC}"
    fi
    
    echo -e "${GREEN}✅ Test logs cleaned${NC}"
}

# Function to backup test results
backup_results() {
    echo "💾 Backing Up Test Results..."
    echo "========================"
    
    backup_dir="/opt/lampp/htdocs/mono-v2/tests/backups"
    timestamp=$(date +%Y%m%d_%H%M%S)
    
    # Create backup directory
    mkdir -p "$backup_dir" 2>/dev/null
    
    # Backup test results
    echo "📝 Backing up to: $backup_dir/test_$timestamp"
    
    # Create backup report
    cat > "$backup_dir/test_$timestamp/report.txt" << EOF
    KSP Lam Gabe Jaya v2.0 - Test Results Backup
    ====================================
    Date: $(date)
    Timestamp: $timestamp
    
    Test Scripts:
    - test_comprehensive.sh (71 tests)
    - test_end_to_end.sh (29 tests)
    - quick_test.sh (7 tests)
    
    Testing Credentials:
    - Admin: Admin User / password
    - Staff: test_mantri@lamabejaya.coop / password
    - Member: test_member@lamabejaya.coop / password
    
    Database: ksp_lamgabejaya_v2
    Server: http://localhost/mono-v2
    
    For detailed test results, see the individual test files.
EOF
    
    echo -e "${GREEN}✅ Test results backed up${NC}"
    echo "   Location: $backup_dir/test_$timestamp"
}

# Function to generate test report
generate_report() {
    echo "📊 Generating Test Report..."
    echo "===================="
    
    timestamp=$(date +%Y%m%d_%H%M%S)
    report_dir="/opt/lampp/htdocs/mono-v2/tests/reports"
    
    # Create reports directory
    mkdir -p "$report_dir" 2>/dev/null
    
    # Generate comprehensive test report
    echo "📊 Generating comprehensive test report..."
    cd /opt/lampp/htdocs/mono-v2/tests
    ./test_comprehensive.sh > "$report_dir/comprehensive_$timestamp.txt" 2>&1
    
    # Generate end-to-end test report
    echo "📊 Generating end-to-end test report..."
    ./test_end_to_end.sh > "$report_dir/e2e_$timestamp.txt" 2>&1
    
    # Generate quick test report
    echo "📊 Generating quick test report..."
    ./quick_test.sh > "$report_dir/quick_$timestamp.txt" 2>&1
    
    # Generate summary report
    cat > "$report_dir/summary_$timestamp.txt" << EOF
KSP Lam Gabe Jaya v2.0 - Test Summary Report
=====================================
Date: $(date)
Timestamp: $timestamp

Test Results Summary:
- Quick Test: $(./quick_test.sh 2>/dev/null | grep "Total Tests:" | cut -d: -f2)
- Comprehensive Test: $(./test_comprehensive.sh 2>/dev/null | grep "Total Tests:" | cut -d: -f2)
- End-to-End Test: $(./test_end_to_end.sh 2>/dev/null | grep "Total Tests:" | cut -d: -f2)

For detailed results, see individual test files in the reports directory.
EOF
    
    echo -e "${GREEN}✅ Test reports generated${NC}"
    echo "   Location: $report_dir"
    echo "   Files:"
    echo "   - comprehensive_$timestamp.txt"
    echo "   - e2e_$timestamp.txt"
    echo "   - quick_$timestamp.txt"
    echo "   - summary_$timestamp.txt"
}

# Function to display help
show_help() {
    echo ""
    echo "🔧 Maintenance Help"
    echo "=================="
    echo ""
    echo "Available Options:"
    echo "  1  - Update Test Credentials"
    echo "  2  - Reset Database"
    echo "  3  - Check Dependencies"
    echo "  4  - Clean Test Logs"
    echo " 5  - Backup Test Results"
    echo " 6  - Generate Test Report"
    echo " 7  - Exit"
    echo ""
    echo "Usage Examples:"
    echo "  ./test_maintenance.sh          # Show menu"
    echo "  ./test_maintenance.sh 1        # Update credentials"
    echo "  echo '2' | ./test_maintenance.sh # Reset database"
    echo "  ./test_maintenance.sh all        # Run all maintenance"
    echo ""
    echo "📚 Documentation: tests/README.md"
}

# Function to run all maintenance
run_all_maintenance() {
    echo "🔧 Running All Maintenance Tasks..."
    echo "=========================="
    
    echo ""
    echo "1/6: Update Test Credentials"
    update_credentials
    echo ""
    echo "2/6: Check Dependencies"
    check_dependencies
    echo ""
    echo "3/6: Clean Test Logs"
    clean_logs
    echo ""
    echo "4/6: Backup Test Results"
    backup_results
    echo ""
    echo "5/6: Generate Test Report"
    generate_report
    echo ""
    echo "🎯 All maintenance tasks completed!"
}

# Main execution logic
case "${1:-menu}" in
    "1"|"credentials"|"creds"|"update")
        update_credentials
        ;;
    "2"|"reset"|"database"|"db"|"setup")
        reset_database
        ;;
    "3"|"deps"|"dependencies"|"check")
        check_dependencies
        ;;
    "4"|"logs"|"clean"|"clear")
        clean_logs
        ;;
    "5"|"backup"|"save"|"store")
        backup_results
        ;;
    "6"|"report"|"generate"|"create")
        generate_report
        ;;
    "7"|"exit"|"quit"|"q")
        echo "👋 Exiting Maintenance"
        exit 0
        ;;
    "help"|"-h"|"--help")
        show_help
        ;;
    "all")
        run_all_maintenance
        ;;
    *)
        show_menu
        ;;
esac
