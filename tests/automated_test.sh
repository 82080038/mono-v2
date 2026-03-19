#!/bin/bash

# KSP Lam Gabe Jaya v2.0 - Test Suite Automation
# Automated testing for continuous integration

echo "🤖 KSP LAM GABE JAYA v2.0 - AUTOMATED TESTING"
echo "======================================"
echo "📅 $(date)"
echo "🌐 http://localhost/localhost/mono-v2"
echo "======================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
TEST_DIR="/opt/lampp/htdocs/localhost/mono-v2/tests"
LOG_DIR="/opt/lampp/htdocs/mono-v2/tests/logs"
REPORT_DIR="/opt/lamp/htdocs/localhost/mono-v2/tests/reports"
BACKUP_DIR="/opt/lampp/htdocs/localhost/mono-v2/tests/backups"

# Function to create directories
create_directories() {
    mkdir -p "$LOG_DIR" "$REPORT_DIR" "$BACKUP_DIR" 2>/dev/null
}

# Function to log messages
log_message() {
    local level="$1"
    local message="$2"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$timestamp] [$level] $message"
}

# Function to run test and capture results
run_test() {
    local test_name="$1"
    local test_script="$2"
    local log_file="$3"
    
    log_message "INFO" "Starting $test_name..."
    
    cd "$TEST_DIR"
    
    # Run test and capture output
    if [ -f "$test_script" ]; then
        if [ -n "$log_file" ]; then
            "$test_script" > "$log_file" 2>&1
        else
            "$test_script" 2>&1 | tee -a "$LOG_DIR/$(date +%Y%m%d).log"
        fi
        
        # Extract test results
        if [ -n "$log_file" ]; then
            total_tests=$(grep "Total Tests:" "$log_file" | tail -1 | cut -d ':' -f2 | tr -d ' ')
            passed_tests=$(grep "Passed:" "$log_file" | tail -1 | cut -d ':' -f2 | tr -d ' ')
            failed_tests=$(grep "Failed:" "$log_file" | tail -1 | cut -d ':' -f2 | tr -d ' ')
            
            log_message "INFO" "$test_name completed: $total_tests total, $passed_tests passed, $failed_tests failed"
            
            # Check if all tests passed
            if [ "$failed_tests" -eq 0 ]; then
                log_message "SUCCESS" "$test_name: ALL TESTS PASSED"
            else
                log_message "WARNING" "$test_name: $failed_tests tests failed"
            fi
        else
            log_message "ERROR" "Test script not found: $test_script"
        fi
    else
        log_message "ERROR" "Test script not found: $test_script"
    fi
}

# Function to generate summary report
generate_summary_report() {
    local report_file="$REPORT_DIR/summary_$(date +%Y%m%d_%H%M%S).txt"
    
    cd "$TEST_DIR"
    
    echo "KSP Lam Gabe Jaya v2.0 - Automated Test Summary" > "$report_file"
    echo "=====================================" >> "$report_file"
    echo "Date: $(date)" >> "$report_file"
    echo "Timestamp: $(date +%Y-%m-%d %H:%M:%S)" >> "$report_file"
    echo "" >> "$report_file"
    
    # Run quick test for summary
    echo "Quick Test Results:" >> "$report_file"
    ./quick_test.sh >> "$report_file" 2>&1
    echo "" >> "$report_file"
    
    echo "Comprehensive Test Results:" >> "$report_file"
    ./test_comprehensive.sh >> "$report_file" 2>&1
    echo "" >> "$report_file"
    
    echo "End-to-End Test Results:" >> "$report_file"
    ./test_end_to_end.sh >> "$report_file" 2>&1
    echo "" >> "$report_file"
    
    echo "Generated: $(date)" >> "$report_file"
    
    log_message "INFO" "Summary report generated: $report_file"
}

# Function to backup test results
backup_test_results() {
    local backup_file="$BACKUP_DIR/backup_$(date +%Y%m%d_%H%M%S).tar.gz"
    
    cd "$TEST_DIR"
    
    # Create backup archive
    tar -czf "$backup_file" \
        *.sh \
        *.md \
        *.txt \
        logs/ \
        reports/ \
        backups/ \
        2>/dev/null
    
    log_message "INFO" "Test results backed up to: $backup_file"
}

