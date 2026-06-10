# Laravel E-commerce (Variable Products)

Laravel 11 + MySQL/MariaDB + Blade + Tailwind + Alpine.js. Variable products with
attribute-based variants (SKUs), wishlist, product compare, dual purchasing flows
(standard cart + Buy Now), order management with a gateway-agnostic payment layer.

## Stack

- **Laravel** 11.x, PHP 8.2+
- **DB**: MySQL 8.x (works on XAMPP's MariaDB 10.4)
- **Frontend**: Blade + Tailwind CSS + Alpine.js (Breeze auth scaffold)
- **Payments**: pluggable `PaymentGateway` — `fake` (local), `stripe`, `razorpay`

## Setup

```bash
composer install
npm install && npm run build       # or: npm run dev

# .env is preconfigured for MySQL on localhost / db "ecommerce".
# Create the schema and demo data:
php artisan migrate --seed
php artisan storage:link           # if you add variant images

php artisan serve
```

### Demo accounts (from the seeder)

| Role     | Email                  | Password |
|----------|------------------------|----------|
| Admin    | admin@example.com      | password |
| Customer | customer@example.com   | password |

The seeder creates one product, **Aurora Smartphone**, with 4 generated variants
(Color × Storage).

## Payment gateways

Set `PAYMENT_DRIVER` in `.env` to `fake` (default), `stripe`, or `razorpay`.
`fake` routes to a local page where you simulate success/failure — no keys needed.
For Stripe, run `composer require stripe/stripe-php` and set `STRIPE_*` keys.

## Architecture map

| Concern | Location |
|---|---|
| Schema | `database/migrations/2026_06_10_*` |
| Models | `app/Models/{Product,Attribute,AttributeValue,ProductVariant,Cart,CartItem,Order,OrderItem}.php` |
| Cartesian variant generator | `app/Services/VariantGenerator.php` |
| Cart (db-backed) | `app/Services/CartService.php` |
| Buy Now vs Cart resolver | `app/Services/CheckoutSourceResolver.php` |
| Order placement + stock locking | `app/Services/OrderService.php` |
| Payment layer | `app/Services/Payments/*` (bound in `AppServiceProvider`) |
| Buy Now / checkout | `app/Http/Controllers/CheckoutController.php`, `OrderController.php` |
| Admin variant generation | `app/Http/Controllers/Admin/ProductVariantController.php` |
| Admin order status | `app/Http/Controllers/Admin/OrderController.php` |
| Frontend variant selector | `resources/views/products/show.blade.php` (Alpine) |

## Two purchasing flows

- **Flow A — Cart**: `cart.add` → `cart.index` → `checkout.show` → `checkout.place` → pay.
- **Flow B — Buy Now**: `checkout.buyNow` stores an ephemeral `session('buy_now')`
  payload and redirects to `checkout.show?mode=buy_now`, bypassing the cart.

Both normalise to a `CheckoutSource` so order placement, stock locking, and payment
are identical downstream — see `CheckoutSourceResolver`.
