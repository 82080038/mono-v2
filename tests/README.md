---
description: Testing Suite for KSP Lam Gabe Jaya v2.0

This directory contains comprehensive testing scripts for the KSP Lam Gabe Jaya web application.

---

## 🧪 Available Testing Scripts

### 📋 `test_comprehensive.sh`
**Comprehensive Front-end to Back-end Testing Suite**
- **Total Tests**: 71 tests
- **Coverage**: All application components
- **Purpose**: Complete system validation

**Usage:**
```bash
./test_comprehensive.sh
```

**Test Categories:**
- Server connectivity tests
- Front-end content validation
- Responsive design tests
- JavaScript functionality
- API endpoint testing
- Mobile responsiveness
- Navigation tests
- User menu functionality
- Role-based menu tests
- Page loading performance
- Error handling
- Browser compatibility
- File system validation
- Database connectivity
- Performance tests
- End-to-end workflows
- Mobile workflows

---

### 🔄 `test_end_to_end.sh`
**End-to-End Testing with Real API Calls**
- **Total Tests**: 29 tests
- **Coverage**: Critical user workflows
- **Purpose**: Production readiness validation

**Usage:**
```bash
./test_end_to_end.sh
```

**Test Categories:**
- API endpoint testing
- Front-end to back-end integration
- User workflow testing
- Dashboard access testing
- Responsive design testing
- JavaScript functionality testing
- Performance testing
- Error handling testing
- Mobile compatibility
- Database integration

---

### 🗄️ `setup_database.sh`
**Database Setup and Seeding**
- **Purpose**: Create testing database and sample data
- **Usage**: One-time setup for testing environment

**Usage:**
```bash
./setup_database.sh
```

**Features:**
- Database creation
- Users table creation
- Sample user accounts
- Testing credentials setup

---

## 🚀 Quick Start

### 1. Initial Setup (One-time)
```bash
# Create database and sample data
./setup_database.sh
```

### 2. Run Comprehensive Tests
```bash
# Run all 71 tests
./test_comprehensive.sh
```

### 3. Run End-to-End Tests
```bash
# Run critical workflows
./test_end_to_end.sh
```

---

## 📊 Test Results Interpretation

### ✅ **Success Criteria**
- **All tests passed**: Application ready for production
- **Success rate > 90%**: Generally acceptable for deployment
- **Success rate < 90%**: Review failed tests before deployment

### 🔍 **Failed Tests Analysis**
- **HTTP errors**: Check server configuration
- **Database errors**: Verify database connection
- **JavaScript errors**: Check browser console
- **Content missing**: Verify file paths and content

---

## 🔧 Testing Credentials

### **👤 Default Test Users**
- **Admin**: `Admin User / password`
- **Staff**: `test_mantri@lamabejaya.coop / password`
- **Member**: `test_member@lamabejaya.coop / password`

### **🗄️ Database Configuration**
- **Host**: `localhost`
- **Database**: `ksp_lamgabejaya_v2`
- **Socket**: `/opt/lampp/var/mysql/mysql.sock`

---

## 📱 Test Environment Requirements

### **🌐 Web Server**
- Apache/Nginx running
- PHP 8.2+ installed
- MySQL/MariaDB accessible

### **🔧 Dependencies**
- curl (for HTTP requests)
- jq (for JSON parsing)
- bc (for calculations)
- MySQL client (for database operations)

### **📁 File Permissions**
- Scripts must be executable: `chmod +x *.sh`
- Database access permissions
- Log file write permissions

---

## 🐛 Troubleshooting

### **Common Issues**

#### **Database Connection Failed**
```bash
# Check MySQL status
sudo systemctl status mysql

# Check database exists
mysql -u root -p -e "SHOW DATABASES;"

# Run database setup
./setup_database.sh
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

#### **Permission Denied**
```bash
# Make scripts executable
chmod +x *.sh

# Check file permissions
ls -la *.sh
```

---

## 📝 Test Reports

### **📊 Generating Reports**
Test scripts automatically generate detailed reports including:
- Test execution summary
- Pass/fail counts
- Success rate percentage
- Detailed error messages
- Performance metrics

### **📄 Report Storage**
Test results are displayed in terminal and can be redirected:
```bash
# Save to file
./test_comprehensive.sh > test_report.txt

# Save with timestamp
./test_comprehensive.sh | tee "test_$(date +%Y%m%d_%H%M%S).txt"
```

---

## 🔄 Continuous Testing

### **📅 Schedule Regular Tests**
```bash
# Add to crontab for daily testing
0 9 * * * /opt/lampp/htdocs/mono-v2/test_end_to_end.sh

# Weekly comprehensive testing
0 1 * * 1 /opt/lampp/htdocs/mono-v2/test_comprehensive.sh
```

### **🚀 Pre-deployment Checklist**
- Run comprehensive tests: `./test_comprehensive.sh`
- Run end-to-end tests: `./test_end_to_end.sh`
- Verify 100% success rate
- Check performance metrics
- Validate error handling

---

## 📚 Testing Documentation

### **📖 Test Coverage**
- **Front-end**: HTML structure, CSS styling, JavaScript functionality
- **Back-end**: API endpoints, database operations, authentication
- **Integration**: User workflows, data flow, error handling
- **Performance**: Load times, concurrent connections, mobile compatibility

### **🎯 Test Scenarios**
- **Happy Path**: Normal user workflows
- **Error Cases**: Invalid inputs, missing data, system errors
- **Edge Cases**: Boundary conditions, unusual inputs
- **Security**: Authentication, authorization, input validation

---

## 📞 Support

### **🐛 Bug Reporting**
If tests fail consistently:
1. Check error logs: `tail -f /opt/lampp/logs/error_log`
2. Run individual test components
3. Verify database connectivity
4. Check file permissions
5. Validate configuration files

### **📞 Getting Help**
- Review this documentation
- Check test script comments
- Examine error messages
- Verify system requirements

---

**Last Updated**: 2026-03-19
**Version**: 1.0.0
**Application**: KSP Lam Gabe Jaya v2.0
