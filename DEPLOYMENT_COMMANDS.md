# Deployment Commands for Hostinger

## Step 1: Commit and Push Changes Locally

```bash
cd "/Users/yogeshgupta/My Projects/Drivarr/ullaz_order/ullaz_order"

# Stage all modified files
git add app/Console/Commands/productImportData.php
git add app/Http/Controllers/Api/v1/AuthController.php
git add app/Http/Controllers/Front/UserhomeController.php
git add app/Http/Middleware/CustomDomain.php
git add app/Http/Middleware/DatabaseDynamic.php
git add app/Providers/AppServiceProvider.php
git add app/Providers/MailConfigServiceProvider.php
git add app/Providers/MailServiceProvider.php
git add app/helpers.php
git add .gitignore

# Commit changes
git commit -m "Fix: PHP 8 deprecation warnings, database connection errors, and remove royo_ prefix

- Fixed PHP 8 deprecation warnings in helpers.php (all parameters optional)
- Added error handling for Redis connections (graceful fallback)
- Added error handling for database connections in all service providers
- Removed hardcoded 'royo_' prefix from database names
- Updated default DB_USERNAME from 'royoorders' to use env variable
- Fixed AuthController and UserhomeController constructor database queries
- App now works gracefully even if Redis or database temporarily unavailable"

# Push to remote
git push origin <your-branch-name>
```

## Step 2: SSH into Hostinger and Deploy

```bash
# SSH into Hostinger
ssh -p 65002 u714731071@147.93.117.75

# Navigate to your Laravel project directory
cd ~/domains/drivarr.com/public_html
# OR wherever your Laravel app is located
# Common locations:
# - ~/public_html
# - ~/domains/yourdomain.com/public_html
# - ~/laravel_app

# Switch to your branch (if using git on server)
git fetch origin
git checkout <your-branch-name>
git pull origin <your-branch-name>

# OR if you're deploying from a specific branch via hPanel:
# Just switch branch in hPanel, then pull:
git pull origin <your-branch-name>
```

## Step 3: Install Dependencies and Clear Caches

```bash
# Make sure you're in the Laravel project root
cd /path/to/your/laravel/project

# Install/update Composer dependencies
composer install --no-dev --optimize-autoloader

# Clear all Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Step 4: Set Permissions (if needed)

```bash
# Set proper permissions for storage and cache
chmod -R 775 storage bootstrap/cache
chown -R u714731071:u714731071 storage bootstrap/cache
```

## Step 5: Verify Deployment

```bash
# Check if server can start (syntax check)
php artisan --version

# Check for any errors in logs
tail -f storage/logs/laravel.log
```

## Quick One-Liner Deployment (After Git Pull)

```bash
cd ~/domains/drivarr.com/public_html && \
git pull origin <your-branch-name> && \
composer install --no-dev --optimize-autoloader && \
php artisan config:clear && \
php artisan cache:clear && \
php artisan route:clear && \
php artisan view:clear && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache && \
echo "✅ Deployment complete!"
```

## Troubleshooting

### If composer command not found:
```bash
# Use full path to composer
/usr/local/bin/composer install --no-dev --optimize-autoloader
# OR
php /usr/local/bin/composer.phar install --no-dev --optimize-autoloader
```

### If artisan command fails:
```bash
# Check PHP version
php -v

# Check if you're in the right directory
pwd
ls -la artisan
```

### If you get permission errors:
```bash
# Check current user
whoami

# Check file ownership
ls -la storage/
```

## Files Changed in This Deployment

1. `app/helpers.php` - PHP 8 deprecation fixes
2. `app/Providers/AppServiceProvider.php` - Error handling + prefix removal
3. `app/Providers/MailConfigServiceProvider.php` - Error handling + prefix removal
4. `app/Providers/MailServiceProvider.php` - Error handling
5. `app/Http/Middleware/CustomDomain.php` - Error handling + prefix removal
6. `app/Http/Middleware/DatabaseDynamic.php` - Prefix removal
7. `app/Http/Controllers/Api/v1/AuthController.php` - Constructor fix
8. `app/Http/Controllers/Front/UserhomeController.php` - Constructor fix
9. `app/Console/Commands/productImportData.php` - Prefix removal + constructor fix
10. `.gitignore` - Added dump.rdb
