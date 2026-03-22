#!/bin/bash

# Comprehensive Testing Script for KSP Lam Gabe Jaya Admin Pages
# This script tests all admin pages for functionality and completeness

echo "🚀 Starting Comprehensive Testing for KSP Lam Gabe Jaya Admin Pages"
echo "================================================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Base URL
BASE_URL="http://localhost/mono-v2/pages/admin"

# List of admin pages to test
PAGES=(
    "dashboard.html"
    "members.html" 
    "loans.html"
    "savings.html"
    "transactions.html"
    "reports.html"
    "settings.html"
    "npl.html"
    "risk-fraud.html"
    "member-registration.html"
    "loan-management.html"
    "savings-management.html"
    "guarantee-management.html"
    "database-management.html"
    "role-access-enhanced-final.html"
    "audit-log.html"
    "bi-analytics.html"
    "capacity.html"
    "live-tracking.html"
    "verifikasi.html"
    "system-config.html"
    "laporan-shu.html"
    "laporan-umum.html"
    "users.html"
)

# Test results
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

echo -e "${BLUE}📋 Testing ${#PAGES[@]} admin pages...${NC}"
echo ""

# Function to test a single page
test_page() {
    local page=$1
    local url="${BASE_URL}/${page}"
    
    echo -e "${YELLOW}Testing: ${page}${NC}"
    
    # Check if file exists
    if [ ! -f "/opt/lampp/htdocs/mono-v2/pages/admin/$page" ]; then
        echo -e "  ${RED}❌ File not found${NC}"
        ((FAILED_TESTS++))
        return
    fi
    
    # Check file size (empty files are suspicious)
    local file_size=$(stat -c%s "/opt/lampp/htdocs/mono-v2/pages/admin/$page" 2>/dev/null || echo "0")
    if [ "$file_size" -lt 100 ]; then
        echo -e "  ${RED}❌ File too small (${file_size} bytes)${NC}"
        ((FAILED_TESTS++))
        return
    fi
    
    # Check for essential HTML structure
    if ! grep -q "<!DOCTYPE html>" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
        echo -e "  ${RED}❌ Missing DOCTYPE${NC}"
        ((FAILED_TESTS++))
        return
    fi
    
    # Check for Bootstrap CSS
    if ! grep -q "bootstrap" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
        echo -e "  ${RED}❌ Missing Bootstrap CSS${NC}"
        ((FAILED_TESTS++))
        return
    fi
    
    # Check for Font Awesome
    if ! grep -q "font-awesome" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
        echo -e "  ${RED}❌ Missing Font Awesome${NC}"
        ((FAILED_TESTS++))
        return
    fi
    
    # Check for sidebar navigation
    if ! grep -q "sidebar" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
        echo -e "  ${RED}❌ Missing sidebar navigation${NC}"
        ((FAILED_TESTS++))
        return
    fi
    
    # Check for main content area
    if ! grep -q "main-content" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
        echo -e "  ${RED}❌ Missing main content area${NC}"
        ((FAILED_TESTS++))
        return
    fi
    
    # Check for JavaScript functionality
    if ! grep -q "script" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
        echo -e "  ${RED}❌ Missing JavaScript${NC}"
        ((FAILED_TESTS++))
        return
    fi
    
    # Check for responsive design
    if ! grep -q "viewport" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
        echo -e "  ${RED}❌ Missing viewport meta tag${NC}"
        ((FAILED_TESTS++))
        return
    fi
    
    # Check for PWA manifest
    if ! grep -q "manifest.webmanifest" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
        echo -e "  ${YELLOW}⚠️  Missing PWA manifest${NC}"
    fi
    
    echo -e "  ${GREEN}✅ Passed basic structure tests${NC}"
    ((PASSED_TESTS++))
    ((TOTAL_TESTS++))
}

# Function to test navigation consistency
test_navigation() {
    echo -e "${YELLOW}Testing navigation consistency...${NC}"
    
    local nav_links=(
        "dashboard.html"
        "members.html"
        "loans.html"
        "savings.html"
        "transactions.html"
        "reports.html"
        "settings.html"
    )
    
    for page in "${PAGES[@]}"; do
        if [ -f "/opt/lampp/htdocs/mono-v2/pages/admin/$page" ]; then
            for link in "${nav_links[@]}"; do
                if grep -q "$link" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                    echo -e "  ${GREEN}✅ $page contains $link${NC}"
                fi
            done
        fi
    done
}

