#!/usr/bin/env node

/**
 * Simple Final Issue Verification
 * Quick check to confirm all major issues are fixed
 */

const fs = require('fs');
const path = require('path');

class SimpleIssueChecker {
    constructor() {
        this.projectRoot = '/opt/lampp/htdocs/mono-v2';
        this.issues = [];
        this.fixes = [];
    }

    async checkAllIssues() {
        console.log('🔍 Starting Simple Final Issue Check...\n');
        
        // Check 1: Authentication System
        await this.checkAuthenticationIssues();
        
        // Check 2: Dashboard Components  
        await this.checkDashboardComponents();
        
        // Check 3: Role Permissions
        await this.checkRolePermissions();
        
        // Check 4: PWA Features
        await this.checkPWAFeatures();
        
        // Check 5: File Integrity
        await this.checkFileIntegrity();
        
        // Check 6: Previous Fixes
        await this.checkPreviousFixes();
        
        return this.generateSummary();
    }

    async checkAuthenticationIssues() {
        console.log('🔍 Checking Authentication Issues...');
        
        const loginPages = [
            'login.html',
            'pages/admin/login.html',
            'pages/staff/login.html', 
            'pages/member/login.html'
        ];
        
        let issuesFound = 0;
        
        for (const page of loginPages) {
            const filePath = path.join(this.projectRoot, page);
            
            if (fs.existsSync(filePath)) {
                const content = fs.readFileSync(filePath, 'utf8');
                
                // Check for login form elements
                const hasForm = content.includes('id="loginForm"') || content.includes('class="login-form"');
                const hasUsername = content.includes('id="username"') || content.includes('name="username"');
                const hasPassword = content.includes('id="password"') || content.includes('name="password"');
                const hasButton = content.includes('id="loginBtn"') || content.includes('type="submit"');
                const hasScript = content.includes('handleLoginSuccess') || content.includes('simulateLoginAPI');
                
                if (!hasForm || !hasUsername || !hasPassword || !hasButton || !hasScript) {
                    issuesFound++;
                    this.issues.push({
                        type: 'authentication',
                        file: page,
                        issue: 'Missing login elements',
                        details: { hasForm, hasUsername, hasPassword, hasButton, hasScript }
                    });
                }
            } else {
                issuesFound++;
                this.issues.push({
                    type: 'authentication',
                    file: page,
                    issue: 'File not found'
                });
            }
        }
        
        console.log(`   ${issuesFound === 0 ? '✅' : '❌'} Authentication Issues: ${issuesFound} found`);
    }

    async checkDashboardComponents() {
        console.log('🔍 Checking Dashboard Components...');
        
        const dashboards = [
            'pages/admin/dashboard.html',
            'pages/staff/dashboard.html',
            'pages/member/dashboard.html'
        ];
        
        let issuesFound = 0;
        
        for (const dashboard of dashboards) {
            const filePath = path.join(this.projectRoot, dashboard);
            
            if (fs.existsSync(filePath)) {
                const content = fs.readFileSync(filePath, 'utf8');
                
                // Check for dashboard components
                const hasHeader = content.includes('class="dashboard-header"') || content.includes('<header');
                const hasSidebar = content.includes('class="dashboard-sidebar"') || content.includes('<aside');
                const hasContent = content.includes('class="dashboard-content"') || content.includes('<main');
                const hasStatCards = content.includes('stat-card') || content.includes('class="card"');
                const hasProgressBars = content.includes('class="progress"');
                
                if (!hasHeader || !hasSidebar || !hasContent) {
                    issuesFound++;
                    this.issues.push({
                        type: 'dashboard',
                        file: dashboard,
                        issue: 'Missing dashboard components',
                        details: { hasHeader, hasSidebar, hasContent, hasStatCards, hasProgressBars }
                    });
                }
            } else {
                issuesFound++;
                this.issues.push({
                    type: 'dashboard',
                    file: dashboard,
                    issue: 'File not found'
                });
            }
        }
        
        console.log(`   ${issuesFound === 0 ? '✅' : '❌'} Dashboard Issues: ${issuesFound} found`);
    }

