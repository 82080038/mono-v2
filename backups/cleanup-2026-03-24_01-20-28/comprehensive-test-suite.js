const puppeteer = require('puppeteer');
const fs = require('fs');

/**
 * Comprehensive Test Suite for KSP Lam Gabe Jaya
 * Tests all functionality after OOP refactoring
 */

class ComprehensiveTestSuite {
    constructor() {
        this.browser = null;
        this.page = null;
        this.testResults = [];
        this.baseUrl = 'http://localhost/mono-v2';
        this.screenshotDir = './screenshots';
        this.testData = {
            users: [
                { username: 'bos', password: 'bos', role: 'bos', expectedPages: ['dashboard', 'laporan', 'nasabah', 'pinjaman', 'simpanan', 'pengaturan'] },
                { username: 'admin', password: 'admin', role: 'admin', expectedPages: ['dashboard', 'nasabah', 'pinjaman', 'simpanan', 'transaksi', 'laporan'] },
                { username: 'teller', password: 'teller', role: 'teller', expectedPages: ['dashboard', 'nasabah', 'setoran', 'penarikan', 'pembayaran', 'laporan_harian'] },
                { username: 'collector', password: 'collector', role: 'collector', expectedPages: ['dashboard', 'rute', 'jadwal', 'nasabah_kunjungan', 'kutipan', 'gps_log'] },
                { username: 'nasabah', password: 'nasabah', role: 'nasabah', expectedPages: ['dashboard', 'profil', 'simpanan_saya', 'pinjaman_saya', 'riwayat', 'pembayaran'] }
            ]
        };
    }

    async init() {
        console.log('🚀 Starting Comprehensive Test Suite for KSP Lam Gabe Jaya');
        console.log('='.repeat(70));
        
        // Create screenshots directory
        if (!fs.existsSync(this.screenshotDir)) {
            fs.mkdirSync(this.screenshotDir, { recursive: true });
        }

        // Launch browser with enhanced settings
        this.browser = await puppeteer.launch({
            headless: false, // Set to true for CI/CD
            defaultViewport: { width: 1366, height: 768 },
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--no-first-run',
                '--no-zygote',
                '--single-process',
                '--disable-gpu'
            ]
        });

        this.page = await this.browser.newPage();
        
        // Set up request interception for debugging
        await this.page.setRequestInterception(true);
        this.page.on('request', (request) => {
            request.continue();
        });

        // Capture console errors
        this.page.on('console', (msg) => {
            if (msg.type() === 'error') {
                this.addTestResult('JavaScript Error', false, msg.text(), 'error');
            }
        });

