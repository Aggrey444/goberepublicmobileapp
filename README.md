# GOBE Republic — Food Ordering System

A full-stack e-commerce/ordering system for GOBE Republic with three parts:

- **repo root (Laravel)** — Laravel 12 + Sanctum REST API **and** a Laravel Blade admin dashboard
- **`mobile/`** — Expo SDK 57 (React Native) customer mobile app with Expo Router
- **`docs/`** — API reference (`API.md`) and Hostinger deployment guide (`DEPLOYMENT.md`)

> **Payments are intentionally not yet wired up** — see the Paystack integration notes below.

---

## Stack

| Layer     | Technology                                                        |
|-----------|-------------------------------------------------------------------|
| Backend   | Laravel 12.68, Sanctum 4.3.3, PHP 8.2                             |
| Database  | SQLite (local dev) / MySQL 8 (Hostinger production)               |
| Admin UI  | Laravel Blade (server-rendered at `/admin/login`)                 |
| Mobile    | Expo SDK 57, React Native 0.86, React 19, Expo Router, Zustand, axios |

---

## Features

### Customer app (mobile)
- Register / login (email or phone), profile
- Browse categories & products (search, filter, pagination)
- Cart (add / update quantity / remove / clear)
- Checkout with delivery information (order lifecycle + in-app notifications)
- Order history + order detail with status & payment status
- Push notification token registration + notification list
- Paystack checkout flow (UI present; server verification behind the payments flag)

### Admin dashboard (web)
- Login (session auth), dashboard stats
- Products & categories CRUD
- Customer management
- Order management with allowed status transitions
- Admin / staff user management

### Backend API
Verified by **44 PHPUnit feature tests** (137 assertions): auth, catalog, cart, orders,
notifications, device tokens, and the full admin API.

---

## Local setup (Windows)

### Backend (Laravel is at the repo root)

```bash
# from repo root
copy .env.example .env
# set DB_CONNECTION=sqlite, APP_URL=http://localhost:8000

composer install
php artisan key:generate
touch database/database.sqlite        # empty file
php artisan migrate --seed            # creates admin + categories + products
php artisan storage:link
php artisan serve                     # http://127.0.0.1:8000
```

- **Admin dashboard:** `http://127.0.0.1:8000/admin/login`
  - email `ADMIN_EMAIL` from `.env` (default `admin@gobe.com`)
  - password `ADMIN_PASSWORD` from `.env` (default `password`)
- **API base:** `http://127.0.0.1:8000/api/v1` (see `docs/API.md`)

### Tests

```bash
vendor/bin/phpunit
```

### Mobile

```bash
cd mobile
npm install
# create .env (copy .env.example)
EXPO_PUBLIC_API_URL=http://127.0.0.1:8000
npm start          # Expo dev server
```

- Android emulator uses `http://10.0.2.2:8000` automatically (see `constants/config.ts`).
- Physical device via Expo Go: set `EXPO_PUBLIC_API_URL` to your machine's LAN IP.

---

## Environment variables (backend `.env`)

| Variable             | Purpose                                   |
|----------------------|-------------------------------------------|
| `ADMIN_EMAIL`        | Admin seeder email                        |
| `ADMIN_PASSWORD`     | Admin seeder password                     |
| `PAYSTACK_*`         | Paystack keys (empty until payments on)   |
| `EXPO_ACCESS_TOKEN`  | Optional Expo push API access token       |
| `FILESYSTEM_DISK`    | Storage disk (default `local`)            |

---

## Project structure

```
app/
  Http/Controllers/Api/        # customer API
  Http/Controllers/Admin/      # admin token API
  Http/Controllers/AdminWeb/   # Blade admin dashboard
  Models/                      # Eloquent models + status maps
  Services/                    # PaystackService, PushNotificationService, NotificationService
  Http/Resources/              # API resource transformers
  Http/Requests/               # Validation
  Support/ApiResponse.php      # response envelope helper
mobile/
  app/                         # Expo Router routes
  services/                    # API client + domain services
  stores/                      # Zustand auth/cart stores
  components/                  # UI components
  constants/types/hooks/
```

---

## Deployment

See **[docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)** for step-by-step Hostinger shared-hosting
deployment and the EAS mobile build process.

---

## Status transitions (orders)

`PENDING → PAYMENT_PENDING → PAID → PROCESSING → READY → OUT_FOR_DELIVERY → DELIVERED`
(cancelled allowed from PENDING / PAYMENT_PENDING / PAID / PROCESSING).

---

## Known not-yet-implemented

- **Payments**: Paystack flow exists in code (initialize/verify/webhook + mobile UI) but is
  **not configured/tested** — `PAYSTACK_SECRET_KEY` is empty. Enable when ready.
- Delivery fee calculation (currently `0`)
- Refunds, cancellations policy, promotions/discounts, inventory tracking
