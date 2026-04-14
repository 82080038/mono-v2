/**
 * Comprehensive Puppeteer Test — All Pages
 * Tests: HTTP 200, no console errors, sidebar/nav present, no broken layout
 */
const puppeteer = require('puppeteer');

const BASE = 'http://localhost/mono-v2';

// Inject valid localStorage userData so auth guard passes
const MOCK_USER_ADMIN = JSON.stringify({
    token: 'test-token-admin',
    role: 'admin',
    name: 'Test Admin',
    email: 'admin@test.com'
});
const MOCK_USER_SUPER_ADMIN = JSON.stringify({
    token: 'test-token-super',
    role: 'super_admin',
    name: 'Test SuperAdmin',
    email: 'super@test.com'
});
const MOCK_USER_MEMBER = JSON.stringify({
    token: 'test-token-member',
    role: 'member',
    name: 'Test Member',
    email: 'member@test.com'
});
const MOCK_USER_STAFF = JSON.stringify({
    token: 'test-token-staff',
    role: 'teller',
    name: 'Test Teller',
    email: 'teller@test.com'
});

const SUPER_ADMIN_ONLY = new Set(['backup-restore','role-access','system-config']);
const ADMIN_PAGES = [
    'accounting','analytics','approval-workflow','audit-log','backup-restore',
    'bi-analytics','capacity','dashboard','dashboard-v2','laporan-shu',
    'laporan-umum','live-tracking','loan-management','loans','member-registration',
    'members','npl','reports','risk-fraud','role-access','savings-management',
    'savings','settings','system-config','users','verifikasi'
].map(p => ({ name: p, url: `${BASE}/pages/admin/${p}.php`, role: SUPER_ADMIN_ONLY.has(p) ? 'super_admin' : 'admin' }));

const MEMBER_PAGES = [
    'dashboard','ajukan-pinjaman','buku-kas','pembayaran',
    'pinjaman-saya','poin-reward','riwayat','simpanan-saya'
].map(p => ({ name: `member/${p}`, url: `${BASE}/pages/member/${p}.html`, role: 'member' }));

const STAFF_PAGES = [
    'dashboard','dashboard-complete','dashboard-teller-complete',
    'dashboard-kasir','dashboard-mantri','dashboard-surveyor','dashboard-teller',
    'dashboard-collector','cetak-struk','gps-log','loans','members',
    'offline-sync','route','savings','setoran-qr','target-harian','transaksi-harian'
].map(p => ({ name: `staff/${p}`, url: `${BASE}/pages/staff/${p}.html`, role: 'staff' }));

const ALL_PAGES = [...ADMIN_PAGES, ...MEMBER_PAGES, ...STAFF_PAGES];

async function setAuth(page, role) {
    const data = role === 'super_admin' ? MOCK_USER_SUPER_ADMIN
               : role === 'admin' ? MOCK_USER_ADMIN
               : role === 'member' ? MOCK_USER_MEMBER
               : MOCK_USER_STAFF;
    await page.evaluateOnNewDocument((d) => {
        localStorage.setItem('userData', d);
    }, data);
}

