# Role System Guide - KSP Lam Gabe Jaya

## 🎯 Overview

Sistem role yang disederhanakan untuk kebutuhan koperasi dengan 5 role utama yang sesuai dengan operasional door-to-door.

## 👥 Role Hierarchy

### **Struktur Role (0=highest, 4=lowest)**
```
0. Bos/Pemilik          → Pemilik Koperasi
1. Admin                → Administrator Sistem  
2. Teller               → Petugas Kasir
3. Petugas Lapangan      → Petugas Kutipan Lapangan
4. Nasabah              → Anggota Koperasi
```

## 📋 Detail Role

### **🎩 1. Bos/Pemilik (ROLE_BOS)**
- **Level:** 0 (Tertinggi)
- **Akses:** Full system access
- **Fokus:** Monitoring bisnis, laporan keuangan, pengambilan keputusan strategis

#### **Menu:**
- Dashboard → Ringkasan Bisnis
- Laporan Keuangan → Overview finansial
- Data Nasabah → Database anggota
- Pinjaman → Portfolio pinjaman
- Simpanan → Overview simpanan
- Pengaturan → Konfigurasi sistem

#### **Widgets:**
- Ringkasan Bisnis
- Kesehatan Keuangan  
- Petugas Terbaik
- Alert Bisnis

---

### **👨‍💼 2. Admin (ROLE_ADMIN)**
- **Level:** 1
- **Akses:** Operasional penuh
- **Fokus:** Manajemen harian, nasabah, transaksi

#### **Menu:**
- Dashboard → Ringkasan Operasional
- Nasabah → Manajemen anggota
- Pinjaman → Pengajuan & monitoring
- Simpanan → Manajemen simpanan
- Transaksi → Input & monitoring
- Laporan → Laporan operasional

#### **Widgets:**
- Ringkasan Operasional
- Statistik Nasabah
- Portfolio Pinjaman
- Aktivitas Terbaru
- Aksi Cepat
- Notifikasi

---

### **💰 3. Teller (ROLE_TELLER)**
- **Level:** 2
- **Akses:** Transaksi kas
- **Fokus:** Pelayanan kas, setoran, penarikan

#### **Menu:**
- Dashboard → Ringkasan Harian
- Nasabah → Info nasabah
- Setoran → Input setoran
- Penarikan → Input penarikan
- Pembayaran → Cicilan pinjaman
- Laporan Harian → Rekap harian

#### **Widgets:**
- Ringkasan Harian
- Antrian Transaksi
- Saldo Kas
- Transaksi Terbaru

---

### **🚚 4. Petugas Lapangan (ROLE_FIELD_COLLECTOR)**
- **Level:** 3
- **Akses:** Operasional lapangan
- **Fokus:** Kutipan, kunjungan nasabah, GPS tracking

#### **Menu:**
- Dashboard → Target & progress
- Jadwal Kutipan → Schedule harian
- Rute Hari Ini → Navigation
- Nasabah Kunjungan → Daftar kunjungan
- Kutipan → Input kutipan
- GPS Log → Tracking log

#### **Widgets:**
- Target Harian
- Status Kutipan
- Progress Rute
- Kunjungan Hari Ini
- GPS Tracking

---

### **👤 5. Nasabah (ROLE_NASABAH)**
- **Level:** 4 (Terendah)
- **Akses:** Data pribadi
- **Fokus:** Info akun, simpanan, pinjaman pribadi

#### **Menu:**
- Dashboard → Ringkasan akun
- Profil Saya → Data pribadi
- Simpanan Saya → Saldo & riwayat
- Pinjaman Saya → Status & cicilan
- Riwayat Transaksi → Semua transaksi
- Pembayaran → Bayar cicilan

#### **Widgets:**
- Ringkasan Akun
- Saldo Simpanan
- Status Pinjaman
- Jadwal Pembayaran
- Transaksi Terbaru

## 🔐 Access Control

### **Permission Matrix**
| Fitur | Bos | Admin | Teller | Petugas | Nasabah |
|-------|-----|-------|--------|---------|---------|
| **Laporan Keuangan** | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Manajemen Nasabah** | ✅ | ✅ | 👁️ | 👁️ | 👁️ |
| **Input Pinjaman** | ✅ | ✅ | ❌ | ❌ | 📝 |
| **Input Simpanan** | ✅ | ✅ | ✅ | ❌ | 📝 |
| **Transaksi Kas** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Kutipan Lapangan** | ✅ | 👁️ | ❌ | ✅ | ❌ |
| **GPS Tracking** | ✅ | 👁️ | ❌ | ✅ | ❌ |
| **Pengaturan Sistem** | ✅ | ❌ | ❌ | ❌ | ❌ |

