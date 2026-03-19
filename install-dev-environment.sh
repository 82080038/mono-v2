#!/bin/bash

# Development Environment Setup Script
# This script sets up the development environment for KSP Lam Gabe Jaya v2.0

echo "🚀 Setting up KSP Lam Gabe Jaya Development Environment..."

# Check if we're in the right directory
if [ ! -f "index.html" ]; then
    echo "❌ Error: Please run this script from the project root directory"
    exit 1
fi

# Create necessary directories
echo "📁 Creating directories..."
mkdir -p logs
mkdir -p uploads/avatars
mkdir -p uploads/documents
mkdir -p backups
mkdir -p api/keys

# Set permissions
echo "🔐 Setting permissions..."
chmod 755 logs uploads backups
chmod 755 uploads/avatars uploads/documents api/keys
chmod 664 .env.example

# Copy configuration files if they don't exist
if [ ! -f ".env" ]; then
    echo "⚙️ Creating .env from template..."
    cp .env.example .env
    echo "📝 Please update .env with your database credentials"
fi

if [ ! -f "api/config.php" ]; then
    echo "⚙️ Creating API config from template..."
    cp api/config.example.php api/config.php
fi

if [ ! -f "config/local.php" ]; then
    echo "⚙️ Creating local config from template..."
    cp config/local.example.php config/local.php
fi

# Check PHP installation
echo "🔍 Checking PHP installation..."
if command -v php &> /dev/null; then
    echo "✅ PHP found: $(php --version | head -n1)"
else
    echo "❌ PHP not found. Please install PHP 8.0 or higher"
    exit 1
fi

# Check required PHP extensions
echo "🔍 Checking PHP extensions..."
required_extensions=("pdo" "pdo_mysql" "mbstring" "json" "curl")
for ext in "${required_extensions[@]}"; do
    if php -m | grep -q "^$ext$"; then
        echo "✅ $ext extension available"
    else
        echo "❌ $ext extension missing"
    fi
done

# Database setup prompt
echo ""
echo "🗄️ Database Setup:"
echo "1. Create database 'ksp_lamgabejaya_v2' in MySQL/MariaDB"
echo "2. Import database/phase1_schema.sql"
echo "3. Update .env with your database credentials"
echo ""

# Test database connection if .env exists
if [ -f ".env" ]; then
    echo "🔗 Testing database connection..."
    php -r "
    require_once 'config/Config.php';
    try {
        \$config = Config::getInstance();
        \$db = \$config->getDatabase();
        \$pdo = new PDO(\"mysql:host={\$db['host']};dbname={\$db['name']};charset=utf8mb4\", \$db['user'], \$db['password']);
        echo '✅ Database connection successful\n';
    } catch (Exception \$e) {
        echo '❌ Database connection failed: ' . \$e->getMessage() . '\n';
    }
    " 2>/dev/null || echo "⚠️ Could not test database connection"
fi

echo ""
echo "🎉 Development environment setup complete!"
echo ""
echo "📋 Next steps:"
echo "1. Update .env with your database credentials"
echo "2. Import database/phase1_schema.sql into your MySQL database"
echo "3. Visit info.php in your browser to verify setup"
echo "4. Visit test.html to test API functionality"
echo ""
echo "🌐 Access points:"
echo "- Main app: http://localhost/mono-v2/"
echo "- Development info: http://localhost/mono-v2/info.php"
echo "- Test page: http://localhost/mono-v2/test.html"
echo "- Login: http://localhost/mono-v2/login.html"
echo ""
echo "📚 Documentation:"
echo "- README.md for project overview"
echo "- saran_checklist.md for development progress"
echo "- TESTING.md for testing guidelines"
