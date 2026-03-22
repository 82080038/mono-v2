/**
 * Simple SPA Test - No Puppeteer Required
 * Tests SPA functionality using curl and basic checks
 */
const fs = require('fs');
const { execSync } = require('child_process');

class SimpleSPATest {
    constructor() {
        this.baseUrl = 'http://localhost/mono-v2/pages/admin/dashboard.html';
        this.testResults = [];
    }

    async runAllTests() {
        console.log('🚀 Starting Simple SPA Dashboard Test...\n');
        
        try {
            // Test 1: File Structure
            await this.testFileStructure();
            
            // Test 2: HTTP Status
            await this.testHTTPStatus();
            
            // Test 3: Content Structure
            await this.testContentStructure();
            
            // Test 4: SPA Components
            await this.testSPAComponents();
            
            // Test 5: Assets Loading
            await this.testAssetsLoading();
            
            // Generate report
            this.generateTestReport();
            
        } catch (error) {
            console.error('Test failed:', error);
        }
    }

    async testFileStructure() {
        console.log('📁 Test 1: File Structure');
        
        const files = [
            'pages/admin/dashboard.html',
            'assets/js/content-renderer.js',
            'assets/css/dashboard-layout.css',
            'assets/css/dashboard.css',
            'assets/js/indonesian-translator.js'
        ];
        
        let allFilesExist = true;
        const fileDetails = {};
        
        files.forEach(file => {
            const exists = fs.existsSync(file);
            fileDetails[file] = exists;
            if (!exists) allFilesExist = false;
        });
        
        this.addTestResult('File Structure', {
            passed: allFilesExist,
            details: fileDetails
        });
        
        console.log('   ✅ Checking required files...');
        files.forEach(file => {
            const status = fileDetails[file] ? '✅' : '❌';
            console.log(`   ${status} ${file}`);
        });
        console.log('');
    }

    async testHTTPStatus() {
        console.log('🌐 Test 2: HTTP Status');
        
        const urls = [
            'http://localhost/mono-v2/pages/admin/dashboard.html',
            'http://localhost/mono-v2/assets/js/content-renderer.js',
            'http://localhost/mono-v2/assets/css/dashboard-layout.css',
            'http://localhost/mono-v2/assets/css/dashboard.css'
        ];
        
        let allStatusOK = true;
        const statusDetails = {};
        
        urls.forEach(url => {
            try {
                const response = execSync(`curl -I -s -w "%{http_code}" "${url}"`, { 
                    encoding: 'utf8',
                    timeout: 5000
                });
                const statusCode = response.trim().split('\n').pop();
                const isOK = statusCode === '200';
                statusDetails[url] = { statusCode, isOK };
                if (!isOK) allStatusOK = false;
            } catch (error) {
                statusDetails[url] = { error: error.message, isOK: false };
                allStatusOK = false;
            }
        });
        
        this.addTestResult('HTTP Status', {
            passed: allStatusOK,
            details: statusDetails
        });
        
        console.log('   ✅ Checking HTTP status...');
        Object.entries(statusDetails).forEach(([url, detail]) => {
            const status = detail.isOK ? '✅' : '❌';
            const code = detail.statusCode || 'ERROR';
            console.log(`   ${status} ${url} - ${code}`);
        });
        console.log('');
    }

    async testContentStructure() {
        console.log('📄 Test 3: Content Structure');
        
        try {
            // Read dashboard HTML
            const dashboardContent = fs.readFileSync('pages/admin/dashboard.html', 'utf8');
            
            // Check for SPA elements
            const spaElements = {
                'sidebar': /<aside.*id="sidebar"/.test(dashboardContent),
                'dashboard-main': /<main.*class="dashboard-main"/.test(dashboardContent),
                'dynamic-content': /<div.*id="dashboard-main"/.test(dashboardContent),
                'content-renderer': /content-renderer\.js/.test(dashboardContent),
                'navigation-links': /<a.*href=".*\.html"/.test(dashboardContent),
                'bootstrap': /bootstrap/.test(dashboardContent),
                'font-awesome': /font-awesome/.test(dashboardContent)
            };
            
            const allElementsPresent = Object.values(spaElements).every(Boolean);
            
            this.addTestResult('Content Structure', {
                passed: allElementsPresent,
                details: spaElements
            });
            
            console.log('   ✅ Checking SPA elements...');
            Object.entries(spaElements).forEach(([element, present]) => {
                const status = present ? '✅' : '❌';
                console.log(`   ${status} ${element}`);
            });
            console.log('');
            
        } catch (error) {
            this.addTestResult('Content Structure', {
                passed: false,
                error: error.message
            });
            console.log(`   ❌ Error reading dashboard.html: ${error.message}\n`);
        }
    }