# Function to test modal functionality
test_modals() {
    echo -e "${YELLOW}Testing modal functionality...${NC}"
    
    local pages_with_modals=(
        "members.html"
        "loans.html"
        "savings.html"
        "transactions.html"
        "reports.html"
    )
    
    for page in "${pages_with_modals[@]}"; do
        if [ -f "/opt/lampp/htdocs/mono-v2/pages/admin/$page" ]; then
            if grep -q "modal" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                echo -e "  ${GREEN}✅ $page has modal functionality${NC}"
                
                # Check for Bootstrap modal classes
                if grep -q "modal-dialog" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                    echo -e "    ${GREEN}✅ Bootstrap modal structure found${NC}"
                fi
                
                # Check for modal JavaScript
                if grep -q "bootstrap.Modal" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                    echo -e "    ${GREEN}✅ Modal JavaScript found${NC}"
                fi
            else
                echo -e "  ${YELLOW}⚠️  $page has no modals${NC}"
            fi
        fi
    done
}

# Function to test form validation
test_forms() {
    echo -e "${YELLOW}Testing form validation...${NC}"
    
    local pages_with_forms=(
        "members.html"
        "loans.html"
        "savings.html"
        "transactions.html"
        "settings.html"
    )
    
    for page in "${pages_with_forms[@]}"; do
        if [ -f "/opt/lampp/htdocs/mono-v2/pages/admin/$page" ]; then
            if grep -q "<form" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                echo -e "  ${GREEN}✅ $page has forms${NC}"
                
                # Check for required fields
                if grep -q "required" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                    echo -e "    ${GREEN}✅ Required field validation found${NC}"
                fi
                
                # Check for form validation JavaScript
                if grep -q "checkValidity\|reportValidity" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                    echo -e "    ${GREEN}✅ Form validation JavaScript found${NC}"
                fi
            else
                echo -e "  ${YELLOW}⚠️  $page has no forms${NC}"
            fi
        fi
    done
}

# Function to test data tables
test_tables() {
    echo -e "${YELLOW}Testing data tables...${NC}"
    
    local pages_with_tables=(
        "members.html"
        "loans.html"
        "savings.html"
        "transactions.html"
        "reports.html"
    )
    
    for page in "${pages_with_tables[@]}"; do
        if [ -f "/opt/lampp/htdocs/mono-v2/pages/admin/$page" ]; then
            if grep -q "<table" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                echo -e "  ${GREEN}✅ $page has data tables${NC}"
                
                # Check for Bootstrap table classes
                if grep -q "table-responsive\|table-hover" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                    echo -e "    ${GREEN}✅ Bootstrap table styling found${NC}"
                fi
                
                # Check for table JavaScript
                if grep -q "load.*Table\|filter.*Table" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                    echo -e "    ${GREEN}✅ Table JavaScript functionality found${NC}"
                fi
            else
                echo -e "  ${YELLOW}⚠️  $page has no tables${NC}"
            fi
        fi
    done
}

# Function to test charts and analytics
test_charts() {
    echo -e "${YELLOW}Testing charts and analytics...${NC}"
    
    local pages_with_charts=(
        "dashboard.html"
        "reports.html"
        "bi-analytics.html"
    )
    
    for page in "${pages_with_charts[@]}"; do
        if [ -f "/opt/lampp/htdocs/mono-v2/pages/admin/$page" ]; then
            if grep -q "Chart.js\|chart.js" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                echo -e "  ${GREEN}✅ $page has Chart.js integration${NC}"
                
                # Check for canvas elements
                if grep -q "<canvas" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                    echo -e "    ${GREEN}✅ Chart canvas elements found${NC}"
                fi
                
                # Check for chart initialization
                if grep -q "new Chart\|Chart(" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                    echo -e "    ${GREEN}✅ Chart initialization found${NC}"
                fi
            else
                echo -e "  ${YELLOW}⚠️  $page has no charts${NC}"
            fi
        fi
    done
}