# Function to clean old files
cleanup_old_files() {
    local days_old="${1:-7}"
    
    # Clean old log files (older than specified days)
    find "$LOG_DIR" -name "*.log" -mtime +$days_old -delete 2>/dev/null
    
    # Clean old report files (older than specified days)
    find "$REPORT_DIR" -name "*.txt" -mtime +$days_old -delete 2>/dev/null
    
    # Clean old backup files (older than specified days)
    find "$BACKUP_DIR" -name "*.tar.gz" -mtime +$days_old -delete 2>/dev/null
    
    log_message "INFO" "Cleaned files older than $days_old days"
}

# Function to send notification (placeholder)
send_notification() {
    local message="$1"
    local priority="${2:-INFO}"
    
    # This would integrate with a notification service
    # For now, just log the message
    log_message "$priority" "$message"
}

# Main execution logic
echo ""
echo "🔧 Automated Testing Workflow"
echo "======================"

# Create necessary directories
create_directories

# Run tests based on parameters
case "${1:-all}" in
    "quick"|"fast")
        run_test "Quick Test" "quick_test.sh" "$LOG_DIR/quick_$(date +%Y%m%d).log"
        ;;
    "comprehensive"|"full"|"complete")
        run_test "Comprehensive Test" "test_comprehensive.sh" "$LOG_DIR/comprehensive_$(date +%Y%m%d).log"
        ;;
    "end-to-end"|"e2e"|"workflow")
        run_test "End-to-End Test" "test_end_to_end.sh" "$LOG_DIR/e2e_$(date +%Y%m%d).log"
        ;;
    "all")
        echo "Running all test suites..."
        echo ""
        run_test "Quick Test" "quick_test.sh" "$LOG_DIR/quick_$(date +%Y%m%d).log"
        run_test "Comprehensive Test" "test_comprehensive.sh" "$LOG_DIR/comprehensive_$(date +%Y%m%d).log"
        run_test "End-to-End Test" "test_end_to_end.sh" "$LOG_DIR/e2e_$(date +%Y%m%d).log"
        ;;
    "report"|"summary")
        generate_summary_report
        ;;
    "backup")
        backup_test_results
        ;;
    "cleanup")
        cleanup_old_files "${2:-7}"
        ;;
    "notify")
        send_notification "$2" "$3"
        ;;
    "help"|"-h"|"--help")
        echo ""
        echo "🔧 Automated Testing Help"
        echo "=================="
        echo ""
        echo "Usage: $0 [option]"
        echo ""
        echo "Options:"
        echo "  quick, fast          - Run quick test (7 tests)"
        echo "  comprehensive, full      - Run comprehensive test (71 tests)"
        echo "  end-to-end, e2e         - Run end-to-end test (29 tests)"
        echo "  all                    - Run all test suites"
        echo "  report                 - Generate summary report"
        "  backup                 - Backup test results"
        "  cleanup [days]         - Clean old files (default: 7 days)"
        "  notify [priority] [message]  - Send notification"
        "  help, -h, --help        - Show this help"
        echo ""
        echo "Examples:"
        echo "  $0 quick"
        echo "  $0 comprehensive"
        "  $0 all"
        echo "  $0 report"
        echo "  $0 backup"
        echo "  $0 cleanup 14"
        echo ""
        echo "🔧 Features:"
        echo "  - Automatic logging"
        echo "  - Test result capture"
        "  - Report generation"
        echo "  - Backup management"
        "  - Log cleanup"
        "  - Notification system"
        ;;
    *)
        echo "Running default test suite (quick test)..."
        run_test "Quick Test" "quick_test.sh" "$LOG_DIR/quick_$(date +%Y%m%d).log"
        ;;
esac

echo ""
echo "🎯 Automated Testing Completed!"
echo "========================"

# Send completion notification
send_notification "Automated testing completed" "INFO"
