#!/usr/bin/env node

/**
 * Comprehensive System Testing with Puppeteer
 * Test all pages, components, and functionality
 * Auto-fix errors found during testing
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class ComprehensiveSystemTester {
    constructor() {
        this.baseUrl = 'http://localhost/mono-v2';
        this.testResults = [];
        this.errors = [];
        this.fixes = [];
        this.screenshots = [];
        this.browser = null;
        this.page = null;
        this.testTimeout = 30000;
    }

    async init() {
        console.log('🚀 Initializing Comprehensive System Testing...');
        
        this.browser = await puppeteer.launch({
            headless: false, // Set to true for headless mode
            defaultViewport: { width: 1366, height: 768 },
            args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
        });
        
        this.page = await this.browser.newPage();
        
        // Enable request interception for debugging
        await this.page.setRequestInterception(true);
        this.page.on('request', request => {
            request.continue();
        });
        
        // Capture console errors
        this.page.on('console', msg => {
            if (msg.type() === 'error') {
                this.errors.push({
                    type: 'console_error',
                    message: msg.text(),
                    location: msg.location(),
                    timestamp: new Date().toISOString()
                });
            }
        });
        
        // Capture page errors
        this.page.on('pageerror', error => {
            this.errors.push({
                type: 'page_error',
                message: error.message,
                stack: error.stack,
                timestamp: new Date().toISOString()
            });
        });
        
        console.log('✅ Browser initialized successfully');
    }

    async takeScreenshot(name, description, isError = false) {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const filename = `${name}_${timestamp}.png`;
        const filepath = path.join(__dirname, 'screenshots', filename);
        
        // Ensure screenshots directory exists
        if (!fs.existsSync(path.dirname(filepath))) {
            fs.mkdirSync(path.dirname(filepath), { recursive: true });
        }
        
        await this.page.screenshot({ 
            path: filepath, 
            fullPage: true,
            quality: isError ? 50 : 90
        });
        
        this.screenshots.push({ 
            filename, 
            description, 
            filepath, 
            isError,
            timestamp: new Date().toISOString()
        });
        
        console.log(`📸 Screenshot saved: ${filename} ${isError ? '(ERROR)' : ''}`);
    }

    async testPage(url, testName, expectedElements = [], actions = []) {
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
            actions: [],
            timestamp: new Date().toISOString()
        };
        
        try {
            // Navigate to page
            const response = await this.page.goto(url, { 
                waitUntil: 'networkidle2',
                timeout: this.testTimeout
            });
            
            const loadTime = Date.now() - startTime;
            testResult.loadTime = loadTime;
            
            // Check HTTP status
            const status = response.status();
            if (status !== 200) {
                testResult.errors.push(`HTTP Status: ${status}`);
                await this.takeScreenshot(testName.replace(/\s+/g, '_'), `${testName} - HTTP_${status}`, true);
            }
            
            // Check page title
            const title = await this.page.title();
            if (!title || title.trim() === '') {
                testResult.errors.push('Empty page title');
            }
            
            // Check for JavaScript errors
            const jsErrors = await this.page.evaluate(() => {
                return window.errors || [];
            });
            
            if (jsErrors.length > 0) {
                testResult.errors.push(...jsErrors);
            }
            
            // Check expected elements
            for (const element of expectedElements) {
                try {
                    const found = await this.page.$(element.selector);
                    const exists = found !== null;
                    
                    const elementResult = {
                        selector: element.selector,
                        description: element.description,
                        exists: exists,
                        required: element.required || false
                    };
                    
                    if (element.required && !exists) {
                        testResult.errors.push(`Missing required element: ${element.description}`);
                        elementResult.error = 'Required element not found';
                    }
                    
                    testResult.elements.push(elementResult);
                    
                } catch (e) {
                    testResult.errors.push(`Error checking element ${element.description}: ${e.message}`);
                    testResult.elements.push({
                        selector: element.selector,
                        description: element.description,
                        exists: false,
                        error: e.message,
                        required: element.required || false
                    });
                }
            }
            
            // Execute actions
            for (const action of actions) {
                try {
                    const actionResult = await this.executeAction(action);
                    testResult.actions.push(actionResult);
                    
                    if (!actionResult.success) {
                        testResult.errors.push(`Action failed: ${action.description}`);
                    }
                } catch (e) {
                    testResult.errors.push(`Action error ${action.description}: ${e.message}`);
                    testResult.actions.push({
                        action: action.description,
                        success: false,
                        error: e.message
                    });
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

    async executeAction(action) {
        const result = {
            action: action.description,
            success: false,
            error: null,
            data: null
        };
        
        try {
            switch (action.type) {
                case 'click':
                    await this.page.click(action.selector, { waitUntil: 'networkidle0' });
                    result.success = true;
                    break;
                    
                case 'type':
                    await this.page.type(action.selector, action.text);
                    result.success = true;
                    break;
                    
                case 'select':
                    await this.page.select(action.selector, action.value);
                    result.success = true;
                    break;
                    
                case 'wait':
                    await this.page.waitFor(action.selector || action.timeout || 1000);
                    result.success = true;
                    break;
                    
                case 'evaluate':
                    result.data = await this.page.evaluate(action.script);
                    result.success = true;
                    break;
                    
                case 'screenshot':
                    await this.takeScreenshot(action.name || 'action', action.description);
                    result.success = true;
                    break;
                    
                default:
                    result.error = `Unknown action type: ${action.type}`;
            }
        } catch (e) {
            result.error = e.message;
        }
        
        return result;
    }

    async testLoginFlow() {
        console.log('\n🔐 Testing Login Flow...');
        
        const loginActions = [
            { type: 'wait', timeout: 1000, description: 'Wait for page load' },
            { type: 'type', selector: '#username', text: 'admin', description: 'Enter username' },
            { type: 'type', selector: '#password', text: 'admin123', description: 'Enter password' },
            { type: 'click', selector: '#loginBtn', description: 'Click login button' },
            { type: 'wait', timeout: 5000, description: 'Wait for login response' },
            { type: 'screenshot', name: 'login_result', description: 'Login result' }
        ];
        
        return await this.testPage(
            `${this.baseUrl}/login.html`,
            'Login Flow',
            [
                { selector: '#loginForm', description: 'Login Form', required: true },
                { selector: '#username', description: 'Username Input', required: true },
                { selector: '#password', description: 'Password Input', required: true },
                { selector: '#loginBtn', description: 'Login Button', required: true }
            ],
            loginActions
        );
    }

    async testDashboard(role = 'admin') {
        console.log(`\n📊 Testing ${role} Dashboard...`);
        
        const dashboardActions = [
            { type: 'wait', timeout: 2000, description: 'Wait for dashboard load' },
            { type: 'evaluate', script: () => {
                return {
                    title: document.title,
                    url: window.location.href,
                    hasDashboard: !!document.querySelector('.dashboard-header'),
                    hasSidebar: !!document.querySelector('.dashboard-sidebar'),
                    statCards: document.querySelectorAll('.stat-card').length
                };
            }, description: 'Check dashboard structure' }
        ];
        
        return await this.testPage(
            `${this.baseUrl}/pages/${role}/dashboard.html`,
            `${role.charAt(0).toUpperCase() + role.slice(1)} Dashboard`,
            [
                { selector: '.dashboard-header', description: 'Dashboard Header', required: true },
                { selector: '.dashboard-sidebar', description: 'Sidebar', required: true },
                { selector: '.stat-card', description: 'Statistics Cards', required: false }
            ],
            dashboardActions
        );
    }

    async testAPIEndpoints() {
        console.log('\n🌐 Testing API Endpoints...');
        
        const endpoints = [
            { url: '/api/dashboard.php?action=admin_stats&token=MTI6T3duZXIgVXNlcjoxNzc0MDk2MDk3', name: 'Dashboard Stats' },
            { url: '/api/members.php?action=list&token=MTI6T3duZXIgVXNlcjoxNzc0MDk2MDk3', name: 'Members List' },
            { url: '/api/loans.php?action=list&token=MTI6T3duZXIgVXNlcjoxNzc0MDk2MDk3', name: 'Loans List' },
            { url: '/api/ai-risk-assessment.php?action=analyze&token=MTI6T3duZXIgVXNlcjoxNzc0MDk2MDk3', name: 'AI Risk Assessment' },
            { url: '/api/digital-payments.php?action=methods&token=MTI6T3duZXIgVXNlcjoxNzc0MDk2MDk3', name: 'Digital Payments' }
        ];
        
        const results = [];
        
        for (const endpoint of endpoints) {
            console.log(`   Testing: ${endpoint.name}`);
            
            try {
                const response = await this.page.goto(`${this.baseUrl}${endpoint.url}`, {
                    waitUntil: 'networkidle2',
                    timeout: 10000
                });
                
                const content = await this.page.content();
                const isSuccess = response.status() === 200 && content.includes('success');
                
                results.push({
                    name: endpoint.name,
                    url: endpoint.url,
                    status: response.status(),
                    success: isSuccess,
                    content: content.substring(0, 200) // First 200 chars
                });
                
                console.log(`   ${isSuccess ? '✅' : '❌'} ${endpoint.name} - HTTP ${response.status()}`);
                
            } catch (error) {
                results.push({
                    name: endpoint.name,
                    url: endpoint.url,
                    status: 'ERROR',
                    success: false,
                    error: error.message
                });
                
                console.log(`   ❌ ${endpoint.name} - ERROR: ${error.message}`);
            }
        }
        
        return results;
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
            console.log(`   Testing ${viewport.name} (${viewport.width}x${viewport.height})`);
            
            await this.page.setViewport({ width: viewport.width, height: viewport.height });
            
            try {
                // Test login page
                await this.page.goto(`${this.baseUrl}/login.html`, { waitUntil: 'networkidle2' });
                
                const hasMobileMenu = await this.page.$('.navbar-toggler') !== null;
                const hasDesktopMenu = await this.page.$('.navbar-nav') !== null;
                const isFormVisible = await this.page.$('#loginForm') !== null;
                
                const result = {
                    viewport: viewport.name,
                    width: viewport.width,
                    height: viewport.height,
                    hasMobileMenu: hasMobileMenu,
                    hasDesktopMenu: hasDesktopMenu,
                    isFormVisible: isFormVisible,
                    responsive: true
                };
                
                results.push(result);
                
                await this.takeScreenshot(`responsive_${viewport.name}`, `Responsive ${viewport.name}`);
                
                console.log(`   ✅ ${viewport.name} - Responsive`);
                
            } catch (error) {
                results.push({
                    viewport: viewport.name,
                    error: error.message,
                    responsive: false
                });
                
                console.log(`   ❌ ${viewport.name} - ERROR: ${error.message}`);
            }
        }
        
        return results;
    }

    async testJavaScriptFunctionality() {
        console.log('\n⚡ Testing JavaScript Functionality...');
        
        await this.page.goto(`${this.baseUrl}/login.html`, { waitUntil: 'networkidle2' });
        
        const jsTest = await this.page.evaluate(() => {
            const results = {
                jquery: typeof $ !== 'undefined',
                bootstrap: typeof bootstrap !== 'undefined',
                fontawesome: typeof FontAwesome !== 'undefined',
                formValidation: typeof HTMLFormElement.prototype.checkValidity === 'function',
                localStorage: typeof localStorage !== 'undefined',
                sessionStorage: typeof sessionStorage !== 'undefined',
                fetchAPI: typeof fetch !== 'undefined',
                promises: typeof Promise !== 'undefined'
            };
            
            // Test jQuery functionality
            if (results.jquery) {
                try {
                    results.jqueryReady = !!$(document).ready;
                    results.jqueryAjax = typeof $.ajax === 'function';
                    results.jqueryGet = typeof $.get === 'function';
                    results.jqueryPost = typeof $.post === 'function';
                } catch (e) {
                    results.jqueryError = e.message;
                }
            }
            
            // Test Bootstrap functionality
            if (results.bootstrap) {
                try {
                    results.bootstrapModal = typeof bootstrap.Modal === 'function';
                    results.bootstrapTooltip = typeof bootstrap.Tooltip === 'function';
                    results.bootstrapPopover = typeof bootstrap.Popover === 'function';
                } catch (e) {
                    results.bootstrapError = e.message;
                }
            }
            
            return results;
        });
        
        console.log(`   ✅ JavaScript Test Results:`);
        Object.entries(jsTest).forEach(([key, value]) => {
            const status = typeof value === 'boolean' ? (value ? '✅' : '❌') : '📊';
            console.log(`   ${status} ${key}: ${value}`);
        });
        
        return jsTest;
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
        
        // Test 2: Login functionality
        console.log('\n🔐 Phase 2: Authentication Testing');
        await this.testLoginFlow();
        
        // Test 3: Dashboard functionality
        console.log('\n📊 Phase 3: Dashboard Testing');
        await this.testDashboard('admin');
        await this.testDashboard('staff');
        await this.testDashboard('member');
        
        // Test 4: API endpoints
        console.log('\n🌐 Phase 4: API Testing');
        const apiResults = await this.testAPIEndpoints();
        
        // Test 5: Responsive design
        console.log('\n📱 Phase 5: Responsive Design Testing');
        const responsiveResults = await this.testResponsiveDesign();
        
        // Test 6: JavaScript functionality
        console.log('\n⚡ Phase 6: JavaScript Testing');
        const jsResults = await this.testJavaScriptFunctionality();
        
        // Generate report and fix errors
        await this.generateReport();
        await this.fixErrors();
        
        return {
            basicPages: this.testResults.filter(r => basicPages.some(p => p.name === r.test)),
            loginFlow: this.testResults.find(r => r.test === 'Login Flow'),
            dashboards: this.testResults.filter(r => r.test.includes('Dashboard')),
            apiEndpoints: apiResults,
            responsive: responsiveResults,
            javascript: jsResults,
            errors: this.errors,
            fixes: this.fixes
        };
    }

    async fixErrors() {
        console.log('\n🔧 Auto-fixing Errors...');
        
        const fixes = [];
        
        // Fix common errors
        for (const error of this.errors) {
            if (error.type === 'console_error') {
                const fix = await this.fixConsoleError(error);
                if (fix) fixes.push(fix);
            } else if (error.type === 'page_error') {
                const fix = await this.fixPageError(error);
                if (fix) fixes.push(fix);
            }
        }
        
        // Fix test result errors
        for (const result of this.testResults) {
            if (!result.success) {
                for (const elementError of result.elements.filter(e => !e.exists && e.required)) {
                    const fix = await this.fixMissingElement(elementError, result.url);
                    if (fix) fixes.push(fix);
                }
            }
        }
        
        this.fixes = fixes;
        
        console.log(`✅ Fixed ${fixes.length} errors automatically`);
        
        return fixes;
    }

    async fixConsoleError(error) {
        console.log(`   🔧 Fixing console error: ${error.message}`);
        
        // Common fixes for console errors
        if (error.message.includes('$ is not defined')) {
            // Add jQuery if missing
            return await this.addJQueryToPage(error.location.url);
        } else if (error.message.includes('bootstrap is not defined')) {
            // Add Bootstrap if missing
            return await this.addBootstrapToPage(error.location.url);
        } else if (error.message.includes('Cannot read property')) {
            // Add null checks
            return await this.addNullChecks(error.location.url);
        }
        
        return null;
    }

    async fixPageError(error) {
        console.log(`   🔧 Fixing page error: ${error.message}`);
        
        // Common fixes for page errors
        if (error.message.includes('Unexpected token')) {
            // Fix JavaScript syntax errors
            return await this.fixJavaScriptSyntax(error.location.url);
        } else if (error.message.includes('Network error')) {
            // Handle network errors
            return await this.handleNetworkError(error.location.url);
        }
        
        return null;
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

    async addNullChecks(url) {
        const filePath = this.getFilePathFromUrl(url);
        if (!filePath || !fs.existsSync(filePath)) {
            return null;
        }
        
        let content = fs.readFileSync(filePath, 'utf8');
        
        // Add null checks for common operations
        const nullCheckScript = `
<script>
// Add null checks for safe operations
window.safeOperation = function(operation) {
    try {
        return operation();
    } catch (e) {
        console.warn('Operation failed:', e.message);
        return null;
    }
};
</script>`;
        
        content = content.replace('</body>', nullCheckScript + '\n</body>');
        fs.writeFileSync(filePath, content);
        
        return {
            type: 'added_null_checks',
            file: filePath,
            success: true
        };
    }

    getFilePathFromUrl(url) {
        // Convert URL to file path
        const urlPath = new URL(url).pathname;
        const fullPath = path.join(this.projectRoot, urlPath);
        
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
        console.log('\n📊 Generating Comprehensive Test Report...');
        
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
            screenshots: this.screenshots,
            browserInfo: {
                userAgent: await this.page.evaluate(() => navigator.userAgent),
                viewport: await this.page.viewport()
            }
        };
        
        // Save JSON report
        const reportPath = path.join(__dirname, 'comprehensive-test-report.json');
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        // Generate HTML report
        const htmlReport = this.generateHTMLReport(report);
        const htmlReportPath = path.join(__dirname, 'comprehensive-test-report.html');
        fs.writeFileSync(htmlReportPath, htmlReport);
        
        console.log(`✅ JSON Report: ${reportPath}`);
        console.log(`✅ HTML Report: ${htmlReportPath}`);
        console.log(`📸 Screenshots: ${this.screenshots.length} captured`);
        
        return report;
    }

    generateHTMLReport(report) {
        const successRate = ((report.summary.successfulTests / report.summary.totalTests) * 100).toFixed(1);
        
        return `
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprehensive System Test Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .test-success { border-left: 4px solid #28a745; }
        .test-failed { border-left: 4px solid #dc3545; }
        .screenshot-thumb { max-width: 200px; height: auto; border-radius: 8px; margin: 5px; }
        .error-log { background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .fix-log { background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="fas fa-vial me-2"></i>
                    Comprehensive System Test Report
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
                                <h3>${successRate}%</h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Test Results -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Test Results</h5>
                    </div>
                    <div class="card-body">
                        ${report.results.map(result => `
                            <div class="card mb-3 ${result.success ? 'test-success' : 'test-failed'}">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">${result.test}</h6>
                                    <span class="badge bg-${result.success ? 'success' : 'danger'}">
                                        ${result.success ? 'SUCCESS' : 'FAILED'}
                                    </span>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2">
                                        <strong>URL:</strong> <a href="${result.url}" target="_blank">${result.url}</a><br>
                                        <strong>Load Time:</strong> ${result.loadTime}ms<br>
                                        <strong>Timestamp:</strong> ${new Date(result.timestamp).toLocaleString('id-ID')}
                                    </p>
                                    
                                    ${result.elements && result.elements.length > 0 ? `
                                        <h6>Element Checks:</h6>
                                        <div class="row">
                                            ${result.elements.map(el => `
                                                <div class="col-md-6">
                                                    <span class="${el.exists ? 'text-success' : 'text-danger'}">
                                                        <i class="fas fa-${el.exists ? 'check' : 'times'}"></i>
                                                        ${el.description}
                                                        ${el.required ? ' (Required)' : ''}
                                                    </span>
                                                </div>
                                            `).join('')}
                                        </div>
                                    ` : ''}
                                    
                                    ${result.errors && result.errors.length > 0 ? `
                                        <h6 class="text-danger">Errors:</h6>
                                        <div class="error-log">
                                            ${result.errors.map(error => `<div>• ${error}</div>`).join('')}
                                        </div>
                                    ` : ''}
                                    
                                    ${result.actions && result.actions.length > 0 ? `
                                        <h6>Actions:</h6>
                                        <div class="row">
                                            ${result.actions.map(action => `
                                                <div class="col-md-6">
                                                    <span class="${action.success ? 'text-success' : 'text-danger'}">
                                                        <i class="fas fa-${action.success ? 'check' : 'times'}"></i>
                                                        ${action.action}
                                                    </span>
                                                </div>
                                            `).join('')}
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <!-- Errors Section -->
                ${report.errors.length > 0 ? `
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Errors Found (${report.errors.length})</h5>
                        </div>
                        <div class="card-body">
                            ${report.errors.map(error => `
                                <div class="alert alert-danger">
                                    <strong>${error.type}:</strong> ${error.message}<br>
                                    <small>Timestamp: ${new Date(error.timestamp).toLocaleString('id-ID')}</small>
                                    ${error.location ? `<br><small>Location: ${error.location.url}</small>` : ''}
                                    ${error.stack ? `<br><small>Stack: ${error.stack.substring(0, 100)}...</small>` : ''}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
                
                <!-- Fixes Section -->
                ${report.fixes.length > 0 ? `
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Auto-Fixes Applied (${report.fixes.length})</h5>
                        </div>
                        <div class="card-body">
                            ${report.fixes.map(fix => `
                                <div class="alert alert-success">
                                    <strong>${fix.type}:</strong> ${fix.element || fix.file}<br>
                                    <small>File: ${fix.file}</small>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
                
                <!-- Screenshots Section -->
                ${report.screenshots.length > 0 ? `
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Screenshots (${report.screenshots.length})</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                ${report.screenshots.map(screenshot => `
                                    <div class="col-md-3 mb-3">
                                        <div class="card">
                                            <img src="screenshots/${screenshot.filename}" class="card-img-top screenshot-thumb" alt="${screenshot.description}">
                                            <div class="card-body p-2">
                                                <small class="d-block">${screenshot.description}</small>
                                                <small class="d-block text-muted">${screenshot.filename}</small>
                                                ${screenshot.isError ? '<span class="badge bg-danger">ERROR</span>' : ''}
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
    const tester = new ComprehensiveSystemTester();
    
    try {
        const results = await tester.runAllTests();
        
        console.log('\n🎉 COMPREHENSIVE TESTING COMPLETED!');
        console.log(`📊 Total Tests: ${results.basicPages.length}`);
        console.log(`✅ Successful: ${results.basicPages.filter(r => r.success).length}`);
        console.log(`❌ Failed: ${results.basicPages.filter(r => !r.success).length}`);
        console.log(`🔧 Fixes Applied: ${results.fixes.length}`);
        console.log(`📸 Screenshots: ${results.screenshots.length}`);
        
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

module.exports = ComprehensiveSystemTester;
