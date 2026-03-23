# KSP Lam Gabe Jaya - Sistem Manajemen Koperasi

## 🏢 Tentang Aplikasi

KSP Lam Gabe Jaya adalah sistem manajemen koperasi simpan pinjam yang dikembangkan dengan arsitektur modern 3-tier untuk memberikan solusi yang handal, aman, dan mudah digunakan.

## 📋 Fitur Utama

### 🔐 Authentication & Authorization
- Multi-role user system (BOS, Admin, Teller, Collector, Nasabah)
- Secure session management
- API-based authentication
- Password hashing dengan bcrypt

### 👥 Manajemen Anggota
- Registrasi anggota baru
- Validasi data anggota (NIK, telepon, alamat)
- Status keanggotaan (active, inactive, suspended)
- Riwayat transaksi anggota

### 💰 Manajemen Simpanan
- Jenis simpanan (wajib, sukarela, berjangka)
- Perhitungan bunga otomatis
- Laporan simpanan anggota
- Penarikan simpanan

### 🏦 Manajemen Pinjaman
- Pengajuan pinjaman online
- Proses approval multi-level
- Perhitungan bunga dan angsuran
- Tracking pembayaran angsuran

### 📊 Dashboard & Laporan
- Dashboard dinamis per-role
- Laporan keuangan real-time
- Export data (PDF, Excel)
- Grafik dan visualisasi data

### 🌐 Dynamic Navigation System
- SPA-like navigation tanpa reload
- Hash-based URLs
- Dynamic content loading
- Browser back/forward support

## 🏗️ Arsitektur Teknis

### **Frontend Stack**
- **HTML5** dengan semantic elements
- **Bootstrap 5.3.0** untuk responsive design
- **Font Awesome 6.4.0** dengan local fallback
- **Vanilla JavaScript** untuk dynamic navigation

### **Backend Stack**
- **PHP 8.x** dengan OOP patterns
- **MySQL/MariaDB** untuk data persistence
- **RESTful API** untuk client-server communication
- **Session-based authentication**

### **Database Schema**
```sql
users (id, username, password, role, name, email, phone, address, created_at, updated_at, status)
transactions (id, user_id, type, amount, description, created_at, status)
savings (id, user_id, type, amount, interest_rate, created_at, status)
loans (id, user_id, amount, interest_rate, term, monthly_payment, created_at, status)
```

## 🎯 Role-Based Access Control

### **🔴 BOS (Owner)**
- Full system access
- Financial reports and analytics
- User management
- System configuration

### **🔵 Admin**
- Operational management
- Transaction approval
- Member management
- Report generation

### **🟢 Teller**
- Daily transactions
- Deposit and withdrawal
- Customer service
- Basic reporting

### **🟡 Collector**
- Field operations
- Payment collection
- Route management
- GPS tracking

### **🟣 Nasabah**
- Personal dashboard
- Account overview
- Transaction history
- Loan applications

## 🚀 Dynamic Content Implementation

### **Dashboard Per Role**
Setiap role memiliki dashboard yang disesuaikan dengan kebutuhan:

#### **BOS Dashboard**
- Total Anggota: 150 (+12%)
- Total Simpanan: Rp 250Jt (+15%)
- Total Omzet: Rp 450Jt (+18%)
- Grafik pertumbuhan keuangan

#### **Admin Dashboard**
- Anggota Aktif: 125 (+8%)
- Transaksi Hari Ini: 45 (+10%)
- User Terdaftar: 180 (+6%)
- Task list management

#### **Teller Dashboard**
- Transaksi Hari Ini: 28 (+12%)
- Setoran: Rp 15Jt (+8%)
- Penarikan: Rp 8Jt (-5%)
- Transaksi terkini table

#### **Collector Dashboard**
- Target Hari Ini: 15 (0%)
- Kunjungan Selesai: 8 (+53%)
- Kutipan Terkumpul: Rp 2.5Jt (+18%)
- Rute dan GPS tracking

