#!/usr/bin/env node

/**
 * Comprehensive Role & PWA Testing Suite
 * Test all role-based features and PWA functionality
 * Ensure PWA doesn't interfere with development
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class RolePWATester {
    constructor() {
        this.baseUrl = 'http://localhost/mono-v2';
        this.testResults = [];
        this.roleTests = [];
        this.pwaTests = [];
        this.errors = [];
        this.fixes = [];
        this.browser = null;
        this.page = null;
    }

    async init() {
        console.log('🚀 Initializing Role & PWA Testing Suite...');
        
        this.browser = await puppeteer.launch({
            headless: false,
            defaultViewport: { width: 1366, height: 768 },
            args: [
                '--no-sandbox', 
                '--disable-setuid-sandbox',
                '--disable-web-security',
                '--disable-features=VizDisplayCompositor'
            ]
        });
        
        this.page = await this.browser.newPage();
        
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
        
        console.log('✅ Browser initialized for Role & PWA testing');
    }

    async takeScreenshot(name, description, isError = false) {
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
            
            console.log(`📸 Screenshot: ${filename} ${isError ? '(ERROR)' : ''}`);
        } catch (error) {
            console.log(`❌ Screenshot failed: ${error.message}`);
        }
    }

    async testRoleFeatures(role, credentials) {
        console.log(`\n👤 Testing ${role.toUpperCase()} Role Features...`);
        
        const roleTest = {
            role: role,
            credentials: credentials,
            loginTest: null,
            dashboardTest: null,
            permissionsTest: null,
            navigationTest: null,
            featuresTest: null,
            timestamp: new Date().toISOString()
        };
        
        try {
            // Test 1: Login with role credentials
            const loginResult = await this.testRoleLogin(role, credentials);
            roleTest.loginTest = loginResult;
            
            if (loginResult.success) {
                // Test 2: Dashboard access and features
                const dashboardResult = await this.testRoleDashboard(role);
                roleTest.dashboardTest = dashboardResult;
                
                // Test 3: Role-specific permissions
                const permissionsResult = await this.testRolePermissions(role);
                roleTest.permissionsTest = permissionsResult;
                
                // Test 4: Navigation and menu items
                const navigationResult = await this.testRoleNavigation(role);
                roleTest.navigationTest = navigationResult;
                
                // Test 5: Role-specific features
                const featuresResult = await this.testRoleSpecificFeatures(role);
                roleTest.featuresTest = featuresResult;
            }
            
            this.roleTests.push(roleTest);
            
            console.log(`${loginResult.success ? '✅' : '❌'} ${role.toUpperCase()} Role Test Completed`);
            
            return roleTest;
            
        } catch (error) {
            console.log(`❌ ${role.toUpperCase()} Role Test Error: ${error.message}`);
            roleTest.error = error.message;
            this.roleTests.push(roleTest);
            return roleTest;
        }
    }

    async testRoleLogin(role, credentials) {
        console.log(`   🔐 Testing ${role} login...`);
        
        const loginUrl = role === 'admin' ? '/pages/admin/login.html' : 
                        role === 'staff' ? '/pages/staff/login.html' : 
                        '/pages/member/login.html';
        
        try {
            // Navigate to login page
            await this.page.goto(`${this.baseUrl}${loginUrl}`, { waitUntil: 'networkidle2' });
            
            // Check if login form exists
            const hasLoginForm = await this.page.$('#loginForm, .login-form, form') !== null;
            const hasUsernameInput = await this.page.$('#username, #email, [name="username"], [name="email"]') !== null;
            const hasPasswordInput = await this.page.$('#password, [name="password"]') !== null;
            const hasLoginButton = await this.page.$('#loginBtn, .login-btn, button[type="submit"]') !== null;
            
            if (!hasLoginForm || !hasUsernameInput || !hasPasswordInput || !hasLoginButton) {
                return {
                    success: false,
                    issues: ['Missing login form elements'],
                    elements: { hasLoginForm, hasUsernameInput, hasPasswordInput, hasLoginButton }
                };
            }
            
            // Fill login form
            await this.page.type('#username, #email, [name="username"], [name="email"]', credentials.username);
            await this.page.type('#password, [name="password"]', credentials.password);
            
            // Click login button
            await this.page.click('#loginBtn, .login-btn, button[type="submit"]');
            
            // Wait for navigation or response
            await this.page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 10000 }).catch(() => {});
            
            // Check if login successful (redirected to dashboard)
            const currentUrl = this.page.url();
            const isLoggedIn = currentUrl.includes('dashboard') || currentUrl.includes('admin') || currentUrl.includes('staff') || currentUrl.includes('member');
            
            await this.takeScreenshot(`${role}_login_result`, `${role} Login Result`, !isLoggedIn);
            
            return {
                success: isLoggedIn,
                currentUrl: currentUrl,
                elements: { hasLoginForm, hasUsernameInput, hasPasswordInput, hasLoginButton }
            };
            
        } catch (error) {
            return {
                success: false,
                error: error.message
            };
        }
    }

    async testRoleDashboard(role) {
        console.log(`   📊 Testing ${role} dashboard...`);
        
        try {
            const dashboardUrl = `/pages/${role}/dashboard.html`;
            await this.page.goto(`${this.baseUrl}${dashboardUrl}`, { waitUntil: 'networkidle2' });
            
            // Check dashboard components
            const dashboardAnalysis = await this.page.evaluate(() => {
                return {
                    hasHeader: !!document.querySelector('.dashboard-header, header'),
                    hasSidebar: !!document.querySelector('.dashboard-sidebar, aside'),
                    hasStatCards: document.querySelectorAll('.stat-card, .card').length,
                    hasProgressBars: document.querySelectorAll('.progress').length,
                    hasCharts: document.querySelectorAll('canvas, .chart').length,
                    hasTables: document.querySelectorAll('table').length,
                    roleSpecificElements: {
                        admin: document.querySelectorAll('[data-role="admin"], .admin-only').length,
                        staff: document.querySelectorAll('[data-role="staff"], .staff-only').length,
                        member: document.querySelectorAll('[data-role="member"], .member-only').length
                    }
                };
            });
            
            await this.takeScreenshot(`${role}_dashboard`, `${role} Dashboard`);
            
            return {
                success: true,
                components: dashboardAnalysis
            };
            
        } catch (error) {
            return {
                success: false,
                error: error.message
            };
        }
    }

    async testRolePermissions(role) {
        console.log(`   🔒 Testing ${role} permissions...`);
        
        try {
            // Test access to role-specific pages
            const rolePages = this.getRolePages(role);
            const permissionTests = [];
            
            for (const page of rolePages) {
                try {
                    await this.page.goto(`${this.baseUrl}${page.url}`, { waitUntil: 'networkidle2', timeout: 5000 });
                    
                    const hasAccess = this.page.url().includes('login') === false;
                    const statusCode = await this.page.evaluate(() => document.body.innerText.includes('403') || document.body.innerText.includes('Access Denied') ? 403 : 200);
                    
                    permissionTests.push({
                        page: page.name,
                        url: page.url,
                        hasAccess: hasAccess,
                        statusCode: statusCode,
                        expectedAccess: page.expectedAccess
                    });
                    
                } catch (error) {
                    permissionTests.push({
                        page: page.name,
                        url: page.url,
                        hasAccess: false,
                        error: error.message,
                        expectedAccess: page.expectedAccess
                    });
                }
            }
            
            return {
                success: true,
                permissions: permissionTests
            };
            
        } catch (error) {
            return {
                success: false,
                error: error.message
            };
        }
    }

    async testRoleNavigation(role) {
        console.log(`   🧭 Testing ${role} navigation...`);
        
        try {
            const navigationAnalysis = await this.page.evaluate(() => {
                const navItems = document.querySelectorAll('.nav-link, .menu-item, a[href]');
                const navigation = [];
                
                navItems.forEach(item => {
                    const href = item.getAttribute('href');
                    const text = item.textContent.trim();
                    
                    if (href && !href.includes('#') && !href.includes('javascript')) {
                        navigation.push({
                            text: text,
                            href: href,
                            isVisible: item.offsetParent !== null
                        });
                    }
                });
                
                return {
                    totalNavItems: navigation.length,
                    visibleNavItems: navigation.filter(item => item.isVisible).length,
                    navigationItems: navigation
                };
            });
            
            return {
                success: true,
                navigation: navigationAnalysis
            };
            
        } catch (error) {
            return {
                success: false,
                error: error.message
            };
        }
    }

    async testRoleSpecificFeatures(role) {
        console.log(`   ⚡ Testing ${role}-specific features...`);
        
        try {
            const features = await this.page.evaluate((role) => {
                const results = {
                    admin: {
                        hasUserManagement: !!document.querySelector('[href*="members"], [href*="users"]'),
                        hasLoanManagement: !!document.querySelector('[href*="loans"]'),
                        hasReports: !!document.querySelector('[href*="reports"]'),
                        hasSettings: !!document.querySelector('[href*="settings"]'),
                        hasSystemConfig: !!document.querySelector('[data-feature="system-config"]')
                    },
                    staff: {
                        hasMemberManagement: !!document.querySelector('[href*="members"]'),
                        hasLoanProcessing: !!document.querySelector('[href*="loans"]'),
                        hasTransactions: !!document.querySelector('[href*="transactions"]'),
                        hasCollections: !!document.querySelector('[href*="collections"]'),
                        hasReports: !!document.querySelector('[href*="reports"]')
                    },
                    member: {
                        hasDashboard: !!document.querySelector('[href*="dashboard"]'),
                        hasProfile: !!document.querySelector('[href*="profile"]'),
                        hasLoanApplications: !!document.querySelector('[href*="loan-application"]'),
                        hasSavings: !!document.querySelector('[href*="savings"]'),
                        hasTransactions: !!document.querySelector('[href*="transactions"]')
                    }
                };
                
                return results[role] || {};
            }, role);
            
            return {
                success: true,
                features: features
            };
            
        } catch (error) {
            return {
                success: false,
                error: error.message
            };
        }
    }

    getRolePages(role) {
        const pages = {
            admin: [
                { name: 'Admin Dashboard', url: '/pages/admin/dashboard.html', expectedAccess: true },
                { name: 'Members Management', url: '/pages/admin/members.html', expectedAccess: true },
                { name: 'Loans Management', url: '/pages/admin/loans.html', expectedAccess: true },
                { name: 'Reports', url: '/pages/admin/reports.html', expectedAccess: true },
                { name: 'Staff Dashboard', url: '/pages/staff/dashboard.html', expectedAccess: false },
                { name: 'Member Dashboard', url: '/pages/member/dashboard.html', expectedAccess: false }
            ],
            staff: [
                { name: 'Staff Dashboard', url: '/pages/staff/dashboard.html', expectedAccess: true },
                { name: 'Members', url: '/pages/admin/members.html', expectedAccess: true },
                { name: 'Loans', url: '/pages/admin/loans.html', expectedAccess: true },
                { name: 'Transactions', url: '/pages/staff/transactions.html', expectedAccess: true },
                { name: 'Admin Dashboard', url: '/pages/admin/dashboard.html', expectedAccess: false },
                { name: 'Reports', url: '/pages/admin/reports.html', expectedAccess: false }
            ],
            member: [
                { name: 'Member Dashboard', url: '/pages/member/dashboard.html', expectedAccess: true },
                { name: 'Loan Application', url: '/pages/member/loan-application.html', expectedAccess: true },
                { name: 'Profile', url: '/pages/member/profile.html', expectedAccess: true },
                { name: 'Admin Dashboard', url: '/pages/admin/dashboard.html', expectedAccess: false },
                { name: 'Staff Dashboard', url: '/pages/staff/dashboard.html', expectedAccess: false }
            ]
        };
        
        return pages[role] || [];
    }

    async testPWAFeatures() {
        console.log('\n📱 Testing PWA Features...');
        
        const pwaTest = {
            serviceWorker: null,
            manifest: null,
            offlineSupport: null,
            installability: null,
            caching: null,
            developmentImpact: null,
            timestamp: new Date().toISOString()
        };
        
        try {
            // Test 1: Service Worker
            pwaTest.serviceWorker = await this.testServiceWorker();
            
            // Test 2: Web App Manifest
            pwaTest.manifest = await this.testWebAppManifest();
            
            // Test 3: Offline Support
            pwaTest.offlineSupport = await this.testOfflineSupport();
            
            // Test 4: Installability
            pwaTest.installability = await this.testInstallability();
            
            // Test 5: Caching Strategy
            pwaTest.caching = await this.testCachingStrategy();
            
            // Test 6: Development Impact
            pwaTest.developmentImpact = await this.testDevelopmentImpact();
            
            this.pwaTests.push(pwaTest);
            
            console.log('✅ PWA Testing Completed');
            
            return pwaTest;
            
        } catch (error) {
            console.log(`❌ PWA Testing Error: ${error.message}`);
            pwaTest.error = error.message;
            this.pwaTests.push(pwaTest);
            return pwaTest;
        }
    }

    async testServiceWorker() {
        console.log('   🔧 Testing Service Worker...');
        
        try {
            // Check if service worker is registered
            const swStatus = await this.page.evaluate(() => {
                return navigator.serviceWorker ? {
                    ready: !!navigator.serviceWorker.ready,
                    controller: !!navigator.serviceWorker.controller,
                    registration: null
                } : { ready: false, controller: false, registration: null };
            });
            
            // Check service worker file exists
            const swFileExists = await this.page.goto(`${this.baseUrl}/sw.js`, { waitUntil: 'networkidle2' })
                .then(response => response.status() === 200)
                .catch(() => false);
            
            return {
                registered: swStatus.ready,
                controlling: swStatus.controller,
                fileExists: swFileExists,
                status: swStatus
            };
            
        } catch (error) {
            return {
                registered: false,
                error: error.message
            };
        }
    }

    async testWebAppManifest() {
        console.log('   📋 Testing Web App Manifest...');
        
        try {
            // Check if manifest link exists
            const hasManifestLink = await this.page.$('link[rel="manifest"]') !== null;
            
            // Try to fetch manifest
            const manifestExists = await this.page.goto(`${this.baseUrl}/manifest.json`, { waitUntil: 'networkidle2' })
                .then(response => response.status() === 200)
                .catch(() => false);
            
            let manifestContent = null;
            if (manifestExists) {
                try {
                    manifestContent = await this.page.evaluate(() => {
                        return fetch('/manifest.json').then(r => r.json()).catch(() => null);
                    });
                } catch (e) {
                    manifestContent = null;
                }
            }
            
            return {
                hasLink: hasManifestLink,
                exists: manifestExists,
                content: manifestContent
            };
            
        } catch (error) {
            return {
                hasLink: false,
                exists: false,
                error: error.message
            };
        }
    }

    async testOfflineSupport() {
        console.log('   📶 Testing Offline Support...');
        
        try {
            // Simulate offline mode
            await this.page.setOfflineMode(true);
            
            // Try to access a page
            const offlineResponse = await this.page.goto(`${this.baseUrl}/pages/admin/dashboard.html`, { waitUntil: 'networkidle2', timeout: 5000 })
                .then(response => response.status())
                .catch(() => 0);
            
            // Restore online mode
            await this.page.setOfflineMode(false);
            
            return {
                offlineSupported: offlineResponse === 200,
                offlineResponse: offlineResponse
            };
            
        } catch (error) {
            return {
                offlineSupported: false,
                error: error.message
            };
        }
    }

    async testInstallability() {
        console.log('   📲 Testing Installability...');
        
        try {
            // Check PWA install criteria
            const installCriteria = await this.page.evaluate(() => {
                return {
                    hasServiceWorker: !!navigator.serviceWorker,
                    isHTTPS: location.protocol === 'https:' || location.hostname === 'localhost',
                    hasManifest: !!document.querySelector('link[rel="manifest"]'),
                    hasIcon: !!document.querySelector('link[rel="icon"], link[rel="apple-touch-icon"]')
                };
            });
            
            const isInstallable = Object.values(installCriteria).every(criteria => criteria);
            
            return {
                installable: isInstallable,
                criteria: installCriteria
            };
            
        } catch (error) {
            return {
                installable: false,
                error: error.message
            };
        }
    }

    async testCachingStrategy() {
        console.log('   💾 Testing Caching Strategy...');
        
        try {
            // Check cache headers
            const cacheTest = await this.page.goto(`${this.baseUrl}/pages/admin/dashboard.html`, { waitUntil: 'networkidle2' });
            const cacheControl = cacheTest.headers()['cache-control'] || '';
            const etag = cacheTest.headers()['etag'] || '';
            
            // Check if resources are cached
            const resourceAnalysis = await this.page.evaluate(() => {
                const resources = performance.getEntriesByType('resource');
                const cachedResources = resources.filter(r => r.transferSize === 0 && r.decodedBodySize > 0);
                
                return {
                    totalResources: resources.length,
                    cachedResources: cachedResources.length,
                    cacheRatio: (cachedResources.length / resources.length * 100).toFixed(1)
                };
            });
            
            return {
                cacheControl: cacheControl,
                etag: etag,
                resources: resourceAnalysis
            };
            
        } catch (error) {
            return {
                error: error.message
            };
        }
    }

    async testDevelopmentImpact() {
        console.log('   🛠️ Testing Development Impact...');
        
        try {
            // Test if PWA interferes with development
            const devImpactTest = await this.page.evaluate(() => {
                return {
                    consoleErrors: window.consoleErrors || 0,
                    networkErrors: window.networkErrors || 0,
                    serviceWorkerErrors: window.swErrors || 0,
                    hotReloadWorking: typeof hotReload !== 'undefined',
                    devToolsAccessible: !!window.devtools
                };
            });
            
            // Test page reload behavior
            const startTime = Date.now();
            await this.page.reload({ waitUntil: 'networkidle2' });
            const reloadTime = Date.now() - startTime;
            
            return {
                impact: devImpactTest,
                reloadTime: reloadTime,
                interferesWithDev: reloadTime > 5000 || devImpactTest.consoleErrors > 0
            };
            
        } catch (error) {
            return {
                interferesWithDev: true,
                error: error.message
            };
        }
    }

    async runAllTests() {
        console.log('\n🎯 Starting Comprehensive Role & PWA Testing...\n');
        
        // Test all roles
        console.log('👤 Phase 1: Role-Based Testing');
        await this.testRoleFeatures('admin', { username: 'admin', password: 'admin123' });
        await this.testRoleFeatures('staff', { username: 'staff', password: 'staff123' });
        await this.testRoleFeatures('member', { username: 'member', password: 'member123' });
        
        // Test PWA features
        console.log('\n📱 Phase 2: PWA Testing');
        await this.testPWAFeatures();
        
        // Generate comprehensive report
        await this.generateComprehensiveReport();
        
        return {
            roleTests: this.roleTests,
            pwaTests: this.pwaTests,
            errors: this.errors,
            fixes: this.fixes
        };
    }

    async generateComprehensiveReport() {
        console.log('\n📊 Generating Comprehensive Report...');
        
        const report = {
            summary: {
                totalRoleTests: this.roleTests.length,
                successfulRoleTests: this.roleTests.filter(r => r.loginTest?.success).length,
                totalPWATests: this.pwaTests.length,
                successfulPWATests: this.pwaTests.length,
                totalErrors: this.errors.length,
                totalFixes: this.fixes.length,
                timestamp: new Date().toISOString()
            },
            roleTests: this.roleTests,
            pwaTests: this.pwaTests,
            errors: this.errors,
            fixes: this.fixes,
            recommendations: this.generateRecommendations()
        };
        
        // Save JSON report
        const reportPath = path.join(__dirname, 'role-pwa-test-report.json');
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        // Generate HTML report
        const htmlReport = this.generateHTMLReport(report);
        const htmlReportPath = path.join(__dirname, 'role-pwa-test-report.html');
        fs.writeFileSync(htmlReportPath, htmlReport);
        
        console.log(`✅ JSON Report: ${reportPath}`);
        console.log(`✅ HTML Report: ${htmlReportPath}`);
        console.log(`📊 Role Tests: ${report.summary.totalRoleTests}`);
        console.log(`✅ Successful Role Tests: ${report.summary.successfulRoleTests}`);
        console.log(`📱 PWA Tests: ${report.summary.totalPWATests}`);
        console.log(`🔧 Total Errors: ${report.summary.totalErrors}`);
        
        return report;
    }

    generateRecommendations() {
        const recommendations = [];
        
        // Analyze role test results
        const failedRoleTests = this.roleTests.filter(r => !r.loginTest?.success);
        if (failedRoleTests.length > 0) {
            recommendations.push({
                type: 'role_authentication',
                priority: 'high',
                issue: 'Role authentication failures',
                count: failedRoleTests.length,
                recommendation: 'Fix authentication system for all roles'
            });
        }
        
        // Analyze PWA test results
        if (this.pwaTests.length > 0) {
            const pwaTest = this.pwaTests[0];
            
            if (!pwaTest.serviceWorker?.registered) {
                recommendations.push({
                    type: 'pwa_service_worker',
                    priority: 'medium',
                    issue: 'Service worker not properly registered',
                    recommendation: 'Implement proper service worker registration'
                });
            }
            
            if (pwaTest.developmentImpact?.interferesWithDev) {
                recommendations.push({
                    type: 'pwa_dev_impact',
                    priority: 'high',
                    issue: 'PWA interferes with development',
                    recommendation: 'Configure PWA to not interfere with development mode'
                });
            }
        }
        
        return recommendations;
    }

    generateHTMLReport(report) {
        return `
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role & PWA Test Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="fas fa-users-cog me-2"></i>
                    Role & PWA Comprehensive Test Report
                </h1>
                
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Role Tests</h5>
                                <h3>${report.summary.totalRoleTests}</h3>
                                <p class="mb-0">Total role tests</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Successful</h5>
                                <h3>${report.summary.successfulRoleTests}</h3>
                                <p class="mb-0">Role tests passed</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">PWA Tests</h5>
                                <h3>${report.summary.totalPWATests}</h3>
                                <p class="mb-0">PWA features tested</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Errors</h5>
                                <h3>${report.summary.totalErrors}</h3>
                                <p class="mb-0">Total errors found</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Role Tests Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-tag me-2"></i>
                            Role-Based Test Results
                        </h5>
                    </div>
                    <div class="card-body">
                        ${report.roleTests.map(roleTest => `
                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">${roleTest.role.toUpperCase()} Role</h6>
                                    <span class="badge bg-${roleTest.loginTest?.success ? 'success' : 'danger'}">
                                        ${roleTest.loginTest?.success ? 'SUCCESS' : 'FAILED'}
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Login Test</h6>
                                            <p class="mb-2">
                                                <strong>Success:</strong> ${roleTest.loginTest?.success ? 'Yes' : 'No'}<br>
                                                <strong>Current URL:</strong> ${roleTest.loginTest?.currentUrl || 'N/A'}
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Dashboard Test</h6>
                                            <p class="mb-2">
                                                <strong>Success:</strong> ${roleTest.dashboardTest?.success ? 'Yes' : 'No'}<br>
                                                <strong>Components:</strong> ${roleTest.dashboardTest?.components ? 'Loaded' : 'Not loaded'}
                                            </p>
                                        </div>
                                    </div>
                                    ${roleTest.permissionsTest ? `
                                        <h6>Permissions Test</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Page</th>
                                                        <th>Access</th>
                                                        <th>Expected</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${roleTest.permissionsTest.permissions.map(perm => `
                                                        <tr>
                                                            <td>${perm.page}</td>
                                                            <td><span class="badge bg-${perm.hasAccess ? 'success' : 'danger'}">${perm.hasAccess ? 'Granted' : 'Denied'}</span></td>
                                                            <td><span class="badge bg-info">${perm.expectedAccess ? 'Granted' : 'Denied'}</span></td>
                                                        </tr>
                                                    `).join('')}
                                                </tbody>
                                            </table>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <!-- PWA Tests Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-mobile-alt me-2"></i>
                            PWA Feature Test Results
                        </h5>
                    </div>
                    <div class="card-body">
                        ${report.pwaTests.map(pwaTest => `
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Service Worker</h6>
                                    <p class="mb-2">
                                        <strong>Registered:</strong> ${pwaTest.serviceWorker?.registered ? 'Yes' : 'No'}<br>
                                        <strong>Controlling:</strong> ${pwaTest.serviceWorker?.controlling ? 'Yes' : 'No'}<br>
                                        <strong>File Exists:</strong> ${pwaTest.serviceWorker?.fileExists ? 'Yes' : 'No'}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Web App Manifest</h6>
                                    <p class="mb-2">
                                        <strong>Has Link:</strong> ${pwaTest.manifest?.hasLink ? 'Yes' : 'No'}<br>
                                        <strong>Exists:</strong> ${pwaTest.manifest?.exists ? 'Yes' : 'No'}<br>
                                        <strong>Content:</strong> ${pwaTest.manifest?.content ? 'Loaded' : 'Not loaded'}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Offline Support</h6>
                                    <p class="mb-2">
                                        <strong>Supported:</strong> ${pwaTest.offlineSupport?.offlineSupported ? 'Yes' : 'No'}<br>
                                        <strong>Response:</strong> ${pwaTest.offlineSupport?.offlineResponse || 'N/A'}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Development Impact</h6>
                                    <p class="mb-2">
                                        <strong>Interferes:</strong> ${pwaTest.developmentImpact?.interferesWithDev ? 'Yes' : 'No'}<br>
                                        <strong>Reload Time:</strong> ${pwaTest.developmentImpact?.reloadTime || 'N/A'}ms
                                    </p>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <!-- Recommendations -->
                ${report.recommendations.length > 0 ? `
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Recommendations
                            </h5>
                        </div>
                        <div class="card-body">
                            ${report.recommendations.map(rec => `
                                <div class="alert alert-${rec.priority === 'high' ? 'danger' : 'warning'}">
                                    <h6>${rec.issue}</h6>
                                    <p class="mb-0">${rec.recommendation}</p>
                                    <small>Priority: ${rec.priority} | Count: ${rec.count || 1}</small>
                                </div>
                            `).join('')}
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
    const tester = new RolePWATester();
    
    try {
        await tester.init();
        const results = await tester.runAllTests();
        
        console.log('\n🎉 COMPREHENSIVE ROLE & PWA TESTING COMPLETED!');
        
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

module.exports = RolePWATester;
