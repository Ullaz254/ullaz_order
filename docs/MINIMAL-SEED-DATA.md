# Minimal seed data to unblock UI (home page)

If the home page returns **500 Internal Server Error** or **404 Page not found**, it is usually because required tables are empty. The app expects at least **one row** in each of the tables below so that the home page and basic UI can render.

---

## Quick fix: run the seeder (recommended)

**Tables already exist** (migrations were run) but **data is missing**. From the project root run:

```bash
php artisan db:seed --class=MinimalUiSeeder --force
```

This inserts one row into `languages`, `clients`, `client_preferences`, and `client_languages` **only when each table is empty**. Safe to run multiple times. Then reload `http://localhost:8000/`.

---

## Manual fix: raw SQL (e.g. Hostinger phpMyAdmin)

If you prefer to add rows by hand, add the following **in order**. You can run these in **Hostinger phpMyAdmin** or any MySQL client connected to your DB.

---

## 1. `languages` (required for nav / translations)

```sql
INSERT INTO languages (id, sort_code, name, nativeName, created_at, updated_at)
VALUES (1, 'en', 'English', 'English', NOW(), NOW());
```

---

## 2. `clients` (required – home page and AppServiceProvider)

Use a single client row with a unique `code`. All other optional FKs can be NULL.

```sql
INSERT INTO clients (
  id, name, email, phone_number, password, code,
  is_deleted, is_blocked, status, created_at, updated_at
) VALUES (
  1,
  'Drivarr Client',
  'admin@drivarr.com',
  NULL,
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- password: password
  'drivarr',
  0,
  0,
  1,
  NOW(),
  NOW()
);
```

- **code** must be unique (e.g. `drivarr`). You will use this same value as `client_code` in `client_preferences` and `client_languages`.
- If your table has `country_id` or `language_id` as NOT NULL, set them to an existing ID or alter the column to allow NULL for this seed.

---

## 3. `client_preferences` (required – home page crashes without it)

At least one row with `client_code` matching `clients.code`. Enable at least one vendor type (e.g. `delivery_check = 1`) so the home page has a valid mode.

```sql
INSERT INTO client_preferences (
  id,
  client_code,
  is_hyperlocal,
  Default_latitude,
  Default_longitude,
  delivery_check,
  dinein_check,
  takeaway_check,
  rental_check,
  pick_drop_check,
  on_demand_check,
  laundry_check,
  appointment_check,
  p2p_check,
  created_at,
  updated_at
) VALUES (
  1,
  'drivarr',
  0,
  28.5355,
  77.3910,
  1,
  0,
  0,
  0,
  0,
  0,
  0,
  0,
  0,
  NOW(),
  NOW()
);
```

- **client_code** must equal the `code` you used in `clients` (e.g. `drivarr`).
- **is_hyperlocal**: `0` = use default lat/long; `1` = hyperlocal (location-based).
- **Default_latitude** / **Default_longitude**: used when the user has no location (e.g. 28.5355, 77.3910 for Delhi NCR).
- Set **one** of the `*_check` columns to `1` (e.g. `delivery_check = 1`). If your schema has `car_rental_check`, add it with value `0` in the INSERT.

---

## 4. `client_languages` (required for category nav – avoids null `$primary`)

Category navigation uses `ClientLanguage::orderBy('is_primary','desc')->first()`. If this is null, the next use of `$primary->language_id` causes a 500. Add one row linking your client to the language you inserted.

```sql
INSERT INTO client_languages (client_code, language_id, is_primary, is_active, created_at, updated_at)
VALUES ('drivarr', 1, 1, 1, NOW(), NOW());
```

- **client_code** must match `clients.code`.
- **language_id** must match `languages.id` (e.g. 1).

---

## Optional but useful for full UI

These are not strictly required for the home page to load, but they help avoid other 500s or empty sections:

| Table | Purpose | Minimal row |
|-------|--------|-------------|
| **client_preference_additional** | Key-value preferences (e.g. on-demand pricing) | Can stay empty; code often treats “no row” as default. |
| **client_currencies** | Currency for prices | One row with `client_code`, `currency_id` (requires `currencies`), `is_primary = 1`, `doller_compare`. |
| **currencies** | If you add client_currencies | One row with `id`, `name`, `code`, `symbol`. |
| **types** | Category types (delivery, dine-in, etc.) | One or more rows so categories can have a `type_id`. |
| **categories** | Home page category list | One row so the nav has at least one category. |
| **category_translations** | Category names per language | One row per category with `category_id`, `language_id`, `name`. |
| **vendors** | Vendors in service area | One row if the home page or nav filters by vendors. |

---

## Order summary

1. **languages** (id = 1)
2. **clients** (id = 1, code = `drivarr`)
3. **client_preferences** (id = 1, client_code = `drivarr`, at least one `*_check` = 1)
4. **client_languages** (client_code = `drivarr`, language_id = 1, is_primary = 1)

After inserting these, reload `http://localhost:8000/`. If you still get 500, check `storage/logs/laravel.log` for the exact error and table/column involved.

---

## If you prefer not to add data yet

The code now returns **503** with a clear message when `client_preferences` is missing, instead of a 500. You will see:

- **“Setup required: Please add a client and client preferences in the database. See docs/MINIMAL-SEED-DATA.md.”**

So you can either add the minimal rows above or use that message as a reminder to run the seed.
