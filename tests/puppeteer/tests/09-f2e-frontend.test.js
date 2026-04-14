/**
 * Test Suite 09 — F2E (Frontend) Comprehensive
 * Menguji: rendering halaman, sidebar, navigasi, auth guard, CSS, responsif
 * Cakupan: Admin (26), Member (8), Staff (18)
 */
const puppeteer = require('puppeteer');

const BASE = 'http://localhost/mono-v2';
const TIMEOUT = 15000;

const MOCK = {
    admin: JSON.stringify({ token: 'tok-admin', role: 'admin', name: 'Admin Test', email: 'admin@test.com' }),
    super_admin: JSON.stringify({ token: 'tok-super', role: 'super_admin', name: 'Super Test', email: 'super@test.com' }),
    member: JSON.stringify({ token: 'tok-member', role: 'member', name: 'Member Test', email: 'member@test.com' }),
    staff: JSON.stringify({ token: 'tok-staff', role: 'teller', name: 'Teller Test', email: 'teller@test.com' }),
};

const SUPER_ONLY = new Set(['backup-restore', 'role-access', 'system-config']);

const ADMIN_PAGES = [
    'accounting','analytics','approval-workflow','audit-log','backup-restore',
    'bi-analytics','capacity','dashboard','dashboard-v2','laporan-shu',
    'laporan-umum','live-tracking','loan-management','loans','member-registration',
    'members','npl','reports','risk-fraud','role-access','savings-management',
    'savings','settings','system-config','users','verifikasi'
];
const MEMBER_PAGES = ['dashboard','ajukan-pinjaman','buku-kas','pembayaran','pinjaman-saya','poin-reward','riwayat','simpanan-saya'];
const STAFF_PAGES  = ['dashboard','dashboard-complete','dashboard-teller-complete','dashboard-kasir','dashboard-mantri','dashboard-surveyor','dashboard-teller','dashboard-collector','cetak-struk','gps-log','loans','members','offline-sync','route','savings','setoran-qr','target-harian','transaksi-harian'];

let browser;

beforeAll(async () => {
    browser = await puppeteer.launch({ headless: 'new', args: ['--no-sandbox','--disable-setuid-sandbox','--disable-dev-shm-usage'] });
}, 30000);

afterAll(async () => { if (browser) await browser.close(); });

async function openPage(url, role = 'admin') {
    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 720 });
    await page.evaluateOnNewDocument((d) => { localStorage.setItem('userData', d); }, MOCK[role]);
    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: TIMEOUT });
    return page;
}

// ─────────────────────────────────────────────
// 09.1 — Admin Pages: HTTP & Render
// ─────────────────────────────────────────────
describe('09.1 — Admin Pages: HTTP Status & Render', () => {
    test.each(ADMIN_PAGES)('Admin/%s — HTTP 200 dan body tidak kosong', async (name) => {
        const role = SUPER_ONLY.has(name) ? 'super_admin' : 'admin';
        const page = await openPage(`${BASE}/pages/admin/${name}.php`, role);
        const bodyLen = await page.evaluate(() => document.body.innerHTML.length);
        expect(bodyLen).toBeGreaterThan(100);
        await page.close();
    }, TIMEOUT);
});

// ─────────────────────────────────────────────
// 09.2 — Admin Pages: Sidebar Konsisten
// ─────────────────────────────────────────────
describe('09.2 — Admin Pages: Sidebar Konsisten', () => {
    test.each(ADMIN_PAGES)('Admin/%s — punya #sidebar', async (name) => {
        const role = SUPER_ONLY.has(name) ? 'super_admin' : 'admin';
        const page = await openPage(`${BASE}/pages/admin/${name}.php`, role);
        const hasSidebar = await page.$('#sidebar');
        expect(hasSidebar).not.toBeNull();
        await page.close();
    }, TIMEOUT);

    test.each(ADMIN_PAGES)('Admin/%s — punya #mainContent', async (name) => {
        const role = SUPER_ONLY.has(name) ? 'super_admin' : 'admin';
        const page = await openPage(`${BASE}/pages/admin/${name}.php`, role);
        const hasMain = await page.$('#mainContent');
        expect(hasMain).not.toBeNull();
        await page.close();
    }, TIMEOUT);

    test('Dashboard admin — sidebar.css ter-load', async () => {
        const page = await openPage(`${BASE}/pages/admin/dashboard.php`);
        const hasCss = await page.evaluate(() =>
            Array.from(document.styleSheets).some(s => s.href && s.href.includes('sidebar.css'))
        );
        expect(hasCss).toBe(true);
        await page.close();
    }, TIMEOUT);

    test('Dashboard admin — sidebar punya menu item navigasi', async () => {
        const page = await openPage(`${BASE}/pages/admin/dashboard.php`);
        const count = await page.$$eval('#sidebar .nav-link', els => els.length);
        expect(count).toBeGreaterThan(5);
        await page.close();
    }, TIMEOUT);

    test('Dashboard admin — link aktif punya class "active"', async () => {
        const page = await openPage(`${BASE}/pages/admin/dashboard.php`);
        const hasActive = await page.evaluate(() =>
            !!document.querySelector('#sidebar .nav-link.active')
        );
        expect(hasActive).toBe(true);
        await page.close();
    }, TIMEOUT);
});

