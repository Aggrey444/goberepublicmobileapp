# GOBE Republic — Deployment Guide

This guide covers deploying to **Hostinger Business (shared hosting, no VPS)** for the backend,
and building the Expo mobile app with EAS. It is written for the production environment:

- `APP_ENV=production`, `APP_DEBUG=false`
- **MySQL** database (Hostinger), not SQLite
- Web server serves only `public/`
- All secrets via environment variables, never committed

---

## 1. Repo layout

```
gobe-republic/                  # Laravel lives at the repo ROOT (good for Hostinger Git deploy)
├── app/  bootstrap/  config/  database/  public/  routes/  ...   # Laravel backend + Blade admin
├── mobile/    # Expo SDK 57 (React Native) customer app
└── docs/      # This guide + API reference
```

Because Laravel is at the repo root, Hostinger's Git integration can deploy it straight to a
document root: the web-app content (the `public/` dir) sits at `<webroot>/public/index.php`.

---

## 2. Deploy the Laravel backend to Hostinger

### 2.1 Prepare the package locally

```bash
# from repo root
composer install --no-dev --optimize-autoloader
```

Configure production values in a **local copy** of `.env` (see section 3) but do **not** ship
`.env` changes to the repo. The final `.env` lives only on the server.

### 2.2 Create the Hostinger sub-domain

In Hostinger's hPanel: **Domains → Subdomains** → add e.g. `api`
(or `shop` if the backend doubles as the web app) pointing to a folder you choose, e.g.
`public_html/api`. This becomes the deploy target.

### 2.3 Deploy via Hostinger Git integration (recommended)

Because **Laravel + its `public/` dir are at the repo root**, Git integration works cleanly:

1. Push the repo to GitHub (done — `https://github.com/Aggrey444/goberepublicmobileapp`).
2. In hPanel → **Websites → (your site) → Git** → connect the repo, select the deploy branch
   (e.g. `master`), and set the **deploy path** to the sub-domain folder (e.g. `public_html/api`).
3. The pull places `app/`, `bootstrap/`, `config/`, … `public/` directly under that folder.

Confirm the web root points at `.../api/public/index.php`:

- hPanel **Websites → (site) → Manage → Files**, or **Domains → Subdomains → (subdomain) →
  Document root** set to the `public` directory. On Hostinger, set the sub-domain's document
  root to a subfolder such that `public/index.php` is the front controller:
  `public_html/api/public`.

If your plan won't point a sub-domain document root into a subfolder, use the symlink or
docroot-copy approach under **2.4**.

### 2.4 Alternative (no Git integration): upload + make `public` the web root

Upload the repo (Laravel is at the root) into `public_html/api/`. Then ensure the server serves
`.../api/public/index.php`:

- **Preferred:** point the sub-domain document root at `.../api/public`.
- **Or symlink:** from hPanel File Manager, create a symbolic link named `public_html` inside
  `public_html/api/` pointing to `public_html/api/public`. Ask Hostinger support if symlinks
  aren't available.

`public/.htaccess` (already shipped) enables `public/index.php` as the front controller.
Ensure `mod_rewrite` is on (default on Hostinger).

### 2.5 Environment + storage permissions

Create `.env` at the Laravel root (`public_html/api/.env`) with production values (section 3), then:

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

> Re-run `config:cache`/`route:cache` after any code change, and `git pull` + the caches after
> each Hostinger Git sync.

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
