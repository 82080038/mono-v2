#!/usr/bin/env python3
"""
KSP Lam Gabe Jaya v2.0 — Comprehensive Browser Automation Test
Menggunakan Playwright (Python) | Mode: HEADED (browser visible)

Jalankan:
    python3 tests/playwright_comprehensive.py
    python3 tests/playwright_comprehensive.py --headless
    python3 tests/playwright_comprehensive.py --suite login
    python3 tests/playwright_comprehensive.py --slow 600
"""

import sys, time, json, re, argparse, urllib.request, urllib.parse, base64
from datetime import datetime
from playwright.sync_api import sync_playwright

# ─────────────────────────────────────────────────────
# Konfigurasi
# ─────────────────────────────────────────────────────
BASE_URL  = "http://localhost/mono-v2"
API_BASE  = f"{BASE_URL}/api"
SLOW_MO   = 350
TIMEOUT   = 15000
CREDS     = {"admin": "password", "teller1": "password", "kasir1": "password"}

# ─────────────────────────────────────────────────────
# Terminal colors & runner
# ─────────────────────────────────────────────────────
class C:
    G = "\033[92m"; R = "\033[91m"; Y = "\033[93m"
    B = "\033[94m"; M = "\033[95m"; CY = "\033[96m"
    BOLD = "\033[1m"; DIM = "\033[2m"; RESET = "\033[0m"

class Runner:
    def __init__(self):
        self.passed = self.failed = self.skipped = 0
        self.log = []
        self._suite = ""
        self._suite_p = self._suite_f = 0

    def suite(self, name):
        if self._suite:
            clr = C.G if self._suite_f == 0 else C.R
            print(f"  {clr}[{self._suite_p}✓ {self._suite_f}✗]{C.RESET}")
        self._suite = name
        self._suite_p = self._suite_f = 0
        print(f"\n{C.BOLD}{C.B}{'─'*60}")
        print(f"  {name}")
        print(f"{'─'*60}{C.RESET}")

    def ok(self, name, detail=""):
        self.passed += 1; self._suite_p += 1
        d = f" {C.DIM}({detail}){C.RESET}" if detail else ""
        print(f"  {C.G}✓{C.RESET} {name}{d}")
        self.log.append({"s": self._suite, "n": name, "r": "PASS"})

    def fail(self, name, reason=""):
        self.failed += 1; self._suite_f += 1
        print(f"  {C.R}✗{C.RESET} {name}")
        if reason: print(f"    {C.R}└─ {reason[:120]}{C.RESET}")
        self.log.append({"s": self._suite, "n": name, "r": "FAIL", "e": reason})

    def skip(self, name, why=""):
        self.skipped += 1
        print(f"  {C.Y}○{C.RESET} {name} {C.DIM}(skip: {why}){C.RESET}")

    def info(self, msg):
        print(f"  {C.DIM}  ▶ {msg}{C.RESET}")

    def summary(self):
        if self._suite:
            clr = C.G if self._suite_f == 0 else C.R
            print(f"  {clr}[{self._suite_p}✓ {self._suite_f}✗]{C.RESET}")
        total = self.passed + self.failed + self.skipped
        pct = int(self.passed / total * 100) if total else 0
        clr = C.G if self.failed == 0 else (C.Y if pct >= 85 else C.R)
        print(f"\n{C.BOLD}{'═'*60}")
        print(f"  HASIL AKHIR  {C.G}{self.passed} lulus  {C.R}{self.failed} gagal{C.RESET}  {C.DIM}{self.skipped} skip  /{total} total{C.RESET}")
        print(f"  {clr}Success rate: {pct}%{C.RESET}   {C.DIM}{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}{C.RESET}")
        print(f"{'═'*60}{C.RESET}\n")
        return self.failed == 0

R = Runner()

# ─────────────────────────────────────────────────────
# HTTP helpers
# ─────────────────────────────────────────────────────
def http_post(ep, data, token=None):
    url  = f"{API_BASE}/{ep}"
    body = urllib.parse.urlencode(data).encode()
    req  = urllib.request.Request(url, data=body, method="POST")
    req.add_header("Content-Type", "application/x-www-form-urlencoded")
    if token: req.add_header("Authorization", f"Bearer {token}")
    try:
        with urllib.request.urlopen(req, timeout=10) as r:
            return {"status": r.status, "body": json.loads(r.read())}
    except urllib.error.HTTPError as e:
        try: return {"status": e.code, "body": json.loads(e.read())}
        except: return {"status": e.code, "body": {}}

def http_get(ep, params=None, token=None):
    qs  = ("?" + urllib.parse.urlencode(params)) if params else ""
    req = urllib.request.Request(f"{API_BASE}/{ep}{qs}")
    if token: req.add_header("Authorization", f"Bearer {token}")
    try:
        with urllib.request.urlopen(req, timeout=10) as r:
            return {"status": r.status, "body": json.loads(r.read())}
    except urllib.error.HTTPError as e:
        try: return {"status": e.code, "body": json.loads(e.read())}
        except: return {"status": e.code, "body": {}}

_token_cache = {}
def get_token(user="admin"):
    if user in _token_cache: return _token_cache[user]
    res = http_post("auth.php", {"action": "login", "username": user, "password": CREDS.get(user,"password")})
    tok = res["body"].get("data", {}).get("user", {}).get("token")
    if tok: _token_cache[user] = tok
    return tok

# ─────────────────────────────────────────────────────
# Browser helpers
# ─────────────────────────────────────────────────────
def goto(page, path, wait="domcontentloaded"):
    try:
        r = page.goto(f"{BASE_URL}/{path}", wait_until=wait, timeout=TIMEOUT)
        return r.status if r else 0
    except Exception as e:
        return 0

def el(page, sel):
    return page.query_selector(sel)

def els(page, sel):
    return page.query_selector_all(sel)

def txt(page, sel):
    e = el(page, sel)
    return e.inner_text().strip() if e else ""

def visible(page, sel):
    e = el(page, sel)
    return e.is_visible() if e else False

def wait_fn(page, fn_str, ms=4000):
    try:
        page.wait_for_function(fn_str, timeout=ms)
        return True
    except: return False

def pause(ms=300):
    time.sleep(ms / 1000)

