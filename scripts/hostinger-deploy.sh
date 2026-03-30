#!/usr/bin/env bash
# Deploy ullaz_order to Hostinger (or similar) over SSH.
# Usage: from project root on the server: bash scripts/hostinger-deploy.sh
set -euo pipefail

APP_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$APP_ROOT"

php -v
if command -v composer >/dev/null 2>&1; then
  composer install --no-dev --optimize-autoloader
fi

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

echo "Deploy steps finished. Smoke test:"
echo "  - /client/customize"
echo "  - /client/app-styling"
echo "  - Marketing > Banners (Web + Mobile)"
echo "Maps: open a page with map; DevTools Network should load maps.googleapis.com (200)."
