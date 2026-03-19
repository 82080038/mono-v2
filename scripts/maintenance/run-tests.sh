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