#### **Nasabah Dashboard**
- Saldo Simpanan: Rp 5Jt (+2%)
- Pinjaman Aktif: Rp 10Jt (0%)
- Cicilan Bulanan: Rp 500rb
- Ringkasan akun personal

### **Halaman Fungsional**
- **Laporan Keuangan**: Financial metrics, charts, export functionality
- **Data Nasabah**: Customer management dengan CRUD operations
- **Manajemen Transaksi**: Transaction history dengan filters
- **Profil Personal**: User account management

## 🔧 Installation & Setup

### **Prerequisites**
- PHP 8.x dengan extensions:
  - mysqli
  - json
  - session
  - openssl
- MySQL/MariaDB 5.7+
- Web server (Apache/Nginx)
- Modern web browser

### **Installation Steps**
```bash
# 1. Clone repository
git clone <repository-url>
cd mono-v2

# 2. Setup database
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed_data.sql

# 3. Configure
cp config/config.example.php config/config.php
# Edit config.php dengan database credentials

# 4. Set permissions
chmod 755 .
chmod 644 *.php
chmod 755 api/

# 5. Access application
http://localhost/mono-v2/
```

### **Default Login Credentials**
```
BOS: username=bos, password=bos
Admin: username=admin, password=admin
Teller: username=teller, password=teller
Collector: username=collector, password=collector
Nasabah: username=nasabah, password=nasabah
```

## 🧪 Testing

### **Automated Tests**
```bash
# Test login untuk semua roles
php test-login-all-roles.php

# Test dynamic navigation
php test-dynamic-navigation.php

# Test comprehensive fixes
php test-comprehensive-fixes.php

# Test JavaScript syntax
php test-javascript-syntax.php

# Test error checking
php test-comprehensive-errors.php
```

### **Manual Testing**
1. Login dengan setiap role
2. Test navigation menu
3. Verify dashboard content per role
4. Test dynamic content loading
5. Check responsive design

## 🐛 Known Issues & Fixes

### **✅ Resolved Issues**
1. **Font Awesome Integrity Hash**: Fixed dengan version 6.4.0 + local fallback
2. **Role-Based Statistics**: Setiap role sekarang punya statistik relevan
3. **Dynamic Content**: Halaman tidak lagi menampilkan dashboard content
4. **JavaScript Syntax**: Template literals dan function calls sudah diperbaiki

### **🔧 Current Status**
- ✅ Authentication system working
- ✅ Role-based access control implemented
- ✅ Dynamic navigation functional
- ✅ Dashboard per role working
- ⚠️ JavaScript syntax errors (being addressed)
- ⚠️ Some pages still showing "Coming Soon"

## 📱 API Documentation

### **Authentication Endpoint**
```
POST /api/auth.php
Content-Type: application/x-www-form-urlencoded

action=login&username={username}&password={password}
```

### **Response Format**
```json
{
    "success": true,
    "user": {
        "id": 1,
        "username": "bos",
        "role": "bos",
        "name": "Administrator"
    },
    "message": "Login successful"
}
```

## 🔄 Version History

### **v2.0.0 - Current**
- ✅ Dynamic navigation system
- ✅ Role-based dashboards
- ✅ SPA-like experience
- ✅ Font Awesome fallback
- ✅ Comprehensive testing

### **v1.0.0 - Original**
- ✅ Basic authentication
- ✅ Role system
- ✅ Database structure
- ✅ Basic UI

## 🤝 Contributing

1. Fork repository
2. Create feature branch
3. Make changes
4. Test thoroughly
5. Submit pull request

## 📞 Support

Untuk support atau pertanyaan:
- Email: support@ksp-lamgabejaya.com
- Phone: +62 xxx-xxxx-xxxx
- Documentation: `/documentation/`

## 📄 License

MIT License - see LICENSE file untuk details

---

