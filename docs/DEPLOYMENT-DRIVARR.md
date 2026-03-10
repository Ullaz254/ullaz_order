# Deployment to drivarr.com – Checklist

## What was fixed for localhost (and production impact)

### CSS (shimmer) – fixed

-   **Issue:** `shimmer-less.less` was loaded with `rel="stylesheet/less"`, so the browser compiled LESS at runtime (~70+ seconds), making the page look unstyled.
-   **Fix:** Pre-compiled CSS added as `public/front-assets/css/shimmer-less.css`. Templates now use this file with `rel="stylesheet"`.
-   **On drivarr.com:** Same fix applies. No client-side LESS; CSS loads immediately.

### API (cancelled / pending) – localhost vs drivarr.com

-   **On localhost:** Homepage APIs (`homePageDataCategoryMenu`, `homePageDataNew`, `cartProducts`) were cancelled or pending because:
    1. Redirect to category page was firing on localhost and aborting in-flight requests (fixed: redirect skipped on localhost in JS).
    2. Typo in `location.js` (`selected_address` split across lines) caused a JS error (fixed).
-   **On drivarr.com:**
    -   Domain-based routes will be used; API base URL will match the site (no localhost vs drivarr mismatch).
    -   If `getConfig` or other endpoints are slow (e.g. 14+ seconds), that will still happen on production until backend/DB is optimized.
    -   Ensure `APP_URL` and `Main_Domain` (or equivalent) in `.env` on drivarr.com are set to the production URL.

### Controllers

-   **UserhomeController:** Redirect to `categoryDetail` is skipped when host is localhost so the homepage loads. On drivarr.com the redirect runs as before (route exists in domain group).

## If you see “Laravel Application” / welcome page instead of your site

The app shows that page when the homepage controller throws an exception. Do the following on the server:

1. **Set domain in `.env`** (required for drivarr.com):

    ```env
    APP_URL=https://drivarr.com
    Main_Domain=drivarr.com
    ```

    Then run: `php artisan config:clear` and `php artisan config:cache`.

2. **Check the real error** in `storage/logs/laravel.log` (e.g. `tail -80 storage/logs/laravel.log`). Look for `UserhomeController index failed` to see the exception.

3. **Redis not installed / Connection refused**: If you see `Connection refused [tcp://127.0.0.1:6379]` in logs or when running `php artisan config:cache`, either:
    - **Option A (recommended when Redis is not available):** In `.env` set:
        ```env
        CACHE_DRIVER=file
        SESSION_DRIVER=file
        ```
        Then run only `php artisan config:clear` (do **not** run `config:cache` until Redis is available if you want to use it later).
    - **Option B:** Install and start Redis on the server, then keep `CACHE_DRIVER=redis` if you prefer.
      The app is now resilient: if Redis is down, bootstrap will still succeed (cache calls fall back or skip). Using `file` driver avoids Redis entirely.

## Before deploying to drivarr.com

1. **Environment**

    - Set `APP_URL=https://drivarr.com` and `Main_Domain=drivarr.com` in `.env` on the server.
    - Ensure production DB and Redis (if used) are configured and reachable.

2. **Assets**

    - Use the new `shimmer-less.css` (already in repo). Do **not** rely on client-side LESS on production.
    - Run `php artisan config:cache` and `php artisan route:cache` after deploy if you use config/route caching.

3. **Performance**

    - If `getConfig` or homepage APIs are slow locally, profile and optimize (DB queries, middleware) before or right after deploy; slowness will carry over to production.

4. **Testing**
    - Smoke-test on staging with production-like config (same domain/APP_URL pattern as drivarr.com) for:
        - Homepage load (no redirect loop, content loads).
        - Network tab: `homePageDataCategoryMenu`, `homePageDataNew`, `cartProducts`, `getConfig` return 200 and reasonable response times.

## Homepage blank / APIs return 200 but empty data

If the homepage loads but stays white or shows only a spinner, and the Network tab shows `getConfig`, `homePageDataNew`, `homePageDataCategoryMenu` with **200 OK** but very small payloads (~1 KB), the database is missing the data those APIs need.

1. **Check which tables are empty:**

    ```bash
    php artisan home:check-data
    ```

    This lists tables (client_preferences, categories, cab_booking_layouts, client_languages, types, etc.) and reports EMPTY or OK.

2. **Seed minimal homepage data (safe to run multiple times):**

    - **If the Artisan command is available:** `php artisan home:seed-minimal`
    - **If the seeder class is available:** `php artisan db:seed --class=HomepageMinimalSeeder`
    - **If neither is on the server** (e.g. "Command not defined" / "HomepageMinimalSeeder does not exist"): upload the one-off script from the repo root and run:
        ```bash
        php seed-homepage-once.php
        ```
        Then delete `seed-homepage-once.php`. The script bootstraps Laravel and inserts the same data; safe to run multiple times.
        All of the above insert only when a table is empty:
    - 1 language, 1 country, 1 currency
    - 1 client (code `DRIVARR`), 1 client_preference, 1 client_language
    - Types (via TypeSeeder), categories + category_translations (via CategorySeeder)
    - 2 cab_booking_layouts (pickup_delivery, vendors) with `type=1`, `is_active=1` for web

3. **Verify:**

    ```bash
    php artisan home:check-data
    ```

    Then reload the site; getConfig and home sections should return data and the main content area can render.

4. **Optional:** To add more layout sections (e.g. featured_products, brands), run:
    ```bash
    php artisan db:seed --class=HomePageLabelSeederDefault
    ```
    (Only if cab_booking_layouts already has rows; this adds or updates layout slugs.)

## HTTP 500 on homepage (This page isn't working)

If the site shows **HTTP ERROR 500** or "This page isn't working", the app is throwing an exception. The exact error is written to the Laravel log.

1. **On the server**, from the project root, run:

    ```bash
    tail -150 storage/logs/laravel.log
    ```

    Or open the latest log file (e.g. `storage/logs/laravel-2026-03-07.log`).

2. **Look for** the most recent error entry. You should see either:

    - `UserhomeController failed on home route` (exception in the route wrapper), or
    - `UserhomeController index failed` (exception inside the controller),
      plus the **error message** and **stack trace**.

3. **Common causes** after seeding:

    - **Missing column**: e.g. "Unknown column 'xyz' in 'field list'" → run `php artisan migrate` or add the column.
    - **Missing table**: e.g. "Table 'db.client_preference_additional' doesn't exist" → run migrations.
    - **Class or file not found**: e.g. "Class 'X' not found" → run `composer dump-autoload` or deploy the missing file.
    - **Storage/S3 or .env**: e.g. "Unable to locate bucket" or "AWS_ACCESS_KEY_ID" → fix `.env` or disable S3 for public assets if not used.

4. **Temporarily show errors in the browser** (only for debugging): In `.env` set `APP_DEBUG=true`, then reload the page. You will see the exception and trace on screen. Set `APP_DEBUG=false` again when done.

## Summary

| Item              | localhost                              | drivarr.com (after deploy)          |
| ----------------- | -------------------------------------- | ----------------------------------- |
| Shimmer CSS       | Fixed (pre-compiled CSS)               | Same fix; no LESS in browser        |
| API host/routes   | Fixed (localhost routes + no redirect) | Uses domain routes; no URL mismatch |
| Slow getConfig    | Still possible                         | Same until backend is optimized     |
| Category redirect | Skipped on localhost                   | Works (route exists for domain)     |

Deploying to drivarr.com with the current fixes should resolve the “CSS not coming” and “API cancelled” issues **provided** production env and routes are correct and server/DB are healthy. Address `getConfig` and any other slow endpoints separately for better UX.
