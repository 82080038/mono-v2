#!/usr/bin/env node

/**
 * Advanced System Testing with Error Detection and Fixes
 * Comprehensive testing with JavaScript validation
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class AdvancedSystemTester {
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
        console.log('🚀 Initializing Advanced System Testing...');
        
        this.browser = await puppeteer.launch({
            headless: false,
            defaultViewport: { width: 1366, height: 768 },
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        this.page = await this.browser.newPage();
        
        // Capture all console messages
        this.page.on('console', msg => {
            this.errors.push({
                type: 'console',
                level: msg.type(),
                message: msg.text(),
                location: msg.location(),
                timestamp: new Date().toISOString()
            });
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
        
        // Capture request failures
        this.page.on('requestfailed', request => {
            this.errors.push({
                type: 'request_failed',
                url: request.url(),
                failure: request.failure(),
                timestamp: new Date().toISOString()
            });
        });
        
        console.log('✅ Browser initialized with advanced monitoring');
    }

    async takeScreenshot(name, description) {
        try {
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            const filename = `${name}_${timestamp}.png`;
            const filepath = path.join(__dirname, 'screenshots', filename);
            
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
                timestamp: new Date().toISOString()
            });
            
            console.log(`📸 Screenshot: ${filename}`);
        } catch (error) {
            console.log(`❌ Screenshot failed: ${error.message}`);
        }
    }

    async testPageComprehensive(url, testName) {
        console.log(`\n🧪 Comprehensive Test: ${testName}`);
        console.log(`📍 URL: ${url}`);
        
        const startTime = Date.now();
        let testResult = {
            test: testName,
            url: url,
            success: false,
            loadTime: 0,
            issues: [],
            elements: [],
            javascript: {},
            performance: {},
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
                testResult.issues.push(`HTTP Status: ${status}`);
            }
            
            // Check page structure
            const pageStructure = await this.page.evaluate(() => {
                return {
                    hasTitle: !!document.title && document.title.trim() !== '',
                    hasBody: !!document.body,
                    hasHead: !!document.head,
                    hasMeta: !!document.querySelector('meta charset'),
                    title: document.title,
                    bodyClasses: document.body.className,
                    lang: document.documentElement.lang
                };
            });
            
            testResult.elements = pageStructure;
            
            if (!pageStructure.hasTitle) {
                testResult.issues.push('Missing or empty page title');
            }
            
            if (!pageStructure.hasMeta) {
                testResult.issues.push('Missing charset meta tag');
            }
            
            // Check JavaScript libraries
            const jsLibraries = await this.page.evaluate(() => {
                return {
                    jquery: typeof $ !== 'undefined',
                    jqueryVersion: typeof $ !== 'undefined' ? $.fn.jquery : null,
                    bootstrap: typeof bootstrap !== 'undefined',
                    bootstrapVersion: typeof bootstrap !== 'undefined' ? '5.x' : null,
                    fontawesome: typeof FontAwesome !== 'undefined' || !!document.querySelector('[class*="fa-"]'),
                    customScripts: Array.from(document.querySelectorAll('script[src]')).map(s => s.src).filter(s => !s.includes('cdn'))
                };
            });
            
            testResult.javascript = jsLibraries;
            
            // Check for missing libraries
            if (!jsLibraries.jquery) {
                testResult.issues.push('jQuery not loaded');
            }
            if (!jsLibraries.bootstrap) {
                testResult.issues.push('Bootstrap not loaded');
            }
            if (!jsLibraries.fontawesome) {
                testResult.issues.push('Font Awesome not loaded');
            }
            
            // Check for common elements
            const commonElements = await this.page.evaluate(() => {
                return {
                    hasNavigation: !!document.querySelector('nav') || !!document.querySelector('.navbar'),
                    hasHeader: !!document.querySelector('header') || !!document.querySelector('.header'),
                    hasMain: !!document.querySelector('main') || !!document.querySelector('.main'),
                    hasFooter: !!document.querySelector('footer') || !!document.querySelector('.footer'),
                    hasForms: document.querySelectorAll('form').length,
                    hasButtons: document.querySelectorAll('button, .btn').length,
                    hasCards: document.querySelectorAll('.card').length,
                    hasModals: document.querySelectorAll('.modal').length,
                    hasTables: document.querySelectorAll('table').length,
                    hasAlerts: document.querySelectorAll('.alert').length
                };
            });
            
            testResult.elements = { ...testResult.elements, ...commonElements };
            
            // Check performance metrics
            const performance = await this.page.evaluate(() => {
                const navigation = performance.getEntriesByType('navigation')[0];
                return {
                    domContentLoaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
                    loadComplete: navigation.loadEventEnd - navigation.loadEventStart,
                    totalLoadTime: navigation.loadEventEnd - navigation.fetchStart,
                    resourceCount: performance.getEntriesByType('resource').length
                };
            });
            
            testResult.performance = performance;
            
            // Check for specific dashboard elements
            if (testName.includes('Dashboard')) {
                const dashboardElements = await this.page.evaluate(() => {
                    return {
                        hasDashboardHeader: !!document.querySelector('.dashboard-header'),
                        hasDashboardSidebar: !!document.querySelector('.dashboard-sidebar'),
                        hasStatCards: document.querySelectorAll('.stat-card').length,
                        hasProgressBars: document.querySelectorAll('.progress').length,
                        hasCharts: document.querySelectorAll('canvas, .chart').length,
                        hasTables: document.querySelectorAll('.table').length
                    };
                });
                
                testResult.elements = { ...testResult.elements, ...dashboardElements };
                
                if (!dashboardElements.hasDashboardHeader) {
                    testResult.issues.push('Missing dashboard header');
                }
                if (!dashboardElements.hasDashboardSidebar) {
                    testResult.issues.push('Missing dashboard sidebar');
                }
            }
            
            // Check for login form elements
            if (testName.includes('Login')) {
                const loginElements = await this.page.evaluate(() => {
                    return {
                        hasLoginForm: !!document.querySelector('#loginForm, .login-form'),
                        hasUsernameInput: !!document.querySelector('#username, #email, [name="username"], [name="email"]'),
                        hasPasswordInput: !!document.querySelector('#password, [name="password"]'),
                        hasLoginButton: !!document.querySelector('#loginBtn, .login-btn, button[type="submit"]'),
                        hasValidation: !!document.querySelector('[required], [data-required]')
                    };
                });
                
                testResult.elements = { ...testResult.elements, ...loginElements };
                
                if (!loginElements.hasLoginForm) {
                    testResult.issues.push('Missing login form');
                }
                if (!loginElements.hasUsernameInput) {
                    testResult.issues.push('Missing username input');
                }
                if (!loginElements.hasPasswordInput) {
                    testResult.issues.push('Missing password input');
                }
                if (!loginElements.hasLoginButton) {
                    testResult.issues.push('Missing login button');
                }
            }
            
            // Determine success
            testResult.success = testResult.issues.length === 0;
            
            // Take screenshot
            await this.takeScreenshot(testName.replace(/\s+/g, '_'), `${testName} - ${testResult.success ? 'SUCCESS' : 'HAS_ISSUES'}`);
            
            this.testResults.push(testResult);
            
            console.log(`${testResult.success ? '✅' : '⚠️'} ${testName} - ${loadTime}ms`);
            if (testResult.issues.length > 0) {
                console.log(`   Issues: ${testResult.issues.join(', ')}`);
            }
            
            return testResult;
            
        } catch (error) {
            const loadTime = Date.now() - startTime;
            
            testResult.loadTime = loadTime;
            testResult.issues.push(`Page load error: ${error.message}`);
            
            this.testResults.push(testResult);
            
            await this.takeScreenshot(testName.replace(/\s+/g, '_'), `${testName} - ERROR`);
            
            console.log(`❌ ${testName} - ERROR: ${error.message}`);
            return testResult;
        }
    }

    async runComprehensiveTests() {
        console.log('🎯 Starting Comprehensive System Testing...\n');
        
        // Test all pages
        const pages = [
            { url: '/login.html', name: 'Login Page' },
            { url: '/index.html', name: 'Home Page' },
            { url: '/pages/admin/dashboard.html', name: 'Admin Dashboard' },
            { url: '/pages/staff/dashboard.html', name: 'Staff Dashboard' },
            { url: '/pages/member/dashboard.html', name: 'Member Dashboard' },
            { url: '/pages/admin/members.html', name: 'Members Page' },
            { url: '/pages/admin/loans.html', name: 'Loans Page' },
            { url: '/pages/admin/reports.html', name: 'Reports Page' }
        ];
        
        for (const page of pages) {
            await this.testPageComprehensive(`${this.baseUrl}${page.url}`, page.name);
        }
        
        // Analyze errors and fix them
        await this.analyzeAndFixErrors();
        
        // Generate comprehensive report
        await this.generateComprehensiveReport();
        
        return this.testResults;
    }

    async analyzeAndFixErrors() {
        console.log('\n🔧 Analyzing Errors and Applying Fixes...');
        
        const fixes = [];
        
        // Group errors by type
        const errorGroups = this.errors.reduce((groups, error) => {
            const key = error.type;
            if (!groups[key]) groups[key] = [];
            groups[key].push(error);
            return groups;
        }, {});
        
        // Fix JavaScript errors
        if (errorGroups.console || errorGroups.page_error) {
            console.log('   🔧 Fixing JavaScript errors...');
            
            for (const result of this.testResults) {
                if (result.issues.includes('jQuery not loaded')) {
                    const fix = await this.addJQueryToPage(result.url);
                    if (fix) fixes.push(fix);
                }
                
                if (result.issues.includes('Bootstrap not loaded')) {
                    const fix = await this.addBootstrapToPage(result.url);
                    if (fix) fixes.push(fix);
                }
                
                if (result.issues.includes('Font Awesome not loaded')) {
                    const fix = await this.addFontAwesomeToPage(result.url);
                    if (fix) fixes.push(fix);
                }
            }
        }
        
        // Fix missing elements
        console.log('   🔧 Fixing missing elements...');
        
        for (const result of this.testResults) {
            if (result.issues.includes('Missing dashboard header')) {
                const fix = await this.addDashboardHeader(result.url);
                if (fix) fixes.push(fix);
            }
            
            if (result.issues.includes('Missing dashboard sidebar')) {
                const fix = await this.addDashboardSidebar(result.url);
                if (fix) fixes.push(fix);
            }
            
            if (result.issues.includes('Missing charset meta tag')) {
                const fix = await this.addMetaCharset(result.url);
                if (fix) fixes.push(fix);
            }
        }
        
        this.fixes = fixes;
        
        console.log(`✅ Applied ${fixes.length} fixes`);
        
        return fixes;
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

    async addFontAwesomeToPage(url) {
        const filePath = this.getFilePathFromUrl(url);
        if (!filePath || !fs.existsSync(filePath)) {
            return null;
        }
        
        let content = fs.readFileSync(filePath, 'utf8');
        
        if (!content.includes('font-awesome')) {
            const fontAwesomeCSS = '<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">';
            content = content.replace('</head>', fontAwesomeCSS + '\n    </head>');
            fs.writeFileSync(filePath, content);
            
            return {
                type: 'added_fontawesome',
                file: filePath,
                success: true
            };
        }
        
        return null;
    }

    async addDashboardHeader(url) {
        const filePath = this.getFilePathFromUrl(url);
        if (!filePath || !fs.existsSync(filePath)) {
            return null;
        }
        
        let content = fs.readFileSync(filePath, 'utf8');
        
        if (!content.includes('dashboard-header')) {
            const headerHTML = `
<div class="dashboard-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3 mb-0">Dashboard</h1>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group">
                    <button class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> New
                    </button>
                    <button class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>`;
            
            content = content.replace('<div class="dashboard-content">', headerHTML + '\n        <div class="dashboard-content">');
            fs.writeFileSync(filePath, content);
            
            return {
                type: 'added_dashboard_header',
                file: filePath,
                success: true
            };
        }
        
        return null;
    }

    async addDashboardSidebar(url) {
        const filePath = this.getFilePathFromUrl(url);
        if (!filePath || !fs.existsSync(filePath)) {
            return null;
        }
        
        let content = fs.readFileSync(filePath, 'utf8');
        
        if (!content.includes('dashboard-sidebar')) {
            const sidebarHTML = `
<div class="dashboard-sidebar">
    <div class="sidebar-content">
        <div class="sidebar-header">
            <h5>KSP Lam Gabe Jaya</h5>
        </div>
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.html">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="members.html">
                        <i class="fas fa-users me-2"></i> Anggota
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="loans.html">
                        <i class="fas fa-hand-holding-usd me-2"></i> Pinjaman
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="savings.html">
                        <i class="fas fa-piggy-bank me-2"></i> Simpanan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.html">
                        <i class="fas fa-chart-bar me-2"></i> Laporan
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>`;
            
            content = content.replace('<div class="dashboard-content">', sidebarHTML + '\n        <div class="dashboard-content">');
            fs.writeFileSync(filePath, content);
            
            return {
                type: 'added_dashboard_sidebar',
                file: filePath,
                success: true
            };
        }
        
        return null;
    }

    async addMetaCharset(url) {
        const filePath = this.getFilePathFromUrl(url);
        if (!filePath || !fs.existsSync(filePath)) {
            return null;
        }
        
        let content = fs.readFileSync(filePath, 'utf8');
        
        if (!content.includes('charset')) {
            const metaCharset = '<meta charset="UTF-8">';
            content = content.replace('<head>', `<head>\n    ${metaCharset}`);
            fs.writeFileSync(filePath, content);
            
            return {
                type: 'added_meta_charset',
                file: filePath,
                success: true
            };
        }
        
        return null;
    }

    getFilePathFromUrl(url) {
        const urlPath = new URL(url).pathname;
        const fullPath = path.join(__dirname, urlPath);
        
        if (fs.existsSync(fullPath) && fs.statSync(fullPath).isDirectory()) {
            const indexPath = path.join(fullPath, 'index.html');
            return fs.existsSync(indexPath) ? indexPath : path.join(fullPath, 'login.html');
        }
        
        if (!fs.existsSync(fullPath) && !fullPath.endsWith('.html')) {
            return fullPath + '.html';
        }
        
        return fullPath;
    }

    async generateComprehensiveReport() {
        console.log('\n📊 Generating Comprehensive Report...');
        
        const report = {
            summary: {
                totalTests: this.testResults.length,
                successfulTests: this.testResults.filter(r => r.success).length,
                testsWithIssues: this.testResults.filter(r => !r.success).length,
                totalErrors: this.errors.length,
                totalFixes: this.fixes.length,
                totalScreenshots: this.screenshots.length,
                timestamp: new Date().toISOString()
            },
            results: this.testResults,
            errors: this.errors,
            fixes: this.fixes,
            screenshots: this.screenshots,
            recommendations: this.generateRecommendations()
        };
        
        // Save JSON report
        const reportPath = path.join(__dirname, 'advanced-test-report.json');
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        console.log(`✅ Report saved: ${reportPath}`);
        console.log(`📊 Total Tests: ${report.summary.totalTests}`);
        console.log(`✅ Successful: ${report.summary.successfulTests}`);
        console.log(`⚠️ With Issues: ${report.summary.testsWithIssues}`);
        console.log(`🔧 Fixes Applied: ${report.summary.totalFixes}`);
        console.log(`📸 Screenshots: ${report.summary.totalScreenshots}`);
        
        return report;
    }

    generateRecommendations() {
        const recommendations = [];
        
        // Analyze common issues
        const commonIssues = this.testResults.flatMap(r => r.issues);
        const issueCounts = commonIssues.reduce((counts, issue) => {
            counts[issue] = (counts[issue] || 0) + 1;
            return counts;
        }, {});
        
        // Generate recommendations based on issues
        if (issueCounts['jQuery not loaded'] > 0) {
            recommendations.push({
                issue: 'jQuery not loaded',
                count: issueCounts['jQuery not loaded'],
                recommendation: 'Add jQuery to all pages for consistent functionality',
                priority: 'high'
            });
        }
        
        if (issueCounts['Bootstrap not loaded'] > 0) {
            recommendations.push({
                issue: 'Bootstrap not loaded',
                count: issueCounts['Bootstrap not loaded'],
                recommendation: 'Add Bootstrap CSS and JS to all pages',
                priority: 'high'
            });
        }
        
        if (issueCounts['Missing dashboard header'] > 0) {
            recommendations.push({
                issue: 'Missing dashboard header',
                count: issueCounts['Missing dashboard header'],
                recommendation: 'Add consistent dashboard header to all dashboard pages',
                priority: 'medium'
            });
        }
        
        if (issueCounts['Missing dashboard sidebar'] > 0) {
            recommendations.push({
                issue: 'Missing dashboard sidebar',
                count: issueCounts['Missing dashboard sidebar'],
                recommendation: 'Add navigation sidebar to all dashboard pages',
                priority: 'medium'
            });
        }
        
        return recommendations;
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
    const tester = new AdvancedSystemTester();
    
    try {
        await tester.init();
        const results = await tester.runComprehensiveTests();
        
        console.log('\n🎉 COMPREHENSIVE TESTING COMPLETED!');
        
        // Print summary
        const successful = results.filter(r => r.success).length;
        const withIssues = results.filter(r => !r.success).length;
        
        console.log(`📊 Summary:`);
        console.log(`   Total Tests: ${results.length}`);
        console.log(`   ✅ Successful: ${successful}`);
        console.log(`   ⚠️ With Issues: ${withIssues}`);
        console.log(`   🔧 Fixes Applied: ${tester.fixes.length}`);
        console.log(`   📸 Screenshots: ${tester.screenshots.length}`);
        
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

module.exports = AdvancedSystemTester;
