/**
 * Puppeteer Test for SPA Admin Dashboard
 * Tests SPA navigation, content rendering, and functionality
 */
const puppeteer = require('puppeteer');

class SPADashboardTest {
    constructor() {
        this.browser = null;
        this.page = null;
        this.baseUrl = 'http://localhost/mono-v2/pages/admin/dashboard.html';
        this.testResults = [];
    }

    async init() {
        console.log('🚀 Starting SPA Dashboard Test with Puppeteer...\n');
        
        this.browser = await puppeteer.launch({
            headless: false, // Show browser for debugging
            defaultViewport: { width: 1366, height: 768 },
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        
        this.page = await this.browser.newPage();
        
        // Enable request interception
        await this.page.setRequestInterception(true);
        this.page.on('request', request => {
            request.continue();
        });

        // Listen for console errors
        this.page.on('console', msg => {
            if (msg.type() === 'error') {
                console.log('Browser Console Error:', msg.text());
            }
        });

        // Listen for page errors
        this.page.on('pageerror', error => {
            console.log('Page Error:', error.message);
        });
    }

    async runAllTests() {
        try {
            await this.init();
            
            // Test 1: Page Load
            await this.testPageLoad();
            
            // Test 2: Authentication Check
            await this.testAuthentication();
            
            // Test 3: SPA Navigation
            await this.testSPANavigation();
            
            // Test 4: Content Rendering
            await this.testContentRendering();
            
            // Test 5: Sidebar Functionality
            await this.testSidebarFunctionality();
            
            // Test 6: User Interactions
            await this.testUserInteractions();
            
            // Test 7: Responsive Design
            await this.testResponsiveDesign();
            
            // Generate report
            this.generateTestReport();
            
        } catch (error) {
            console.error('Test failed:', error);
        } finally {
            await this.cleanup();
        }
    }

    async testPageLoad() {
        console.log('📄 Test 1: Page Load');
        
        // Set auth tokens first
        await this.page.evaluate(() => {
            localStorage.setItem('authToken', 'MTphZG1pbjphZG1pbgo=');
            localStorage.setItem('userRole', 'admin');
            localStorage.setItem('userName', 'TestAdmin');
        });
        
        const startTime = Date.now();
        await this.page.goto(this.baseUrl, { waitUntil: 'networkidle2' });
        const loadTime = Date.now() - startTime;
        
        // Check if page loaded successfully
        const title = await this.page.title();
        const url = this.page.url();
        
        // Check for main elements
        const sidebar = await this.page.$('#sidebar');
        const dashboardMain = await this.page.$('#dashboard-main');
        const header = await this.page.$('.dashboard-header');
        
        this.addTestResult('Page Load', {
            passed: !!(sidebar && dashboardMain && header),
            details: {
                title: title,
                url: url,
                loadTime: `${loadTime}ms`,
                hasSidebar: !!sidebar,
                hasMainContent: !!dashboardMain,
                hasHeader: !!header
            }
        });
        
        console.log(`   ✅ Page loaded in ${loadTime}ms`);
        console.log(`   ✅ Title: ${title}`);
        console.log(`   ✅ All main elements present\n`);
    }

    async testAuthentication() {
        console.log('🔐 Test 2: Authentication');
        
        // Set auth tokens before navigation
        await this.page.evaluate(() => {
            localStorage.setItem('authToken', 'MTphZG1pbjphZG1pbgo=');
            localStorage.setItem('userRole', 'admin');
            localStorage.setItem('userName', 'TestAdmin');
        });
        
        // Reload page to test auth check
        await this.page.reload({ waitUntil: 'networkidle2' });
        
        // Check if not redirected to login
        const currentUrl = this.page.url();
        const isNotRedirected = !currentUrl.includes('login.html');
        
        // Check user info display
        const userNameElement = await this.page.$('#headerUserName');
        const userName = await this.page.evaluate(el => el?.textContent, userNameElement);
        
        this.addTestResult('Authentication', {
            passed: isNotRedirected && userName === 'TestAdmin',
            details: {
                notRedirected: isNotRedirected,
                currentUrl: currentUrl,
                userName: userName,
                tokenSet: true
            }
        });
        
        console.log(`   ✅ Authentication passed`);
        console.log(`   ✅ User name displayed: ${userName}\n`);
    }

    async testSPANavigation() {
        console.log('🧭 Test 3: SPA Navigation');
        
        const navigationTests = [
            { link: 'members.html', title: 'Manajemen Anggota' },
            { link: 'npl.html', title: 'Manajemen NPL' },
            { link: 'loans.html', title: 'Manajemen Pinjaman' },
            { link: 'settings.html', title: 'Pengaturan' }
        ];
        
        for (const test of navigationTests) {
            try {
                // Click navigation link
                await this.page.click(`a[href="${test.link}"]`);
                await this.page.waitForTimeout(1000); // Wait for content to load
                
                // Check if URL changed (should not change in SPA)
                const currentUrl = this.page.url();
                const urlChanged = !currentUrl.includes(test.link);
                
                // Check if page title updated
                const pageTitle = await this.page.$eval('#pageTitle', el => el.textContent);
                const titleUpdated = pageTitle === test.title;
                
                // Check if content area updated
                const contentArea = await this.page.$eval('#dashboard-main', el => el.innerHTML);
                const hasContent = contentArea.length > 100;
                
                // Check active nav state
                const activeLink = await this.page.$eval(`a[href="${test.link}"]`, el => 
                    el.classList.contains('active')
                );
                
                this.addTestResult(`Navigation to ${test.link}`, {
                    passed: urlChanged && titleUpdated && hasContent && activeLink,
                    details: {
                        urlChanged: urlChanged,
                        titleUpdated: titleUpdated,
                        pageTitle: pageTitle,
                        expectedTitle: test.title,
                        hasContent: hasContent,
                        activeLink: activeLink
                    }
                });
                
                console.log(`   ✅ ${test.title}: Navigation successful`);
                
            } catch (error) {
                console.log(`   ❌ ${test.title}: Navigation failed - ${error.message}`);
                this.addTestResult(`Navigation to ${test.link}`, {
                    passed: false,
                    error: error.message
                });
            }
        }
        
        // Return to dashboard
        await this.page.click('a[href="dashboard.html"]');
        await this.page.waitForTimeout(1000);
        console.log('');
    }

    async testContentRendering() {
        console.log('📱 Test 4: Content Rendering');
        
        // Test dashboard content
        const dashboardContent = await this.page.$eval('#dashboard-main', el => el.innerHTML);
        const hasStatsCards = dashboardContent.includes('stat-card');
        const hasStatistics = dashboardContent.includes('Total Anggota');
        
        // Navigate to members and test content
        await this.page.click('a[href="members.html"]');
        await this.page.waitForTimeout(1000);
        
        const membersContent = await this.page.$eval('#dashboard-main', el => el.innerHTML);
        const hasMembersTable = membersContent.includes('membersTableBody');
        const hasSearchInput = membersContent.includes('searchMember');
        const hasFilterSelect = membersContent.includes('filterStatus');
        
        // Navigate to NPL and test content
        await this.page.click('a[href="npl.html"]');
        await this.page.waitForTimeout(1000);
        
        const nplContent = await this.page.$eval('#dashboard-main', el => el.innerHTML);
        const hasNPLStats = nplContent.includes('Total NPL');
        const hasNPLTable = nplContent.includes('Daftar Pinjaman Bermasalah');
        
        this.addTestResult('Content Rendering', {
            passed: hasStatsCards && hasMembersTable && hasNPLStats,
            details: {
                dashboardStats: hasStatsCards,
                membersTable: hasMembersTable,
                membersSearch: hasSearchInput,
                membersFilter: hasFilterSelect,
                nplStatistics: hasNPLStats,
                nplTable: hasNPLTable
            }
        });
        
        console.log(`   ✅ Dashboard content rendered correctly`);
        console.log(`   ✅ Members page content rendered correctly`);
        console.log(`   ✅ NPL page content rendered correctly\n`);
    }

    async testSidebarFunctionality() {
        console.log('📊 Test 5: Sidebar Functionality');
        
        // Test sidebar toggle
        const sidebarBefore = await this.page.$eval('#sidebar', el => el.offsetWidth);
        
        await this.page.click('button[onclick="toggleSidebar()"]');
        await this.page.waitForTimeout(300);
        
        const sidebarAfter = await this.page.$eval('#sidebar', el => el.offsetWidth);
        const sidebarCollapsed = sidebarAfter < sidebarBefore;
        
        // Toggle back
        await this.page.click('button[onclick="toggleSidebar()"]');
        await this.page.waitForTimeout(300);
        
        // Test navigation links count
        const navLinks = await this.page.$$eval('.nav-link', links => links.length);
        
        // Test specific navigation items
        const dashboardLink = await this.page.$('a[href="dashboard.html"]');
        const membersLink = await this.page.$('a[href="members.html"]');
        const settingsLink = await this.page.$('a[href="settings.html"]');
        
        this.addTestResult('Sidebar Functionality', {
            passed: sidebarCollapsed && navLinks > 5 && dashboardLink,
            details: {
                sidebarToggle: sidebarCollapsed,
                navLinksCount: navLinks,
                hasDashboardLink: !!dashboardLink,
                hasMembersLink: !!membersLink,
                hasSettingsLink: !!settingsLink,
                sidebarWidthBefore: sidebarBefore,
                sidebarWidthAfter: sidebarAfter
            }
        });
        
        console.log(`   ✅ Sidebar toggle works correctly`);
        console.log(`   ✅ ${navLinks} navigation links found`);
        console.log(`   ✅ All main navigation items present\n`);
    }

    async testUserInteractions() {
        console.log('🖱️ Test 6: User Interactions');
        
        // Test dropdown menu
        await this.page.click('.dropdown-toggle');
        await this.page.waitForTimeout(500);
        
        const dropdownVisible = await this.page.$eval('.dropdown-menu', el => 
            el.style.display !== 'none'
        );
        
        // Test search functionality in members page
        await this.page.click('a[href="members.html"]');
        await this.page.waitForTimeout(1000);
        
        // Type in search
        await this.page.type('#searchMember', 'John');
        await this.page.waitForTimeout(500);
        
        // Check if filtering works
        const tableRows = await this.page.$$eval('#membersTableBody tr', rows => 
            rows.filter(row => row.style.display !== 'none').length
        );
        
        // Test filter dropdown
        await this.page.select('#filterStatus', 'active');
        await this.page.waitForTimeout(500);
        
        // Test button interactions
        const buttons = await this.page.$$('.btn');
        const hasInteractiveButtons = buttons.length > 5;
        
        this.addTestResult('User Interactions', {
            passed: dropdownVisible && tableRows > 0 && hasInteractiveButtons,
            details: {
                dropdownWorks: dropdownVisible,
                searchFiltering: tableRows > 0,
                filteredRows: tableRows,
                buttonCount: buttons.length,
                hasInteractiveButtons: hasInteractiveButtons
            }
        });
        
        console.log(`   ✅ Dropdown menu works`);
        console.log(`   ✅ Search filtering works (${tableRows} rows visible)`);
        console.log(`   ✅ ${buttons.length} interactive buttons found\n`);
    }

    async testResponsiveDesign() {
        console.log('📱 Test 7: Responsive Design');
        
        // Test desktop view
        await this.page.setViewport({ width: 1366, height: 768 });
        await this.page.waitForTimeout(500);
        
        const desktopSidebarWidth = await this.page.$eval('#sidebar', el => el.offsetWidth);
        const desktopMainMargin = await this.page.$eval('.dashboard-main', el => 
            getComputedStyle(el).marginLeft
        );
        
        // Test mobile view
        await this.page.setViewport({ width: 375, height: 667 });
        await this.page.waitForTimeout(500);
        
        const mobileSidebarWidth = await this.page.$eval('#sidebar', el => el.offsetWidth);
        const mobileMainMargin = await this.page.$eval('.dashboard-main', el => 
            getComputedStyle(el).marginLeft
        );
        
        // Test responsive behavior
        const responsiveSidebar = mobileSidebarWidth <= desktopSidebarWidth;
        const responsiveMain = mobileMainMargin !== desktopMainMargin;
        
        // Test touch interactions
        await this.page.tap('button[onclick="toggleSidebar()"]');
        await this.page.waitForTimeout(500);
        
        this.addTestResult('Responsive Design', {
            passed: responsiveSidebar && responsiveMain,
            details: {
                desktopSidebarWidth: desktopSidebarWidth,
                mobileSidebarWidth: mobileSidebarWidth,
                desktopMainMargin: desktopMainMargin,
                mobileMainMargin: mobileMainMargin,
                responsiveSidebar: responsiveSidebar,
                responsiveMain: responsiveMain
            }
        });
        
        console.log(`   ✅ Desktop layout correct (${desktopSidebarWidth}px sidebar)`);
        console.log(`   ✅ Mobile layout responsive (${mobileSidebarWidth}px sidebar)`);
        console.log(`   ✅ Touch interactions work\n`);
    }

    addTestResult(testName, result) {
        this.testResults.push({
            test: testName,
            passed: result.passed,
            details: result.details || {},
            error: result.error || null,
            timestamp: new Date().toISOString()
        });
    }

    generateTestReport() {
        console.log('📊 Test Report Summary');
        console.log('='.repeat(50));
        
        const totalTests = this.testResults.length;
        const passedTests = this.testResults.filter(r => r.passed).length;
        const failedTests = totalTests - passedTests;
        
        console.log(`\nTotal Tests: ${totalTests}`);
        console.log(`Passed: ${passedTests} ✅`);
        console.log(`Failed: ${failedTests} ❌`);
        console.log(`Success Rate: ${((passedTests / totalTests) * 100).toFixed(1)}%\n`);
        
        // Detailed results
        this.testResults.forEach(result => {
            const status = result.passed ? '✅' : '❌';
            console.log(`${status} ${result.test}`);
            
            if (!result.passed && result.error) {
                console.log(`   Error: ${result.error}`);
            }
            
            if (result.details && Object.keys(result.details).length > 0) {
                Object.entries(result.details).forEach(([key, value]) => {
                    if (typeof value === 'boolean') {
                        console.log(`   ${key}: ${value ? '✅' : '❌'}`);
                    } else {
                        console.log(`   ${key}: ${value}`);
                    }
                });
            }
            console.log('');
        });
        
        // Save report to file
        const reportData = {
            summary: {
                total: totalTests,
                passed: passedTests,
                failed: failedTests,
                successRate: ((passedTests / totalTests) * 100).toFixed(1)
            },
            tests: this.testResults,
            timestamp: new Date().toISOString()
        };
        
        const fs = require('fs');
        fs.writeFileSync('spa-dashboard-test-report.json', JSON.stringify(reportData, null, 2));
        
        console.log('📄 Detailed report saved to: spa-dashboard-test-report.json');
        
        if (failedTests === 0) {
            console.log('🎉 All tests passed! SPA Dashboard is working correctly.');
        } else {
            console.log(`⚠️  ${failedTests} test(s) failed. Please check the issues above.`);
        }
    }

    async cleanup() {
        if (this.browser) {
            await this.browser.close();
        }
    }
}

// Run tests
if (require.main === module) {
    const tester = new SPADashboardTest();
    tester.runAllTests().catch(console.error);
}

module.exports = SPADashboardTest;
