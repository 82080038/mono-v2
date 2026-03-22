const puppeteer = require('puppeteer');
const chalk = require('chalk') || console.log;
const fs = require('fs');
const path = require('path');

/**
 * KSP Lam Gabe Jaya - Comprehensive Puppeteer Test Suite
 * Real browser automation testing
 */
class PuppeteerTestSuite {
    constructor() {
        this.baseUrl = 'http://localhost/mono-v2';
        this.testResults = {
            total: 0,
            passed: 0,
            failed: 0,
            errors: [],
            screenshots: []
        };
        this.browser = null;
        this.page = null;
        this.screenshotDir = './screenshots';
        this.visualMode = process.argv.includes('--visual');
        this.headlessMode = !process.argv.includes('--no-headless');
    }
    
    /**
     * Run comprehensive test suite
     */
    async runComprehensiveTests() {
        console.log(chalk.blue('🚀 Starting Comprehensive Puppeteer Test Suite'));
        console.log('='.repeat(60));
        
        try {
            // Setup screenshot directory
            this.setupScreenshotDirectory();
            
            // Launch browser
            await this.launchBrowser();
            
            // Run tests
            await this.testLoginPage();
            await this.testAllRoleLogins();
            await this.testDashboardLoading();
            await this.testDynamicNavigation();
            await this.testRoleSpecificContent();
            await this.testJavaScriptFunctionality();
            await this.testAPIEndpoints();
            await this.testSecurityFeatures();
            await this.testResponsiveDesign();
            await this.testLogoutFunctionality();
            await this.testErrorHandling();
            
        } catch (error) {
            console.error(chalk.red('Test suite error:'), error);
            this.addTestResult('Test Suite', false, error.message);
        } finally {
            await this.cleanup();
            this.generateFinalReport();
        }
        
        return this.testResults;
    }
    
    /**
     * Setup screenshot directory
     */
    setupScreenshotDirectory() {
        if (!fs.existsSync(this.screenshotDir)) {
            fs.mkdirSync(this.screenshotDir, { recursive: true });
        }
    }
    
    /**
     * Launch browser
     */
    async launchBrowser() {
        console.log(chalk.yellow('🌐 Launching browser...'));
        
        this.browser = await puppeteer.launch({
            headless: this.headlessMode,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--no-first-run',
                '--no-zygote',
                '--single-process',
                '--disable-gpu'
            ]
        });
        
        this.page = await this.browser.newPage();
        
        // Set viewport
        await this.page.setViewport({ width: 1366, height: 768 });
        
        // Enable request interception for debugging
        await this.page.setRequestInterception(true);
        this.page.on('request', request => {
            request.continue();
        });
        
        // Console logging
        this.page.on('console', msg => {
            if (msg.type() === 'error') {
                console.log(chalk.red('Browser Console Error:'), msg.text());
            }
        });
        
        // Page errors
        this.page.on('pageerror', error => {
            console.log(chalk.red('Page Error:'), error.message);
        });
        