**KSP Lam Gabe Jaya** - Solusi Digital untuk Koperasi Modern 🏦✨
- Jadwal pembayaran angsuran
- Pelunasan pinjaman

### 📊 Laporan & Analisis
- Laporan keuangan bulanan/tahunan
- Analisis performa koperasi
- Export laporan ke PDF/Excel
- Dashboard real-time

## 🏗️ Arsitektur Sistem

### 📁 Struktur Direktori
```
mono-v2/
├── 📁 public/                 # Frontend - Web accessible files
│   ├── 📄 index.php          # Main entry point
│   ├── 📁 assets/            # CSS, JS, images
│   └── 📁 uploads/           # User uploads
├── 📁 app/                   # Middleware - Business logic
│   ├── 📁 Controllers/       # Application controllers
│   ├── 📁 Models/            # Data models
│   ├── 📁 Services/          # Business services
│   └── 📁 Views/             # View templates
├── 📁 core/                  # Backend - System core
│   ├── 📁 Config/            # Configuration files
│   ├── 📁 Auth/              # Authentication system
│   ├── 📁 Database/          # Database utilities
│   ├── 📁 Cache/             # Caching system
│   └── 📁 Logger/            # Logging system
├── 📁 api/                   # RESTful API endpoints
│   ├── 📄 auth.php           # Authentication API
│   ├── 📄 members.php        # Member management API
│   ├── 📄 transactions.php   # Transaction API
│   └── 📄 reports.php        # Reports API
├── 📁 storage/               # Application storage
│   ├── 📁 cache/             # Cache files
│   ├── 📁 logs/              # Application logs
│   ├── 📁 sessions/          # Session files
│   └── 📁 uploads/           # Upload storage
├── 📁 database/              # Database files
│   ├── 📄 gabe.sql           # Database export
│   └── 📄 schema.sql         # Database schema
├── 📁 docs/                  # Documentation
├── 📁 tests/                 # Test files
└── 📁 scripts/               # Utility scripts
```

### 🔧 Teknologi yang Digunakan

#### Backend
- **PHP 8.3+** - Server-side scripting
- **MySQL/MariaDB** - Database management
- **PDO** - Database abstraction
- **Composer** - Dependency management

#### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Styling and animations
- **JavaScript (ES6+)** - Client-side scripting
- **Bootstrap 5** - UI framework
- **jQuery** - DOM manipulation

#### API
- **RESTful API** - API architecture
- **JSON** - Data exchange format
- **JWT-like tokens** - Session management

## 🚀 Instalasi

### Persyaratan Sistem
- PHP 8.3 atau lebih tinggi
- MySQL/MariaDB 10.4+
- Apache/Nginx web server
- Composer (untuk dependency management)

### Langkah Instalasi

#### 1. Clone Repository
```bash
git clone https://github.com/your-repo/mono-v2.git
cd mono-v2
```

#### 2. Konfigurasi Database
```bash
# Import database
mysql -u root -p < database/gabe.sql

# Atau gunakan phpMyAdmin
# 1. Buka phpMyAdmin
# 2. Import file database/gabe.sql
```

#### 3. Konfigurasi Environment
```bash
# Salin file konfigurasi
cp .env.example .env

# Edit file .env
nano .env
```

#### 4. Set Permissions
```bash
chmod -R 755 /path/to/mono-v2
chmod -R 777 /path/to/mono-v2/storage
chmod -R 777 /path/to/mono-v2/public/uploads
```

#### 5. Akses Aplikasi
```bash
# Buka browser
http://localhost/mono-v2/
```

## 🔑 Login Default

### Default Users
```
Username: bos        Password: bos        Role: Bos/Pemilik Koperasi
Username: admin      Password: admin      Role: Administrator
Username: teller     Password: teller     Role: Petugas Teller
Username: collector  Password: collector  Role: Petugas Lapangan
Username: nasabah    Password: nasabah    Role: Nasabah/Anggota
```