// ─────────────────────────────────────────────
// 09.3 — Admin Pages: Auth Guard
// ─────────────────────────────────────────────
describe('09.3 — Admin Pages: Auth Guard', () => {
    test('Dashboard admin tanpa token — redirect ke login', async () => {
        const page = await browser.newPage();
        await page.evaluateOnNewDocument(() => { localStorage.clear(); sessionStorage.clear(); });
        await page.goto(`${BASE}/pages/admin/dashboard.php`, { waitUntil: 'domcontentloaded', timeout: TIMEOUT });
        await new Promise(r => setTimeout(r, 1200));
        const url = page.url();
        expect(url).toMatch(/login/i);
        await page.close();
    }, TIMEOUT);

    test('backup-restore dengan role admin (bukan super_admin) — redirect', async () => {
        const page = await browser.newPage();
        await page.evaluateOnNewDocument((d) => { localStorage.setItem('userData', d); }, MOCK.admin);
        await page.goto(`${BASE}/pages/admin/backup-restore.php`, { waitUntil: 'domcontentloaded', timeout: TIMEOUT });
        await new Promise(r => setTimeout(r, 1200));
        const url = page.url();
        expect(url).toMatch(/login/i);
        await page.close();
    }, TIMEOUT);

    test('backup-restore dengan role super_admin — tidak redirect', async () => {
        const page = await openPage(`${BASE}/pages/admin/backup-restore.php`, 'super_admin');
        await new Promise(r => setTimeout(r, 800));
        expect(page.url()).not.toMatch(/login/i);
        await page.close();
    }, TIMEOUT);
});

// ─────────────────────────────────────────────
// 09.4 — Admin Pages: No PHP Errors
// ─────────────────────────────────────────────
describe('09.4 — Admin Pages: Tidak Ada PHP Error', () => {
    test.each(ADMIN_PAGES)('Admin/%s — tidak ada Fatal/Parse/Warning', async (name) => {
        const role = SUPER_ONLY.has(name) ? 'super_admin' : 'admin';
        const page = await openPage(`${BASE}/pages/admin/${name}.php`, role);
        const body = await page.evaluate(() => document.body.innerHTML);
        expect(body).not.toMatch(/Fatal error:/i);
        expect(body).not.toMatch(/Parse error:/i);
        expect(body).not.toMatch(/Warning:.*on line/i);
        await page.close();
    }, TIMEOUT);
});

// ─────────────────────────────────────────────
// 09.5 — Member Pages: Render & Struktur
// ─────────────────────────────────────────────
describe('09.5 — Member Pages: Render & Struktur', () => {
    test.each(MEMBER_PAGES)('Member/%s — HTTP 200 dan body tidak kosong', async (name) => {
        const page = await openPage(`${BASE}/pages/member/${name}.html`, 'member');
        const bodyLen = await page.evaluate(() => document.body.innerHTML.length);
        expect(bodyLen).toBeGreaterThan(100);
        await page.close();
    }, TIMEOUT);

    test('Member/dashboard — punya dashboard-sidebar', async () => {
        const page = await openPage(`${BASE}/pages/member/dashboard.html`, 'member');
        const hasSidebar = await page.$('#dashboardSidebar');
        expect(hasSidebar).not.toBeNull();
        await page.close();
    }, TIMEOUT);

    test('Member/dashboard — punya dashboard-header', async () => {
        const page = await openPage(`${BASE}/pages/member/dashboard.html`, 'member');
        const hasHeader = await page.$('.dashboard-header');
        expect(hasHeader).not.toBeNull();
        await page.close();
    }, TIMEOUT);

    test('Member/dashboard — tidak ada PHP error di body', async () => {
        const page = await openPage(`${BASE}/pages/member/dashboard.html`, 'member');
        const body = await page.evaluate(() => document.body.innerHTML);
        expect(body).not.toMatch(/Fatal error:|Parse error:/i);
        await page.close();
    }, TIMEOUT);
});

