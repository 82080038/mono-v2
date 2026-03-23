#!/usr/bin/env node

/**
 * Simplified Comprehensive System Testing with Puppeteer
 * Test all pages and fix errors found
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class SimpleSystemTester {
    constructor() {
        this.baseUrl = 'http://localhost/mono-v2';
        this.testResults = [];
        this.errors = [];
        this.fixes = [];
        this.screenshots = [];
        this.browser = null;
        this.page = null;
    }

    async init() {
        console.log('🚀 Initializing System Testing...');
        
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
                    timestamp: new Date().toISOString()
                });
            }
        });
        
        console.log('✅ Browser initialized');
    }

    async takeScreenshot(name, description, isError = false) {
        try {
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            const filename = `${name}_${timestamp}.png`;
            const filepath = path.join(__dirname, 'screenshots', filename);
            
            // Ensure screenshots directory exists
            if (!fs.existsSync(path.dirname(filepath))) {
                fs.mkdirSync(path.dirname(filepath), { recursive: true });
            }
            
            await this.page.screenshot({ 
                path: filepath, 
                fullPage: true
            });
            
            this.screenshots.push({ 
                filename, 
                description, 
                filepath, 
                isError,
                timestamp: new Date().toISOString()
            });
            
            console.log(`📸 Screenshot: ${filename} ${isError ? '(ERROR)' : ''}`);
        } catch (error) {
            console.log(`❌ Screenshot failed: ${error.message}`);
        }
    }

    async testPage(url, testName, expectedElements = []) {
        console.log(`\n🧪 Testing: ${testName}`);
        console.log(`📍 URL: ${url}`);
        
        const startTime = Date.now();
        let testResult = {
            test: testName,
            url: url,
            success: false,
            loadTime: 0,
            errors: [],
            elements: [],
            timestamp: new Date().toISOString()
        };
        
        try {
            // Navigate to page
            const response = await this.page.goto(url, { 
                waitUntil: 'networkidle2',
                timeout: 30000
            });
            
            const loadTime = Date.now() - startTime;
            testResult.loadTime = loadTime;
            
            // Check HTTP status
            const status = response.status();
            if (status !== 200) {
                testResult.errors.push(`HTTP Status: ${status}`);
            }
            
            // Check page title
            const title = await this.page.title();
            if (!title || title.trim() === '') {
                testResult.errors.push('Empty page title');
            }
            
            // Check expected elements
            for (const element of expectedElements) {
                try {
                    const found = await this.page.$(element.selector);
                    const exists = found !== null;
                    
                    testResult.elements.push({
                        selector: element.selector,
                        description: element.description,
                        exists: exists,
                        required: element.required || false
                    });
                    
                    if (element.required && !exists) {
                        testResult.errors.push(`Missing required element: ${element.description}`);
                    }
                } catch (e) {
                    testResult.errors.push(`Error checking element ${element.description}: ${e.message}`);
                }
            }
            
            // Determine success
            testResult.success = testResult.errors.length === 0;
            
            // Take screenshot
            await this.takeScreenshot(testName.replace(/\s+/g, '_'), `${testName} - ${testResult.success ? 'SUCCESS' : 'FAILED'}`, !testResult.success);
            
            this.testResults.push(testResult);
            
            console.log(`${testResult.success ? '✅' : '❌'} ${testName} - ${loadTime}ms`);
            if (!testResult.success) {
                console.log(`   Errors: ${testResult.errors.join(', ')}`);
            }
            
            return testResult;
            
        } catch (error) {
            const loadTime = Date.now() - startTime;
            
            testResult.loadTime = loadTime;
            testResult.errors.push(`Page load error: ${error.message}`);
            
            this.testResults.push(testResult);
            
            await this.takeScreenshot(testName.replace(/\s+/g, '_'), `${testName} - ERROR`, true);
            
            console.log(`❌ ${testName} - ERROR: ${error.message}`);
            return testResult;
        }
    }

    async runAllTests() {
        console.log('\n🎯 Starting Comprehensive System Testing...\n');
        
        // Test 1: Basic page loading
        console.log('📄 Phase 1: Basic Page Loading');
        const basicPages = [
            { url: '/login.html', name: 'Login Page' },
            { url: '/index.html', name: 'Home Page' },
            { url: '/pages/admin/dashboard.html', name: 'Admin Dashboard' },
            { url: '/pages/staff/dashboard.html', name: 'Staff Dashboard' },
            { url: '/pages/member/dashboard.html', name: 'Member Dashboard' },
            { url: '/pages/admin/members.html', name: 'Members Page' },
            { url: '/pages/admin/loans.html', name: 'Loans Page' }
        ];
        
        for (const page of basicPages) {
            await this.testPage(
                `${this.baseUrl}${page.url}`,
                page.name,
                [
                    { selector: 'title', description: 'Page Title', required: true },
                    { selector: 'body', description: 'Page Body', required: true }
                ]
            );
        }
        
        // Test 2: API endpoints
        console.log('\n🌐 Phase 2: API Testing');
        await this.testAPIEndpoints();
        
        // Generate report and fix errors
        await this.generateReport();
        await this.fixErrors();
        
        return this.testResults;
    }

    async testAPIEndpoints() {
        console.log('\n🌐 Testing API Endpoints...');
        
        const endpoints = [
            { url: '/api/dashboard.php?action=admin_stats&token=MTI6T3duZXIgVXNlcjoxNzc0MDk2MDk3', name: 'Dashboard Stats' },
            { url: '/api/members.php?action=list&token=MTI6T3duZXIgVXNlcjoxNzc0MDk2MDk3', name: 'Members List' },
            { url: '/api/loans.php?action=list&token=MTI6T3duZXIgVXNlcjoxNzc0MDk2MDk3', name: 'Loans List' },
            { url: '/api/digital-payments.php?action=methods&token=MTI6T3duZXIgVXNlcjoxNzc0MDk2MDk3', name: 'Digital Payments' }
        ];
        
        for (const endpoint of endpoints) {
            console.log(`   Testing: ${endpoint.name}`);
            
            try {
                const response = await this.page.goto(`${this.baseUrl}${endpoint.url}`, {
                    waitUntil: 'networkidle2',
                    timeout: 10000
                });
                
                const content = await this.page.content();
                const isSuccess = response.status() === 200 && content.includes('success');
                
                this.testResults.push({
                    test: endpoint.name,
                    url: `${this.baseUrl}${endpoint.url}`,
                    success: isSuccess,
                    loadTime: 0,
                    errors: isSuccess ? [] : [`HTTP ${response.status()}`],
                    timestamp: new Date().toISOString()
                });
                
                console.log(`   ${isSuccess ? '✅' : '❌'} ${endpoint.name} - HTTP ${response.status()}`);
                
            } catch (error) {
                this.testResults.push({
                    test: endpoint.name,
                    url: `${this.baseUrl}${endpoint.url}`,
                    success: false,
                    loadTime: 0,
                    errors: [error.message],
                    timestamp: new Date().toISOString()
                });
                
                console.log(`   ❌ ${endpoint.name} - ERROR: ${error.message}`);
            }
        }
    }

    async fixErrors() {
        console.log('\n🔧 Auto-fixing Errors...');
        
        const fixes = [];
        
        // Fix test result errors
        for (const result of this.testResults) {
            if (!result.success) {
                for (const elementError of result.elements.filter(e => !e.exists && e.required)) {
                    const fix = await this.fixMissingElement(elementError, result.url);
                    if (fix) fixes.push(fix);
                }
                
                // Fix missing JavaScript libraries
                if (result.errors.some(e => e.includes('$ is not defined'))) {
                    const fix = await this.addJQueryToPage(result.url);
                    if (fix) fixes.push(fix);
                }
                
                if (result.errors.some(e => e.includes('bootstrap is not defined'))) {
                    const fix = await this.addBootstrapToPage(result.url);
                    if (fix) fixes.push(fix);
                }
            }
        }
        
        this.fixes = fixes;
        
        console.log(`✅ Fixed ${fixes.length} errors automatically`);
        
        return fixes;
    }

    async fixMissingElement(element, url) {
        console.log(`   🔧 Adding missing element: ${element.description}`);
        
        const filePath = this.getFilePathFromUrl(url);
        if (!filePath || !fs.existsSync(filePath)) {
            return null;
        }
        
        let content = fs.readFileSync(filePath, 'utf8');
        
        // Add missing elements based on selector
        if (element.selector === '.dashboard-header') {
            if (!content.includes('<div class="dashboard-header"')) {
                const headerHTML = `
<div class="dashboard-header">
    <div class="header-content">
        <h1 class="header-title">Dashboard</h1>
        <div class="header-actions">
            <button class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> New
            </button>
        </div>
    </div>
</div>`;
                
                content = content.replace('<div class="dashboard-content">', headerHTML + '\n        <div class="dashboard-content">');
                fs.writeFileSync(filePath, content);
                
                return {
                    type: 'added_element',
                    element: element.description,
                    file: filePath,
                    success: true
                };
            }
        }
        
        if (element.selector === '.dashboard-sidebar') {
            if (!content.includes('<div class="dashboard-sidebar"')) {
                const sidebarHTML = `
<div class="dashboard-sidebar">
    <div class="sidebar-content">
        <div class="sidebar-menu">
            <a href="dashboard.html" class="menu-item active">
                <i class="fas fa-home me-2"></i> Dashboard
            </a>
            <a href="members.html" class="menu-item">
                <i class="fas fa-users me-2"></i> Anggota
            </a>
            <a href="loans.html" class="menu-item">
                <i class="fas fa-hand-holding-usd me-2"></i> Pinjaman
            </a>
        </div>
    </div>
</div>`;
                
                content = content.replace('<div class="dashboard-content">', sidebarHTML + '\n        <div class="dashboard-content">');
                fs.writeFileSync(filePath, content);
                
                return {
                    type: 'added_element',
                    element: element.description,
                    file: filePath,
                    success: true
                };
            }
        }
        
        return null;
    }

    async addJQueryToPage(url) {
        const filePath = this.getFilePathFromUrl(url);
        if (!filePath || !fs.existsSync(filePath)) {
            return null;
        }
        
        let content = fs.readFileSync(filePath, 'utf8');
        
        if (!content.includes('jquery')) {
            const jqueryScript = '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
            content = content.replace('</head>', jqueryScript + '\n    </head>');
            fs.writeFileSync(filePath, content);
            
            return {
                type: 'added_jquery',
                file: filePath,
                success: true
            };
        }
        
        return null;
    }

    async addBootstrapToPage(url) {
        const filePath = this.getFilePathFromUrl(url);
        if (!filePath || !fs.existsSync(filePath)) {
            return null;
        }
        
        let content = fs.readFileSync(filePath, 'utf8');
        
        if (!content.includes('bootstrap')) {
            const bootstrapCSS = '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
            const bootstrapJS = '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>';
            
            content = content.replace('</head>', bootstrapCSS + '\n    </head>');
            content = content.replace('</body>', bootstrapJS + '\n</body>');
            
            fs.writeFileSync(filePath, content);
            
            return {
                type: 'added_bootstrap',
                file: filePath,
                success: true
            };
        }
        
        return null;
    }

    getFilePathFromUrl(url) {
        // Convert URL to file path
        const urlPath = new URL(url).pathname;
        const fullPath = path.join(__dirname, urlPath);
        
        // If it's a directory, look for index.html
        if (fs.existsSync(fullPath) && fs.statSync(fullPath).isDirectory()) {
            const indexPath = path.join(fullPath, 'index.html');
            return fs.existsSync(indexPath) ? indexPath : path.join(fullPath, 'login.html');
        }
        
        // If it doesn't exist, try adding .html
        if (!fs.existsSync(fullPath) && !fullPath.endsWith('.html')) {
            return fullPath + '.html';
        }
        
        return fullPath;
    }

    async generateReport() {
        console.log('\n📊 Generating Test Report...');
        
        const report = {
            summary: {
                totalTests: this.testResults.length,
                successfulTests: this.testResults.filter(r => r.success).length,
                failedTests: this.testResults.filter(r => !r.success).length,
                totalErrors: this.errors.length,
                totalFixes: this.fixes.length,
                totalScreenshots: this.screenshots.length,
                timestamp: new Date().toISOString()
            },
            results: this.testResults,
            errors: this.errors,
            fixes: this.fixes,
            screenshots: this.screenshots
        };
        
        // Save JSON report
        const reportPath = path.join(__dirname, 'comprehensive-test-report.json');
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        console.log(`✅ Report saved: ${reportPath}`);
        console.log(`📊 Total Tests: ${report.summary.totalTests}`);
        console.log(`✅ Successful: ${report.summary.successfulTests}`);
        console.log(`❌ Failed: ${report.summary.failedTests}`);
        console.log(`🔧 Fixes Applied: ${report.summary.totalFixes}`);
        console.log(`📸 Screenshots: ${report.summary.totalScreenshots}`);
        
        return report;
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
    const tester = new SimpleSystemTester();
    
    try {
        await tester.init();
        const results = await tester.runAllTests();
        
        console.log('\n🎉 COMPREHENSIVE TESTING COMPLETED!');
        
        return results;
        
    } catch (error) {
        console.error('❌ Testing failed:', error.message);
        throw error;
    } finally {
        await tester.cleanup();
    }
}

// Run if called directly
if (require.main === module) {
    main().catch(console.error);
}

module.exports = SimpleSystemTester;
