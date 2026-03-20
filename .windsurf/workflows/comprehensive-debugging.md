---
description: Comprehensive debugging workflow with internet assistance for web applications
---

# Comprehensive Debugging Workflow

## Overview
This workflow provides a systematic approach to debugging web applications using internet research and best practices.

## When to Use
- When encountering persistent errors
- Before deploying to production
- When new features are added
- When performance issues arise

## Steps

### 1. Initial Assessment
```bash
# Check application status
echo "=== APPLICATION STATUS CHECK ==="
curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/login.html"
curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/pages/admin/dashboard.html"
```

### 2. PHP Configuration Check
```bash
# Create and run PHP error check
echo '<?php
echo "Display Errors: " . (ini_get("display_errors") ? "ON" : "OFF") . "\n";
echo "Error Reporting: " . ini_get("error_reporting") . "\n";
echo "Log Errors: " . (ini_get("log_errors") ? "ON" : "OFF") . "\n";
echo "PHP Version: " . phpversion() . "\n";
 ?>' > /tmp/php_check.php
php /tmp/php_check.php
```

### 3. Syntax Validation
```bash
# Check all PHP files
find api -name "*.php" -exec php -l {} \;

# Check JavaScript files (NOT HTML files)
find assets/js -name "*.js" -exec node -c {} \;

# For JavaScript in HTML, extract first:
sed -n '/<script>/,/<\/script>/p' file.html | sed '1d;$d' | sed '/^<script src/d' > extracted.js
node -c extracted.js
```

### 4. Database Connection Test
```bash
# Create database test
echo '<?php
try {
    $conn = new PDO("mysql:host=localhost;dbname=mono_v2", "root", "");
    echo "✅ Database connection successful\n";
} catch(PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
 ?>' > /tmp/db_test.php
php /tmp/db_test.php

# Check MySQL service
ps aux | grep mysql | grep -v grep
ls -la /opt/lampp/var/mysql/mysql.sock
```

### 5. API Endpoint Testing
```bash
# Test critical endpoints
curl -s -X POST "http://localhost/mono-v2/api/auth_simple.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "username=admin&password=password"

# Test all endpoints
for endpoint in auth.php auth_simple.php members.php loans.php; do
    echo "Testing $endpoint:"
    curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/api/$endpoint"
done
```

### 6. Security Headers Check
```bash
# Verify CORS and security headers
curl -s -I "http://localhost/mono-v2/api/auth_simple.php" | grep -E "(Access-Control|X-)"
```

### 7. Session Management Test
```bash
# Test session functionality
echo '<?php
session_start();
echo "Session ID: " . session_id() . "\n";
echo "Session support: " . (session_status() === PHP_SESSION_ACTIVE ? "ACTIVE" : "INACTIVE") . "\n";
$_SESSION["test"] = "value";
echo "Session write test: " . (isset($_SESSION["test"]) ? "SUCCESS" : "FAILED") . "\n";
 ?>' > /tmp/session_test.php
php /tmp/session_test.php
```

### 8. Security Vulnerability Scan
```bash
# Check for hardcoded passwords
grep -r "password=" . --include="*.php" --include="*.js"

# Check for SQL injection risks
grep -r "\$_GET|\$_POST" api/ --include="*.php"

# Check file permissions
ls -la api/auth.php assets/js/auth-fixed.js
```

### 9. Asset Verification
```bash
# Check CSS files
for css_file in assets/css/dashboard-layout.css assets/css/dashboard.css assets/css/main.css; do
    echo "Checking $css_file:"
    [ -f "$css_file" ] && echo "✅ File exists" || echo "❌ File missing"
done
```

### 10. Full Application Test
```bash
# Test complete user flow
echo "=== COMPLETE APPLICATION TEST ==="
echo "1. Login page: $(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/login.html")"
echo "2. Dashboard: $(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/pages/admin/dashboard.html")"
echo "3. CSS files: $(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mono-v2/assets/css/dashboard-layout.css")"
```

## Internet Research Integration

### When to Search Internet
1. **Tool Limitations**: When tools give unexpected results
2. **Persistent Issues**: When problems keep recurring
3. **Best Practices**: For standard debugging approaches
4. **Security Concerns**: For latest security practices

### Search Queries to Use
- "PHP error debugging checklist"
- "JavaScript syntax error in HTML file"
- "MySQL connection troubleshooting XAMPP"
- "API endpoint testing best practices"
- "Web application security checklist"

## Common False Positives
- `node -c` on HTML files (cannot parse HTML)
- HTTP 400 on API endpoints (needs proper request format)
- Syntax errors in minified code

## Critical Insights
- Always extract JavaScript from HTML before syntax checking
- Browser console is more reliable than command-line tools for web JavaScript
- Test APIs with curl before implementing in frontend
- Security headers should be verified in production

## Final Report Template
```
=== COMPREHENSIVE DEBUGGING REPORT ===

✅ WORKING COMPONENTS:
- PHP Configuration
- Database Connection  
- Authentication System
- Frontend Assets
- Security Headers

⚠️ PARTIAL WORKING:
- API Endpoints (need proper requests)

🔴 ISSUES FOUND:
- Security vulnerabilities
- Missing configurations

📊 STATUS: XX% Functional
```

## Automation Script
Create a comprehensive debug script that runs all checks automatically and generates a report.

## Notes
- This workflow should be used before any major deployment
- Results should be documented for future reference
- Internet research should be integrated for complex issues
- Always verify results in browser, not just command-line tools
