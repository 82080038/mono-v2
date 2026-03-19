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
