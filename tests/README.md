# Tests — KSP Lam Gabe Jaya v2.0

## File

| File | Deskripsi | Test Count |
|---|---|---|
| `playwright_comprehensive.py` | Suite lengkap 15 modul | 195 test |
| `playwright_test.py` | Suite dasar (referensi lama) | 52 test |
| `results.json` | Hasil run terakhir (JSON) | — |

## Jalankan

```bash
# Default: headed, semua suite
python3 tests/playwright_comprehensive.py

# Headless
python3 tests/playwright_comprehensive.py --headless

# Suite spesifik: ui, login, dashboard, loans, member, savings,
#                 admin_pages, staff, api_auth, api_data,
#                 navigation, responsive, a11y, performance, security
python3 tests/playwright_comprehensive.py --suite api_auth

# Slow motion berbeda
python3 tests/playwright_comprehensive.py --slow 800
```

## Prerequisite

```bash
pip3 install playwright --break-system-packages
playwright install chromium
```

## Hasil Terakhir

```
185 lulus / 0 gagal / 10 skip / 195 total — 94%
```
