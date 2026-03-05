# Deployment to drivarr.com – Checklist

## What was fixed for localhost (and production impact)

### CSS (shimmer) – fixed
- **Issue:** `shimmer-less.less` was loaded with `rel="stylesheet/less"`, so the browser compiled LESS at runtime (~70+ seconds), making the page look unstyled.
- **Fix:** Pre-compiled CSS added as `public/front-assets/css/shimmer-less.css`. Templates now use this file with `rel="stylesheet"`.
- **On drivarr.com:** Same fix applies. No client-side LESS; CSS loads immediately.

### API (cancelled / pending) – localhost vs drivarr.com
- **On localhost:** Homepage APIs (`homePageDataCategoryMenu`, `homePageDataNew`, `cartProducts`) were cancelled or pending because:
  1. Redirect to category page was firing on localhost and aborting in-flight requests (fixed: redirect skipped on localhost in JS).
  2. Typo in `location.js` (`selected_address` split across lines) caused a JS error (fixed).
- **On drivarr.com:**
  - Domain-based routes will be used; API base URL will match the site (no localhost vs drivarr mismatch).
  - If `getConfig` or other endpoints are slow (e.g. 14+ seconds), that will still happen on production until backend/DB is optimized.
  - Ensure `APP_URL` and `Main_Domain` (or equivalent) in `.env` on drivarr.com are set to the production URL.

### Controllers
- **UserhomeController:** Redirect to `categoryDetail` is skipped when host is localhost so the homepage loads. On drivarr.com the redirect runs as before (route exists in domain group).

## Before deploying to drivarr.com

1. **Environment**
   - Set `APP_URL` (and any domain config) to `https://drivarr.com` (or your production URL).
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

## Summary

| Item              | localhost                         | drivarr.com (after deploy)        |
|-------------------|-----------------------------------|-----------------------------------|
| Shimmer CSS       | Fixed (pre-compiled CSS)          | Same fix; no LESS in browser      |
| API host/routes   | Fixed (localhost routes + no redirect) | Uses domain routes; no URL mismatch |
| Slow getConfig    | Still possible                    | Same until backend is optimized   |
| Category redirect | Skipped on localhost              | Works (route exists for domain)   |

Deploying to drivarr.com with the current fixes should resolve the “CSS not coming” and “API cancelled” issues **provided** production env and routes are correct and server/DB are healthy. Address `getConfig` and any other slow endpoints separately for better UX.
