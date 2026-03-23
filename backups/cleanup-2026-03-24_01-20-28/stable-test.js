const puppeteer = require('puppeteer');
const chalk = require('chalk');

/**
 * KSP Lam Gabe Jaya - Stable Comprehensive Test Suite
 */
class StableTestSuite {
    constructor() {
        this.baseUrl = 'http://localhost/mono-v2';
        this.testResults = { total: 0, passed: 0, failed: 0, errors: [] };
    }
    
    async runComprehensiveTests() {
        console.log(chalk.blue('🚀 KSP Lam Gabe Jaya - Comprehensive Test Suite'));
        console.log('='.repeat(60));
        
        const browser = await puppeteer.launch({ 
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        try {
            // Test 1: Login Page
            await this.testLoginPage(browser);
            
            // Test 2: All Role Logins
            await this.testAllRoleLogins(browser);
            
            // Test 3: Dashboard Content
            await this.testDashboardContent(browser);
            
            // Test 4: Navigation System
            await this.testNavigationSystem(browser);
            
            // Test 5: API Endpoints
            await this.testAPIEndpoints(browser);
            
            // Test 6: Security Features
            await this.testSecurityFeatures(browser);
            
            // Test 7: Logout Functionality
            await this.testLogoutFunctionality(browser);
            
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
    
    async testAllRoleLogins(browser) {
        console.log(chalk.yellow('👥 Testing All Role Logins...'));
        
        const roles = [
            { name: 'bos', username: 'bos', password: 'bos' },
            { name: 'admin', username: 'admin', password: 'admin' },
            { name: 'teller', username: 'teller', password: 'teller' },
            { name: 'collector', username: 'collector', password: 'collector' },
            { name: 'nasabah', username: 'nasabah', password: 'nasabah' }
        ];
        
        for (const role of roles) {
            await this.testRoleLogin(browser, role);
        }
    }
    
    async testRoleLogin(browser, role) {
        const page = await browser.newPage();
        
        try {
            // Always go to login page first
            await page.goto(this.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
            
            // Wait for form to be ready
            await page.waitForSelector('input[name="username"]', { timeout: 5000 });
            
            // Fill and submit form
            await page.type('input[name="username"]', role.username, { delay: 50 });
            await page.type('input[name="password"]', role.password, { delay: 50 });
            
            await Promise.all([
                page.waitForNavigation({ waitUntil: 'networkidle2' }),
                page.click('button[type="submit"]')
            ]);
            
            // Check if redirected to dashboard
            const url = page.url();
            const isDashboard = url.includes('dashboard') || url.includes('main.php');
            
            if (isDashboard) {
                this.addResult(`${role.name} Login`, true, 'Login successful');
                
                // Check for role-specific content
                const hasContent = await this.checkRoleContent(page, role.name);
                this.addResult(`${role.name} Content`, hasContent, 
                    hasContent ? 'Role content found' : 'Role content missing'
                );
            } else {
                this.addResult(`${role.name} Login`, false, 'Not redirected to dashboard');
            }
            
        } catch (error) {
            this.addResult(`${role.name} Login`, false, error.message);
        } finally {
            await page.close();
        }
    }
    
    async checkRoleContent(page, role) {
        try {
            const content = {
                bos: ['Total Anggota', 'Total Simpanan', 'Total Omzet'],
                admin: ['Anggota Aktif', 'Transaksi Hari Ini', 'User Terdaftar'],
                teller: ['Transaksi Hari Ini', 'Setoran', 'Penarikan'],
                collector: ['Target Hari Ini', 'Kunjungan Selesai', 'Kutipan Terkumpul'],
                nasabah: ['Saldo Simpanan', 'Pinjaman Aktif', 'Cicilan Bulanan']
            };
            
            const expectedTexts = content[role] || [];
            
            for (const text of expectedTexts) {
                const element = await page.$(`text=${text}`);
                if (!element) return false;
            }
            
            return true;
        } catch (error) {
            return false;
        }
    }
    
    async testDashboardContent(browser) {
        console.log(chalk.yellow('📊 Testing Dashboard Content...'));
        
        const page = await browser.newPage();
        
        try {
            await this.loginAs(page, 'bos');
            
            // Check dashboard elements
            const elements = [
                '.dashboard-header',
                '.stats-number',
                '.sidebar',
                '.app-main'
            ];
            
            let hasAllElements = true;
            
            for (const selector of elements) {
                const element = await page.$(selector);
                if (!element) {
                    hasAllElements = false;
                    break;
                }
            }
            
            this.addResult('Dashboard Content', hasAllElements, 
                hasAllElements ? 'All elements found' : 'Missing elements'
            );
            
        } catch (error) {
            this.addResult('Dashboard Content', false, error.message);
        } finally {
            await page.close();
        }
    }
    
    async testNavigationSystem(browser) {
        console.log(chalk.yellow('🧭 Testing Navigation System...'));
        
        const page = await browser.newPage();
        
        try {
            await this.loginAs(page, 'bos');
            
            // Test navigation links
            const navTests = [
                { link: 'a[href="#dashboard"]', name: 'Dashboard' },
                { link: 'a[href="#laporan"]', name: 'Laporan' },
                { link: 'a[href="#nasabah"]', name: 'Nasabah' }
            ];
            
            for (const test of navTests) {
                try {
                    const link = await page.$(test.link);
                    if (link) {
                        this.addResult(`Nav: ${test.name}`, true, 'Link found');
                    } else {
                        this.addResult(`Nav: ${test.name}`, false, 'Link not found');
                    }
                } catch (error) {
                    this.addResult(`Nav: ${test.name}`, false, error.message);
                }
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
            // Test login API
            const loginResult = await page.evaluate(async () => {
                try {
                    const response = await fetch('/api/auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=login&username=bos&password=bos'
                    });
                    return await response.json();
                } catch (error) {
                    return { success: false, error: error.message };
                }
            });
            
            if (loginResult.success) {
                this.addResult('API: Login', true, 'Login API working');
            } else {
                this.addResult('API: Login', false, loginResult.error || 'Login failed');
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
            
            this.addResult('Security: XSS', !hasXSS, 
                hasXSS ? 'XSS not filtered' : 'XSS filtered'
            );
            
        } catch (error) {
            this.addResult('Security Features', false, error.message);
        } finally {
            await page.close();
        }
    }
    
    async testLogoutFunctionality(browser) {
        console.log(chalk.yellow('🚪 Testing Logout Functionality...'));
        
        const page = await browser.newPage();
        
        try {
            await this.loginAs(page, 'bos');
            
            // Handle confirmation dialog
            page.on('dialog', async dialog => {
                await dialog.accept();
            });
            
            // Find and click logout button
            const logoutButton = await page.$('a[onclick="logout()"]');
            
            if (logoutButton) {
                await logoutButton.click();
                await page.waitForTimeout(2000);
                
                const url = page.url();
                const isLoginPage = url.includes('login.php');
                
                this.addResult('Logout Functionality', isLoginPage,
                    isLoginPage ? 'Logout successful' : 'Logout failed'
                );
            } else {
                this.addResult('Logout Functionality', false, 'Logout button not found');
            }
            
        } catch (error) {
            this.addResult('Logout Functionality', false, error.message);
        } finally {
            await page.close();
        }
    }
    
    async loginAs(page, role) {
        await page.goto(this.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
        await page.waitForSelector('input[name="username"]', { timeout: 5000 });
        await page.type('input[name="username"]', role, { delay: 50 });
        await page.type('input[name="password"]', role, { delay: 50 });
        
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
        console.log(chalk.blue('📊 COMPREHENSIVE TEST REPORT'));
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
        
        console.log('\n' + '='.repeat(60));
        console.log(chalk.blue('🚀 KSP Lam Gabe Jaya - Test Complete'));
        console.log('='.repeat(60));
    }
}

// Run tests
if (require.main === module) {
    const testSuite = new StableTestSuite();
    testSuite.runComprehensiveTests().then(results => {
        process.exit(results.failed > 0 ? 1 : 0);
    }).catch(error => {
        console.error('Test suite failed:', error);
        process.exit(1);
    });
}

module.exports = StableTestSuite;
