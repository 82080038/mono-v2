#!/usr/bin/env node

/**
 * Browser Testing Script for KSP Lam Gabe Jaya
 * Uses Puppeteer for automated browser testing
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class BrowserTester {
    constructor() {
        this.baseUrl = 'http://localhost/mono-v2';
        this.testResults = [];
        this.screenshots = [];
        this.browser = null;
        this.page = null;
    }

    async init() {
        console.log('🚀 Initializing Browser Testing...');
        
        this.browser = await puppeteer.launch({
            headless: false, // Set to true for headless mode
            defaultViewport: { width: 1366, height: 768 },
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        this.page = await this.browser.newPage();
        
        // Set user agent
        await this.page.setUserAgent('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        
        console.log('✅ Browser initialized successfully');
    }

    async takeScreenshot(name, description) {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const filename = `${name}_${timestamp}.png`;
        const filepath = path.join(__dirname, 'screenshots', filename);
        
        // Ensure screenshots directory exists
        if (!fs.existsSync(path.dirname(filepath))) {
            fs.mkdirSync(path.dirname(filepath), { recursive: true });
        }
        
        await this.page.screenshot({ path: filepath, fullPage: true });
        this.screenshots.push({ filename, description, filepath });
        console.log(`📸 Screenshot saved: ${filename}`);
    }

    async testPage(url, testName, expectedElements = []) {
        console.log(`\n🧪 Testing: ${testName}`);
        console.log(`📍 URL: ${url}`);
        
        const startTime = Date.now();
        let success = true;
        let errors = [];
        let elementResults = [];
        
        try {
            // Navigate to page
            const response = await this.page.goto(url, { 
                waitUntil: 'networkidle2',
                timeout: 30000 
            });
            
            const loadTime = Date.now() - startTime;
            const status = response.status();
            
            // Check HTTP status
            if (status !== 200) {
                success = false;
                errors.push(`HTTP Status: ${status}`);
            }
            
            // Check page title
            const title = await this.page.title();
            if (!title || title.trim() === '') {
                success = false;
                errors.push('Empty page title');
            }
            
            // Check expected elements
            for (const element of expectedElements) {
                try {
                    const found = await this.page.$(element.selector);
                    const exists = found !== null;
                    
                    elementResults.push({
                        selector: element.selector,
                        description: element.description,
                        exists: exists,
                        text: exists ? await this.page.evaluate(el => el.textContent, found) : ''
                    });
                    
                    if (element.required && !exists) {
                        success = false;
                        errors.push(`Missing required element: ${element.description}`);
                    }
                } catch (e) {
                    elementResults.push({
                        selector: element.selector,
                        description: element.description,
                        exists: false,
                        error: e.message
                    });
                    
                    if (element.required) {
                        success = false;
                        errors.push(`Error checking element: ${element.description} - ${e.message}`);
                    }
                }
            }
            
            // Check for JavaScript errors
            const jsErrors = await this.page.evaluate(() => {
                return window.errors || [];
            });
            
            if (jsErrors.length > 0) {
                success = false;
                errors.push(...jsErrors);
            }
            
            // Take screenshot
            await this.takeScreenshot(testName.replace(/\s+/g, '_'), `${testName} - ${success ? 'SUCCESS' : 'FAILED'}`);
            
            const result = {
                test: testName,
                url: url,
                success: success,
                loadTime: loadTime,
                status: status,
                title: title,
                errors: errors,
                elements: elementResults,
                timestamp: new Date().toISOString()
            };
            
            this.testResults.push(result);
            
            console.log(`${success ? '✅' : '❌'} ${testName} - ${loadTime}ms`);
            if (!success) {
                console.log(`   Errors: ${errors.join(', ')}`);
            }
            
            return result;
            
        } catch (error) {
            const loadTime = Date.now() - startTime;
            
            const result = {
                test: testName,
                url: url,
                success: false,
                loadTime: loadTime,
                errors: [error.message],
                timestamp: new Date().toISOString()
            };
            
            this.testResults.push(result);
            
            console.log(`❌ ${testName} - ERROR: ${error.message}`);
            return result;
        }
    }

    async testLoginFlow() {
        console.log('\n🔐 Testing Login Flow...');
        
        try {
            // Navigate to login page
            await this.page.goto(`${this.baseUrl}/login.html`, { waitUntil: 'networkidle2' });
            
            // Check login form elements
            const loginForm = await this.page.$('#loginForm');
            const emailInput = await this.page.$('#emailInput');
            const passwordInput = await this.page.$('#passwordInput');
            const loginButton = await this.page.$('#loginBtn');
            
            const elements = [
                { selector: '#loginForm', description: 'Login Form', required: true },
                { selector: '#emailInput', description: 'Email Input', required: true },
                { selector: '#passwordInput', description: 'Password Input', required: true },
                { selector: '#loginBtn', description: 'Login Button', required: true }
            ];
            
            let elementResults = [];
            let success = true;
            
            for (const element of elements) {
                const found = await this.page.$(element.selector);
                const exists = found !== null;
                
                elementResults.push({
                    selector: element.selector,
                    description: element.description,
                    exists: exists
                });
                
                if (element.required && !exists) {
                    success = false;
                }
            }
            
            // Try to fill login form
            if (emailInput && passwordInput && loginButton) {
                await this.page.type('#emailInput', 'admin');
                await this.page.type('#passwordInput', 'admin123');
                
                // Submit form
                await Promise.all([
                    this.page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 10000 }),
                    this.page.click('#loginBtn')
                ]);
                
                // Check if redirected to dashboard
                const currentUrl = this.page.url();
                const loginSuccess = currentUrl.includes('dashboard.html') || currentUrl.includes('admin');
                
                if (!loginSuccess) {
                    success = false;
                    elementResults.push({
                        selector: 'dashboard',
                        description: 'Dashboard Redirect',
                        exists: false,
                        error: 'Login did not redirect to dashboard'
                    });
                }
            }
            
            await this.takeScreenshot('Login_Flow', `Login Flow - ${success ? 'SUCCESS' : 'FAILED'}`);
            
            const result = {
                test: 'Login Flow',
                url: `${this.baseUrl}/login.html`,
                success: success,
                elements: elementResults,
                timestamp: new Date().toISOString()
            };
            
            this.testResults.push(result);
            
            console.log(`${success ? '✅' : '❌'} Login Flow Test`);
            
            return result;
            
        } catch (error) {
            console.log(`❌ Login Flow Error: ${error.message}`);
            return { success: false, error: error.message };
        }
    }

    async testResponsiveDesign() {
        console.log('\n📱 Testing Responsive Design...');
        
        const viewports = [
            { width: 375, height: 667, name: 'Mobile' },
            { width: 768, height: 1024, name: 'Tablet' },
            { width: 1366, height: 768, name: 'Desktop' }
        ];
        
        const results = [];
        
        for (const viewport of viewports) {
            console.log(`  📱 Testing ${viewport.name} (${viewport.width}x${viewport.height})`);
            
            await this.page.setViewport({ width: viewport.width, height: viewport.height });
            
            // Test login page responsive
            await this.page.goto(`${this.baseUrl}/login.html`, { waitUntil: 'networkidle2' });
            
            // Check if mobile menu appears/disappears
            const mobileMenu = await this.page.$('.navbar-toggler');
            const desktopMenu = await this.page.$('.navbar-nav');
            
            const responsive = {
                viewport: viewport.name,
                width: viewport.width,
                height: viewport.height,
                hasMobileMenu: mobileMenu !== null,
                hasDesktopMenu: desktopMenu !== null,
                responsive: true
            };
            
            await this.takeScreenshot(`Responsive_${viewport.name}`, `Responsive ${viewport.name}`);
            
            results.push(responsive);
        }
        
        return results;
    }

    async testJavaScriptFunctionality() {
        console.log('\n⚡ Testing JavaScript Functionality...');
        
        await this.page.goto(`${this.baseUrl}/login.html`, { waitUntil: 'networkidle2' });
        
        // Check if JavaScript is working
        const jsWorking = await this.page.evaluate(() => {
            try {
                // Check jQuery
                if (typeof $ === 'undefined') {
                    return { working: false, error: 'jQuery not loaded' };
                }
                
                // Check Bootstrap
                if (typeof bootstrap === 'undefined') {
                    return { working: false, error: 'Bootstrap not loaded' };
                }
                
                // Check form validation
                const form = document.querySelector('#loginForm');
                if (form && typeof form.checkValidity === 'function') {
                    return { working: true, features: ['jQuery', 'Bootstrap', 'Form Validation'] };
                }
                
                return { working: true, features: ['jQuery', 'Bootstrap'] };
            } catch (e) {
                return { working: false, error: e.message };
            }
        });
        
        console.log(`${jsWorking.working ? '✅' : '❌'} JavaScript Functionality`);
        if (jsWorking.working) {
            console.log(`   Features: ${jsWorking.features.join(', ')}`);
        } else {
            console.log(`   Error: ${jsWorking.error}`);
        }
        
        return jsWorking;
    }

    async runAllTests() {
        console.log('\n🎯 Starting Comprehensive Browser Testing...\n');
        
        // Test main pages
        const pageTests = [
            {
                url: `${this.baseUrl}/login.html`,
                name: 'Login Page',
                elements: [
                    { selector: '#loginForm', description: 'Login Form', required: true },
                    { selector: '#emailInput', description: 'Email Input', required: true },
                    { selector: '#passwordInput', description: 'Password Input', required: true },
                    { selector: '#loginBtn', description: 'Login Button', required: true },
                    { selector: 'title', description: 'Page Title', required: true }
                ]
            },
            {
                url: `${this.baseUrl}/pages/admin/dashboard.html`,
                name: 'Admin Dashboard',
                elements: [
                    { selector: '.dashboard-header', description: 'Dashboard Header', required: true },
                    { selector: '.dashboard-sidebar', description: 'Sidebar', required: true },
                    { selector: '.stat-card', description: 'Statistics Cards', required: false },
                    { selector: 'title', description: 'Page Title', required: true }
                ]
            },
            {
                url: `${this.baseUrl}/pages/staff/dashboard.html`,
                name: 'Staff Dashboard',
                elements: [
                    { selector: '.dashboard-header', description: 'Dashboard Header', required: true },
                    { selector: '.dashboard-sidebar', description: 'Sidebar', required: true },
                    { selector: 'title', description: 'Page Title', required: true }
                ]
            },
            {
                url: `${this.baseUrl}/pages/member/dashboard.html`,
                name: 'Member Dashboard',
                elements: [
                    { selector: '.dashboard-header', description: 'Dashboard Header', required: true },
                    { selector: '.dashboard-sidebar', description: 'Sidebar', required: true },
                    { selector: 'title', description: 'Page Title', required: true }
                ]
            }
        ];
        
        // Run page tests
        for (const test of pageTests) {
            await this.testPage(test.url, test.name, test.elements);
        }
        
        // Test login flow
        await this.testLoginFlow();
        
        // Test responsive design
        await this.testResponsiveDesign();
        
        // Test JavaScript functionality
        await this.testJavaScriptFunctionality();
        
        // Generate report
        await this.generateReport();
    }

    async generateReport() {
        console.log('\n📊 Generating Test Report...');
        
        const report = {
            summary: {
                totalTests: this.testResults.length,
                successfulTests: this.testResults.filter(r => r.success).length,
                failedTests: this.testResults.filter(r => !r.success).length,
                totalScreenshots: this.screenshots.length,
                timestamp: new Date().toISOString()
            },
            results: this.testResults,
            screenshots: this.screenshots,
            browserInfo: {
                userAgent: await this.page.evaluate(() => navigator.userAgent),
                viewport: await this.page.viewport()
            }
        };
        
        // Save report
        const reportPath = path.join(__dirname, 'browser-test-report.json');
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        // Generate HTML report
        const htmlReport = this.generateHTMLReport(report);
        const htmlReportPath = path.join(__dirname, 'browser-test-report.html');
        fs.writeFileSync(htmlReportPath, htmlReport);
        
        console.log(`✅ Report saved: ${reportPath}`);
        console.log(`✅ HTML Report saved: ${htmlReportPath}`);
        
        // Print summary
        console.log('\n📈 Test Summary:');
        console.log(`   Total Tests: ${report.summary.totalTests}`);
        console.log(`   Successful: ${report.summary.successfulTests}`);
        console.log(`   Failed: ${report.summary.failedTests}`);
        console.log(`   Success Rate: ${((report.summary.successfulTests / report.summary.totalTests) * 100).toFixed(1)}%`);
        console.log(`   Screenshots: ${report.summary.totalScreenshots}`);
    }

    generateHTMLReport(report) {
        return `
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browser Test Report - KSP Lam Gabe Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .test-success { border-left: 4px solid #28a745; }
        .test-failed { border-left: 4px solid #dc3545; }
        .screenshot-thumb { max-width: 200px; height: auto; border-radius: 8px; }
        .element-check { font-size: 0.9em; }
        .element-exists { color: #28a745; }
        .element-missing { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="fas fa-browser me-2"></i>
                    Browser Test Report
                    <small class="text-muted">KSP Lam Gabe Jaya</small>
                </h1>
                
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Tests</h5>
                                <h3>${report.summary.totalTests}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Successful</h5>
                                <h3>${report.summary.successfulTests}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5 class="card-title">Failed</h5>
                                <h3>${report.summary.failedTests}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Success Rate</h5>
                                <h3>${((report.summary.successfulTests / report.summary.totalTests) * 100).toFixed(1)}%</h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Test Results -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Test Results
                        </h5>
                    </div>
                    <div class="card-body">
                        ${report.results.map(result => `
                            <div class="card mb-3 ${result.success ? 'test-success' : 'test-failed'}">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="fas fa-${result.success ? 'check-circle text-success' : 'times-circle text-danger'} me-2"></i>
                                        ${result.test}
                                    </h6>
                                    <span class="badge bg-${result.success ? 'success' : 'danger'}">
                                        ${result.success ? 'SUCCESS' : 'FAILED'}
                                    </span>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2">
                                        <strong>URL:</strong> <a href="${result.url}" target="_blank">${result.url}</a><br>
                                        <strong>Load Time:</strong> ${result.loadTime}ms<br>
                                        ${result.status ? `<strong>Status:</strong> ${result.status}<br>` : ''}
                                        ${result.title ? `<strong>Title:</strong> ${result.title}<br>` : ''}
                                        <strong>Timestamp:</strong> ${new Date(result.timestamp).toLocaleString('id-ID')}
                                    </p>
                                    
                                    ${result.elements && result.elements.length > 0 ? `
                                        <h6>Element Checks:</h6>
                                        <div class="element-check">
                                            ${result.elements.map(el => `
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span>${el.description}</span>
                                                    <span class="${el.exists ? 'element-exists' : 'element-missing'}">
                                                        <i class="fas fa-${el.exists ? 'check' : 'times'}"></i>
                                                        ${el.exists ? 'Found' : 'Missing'}
                                                    </span>
                                                </div>
                                            `).join('')}
                                        </div>
                                    ` : ''}
                                    
                                    ${result.errors && result.errors.length > 0 ? `
                                        <h6 class="text-danger">Errors:</h6>
                                        <ul class="text-danger">
                                            ${result.errors.map(error => `<li>${error}</li>`).join('')}
                                        </ul>
                                    ` : ''}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <!-- Screenshots -->
                ${report.screenshots.length > 0 ? `
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-images me-2"></i>
                                Screenshots (${report.screenshots.length})
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                ${report.screenshots.map(screenshot => `
                                    <div class="col-md-3 mb-3">
                                        <div class="card">
                                            <img src="screenshots/${screenshot.filename}" class="card-img-top screenshot-thumb" alt="${screenshot.description}">
                                            <div class="card-body p-2">
                                                <small class="d-block text-muted">${screenshot.description}</small>
                                                <small class="d-block">${screenshot.filename}</small>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                ` : ''}
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>`;
    }

    async cleanup() {
        if (this.browser) {
            await this.browser.close();
            console.log('🧹 Browser closed');
        }
    }
}

// Main execution
async function main() {
    const tester = new BrowserTester();
    
    try {
        await tester.init();
        await tester.runAllTests();
    } catch (error) {
        console.error('❌ Test execution failed:', error.message);
    } finally {
        await tester.cleanup();
    }
}

// Run if called directly
if (require.main === module) {
    main().catch(console.error);
}

module.exports = BrowserTester;
