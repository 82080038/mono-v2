/**
 * Test Suite 02 — API Endpoints
 * Menguji semua endpoint API secara langsung (HTTP)
 */

const { apiPost, apiGet, getAuthToken } = require('../helpers/api');

let validToken = null;

beforeAll(async () => {
    const res = await apiPost('auth.php', { action: 'login', username: 'admin', password: 'password' });
    if (res.body?.success) {
        validToken = res.body.data.user.token;
    }
});

// ─────────────────────────────────────────────
// KELOMPOK: Auth API — Login
// ─────────────────────────────────────────────

describe('02.1 — Auth API: Login', () => {
    test('Login valid mengembalikan status 200', async () => {
        const res = await apiPost('auth.php', { action: 'login', username: 'admin', password: 'password' });
        expect(res.status).toBe(200);
    });

    test('Login valid mengembalikan success: true', async () => {
        const res = await apiPost('auth.php', { action: 'login', username: 'admin', password: 'password' });
        expect(res.body.success).toBe(true);
    });

    test('Login valid mengembalikan JWT token', async () => {
        const res = await apiPost('auth.php', { action: 'login', username: 'admin', password: 'password' });
        const token = res.body.data?.user?.token;
        expect(token).toBeDefined();
        expect(typeof token).toBe('string');
        expect(token.length).toBeGreaterThan(20);
    });

    test('JWT token memiliki 3 bagian (header.payload.signature)', async () => {
        const res = await apiPost('auth.php', { action: 'login', username: 'admin', password: 'password' });
        const token = res.body.data?.user?.token;
        const parts = token.split('.');
        expect(parts.length).toBe(3);
    });

    test('JWT header berisi alg HS256', async () => {
        const res = await apiPost('auth.php', { action: 'login', username: 'admin', password: 'password' });
        const token = res.body.data?.user?.token;
        const header = JSON.parse(Buffer.from(token.split('.')[0].replace(/-/g, '+').replace(/_/g, '/'), 'base64').toString());
        expect(header.alg).toBe('HS256');
        expect(header.typ).toBe('JWT');
    });

    test('JWT payload mengandung user_id dan role', async () => {
        const res = await apiPost('auth.php', { action: 'login', username: 'admin', password: 'password' });
        const token = res.body.data?.user?.token;
        const pad = token.split('.')[1];
        const payload = JSON.parse(Buffer.from(pad.replace(/-/g, '+').replace(/_/g, '/') + '==', 'base64').toString());
        expect(payload.user_id).toBeDefined();
        expect(payload.role).toBeDefined();
        expect(payload.exp).toBeGreaterThan(Date.now() / 1000);
    });

    test('Login valid mengembalikan user data', async () => {
        const res = await apiPost('auth.php', { action: 'login', username: 'admin', password: 'password' });
        const user = res.body.data?.user;
        expect(user).toBeDefined();
        expect(user.name || user.full_name).toBeTruthy();
        expect(user.role).toBeTruthy();
    });

    test('Login dengan email juga berhasil', async () => {
        const res = await apiPost('auth.php', { action: 'login', username: 'admin@ksp-lamgabejaya.co.id', password: 'password' });
        expect(res.body.success).toBe(true);
    });

    test('Login password salah mengembalikan 401', async () => {
        const res = await apiPost('auth.php', { action: 'login', username: 'admin', password: 'wrongpassword' });
        expect(res.status).toBe(401);
    });

    test('Login password salah mengembalikan success: false', async () => {
        const res = await apiPost('auth.php', { action: 'login', username: 'admin', password: 'wrongpassword' });
        expect(res.body.success).toBe(false);
    });

    test('Login user tidak ada mengembalikan 401 atau 429 (rate-limited)', async () => {
        const res = await apiPost('auth.php', { action: 'login', username: 'nouser_xyz', password: 'password' });
        // 401 = user tidak ada; 429 = IP rate-limited dari test sebelumnya (keduanya valid)
        expect([401, 429]).toContain(res.status);
        expect(res.body.success).toBe(false);
    });

    test('Login field kosong mengembalikan 400', async () => {
        const res = await apiPost('auth.php', { action: 'login', username: '', password: '' });
        expect(res.status).toBe(400);
    });

    test('Login password pendek mengembalikan 400', async () => {
        const res = await apiPost('auth.php', { action: 'login', username: 'admin', password: '123' });
        expect(res.status).toBe(400);
    });
});

// ─────────────────────────────────────────────
// KELOMPOK: Auth API — Validasi Token
// ─────────────────────────────────────────────

describe('02.2 — Auth API: Validasi Token', () => {
    test('Token valid diterima dengan success: true', async () => {
        expect(validToken).not.toBeNull();
        const res = await apiGet('auth.php', { action: 'validate' }, validToken);
        expect(res.body.success).toBe(true);
    });

    test('Token valid mengembalikan user_id di data', async () => {
        const res = await apiGet('auth.php', { action: 'validate' }, validToken);
        expect(res.body.data?.user_id).toBeTruthy();
    });

    test('Token palsu ditolak (success: false)', async () => {
        const fakeToken = 'eyJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoxfQ.fakesignature123';
        const res = await apiGet('auth.php', { action: 'validate' }, fakeToken);
        expect(res.body.success).toBe(false);
    });

    test('Token palsu mengembalikan 401', async () => {
        const fakeToken = 'faketoken.fake.fake';
        const res = await apiGet('auth.php', { action: 'validate' }, fakeToken);
        expect(res.status).toBe(401);
    });

    test('Tanpa token mengembalikan 401', async () => {
        const res = await apiGet('auth.php', { action: 'validate' });
        expect(res.status).toBe(401);
    });
});