async function testPage(browser, { name, url, role }) {
    const page = await browser.newPage();
    const errors = [];
    const warnings = [];

    page.on('console', msg => {
        const type = msg.type();
        const text = msg.text();
        // Ignore known safe warnings
        if (text.includes('favicon') || text.includes('net::ERR_') && text.includes('favicon')) return;
        if (type === 'error') errors.push(text.substring(0, 120));
        if (type === 'warning') warnings.push(text.substring(0, 120));
    });

    page.on('pageerror', err => errors.push('PAGE_ERR: ' + err.message.substring(0, 120)));

    await setAuth(page, role);

    let httpCode = 0;
    let redirected = false;
    try {
        const resp = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 15000 });
        httpCode = resp ? resp.status() : 0;
        const finalUrl = page.url();
        redirected = !finalUrl.includes(url.split('/').pop().split('?')[0]);
    } catch (e) {
        errors.push('GOTO_ERR: ' + e.message.substring(0, 80));
    }

    // Evaluate page structure
    const checks = await page.evaluate((role) => {
        const r = {};
        r.hasTitle    = document.title.length > 0;
        r.hasBody     = !!document.body;
        r.bodyNotEmpty = document.body.innerHTML.trim().length > 50;

        if (role === 'admin') {
            r.hasSidebar     = !!document.getElementById('sidebar');
            r.hasMainContent = !!document.getElementById('mainContent');
            r.hasTopbar      = !!document.querySelector('.topbar');
        } else {
            // member/staff: check they have meaningful content
            r.hasSidebar     = true; // not required for member/staff standalone pages
            r.hasMainContent = true;
            r.hasTopbar      = true;
        }
        // Check for PHP errors in rendered output
        r.hasPhpError = document.body.innerHTML.includes('Fatal error:') ||
                        document.body.innerHTML.includes('Parse error:') ||
                        document.body.innerHTML.includes('Warning: ') && document.body.innerHTML.includes(' on line ');
        return r;
    }, role);

    await page.close();

    const issues = [];
    if (httpCode !== 200) issues.push(`HTTP_${httpCode}`);
    if (redirected && (role === 'admin' || role === 'super_admin')) issues.push('REDIRECTED');
    if (!checks.bodyNotEmpty) issues.push('EMPTY_BODY');
    if (!checks.hasSidebar)   issues.push('NO_SIDEBAR');
    if (!checks.hasMainContent) issues.push('NO_MAINCONTENT');
    if (checks.hasPhpError)   issues.push('PHP_ERROR');
    // Filter noise from console errors (CORS, extension, etc.)
    const realErrors = errors.filter(e =>
        !e.includes('favicon') &&
        !e.includes('chrome-extension') &&
        !e.includes('ERR_BLOCKED_BY_CLIENT') &&
        !e.includes('net::ERR_ABORTED') &&
        !e.includes('Failed to load resource') &&
        !e.includes('ERR_CONNECTION_REFUSED')
    );
    if (realErrors.length > 0) issues.push(`JS_ERR(${realErrors.length})`);

    return { name, url, issues, httpCode, jsErrors: realErrors, warnings };
}

async function run() {
    console.log('='.repeat(65));
    console.log('  COMPREHENSIVE PUPPETEER TEST — ALL PAGES');
    console.log(`  Total: ${ALL_PAGES.length} pages`);
    console.log('='.repeat(65));

    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox','--disable-setuid-sandbox','--disable-dev-shm-usage']
    });

    const results = [];
    for (const pageInfo of ALL_PAGES) {
        const result = await testPage(browser, pageInfo);
        const status = result.issues.length === 0 ? 'PASS' : 'FAIL';
        const marker = status === 'PASS' ? '✓' : '✗';
        console.log(`  ${marker} [${result.httpCode}] ${result.name}` +
            (result.issues.length ? `  → ${result.issues.join(', ')}` : ''));
        if (result.jsErrors.length > 0) {
            result.jsErrors.forEach(e => console.log(`      JS: ${e}`));
        }
        results.push({ ...result, status });
    }

    await browser.close();

    const passed = results.filter(r => r.status === 'PASS').length;
    const failed = results.filter(r => r.status === 'FAIL').length;

    console.log('');
    console.log('='.repeat(65));
    console.log(`  RESULT: ${passed} PASSED  |  ${failed} FAILED  |  ${results.length} TOTAL`);
    console.log('='.repeat(65));

    if (failed > 0) {
        console.log('\n  FAILURES:');
        results.filter(r => r.status === 'FAIL').forEach(r => {
            console.log(`    ✗ ${r.name}: ${r.issues.join(', ')}`);
        });
    }

    // Save results to JSON
    const fs = require('fs');
    fs.writeFileSync(
        '/opt/lampp/htdocs/mono-v2/logs/puppeteer_test_results.json',
        JSON.stringify({ timestamp: new Date().toISOString(), passed, failed, total: results.length, results }, null, 2)
    );
    console.log('\n  Results saved to logs/puppeteer_test_results.json');

    process.exit(failed > 0 ? 1 : 0);
}

run().catch(err => {
    console.error('Test runner failed:', err);
    process.exit(1);
});
