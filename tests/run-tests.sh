#!/bin/bash

# KSP System Test Runner
# Comprehensive testing script for KSP Lam Gabe Jaya system

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
TEST_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$TEST_DIR")"
SCREENSHOT_DIR="$TEST_DIR/test-screenshots"
REPORT_DIR="$TEST_DIR/test-reports"

# Functions
print_header() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Check dependencies
check_dependencies() {
    print_header "Checking Dependencies"
    
    # Check Node.js
    if ! command -v node &> /dev/null; then
        print_error "Node.js is not installed. Please install Node.js 16 or higher."
        exit 1
    fi
    
    NODE_VERSION=$(node -v | cut -d'v' -f2 | cut -d'.' -f1)
    if [ "$NODE_VERSION" -lt 16 ]; then
        print_error "Node.js version $NODE_VERSION is too old. Please upgrade to Node.js 16 or higher."
        exit 1
    fi
    
    print_success "Node.js $(node -v) found"
    
    # Check npm
    if ! command -v npm &> /dev/null; then
        print_error "npm is not installed."
        exit 1
    fi
    
    print_success "npm $(npm -v) found"
    
    # Check Puppeteer
    if [ ! -d "$TEST_DIR/node_modules/puppeteer" ]; then
        print_warning "Puppeteer not found. Installing..."
        cd "$TEST_DIR"
        npm install
        print_success "Puppeteer installed"
    else
        print_success "Puppeteer found"
    fi
    
    # Check if XAMPP/Apache is running
    if ! curl -s "http://localhost" > /dev/null; then
        print_error "Apache/XAMPP is not running. Please start Apache/XAMPP."
        exit 1
    fi
    
    print_success "Apache/XAMPP is running"
    
    # Check if KSP application is accessible
    if ! curl -s "http://localhost/mono-v2/login.php" > /dev/null; then
        print_error "KSP application is not accessible. Please check your configuration."
        exit 1
    fi
    
    print_success "KSP application is accessible"
}

