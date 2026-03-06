# Run Laravel from public_html (when you can't change document root)

Use this only if **you cannot** set the domain document root to `laravel_app/public` and the file manager won't let you move files between `public_html` and `laravel_app`.

---

## Option A: Copy in File Manager (no move)

1. **Open File Manager** in hPanel (Files → File Manager).

2. **Go to your home folder** (click "My files" or the path that shows `u714731071`). You should see:

    - `domains` (and inside: `drivarr.com` → `public_html`)
    - `laravel_app`

3. **Copy Laravel’s public contents into public_html**

    - Open **`laravel_app/public`**.
    - Select **all** files and folders inside it (e.g. `index.php`, `.htaccess`, `css`, `js`, `front-assets`, etc.).
    - Use **Copy** (not Move).
    - Go to **`domains/drivarr.com/public_html`** (or wherever your site’s `public_html` is).
    - **Paste**. Overwrite if asked.

4. **Replace `public_html/index.php`** so it loads Laravel from `laravel_app`:

    - In `public_html`, open **`index.php`** and **edit** it.
    - Replace the **whole content** with the contents of the file below (from "Laravel public_html index.php").
    - Save.

5. **.htaccess**
    - If `public_html` already has an `.htaccess`, keep it. If you copied one from `laravel_app/public`, that’s fine. It should contain the usual Laravel rewrite rules.

After this, the site will run from `public_html` but use the app in `laravel_app`. No need to move anything from `public_html` to `public`.

---

## Option B: Use SSH (if File Manager doesn’t show laravel_app)

SSH in and run:

```bash
# Go to domain’s public_html (path may vary)
cd /home/u714731071/domains/drivarr.com

# Backup current public_html
mv public_html public_html_old

# Make public_html point to Laravel’s public folder
ln -s /home/u714731071/laravel_app/public public_html
```

Then the web root is effectively `laravel_app/public`. No copying or moving files in the file manager.

---

## Laravel public_html index.php (for Option A)

Use this **only** when you copied the contents of `laravel_app/public` into `public_html` and the app lives at `/home/u714731071/laravel_app`. If your `laravel_app` path is different, change the path in the two `require` lines.

```php
<?php
define('LARAVEL_START', microtime(true));

// Bootstrap Laravel from laravel_app (one level up from domains/drivarr.com, then laravel_app)
require __DIR__.'/../../../laravel_app/vendor/autoload.php';
$app = require_once __DIR__.'/../../../laravel_app/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$response->send();
$kernel->terminate($request, $response);
```

**If your path is different:** If `public_html` is at `domains/drivarr.com/public_html` and `laravel_app` is in your home folder, use `../../../laravel_app`. If Laravel is elsewhere, adjust the path in the two `require` lines.