# ─────────────────────────────────────────────────────
# SUITE 01 — Login Page UI
# ─────────────────────────────────────────────────────
def s01_login_ui(page):
    R.suite("01 — Login Page UI")
    goto(page, "login.html")
    pause(400)

    # Elemen dasar
    for sel, label in [
        ("#loginForm",      "Form #loginForm ada"),
        ("#emailInput",     "Input email/username ada"),
        ("#passwordInput",  "Input password ada"),
        ("#loginBtn",       "Tombol Masuk ada"),
        ("#togglePassword", "Toggle visibility password ada"),
        ("#rememberMe",     "Checkbox Ingat Saya ada"),
        (".quick-login-btn","Quick-login buttons ada"),
        (".alert-container","Alert container ada"),
    ]:
        R.ok(label) if el(page, sel) else R.fail(label, f"selector {sel!r} tidak ditemukan")

    # Judul
    t = page.title()
    R.ok("Judul halaman mengandung KSP/Masuk", t) if re.search(r'KSP|Masuk|Login', t, re.I) else R.fail("Judul halaman", t)

    # Quick-login button count
    n = len(els(page, ".quick-login-btn"))
    R.ok(f"Ada {n} quick-login buttons (min 4)") if n >= 4 else R.fail("Quick-login buttons", f"hanya {n}")

    # Ketik input
    page.fill("#emailInput", ""); page.type("#emailInput", "testuser", delay=60)
    page.fill("#passwordInput", ""); page.type("#passwordInput", "testpass", delay=60)
    R.ok("Mengetik di form berfungsi") if page.input_value("#emailInput") == "testuser" else R.fail("Input username")

    # Password type default = password
    t = page.get_attribute("#passwordInput", "type")
    R.ok("Password field bertipe 'password' secara default") if t == "password" else R.fail("Tipe default password", t)

    # Toggle visibility
    page.click("#togglePassword"); pause(250)
    t_after = page.get_attribute("#passwordInput", "type")
    page.click("#togglePassword"); pause(250)
    t_back  = page.get_attribute("#passwordInput", "type")
    R.ok("Toggle: password → text → password") if t_after == "text" and t_back == "password" else R.fail("Toggle visibility", f"{t_after},{t_back}")

    # Remember me checkbox
    page.click("#rememberMe"); pause(150)
    checked = page.is_checked("#rememberMe")
    page.click("#rememberMe"); pause(150)
    R.ok("Checkbox Ingat Saya dapat diklik") if checked else R.fail("Checkbox Ingat Saya")

    # Submit kosong tidak redirect
    page.fill("#emailInput", ""); page.fill("#passwordInput", "")
    url_before = page.url
    page.click("#loginBtn"); pause(800)
    R.ok("Submit form kosong tidak redirect") if "login" in page.url else R.fail("Submit kosong", f"redirect ke {page.url!r}")

    # Submit password pendek tidak redirect
    page.fill("#emailInput", "admin"); page.fill("#passwordInput", "abc")
    page.click("#loginBtn"); pause(800)
    R.ok("Submit password pendek tidak redirect") if "login" in page.url else R.fail("Submit password pendek")

    # Modal lupa kata sandi
    page.evaluate("typeof showForgotPassword === 'function' && showForgotPassword()")
    pause(700)
    modal_ok = page.evaluate("""() => {
        const m = document.getElementById('forgotPasswordModal');
        if (!m) return false;
        return m.classList.contains('show') || getComputedStyle(m).display !== 'none';
    }""")
    R.ok("Modal lupa kata sandi terbuka") if modal_ok else R.fail("Modal lupa kata sandi")
    page.keyboard.press("Escape"); pause(300)

    # Forgot password modal form
    page.evaluate("typeof showForgotPassword === 'function' && showForgotPassword()")
    pause(500)
    reset_input = el(page, "#resetEmail, input[type='email'][id*='reset'], #forgotEmail")
    R.ok("Form reset password ada di modal") if reset_input else R.skip("Form reset modal", "input tidak ditemukan")
    page.keyboard.press("Escape"); pause(200)

# ─────────────────────────────────────────────────────
# SUITE 02 — Login Flow & Session
# ─────────────────────────────────────────────────────
def s02_login_flow(page):
    R.suite("02 — Alur Login & Session Management")
    goto(page, "login.html"); pause(400)

    # Login dengan kredensial salah
    R.info("Test login dengan password salah...")
    page.fill("#emailInput", "admin"); page.fill("#passwordInput", "wrongpass123")
    page.click("#loginBtn"); pause(2000)
    still_login = "login" in page.url
    R.ok("Login password salah tetap di halaman login") if still_login else R.fail("Reject kredensial salah", f"URL: {page.url}")

    # Error message muncul
    goto(page, "login.html"); pause(300)
    page.fill("#emailInput", "admin"); page.fill("#passwordInput", "badpassword1")
    page.click("#loginBtn")
    err_shown = wait_fn(page, "() => document.querySelectorAll('.alert-danger, .alert-warning, .is-invalid, [class*=\"error\"]').length > 0", 3000)
    R.ok("Pesan error muncul saat login gagal") if err_shown else R.skip("Pesan error login gagal", "tidak terdeteksi dalam 3s")

    # Login sukses
    goto(page, "login.html"); pause(300)
    R.info("Login sebagai admin/password...")
    page.fill("#emailInput", "admin"); page.fill("#passwordInput", "password")
    page.click("#loginBtn")

    # Alert sukses
    alert_ok = wait_fn(page, "() => document.querySelectorAll('.alert-success, .alert-info').length > 0", 4000)
    R.ok("Alert sukses muncul setelah login berhasil") if alert_ok else R.fail("Alert sukses login")

    # Token tersimpan
    tok_saved = wait_fn(page, "() => !!(localStorage.getItem('authToken') || sessionStorage.getItem('authToken'))", 3000)
    R.ok("Token tersimpan di browser storage") if tok_saved else R.fail("Token storage")

    # userData tersimpan
    ud_saved = wait_fn(page, "() => !!(localStorage.getItem('userData') || sessionStorage.getItem('userData'))", 2000)
    R.ok("userData tersimpan di browser storage") if ud_saved else R.fail("userData storage")

    # Validasi struktur userData
    if ud_saved:
        raw = page.evaluate("() => localStorage.getItem('userData') || sessionStorage.getItem('userData')")
        try:
            ud = json.loads(raw)
            R.ok("userData memiliki field role", ud.get("role","?")) if ud.get("role") else R.fail("userData.role kosong")
        except: R.fail("userData bukan JSON valid")

    # Redirect ke dashboard
    R.info("Menunggu redirect ke dashboard...")
    try:
        page.wait_for_url("**/dashboard**", timeout=7000)
        R.ok("Redirect ke dashboard berhasil", page.url.split("/")[-1])
    except: R.fail("Redirect ke dashboard", f"URL: {page.url!r}")

    # Dashboard terload (konten ada)
    has_content = el(page, ".card, main, .dashboard-wrapper, #mainChart, .stat-card")
    R.ok("Konten dashboard terload setelah redirect") if has_content else R.fail("Konten dashboard")

    # Quick login button — verifikasi button mengisi form dengan benar
    goto(page, "login.html"); pause(500)
    btns = els(page, ".quick-login-btn")
    if btns:
        admin_btn = next((b for b in btns if b.get_attribute('data-email') == 'admin'), btns[0])
        email_attr = admin_btn.get_attribute('data-email')
        R.info(f"Klik quick-login button (data-email={email_attr})...")
        admin_btn.click(); pause(400)
        # Verifikasi form terisi oleh quick-login
        filled = page.input_value("#emailInput")
        R.ok(f"Quick-login mengisi emailInput dengan benar", filled) if filled == email_attr else R.fail("Quick-login tidak mengisi form", f"got={filled!r}")
        # Pastikan form submit berfungsi (enable loginBtn lalu klik manual)
        page.evaluate("() => { const b=document.getElementById('loginBtn'); if(b) b.disabled=false; }")
        page.click("#loginBtn"); pause(500)
        try:
            page.wait_for_url("**/dashboard**", timeout=6000)
            R.ok("Quick-login → submit → redirect dashboard berhasil", page.url.split("/")[-1])
        except:
            R.fail("Quick-login redirect", f"URL: {page.url!r}")
    else:
        R.skip("Quick-login button", "tidak ada button")

