#!/bin/bash
# Script to fix .env configuration for production server
# This script updates the .env file with correct production settings

PROJECT_DIR="/var/www/html/projects/admin-stage"
cd "$PROJECT_DIR" || exit 1

ENV_FILE=".env"

if [ ! -f "$ENV_FILE" ]; then
    echo "Error: .env file not found!"
    exit 1
fi

echo "Fixing .env configuration for production..."

# Backup .env file
cp "$ENV_FILE" "${ENV_FILE}.backup.$(date +%Y%m%d_%H%M%S)"
echo "✓ Created backup of .env file"

# Fix APP_URL (remove typo 'hhttp' and update to actual domain)
sed -i 's|APP_URL=hhttp://localhost/apitaksh/|APP_URL=https://admin-stage.takshallinone.in|g' "$ENV_FILE"
sed -i 's|APP_URL=http://localhost/apitaksh/|APP_URL=https://admin-stage.takshallinone.in|g' "$ENV_FILE"

# Set APP_ENV to production
sed -i 's/APP_ENV=local/APP_ENV=production/g' "$ENV_FILE"

# Set APP_DEBUG to false
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/g' "$ENV_FILE"

echo "✓ Updated APP_URL to https://admin-stage.takshallinone.in"
echo "✓ Set APP_ENV to production"
echo "✓ Set APP_DEBUG to false"
echo ""
echo "IMPORTANT: Please verify and update the following in .env if needed:"
echo "  - DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD (database credentials)"
echo "  - Any other environment-specific settings"
echo ""
echo "After updating .env, run: php artisan config:clear"





