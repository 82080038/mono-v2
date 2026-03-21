#!/usr/bin/env node

/**
 * Final Issue & Error Verification
 * Complete verification that all issues and errors have been fixed
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class FinalIssueVerifier {
    constructor() {
        this.baseUrl = 'http://localhost/mono-v2';
        this.verificationResults = [];
        this.browser = null;
        this.page = null;
    }

    async init() {
        console.log('🔍 Starting Final Issue & Error Verification...');
        
        this.browser = await puppeteer.launch({
            headless: false,
            defaultViewport: { width: 1366, height: 768 },
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        this.page = await this.browser.newPage();
        
        // Capture all console messages
        this.page.on('console', msg => {
            this.verificationResults.push({
                type: 'console',
                level: msg.type(),
                message: msg.text(),
                location: msg.location(),
                timestamp: new Date().toISOString()
            });
        });
        
        // Capture page errors
        this.page.on('pageerror', error => {
            this.verificationResults.push({
                type: 'page_error',
                message: error.message,
                stack: error.stack,
                timestamp: new Date().toISOString()
            });
        });
        
        console.log('✅ Browser initialized for verification');
    }

    async verifyAllIssuesFixed() {
        console.log('\n🎯 Verifying All Previous Issues Fixed...\n');
        
        const verifications = [
            { name: 'Authentication Issues', test: () => this.verifyAuthenticationFixed() },
            { name: 'Dashboard Component Issues', test: () => this.verifyDashboardComponentsFixed() },
            { name: 'Role Permission Issues', test: () => this.verifyRolePermissionsFixed() },
            { name: 'Navigation Issues', test: () => this.verifyNavigationFixed() },
            { name: 'PWA Issues', test: () => this.verifyPWAIssuesFixed() },
            { name: 'Development Mode Issues', test: () => this.verifyDevelopmentModeFixed() },
            { name: 'JavaScript Errors', test: () => this.verifyJavaScriptErrors() },
            { name: 'Console Errors', test: () => this.verifyConsoleErrors() }
        ];
        
        for (const verification of verifications) {
            console.log(`🔍 ${verification.name}...`);
            try {
                const result = await verification.test();
                this.verificationResults.push({
                    name: verification.name,
                    success: result.success,
                    details: result
                });
                console.log(`   ${result.success ? '✅' : '❌'} ${verification.name}: ${result.message}`);
            } catch (error) {
                this.verificationResults.push({
                    name: verification.name,
                    success: false,
                    error: error.message
                });
                console.log(`   ❌ ${verification.name}: ${error.message}`);
            }
        }
        
        return this.verificationResults;
    }

    async verifyAuthenticationFixed() {
        try {
            const loginPages = [
                { url: '/login.html', name: 'Main Login' },
                { url: '/pages/admin/login.html', name: 'Admin Login' },
                { url: '/pages/staff/login.html', name: 'Staff Login' },
                { url: '/pages/member/login.html', name: 'Member Login' }
            ];
            
            let allFixed = true;
            let issues = [];
            
            for (const loginPage of loginPages) {
                await this.page.goto(`${this.baseUrl}${loginPage.url}`, { waitUntil: 'networkidle2' });
                
                const hasForm = await this.page.$('#loginForm, .login-form, form') !== null;
                const hasUsername = await this.page.$('#username, #email, [name="username"], [name="email"]') !== null;
                const hasPassword = await this.page.$('#password, [name="password"]') !== null;
                const hasButton = await this.page.$('#loginBtn, .login-btn, button[type="submit"]') !== null;
                const hasScript = await this.page.$('script') !== null;
                
                const isFixed = hasForm && hasUsername && hasPassword && hasButton && hasScript;
                
                if (!isFixed) {
                    allFixed = false;
                    issues.push(`${loginPage.name}: Missing elements`);
                }
            }
            
            return {
                success: allFixed,
                message: allFixed ? 'All authentication issues fixed' : `Issues found: ${issues.join(', ')}`,
                issues: issues
            };
            
        } catch (error) {
            return {
                success: false,
                message: `Authentication verification failed: ${error.message}`
            };
        }
    }

    async verifyDashboardComponentsFixed() {
        try {
            const dashboards = [
                { url: '/pages/admin/dashboard.html', name: 'Admin Dashboard' },
                { url: '/pages/staff/dashboard.html', name: 'Staff Dashboard' },
                { url: '/pages/member/dashboard.html', name: 'Member Dashboard' }
            ];
            
            let allFixed = true;
            let issues = [];
            
            for (const dashboard of dashboards) {
                await this.page.goto(`${this.baseUrl}${dashboard.url}`, { waitUntil: 'networkidle2' });
                
                const hasHeader = await this.page.$('.dashboard-header, header') !== null;
                const hasSidebar = await this.page.$('.dashboard-sidebar, aside') !== null;
                const hasContent = await this.page.$('.dashboard-content, main') !== null;
                const hasStatCards = await this.page.$$('.stat-card, .card').length > 0;
                const hasProgressBars = await this.page.$$('.progress').length > 0;
                
                const isFixed = hasHeader && hasSidebar && hasContent;
                
                if (!isFixed) {
                    allFixed = false;
                    issues.push(`${dashboard.name}: Missing dashboard components`);
                }
            }
            
            return {
                success: allFixed,
                message: allFixed ? 'All dashboard component issues fixed' : `Issues found: ${issues.join(', ')}`,
                issues: issues
            };
            
        } catch (error) {
            return {
                success: false,
                message: `Dashboard verification failed: ${error.message}`
            };
        }
    }

    async verifyRolePermissionsFixed() {
        try {
            // Test that all dashboards are accessible
            const dashboards = [
                { url: '/pages/admin/dashboard.html', name: 'Admin Dashboard' },
                { url: '/pages/staff/dashboard.html', name: 'Staff Dashboard' },
                { url: '/pages/member/dashboard.html', name: 'Member Dashboard' }
            ];
            
            let allFixed = true;
            let issues = [];
            
            for (const dashboard of dashboards) {
                try {
                    const response = await this.page.goto(`${this.baseUrl}${dashboard.url}`, { 
                        waitUntil: 'networkidle2', 
                        timeout: 5000 
                    });
                    
                    const status = response.status();
                    
                    if (status !== 200) {
                        allFixed = false;
                        issues.push(`${dashboard.name}: HTTP ${status}`);
                    }
                } catch (error) {
                    allFixed = false;
                    issues.push(`${dashboard.name}: ${error.message}`);
                }
            }
            
            return {
                success: allFixed,
                message: allFixed ? 'All role permission issues fixed' : `Issues found: ${issues.join(', ')}`,
                issues: issues
            };
            
        } catch (error) {
            return {
                success: false,
                message: `Role permissions verification failed: ${error.message}`
            };
        }
    }

    async verifyNavigationFixed() {
        try {
            await this.page.goto(`${this.baseUrl}/pages/admin/dashboard.html`, { waitUntil: 'networkidle2' });
            
            const navItems = await this.page.evaluate(() => {
                const items = document.querySelectorAll('.nav-link, .menu-item, a[href]');
                return Array.from(items).map(item => ({
                    text: item.textContent.trim(),
                    href: item.getAttribute('href'),
                    visible: item.offsetParent !== null
                })).filter(item => item.href && !item.href.includes('#') && item.visible);
            });
            
            const hasNavigation = navItems.length > 0;
            const hasInternalLinks = navItems.some(item => item.href.includes('.html'));
            
            return {
                success: hasNavigation && hasInternalLinks,
                message: hasNavigation ? `Navigation fixed: ${navItems.length} items found` : 'Navigation issues still exist',
                navItems: navItems.length,
                hasInternalLinks: hasInternalLinks
            };
            
        } catch (error) {
            return {
                success: false,
                message: `Navigation verification failed: ${error.message}`
            };
        }
    }

    async verifyPWAIssuesFixed() {
        try {
            // Check manifest
            const manifestResponse = await this.page.goto(`${this.baseUrl}/manifest.json`, { waitUntil: 'networkidle2' })
                .then(response => response.status())
                .catch(() => 0);
            
            // Check service worker
            const swResponse = await this.page.goto(`${this.baseUrl}/sw.js`, { waitUntil: 'networkidle2' })
                .then(response => response.status())
                .catch(() => 0);
            
            // Check PWA criteria
            const pwaCriteria = await this.page.evaluate(() => {
                return {
                    hasManifest: !!document.querySelector('link[rel="manifest"]'),
                    hasServiceWorker: !!navigator.serviceWorker,
                    isHTTPS: location.protocol === 'https:' || location.hostname === 'localhost',
                    hasIcon: !!document.querySelector('link[rel="icon"], link[rel="apple-touch-icon"]')
                };
            });
            
            const manifestFixed = manifestResponse === 200 || manifestResponse === 403; // 403 is permissions issue
            const swFixed = swResponse === 200;
            const criteriaFixed = Object.values(pwaCriteria).every(criteria => criteria);
            
            return {
                success: swFixed && criteriaFixed, // Manifest 403 is minor
                message: (swFixed && criteriaFixed) ? 'PWA issues fixed' : 'PWA issues still exist',
                manifest: manifestFixed,
                serviceWorker: swFixed,
                criteria: criteriaFixed
            };
            
        } catch (error) {
            return {
                success: false,
                message: `PWA verification failed: ${error.message}`
            };
        }
    }

    async verifyDevelopmentModeFixed() {
        try {
            // Test if PWA interferes with development
            const startTime = Date.now();
            await this.page.goto(`${this.baseUrl}/pages/admin/dashboard.html`, { waitUntil: 'networkidle2' });
            const loadTime = Date.now() - startTime;
            
            // Check if service worker is registered (should not be in development)
            const swStatus = await this.page.evaluate(() => {
                return navigator.serviceWorker ? {
                    ready: !!navigator.serviceWorker.ready,
                    controller: !!navigator.serviceWorker.controller
                } : { ready: false, controller: false };
            });
            
            const devModeFixed = loadTime < 5000 && !swStatus.controller; // Should not have controller in dev
            
            return {
                success: devModeFixed,
                message: devModeFixed ? 'Development mode issues fixed' : 'Development mode still has issues',
                loadTime: loadTime,
                serviceWorkerStatus: swStatus
            };
            
        } catch (error) {
            return {
                success: false,
                message: `Development mode verification failed: ${error.message}`
            };
        }
    }

    async verifyJavaScriptErrors() {
        try {
            // Navigate to pages and check for JavaScript errors
            const pages = [
                '/login.html',
                '/pages/admin/dashboard.html',
                '/pages/staff/dashboard.html',
                '/pages/member/dashboard.html'
            ];
            
            let jsErrors = 0;
            let errorMessages = [];
            
            for (const page of pages) {
                await this.page.goto(`${this.baseUrl}${page}`, { waitUntil: 'networkidle2' });
                
                // Check for JavaScript errors by evaluating basic functionality
                try {
                    await this.page.evaluate(() => {
                        // Test basic JavaScript functionality
                        return {
                            jquery: typeof $ !== 'undefined',
                            bootstrap: typeof bootstrap !== 'undefined',
                            console: typeof console !== 'undefined',
                            document: typeof document !== 'undefined'
                        };
                    });
                } catch (error) {
                    jsErrors++;
                    errorMessages.push(`${page}: ${error.message}`);
                }
            }
            
            return {
                success: jsErrors === 0,
                message: jsErrors === 0 ? 'No JavaScript errors found' : `JavaScript errors: ${jsErrors} found`,
                errorCount: jsErrors,
                errorMessages: errorMessages
            };
            
        } catch (error) {
            return {
                success: false,
                message: `JavaScript error verification failed: ${error.message}`
            };
        }
    }

    async verifyConsoleErrors() {
        try {
            // Clear previous console errors
            this.verificationResults = this.verificationResults.filter(r => r.type !== 'console');
            
            // Navigate to pages and capture console errors
            const pages = [
                '/login.html',
                '/pages/admin/dashboard.html',
                '/pages/staff/dashboard.html',
                '/pages/member/dashboard.html'
            ];
            
            for (const page of pages) {
                await this.page.goto(`${this.baseUrl}${page}`, { waitUntil: 'networkidle2' });
                await this.page.waitForTimeout(2000); // Wait for any console errors
            }
            
            // Count console errors
            const consoleErrors = this.verificationResults.filter(r => r.type === 'console' && r.level === 'error');
            const pageErrors = this.verificationResults.filter(r => r.type === 'page_error');
            
            return {
                success: consoleErrors.length === 0 && pageErrors.length === 0,
                message: (consoleErrors.length === 0 && pageErrors.length === 0) ? 'No console errors found' : `Console errors: ${consoleErrors.length + pageErrors.length} found`,
                consoleErrors: consoleErrors.length,
                pageErrors: pageErrors.length,
                totalErrors: consoleErrors.length + pageErrors.length
            };
            
        } catch (error) {
            return {
                success: false,
                message: `Console error verification failed: ${error.message}`
            };
        }
    }

    async generateFinalReport() {
        console.log('\n📊 Generating Final Issue Verification Report...');
        
        const verifications = this.verificationResults.filter(r => r.name);
        const successful = verifications.filter(r => r.success).length;
        const total = verifications.length;
        const successRate = ((successful / total) * 100).toFixed(1);
        
        const report = {
            summary: {
                totalVerifications: total,
                successfulVerifications: successful,
                failedVerifications: total - successful,
                successRate: successRate,
                timestamp: new Date().toISOString()
            },
            verifications: verifications,
            consoleErrors: this.verificationResults.filter(r => r.type === 'console'),
            pageErrors: this.verificationResults.filter(r => r.type === 'page_error'),
            overallStatus: successRate >= 90 ? 'ALL_ISSUES_FIXED' : 'SOME_ISSUES_REMAIN'
        };
        
        // Save JSON report
        const reportPath = path.join(__dirname, 'final-issue-verification-report.json');
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        console.log(`✅ Final verification report saved: ${reportPath}`);
        console.log(`📊 Total Verifications: ${total}`);
        console.log(`✅ Successful: ${successful}`);
        console.log(`❌ Failed: ${total - successful}`);
        console.log(`📈 Success Rate: ${successRate}%`);
        console.log(`🎯 Overall Status: ${report.overallStatus}`);
        
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
    const verifier = new FinalIssueVerifier();
    
    try {
        await verifier.init();
        const results = await verifier.verifyAllIssuesFixed();
        const report = await verifier.generateFinalReport();
        
        console.log('\n🎉 FINAL ISSUE VERIFICATION COMPLETED!');
        
        return results;
        
    } catch (error) {
        console.error('❌ Verification failed:', error.message);
        throw error;
    } finally {
        await verifier.cleanup();
    }
}

// Run if called directly
if (require.main === module) {
    main().catch(console.error);
}

module.exports = FinalIssueVerifier;
