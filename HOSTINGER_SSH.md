# Hostinger deploy via SSH

Use this when you have SSH access. Fill the variables in **§ Variables to set** on the server.

---

## 1. SSH in and go to your web root

```bash
ssh u123456789@yourdomain.com
cd domains/yourdomain.com/public_html
# Or wherever your site root is (e.g. private_html or a subfolder)
```

---

## 2. Get the code

**Option A – Clone from GitHub (first time)**

```bash
git clone https://github.com/metatech-offical/VyoooBackend.git .
# Or clone into a folder, then point document root to that folder/public
```

**Option B – Already uploaded, just pull**

```bash
git pull origin main
```

---

## 3. Document root

- Your **domain’s document root** must be the **`public`** folder of this project.  
- Example: if the repo is at `~/domains/yourdomain.com/vyooo`, set document root to `vyooo/public`.  
- In Hostinger: **Domains** → your domain → **Document root** → set to `.../VyoooBackend/public` (or your folder name + `/public`).

---

## 4. Install PHP dependencies

```bash
cd /path/to/your/project/root   # same folder as artisan
composer install --no-dev --optimize-autoloader
```

If `composer` is not in PATH, use:

```bash
php /path/to/composer.phar install --no-dev --optimize-autoloader
```

---

## 5. Create `.env` and set variables

```bash
cp .env.example .env
php artisan key:generate --show
# Copy the key, then edit .env (see below)
nano .env
# or: vi .env
```

Use the variable list in **§ Variables to set** below. At minimum set: **APP_KEY**, **APP_URL**, **DB_***, **RC_PROJECT_ID**, **RC_KIT_API_KEY**, **SESSION_SECURE_COOKIE=true**.

---

## 6. Storage link and migrations

```bash
php artisan storage:link
php artisan migrate --force
```

**If `php artisan storage:link` fails** (e.g. "Call to undefined function symlink()" – common on Hostinger shared hosting because PHP’s `symlink()` is disabled), create the link manually via SSH:

```bash
cd /home/u199293942/domains/vyooo.com/public_html/AppCrm/public
ln -s ../storage/app/public storage
```

If `ln -s` is not allowed, create `public/storage` as a normal folder and copy the contents once (uploads will still go to `storage/app/public`; you may need to sync or copy new files periodically):

```bash
cp -r ../storage/app/public public/storage
```

If you imported **vyooo_database.sql** (or shortzz_database.sql) instead of migrations, you can skip `migrate`.

---

## 7. Permissions (if you get 500 or write errors)

```bash
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
# Some hosts want 775 for storage and bootstrap/cache
```

---

## 8. Google credentials (for dashboard)

Upload your Firebase service account JSON to the **project root** (same folder as `artisan`) as:

```text
googleCredentials.json
```

Via SSH you can paste content:

```bash
nano googleCredentials.json
# Paste the JSON, save and exit
```

---

## 9. Login 500 / "The MAC is invalid"

If login returns 500 and the log says **DecryptException** or **The MAC is invalid**, the admin password in the database was encrypted with a different **APP_KEY** than the one in your server `.env`.

**Fix (pick one):**

1. **Use the same APP_KEY**  
   If you seeded the DB on your local machine, copy the same `APP_KEY` from your local `.env` into the server `.env`, then clear config cache:  
   `php artisan config:clear && php artisan config:cache`

2. **Reset the admin password on the server**  
   So it is encrypted with the current APP_KEY:
   - Use the app’s **Forgot password** flow (it needs DB username/password from `.env`), or  
   - Use the Artisan command (works when tinker cannot run, e.g. shared hosting):
     ```bash
     php artisan admin:set-password YourNewPassword
     ```
     Then log in with `admin` / `YourNewPassword`.  
     For another user: `php artisan admin:set-password YourNewPassword --user=username`.

---

## 10. Cron (optional)

Add crons in Hostinger (Cron Jobs) or via `crontab -e`:

```bash
0,30 * * * * curl -s "https://yourdomain.com/api/cron/reGeneratePlaceApiToken"
0 0 * * * curl -s "https://yourdomain.com/api/cron/deleteExpiredStories"
0 0 * * * curl -s "https://yourdomain.com/api/cron/deleteOldNotifications"
0 0 * * * curl -s "https://yourdomain.com/api/cron/countDailyActiveUsers"
```

Replace `https://yourdomain.com` with your real domain.

---

# Variables to set (`.env` on server)

Copy this block into `.env` on the server and replace the placeholders.

**Required – you must set these:**

```env
APP_NAME=Vyooo
APP_ENV=production
APP_KEY=PASTE_OUTPUT_OF_php_artisan_key:generate_--show
APP_DEBUG=false
APP_URL=https://yourdomain.com/

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_cpanel_db_name
DB_USERNAME=your_cpanel_db_user
DB_PASSWORD=your_cpanel_db_password

RC_PROJECT_ID=your_revenuecat_project_id
RC_KIT_API_KEY=your_revenuecat_secret_api_key

NOTIFICATION_TOPIC=vyooo

FILES_STORAGE_LOCATION=PUBLIC

SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
```

**Optional but recommended:**

```env
LOG_CHANNEL=stack
LOG_LEVEL=error
SESSION_LIFETIME=120
BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DRIVER=public
FILESYSTEM_CLOUD=local
```

**If you use AWS S3 for storage instead of server disk:**

```env
FILES_STORAGE_LOCATION=AWSS3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket
AWS_ITEM_BASE_URL=https://your-bucket-url/
```

**If you use DigitalOcean Spaces:**

```env
FILES_STORAGE_LOCATION=DOSPACE
DO_SPACE_ACCESS_KEY_ID=...
DO_SPACE_SECRET_ACCESS_KEY=...
DO_SPACE_REGION=...
DO_SPACE_BUCKET=...
DO_SPACE_ENDPOINT=...
DO_SPACE_URL=...
```

**Mail (only if you send mail from the app):**

```env
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=...
MAIL_FROM_NAME="${APP_NAME}"
```

**Reminder**

- **APP_URL** must end with `/`.
- **SESSION_SECURE_COOKIE** must be **true** on HTTPS.
- **APP_KEY** must be from `php artisan key:generate --show` (one key per app).
