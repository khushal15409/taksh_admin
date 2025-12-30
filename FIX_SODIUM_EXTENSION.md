# Quick Fix: PHP Sodium Extension Error

## Error Message
```
lcobucci/jwt 5.6.0 requires ext-sodium * -> it is missing from your system
```

## Quick Solution for PHP 8.4 (cPanel/CloudLinux)

Based on your error message, you're using PHP 8.4. Here's how to fix it:

### Step 1: Check Current PHP Configuration
```bash
php --ini
# This will show which php.ini files are being used
```

### Step 2: Enable Sodium Extension

**Option A: Edit php.ini directly**
```bash
# Edit the main php.ini file
nano /opt/alt/php84/etc/php.ini

# OR edit the linked configuration
nano /opt/alt/php84/link/conf/alt_php.ini
```

**Find and uncomment (or add) this line:**
```ini
extension=sodium
```

**Save the file** (Ctrl+X, then Y, then Enter)

**Option B: Use cPanel Select PHP Version (if available)**
1. Log into cPanel
2. Go to "Select PHP Version" or "MultiPHP Manager"
3. Click "Extensions"
4. Find "sodium" and enable it
5. Save changes

### Step 3: Restart PHP-FPM
```bash
# For cPanel/CloudLinux:
/scripts/restartsrv_php-fpm

# OR if you have systemd:
sudo systemctl restart php84-php-fpm

# OR for Alt-PHP:
sudo systemctl restart alt-php84-php-fpm
```

### Step 4: Verify Extension is Loaded
```bash
php -m | grep sodium
# Should output: sodium

# OR more detailed check:
php -i | grep -i sodium
```

### Step 5: Retry Composer Install
```bash
cd /var/www/html/projects/admin-stage
composer install --no-dev --optimize-autoloader
```

## Alternative: Temporary Workaround

If you cannot enable the extension immediately (e.g., no root access), you can temporarily bypass the check:

```bash
composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-sodium
```

**⚠️ IMPORTANT**: This is only a temporary workaround. The sodium extension is required for Laravel Passport (OAuth2) to work properly. You should enable it as soon as possible.

## Why is Sodium Required?

- Laravel Passport uses `lcobucci/jwt` for JWT token generation
- Version 5.6.0 of `lcobucci/jwt` requires the PHP Sodium extension for cryptographic operations
- This is a security requirement for proper JWT token signing and verification

## Troubleshooting

### If extension still doesn't load after enabling:

1. **Check if the extension file exists:**
```bash
ls -la /opt/alt/php84/lib64/php/modules/sodium.so
# OR
find /opt/alt/php84 -name "sodium.so"
```

2. **If the file doesn't exist, you may need to install it:**
```bash
# For CloudLinux/cPanel, you might need to install via yum/dnf
# Contact your hosting provider or system administrator
```

3. **Check PHP error logs:**
```bash
tail -f /opt/alt/php84/var/log/php-fpm/error.log
```

### If you don't have root/sudo access:

Contact your hosting provider or system administrator to enable the sodium extension. Most shared hosting providers can enable this through their control panel.

## Verification Commands

```bash
# Check PHP version
php -v

# Check if sodium is loaded
php -m | grep sodium

# Check sodium configuration
php -i | grep -A 5 sodium

# Test composer install
composer install --no-dev --optimize-autoloader
```

