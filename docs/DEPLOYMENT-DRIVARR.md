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
   ```bash
   php artisan db:seed --class=HomepageMinimalSeeder
   ```
   This inserts only when a table is empty:
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

## Summary

| Item              | localhost                              | drivarr.com (after deploy)          |
| ----------------- | -------------------------------------- | ----------------------------------- |
| Shimmer CSS       | Fixed (pre-compiled CSS)               | Same fix; no LESS in browser        |
| API host/routes   | Fixed (localhost routes + no redirect) | Uses domain routes; no URL mismatch |
| Slow getConfig    | Still possible                         | Same until backend is optimized     |
| Category redirect | Skipped on localhost                   | Works (route exists for domain)     |

Deploying to drivarr.com with the current fixes should resolve the “CSS not coming” and “API cancelled” issues **provided** production env and routes are correct and server/DB are healthy. Address `getConfig` and any other slow endpoints separately for better UX.
