# Project Map

## Recent Modifications

### Hide Hero on Mobile & Cache Manager (2026-05-14)
- **Issue**: 
  1. The hero section needed to be completely hidden on mobile (not just the text overlay).
  2. The `mobile_image_path` field became unnecessary since the entire hero is hidden on mobile.
  3. Admins needed a clear way to clear cache for images/multimedia updates.
- **Solution**:
  - `resources/views/livewire/store/home.blade.php`: Wrapped `<livewire:store.hero-slider />` in `<div class="hidden md:block">` to hide hero on mobile.
  - `resources/views/store/home.blade.php`: Same wrapping for the standalone blade view.
  - `resources/views/livewire/store/hero-slider.blade.php`: Simplified - removed `<picture>`/`<source>` elements and conditional classes (`hidden md:block`/`hidden md:flex`) since `mobile_image_path` is no longer used.
  - `app/Models/Slider.php`: Removed `mobile_image_path` from `$fillable`.
  - `app/Filament/Resources/Sliders/Schemas/SliderForm.php`: Removed `mobile_image_path` FileUpload field; simplified section to single image upload.
  - `app/Filament/Pages/CacheManager.php` **(NEW)**: Filament page under System group for super-admins to clear response cache, application cache, blade cache, or all caches at once. Uses `ResponseCache::clear()` and `Artisan::call()` with confirmation dialogs.
  - `resources/views/filament/pages/cache-manager.blade.php` **(NEW)**: Admin UI with action buttons and current cache configuration display.
  - `lang/en/admin.php` + `lang/ar/admin.php`: Added cache manager translations.
- **Tests**:
  - `tests/Feature/HeroSliderTest.php`: Added `test_hero_is_hidden_on_mobile_by_css_class` to verify the `hidden md:block` wrapper class exists.
  - `tests/Feature/CacheManagerTest.php` **(NEW)**: Tests for cache manager page accessibility and cache clearing operations.

### Render-Blocking JS Optimization & TBT Reduction (2026-05-14)
- **Goal**: Reduce render-blocking JavaScript and Total Blocking Time (TBT) for PageSpeed Insights.
- **Issues Found & Fixed**:
  1. **Facebook Pixel** was loaded synchronously in `<head>` of `layouts/store.blade.php` — the inline `!function()` wrapper blocked HTML parsing until it finished executing, even though the external script used `t.async=!0`.
  2. **Font Awesome 6** was loaded via a synchronous `<link rel="stylesheet">` in `<head>` of `components/store-layout.blade.php` — render-blocking because the browser must fetch and parse the entire CSSOM before painting.
- **Solutions**:
  - `resources/views/components/analytics/facebook-pixel.blade.php` **(REFACTORED)**: Wrapped the pixel loading in a self-executing IIFE that implements a **hybrid delay-on-interaction** approach:
    - Listens for `scroll`, `click`, `mousemove`, `touchstart` events. The first user interaction triggers pixel load and removes all listeners.
    - A `setTimeout(loadPixel, 4000)` acts as a fallback — if the user never interacts, the pixel loads after 4 seconds anyway.
    - The `<noscript>` fallback (for crawlers without JS) remains unaffected.
  - `resources/views/layouts/store.blade.php`: Moved the `<x-analytics.facebook-pixel />` include from `<head>` (line 101) to just before `</body>` (line 228). The pixel's internal delay mechanism now prevents ANY execution until user interaction or 4s timeout.
  - `resources/views/components/store-layout.blade.php` **(Font Awesome fix)**: Added `media="print"` and `onload="this.media='all'"` to the Font Awesome CDN `<link>`. The browser treats `media="print"` as low-priority and non-render-blocking. Once loaded, the `onload` handler swaps it to `media="all"`, applying the styles.
- **Rationale (Vite @vite() left untouched)**:
  - `@vite()` generates `<script type="module">` which is automatically deferred by all modern browsers — does NOT block rendering.
  - Separating the CSS and JS `@vite()` calls would require Vite config changes and offers negligible TBT benefit.
