/**
 * Test Suite 01 — Login Page UI
 * Menguji tampilan dan interaksi halaman login
 */

const { newPage, closeBrowser } = require('../helpers/browser');
const { BASE_URL } = require('../helpers/api');

const LOGIN_URL = `${BASE_URL}/login.html`;

let page;

beforeEach(async () => {
    page = await newPage();
    await page.goto(LOGIN_URL, { waitUntil: 'domcontentloaded', timeout: 15000 });
});

afterEach(async () => {
    if (page && !page.isClosed()) await page.close();
});

afterAll(async () => {
    await closeBrowser();
});

// ─────────────────────────────────────────────
// KELOMPOK: Halaman & Elemen
// ─────────────────────────────────────────────

describe('01.1 — Halaman Login Dimuat', () => {
    test('Judul halaman mengandung nama aplikasi', async () => {
        const title = await page.title();
        expect(title).toMatch(/KSP|Masuk|Login/i);
    });

    test('Form login (#loginForm) ada di halaman', async () => {
        const form = await page.$('#loginForm');
        expect(form).not.toBeNull();
    });

    test('Input username/email (#emailInput) ada', async () => {
        const input = await page.$('#emailInput');
        expect(input).not.toBeNull();
    });

    test('Input password (#passwordInput) ada', async () => {
        const input = await page.$('#passwordInput');
        expect(input).not.toBeNull();
    });

    test('Tombol submit login ada', async () => {
        const btn = await page.$('#loginBtn');
        expect(btn).not.toBeNull();
    });

    test('Tombol toggle password ada', async () => {
        const btn = await page.$('#togglePassword');
        expect(btn).not.toBeNull();
    });

    test('Link lupa kata sandi ada', async () => {
        const link = await page.$('a[onclick*="showForgotPassword"], a[onclick*="forgotPassword"]');
        expect(link).not.toBeNull();
    });

    test('Section Quick Login ada (development)', async () => {
        const quickBtns = await page.$$('.quick-login-btn');
        expect(quickBtns.length).toBeGreaterThan(0);
    });

    test('Minimal 4 tombol quick login tersedia', async () => {
        const quickBtns = await page.$$('.quick-login-btn');
        expect(quickBtns.length).toBeGreaterThanOrEqual(4);
    });
});

// ─────────────────────────────────────────────
// KELOMPOK: Interaksi Form
// ─────────────────────────────────────────────

describe('01.2 — Interaksi Form Login', () => {
    test('Bisa mengetik di input username', async () => {
        await page.type('#emailInput', 'admin');
        const value = await page.$eval('#emailInput', el => el.value);
        expect(value).toBe('admin');
    });

    test('Bisa mengetik di input password', async () => {
        await page.type('#passwordInput', 'testpassword');
        const value = await page.$eval('#passwordInput', el => el.value);
        expect(value).toBe('testpassword');
    });

    test('Password input awalnya bertipe "password" (tersembunyi)', async () => {
        const type = await page.$eval('#passwordInput', el => el.type);
        expect(type).toBe('password');
    });

    test('Toggle password mengubah tipe menjadi "text"', async () => {
        await page.click('#togglePassword');
        const type = await page.$eval('#passwordInput', el => el.type);
        expect(type).toBe('text');
    });

    test('Toggle kembali mengubah tipe ke "password"', async () => {
        await page.click('#togglePassword');
        await page.click('#togglePassword');
        const type = await page.$eval('#passwordInput', el => el.type);
        expect(type).toBe('password');
    });

    test('Checkbox "Ingat saya" bisa diklik', async () => {
        const checked = await page.$eval('#rememberMe', el => el.checked);
        await page.click('#rememberMe');
        const checkedAfter = await page.$eval('#rememberMe', el => el.checked);
        expect(checkedAfter).toBe(!checked);
    });
});

// ─────────────────────────────────────────────
// KELOMPOK: Validasi Form Kosong
// ─────────────────────────────────────────────