**Legend:**
- ✅ = Full access
- 👁️ = View only
- 📝 = Create own data
- ❌ = No access

## 🎮 Demo Accounts

### **Login Credentials:**
```
Bos/Pemilik:        bos/bos
Admin:              admin/admin  
Teller:             teller/teller
Petugas Lapangan:    collector/collector
Nasabah:            nasabah/nasabah
```

### **Quick Testing:**
1. **Bos** → Lihat overview bisnis
2. **Admin** → Manajemen nasabah & pinjaman
3. **Teller** → Transaksi kas harian
4. **Petugas** → Simulasi kunjungan lapangan
5. **Nasabah** → Portal anggota

## 🔄 Role-Based Features

### **Dashboard Customization**
Setiap role melihat dashboard yang relevan:

#### **Bos Dashboard:**
- Focus: Business metrics & financial health
- Data: Revenue, portfolio, performance indicators
- Actions: Strategic decisions, system settings

#### **Admin Dashboard:**
- Focus: Daily operations & member management
- Data: Member stats, loan portfolio, transaction volume
- Actions: Member management, loan approval, report generation

#### **Teller Dashboard:**
- Focus: Cash transactions & service queue
- Data: Daily transactions, cash balance, member visits
- Actions: Process deposits, withdrawals, payments

#### **Petugas Dashboard:**
- Focus: Field operations & collection targets
- Data: Daily targets, route progress, GPS location
- Actions: Log visits, record collections, GPS tracking

#### **Nasabah Dashboard:**
- Focus: Personal account information
- Data: Account balance, loan status, payment schedule
- Actions: View statements, make payments, request loans

## 📱 Mobile Considerations

### **Petugas Lapangan - Mobile First:**
- **GPS Tracking** - Real-time location
- **Offline Mode** - Work without internet
- **Photo Capture** - Document verification
- **Digital Signature** - Confirmation receipts
- **Route Optimization** - Efficient travel paths

### **Nasabah - Mobile Friendly:**
- **Quick Balance** - Instant account info
- **Payment Reminders** - Push notifications
- **Loan Calculator** - Simulation tools
- **Document Upload** - Loan applications
- **Chat Support** - Direct communication

## 🔧 Implementation Notes

### **Database Schema:**
```sql
-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role TINYINT NOT NULL, -- 0-4 based on ROLE_* constants
    email VARCHAR(100),
    phone VARCHAR(20),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### **Permission Checking:**
```php
// Check if user can access feature
function canAccessFeature($userRole, $feature) {
    $permissions = [
        'financial_reports' => [ROLE_BOS],
        'member_management' => [ROLE_BOS, ROLE_ADMIN],
        'cash_transactions' => [ROLE_BOS, ROLE_ADMIN, ROLE_TELLER],
        'field_operations' => [ROLE_BOS, ROLE_ADMIN, ROLE_FIELD_COLLECTOR],
        'personal_account' => [ROLE_BOS, ROLE_ADMIN, ROLE_TELLER, ROLE_FIELD_COLLECTOR, ROLE_NASABAH]
    ];
    
    return in_array($userRole, $permissions[$feature] ?? []);
}
```

### **Role Validation:**
```php
// Middleware for role-based access
function requireRole($requiredRole) {
    $user = getCurrentUser();
    
    if (!$user || $user['role'] > $requiredRole) {
        throw new AuthorizationException("Insufficient privileges");
    }
    
    return $user;
}
```

## 🚀 Future Enhancements

### **Planned Features:**
1. **Dynamic Permissions** - Configurable per feature
2. **Role Delegation** - Temporary permission grants
3. **Audit Trail** - Role-based activity logging
4. **Multi-Branch** - Location-based permissions
5. **Time-based Access** - Schedule restrictions

### **Security Improvements:**
1. **Two-Factor Auth** - For high-privilege roles
2. **Session Management** - Role-based timeout
3. **IP Restrictions** - Location-based access
4. **Device Management** - Trusted devices

---

**Status:** ✅ **IMPLEMENTED** - 5-role system ready for production use!