- **Tests**:
  - `tests/Feature/HeroSliderTest.php`: Existing tests unaffected (no changes to hero slider logic).
  - `tests/Feature/CacheManagerTest.php`: Existing tests unaffected.

### Image Delivery & LCP Optimization (2026-05-14)
- **Goal**: Reduce Largest Contentful Paint (LCP) from ~7.7s by optimizing image delivery, loading priority, and server caching headers.
- **Issues Found**:
  1. All product images used `loading="lazy"` indiscriminately — even above-the-fold LCP candidates were delayed.
  2. Missing `decoding="async"` on below-the-fold images — browser couldn't decode images off the main thread.
  3. Missing `fetchpriority="high"` on any product images — browser had no hint which images to prioritize.
  4. Spatie Media Library conversions used `keepOriginalImageFormat()` (JPEG/PNG originals) — no WebP, no optimize pipeline.
  5. `.htaccess` had no caching rules for font files (woff, woff2, ttf).
  6. No Nginx configuration reference existed in the project.
- **Solutions** (8 files modified, 2 files created):

  **A. Above/Below the Fold Image Strategy** (`resources/views/`):
  - `components/store/product-card.blade.php`: Added `$aboveFold` prop (`@props(['product', 'aboveFold' => false])`). When `true` → `fetchpriority="high"` (no `loading="lazy"`). When `false` → `loading="lazy" decoding="async"`.
  - `components/cosmetics/product-card.blade.php`: Same `$aboveFold` prop added.
  - `livewire/store/home.blade.php` (Featured Products loop): Changed `<x-store.product-card :product="$product" />` → `:aboveFold="$loop->iteration <= 4"` for the first 4 items.
  - `livewire/store/featured-products.blade.php`: Same `:aboveFold="$loop->iteration <= 4"` applied.
  - `livewire/store/product-list.blade.php`: Same — first 4 products in the listing grid get priority.

  **B. Spatie Media Library — Tiered WebP Conversions** (`app/Models/Product.php`):
  - Replaced `->keepOriginalImageFormat()` with `->format('webp')` on all 3 conversions.
  - **thumbnail** (150x150): `->format('webp')->quality(70)->optimize()` — smallest possible for quick load in wishlist/mini-cart.
  - **card** (400x400): `->format('webp')->quality(70)->optimize()` — tiny payload for product grids; WebP at q70 saves ~50-70% vs original JPEG.
  - **preview** (1200x1200): `->format('webp')->quality(95)` — **NO `->optimize()`** intentionally, so zoom detail remains razor-sharp on product detail pages.
  - *Note*: Existing images must be regenerated. Run on the server:
    ```
    php artisan media:regenerate
    ```

  **C. Server Caching Headers** (`public/.htaccess`):
  - Added `ExpiresByType` rules for: `font/woff`, `font/woff2`, `font/ttf`, `font/eot`, `font/otf`, `application/font-woff`, `application/vnd.ms-fontobject`, `application/x-font-ttf` — all with 1-year expiry.
  - Updated `<FilesMatch>` to include `woff|woff2|ttf|eot|otf`.
  - Upgraded CSS/JS caching from 1 month → 1 year (safe because Vite appends content hashes).
  
  **D. Nginx Reference Config** (`.nginx.conf.example` — NEW):
  - A production-grade reference for Nginx users covering:
    - 1-year `Cache-Control: public, immutable` for images, fonts, CSS, JS
    - Gzip compression for text/font assets
    - Security headers (X-Frame-Options, X-Content-Type-Options, X-XSS-Protection, Referrer-Policy)
    - Laravel front controller, PHP-FPM, hidden file protection
    - HTTP → HTTPS redirect
- **Tests**:
  - `tests/Feature/CacheManagerTest.php`: 3 tests pass — no regression.
  - `tests/Feature/HeroSliderTest.php`: Pre-existing SQLite migration issue (unrelated to these changes).

## Deprecated Code
- `mobile_image_path` field in Slider model/form/blade — replaced by auto-hide on mobile approach. Column remains in database but is no longer used.
