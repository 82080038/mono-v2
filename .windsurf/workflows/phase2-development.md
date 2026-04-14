---
description: Panduan pengembangan Phase 2 - Akuntansi, Laporan, dan Kepatuhan untuk KSP Lam Gabe Jaya v2.0
---

# Phase 2 Development Workflow — Akuntansi & Kepatuhan

## 🎯 Tujuan Phase 2 (Q2 2026)

Membangun sistem akuntansi terintegrasi, laporan keuangan otomatis, kepatuhan regulasi, dan penguatan keamanan (PHP auth middleware + full RBAC).

---

## 📋 Checklist Phase 2

### 2.1 PHP Auth Middleware (PRIORITAS #1)
- [ ] Buat `core/Middleware.php` — class untuk auth guard di level PHP
- [ ] Tambahkan middleware di semua API endpoint yang sensitif
- [ ] Buat `core/Permission.php` — mapping role → allowed actions
- [ ] Pastikan setiap API cek `Authorization: Bearer <token>` via `Auth::validate()`

**Pola implementasi:**
```php
// Di setiap API endpoint:
require_once '../core/Middleware.php';
$middleware = new Middleware();
$user = $middleware->requireAuth(); // throw 401 jika tidak valid
$middleware->requireRole(['admin', 'kasir']); // throw 403 jika role salah
```

### 2.2 Sistem Akuntansi
- [ ] Tabel `chart_of_accounts` (kode akun, nama, tipe: asset/liability/equity/revenue/expense)
- [ ] Tabel `journal_entries` (no_jurnal, tanggal, keterangan, referensi)
- [ ] Tabel `journal_entry_lines` (debit/kredit per akun)
- [ ] API `api/accounting.php` — CRUD jurnal, trial balance, laporan
- [ ] Auto-journaling saat transaksi simpanan/pinjaman terjadi
- [ ] Halaman `pages/admin/accounting.html` — input jurnal manual & laporan

**Akun dasar yang dibutuhkan:**
```
1-000 Kas dan Bank
1-100 Piutang Pinjaman
1-200 Simpanan di Bank
2-000 Simpanan Anggota (Kewajiban)
2-100 Hutang Lain-lain
3-000 Modal / Simpanan Pokok
4-000 Pendapatan Jasa Pinjaman
5-000 Beban Operasional
```

### 2.3 Laporan Keuangan
- [ ] **Neraca (Balance Sheet)** — per tanggal tertentu
- [ ] **Laba Rugi (Income Statement)** — per periode
- [ ] **Arus Kas (Cash Flow)** — per periode
- [ ] **Laporan SHU** — per tahun buku
- [ ] Halaman `pages/admin/laporan-keuangan.html`

### 2.4 Export PDF & Excel
- [ ] Install/setup TCPDF atau DomPDF untuk PDF
- [ ] Install/setup PhpSpreadsheet untuk Excel
- [ ] Endpoint: `api/reports.php?type=pdf&report=neraca&periode=2026-01`
- [ ] Endpoint: `api/reports.php?type=excel&report=angsuran&member_id=...`
- [ ] Laporan yang perlu PDF: Neraca, Laba Rugi, Angsuran per Anggota, Rekap Bulanan

**Cara install via composer (jika belum ada):**
```bash
cd /opt/lampp/htdocs/mono-v2
composer require tecnickcom/tcpdf
composer require phpoffice/phpspreadsheet
```

### 2.5 Kalkulasi SHU (Sisa Hasil Usaha)
- [ ] Tabel `shu_periods` — periode perhitungan SHU
- [ ] Tabel `shu_distributions` — distribusi per anggota
- [ ] API `api/shu.php` — kalkulasi & distribusi SHU
- [ ] Formula SHU:
  ```
  SHU = Pendapatan Jasa - Beban Operasional
  Porsi anggota = (Jasa Simpanan + Jasa Pinjaman) / Total Transaksi × SHU
  ```
- [ ] Halaman `pages/admin/laporan-shu.html` — tampilkan kalkulasi & distribusi

### 2.6 Audit Trail
- [ ] Tabel `audit_logs` (user_id, action, table_name, record_id, old_value, new_value, ip_address, timestamp)
- [ ] Helper `core/AuditLogger.php` — log setiap CREATE/UPDATE/DELETE penting
- [ ] Integrasikan di: loans.php, savings.php, members.php, accounting.php
- [ ] Halaman `pages/admin/audit-log.html` — tampilkan audit log dengan filter

### 2.7 Approval Workflow Multi-Level
- [ ] Tabel `approval_workflow` (entity_type, entity_id, level, approver_role, status, note)
- [ ] Maker-Checker: teller input → kasir review → admin approve
- [ ] Notifikasi in-app saat ada yang perlu di-approve
- [ ] API endpoint: `api/approvals.php`

---

## 🔄 Urutan Implementasi yang Disarankan

```
1. PHP Auth Middleware     → keamanan dulu sebelum fitur baru
2. Audit Trail             → logging foundation
3. Chart of Accounts       → master data akuntansi
4. Auto-journaling         → transaksi auto-catat ke jurnal
5. Laporan Keuangan        → UI + kalkulasi
6. Export PDF/Excel        → output laporan
7. SHU Calculation         → fitur koperasi inti
8. Approval Workflow       → governance
```

---

## 📁 File yang Perlu Dibuat/Dimodifikasi

### File Baru
```
core/Middleware.php           → PHP auth middleware
core/Permission.php           → RBAC permission map
core/AuditLogger.php          → audit trail helper
api/accounting.php            → accounting CRUD API
api/reports.php               → PDF/Excel export API
api/shu.php                   → SHU calculation API
api/approvals.php             → approval workflow API
pages/admin/accounting.html   → halaman akuntansi
pages/admin/laporan-keuangan.html → laporan keuangan
pages/admin/laporan-shu.html  → laporan SHU (sudah ada sebagai stub)
pages/admin/audit-log.html    → audit trail (sudah ada sebagai stub)
```

### File yang Dimodifikasi
```
api/loans.php     → tambah auto-journaling + audit trail
api/savings.php   → tambah auto-journaling + audit trail
api/members.php   → tambah audit trail
database/ksp_lamgabejaya_v2.sql → tambah tabel baru
```

---

## ✅ Definition of Done Phase 2

- [ ] Semua API dilindungi PHP auth middleware
- [ ] RBAC berjalan di level API (bukan hanya JS)
- [ ] Jurnal otomatis terbuat saat transaksi simpanan/pinjaman
- [ ] Neraca dan Laba Rugi dapat di-generate untuk periode manapun
- [ ] SHU dapat dikalkulasi dan didistribusikan
- [ ] Export PDF dan Excel bekerja untuk semua laporan utama
- [ ] Audit trail mencatat semua perubahan data penting
- [ ] Approval workflow multi-level berjalan
- [ ] Playwright test tidak ada regresi (masih ≥ 185/195)

---

## 🔧 Testing Phase 2

```bash
# Jalankan full test suite setelah implementasi
python3 /opt/lampp/htdocs/mono-v2/tests/playwright_comprehensive.py --headless

# Test API accounting manual
curl -s -X POST http://localhost/mono-v2/api/accounting.php \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"action":"get_trial_balance","periode":"2026-01"}'

# Test middleware
curl -s -X GET http://localhost/mono-v2/api/members.php
# Harus return 401 Unauthorized
```