    async testSPAComponents() {
        console.log('🧩 Test 4: SPA Components');
        
        try {
            // Read content renderer
            const rendererContent = fs.readFileSync('assets/js/content-renderer.js', 'utf8');
            
            // Check for SPA functionality
            const spaFunctions = {
                'ContentRenderer class': /class ContentRenderer/.test(rendererContent),
                'setupNavigation': /setupNavigation/.test(rendererContent),
                'loadContent': /loadContent/.test(rendererContent),
                'renderContent': /renderContent/.test(rendererContent),
                'updateActiveNav': /updateActiveNav/.test(rendererContent),
                'getPageContent': /getPageContent/.test(rendererContent),
                'getDashboardContent': /getDashboardContent/.test(rendererContent),
                'getMembersContent': /getMembersContent/.test(rendererContent),
                'getNPLContent': /getNPLContent/.test(rendererContent),
                'DOMContentLoaded listener': /DOMContentLoaded/.test(rendererContent),
                'navigation interception': /preventDefault/.test(rendererContent)
            };
            
            const allFunctionsPresent = Object.values(spaFunctions).every(Boolean);
            
            this.addTestResult('SPA Components', {
                passed: allFunctionsPresent,
                details: spaFunctions
            });
            
            console.log('   ✅ Checking SPA functions...');
            Object.entries(spaFunctions).forEach(([func, present]) => {
                const status = present ? '✅' : '❌';
                console.log(`   ${status} ${func}`);
            });
            console.log('');
            
        } catch (error) {
            this.addTestResult('SPA Components', {
                passed: false,
                error: error.message
            });
            console.log(`   ❌ Error reading content-renderer.js: ${error.message}\n`);
        }
    }

    async testAssetsLoading() {
        console.log('📦 Test 5: Assets Loading');
        
        // Check CSS files
        const cssFiles = [
            'assets/css/dashboard-layout.css',
            'assets/css/dashboard.css'
        ];
        
        // Check JS files
        const jsFiles = [
            'assets/js/content-renderer.js',
            'assets/js/indonesian-translator.js'
        ];
        
        const assetDetails = {};
        let allAssetsOK = true;
        
        // Test CSS files
        cssFiles.forEach(file => {
            try {
                const content = fs.readFileSync(file, 'utf8');
                const hasContent = content.length > 100;
                assetDetails[file] = { 
                    type: 'css', 
                    size: content.length, 
                    hasContent,
                    isOK: hasContent
                };
                if (!hasContent) allAssetsOK = false;
            } catch (error) {
                assetDetails[file] = { type: 'css', error: error.message, isOK: false };
                allAssetsOK = false;
            }
        });
        
        // Test JS files
        jsFiles.forEach(file => {
            try {
                const content = fs.readFileSync(file, 'utf8');
                const hasContent = content.length > 500;
                assetDetails[file] = { 
                    type: 'js', 
                    size: content.length, 
                    hasContent,
                    isOK: hasContent
                };
                if (!hasContent) allAssetsOK = false;
            } catch (error) {
                assetDetails[file] = { type: 'js', error: error.message, isOK: false };
                allAssetsOK = false;
            }
        });
        
        this.addTestResult('Assets Loading', {
            passed: allAssetsOK,
            details: assetDetails
        });
        
        console.log('   ✅ Checking assets...');
        Object.entries(assetDetails).forEach(([file, detail]) => {
            const status = detail.isOK ? '✅' : '❌';
            const size = detail.size ? ` (${detail.size} bytes)` : '';
            console.log(`   ${status} ${file}${size}`);
        });
        console.log('');
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
                    } else if (typeof value === 'object' && value.isOK !== undefined) {
                        const status = value.isOK ? '✅' : '❌';
                        const extra = value.size ? ` (${value.size} bytes)` : value.statusCode ? ` (${value.statusCode})` : '';
                        console.log(`   ${key}: ${status}${extra}`);
                    } else {
                        console.log(`   ${key}: ${JSON.stringify(value)}`);
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
        
        fs.writeFileSync('simple-spa-test-report.json', JSON.stringify(reportData, null, 2));
        
        console.log('📄 Detailed report saved to: simple-spa-test-report.json');
        
        if (failedTests === 0) {
            console.log('🎉 All tests passed! SPA Dashboard structure is correct.');
            console.log('\n📋 Next Steps:');
            console.log('1. Open dashboard in browser: http://localhost/mono-v2/pages/admin/dashboard.html');
            console.log('2. Set authentication tokens in localStorage');
            console.log('3. Test navigation between pages');
            console.log('4. Verify content loads without page refresh');
        } else {
            console.log(`⚠️  ${failedTests} test(s) failed. Please check the issues above.`);
        }
    }
}

// Run tests
if (require.main === module) {
    const tester = new SimpleSPATest();
    tester.runAllTests().catch(console.error);
}

module.exports = SimpleSPATest;
