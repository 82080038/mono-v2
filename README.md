# KSP Lam Gabe Jaya v2.0

> **Status:** ✅ Production-Ready MVP  
> **Versi:** 2.0.0  
> **Terakhir diupdate:** April 2026  
> **Test coverage:** 185/195 (94%) — 0 gagal

---

## 🎯 Project Overview

Sistem informasi koperasi simpan pinjam berbasis web untuk **KSP Lam Gabe Jaya**. Dibangun dengan PHP native + Bootstrap 5 + vanilla JavaScript, menggunakan JWT authentication dan arsitektur MVC sederhana.

### Prinsip Desain

1. **100% English Code** — variabel, fungsi, class dalam bahasa Inggris
2. **Indonesian UI** — semua teks tampilan dalam bahasa Indonesia
3. **Clean Architecture** — struktur folder terorganisir, separation of concerns
4. **Bootstrap 5 Konsisten** — sistem warna unified via CSS variables
5. **Security-first** — JWT, rate limiting, input validation, prepared statements

---

## 🚀 Status Aplikasi (April 2026)

### ✅ Selesai & Berfungsi

| Modul | Status | Keterangan |
|---|---|---|
| Authentication (JWT) | ✅ Live | Login, logout, token validate, rate limiting |
| Dashboard Admin | ✅ Live | Statistik, chart, navigasi sidebar |
| Manajemen Pinjaman | ✅ Live | CRUD pinjaman, approval flow, pembayaran |
| Registrasi Anggota | ✅ Live | Multi-step form (5 tahap) |
| Manajemen Simpanan | ✅ Live | Setoran, penarikan, jenis rekening |
| Dashboard Teller | ✅ Live | Transaksi harian, saldo, anggota dilayani |
| Dashboard Kasir | ✅ Live | Ringkasan kas harian |
| Dashboard Mantri | ✅ Live | Target kunjungan, anggota binaan |
| Dashboard Collector | ✅ Live | Rute, tunggakan, penagihan |
| Dashboard Surveyor | ✅ Live | Antrian survei, laporan |
| Dashboard Member | ✅ Live | Info rekening & pinjaman anggota |
| CSS Theming | ✅ Unified | Bootstrap 5 color vars, zero inline-color inconsistency |
| Database | ✅ 22 tabel | Schema + seed data tersedia di `database/` |

### 🔧 Infrastruktur

- **Backend:** PHP 8.x + LAMPP (Apache + MariaDB 10.4)
- **Frontend:** Bootstrap 5.3, FontAwesome 6.4, Chart.js
- **Auth:** JWT HS256, token blacklist, rate limiting (5x/15min)
- **Testing:** Playwright Python (195 test, 94% pass rate)

---

## 📁 Struktur Proyek

```
mono-v2/
├── api/                    # REST API endpoints (PHP)
│   ├── auth.php            # Login, logout, validate, register
│   ├── loans.php           # CRUD pinjaman & pembayaran
│   ├── members.php         # CRUD anggota
│   ├── savings.php         # CRUD simpanan & transaksi
│   └── ...                 # analytics, ai_ml, gps, dll
├── assets/
│   ├── css/
│   │   ├── main.css        # Global styles + CSS variables (BS5)
│   │   ├── dashboard.css   # Dashboard-specific styles
│   │   └── dashboard-layout.css  # Sticky header, sidebar scroll
│   └── js/
│       ├── auth-fixed.js   # Auth flow, JWT handling, redirect
│       ├── config.js       # APP_CONFIG, base URL
│       └── main.js         # Shared utilities
├── config/
│   ├── Config.php          # DB connection, JWT secret
│   └── constants.php       # App-wide constants
├── core/
│   ├── Auth.php            # JWT encode/decode
│   └── helpers/
│       └── DataValidator.php  # Input validation & sanitization
├── database/
│   └── ksp_lamgabejaya_v2.sql  # Full dump (schema + data)
├── docs/                   # Dokumentasi lengkap
├── pages/
│   ├── admin/              # Halaman admin (dashboard, pinjaman, dll)
│   ├── staff/              # Halaman staff berbasis role
│   └── member/             # Portal anggota
├── tests/
│   ├── playwright_comprehensive.py  # 195 test, 15 suite
│   ├── playwright_test.py           # 52 test (basic)
│   └── results.json                 # Hasil run terakhir
├── login.html              # Halaman login
└── index.html              # Landing page
```

---

## ⚡ Quick Start

```bash
# 1. Jalankan LAMPP
sudo /opt/lampp/lampp start

# 2. Import database
/opt/lampp/bin/mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS ksp_lamgabejaya_v2;"
/opt/lampp/bin/mysql -u root -proot ksp_lamgabejaya_v2 < database/ksp_lamgabejaya_v2.sql

# 3. Buka browser
http://localhost/mono-v2/login.html

# Login default:
# Username: admin   | Password: password
# Username: teller1 | Password: password
```

---

## 🧪 Testing

```bash
# Headed (browser visible)
python3 tests/playwright_comprehensive.py

# Headless
python3 tests/playwright_comprehensive.py --headless

# Suite spesifik
python3 tests/playwright_comprehensive.py --suite login
python3 tests/playwright_comprehensive.py --suite api_auth
```

**Hasil terakhir:** 185 lulus / 0 gagal / 10 skip dari 195 total (94%)

---

## 🗺️ Rencana Lanjutan

Lihat [`docs/DEVELOPMENT_ROADMAP.md`](docs/DEVELOPMENT_ROADMAP.md) untuk detail lengkap.

| Prioritas | Fitur |
|---|---|
| 🔴 Tinggi | Role-based access control penuh, middleware auth di semua halaman |
| 🔴 Tinggi | Laporan PDF (TCPDF/DomPDF) |
| 🟡 Sedang | Notifikasi real-time (WebSocket/SSE) |
| 🟡 Sedang | Offline mode + PWA support |
| 🟢 Rendah | Mobile app (React Native / Flutter) |
| 🟢 Rendah | AI credit scoring otomatis |

---

## � Dokumentasi Lanjutan

- [API Documentation](docs/API_Documentation.md)
- [Development Roadmap](docs/DEVELOPMENT_ROADMAP.md)
- [Programmer Guide](docs/technical/PROGRAMMER_GUIDE.md)
- [User Manual](docs/user-guides/USER_MANUAL.md)
- [Deployment Guide](docs/technical/PRODUCTION_DEPLOYMENT_GUIDE.md)
- [Test Report](TESTING.md)

---

## 📄 Lisensi

Proprietary — © 2026 KSP Lam Gabe Jaya. All rights reserved.

---

*Dibuat dengan ❤️ menggunakan PHP, Bootstrap 5, dan Playwright untuk testing.*

