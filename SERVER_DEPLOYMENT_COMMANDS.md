# Server Deployment Commands for Laravel 11 Upgrade

## Pre-Deployment Checklist
1. **Backup your database** before running migrations
2. **Backup your application files** (especially `.env` file)
3. Ensure PHP version is **8.2 or higher**
4. **Enable PHP Sodium Extension** (required for Laravel Passport)
5. Put your application in **maintenance mode** (optional but recommended)

## Step-by-Step Deployment Commands

### 1. Navigate to Project Directory
```bash
cd /var/www/html/projects/admin-stage
```

### 2. Enable Maintenance Mode (Optional)
```bash
php artisan down
```

### 3. Pull Latest Code Changes
```bash
git pull origin main
# OR if using a different branch:
# git pull origin your-branch-name
```

### 4. Install/Update Composer Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

**⚠️ If you get an error about missing `ext-sodium` extension, see the "PHP Sodium Extension Setup" section below.**

### 5. Clear All Caches
```bash
php artisan optimize:clear
```

### 6. Run Database Migrations
```bash
php artisan migrate --force
```

### 7. Clear and Cache Configuration
```bash
php artisan config:clear
php artisan config:cache
```

### 8. Cache Routes (Skip if you have route name conflicts)
```bash
# Only run if you've fixed duplicate route names
php artisan route:cache
# If you have route conflicts, skip this step
```

### 9. Cache Views
```bash
php artisan view:cache
```

### 10. Optimize Autoloader
```bash
composer dump-autoload --optimize
```

### 11. Set Proper Permissions (if needed)
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
# Adjust www-data:www-data to your server's user:group
```

### 12. Restart Queue Workers (if using queues)
```bash
php artisan queue:restart
```

### 13. Restart PHP-FPM (if applicable)
```bash
# For systemd:
sudo systemctl restart php8.2-fpm
# OR for your PHP version:
# sudo systemctl restart php-fpm

# For service management:
# sudo service php8.2-fpm restart
```

### 14. Disable Maintenance Mode
```bash
php artisan up
```

## Complete Command Sequence (Copy-Paste Ready)

```bash
cd /var/www/html/projects/admin-stage

# ⚠️ FIRST: Verify PHP Sodium extension is enabled
php -m | grep sodium
# If not found, enable it first (see "PHP Sodium Extension Setup" section)

# Optional: Enable maintenance mode
php artisan down

# Update dependencies
composer install --no-dev --optimize-autoloader
# If you get sodium error, use: composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-sodium

# Clear caches
php artisan optimize:clear

# Run migrations
php artisan migrate --force

# Cache configuration
php artisan config:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize

# Set permissions (adjust user:group as needed)
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Restart queue workers (if using)
php artisan queue:restart

# Restart PHP-FPM (adjust version as needed)
sudo systemctl restart php8.2-fpm

# Disable maintenance mode
php artisan up
```

## Important Notes

### Route Caching Issue
⚠️ **IMPORTANT**: The application has a duplicate route name conflict:
- Route name `admin.store.store-filter` is duplicated
- You need to fix this in your route files before running `php artisan route:cache`
- Until fixed, skip the route caching step

### Package Changes
- `beyondcode/laravel-websockets` has been removed (not compatible with Laravel 11)
- Consider using Laravel Reverb or an alternative WebSocket solution

### PHP Version Requirement
- Laravel 11 requires PHP 8.2 or higher
- Verify your PHP version: `php -v`

### Environment Variables
- Make sure your `.env` file is properly configured
- Check that `APP_ENV` and `APP_DEBUG` are set correctly for production

## PHP Sodium Extension Setup

### ⚠️ IMPORTANT: Required Extension
Laravel Passport requires the PHP Sodium extension. If you see this error:
```
lcobucci/jwt 5.6.0 requires ext-sodium * -> it is missing from your system
```

### Solution 1: Enable Sodium Extension (Recommended)

#### For cPanel/CloudLinux (PHP 8.4):
```bash
# Check current PHP version
php -v

# Enable sodium extension in php.ini
# Edit the PHP configuration file:
nano /opt/alt/php84/etc/php.ini
# OR
nano /opt/alt/php84/link/conf/alt_php.ini

# Add or uncomment this line:
extension=sodium

# Save and restart PHP-FPM
# For cPanel:
/scripts/restartsrv_php-fpm
# OR
sudo systemctl restart php84-php-fpm
```

#### For Standard Linux (Ubuntu/Debian):
```bash
# Install sodium extension
sudo apt-get update
sudo apt-get install php8.4-sodium  # Adjust version as needed

# OR for PHP 8.2:
sudo apt-get install php8.2-sodium

# Enable it in php.ini
sudo nano /etc/php/8.4/fpm/php.ini  # Adjust path as needed
# Add: extension=sodium

# Restart PHP-FPM
sudo systemctl restart php8.4-fpm  # Adjust version as needed
```

#### For CentOS/RHEL:
```bash
# Install sodium extension
sudo yum install php-sodium
# OR for newer versions:
sudo dnf install php-sodium

# Restart PHP-FPM
sudo systemctl restart php-fpm
```

### Verify Sodium Extension is Enabled
```bash
php -m | grep sodium
# Should output: sodium

# OR check with:
php -i | grep sodium
```

### Solution 2: Temporary Workaround (Not Recommended for Production)
If you cannot enable the sodium extension immediately, you can temporarily ignore the requirement:
```bash
composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-sodium
```

**⚠️ Warning**: This is a temporary workaround. The sodium extension is required for Laravel Passport to function properly. You should enable it as soon as possible.

## Troubleshooting

### If migrations fail:
```bash
php artisan migrate:status
php artisan migrate --force
```

### If you encounter permission errors:
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache
```

### If composer fails due to missing extensions:
```bash
# Check which PHP extensions are installed
php -m

# If sodium is missing, enable it (see "PHP Sodium Extension Setup" above)
# Then retry:
composer install --no-dev --optimize-autoloader
```

### If composer fails with other errors:
```bash
composer self-update
composer install --no-dev --optimize-autoloader
```

### To check Laravel version:
```bash
php artisan --version
```

## Post-Deployment Verification

1. Check application is running: Visit your site URL
2. Check logs for errors: `tail -f storage/logs/laravel.log`
3. Verify Laravel version: `php artisan --version` (should show 11.x)
4. Test critical functionality (login, API endpoints, etc.)

## Rollback (if needed)

If you need to rollback:

```bash
cd /var/www/html/projects/admin-stage

# Restore from backup
# Restore database backup
# Restore application files

# Revert composer.json and run:
composer update laravel/framework:^10.0 --with-all-dependencies

# Clear caches
php artisan optimize:clear

# Restart services
sudo systemctl restart php8.2-fpm
```

