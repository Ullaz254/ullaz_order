# Deploy Fix for 404 on drivarr.com

## Quick Fix Steps on Hostinger

### Step 1: SSH and Deploy Code
```bash
ssh -p 65002 u714731071@147.93.117.75
cd ~/domains/drivarr.com/public_html  # Adjust path as needed

# After switching branch in hPanel, pull changes
git pull origin <your-branch-name>

# Install dependencies
composer install --no-dev --optimize-autoloader

# Clear and rebuild caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 2: Set Main_Domain in .env (CRITICAL)

Edit your `.env` file on Hostinger and add/update:
```env
Main_Domain=drivarr.com
```

**This is the key fix!** Without this, the middleware will look for a client record in the database.

### Step 3: Verify .env File

Make sure your `.env` has:
```env
APP_ENV=production
APP_DEBUG=false
Main_Domain=drivarr.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u714731071_drivarr_db
DB_USERNAME=u714731071_drivarr_user
DB_PASSWORD=your_password_here

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=predis
```

### Step 4: Test

After deploying and setting `Main_Domain`, visit `https://drivarr.com` - it should work!

## Alternative: Create Client Record

If you DON'T want to use `Main_Domain`, you can create a client record instead:

```sql
-- Connect to your database
mysql -u u714731071_drivarr_user -p u714731071_drivarr_db

-- Insert client record
INSERT INTO clients (
    name, 
    code, 
    custom_domain, 
    database_name, 
    database_username, 
    database_password,
    database_host,
    database_port,
    status,
    is_deleted,
    is_blocked,
    created_at,
    updated_at
) VALUES (
    'Drivarr',
    'drivarr',
    'drivarr.com',
    'u714731071_drivarr_db',
    'u714731071_drivarr_user',
    'your_db_password',
    '127.0.0.1',
    '3306',
    1,
    0,
    0,
    NOW(),
    NOW()
);
```

## What Was Fixed

1. **CustomDomain middleware** - Now allows `Main_Domain` to pass through without client lookup
2. **404 handling** - Changed from redirect to non-existent route to proper `abort(404)`
3. **Error handling** - All database/Redis errors are handled gracefully

## Troubleshooting

### Still getting 404?
1. Check `.env` has `Main_Domain=drivarr.com`
2. Run `php artisan config:clear && php artisan config:cache`
3. Check logs: `tail -f storage/logs/laravel.log`
4. Verify route cache: `php artisan route:list | grep userHome`

### Redis warnings?
These are **normal and expected**. The app works without Redis - it just falls back to database queries.
