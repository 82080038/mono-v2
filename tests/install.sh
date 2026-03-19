#!/bin/bash

# KSP Lam Gabe Jaya v2.0 - Test Suite Installation
# Install and configure testing environment

echo "🧪 KSP LAM GABE JAYA v2.0 - TEST SUITE INSTALLATION"
echo "======================================"
echo "📅 $(date)"
echo "🌐 http://localhost/mono-v2"
echo "======================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Function to check if running as root
check_root() {
    if [ "$EUID" -ne 0 ]; then
        echo -e "${RED}❌ This script must be run as root or with sudo${NC}"
        exit 1
    fi
}

# Function to install dependencies
install_dependencies() {
    echo "📦 Installing Dependencies..."
    echo "======================"
    
    # Update package list
    echo "🔄 Updating package list..."
    apt-get update -qq
    
    # Install required packages
    echo "📦 Installing required packages..."
    apt-get install -y curl jq bc mysql-client php-cli php-mysql
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Dependencies installed successfully${NC}"
    else
        echo -e "${RED}❌ Failed to install dependencies${NC}"
        exit 1
    fi
}

# Function to setup directories
setup_directories() {
    echo "📁 Setting Up Directories..."
    echo "======================"
    
    # Create test directories
    mkdir -p /opt/lampp/htdocs/mono-v2/tests/{logs,reports,backups}
    
    # Set permissions
    chmod 755 /opt/lampp/htdocs/mono-v2/tests
    chmod 755 /opt/lampp/htdocs/mono-v2/tests/{logs,reports,backups}
    
    echo -e "${GREEN}✅ Directories created${NC}"
}

# Function to setup scripts
setup_scripts() {
    echo "🔧 Setting Up Scripts..."
    echo "===================="
    
    cd /opt/lampp/htdocs/mono-v2/tests
    
    # Make all scripts executable
    chmod +x *.sh
    
    echo -e "${GREEN}✅ Scripts made executable${NC}"
}

# Function to setup database
setup_database() {
    echo "🗄️ Setting Up Database..."
    echo "===================="
    
    cd /opt/lampp/htdocs/mono-v2/tests
    
    # Run database setup
    ./setup_database.sh
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Database setup completed${NC}"
    else
        echo -e "${RED}❌ Database setup failed${NC}"
        exit 1
    fi
}

# Function to test installation
test_installation() {
    echo "🧪 Testing Installation..."
    echo "===================="
    
    cd /opt/lampp/htdocs/mono-v2/tests
    
    # Run quick test
    ./quick_test.sh
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Installation test passed${NC}"
    else
        echo -e "${RED}❌ Installation test failed${NC}"
        exit 1
    fi
}

# Function to create symlinks
create_symlinks() {
    echo "🔗 Creating Symlinks..."
    echo "===================="
    
    # Create symlink in project root
    ln -sf /opt/lampp/htdocs/mono-v2/tests/test_runner.sh /opt/lampp/htdocs/mono-v2/test.sh
    
    echo -e "${GREEN}✅ Symlinks created${NC}"
}

# Function to setup cron jobs
setup_cron() {
    echo "⏰ Setting Up Cron Jobs..."
    echo "===================="
    
    # Add daily quick test
    (crontab -l 2>/dev/null; echo "0 9 * * * /opt/lampp/htdocs/mono-v2/tests/quick_test.sh > /opt/lampp/htdocs/mono-v2/tests/logs/daily_test.log 2>&1") | crontab -
    
    # Add weekly comprehensive test
    (crontab -l 2>/dev/null; echo "0 1 * * 1 /opt/lampp/htdocs/mono-v2/tests/test_comprehensive.sh > /opt/lampp/htdocs/mono-v2/tests/logs/weekly_test.log 2>&1") | crontab -
    
    echo -e "${GREEN}✅ Cron jobs configured${NC}"
    echo "   Daily quick test: 9:00 AM"
    echo "   Weekly comprehensive test: Monday 1:00 AM"
}

# Function to display usage
display_usage() {
    echo ""
    echo "🧪 Test Suite Usage"
    echo "=================="
    echo ""
    echo "Quick Start:"
    echo "  ./test_runner.sh                    # Show test menu"
    echo "  ./test_runner.sh 1                  # Quick test"
    echo "  ./test_runner.sh 2                  # Comprehensive test"
    echo "  ./test_runner.sh 3                  # End-to-end test"
    echo "  ./test_runner.sh 4                  # Database setup"
    echo "  ./test_runner.sh 5                  # Run all tests"
    echo ""
    echo "Maintenance:"
    echo "  ./test_maintenance.sh                # Show maintenance menu"
    echo "  ./test_maintenance.sh 1              # Update credentials"
    echo "  ./test_maintenance.sh 2              # Reset database"
    echo "  ./test_maintenance.sh 3              # Check dependencies"
    echo "  ./test_maintenance.sh 4              # Clean logs"
    echo "  ./test_maintenance.sh 5              # Backup results"
    echo "  ./test_maintenance.sh 6              # Generate reports"
    echo ""
    echo "Automation:"
    echo "  ./automated_test.sh                  # Run all tests"
    echo "  ./automated_test.sh quick            # Quick test only"
    echo "  ./automated_test.sh comprehensive    # Comprehensive test only"
    echo "  ./automated_test.sh end-to-end       - End-to-end test only"
    echo "  ./automated_test.sh report           # Generate report"
    echo "  ./automated_test.sh backup           # Backup results"
    echo ""
    echo "📚 Documentation: tests/README.md"
    echo ""
    echo "🔑 Testing Credentials:"
    echo "  Admin: Admin User / password"
    echo "  Staff: test_mantri@lamabejaya.coop / password"
    echo "  Member: test_member@lamabejaya.coop / password"
}

