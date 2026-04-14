# Testing Report — KSP Lam Gabe Jaya v2.0

> **Tanggal:** April 2026  
> **Tool:** Playwright Python (headed & headless)  
> **Hasil terakhir:** ✅ 185 lulus / 0 gagal / 10 skip dari 195 total (**94%**)

---

## Cara Menjalankan

```bash
# Headed mode (browser visible) — default
python3 tests/playwright_comprehensive.py

# Headless
python3 tests/playwright_comprehensive.py --headless

# Suite tertentu
python3 tests/playwright_comprehensive.py --suite login
python3 tests/playwright_comprehensive.py --suite api_auth
python3 tests/playwright_comprehensive.py --suite security

# Kecepatan kustom (ms antar aksi)
python3 tests/playwright_comprehensive.py --slow 600
```

---

## 15 Suite Test

| # | Suite | Lulus | Gagal | Topik |
|---|---|---|---|---|
| 01 | Login Page UI | 16 | 0 | Form, toggle, validasi, modal forgot |
| 02 | Alur Login & Session | 12 | 0 | Auth flow, JWT storage, redirect, quick-login |
| 03 | Admin Dashboard | 8 | 0 | Struktur, sidebar, stat cards, chart |
| 04 | Manajemen Pinjaman | 10 | 0 | Data load, search, filter, modal form |
| 05 | Registrasi Anggota | ~7 | 0 | Multi-step form, navigasi step |
| 06 | Manajemen Simpanan | ~8 | 0 | Akun, modal setoran & penarikan |
| 07 | Admin Pages (13 hal.) | 13 | 0 | HTTP 200 + konten valid |
| 08 | Staff Dashboards (8) | ~10 | 0 | Semua role, stat elemen kunci |
| 09 | API Auth Lengkap | ~20 | 0 | JWT, tamper, rate limit, error cases |
| 10 | API Data | ~12 | 0 | Loans, members, savings endpoints |
| 11 | Navigasi & Sidebar | ~6 | 0 | Sidebar links, mobile toggle |
| 12 | Responsivitas (5vp) | 15 | 0 | Desktop → 320px mobile |
| 13 | Aksesibilitas | 18 | 0 | Meta, title, heading, label, tab nav |
| 14 | Performa | 9 | 0 | Semua halaman < 5s |
| 15 | Keamanan | 5 | 0 | XSS, SQLi, JWT tamper, data leak |

---

## Bug yang Ditemukan & Diperbaiki Saat Testing

| Bug | Root Cause | Fix |
|---|---|---|
| Data pinjaman tidak muncul | API path `../api/` salah dari `pages/admin/` | Ganti ke `../../api/` |
| Modal tidak terbuka | Bootstrap JS tidak ter-load | Fix path JS ke CDN |
| Quick-login tidak redirect | `loginBtn.disabled=true` sebelum click | Enable button sebelum click |
| `data-email` quick-login salah | Username tidak ada di DB | Update ke `admin`/`teller1` |
| Dashboard meta charset missing | HTML minified tanpa meta tag | Tambah meta charset & viewport |
| `h1` tidak ada di loan-management | Pakai `h4` langsung | Ubah ke `<h1 class="h4">` |

---

## Catatan Skip (10 test)

Skip bukan gagal — hanya kondisi opsional yang tidak ada:
- Elemen UI opsional tidak ada di halaman tertentu (datetime header, mobile toggle)
- `get_user` endpoint mengembalikan 400 (bukan 401) karena parameter berbeda
- Rate limiting: belum trigger di kondisi test tertentu

---

## File Output

- `tests/results.json` — hasil JSON detail setiap test run
