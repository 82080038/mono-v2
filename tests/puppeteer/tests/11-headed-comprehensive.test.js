const { describe, test, expect, beforeAll } = require('@jest/globals');
const puppeteer = require('puppeteer');

const BASE_URL = 'http://localhost/mono-v2';
const MOCK_USER = {
    token: 'test_token_123456',
    name: 'Test User',
    role: 'admin'
};

describe('11. Comprehensive Headed Mode Tests', () => {
    let browser;

    beforeAll(async () => {
        browser = await puppeteer.launch({
            headless: false,
            args: ['--no-sandbox', '--disable-setuid-sandbox', '--start-maximized']
        });
    });

    afterAll(async () => {
        await browser.close();
    });

    test('11.1 - Admin pages load with sidebar and no errors', async () => {
        const page = await browser.newPage();
        await page.evaluateOnNewDocument(userData => {
            localStorage.setItem('userData', JSON.stringify(userData));
        }, MOCK_USER);

        const adminPages = [
            'dashboard.php',
            'members.php',
            'loans.php',
            'savings.php',
            'users.php',
            'accounting.php',
            'analytics.php',
            'reports.php',
            'live-tracking.php'
        ];

        for (const pg of adminPages) {
            const url = `${BASE_URL}/pages/admin/${pg}`;
            console.log(`Testing admin: ${pg}`);
            
            const response = await page.goto(url, { waitUntil: 'networkidle2' });
            expect(response.status()).toBe(200);

            // Check for sidebar
            const sidebar = await page.$('#sidebar');
            expect(sidebar).toBeTruthy();

            // Check for no console errors
            const errors = await page.evaluate(() => {
                const errors = [];
                window.addEventListener('error', e => errors.push(e.message));
                return errors;
            });
            expect(errors).toHaveLength(0);

            // Check page title
            const title = await page.title();
            expect(title).toContain('KSP Lam Gabe Jaya');
        }

        await page.close();
    });

    test('11.2 - Member pages load with auth guard and no errors', async () => {
        const page = await browser.newPage();
        await page.evaluateOnNewDocument(userData => {
            localStorage.setItem('userData', JSON.stringify(userData));
        }, { ...MOCK_USER, role: 'member' });

        const memberPages = [
            'dashboard.html',
            'simpanan-saya.html',
            'ajukan-pinjaman.html',
            'pinjaman-saya.html',
            'riwayat.html',
            'buku-kas.html',
            'pembayaran.html',
            'poin-reward.html'
        ];

        for (const pg of memberPages) {
            const url = `${BASE_URL}/pages/member/${pg}`;
            console.log(`Testing member: ${pg}`);
            
            const response = await page.goto(url, { waitUntil: 'networkidle2' });
            expect(response.status()).toBe(200);

            // Check for no console errors
            const errors = await page.evaluate(() => {
                const errors = [];
                window.addEventListener('error', e => errors.push(e.message));
                return errors;
            });
            expect(errors).toHaveLength(0);
        }

        await page.close();
    });

    test('11.3 - Staff pages load with auth guard and no errors', async () => {
        const page = await browser.newPage();
        await page.evaluateOnNewDocument(userData => {
            localStorage.setItem('userData', JSON.stringify(userData));
        }, { ...MOCK_USER, role: 'staff' });

        const staffPages = [
            'dashboard.html',
            'loans.html',
            'savings.html',
            'members.html',
            'transaksi-harian.html',
            'target-harian.html'
        ];

        for (const pg of staffPages) {
            const url = `${BASE_URL}/pages/staff/${pg}`;
            console.log(`Testing staff: ${pg}`);
            
            const response = await page.goto(url, { waitUntil: 'networkidle2' });
            expect(response.status()).toBe(200);

            // Check for no console errors
            const errors = await page.evaluate(() => {
                const errors = [];
                window.addEventListener('error', e => errors.push(e.message));
                return errors;
            });
            expect(errors).toHaveLength(0);
        }

        await page.close();
    });

    test('11.4 - Login page loads correctly', async () => {
        const page = await browser.newPage();
        const url = `${BASE_URL}/login.html`;
        
        const response = await page.goto(url, { waitUntil: 'networkidle2' });
        expect(response.status()).toBe(200);

        // Check for login form
        const loginForm = await page.$('form');
        expect(loginForm).toBeTruthy();

        // Check for no console errors
        const errors = await page.evaluate(() => {
            const errors = [];
            window.addEventListener('error', e => errors.push(e.message));
            return errors;
        });
        expect(errors).toHaveLength(0);

        await page.close();
    });

    test('11.5 - CSS files load correctly', async () => {
        const page = await browser.newPage();
        await page.evaluateOnNewDocument(userData => {
            localStorage.setItem('userData', JSON.stringify(userData));
        }, MOCK_USER);

        const url = `${BASE_URL}/pages/admin/dashboard.php`;
        await page.goto(url, { waitUntil: 'networkidle2' });

        // Check CSS files
        const cssLinks = await page.$$eval('link[rel="stylesheet"]', links => 
            links.map(l => l.href).filter(h => h.includes('css'))
        );
        expect(cssLinks.length).toBeGreaterThan(0);

        await page.close();
    });

    test('11.6 - JavaScript files load correctly', async () => {
        const page = await browser.newPage();
        await page.evaluateOnNewDocument(userData => {
            localStorage.setItem('userData', JSON.stringify(userData));
        }, MOCK_USER);

        const url = `${BASE_URL}/pages/admin/dashboard.php`;
        await page.goto(url, { waitUntil: 'networkidle2' });

        // Check JS files
        const jsScripts = await page.$$eval('script[src]', scripts =>
            scripts.map(s => s.src).filter(s => s.includes('.js'))
        );
        expect(jsScripts.length).toBeGreaterThan(0);

        await page.close();
    });
});
