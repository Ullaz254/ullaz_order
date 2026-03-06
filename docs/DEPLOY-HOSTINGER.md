# Deploy on Hostinger (after git pull)

Run these on the server over SSH, from your **project root** (where `artisan` and `composer.json` are).

## 1. Go to project directory

```bash
cd /path/to/ullaz_order
# Example: cd ~/domains/drivarr.com/ullaz_order  (adjust to your Hostinger path)
```

## 2. Install / update PHP dependencies

```bash
composer install --no-dev --optimize-autoloader
```

-   `--no-dev`: no dev packages in production
-   If you don’t have Composer on the server, use Hostinger’s PHP/Composer from panel or install Composer in your home dir.

## 3. Environment file

Make sure `.env` exists on the server (it’s usually **not** in git).

-   If you already have `.env` on the server, keep it and only change what’s needed after pull.
-   If this is a fresh deploy, copy from example and edit:

```bash
cp .env.example .env
# Edit .env (APP_URL, DB_*, etc.) with your editor or nano
```

Generate key only if `.env` is new and no key is set:

```bash
php artisan key:generate
```

## 4. Laravel caches (recommended for production)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 5. Migrations (if you have DB changes)

```bash
php artisan migrate --force
```

-   `--force` is needed in production.
-   If you don’t use migrations, skip this.

## 6. Permissions

Laravel needs to write to `storage` and `bootstrap/cache`:

```bash
chmod -R 775 storage bootstrap/cache
# If your web server user is different (e.g. www-data), set ownership:
# chown -R $USER:www-data storage bootstrap/cache
```

(Adjust user/group to match your Hostinger setup if they document it.)

## 7. Document root

-   The **document root** of the domain (e.g. drivarr.com) must point to the **`public`** folder of this project.  
    **Example:** If the app lives in `/home/u714731071/laravel_app`, set document root to `/home/u714731071/laravel_app/public` (not `laravel_app` or `public_html`).
-   In Hostinger: Domain → Advanced → Document Root (or similar) and set it to that `public` path.
-   If the domain currently points to `public_html`, either:
    -   Change document root to `laravel_app/public`, or
    -   Add a symlink: e.g. `public_html` → `laravel_app/public` (only if your panel allows it).
-   Do **not** point the domain to the project root; Laravel must serve from `public`.

## 8. After each future `git pull`

Run:

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force   # only if you pulled new migrations
```

**If the homepage shows 404 after deploy:** Clear and rebuild route cache (route cache can serve old logic):

```bash
php artisan route:clear
php artisan route:cache
```

Or use the script (see below):

```bash
./deploy.sh
```

---

## One-time script (optional)

Save as `deploy.sh` in the project root and run `./deploy.sh` after each `git pull`:

```bash
#!/bin/bash
set -e
echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader
echo "Caching..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "Migrations (if any)..."
php artisan migrate --force
echo "Done."
```

Make it executable once: `chmod +x deploy.sh`

---

## Troubleshooting

### Homepage shows "Laravel Application" / welcome page instead of the store

That usually means `UserhomeController@index` is throwing an exception and the controller’s catch is returning the welcome view. Fix by finding the real error.

**1. Check the Laravel log on the server**

```bash
cd /home/u714731071/laravel_app
tail -100 storage/logs/laravel.log
```

Or open the latest log file and look for the most recent `UserhomeController` or homepage-related error and stack trace.

**2. Typical causes**

-   **Route not found** (e.g. `route('categoryDetail')` or similar): often due to route cache or domain. Try `php artisan route:clear` then `php artisan route:cache` again after ensuring `APP_URL` in `.env` is `https://drivarr.com`.
-   **Missing view or wrong path**: error message will name the view file.
-   **Database/query error**: error message or stack trace will point to the query or model.
-   **Wrong document root**: if the domain points to `public_html` instead of `laravel_app/public`, the app may not run correctly. Set document root to `laravel_app/public` (see §7 above).
