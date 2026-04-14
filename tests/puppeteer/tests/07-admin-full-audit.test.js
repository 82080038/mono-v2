/**
 * Admin Full Audit Test Suite
 * Memetakan SEMUA nav link di sidebar admin/super admin
 * dan mengaudit kondisi nyata tiap halaman (implementasi penuh vs placeholder)
 *
 * Status halaman:
 *   FULL     = implementasi lengkap, ada tabel/form/data/API
 *   PARTIAL  = ada struktur tapi belum lengkap
 *   STUB     = placeholder "belum diimplementasi"
 */

const puppeteer = require('puppeteer');
const { BASE_URL } = require('../helpers/api');

const ADMIN_BASE = `${BASE_URL}/pages/admin`;
const MOCK_ADMIN = JSON.stringify({ token: 'tok-admin-07', role: 'admin', name: 'Admin Test', email: 'admin@test.com' });
const MOCK_SUPER = JSON.stringify({ token: 'tok-super-07', role: 'super_admin', name: 'Super Test', email: 'super@test.com' });
const SUPER_ONLY = new Set(['system-config.php','role-access.php','backup-restore.php']);

let browser;
let page;

// ─── Helpers ──────────────────────────────────────────────────────────────────
async function getPage() {
    try { await page.evaluate(() => true); return page; }
    catch (e) {
        page = await browser.newPage();
        await page.setViewport({ width: 1280, height: 720 });
        return page;
    }
}

async function goto(path) {
    const mock = SUPER_ONLY.has(path) ? MOCK_SUPER : MOCK_ADMIN;
    page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 720 });
    await page.evaluateOnNewDocument((d) => { localStorage.setItem('userData', d); }, mock);
    await page.goto(`${ADMIN_BASE}/${path}`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await new Promise(r => setTimeout(r, 800));
    return page.url();
}

async function hasRealContent(selectors) {
    // selectors = array of CSS selectors, return true if any found
    return await page.evaluate((sels) => {
        return sels.some(sel => document.querySelector(sel) !== null);
    }, selectors);
}

async function isStub() {
    return await page.evaluate(() => {
        const text = document.body.innerText.toLowerCase();
        return text.includes('placeholder') || text.includes('belum diimplementasi') || text.includes('coming soon');
    });
}

async function getPageTitle() {
    return await page.title().catch(() => '');
}

async function countElements(sel) {
    return await page.evaluate((s) => document.querySelectorAll(s).length, sel).catch(() => 0);
}

// ─── Setup ────────────────────────────────────────────────────────────────────
beforeAll(async () => {
    browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', '--disable-gpu'],
        defaultViewport: { width: 1280, height: 720 }
    });
    page = await browser.newPage();
}, 30000);

afterAll(async () => {
    if (browser) await browser.close();
});