        // Capture page errors
        this.page.on('pageerror', (error) => {
            this.addTestResult('Page Error', false, error.message, 'error');
        });
    }

    async runAllTests() {
        try {
            await this.init();
            
            // Phase 1: Basic Application Tests
            await this.testBasicApplication();
            
            // Phase 2: Authentication Tests
            await this.testAuthentication();
            
            // Phase 3: Dashboard Tests
            await this.testDashboard();
            
            // Phase 4: Navigation Tests
            await this.testNavigation();
            
            // Phase 5: Security Tests
            await this.testSecurity();
            
            // Phase 6: Performance Tests
            await this.testPerformance();
            
            // Phase 7: API Tests
            await this.testAPI();
            
            // Generate comprehensive report
            await this.generateReport();
            
        } catch (error) {
            console.error('❌ Test suite failed:', error);
            this.addTestResult('Test Suite Error', false, error.message, 'critical');
        } finally {
            await this.cleanup();
        }
    }

    async testBasicApplication() {
        console.log('\n🔍 PHASE 1: BASIC APPLICATION TESTS');
        console.log('-'.repeat(70));

        // Test 1: Application Accessibility
        await this.testApplicationAccessibility();
        
        // Test 2: Login Page Loading
        await this.testLoginPageLoading();
        
        // Test 3: Form Validation
        await this.testFormValidation();
        
        // Test 4: Static Assets Loading
        await this.testStaticAssets();
    }

    async testApplicationAccessibility() {
        try {
            console.log('📡 Testing application accessibility...');
            
            const response = await this.page.goto(this.baseUrl + '/login.php', {
                waitUntil: 'networkidle2',
                timeout: 30000
            });

            const status = response.status();
            const title = await this.page.title();
            const url = this.page.url();

            if (status === 200 && title.includes('KSP Lam Gabe Jaya')) {
                this.addTestResult('Application Accessibility', true, `Status: ${status}, Title: ${title}`);
                await this.takeScreenshot('application_accessible');
            } else {
                this.addTestResult('Application Accessibility', false, `Status: ${status}, Title: ${title}`);
            }

        } catch (error) {
            this.addTestResult('Application Accessibility', false, error.message);
        }
    }

    async testLoginPageLoading() {
        try {
            console.log('📄 Testing login page loading...');
            
            // Check for essential elements
            const elements = [
                '.login-card',
                '#username',
                '#password',
                'form[method="POST"]',
                '.demo-accounts'
            ];

            let allElementsFound = true;
            const missingElements = [];

            for (const selector of elements) {
                const element = await this.page.$(selector);
                if (!element) {
                    allElementsFound = false;
                    missingElements.push(selector);
                }
            }

            if (allElementsFound) {
                this.addTestResult('Login Page Elements', true, 'All essential elements found');
                await this.takeScreenshot('login_page_complete');
            } else {
                this.addTestResult('Login Page Elements', false, `Missing: ${missingElements.join(', ')}`);
            }

            // Check for JavaScript functionality
            const javascriptEnabled = await this.page.evaluate(() => {
                return typeof window.addEventListener !== 'undefined';
            });

            this.addTestResult('JavaScript Enabled', javascriptEnabled, javascriptEnabled ? 'JavaScript is working' : 'JavaScript not working');

        } catch (error) {
            this.addTestResult('Login Page Loading', false, error.message);
        }
    }

    async testFormValidation() {
        try {
            console.log('✅ Testing form validation...');

            // Test empty form submission
            await this.page.click('button[type="submit"]');
            await this.page.waitForTimeout(1000);

            // Check for validation messages
            const validationMessage = await this.page.$eval('.alert-danger', el => el.textContent.trim()).catch(() => '');

            if (validationMessage.includes('Username dan password harus diisi')) {
                this.addTestResult('Empty Form Validation', true, validationMessage);
            } else {
                this.addTestResult('Empty Form Validation', false, 'Validation message not found');
            }

            // Test password visibility toggle
            const passwordInput = await this.page.$('#password');
            const toggleButton = await this.page.$('#togglePassword');

            if (passwordInput && toggleButton) {
                const initialType = await this.page.$eval('#password', el => el.type);
                
                await this.page.click('#togglePassword');
                await this.page.waitForTimeout(500);
                
                const newType = await this.page.$eval('#password', el => el.type);
                
                if (initialType !== newType) {
                    this.addTestResult('Password Toggle', true, 'Password visibility toggle works');
                } else {
                    this.addTestResult('Password Toggle', false, 'Password toggle not working');
                }
            } else {
                this.addTestResult('Password Toggle', false, 'Password elements not found');
            }

        } catch (error) {
            this.addTestResult('Form Validation', false, error.message);
        }
    }

    async testStaticAssets() {
        try {
            console.log('🎨 Testing static assets loading...');

            // Check CSS files
            const cssLinks = await this.page.$$eval('link[rel="stylesheet"]', links => 
                links.map(link => link.href)
            );

            // Check JavaScript files
            const jsScripts = await this.page.$$eval('script[src]', scripts => 
                scripts.map(script => script.src)
            );

            // Check images
            const images = await this.page.$$eval('img', imgs => 
                imgs.map(img => img.src)
            );

            this.addTestResult('CSS Files Loaded', cssLinks.length > 0, `Found ${cssLinks.length} CSS files`);
            this.addTestResult('JavaScript Files Loaded', jsScripts.length > 0, `Found ${jsScripts.length} JS files`);
            this.addTestResult('Images Loaded', images.length > 0, `Found ${images.length} images`);

            // Test Bootstrap functionality
            const bootstrapLoaded = await this.page.evaluate(() => {
                return typeof bootstrap !== 'undefined';
            });

            this.addTestResult('Bootstrap Loaded', bootstrapLoaded, bootstrapLoaded ? 'Bootstrap is working' : 'Bootstrap not loaded');

        } catch (error) {
            this.addTestResult('Static Assets', false, error.message);
        }
    }

    async testAuthentication() {
        console.log('\n🔐 PHASE 2: AUTHENTICATION TESTS');
        console.log('-'.repeat(70));

        for (const user of this.testData.users) {
            await this.testUserLogin(user);
            await this.testUserLogout(user);
        }
    }

    async testUserLogin(userData) {
        try {
            console.log(`👤 Testing ${userData.role} login...`);

            // Navigate to login page
            await this.page.goto(this.baseUrl + '/login.php', { waitUntil: 'networkidle2' });

            // Fill login form
            await this.page.type('#username', userData.username, { delay: 100 });
            await this.page.type('#password', userData.password, { delay: 100 });

            // Submit form
            await this.page.click('button[type="submit"]');
            
            // Wait for redirect or response
            await this.page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 10000 });

            const currentUrl = this.page.url();
            const pageTitle = await this.page.title();

            // Check if login was successful
            if (currentUrl.includes('index.php') || currentUrl.includes('dashboard')) {
                this.addTestResult(`${userData.role} Login`, true, `Redirected to: ${currentUrl}`);
                await this.takeScreenshot(`${userData.role}_dashboard_loaded`);
                
                // Verify user session
                const sessionValid = await this.page.evaluate(() => {
                    return document.body.innerHTML.includes('Dashboard') || 
                           document.body.innerHTML.includes('dashboard');
                });

                this.addTestResult(`${userData.role} Session Valid`, sessionValid, sessionValid ? 'Session is valid' : 'Session invalid');

            } else {
                this.addTestResult(`${userData.role} Login`, false, `Failed to redirect. Current URL: ${currentUrl}`);
                await this.takeScreenshot(`${userData.role}_login_failed`);
            }

        } catch (error) {
            this.addTestResult(`${userData.role} Login`, false, error.message);
        }
    }

    async testUserLogout(userData) {
        try {
            console.log(`🚪 Testing ${userData.role} logout...`);

            // Look for logout button/link
            const logoutSelectors = [
                'a[href*="logout"]',
                'button[onclick*="logout"]',
                '.logout',
                '[data-action="logout"]'
            ];

            let logoutFound = false;
            for (const selector of logoutSelectors) {
                const logoutElement = await this.page.$(selector);
                if (logoutElement) {
                    logoutFound = true;
                    await logoutElement.click();
                    break;
                }
            }

            if (!logoutFound) {
                // Try to trigger logout via JavaScript
                await this.page.evaluate(() => {
                    if (typeof logout === 'function') {
                        logout();
                    }
                });
            }

            // Wait for redirect
            await this.page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 10000 });

            const currentUrl = this.page.url();

            if (currentUrl.includes('login.php')) {
                this.addTestResult(`${userData.role} Logout`, true, 'Successfully logged out and redirected');
                await this.takeScreenshot(`${userData.role}_logout_success`);
            } else {
                this.addTestResult(`${userData.role} Logout`, false, `Logout failed. Current URL: ${currentUrl}`);
            }

        } catch (error) {
            this.addTestResult(`${userData.role} Logout`, false, error.message);
        }
    }

    async testDashboard() {
        console.log('\n📊 PHASE 3: DASHBOARD TESTS');
        console.log('-'.repeat(70));

        // Test with admin user
        await this.testUserLogin({ username: 'admin', password: 'admin' });

        // Test dashboard elements
        await this.testDashboardElements();
        
        // Test dashboard widgets
        await this.testDashboardWidgets();
        
        // Test dashboard responsiveness
        await this.testDashboardResponsive();
    }

    async testDashboardElements() {
        try {
            console.log('🧩 Testing dashboard elements...');

            const dashboardElements = [
                '.dashboard-header',
                '.sidebar',
                '.main-content',
                '.user-info',
                '.nav-link'
            ];

            let allElementsFound = true;
            const foundElements = [];

            for (const selector of dashboardElements) {
                const element = await this.page.$(selector);
                if (element) {
                    foundElements.push(selector);
                } else {
                    allElementsFound = false;
                }
            }

            this.addTestResult('Dashboard Elements', allElementsFound, 
                `Found: ${foundElements.length}/${dashboardElements.length} elements`);
            
            await this.takeScreenshot('dashboard_elements');

        } catch (error) {
            this.addTestResult('Dashboard Elements', false, error.message);
        }
    }

    async testDashboardWidgets() {
        try {
            console.log('📦 Testing dashboard widgets...');

            // Wait for widgets to load
            await this.page.waitForTimeout(2000);

            // Check for widget containers
            const widgets = await this.page.$$('.widget, .card, .stat-card');
            
            this.addTestResult('Dashboard Widgets', widgets.length > 0, 
                `Found ${widgets.length} widgets`);

            // Test widget functionality
            if (widgets.length > 0) {
                const widgetContent = await this.page.evaluate(() => {
                    const firstWidget = document.querySelector('.widget, .card, .stat-card');
                    return firstWidget ? firstWidget.textContent.trim().length > 0 : false;
                });

                this.addTestResult('Widget Content', widgetContent, 
                    widgetContent ? 'Widgets have content' : 'Widgets are empty');
            }

            await this.takeScreenshot('dashboard_widgets');

        } catch (error) {
            this.addTestResult('Dashboard Widgets', false, error.message);
        }
    }

    async testDashboardResponsive() {
        try {
            console.log('📱 Testing dashboard responsiveness...');

            // Test desktop view
            await this.page.setViewport({ width: 1366, height: 768 });
            const desktopLayout = await this.page.evaluate(() => {
                const sidebar = document.querySelector('.sidebar');
                return sidebar ? sidebar.offsetWidth > 200 : false;
            });

            // Test mobile view
            await this.page.setViewport({ width: 375, height: 667 });
            await this.page.waitForTimeout(1000);
            
            const mobileLayout = await this.page.evaluate(() => {
                const sidebar = document.querySelector('.sidebar');
                return sidebar ? sidebar.offsetWidth < 200 : false;
            });

            // Reset to desktop
            await this.page.setViewport({ width: 1366, height: 768 });

            this.addTestResult('Desktop Layout', desktopLayout, 'Desktop layout works');
            this.addTestResult('Mobile Layout', mobileLayout, 'Mobile layout works');

            await this.takeScreenshot('dashboard_responsive');

        } catch (error) {
            this.addTestResult('Dashboard Responsive', false, error.message);
        }
    }

    async testNavigation() {
        console.log('\n🧭 PHASE 4: NAVIGATION TESTS');
        console.log('-'.repeat(70));

        // Test navigation for each role
        for (const user of this.testData.users) {
            await this.testUserLogin(user);
            await this.testUserNavigation(user);
            await this.testUserLogout(user);
        }
    }

    async testUserNavigation(userData) {
        try {
            console.log(`🧭 Testing ${userData.role} navigation...`);

            // Wait for dashboard to load
            await this.page.waitForTimeout(2000);

            // Get navigation links
            const navLinks = await this.page.$$('.nav-link, .menu-item');
            
            this.addTestResult(`${userData.role} Navigation Links`, navLinks.length > 0, 
                `Found ${navLinks.length} navigation links`);

            // Test each expected page
            for (const page of userData.expectedPages) {
                await this.testPageNavigation(page, userData.role);
            }

        } catch (error) {
            this.addTestResult(`${userData.role} Navigation`, false, error.message);
        }
    }

    async testPageNavigation(pageName, userRole) {
        try {
            console.log(`  📄 Testing ${pageName} page...`);

            // Look for navigation link
            const pageSelector = `a[href="#${pageName}"], .nav-link:contains("${pageName}")`;
            
            // Try to find and click the navigation link
            const navigationSuccess = await this.page.evaluate((targetPage) => {
                const links = document.querySelectorAll('a[href], .nav-link');
                for (const link of links) {
                    if (link.href.includes(targetPage) || link.textContent.includes(targetPage)) {
                        link.click();
                        return true;
                    }
                }
                return false;
            }, pageName);

            if (navigationSuccess) {
                await this.page.waitForTimeout(2000);
                
                // Check if page content loaded
                const pageContent = await this.page.evaluate(() => {
                    const mainContent = document.querySelector('#app-main, .main-content, .content');
                    return mainContent ? mainContent.textContent.trim().length > 0 : false;
                });

                this.addTestResult(`${userRole} ${pageName} Navigation`, pageContent, 
                    pageContent ? 'Page content loaded' : 'Page content not loaded');

                await this.takeScreenshot(`${userRole}_${pageName}_page`);
            } else {
                this.addTestResult(`${userRole} ${pageName} Navigation`, false, 'Navigation link not found');
            }

        } catch (error) {
            this.addTestResult(`${userRole} ${pageName} Navigation`, false, error.message);
        }
    }

    async testSecurity() {
        console.log('\n🔒 PHASE 5: SECURITY TESTS');
        console.log('-'.repeat(70));

        await this.testSecurityHeaders();
        await this.testSessionSecurity();
        await this.testInputValidation();
        await this.testXSSProtection();
    }

    async testSecurityHeaders() {
        try {
            console.log('🛡️ Testing security headers...');

            const response = await this.page.goto(this.baseUrl + '/login.php');
            const headers = response.headers();

            const securityHeaders = [
                'x-frame-options',
                'x-content-type-options',
                'referrer-policy'
            ];

            let headersFound = 0;
            for (const header of securityHeaders) {
                if (headers[header]) {
                    headersFound++;
                }
            }

            this.addTestResult('Security Headers', headersFound >= 2, 
                `Found ${headersFound}/${securityHeaders.length} security headers`);

        } catch (error) {
            this.addTestResult('Security Headers', false, error.message);
        }
    }

    async testSessionSecurity() {
        try {
            console.log('🔐 Testing session security...');

            // Login as user
            await this.testUserLogin({ username: 'admin', password: 'admin' });

            // Check if session is properly set
            const cookies = await this.page.cookies();
            const sessionCookie = cookies.find(cookie => cookie.name.includes('PHPSESSID') || cookie.name.includes('session'));

            this.addTestResult('Session Cookie', !!sessionCookie, 
                sessionCookie ? 'Session cookie found' : 'Session cookie not found');

            // Test session timeout simulation
            await this.page.evaluate(() => {
                // Clear session to simulate timeout
                document.cookie = 'PHPSESSID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            });

            // Try to access protected page
            await this.page.goto(this.baseUrl + '/main.php', { waitUntil: 'networkidle2' });
            const currentUrl = this.page.url();

            if (currentUrl.includes('login.php')) {
                this.addTestResult('Session Timeout', true, 'Properly redirected to login');
            } else {
                this.addTestResult('Session Timeout', false, 'Not redirected after session clear');
            }

        } catch (error) {
            this.addTestResult('Session Security', false, error.message);
        }
    }

    async testInputValidation() {
        try {
            console.log('✅ Testing input validation...');

            await this.page.goto(this.baseUrl + '/login.php');

            // Test SQL injection attempt
            await this.page.type('#username', "' OR '1'='1");
            await this.page.type('#password', "' OR '1'='1");
            await this.page.click('button[type="submit"]');
            await this.page.waitForTimeout(2000);

            const currentUrl = this.page.url();
            
            if (currentUrl.includes('login.php')) {
                this.addTestResult('SQL Injection Protection', true, 'SQL injection blocked');
            } else {
                this.addTestResult('SQL Injection Protection', false, 'SQL injection not blocked');
            }

            // Test XSS attempt
            await this.page.goto(this.baseUrl + '/login.php');
            await this.page.type('#username', '<script>alert("XSS")</script>');
            await this.page.type('#password', '<script>alert("XSS")</script>');
            await this.page.click('button[type="submit"]');
            await this.page.waitForTimeout(2000);

            // Check if alert was triggered (XSS successful)
            let xssTriggered = false;
            this.page.once('dialog', () => {
                xssTriggered = true;
            });

            this.addTestResult('XSS Protection', !xssTriggered, 
                xssTriggered ? 'XSS vulnerability found' : 'XSS protection working');

        } catch (error) {
            this.addTestResult('Input Validation', false, error.message);
        }
    }

    async testXSSProtection() {
        try {
            console.log('🛡️ Testing XSS protection...');

            // Test reflected XSS in URL parameters
            const xssPayload = '<img src=x onerror=console.log("XSS")>';
            await this.page.goto(`${this.baseUrl}/login.php?test=${encodeURIComponent(xssPayload)}`);

            // Check if XSS payload appears in page without being executed
            const pageContent = await this.page.content();
            const xssInContent = pageContent.includes('<img src=x onerror=console.log("XSS")>');

            this.addTestResult('XSS in URL', !xssInContent, 
                xssInContent ? 'XSS payload in content' : 'XSS payload sanitized');

        } catch (error) {
            this.addTestResult('XSS Protection', false, error.message);
        }
    }

    async testPerformance() {
        console.log('\n⚡ PHASE 6: PERFORMANCE TESTS');
        console.log('-'.repeat(70));

        await this.testPageLoadTime();
        await this.testResourceLoading();
        await this.testJavaScriptPerformance();
    }

    async testPageLoadTime() {
        try {
            console.log('⏱️ Testing page load time...');

            const startTime = Date.now();
            await this.page.goto(this.baseUrl + '/login.php', { waitUntil: 'networkidle2' });
            const loadTime = Date.now() - startTime;

            const loadTimeAcceptable = loadTime < 5000; // 5 seconds

            this.addTestResult('Login Page Load Time', loadTimeAcceptable, 
                `Load time: ${loadTime}ms`);

            // Test dashboard load time
            await this.testUserLogin({ username: 'admin', password: 'admin' });
            
            const dashboardStartTime = Date.now();
            await this.page.goto(this.baseUrl + '/main.php', { waitUntil: 'networkidle2' });
            const dashboardLoadTime = Date.now() - dashboardStartTime;

            const dashboardLoadTimeAcceptable = dashboardLoadTime < 3000; // 3 seconds

            this.addTestResult('Dashboard Load Time', dashboardLoadTimeAcceptable, 
                `Dashboard load time: ${dashboardLoadTime}ms`);

        } catch (error) {
            this.addTestResult('Page Load Time', false, error.message);
        }
    }

    async testResourceLoading() {
        try {
            console.log('📦 Testing resource loading...');

            const resources = [];
            this.page.on('response', response => {
                resources.push({
                    url: response.url(),
                    status: response.status(),
                    type: response.request().resourceType()
                });
            });

            await this.page.goto(this.baseUrl + '/login.php', { waitUntil: 'networkidle2' });

            const failedResources = resources.filter(r => r.status >= 400);
            const totalResources = resources.length;

            this.addTestResult('Resource Loading', failedResources.length === 0, 
                `${totalResources} resources, ${failedResources.length} failed`);

            if (failedResources.length > 0) {
                console.log('Failed resources:', failedResources.map(r => r.url));
            }

        } catch (error) {
            this.addTestResult('Resource Loading', false, error.message);
        }
    }

    async testJavaScriptPerformance() {
        try {
            console.log('⚡ Testing JavaScript performance...');

            const jsMetrics = await this.page.evaluate(() => {
                const navigationStart = performance.timing.navigationStart;
                const loadEventEnd = performance.timing.loadEventEnd;
                const domContentLoaded = performance.timing.domContentLoadedEventEnd;
                
                return {
                    domLoadTime: domContentLoaded - navigationStart,
                    pageLoadTime: loadEventEnd - navigationStart,
                    memoryUsage: performance.memory ? performance.memory.usedJSHeapSize : 0
                };
            });

            const domLoadAcceptable = jsMetrics.domLoadTime < 3000;
            const pageLoadAcceptable = jsMetrics.pageLoadTime < 5000;

            this.addTestResult('DOM Load Time', domLoadAcceptable, 
                `DOM load: ${jsMetrics.domLoadTime}ms`);
            this.addTestResult('Page Load Time', pageLoadAcceptable, 
                `Page load: ${jsMetrics.pageLoadTime}ms`);

        } catch (error) {
            this.addTestResult('JavaScript Performance', false, error.message);
        }
    }

    async testAPI() {
        console.log('\n🔌 PHASE 7: API TESTS');
        console.log('-'.repeat(70));

        await this.testLoginAPI();
        await this.testSessionAPI();
        await this.testLogoutAPI();
    }

    async testLoginAPI() {
        try {
            console.log('🔑 Testing login API...');

            const apiResponse = await this.page.evaluate(async () => {
                const response = await fetch('/mono-v2/api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=login&username=admin&password=admin'
                });
                
                return {
                    status: response.status,
                    data: await response.json()
                };
            });

            const loginSuccess = apiResponse.status === 200 && apiResponse.data.success;

            this.addTestResult('Login API', loginSuccess, 
                loginSuccess ? 'Login API working' : `Error: ${apiResponse.data.message}`);

        } catch (error) {
            this.addTestResult('Login API', false, error.message);
        }
    }

    async testSessionAPI() {
        try {
            console.log('🔍 Testing session API...');

            const sessionResponse = await this.page.evaluate(async () => {
                const response = await fetch('/mono-v2/api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=check_session'
                });
                
                return {
                    status: response.status,
                    data: await response.json()
                };
            });

            const sessionWorking = sessionResponse.status === 200;

            this.addTestResult('Session API', sessionWorking, 
                sessionWorking ? 'Session API working' : `Error: ${sessionResponse.data.message}`);

        } catch (error) {
            this.addTestResult('Session API', false, error.message);
        }
    }

    async testLogoutAPI() {
        try {
            console.log('🚪 Testing logout API...');

            const logoutResponse = await this.page.evaluate(async () => {
                const response = await fetch('/mono-v2/api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=logout'
                });
                
                return {
                    status: response.status,
                    data: await response.json()
                };
            });

            const logoutWorking = logoutResponse.status === 200 && logoutResponse.data.success;

            this.addTestResult('Logout API', logoutWorking, 
                logoutWorking ? 'Logout API working' : `Error: ${logoutResponse.data.message}`);

        } catch (error) {
            this.addTestResult('Logout API', false, error.message);
        }
    }

    async takeScreenshot(name) {
        try {
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            const filename = `${name}_${timestamp}.png`;
            const path = `${this.screenshotDir}/${filename}`;
            
            await this.page.screenshot({ path, fullPage: true });
            console.log(`📸 Screenshot saved: ${path}`);
        } catch (error) {
            console.log(`❌ Failed to take screenshot: ${error.message}`);
        }
    }

    addTestResult(testName, passed, details, type = 'info') {
        const result = {
            test: testName,
            passed: passed,
            details: details,
            type: type,
            timestamp: new Date().toISOString()
        };
        
        this.testResults.push(result);
        
        const status = passed ? '✅' : '❌';
        const icon = type === 'error' ? '🚨' : type === 'critical' ? '💥' : '📋';
        console.log(`${icon} ${status} ${testName}: ${details}`);
    }

    async generateReport() {
        console.log('\n📊 GENERATING COMPREHENSIVE TEST REPORT');
        console.log('='.repeat(70));

        const totalTests = this.testResults.length;
        const passedTests = this.testResults.filter(r => r.passed).length;
        const failedTests = this.testResults.filter(r => !r.passed).length;
        const successRate = ((passedTests / totalTests) * 100).toFixed(1);

        console.log(`\n📈 TEST SUMMARY:`);
        console.log(`Total Tests: ${totalTests}`);
        console.log(`Passed: ${passedTests}`);
        console.log(`Failed: ${failedTests}`);
        console.log(`Success Rate: ${successRate}%`);

        // Generate detailed report
        const report = {
            summary: {
                total: totalTests,
                passed: passedTests,
                failed: failedTests,
                successRate: successRate,
                timestamp: new Date().toISOString()
            },
            phases: {
                'Basic Application': this.testResults.filter(r => 
                    ['Application Accessibility', 'Login Page Elements', 'JavaScript Enabled', 
                     'Empty Form Validation', 'Password Toggle', 'CSS Files Loaded', 
                     'JavaScript Files Loaded', 'Images Loaded', 'Bootstrap Loaded'].some(name => 
                        r.test.includes(name))),
                'Authentication': this.testResults.filter(r => r.test.includes('Login') || r.test.includes('Logout')),
                'Dashboard': this.testResults.filter(r => r.test.includes('Dashboard')),
                'Navigation': this.testResults.filter(r => r.test.includes('Navigation')),
                'Security': this.testResults.filter(r => 
                    ['Security Headers', 'Session', 'SQL Injection', 'XSS'].some(name => r.test.includes(name))),
                'Performance': this.testResults.filter(r => 
                    ['Load Time', 'Resource Loading', 'Performance'].some(name => r.test.includes(name))),
                'API': this.testResults.filter(r => r.test.includes('API'))
            },
            details: this.testResults,
            recommendations: this.generateRecommendations()
        };

        // Save report to file
        const reportPath = './comprehensive-test-report.json';
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        
        // Generate markdown report
        const markdownReport = this.generateMarkdownReport(report);
        const markdownPath = './COMPREHENSIVE_TEST_REPORT.md';
        fs.writeFileSync(markdownPath, markdownReport);

        console.log(`\n📄 Reports generated:`);
        console.log(`JSON: ${reportPath}`);
        console.log(`Markdown: ${markdownPath}`);
        console.log(`Screenshots: ${this.screenshotDir}/`);

        // Show critical issues
        const criticalIssues = this.testResults.filter(r => r.type === 'critical' && !r.passed);
        if (criticalIssues.length > 0) {
            console.log(`\n🚨 CRITICAL ISSUES (${criticalIssues.length}):`);
            criticalIssues.forEach(issue => {
                console.log(`   • ${issue.test}: ${issue.details}`);
            });
        }

        // Show failed tests
        if (failedTests > 0) {
            console.log(`\n❌ FAILED TESTS (${failedTests}):`);
            this.testResults.filter(r => !r.passed).forEach(test => {
                console.log(`   • ${test.test}: ${test.details}`);
            });
        }
    }

    generateRecommendations() {
        const recommendations = [];
        
        const failedTests = this.testResults.filter(r => !r.passed);
        
        if (failedTests.some(t => t.test.includes('Application Accessibility'))) {
            recommendations.push('Check server configuration and ensure Apache/Nginx is running properly');
        }
        
        if (failedTests.some(t => t.test.includes('Login'))) {
            recommendations.push('Review authentication logic and database connectivity');
        }
        
        if (failedTests.some(t => t.test.includes('Dashboard'))) {
            recommendations.push('Check dashboard JavaScript and ensure all dependencies are loaded');
        }
        
        if (failedTests.some(t => t.test.includes('Security'))) {
            recommendations.push('Implement proper security headers and input validation');
        }
        
        if (failedTests.some(t => t.test.includes('Performance'))) {
            recommendations.push('Optimize resource loading and implement caching');
        }
        
        if (failedTests.some(t => t.test.includes('API'))) {
            recommendations.push('Review API endpoints and ensure proper error handling');
        }
        
        if (recommendations.length === 0) {
            recommendations.push('All tests passed! Application is working correctly.');
        }
        
        return recommendations;
    }

    generateMarkdownReport(report) {
        const { summary, phases, details, recommendations } = report;
        
        let markdown = `# 🧪 Comprehensive Test Report - KSP Lam Gabe Jaya

## 📊 Test Summary

- **Total Tests**: ${summary.total}
- **Passed**: ${summary.passed}
- **Failed**: ${summary.failed}
- **Success Rate**: ${summary.successRate}%
- **Timestamp**: ${new Date(summary.timestamp).toLocaleString()}

## 🎯 Test Results by Phase

`;

        // Add phase results
        Object.entries(phases).forEach(([phaseName, tests]) => {
            const phasePassed = tests.filter(t => t.passed).length;
            const phaseTotal = tests.length;
            const phaseRate = phaseTotal > 0 ? ((phasePassed / phaseTotal) * 100).toFixed(1) : 0;
            
            markdown += `### ${phaseName}
- **Passed**: ${phasePassed}/${phaseTotal} (${phaseRate}%)
`;

            if (tests.length > 0) {
                markdown += `
| Test | Status | Details |
|------|--------|---------|
`;
                tests.forEach(test => {
                    const status = test.passed ? '✅' : '❌';
                    markdown += `| ${test.test} | ${status} | ${test.details} |\n`;
                });
            }
            markdown += '\n';
        });

        // Add recommendations
        markdown += `## 🔧 Recommendations

`;
        recommendations.forEach(rec => {
            markdown += `- ${rec}\n`;
        });

        // Add detailed results
        markdown += `
## 📋 Detailed Test Results

| Test | Status | Type | Details |
|------|--------|------|---------|
`;
        details.forEach(test => {
            const status = test.passed ? '✅' : '❌';
            markdown += `| ${test.test} | ${status} | ${test.type} | ${test.details} |\n`;
        });

        markdown += `
## 📸 Screenshots

All test screenshots are saved in the \`./screenshots/\` directory.

## 🚀 Next Steps

1. Fix all failed tests
2. Address critical issues immediately
3. Optimize performance issues
4. Enhance security measures
5. Implement missing features

---

*Report generated on ${new Date(summary.timestamp).toLocaleString()}*
`;

        return markdown;
    }

    async cleanup() {
        if (this.browser) {
            await this.browser.close();
        }
    }
}

// Run the comprehensive test suite
async function runComprehensiveTests() {
    const testSuite = new ComprehensiveTestSuite();
    await testSuite.runAllTests();
}

// Execute tests
runComprehensiveTests().catch(console.error);
