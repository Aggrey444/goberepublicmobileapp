# GOBE Republic — Deployment Guide

This guide covers deploying to **Hostinger Business (shared hosting, no VPS)** for the backend,
and building the Expo mobile app with EAS. It is written for the production environment:

- `APP_ENV=production`, `APP_DEBUG=false`
- **MySQL** database (Hostinger), not SQLite
- Web server serves only `backend/public`
- All secrets via environment variables, never committed

---

## 1. Repo layout

```
gobe-republic/
├── backend/   # Laravel 12 API + Blade admin dashboard
├── mobile/    # Expo SDK 57 (React Native) customer app
└── docs/      # This guide + API reference
```

---

## 2. Deploy the Laravel backend to Hostinger

### 2.1 Prepare the package locally

```bash
cd backend
composer install --no-dev --optimize-autoloader
```

Configure production values in a **local copy** of `.env` (see section 3) but do **not** ship
`.env` changes to the repo. The final `.env` lives only on the server.

### 2.2 Create the Hostinger sub-domain (e.g. `api.goberepublic.com`)

In Hostinger's hPanel:
1. **Domains → Subdomains** → add `api` pointing to `public_html/api`.
2. This creates `public_html/api/public_html` — a common confusing layout.
   The Laravel web root must be the `backend/public` directory.

### 2.3 Upload

Upload the `backend` directory contents to `public_html/api/` via FTP/SFTP/File Manager.

The key requirement: **the server must serve `backend/public/index.php`**.
Two supported layouts:

**Option A (recommended) — put Laravel below docroot and symlink `public`:**
```
public_html/api/
├── app/  bootstrap/  config/  database/  routes/  ...   (Laravel root)
└── public_html/                                            (symlink -> public/)
```
From hPanel File Manager create a **symbolic link** named `public_html` inside
`public_html/api/` that points to `public_html/api/public`.

**Option B — move `public` contents to docroot:**
Copy the contents of `backend/public` into `public_html/api/` and adjust relative paths
in `index.php` to point one level up (e.g. `require __DIR__.'/../vendor/autoload.php';`).

> Ask Hostinger support if you cannot create symlinks on your plan.

### 2.4 Server configuration (htaccess)

`backend/public/.htaccess` (already shipped) enables `public/index.php` as the front
controller. Ensure `mod_rewrite` is on (default on Hostinger).

### 2.5 Environment + storage permissions

Create `public_html/api/.env` with the production values (section 3), then:

```bash
cd public_html/api
php artisan key:generate
php artisan storage:link            # creates public/storage -> storage/app/public
chmod -R 775 storage bootstrap/cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --force          # creates the first admin + categories + products
```

> Re-run `config:cache`/`route:cache` after any code change.

### 2.6 Confirm

- `https://api.goberepublic.com/api/v1/products` returns JSON.
- `https://api.goberepublic.com/admin/login` loads the dashboard.

---

## 3. Production `.env` (backend)

```dotenv
APP_NAME="GOBE Republic"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.goberepublic.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456789_gobe
DB_USERNAME=u123456789_gobe
DB_PASSWORD=super-secret

# Admin web dashboard seeder credentials (set once before seeding)
ADMIN_EMAIL=admin@gobe.com
ADMIN_PASSWORD=change-me-now

# Paystack (leave empty until you configure payments)
PAYSTACK_PUBLIC_KEY=
PAYSTACK_SECRET_KEY=
PAYSTACK_BASE_URL=https://api.paystack.co

# Optional Expo push access token (Expo Push API)
EXPO_ACCESS_TOKEN=

FILESYSTEM_DISK=local
```

Create the MySQL database + user in hPanel → **Databases → MySQL Databases**.

---

## 4. Redis / queues note

Hostinger Business does **not** include Redis. The Expo push HTTP call in
`App\Services\PushNotificationService` runs synchronously, which is fine at low volume.
If you later add queue workers, use the database driver (`QUEUE_CONNECTION=database`)
and a cron job calling `php artisan queue:work --once`.

---

## 5. Mobile app build (EAS)

The mobile app talks to the backend via `EXPO_PUBLIC_API_URL`.

### 5.1 Set the API URL

Create `mobile/.env`:

```dotenv
EXPO_PUBLIC_API_URL=https://api.goberepublic.com
```

### 5.2 Build

```bash
cd mobile
npm install
npx eas login
npx eas build:configure
npx eas build --platform android --profile production
npx eas build --platform ios --profile production   # requires Apple Developer account
```

### 5.3 Push notifications

Push uses **Expo push tokens** (`ExpoPushToken[...]`), which require:

- `expo-notifications` (already included)
- A physical device or emulator — push tokens do not work on the Expo web/Go mock.
- Android: `projectId` from `app.json` → `extra.eas.projectId` (set automatically by `eas build:configure`).

Devices call `POST /api/v1/device-tokens` (with `{ token }`) which the backend uses to send
notifications via the Expo Push API.

---

## 6. Production checklist

- [ ] `APP_DEBUG=false`
- [ ] MySQL database created and `.env` updated
- [ ] `php artisan config:cache && route:cache`
- [ ] `php artisan migrate --force && db:seed --force`
- [ ] Admin credentials changed after first login
- [ ] `storage:link` created, storage dirs writable
- [ ] Backend reachable at `https://api.goberepublic.com`
- [ ] Mobile `EXPO_PUBLIC_API_URL` points to the production backend
- [ ] HTTPS enforced (Hostinger SSL on by default for subdomains)