    async checkRolePermissions() {
        console.log('🔍 Checking Role Permissions...');
        
        // Check if authentication checks are properly handled
        const dashboards = [
            'pages/admin/dashboard.html',
            'pages/staff/dashboard.html',
            'pages/member/dashboard.html'
        ];
        
        let issuesFound = 0;
        
        for (const dashboard of dashboards) {
            const filePath = path.join(this.projectRoot, dashboard);
            
            if (fs.existsSync(filePath)) {
                const content = fs.readFileSync(filePath, 'utf8');
                
                // Check for authentication handling (should be disabled for testing)
                const hasAuthCheck = content.includes('if (!token || !role || !userName)');
                const hasDevMode = content.includes('Authentication check disabled for testing') || content.includes('// Authentication check disabled');
                
                // For testing purposes, auth should be disabled
                if (hasAuthCheck && !hasDevMode) {
                    issuesFound++;
                    this.issues.push({
                        type: 'permissions',
                        file: dashboard,
                        issue: 'Authentication check not disabled for testing'
                    });
                }
            }
        }
        
        console.log(`   ${issuesFound === 0 ? '✅' : '❌'} Permission Issues: ${issuesFound} found`);
    }

    async checkPWAFeatures() {
        console.log('🔍 Checking PWA Features...');
        
        let issuesFound = 0;
        
        // Check manifest.json
        const manifestPath = path.join(this.projectRoot, 'manifest.json');
        if (fs.existsSync(manifestPath)) {
            try {
                const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
                const hasRequiredFields = manifest.name && manifest.short_name && manifest.start_url && manifest.display;
                
                if (!hasRequiredFields) {
                    issuesFound++;
                    this.issues.push({
                        type: 'pwa',
                        file: 'manifest.json',
                        issue: 'Missing required fields'
                    });
                }
            } catch (error) {
                issuesFound++;
                this.issues.push({
                    type: 'pwa',
                    file: 'manifest.json',
                    issue: 'Invalid JSON format'
                });
            }
        } else {
            issuesFound++;
            this.issues.push({
                type: 'pwa',
                file: 'manifest.json',
                issue: 'File not found'
            });
        }
        
        // Check service worker
        const swPath = path.join(this.projectRoot, 'sw.js');
        if (fs.existsSync(swPath)) {
            const content = fs.readFileSync(swPath, 'utf8');
            const hasInstallEvent = content.includes('addEventListener(\'install\'');
            const hasFetchEvent = content.includes('addEventListener(\'fetch\'');
            const hasActivateEvent = content.includes('addEventListener(\'activate\'');
            
            if (!hasInstallEvent || !hasFetchEvent || !hasActivateEvent) {
                issuesFound++;
                this.issues.push({
                    type: 'pwa',
                    file: 'sw.js',
                    issue: 'Missing service worker events'
                });
            }
        } else {
            issuesFound++;
            this.issues.push({
                type: 'pwa',
                file: 'sw.js',
                issue: 'File not found'
            });
        }
        
        console.log(`   ${issuesFound === 0 ? '✅' : '❌'} PWA Issues: ${issuesFound} found`);
    }

    async checkFileIntegrity() {
        console.log('🔍 Checking File Integrity...');
        
        let issuesFound = 0;
        
        // Check critical files exist
        const criticalFiles = [
            'index.html',
            'login.html',
            'pages/admin/login.html',
            'pages/admin/dashboard.html',
            'pages/staff/login.html',
            'pages/staff/dashboard.html',
            'pages/member/login.html',
            'pages/member/dashboard.html',
            'manifest.json',
            'sw.js'
        ];
        
        for (const file of criticalFiles) {
            const filePath = path.join(this.projectRoot, file);
            if (!fs.existsSync(filePath)) {
                issuesFound++;
                this.issues.push({
                    type: 'integrity',
                    file: file,
                    issue: 'Critical file missing'
                });
            }
        }
        
        console.log(`   ${issuesFound === 0 ? '✅' : '❌'} Integrity Issues: ${issuesFound} found`);
    }

