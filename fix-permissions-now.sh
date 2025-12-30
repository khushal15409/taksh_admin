#!/bin/bash
# URGENT FIX - Run this with SUDO immediately
# This fixes the 500 error by setting correct ownership and permissions

cd /var/www/html/projects/admin-stage || exit 1

echo "=========================================="
echo "FIXING 500 ERROR - File Permissions"
echo "=========================================="
echo ""

# Fix ownership - THIS IS THE MAIN FIX
echo "1. Changing ownership to www-data..."
sudo chown -R www-data:www-data storage bootstrap/cache
echo "   ✓ Done"

# Fix directory permissions
echo "2. Setting directory permissions..."
sudo find storage bootstrap/cache -type d -exec chmod 775 {} \;
echo "   ✓ Done"

# Fix file permissions
echo "3. Setting file permissions..."
sudo find storage bootstrap/cache -type f -exec chmod 664 {} \;
echo "   ✓ Done"

# OAuth keys need 600
echo "4. Fixing OAuth key permissions..."
sudo chmod 600 storage/oauth-private.key storage/oauth-public.key
echo "   ✓ Done"

# Clear caches
echo "5. Clearing Laravel caches..."
php artisan config:clear > /dev/null 2>&1
php artisan cache:clear > /dev/null 2>&1
php artisan route:clear > /dev/null 2>&1
php artisan view:clear > /dev/null 2>&1
php artisan optimize:clear > /dev/null 2>&1
echo "   ✓ Done"

echo ""
echo "=========================================="
echo "FIX COMPLETE!"
echo "=========================================="
echo ""
echo "Test your site now: https://admin-stage.takshallinone.in"
echo ""
echo "If still getting errors, check:"
echo "  - tail -f storage/logs/laravel.log"
echo "  - sudo tail -f /var/log/apache2/error.log"