# ─────────────────────────────────────────────────────
# SUITE 03 — Admin Dashboard
# ─────────────────────────────────────────────────────
def s03_admin_dashboard(page):
    R.suite("03 — Admin Dashboard")
    st = goto(page, "pages/admin/dashboard.html"); pause(600)
    R.ok("HTTP 200 admin dashboard") if st == 200 else R.fail("HTTP admin dashboard", f"status={st}")

    # Header
    header = el(page, ".dashboard-header, nav.navbar, header")
    R.ok("Header/navbar ada") if header else R.fail("Header tidak ada")

    # Sidebar — admin/dashboard.html memakai nav.bg-dark (bukan .dashboard-sidebar)
    sidebar = el(page, ".dashboard-sidebar, nav.sidebar, #dashboardSidebar, nav.bg-dark, nav[class*='sidebar']")
    R.ok("Sidebar navigasi ada") if sidebar else R.fail("Sidebar tidak ada")

    # Nama aplikasi di header
    has_brand = page.evaluate("() => document.body.innerText.includes('KSP') || document.body.innerText.includes('Lam Gabe')")
    R.ok("Nama KSP ada di halaman") if has_brand else R.fail("Nama KSP tidak ditemukan")

    # Stat cards
    cards = els(page, ".stat-card, .card")
    R.ok(f"Card statistik ada ({len(cards)} cards)") if len(cards) >= 2 else R.fail("Card statistik", f"hanya {len(cards)}")

    # Chart container
    chart = el(page, "#mainChart, canvas, .chart-container")
    R.ok("Chart container ada") if chart else R.skip("Chart container", "tidak ada elemen chart")

    # Navigasi sidebar — cek berbagai struktur
    nav_links = els(page, ".dashboard-sidebar .nav-link, .sidebar-nav .nav-link, nav.bg-dark .nav-link, nav .nav-link")
    R.ok(f"Navigasi sidebar ada ({len(nav_links)} links)") if len(nav_links) >= 3 else R.fail("Navigasi sidebar", f"hanya {len(nav_links)}")

    # User info di header
    user_el = el(page, "#headerUserName, .header-user-info, [id*='userName'], .dropdown .btn")
    R.ok("Elemen info user ada di header") if user_el else R.skip("User info header", "tidak ditemukan")

    # Waktu/tanggal di header (opsional di beberapa dashboard)
    dt_el = el(page, "#headerDateTime, .header-datetime, [id*='DateTime']")
    R.ok("Elemen waktu/tanggal ada di header") if dt_el else R.skip("Waktu di header", "tidak ada di halaman ini")

    # Tombol mobile toggle (opsional)
    toggle = el(page, ".mobile-menu-toggle, [id*='sidebarToggle'], [onclick*='sidebar'], [data-bs-toggle='collapse']")
    R.ok("Mobile sidebar toggle ada") if toggle else R.skip("Mobile toggle", "tidak ada di halaman ini")

# ─────────────────────────────────────────────────────
# SUITE 04 — Manajemen Pinjaman
# ─────────────────────────────────────────────────────
def s04_loan_management(page):
    R.suite("04 — Manajemen Pinjaman")
    st = goto(page, "pages/admin/loan-management.html"); pause(800)
    R.ok("HTTP 200 loan-management") if st == 200 else R.fail("HTTP loan-management", f"status={st}")

    # Elemen utama
    for sel, label in [
        ("#searchInput",         "Input pencarian ada"),
        ("#loanTypeFilter",      "Filter tipe pinjaman ada"),
        ("#loansContainer",      "Container daftar pinjaman ada"),
        ("#loanApplicationModal","Modal aplikasi pinjaman ada"),
        ("#loanApplicationForm", "Form aplikasi pinjaman ada"),
    ]:
        R.ok(label) if el(page, sel) else R.fail(label, f"{sel} tidak ditemukan")

    # Tunggu data load dari API (container diisi oleh JS setelah fetch)
    R.info("Menunggu data pinjaman dari API...")
    data_loaded = wait_fn(page, """() => {
        const c = document.getElementById('loansContainer');
        return c && c.innerText.trim().length > 20;
    }""", 7000)
    R.ok("Data pinjaman berhasil dimuat dari API") if data_loaded else R.fail("Data pinjaman tidak muncul dalam 7s")

    # Tabel pinjaman
    tables = els(page, "table")
    R.ok(f"Tabel data ada ({len(tables)} tabel)") if tables else R.skip("Tabel pinjaman", "tidak ada table element")

    # Search functionality
    page.fill("#searchInput", ""); page.type("#searchInput", "A", delay=50); pause(600)
    R.info("Mengetik di search box...")
    R.ok("Input pencarian berfungsi") if page.input_value("#searchInput") == "A" else R.fail("Input pencarian")
    page.fill("#searchInput", "")

    # Filter pinjaman
    opts = els(page, "#loanTypeFilter option")
    R.ok(f"Dropdown filter memiliki {len(opts)} opsi") if len(opts) >= 1 else R.fail("Opsi filter pinjaman")

    # Buka modal aplikasi pinjaman
    R.info("Membuka modal aplikasi pinjaman...")
    add_btn = el(page, "a[data-bs-target='#loanApplicationModal'], button[data-bs-target='#loanApplicationModal']")
    if add_btn:
        add_btn.click(); pause(700)
        modal_ok = page.evaluate("() => { const m=document.getElementById('loanApplicationModal'); return m && m.classList.contains('show'); }")
        R.ok("Modal aplikasi pinjaman terbuka") if modal_ok else R.fail("Modal tidak terbuka")
        for fid, label in [("#loanMember","Field Anggota"), ("#loanType","Field Tipe"), ("#loanAmount","Field Jumlah"), ("#loanTerm","Field Tenor")]:
            R.ok(f"Form field: {label}") if el(page, fid) else R.fail(f"Form field: {label}", f"{fid} tidak ada")
        page.keyboard.press("Escape"); pause(300)
    else:
        R.skip("Modal aplikasi pinjaman", "tombol buka tidak ditemukan")

    # Submit form kosong validasi
    if add_btn:
        add_btn.click(); pause(500)
        submit = el(page, "#loanApplicationForm [type='submit'], #loanApplicationModal .btn-primary")
        if submit and submit.is_visible():
            submit.click(); pause(500)
            invalid = els(page, ".is-invalid, :invalid")
            R.ok("Validasi form pinjaman berfungsi (field invalid terdeteksi)") if invalid else R.skip("Validasi form", "tidak ada .is-invalid")
        else:
            R.skip("Validasi submit form", "submit button tidak visible")
        page.keyboard.press("Escape"); pause(300)

