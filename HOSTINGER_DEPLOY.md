# Deploy Shortzz Backend on Hostinger (cPanel)

Follow these steps and the official Shortzz documentation for a successful deploy.

---

## 1. Before you upload

### On your computer (optional but recommended)

- Set **production** values in `.env` (or create `.env.production`) so you know what to put on the server:
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `APP_URL=https://yourdomain.com/` (your Hostinger domain, **with trailing slash**)
  - `SESSION_SECURE_COOKIE=true` (for HTTPS)
- Run `php artisan key:generate --show` and save the key for the server `.env`.
- Do **not** upload `.env` to GitHub. On the server you will create `.env` manually.

### What to upload

- Upload the **entire project** (including `vendor/` if you ran `composer install` locally), **or**
- Upload the project **without** `vendor/` and run `composer install --no-dev` on the server (SSH or cPanel Terminal).

**Do not upload:** `.env`, `node_modules/`, `.git/` (optional).  
**Must be on server:** `vendor/` (either uploaded or installed on server).

---

## 2. Document root (important)

Laravel must be served from the **`public`** folder so that:
- The URL is `https://yourdomain.com/` (not `https://yourdomain.com/public/`).
- `.env` and code are **not** inside the web root (more secure).

**Option A – Recommended: point domain to `public`**

- In cPanel: **Domains** → your domain → **Document Root** (or **Root Directory**).
- Set it to the folder that **contains** `index.php` and `storage` symlink, i.e. the **`public`** folder of your project.  
  Example: if the project is at `home/username/shortzz_backend`, set document root to `shortzz_backend/public`.

**Option B – Document root is the project root**

- If you cannot change the document root and it points to the project root (e.g. `shortzz_backend`), the existing **root `.htaccess`** will forward requests to `public/` and `server.php`. Ensure the root `.htaccess` is present (it is in the repo).

---

## 3. Database (cPanel)

1. **MySQL Database**
   - cPanel → **MySQL® Databases**.
   - Create a database (e.g. `username_shortzz`).
   - Create a user and password; add the user to the database with **ALL PRIVILEGES**.

2. **Import schema**
   - cPanel → **phpMyAdmin** → select your database.
   - **Import** → choose `shortzz_database.sql` (from the Shortzz package) and run import.  
   - If you don’t have that file, run migrations on the server:  
     `php artisan migrate --force` (via SSH or cPanel Terminal).

3. **`.env` on server**
   - In the project root (one level above `public`), create or edit `.env`.
   - Set at least:

```env
APP_NAME=Shortzz
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_KEY_FROM_artisan_key:generate
APP_URL=https://yourdomain.com/

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

FILES_STORAGE_LOCATION=PUBLIC

SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true

# RevenueCat (from your current .env)
RC_PROJECT_ID=your_rc_project_id
RC_KIT_API_KEY=your_rc_kit_api_key

NOTIFICATION_TOPIC=shortzz
```

- Replace `yourdomain.com`, database name/user/password, and RevenueCat values.  
- **APP_URL must end with `/`.**

---

## 4. Storage link (language CSV & uploads)

- Run once on the server (SSH or cPanel Terminal, from project root):

```bash
php artisan storage:link
```

- If your host disallows symlinks, create a **symbolic link** manually:
  - From the **`public`** folder, create a link named `storage` pointing to `../storage/app/public`.  
- Or copy the contents of `storage/app/public` into `public/storage` (less ideal; you’d need to repeat for new uploads).

---

## 5. Google / Firebase credentials

- Upload your Firebase **service account JSON** to the **project root** (same folder as `artisan`) as **`googleCredentials.json`** (e.g. via File Manager or FTP).  
- The dashboard expects this file; without it the dashboard may show an error (API and login still work).

---

## 6. PHP version and extensions (cPanel)

- **PHP Version:** 8.1 or 8.2 (cPanel → **Select PHP Version** or **MultiPHP Manager**).
- Ensure extensions are enabled: `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`.

---

## 7. Cron jobs (cPanel)

- cPanel → **Cron Jobs**.
- Add the commands from the Shortzz doc (e.g. run every hour / daily):

```text
# Regenerate Place API token – twice per hour
0,30 * * * * curl --request GET 'https://yourdomain.com/api/cron/reGeneratePlaceApiToken'

# Delete expired stories – daily
0 0 * * * curl --request GET 'https://yourdomain.com/api/cron/deleteExpiredStories'

# Delete old notifications – daily
0 0 * * * curl --request GET 'https://yourdomain.com/api/cron/deleteOldNotifications'

# Daily active users – daily
0 0 * * * curl --request GET 'https://yourdomain.com/api/cron/countDailyActiveUsers'
```

- Replace `https://yourdomain.com` with your real domain.

---

## 8. Optional: server limits (Shortzz doc)

If the doc recommends it, in cPanel or `php.ini`:

- `memory_limit` = 500M  
- `upload_max_filesize` = 500M  
- `post_max_size` = 500M  
- `max_input_time` = 60  

---

## 9. After deploy

1. Open **https://yourdomain.com/** → you should see the **login** page.
2. Log in with admin (e.g. **admin** / **admin123** per the doc).
3. In **Settings** set **APP_URL** / base URL if the app has such a field (the backend uses `.env` `APP_URL` for storage URLs).
4. Test language CSV: open a language CSV URL (e.g. from App Languages) and confirm it downloads.

---

## Quick checklist

- [ ] Project uploaded (with `vendor/` or run `composer install` on server).
- [ ] Document root set to **`public`** (or root `.htaccess` in place if root is project root).
- [ ] MySQL database created and user assigned; schema imported or migrations run.
- [ ] `.env` created on server with `APP_KEY`, `APP_URL` (with `/`), `DB_*`, `SESSION_SECURE_COOKIE=true`, `APP_ENV=production`, `APP_DEBUG=false`.
- [ ] `php artisan storage:link` run (or symlink/copy as above).
- [ ] `googleCredentials.json` in project root.
- [ ] PHP 8.1+ and required extensions enabled.
- [ ] Cron jobs added for the four API cron URLs.
- [ ] Admin login and dashboard work; language CSV URL loads.

No code changes are required in the repo for Hostinger; only server setup and `.env` on the server.
