# Nature Care Products

Marketing website + e-commerce storefront + business enquiry platform for Nature Care Products,
built on the TALL stack (Tailwind CSS v4, Alpine.js, Laravel 13, Livewire 3) with a FilamentPHP 4
admin panel.

> **Phase 1 scope:** marketing site + product catalog + business enquiry / contact enquiry
> capture.
>
> **Phase 2 scope (this phase):** direct-to-consumer e-commerce — cart, guest checkout (Razorpay
> online payment or Cash on Delivery), pincode-based shipping zones, coupon codes, GST invoicing,
> order tracking by mobile number, and full order management in the admin panel. There is still no
> customer login/registration — orders are guest checkouts keyed on mobile number, matching how
> `/track` works.

## Tech Stack

- Laravel 13, PHP 8.3
- Livewire 3 (Livewire's bundled Alpine.js — no separate Alpine install needed)
- Tailwind CSS v4 (via `@tailwindcss/vite`)
- FilamentPHP 4 admin panel at `/admin`
- MySQL 8
- `spatie/laravel-medialibrary` for product images
- `spatie/laravel-sitemap` for `/sitemap.xml`
- Filament's native Export (CSV/XLSX) for the Business Enquiry admin table
- `razorpay/razorpay` — official Razorpay PHP SDK, used for online checkout + webhook signature
  verification
- `barryvdh/laravel-dompdf` — renders GST tax invoices as downloadable PDFs

## Requirements

- PHP 8.3+ with the extensions Laravel needs (`pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `zip`, etc.)
- Composer 2
- Node.js 20+ / npm
- MySQL 8 (or compatible)

## Setup

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

Edit `.env` and set:

- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` — your local MySQL credentials (database
  `nature_care_products` is assumed; create it first: `CREATE DATABASE nature_care_products;`)
- `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` — SMTP credentials for
  outgoing enquiry notifications
- `MAIL_FROM_ADDRESS` — defaults to `enquiry@naturecareplus.com`
- `MAIL_ADMIN_ADDRESS` — defaults to `online@viviz.in`; where new enquiry notifications are sent
  (can also be changed later from the admin Settings page, which takes priority once set)
- `NATURECARE_WHATSAPP_NUMBER` — WhatsApp Business number in `91XXXXXXXXXX` format
- `NATURECARE_META_PIXEL_ID` — leave empty to disable Meta Pixel; set it to enable `PageView` +
  `Lead` (fired on the `/partner/thank-you` page) tracking
- `APP_URL` — should be `https://naturecareplus.com` in production (used for canonical URLs,
  sitemap, Organization schema, **and signed order-tracking/success URLs** — if this doesn't match
  the domain you're testing against, signed links generated during checkout will 403. In local
  dev, either browse via `APP_URL`'s own host or temporarily set `APP_URL=http://127.0.0.1:8000`
  in your local `.env` while testing checkout in a browser.)
- `RAZORPAY_KEY_ID` / `RAZORPAY_KEY_SECRET` — Razorpay API keys (Dashboard → Settings → API Keys).
  Leave blank in local dev if you don't need to exercise the live Razorpay Checkout flow — COD
  checkout and the rest of the storefront work without these; `CheckoutRazorpayTest` mocks
  `RazorpayService` so the test suite doesn't need real keys either.
- `RAZORPAY_WEBHOOK_SECRET` — set when registering the webhook (see below)

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
```

Seeding creates:

- 5 categories (Home Care, Kitchen Care, Laundry Care, Personal Care, Commercial Range)
- 15 products across those categories, each with one or more `product_variants` (size/SKU/price)
- Default site settings (phone, WhatsApp, email, address, map embed URL)
- One shipping zone: Tamil Nadu (pincode prefix `6`), ₹49 shipping, COD available, free shipping
  above ₹999
- One sample coupon: `WELCOME10` (10% off, min cart ₹299, capped at ₹150 off)

> Freshly seeded product variants have `stock_qty = 0` (Phase 1 never tracked stock) — set stock
> per size from **Catalog → Products → [product] → Sizes / Variants** before they're purchasable
> on the storefront.

### Razorpay webhook setup

Register `https://<your-domain>/webhooks/razorpay` in the Razorpay Dashboard (Settings →
Webhooks), select the `payment.captured` event, and set the same secret you configure it with as
`RAZORPAY_WEBHOOK_SECRET`. The webhook is a backup confirmation path — `OrderService::markPaid()`
is idempotent, so whichever of the browser's Checkout.js success callback or this webhook arrives
first marks the order paid; the second is a no-op. The route is CSRF-exempt (`bootstrap/app.php`)
since Razorpay can't supply a Laravel CSRF token — authenticity instead comes from verifying the
`X-Razorpay-Signature` header against `RAZORPAY_WEBHOOK_SECRET`.

### Create an admin user

```bash
php artisan app:create-admin-user
```

This prompts for an email, name, and password (interactively). Non-interactively it defaults to
`admin@example.com` / name `Admin` and still prompts for a password:

```bash
php artisan app:create-admin-user --email=you@example.com --name="Your Name"
```

Log in at `/admin`.

### Build frontend assets

```bash
npm run build      # production build
npm run dev        # dev server with HMR
```

### Run the app

```bash
composer run dev
```

This runs the PHP dev server, queue listener, log tailer (`pail`), and Vite dev server together.
Or run `php artisan serve` on its own.

## Queues

Enquiry notification emails and order lifecycle emails (`OrderPlaced`, `OrderShipped`,
`OrderDelivered`, `AdminNewOrderAlert`) are all queued (`ShouldQueue`). In local development
`QUEUE_CONNECTION=database` works out of the box with `php artisan queue:listen`. In production,
run a persistent queue worker (`php artisan queue:work`) — `composer run dev` starts a listener
for local use only.

## Testing

```bash
php artisan test
```

Tests run against an in-memory SQLite database (see `phpunit.xml`) and cover:

- `PartnerEnquiryForm` — step navigation, validation (mobile format, conditional godown
  requirement), honeypot spam protection, rate limiting (3/hour/IP), successful submission +
  admin email
- `ContactForm` — validation, honeypot, rate limiting, successful submission + admin email
- Public page smoke tests (home, products, product detail, partner, about, contact, sitemap)
- Admin panel access control and each resource's index/create/edit pages
- `ProductVariantMigrator` (Unit) — the legacy `products.variants` JSON → `product_variants` row
  conversion logic (paise conversion, SKU generation, malformed-entry skipping)
- `CartTest` — add/update/remove cart items, out-of-stock guard, subtotal/total recalculation
- `CouponApplyTest` — flat vs. percent discount, max-discount cap, min-cart-value rejection,
  expired coupons
- `ShippingZoneResolverTest` — longest-pincode-prefix-wins resolution, inactive zones ignored
- `CheckoutCodTest` — full COD checkout flow, stock decrement, rate limiting, zone-based COD
  availability
- `CheckoutRazorpayTest` — Razorpay checkout flow with `RazorpayService` mocked (pending order
  created without touching stock, `confirmRazorpayPayment` marks paid + decrements stock only on
  a valid signature)
- `RazorpayWebhookTest` — signature verification, idempotency (JS callback + webhook both firing
  never double-decrements stock), unhandled event types acknowledged and ignored
- `OrderTrackingTest` — mobile + order number lookup, rate limiting
- `OrderCancellationTest` — stock is only restored if it was actually decremented
  (`stock_decremented_at`), matching the COD-decrements-on-placement vs.
  Razorpay-decrements-on-payment split
- `Filament/OrderResourceTest` — admin access control, GST invoice download, and every
  `ViewOrder` page action (update status, add tracking, mark COD paid, cancel)

## Storefront (`/products/{slug}`, `/cart`, `/checkout`, `/track`)

- **Product page** — size/variant selector (`AddToCart` Livewire component) showing live stock and
  selling price per size; out-of-stock sizes are disabled, not hidden
- **Cart** (`/cart`) — qty update/remove, pincode delivery-availability check (shipping charge +
  COD availability preview), coupon apply/remove
- **Checkout** (`/checkout`) — 3-step guest checkout (contact → address → payment); COD is only
  offered if the resolved shipping zone allows it; Razorpay path uses the official Checkout.js
  widget (loaded from Razorpay's CDN only on this page) with server-side signature verification
  before an order is ever marked paid
- **Order success** (`/orders/{order_number}/success`) and **tracking** (`/track`) — both use
  Laravel signed URLs / mobile-number-gated lookup respectively, since there's no customer login
- Header cart icon and a mobile bottom nav bar (`Home / Products / Cart / WhatsApp`) both update
  live via a `cart-updated` Livewire event

## Admin Panel (`/admin`)

- **Dashboard** — enquiries-this-month stat, new-enquiries stat, unread-contacts stat, enquiries by
  partner type (donut chart), recent business enquiries table, plus Phase 2 additions: today's
  orders/revenue/pending-COD/low-stock stat cards, 30-day revenue line chart, low-stock variants
  table, pending COD confirmations table. Dashboard widgets lazy-load as you scroll — this is
  normal Filament behaviour, not a bug, if a widget briefly appears blank on a very fast scroll.
- **Catalog → Categories** — CRUD, image upload, drag-to-reorder
- **Catalog → Products** — CRUD, media library image uploads (drag-reorderable, WebP conversions),
  featured/active/commercial toggles, HSN code (used on GST invoices), SEO fields. Sizes/variants
  are managed on a dedicated inline-editable relation manager table (SKU, MRP, selling price,
  weight, stock, active) on the product edit page — replaces the old JSON repeater.
- **Orders → Orders** — list + view only (orders are never hand-authored); status/payment/method
  badges, filters (status, payment status, payment method, date range); the order view page has
  actions to update status, add courier tracking, mark a COD order paid, cancel with a reason
  (restores stock only if it was actually decremented), and download the GST invoice PDF
- **Orders → Coupons** — full CRUD; flat or percentage discounts, optional min cart value, optional
  max discount cap, optional usage limit, optional validity window
- **Orders → Shipping Zones** — full CRUD plus CSV import (`ShippingZoneImporter`); pincode-prefix
  based, longest-prefix-wins if two zones overlap (e.g. a `"600"` zone beats a broader `"6"` zone)
- **Enquiries → Business Enquiries** — partner type & status badges, inline-editable status,
  filters (partner type / status / state / date range), one-click "WhatsApp" action, view page
  with admin notes field, CSV/XLSX export (single record or bulk)
- **Enquiries → Contact Enquiries** — read-only inbox, mark read/unread, view full message
- **Settings** — phone, WhatsApp number, public email, admin notification email, address, Google
  Map embed URL, social links, Meta Pixel ID, plus a Phase 2 "Orders & Tax" section: GSTIN, GST
  rate %, COD handling fee, default shipping charge, low-stock threshold (all editable without a
  deploy). **Razorpay keys are deliberately not here** — see "Where secrets live" below.

Access control: any authenticated `User` can access the panel (`App\Models\User::canAccessPanel`
returns `true`) since there's no public self-registration flow — only trusted admins are created
via `app:create-admin-user`.

### Where secrets live

Razorpay's key/secret/webhook-secret live in `.env` → `config/services.php` only, never in the
DB-backed `settings` table or the Settings page. `Setting::value` is a plain unencrypted column
with one shared forever-cache key, and every authenticated admin can access the Settings page (no
roles/permissions package is installed) — so anything placed there is visible to every admin.
GSTIN / COD fee / shipping charge / GST rate / low-stock threshold are low-sensitivity operational
values that legitimately belong in the Settings page instead, stored in human units (rupees /
percent) and converted to paise only at the point of consumption in `OrderService` / `GstService`.

## SEO

- `/sitemap.xml` — dynamically generated (home, products index, partner, about, contact, every
  active product)
- `public/robots.txt` — allows crawling, disallows `/admin`, points to the sitemap
- `Organization` JSON-LD on the homepage, `Product` JSON-LD (with per-variant `Offer`s) on product
  detail pages
- Per-page meta title/description + Open Graph tags via the `x-layouts.app` component;
  per-product SEO fields are editable in the admin

## Project Structure Notes

- `App\Enums\PartnerType` / `App\Enums\EnquiryStatus` — native PHP enums implementing Filament's
  `HasLabel`/`HasColor`, used for both validation and admin badges. Add a new partner type by
  adding an enum case — no schema change needed since `business_enquiries.partner_type` is a
  plain string column. Phase 2 adds the same pattern for `PaymentMethod`, `PaymentStatus`,
  `OrderStatus`, `CouponType`.
- `App\Models\Setting` — simple key/value store, cached forever and invalidated on save; read via
  `Setting::get('key', $default)`.
- **Money is always integer paise in the database.** `App\Support\Money` (immutable value object:
  `Money::fromRupees()`, `->rupees()`, `->format()` → `"₹199.00"`) plus `App\Casts\MoneyCast`
  (the Eloquent cast applied to every money column on `ProductVariant`, `Cart`/`CartItem`,
  `Order`/`OrderItem`, `ShippingZone`) are the *only* places `/100` or `*100` arithmetic should
  appear in application code — the one documented exception is Setting-KV rupee↔paise conversion,
  done explicitly at the point of consumption since `Setting::value` is a plain string. Note
  `Coupon.value` is **not** MoneyCast — its meaning depends on `Coupon.type` (paise for `flat`,
  1–100 for `percent`), so it's formatted conditionally via `Coupon::formattedValue()` instead.
- `App\Models\Product::variants()` — `HasMany` to `App\Models\ProductVariant` (`size_label`, `sku`,
  `mrp`, `selling_price`, `weight_grams`, `stock_qty`, `is_active`). Replaces the Phase 1
  `products.variants` JSON column, which no longer exists (dropped in
  `2026_08_06_100010_drop_variants_column_from_products_table`). `Product::lowestVariantPrice()`
  now queries this relation instead of decoding JSON.
- **Cart** is DB-backed (`carts`/`cart_items`), keyed by Laravel's own `session()->getId()` — no
  new middleware needed since the `web` group already starts a session for every guest request.
  `App\Services\CartService` (bound as a singleton) owns all cart mutation; it deliberately does
  **not** factor shipping into `recalculate()` — shipping is only locked in at checkout once a
  pincode resolves a `ShippingZone`, so a stale cached shipping charge can never survive an admin
  editing a zone's price.
- **Orders are guest data**, keyed on mobile number via `App\Models\Customer` (name, unique
  mobile, email, addresses json) — upserted on order placement, used only to prefill `/track`
  lookups. There is still no customer authentication anywhere in the app.
- `App\Services\OrderService::createPendingOrder()` re-validates every cart item's live
  stock/price against `ProductVariant` immediately before creating the order, throwing
  `App\Exceptions\CartValidationException` (caught by the `Checkout` Livewire component to show
  inline errors) if anything changed since the item was added to the cart.
- **Stock is decremented differently by payment method**, tracked via `orders.stock_decremented_at`
  (nullable): COD orders decrement immediately on placement (payment happens on delivery, so the
  goods are committed right away); Razorpay orders only decrement once `OrderService::markPaid()`
  runs, so a never-paid Razorpay order never has stock to restore. `OrderService::cancel()` checks
  this column before restoring stock, rather than assuming every cancelled order had stock taken.
- **Order/invoice numbering** (`App\Services\SequenceGenerator`) is race-safe via
  `lockForUpdate()` inside the same `DB::transaction()` as the insert, with the unique index as a
  final safety net — `OrderService` retries once on a unique-violation `QueryException`. sqlite
  (the test suite's database) has no real row-locking, so this specific guarantee is verified by
  code review + manual concurrent-checkout testing against MySQL, not by PHPUnit.
- **Razorpay JS-callback and webhook are mutually idempotent by sharing one code path** —
  `Checkout::confirmRazorpayPayment()` (browser success callback) and
  `RazorpayWebhookController` (server-to-server backup) both call the same
  `OrderService::markPaid()`, which is a no-op if the order is already paid. Whichever arrives
  first wins; the second is harmless.
- **GST** (`App\Services\GstService`) treats stored order/line totals as tax-inclusive (matching
  the existing MRP convention) and backs out the taxable value at a configurable rate
  (`Setting::get('gst_rate_percent', 18)`) rather than adding tax on top. CGST+SGST (split 50/50,
  any 1-paise rounding remainder folded into SGST) apply when the shipping state is Tamil Nadu
  (case-insensitive); IGST applies otherwise. `products.hsn_code` is copied onto
  `order_items.hsn_code_snapshot` at order-creation time so historical invoices stay correct even
  if a product's HSN code changes later.
