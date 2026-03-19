#!/bin/bash

# KSP Lam Gabe Jaya v2.0 - Database Setup Script
# Create database and tables for testing

echo "🗄️  DATABASE SETUP FOR TESTING"
echo "=================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Database configuration
DB_HOST="localhost"
DB_NAME="ksp_lamgabejaya_v2"
DB_USER="root"
DB_PASS="root"
DB_SOCKET="/opt/lampp/var/mysql/mysql.sock"

echo "📋 Database Configuration:"
echo "Host: $DB_HOST"
echo "Database: $DB_NAME"
echo "User: $DB_USER"
echo "Socket: $DB_SOCKET"
echo ""

# Function to execute MySQL command
execute_mysql() {
    mysql --host="$DB_HOST" --user="$DB_USER" --password="$DB_PASS" --socket="$DB_SOCKET" -e "$1" 2>/dev/null
}

# Check if MySQL is running
echo "🔍 Checking MySQL connection..."
if execute_mysql "SELECT 1;" >/dev/null 2>&1; then
    echo -e "${GREEN}✅ MySQL connection successful${NC}"
else
    echo -e "${RED}❌ MySQL connection failed${NC}"
    echo "Please ensure MySQL is running and credentials are correct."
    exit 1
fi

# Create database if not exists
echo "📝 Creating database..."
execute_mysql "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Database '$DB_NAME' created/exists${NC}"
else
    echo -e "${RED}❌ Failed to create database${NC}"
    exit 1
fi

# Use the database
echo "📋 Creating tables..."

# Create users table
execute_mysql "USE $DB_NAME;
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'staff', 'member') NOT NULL DEFAULT 'member',
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
);" 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Users table created${NC}"
else
    echo -e "${RED}❌ Failed to create users table${NC}"
fi

# Create sample admin user for testing
echo "👤 Creating sample admin user..."
execute_mysql "USE $DB_NAME;
INSERT INTO users (uuid, username, email, password_hash, full_name, role, status) 
VALUES ('admin-001', 'admin', 'admin@ksp.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', 'active')
ON DUPLICATE KEY UPDATE password_hash = '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';" 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Sample admin user created${NC}"
    echo "   Username: admin"
    echo "   Email: admin@ksp.com"
    echo "   Password: password"
else
    echo -e "${RED}❌ Failed to create admin user${NC}"
fi

# Create sample staff user for testing
execute_mysql "USE $DB_NAME;
INSERT INTO users (uuid, username, email, password_hash, full_name, role, status) 
VALUES ('staff-001', 'mantri', 'mantri@ksp.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mantri KSP', 'staff', 'active')
ON DUPLICATE KEY UPDATE password_hash = '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';" 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Sample staff user created${NC}"
    echo "   Username: mantri"
    echo "   Email: mantri@ksp.com"
    echo "   Password: password"
else
    echo -e "${RED}❌ Failed to create staff user${NC}"
fi

# Create sample member user for testing
execute_mysql "USE $DB_NAME;
INSERT INTO users (uuid, username, email, password_hash, full_name, role, status) 
VALUES ('member-001', 'member', 'member@ksp.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Member KSP', 'member', 'active')
ON DUPLICATE KEY UPDATE password_hash = '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';" 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Sample member user created${NC}"
    echo "   Username: member"
    echo "   Email: member@ksp.com"
    echo "   Password: password"
else
    echo -e "${RED}❌ Failed to create member user${NC}"
fi

echo ""
echo "📊 Database Summary:"
execute_mysql "USE $DB_NAME; SELECT COUNT(*) as total_users FROM users;" 2>/dev/null

echo ""
echo "🎯 Testing Credentials:"
echo "Admin: admin / password"
echo "Staff: mantri / password"
echo "Member: member / password"

echo ""
echo -e "${GREEN}✅ Database setup completed successfully!${NC}"
echo "You can now test the login functionality."