# Function to verify installation
verify_installation() {
    echo "🔍 Verifying Installation..."
    echo "======================"
    
    # Check if all files exist
    required_files=(
        "/opt/lampp/htdocs/mono-v2/tests/test_runner.sh"
        "/opt/lampp/htdocs/mono-v2/tests/test_comprehensive.sh"
        "/opt/lampp/htdocs/mono-v2/tests/test_end_to_end.sh"
        "/opt/lampp/htdocs/mono-v2/tests/quick_test.sh"
        "/opt/lampp/htdocs/mono-v2/tests/setup_database.sh"
        "/opt/lampp/htdocs/mono-v2/tests/test_maintenance.sh"
        "/opt/lampp/htdocs/mono-v2/tests/automated_test.sh"
        "/opt/lampp/htdocs/mono-v2/tests/README.md"
    )
    
    missing_files=()
    
    for file in "${required_files[@]}"; do
        if [ ! -f "$file" ]; then
            missing_files+=("$file")
        fi
    done
    
    if [ ${#missing_files[@]} -eq 0 ]; then
        echo -e "${GREEN}✅ All test files present${NC}"
    else
        echo -e "${RED}❌ Missing files: ${missing_files[*]}${NC}"
        exit 1
    fi
    
    # Check if directories exist
    required_dirs=(
        "/opt/lampp/htdocs/mono-v2/tests/logs"
        "/opt/lampp/htdocs/mono-v2/tests/reports"
        "/opt/lampp/htdocs/mono-v2/tests/backups"
    )
    
    missing_dirs=()
    
    for dir in "${required_dirs[@]}"; do
        if [ ! -d "$dir" ]; then
            missing_dirs+=("$dir")
        fi
    done
    
    if [ ${#missing_dirs[@]} -eq 0 ]; then
        echo -e "${GREEN}✅ All directories present${NC}"
    else
        echo -e "${RED}❌ Missing directories: ${missing_dirs[*]}${NC}"
        exit 1
    fi
    
    # Check if scripts are executable
    if [ -x "/opt/lampp/htdocs/mono-v2/tests/test_runner.sh" ]; then
        echo -e "${GREEN}✅ Scripts are executable${NC}"
    else
        echo -e "${RED}❌ Scripts not executable${NC}"
        exit 1
    fi
    
    echo -e "${GREEN}✅ Installation verified${NC}"
}

# Main installation process
echo ""
echo "🧪 KSP Lam Gabe Jaya v2.0 Test Suite Installation"
echo "======================================"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${YELLOW}⚠️  Some operations require root privileges${NC}"
    echo "Please run with sudo: sudo $0"
    echo ""
    echo "Continuing with non-root installation..."
    echo ""
fi

# Install dependencies
install_dependencies

# Setup directories
setup_directories

# Setup scripts
setup_scripts

# Setup database
setup_database

# Test installation
test_installation

# Create symlinks (if not root)
if [ "$EUID" -eq 0 ]; then
    create_symlinks
    setup_cron
fi

# Verify installation
verify_installation

echo ""
echo "🎉 Installation Completed Successfully!"
echo "=================================="
echo ""
echo -e "${GREEN}✅ Test suite installed and configured${NC}"
echo ""
echo "📁 Installation Location:"
echo "  /opt/lampp/htdocs/mono-v2/tests/"
echo ""
echo "🔧 Quick Start:"
echo "  cd /opt/lampp/htdocs/mono-v2/tests"
echo "  ./test_runner.sh"
echo ""
echo "📚 Documentation:"
echo "  /opt/lampp/htdocs/mono-v2/tests/README.md"
echo ""
echo "🔑 Testing Credentials:"
echo "  Admin: Admin User / password"
echo "  Staff: test_mantri@lamabejaya.coop / password"
echo "  Member: test_member@lamabejaya.coop / password"
echo ""
echo "🌐 Application URLs:"
echo "  Landing: http://localhost/mono-v2/"
echo "  Login: http://localhost/mono-v2/login.html"
echo "  Admin Dashboard: http://localhost/mono-v2/pages/admin/dashboard.html"
echo ""
echo "📞 For help and documentation, see tests/README.md"
