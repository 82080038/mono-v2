/**
 * Test Suite 04 — Responsive & Aksesibilitas
 * Menguji tampilan di berbagai ukuran layar
 */

const { newPage, closeBrowser } = require('../helpers/browser');
const { BASE_URL } = require('../helpers/api');

const LOGIN_URL  = `${BASE_URL}/login.html`;
const DASH_ADMIN = `${BASE_URL}/pages/admin/dashboard.html`;

const VIEWPORTS = {
    desktop:  { width: 1280, height: 720,  label: 'Desktop (1280×720)' },
    tablet:   { width: 768,  height: 1024, label: 'Tablet (768×1024)' },
    mobile:   { width: 375,  height: 812,  label: 'Mobile (375×812)' }
};

afterAll(async () => {
    await closeBrowser();
});

// ─────────────────────────────────────────────
// KELOMPOK: Responsive Login Page
// ─────────────────────────────────────────────

describe('04.1 — Login Page Responsive', () => {
    for (const [key, vp] of Object.entries(VIEWPORTS)) {
        test(`Login page tampil di ${vp.label}`, async () => {
            const page = await newPage();
            await page.setViewport({ width: vp.width, height: vp.height });
            await page.goto(LOGIN_URL, { waitUntil: 'domcontentloaded' });

            const formVisible = await page.$eval('#loginForm', el => {
                const rect = el.getBoundingClientRect();
                return rect.width > 0 && rect.height > 0;
            });
            expect(formVisible).toBe(true);
            await page.close();
        });

        test(`Input username terlihat di ${vp.label}`, async () => {
            const page = await newPage();
            await page.setViewport({ width: vp.width, height: vp.height });
            await page.goto(LOGIN_URL, { waitUntil: 'domcontentloaded' });

            const visible = await page.$eval('#emailInput', el => {
                const rect = el.getBoundingClientRect();
                return rect.width > 0;
            });
            expect(visible).toBe(true);
            await page.close();
        });
    }
});

// ─────────────────────────────────────────────
// KELOMPOK: Responsive Dashboard
// ─────────────────────────────────────────────

describe('04.2 — Dashboard Responsive', () => {
    for (const [key, vp] of Object.entries(VIEWPORTS)) {
        test(`Dashboard dimuat di ${vp.label}`, async () => {
            const page = await newPage();
            await page.setViewport({ width: vp.width, height: vp.height });
            const res = await page.goto(DASH_ADMIN, { waitUntil: 'domcontentloaded' });
            expect(res.status()).toBe(200);
            await page.close();
        });
    }
});

// ─────────────────────────────────────────────
// KELOMPOK: Performa
// ─────────────────────────────────────────────

describe('04.3 — Performa Halaman', () => {
    test('Login page dimuat dalam < 5 detik', async () => {
        const page = await newPage();
        const start = Date.now();
        await page.goto(LOGIN_URL, { waitUntil: 'domcontentloaded' });
        const duration = Date.now() - start;
        expect(duration).toBeLessThan(5000);
        await page.close();
    });

    test('Admin dashboard dimuat dalam < 5 detik', async () => {
        const page = await newPage();
        const start = Date.now();
        await page.goto(DASH_ADMIN, { waitUntil: 'domcontentloaded' });
        const duration = Date.now() - start;
        expect(duration).toBeLessThan(5000);
        await page.close();
    });

    test('Login page tidak memiliki JS error kritis', async () => {
        const page = await newPage();
        const errors = [];
        page.on('pageerror', err => errors.push(err.message));
        await page.goto(LOGIN_URL, { waitUntil: 'domcontentloaded' });
        await new Promise(r => setTimeout(r, 1000));

        // Filter error kritis (bukan CDN network errors)
        const criticalErrors = errors.filter(e =>
            !e.includes('net::ERR') &&
            !e.includes('Failed to load resource') &&
            !e.includes('favicon')
        );
        expect(criticalErrors.length).toBe(0);
        await page.close();
    });
});

// ─────────────────────────────────────────────
// KELOMPOK: Aksesibilitas Dasar
// ─────────────────────────────────────────────

describe('04.4 — Aksesibilitas Dasar', () => {
    test('Login page memiliki tag <title>', async () => {
        const page = await newPage();
        await page.goto(LOGIN_URL, { waitUntil: 'domcontentloaded' });
        const title = await page.title();
        expect(title.length).toBeGreaterThan(0);
        await page.close();
    });

    test('Form login memiliki label untuk input', async () => {
        const page = await newPage();
        await page.goto(LOGIN_URL, { waitUntil: 'domcontentloaded' });
        const labelCount = await page.$$eval('label', labels => labels.length);
        expect(labelCount).toBeGreaterThan(0);
        await page.close();
    });

    test('Tombol login memiliki teks yang jelas', async () => {
        const page = await newPage();
        await page.goto(LOGIN_URL, { waitUntil: 'domcontentloaded' });
        const btnText = await page.$eval('#loginBtn', el => el.innerText.trim());
        expect(btnText.length).toBeGreaterThan(0);
        await page.close();
    });

    test('Halaman memiliki meta charset UTF-8', async () => {
        const page = await newPage();
        await page.goto(LOGIN_URL, { waitUntil: 'domcontentloaded' });
        const charset = await page.$eval('meta[charset]', el => el.getAttribute('charset')).catch(() => null);
        expect(charset).toMatch(/utf-8/i);
        await page.close();
    });

    test('Halaman memiliki meta viewport untuk mobile', async () => {
        const page = await newPage();
        await page.goto(LOGIN_URL, { waitUntil: 'domcontentloaded' });
        const viewport = await page.$eval('meta[name="viewport"]', el => el.getAttribute('content')).catch(() => null);
        expect(viewport).toBeTruthy();
        await page.close();
    });
});