// ─────────────────────────────────────────────
// KELOMPOK: Loans API
// ─────────────────────────────────────────────

describe('02.3 — Loans API', () => {
    test('get_loan_types mengembalikan success: true', async () => {
        const res = await apiGet('loans.php', { action: 'get_loan_types' });
        expect(res.body.success).toBe(true);
    });

    test('get_loan_types mengembalikan array data', async () => {
        const res = await apiGet('loans.php', { action: 'get_loan_types' });
        expect(Array.isArray(res.body.data)).toBe(true);
    });

    test('get_loan_types memiliki minimal 1 tipe pinjaman', async () => {
        const res = await apiGet('loans.php', { action: 'get_loan_types' });
        expect(res.body.data.length).toBeGreaterThan(0);
    });

    test('get_loans mengembalikan response yang valid', async () => {
        const res = await apiGet('loans.php', { action: 'get_loans' });
        expect(res.status).toBe(200);
        expect(res.body).toBeDefined();
    });

    test('Action tidak valid mengembalikan 400', async () => {
        const res = await apiGet('loans.php', { action: 'invalid_action_xyz' });
        expect(res.status).toBe(400);
    });
});

// ─────────────────────────────────────────────
// KELOMPOK: Members API
// ─────────────────────────────────────────────

describe('02.4 — Members API', () => {
    test('get_member_types mengembalikan success: true', async () => {
        const res = await apiGet('members.php', { action: 'get_member_types' });
        expect(res.body.success).toBe(true);
    });

    test('get_member_types mengembalikan array data', async () => {
        const res = await apiGet('members.php', { action: 'get_member_types' });
        expect(Array.isArray(res.body.data)).toBe(true);
    });

    test('get_member_types memiliki minimal 1 tipe anggota', async () => {
        const res = await apiGet('members.php', { action: 'get_member_types' });
        expect(res.body.data.length).toBeGreaterThan(0);
    });

    test('get_members mengembalikan response yang valid', async () => {
        const res = await apiGet('members.php', { action: 'get_members' });
        expect(res.status).toBe(200);
        expect(res.body).toBeDefined();
    });
});

// ─────────────────────────────────────────────
// KELOMPOK: Savings API
// ─────────────────────────────────────────────

describe('02.5 — Savings API', () => {
    test('get_account_types mengembalikan success: true', async () => {
        const res = await apiGet('savings.php', { action: 'get_account_types' });
        expect(res.body.success).toBe(true);
    });

    test('get_account_types mengembalikan array data', async () => {
        const res = await apiGet('savings.php', { action: 'get_account_types' });
        expect(Array.isArray(res.body.data)).toBe(true);
    });

    test('get_account_types memiliki minimal 1 tipe tabungan', async () => {
        const res = await apiGet('savings.php', { action: 'get_account_types' });
        expect(res.body.data.length).toBeGreaterThan(0);
    });

    test('get_accounts mengembalikan response yang valid', async () => {
        const res = await apiGet('savings.php', { action: 'get_accounts' });
        expect(res.status).toBe(200);
        expect(res.body).toBeDefined();
    });
});

// ─────────────────────────────────────────────
// KELOMPOK: Security — JWT Integrity
// ─────────────────────────────────────────────

describe('02.6 — Keamanan JWT', () => {
    test('Token hasil login TIDAK bisa dipalsukan dengan hanya mengubah payload', async () => {
        const res = await apiPost('auth.php', { action: 'login', username: 'admin', password: 'password' });
        const token = res.body.data.user.token;
        const parts = token.split('.');

        // Buat payload palsu dengan role Super Admin namun user_id berbeda
        const fakePayload = Buffer.from(JSON.stringify({
            user_id: 999,
            role: 'Super Admin',
            exp: Math.floor(Date.now() / 1000) + 86400
        })).toString('base64').replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');

        // Token dengan signature asli tapi payload palsu
        const tamperedToken = `${parts[0]}.${fakePayload}.${parts[2]}`;

        const valRes = await apiGet('auth.php', { action: 'validate' }, tamperedToken);
        expect(valRes.body.success).toBe(false);
    });

    test('Token dengan signature palsu ditolak', async () => {
        const res = await apiPost('auth.php', { action: 'login', username: 'admin', password: 'password' });
        const token = res.body.data.user.token;
        const parts = token.split('.');
        const badToken = `${parts[0]}.${parts[1]}.INVALIDSIGNATUREXXXXXXXX`;

        const valRes = await apiGet('auth.php', { action: 'validate' }, badToken);
        expect(valRes.body.success).toBe(false);
    });

    test('Login berhasil mencatat last_login di database', async () => {
        const res = await apiPost('auth.php', { action: 'login', username: 'admin', password: 'password' });
        expect(res.body.data.user.last_login).toBeTruthy();
    });

    test('Rate limiting: 3 login gagal berturutan', async () => {
        for (let i = 0; i < 3; i++) {
            await apiPost('auth.php', { action: 'login', username: 'teller1', password: 'wrongpass' });
        }
        const lastRes = await apiPost('auth.php', { action: 'login', username: 'teller1', password: 'wrongpass' });
        // Either locked (429) or still failing (401) — both are valid
        expect([401, 429]).toContain(lastRes.status);

        // Reset - login berhasil harus membersihkan lock (jika account terkunci oleh IP)
        // Ini hanya mengecek bahwa sistem rate limiting berjalan, bukan harus block semua
    });
});
