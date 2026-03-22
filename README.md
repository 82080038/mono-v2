# KSP Lam Gabe Jaya - Sistem Manajemen Koperasi

## 🏢 Tentang Aplikasi

KSP Lam Gabe Jaya adalah sistem manajemen koperasi simpan pinjam yang dikembangkan dengan arsitektur modern 3-tier untuk memberikan solusi yang handal, aman, dan mudah digunakan.

## 📋 Fitur Utama

### 🔐 Authentication & Authorization
- Multi-role user system (Admin, Manager, Staff, Member)
- Secure session management
- API-based authentication
- Password hashing with bcrypt

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

```
Username: admin
Password: password
Role: Administrator
```

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
- `members` - Data anggota koperasi
- `savings` - Data simpanan
- `loans` - Data pinjaman
- `transactions` - Data transaksi
- `reports` - Data laporan

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
- Password hashing dengan bcrypt
- SQL injection prevention dengan PDO
- XSS protection dengan output escaping
- CSRF protection dengan tokens
- Session security dengan HttpOnly cookies
- File upload validation

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

## 📄 Lisensi

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
