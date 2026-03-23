/**
 * KSP Lam Gabe Jaya - Comprehensive E2E Test Suite
 * Complete system testing with error detection and fixes
 */

const puppeteer = require('puppeteer');
const fs = require('fs');

class ComprehensiveTestSuite {
    constructor() {
        this.baseURL = 'http://localhost/mono-v2';
        this.testResults = [];
        this.browser = null;
        this.page = null;
        this.errors = [];
    }

    async init() {
        console.log('🚀 Starting Comprehensive E2E Tests...\n');
        
        this.browser = await puppeteer.launch({
            headless: false,
            defaultViewport: { width: 1366, height: 768 },
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        this.page = await this.browser.newPage();
        
        // Capture console errors
        this.page.on('console', msg => {
            if (msg.type() === 'error') {
                this.errors.push({
                    type: 'console_error',
                    message: msg.text(),
                    location: msg.location()
                });
                console.log(`❌ Console Error: ${msg.text()}`);
            }
        });
        
        this.page.on('pageerror', error => {
            this.errors.push({
                type: 'page_error',
                message: error.message,
                stack: error.stack
            });
            console.log(`❌ Page Error: ${error.message}`);
        });
    }

    async testLoginPageDetailed() {
        console.log('🔍 Testing Login Page (Detailed)...');
        
        try {
            await this.page.goto(`${this.baseURL}/login.php`, { 
                waitUntil: 'networkidle2',
                timeout: 15000 
            });
            
            // Check page structure
            const pageStructure = await this.page.evaluate(() => {
                return {
                    title: document.title,
                    hasLoginForm: !!document.querySelector('form'),
                    hasUsername: !!document.querySelector('#username'),
                    hasPassword: !!document.querySelector('#password'),
                    hasSubmit: !!document.querySelector('.btn-primary, .btn-login, button[type="submit"]'),
                    hasLogo: !!document.querySelector('.logo, .logo-img'),
                    hasDemoAccounts: !!document.querySelector('.demo-accounts, .demo-list'),
                    cssLoaded: !!document.querySelector('link[href*="login.css"]'),
                    bootstrapLoaded: !!document.querySelector('link[href*="bootstrap"]'),
                    fontAwesomeLoaded: !!document.querySelector('link[href*="font-awesome"]'),
                    bodyClasses: document.body.className,
                    hasErrorAlerts: !!document.querySelector('.alert-danger'),
                    hasSuccessAlerts: !!document.querySelector('.alert-success')
                };
            });
            
            // Test form validation
            await this.page.click('.btn-primary, .btn-login, button[type="submit"]');
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            const validationTest = await this.page.evaluate(() => {
                const alerts = document.querySelectorAll('.alert-danger');
                return {
                    hasValidationError: alerts.length > 0,
                    validationMessage: alerts.length > 0 ? alerts[0].textContent : null
                };
            });
            
            // Test CSS loading
            const cssTest = await this.page.evaluate(() => {
                const loginCard = document.querySelector('.login-card');
                if (!loginCard) return { loaded: false };
                
                const styles = window.getComputedStyle(loginCard);
                return {
                    loaded: true,
                    hasBackground: styles.background !== 'none',
                    hasBorder: styles.border !== 'none',
                    hasPadding: styles.padding !== '0px',
                    isVisible: styles.display !== 'none'
                };
            });
            
            const result = {
                test: 'Login Page Detailed',
                structure: pageStructure,
                validation: validationTest,
                css: cssTest,
                errors: this.errors.filter(e => e.type === 'console_error'),
                status: pageStructure.hasLoginForm && pageStructure.hasUsername && pageStructure.hasPassword && cssTest.loaded ? 'PASS' : 'FAIL'
            };
            
            this.testResults.push(result);
            console.log(`✅ Login Page Detailed: ${result.status}\n`);
            
        } catch (error) {
            console.error(`❌ Login Page Detailed Error: ${error.message}\n`);
            this.testResults.push({
                test: 'Login Page Detailed',
                status: 'FAIL',
                error: error.message
            });
        }
    }

    async testAllUserLogins() {
        console.log('🔍 Testing All User Logins...');
        
        const users = [
            { role: 'Bos', username: 'bos', password: 'bos', expectedRole: 'bos' },
            { role: 'Admin', username: 'admin', password: 'admin', expectedRole: 'admin' },
            { role: 'Teller', username: 'teller', password: 'teller', expectedRole: 'teller' },
            { role: 'Collector', username: 'collector', password: 'collector', expectedRole: 'collector' },
            { role: 'Nasabah', username: 'nasabah', password: 'nasabah', expectedRole: 'nasabah' }
        ];
        
        for (const user of users) {
            await this.testSingleUserLogin(user);
            await this.clearSession();
        }
    }

    async testSingleUserLogin(user) {
        console.log(`🔍 Testing Login: ${user.role}...`);
        
        try {
            // Clear cookies and go to login
            await this.page.evaluate(() => {
                document.cookie.split(";").forEach(c => {
                    document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
                });
            });
            
            await this.page.goto(`${this.baseURL}/login.php`, { 
                waitUntil: 'networkidle2',
                timeout: 10000 
            });
            
            // Fill and submit form
            await this.page.type('#username', user.username, { delay: 100 });
            await this.page.type('#password', user.password, { delay: 100 });
            
            await Promise.all([
                this.page.waitForNavigation({ 
                    waitUntil: 'networkidle2', 
                    timeout: 15000 
                }).catch(() => {
                    // Fallback: wait for dashboard content
                    return this.page.waitForSelector('body', { timeout: 5000 });
                }),
                this.page.click('.btn-primary, .btn-login, button[type="submit"]')
            ]);
            
            // Check login result
            const loginResult = await this.page.evaluate(() => {
                return {
                    currentURL: window.location.href,
                    title: document.title,
                    hasDashboard: document.body.textContent.includes('Dashboard') || document.body.textContent.includes('dashboard'),
                    hasError: !!document.querySelector('.alert-danger'),
                    errorMessage: document.querySelector('.alert-danger')?.textContent,
                    bodyText: document.body.innerText.substring(0, 200)
                };
            });
            
            // Check if redirected correctly
            const isDashboard = loginResult.currentURL.includes('dashboard') || loginResult.currentURL.includes('main.php');
            
            const result = {
                test: `Login - ${user.role}`,
                user: user,
                redirectedTo: loginResult.currentURL,
                isDashboard: isDashboard,
                hasError: loginResult.hasError,
                errorMessage: loginResult.errorMessage,
                bodyContent: loginResult.bodyText,
                status: isDashboard && !loginResult.hasError ? 'PASS' : 'FAIL'
            };
            
            this.testResults.push(result);
            console.log(`✅ Login ${user.role}: ${result.status}\n`);
            
        } catch (error) {
            console.error(`❌ Login ${user.role} Error: ${error.message}\n`);
            this.testResults.push({
                test: `Login - ${user.role}`,
                user: user,
                status: 'FAIL',
                error: error.message
            });
        }
    }

    async clearSession() {
        try {
            // Clear cookies properly
            const cookies = await this.page.cookies();
            await this.page.deleteCookie(...cookies);
            
            // Clear storage
            await this.page.evaluate(() => {
                localStorage.clear();
                sessionStorage.clear();
            });
            
            // Wait for session to clear
            await new Promise(resolve => setTimeout(resolve, 1000));
        } catch (error) {
            console.log(`⚠️ Session clear error: ${error.message}`);
        }
    }

    async testDashboardFunctionality() {
        console.log('🔍 Testing Dashboard Functionality...');
        
        try {
            // Login as bos first
            await this.page.goto(`${this.baseURL}/login.php`, { 
                waitUntil: 'networkidle2',
                timeout: 10000 
            });
            
            await this.page.type('#username', 'bos', { delay: 100 });
            await this.page.type('#password', 'bos', { delay: 100 });
            
            await Promise.all([
                this.page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 10000 }),
                this.page.click('.btn-primary, .btn-login, button[type="submit"]')
            ]);
            
            // Wait for dashboard to load
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            // Test dashboard content
            const dashboardContent = await this.page.evaluate(() => {
                return {
                    title: document.title,
                    hasNavigation: !!document.querySelector('nav, .navbar, .navigation, .menu'),
                    hasUserInfo: !!document.querySelector('.user-info, .profile, .user'),
                    hasWidgets: !!document.querySelector('.widget, .card, .dashboard-item'),
                    hasCharts: !!document.querySelector('canvas, .chart'),
                    hasTables: !!document.querySelector('table'),
                    hasButtons: !!document.querySelectorAll('button, .btn').length > 0,
                    hasLinks: !!document.querySelectorAll('a').length > 0,
                    bodyText: document.body.innerText.substring(0, 300),
                    hasError: !!document.querySelector('.alert-danger'),
                    errorMessages: Array.from(document.querySelectorAll('.alert-danger')).map(el => el.textContent)
                };
            });
            
            // Test navigation links
            const navigationTest = await this.page.evaluate(() => {
                const links = document.querySelectorAll('a[href]');
                const workingLinks = [];
                
                links.forEach(link => {
                    const href = link.getAttribute('href');
                    if (href && !href.startsWith('#') && !href.startsWith('javascript')) {
                        workingLinks.push({
                            text: link.textContent.trim(),
                            href: href
                        });
                    }
                });
                
                return {
                    totalLinks: links.length,
                    workingLinks: workingLinks.slice(0, 10) // First 10 links
                };
            });
            
            // Test role-specific content
            const roleContent = await this.page.evaluate(() => {
                const bodyText = document.body.innerText.toLowerCase();
                return {
                    hasBosContent: bodyText.includes('bos') || bodyText.includes('laporan') || bodyText.includes('pengaturan'),
                    hasAdminContent: bodyText.includes('admin') || bodyText.includes('data') || bodyText.includes('manajemen'),
                    hasTellerContent: bodyText.includes('teller') || bodyText.includes('transaksi') || bodyText.includes('setoran'),
                    hasCollectorContent: bodyText.includes('collector') || bodyText.includes('kunjungan') || bodyText.includes('rute'),
                    hasNasabahContent: bodyText.includes('nasabah') || bodyText.includes('simpanan') || bodyText.includes('pinjaman')
                };
            });
            
            const result = {
                test: 'Dashboard Functionality',
                content: dashboardContent,
                navigation: navigationTest,
                roleContent: roleContent,
                errors: this.errors.filter(e => e.type === 'page_error'),
                status: dashboardContent.title.includes('Dashboard') && !dashboardContent.hasError ? 'PASS' : 'FAIL'
            };
            
            this.testResults.push(result);
            console.log(`✅ Dashboard Functionality: ${result.status}\n`);
            
        } catch (error) {
            console.error(`❌ Dashboard Functionality Error: ${error.message}\n`);
            this.testResults.push({
                test: 'Dashboard Functionality',
                status: 'FAIL',
                error: error.message
            });
        }
    }

