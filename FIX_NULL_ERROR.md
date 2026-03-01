# Fix for Null Pointer Error

## Error Fixed
```
ErrorException: Attempt to read property "language_id" on null
in FrontController.php (line 341)
```

## Problem
When `drivarr.com` passes through as the main domain, the `CustomDomain` middleware doesn't set up client language data. The `categoryNavOld()` method tries to access `$primary->language_id` but `$primary` is null because `ClientLanguage::orderBy('is_primary','desc')->first()` returns null when there's no client language data.

## Solution Applied
Added null check in `FrontController.php` line 340-342:

**Before:**
```php
->where(function ($qrt) use($lang_id,$primary){
    $qrt->where('cts.language_id', $lang_id)->orWhere('cts.language_id',$primary->language_id);
})
```

**After:**
```php
->where(function ($qrt) use($lang_id,$primary){
    $qrt->where('cts.language_id', $lang_id);
    if ($primary && isset($primary->language_id)) {
        $qrt->orWhere('cts.language_id', $primary->language_id);
    }
})
```

## Deployment

```bash
# Pull latest code
git pull origin <your-branch-name>

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild (skip route:cache if duplicate route error)
php artisan config:cache
php artisan view:cache
# php artisan route:cache  # Skip this if you get duplicate route error
```

## Route Cache Issue

You're getting:
```
Unable to prepare route [payment/success/icici] for serialization. 
Another route has already been assigned name [payment.icici.success].
```

**Solution:** Skip route caching for now:
```bash
# Don't run: php artisan route:cache
# Routes will work without caching, just slightly slower
```

Or find and fix the duplicate route name in your routes files.

## Test

After deploying, visit `https://drivarr.com` - it should load without the null pointer error.
