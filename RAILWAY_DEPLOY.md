# Deploy Shortzz Backend on Railway

Use this checklist to deploy and run the project successfully on Railway.

---

## 1. Create project and connect repo

- Create a **New Project** on [Railway](https://railway.com/new).
- **Deploy from GitHub repo** → select `metatech-offical/VyoooBackend`.
- Add a **MySQL** database: in the project, click **+ New** → **Database** → **MySQL**. Railway will create a MySQL service and expose variables (e.g. `MYSQL_URL` or `MYSQLPUBLICURL`).

---

## 2. Set environment variables

In your **service** (the app, not the database) → **Variables** → **Raw Editor**, add at least:

### Required (copy from your local `.env` or set new)

| Variable | Example / Note |
|----------|----------------|
| `APP_NAME` | `Shortzz` (no spaces) |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | Run `php artisan key:generate --show` locally and paste the key |
| `APP_URL` | **Your Railway app URL** (e.g. `https://your-app.up.railway.app/`) — **must end with `/`** |

### Database (from Railway MySQL)

Railway MySQL often gives a URL. Use either:

- **Option A – URL:**  
  `DATABASE_URL` = the MySQL URL Railway provides (e.g. `mysql://root:password@host:port/railway`).

- **Option B – Separate vars:**  
  Set from the MySQL service variables Railway shows:
  - `DB_CONNECTION` = `mysql`
  - `DB_HOST` = MySQL host
  - `DB_PORT` = `3306`
  - `DB_DATABASE` = database name
  - `DB_USERNAME` = username
  - `DB_PASSWORD` = password

### Logging (recommended for Railway)

| Variable | Value |
|----------|--------|
| `LOG_CHANNEL` | `stderr` |
| `LOG_LEVEL` | `debug` or `error` |

### App-specific (from your .env)

| Variable | Note |
|----------|------|
| `NOTIFICATION_TOPIC` | e.g. `shortzz` |
| `RC_PROJECT_ID` | RevenueCat project ID |
| `RC_KIT_API_KEY` | RevenueCat secret API key |

### File storage (important on Railway)

Railway’s filesystem is **ephemeral** (uploads disappear on redeploy). For production:

- Set **`FILES_STORAGE_LOCATION`** = **`AWSS3`** (or `DOSPACE`) and configure the corresponding variables (e.g. `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_ITEM_BASE_URL`), **or**
- If you keep **`PUBLIC`** for a quick test, language CSV and uploads will work only until the next deploy; then use S3/Spaces for real use.

### Session (for HTTPS)

| Variable | Value |
|----------|--------|
| `SESSION_SECURE_COOKIE` | `true` |

### Optional (leave default if not used)

- `BROADCAST_DRIVER`, `CACHE_DRIVER`, `QUEUE_CONNECTION`, `SESSION_DRIVER`, `FILESYSTEM_DRIVER`, `FILESYSTEM_CLOUD`
- Mail, Redis, Pusher, AWS/DO if not using

---

## 3. Build and deploy settings

In the **app service** (not MySQL):

### Build

- **Build Command:** leave default (Railway will detect Laravel/PHP and run Composer), or set to:
  ```bash
  composer install --no-dev --optimize-autoloader
  ```

### Pre-deploy (run before each deploy)

So migrations and storage link run on every deploy:

**Pre-Deploy Command:**

```bash
php artisan migrate --force && php artisan storage:link
```

- If your MySQL URL is in `DATABASE_URL`, Laravel will use it automatically.
- `storage:link` creates the `public/storage` symlink (needed for language CSVs and any public disk files).

### Start command

- Leave **default** so Railway uses Nixpacks’ PHP/Laravel start (serves from `public/` on port 8080).  
- Or set explicitly if needed:
  ```bash
  php artisan serve --host=0.0.0.0 --port=8080
  ```
  (Railway may expect port from `PORT` env; Nixpacks usually sets this.)

---

## 4. Google / Firebase credentials

The dashboard expects **`googleCredentials.json`** in the project root (Firebase service account JSON). It is **not** in the repo (for security).

Options:

- **A) Build-time file:** Add the JSON as a **secret file** or inject it in the build (e.g. from an env var that contains the JSON string) and write it to `googleCredentials.json` in the Pre-Deploy or a custom build step.  
- **B) Runtime env:** If you change the code to read credentials from an env var (e.g. `GOOGLE_CREDENTIALS_JSON`), set that variable in Railway with the full JSON string.

Until this file (or env) is set, the **dashboard** may show an error; the **API** and **login** can still work.

---

## 5. Generate domain

- In the **app service** → **Settings** → **Networking** → **Generate Domain**.
- Copy the URL (e.g. `https://vyooobackend-production-xxxx.up.railway.app`).
- Set **`APP_URL`** in Variables to that URL **with trailing slash** (e.g. `https://vyooobackend-production-xxxx.up.railway.app/`).
- Redeploy so the app uses the new `APP_URL`.

---

## 6. After first deploy

1. Open **`APP_URL`** in the browser → you should see the **login** page.
2. Log in with admin (e.g. **admin** / **admin123** if you’ve seeded that).
3. If the dashboard shows “googleCredentials.json does not exist” or invalid JSON, add the file or env as in **§4**.
4. If you use **cron** (e.g. reGeneratePlaceApiToken, deleteExpiredStories): add a **Cron** or **Worker** service that runs your cron commands on schedule (see Railway docs on Cron jobs).

---

## 7. Quick checklist

- [ ] Project created and GitHub repo connected.
- [ ] MySQL database added and DB variables (or `DATABASE_URL`) set.
- [ ] All required env vars set (especially `APP_KEY`, `APP_URL`, `DB_*` or `DATABASE_URL`).
- [ ] `APP_URL` ends with `/` and uses your Railway domain.
- [ ] `LOG_CHANNEL=stderr` for Railway logs.
- [ ] Pre-Deploy: `php artisan migrate --force && php artisan storage:link`.
- [ ] For production uploads: `FILES_STORAGE_LOCATION=AWSS3` (or DOSPACE) and credentials set.
- [ ] Domain generated and set in `APP_URL`.
- [ ] `googleCredentials.json` (or equivalent) set if you need the dashboard.
- [ ] Session cookie: `SESSION_SECURE_COOKIE=true` for HTTPS.

---

## 8. Optional: Cron jobs

If your app uses cron (e.g. from the Shortzz doc):

- Use Railway **Cron** (or a separate service with a cron process) to hit:
  - `https://YOUR_APP_URL/api/cron/reGeneratePlaceApiToken`
  - `https://YOUR_APP_URL/api/cron/deleteExpiredStories`
  - etc.  
- Set the same env vars for the cron service as for the app (so URLs and keys work).

---

## Troubleshooting

- **500 / white screen:** Check **Logs** in Railway; often missing `APP_KEY` or DB connection. Set `APP_DEBUG=true` temporarily to see errors (then set back to `false`).
- **DB connection refused:** Ensure DB vars (or `DATABASE_URL`) point to Railway’s MySQL (use **private** MySQL host from Railway if available).
- **CSV / storage 404:** Run `php artisan storage:link` in Pre-Deploy; for persistent files use S3/Spaces and `FILES_STORAGE_LOCATION=AWSS3`.
- **Login redirect / session:** Use `APP_URL` with trailing slash and `SESSION_SECURE_COOKIE=true` on HTTPS.
