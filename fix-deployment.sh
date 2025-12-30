#!/bin/bash
# Laravel Deployment Fix Script
# Run this script with sudo to fix common deployment issues

echo "=== Laravel Deployment Fix Script ==="
echo ""

# Get the project directory
PROJECT_DIR="/var/www/html/projects/admin-stage"
cd "$PROJECT_DIR" || exit 1

echo "1. Fixing OAuth key permissions..."
chmod 600 storage/oauth-private.key storage/oauth-public.key
echo "   ✓ OAuth keys permissions fixed"

echo ""
echo "2. Setting storage and cache directory permissions..."
find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;
chmod 600 storage/oauth-private.key storage/oauth-public.key
echo "   ✓ Directory permissions set"

echo ""
echo "3. Changing ownership to www-data..."
chown -R www-data:www-data storage bootstrap/cache
echo "   ✓ Ownership changed to www-data"

echo ""
echo "4. Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
echo "   ✓ Laravel caches cleared"

echo ""
echo "5. Clearing and optimizing application..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
echo "   ✓ Laravel caches cleared"

echo ""
echo "=== Deployment fixes completed! ==="
echo ""
echo "IMPORTANT: Please verify the following:"
echo "  ✓ .env file has correct database credentials"
echo "  ✓ Database is accessible from the server"
echo "  ✓ APP_KEY is set in .env file (should be: base64:...)"
echo "  ✓ APP_URL is set to: https://admin-stage.takshallinone.in"
echo "  ✓ public/.htaccess file exists (✓ Fixed)"
echo "  ✓ OAuth key permissions are correct (✓ Fixed)"
echo ""
echo "If APP_KEY is missing, run: php artisan key:generate"
echo ""
echo "Note: If you still get 500 errors, check:"
echo "  - Web server document root points to: $PROJECT_DIR/public"
echo "  - Apache/Nginx configuration is correct"
echo "  - PHP error logs: /var/log/apache2/error.log or /var/log/nginx/error.log"