    async testAPIEndpoints() {
        console.log('🔍 Testing API Endpoints...');
        
        const endpoints = [
            { 
                name: 'Auth Login', 
                url: `${this.baseURL}/api/auth.php`, 
                method: 'POST', 
                data: 'username=bos&password=bos&action=login' 
            },
            { 
                name: 'Auth Login (Admin)', 
                url: `${this.baseURL}/api/auth.php`, 
                method: 'POST', 
                data: 'username=admin&password=admin&action=login' 
            },
            { 
                name: 'Auth Check Session', 
                url: `${this.baseURL}/api/auth.php`, 
                method: 'GET', 
                data: 'action=check_session' 
            },
            { 
                name: 'Auth Logout', 
                url: `${this.baseURL}/api/auth.php`, 
                method: 'POST', 
                data: 'action=logout' 
            }
        ];
        
        for (const endpoint of endpoints) {
            await this.testAPIEndpoint(endpoint);
        }
    }

    async testAPIEndpoint(endpoint) {
        console.log(`🔍 Testing API: ${endpoint.name}...`);
        
        try {
            const response = await this.page.evaluate(async (endpoint) => {
                try {
                    // Use URLSearchParams for proper form encoding
                    const params = new URLSearchParams();
                    endpoint.data.split('&').forEach(param => {
                        const [key, value] = param.split('=');
                        params.append(key, value);
                    });
                    
                    const response = await fetch(endpoint.url, {
                        method: endpoint.method,
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json'
                        },
                        body: endpoint.method === 'POST' ? params.toString() : undefined
                    });
                    
                    const text = await response.text();
                    let json;
                    
                    try {
                        json = JSON.parse(text);
                    } catch {
                        json = { raw: text };
                    }
                    
                    return {
                        status: response.status,
                        ok: response.ok,
                        statusText: response.statusText,
                        headers: Object.fromEntries(response.headers.entries()),
                        response: json
                    };
                } catch (error) {
                    return {
                        error: error.message,
                        status: 0
                    };
                }
            }, endpoint);
            
            const result = {
                test: `API - ${endpoint.name}`,
                endpoint: endpoint.url,
                method: endpoint.method,
                response: response,
                status: response.ok || (response.status >= 200 && response.status < 300) ? 'PASS' : 'FAIL'
            };
            
            this.testResults.push(result);
            console.log(`✅ API ${endpoint.name}: ${result.status} (${response.status || response.error})\n`);
            
        } catch (error) {
            console.error(`❌ API ${endpoint.name} Error: ${error.message}\n`);
            this.testResults.push({
                test: `API - ${endpoint.name}`,
                status: 'FAIL',
                error: error.message
            });
        }
    }

    async testPageNavigation() {
        console.log('🔍 Testing Page Navigation...');
        
        try {
            // Login first
            await this.page.goto(`${this.baseURL}/login.php`, { 
                waitUntil: 'networkidle2',
                timeout: 10000 
            });
            
            await this.page.type('#username', 'bos', { delay: 100 });
            await this.page.type('#password', 'bos', { delay: 100 });
            
            await Promise.all([
                this.page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 10000 }),
                this.page.click('.btn-primary, .btn-login, button[type="submit"]')
            ]);
            
            // Test navigation to different pages
            const pages = [
                { name: 'Dashboard', url: `${this.baseURL}/index.php?page=dashboard` },
                { name: 'Members', url: `${this.baseURL}/index.php?page=members` },
                { name: 'Accounts', url: `${this.baseURL}/index.php?page=accounts` },
                { name: 'Transactions', url: `${this.baseURL}/index.php?page=transactions` },
                { name: 'Loans', url: `${this.baseURL}/index.php?page=loans` },
                { name: 'Reports', url: `${this.baseURL}/index.php?page=reports` }
            ];
            
            for (const page of pages) {
                const pageResult = await this.testPageLoad(page);
                this.testResults.push(pageResult);
            }
            
            console.log(`✅ Page Navigation: Complete\n`);
            
        } catch (error) {
            console.error(`❌ Page Navigation Error: ${error.message}\n`);
            this.testResults.push({
                test: 'Page Navigation',
                status: 'FAIL',
                error: error.message
            });
        }
    }

    async testPageLoad(page) {
        console.log(`🔍 Testing Page: ${page.name}...`);
        
        try {
            await this.page.goto(page.url, { 
                waitUntil: 'networkidle2',
                timeout: 10000 
            });
            
            const pageResult = await this.page.evaluate(() => {
                return {
                    title: document.title,
                    hasContent: document.body.innerText.length > 100,
                    hasError: !!document.querySelector('.alert-danger'),
                    hasFatalError: document.body.textContent.includes('Fatal error') || document.body.textContent.includes('Uncaught Exception'),
                    bodyText: document.body.innerText.substring(0, 200),
                    hasNavigation: !!document.querySelector('nav, .navbar, .navigation, .menu'),
                    hasForms: !!document.querySelector('form'),
                    hasTables: !!document.querySelector('table'),
                    hasButtons: !!document.querySelectorAll('button, .btn').length > 0
                };
            });
            
            const result = {
                test: `Page Load - ${page.name}`,
                url: page.url,
                content: pageResult,
                status: !pageResult.hasFatalError && !pageResult.hasError ? 'PASS' : 'FAIL'
            };
            
            console.log(`✅ Page ${page.name}: ${result.status}\n`);
            return result;
            
        } catch (error) {
            console.error(`❌ Page ${page.name} Error: ${error.message}\n`);
            return {
                test: `Page Load - ${page.name}`,
                url: page.url,
                status: 'FAIL',
                error: error.message
            };
        }
    }

    async testErrorHandling() {
        console.log('🔍 Testing Error Handling...');
        
        try {
            // Test invalid login
            await this.page.goto(`${this.baseURL}/login.php`, { 
                waitUntil: 'networkidle2',
                timeout: 10000 
            });
            
            await this.page.type('#username', 'invaliduser', { delay: 100 });
            await this.page.type('#password', 'wrongpassword', { delay: 100 });
            
            await this.page.click('.btn-primary, .btn-login, button[type="submit"]');
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            const errorTest = await this.page.evaluate(() => {
                const errorAlerts = document.querySelectorAll('.alert-danger');
                return {
                    hasError: errorAlerts.length > 0,
                    errorMessage: errorAlerts.length > 0 ? errorAlerts[0].textContent : null,
                    errorCount: errorAlerts.length
                };
            });
            
            // Test empty form submission
            await this.page.goto(`${this.baseURL}/login.php`, { 
                waitUntil: 'networkidle2',
                timeout: 10000 
            });
            
            await this.page.click('.btn-primary, .btn-login, button[type="submit"]');
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            const validationTest = await this.page.evaluate(() => {
                const errorAlerts = document.querySelectorAll('.alert-danger');
                return {
                    hasValidationError: errorAlerts.length > 0,
                    validationMessage: errorAlerts.length > 0 ? errorAlerts[0].textContent : null
                };
            });
            
            const result = {
                test: 'Error Handling',
                invalidLogin: errorTest,
                emptyForm: validationTest,
                status: errorTest.hasError && validationTest.hasValidationError ? 'PASS' : 'FAIL'
            };
            
            this.testResults.push(result);
            console.log(`✅ Error Handling: ${result.status}\n`);
            
        } catch (error) {
            console.error(`❌ Error Handling Test Error: ${error.message}\n`);
            this.testResults.push({
                test: 'Error Handling',
                status: 'FAIL',
                error: error.message
            });
        }
    }

    async generateReport() {
        const passed = this.testResults.filter(r => r.status === 'PASS').length;
        const failed = this.testResults.filter(r => r.status === 'FAIL').length;
        const skipped = this.testResults.filter(r => r.status === 'SKIP').length;
        const total = this.testResults.length;
        
        const report = {
            timestamp: new Date().toISOString(),
            summary: {
                total,
                passed,
                failed,
                skipped,
                successRate: ((passed / total) * 100).toFixed(1)
            },
            errors: this.errors,
            results: this.testResults,
            recommendations: this.generateRecommendations()
        };
        
        // Save detailed report
        fs.writeFileSync('comprehensive-test-report.json', JSON.stringify(report, null, 2));
        
        // Generate summary
        console.log('\n📊 COMPREHENSIVE TEST REPORT');
        console.log('='.repeat(60));
        console.log(`Total Tests: ${total}`);
        console.log(`✅ Passed: ${passed}`);
        console.log(`❌ Failed: ${failed}`);
        console.log(`⏭️ Skipped: ${skipped}`);
        console.log(`📈 Success Rate: ${report.summary.successRate}%`);
        
        console.log('\n📋 FAILED TESTS:');
        this.testResults
            .filter(r => r.status === 'FAIL')
            .forEach((result, index) => {
                console.log(`❌ ${index + 1}. ${result.test}`);
                if (result.error) console.log(`   Error: ${result.error}`);
            });
        
        console.log('\n🔧 RECOMMENDATIONS:');
        report.recommendations.forEach((rec, index) => {
            console.log(`${index + 1}. ${rec}`);
        });
        
        console.log('\n📄 Detailed report saved to: comprehensive-test-report.json');
        
        return report;
    }

    generateRecommendations() {
        const recommendations = [];
        const failedTests = this.testResults.filter(r => r.status === 'FAIL');
        
        if (failedTests.length === 0) {
            recommendations.push('All tests passed! System is working correctly.');
            return recommendations;
        }
        
        // Analyze failure patterns
        const loginFailures = failedTests.filter(t => t.test.includes('Login'));
        const dashboardFailures = failedTests.filter(t => t.test.includes('Dashboard'));
        const apiFailures = failedTests.filter(t => t.test.includes('API'));
        const pageFailures = failedTests.filter(t => t.test.includes('Page Load'));
        
        if (loginFailures.length > 0) {
            recommendations.push('Fix login authentication issues - check database connections and user credentials.');
        }
        
        if (dashboardFailures.length > 0) {
            recommendations.push('Resolve dashboard loading errors - check PHP errors and missing dependencies.');
        }
        
        if (apiFailures.length > 0) {
            recommendations.push('Fix API endpoint issues - check request parameters and response handling.');
        }
        
        if (pageFailures.length > 0) {
            recommendations.push('Resolve page navigation errors - check routing and missing files.');
        }
        
        if (this.errors.length > 0) {
            recommendations.push(`Address ${this.errors.length} console/page errors found during testing.`);
        }
        
        return recommendations;
    }

    async runAllTests() {
        try {
            await this.init();
            
            // Run comprehensive tests
            await this.testLoginPageDetailed();
            await this.testAllUserLogins();
            await this.testDashboardFunctionality();
            await this.testAPIEndpoints();
            await this.testPageNavigation();
            await this.testErrorHandling();
            
            // Generate report
            const report = await this.generateReport();
            
            return report;
            
        } catch (error) {
            console.error('❌ Test Suite Error:', error.message);
        } finally {
            if (this.browser) {
                await this.browser.close();
            }
        }
    }
}

// Run comprehensive tests
if (require.main === module) {
    const testSuite = new ComprehensiveTestSuite();
    testSuite.runAllTests().catch(console.error);
}

module.exports = ComprehensiveTestSuite;
