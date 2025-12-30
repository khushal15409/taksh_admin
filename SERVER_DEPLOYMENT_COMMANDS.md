# Server Deployment Commands for Laravel 11 Upgrade

## Pre-Deployment Checklist
1. **Backup your database** before running migrations
2. **Backup your application files** (especially `.env` file)
3. Ensure PHP version is **8.2 or higher**
4. Put your application in **maintenance mode** (optional but recommended)

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

# Optional: Enable maintenance mode
php artisan down

# Update dependencies
composer install --no-dev --optimize-autoloader

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

### If composer fails:
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

