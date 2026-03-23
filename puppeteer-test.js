/**
 * KSP Lam Gabe Jaya - Puppeteer E2E Test
 * Comprehensive testing of login and dashboard functionality
 */

const puppeteer = require('puppeteer');

class KSPTestSuite {
    constructor() {
        this.baseURL = 'http://localhost/mono-v2';
        this.testResults = [];
        this.browser = null;
        this.page = null;
    }

    async init() {
        console.log('🚀 Starting KSP Lam Gabe Jaya E2E Tests...\n');
        
        this.browser = await puppeteer.launch({
            headless: false, // Show browser for debugging
            defaultViewport: { width: 1366, height: 768 },
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        this.page = await this.browser.newPage();
        
        // Enable request logging
        this.page.on('request', request => {
            console.log(`📡 Request: ${request.method()} ${request.url()}`);
        });
        
        this.page.on('response', response => {
            console.log(`📡 Response: ${response.status()} ${response.url()}`);
        });
    }

    async testLoginPage() {
        console.log('🔍 Testing Login Page...');
        
        try {
            // Navigate to login page
            await this.page.goto(`${this.baseURL}/login.php`, { 
                waitUntil: 'networkidle2',
                timeout: 10000 
            });
            
            // Check page title
            const title = await this.page.title();
            console.log(`📄 Page Title: ${title}`);
            
            // Check if login form exists
            const loginForm = await this.page.$('form');
            const usernameInput = await this.page.$('#username');
            const passwordInput = await this.page.$('#password');
            const submitButton = await this.page.$('.btn-primary');
            
            // Check if elements are visible
            const formVisible = await this.page.evaluate(el => {
                return el && el.offsetParent !== null;
            }, loginForm);
            
            const usernameVisible = await this.page.evaluate(el => {
                return el && el.offsetParent !== null;
            }, usernameInput);
            
            const passwordVisible = await this.page.evaluate(el => {
                return el && el.offsetParent !== null;
            }, passwordInput);
            
            const submitVisible = await this.page.evaluate(el => {
                return el && el.offsetParent !== null;
            }, submitButton);
            
            // Check for CSS loading
            const cssLoaded = await this.page.evaluate(() => {
                const styles = getComputedStyle(document.querySelector('.login-card'));
                return styles.display !== 'none';
            });
            
            // Check for console errors
            const consoleErrors = await this.page.evaluate(() => {
                return window.consoleErrors || [];
            });
            
            const result = {
                test: 'Login Page',
                url: `${this.baseURL}/login.php`,
                title: title,
                elements: {
                    form: formVisible,
                    username: usernameVisible,
                    password: passwordVisible,
                    submit: submitVisible
                },
                css: cssLoaded,
                errors: consoleErrors,
                status: formVisible && usernameVisible && passwordVisible && submitVisible && cssLoaded ? 'PASS' : 'FAIL'
            };
            
            this.testResults.push(result);
            console.log(`✅ Login Page Test: ${result.status}\n`);
            
        } catch (error) {
            console.error(`❌ Login Page Test Error: ${error.message}\n`);
            this.testResults.push({
                test: 'Login Page',
                status: 'FAIL',
                error: error.message
            });
        }
    }

    async testLogin(role, username, password) {
        console.log(`🔍 Testing Login for ${role}...`);
        
        try {
            // Navigate to login page
            await this.page.goto(`${this.baseURL}/login.php`, { 
                waitUntil: 'networkidle2',
                timeout: 10000 
            });
            
            // Fill login form
            await this.page.type('#username', username, { delay: 100 });
            await this.page.type('#password', password, { delay: 100 });
            
            // Click submit button
            await Promise.all([
                this.page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 10000 }),
                this.page.click('.btn-primary')
            ]);
            
            // Check if redirected to dashboard
            const currentURL = this.page.url();
            const isDashboard = currentURL.includes('dashboard') || currentURL.includes('main.php');
            
            // Check if user session exists
            const sessionData = await this.page.evaluate(() => {
                return {
                    hasSession: !!document.querySelector('body'),
                    bodyClass: document.body.className
                };
            });
            
            const result = {
                test: `Login - ${role}`,
                username: username,
                redirectedTo: currentURL,
                isDashboard: isDashboard,
                sessionData: sessionData,
                status: isDashboard ? 'PASS' : 'FAIL'
            };
            
            this.testResults.push(result);
            console.log(`✅ Login Test (${role}): ${result.status}\n`);
            
            return isDashboard;
            
        } catch (error) {
            console.error(`❌ Login Test Error (${role}): ${error.message}\n`);
            this.testResults.push({
                test: `Login - ${role}`,
                status: 'FAIL',
                error: error.message
            });
            return false;
        }
    }

