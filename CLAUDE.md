# Nature Care Products — Project Guide

## Stack
- Laravel 13 (PHP 8.3), Livewire 3, Filament 4 (admin panel at `/admin`)
- Tailwind CSS v4 (CSS-first config in `resources/css/app.css`, no `tailwind.config.js`)
- Vite build, `laravel-vite-plugin`
- Spatie Media Library (product images), Spatie Sitemap, Razorpay (payments), barryvdh/laravel-dompdf (invoices)
- No React / React Native in this project — storefront is Blade + Livewire + Alpine-free vanilla JS; admin is Filament's own Livewire components. Do not reach for React-only tooling (shadcn, JSX component libs) here.

## Design system (already established — extend it, don't replace it)
Defined in `resources/css/app.css` under `@theme`:
- **Brand teal** `--color-brand-50…900` — sampled from the logo's "nature" half (`brand-600 = #3e999a`). Primary actions, links, category labels.
- **Aqua/coral** `--color-aqua-50…700` — sampled from the logo's "care" half (`aqua-400 = #f06b4a`). Use for secondary accents/alerts, not as a second primary.
- **Font**: `Instrument Sans` (`--font-sans`), falls back to system sans.
- Corner radius convention: `rounded-2xl` on cards, `rounded-full` on pills/badges.
- Custom utility: `.whatsapp-float` pulse animation (see `@layer utilities` in app.css) — the pattern to follow for any other purposeful micro-animation (keep it in app.css, keyframe-based, not a JS animation lib).

When touching UI, stay inside this palette (`brand-*` / `aqua-*` / grays) — do not introduce ad hoc hex colors or a new accent color without updating `@theme` first.

## Filament v4 conventions used in this codebase
Resources follow Filament v4's split-schema pattern — mirror this for any new resource:
```
app/Filament/Resources/<Name>/
  <Name>Resource.php
  Schemas/<Name>Form.php      # ->configure(Schema $schema): Schema
  Tables/<Name>Table.php      # ->configure(Table $table): Table
  Pages/List<Name>.php, Create<Name>.php, Edit<Name>.php, (View<Name>.php)
```
Infolists live in `Schemas/<Name>Infolist.php` where a resource has a dedicated view page (see `Orders`, `ContactEnquiries`, `BusinessEnquiries`). Dashboard widgets live in `app/Filament/Widgets/`.

Do **not** install or apply generic "Filament v5" skills/guides to this project — the API differs from v4 and will suggest methods that don't exist here.

## UI/UX workflow for this project
1. Use the **frontend-design** skill for any new page/component so output stays intentional rather than generic — but its suggestions must be reconciled with the existing brand palette above, not a fresh palette.
2. Use the **web-design-guidelines** skill (`/web-design-guidelines <file>`) before merging any storefront-facing template change — this is a real e-commerce checkout/cart flow, so keyboard nav, contrast, and semantic HTML on `cart`, `checkout`, and `product` pages matter for both conversions and accessibility compliance.
3. Use the **webapp-testing** skill (Playwright-based) to click through cart → checkout → order-track after UI changes; this project has no automated browser test suite yet.
4. Use the **dataviz** skill for any Filament dashboard widget chart work (see `EnquiriesByPartnerTypeChart`, `MonthlyRevenueChart`).
5. Chrome DevTools MCP (configured in `.mcp.json`) is available for real Core Web Vitals checks on product listing/detail pages — product images are the likely LCP element, verify after any change to `product-card.blade.php` or the media pipeline.

## Key storefront templates
- `resources/views/components/layouts/app.blade.php` — base layout, header/footer/mobile bar included globally
- `resources/views/components/product-card.blade.php` — product grid card
- `resources/views/livewire/{product-catalog,cart-page,checkout,header-cart,add-to-cart,order-tracking}.blade.php` — the commerce flow
- `resources/views/components/{site-header,site-footer,mobile-bottom-bar,whatsapp-float}.blade.php` — persistent chrome