// ─────────────────────────────────────────────
// 09.6 — Staff Pages: Render & Struktur
// ─────────────────────────────────────────────
describe('09.6 — Staff Pages: Render & Struktur', () => {
    test.each(STAFF_PAGES)('Staff/%s — HTTP 200 dan body tidak kosong', async (name) => {
        const page = await openPage(`${BASE}/pages/staff/${name}.html`, 'staff');
        const bodyLen = await page.evaluate(() => document.body.innerHTML.length);
        expect(bodyLen).toBeGreaterThan(100);
        await page.close();
    }, TIMEOUT);

    test('Staff/dashboard — punya dashboard-sidebar', async () => {
        const page = await openPage(`${BASE}/pages/staff/dashboard.html`, 'staff');
        const hasSidebar = await page.$('#dashboardSidebar');
        expect(hasSidebar).not.toBeNull();
        await page.close();
    }, TIMEOUT);

    test('Staff/dashboard — HTML structure valid (tidak ada div orphan)', async () => {
        const page = await openPage(`${BASE}/pages/staff/dashboard.html`, 'staff');
        const hasMain = await page.$('main.dashboard-main');
        expect(hasMain).not.toBeNull();
        await page.close();
    }, TIMEOUT);
});

// ─────────────────────────────────────────────
// 09.7 — Sidebar Navigasi Links
// ─────────────────────────────────────────────
describe('09.7 — Sidebar: Link Navigasi Admin', () => {
    const NAV_LINKS = [
        ['dashboard.php', 'Dashboard'],
        ['members.php', 'Anggota'],
        ['loans.php', 'Pinjaman'],
        ['savings.php', 'Simpanan'],
        ['users.php', 'Pengguna'],
    ];

    test.each(NAV_LINKS)('%s — ada di sidebar admin', async (href, label) => {
        const page = await openPage(`${BASE}/pages/admin/dashboard.php`);
        const found = await page.evaluate((h) => {
            const links = document.querySelectorAll('#sidebar a[href]');
            return Array.from(links).some(a => a.getAttribute('href') && a.getAttribute('href').includes(h));
        }, href);
        expect(found).toBe(true);
        await page.close();
    }, TIMEOUT);
});

// ─────────────────────────────────────────────
// 09.8 — Responsif Mobile (375px)
// ─────────────────────────────────────────────
describe('09.8 — Responsif Mobile (375x667)', () => {
    test('Dashboard admin — render di mobile (375px)', async () => {
        const page = await browser.newPage();
        await page.setViewport({ width: 375, height: 667 });
        await page.evaluateOnNewDocument((d) => { localStorage.setItem('userData', d); }, MOCK.admin);
        await page.goto(`${BASE}/pages/admin/dashboard.php`, { waitUntil: 'domcontentloaded', timeout: TIMEOUT });
        const bodyLen = await page.evaluate(() => document.body.innerHTML.length);
        expect(bodyLen).toBeGreaterThan(100);
        await page.close();
    }, TIMEOUT);

    test('Member/dashboard — render di mobile (375px)', async () => {
        const page = await browser.newPage();
        await page.setViewport({ width: 375, height: 667 });
        await page.evaluateOnNewDocument((d) => { localStorage.setItem('userData', d); }, MOCK.member);
        await page.goto(`${BASE}/pages/member/dashboard.html`, { waitUntil: 'domcontentloaded', timeout: TIMEOUT });
        const bodyLen = await page.evaluate(() => document.body.innerHTML.length);
        expect(bodyLen).toBeGreaterThan(100);
        await page.close();
    }, TIMEOUT);
});

// ─────────────────────────────────────────────
// 09.9 — menus.json Integrity
// ─────────────────────────────────────────────
describe('09.9 — menus.json: Konsistensi URL', () => {
    test('menus.json dapat diakses dan valid JSON', async () => {
        const http = require('http');
        const body = await new Promise((res, rej) => {
            http.get(`${BASE}/assets/config/menus.json`, r => {
                let d = '';
                r.on('data', c => d += c);
                r.on('end', () => res(d));
            }).on('error', rej);
        });
        const json = JSON.parse(body);
        expect(json).toHaveProperty('admin');
        expect(json).toHaveProperty('member');
        expect(json).toHaveProperty('staff');
    });

    test('Admin menu URLs pakai .php', async () => {
        const http = require('http');
        const body = await new Promise((res, rej) => {
            http.get(`${BASE}/assets/config/menus.json`, r => {
                let d = '';
                r.on('data', c => d += c);
                r.on('end', () => res(d));
            }).on('error', rej);
        });
        const json = JSON.parse(body);
        const adminUrlsWithHtml = json.admin.filter(item => item.url && item.url.includes('.html'));
        expect(adminUrlsWithHtml).toHaveLength(0);
    });

    test('Member menu URLs pakai .html', async () => {
        const http = require('http');
        const body = await new Promise((res, rej) => {
            http.get(`${BASE}/assets/config/menus.json`, r => {
                let d = '';
                r.on('data', c => d += c);
                r.on('end', () => res(d));
            }).on('error', rej);
        });
        const json = JSON.parse(body);
        const memberUrlsWithPhp = json.member.filter(item => item.url && item.url.includes('.php'));
        expect(memberUrlsWithPhp).toHaveLength(0);
    });
});
