const puppeteer = require('puppeteer');

/**
 * Simple Puppeteer Test for Debugging
 */
async function runSimpleTest() {
    console.log('🚀 Starting Simple Puppeteer Test');
    
    const browser = await puppeteer.launch({ 
        headless: false, // Show browser for debugging
        slowMo: 100 // Slow down for debugging
    });
    
    const page = await browser.newPage();
    
    try {
        // Test 1: Go to login page
        console.log('📄 Testing login page...');
        await page.goto('http://localhost/mono-v2/login.php', { waitUntil: 'networkidle2' });
        
        // Take screenshot
        await page.screenshot({ path: 'debug-login-page.png' });
        
        // Check if login form exists
        const usernameField = await page.$('input[name="username"]');
        const passwordField = await page.$('input[name="password"]');
        const submitButton = await page.$('button[type="submit"]');
        
        console.log('Username field:', usernameField ? '✅ Found' : '❌ Not found');
        console.log('Password field:', passwordField ? '✅ Found' : '❌ Not found');
        console.log('Submit button:', submitButton ? '✅ Found' : '❌ Not found');
        
        if (usernameField && passwordField && submitButton) {
            // Test 2: Try login
            console.log('🔐 Testing login...');
            await usernameField.type('bos', { delay: 100 });
            await passwordField.type('bos', { delay: 100 });
            
            // Wait for navigation
            await Promise.all([
                page.waitForNavigation({ waitUntil: 'networkidle2' }),
                submitButton.click()
            ]);
            
            // Take screenshot after login
            await page.screenshot({ path: 'debug-after-login.png' });
            
            // Check URL
            const url = page.url();
            console.log('URL after login:', url);
            
            // Check for dashboard elements
            const dashboard = await page.$('.dashboard-header');
            console.log('Dashboard header:', dashboard ? '✅ Found' : '❌ Not found');
            
            // Check for JavaScript errors
            const errors = await page.evaluate(() => {
                const errors = [];
                window.addEventListener('error', (e) => {
                    errors.push(e.message);
                });
                return errors;
            });
            
            if (errors.length > 0) {
                console.log('JavaScript errors:', errors);
            } else {
                console.log('✅ No JavaScript errors detected');
            }
            
        } else {
            console.log('❌ Login form elements missing');
        }
        
    } catch (error) {
        console.error('❌ Test failed:', error.message);
        
        // Take screenshot on error
        try {
            await page.screenshot({ path: 'debug-error.png' });
        } catch (screenshotError) {
            console.log('Could not take screenshot:', screenshotError.message);
        }
    }
    
    await browser.close();
    console.log('🏁 Simple test completed');
}

runSimpleTest().catch(console.error);
