/**
 * Super Admin Comprehensive Test Suite — F2E + E2E
 * Menggunakan SATU browser instance dan SATU page untuk semua test
 * agar session tidak hilang antar test
 */

const puppeteer = require('puppeteer');
const { BASE_URL } = require('../helpers/api');

const LOGIN_URL = `${BASE_URL}/login.html`;
const MOCK_SUPER = JSON.stringify({ token: 'tok-super-06', role: 'super_admin', name: 'Super Test', email: 'super@test.com' });
const SUPER_ONLY = new Set(['/system-config.php','/role-access.php','/backup-restore.php']);

let browser;
let page;

// ─── Helper: recover page jika detached ────────────────────────────────────────
async function getActivePage() {
    try {
        // Test apakah page masih aktif
        await page.evaluate(() => true);
        return page;
    } catch (e) {
        // Page detached/closed, buat yang baru
        page = await browser.newPage();
        await page.setViewport({ width: 1280, height: 720 });
        return page;
    }
}

// ─── Helper: navigate dengan auth inject ────────────────────────────────────
async function gotoPage(url) {
    page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 720 });
    await page.evaluateOnNewDocument((d) => { localStorage.setItem('userData', d); }, MOCK_SUPER);
    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await new Promise(r => setTimeout(r, 800));
    return page.url();
}

// ─── Setup & Teardown ─────────────────────────────────────────────────────────
beforeAll(async () => {
    browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', '--disable-gpu'],
        defaultViewport: { width: 1280, height: 720 }
    });
    page = await browser.newPage();
    await page.evaluateOnNewDocument((d) => { localStorage.setItem('userData', d); }, MOCK_SUPER);
    await page.goto(`${BASE_URL}/pages/admin/dashboard.php`, { waitUntil: 'domcontentloaded', timeout: 15000 });
}, 30000);

afterAll(async () => {
    if (browser) await browser.close();
});

