# User Manual - Panduan Pengguna Aplikasi Koperasi SaaS

## 📖 Daftar Isi

1. [Pendahuluan](#pendahuluan)
2. [Login & Authentication](#login--authentication)
3. [Dashboard Overview](#dashboard-overview)
4. [Panduan Role Spesifik](#panduan-role-spesifik)
5. [Fitur Umum](#fitur-umum)
6. [Troubleshooting](#troubleshooting)

---

## 🎯 Pendahuluan

### Tentang Aplikasi
Aplikasi Koperasi SaaS adalah sistem manajemen koperasi modern yang dirancang untuk meningkatkan efisiensi operasional koperasi harian. Aplikasi ini dilengkapi dengan fitur GPS tracking, manajemen pinjaman, simpanan, dan pelaporan komprehensif.

### Fitur Utama
- **Multi-Role System**: 8 role pengguna dengan akses berbeda
- **GPS Tracking**: Monitoring lokasi petugas lapangan
- **Manajemen Pinjaman**: Proses pinjaman digital end-to-end
- **Simpanan**: Manajemen rekening simpanan anggota
- **Pelaporan**: Laporan real-time dan analitik
- **Mobile Responsive**: Akses dari berbagai perangkat

---

## 🔐 Login & Authentication

### Cara Login
1. Buka browser dan akses `http://localhost/mono`
2. Masukkan email dan password
3. Klik tombol "Masuk"
4. Sistem akan mengarahkan ke dashboard sesuai role

### Akun Demo
| Role | Email | Password |
|------|-------|----------|
| Super Admin | `test_super_admin@lamabejaya.coop` | `password123` |
| Admin | `test_admin@lamabejaya.coop` | `password123` |
| Mantri | `test_mantri@lamabejaya.coop` | `password123` |
| Member | `test_member@lamabejaya.coop` | `password123` |
| Kasir | `test_kasir@lamabejaya.coop` | `password123` |
| Teller | `test_teller@lamabejaya.coop` | `password123` |
| Surveyor | `test_surveyor@lamabejaya.coop` | `password123` |
| Collector | `test_collector@lamabejaya.coop` | `password123` |

### Keamanan
- Password dienkripsi dengan hashing
- Session timeout otomatis
- Logout otomatis saat tidak aktif
- Akses berdasarkan role permission

---

## 📊 Dashboard Overview

### Navigasi Utama
- **Sidebar Menu**: Menu navigasi utama di sisi kiri
- **Top Bar**: Informasi user dan logout
- **Main Content**: Area konten utama
- **Quick Actions**: Tombol aksi cepat

### Widget Dashboard
- **Statistik Hari Ini**: Jumlah transaksi, anggota, dll
- **Grafik Performa**: Visualisasi data mingguan/bulanan
- **Aktivitas Terbaru**: Log aktivitas sistem
- **Task List**: Daftar tugas pending

### Responsive Design
- **Desktop**: Layout penuh dengan sidebar
- **Tablet**: Layout medium dengan collapsible sidebar
- **Mobile**: Layout mobile dengan hamburger menu

---

## 👥 Panduan Role Spesifik

### 🔴 Super Admin

#### **Dashboard**
- Monitoring sistem keseluruhan
- Statistik pengguna aktif
- Status server dan database
- Laporan kesalahan sistem

#### **Fitur Utama**
- **User Management**: Tambah, edit, hapus user
- **System Settings**: Konfigurasi sistem
- **Role Management**: Kelola role dan permission
- **System Reports**: Laporan sistem komprehensif
- **Security Center**: Monitoring keamanan

#### **Tugas Utama**
- Mengelola user dan role
- Monitoring kesehatan sistem
- Konfigurasi pengaturan global
- Backup dan restore data

---

### 🔵 Admin

#### **Dashboard**
- Statistik operasional harian
- Performa pinjaman dan simpanan
- Aktivitas anggota
- Laporan keuangan

#### **Fitur Utama**
- **Member Management**: Kelola data anggota
- **Loan Management**: Proses pinjaman
- **Reports**: Laporan operasional
- **Settings**: Pengaturan admin
- **Staff Management**: Kelola staf

#### **Tugas Utama**
- Approve/reject aplikasi pinjaman
- Monitoring performa koperasi
- Manajemen anggota dan staf
- Generate laporan operasional

---

### 🟠 Mantri

#### **Dashboard**
- Statistik kunjungan lapangan
- Target penagihan harian
- GPS tracking real-time
- Jadwal kunjungan

#### **Fitur Utama**
- **Field Data**: Input data lapangan
- **GPS Tracking**: Monitoring lokasi
- **Route Planning**: Perencanaan rute
- **Collection**: Penagihan lapangan
- **Verification**: Verifikasi anggota

#### **Tugas Utama**
- Kunjungi anggota di lokasi
- Lakukan penagihan pinjaman
- Input data lapangan
- Verifikasi dokumen anggota

---

### 🟢 Member

#### **Dashboard**
- Informasi rekening
- Riwayat transaksi
- Status pinjaman
- Notifikasi

#### **Fitur Utama**
- **Profile**: Kelola profil pribadi
- **Accounts**: Informasi rekening
- **Transactions**: Riwayat transaksi
- **Applications**: Aplikasi pinjaman
- **Messages**: Komunikasi dengan koperasi

#### **Tugas Utama**
- Cek saldo rekening
- Ajukan aplikasi pinjaman
- Lihat riwayat transaksi
- Update profil pribadi

---

### 🟡 Kasir

#### **Dashboard**
- Statistik pembayaran
- Saldo kas harian
- Transaksi pending
- Laporan kas

#### **Fitur Utama**
- **Payment Processing**: Proses pembayaran
- **Cash Management**: Manajemen kas
- **Transactions**: Riwayat transaksi
- **Reports**: Laporan kas

#### **Tugas Utama**
- Proses pembayaran tunai
- Kelola saldo kas
- Input transaksi harian
- Generate laporan kas

---

### 🟣 Teller

#### **Dashboard**
- Statistik rekening
- Aplikasi pinjaman
- Performa kredit
- Aktivitas teller

#### **Fitur Utama**
- **Account Management**: Manajemen rekening
- **Loan Processing**: Proses pinjaman
- **Credit Reports**: Laporan kredit
- **Customer Service**: Layanan nasabah

#### **Tugas Utama**
- Buka/tutup rekening
- Proses aplikasi pinjaman
- Layani nasabah
- Generate laporan kredit

---

### 🟤 Surveyor

#### **Dashboard**
- Statistik survei
- Verifikasi pending
- Jadwal survei
- Progress tracking

#### **Fitur Utama**
- **Survey Management**: Manajemen survei
- **Verification**: Verifikasi data
- **Field Data**: Input lapangan
- **Reports**: Laporan survei

#### **Tugas Utama**
- Lakukan survei calon anggota
- Verifikasi dokumen
- Input hasil survei
- Generate laporan verifikasi

---

### ⚫ Collector

#### **Dashboard**
- Statistik penagihan
- Akun telat bayar
- Rute penagihan
- Performa collection

#### **Fitur Utama**
- **Collection Management**: Manajemen penagihan
- **Overdue Accounts**: Akun telat bayar
- **Reports**: Laporan penagihan
- **Route Planning**: Perencanaan rute

#### **Tugas Utama**
- Lakukan penagihan lapangan
- Update status pembayaran
- Kelola akun telat bayar
- Generate laporan collection

---

## 🔧 Fitur Umum

### **Navigasi & Search**
- **Search Bar**: Cari data cepat
- **Breadcrumb**: Navigasi hierarki
- **Quick Links**: Link akses cepat
- **Recent Items**: Item terakhir diakses

### **Forms & Validation**
- **Auto-save**: Simpan otomatis
- **Validation**: Validasi real-time
- **Error Messages**: Pesan error jelas
- **Success Notifications**: Notifikasi sukses

### **Reports & Analytics**
- **Export**: Export ke PDF/Excel
- **Filter**: Filter data dinamis
- **Date Range**: Pilih rentang tanggal
- **Drill-down**: Detail data terperinci

### **Notifications**
- **Real-time**: Notifikasi real-time
- **Email**: Email notifications
- **SMS**: SMS alerts (jika diaktifkan)
- **Push**: Push notifications

---

## 🐛 Troubleshooting

### **Masalah Login**
**Problem**: Tidak bisa login
**Solution**:
1. Periksa email dan password
2. Pastikan akun aktif
3. Clear browser cache
4. Hubungi admin

### **Dashboard Tidak Muncul**
**Problem**: Dashboard blank/error
**Solution**:
1. Refresh browser
2. Check koneksi internet
3. Clear browser cache
4. Coba browser lain

### **Data Tidak Muncul**
**Problem**: Tabel/data kosong
**Solution**:
1. Check filter tanggal
2. Refresh data
3. Check permission akses
4. Hubungi admin

### **Error Messages**
**Problem**: Pesan error muncul
**Solution**:
1. Screenshot error
2. Catat langkah yang dilakukan
3. Hubungi support
4. Check log sistem

### **Performance Issues**
**Problem**: Aplikasi lambat
**Solution**:
1. Close tab browser lain
2. Check koneksi internet
3. Clear browser cache
4. Restart browser

---

## 📞 Support & Bantuan

### **Contact Information**
- **Email Support**: support@koperasi.coop
- **Phone Support**: 021-12345678
- **WhatsApp**: 08123456789
- **Office Hours**: Senin-Jumat, 08:00-17:00

### **Help Resources**
- **User Manual**: Dokumentasi ini
- **Video Tutorial**: [Link video]
- **FAQ**: [Link FAQ]
- **Training Schedule**: [Link training]

### **Emergency Support**
- **System Down**: Hubungi IT Support
- **Data Loss**: Hubungi Database Admin
- **Security Issue**: Hubungi Security Team

---

## 📈 Tips & Best Practices

### **Untuk Super Admin**
- Backup data secara teratur
- Monitor sistem health
- Update password secara berkala
- Review access logs

### **Untuk Admin**
- Approve pinjaman dengan hati-hati
- Monitor performa koperasi
- Update data anggota
- Generate laporan berkala

### **Untuk Mantri**
- Update GPS tracking
- Input data lapangan akurat
- Follow up penagihan tepat waktu
- Verifikasi dokumen lengkap

### **Untuk Member**
- Update profil secara berkala
- Monitor rekening dan pinjaman
- Laporkan masalah segera
- Gunakan fitur auto-save

---

**Versi Dokumen**: 1.0  
**Tanggal Update**: 18 Maret 2026  
**Next Review**: 18 April 2026

---

*Panduan ini akan terus diperbarui sesuai dengan perkembangan aplikasi. Untuk saran dan masukan, hubungi tim support.*
