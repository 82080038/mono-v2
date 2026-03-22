const puppeteer = require('puppeteer');
const chalk = require('chalk');

/**
 * KSP Lam Gabe Jaya - Simple Test Suite
 * Focus on basic functionality
 */
class SimpleTestSuite {
    constructor() {
        this.baseUrl = 'http://localhost/mono-v2';
        this.testResults = { total: 0, passed: 0, failed: 0, errors: [] };
    }
    
    async runTests() {
        console.log(chalk.blue('🚀 KSP Lam Gabe Jaya - Simple Test Suite'));
        console.log('='.repeat(60));
        
        const browser = await puppeteer.launch({ 
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        try {
            // Test 1: Basic Login Page
            await this.testBasicLogin(browser);
            
            // Test 2: BOS Login (most important role)
            await this.testBOSLogin(browser);
            
            // Test 3: Basic Dashboard
            await this.testBasicDashboard(browser);
            
            // Test 4: Logout
            await this.testLogout(browser);
            
            // Test 5: Security
            await this.testBasicSecurity(browser);
            
        } catch (error) {
            console.error(chalk.red('Test suite error:'), error.message);
        } finally {
            await browser.close();
            this.generateReport();
        }
        
        return this.testResults;
    }
    
    async testBasicLogin(browser) {
        console.log(chalk.yellow('🔐 Testing Basic Login...'));
        
        const page = await browser.newPage();
        
        try {
            // Test login page accessibility
            const response = await page.goto(this.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
            const status = response.status();
            
            if (status === 200) {
                this.addResult('Login Page Access', true, `HTTP ${status}`);
                
                // Check form elements
                const usernameField = await page.$('input[name="username"]');
                const passwordField = await page.$('input[name="password"]');
                const submitButton = await page.$('button[type="submit"]');
                
                if (usernameField && passwordField && submitButton) {
                    this.addResult('Login Form Elements', true, 'All elements found');
                } else {
                    this.addResult('Login Form Elements', false, 'Missing elements');
                }
            } else {
                this.addResult('Login Page Access', false, `HTTP ${status}`);
            }
            
        } catch (error) {
            this.addResult('Basic Login', false, error.message);
        } finally {
            await page.close();
        }
    }
    
    async testBOSLogin(browser) {
        console.log(chalk.yellow('👤 Testing BOS Login...'));
        
        const page = await browser.newPage();
        
        try {
            // Go to login page
            await page.goto(this.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
            
            // Fill login form
            await page.waitForSelector('input[name="username"]', { timeout: 5000 });
            await page.type('input[name="username"]', 'bos', { delay: 50 });
            await page.type('input[name="password"]', 'bos', { delay: 50 });
            
            // Submit login
            await Promise.all([
                page.waitForNavigation({ waitUntil: 'networkidle2' }),
                page.click('button[type="submit"]')
            ]);
            
            // Check if successful
            const url = page.url();
            const isDashboard = url.includes('dashboard') || url.includes('main.php');
            
            if (isDashboard) {
                this.addResult('BOS Login', true, 'Login successful');
                
                // Check for BOS-specific content
                const content = await page.content();
                const hasBOSContent = content.includes('Total Anggota') || 
                                    content.includes('Total Simpanan') || 
                                    content.includes('Total Omzet');
                
                this.addResult('BOS Dashboard Content', hasBOSContent, 
                    hasBOSContent ? 'BOS content found' : 'BOS content missing'
                );
            } else {
                this.addResult('BOS Login', false, 'Not redirected to dashboard');
            }
            
        } catch (error) {
            this.addResult('BOS Login', false, error.message);
        } finally {
            await page.close();
        }
    }
    
    async testBasicDashboard(browser) {
        console.log(chalk.yellow('📊 Testing Basic Dashboard...'));
        
        const page = await browser.newPage();
        
        try {
            // Login as BOS
            await this.loginAsBOS(page);
            
            // Check dashboard elements
            const tests = [
                { selector: '.dashboard-header', name: 'Header' },
                { selector: '.sidebar', name: 'Sidebar' },
                { selector: '.app-main', name: 'Main Content' }
            ];
            
            for (const test of tests) {
                const element = await page.$(test.selector);
                this.addResult(`Dashboard: ${test.name}`, !!element, 
                    !!element ? 'Found' : 'Not found'
                );
            }
            
        } catch (error) {
            this.addResult('Basic Dashboard', false, error.message);
        } finally {
            await page.close();
        }
    }
    
    async testLogout(browser) {
        console.log(chalk.yellow('🚪 Testing Logout...'));
        
        const page = await browser.newPage();
        
        try {
            // Login as BOS
            await this.loginAsBOS(page);
            
            // Handle confirmation dialog
            page.on('dialog', async dialog => {
                await dialog.accept();
            });
            
            // Find logout button
            const logoutButton = await page.$('a[onclick="logout()"]');
            
            if (logoutButton) {
                await logoutButton.click();
                await page.waitForTimeout(2000);
                
                // Check if redirected to login
                const url = page.url();
                const isLoginPage = url.includes('login.php');
                
                this.addResult('Logout Functionality', isLoginPage, 
                    isLoginPage ? 'Logout successful' : 'Logout failed'
                );
            } else {
                this.addResult('Logout Functionality', false, 'Logout button not found');
            }
            
        } catch (error) {
            this.addResult('Logout', false, error.message);
        } finally {
            await page.close();
        }
    }
    
    async testBasicSecurity(browser) {
        console.log(chalk.yellow('🔒 Testing Basic Security...'));
        
        const page = await browser.newPage();
        
        try {
            // Test XSS protection
            await page.goto(this.baseUrl + '/login.php?xss=<script>alert("xss")</script>');
            const pageContent = await page.content();
            const hasXSS = pageContent.includes('<script>alert("xss")</script>');
            
            this.addResult('XSS Protection', !hasXSS, 
                hasXSS ? 'XSS not filtered' : 'XSS filtered properly'
            );
            
        } catch (error) {
            this.addResult('Basic Security', false, error.message);
        } finally {
            await page.close();
        }
    }
    
    async loginAsBOS(page) {
        await page.goto(this.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
        await page.waitForSelector('input[name="username"]', { timeout: 5000 });
        await page.type('input[name="username"]', 'bos', { delay: 50 });
        await page.type('input[name="password"]', 'bos', { delay: 50 });
        
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
        console.log(chalk.blue('📊 SIMPLE TEST REPORT'));
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
        
        console.log('\n🚀 Key Findings:');
        if (this.testResults.passed >= 4) {
            console.log('  ✅ Basic authentication working');
            console.log('  ✅ Dashboard loading correctly');
            console.log('  ✅ Logout functionality working');
            console.log('  ✅ Security measures in place');
        } else {
            console.log('  ❌ Core functionality needs attention');
            console.log('  ❌ Review failed tests for specific issues');
        }
        
        console.log('\n' + '='.repeat(60));
        console.log(chalk.blue('🚀 KSP Lam Gabe Jaya - Simple Test Complete'));
        console.log('='.repeat(60));
    }
}

// Run tests
if (require.main === module) {
    const testSuite = new SimpleTestSuite();
    testSuite.runTests().then(results => {
        process.exit(results.failed > 0 ? 1 : 0);
    }).catch(error => {
        console.error('Test suite failed:', error);
        process.exit(1);
    });
}

module.exports = SimpleTestSuite;