### � Role Management System

#### Hierarki Role
Sistem menggunakan 5 level role dengan hierarki akses yang jelas:

```
Level 0: 🔴 BOS/Pemilik Koperasi     (Full Access)
Level 1: 🔵 Administrator            (System Management)  
Level 2: 🟢 Petugas Teller           (Operational)
Level 3: 🟡 Petugas Lapangan        (Field Operations)
Level 4: 🟣 Nasabah/Anggota           (Personal Access)
```

#### Detail Role & Permissions

##### 🔴 BOS/Pemilik Koperasi
**Username**: `bos` | **Password**: `bos`

**Deskripsi**: Pemilik utama koperasi dengan akses penuh ke seluruh sistem.

**Permissions**:
- ✅ **Full System Access** - Kontrol penuh semua fitur
- ✅ **User Management** - Kelola semua user termasuk admin
- ✅ **System Configuration** - Pengaturan sistem dan konfigurasi
- ✅ **Financial Oversight** - Akses semua laporan keuangan
- ✅ **Business Intelligence** - Analisis dan dashboard komprehensif

**Fitur Utama**:
- Dashboard bisnis lengkap
- Laporan keuangan detail
- Manajemen pengguna sistem
- Pengaturan koperasi
- Backup & restore data

---

##### 🔵 Administrator
**Username**: `admin` | **Password**: `admin`

**Deskripsi**: Pengelola sistem harian dengan akses administratif penuh.

**Permissions**:
- ✅ **System Administration** - Kelola konfigurasi sistem
- ✅ **User Management** - Kelola user (kecuali BOS)
- ✅ **Database Management** - Operasi database dan maintenance
- ✅ **Report Access** - Akses semua laporan operasional
- ✅ **Configuration Control** - Pengaturan aplikasi

**Fitur Utama**:
- Dashboard operasional
- Manajemen member dan user
- Portfolio pinjaman
- Laporan dan statistik
- Aksi cepat administratif

---

##### 🟢 Petugas Teller
**Username**: `teller` | **Password**: `teller`

**Deskripsi**: Petugas kasir yang menangani transaksi harian dan layanan anggota.

**Permissions**:
- ✅ **Transaction Processing** - Proses semua transaksi
- ✅ **Member Services** - Layanan nasabah harian
- ✅ **Cash Management** - Kelola kas dan uang tunai
- ✅ **Basic Reporting** - Laporan transaksi harian
- ✅ **Customer Support** - Bantian nasabah

**Fitur Utama**:
- Dashboard transaksi harian
- Antrian transaksi
- Setoran & penarikan
- Pembayaran angsuran
- Laporan kas harian

---

##### 🟡 Petugas Lapangan
**Username**: `collector` | **Password**: `collector`

**Deskripsi**: Petugas lapangan untuk kutipan, kunjungan nasabah, dan operasi mobile.

**Permissions**:
- ✅ **Field Operations** - Operasi di luar kantor
- ✅ **Collection Management** - Kutipan pembayaran
- ✅ **Member Visits** - Kunjungan nasabah
- ✅ **Mobile Access** - Akses via mobile app
- ✅ **GPS Tracking** - Pelacakan lokasi kunjungan

**Fitur Utama**:
- Dashboard operasi lapangan
- Jadwal kunjungan harian
- Rute dan target kutipan
- GPS tracking
- Laporan kunjungan

---

##### 🟣 Nasabah/Anggota
**Username**: `nasabah` | **Password**: `nasabah`

**Deskripsi**: Anggota koperasi dengan akses terbatas ke data pribadi.

**Permissions**:
- ✅ **Personal Data Access** - Data pribadi sendiri
- ✅ **Account Viewing** - Lihat rekening sendiri
- ✅ **Loan Applications** - Ajukan pinjaman
- ✅ **Transaction History** - Riwayat transaksi
- ✅ **Profile Management** - Kelola profil sendiri