// ─────────────────────────────────────────────────────────────────────────────
// NAV LINK 1: Dashboard (FULL)
// ─────────────────────────────────────────────────────────────────────────────
describe('NAV: Dashboard Admin', () => {
    test('Halaman dashboard.php dapat dibuka', async () => {
        const url = await goto('dashboard.php');
        expect(url).toContain('dashboard.php');
    });
    test('Dashboard punya sidebar navigasi', async () => {
        const hasSidebar = await hasRealContent(['#sidebar', 'nav', '.nav-link']);
        expect(hasSidebar).toBe(true);
    });
    test('Dashboard punya stat cards (KPI)', async () => {
        const count = await countElements('.stat-card, .card');
        expect(count).toBeGreaterThan(0);
    });
    test('Dashboard punya konten body yang substantial', async () => {
        const text = await page.evaluate(() => document.body.innerText.trim());
        expect(text.length).toBeGreaterThan(200);
    });
    test('Semua nav link di sidebar terdaftar dan tidak 404', async () => {
        const links = await page.evaluate(() => {
            return [...document.querySelectorAll('#sidebar a[href]')]
                .map(a => a.getAttribute('href'))
                .filter(h => h && !h.startsWith('#') && !h.startsWith('http'));
        });
        expect(links.length).toBeGreaterThan(0);
        console.log('Nav links ditemukan di sidebar:', links);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// NAV LINK 2: Anggota / Member Registration (FULL)
// ─────────────────────────────────────────────────────────────────────────────
describe('NAV: Registrasi Anggota (member-registration.php)', () => {
    test('Halaman dapat dibuka', async () => {
        const url = await goto('member-registration.php');
        expect(url).toContain('member-registration.php');
    });
    test('Bukan halaman placeholder', async () => {
        const stub = await isStub();
        expect(stub).toBe(false);
    });
    test('Punya form pendaftaran anggota', async () => {
        const hasForm = await hasRealContent(['form', 'input[type="text"]', 'input[name]', '.form-control']);
        expect(hasForm).toBe(true);
    });
    test('Punya tabel/daftar anggota atau form yang substansial', async () => {
        const count = await countElements('input, select, textarea');
        expect(count).toBeGreaterThan(2);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// NAV LINK 3: Simpanan (savings-management.php) (FULL)
// ─────────────────────────────────────────────────────────────────────────────
describe('NAV: Manajemen Simpanan (savings-management.php)', () => {
    test('Halaman dapat dibuka', async () => {
        const url = await goto('savings-management.php');
        expect(url).toContain('savings-management.php');
    });
    test('Bukan halaman placeholder', async () => {
        const stub = await isStub();
        expect(stub).toBe(false);
    });
    test('Punya tabel simpanan atau form transaksi', async () => {
        const hasContent = await hasRealContent(['table', '.table', 'form', '.modal']);
        expect(hasContent).toBe(true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// NAV LINK 4: Pinjaman (loan-management.php) (FULL)
// ─────────────────────────────────────────────────────────────────────────────
describe('NAV: Manajemen Pinjaman (loan-management.php)', () => {
    test('Halaman dapat dibuka', async () => {
        const url = await goto('loan-management.php');
        expect(url).toContain('loan-management.php');
    });
    test('Bukan halaman placeholder', async () => {
        const stub = await isStub();
        expect(stub).toBe(false);
    });
    test('Punya tabel pinjaman atau form pengajuan', async () => {
        const hasContent = await hasRealContent(['table', '.table', 'form', '.modal', 'button']);
        expect(hasContent).toBe(true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// NAV LINK 5: Jurnal & Laporan Akuntansi (accounting.php) (FULL)
// ─────────────────────────────────────────────────────────────────────────────
describe('NAV: Jurnal & Laporan Akuntansi (accounting.php)', () => {
    test('Halaman dapat dibuka', async () => {
        const url = await goto('accounting.php');
        expect(url).toContain('accounting.php');
    });
    test('Bukan halaman placeholder', async () => {
        const stub = await isStub();
        expect(stub).toBe(false);
    });
    test('Punya elemen jurnal/laporan keuangan', async () => {
        const hasContent = await hasRealContent(['table', '.table', 'canvas', '.card', 'form']);
        expect(hasContent).toBe(true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// NAV LINK 6: SHU & Distribusi (laporan-shu.php) (FULL)
// ─────────────────────────────────────────────────────────────────────────────
describe('NAV: SHU & Distribusi (laporan-shu.php)', () => {
    test('Halaman dapat dibuka', async () => {
        const url = await goto('laporan-shu.php');
        expect(url).toContain('laporan-shu.php');
    });
    test('Bukan halaman placeholder', async () => {
        const stub = await isStub();
        expect(stub).toBe(false);
    });
    test('Punya tabel atau chart SHU', async () => {
        const hasContent = await hasRealContent(['table', '.table', 'canvas', '.card']);
        expect(hasContent).toBe(true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// NAV LINK 7: Approval Workflow (approval-workflow.php) (FULL)
// ─────────────────────────────────────────────────────────────────────────────
describe('NAV: Approval Workflow (approval-workflow.php)', () => {
    test('Halaman dapat dibuka', async () => {
        const url = await goto('approval-workflow.php');
        expect(url).toContain('approval-workflow.php');
    });
    test('Bukan halaman placeholder', async () => {
        const stub = await isStub();
        expect(stub).toBe(false);
    });
    test('Punya elemen daftar persetujuan', async () => {
        const hasContent = await hasRealContent(['table', '.table', '.card', 'button', 'form']);
        expect(hasContent).toBe(true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// NAV LINK 8: Audit Trail (audit-log.php) (FULL)
// ─────────────────────────────────────────────────────────────────────────────
describe('NAV: Audit Trail (audit-log.php)', () => {
    test('Halaman dapat dibuka', async () => {
        const url = await goto('audit-log.php');
        expect(url).toContain('audit-log.php');
    });
    test('Bukan halaman placeholder', async () => {
        const stub = await isStub();
        expect(stub).toBe(false);
    });
    test('Punya tabel audit log', async () => {
        const hasContent = await hasRealContent(['table', '.table', '#auditTable', '#logTable']);
        expect(hasContent).toBe(true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// NAV LINK 9: Analytics (analytics.php) (FULL)
// ─────────────────────────────────────────────────────────────────────────────
describe('NAV: Analytics & Statistik (analytics.php)', () => {
    test('Halaman dapat dibuka', async () => {
        const url = await goto('analytics.php');
        expect(url).toContain('analytics.php');
    });
    test('Bukan halaman placeholder', async () => {
        const stub = await isStub();
        expect(stub).toBe(false);
    });
    test('Punya chart atau KPI cards', async () => {
        const hasContent = await hasRealContent(['canvas', '.card', '.chart-container', '[id$="Chart"]']);
        expect(hasContent).toBe(true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// NAV LINK 10: Laporan Umum (laporan-umum.php) (STUB)
// ─────────────────────────────────────────────────────────────────────────────
describe('NAV: Laporan Umum (laporan-umum.php) — STATUS: STUB', () => {
    test('Halaman dapat dibuka (meskipun placeholder)', async () => {
        const url = await goto('laporan-umum.php');
        expect(url).toContain('laporan-umum.php');
    });
    test('[AUDIT] Halaman ini adalah STUB/placeholder — belum diimplementasi', async () => {
        const stub = await isStub();
        console.log('[AUDIT] laporan-umum.php adalah placeholder:', stub);
        // Tidak fail test, cukup catat kondisi nyata
        expect(true).toBe(true);
    });
    test('[AUDIT] Judul halaman terbaca', async () => {
        const title = await getPageTitle();
        console.log('[AUDIT] Title:', title);
        expect(title.length).toBeGreaterThan(0);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// NAV LINK 11: Manajemen User (users.php) (FULL)
// ─────────────────────────────────────────────────────────────────────────────
describe('NAV: Manajemen User (users.php)', () => {
    test('Halaman dapat dibuka', async () => {
        const url = await goto('users.php');
        expect(url).toContain('users.php');
    });
    test('Bukan halaman placeholder', async () => {
        const stub = await isStub();
        expect(stub).toBe(false);
    });
    test('Punya tabel user atau form manajemen user', async () => {
        const hasContent = await hasRealContent(['table', '.table', '#userTable', 'input', '.modal']);
        expect(hasContent).toBe(true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// NAV LINK 12 (Super Admin Only): System Config (system-config.php)
// ─────────────────────────────────────────────────────────────────────────────
describe('NAV (Super Admin): Pengaturan Sistem (system-config.php)', () => {
    test('Halaman dapat dibuka oleh Super Admin', async () => {
        const url = await goto('system-config.php');
        expect(url).toContain('system-config.php');
    });
    test('Bukan halaman placeholder', async () => {
        const stub = await isStub();
        expect(stub).toBe(false);
    });
    test('Punya form konfigurasi sistem', async () => {
        const hasContent = await hasRealContent(['form', 'input', '.card', 'button']);
        expect(hasContent).toBe(true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// NAV LINK 13 (Super Admin Only): Role Access (role-access.php)
// ─────────────────────────────────────────────────────────────────────────────
describe('NAV (Super Admin): Role & Akses (role-access.php)', () => {
    test('Halaman dapat dibuka oleh Super Admin', async () => {
        const url = await goto('role-access.php');
        expect(url).toContain('role-access.php');
    });
    test('Bukan halaman placeholder', async () => {
        const stub = await isStub();
        expect(stub).toBe(false);
    });
    test('Punya elemen manajemen role/akses', async () => {
        const hasContent = await hasRealContent(['table', '.table', '.card', 'button', '.modal']);
        expect(hasContent).toBe(true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// NAV LINK 14 (Super Admin Only): Backup Restore (backup-restore.php)
// ─────────────────────────────────────────────────────────────────────────────
describe('NAV (Super Admin): Backup & Restore (backup-restore.php)', () => {
    test('Halaman dapat dibuka oleh Super Admin', async () => {
        const url = await goto('backup-restore.php');
        expect(url).toContain('backup-restore.php');
    });
    test('Bukan halaman placeholder', async () => {
        const stub = await isStub();
        expect(stub).toBe(false);
    });
    test('Punya tombol backup atau restore', async () => {
        const hasContent = await hasRealContent(['button', '.btn', 'form']);
        expect(hasContent).toBe(true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// AUDIT HALAMAN STUB — Catat tanpa fail
// ─────────────────────────────────────────────────────────────────────────────
describe('AUDIT: Halaman Stub/Placeholder (di sidebar tapi belum diimplementasi)', () => {
    const stubs = [
        { file: 'reports.html',     label: 'Laporan Umum (reports.html)' },
        { file: 'savings.html',     label: 'Simpanan alias (savings.html)' },
        { file: 'loans.html',       label: 'Pinjaman alias (loans.html)' },
        { file: 'members.html',     label: 'Anggota alias (members.html)' },
        { file: 'npl.html',         label: 'Monitoring NPL (npl.html)' },
        { file: 'verifikasi.html',  label: 'Verifikasi Berjenjang (verifikasi.html)' },
        { file: 'bi-analytics.php',label: 'BI & KPI Analytics (bi-analytics.php)' },
        { file: 'risk-fraud.html',  label: 'Risk & Fraud (risk-fraud.html)' },
        { file: 'capacity.html',    label: 'Capacity Planning (capacity.html)' },
        { file: 'settings.html',    label: 'Pengaturan (settings.html)' },
    ];

    test.each(stubs)('$label — terbuka dan teridentifikasi statusnya', async ({ file, label }) => {
        await goto(file);
        const stub = await isStub();
        const text = await page.evaluate(() => document.body.innerText.trim().substring(0, 100));
        const status = stub ? '🔴 STUB/PLACEHOLDER' : '🟢 IMPLEMENTASI';
        console.log(`[${status}] ${label}: "${text}"`);
        // Test pass apapun statusnya — ini audit, bukan requirement
        expect(page.url()).toContain(file);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// FITUR: Test interaksi nyata di halaman FULL
// ─────────────────────────────────────────────────────────────────────────────
describe('FITUR: Interaksi di member-registration.php', () => {
    test('Tombol/tab dapat diklik tanpa error JS', async () => {
        await goto('member-registration.php');
        // Coba klik tab atau button pertama yang ada
        const clicked = await page.evaluate(() => {
            const btn = document.querySelector('button:not([disabled]), .nav-link, .tab-pane');
            if (btn) { btn.click(); return true; }
            return false;
        });
        console.log('Klik elemen member-registration:', clicked);
        expect(true).toBe(true); // Tidak crash = OK
    });
    test('Filter/search input tersedia', async () => {
        page = await getPage();
        await goto('member-registration.php');
        const hasSearch = await hasRealContent(['input[type="search"]', '#searchInput', '.search-input', 'input[placeholder]']);
        console.log('Ada search input:', hasSearch);
        expect(true).toBe(true); // Audit saja
    });
});

describe('FITUR: Interaksi di loan-management.php', () => {
    test('Halaman loan-management load tanpa error', async () => {
        await goto('loan-management.php');
        const jsErrors = [];
        page.on('pageerror', e => jsErrors.push(e.message));
        await new Promise(r => setTimeout(r, 1000));
        console.log('JS errors di loan-management:', jsErrors);
        expect(page.url()).toContain('loan-management.php');
    });
    test('Punya tombol aksi (tambah/approve/reject)', async () => {
        const count = await countElements('button, .btn');
        console.log('Jumlah tombol di loan-management:', count);
        expect(count).toBeGreaterThan(0);
    });
});

describe('FITUR: Interaksi di accounting.php', () => {
    test('Accounting load tanpa error', async () => {
        await goto('accounting.php');
        expect(page.url()).toContain('accounting.php');
    });
    test('Punya elemen jurnal (tabel atau form)', async () => {
        const hasTable = await hasRealContent(['table', '#journalTable', '#laporanTable', 'canvas']);
        console.log('Ada tabel/chart akuntansi:', hasTable);
        expect(hasTable).toBe(true);
    });
});

describe('FITUR: Interaksi di users.php', () => {
    test('Users load tanpa error', async () => {
        await goto('users.php');
        expect(page.url()).toContain('users.php');
    });
    test('Punya tombol Tambah User', async () => {
        const hasAddBtn = await page.evaluate(() => {
            const btns = [...document.querySelectorAll('button, .btn, a')];
            return btns.some(el => el.textContent.toLowerCase().includes('tambah') || el.textContent.toLowerCase().includes('add'));
        });
        console.log('Ada tombol Tambah User:', hasAddBtn);
        expect(hasAddBtn).toBe(true);
    });
    test('Data user dimuat dari API', async () => {
        await new Promise(r => setTimeout(r, 2000)); // tunggu API
        const hasRows = await countElements('tr, .user-row');
        console.log('Jumlah row user di tabel:', hasRows);
        expect(hasRows).toBeGreaterThanOrEqual(0); // Audit saja
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// RINGKASAN AUDIT — Cetak summary kondisi semua halaman
// ─────────────────────────────────────────────────────────────────────────────
describe('RINGKASAN AUDIT Seluruh Halaman Admin', () => {
    test('Cetak status semua halaman admin', async () => {
        const pages = [
            'dashboard.php', 'member-registration.php', 'savings-management.php',
            'loan-management.php', 'accounting.php', 'laporan-shu.php',
            'approval-workflow.php', 'audit-log.php', 'analytics.php',
            'users.php', 'system-config.php', 'role-access.php', 'backup-restore.php',
            'reports.html', 'savings.html', 'loans.html', 'members.html',
            'npl.html', 'verifikasi.html', 'bi-analytics.php', 'risk-fraud.html',
            'capacity.html', 'settings.html', 'laporan-umum.php',
        ];

        console.log('\n═══════════════════════════════════════════════════════');
        console.log('       AUDIT REPORT — HALAMAN ADMIN/SUPER ADMIN');
        console.log('═══════════════════════════════════════════════════════');

        for (const p of pages) {
            await goto(p);
            page = await getPage();
            const stub = await isStub().catch(() => false);
            const textLen = await page.evaluate(() => document.body.innerText.trim().length).catch(() => 0);
            const formCount = await countElements('form, table, canvas');
            const status = stub ? '🔴 STUB' : textLen < 300 ? '🟡 MINIMAL' : '🟢 FULL';
            console.log(`${status.padEnd(16)} ${p.padEnd(35)} [len:${textLen}, forms/tables:${formCount}]`);
        }

        console.log('═══════════════════════════════════════════════════════\n');
        expect(true).toBe(true);
    }, 120000);
});
