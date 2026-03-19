# KSP Lam Gabe Jaya v2.0 - Clean Architecture

## 🎯 Project Overview

KSP Lam Gabe Jaya v2.0 adalah sistem koperasi simpan pinjam yang dibangun dari nol dengan arsitektur yang bersih dan standar industri modern.

### ✅ Key Principles

1. **100% English Code** - Semua variable, function, class, property dalam bahasa Inggris
2. **Indonesian UI Only** - Hanya text yang ditampilkan ke user yang berbahasa Indonesia
3. **Clean Architecture** - Struktur folder yang terorganisir dengan baik
4. **Modern Standards** - Menggunakan best practices dan teknologi terkini
5. **Maintainable** - Kode yang mudah dipahami dan dikembangkan

---

## 📁 Project Structure

```
mono-v2/
├── index.html                     # Landing page (Indonesian UI)
├── login.html                     # Login page (Indonesian UI)
├── assets/
│   ├── css/
│   │   ├── main.css              # 100% English CSS
│   │   └── dashboard.css         # 100% English CSS
│   ├── js/
│   │   ├── main.js               # 100% English JavaScript
│   │   ├── auth.js               # 100% English JavaScript
│   │   └── config.js             # 100% English JavaScript
│   └── images/
│       └── ...                   # Application images
├── pages/
│   ├── admin/                    # Admin dashboard pages
│   ├── staff/                    # Staff dashboard pages
│   └── member/                   # Member dashboard pages
├── api/                          # REST API endpoints
├── config/                       # Configuration files
├── core/                         # Core application logic
├── utils/                        # Utility functions
├── database/                     # Database files and seeds
├── docs/                         # Documentation
└── scripts/                      # Automation scripts
```

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.0+
- MariaDB/MySQL 10.6+
- Web server (Apache/Nginx)
- Composer (optional)

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/82080038/mono-v2.git
cd mono-v2
```

2. **Database Setup**
```bash
# Import the database
mysql -u root -p < database/mono-v2.sql

# Or run the seed script
php database/complete_seed_data.php
```

3. **Configuration**
```bash
# Copy environment template
cp .env.example .env

# Edit database credentials
nano .env
```

4. **Web Server Configuration**
- Point document root to `/var/www/html/mono-v2`
- Ensure URL rewriting is enabled
- Configure PHP session settings

5. **Access the Application**
- Landing page: `http://localhost/mono-v2/`
- Login page: `http://localhost/mono-v2/login.html`

---

## 👥 User Roles & Access

### Role-Based Access Control (RBAC)

| Role | Dashboard Path | Key Features |
|------|----------------|--------------|
| **Super Admin** | `/pages/admin/dashboard.html` | System configuration, audit logs, backup/restore |
| **Admin** | `/pages/admin/dashboard.html` | User management, reports, analytics |
| **Mantri** | `/pages/staff/dashboard-mantri.html` | Field collections, route management |
| **Kasir** | `/pages/staff/dashboard-kasir.html` | Cash transactions, payments |
| **Teller** | `/pages/staff/dashboard-teller.html` | Customer service, deposits |
| **Surveyor** | `/pages/staff/dashboard-surveyor.html` | Field surveys, assessments |
| **Collector** | `/pages/staff/dashboard-collector.html` | Collections, follow-ups |
| **Member** | `/pages/member/dashboard.html` | Account management, loans |

### Quick Login (Development)
Use the quick login buttons on the login page for testing:
- Password: `password123`
- All roles available for instant access

---

## 🛠️ Features

### Core Features
- ✅ **Role-Based Dashboards** - Different interfaces per role
- ✅ **Dynamic Menu System** - JSON-based menu configuration
- ✅ **Real-time Authentication** - Secure session management
- ✅ **Responsive Design** - Mobile-friendly interface
- ✅ **Offline Support** - Field operations without internet
- ✅ **GPS Tracking** - Location-based services
- ✅ **Payment Integration** - QRIS, e-wallet support

### Admin Features
- User and role management
- System configuration
- Audit logging
- Backup and restore
- Comprehensive reports
- Analytics dashboard

### Staff Features
- Route optimization
- Offline synchronization
- Digital receipt printing
- Target tracking
- GPS logging
- Daily transaction recording

### Member Features
- Digital passbook
- Loan applications
- Transaction history
- Payment processing
- Rewards system

---

## 📊 Database Schema

### Core Tables
- `users` - User accounts and authentication
- `roles` - Role definitions and permissions
- `members` - Member information
- `loans` - Loan records and status
- `cooperatives` - Cooperative data
- `login_attempts` - Security logging
- `migrations` - Database version control

### Relationships
- Users → Roles (Many-to-One)
- Members → Loans (One-to-Many)
- Loans → Payments (One-to-Many)

---

## 🔧 Configuration

### Environment Variables (.env)
```env
# Database
DB_HOST=localhost
DB_NAME=ksp_lamgabejaya_v2
DB_USER=root
DB_PASS=root
DB_CHARSET=utf8mb4

# Application
BASE_PATH=/mono-v2
DEBUG_MODE=false
SESSION_TIMEOUT=3600

# Security
ENCRYPTION_KEY=your_secret_key
JWT_SECRET=your_jwt_secret
```