# Setup test environment
setup_environment() {
    print_header "Setting Up Test Environment"
    
    # Create directories
    mkdir -p "$SCREENSHOT_DIR"
    mkdir -p "$REPORT_DIR"
    
    # Clean old screenshots and reports
    rm -f "$SCREENSHOT_DIR"/*.png
    rm -f "$REPORT_DIR"/*.json
    rm -f "$REPORT_DIR"/*.html
    
    print_success "Test environment setup completed"
}

# Run comprehensive tests
run_comprehensive_tests() {
    print_header "Running Comprehensive System Tests"
    
    cd "$TEST_DIR"
    
    # Set environment variables
    export NODE_ENV=test
    export PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
    
    # Run tests
    if [ "$1" = "--headless" ]; then
        print_info "Running tests in headless mode..."
        node comprehensive-system-test.js --headless
    elif [ "$1" = "--visual" ]; then
        print_info "Running tests in visual mode (browser will be visible)..."
        node comprehensive-system-test.js
    else
        print_info "Running tests in default mode..."
        node comprehensive-system-test.js
    fi
    
    # Check if tests completed successfully
    if [ $? -eq 0 ]; then
        print_success "All tests completed successfully"
    else
        print_error "Some tests failed"
        exit 1
    fi
}

# Generate test summary
generate_summary() {
    print_header "Test Summary"
    
    # Find the latest report
    LATEST_REPORT=$(ls -t "$REPORT_DIR"/test-report-*.json 2>/dev/null | head -1)
    
    if [ -z "$LATEST_REPORT" ]; then
        print_error "No test report found"
        return
    fi
    
    # Extract summary from report
    if command -v jq &> /dev/null; then
        TOTAL=$(jq -r '.summary.total' "$LATEST_REPORT")
        PASSED=$(jq -r '.summary.passed' "$LATEST_REPORT")
        FAILED=$(jq -r '.summary.failed' "$LATEST_REPORT")
        RATE=$(jq -r '.summary.successRate' "$LATEST_REPORT")
        
        echo "Total Tests: $TOTAL"
        echo -e "Passed: ${GREEN}$PASSED${NC}"
        echo -e "Failed: ${RED}$FAILED${NC}"
        echo "Success Rate: $RATE"
    else
        print_warning "jq not found. Install jq for better report formatting."
        print_info "Report available at: $LATEST_REPORT"
    fi
    
    # Show HTML report location
    LATEST_HTML_REPORT=$(ls -t "$REPORT_DIR"/test-report-*.html 2>/dev/null | head -1)
    if [ -n "$LATEST_HTML_REPORT" ]; then
        print_info "HTML Report: $LATEST_HTML_REPORT"
        print_info "Open the HTML report in your browser to view detailed results."
    fi
}

# Quick test (subset of tests)
run_quick_tests() {
    print_header "Running Quick Tests"
    
    cd "$TEST_DIR"
    
    # Create a quick test version
    cat > quick-test.js << 'EOF'
const KSPSystemTester = require('./comprehensive-system-test.js');

class QuickTester extends KSPSystemTester {
    async runQuickTests() {
        console.log('🚀 Running Quick Tests...\n');
        
        try {
            await this.init();
            
            // Test only critical functionality
            await this.runTest('BOS Dashboard', () => this.testBOSDashboard());
            await this.logout();
            
            await this.runTest('Teller Dashboard', () => this.testTellerDashboard());
            await this.logout();
            
            await this.runTest('Mobile Responsiveness', () => this.testMobileResponsiveness());
            await this.runTest('Form Validation', () => this.testFormValidation());
            
        } catch (error) {
            console.error('❌ Quick test suite failed:', error.message);
        } finally {
            await this.generateReport();
            await this.cleanup();
        }
    }
}

const tester = new QuickTester();
tester.runQuickTests().catch(console.error);
EOF
    
    node quick-test.js
    
    # Clean up
    rm -f quick-test.js
    
    print_success "Quick tests completed"
}

# Performance test
run_performance_tests() {
    print_header "Running Performance Tests"
    
    cd "$TEST_DIR"
    
    # Create performance test
    cat > performance-test.js << 'EOF'
const puppeteer = require('puppeteer');

class PerformanceTester {
    constructor() {
        this.browser = null;
        this.page = null;
    }
    
    async init() {
        this.browser = await puppeteer.launch({ headless: true });
        this.page = await this.browser.newPage();
        await this.page.setViewport({ width: 1920, height: 1080 });
    }
    
    async measurePageLoad(url, testName) {
        const startTime = Date.now();
        await this.page.goto(url);
        await this.page.waitForSelector('.dashboard-header', { timeout: 10000 });
        const loadTime = Date.now() - startTime;
        
        // Get performance metrics
        const metrics = await this.page.evaluate(() => {
            const navigation = performance.getEntriesByType('navigation')[0];
            return {
                domContentLoaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
                load: navigation.loadEventEnd - navigation.loadEventStart,
                firstPaint: performance.getEntriesByType('paint')[0]?.startTime || 0,
                firstContentfulPaint: performance.getEntriesByType('paint')[1]?.startTime || 0
            };
        });
        
        console.log(`${testName}: ${loadTime}ms (DOM: ${metrics.domContentLoaded}ms, Load: ${metrics.load}ms)`);
        
        return { loadTime, metrics };
    }
    
    async runPerformanceTests() {
        console.log('⚡ Running Performance Tests...\n');
        
        const tests = [
            { name: 'Dashboard Load', url: 'http://localhost/mono-v2/?page=dashboard' },
            { name: 'Deposit Page Load', url: 'http://localhost/mono-v2/?page=setoran' },
            { name: 'Reports Page Load', url: 'http://localhost/mono-v2/?page=laporan' }
        ];
        
        const results = [];
        
        for (const test of tests) {
            const result = await this.measurePageLoad(test.url, test.name);
            results.push({ ...test, ...result });
        }
        
        // Calculate averages
        const avgLoadTime = results.reduce((sum, r) => sum + r.loadTime, 0) / results.length;
        console.log(`\nAverage Load Time: ${avgLoadTime.toFixed(2)}ms`);
        
        // Check performance thresholds
        const threshold = 3000; // 3 seconds
        const slowPages = results.filter(r => r.loadTime > threshold);
        
        if (slowPages.length > 0) {
            console.log('\n⚠️  Slow pages (>' + threshold + 'ms):');
            slowPages.forEach(page => {
                console.log(`  - ${page.name}: ${page.loadTime}ms`);
            });
        } else {
            console.log('\n✅ All pages loaded within acceptable time limits');
        }
        
        return results;
    }
    
    async cleanup() {
        await this.browser.close();
    }
}

const tester = new PerformanceTester();
tester.init().then(() => {
    return tester.runPerformanceTests();
}).then(() => {
    return tester.cleanup();
}).catch(console.error);
EOF
    
    node performance-test.js
    
    # Clean up
    rm -f performance-test.js
    
    print_success "Performance tests completed"
}

# Show help
show_help() {
    echo "KSP System Test Runner"
    echo ""
    echo "Usage: $0 [OPTION]"
    echo ""
    echo "Options:"
    echo "  --help              Show this help message"
    echo "  --quick             Run quick tests (subset)"
    echo "  --performance       Run performance tests only"
    echo "  --headless          Run tests in headless mode"
    echo "  --visual            Run tests in visual mode (browser visible)"
    echo "  --setup             Setup test environment only"
    echo "  --check             Check dependencies only"
    echo "  --summary           Show test summary only"
    echo ""
    echo "Examples:"
    echo "  $0                  Run comprehensive tests"
    echo "  $0 --quick          Run quick tests"
    echo "  $0 --performance    Run performance tests"
    echo "  $0 --headless       Run tests in headless mode"
}

# Main execution
main() {
    case "${1:-}" in
        --help)
            show_help
            ;;
        --setup)
            check_dependencies
            setup_environment
            ;;
        --check)
            check_dependencies
            ;;
        --quick)
            check_dependencies
            setup_environment
            run_quick_tests
            generate_summary
            ;;
        --performance)
            check_dependencies
            setup_environment
            run_performance_tests
            ;;
        --headless)
            check_dependencies
            setup_environment
            run_comprehensive_tests --headless
            generate_summary
            ;;
        --visual)
            check_dependencies
            setup_environment
            run_comprehensive_tests --visual
            generate_summary
            ;;
        --summary)
            generate_summary
            ;;
        "")
            check_dependencies
            setup_environment
            run_comprehensive_tests
            generate_summary
            ;;
        *)
            print_error "Unknown option: $1"
            show_help
            exit 1
            ;;
    esac
}

# Run main function
main "$@"
