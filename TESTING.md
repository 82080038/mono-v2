# KSP Lam Gabe Jaya v2.0 - Testing Suite

🧪 **Complete Testing Framework for KSP Lam Gabe Jaya Web Application**

## 📁 **Directory Structure**

```
tests/
├── README.md                    # Complete documentation
├── install.sh                   # Installation script
├── test_runner.sh               # Main test launcher
├── test_maintenance.sh          # Maintenance utilities
├── automated_test.sh            # Automated testing
├── quick_test.sh                # Quick health check (7 tests)
├── test_comprehensive.sh        # Comprehensive testing (71 tests)
├── test_end_to_end.sh           # End-to-end testing (29 tests)
├── setup_database.sh            # Database setup and seeding
├── logs/                        # Test logs
├── reports/                     # Test reports
└── backups/                     # Test result backups
```

## 🚀 **Quick Start**

### **1. Installation (One-time)**
```bash
# Install and configure testing environment
sudo ./tests/install.sh
```

### **2. Run Tests**
```bash
# Show test menu
./tests/test_runner.sh

# Quick test (7 tests)
./tests/test_runner.sh 1

# Comprehensive test (71 tests)
./tests/test_runner.sh 2

# End-to-end test (29 tests)
./tests/test_runner.sh 3

# Run all tests
./tests/test_runner.sh 5
```

### **3. Maintenance**
```bash
# Show maintenance menu
./tests/test_maintenance.sh

# Update test credentials
./tests/test_maintenance.sh 1

# Reset database
./tests/test_maintenance.sh 2

# Check dependencies
./tests/test_maintenance.sh 3
```

## 📊 **Test Coverage**

### **🔍 Quick Test (7 tests)**
- Landing page accessibility
- Login page accessibility
- Dashboard accessibility (admin, staff, member)
- API endpoint availability
- Authentication functionality

### **🔧 Comprehensive Test (71 tests)**
- Server connectivity
- Front-end content validation
- Responsive design
- JavaScript functionality
- API endpoint testing
- Mobile responsiveness
- Navigation testing
- User menu functionality
- Role-based menu testing
- Performance testing
- Error handling
- Browser compatibility
- File system validation
- Database connectivity

### **🔄 End-to-End Test (29 tests)**
- API endpoint testing
- Front-end to back-end integration
- User workflow testing
- Dashboard access testing
- Mobile compatibility
- Database integration

## 🔑 **Testing Credentials**

### **👤 Default Test Users**
- **Admin**: `Admin User / password`
- **Staff**: `test_mantri@lamabejaya.coop / password`
- **Member**: `test_member@lamabejaya.coop / password`

### **🗄️ Database Configuration**
- **Host**: `localhost`
- **Database**: `ksp_lamgabejaya_v2`
- **Socket**: `/opt/lampp/var/mysql/mysql.sock`

## 📚 **Documentation**

### **📖 Complete Guide**
See `tests/README.md` for comprehensive documentation including:
- Detailed test descriptions
- Troubleshooting guide
- Configuration options
- Advanced usage examples

### **🔧 Script Descriptions**

#### **📋 `test_runner.sh`**
Main test launcher with menu interface
- Interactive menu selection
- Individual test execution
- Batch test execution
- Help and documentation

#### **🔧 `test_maintenance.sh`**
Maintenance and utilities
- Update test credentials
- Reset database
- Check dependencies
- Clean logs
- Backup results
- Generate reports

#### **🤖 `automated_test.sh`**
Automated testing for CI/CD
- Scheduled testing
- Automatic logging
- Report generation
- Backup management
- Notification system

#### **📱 `quick_test.sh`**
Fast health check
- 7 essential tests
- Quick validation
- Real-time results
- Performance check

#### **🔬 `test_comprehensive.sh`**
Complete system validation
- 71 comprehensive tests
- Full coverage
- Detailed reporting
- Performance metrics

#### **🔄 `test_end_to_end.sh`**
Critical workflow testing
- 29 end-to-end tests
- Real API calls
- User workflows
- Integration testing

#### **🗄️ `setup_database.sh`**
Database setup and seeding
- Database creation
- Table creation
- Sample data insertion
- Test user setup

## 📈 **Test Results**

### **✅ Success Criteria**
- **100% Success Rate**: Application ready for production
- **>90% Success Rate**: Generally acceptable
- **<90% Success Rate**: Review required

### **📊 Performance Metrics**
- Page load times (< 2 seconds)
- API response times (< 1 second)
- Concurrent connections handling
- Mobile compatibility

### **🔍 Error Analysis**
- HTTP status codes
- Database connection errors
- JavaScript console errors
- Missing file errors

## 🚨 **Troubleshooting**

### **Common Issues**

#### **Database Connection Failed**
```bash
# Check MySQL status
sudo systemctl status mysql

# Check database exists
mysql -u root -p -e "SHOW DATABASES;"

# Run database setup
./tests/setup_database.sh
```

#### **HTTP 500 Errors**
```bash
# Check Apache error logs
tail -f /opt/lampp/logs/error_log

# Check PHP syntax
php -l /path/to/file.php
```

#### **Missing Dependencies**
```bash
# Install required tools
sudo apt-get install curl jq bc mysql-client
```

#### **Permission Issues**
```bash
# Make scripts executable
chmod +x tests/*.sh

# Check file permissions
ls -la tests/*.sh
```

## 📅 **Scheduled Testing**

### **🕐 Cron Jobs**
```bash
# Daily quick test (9:00 AM)
0 9 * * * /opt/lampp/htdocs/mono-v2/tests/quick_test.sh

# Weekly comprehensive test (Monday 1:00 AM)
0 1 * * 1 /opt/lampp/htdocs/mono-v2/tests/test_comprehensive.sh
```

### **🤖 Automation**
```bash
# Run all tests automatically
./tests/automated_test.sh all

# Generate daily report
./tests/automated_test.sh report

# Backup results
./tests/automated_test.sh backup
```

## 📞 **Support**

### **🐛 Bug Reporting**
1. Check error logs: `tail -f /opt/lampp/logs/error_log`
2. Run individual test components
3. Verify database connectivity
4. Check file permissions
5. Validate configuration files

### **📚 Resources**
- **Documentation**: `tests/README.md`
- **Installation**: `tests/install.sh`
- **Help**: `./tests/test_runner.sh help`
- **Maintenance**: `./tests/test_maintenance.sh`

## 🎯 **Best Practices**

### **🔧 Before Deployment**
1. Run comprehensive tests: `./tests/test_comprehensive.sh`
2. Run end-to-end tests: `./tests/test_end_to_end.sh`
3. Verify 100% success rate
4. Check performance metrics
5. Validate error handling

### **📅 Regular Maintenance**
1. Update test credentials weekly
2. Clean old logs monthly
3. Backup test results quarterly
4. Review test reports annually

### **🚀 Continuous Integration**
1. Integrate with CI/CD pipeline
2. Run automated tests on commits
3. Generate test reports
4. Monitor performance trends

---

**🎉 Testing Suite Successfully Installed and Configured!**

**Last Updated**: 2026-03-19  
**Version**: 1.0.0  
**Application**: KSP Lam Gabe Jaya v2.0
