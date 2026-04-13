/**
 * Test Suite 03 — Dashboard & Navigasi
 * Menguji halaman dashboard setelah login berhasil
 */

const { newPage, closeBrowser } = require('../helpers/browser');
const { BASE_URL } = require('../helpers/api');

const LOGIN_URL    = `${BASE_URL}/login.html`;
const DASH_ADMIN   = `${BASE_URL}/pages/admin/dashboard.html`;
const DASH_TELLER  = `${BASE_URL}/pages/staff/dashboard-teller-complete.html`;
const MEMBERS_PAGE = `${BASE_URL}/pages/admin/members.html`;
const LOANS_PAGE   = `${BASE_URL}/pages/admin/loan-management.html`;

let page;

/**
 * Helper: login via UI dan tunggu redirect
 */
async function loginAndWaitForDashboard() {
    const p = await newPage();
    await p.goto(LOGIN_URL, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await p.type('#emailInput', 'admin');
    await p.type('#passwordInput', 'password');
    await p.click('#loginBtn');
    await p.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 10000 }).catch(() => {});
    await new Promise(r => setTimeout(r, 500));
    return p;
}

afterAll(async () => {
    await closeBrowser();
});

// ─────────────────────────────────────────────
// KELOMPOK: Admin Dashboard
// ─────────────────────────────────────────────

describe('03.1 — Admin Dashboard', () => {
    test('Halaman admin dashboard dapat dimuat (HTTP 200)', async () => {
        const response = await (await newPage()).goto(DASH_ADMIN, { waitUntil: 'domcontentloaded' });
        expect(response.status()).toBe(200);
    });

    test('Judul halaman admin dashboard ada', async () => {
        page = await newPage();
        await page.goto(DASH_ADMIN, { waitUntil: 'domcontentloaded' });
        const title = await page.title();
        expect(title).toBeTruthy();
        await page.close();
    });

    test('Dashboard menampilkan konten utama', async () => {
        page = await newPage();
        await page.goto(DASH_ADMIN, { waitUntil: 'domcontentloaded' });
        const body = await page.$('body');
        expect(body).not.toBeNull();
        const content = await page.$eval('body', el => el.innerText.length);
        expect(content).toBeGreaterThan(50);
        await page.close();
    });

    test('Terdapat elemen kartu/card statistik di dashboard', async () => {
        page = await newPage();
        await page.goto(DASH_ADMIN, { waitUntil: 'domcontentloaded' });
        const cards = await page.$$('.card');
        expect(cards.length).toBeGreaterThan(0);
        await page.close();
    });

    test('Terdapat navigasi/menu di dashboard', async () => {
        page = await newPage();
        await page.goto(DASH_ADMIN, { waitUntil: 'domcontentloaded' });
        const nav = await page.$('nav, .navbar, .sidebar, aside');
        expect(nav).not.toBeNull();
        await page.close();
    });

    test('Terdapat teks "KSP" atau nama aplikasi di halaman', async () => {
        page = await newPage();
        await page.goto(DASH_ADMIN, { waitUntil: 'domcontentloaded' });
        const text = await page.$eval('body', el => el.innerText);
        expect(text).toMatch(/KSP|Koperasi|Lam Gabe/i);
        await page.close();
    });
});

// ─────────────────────────────────────────────
// KELOMPOK: Halaman Admin lainnya
// ─────────────────────────────────────────────

describe('03.2 — Halaman Admin Lainnya', () => {
    test('Halaman members.html dapat dimuat', async () => {
        page = await newPage();
        const res = await page.goto(MEMBERS_PAGE, { waitUntil: 'domcontentloaded' });
        expect(res.status()).toBe(200);
        await page.close();
    });

    test('Halaman loan-management.html dapat dimuat', async () => {
        page = await newPage();
        const res = await page.goto(LOANS_PAGE, { waitUntil: 'domcontentloaded' });
        expect(res.status()).toBe(200);
        await page.close();
    });

    test('Halaman member-registration.html dapat dimuat', async () => {
        page = await newPage();
        const res = await page.goto(`${BASE_URL}/pages/admin/member-registration.html`, { waitUntil: 'domcontentloaded' });
        expect(res.status()).toBe(200);
        await page.close();
    });

    test('Halaman savings-management.html dapat dimuat', async () => {
        page = await newPage();
        const res = await page.goto(`${BASE_URL}/pages/admin/savings-management.html`, { waitUntil: 'domcontentloaded' });
        expect(res.status()).toBe(200);
        await page.close();
    });

    test('Halaman reports.html dapat dimuat', async () => {
        page = await newPage();
        const res = await page.goto(`${BASE_URL}/pages/admin/reports.html`, { waitUntil: 'domcontentloaded' });
        expect(res.status()).toBe(200);
        await page.close();
    });
});

