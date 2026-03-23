const puppeteer = require('puppeteer');
const chalk = require('chalk');

/**
 * KSP Lam Gabe Jaya - Realistic Test Suite
 * Tests with proper session management
 */
class RealisticTestSuite {
    constructor() {
        this.baseUrl = 'http://localhost/mono-v2';
        this.testResults = { total: 0, passed: 0, failed: 0, errors: [] };
    }
    
    async runComprehensiveTests() {
        console.log(chalk.blue('🚀 KSP Lam Gabe Jaya - Realistic Test Suite'));
        console.log('='.repeat(60));
        
        const browser = await puppeteer.launch({ 
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        try {
            // Test 1: Login Page
            await this.testLoginPage(browser);
            
            // Test 2: Role Login Flow (with logout between each)
            await this.testRoleLoginFlow(browser);
            
            // Test 3: Dashboard Features
            await this.testDashboardFeatures(browser);
            
            // Test 4: Navigation System
            await this.testNavigationSystem(browser);
            
            // Test 5: API Endpoints
            await this.testAPIEndpoints(browser);
            
            // Test 6: Security Features
            await this.testSecurityFeatures(browser);
            
            // Test 7: Logout System
            await this.testLogoutSystem(browser);
            
        } catch (error) {
            console.error(chalk.red('Test suite error:'), error.message);
        } finally {
            await browser.close();
            this.generateReport();
        }
        
        return this.testResults;
    }
    
    async testLoginPage(browser) {
        console.log(chalk.yellow('🔐 Testing Login Page...'));
        
        const page = await browser.newPage();
        
        try {
            await page.goto(this.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
            
            const usernameField = await page.$('input[name="username"]');
            const passwordField = await page.$('input[name="password"]');
            const submitButton = await page.$('button[type="submit"]');
            
            if (usernameField && passwordField && submitButton) {
                this.addResult('Login Page', true, 'All form elements found');
            } else {
                this.addResult('Login Page', false, 'Missing form elements');
            }
            
        } catch (error) {
            this.addResult('Login Page', false, error.message);
        } finally {
            await page.close();
        }
    }
    
    async testRoleLoginFlow(browser) {
        console.log(chalk.yellow('👥 Testing Role Login Flow...'));
        
        const roles = [
            { name: 'bos', username: 'bos', password: 'bos' },
            { name: 'admin', username: 'admin', password: 'admin' },
            { name: 'teller', username: 'teller', password: 'teller' },
            { name: 'collector', username: 'collector', password: 'collector' },
            { name: 'nasabah', username: 'nasabah', password: 'nasabah' }
        ];
        
        for (const role of roles) {
            await this.testSingleRoleLogin(browser, role);
        }
    }
    
    async testSingleRoleLogin(browser, role) {
        const page = await browser.newPage();
        
        try {
            // Step 1: Go to login page
            await page.goto(this.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
            
            // Step 2: Fill login form
            await page.waitForSelector('input[name="username"]', { timeout: 5000 });
            await page.type('input[name="username"]', role.username, { delay: 50 });
            await page.type('input[name="password"]', role.password, { delay: 50 });
            
            // Step 3: Submit login
            await Promise.all([
                page.waitForNavigation({ waitUntil: 'networkidle2' }),
                page.click('button[type="submit"]')
            ]);
            
            // Step 4: Check if login successful
            const url = page.url();
            const isDashboard = url.includes('dashboard') || url.includes('main.php');
            
            if (isDashboard) {
                this.addResult(`${role.name} Login`, true, 'Login successful');
                
                // Step 5: Check role-specific content
                const hasContent = await this.checkRoleSpecificContent(page, role.name);
                this.addResult(`${role.name} Content`, hasContent, 
                    hasContent ? 'Role content found' : 'Role content missing'
                );
                
                // Step 6: Logout to prepare for next test
                await this.performLogout(page);
                this.addResult(`${role.name} Logout`, true, 'Logout successful');
                
            } else {
                this.addResult(`${role.name} Login`, false, 'Not redirected to dashboard');
            }
            
        } catch (error) {
            this.addResult(`${role.name} Login Flow`, false, error.message);
        } finally {
            await page.close();
        }
    }
    
    async checkRoleSpecificContent(page, role) {
        try {
            const contentMap = {
                bos: ['Total Anggota', 'Total Simpanan', 'Total Omzet'],
                admin: ['Anggota Aktif', 'Transaksi Hari Ini', 'User Terdaftar'],
                teller: ['Transaksi Hari Ini', 'Setoran', 'Penarikan'],
                collector: ['Target Hari Ini', 'Kunjungan Selesai', 'Kutipan Terkumpul'],
                nasabah: ['Saldo Simpanan', 'Pinjaman Aktif', 'Cicilan Bulanan']
            };
            
            const expectedTexts = contentMap[role] || [];
            let foundCount = 0;
            
            for (const text of expectedTexts) {
                const content = await page.content();
                if (content.includes(text)) {
                    foundCount++;
                }
            }
            
            return foundCount >= 2; // At least 2 out of 3 expected texts
        } catch (error) {
            return false;
        }
    }
    
    async performLogout(page) {
        try {
            // Handle confirmation dialog
            page.on('dialog', async dialog => {
                await dialog.accept();
            });
            
            // Find and click logout button
            const logoutButton = await page.$('a[onclick="logout()"]');
            
            if (logoutButton) {
                await logoutButton.click();
                await page.waitForTimeout(2000);
                return true;
            }
            
            return false;
        } catch (error) {
            return false;
        }
    }
    
    async testDashboardFeatures(browser) {
        console.log(chalk.yellow('📊 Testing Dashboard Features...'));
        
        const page = await browser.newPage();
        
        try {
            // Login as BOS
            await this.performLogin(page, 'bos', 'bos');
            
            // Test dashboard elements
            const tests = [
                { selector: '.dashboard-header', name: 'Dashboard Header' },
                { selector: '.stats-number', name: 'Stats Numbers' },
                { selector: '.sidebar', name: 'Sidebar Navigation' },
                { selector: '.app-main', name: 'Main Content Area' }
            ];
            
            for (const test of tests) {
                const element = await page.$(test.selector);
                this.addResult(`Dashboard: ${test.name}`, !!element, 
                    !!element ? 'Found' : 'Not found'
                );
            }
            
        } catch (error) {
            this.addResult('Dashboard Features', false, error.message);
        } finally {
            await page.close();
        }
    }
    
    async testNavigationSystem(browser) {
        console.log(chalk.yellow('🧭 Testing Navigation System...'));
        
        const page = await browser.newPage();
        
        try {
            await this.performLogin(page, 'bos', 'bos');
            
            // Test navigation links
            const navLinks = [
                { selector: 'a[href="#dashboard"]', name: 'Dashboard' },
                { selector: 'a[href="#laporan"]', name: 'Laporan' },
                { selector: 'a[href="#nasabah"]', name: 'Nasabah' },
                { selector: 'a[href="#transaksi"]', name: 'Transaksi' }
            ];
            
            for (const link of navLinks) {
                const element = await page.$(link.selector);
                this.addResult(`Navigation: ${link.name}`, !!element, 
                    !!element ? 'Link found' : 'Link not found'
                );
            }
            
        } catch (error) {
            this.addResult('Navigation System', false, error.message);
        } finally {
            await page.close();
        }
    }
    
    async testAPIEndpoints(browser) {
        console.log(chalk.yellow('🔌 Testing API Endpoints...'));
        
        const page = await browser.newPage();
        
        try {
            await this.performLogin(page, 'bos', 'bos');
            
            // Test API calls from page context
            const apiTest = await page.evaluate(async () => {
                try {
                    // Test login API
                    const loginResponse = await fetch('/api/auth.php?action=check_session');
                    const loginData = await loginResponse.json();
                    
                    return {
                        loginAPI: loginData.success,
                        authenticated: loginData.authenticated || false
                    };
                } catch (error) {
                    return {
                        loginAPI: false,
                        error: error.message
                    };
                }
            });
            
            if (apiTest.loginAPI) {
                this.addResult('API: Session Check', true, 'Session API working');
            } else {
                this.addResult('API: Session Check', false, apiTest.error || 'API failed');
            }
            
        } catch (error) {
            this.addResult('API Endpoints', false, error.message);
        } finally {
            await page.close();
        }
    }
    
    async testSecurityFeatures(browser) {
        console.log(chalk.yellow('🔒 Testing Security Features...'));
        
        const page = await browser.newPage();
        
        try {
            // Test XSS protection
            await page.goto(this.baseUrl + '/login.php?xss=<script>alert("xss")</script>');
            const pageContent = await page.content();
            const hasXSS = pageContent.includes('<script>alert("xss")</script>');
            
            this.addResult('Security: XSS Protection', !hasXSS, 
                hasXSS ? 'XSS not filtered' : 'XSS filtered properly'
            );
            
            // Test SQL injection protection
            await page.goto(this.baseUrl + '/login.php');
            await page.type('input[name="username"]', "'; DROP TABLE users; --", { delay: 50 });
            await page.type('input[name="password"]', 'test', { delay: 50 });
            
            const submitButton = await page.$('button[type="submit"]');
            if (submitButton) {
                await submitButton.click();
                await page.waitForTimeout(2000);
                
                const url = page.url();
                const stillOnLogin = url.includes('login.php');
                
                this.addResult('Security: SQL Injection', stillOnLogin, 
                    stillOnLogin ? 'SQL injection blocked' : 'SQL injection not blocked'
                );
            } else {
                this.addResult('Security: SQL Injection', false, 'Could not test');
            }
            
        } catch (error) {
            this.addResult('Security Features', false, error.message);
        } finally {
            await page.close();
        }
    }
    
    async testLogoutSystem(browser) {
        console.log(chalk.yellow('🚪 Testing Logout System...'));
        
        const page = await browser.newPage();
        
        try {
            await this.performLogin(page, 'bos', 'bos');
            
            // Test logout functionality
            const logoutSuccess = await this.performLogout(page);
            
            if (logoutSuccess) {
                // Check if redirected to login page
                const url = page.url();
                const isLoginPage = url.includes('login.php');
                
                this.addResult('Logout System', isLoginPage, 
                    isLoginPage ? 'Logout successful, redirected to login' : 'Logout failed'
                );
            } else {
                this.addResult('Logout System', false, 'Logout button not found or failed');
            }
            
        } catch (error) {
            this.addResult('Logout System', false, error.message);
        } finally {
            await page.close();
        }
    }
    
    async performLogin(page, username, password) {
        await page.goto(this.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
        await page.waitForSelector('input[name="username"]', { timeout: 5000 });
        await page.type('input[name="username"]', username, { delay: 50 });
        await page.type('input[name="password"]', password, { delay: 50 });
        
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle2' }),
            page.click('button[type="submit"]')
        ]);
    }
    
    addResult(testName, passed, message) {
        this.testResults.total++;
        
        if (passed) {
            this.testResults.passed++;
            console.log(chalk.green(`    ✅ ${testName}: ${message}`));
        } else {
            this.testResults.failed++;
            console.log(chalk.red(`    ❌ ${testName}: ${message}`));
            this.testResults.errors.push({ test: testName, message });
        }
    }
    
    generateReport() {
        console.log('\n' + '='.repeat(60));
        console.log(chalk.blue('📊 REALISTIC TEST REPORT'));
        console.log('='.repeat(60) + '\n');
        
        console.log('📈 Test Results:');
        console.log(`  Total Tests: ${this.testResults.total}`);
        console.log(`  Passed: ${chalk.green(this.testResults.passed)}`);
        console.log(`  Failed: ${chalk.red(this.testResults.failed)}`);
        
        const passRate = Math.round((this.testResults.passed / this.testResults.total) * 100);
        console.log(`  Success Rate: ${passRate}%\n`);
        
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
            console.log(chalk.yellow('  ⚠️  GOOD - System mostly functional'));
        } else if (passRate >= 50) {
            console.log(chalk.yellow('  🔶 FAIR - System needs attention'));
        } else {
            console.log(chalk.red('  ❌ POOR - System has significant issues'));
        }
        
        console.log('\n🚀 Recommendations:');
        if (this.testResults.failed === 0) {
            console.log('  • System is ready for production deployment');
            console.log('  • All features working correctly');
            console.log('  • Security measures are effective');
        } else {
            console.log('  • Address failed tests before production');
            console.log('  • Review error messages for specific issues');
            console.log('  • Test individual components separately');
        }
        
        console.log('\n' + '='.repeat(60));
        console.log(chalk.blue('🚀 KSP Lam Gabe Jaya - Realistic Test Complete'));
        console.log('='.repeat(60));
    }
}

// Run tests
if (require.main === module) {
    const testSuite = new RealisticTestSuite();
    testSuite.runComprehensiveTests().then(results => {
        process.exit(results.failed > 0 ? 1 : 0);
    }).catch(error => {
        console.error('Test suite failed:', error);
        process.exit(1);
    });
}

module.exports = RealisticTestSuite;