**Fitur Utama**:
- Dashboard personal
- Informasi simpanan
- Status pinjaman
- Riwayat transaksi
- Pengajuan online

---

#### 🔄 Role-Based Access Control (RBAC)

**Implementasi Teknis**:
- **Database**: `role_master` table dengan permissions JSON
- **Session Management**: Role data tersimpan di session
- **Permission Checking**: Fungsi `hasPermission()` untuk validasi
- **Dynamic Menu**: Menu items disesuaikan per role
- **API Security**: Endpoint protection berdasarkan role

**Struktur Database**:
```sql
-- Role Master Table
CREATE TABLE role_master (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) UNIQUE NOT NULL,        -- bos, admin, teller, collector, nasabah
    role_display_name VARCHAR(100) NOT NULL,     -- Bos/Pemilik Koperasi, dll
    role_level INT NOT NULL,                      -- 0, 1, 2, 3, 4 (lower = higher priority)
    permissions JSON,                            -- {"access": "full", "can_manage_users": true}
    is_active BOOLEAN DEFAULT TRUE
);

-- Users Table dengan Role Reference
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    role_id INT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES role_master(id)
);
```

**Permission Examples**:
```json
// BOS Permissions
{
    "access": "full",
    "can_manage_users": true,
    "can_manage_system": true,
    "can_view_all_reports": true,
    "can_manage_finances": true
}

// Teller Permissions  
{
    "access": "operational",
    "can_process_transactions": true,
    "can_manage_members": true,
    "can_view_reports": true,
    "can_handle_cash": true
}

// Nasabah Permissions
{
    "access": "personal", 
    "can_view_own_data": true,
    "can_apply_loans": true,
    "can_view_transactions": true,
    "can_manage_profile": true
}
```

#### 🎯 Role-Based Dashboard

Setiap role memiliki dashboard yang disesuaikan dengan kebutuhan:

- **BOS**: Overview bisnis, financial health, top performers
- **Admin**: Operational summary, member stats, loan portfolio  
- **Teller**: Daily summary, transaction queue, cash balance
- **Collector**: Daily targets, collection status, route progress
- **Nasabah**: Account summary, savings balance, loan status

#### 🔐 Security Implementation

**Session Security**:
- Role data disimpan di session terenkripsi
- Automatic logout setelah idle timeout
- Session validation pada setiap request

**Permission Validation**:
```php
// Check permission example
if (hasPermission($userRole, 'can_manage_users')) {
    // Allow user management
} else {
    // Deny access
    redirect('/dashboard');
}
```

**API Security**:
- Endpoint protection berdasarkan role
- JWT tokens dengan role information
- Request validation untuk setiap API call

### �🔐 Keamanan Password

#### Hashing Algorithm
- **Algoritma**: bcrypt (PASSWORD_DEFAULT)
- **Cost Factor**: 10 ( dapat disesuaikan )
- **Fungsi**: `password_hash()` dan `password_verify()`

#### Format Hash di Database
```sql
-- Contoh hash untuk password "password"
$2y$10$drgQXhqMgJ4gWOuyCmb/IuC2sCPB27/Wo1u5SfMfG5lVJzYa3wghu
```

#### Struktur Hash
- **$2y$**: Algoritma bcrypt version
- **10**: Cost factor (computational cost)
- **22 karakter**: Salt (random)
- **31 karakter**: Hash result

#### Pembuatan Hash Baru
```php
// Generate hash untuk password baru
$password = 'new_password';
$hash = password_hash($password, PASSWORD_DEFAULT);

// Verifikasi password
if (password_verify($input_password, $stored_hash)) {
    // Password benar
}
```

#### Update Password User
```sql
-- Update password untuk user tertentu
UPDATE users SET password = '$2y$10$NEW_HASH_HERE' WHERE username = 'username';
```