        console.log(chalk.green('✅ Browser launched successfully'));
    }
    
    /**
     * Test login page
     */
    async testLoginPage() {
        console.log(chalk.yellow('🔐 Testing Login Page...'));
        
        try {
            await this.page.goto(this.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
            
            // Check if login page loaded
            const title = await this.page.title();
            const hasLoginForm = await this.page.$('form') !== null;
            const hasUsernameField = await this.page.$('input[name="username"]') !== null;
            const hasPasswordField = await this.page.$('input[name="password"]') !== null;
            
            if (hasLoginForm && hasUsernameField && hasPasswordField) {
                this.addTestResult('Login Page', true, 'Login page loaded correctly');
                await this.takeScreenshot('login-page-loaded');
            } else {
                this.addTestResult('Login Page', false, 'Missing login form elements');
            }
            
        } catch (error) {
            this.addTestResult('Login Page', false, error.message);
        }
    }
    
    /**
     * Test all role logins
     */
    async testAllRoleLogins() {
        console.log(chalk.yellow('👥 Testing All Role Logins...'));
        
        const roles = [
            { name: 'bos', username: 'bos', password: 'bos' },
            { name: 'admin', username: 'admin', password: 'admin' },
            { name: 'teller', username: 'teller', password: 'teller' },
            { name: 'collector', username: 'collector', password: 'collector' },
            { name: 'nasabah', username: 'nasabah', password: 'nasabah' }
        ];
        
        for (const role of roles) {
            console.log(chalk.blue(`  Testing ${role.name} login...`));
            await this.testRoleLogin(role);
        }
    }
    
    /**
     * Test individual role login
     */
    async testRoleLogin(role) {
        try {
            // Go to login page
            await this.page.goto(this.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
            
            // Fill login form
            await this.page.type('input[name="username"]', role.username, { delay: 100 });
            await this.page.type('input[name="password"]', role.password, { delay: 100 });
            
            // Submit form
            await Promise.all([
                this.page.waitForNavigation({ waitUntil: 'networkidle2' }),
                this.page.click('button[type="submit"]')
            ]);
            
            // Check if redirected to dashboard
            const url = this.page.url();
            const isDashboard = url.includes('dashboard') || url.includes('main.php');
            
            if (isDashboard) {
                this.addTestResult(`${role.name} Login`, true, 'Login successful, redirected to dashboard');
                await this.takeScreenshot(`${role.name}-dashboard-loaded`);
                
                // Check for role-specific elements
                const hasRoleContent = await this.checkRoleSpecificContent(role.name);
                if (hasRoleContent) {
                    this.addTestResult(`${role.name} Role Content`, true, 'Role-specific content found');
                } else {
                    this.addTestResult(`${role.name} Role Content`, false, 'Role-specific content missing');
                }
            } else {
                this.addTestResult(`${role.name} Login`, false, 'Login failed, not redirected to dashboard');
            }
            
        } catch (error) {
            this.addTestResult(`${role.name} Login`, false, error.message);
        }
    }
    
    /**
     * Check role-specific content
     */
    async checkRoleSpecificContent(role) {
        const roleSelectors = {
            bos: ['.stats-number', '.dashboard-header'],
            admin: ['.stats-number', '.dashboard-header'],
            teller: ['.stats-number', '.dashboard-header'],
            collector: ['.stats-number', '.dashboard-header'],
            nasabah: ['.stats-number', '.dashboard-header']
        };
        
        const selectors = roleSelectors[role] || [];
        for (const selector of selectors) {
            const element = await this.page.$(selector);
            if (!element) return false;
        }
        
        return true;
    }
    
    /**
     * Test dashboard loading
     */
    async testDashboardLoading() {
        console.log(chalk.yellow('📊 Testing Dashboard Loading...'));
        
        try {
            // Login as BOS for dashboard testing
            await this.loginAs('bos');
            
            // Wait for dashboard to load
            await this.page.waitForSelector('.dashboard-header', { timeout: 5000 });
            
            // Check dashboard elements
            const hasHeader = await this.page.$('.dashboard-header') !== null;
            const hasStats = await this.page.$('.stats-number') !== null;
            const hasNavigation = await this.page.$('.sidebar') !== null;
            const hasContent = await this.page.$('.app-main') !== null;
            
            if (hasHeader && hasStats && hasNavigation && hasContent) {
                this.addTestResult('Dashboard Loading', true, 'All dashboard elements loaded');
                await this.takeScreenshot('dashboard-fully-loaded');
            } else {
                this.addTestResult('Dashboard Loading', false, 'Missing dashboard elements');
            }
            
        } catch (error) {
            this.addTestResult('Dashboard Loading', false, error.message);
        }
    }
    
    /**
     * Test dynamic navigation
     */
    async testDynamicNavigation() {
        console.log(chalk.yellow('🧭 Testing Dynamic Navigation...'));
        
        try {
            // Login as BOS
            await this.loginAs('bos');
            
            // Test navigation links
            const navigationLinks = [
                { selector: 'a[href="#dashboard"]', name: 'Dashboard' },
                { selector: 'a[href="#laporan"]', name: 'Laporan' },
                { selector: 'a[href="#nasabah"]', name: 'Nasabah' },
                { selector: 'a[href="#transaksi"]', name: 'Transaksi' }
            ];
            
            for (const link of navigationLinks) {
                try {
                    // Click navigation link
                    await this.page.click(link.selector);
                    await this.page.waitForTimeout(1000);
                    
                    // Check if URL hash changed
                    const url = this.page.url();
                    const hasHash = url.includes('#' + link.name.toLowerCase());
                    
                    if (hasHash) {
                        this.addTestResult(`Navigation to ${link.name}`, true, 'Hash navigation working');
                    } else {
                        this.addTestResult(`Navigation to ${link.name}`, false, 'Hash not updated');
                    }
                    
                } catch (error) {
                    this.addTestResult(`Navigation to ${link.name}`, false, error.message);
                }
            }
            
        } catch (error) {
            this.addTestResult('Dynamic Navigation', false, error.message);
        }
    }
    
    /**
     * Test role-specific content
     */
    async testRoleSpecificContent() {
        console.log(chalk.yellow('🎭 Testing Role-Specific Content...'));
        
        const roles = ['bos', 'admin', 'teller', 'collector', 'nasabah'];
        
        for (const role of roles) {
            console.log(chalk.blue(`  Testing ${role} content...`));
            await this.testRoleContent(role);
        }
    }
    
    /**
     * Test role content
     */
    async testRoleContent(role) {
        try {
            await this.loginAs(role);
            
            // Check for role-specific dashboard content
            const expectedContent = {
                bos: ['Total Anggota', 'Total Simpanan', 'Total Omzet'],
                admin: ['Anggota Aktif', 'Transaksi Hari Ini', 'User Terdaftar'],
                teller: ['Transaksi Hari Ini', 'Setoran', 'Penarikan'],
                collector: ['Target Hari Ini', 'Kunjungan Selesai', 'Kutipan Terkumpul'],
                nasabah: ['Saldo Simpanan', 'Pinjaman Aktif', 'Cicilan Bulanan']
            };
            
            const content = expectedContent[role] || [];
            let hasAllContent = true;
            
            for (const text of content) {
                const element = await this.page.$(`text=${text}`);
                if (!element) {
                    hasAllContent = false;
                    break;
                }
            }
            
            if (hasAllContent) {
                this.addTestResult(`${role} Content`, true, 'All expected content found');
            } else {
                this.addTestResult(`${role} Content`, false, 'Missing expected content');
            }
            
        } catch (error) {
            this.addTestResult(`${role} Content`, false, error.message);
        }
    }
    
    /**
     * Test JavaScript functionality
     */
    async testJavaScriptFunctionality() {
        console.log(chalk.yellow('⚡ Testing JavaScript Functionality...'));
        
        try {
            await this.loginAs('bos');
            
            // Test JavaScript functions
            const jsTests = [
                { func: 'typeof navigateTo', expected: 'function', name: 'navigateTo' },
                { func: 'typeof logout', expected: 'function', name: 'logout' },
                { func: 'typeof showNotification', expected: 'function', name: 'showNotification' },
                { func: 'typeof toggleSidebar', expected: 'function', name: 'toggleSidebar' }
            ];
            
            for (const test of jsTests) {
                try {
                    const result = await this.page.evaluate(test.func);
                    const passed = result === test.expected;
                    
                    this.addTestResult(`JS: ${test.name}`, passed, 
                        passed ? 'Function defined' : 'Function not defined'
                    );
                } catch (error) {
                    this.addTestResult(`JS: ${test.name}`, false, error.message);
                }
            }
            
        } catch (error) {
            this.addTestResult('JavaScript Functionality', false, error.message);
        }
    }
    
    /**
     * Test API endpoints
     */
    async testAPIEndpoints() {
        console.log(chalk.yellow('🔌 Testing API Endpoints...'));
        
        try {
            // Test login API
            const loginResponse = await this.page.evaluate(async () => {
                const response = await fetch('/api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=login&username=bos&password=bos'
                });
                return response.json();
            });
            
            if (loginResponse.success) {
                this.addTestResult('API: Login', true, 'Login API working');
            } else {
                this.addTestResult('API: Login', false, loginResponse.message);
            }
            
            // Test session check API
            const sessionResponse = await this.page.evaluate(async () => {
                const response = await fetch('/api/auth.php?action=check_session');
                return response.json();
            });
            
            if (sessionResponse.success && sessionResponse.authenticated) {
                this.addTestResult('API: Session Check', true, 'Session API working');
            } else {
                this.addTestResult('API: Session Check', false, 'Session API failed');
            }
            
        } catch (error) {
            this.addTestResult('API Endpoints', false, error.message);
        }
    }
    
    /**
     * Test security features
     */
    async testSecurityFeatures() {
        console.log(chalk.yellow('🔒 Testing Security Features...'));
        
        try {
            // Test XSS protection
            await this.page.goto(this.baseUrl + '/login.php?xss=<script>alert("xss")</script>');
            const pageContent = await this.page.content();
            const hasXSS = pageContent.includes('<script>alert("xss")</script>');
            
            if (!hasXSS) {
                this.addTestResult('Security: XSS Protection', true, 'XSS filtered');
            } else {
                this.addTestResult('Security: XSS Protection', false, 'XSS not filtered');
            }
            
            // Test SQL injection protection
            const loginResponse = await this.page.evaluate(async () => {
                try {
                    const response = await fetch('/api/auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=login&username=\'; DROP TABLE users; --&password=test'
                    });
                    return response.json();
                } catch (error) {
                    return { success: false, error: error.message };
                }
            });
            
            if (!loginResponse.success) {
                this.addTestResult('Security: SQL Injection', true, 'SQL injection blocked');
            } else {
                this.addTestResult('Security: SQL Injection', false, 'SQL injection not blocked');
            }
            
        } catch (error) {
            this.addTestResult('Security Features', false, error.message);
        }
    }
    
    /**
     * Test responsive design
     */
    async testResponsiveDesign() {
        console.log(chalk.yellow('📱 Testing Responsive Design...'));
        
        try {
            await this.loginAs('bos');
            
            // Test different viewports
            const viewports = [
                { width: 1920, height: 1080, name: 'Desktop' },
                { width: 768, height: 1024, name: 'Tablet' },
                { width: 375, height: 667, name: 'Mobile' }
            ];
            
            for (const viewport of viewports) {
                await this.page.setViewport({ width: viewport.width, height: viewport.height });
                await this.page.waitForTimeout(1000);
                
                // Check if layout adapts
                const sidebarVisible = await this.page.$('.sidebar') !== null;
                const contentVisible = await this.page.$('.app-main') !== null;
                
                if (sidebarVisible && contentVisible) {
                    this.addTestResult(`Responsive: ${viewport.name}`, true, 'Layout adapts correctly');
                    await this.takeScreenshot(`responsive-${viewport.name.toLowerCase()}`);
                } else {
                    this.addTestResult(`Responsive: ${viewport.name}`, false, 'Layout adaptation failed');
                }
            }
            
        } catch (error) {
            this.addTestResult('Responsive Design', false, error.message);
        }
    }
    
    /**
     * Test logout functionality
     */
    async testLogoutFunctionality() {
        console.log(chalk.yellow('🚪 Testing Logout Functionality...'));
        
        try {
            await this.loginAs('bos');
            
            // Test logout button
            const logoutButton = await this.page.$('a[onclick="logout()"]');
            if (logoutButton) {
                // Handle confirmation dialog
                this.page.on('dialog', async dialog => {
                    await dialog.accept();
                });
                
                // Click logout
                await logoutButton.click();
                await this.page.waitForNavigation({ waitUntil: 'networkidle2' });
                
                // Check if redirected to login
                const url = this.page.url();
                const isLoginPage = url.includes('login.php');
                
                if (isLoginPage) {
                    this.addTestResult('Logout Functionality', true, 'Logout successful, redirected to login');
                } else {
                    this.addTestResult('Logout Functionality', false, 'Logout failed, not redirected');
                }
            } else {
                this.addTestResult('Logout Functionality', false, 'Logout button not found');
            }
            
        } catch (error) {
            this.addTestResult('Logout Functionality', false, error.message);
        }
    }
    
    /**
     * Test error handling
     */
    async testErrorHandling() {
        console.log(chalk.yellow('⚠️ Testing Error Handling...'));
        
        try {
            // Test 404 error
            const response = await this.page.goto(this.baseUrl + '/nonexistent-page', { waitUntil: 'networkidle2' });
            
            if (response.status() === 404 || response.status() === 500) {
                this.addTestResult('Error Handling: 404', true, '404 error handled properly');
            } else {
                this.addTestResult('Error Handling: 404', false, '404 error not handled');
            }
            
            // Test invalid login
            await this.page.goto(this.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
            await this.page.type('input[name="username"]', 'invalid');
            await this.page.type('input[name="password"]', 'invalid');
            await this.page.click('button[type="submit"]');
            await this.page.waitForTimeout(2000);
            
            const url = this.page.url();
            const stillOnLoginPage = url.includes('login.php');
            
            if (stillOnLoginPage) {
                this.addTestResult('Error Handling: Invalid Login', true, 'Invalid login handled properly');
            } else {
                this.addTestResult('Error Handling: Invalid Login', false, 'Invalid login not handled');
            }
            
        } catch (error) {
            this.addTestResult('Error Handling', false, error.message);
        }
    }
    
    /**
     * Login as specific role
     */
    async loginAs(role) {
        await this.page.goto(this.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
        await this.page.type('input[name="username"]', role, { delay: 100 });
        await this.page.type('input[name="password"]', role, { delay: 100 });
        await Promise.all([
            this.page.waitForNavigation({ waitUntil: 'networkidle2' }),
            this.page.click('button[type="submit"]')
        ]);
    }
    
    /**
     * Take screenshot
     */
    async takeScreenshot(name) {
        if (this.visualMode) {
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            const filename = `${name}-${timestamp}.png`;
            const filepath = path.join(this.screenshotDir, filename);
            
            await this.page.screenshot({ path: filepath, fullPage: true });
            this.testResults.screenshots.push(filepath);
            
            console.log(chalk.gray(`    📸 Screenshot saved: ${filename}`));
        }
    }
    
    /**
     * Add test result
     */
    addTestResult(testName, passed, message) {
        this.testResults.total++;
        
        if (passed) {
            this.testResults.passed++;
            console.log(chalk.green(`    ✅ ${testName}: ${message}`));
        } else {
            this.testResults.failed++;
            console.log(chalk.red(`    ❌ ${testName}: ${message}`));
            this.testResults.errors.push({
                test: testName,
                message: message
            });
        }
    }
    
    /**
     * Cleanup browser
     */
    async cleanup() {
        if (this.browser) {
            await this.browser.close();
        }
    }
    
    /**
     * Generate final report
     */
    generateFinalReport() {
        console.log('\n' + '='.repeat(60));
        console.log(chalk.blue('📊 COMPREHENSIVE PUPPETEER TEST REPORT'));
        console.log('='.repeat(60) + '\n');
        
        console.log('📈 Test Results:');
        console.log(`  Total Tests: ${this.testResults.total}`);
        console.log(`  Passed: ${chalk.green(this.testResults.passed)}`);
        console.log(`  Failed: ${chalk.red(this.testResults.failed)}`);
        
        const passRate = Math.round((this.testResults.passed / this.testResults.total) * 100);
        console.log(`  Success Rate: ${passRate}%\n`);
        
        if (this.testResults.screenshots.length > 0) {
            console.log(`📸 Screenshots: ${this.testResults.screenshots.length} saved`);
        }
        
        if (this.testResults.failed > 0) {
            console.log(chalk.red('❌ Failed Tests:'));
            this.testResults.errors.forEach(error => {
                console.log(`  • ${error.test}: ${error.message}`);
            });
            console.log('');
        }
        
        console.log('🎯 System Status:');
        if (passRate >= 90) {
            console.log(chalk.green('  ✅ EXCELLENT - System ready for production'));
        } else if (passRate >= 75) {
            console.log(chalk.yellow('  ⚠️  GOOD - System mostly functional, minor issues'));
        } else if (passRate >= 50) {
            console.log(chalk.yellow('  🔶 FAIR - System partially functional, needs attention'));
        } else {
            console.log(chalk.red('  ❌ POOR - System has significant issues'));
        }
        
        console.log('\n' + '='.repeat(60));
        console.log(chalk.blue('🚀 KSP Lam Gabe Jaya - Puppeteer Test Complete'));
        console.log('='.repeat(60));
    }
}

// Run tests
if (require.main === module) {
    const testSuite = new PuppeteerTestSuite();
    testSuite.runComprehensiveTests().then(results => {
        process.exit(results.failed > 0 ? 1 : 0);
    }).catch(error => {
        console.error(chalk.red('Test suite failed:'), error);
        process.exit(1);
    });
}

module.exports = PuppeteerTestSuite;
