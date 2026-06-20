# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Drivarr (ullaz_order) is a large-scale multi-vendor e-commerce and order management platform built on **Laravel 8** with **Vue.js 2** frontend. It supports multi-tenant deployments, 17+ payment gateways, multiple delivery partners, real-time chat/notifications, and 10 languages including RTL.

## Commands

### PHP / Laravel
```bash
php artisan serve              # Start local dev server
php artisan migrate            # Run database migrations
php artisan migrate:rollback   # Rollback last migration batch
php artisan tinker             # Interactive REPL
php artisan queue:work         # Run queue worker
php artisan cache:clear        # Clear application cache
php artisan config:clear       # Clear config cache
php artisan route:list         # List all routes
```

### Frontend (Vue.js 2 + Laravel Mix)
```bash
npm run dev         # Development build
npm run watch       # Watch mode (auto-rebuild on change)
npm run hot         # Hot reload dev server
npm run prod        # Production build (minified)
```

### Testing
```bash
vendor/bin/phpunit                     # Run all tests
vendor/bin/phpunit --filter TestName   # Run a single test class or method
```

### Real-time Server (Socket.io)
```bash
node server.js    # Start the Socket.io server separately (required for chat/notifications)
```

## Architecture

### Route Organization
Routes are split across multiple files — **do not put new routes in `web.php` directly**:
- `routes/frontend.php` — Customer-facing pages (64KB, ~2000 lines)
- `routes/backend.php` — Vendor/admin panel (67KB, ~2000 lines)
- `routes/godpanel.php` — Super-admin features
- `routes/api.php` → `routes/v1/` and `routes/v2/` — REST API (v2 is newer/preferred)
- `routes/commonRoute.php` — Routes shared across panels
- `routes/images.php` — Image proxy/serving

### Controller Groups
Controllers are grouped by panel under `app/Http/Controllers/`:
- `Front/` — 116 controllers for customer-facing web routes
- `Client/` — 101 controllers for vendor/admin backend
- `Api/v1/` — 81 API controllers
- `Godpanel/` — Super-admin
- `Auth/` — Authentication (web + social login)

### Models
353 Eloquent models live flat in `app/Models/`. Soft deletes and auditing (`owen-it/laravel-auditing`) are widely used. Observers in `app/Observers/` handle model lifecycle hooks.

### Business Logic Placement
There are only 3 dedicated services (`app/Services/`): `CartCalculationService`, `FirebaseService`, and `InventoryService`. Most complex business logic lives in **controllers** and **traits** (`app/Http/Traits/` — 80+ traits). When adding significant logic, prefer a trait or extend an existing service rather than adding to controllers directly.

### The Helpers File
`app/helpers.php` (94KB) is a massive global utility file. Check it before writing a new helper function — it likely already exists here.

### Frontend
Vue 2 SPA components are compiled via Laravel Mix (Webpack). The Socket.io client connects to the separate `server.js` Node process for real-time features.

## Multi-Tenancy
The app supports multiple tenants via domain/subdomain routing. Middleware handles dynamic database switching per domain. Be careful when writing queries — the active database connection can change per request.

## Key Integrations
- **Payments**: 17+ gateways (Stripe, Razorpay, PayPal, etc.) — gateway logic is spread across models and controllers under `Client/Payment*`
- **Delivery partners**: Dunzo, Shiprocket, Lalamove, Roadie — webhooks are handled in dedicated controllers
- **Notifications**: Firebase FCM (`app/Services/FirebaseService.php`) + Socket.io
- **Search**: Algolia Scout (configured in `config/scout.php`)
- **Permissions**: Spatie Laravel Permission (`spatie/laravel-permission`)
- **Wallet**: Bavix (`bavix/laravel-wallet`)

## Environment
- The app uses `.env` for all secrets including DB credentials, payment keys, and Firebase config
- `STATIC_ASSETS_BASE_URL` controls the CDN base URL for images
- Image proxying is handled via Glide/Phumbor; URLs are built via helpers in `helpers.php`
- Localization is set per-request by middleware; 10 languages supported (`en`, `ar`, `fr`, `de`, `es`, `sv`, `vi`, `pt`, `sk`, `nl`)

## API Versioning
- v1 (`routes/v1/auth.php`, `routes/v1/guest.php`) — legacy, kept for backward compatibility
- v2 (`routes/v2/auth.php`, `routes/v2/guest.php`) — current, prefer for new API work
- API uses JWT authentication; web routes use Laravel session auth
