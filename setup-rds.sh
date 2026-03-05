#!/bin/bash

# AWS RDS Setup Script
# This script downloads the RDS CA bundle and tests the connection

echo "=== AWS RDS Setup Script ==="
echo ""

# Get the directory where the script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# Download AWS RDS CA bundle
echo "Step 1: Downloading AWS RDS CA bundle..."
CA_BUNDLE_PATH="$SCRIPT_DIR/rds-ca-bundle.pem"

if [ ! -f "$CA_BUNDLE_PATH" ]; then
    echo "Downloading global bundle..."
    curl -k -o "$CA_BUNDLE_PATH" https://truststore.pki.rds.amazonaws.com/global/global-bundle.pem 2>/dev/null
    
    if [ $? -eq 0 ] && [ -f "$CA_BUNDLE_PATH" ]; then
        echo "✓ CA bundle downloaded successfully"
    else
        echo "⚠ Failed to download. Trying region-specific bundle..."
        curl -k -o "$CA_BUNDLE_PATH" https://truststore.pki.rds.amazonaws.com/af-south-1/af-south-1-bundle.pem 2>/dev/null
        
        if [ $? -eq 0 ] && [ -f "$CA_BUNDLE_PATH" ]; then
            echo "✓ CA bundle downloaded successfully"
        else
            echo "✗ Failed to download CA bundle automatically"
            echo "Please download manually:"
            echo "  curl -o rds-ca-bundle.pem https://truststore.pki.rds.amazonaws.com/global/global-bundle.pem"
            exit 1
        fi
    fi
else
    echo "✓ CA bundle already exists"
fi

echo ""
echo "Step 2: Testing PHP and extensions..."
php -r "echo 'PHP Version: ' . PHP_VERSION . PHP_EOL;"
php -r "echo 'PDO MySQL: ' . (extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled') . PHP_EOL;"

echo ""
echo "Step 3: Running connection test..."
if [ -f "test-rds-connection.php" ]; then
    php test-rds-connection.php
else
    echo "✗ test-rds-connection.php not found"
    exit 1
fi

echo ""
echo "=== Setup Complete ==="
echo ""
echo "Next steps:"
echo "1. Update your .env file with RDS credentials"
echo "2. Configure RDS Security Group to allow your IP"
echo "3. Run: php artisan migrate"
echo "4. Start server: php artisan serve"