#### Security Notes
- Password tidak pernah disimpan sebagai plain text
- Setiap user memiliki salt yang unik
- Hash computation slow untuk mencegah brute force
- Gunakan `PASSWORD_DEFAULT` untuk kompatibilitas masa depan

## 📚 Dokumentasi

### 📖 Panduan Pengguna
- [Panduan Instalasi](docs/INSTALLATION.md)
- [Panduan Pengguna](docs/USER_GUIDE.md)
- [Panduan Administrator](docs/ADMIN_GUIDE.md)

### 🔧 Dokumentasi Teknis
- [API Documentation](docs/API_DOCUMENTATION.md)
- [Database Schema](docs/DATABASE_SCHEMA.md)
- [Architecture Guide](docs/ARCHITECTURE.md)

## 🔌 API Endpoints

### Authentication
```http
POST /api/auth.php                    # Login
GET  /api/auth.php?action=check_session # Session check
POST /api/auth.php?action=logout      # Logout
```

### Members
```http
GET    /api/members.php               # Get all members
POST   /api/members.php               # Create member
GET    /api/members.php?id={id}       # Get member by ID
PUT    /api/members.php?id={id}       # Update member
DELETE /api/members.php?id={id}       # Delete member
```

### Transactions
```http
GET    /api/transactions.php          # Get transactions
POST   /api/transactions.php          # Create transaction
GET    /api/transactions.php?id={id}  # Get transaction by ID
```

## 🗄️ Database Schema

### Tabel Utama
- `users` - Data pengguna
- `role_master` - Master data role dan permissions
- `members` - Data anggota koperasi
- `savings` - Data simpanan
- `loans` - Data pinjaman
- `transactions` - Data transaksi
- `reports` - Data laporan

### Database Schema - Role Management
```sql
-- Role Master Table
CREATE TABLE role_master (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) UNIQUE NOT NULL,
    role_display_name VARCHAR(100) NOT NULL,
    role_description TEXT,
    role_level INT NOT NULL COMMENT 'Lower number = higher priority',
    permissions JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users Table with Role Reference
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,  -- bcrypt hash
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    role_id INT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES role_master(id),
    status ENUM('active','inactive','suspended') DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 🛠️ Development

### Environment Setup
```bash
# Install dependencies
composer install

# Run development server
php -S localhost:8000 -t public/
```

### Testing
```bash
# Run all tests
php tests/SystemTest.php

# Run specific test
php tests/AuthenticationTest.php
```

### Code Style
- Mengikuti PSR-12 coding standard
- Menggunakan PHPDoc untuk dokumentasi
- Unit testing dengan PHPUnit

## 🔒 Keamanan

### Fitur Keamanan
- **Password hashing dengan bcrypt** (cost factor 10)
- **SQL injection prevention** dengan PDO prepared statements
- **XSS protection** dengan output escaping
- **CSRF protection** dengan tokens
- **Session security** dengan HttpOnly cookies
- **Rate limiting** untuk login attempts (max 5 attempts)
- **File upload validation** dengan type checking
- **Audit logging** untuk semua aktivitas penting

### Password Security Implementation
```php
// Hash generation (saat registrasi/update password)
$hash = password_hash($password, PASSWORD_DEFAULT);

// Password verification (saat login)
if (password_verify($inputPassword, $storedHash)) {
    // Login berhasil
}
```

### Database Password Storage
```sql
-- Tabel users dengan kolom password
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,  -- bcrypt hash
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    role ENUM('bos','admin','teller','collector','nasabah') NOT NULL,
    status ENUM('active','inactive','suspended') DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Best Practices
- Regular security audits
- Input validation and sanitization
- Error handling without information disclosure
- Secure session management
- Regular updates and patches

## 📈 Monitoring & Logging

### Logging System
- Application logs: `storage/logs/`
- Error logs: `storage/logs/error.log`
- Access logs: `storage/logs/access.log`
- Security logs: `storage/logs/security.log`

### Monitoring
- System health checks
- Performance monitoring
- Database query optimization
- Error tracking and reporting

