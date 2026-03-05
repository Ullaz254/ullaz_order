#!/bin/bash

# Laravel Project Startup Script
# This script helps you start your Laravel application

set -e

echo "=== Laravel Project Startup ==="
echo ""

# Get the directory where the script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

echo "📁 Project directory: $SCRIPT_DIR"
echo ""

# Step 1: Check Redis
echo "Step 1: Checking Redis..."
if redis-cli ping > /dev/null 2>&1; then
    echo "✅ Redis is running"
else
    echo "⚠️  Redis is not running. Starting Redis..."
    brew services start redis > /dev/null 2>&1 || {
        echo "❌ Failed to start Redis. Please start it manually:"
        echo "   brew services start redis"
        echo ""
        echo "Or run Redis manually:"
        echo "   redis-server"
        echo ""
        read -p "Press Enter to continue anyway (Redis is optional)..."
    }
    sleep 2
    if redis-cli ping > /dev/null 2>&1; then
        echo "✅ Redis started successfully"
    else
        echo "⚠️  Redis still not running. Continuing without Redis..."
    fi
fi
echo ""

# Step 2: Check dependencies
echo "Step 2: Checking dependencies..."
if [ ! -d "vendor" ]; then
    echo "📦 Installing PHP dependencies..."
    composer install
else
    echo "✅ PHP dependencies installed"
fi

if [ ! -d "node_modules" ]; then
    echo "📦 Installing Node dependencies..."
    npm install
else
    echo "✅ Node dependencies installed"
fi
echo ""

# Step 3: Check .env file
echo "Step 3: Checking .env file..."
if [ ! -f ".env" ]; then
    echo "❌ .env file not found!"
    if [ -f ".env.example" ]; then
        echo "📋 Copying .env.example to .env..."
        cp .env.example .env
        echo "⚠️  Please update .env with your configuration"
    else
        echo "❌ .env.example also not found. Please create .env manually"
        exit 1
    fi
else
    echo "✅ .env file exists"
fi
echo ""

# Step 4: Generate application key
echo "Step 4: Checking application key..."
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "🔑 Generating application key..."
    php artisan key:generate
else
    echo "✅ Application key exists"
fi
echo ""

# Step 5: Clear caches
echo "Step 5: Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "✅ Caches cleared"
echo ""

# Step 6: Test database connection
echo "Step 6: Testing database connection..."
if php artisan db:show > /dev/null 2>&1; then
    echo "✅ Database connection successful"
else
    echo "⚠️  Database connection test failed. This might be okay if migrations haven't run yet."
    echo "   You can test manually: php artisan tinker"
fi
echo ""

# Step 7: Run migrations (optional)
read -p "Do you want to run database migrations? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🔄 Running migrations..."
    php artisan migrate
    echo "✅ Migrations completed"
else
    echo "⏭️  Skipping migrations"
fi
echo ""

# Step 8: Start the server
echo "Step 8: Starting Laravel development server..."
echo ""
echo "🚀 Server will start at: http://localhost:8000"
echo "📝 Press Ctrl+C to stop the server"
echo ""
echo "Starting server..."
echo ""

php artisan serve