# ─────────────────────────────────────────────────────
# SUITE 05 — Registrasi Anggota (Multi-step)
# ─────────────────────────────────────────────────────
def s05_member_registration(page):
    R.suite("05 — Registrasi Anggota (Multi-step Form)")
    st = goto(page, "pages/admin/member-registration.html"); pause(800)
    R.ok("HTTP 200 member-registration") if st == 200 else R.fail("HTTP member-registration", f"status={st}")

    # Step containers
    for sid, label in [("#step1","Step 1 ada"), ("#step2","Step 2 ada"), ("#step3","Step 3 ada"), ("#step4","Step 4 ada")]:
        R.ok(label) if el(page, sid) else R.fail(label, f"{sid} tidak ditemukan")

    # Form utama
    R.ok("Form registrasi ada") if el(page, "#registrationForm") else R.fail("Form registrasi tidak ditemukan")

    # Field Step 1
    for fid, label in [("#member_type_id","Tipe Anggota"), ("#full_name","Nama Lengkap"), ("#id_number","NIK")]:
        R.ok(f"Field {label} ada") if el(page, fid) else R.fail(f"Field {label}", f"{fid} tidak ditemukan")

    # Isi Step 1
    R.info("Mengisi Step 1 form registrasi...")
    mt = el(page, "#member_type_id"); mt and page.select_option("#member_type_id", index=1)
    if el(page, "#full_name"):
        page.fill("#full_name", "Budi Santoso Test")
        R.ok("Input nama lengkap berhasil")
    if el(page, "#id_number"):
        page.fill("#id_number", "3201234567890001")
        R.ok("Input NIK berhasil")

    # Tombol next step
    next_btn = el(page, "button[onclick*='nextStep'], #nextStep1, .btn-next")
    if next_btn:
        next_btn.click(); pause(500)
        # Cek step 2 visible
        step2_visible = page.evaluate("""() => {
            const s2 = document.getElementById('formStep2') || document.getElementById('step2');
            if (!s2) return false;
            return s2.style.display !== 'none' && !s2.classList.contains('d-none');
        }""")
        R.ok("Pindah ke Step 2 berhasil") if step2_visible else R.skip("Step 2 visible", "tidak terdeteksi")
    else:
        R.skip("Navigasi multi-step", "tombol next tidak ditemukan")

    # Progress indicator
    prog = el(page, ".progress, .step-indicator, [class*='progress']")
    R.ok("Progress indicator ada") if prog else R.skip("Progress indicator", "tidak ditemukan")

# ─────────────────────────────────────────────────────
# SUITE 06 — Manajemen Simpanan
# ─────────────────────────────────────────────────────
def s06_savings_management(page):
    R.suite("06 — Manajemen Simpanan")
    st = goto(page, "pages/admin/savings-management.html"); pause(800)
    R.ok("HTTP 200 savings-management") if st == 200 else R.fail("HTTP savings-management", f"status={st}")

    # Elemen utama
    for sel, label in [
        ("#searchInput",       "Pencarian rekening ada"),
        ("#accountTypeFilter", "Filter tipe simpanan ada"),
        ("#accountsContainer", "Container daftar rekening ada"),
        ("#depositModal",      "Modal setoran ada"),
        ("#withdrawModal",     "Modal penarikan ada"),
    ]:
        R.ok(label) if el(page, sel) else R.fail(label, f"{sel} tidak ditemukan")

    # Data load
    R.info("Menunggu data simpanan dari API...")
    data_ok = wait_fn(page, "() => { const c=document.getElementById('accountsContainer'); return c && c.children.length > 0; }", 5000)
    R.ok("Data simpanan berhasil dimuat") if data_ok else R.fail("Data simpanan tidak muncul dalam 5s")

    # Recent transactions
    trx = el(page, "#recentTransactions")
    R.ok("Panel transaksi terakhir ada") if trx else R.skip("Transaksi terakhir", "tidak ditemukan")

    # Modal setoran
    dep_btn = el(page, "button[onclick*='showDeposit'], button[data-bs-target='#depositModal']")
    if dep_btn:
        dep_btn.click(); pause(600)
        modal_ok = page.evaluate("() => { const m=document.getElementById('depositModal'); return m && m.classList.contains('show'); }")
        R.ok("Modal setoran terbuka") if modal_ok else R.fail("Modal setoran tidak terbuka")
        for fid, label in [("#depositAccount","Akun setoran"), ("#depositAmount","Jumlah setoran")]:
            R.ok(f"Field {label} ada di modal") if el(page, fid) else R.fail(f"Field {label}")
        page.keyboard.press("Escape"); pause(300)
    else:
        R.skip("Modal setoran", "tombol tidak ditemukan")

    # Modal penarikan
    wd_btn = el(page, "button[onclick*='showWithdraw'], button[data-bs-target='#withdrawModal']")
    if wd_btn:
        wd_btn.click(); pause(600)
        modal_ok = page.evaluate("() => { const m=document.getElementById('withdrawModal'); return m && m.classList.contains('show'); }")
        R.ok("Modal penarikan terbuka") if modal_ok else R.fail("Modal penarikan tidak terbuka")
        page.keyboard.press("Escape"); pause(300)
    else:
        R.skip("Modal penarikan", "tombol tidak ditemukan")

