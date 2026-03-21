#!/usr/bin/env node

/**
 * Final Role & PWA Verification
 * Verify all critical functionality works correctly
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class FinalRolePWAVerifier {
    constructor() {
        this.baseUrl = 'http://localhost/mono-v2';
        this.verificationResults = [];
        this.browser = null;
        this.page = null;
    }

    async init() {
        console.log('🔍 Starting Final Role & PWA Verification...');
        
        this.browser = await puppeteer.launch({
            headless: false,
            defaultViewport: { width: 1366, height: 768 },
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        this.page = await this.browser.newPage();
        console.log('✅ Browser initialized for verification');
    }

    async verifyCriticalFunctionality() {
        console.log('\n🎯 Verifying Critical Functionality...\n');
        
        const verifications = [
            { name: 'Login Pages Access', test: () => this.verifyLoginPages() },
            { name: 'Dashboard Access', test: () => this.verifyDashboardAccess() },
            { name: 'Navigation Functionality', test: () => this.verifyNavigation() },
            { name: 'PWA Features', test: () => this.verifyPWAFeatures() },
            { name: 'Development Mode', test: () => this.verifyDevelopmentMode() }
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

    async verifyLoginPages() {
        const loginPages = [
            { url: '/login.html', name: 'Main Login' },
            { url: '/pages/admin/login.html', name: 'Admin Login' },
            { url: '/pages/staff/login.html', name: 'Staff Login' },
            { url: '/pages/member/login.html', name: 'Member Login' }
        ];
        
        let allAccessible = true;
        let results = [];
        
        for (const loginPage of loginPages) {
            try {
                await this.page.goto(`${this.baseUrl}${loginPage.url}`, { waitUntil: 'networkidle2', timeout: 10000 });
                
                const hasForm = await this.page.$('#loginForm, .login-form, form') !== null;
                const hasUsername = await this.page.$('#username, #email, [name="username"], [name="email"]') !== null;
                const hasPassword = await this.page.$('#password, [name="password"]') !== null;
                const hasButton = await this.page.$('#loginBtn, .login-btn, button[type="submit"]') !== null;
                
                const isWorking = hasForm && hasUsername && hasPassword && hasButton;
                
                results.push({
                    page: loginPage.name,
                    working: isWorking,
                    elements: { hasForm, hasUsername, hasPassword, hasButton }
                });
                
                if (!isWorking) allAccessible = false;
                
            } catch (error) {
                results.push({
                    page: loginPage.name,
                    working: false,
                    error: error.message
                });
                allAccessible = false;
            }
        }
        
        return {
            success: allAccessible,
            message: allAccessible ? 'All login pages accessible' : 'Some login pages have issues',
            results: results
        };
    }

    async verifyDashboardAccess() {
        const dashboards = [
            { url: '/pages/admin/dashboard.html', name: 'Admin Dashboard' },
            { url: '/pages/staff/dashboard.html', name: 'Staff Dashboard' },
            { url: '/pages/member/dashboard.html', name: 'Member Dashboard' }
        ];
        
        let allAccessible = true;
        let results = [];
        
        for (const dashboard of dashboards) {
            try {
                await this.page.goto(`${this.baseUrl}${dashboard.url}`, { waitUntil: 'networkidle2', timeout: 10000 });
                
                const hasHeader = await this.page.$('.dashboard-header, header') !== null;
                const hasSidebar = await this.page.$('.dashboard-sidebar, aside') !== null;
                const hasContent = await this.page.$('.dashboard-content, main') !== null;
                const hasStatCards = await this.page.$$('.stat-card, .card').length > 0;
                
                const isWorking = hasHeader && hasSidebar && hasContent;
                
                results.push({
                    dashboard: dashboard.name,
                    working: isWorking,
                    components: { hasHeader, hasSidebar, hasContent, hasStatCards }
                });
                
                if (!isWorking) allAccessible = false;
                
            } catch (error) {
                results.push({
                    dashboard: dashboard.name,
                    working: false,
                    error: error.message
                });
                allAccessible = false;
            }
        }
        
        return {
            success: allAccessible,
            message: allAccessible ? 'All dashboards accessible' : 'Some dashboards have issues',
            results: results
        };
    }

    async verifyNavigation() {
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
                success: hasNavigation,
                message: hasNavigation ? `Found ${navItems.length} navigation items` : 'No navigation found',
                navItems: navItems,
                hasInternalLinks: hasInternalLinks
            };
            
        } catch (error) {
            return {
                success: false,
                message: `Navigation verification failed: ${error.message}`
            };
        }
    }

    async verifyPWAFeatures() {
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
            
            const pwaReady = manifestResponse === 200 && swResponse === 200 && 
                            Object.values(pwaCriteria).every(criteria => criteria);
            
            return {
                success: pwaReady,
                message: pwaReady ? 'PWA features ready' : 'PWA features incomplete',
                manifest: manifestResponse === 200,
                serviceWorker: swResponse === 200,
                criteria: pwaCriteria
            };
            
        } catch (error) {
            return {
                success: false,
                message: `PWA verification failed: ${error.message}`
            };
        }
    }

    async verifyDevelopmentMode() {
        try {
            // Test if PWA interferes with development
            const startTime = Date.now();
            await this.page.goto(`${this.baseUrl}/pages/admin/dashboard.html`, { waitUntil: 'networkidle2' });
            const loadTime = Date.now() - startTime;
            
            // Check if service worker is registered (should not be in development)
            const swRegistered = await this.page.evaluate(() => {
                return navigator.serviceWorker && navigator.serviceWorker.ready;
            }).then(() => true).catch(() => false);
            
            // Check for development-friendly features
            const devFriendly = await this.page.evaluate(() => {
                return {
                    consoleErrors: window.consoleErrors || 0,
                    hotReloadWorking: typeof hotReload !== 'undefined',
                    devToolsAccessible: !!window.devtools
                };
            });
            
            const devModeWorking = loadTime < 5000 && (!swRegistered || devFriendly.hotReloadWorking);
            
            return {
                success: devModeWorking,
                message: devModeWorking ? 'Development mode working properly' : 'Development mode has issues',
                loadTime: loadTime,
                serviceWorkerRegistered: swRegistered,
                devFriendly: devFriendly
            };
            
        } catch (error) {
            return {
                success: false,
                message: `Development mode verification failed: ${error.message}`
            };
        }
    }

    async runVerification() {
        try {
            await this.init();
            const results = await this.verifyCriticalFunctionality();
            
            const successful = results.filter(r => r.success).length;
            const total = results.length;
            const successRate = ((successful / total) * 100).toFixed(1);
            
            console.log('\n📊 Verification Results:');
            console.log(`✅ Successful: ${successful}/${total} (${successRate}%)`);
            
            results.forEach(result => {
                console.log(`${result.success ? '✅' : '❌'} ${result.name}: ${result.message}`);
            });
            
            await this.generateVerificationReport(results);
            
            return results;
            
        } catch (error) {
            console.error('❌ Verification failed:', error.message);
            throw error;
        } finally {
            await this.cleanup();
        }
    }

    async generateVerificationReport(results) {
        console.log('\n📊 Generating Verification Report...');
        
        const report = {
            summary: {
                totalVerifications: results.length,
                successfulVerifications: results.filter(r => r.success).length,
                successRate: ((results.filter(r => r.success).length / results.length) * 100).toFixed(1),
                timestamp: new Date().toISOString()
            },
            verifications: results,
            overallStatus: results.every(r => r.success) ? 'PASS' : 'NEEDS_ATTENTION'
        };
        
        // Save JSON report
        const reportPath = path.join(__dirname, 'final-role-pwa-verification.json');
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        console.log(`✅ Verification report saved: ${reportPath}`);
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
    const verifier = new FinalRolePWAVerifier();
    
    try {
        const results = await verifier.runVerification();
        
        console.log('\n🎉 FINAL VERIFICATION COMPLETED!');
        
        return results;
        
    } catch (error) {
        console.error('❌ Verification failed:', error.message);
        throw error;
    }
}

// Run if called directly
if (require.main === module) {
    main().catch(console.error);
}

module.exports = FinalRolePWAVerifier;
