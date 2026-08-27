# GOBE Republic — API Reference

Base URL (dev): `http://127.0.0.1:8000/api/v1`
Base URL (prod): `https://<your-backend>/api/v1`

All responses use the envelope:

```json
{ "success": true, "message": "...", "data": { ... } }
```

Errors use `{ "success": false, "message": "...", "errors": {...} }`.

Authenticated endpoints accept `Authorization: Bearer <token>` (Laravel Sanctum).

---

## Customer API

### Auth

| Method | Endpoint            | Auth | Description                           |
|--------|---------------------|------|---------------------------------------|
| POST   | `/auth/register`    | No   | Register customer                     |
| POST   | `/auth/login`       | No   | Login by email or phone               |
| POST   | `/auth/logout`      | Yes  | Revoke current token                  |
| GET    | `/auth/me`          | Yes  | Current profile                       |

`register` body: `name`, `email?`, `phone?`, `password`, `password_confirmation`.
`login` body: `email` **or** `phone`, `password`.
Response: `{ user, token }`.

### Catalog (public)

| Method | Endpoint                | Description                          |
|--------|-------------------------|--------------------------------------|
| GET    | `/categories`           | Active categories (array)            |
| GET    | `/categories/{id}`      | Show a category                      |
| GET    | `/products`             | Paginated active products            |
| GET    | `/products/{id}`        | Show a product                       |

`/products` query params: `page`, `per_page`, `category_id`, `search` (or `q`).
Response: `{ items, pagination }`.

### Cart (auth)

| Method | Endpoint               | Description              |
|--------|------------------------|--------------------------|
| GET    | `/cart`                | View cart                |
| POST   | `/cart/items`          | Add item (`product_id`,`quantity`) |
| PUT/PATCH | `/cart/items/{itemId}`| Update quantity (`quantity`) |
| DELETE | `/cart/items/{itemId}` | Remove item              |
| DELETE | `/cart`                | Clear cart               |

Cart response: `{ id, user_id, items[], subtotal, item_count }`.

### Orders (auth)

| Method | Endpoint            | Description                       |
|--------|---------------------|-----------------------------------|
| POST   | `/orders`           | Checkout (create order)           |
| POST   | `/checkout`         | Alias of `/orders`                |
| GET    | `/orders`           | List current user's orders (array)|
| GET    | `/orders/{id}`      | Show an order (authorized only)   |

`POST /orders` body:

```json
{
  "delivery_information": {
    "recipient_name": "Jane Doe",
    "phone": "08012345678",
    "address": "12 Street",
    "city": "Lagos",
    "additional_notes": "optional"
  }
}
```

Flat fields (`recipient_name`, `phone`, `address`, ...) are also accepted.

### Notifications (auth)

| Method | Endpoint                          | Description                  |
|--------|-----------------------------------|------------------------------|
| GET    | `/notifications`                  | List notifications (array)   |
| POST   | `/notifications/{id}/read`        | Mark one as read             |
| POST   | `/notifications/read-all`         | Mark all as read             |

### Device tokens (auth)

| Method | Endpoint          | Description                      |
|--------|-------------------|----------------------------------|
| POST   | `/device-tokens`  | Register push token `{ token, platform? }` |
| DELETE | `/device-tokens`  | Remove a token `{ token }`       |

---

## Admin API

Admin endpoints are prefixed `/admin` and require a token from `POST /admin/login`
(an account whose role is `SUPER_ADMIN`, `ADMIN`, or `STAFF`) plus the `admin` middleware.

| Method | Endpoint                     | Description                         |
|--------|------------------------------|-------------------------------------|
| POST   | `/admin/login`               | Admin login                         |
| POST   | `/admin/logout`              | Admin logout                        |
| GET    | `/admin/me`                  | Current admin profile               |
| GET    | `/admin/dashboard`           | Dashboard statistics                |
| GET    | `/admin/products`            | List products                       |
| POST   | `/admin/products`            | Create product                      |
| GET    | `/admin/products/{id}`       | Show product                        |
| PATCH  | `/admin/products/{id}`       | Update product                      |
| DELETE | `/admin/products/{id}`       | Delete product                      |
| GET    | `/admin/categories`          | List categories                     |
| POST   | `/admin/categories`          | Create category                     |
| GET    | `/admin/categories/{id}`     | Show category                       |
| PATCH  | `/admin/categories/{id}`     | Update category                     |
| DELETE | `/admin/categories/{id}`     | Delete category                     |
| GET    | `/admin/customers`           | List customers                      |
| GET    | `/admin/customers/{id}`      | Show a customer + orders            |
| GET    | `/admin/orders`              | List orders (filter by status)      |
| GET    | `/admin/orders/{id}`         | Show an order                       |
| PATCH  | `/admin/orders/{id}/status`  | Transition order status             |
| GET    | `/admin/users`               | List admin/staff users              |
| POST   | `/admin/users`               | Create admin/staff (SUPER_ADMIN)    |
| PATCH  | `/admin/users/{id}`          | Update admin/staff                  |

---

## Web admin dashboard

Browser-based dashboard at `/admin/login` (session auth). Endpoints live under
`routes/web.php` and are served by the Blade views in `backend/resources/views/admin`.

---

## Statuses

**Order status** (transition map enforced on `/admin/orders/{id}/status`):
`PENDING → PAYMENT_PENDING → PAID → PROCESSING → READY → OUT_FOR_DELIVERY → DELIVERED`
(cancellable from PENDING/PAYMENT_PENDING/PAID/PROCESSING).

**Payment status**: `PENDING`, `SUCCESSFUL`, `FAILED`, `CANCELLED`, `REFUNDED`.

---

## Running tests

```bash
cd backend
php artisan migrate --env=testing
vendor/bin/phpunit
```
