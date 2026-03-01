# Fix: Database Connection for Main Domain

## Problem
The query was trying to access `royoorders.cab_booking_layouts` but the new database is `u714731071_drivarr_db`. This happened because:

1. For the main domain (`drivarr.com`), the database switching logic in `AppServiceProvider::connectDynamicDb()` was being skipped
2. The default database connection was still set to `royoorders` instead of the new database from `.env`

## Solution

### 1. Added Error Handling
- Wrapped `CabBookingLayout` query in try-catch in `UserhomeController.php`
- If the table doesn't exist or query fails, it will continue with an empty collection instead of crashing

### 2. Fixed Database Connection for Main Domain
- Modified `AppServiceProvider::connectDynamicDb()` to ensure the main domain uses the database from `.env`
- Added explicit check for `drivarr.com` and `Main_Domain`
- Switches to the correct database before processing requests

## Files Changed

1. `app/Http/Controllers/Front/UserhomeController.php`
   - Added try-catch around `CabBookingLayout` query (line 610-625)

2. `app/Providers/AppServiceProvider.php`
   - Added database switching logic for main domain (line 217-240)

## Deployment Steps

```bash
# 1. Pull latest code
git pull origin php-migration

# 2. Clear config cache (CRITICAL - ensures .env values are loaded)
php artisan config:clear
php artisan config:cache

# 3. Verify .env has correct database
grep "DB_DATABASE" .env
# Should show: DB_DATABASE=u714731071_drivarr_db

# 4. Clear all caches
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 5. Test in browser
# Visit: https://drivarr.com/
# Should now load without database errors
```

## Important Notes

- The error handling will allow the page to load even if `cab_booking_layouts` table doesn't exist
- The page may show limited content if the table is missing, but it won't crash
- Check logs for warnings about missing tables: `tail -f storage/logs/laravel.log`

## If Still Getting Errors

If you still see `royoorders` in errors:
1. Check `.env` file has `DB_DATABASE=u714731071_drivarr_db`
2. Run `php artisan config:clear && php artisan config:cache`
3. Check if there are other hardcoded database names in the codebase
