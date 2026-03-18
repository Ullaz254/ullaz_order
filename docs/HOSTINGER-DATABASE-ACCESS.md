# Fixing "Access denied" and 500 errors when connecting to Hostinger MySQL

## What’s going wrong

The error:

```text
SQLSTATE[HY000] [1045] Access denied for user 'u714731071_drivarr_user'@'...' (using password: YES)
```

can mean either:

1. **IP not allowed** – your current IP is not in Hostinger’s “Remote MySQL” list, or  
2. **Wrong username or password** – the MySQL user or password in `.env` does not match the user in Hostinger (often the case once the IP is whitelisted and the error persists).

So:

- The **500 (and 404-style page)** is from the app failing when it uses the database (e.g. in `AppServiceProvider` or the home controller).
- It is **not** caused by empty tables. Empty tables would only give empty data, not “Access denied”.

## Fix: Allow your IP in Hostinger Remote MySQL

1. Log in to **Hostinger hPanel**.
2. Open **Databases** → **Remote MySQL** (or **MySQL Remote**).
3. In **“Create remote database connection”**:
   - **IP (IPv4 or IPv6):** enter the IP from the error: **`122.161.53.175`**  
     (If you develop from different places, add each IP, or use “Any Host” once you understand the security impact.)
   - **Database:** should already show `u714731071_drivarr_db`.
4. Click **Create**.

After saving, wait a minute and reload your site. The app should stop crashing and the 500/404 from this DB error should go away.

## If your IP changes (e.g. home vs office)

- Your ISP can change your IP. If the error comes back, check the new “Access denied” message for the new IP and add that IP in Remote MySQL the same way.
- Alternatively you can use **“Any Host”** so any IP can connect. That’s convenient but less secure; use only if you accept the risk.

## .env and password

Your `.env` should look like this for Hostinger:

- `DB_HOST=srv1565.hstgr.io`
- `DB_DATABASE=u714731071_drivarr_db`
- `DB_USERNAME=u714731071_drivarr_user`
- `DB_PASSWORD="Zallu3279@@@@"`  ← **use double quotes** if the password has `@`, `#`, or spaces.

If the IP is already whitelisted and you still get “Access denied”, the problem is usually the **password**:

1. In **Hostinger → Databases → phpMyAdmin** (or the DB user section), confirm or reset the password for `u714731071_drivarr_user`.
2. Put the **exact** same password in `.env` as `DB_PASSWORD="..."` (with quotes).
3. Run: `php artisan config:clear` then try again.

## See the exact DB error (local only)

When `APP_ENV=development` or `local`, you can open:

**http://localhost:8000/test-db**

That route tries to connect and returns a JSON message with the real MySQL error (e.g. “Access denied” and reason). Use it to confirm whether the issue is IP, user, or password. Remove or restrict this route in production.

## Empty tables

Empty tables do **not** cause “Access denied” or 500. Once the connection is allowed and the app boots, you can add one or more rows in each important table (e.g. via phpMyAdmin on Hostinger) so the UI shows data instead of empty lists.