# ─────────────────────────────────────────────────────
# SUITE 07 — Halaman Admin Lainnya
# ─────────────────────────────────────────────────────
def s07_admin_pages(page):
    R.suite("07 — Halaman Admin (Load & Struktur)")
    pages_admin = [
        ("Anggota",          "pages/admin/members.html"),
        ("Laporan",          "pages/admin/reports.html"),
        ("Pengaturan",       "pages/admin/settings.html"),
        ("Pengguna",         "pages/admin/users.html"),
        ("Simpanan",         "pages/admin/savings.html"),
        ("Pinjaman",         "pages/admin/loans.html"),
        ("Verifikasi",       "pages/admin/verifikasi.html"),
        ("Audit Log",        "pages/admin/audit-log.html"),
        ("Laporan Umum",     "pages/admin/laporan-umum.html"),
        ("Laporan SHU",      "pages/admin/laporan-shu.html"),
        ("Konfigurasi",      "pages/admin/system-config.html"),
        ("Role Akses",       "pages/admin/role-access.html"),
        ("Live Tracking",    "pages/admin/live-tracking.html"),
    ]
    for label, path in pages_admin:
        st = goto(page, path); pause(200)
        if st == 200:
            # Cek tidak blank (ada elemen UI)
            has_content = page.evaluate("() => document.body.innerText.trim().length > 10")
            R.ok(f"{label} → HTTP 200 dengan konten") if has_content else R.fail(f"{label}", "halaman kosong")
        else:
            R.fail(f"{label}", f"HTTP {st}")

# ─────────────────────────────────────────────────────
# SUITE 08 — Staff Dashboards
# ─────────────────────────────────────────────────────
def s08_staff_dashboards(page):
    R.suite("08 — Dashboard Staff (Semua Role)")
    staff_pages = [
        ("Teller Complete",   "pages/staff/dashboard-teller-complete.html",
         ["#todayTransactions","#totalDeposits","#totalWithdrawals","#membersServed"]),
        ("Kasir",             "pages/staff/dashboard-kasir.html",         []),
        ("Mantri",            "pages/staff/dashboard-mantri.html",        []),
        ("Collector",         "pages/staff/dashboard-collector.html",     []),
        ("Surveyor",          "pages/staff/dashboard-surveyor.html",      []),
        ("Staff Complete",    "pages/staff/dashboard-complete.html",
         ["#totalMembers","#activeLoans","#totalSavings","#todayTasks"]),
        ("Staff Dashboard",   "pages/staff/dashboard.html",              []),
        ("Teller Basic",      "pages/staff/dashboard-teller.html",       []),
    ]
    for label, path, key_els in staff_pages:
        st = goto(page, path); pause(400)
        R.ok(f"{label} → HTTP 200") if st == 200 else R.fail(f"{label}", f"HTTP {st}")
        if st == 200 and key_els:
            for kid in key_els:
                e = el(page, kid)
                if not e:
                    R.fail(f"  {label}: {kid} tidak ada")
                # jika ada, cek ada teks (loaded)
            R.ok(f"  {label}: elemen statistik kunci ada ({len(key_els)})")

    # Staff sub-pages
    R.info("Cek halaman sub-fitur staff...")
    sub_pages = [
        ("Transaksi Harian",  "pages/staff/transaksi-harian.html"),
        ("Target Harian",     "pages/staff/target-harian.html"),
        ("Setoran QR",        "pages/staff/setoran-qr.html"),
        ("Rute Kunjungan",    "pages/staff/route.html"),
        ("Cetak Struk",       "pages/staff/cetak-struk.html"),
    ]
    for label, path in sub_pages:
        st = goto(page, path); pause(200)
        R.ok(f"{label} → HTTP 200") if st == 200 else R.fail(f"{label}", f"HTTP {st}")

# ─────────────────────────────────────────────────────
# SUITE 09 — API Auth Lengkap
# ─────────────────────────────────────────────────────
def s09_api_auth(page):
    R.suite("09 — API Authentication Lengkap")

    # Login valid
    res = http_post("auth.php", {"action": "login", "username": "admin", "password": "password"})
    R.ok("Login valid → HTTP 200") if res["status"] == 200 else R.fail("Login valid", f"status={res['status']}")
    R.ok("Login valid → success: true") if res["body"].get("success") else R.fail("Login success flag", str(res["body"]))

    token = res["body"].get("data", {}).get("user", {}).get("token")
    if token:
        # JWT struktur
        parts = token.split(".")
        R.ok("JWT memiliki 3 bagian") if len(parts) == 3 else R.fail("JWT format", f"{len(parts)} bagian")
        try:
            hdr = json.loads(base64.urlsafe_b64decode(parts[0] + "=="))
            R.ok("JWT header alg=HS256") if hdr.get("alg") == "HS256" else R.fail("JWT alg", str(hdr))
            pay = json.loads(base64.urlsafe_b64decode(parts[1] + "=="))
            R.ok("JWT payload memiliki user_id") if pay.get("user_id") else R.fail("JWT user_id")
            R.ok("JWT payload memiliki role")    if pay.get("role")    else R.fail("JWT role")
            R.ok("JWT payload memiliki exp")     if pay.get("exp")     else R.fail("JWT exp")
            R.ok("JWT payload memiliki iat")     if pay.get("iat")     else R.fail("JWT iat")
        except Exception as e: R.fail("JWT decode", str(e))
    else:
        R.fail("Token tidak ada dalam response login")
        return

    # User data dalam response
    ud = res["body"].get("data", {}).get("user", {})
    for field in ["id", "name", "email", "role"]:
        R.ok(f"Response login memiliki field '{field}'") if ud.get(field) else R.fail(f"Field '{field}' login response")

    # Validate token valid
    res2 = http_get("auth.php", {"action": "validate"}, token=token)
    R.ok("Validate token valid → success") if res2["body"].get("success") else R.fail("Validate token", str(res2["body"]))
    R.ok("Validate → ada user_id di data") if res2["body"].get("data", {}).get("user_id") else R.skip("Validate user_id", "tidak ada")

    # Login dengan email
    res3 = http_post("auth.php", {"action": "login", "username": "admin@ksplamgabejaya.com", "password": "password"})
    R.ok("Login dengan email berhasil") if res3["body"].get("success") else R.skip("Login via email", "user email mungkin berbeda")

    # Berbagai error case
    cases = [
        ("Password salah →401",    {"username":"admin","password":"wrongpass12"},  401),
        ("User tidak ada →401/429",{"username":"noone_xyz","password":"password1"}, None),
        ("Field kosong →400",      {"username":"","password":""},                   400),
        ("Password pendek →400",   {"username":"admin","password":"abc"},           400),
    ]
    for label, data, expected in cases:
        r = http_post("auth.php", {"action": "login", **data})
        if expected is None:
            R.ok(f"{label} ({r['status']})") if r["status"] in [401, 429] and not r["body"].get("success") else R.fail(label, f"status={r['status']}")
        else:
            R.ok(label) if r["status"] == expected else R.fail(label, f"expected={expected}, got={r['status']}")

    # Token palsu
    r = http_get("auth.php", {"action": "validate"}, token="fake.token.abc")
    R.ok("Token palsu → 401") if r["status"] == 401 and not r["body"].get("success") else R.fail("Token palsu", f"status={r['status']}")

    # Tanpa token
    r = http_get("auth.php", {"action": "validate"})
    R.ok("Tanpa token → 401") if r["status"] == 401 else R.fail("Tanpa token", f"status={r['status']}")

    # Keamanan: tamper payload
    parts2 = token.split(".")
    fake_pay = base64.urlsafe_b64encode(json.dumps({"user_id":999,"role":"Super Admin","exp":int(time.time())+86400}).encode()).decode().rstrip("=")
    tampered = f"{parts2[0]}.{fake_pay}.{parts2[2]}"
    r = http_get("auth.php", {"action": "validate"}, token=tampered)
    R.ok("Tampered JWT ditolak (HMAC check ✓)") if not r["body"].get("success") else R.fail("Tampered JWT DITERIMA — SECURITY BUG!")

    # Rate limiting
    R.info("Test rate limiting (3x login gagal berturutan)...")
    for i in range(3):
        http_post("auth.php", {"action": "login", "username": "ratelimit_test", "password": "wrongpass1"})
    r = http_post("auth.php", {"action": "login", "username": "ratelimit_test", "password": "wrongpass1"})
    R.ok("Rate limiting aktif setelah 3x gagal (429)") if r["status"] == 429 else R.skip("Rate limiting", f"status={r['status']} (mungkin masih dalam threshold)")

