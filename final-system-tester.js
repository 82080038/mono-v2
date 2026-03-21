#!/usr/bin/env node

/**
 * Final System Testing with Accurate Detection
 * Test all pages with proper element detection
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class FinalSystemTester {
    constructor() {
        this.baseUrl = 'http://localhost/mono-v2';
        this.testResults = [];
        this.browser = null;
        this.page = null;
    }

    async init() {
        console.log('🚀 Initializing Final System Testing...');
        
        this.browser = await puppeteer.launch({
            headless: false,
            defaultViewport: { width: 1366, height: 768 },
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        this.page = await this.browser.newPage();
        console.log('✅ Browser initialized');
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
            
            console.log(`📸 Screenshot: ${filename}`);
        } catch (error) {
            console.log(`❌ Screenshot failed: ${error.message}`);
        }
    }

    async testPageComprehensive(url, testName) {
        console.log(`\n🧪 Final Test: ${testName}`);
        console.log(`📍 URL: ${url}`);
        
        const startTime = Date.now();
        let testResult = {
            test: testName,
            url: url,
            success: false,
            loadTime: 0,
            issues: [],
            elements: {},
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
            
            // Comprehensive element checking
            const pageAnalysis = await this.page.evaluate(() => {
                const results = {
                    // Basic structure
                    hasTitle: !!document.title && document.title.trim() !== '',
                    hasBody: !!document.body,
                    hasHead: !!document.head,
                    hasMetaCharset: !!document.querySelector('meta[charset]'),
                    title: document.title,
                    
                    // Dashboard components
                    hasDashboardHeader: !!document.querySelector('.dashboard-header, header.dashboard-header, .navbar.dashboard-header'),
                    hasDashboardSidebar: !!document.querySelector('.dashboard-sidebar, aside.dashboard-sidebar, .sidebar.dashboard-sidebar'),
                    hasStatCards: document.querySelectorAll('.stat-card, .card.stat-card, .card').length,
                    hasProgressBars: document.querySelectorAll('.progress').length,
                    
                    // Login components
                    hasLoginForm: !!document.querySelector('#loginForm, .login-form, form.login-form'),
                    hasUsernameInput: !!document.querySelector('#username, #email, [name="username"], [name="email"]'),
                    hasPasswordInput: !!document.querySelector('#password, [name="password"]'),
                    hasLoginButton: !!document.querySelector('#loginBtn, .login-btn, button[type="submit"]'),
                    
                    // Libraries
                    hasJQuery: typeof $ !== 'undefined',
                    hasBootstrap: typeof bootstrap !== 'undefined' || !!document.querySelector('[class*="btn-"]'),
                    hasFontAwesome: !!document.querySelector('[class*="fa-"]'),
                    
                    // Other components
                    hasNavigation: !!document.querySelector('nav, .navbar'),
                    hasForms: document.querySelectorAll('form').length,
                    hasButtons: document.querySelectorAll('button, .btn').length,
                    hasCards: document.querySelectorAll('.card').length,
                    hasTables: document.querySelectorAll('table').length,
                    hasAlerts: document.querySelectorAll('.alert').length,
                    
                    // Indonesian language check
                    lang: document.documentElement.lang,
                    hasIndonesianLang: document.documentElement.lang === 'id'
                };
                
                return results;
            });
            
            testResult.elements = pageAnalysis;
            
            // Check for issues
            if (!pageAnalysis.hasMetaCharset) {
                testResult.issues.push('Missing charset meta tag');
            }
            
            if (testName.includes('Dashboard')) {
                if (!pageAnalysis.hasDashboardHeader) {
                    testResult.issues.push('Missing dashboard header');
                }
                if (!pageAnalysis.hasDashboardSidebar) {
                    testResult.issues.push('Missing dashboard sidebar');
                }
            }
            
            if (testName.includes('Login')) {
                if (!pageAnalysis.hasLoginForm) {
                    testResult.issues.push('Missing login form');
                }
                if (!pageAnalysis.hasUsernameInput) {
                    testResult.issues.push('Missing username input');
                }
                if (!pageAnalysis.hasPasswordInput) {
                    testResult.issues.push('Missing password input');
                }
                if (!pageAnalysis.hasLoginButton) {
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
            
            // Print element details
            console.log(`   Elements: Header=${pageAnalysis.hasDashboardHeader}, Sidebar=${pageAnalysis.hasDashboardSidebar}, Cards=${pageAnalysis.hasCards}, Forms=${pageAnalysis.hasForms}`);
            
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

    async runFinalTests() {
        console.log('🎯 Starting Final Comprehensive Testing...\n');
        
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
        
        // Generate final report
        await this.generateFinalReport();
        
        return this.testResults;
    }

    async generateFinalReport() {
        console.log('\n📊 Generating Final Test Report...');
        
        const successful = this.testResults.filter(r => r.success).length;
        const withIssues = this.testResults.filter(r => !r.success).length;
        
        const report = {
            summary: {
                totalTests: this.testResults.length,
                successfulTests: successful,
                testsWithIssues: withIssues,
                successRate: ((successful / this.testResults.length) * 100).toFixed(1),
                timestamp: new Date().toISOString()
            },
            results: this.testResults,
            recommendations: this.generateRecommendations()
        };
        
        // Save JSON report
        const reportPath = path.join(__dirname, 'final-test-report.json');
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        console.log(`✅ Report saved: ${reportPath}`);
        console.log(`📊 Total Tests: ${report.summary.totalTests}`);
        console.log(`✅ Successful: ${report.summary.successfulTests}`);
        console.log(`⚠️ With Issues: ${report.summary.testsWithIssues}`);
        console.log(`📈 Success Rate: ${report.summary.successRate}%`);
        
        return report;
    }

    generateRecommendations() {
        const recommendations = [];
        
        // Analyze common issues
        const allIssues = this.testResults.flatMap(r => r.issues);
        const issueCounts = allIssues.reduce((counts, issue) => {
            counts[issue] = (counts[issue] || 0) + 1;
            return counts;
        }, {});
        
        Object.entries(issueCounts).forEach(([issue, count]) => {
            if (issue.includes('charset')) {
                recommendations.push({
                    issue: 'Missing charset meta tag',
                    count: count,
                    severity: 'medium',
                    recommendation: 'Add <meta charset="UTF-8"> to all HTML pages'
                });
            } else if (issue.includes('dashboard header')) {
                recommendations.push({
                    issue: 'Missing dashboard header',
                    count: count,
                    severity: 'high',
                    recommendation: 'Add dashboard header with proper class to all dashboard pages'
                });
            } else if (issue.includes('dashboard sidebar')) {
                recommendations.push({
                    issue: 'Missing dashboard sidebar',
                    count: count,
                    severity: 'high',
                    recommendation: 'Add navigation sidebar to all dashboard pages'
                });
            }
        });
        
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
    const tester = new FinalSystemTester();
    
    try {
        await tester.init();
        const results = await tester.runFinalTests();
        
        console.log('\n🎉 FINAL TESTING COMPLETED!');
        
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

module.exports = FinalSystemTester;