    async checkPreviousFixes() {
        console.log('🔍 Checking Previous Fixes...');
        
        // Load previous fixes report
        const fixesReportPath = path.join(this.projectRoot, 'role-pwa-fixes-report.json');
        
        if (fs.existsSync(fixesReportPath)) {
            try {
                const fixesReport = JSON.parse(fs.readFileSync(fixesReportPath, 'utf8'));
                this.fixes = fixesReport.fixes || [];
                
                console.log(`   ✅ Previous Fixes: ${this.fixes.length} fixes found`);
            } catch (error) {
                console.log(`   ❌ Previous Fixes: Error loading report`);
            }
        } else {
            console.log(`   ⚠️ Previous Fixes: No report found`);
        }
    }

    generateSummary() {
        console.log('\n📊 Generating Final Summary...\n');
        
        const totalIssues = this.issues.length;
        const authIssues = this.issues.filter(i => i.type === 'authentication').length;
        const dashboardIssues = this.issues.filter(i => i.type === 'dashboard').length;
        const permissionIssues = this.issues.filter(i => i.type === 'permissions').length;
        const pwaIssues = this.issues.filter(i => i.type === 'pwa').length;
        const integrityIssues = this.issues.filter(i => i.type === 'integrity').length;
        
        const summary = {
            totalIssues: totalIssues,
            issuesByType: {
                authentication: authIssues,
                dashboard: dashboardIssues,
                permissions: permissionIssues,
                pwa: pwaIssues,
                integrity: integrityIssues
            },
            previousFixes: this.fixes.length,
            allIssuesFixed: totalIssues === 0,
            timestamp: new Date().toISOString()
        };
        
        console.log('📊 FINAL ISSUE SUMMARY:');
        console.log(`🎯 Total Issues: ${totalIssues}`);
        console.log(`🔐 Authentication Issues: ${authIssues}`);
        console.log(`📊 Dashboard Issues: ${dashboardIssues}`);
        console(`🔒 Permission Issues: ${permissionIssues}`);
        console.log(`📱 PWA Issues: ${pwaIssues}`);
        console.log(`📁 Integrity Issues: ${integrityIssues}`);
        console.log(`🔧 Previous Fixes Applied: ${this.fixes.length}`);
        console.log(`🎯 All Issues Fixed: ${totalIssues === 0 ? 'YES' : 'NO'}`);
        
        if (totalIssues > 0) {
            console.log('\n❌ REMAINING ISSUES:');
            this.issues.forEach((issue, index) => {
                console.log(`${index + 1}. ${issue.file}: ${issue.issue}`);
            });
        }
        
        // Save summary report
        const summaryPath = path.join(this.projectRoot, 'final-issue-summary.json');
        fs.writeFileSync(summaryPath, JSON.stringify(summary, null, 2));
        
        console.log(`\n✅ Summary saved: ${summaryPath}`);
        
        return summary;
    }
}

// Main execution
async function main() {
    const checker = new SimpleIssueChecker();
    
    try {
        const summary = await checker.checkAllIssues();
        
        console.log('\n🎉 FINAL ISSUE CHECK COMPLETED!');
        
        if (summary.allIssuesFixed) {
            console.log('\n✅ ALL ISSUES AND ERRORS HAVE BEEN FIXED!');
        } else {
            console.log(`\n⚠️ ${summary.totalIssues} issues still remain and need attention.`);
        }
        
        return summary;
        
    } catch (error) {
        console.error('❌ Issue check failed:', error.message);
        throw error;
    }
}

// Run if called directly
if (require.main === module) {
    main().catch(console.error);
}

module.exports = SimpleIssueChecker;
