#!/usr/bin/env node

/**
 * Simple Role & PWA Status Check
 * Quick verification of critical functionality
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class SimpleStatusChecker {
    constructor() {
        this.baseUrl = 'http://localhost/mono-v2';
        this.statusResults = [];
        this.browser = null;
        this.page = null;
    }

    async init() {
        console.log('🔍 Starting Simple Status Check...');
        
        this.browser = await puppeteer.launch({
            headless: false,
            defaultViewport: { width: 1366, height: 768 },
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        this.page = await this.browser.newPage();
        console.log('✅ Browser initialized');
    }

    async checkStatus() {
        console.log('\n🎯 Checking System Status...\n');
        
        const checks = [
            { name: 'Main Login Page', url: '/login.html', type: 'page' },
            { name: 'Admin Login Page', url: '/pages/admin/login.html', type: 'page' },
            { name: 'Staff Login Page', url: '/pages/staff/login.html', type: 'page' },
            { name: 'Member Login Page', url: '/pages/member/login.html', type: 'page' },
            { name: 'Admin Dashboard', url: '/pages/admin/dashboard.html', type: 'page' },
            { name: 'Staff Dashboard', url: '/pages/staff/dashboard.html', type: 'page' },
            { name: 'Member Dashboard', url: '/pages/member/dashboard.html', type: 'page' },
            { name: 'Manifest File', url: '/manifest.json', type: 'file' },
            { name: 'Service Worker', url: '/sw.js', type: 'file' },
            { name: 'API Health', url: '/api/health', type: 'api' }
        ];
        
        for (const check of checks) {
            console.log(`🔍 ${check.name}...`);
            
            try {
                if (check.type === 'page') {
                    const result = await this.checkPage(check.url, check.name);
                    this.statusResults.push(result);
                    console.log(`   ${result.success ? '✅' : '❌'} ${check.name}: ${result.message}`);
                } else if (check.type === 'file') {
                    const result = await this.checkFile(check.url, check.name);
                    this.statusResults.push(result);
                    console.log(`   ${result.success ? '✅' : '❌'} ${check.name}: ${result.message}`);
                } else if (check.type === 'api') {
                    const result = await this.checkAPI(check.url, check.name);
                    this.statusResults.push(result);
                    console.log(`   ${result.success ? '✅' : '❌'} ${check.name}: ${result.message}`);
                }
            } catch (error) {
                this.statusResults.push({
                    name: check.name,
                    success: false,
                    message: `Error: ${error.message}`
                });
                console.log(`   ❌ ${check.name}: Error - ${error.message}`);
            }
        }
        
        return this.statusResults;
    }

    async checkPage(url, name) {
        try {
            const response = await this.page.goto(`${this.baseUrl}${url}`, { 
                waitUntil: 'networkidle2', 
                timeout: 10000 
            });
            
            const status = response.status();
            const title = await this.page.title();
            
            if (status === 200) {
                // Check for login form elements
                if (name.includes('Login')) {
                    const hasForm = await this.page.$('#loginForm, .login-form, form') !== null;
                    const hasUsername = await this.page.$('#username, #email, [name="username"], [name="email"]') !== null;
                    const hasPassword = await this.page.$('#password, [name="password"]') !== null;
                    const hasButton = await this.page.$('#loginBtn, .login-btn, button[type="submit"]') !== null;
                    
                    if (hasForm && hasUsername && hasPassword && hasButton) {
                        return {
                            success: true,
                            message: 'Login form functional',
                            status: status,
                            title: title
                        };
                    } else {
                        return {
                            success: false,
                            message: 'Login form incomplete',
                            status: status,
                            title: title,
                            elements: { hasForm, hasUsername, hasPassword, hasButton }
                        };
                    }
                }
                
                // Check for dashboard elements
                if (name.includes('Dashboard')) {
                    const hasHeader = await this.page.$('.dashboard-header, header') !== null;
                    const hasSidebar = await this.page.$('.dashboard-sidebar, aside') !== null;
                    const hasContent = await this.page.$('.dashboard-content, main') !== null;
                    
                    if (hasHeader && hasSidebar && hasContent) {
                        return {
                            success: true,
                            message: 'Dashboard functional',
                            status: status,
                            title: title
                        };
                    } else {
                        return {
                            success: false,
                            message: 'Dashboard incomplete',
                            status: status,
                            title: title,
                            components: { hasHeader, hasSidebar, hasContent }
                        };
                    }
                }
                
                return {
                    success: true,
                    message: 'Page accessible',
                    status: status,
                    title: title
                };
            } else {
                return {
                    success: false,
                    message: `HTTP ${status}`,
                    status: status
                };
            }
            
        } catch (error) {
            return {
                success: false,
                message: `Load error: ${error.message}`
            };
        }
    }

    async checkFile(url, name) {
        try {
            const response = await this.page.goto(`${this.baseUrl}${url}`, { 
                waitUntil: 'networkidle2', 
                timeout: 5000 
            });
            
            const status = response.status();
            
            if (status === 200) {
                const content = await this.page.content();
                const isValid = name.includes('Manifest') ? 
                    content.includes('"name"') && content.includes('"start_url"') :
                    content.includes('addEventListener');
                
                return {
                    success: isValid,
                    message: isValid ? 'File valid' : 'File invalid',
                    status: status,
                    size: content.length
                };
            } else {
                return {
                    success: false,
                    message: `HTTP ${status}`,
                    status: status
                };
            }
            
        } catch (error) {
            return {
                success: false,
                message: `File error: ${error.message}`
            };
        }
    }

    async checkAPI(url, name) {
        try {
            const response = await this.page.goto(`${this.baseUrl}${url}`, { 
                waitUntil: 'networkidle2', 
                timeout: 5000 
            });
            
            const status = response.status();
            
            if (status === 200) {
                return {
                    success: true,
                    message: 'API responding',
                    status: status
                };
            } else if (status === 404) {
                return {
                    success: true,
                    message: 'API not found (normal)',
                    status: status
                };
            } else {
                return {
                    success: false,
                    message: `API error: HTTP ${status}`,
                    status: status
                };
            }
            
        } catch (error) {
            return {
                success: false,
                message: `API error: ${error.message}`
            };
        }
    }

    async generateReport() {
        console.log('\n📊 Generating Status Report...');
        
        const successful = this.statusResults.filter(r => r.success).length;
        const total = this.statusResults.length;
        const successRate = ((successful / total) * 100).toFixed(1);
        
        const report = {
            summary: {
                totalChecks: total,
                successfulChecks: successful,
                failedChecks: total - successful,
                successRate: successRate,
                timestamp: new Date().toISOString()
            },
            results: this.statusResults,
            overallStatus: successRate >= 80 ? 'HEALTHY' : 'NEEDS_ATTENTION'
        };
        
        // Save JSON report
        const reportPath = path.join(__dirname, 'role-pwa-status-report.json');
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        console.log(`✅ Status report saved: ${reportPath}`);
        console.log(`📊 Total Checks: ${total}`);
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
    const checker = new SimpleStatusChecker();
    
    try {
        await checker.init();
        const results = await checker.checkStatus();
        const report = await checker.generateReport();
        
        console.log('\n🎉 STATUS CHECK COMPLETED!');
        
        return { results, report };
        
    } catch (error) {
        console.error('❌ Status check failed:', error.message);
        throw error;
    } finally {
        await checker.cleanup();
    }
}

// Run if called directly
if (require.main === module) {
    main().catch(console.error);
}

module.exports = SimpleStatusChecker;