### Menu Configuration
Dynamic menus are configured in `assets/config/menus.json`:
```json
{
  "admin": [
    {
      "key": "dashboard",
      "title": "Dashboard",
      "icon": "fas fa-tachometer-alt",
      "url": "dashboard.html"
    }
  ]
}
```

---

## 🚀 Development

### Code Standards
1. **English Variables Only** - All code in English
2. **Indonesian UI Only** - User-facing text in Indonesian
3. **Clean Architecture** - Separation of concerns
4. **Modern PHP** - Use latest features and best practices
5. **Responsive Design** - Mobile-first approach

### File Naming
- **Controllers**: `PascalCase` (e.g., `UserController.php`)
- **Models**: `PascalCase` (e.g., `UserModel.php`)
- **Views**: `kebab-case` (e.g., `user-dashboard.html`)
- **JavaScript**: `kebab-case` (e.g., `user-auth.js`)
- **CSS**: `kebab-case` (e.g., `user-styles.css`)

### API Standards
- RESTful endpoints
- JSON responses
- Proper HTTP status codes
- Error handling and logging
- Input validation and sanitization

---

## 📱 Mobile Support

### Responsive Features
- Touch-friendly navigation
- Mobile-optimized forms
- Offline data synchronization
- GPS integration
- Camera support for documents

### PWA Features (Planned)
- Service workers for offline mode
- Push notifications
- App-like experience
- Home screen installation

---

## 🔒 Security

### Implemented Measures
- Password hashing with bcrypt
- Session management
- CSRF protection
- SQL injection prevention
- XSS protection
- Input validation
- Rate limiting
- Audit logging

### Security Headers
- Content Security Policy
- X-Frame-Options
- X-Content-Type-Options
- Strict-Transport-Security

---

## 📈 Performance

### Optimization Techniques
- Lazy loading
- Image optimization
- CSS/JS minification
- Database indexing
- Caching strategies
- CDN integration (planned)

### Monitoring
- Page load tracking
- Error logging
- Performance metrics
- User analytics

---

## 🔄 Backup & Recovery

### Automated Backups
- Database exports
- File system backups
- Configuration backups
- Off-site storage (planned)

### Recovery Procedures
- Database restoration
- File recovery
- Configuration rollback
- Disaster recovery plan

---

## 🧪 Testing

### Test Coverage
- Unit tests (planned)
- Integration tests (planned)
- End-to-end tests (planned)
- Performance tests (planned)

### Quality Assurance
- Code review process
- Automated testing
- Manual testing procedures
- User acceptance testing

---

## 📚 Documentation

### Available Docs
- [API Documentation](docs/API_Documentation.md)
- [Technical Guide](docs/technical/PROGRAMMER_GUIDE.md)
- [User Manual](docs/user-guides/USER_MANUAL.md)
- [Deployment Guide](docs/technical/PRODUCTION_DEPLOYMENT_GUIDE.md)

### Code Comments
- PHPDoc standards
- Inline documentation
- API endpoint documentation
- Database schema documentation

---

## 🚀 Deployment

### Production Setup
1. **Server Requirements**
   - PHP 8.0+
   - MariaDB 10.6+
   - SSL certificate
   - Domain configuration

2. **Environment Setup**
   - Production .env file
   - Database configuration
   - File permissions
   - Security headers

3. **Performance Optimization**
   - Enable OPcache
   - Configure caching
   - Optimize database
   - Enable compression

### Docker Support (Planned)
- Dockerfile configuration
- Docker Compose setup
- Container orchestration
- CI/CD pipeline

---

## 🤝 Contributing

### Development Workflow
1. Fork the repository
2. Create feature branch
3. Make changes following standards
4. Test thoroughly
5. Submit pull request
6. Code review process

### Code Review Checklist
- Follows coding standards
- Proper documentation
- Security considerations
- Performance impact
- Test coverage

---

## 📞 Support

### Getting Help
- Documentation: Check `/docs` folder
- Issues: Use GitHub Issues
- Discussions: GitHub Discussions
- Email: support@lamabejaya.coop

### Common Issues
- Database connection errors
- Permission problems
- Session issues
- File upload problems

---

## 📄 License

This project is proprietary software for KSP Lam Gabe Jaya.
© 2026 KSP Lam Gabe Jaya. All rights reserved.

---

## 🔄 Version History

### v2.0.0 (Current)
- Complete rewrite with clean architecture
- Role-based dashboard system
- Dynamic menu configuration
- Mobile-responsive design
- Enhanced security features
- Offline support for field operations

### v1.x (Legacy)
- Basic web application
- Limited role support
- Desktop-only interface

---

## 🎯 Future Roadmap

### Phase 1 (Q2 2026)
- Mobile app development
- Enhanced analytics
- API v2 implementation
- Performance optimization

### Phase 2 (Q3 2026)
- AI-powered credit scoring
- Advanced reporting
- Integration with banking systems
- Multi-language support

### Phase 3 (Q4 2026)
- Cloud deployment
- Microservices architecture
- Real-time notifications
- Advanced security features

---

## 📊 Project Statistics

- **Lines of Code**: ~15,000
- **Database Tables**: 7
- **API Endpoints**: 25+
- **User Roles**: 8
- **Features**: 40+
- **Documents**: 10+

---

*Last updated: March 19, 2026*