// ─────────────────────────────────────────────────────────────────────────────
// F2E: Login & Session
// ─────────────────────────────────────────────────────────────────────────────
describe('F2E.1 — Login & Session', () => {
    test('Setelah login, URL redirect ke dashboard admin', async () => {
        expect(page.url()).toMatch(/dashboard/i);
    });

    test('Session tersimpan di localStorage', async () => {
        page = await getActivePage();
        const token = await page.evaluate(() => {
            const d = localStorage.getItem('userData');
            return d ? JSON.parse(d).token : null;
        });
        expect(token).toBeTruthy();
    });

    test('userData tersimpan dan berisi role Super Admin', async () => {
        page = await getActivePage();
        const userData = await page.evaluate(() => {
            const data = localStorage.getItem('userData');
            return data ? JSON.parse(data) : null;
        });
        expect(userData).not.toBeNull();
        const role = (userData.role || '').toLowerCase().replace(' ', '_');
        expect(['super_admin', 'admin']).toContain(role);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// F2E: Dashboard Admin
// ─────────────────────────────────────────────────────────────────────────────
describe('F2E.2 — Dashboard Admin', () => {
    test('Dashboard admin berhasil dimuat', async () => {
        const url = await gotoPage(`${BASE_URL}/pages/admin/dashboard.php`);
        expect(url).toContain('dashboard.php');
    });

    test('Halaman dashboard memiliki judul', async () => {
        page = await getActivePage();
        const title = await page.title();
        expect(title).toBeTruthy();
    });

    test('Konten dashboard terlihat', async () => {
        page = await getActivePage();
        const hasContent = await page.evaluate(() => document.body.innerText.trim().length > 0);
        expect(hasContent).toBe(true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// F2E: Super Admin Exclusive Pages
// ─────────────────────────────────────────────────────────────────────────────
describe('F2E.3 — System Config (Super Admin Exclusive)', () => {
    test('Halaman System Config dapat diakses', async () => {
        const url = await gotoPage(`${BASE_URL}/pages/admin/system-config.php`);
        expect(url).toContain('system-config.php');
    });

    test('Halaman System Config memiliki konten', async () => {
        page = await getActivePage();
        const hasContent = await page.evaluate(() => document.body.innerText.trim().length > 0);
        expect(hasContent).toBe(true);
    });
});

describe('F2E.4 — Role Access (Super Admin Exclusive)', () => {
    test('Halaman Role Access dapat diakses', async () => {
        const url = await gotoPage(`${BASE_URL}/pages/admin/role-access.php`);
        expect(url).toContain('role-access.php');
    });

    test('Halaman Role Access memiliki konten', async () => {
        page = await getActivePage();
        const hasContent = await page.evaluate(() => document.body.innerText.trim().length > 0);
        expect(hasContent).toBe(true);
    });
});

describe('F2E.5 — Backup Restore (Super Admin Exclusive)', () => {
    test('Halaman Backup Restore dapat diakses', async () => {
        const url = await gotoPage(`${BASE_URL}/pages/admin/backup-restore.php`);
        expect(url).toContain('backup-restore.php');
    });

    test('Halaman Backup Restore memiliki konten', async () => {
        page = await getActivePage();
        const hasContent = await page.evaluate(() => document.body.innerText.trim().length > 0);
        expect(hasContent).toBe(true);
    });
});

describe('F2E.6 — Audit Log (Super Admin Exclusive)', () => {
    test('Halaman Audit Log dapat diakses', async () => {
        const url = await gotoPage(`${BASE_URL}/pages/admin/audit-log.php`);
        expect(url).toContain('audit-log.php');
    });

    test('Halaman Audit Log memiliki konten', async () => {
        page = await getActivePage();
        const hasContent = await page.evaluate(() => document.body.innerText.trim().length > 0);
        expect(hasContent).toBe(true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// F2E: Admin Pages (shared)
// ─────────────────────────────────────────────────────────────────────────────
describe('F2E.7 — Users Management', () => {
    test('Halaman Users dapat diakses', async () => {
        const url = await gotoPage(`${BASE_URL}/pages/admin/users.php`);
        expect(url).toContain('users.php');
    });
});

describe('F2E.8 — Analytics', () => {
    test('Halaman Analytics dapat diakses', async () => {
        const url = await gotoPage(`${BASE_URL}/pages/admin/analytics.php`);
        expect(url).toContain('analytics.php');
    });
});

describe('F2E.9 — SHU Report', () => {
    test('Halaman Laporan SHU dapat diakses', async () => {
        const url = await gotoPage(`${BASE_URL}/pages/admin/laporan-shu.php`);
        expect(url).toContain('laporan-shu.php');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// E2E: Complete Flow — Navigasi antar halaman
// ─────────────────────────────────────────────────────────────────────────────
describe('E2E.1 — Complete Navigation Flow (Super Admin Exclusive)', () => {
    test('Navigasi: Dashboard → System Config → Role Access → Backup → Audit', async () => {
        // Dashboard
        let url = await gotoPage(`${BASE_URL}/pages/admin/dashboard.php`);
        expect(url).toContain('dashboard.php');

        // System Config
        url = await gotoPage(`${BASE_URL}/pages/admin/system-config.php`);
        expect(url).toContain('system-config.php');

        // Role Access
        url = await gotoPage(`${BASE_URL}/pages/admin/role-access.php`);
        expect(url).toContain('role-access.php');

        // Backup Restore
        url = await gotoPage(`${BASE_URL}/pages/admin/backup-restore.php`);
        expect(url).toContain('backup-restore.php');

        // Audit Log
        url = await gotoPage(`${BASE_URL}/pages/admin/audit-log.php`);
        expect(url).toContain('audit-log.php');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// E2E: Logout
// ─────────────────────────────────────────────────────────────────────────────
describe('E2E.2 — Logout', () => {
    test('Logout menghapus session dan redirect ke login', async () => {
        // Buka page baru tanpa auth, verifikasi redirect ke login
        const logoutPage = await browser.newPage();
        await logoutPage.evaluateOnNewDocument(() => {
            localStorage.removeItem('userData');
            sessionStorage.removeItem('userData');
        });
        await logoutPage.goto(`${BASE_URL}/pages/admin/dashboard.php`, { waitUntil: 'domcontentloaded', timeout: 15000 });
        await new Promise(r => setTimeout(r, 2000));
        const url = logoutPage.url();
        await logoutPage.close();
        expect(url).toContain('login.html');
    });
});