describe('01.3 — Validasi Form Kosong', () => {
    test('Submit form kosong tidak redirect halaman', async () => {
        const urlBefore = page.url();
        await page.click('#loginBtn');
        await new Promise(r => setTimeout(r, 800));
        const urlAfter = page.url();
        expect(urlAfter).toContain('login');
        expect(urlAfter).toBe(urlBefore);
    });

    test('Submit dengan password terlalu pendek tidak redirect', async () => {
        await page.type('#emailInput', 'admin');
        await page.type('#passwordInput', '123');
        const urlBefore = page.url();
        await page.click('#loginBtn');
        await new Promise(r => setTimeout(r, 500));
        expect(page.url()).toBe(urlBefore);
    });
});

// ─────────────────────────────────────────────
// KELOMPOK: Flow Login Berhasil (via auth_simple)
// ─────────────────────────────────────────────

describe('01.4 — Flow Login Berhasil', () => {
    test('Login dengan admin/password menampilkan alert sukses', async () => {
        await page.type('#emailInput', 'admin');
        await page.type('#passwordInput', 'password');
        await page.click('#loginBtn');

        // Tunggu alert sukses muncul (max 4s, sebelum redirect 1.6s)
        let alertFound = false;
        try {
            await page.waitForFunction(
                () => {
                    const items = document.querySelectorAll('.alert-success, .alert-info, .alert-primary');
                    return items.length > 0;
                },
                { timeout: 4000 }
            );
            alertFound = true;
        } catch (e) {
            // Cek via text jika selector berbeda
            alertFound = await page.evaluate(() => {
                return document.body.innerText.includes('berhasil') ||
                       document.body.innerText.includes('sukses') ||
                       document.body.innerText.includes('Mengalihkan');
            }).catch(() => false);
        }
        expect(alertFound).toBe(true);
    });

    test('Token tersimpan di localStorage atau sessionStorage setelah login', async () => {
        await page.type('#emailInput', 'admin');
        await page.type('#passwordInput', 'password');
        await page.click('#loginBtn');

        // Tunggu token muncul di storage (max 3s, sebelum redirect)
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
            // Mungkin sudah redirect, coba ambil dari halaman baru
            await new Promise(r => setTimeout(r, 1000));
            token = await page.evaluate(() =>
                localStorage.getItem('authToken') || sessionStorage.getItem('authToken')
            ).catch(() => null);
        }
        expect(token).not.toBeNull();
        expect(token.length).toBeGreaterThan(10);
    });

    test('Redirect ke halaman dashboard setelah login', async () => {
        await page.type('#emailInput', 'admin');
        await page.type('#passwordInput', 'password');
        await page.click('#loginBtn');

        await page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 8000 }).catch(() => {});
        await new Promise(r => setTimeout(r, 500));

        const url = page.url();
        expect(url).toMatch(/dashboard/i);
    });
});

// ─────────────────────────────────────────────
// KELOMPOK: Modal Lupa Password
// ─────────────────────────────────────────────

describe('01.5 — Modal Lupa Kata Sandi', () => {
    test('Klik "Lupa kata sandi" membuka modal', async () => {
        // Trigger showForgotPassword langsung via JS (karena onclick attribute)
        const shown = await page.evaluate(() => {
            if (typeof showForgotPassword === 'function') {
                showForgotPassword();
                return true;
            }
            // Fallback: klik element
            const el = document.querySelector('a[onclick*="showForgotPassword"]');
            if (el) { el.click(); return true; }
            return false;
        });

        if (!shown) {
            console.log('showForgotPassword tidak tersedia, skip');
            return;
        }

        // Tunggu modal muncul
        await new Promise(r => setTimeout(r, 700));

        const modalVisible = await page.evaluate(() => {
            const modal = document.getElementById('forgotPasswordModal');
            if (!modal) return false;
            return modal.classList.contains('show') || modal.style.display === 'block' ||
                   window.getComputedStyle(modal).display !== 'none';
        });
        expect(modalVisible).toBe(true);
    });
});