// ─────────────────────────────────────────────
// KELOMPOK: Staff Dashboard
// ─────────────────────────────────────────────

describe('03.3 — Staff Dashboard', () => {
    test('Halaman dashboard teller dapat dimuat', async () => {
        page = await newPage();
        const res = await page.goto(DASH_TELLER, { waitUntil: 'domcontentloaded' });
        expect(res.status()).toBe(200);
        await page.close();
    });

    test('Halaman dashboard-complete.html dapat dimuat', async () => {
        page = await newPage();
        const res = await page.goto(`${BASE_URL}/pages/staff/dashboard-complete.html`, { waitUntil: 'domcontentloaded' });
        expect(res.status()).toBe(200);
        await page.close();
    });

    test('Halaman dashboard-mantri.html dapat dimuat', async () => {
        page = await newPage();
        const res = await page.goto(`${BASE_URL}/pages/staff/dashboard-mantri.html`, { waitUntil: 'domcontentloaded' });
        expect(res.status()).toBe(200);
        await page.close();
    });

    test('Halaman dashboard-kasir.html dapat dimuat', async () => {
        page = await newPage();
        const res = await page.goto(`${BASE_URL}/pages/staff/dashboard-kasir.html`, { waitUntil: 'domcontentloaded' });
        expect(res.status()).toBe(200);
        await page.close();
    });
});

// ─────────────────────────────────────────────
// KELOMPOK: Flow Login → Dashboard
// ─────────────────────────────────────────────

describe('03.4 — Flow Login ke Dashboard', () => {
    test('Setelah login berhasil, URL mengandung "dashboard"', async () => {
        page = await loginAndWaitForDashboard();
        expect(page.url()).toMatch(/dashboard/i);
        await page.close();
    });

    test('Setelah login, userData tersimpan di storage', async () => {
        page = await newPage();
        await page.goto(`${BASE_URL}/login.html`, { waitUntil: 'domcontentloaded' });
        await page.type('#emailInput', 'admin');
        await page.type('#passwordInput', 'password');
        await page.click('#loginBtn');

        // Tunggu storage terisi SEBELUM redirect
        let userData = null;
        try {
            await page.waitForFunction(
                () => localStorage.getItem('userData') || sessionStorage.getItem('userData'),
                { timeout: 3000 }
            );
            const raw = await page.evaluate(() =>
                localStorage.getItem('userData') || sessionStorage.getItem('userData')
            );
            userData = raw ? JSON.parse(raw) : null;
        } catch (e) {
            // Sudah redirect, tetap baca dari halaman sekarang
            await new Promise(r => setTimeout(r, 500));
            const raw = await page.evaluate(() =>
                localStorage.getItem('userData') || sessionStorage.getItem('userData')
            ).catch(() => null);
            userData = raw ? JSON.parse(raw) : null;
        }
        expect(userData).not.toBeNull();
        expect(userData.role).toBeTruthy();
        await page.close();
    });

    test('Setelah login, token tersimpan dan bukan null', async () => {
        page = await newPage();
        await page.goto(`${BASE_URL}/login.html`, { waitUntil: 'domcontentloaded' });
        await page.type('#emailInput', 'admin');
        await page.type('#passwordInput', 'password');
        await page.click('#loginBtn');

        let token = null;
        try {
            await page.waitForFunction(
                () => localStorage.getItem('authToken') || sessionStorage.getItem('authToken'),
                { timeout: 3000 }
            );
            token = await page.evaluate(() =>
                localStorage.getItem('authToken') || sessionStorage.getItem('authToken')
            );
        } catch (e) {
            await new Promise(r => setTimeout(r, 500));
            token = await page.evaluate(() =>
                localStorage.getItem('authToken') || sessionStorage.getItem('authToken')
            ).catch(() => null);
        }
        expect(token).not.toBeNull();
        await page.close();
    });
});