## 🚀 Deployment

### Production Deployment
```bash
# Run deployment script
./scripts/deploy.sh production

# Manual deployment
1. Backup database
2. Update code
3. Run migrations
4. Clear cache
5. Set permissions
6. Restart services
```

### Environment Configuration
- Development: Local development
- Staging: Pre-production testing
- Production: Live environment

## 🤝 Kontribusi

### Cara Berkontribusi
1. Fork repository
2. Create feature branch
3. Make changes
4. Add tests
5. Submit pull request

### Guidelines
- Follow coding standards
- Add documentation
- Test thoroughly
- Update changelog

## 📝 Changelog

### Version 2.0.0 (Current)
- ✅ 3-tier architecture implementation
- ✅ RESTful API development
- ✅ Enhanced security features
- ✅ Improved user interface
- ✅ Database optimization
- ✅ Performance improvements

### Version 1.0.0 (Legacy)
- Basic functionality
- Simple authentication
- Limited features

## 📞 Support

### Hubungi Kami
- Email: admin@kspgabejaya.com
- Phone: +62-xxx-xxxx-xxxx
- Website: www.kspgabejaya.com

### Bantuan Teknis
- Documentation: [docs/](docs/)
- Issue Tracker: GitHub Issues
- Community Forum: [forum.kspgabejaya.com](https://forum.kspgabejaya.com)

## � Troubleshooting

### Login Issues

#### "Invalid username or password"
1. **Verify credentials**:
   ```bash
   # Check user di database
   mysql -u root -proot -e "USE gabe; SELECT username, role, status FROM users;"
   ```

2. **Reset password user**:
   ```bash
   # Generate hash baru
   php -r "echo password_hash('password', PASSWORD_DEFAULT);"
   
   # Update di database
   mysql -u root -proot -e "USE gabe; UPDATE users SET password = 'HASH_HERE' WHERE username = 'admin';"
   ```

3. **Check user status**:
   ```sql
   -- User harus status 'active'
   SELECT username, status FROM users WHERE username = 'admin';
   ```

#### Database Connection Issues
1. **Check XAMPP status**:
   ```bash
   sudo /opt/lampp/lampp status
   ```

2. **Verify database exists**:
   ```bash
   mysql -u root -proot -e "SHOW DATABASES;"
   ```

3. **Check database tables**:
   ```bash
   mysql -u root -proot -e "USE gabe; SHOW TABLES;"
   ```

4. **Verify role_master data**:
   ```bash
   mysql -u root -proot -e "USE gabe; SELECT * FROM role_master;"
   ```

5. **Check user-role relationships**:
   ```bash
   mysql -u root -proot -e "USE gabe; SELECT u.username, r.role_display_name FROM users u JOIN role_master r ON u.role_id = r.id;"
   ```

### Password Hash Verification
```bash
# Test hash verification
php -r "
\$hash = '\$2y\$10\$drgQXhqMgJ4gWOuyCmb/IuC2sCPB27/Wo1u5SfMfG5lVJzYa3wghu';
if (password_verify('password', \$hash)) {
    echo 'Password matches!' . PHP_EOL;
} else {
    echo 'Password does not match' . PHP_EOL;
}
"
```

## �📄 Lisensi

MIT License - lihat file [LICENSE](LICENSE) untuk detail.

## 🙏 Kredit

### Tim Pengembang
- Lead Developer: [Your Name]
- Backend Developer: [Team Member]
- Frontend Developer: [Team Member]
- Database Administrator: [Team Member]

### Teknologi Pihak Ketiga
- PHP: [php.net](https://www.php.net/)
- MySQL: [mysql.com](https://www.mysql.com/)
- Bootstrap: [getbootstrap.com](https://getbootstrap.com/)
- jQuery: [jquery.com](https://jquery.com/)

---

**© 2026 KSP Lam Gabe Jaya. All rights reserved.**