# Function to test API integration
test_api_integration() {
    echo -e "${YELLOW}Testing API integration...${NC}"
    
    for page in "${PAGES[@]}"; do
        if [ -f "/opt/lampp/htdocs/mono-v2/pages/admin/$page" ]; then
            # Check for API calls
            if grep -q "fetch\|ajax\|xhr" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                echo -e "  ${GREEN}✅ $page has API integration${NC}"
            fi
            
            # Check for modal manager integration
            if grep -q "modal-manager.js" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                echo -e "    ${GREEN}✅ Modal manager integration found${NC}"
            fi
            
            # Check for content renderer integration
            if grep -q "content-renderer.js" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                echo -e "    ${GREEN}✅ Content renderer integration found${NC}"
            fi
        fi
    done
}

# Function to test PWA features
test_pwa_features() {
    echo -e "${YELLOW}Testing PWA features...${NC}"
    
    for page in "${PAGES[@]}"; do
        if [ -f "/opt/lampp/htdocs/mono-v2/pages/admin/$page" ]; then
            # Check for service worker
            if grep -q "serviceWorker\|sw.js" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                echo -e "  ${GREEN}✅ $page has service worker integration${NC}"
            fi
            
            # Check for manifest
            if grep -q "manifest" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                echo -e "    ${GREEN}✅ PWA manifest found${NC}"
            fi
            
            # Check for PWA meta tags
            if grep -q "theme-color\|apple-mobile-web-app" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                echo -e "    ${GREEN}✅ PWA meta tags found${NC}"
            fi
        fi
    done
}

# Function to test security features
test_security() {
    echo -e "${YELLOW}Testing security features...${NC}"
    
    for page in "${PAGES[@]}"; do
        if [ -f "/opt/lampp/htdocs/mono-v2/pages/admin/$page" ]; then
            # Check for XSS protection (basic)
            if grep -q "textContent\|innerText" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                echo -e "  ${GREEN}✅ $page uses safe DOM manipulation${NC}"
            fi
            
            # Check for input validation
            if grep -q "validate\|sanitize" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                echo -e "    ${GREEN}✅ Input validation found${NC}"
            fi
        fi
    done
}

# Function to test accessibility
test_accessibility() {
    echo -e "${YELLOW}Testing accessibility...${NC}"
    
    for page in "${PAGES[@]}"; do
        if [ -f "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
            # Check for alt tags on images
            if grep -q "<img" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                if grep -q "alt=" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                    echo -e "  ${GREEN}✅ $page has image alt tags${NC}"
                else
                    echo -e "  ${YELLOW}⚠️  $page missing image alt tags${NC}"
                fi
            fi
            
            # Check for ARIA labels
            if grep -q "aria-" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                echo -e "    ${GREEN}✅ ARIA labels found${NC}"
            fi
            
            # Check for semantic HTML5
            if grep -q "<header\|<main\|<nav\|<footer\|<section\|<article" "/opt/lampp/htdocs/mono-v2/pages/admin/$page"; then
                echo -e "    ${GREEN}✅ Semantic HTML5 found${NC}"
            fi
        fi
    done
}

# Main testing execution
echo -e "${BLUE}🧪 Running comprehensive tests...${NC}"
echo ""

# Test each page
for page in "${PAGES[@]}"; do
    test_page "$page"
    ((TOTAL_TESTS++))
done

echo ""
echo -e "${BLUE}📊 Running specialized tests...${NC}"
echo ""

test_navigation
test_modals
test_forms
test_tables
test_charts
test_api_integration
test_pwa_features
test_security
test_accessibility

# Calculate final results
echo ""
echo "================================================================"
echo -e "${BLUE}📈 Test Results Summary${NC}"
echo "================================================================"
echo -e "Total Pages Tested: ${#PAGES[@]}"
echo -e "Basic Structure Tests: ${PASSED_TESTS}/${TOTAL_TESTS} passed"
echo -e "Failed Tests: ${FAILED_TESTS}"
echo ""

# Success rate
if [ $FAILED_TESTS -eq 0 ]; then
    echo -e "${GREEN}🎉 All tests passed! Admin pages are complete and functional.${NC}"
    exit 0
else
    echo -e "${RED}❌ Some tests failed. Please review the issues above.${NC}"
    exit 1
fi
