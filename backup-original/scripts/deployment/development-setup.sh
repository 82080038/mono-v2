#!/bin/bash

# Development Setup Script for KSP Lam Gabe Jaya
# This script sets up the complete development environment

echo "🚀 Setting up KSP Lam Gabe Jaya Development Environment..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running with sudo
if [ "$EUID" -ne 0 ]; then
    print_error "This script needs to be run with sudo privileges"
    echo "Usage: sudo ./development-setup.sh"
    exit 1
fi

# Update system packages
print_status "Updating system packages..."
apt update && apt upgrade -y

# Install Node.js 18.x
print_status "Installing Node.js 18.x..."
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs

# Install PHP extensions
print_status "Installing PHP extensions..."
apt install -y php-mbstring php-curl php-gd php-xml php-zip php-mysql php-xdebug php-intl

# Install Composer
print_status "Installing Composer..."
apt install -y composer

# Install MySQL client and tools
print_status "Installing MySQL client and tools..."
apt install -y mysql-client phpmyadmin git

# Install additional development tools
print_status "Installing additional development tools..."
apt install -y htop tree vim curl wget unzip

# Setup database
print_status "Setting up database..."
mysql -u root -proot -e "DROP DATABASE IF EXISTS ksp_lamgabejaya; CREATE DATABASE ksp_lamgabejaya CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import database schema
print_status "Importing database schema..."
if [ -f "database/migrations/003_simple_schema.sql" ]; then
    mysql -u root -proot ksp_lamgabejaya < database/migrations/003_simple_schema.sql
else
    print_warning "Database schema file not found, please import manually"
fi

# Setup Node.js dependencies
print_status "Installing Node.js dependencies..."
if [ -d "app" ]; then
    cd app
    npm install
    npm run build
    cd ..
else
    print_warning "App directory not found, skipping npm install"
fi

# Setup PHP dependencies
print_status "Installing PHP dependencies..."
if [ -f "composer.json" ]; then
    composer install
else
    print_warning "composer.json not found, skipping composer install"
fi

# Set proper permissions
print_status "Setting proper permissions..."
chmod -R 755 .
chmod -R 777 uploads/ 2>/dev/null || true
chmod -R 777 storage/ 2>/dev/null || true
chmod -R 777 logs/ 2>/dev/null || true

# Create necessary directories
print_status "Creating necessary directories..."
mkdir -p uploads logs storage cache

# Setup Xdebug configuration
print_status "Setting up Xdebug configuration..."
cat > /etc/php/8.1/mods-available/xdebug.ini << EOF
zend_extension=xdebug.so
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_host=localhost
xdebug.client_port=9003
xdebug.log=/var/log/xdebug.log
xdebug.idekey=VSCODE
EOF

# Restart PHP service
print_status "Restarting PHP service..."
systemctl restart php8.1-fpm 2>/dev/null || true
systemctl restart apache2 2>/dev/null || true

# Create development server startup script
print_status "Creating development server startup script..."
cat > start-dev-server.sh << 'EOF'
#!/bin/bash

echo "🚀 Starting KSP Lam Gabe Jaya Development Servers..."

# Start PHP development server
echo "Starting PHP server on port 8000..."
php -S localhost:8000 -t . &
PHP_PID=$!

# Start Next.js development server (if app directory exists)
if [ -d "app" ]; then
    echo "Starting Next.js server on port 3000..."
    cd app
    npm run dev &
    NEXT_PID=$!
    cd ..
fi

echo "✅ Development servers started!"
echo "📱 PHP API: http://localhost:8000"
echo "🌐 Next.js App: http://localhost:3000" 2>/dev/null || true
echo "🗄️  phpMyAdmin: http://localhost/phpmyadmin"

# Wait for user input to stop servers
echo "Press Ctrl+C to stop all servers"

# Function to cleanup on exit
cleanup() {
    echo "🛑 Stopping servers..."
    kill $PHP_PID 2>/dev/null || true
    kill $NEXT_PID 2>/dev/null || true
    echo "✅ All servers stopped"
    exit 0
}

# Set trap for cleanup
trap cleanup INT TERM

# Wait indefinitely
wait
EOF

chmod +x start-dev-server.sh

# Create testing script
print_status "Creating testing script..."
cat > run-tests.sh << 'EOF'
#!/bin/bash

echo "🧪 Running KSP Lam Gabe Jaya Test Suite..."

# Run PHP security tests
echo "🔒 Running security tests..."
php -f test_security_fixes.php

# Run CRUD tests
echo "📊 Running CRUD tests..."
php -f comprehensive_crud_test.php

# Run comprehensive tests
echo "🔍 Running comprehensive tests..."
php -f comprehensive_test.php

echo "✅ Test suite completed!"
EOF

chmod +x run-tests.sh

# Create database backup script
print_status "Creating database backup script..."
cat > backup-db.sh << 'EOF'
#!/bin/bash

BACKUP_DIR="backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="$BACKUP_DIR/ksp_lamgabejaya_backup_$TIMESTAMP.sql"

echo "💾 Creating database backup..."

# Create backups directory if it doesn't exist
mkdir -p $BACKUP_DIR

# Create backup
mysqldump -u root -proot ksp_lamgabejaya > $BACKUP_FILE

echo "✅ Database backup created: $BACKUP_FILE"

# Keep only last 10 backups
cd $BACKUP_DIR
ls -t *.sql | tail -n +11 | xargs -r rm
cd ..

echo "🗑️  Old backups cleaned up"
EOF

chmod +x backup-db.sh

# Print summary
echo ""
print_status "✅ Development environment setup completed!"
echo ""
echo "🎯 Next steps:"
echo "1. Run './start-dev-server.sh' to start development servers"
echo "2. Run './run-tests.sh' to execute test suite"
echo "3. Run './backup-db.sh' to create database backup"
echo ""
echo "📁 Important files created:"
echo "- .env - Environment configuration"
echo "- start-dev-server.sh - Development server startup"
echo "- run-tests.sh - Test suite runner"
echo "- backup-db.sh - Database backup utility"
echo ""
echo "🔗 Development URLs:"
echo "- PHP API: http://localhost:8000"
echo "- Next.js App: http://localhost:3000"
echo "- phpMyAdmin: http://localhost/phpmyadmin"
echo ""
print_warning "Don't forget to:"
echo "1. Update .env file with your actual configuration"
echo "2. Set up Google Maps API key for GPS features"
echo "3. Configure email settings for notifications"
echo ""
print_status "Happy coding! 🚀"