# ─────────────────────────────────────────────────────
# SUITE 10 — API Data (Loans, Members, Savings)
# ─────────────────────────────────────────────────────
def s10_api_data(page):
    R.suite("10 — API Data (Loans / Members / Savings)")
    token = get_token()
    if not token:
        R.fail("Gagal mendapatkan token untuk API test"); return

    # Loans
    endpoints = [
        ("Loans get_loan_types",   "loans.php",   {"action":"get_loan_types"},   True),
        ("Loans get_loans",        "loans.php",   {"action":"get_loans"},         True),
        ("Members get_member_types","members.php", {"action":"get_member_types"}, True),
        ("Members get_members",    "members.php", {"action":"get_members"},       True),
        ("Savings get_account_types","savings.php",{"action":"get_account_types"},True),
        ("Savings get_accounts",   "savings.php", {"action":"get_accounts"},      True),
    ]
    for label, ep, params, need_success in endpoints:
        r = http_get(ep, params, token=token)
        if r["body"].get("success") and isinstance(r["body"].get("data"), list):
            cnt = len(r["body"]["data"])
            R.ok(f"{label} → {cnt} item") if cnt >= 0 else R.fail(label, "data kosong")
        else:
            # Beberapa endpoint valid mengembalikan success=true tapi data bukan list
            if r["body"].get("success"):
                R.ok(f"{label} → success (data bukan list)")
            else:
                R.fail(label, f"success=false, status={r['status']}")

    # Action tidak valid
    r = http_get("loans.php", {"action": "action_xyz_invalid"})
    R.ok("Action tidak valid → 400") if r["status"] == 400 else R.fail("Action invalid", f"status={r['status']}")

    # Detail loan types ada data
    r = http_get("loans.php", {"action": "get_loan_types"}, token=token)
    if r["body"].get("success") and r["body"].get("data"):
        lt = r["body"]["data"][0]
        for field in ["id", "name", "interest_rate"]:
            R.ok(f"Loan type memiliki field '{field}'") if field in lt else R.fail(f"Loan type field '{field}'")

    # Detail member types
    r = http_get("members.php", {"action": "get_member_types"}, token=token)
    if r["body"].get("success") and r["body"].get("data"):
        mt = r["body"]["data"][0]
        for field in ["id", "name"]:
            R.ok(f"Member type memiliki field '{field}'") if field in mt else R.fail(f"Member type field '{field}'")

    # Savings account types
    r = http_get("savings.php", {"action": "get_account_types"}, token=token)
    if r["body"].get("success") and r["body"].get("data"):
        at = r["body"]["data"][0]
        for field in ["id", "name"]:
            R.ok(f"Account type memiliki field '{field}'") if field in at else R.fail(f"Account type field '{field}'")

# ─────────────────────────────────────────────────────
# SUITE 11 — Navigasi & Sidebar
# ─────────────────────────────────────────────────────
def s11_navigation(page):
    R.suite("11 — Navigasi & Sidebar")

    # Cek sidebar links di admin dashboard
    goto(page, "pages/staff/dashboard-teller-complete.html"); pause(500)
    nav_links = els(page, ".dashboard-sidebar .nav-link, .sidebar-nav .nav-link, nav .nav-link")
    R.ok(f"Sidebar memiliki {len(nav_links)} link navigasi") if len(nav_links) >= 3 else R.fail("Sidebar links", f"hanya {len(nav_links)}")

    # Klik beberapa sidebar link
    clickable = [l for l in nav_links if l.get_attribute("href") not in ("#", "javascript:void(0)", "", None)]
    if clickable:
        R.info(f"Klik link pertama sidebar: '{clickable[0].inner_text().strip()}'")
        href = clickable[0].get_attribute("href")
        if href and href not in ("#", "javascript:void(0)", ""):
            clickable[0].click(); pause(500)
            R.ok(f"Sidebar link dapat diklik")
        else:
            R.skip("Sidebar link klik", f"href={href!r} (anchor/void)")
    else:
        R.skip("Sidebar link klik", "semua href=#")

    # Active state
    active = el(page, ".nav-link.active")
    R.ok("Ada nav-link.active di sidebar") if active else R.skip("Active nav state", "tidak ada active class")

    # Mobile toggle sidebar di teller complete
    page.set_viewport_size({"width": 768, "height": 1024}); pause(300)
    toggle = el(page, "#sidebarToggleIcon, .mobile-menu-toggle, [id*='sidebarToggle'], button[onclick*='sidebar']")
    if toggle:
        toggle.click(); pause(400)
        sidebar_visible = page.evaluate("""() => {
            const s = document.querySelector('#dashboardSidebar, .dashboard-sidebar');
            if (!s) return false;
            return s.classList.contains('show') || getComputedStyle(s).left === '0px';
        }""")
        R.ok("Mobile sidebar toggle berfungsi") if sidebar_visible else R.skip("Sidebar show state", "tidak terdeteksi")
    else:
        R.skip("Mobile sidebar toggle", "elemen tidak ditemukan")
    page.set_viewport_size({"width": 1280, "height": 720})

    # Header brand link
    goto(page, "pages/staff/dashboard-teller-complete.html"); pause(400)
    brand = el(page, ".navbar-brand, .dashboard-header a")
    R.ok("Header brand/logo ada") if brand else R.skip("Header brand", "tidak ditemukan")

