#!/usr/bin/env python3
"""
KSP Lam Gabe Jaya v2.0 — Browser Automation Test
Menggunakan Playwright (Python) dengan browser VISIBLE

Jalankan:
    python3 tests/playwright_test.py
    python3 tests/playwright_test.py --headless   # tanpa tampilan browser
    python3 tests/playwright_test.py --suite api  # hanya suite tertentu
"""

import sys
import time
import json
import argparse
import urllib.request
import urllib.parse
from datetime import datetime
from playwright.sync_api import sync_playwright, expect

# ─────────────────────────────────────────────
# Konfigurasi
# ─────────────────────────────────────────────
BASE_URL  = "http://localhost/mono-v2"
API_BASE  = f"{BASE_URL}/api"
SLOW_MO   = 400   # ms jeda antar aksi (agar terlihat)
TIMEOUT   = 15000 # ms

# ─────────────────────────────────────────────
# Terminal colors
# ─────────────────────────────────────────────
class C:
    GREEN  = "\033[92m"
    RED    = "\033[91m"
    YELLOW = "\033[93m"
    BLUE   = "\033[94m"
    CYAN   = "\033[96m"
    BOLD   = "\033[1m"
    DIM    = "\033[2m"
    RESET  = "\033[0m"

# ─────────────────────────────────────────────
# Runner sederhana
# ─────────────────────────────────────────────
class TestRunner:
    def __init__(self):
        self.passed  = 0
        self.failed  = 0
        self.skipped = 0
        self.results = []
        self.current_suite = ""

    def suite(self, name):
        self.current_suite = name
        print(f"\n{C.BOLD}{C.BLUE}{'─'*55}")
        print(f"  {name}")
        print(f"{'─'*55}{C.RESET}")

    def ok(self, name, detail=""):
        self.passed += 1
        self.results.append({"suite": self.current_suite, "name": name, "status": "PASS"})
        detail_str = f" {C.DIM}({detail}){C.RESET}" if detail else ""
        print(f"  {C.GREEN}✓{C.RESET} {name}{detail_str}")

    def fail(self, name, reason=""):
        self.failed += 1
        self.results.append({"suite": self.current_suite, "name": name, "status": "FAIL", "reason": reason})
        print(f"  {C.RED}✗{C.RESET} {name}")
        if reason:
            print(f"    {C.RED}└─ {reason}{C.RESET}")

    def skip(self, name, reason=""):
        self.skipped += 1
        self.results.append({"suite": self.current_suite, "name": name, "status": "SKIP"})
        print(f"  {C.YELLOW}○{C.RESET} {name} {C.DIM}(skip: {reason}){C.RESET}")

    def summary(self):
        total = self.passed + self.failed + self.skipped
        pct   = int(self.passed / total * 100) if total else 0
        color = C.GREEN if self.failed == 0 else (C.YELLOW if pct >= 80 else C.RED)
        print(f"\n{C.BOLD}{'═'*55}")
        print(f"  HASIL AKHIR  {color}{self.passed} lulus  {C.RED}{self.failed} gagal{C.RESET}  {C.DIM}{self.skipped} skip{C.RESET}  {C.BOLD}/{total} total{C.RESET}")
        print(f"  {color}Success rate: {pct}%{C.RESET}")
        print(f"  Waktu: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        print(f"{'═'*55}{C.RESET}\n")
        return self.failed == 0

R = TestRunner()

# ─────────────────────────────────────────────
# HTTP helpers (tanpa browser)
# ─────────────────────────────────────────────
def http_post(endpoint, data):
    url  = f"{API_BASE}/{endpoint}"
    body = urllib.parse.urlencode(data).encode()
    req  = urllib.request.Request(url, data=body, method="POST")
    req.add_header("Content-Type", "application/x-www-form-urlencoded")
    try:
        with urllib.request.urlopen(req, timeout=10) as res:
            return {"status": res.status, "body": json.loads(res.read())}
    except urllib.error.HTTPError as e:
        try:
            return {"status": e.code, "body": json.loads(e.read())}
        except:
            return {"status": e.code, "body": {}}

def http_get(endpoint, params=None, token=None):
    qs  = ("?" + urllib.parse.urlencode(params)) if params else ""
    url = f"{API_BASE}/{endpoint}{qs}"
    req = urllib.request.Request(url)
    if token:
        req.add_header("Authorization", f"Bearer {token}")
    try:
        with urllib.request.urlopen(req, timeout=10) as res:
            return {"status": res.status, "body": json.loads(res.read())}
    except urllib.error.HTTPError as e:
        try:
            return {"status": e.code, "body": json.loads(e.read())}
        except:
            return {"status": e.code, "body": {}}

def get_token():
    res = http_post("auth.php", {"action": "login", "username": "admin", "password": "password"})
    if res["body"].get("success"):
        return res["body"]["data"]["user"]["token"]
    raise Exception("Gagal login untuk mendapatkan token")

# ─────────────────────────────────────────────
# SUITE 1: Login Page UI (browser visible)
# ─────────────────────────────────────────────
def suite_login_ui(page):
    R.suite("01 — Login Page UI (Browser Visible)")

    # 1. Buka halaman login
    page.goto(f"{BASE_URL}/login.html", wait_until="domcontentloaded")
    time.sleep(0.5)

    # 2. Judul halaman
    title = page.title()
    if "KSP" in title or "Masuk" in title or "Login" in title:
        R.ok("Judul halaman mengandung nama aplikasi", title)
    else:
        R.fail("Judul halaman mengandung nama aplikasi", f"got: {title!r}")

    # 3. Elemen form
    for sel, label in [
        ("#loginForm",   "Form #loginForm ada"),
        ("#emailInput",  "Input username/email ada"),
        ("#passwordInput", "Input password ada"),
        ("#loginBtn",    "Tombol login ada"),
        ("#togglePassword", "Tombol toggle password ada"),
    ]:
        el = page.query_selector(sel)
        if el:
            R.ok(label)
        else:
            R.fail(label, f"selector {sel!r} tidak ditemukan")

    # 4. Link lupa kata sandi
    el = page.query_selector("a[onclick*='showForgotPassword']")
    R.ok("Link lupa kata sandi ada") if el else R.fail("Link lupa kata sandi ada")

    # 5. Quick login buttons
    btns = page.query_selector_all(".quick-login-btn")
    if len(btns) >= 4:
        R.ok(f"Quick login buttons ada ({len(btns)} tombol)")
    else:
        R.fail("Quick login buttons ada", f"hanya {len(btns)}")

    # 6. Ketik di input (terlihat di browser)
    page.fill("#emailInput", "")
    page.type("#emailInput", "admin", delay=80)
    page.type("#passwordInput", "test123", delay=80)
    val = page.input_value("#emailInput")
    R.ok("Mengetik di input username berhasil") if val == "admin" else R.fail("Mengetik di input username", f"got: {val!r}")

    # 7. Toggle password visibility
    ptype_before = page.get_attribute("#passwordInput", "type")
    page.click("#togglePassword")
    time.sleep(0.3)
    ptype_after = page.get_attribute("#passwordInput", "type")
    page.click("#togglePassword")  # kembalikan
    if ptype_before == "password" and ptype_after == "text":
        R.ok("Toggle password: password → text → password")
    else:
        R.fail("Toggle password visibility", f"{ptype_before!r} → {ptype_after!r}")

    # 8. Checkbox remember me
    page.click("#rememberMe")
    checked = page.is_checked("#rememberMe")
    page.click("#rememberMe")
    R.ok("Checkbox 'Ingat saya' dapat diklik") if checked else R.fail("Checkbox 'Ingat saya'")

    # 9. Submit form kosong (harus tidak redirect)
    page.fill("#emailInput", "")
    page.fill("#passwordInput", "")
    url_before = page.url
    page.click("#loginBtn")
    time.sleep(0.8)
    if "login" in page.url:
        R.ok("Submit kosong tidak redirect (validasi frontend bekerja)")
    else:
        R.fail("Submit kosong tidak redirect", f"URL berubah ke {page.url!r}")

    # 10. Modal lupa kata sandi
    page.evaluate("showForgotPassword()")
    time.sleep(0.8)
    modal_visible = page.evaluate("""() => {
        const m = document.getElementById('forgotPasswordModal');
        return m && (m.classList.contains('show') || getComputedStyle(m).display !== 'none');
    }""")
    R.ok("Modal lupa kata sandi terbuka") if modal_visible else R.fail("Modal lupa kata sandi terbuka")
    page.keyboard.press("Escape")
    time.sleep(0.3)

# ─────────────────────────────────────────────
# SUITE 2: Flow Login Berhasil (browser visible)
# ─────────────────────────────────────────────
def suite_login_flow(page):
    R.suite("02 — Flow Login Berhasil (Browser Visible)")

    page.goto(f"{BASE_URL}/login.html", wait_until="domcontentloaded")
    time.sleep(0.4)

    # Isi form login - terlihat di browser
    print(f"  {C.DIM}  → Mengisi form login dengan admin/password...{C.RESET}")
    page.fill("#emailInput", "")
    page.type("#emailInput", "admin", delay=100)
    page.fill("#passwordInput", "")
    page.type("#passwordInput", "password", delay=100)
    time.sleep(0.3)

    # Klik login
    print(f"  {C.DIM}  → Klik tombol masuk...{C.RESET}")
    page.click("#loginBtn")

    # Tunggu alert muncul
    try:
        page.wait_for_function("""() => {
            return document.querySelectorAll('.alert-success, .alert-info').length > 0;
        }""", timeout=4000)
        R.ok("Alert sukses muncul setelah login")
    except:
        body_text = page.inner_text("body")
        if "berhasil" in body_text.lower():
            R.ok("Alert sukses muncul (via teks body)")
        else:
            R.fail("Alert sukses muncul", "tidak ada alert dalam 4s")

    # Tunggu token tersimpan di storage
    try:
        page.wait_for_function("""() =>
            localStorage.getItem('authToken') || sessionStorage.getItem('authToken')
        """, timeout=3000)
        token = page.evaluate("() => localStorage.getItem('authToken') || sessionStorage.getItem('authToken')")
        R.ok("Token tersimpan di storage", f"panjang={len(token)}")
    except:
        R.fail("Token tersimpan di storage", "tidak ada dalam 3s")

    # Tunggu redirect ke dashboard
    print(f"  {C.DIM}  → Menunggu redirect ke dashboard...{C.RESET}")
    try:
        page.wait_for_url("**/dashboard**", timeout=6000)
        R.ok("Redirect ke dashboard berhasil", page.url)
    except:
        R.fail("Redirect ke dashboard", f"URL: {page.url!r}")

    # Dashboard terload
    if page.query_selector(".card") or page.query_selector("main"):
        R.ok("Konten dashboard terload")
    else:
        R.fail("Konten dashboard terload", "tidak ada .card atau main")

# ─────────────────────────────────────────────
# SUITE 3: API Endpoints (HTTP langsung)
# ─────────────────────────────────────────────
def suite_api():
    R.suite("03 — API Endpoints (HTTP)")
    valid_token = None

    # Login valid
    res = http_post("auth.php", {"action": "login", "username": "admin", "password": "password"})
    if res["status"] == 200 and res["body"].get("success"):
        R.ok("POST auth.php login valid → 200 success")
        valid_token = res["body"]["data"]["user"]["token"]
    else:
        R.fail("POST auth.php login valid", f"status={res['status']}")

    # JWT format
    if valid_token:
        parts = valid_token.split(".")
        if len(parts) == 3:
            import base64
            hdr_raw = parts[0] + "=="
            hdr = json.loads(base64.urlsafe_b64decode(hdr_raw + "=="))
            if hdr.get("alg") == "HS256":
                R.ok("JWT header alg=HS256 ✓")
            else:
                R.fail("JWT alg HS256", f"got: {hdr}")
            R.ok("JWT memiliki 3 bagian (header.payload.signature)")
        else:
            R.fail("JWT format 3 bagian", f"got {len(parts)} bagian")
    else:
        R.skip("JWT format", "tidak ada token")

    # Login salah
    res = http_post("auth.php", {"action": "login", "username": "admin", "password": "wrongpassword"})
    if res["status"] == 401 and not res["body"].get("success"):
        R.ok("Login password salah → 401 Unauthorized")
    else:
        R.fail("Login password salah", f"status={res['status']}")

    # Login kosong
    res = http_post("auth.php", {"action": "login", "username": "", "password": ""})
    if res["status"] == 400:
        R.ok("Login field kosong → 400 Bad Request")
    else:
        R.fail("Login field kosong", f"status={res['status']}")

    # Validasi token valid
    if valid_token:
        res = http_get("auth.php", {"action": "validate"}, token=valid_token)
        if res["body"].get("success"):
            R.ok("Validate token valid → success: true")
        else:
            R.fail("Validate token valid", str(res["body"]))
    else:
        R.skip("Validate token", "tidak ada token")

    # Token palsu
    res = http_get("auth.php", {"action": "validate"}, token="fake.token.signature")
    if not res["body"].get("success") and res["status"] == 401:
        R.ok("Token palsu ditolak → 401")
    else:
        R.fail("Token palsu ditolak", f"status={res['status']}")

    # Tanpa token
    res = http_get("auth.php", {"action": "validate"})
    if res["status"] == 401:
        R.ok("Tanpa token → 401")
    else:
        R.fail("Tanpa token", f"status={res['status']}")

    # JWT tampered payload (security)
    if valid_token:
        import base64 as b64
        p = valid_token.split(".")
        fake_payload = b64.urlsafe_b64encode(json.dumps({"user_id": 999, "role": "Super Admin",
                       "exp": int(time.time()) + 86400}).encode()).decode().rstrip("=")
        tampered = f"{p[0]}.{fake_payload}.{p[2]}"
        res = http_get("auth.php", {"action": "validate"}, token=tampered)
        if not res["body"].get("success"):
            R.ok("JWT tampered payload ditolak (HMAC signature check ✓)")
        else:
            R.fail("JWT tampered payload", "seharusnya ditolak!")

    # Loans
    res = http_get("loans.php", {"action": "get_loan_types"})
    if res["body"].get("success") and isinstance(res["body"].get("data"), list):
        R.ok(f"Loans get_loan_types → {len(res['body']['data'])} tipe")
    else:
        R.fail("Loans get_loan_types", str(res["body"]))

    # Members
    res = http_get("members.php", {"action": "get_member_types"})
    if res["body"].get("success") and isinstance(res["body"].get("data"), list):
        R.ok(f"Members get_member_types → {len(res['body']['data'])} tipe")
    else:
        R.fail("Members get_member_types", str(res["body"]))

    # Savings
    res = http_get("savings.php", {"action": "get_account_types"})
    if res["body"].get("success") and isinstance(res["body"].get("data"), list):
        R.ok(f"Savings get_account_types → {len(res['body']['data'])} tipe")
    else:
        R.fail("Savings get_account_types", str(res["body"]))

    # Action tidak valid
    res = http_get("loans.php", {"action": "invalid_action_xyz"})
    if res["status"] == 400:
        R.ok("Action tidak valid → 400 Bad Request")
    else:
        R.fail("Action tidak valid", f"status={res['status']}")

# ─────────────────────────────────────────────
# SUITE 4: Dashboard & Halaman (browser visible)
# ─────────────────────────────────────────────
def suite_dashboard(page):
    R.suite("04 — Dashboard & Halaman Admin (Browser Visible)")

    pages_to_test = [
        ("Admin Dashboard",         "pages/admin/dashboard.html"),
        ("Anggota",                  "pages/admin/members.html"),
        ("Manajemen Pinjaman",       "pages/admin/loan-management.html"),
        ("Registrasi Anggota",       "pages/admin/member-registration.html"),
        ("Manajemen Simpanan",       "pages/admin/savings-management.html"),
        ("Laporan",                  "pages/admin/reports.html"),
        ("Pengaturan",               "pages/admin/settings.html"),
        ("Dashboard Teller",         "pages/staff/dashboard-teller-complete.html"),
        ("Dashboard Kasir",          "pages/staff/dashboard-kasir.html"),
        ("Dashboard Mantri",         "pages/staff/dashboard-mantri.html"),
        ("Dashboard Collector",      "pages/staff/dashboard-collector.html"),
    ]

    for label, path in pages_to_test:
        try:
            res = page.goto(f"{BASE_URL}/{path}", wait_until="domcontentloaded", timeout=8000)
            if res and res.status == 200:
                R.ok(f"{label} → HTTP 200")
            else:
                R.fail(label, f"HTTP {res.status if res else '?'}")
        except Exception as e:
            R.fail(label, str(e)[:60])
        time.sleep(0.2)

# ─────────────────────────────────────────────
# SUITE 5: Responsive & Performa (browser)
# ─────────────────────────────────────────────
def suite_responsive(page):
    R.suite("05 — Responsive & Performa (Browser Visible)")

    viewports = [
        ("Desktop  1280×720",  1280, 720),
        ("Tablet   768×1024",  768,  1024),
        ("Mobile   375×812",   375,  812),
    ]

    for label, w, h in viewports:
        page.set_viewport_size({"width": w, "height": h})
        time.sleep(0.2)

        start = time.time()
        page.goto(f"{BASE_URL}/login.html", wait_until="domcontentloaded")
        elapsed = time.time() - start

        # Form terlihat?
        form_ok = page.evaluate("""() => {
            const f = document.getElementById('loginForm');
            if (!f) return false;
            const r = f.getBoundingClientRect();
            return r.width > 0 && r.height > 0;
        }""")

        label_str = label.strip()
        if form_ok:
            R.ok(f"Login page responsive di {label_str}")
        else:
            R.fail(f"Login page responsive di {label_str}", "form tidak terlihat")

        if elapsed < 5:
            R.ok(f"Performa {label_str} < 5s", f"{elapsed:.2f}s")
        else:
            R.fail(f"Performa {label_str} < 5s", f"terlalu lambat: {elapsed:.2f}s")

    # Reset ke desktop
    page.set_viewport_size({"width": 1280, "height": 720})

    # Aksesibilitas dasar
    page.goto(f"{BASE_URL}/login.html", wait_until="domcontentloaded")

    checks = {
        "Meta charset UTF-8": "document.querySelector('meta[charset]')?.getAttribute('charset')?.toLowerCase() === 'utf-8'",
        "Meta viewport ada": "!!document.querySelector('meta[name=\"viewport\"]')",
        "Label pada form ada": "document.querySelectorAll('label').length > 0",
        "Teks tombol login ada": "document.getElementById('loginBtn')?.innerText?.trim().length > 0",
    }
    for label, expr in checks.items():
        ok = page.evaluate(f"() => Boolean({expr})")
        R.ok(label) if ok else R.fail(label)

    # Cek tidak ada JS error kritis
    errors = []
    page.on("pageerror", lambda e: errors.append(str(e)))
    page.reload(wait_until="domcontentloaded")
    time.sleep(1)
    critical = [e for e in errors if "net::ERR" not in e and "favicon" not in e]
    if not critical:
        R.ok("Tidak ada JS error kritis di halaman login")
    else:
        R.fail("Tidak ada JS error kritis", "; ".join(critical[:2]))

# ─────────────────────────────────────────────
# MAIN
# ─────────────────────────────────────────────
def main():
    parser = argparse.ArgumentParser(description="KSP Browser Automation Test")
    parser.add_argument("--headless", action="store_true", help="Jalankan tanpa tampilan browser")
    parser.add_argument("--suite", choices=["ui", "login", "api", "dashboard", "responsive", "all"],
                        default="all", help="Suite yang dijalankan (default: all)")
    parser.add_argument("--slow", type=int, default=SLOW_MO, help="Delay antar aksi browser (ms, default=400)")
    args = parser.parse_args()

    headed = not args.headless
    mode   = "HEADLESS" if args.headless else "HEADED (browser visible)"

    print(f"\n{C.BOLD}{C.CYAN}{'═'*55}")
    print(f"  KSP Lam Gabe Jaya v2.0 — Playwright Test")
    print(f"  Mode: {mode}  |  slow_mo={args.slow}ms")
    print(f"  Suite: {args.suite.upper()}")
    print(f"  URL: {BASE_URL}")
    print(f"{'═'*55}{C.RESET}")

    with sync_playwright() as p:
        browser = p.chromium.launch(
            headless=not headed,
            slow_mo=args.slow,
            args=["--no-sandbox", "--disable-dev-shm-usage"]
        )
        context = browser.new_context(viewport={"width": 1280, "height": 720})
        page    = context.new_page()
        page.set_default_timeout(TIMEOUT)

        try:
            suite = args.suite

            if suite in ("ui", "all"):
                suite_login_ui(page)

            if suite in ("login", "all"):
                suite_login_flow(page)

            if suite in ("api", "all"):
                suite_api()

            if suite in ("dashboard", "all"):
                suite_dashboard(page)

            if suite in ("responsive", "all"):
                suite_responsive(page)

        except KeyboardInterrupt:
            print(f"\n{C.YELLOW}  [DIHENTIKAN oleh pengguna]{C.RESET}")
        except Exception as e:
            R.fail("Unexpected error", str(e))
        finally:
            if headed:
                print(f"\n  {C.DIM}Browser akan ditutup dalam 2 detik...{C.RESET}")
                time.sleep(2)
            browser.close()

    success = R.summary()
    sys.exit(0 if success else 1)

if __name__ == "__main__":
    main()
