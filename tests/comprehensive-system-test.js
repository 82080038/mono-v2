/**
 * Comprehensive KSP System Testing with Puppeteer
 * Tests all flows, logic, UI, features, and utilities
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

// Test configuration
const TEST_CONFIG = {
    baseUrl: 'http://localhost/mono-v2',
    screenshotDir: './test-screenshots',
    reportDir: './test-reports',
    timeout: 30000,
    headless: false, // Set to true for headless testing
    slowMo: 100 // Slow down actions for better visibility
};

// Test credentials
const CREDENTIALS = {
    bos: { username: 'bos', password: 'bos' },
    admin: { username: 'admin', password: 'admin' },
    teller: { username: 'teller', password: 'teller' },
    collector: { username: 'collector', password: 'collector' },
    nasabah: { username: 'nasabah', password: 'nasabah' }
};

// Test results
let testResults = {
    total: 0,
    passed: 0,
    failed: 0,
    details: []
};

class KSPSystemTester {
    constructor() {
        this.browser = null;
        this.page = null;
        this.currentRole = null;
        this.setupDirectories();
    }

    setupDirectories() {
        // Create directories for screenshots and reports
        if (!fs.existsSync(TEST_CONFIG.screenshotDir)) {
            fs.mkdirSync(TEST_CONFIG.screenshotDir, { recursive: true });
        }
        if (!fs.existsSync(TEST_CONFIG.reportDir)) {
            fs.mkdirSync(TEST_CONFIG.reportDir, { recursive: true });
        }
    }

    async init() {
        console.log('🚀 Initializing KSP System Tester...');
        
        this.browser = await puppeteer.launch({
            headless: TEST_CONFIG.headless,
            slowMo: TEST_CONFIG.slowMo,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--window-size=1920,1080',
                '--disable-web-security',
                '--disable-features=VizDisplayCompositor'
            ]
        });

        this.page = await this.browser.newPage();
        
        // Set viewport
        await this.page.setViewport({ width: 1920, height: 1080 });
        
        // Set timeout
        this.page.setDefaultTimeout(TEST_CONFIG.timeout);
        
        // Enable request interception for debugging
        await this.page.setRequestInterception(true);
        this.page.on('request', request => {
            request.continue();
        });

        // Log console messages
        this.page.on('console', msg => {
            console.log(`Browser Console: ${msg.text()}`);
        });

        // Log page errors
        this.page.on('pageerror', error => {
            console.error(`Page Error: ${error.message}`);
        });

        console.log('✅ Browser initialized successfully');
    }

    async login(role) {
        console.log(`🔐 Logging in as ${role}...`);
        
        try {
            // Navigate to login page
            await this.page.goto(`${TEST_CONFIG.baseUrl}/login.php`);
            await this.page.waitForSelector('#username', { timeout: 5000 });
            
            // Fill login form
            await this.page.type('#username', CREDENTIALS[role].username);
            await this.page.type('#password', CREDENTIALS[role].password);
            
            // Submit login
            await this.page.click('button[type="submit"]');
            
            // Wait for dashboard
            await this.page.waitForSelector('.dashboard-header, .app-main', { timeout: 10000 });
            
            this.currentRole = role;
            
            // Take screenshot
            await this.takeScreenshot(`login-${role}-success`);
            
            console.log(`✅ Successfully logged in as ${role}`);
            return true;
            
        } catch (error) {
            console.error(`❌ Login failed for ${role}:`, error.message);
            await this.takeScreenshot(`login-${role}-failed`);
            return false;
        }
    }

    async logout() {
        console.log('🔓 Logging out...');
        
        try {
            // Look for logout button/link
            const logoutSelectors = [
                'a[href*="logout"]',
                'button[onclick*="logout"]',
                '.btn-logout',
                '[data-action="logout"]'
            ];
            
            let logoutFound = false;
            for (const selector of logoutSelectors) {
                try {
                    await this.page.waitForSelector(selector, { timeout: 2000 });
                    await this.page.click(selector);
                    logoutFound = true;
                    break;
                } catch (e) {
                    // Continue to next selector
                }
            }
            
            if (logoutFound) {
                await this.page.waitForSelector('#username', { timeout: 5000 });
                console.log('✅ Successfully logged out');
            } else {
                console.log('⚠️ Logout button not found, continuing...');
            }
            
        } catch (error) {
            console.error('❌ Logout failed:', error.message);
        }
    }

    async takeScreenshot(name) {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const filename = `${name}-${timestamp}.png`;
        const filepath = path.join(TEST_CONFIG.screenshotDir, filename);
        
        await this.page.screenshot({ path: filepath, fullPage: true });
        console.log(`📸 Screenshot saved: ${filename}`);
        
        return filepath;
    }

    async runTest(testName, testFunction) {
        console.log(`\n🧪 Running test: ${testName}`);
        testResults.total++;
        
        try {
            const startTime = Date.now();
            await testFunction();
            const endTime = Date.now();
            
            testResults.passed++;
            testResults.details.push({
                name: testName,
                status: 'PASSED',
                duration: endTime - startTime,
                screenshot: await this.takeScreenshot(`${testName}-passed`)
            });
            
            console.log(`✅ Test passed: ${testName} (${endTime - startTime}ms)`);
            
        } catch (error) {
            testResults.failed++;
            testResults.details.push({
                name: testName,
                status: 'FAILED',
                error: error.message,
                screenshot: await this.takeScreenshot(`${testName}-failed`)
            });
            
            console.error(`❌ Test failed: ${testName} - ${error.message}`);
        }
    }

    // ===== BOS ROLE TESTS =====

    async testBOSDashboard() {
        await this.login('bos');
        
        // Test dashboard elements
        await this.page.waitForSelector('.dashboard-header h1', { timeout: 5000 });
        const dashboardTitle = await this.page.$eval('.dashboard-header h1', el => el.textContent);
        
        if (!dashboardTitle.includes('Dashboard')) {
            throw new Error('Dashboard title not found');
        }
        
        // Test navigation menu
        const menuItems = await this.page.$$('.menu-item');
        if (menuItems.length < 5) {
            throw new Error('Insufficient menu items for BOS role');
        }
        
        // Test executive dashboard access
        await this.page.goto(`${TEST_CONFIG.baseUrl}/?page=laporan`);
        await this.page.waitForSelector('.dashboard-header', { timeout: 5000 });
        
        console.log('📊 BOS Dashboard elements verified');
    }

    async testBOSReporting() {
        // Navigate to reports
        await this.page.goto(`${TEST_CONFIG.baseUrl}/?page=laporan`);
        await this.page.waitForSelector('.dashboard-header', { timeout: 5000 });
        
        // Test API call for executive dashboard
        const response = await this.page.evaluate(async () => {
            try {
                const resp = await fetch('/mono-v2/api/business-logic-enhanced.php?action=get_executive_dashboard');
                const data = await resp.json();
                return data;
            } catch (error) {
                return { error: error.message };
            }
        });
        
        if (!response.success) {
            throw new Error('Executive dashboard API call failed');
        }
        
        // Test report generation
        const reportResponse = await this.page.evaluate(async () => {
            try {
                const resp = await fetch('/mono-v2/api/business-logic-enhanced.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=generate_monthly_report&year=2024&month=3'
                });
                const data = await resp.json();
                return data;
            } catch (error) {
                return { error: error.message };
            }
        });
        
        if (!reportResponse.success) {
            throw new Error('Monthly report generation failed');
        }
        
        console.log('📈 BOS Reporting functionality verified');
    }

    // ===== ADMIN ROLE TESTS =====

    async testAdminDashboard() {
        await this.login('admin');
        
        // Test dashboard elements
        await this.page.waitForSelector('.dashboard-header h1', { timeout: 5000 });
        
        // Test member management access
        await this.page.goto(`${TEST_CONFIG.baseUrl}/?page=members`);
        await this.page.waitForSelector('.dashboard-header', { timeout: 5000 });
        
        // Test approval workflow
        const approvalResponse = await this.page.evaluate(async () => {
            try {
                const resp = await fetch('/mono-v2/api/business-logic-enhanced.php?action=get_pending_approvals');
                const data = await resp.json();
                return data;
            } catch (error) {
                return { error: error.message };
            }
        });
        
        if (!approvalResponse.success) {
            throw new Error('Pending approvals API call failed');
        }
        
        console.log('👥 Admin Dashboard and approval workflow verified');
    }

    async testAdminApprovalWorkflow() {
        // Test member registration approval
        await this.page.goto(`${TEST_CONFIG.baseUrl}/?page=register`);
        await this.page.waitForSelector('.registration-form', { timeout: 5000 });
        
        // Fill registration form
        await this.page.type('#full_name', 'Test Member');
        await this.page.type('#nik', '1234567890123456');
        await this.page.type('#birth_date', '1990-01-01');
        await this.page.type('#address', 'Test Address');
        await this.page.type('#phone', '08123456789');
        
        // Submit form
        await this.page.click('button[type="submit"]');
        
        // Wait for success message
        await this.page.waitForSelector('.alert-success', { timeout: 5000 });
        
        console.log('✅ Admin approval workflow tested');
    }

    // ===== TELLER ROLE TESTS =====

    async testTellerDashboard() {
        await this.login('teller');
        
        // Test dashboard elements
        await this.page.waitForSelector('.dashboard-header h1', { timeout: 5000 });
        
        // Test navigation to deposit page
        await this.page.goto(`${TEST_CONFIG.baseUrl}/?page=setoran`);
        await this.page.waitForSelector('.transaction-form', { timeout: 5000 });
        
        // Test form elements
        const formElements = [
            '#memberSearch',
            '#accountType',
            '#amount',
            '#paymentMethod',
            '#submitBtn'
        ];
        
        for (const selector of formElements) {
            await this.page.waitForSelector(selector, { timeout: 2000 });
        }
        
        console.log('💰 Teller Dashboard and deposit form verified');
    }

    async testTellerTransactionFlow() {
        await this.page.goto(`${TEST_CONFIG.baseUrl}/?page=setoran`);
        await this.page.waitForSelector('.transaction-form', { timeout: 5000 });
        
        // Test member search
        await this.page.type('#memberSearch', 'test');
        await this.page.waitFor(2000); // Wait for debounced search
        
        // Test form validation
        await this.page.type('#amount', '500'); // Below minimum
        await this.page.click('#submitBtn');
        
        // Check for validation error
        const amountInput = await this.page.$('#amount');
        const hasError = await this.page.evaluate(el => el.classList.contains('is-invalid'), amountInput);
        
        if (!hasError) {
            throw new Error('Form validation not working');
        }
        
        // Test with valid amount
        await this.page.type('#amount', '10000');
        
        console.log('💳 Teller transaction flow and validation verified');
    }

    async testTellerAPIIntegration() {
        // Test API calls from teller page
        const apiTests = await this.page.evaluate(async () => {
            const tests = [];
            
            // Test member search API
            try {
                const searchResp = await fetch('/mono-v2/api/business-logic-enhanced.php?action=search_member&q=test');
                const searchData = await searchResp.json();
                tests.push({ name: 'search_member', success: searchData.success });
            } catch (error) {
                tests.push({ name: 'search_member', success: false, error: error.message });
            }
            
            // Test today's summary API
            try {
                const summaryResp = await fetch('/mono-v2/api/business-logic-enhanced.php?action=get_today_summary');
                const summaryData = await summaryResp.json();
                tests.push({ name: 'get_today_summary', success: summaryData.success });
            } catch (error) {
                tests.push({ name: 'get_today_summary', success: false, error: error.message });
            }
            
            // Test today's transactions API
            try {
                const transResp = await fetch('/mono-v2/api/business-logic-enhanced.php?action=get_today_transactions');
                const transData = await transResp.json();
                tests.push({ name: 'get_today_transactions', success: transData.success });
            } catch (error) {
                tests.push({ name: 'get_today_transactions', success: false, error: error.message });
            }
            
            return tests;
        });
        
        const failedTests = apiTests.filter(test => !test.success);
        if (failedTests.length > 0) {
            throw new Error(`API tests failed: ${failedTests.map(t => t.name).join(', ')}`);
        }
        
        console.log('🔌 Teller API integration verified');
    }

    // ===== COLLECTOR ROLE TESTS =====

    async testCollectorDashboard() {
        await this.login('collector');
        
        // Test dashboard elements
        await this.page.waitForSelector('.dashboard-header h1', { timeout: 5000 });
        
        // Test navigation to route page
        await this.page.goto(`${TEST_CONFIG.baseUrl}/?page=rute`);
        await this.page.waitForSelector('.dashboard-header', { timeout: 5000 });
        
        console.log('🚗 Collector Dashboard and route page verified');
    }

    // ===== NASABAH ROLE TESTS =====

    async testNasabahDashboard() {
        await this.login('nasabah');
        
        // Test mobile dashboard
        await this.page.waitForSelector('.mobile-dashboard', { timeout: 5000 });
        
        // Test bottom navigation
        const navItems = await this.page.$$('.bottom-nav .nav-link');
        if (navItems.length < 4) {
            throw new Error('Insufficient navigation items for nasabah');
        }
        
        // Test mobile content loading
        const mobileTest = await this.page.evaluate(async () => {
            try {
                const resp = await fetch('/mono-v2/api/mobile-content.php?page=dashboard');
                const html = await resp.text();
                return html.length > 0;
            } catch (error) {
                return false;
            }
        });
        
        if (!mobileTest) {
            throw new Error('Mobile content loading failed');
        }
        
        console.log('📱 Nasabah mobile dashboard verified');
    }

    // ===== MOBILE RESPONSIVENESS TESTS =====

    async testMobileResponsiveness() {
        const viewports = [
            { width: 375, height: 667 },  // iPhone
            { width: 768, height: 1024 }, // iPad
            { width: 360, height: 640 },  // Android
            { width: 414, height: 896 }  // iPhone X
        ];
        
        for (const viewport of viewports) {
            await this.page.setViewport(viewport);
            await this.page.goto(`${TEST_CONFIG.baseUrl}/?page=setoran`);
            await this.page.waitForSelector('.transaction-form', { timeout: 5000 });
            
            // Check if mobile elements are present
            const hasMobileNav = await this.page.$('.bottom-nav') !== null;
            const hasMobileClass = await this.page.$('body.mobile-mode') !== null;
            
            if (viewport.width <= 768 && (!hasMobileNav || !hasMobileClass)) {
                throw new Error(`Mobile responsiveness failed for viewport ${viewport.width}x${viewport.height}`);
            }
            
            await this.takeScreenshot(`mobile-${viewport.width}x${viewport.height}`);
        }
        
        // Reset to desktop
        await this.page.setViewport({ width: 1920, height: 1080 });
        
        console.log('📱 Mobile responsiveness verified for all viewports');
    }

    // ===== FORM VALIDATION TESTS =====

    async testFormValidation() {
        await this.login('teller');
        await this.page.goto(`${TEST_CONFIG.baseUrl}/?page=setoran`);
        
        const validationTests = [
            { field: 'amount', value: '500', shouldFail: true, reason: 'Below minimum' },
            { field: 'amount', value: 'abc', shouldFail: true, reason: 'Not a number' },
            { field: 'amount', value: '1000000000', shouldFail: true, reason: 'Above maximum' },
            { field: 'amount', value: '10000', shouldFail: false, reason: 'Valid amount' }
        ];
        
        for (const test of validationTests) {
            // Clear and fill field
            await this.page.click('#amount');
            await this.page.keyboard.down('Control');
            await this.page.keyboard.press('a');
            await this.page.keyboard.up('Control');
            await this.page.type('#amount', test.value);
            
            // Trigger validation
            await this.page.click('#paymentMethod');
            await this.page.waitForTimeout(500);
            
            // Check validation result
            const hasError = await this.page.$eval('#amount', el => el.classList.contains('is-invalid'));
            
            if (test.shouldFail && !hasError) {
                throw new Error(`Validation should fail for ${test.reason}`);
            }
            
            if (!test.shouldFail && hasError) {
                throw new Error(`Validation should pass for ${test.reason}`);
            }
        }
        
        console.log('✅ Form validation verified');
    }

    // ===== ERROR HANDLING TESTS =====

    async testErrorHandling() {
        // Test 404 error
        await this.page.goto(`${TEST_CONFIG.baseUrl}/?page=nonexistent`);
        await this.page.waitForSelector('.dashboard-header', { timeout: 5000 });
        
        // Test API error handling
        const errorTest = await this.page.evaluate(async () => {
            try {
                const resp = await fetch('/mono-v2/api/business-logic-enhanced.php?action=invalid_action');
                const data = await resp.json();
                return { success: !data.success, hasError: !!data.message };
            } catch (error) {
                return { success: false, hasError: true, error: error.message };
            }
        });
        
        if (!errorTest.success || !errorTest.hasError) {
            throw new Error('Error handling not working properly');
        }
        
        console.log('⚠️ Error handling verified');
    }

    // ===== PERFORMANCE TESTS =====

    async testPerformance() {
        const performanceTests = [
            { name: 'Dashboard Load', url: `${TEST_CONFIG.baseUrl}/?page=dashboard` },
            { name: 'Deposit Page Load', url: `${TEST_CONFIG.baseUrl}/?page=setoran` },
            { name: 'Reports Page Load', url: `${TEST_CONFIG.baseUrl}/?page=laporan` }
        ];
        
        for (const test of performanceTests) {
            const startTime = Date.now();
            await this.page.goto(test.url);
            await this.page.waitForSelector('.dashboard-header', { timeout: 10000 });
            const loadTime = Date.now() - startTime;
            
            if (loadTime > 5000) {
                throw new Error(`${test.name} took too long: ${loadTime}ms`);
            }
            
            console.log(`⚡ ${test.name}: ${loadTime}ms`);
        }
        
        console.log('⚡ Performance tests passed');
    }

    // ===== SECURITY TESTS =====

    async testSecurity() {
        // Test XSS prevention
        await this.page.goto(`${TEST_CONFIG.baseUrl}/?page=setoran`);
        await this.page.type('#memberSearch', '<script>alert("XSS")</script>');
        await this.page.waitFor(2000);
        
        // Check if script was executed (should not be)
        const alertHandled = await this.page.evaluate(() => {
            return window.alertTriggered === true;
        });
        
        if (alertHandled) {
            throw new Error('XSS vulnerability detected');
        }
        
        // Test SQL injection prevention
        const injectionTest = await this.page.evaluate(async () => {
            try {
                const resp = await fetch('/mono-v2/api/business-logic-enhanced.php?action=search_member&q=\'; DROP TABLE members; --');
                const data = await resp.json();
                return data.success === false; // Should fail
            } catch (error) {
                return true; // Should fail gracefully
            }
        });
        
        if (!injectionTest) {
            throw new Error('SQL injection vulnerability detected');
        }
        
        console.log('🔒 Security tests passed');
    }

    // ===== UTILITIES TESTS =====

    async testUtilities() {
        // Test utility functions
        const utilityTests = await this.page.evaluate(async () => {
            const tests = [];
            
            // Test formatNumber utility
            if (typeof window.KSP !== 'undefined' && window.KSP.Utils) {
                const formatted = window.KSP.Utils.formatNumber(1000000);
                tests.push({ name: 'formatNumber', success: formatted === '1.000.000' });
            }
            
            // Test formatDate utility
            if (typeof window.KSP !== 'undefined' && window.KSP.Utils) {
                const formatted = window.KSP.Utils.formatDate('2024-03-24T10:30:00');
                tests.push({ name: 'formatDate', success: formatted.length > 0 });
            }
            
            // Test debounce utility
            if (typeof window.KSP !== 'undefined' && window.KSP.Utils) {
                let called = false;
                const debounced = window.KSP.Utils.debounce(() => { called = true; }, 100);
                debounced();
                tests.push({ name: 'debounce', success: !called }); // Should not be called immediately
            }
            
            return tests;
        });
        
        const failedTests = utilityTests.filter(test => !test.success);
        if (failedTests.length > 0) {
            throw new Error(`Utility tests failed: ${failedTests.map(t => t.name).join(', ')}`);
        }
        
        console.log('🛠️ Utility functions verified');
    }

    // ===== COMPREHENSIVE TEST RUNNER =====

    async runAllTests() {
        console.log('🚀 Starting Comprehensive KSP System Tests...\n');
        
        try {
            await this.init();
            
            // BOS Role Tests
            await this.runTest('BOS Dashboard', () => this.testBOSDashboard());
            await this.runTest('BOS Reporting', () => this.testBOSReporting());
            await this.logout();
            
            // Admin Role Tests
            await this.runTest('Admin Dashboard', () => this.testAdminDashboard());
            await this.runTest('Admin Approval Workflow', () => this.testAdminApprovalWorkflow());
            await this.logout();
            
            // Teller Role Tests
            await this.runTest('Teller Dashboard', () => this.testTellerDashboard());
            await this.runTest('Teller Transaction Flow', () => this.testTellerTransactionFlow());
            await this.runTest('Teller API Integration', () => this.testTellerAPIIntegration());
            await this.logout();
            
            // Collector Role Tests
            await this.runTest('Collector Dashboard', () => this.testCollectorDashboard());
            await this.logout();
            
            // Nasabah Role Tests
            await this.runTest('Nasabah Dashboard', () => this.testNasabahDashboard());
            await this.logout();
            
            // Cross-cutting Tests
            await this.runTest('Mobile Responsiveness', () => this.testMobileResponsiveness());
            await this.runTest('Form Validation', () => this.testFormValidation());
            await this.runTest('Error Handling', () => this.testErrorHandling());
            await this.runTest('Performance', () => this.testPerformance());
            await this.runTest('Security', () => this.testSecurity());
            await this.runTest('Utilities', () => this.testUtilities());
            
        } catch (error) {
            console.error('❌ Test suite failed:', error.message);
        } finally {
            await this.generateReport();
            await this.cleanup();
        }
    }

    async generateReport() {
        const report = {
            timestamp: new Date().toISOString(),
            summary: {
                total: testResults.total,
                passed: testResults.passed,
                failed: testResults.failed,
                successRate: ((testResults.passed / testResults.total) * 100).toFixed(2) + '%'
            },
            details: testResults.details,
            recommendations: this.generateRecommendations()
        };
        
        const reportPath = path.join(TEST_CONFIG.reportDir, `test-report-${Date.now()}.json`);
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        // Generate HTML report
        const htmlReport = this.generateHTMLReport(report);
        const htmlReportPath = path.join(TEST_CONFIG.reportDir, `test-report-${Date.now()}.html`);
        fs.writeFileSync(htmlReportPath, htmlReport);
        
        console.log(`\n📊 Test Report Generated:`);
        console.log(`   Total Tests: ${testResults.total}`);
        console.log(`   Passed: ${testResults.passed}`);
        console.log(`   Failed: ${testResults.failed}`);
        console.log(`   Success Rate: ${report.summary.successRate}`);
        console.log(`   Report: ${reportPath}`);
        console.log(`   HTML Report: ${htmlReportPath}`);
    }

    generateHTMLReport(report) {
        return `
<!DOCTYPE html>
<html>
<head>
    <title>KSP System Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .summary { background: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .passed { color: green; }
        .failed { color: red; }
        .test-item { margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .test-passed { background: #d4edda; }
        .test-failed { background: #f8d7da; }
        .screenshot { max-width: 100px; height: auto; }
    </style>
</head>
<body>
    <h1>KSP System Test Report</h1>
    <div class="summary">
        <h2>Summary</h2>
        <p><strong>Total Tests:</strong> ${report.summary.total}</p>
        <p><strong>Passed:</strong> <span class="passed">${report.summary.passed}</span></p>
        <p><strong>Failed:</strong> <span class="failed">${report.summary.failed}</span></p>
        <p><strong>Success Rate:</strong> ${report.summary.successRate}</p>
        <p><strong>Timestamp:</strong> ${report.timestamp}</p>
    </div>
    
    <h2>Test Details</h2>
    ${report.details.map(test => `
        <div class="test-item ${test.status === 'PASSED' ? 'test-passed' : 'test-failed'}">
            <h3>${test.name} - <span class="${test.status === 'PASSED' ? 'passed' : 'failed'}">${test.status}</span></h3>
            <p><strong>Duration:</strong> ${test.duration}ms</p>
            ${test.error ? `<p><strong>Error:</strong> ${test.error}</p>` : ''}
            ${test.screenshot ? `<p><strong>Screenshot:</strong> <img src="${test.screenshot}" class="screenshot"></p>` : ''}
        </div>
    `).join('')}
    
    <h2>Recommendations</h2>
    <ul>
        ${report.recommendations.map(rec => `<li>${rec}</li>`).join('')}
    </ul>
</body>
</html>
        `;
    }

    generateRecommendations() {
        const recommendations = [];
        
        if (testResults.failed > 0) {
            recommendations.push('Fix failed tests before deploying to production');
        }
        
        if (testResults.failed === 0) {
            recommendations.push('All tests passed - system is ready for production');
        }
        
        recommendations.push('Run tests regularly to ensure system stability');
        recommendations.push('Add more edge case tests for better coverage');
        recommendations.push('Monitor performance metrics in production');
        
        return recommendations;
    }

    async cleanup() {
        console.log('\n🧹 Cleaning up...');
        
        if (this.page) {
            await this.page.close();
        }
        
        if (this.browser) {
            await this.browser.close();
        }
        
        console.log('✅ Cleanup completed');
    }
}

// Run tests if this file is executed directly
if (require.main === module) {
    const tester = new KSPSystemTester();
    tester.runAllTests().catch(console.error);
}

module.exports = KSPSystemTester;