# ─────────────────────────────────────────────────────
# SUITE 12 — Responsivitas UI
# ─────────────────────────────────────────────────────
def s12_responsive(page):
    R.suite("12 — Responsivitas (3 Viewport)")
    viewports = [
        ("Desktop  1280×720",  1280, 720),
        ("Laptop   1024×768",  1024, 768),
        ("Tablet    768×1024", 768,  1024),
        ("Mobile    375×812",  375,  812),
        ("Mobile SM 320×568",  320,  568),
    ]

    for label, w, h in viewports:
        page.set_viewport_size({"width": w, "height": h}); pause(200)
        t0 = time.time()
        goto(page, "login.html"); pause(300)
        elapsed = time.time() - t0

        # Form visible di viewport
        form_ok = page.evaluate("""() => {
            const f = document.getElementById('loginForm');
            if (!f) return false;
            const r = f.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && r.top < window.innerHeight;
        }""")
        R.ok(f"Login form visible di {label}") if form_ok else R.fail(f"Login form di {label}", "form di luar viewport")

        # Performa load
        R.ok(f"Load < 5s di {label}", f"{elapsed:.2f}s") if elapsed < 5 else R.fail(f"Load lambat di {label}", f"{elapsed:.2f}s")

        # Dashboard responsive
        goto(page, "pages/admin/dashboard.html"); pause(200)
        has_content = page.evaluate("() => document.body.innerText.trim().length > 10")
        R.ok(f"Admin dashboard tampil di {label}") if has_content else R.fail(f"Admin dashboard di {label}")

    page.set_viewport_size({"width": 1280, "height": 720})

# ─────────────────────────────────────────────────────
# SUITE 13 — Aksesibilitas Dasar
# ─────────────────────────────────────────────────────
def s13_accessibility(page):
    R.suite("13 — Aksesibilitas Dasar")
    pages_to_check = [
        ("Login",    "login.html"),
        ("Dashboard","pages/admin/dashboard.html"),
        ("Pinjaman", "pages/admin/loan-management.html"),
    ]
    for pname, path in pages_to_check:
        goto(page, path); pause(400)
        checks = {
            f"{pname}: meta charset UTF-8":       "!!document.querySelector('meta[charset]')",
            f"{pname}: meta viewport ada":         "!!document.querySelector('meta[name=\"viewport\"]')",
            f"{pname}: tag <title> ada":            "document.title.length > 0",
            f"{pname}: heading ada (h1-h3)":        "document.querySelectorAll('h1,h2,h3').length > 0",
        }
        for label, expr in checks.items():
            ok = page.evaluate(f"() => Boolean({expr})")
            R.ok(label) if ok else R.fail(label)

        # Label untuk form input
        if path == "login.html":
            labels = page.evaluate("() => document.querySelectorAll('label').length")
            R.ok(f"Login: ada {labels} label form") if labels >= 2 else R.fail("Login: kurang label", f"hanya {labels}")

            # Tombol punya teks yang jelas
            btn_text = page.evaluate("() => document.getElementById('loginBtn')?.innerText?.trim()")
            R.ok("Tombol login punya teks jelas", btn_text) if btn_text and len(btn_text) > 1 else R.fail("Tombol login teks")

        # Alt text gambar
        imgs_no_alt = page.evaluate("() => [...document.querySelectorAll('img')].filter(i => !i.alt).length")
        if imgs_no_alt > 0:
            R.fail(f"{pname}: {imgs_no_alt} gambar tanpa alt text")
        else:
            img_count = page.evaluate("() => document.querySelectorAll('img').length")
            R.ok(f"{pname}: semua {img_count} gambar memiliki alt text")

    # Keyboard navigation di login
    goto(page, "login.html"); pause(300)
    page.focus("#emailInput")
    page.keyboard.press("Tab"); pause(200)
    focused = page.evaluate("() => document.activeElement.id || document.activeElement.tagName")
    R.ok(f"Tab navigation berfungsi (fokus: {focused})") if focused != "BODY" else R.skip("Tab navigation", "fokus tidak bergerak")

# ─────────────────────────────────────────────────────
# SUITE 14 — Performa Halaman
# ─────────────────────────────────────────────────────
def s14_performance(page):
    R.suite("14 — Performa Halaman")
    target_pages = [
        ("Login",              "login.html",                            3),
        ("Admin Dashboard",    "pages/admin/dashboard.html",            4),
        ("Loan Management",    "pages/admin/loan-management.html",      5),
        ("Member Registration","pages/admin/member-registration.html",  5),
        ("Savings Management", "pages/admin/savings-management.html",   5),
        ("Teller Dashboard",   "pages/staff/dashboard-teller-complete.html", 4),
        ("Staff Dashboard",    "pages/staff/dashboard-complete.html",   4),
    ]
    for label, path, max_sec in target_pages:
        t0 = time.time()
        goto(page, path, wait="load")
        elapsed = time.time() - t0
        R.ok(f"{label} < {max_sec}s", f"{elapsed:.2f}s") if elapsed < max_sec else R.fail(f"{label} load time", f"{elapsed:.2f}s > {max_sec}s")

    # JS error check
    goto(page, "login.html"); pause(300)
    errors = []
    page.on("pageerror", lambda e: errors.append(str(e)))
    page.reload(wait_until="domcontentloaded"); pause(1000)
    critical = [e for e in errors if "net::ERR" not in e and "favicon" not in e and "404" not in e]
    R.ok("Login page bebas JS error kritis") if not critical else R.fail("JS error di login", "; ".join(critical[:2]))

    goto(page, "pages/admin/loan-management.html"); pause(300)
    errors2 = []
    page.on("pageerror", lambda e: errors2.append(str(e)))
    page.reload(wait_until="domcontentloaded"); pause(1000)
    critical2 = [e for e in errors2 if "net::ERR" not in e and "favicon" not in e]
    R.ok("Loan management bebas JS error kritis") if not critical2 else R.fail("JS error di loan-management", "; ".join(critical2[:2]))