    async testDashboard() {
        console.log('🔍 Testing Dashboard...');
        
        try {
            // Wait for dashboard to load
            await this.page.waitForSelector('body', { timeout: 5000 });
            
            // Check dashboard content
            const dashboardContent = await this.page.evaluate(() => {
                return {
                    title: document.title,
                    bodyText: document.body.innerText.substring(0, 200),
                    hasMainContent: document.querySelector('main, .main, .dashboard, .container') !== null
                };
            });
            
            // Check for navigation elements
            const navigationExists = await this.page.$('nav, .navbar, .navigation, .menu') !== null;
            
            // Check for user info
            const userInfoExists = await this.page.$('.user-info, .profile, .user') !== null;
            
            const result = {
                test: 'Dashboard',
                content: dashboardContent,
                navigation: navigationExists,
                userInfo: userInfoExists,
                status: dashboardContent.hasMainContent ? 'PASS' : 'FAIL'
            };
            
            this.testResults.push(result);
            console.log(`✅ Dashboard Test: ${result.status}\n`);
            
        } catch (error) {
            console.error(`❌ Dashboard Test Error: ${error.message}\n`);
            this.testResults.push({
                test: 'Dashboard',
                status: 'FAIL',
                error: error.message
            });
        }
    }

    async testLogout() {
        console.log('🔍 Testing Logout...');
        
        try {
            // Look for logout link/button
            const logoutLink = await this.page.$('a[href*="logout"], button[onclick*="logout"], .logout');
            
            if (logoutLink) {
                await Promise.all([
                    this.page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 10000 }),
                    logoutLink.click()
                ]);
                
                // Check if redirected to login
                const currentURL = this.page.url();
                const isLoginPage = currentURL.includes('login.php') || currentURL.includes('login');
                
                const result = {
                    test: 'Logout',
                    redirectedTo: currentURL,
                    isLoginPage: isLoginPage,
                    status: isLoginPage ? 'PASS' : 'FAIL'
                };
                
                this.testResults.push(result);
                console.log(`✅ Logout Test: ${result.status}\n`);
                
            } else {
                console.log('⚠️ Logout link not found\n');
                this.testResults.push({
                    test: 'Logout',
                    status: 'SKIP',
                    reason: 'Logout link not found'
                });
            }
            
        } catch (error) {
            console.error(`❌ Logout Test Error: ${error.message}\n`);
            this.testResults.push({
                test: 'Logout',
                status: 'FAIL',
                error: error.message
            });
        }
    }

    async testAPIEndpoints() {
        console.log('🔍 Testing API Endpoints...');
        
        const endpoints = [
            { name: 'Auth Login', url: `${this.baseURL}/api/auth.php`, method: 'POST', data: 'username=admin&password=admin&action=login' },
            { name: 'Auth Check Session', url: `${this.baseURL}/api/auth.php`, method: 'GET', data: 'action=check_session' }
        ];
        
        for (const endpoint of endpoints) {
            try {
                const response = await this.page.evaluate(async (endpoint) => {
                    const formData = new FormData();
                    endpoint.data.split('&').forEach(param => {
                        const [key, value] = param.split('=');
                        formData.append(key, value);
                    });
                    
                    const response = await fetch(endpoint.url, {
                        method: endpoint.method,
                        body: endpoint.method === 'POST' ? formData : undefined
                    });
                    
                    return {
                        status: response.status,
                        ok: response.ok,
                        text: await response.text()
                    };
                }, endpoint);
                
                const result = {
                    test: `API - ${endpoint.name}`,
                    endpoint: endpoint.url,
                    status: response.ok ? 'PASS' : 'FAIL',
                    httpStatus: response.status,
                    response: response.text.substring(0, 100)
                };
                
                this.testResults.push(result);
                console.log(`✅ API Test (${endpoint.name}): ${result.status} (${response.status})\n`);
                
            } catch (error) {
                console.error(`❌ API Test Error (${endpoint.name}): ${error.message}\n`);
                this.testResults.push({
                    test: `API - ${endpoint.name}`,
                    status: 'FAIL',
                    error: error.message
                });
            }
        }
    }

    async runAllTests() {
        try {
            await this.init();
            
            // Test login page
            await this.testLoginPage();
            
            // Test different roles
            const testUsers = [
                { role: 'Bos', username: 'bos', password: 'bos' },
                { role: 'Admin', username: 'admin', password: 'admin' },
                { role: 'Teller', username: 'teller', password: 'teller' },
                { role: 'Collector', username: 'collector', password: 'collector' },
                { role: 'Nasabah', username: 'nasabah', password: 'nasabah' }
            ];
            
            // Test first user login
            const firstUser = testUsers[0];
            const loginSuccess = await this.testLogin(firstUser.role, firstUser.username, firstUser.password);
            
            if (loginSuccess) {
                await this.testDashboard();
                await this.testLogout();
                
                // Test other users
                for (let i = 1; i < Math.min(3, testUsers.length); i++) {
                    const user = testUsers[i];
                    await this.testLogin(user.role, user.username, user.password);
                    await this.testLogout();
                }
            }
            
            // Test API endpoints
            await this.testAPIEndpoints();
            
            // Print results
            this.printResults();
            
        } catch (error) {
            console.error('❌ Test Suite Error:', error.message);
        } finally {
            if (this.browser) {
                await this.browser.close();
            }
        }
    }

    printResults() {
        console.log('\n📊 TEST RESULTS SUMMARY');
        console.log('='.repeat(50));
        
        const passed = this.testResults.filter(r => r.status === 'PASS').length;
        const failed = this.testResults.filter(r => r.status === 'FAIL').length;
        const skipped = this.testResults.filter(r => r.status === 'SKIP').length;
        const total = this.testResults.length;
        
        console.log(`Total Tests: ${total}`);
        console.log(`✅ Passed: ${passed}`);
        console.log(`❌ Failed: ${failed}`);
        console.log(`⏭️ Skipped: ${skipped}`);
        console.log(`📈 Success Rate: ${((passed / total) * 100).toFixed(1)}%`);
        
        console.log('\n📋 DETAILED RESULTS:');
        console.log('-'.repeat(50));
        
        this.testResults.forEach((result, index) => {
            const status = result.status === 'PASS' ? '✅' : result.status === 'FAIL' ? '❌' : '⏭️';
            console.log(`${status} ${index + 1}. ${result.test}`);
            
            if (result.error) {
                console.log(`   Error: ${result.error}`);
            }
            
            if (result.redirectedTo) {
                console.log(`   Redirected to: ${result.redirectedTo}`);
            }
        });
        
        console.log('\n🎯 Test Suite Complete!');
        
        // Save results to file
        const fs = require('fs');
        const reportData = {
            timestamp: new Date().toISOString(),
            summary: {
                total,
                passed,
                failed,
                skipped,
                successRate: ((passed / total) * 100).toFixed(1)
            },
            results: this.testResults
        };
        
        fs.writeFileSync('puppeteer-test-results.json', JSON.stringify(reportData, null, 2));
        console.log('📄 Detailed results saved to: puppeteer-test-results.json');
    }
}

// Run tests
if (require.main === module) {
    const testSuite = new KSPTestSuite();
    testSuite.runAllTests().catch(console.error);
}

module.exports = KSPTestSuite;