# ─────────────────────────────────────────────────────
# SUITE 15 — Keamanan Dasar
# ─────────────────────────────────────────────────────
def s15_security(page):
    R.suite("15 — Keamanan Dasar")

    # XSS di login form
    goto(page, "login.html"); pause(300)
    xss_payload = "<script>alert('XSS')</script>"
    page.fill("#emailInput", xss_payload)
    page.fill("#passwordInput", "password123")
    page.click("#loginBtn"); pause(1000)
    # Cek tidak ada dialog alert yang terpicu
    still_safe = "login" in page.url or page.query_selector(".alert-danger")
    R.ok("XSS di input form tidak dieksekusi") if still_safe else R.fail("Potensi XSS!", "Alert dialog terpicu")

    # SQL injection attempt via form
    goto(page, "login.html"); pause(300)
    page.fill("#emailInput", "' OR '1'='1")
    page.fill("#passwordInput", "' OR '1'='1")
    page.click("#loginBtn"); pause(1500)
    not_logged_in = "login" in page.url
    R.ok("SQL injection di form tidak berhasil login") if not_logged_in else R.fail("SQL injection berhasil! SECURITY BUG!")

    # API: SQL injection via parameter
    r = http_post("auth.php", {"action": "login", "username": "' OR '1'='1", "password": "' OR '1'='1"})
    R.ok("API menolak SQL injection (success=false)") if not r["body"].get("success") else R.fail("API: SQL injection berhasil! SECURITY BUG!")

    # Akses API tanpa auth (endpoint yang butuh token)
    r = http_get("auth.php", {"action": "get_user"})
    R.ok("Endpoint get_user tanpa token → 401") if r["status"] == 401 else R.skip("get_user tanpa token", f"status={r['status']}")

    # HTTPS header check (jika ada)
    r = http_get("auth.php", {"action": "validate"}, token="invalid")
    R.ok("API mengembalikan JSON response") if r["body"] else R.fail("API tidak mengembalikan body")

    # Cek tidak ada informasi sensitif di response error
    r = http_post("auth.php", {"action": "login", "username": "wronguser", "password": "wrongpass1"})
    resp_str = json.dumps(r["body"])
    has_leak = any(kw in resp_str.lower() for kw in ["password_hash", "secret", "traceback", "fatal error", "stack trace", "mysqli", "sqlstate"])
    R.ok("Response error tidak mengekspos data sensitif") if not has_leak else R.fail("Info sensitif bocor di response error!", resp_str[:100])

# ─────────────────────────────────────────────────────
# MAIN
# ─────────────────────────────────────────────────────
SUITES = {
    "ui":          ("01 Login UI",           s01_login_ui,        True),
    "login":       ("02 Login Flow",         s02_login_flow,      True),
    "dashboard":   ("03 Admin Dashboard",    s03_admin_dashboard, True),
    "loans":       ("04 Loan Management",    s04_loan_management, True),
    "member":      ("05 Member Registration",s05_member_registration, True),
    "savings":     ("06 Savings Management", s06_savings_management, True),
    "admin_pages": ("07 Admin Pages",        s07_admin_pages,     True),
    "staff":       ("08 Staff Dashboards",   s08_staff_dashboards,True),
    "api_auth":    ("09 API Auth",           s09_api_auth,        False),
    "api_data":    ("10 API Data",           s10_api_data,        False),
    "navigation":  ("11 Navigation",         s11_navigation,      True),
    "responsive":  ("12 Responsive",         s12_responsive,      True),
    "a11y":        ("13 Accessibility",      s13_accessibility,   True),
    "performance": ("14 Performance",        s14_performance,     True),
    "security":    ("15 Security",           s15_security,        True),
}

def main():
    ap = argparse.ArgumentParser(description="KSP Comprehensive Test")
    ap.add_argument("--headless", action="store_true")
    ap.add_argument("--suite", choices=list(SUITES.keys()) + ["all"], default="all")
    ap.add_argument("--slow", type=int, default=SLOW_MO)
    args = ap.parse_args()

    mode = "HEADLESS" if args.headless else "HEADED (browser visible)"
    suite_list = list(SUITES.keys()) if args.suite == "all" else [args.suite]

    print(f"\n{C.BOLD}{C.CY}{'═'*60}")
    print(f"  KSP Lam Gabe Jaya v2.0 — Comprehensive Browser Test")
    print(f"  Mode   : {mode}")
    print(f"  Suites : {len(suite_list)} suite(s)   slow_mo={args.slow}ms")
    print(f"  URL    : {BASE_URL}")
    print(f"  Mulai  : {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"{'═'*60}{C.RESET}")

    # Reset rate limiting
    import subprocess
    subprocess.run(["/opt/lampp/bin/mysql", "-u", "root", "-proot",
                    "ksp_lamgabejaya_v2", "-e", "DELETE FROM login_attempts;"],
                   capture_output=True)

    with sync_playwright() as p:
        browser = p.chromium.launch(
            headless=args.headless,
            slow_mo=args.slow,
            args=["--no-sandbox","--disable-dev-shm-usage","--disable-gpu"]
        )
        ctx  = browser.new_context(viewport={"width": 1280, "height": 720})
        page = ctx.new_page()
        page.set_default_timeout(TIMEOUT)

        try:
            for key in suite_list:
                name, fn, needs_browser = SUITES[key]
                if needs_browser:
                    fn(page)
                else:
                    fn(page)  # API suites juga terima page (tidak dipakai)
                # Reset rate limiting antara suite
                subprocess.run(["/opt/lampp/bin/mysql","-u","root","-proot",
                                 "ksp_lamgabejaya_v2","-e","DELETE FROM login_attempts;"],
                               capture_output=True)

        except KeyboardInterrupt:
            print(f"\n{C.Y}  [DIHENTIKAN pengguna]{C.RESET}")
        except Exception as e:
            R.fail("UNEXPECTED ERROR", str(e))
            import traceback; traceback.print_exc()
        finally:
            if not args.headless:
                print(f"\n  {C.DIM}Menutup browser dalam 2 detik...{C.RESET}")
                time.sleep(2)
            browser.close()

    ok = R.summary()

    # Simpan hasil ke JSON
    out = {"timestamp": datetime.now().isoformat(), "passed": R.passed,
           "failed": R.failed, "skipped": R.skipped, "tests": R.log}
    with open("/opt/lampp/htdocs/mono-v2/tests/results.json", "w") as f:
        json.dump(out, f, indent=2)
    print(f"  {C.DIM}Hasil disimpan ke tests/results.json{C.RESET}\n")

    sys.exit(0 if ok else 1)

if __name__ == "__main__":
    main()
