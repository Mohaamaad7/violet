# Project Map

## Recent Modifications

### Bilingual Catalog Content & Safe Test Deployment (2026-10-26)
- **Goal**: Store Arabic/English catalog content separately, display only the selected language, preserve original data/IDs/URLs, and initialize the missing test header categories during deployment.
- **Storage / Compatibility**:
  - `database/migrations/2026_10_26_000001_add_catalog_translations.php` **(NEW)**: Adds nullable `_ar`/`_en` columns for product/category names, descriptions and SEO; products also receive localized short/long descriptions, specifications and usage. Backfills only unambiguous content in chunks, without changing original columns, IDs, prices, SKU, slugs or relationships.
  - `app/Support/LegacyCatalogTranslation.php` **(NEW)**: Conservatively extracts clear `Arabic (English)` names or single-language content; does not guess translations for ambiguous mixed text.
  - `app/Models/Concerns/HasCatalogTranslations.php` **(NEW)** + `app/Models/Product.php` + `app/Models/Category.php`: Localized accessors, bilingual search and missing-translation scopes, publication checks, automatic legacy-name initialization for new records, and response-cache invalidation when translations change. Missing names use a localized review label, never the other language's text; the original remains available for review/import/order snapshots.
- **Filament Data Entry**:
  - `app/Filament/Schemas/CatalogTranslationFields.php` **(NEW)**: Reusable Arabic/English tabs with language-specific direction, rich text and SEO inputs. Both names are required for active records; draft/inactive records can be incomplete. Slugs are generated on initial English name entry only, preserving existing URLs on edits.
  - `app/Filament/Resources/Products/Schemas/ProductForm.php`: Uses the tabs, preserves an original-name reference, starts new products as drafts, and supports bilingual inline category creation.
  - `app/Filament/Resources/CategoryResource.php`: Bilingual category entry/labels, translation-completeness indicator/filter, and guarded inline activation.
  - `app/Filament/Resources/Products/Tables/ProductsTable.php`: Localized labels, bilingual search, completeness indicator/filter and full-selection validation before bulk publishing, with a visible notification on incomplete translations.
  - `lang/ar/catalog.php` + `lang/en/catalog.php` **(NEW)**: Entry labels and localized missing-translation messages.
- **Storefront / Search**:
  - `app/Livewire/Store/SearchBar.php`, `app/Livewire/Store/ProductList.php`, `app/Services/ProductService.php`, `app/Services/CategoryService.php`: Search both translations plus original content for compatibility, while displaying names in the current locale. Category service activation validates both names.
  - `app/Livewire/Store/CheckoutPage.php`, `app/Livewire/Store/ComboLandingPage.php`, `app/Livewire/Store/OffersPage.php`: Use localized catalog names in customer-facing prepared data; historical order snapshots remain intact.
  - `resources/views/components/store/header.blade.php`, `resources/views/components/store/category-sections.blade.php`, `resources/views/components/store/product-card.blade.php`, `resources/views/components/store/combo-progress-hint.blade.php`: Localized category/product labels and alt text, retaining the existing image-priority behavior.
  - `resources/views/components/cosmetics/hero.blade.php`, `resources/views/components/cosmetics/product-card.blade.php`: Same locale-specific catalog display in the alternate storefront.
  - `resources/views/livewire/store/home.blade.php`, `resources/views/livewire/store/product-list.blade.php`, `resources/views/livewire/store/category-show.blade.php`, `resources/views/livewire/store/product-details.blade.php`: Localized category/product content throughout grids, filters, straps and detail tabs. The exact hero wrapper and first-four-card LCP priority remain intact.
  - `resources/views/livewire/store/cart-manager.blade.php`, `resources/views/livewire/store/cart-page.blade.php`, `resources/views/livewire/store/wishlist-page.blade.php`, `resources/views/livewire/store/account/my-reviews.blade.php`, `resources/views/livewire/store/account/order-details.blade.php`, `resources/views/livewire/store/account/orders.blade.php`: Localized related-product labels where the catalog record is available.
  - `resources/views/store/product-details.blade.php` + `resources/views/components/store-layout.blade.php`: Bind localized product SEO/title/keywords and translated breadcrumbs through explicit layout props.
  - `app/Support/LocaleAwareResponseHasher.php` **(NEW, previous local fix)** + `config/responsecache.php`: Include the prior locale cache fix in this release so Arabic/English cached pages remain isolated.
- **Test Environment Setup / Deployment**:
  - `database/seeders/StorefrontCategoriesSeeder.php`: Initializes bilingual main-category labels and reuses the old active root `Body_mist` or matching fragrance name when present; never moves products or overwrites custom translations. Missing gender branches are additive and require human product classification.
  - `app/Console/Commands/PrepareStorefront.php` **(NEW)**: `storefront:prepare --activate-category-first` initializes visibility/order/template once, records a setup marker, reports missing names, and preserves later administrator choices on repeat runs.
  - `.github/workflows/deploy-test.yml`: Validates the test checkout before any write, safely resolves a single existing test checkout when the configured path is invalid, serializes deploys, runs Composer/migrations/setup/cache refresh, and limits permission updates to Laravel writable directories. The previous failed deployment was confirmed to have an invalid `TEST_DEPLOY_PATH`; the old script continued after `cd` failed. The replacement stops before modifying anything when a unique verified test path cannot be established.
- **Validation / Instructions**:
  - `tests/Feature/CatalogLocalizationTest.php` **(NEW)**: Covers localized fields/header/home/search/SEO, cache invalidation in both languages, missing-name behavior, source-preserving migration, legacy fragrance-root reuse, repeatable deployment setup, Filament draft/publish validation and all-or-nothing bulk publication.
  - `tests/Feature/StorefrontLanguageSwitchTest.php` **(NEW, previous local fix)**: Cache isolation and both language routes under both templates.
  - `tests/Feature/StorefrontTemplateTest.php` + `tests/Feature/LcpImageTest.php`: Set an explicit locale for English fixtures; existing slider, cache and product-filter tests are retained.
  - `docs/CATALOG_LOCALIZATION_TESTING_AR.md` **(NEW)**: Arabic administrator acceptance steps, legacy-data review guidance and deployment commands.
  - Verified **57 tests / 414 assertions** across the catalog, locale-cache, template, hero, LCP, cache-manager and existing product-filter suites. `npm run build` succeeds with unchanged JS/CSS bundle hashes. Workflow YAML and shell syntax are validated.

### Fix Arabic / English Switching with Response Cache (2026-10-26)
- **Issue**: The language controller correctly updated the session, but Spatie's default response hasher did not include the resolved locale. A cached Arabic homepage or product listing was therefore returned after selecting English, including the stale language-switcher link and RTL direction.
- **Solution**:
  - `app/Support/LocaleAwareResponseHasher.php` **(NEW)**: Extends Spatie's default hasher with the locale resolved by `SetLocale`, while retaining URL/query-string, user, and explicit cache-suffix isolation.
  - `config/responsecache.php`: Registers the locale-aware hasher. Arabic and English now have separate server-side response cache entries; the existing seven-day TTL and cache invalidation behavior remain unchanged. Changing language does not flush the other language's cached pages.
  - `tests/Feature/StorefrontLanguageSwitchTest.php` **(NEW)**: Reproduces the original failure and verifies Arabic → English → Arabic on homepage and product listing under both templates. Covers both `/locale/{locale}` and legacy `/language/{locale}`, session persistence, redirect destination, language/direction, switcher target, and actual cache hits when revisiting either language.
  - `docs/PROJECT_MAP.md`: Records the cause, changed files, verification, and activation steps.
- **Verification**:
  - New regression tests failed in all four cases before the fix (`en` expected, cached `ar` returned), then passed after enabling the locale-aware hasher.
  - `php artisan test tests/Feature/StorefrontLanguageSwitchTest.php tests/Feature/StorefrontTemplateTest.php tests/Feature/HeroSliderTest.php tests/Feature/LcpImageTest.php tests/Feature/CacheManagerTest.php`: **29 tests passed (288 assertions)**.
  - Actual HTTPS requests to `violet.test` using one cookie session returned `ar`, then `en` after `/locale/en`, then `ar` after `/locale/ar`.
  - Applied locally with `php artisan config:clear` and `php artisan responsecache:clear`. Run these commands after deploying the fix to another environment. No frontend asset build or catalog migration is required.

### Storefront Multi-Template Layout & Category Straps (2026-10-25)
- **Goal**: Keep the existing `classic` storefront and add an editable `category_first` layout without deleting catalog data or changing the hero/LCP loading contract.
- **Architecture & Admin**:
  - `app/Enums/StorefrontTemplate.php` **(NEW)**: Defines `classic` and `category_first`.
  - `app/Services/StorefrontLayout.php` **(NEW)**: Resolves `settings.storefront_template` with a safe classic fallback, queries visible main categories, builds an active hierarchy in one query, and loads up to eight products per homepage section with category/media eager loading.
  - `app/Filament/Pages/StorefrontSettings.php` **(NEW)** + `resources/views/filament/pages/storefront-settings.blade.php` **(NEW)**: Layout selector at `/admin/storefront-settings`, using the existing page access controls and server-side option validation.
  - `app/Models/Setting.php`: Saving or deleting the template setting automatically calls `ResponseCache::clear()`, including changes through the generic Settings resource or `Setting::set()`.
  - `app/Filament/Resources/Settings/Schemas/SettingForm.php`: Uses a validated template select for the `storefront_template` key.
  - `lang/en/storefront.php` + `lang/ar/storefront.php` **(NEW)**: Admin labels, strap labels, view-all links, and empty-category messages; frontend strings use `trans_db()`.
- **Catalog Visibility & Data Preservation**:
  - `database/migrations/2026_10_25_000001_add_storefront_visibility_to_categories.php` **(NEW)**: Adds `show_in_header`, `show_on_home`, `sort_order`, and `home_sort_order`. The original `order` remains the classic ordering field.
  - `app/Models/Category.php`: Adds fillable fields/casts, clears response cache after save/delete/restore, and reuses prefetched active product counts when available.
  - `app/Filament/Resources/CategoryResource.php`: Adds independent header/home visibility and ordering controls. Only active root categories are eligible for header/home placement; children remain available through their parent straps.
  - `database/seeders/StorefrontCategoriesSeeder.php` **(NEW)**: Repeatable, additive setup. Header order: Body Care → Skin Care → Fragrance. Home order: Fragrance → Body Care → Skin Care. Reuses existing legacy slugs (`body_care`, `Skin_Care`, `fragrance`/`fragrances`, `msk`, `mkhmry`, `cleansers_toners`, and nested `90ml`/`240ml`). Creates missing required branches only; never deletes, renames, reparents, or moves existing products. An existing template choice is preserved.
  - `database/seeders/DatabaseSeeder.php`: Includes the new storefront setup for installations using the main seeder. Do not use the older destructive `CosmeticsCategoriesSeeder` for this update.
- **Storefront Views & Livewire**:
  - `resources/views/components/store/header.blade.php`: Classic retains its dropdown and nested mobile navigation. Category first places configured main-category links immediately after Products on desktop/mobile, with touch targets, accessible labels, contrast, and responsive spacing.
  - `app/Livewire/Store/Home.php`: Supplies category sections only for category first and eager-loads product media to avoid per-card media queries.
  - `resources/views/components/store/category-sections.blade.php` **(NEW)**: Reuses the existing product-grid/card styling. Only the first nonempty category section passes `:aboveFold="$loop->iteration <= 4"`; subsequent sections remain lazy-loaded.
  - `resources/views/livewire/store/home.blade.php`: Inserts category product sections immediately after the hero, ahead of offers/featured/new arrivals. Classic keeps the existing circular category grid and section order. Image priority falls back to the first available featured/new-arrivals section when configured categories have no products.
  - `resources/views/store/home.blade.php`: Adds the same category sections to the standalone homepage. Both homepage files retain the exact `<div class="hidden md:block">` hero wrapper.
  - `app/Livewire/Store/CategoryShow.php`: In category first, existing category URLs redirect to the product listing with the category slug; classic behavior remains unchanged.
  - `app/Livewire/Store/ProductList.php`: Adds validated one-click category selection, an active hierarchy and computed strap path, descendant-aware filtering, URL restoration, pagination reset, and root-scoped filter clearing. Selecting a child replaces its parent filter, preventing sibling products from leaking into the results. Reuses the prefetched hierarchy/product counts in the sidebar.
  - `resources/views/livewire/store/product-list.blade.php`: Adds horizontal accessible pills above the listing; selecting Women/Men reveals their own 90 ml/240 ml row. Additional categories/deeper levels are driven by the real hierarchy, without hardcoded IDs or names.
  - `resources/views/components/store/product-card.blade.php`: Inspected and unchanged; its `fetchpriority="high"` / `loading="lazy" decoding="async"` contract is retained.
  - `public/build/manifest.json`, `public/build/assets/app-CKyK4PgM.css`, and `public/build/assets/theme-tUSrUiqc.css`: Regenerated tracked Vite assets for the new Tailwind classes, replacing `app-CwEE5uRD.css` and `theme-B6Foz9L6.css`. No JavaScript or Filament theme source changes.
- **Tests & Verification**:
  - `tests/Feature/StorefrontTemplateTest.php` **(NEW)**: Covers template switching and cache invalidation, desktop/mobile navigation order, visibility, classic fallback, homepage ordering, first-four image priority, empty sections, category redirects, body/skin filtering, both fragrance branches/sizes, URL restoration, pagination, inactive/unrelated categories, existing filters, Arabic accessibility, standalone home, repeatable seeding, legacy-slug reuse, Filament validation, and bounded product-query counts.
  - `tests/Feature/LcpImageTest.php`: Fixes the pre-existing missing required `sku` fixture and checks the first product image specifically; the same product can correctly be lazy-loaded again in New Arrivals below the fold.
  - Regression commands: `php artisan test --filter=HeroSliderTest`, `php artisan test --filter=LcpImageTest`, `php artisan test --filter=CacheManagerTest`, and `php artisan test tests/Feature/StorefrontTemplateTest.php`.
  - `npm run build`: Successful; the ES2020 target and JavaScript bundle are unchanged.
  - Requested/new regression set: **25 tests passed (172 assertions)**. Real-browser checks confirmed the Arabic navigation, homepage section order, and Women → 90 ml filtering to seven existing products; classic was restored after preview.
  - Existing `tests/Feature/ProductFilteringTest.php` also passes unchanged: **14 tests (28 assertions)**. Total verified: **39 tests, 200 assertions**.
  - Local catalog verification: **32 categories before and after**; original IDs, names, slugs, parent relationships, classic order, active status, and all product-category assignments are identical. The additive migration and setup seeder have been applied locally; `classic` remains selected.
- **Deployment / Usage**:
  1. Run `php artisan migrate` and `php artisan db:seed --class=StorefrontCategoriesSeeder` on the target environment, then `npm run build` and `php artisan responsecache:clear` so cached HTML references the current assets.
  2. Open **System → Storefront layout** (`/admin/storefront-settings`) and select the desired template. Every save clears the seven-day response cache automatically.
  3. Edit root-category visibility/order in Categories. Arrange Women/Men as children of Fragrance and the size categories as their children; keep products assigned to the relevant leaves. Existing custom names and catalog relationships are preserved.

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

### LCP & Polyfill Hotfix — Post-Audit Tuning (2026-05-15)
- **Issue**: PageSpeed Mobile LCP stuck at 7.6s despite previous optimizations. Three specific problems:
  1. LCP image (first product card on mobile) was still rendering `loading="lazy"` instead of `fetchpriority="high"`.
  2. Images were still served as `.jpg` — `media:regenerate` needed on server.
  3. 8.6 KiB of Babel polyfills (`@babel/plugin-transform-classes`, `Object.assign`) in `app.js` bundle.
- **Investigation & Fixes**:

  **A. LCP Image `$aboveFold` Verification** (no code changes needed — logic verified correct):
  - `resources/views/livewire/store/home.blade.php` line 24: `:aboveFold="$loop->iteration <= 4"` — first 4 featured products correctly receive `aboveFold=true`.
  - `resources/views/components/store/product-card.blade.php` lines 31-35: `@if($aboveFold)` renders `fetchpriority="high"` without `loading="lazy"`; `@else` renders `loading="lazy" decoding="async"`.
  - `resources/views/components/cosmetics/product-card.blade.php`: Same verified pattern.
  - `resources/views/livewire/store/featured-products.blade.php` and `product-list.blade.php`: Same verified pattern.
  - **ROOT CAUSE**: The `spatie/laravel-responsecache` middleware caches the full HTML response for `/` with a 7-day TTL. The cached response was generated BEFORE the `$aboveFold` changes were deployed. **Fix**: The admin must clear the response cache via the Cache Manager page (`/admin/cache-manager` → "Clear Response Cache") or run `php artisan responsecache:clear` on the server. After cache flush, re-run PageSpeed — LCP should drop to ~2-3s.

  **B. Images Not WebP** (code verified — needs server action):
  - `app/Models/Product.php` lines 187, 196, 206: All three conversions (`thumbnail`, `card`, `preview`) use `->format('webp')` correctly.
  - **Server action required**: Run `php artisan media:regenerate` to convert existing images to WebP. New uploads will automatically use WebP.

  **C. Babel Polyfills in `app.js`** (code fix applied):
  - **Root cause**: Vite's default `build.target: 'modules'` transpiles syntax for browsers that support ES modules (Chrome 61+, Edge 16+, Safari 10+). These old Safari/Edge versions DON'T support ES2015+ features like native classes and `Object.assign`, so Vite injects polyfills/transpiled code.
  - **Fix in `vite.config.js`**: Added `build.target: 'es2020'` — Vite now targets browsers with full ES2020 support (classes, arrow functions, `Object.assign`, optional chaining, nullish coalescing). This eliminates all Babel-class polyfills and Object.assign helpers from the bundle.
  - **Compatible browsers**: Chrome 80+, Firefox 80+, Safari 14.1+, Edge 80+ — covers 96%+ of global traffic.
  - Must re-run `npm run build` after this change.

- **Tests**:
  - `tests/Feature/CacheManagerTest.php`: 3 tests pass — no regression.
  - `tests/Feature/HeroSliderTest.php`: Pre-existing SQLite migration issue (unrelated).

### A11y, Touch Targets, Contrast & Livewire Optimization (2026-05-15)
- **Goal**: Boost PageSpeed Accessibility score (82), fix Livewire JS payload, and add LCP verification test.
- **Issues Found & Fixed**:

  **A. Icon-Only Buttons Missing `aria-label` (6 fixes):**
  - `components/store/header.blade.php`:
    - Line 37: Mobile search toggle — added `aria-label="Search"` + `min-h-[44px] min-w-[44px]` for touch target.
    - Line 65: Cart button — added `aria-label="{{ trans_db('store.cart.shopping_cart') }}"` (was only `title`).
    - Line 96: Mobile menu toggle — added `aria-label="Menu"` + touch target classes.
    - Line 265: Subcategory toggle — added `aria-label="Toggle subcategories"`.
  - `components/cosmetics/navbar.blade.php`:
    - Line 63: Cart button — added `aria-label="Cart"` + touch target classes.
    - Line 84: User dropdown — added `aria-label="Account"`.
    - Line 121: Mobile menu toggle — added `aria-label="Toggle menu"` + touch target classes.

  **B. Touch Target Sizing (mobile usability):**
  - Added `min-h-[44px] min-w-[44px]` to all 5 icon-only buttons used on mobile (search toggle, cart, menu toggle in store header; cart and menu toggle in cosmetics navbar).
  - Ensures tap targets meet Google's 44×44 CSS pixel minimum for mobile.

  **C. Text Contrast Fix:**
  - `components/store/header.blade.php` line 278: Changed `text-gray-500` → `text-gray-600` on child category links (white background). Gray-500 on white fails WCAG AA (~2.8:1); gray-600 passes (~4.5:1).

  **D. Livewire JS Optimization:**
  - `config/livewire.php`:
    - Set `inject_assets` from `true` → `false` — prevents Livewire from auto-injecting duplicate `<script>` and `<style>` tags since all layouts already include `@livewireScripts` and `@livewireStyles` manually. Eliminates the duplicate byte overhead flagged by PageSpeed.
    - Restored `temporary_file_upload` config values to their previous working state (disk → `local`, rules → array, directory → `livewire-tmp`, middleware → `throttle:60,1`).

  **E. LCP Verification Test (NEW):**
  - `tests/Feature/LcpImageTest.php`: Creates a featured product, visits `/`, and asserts `fetchpriority="high"` is present in the HTML response. Fails if any code change ever reverts the `$aboveFold` logic or if `loading="lazy"` remains on the first product image.

- **Tests**:
  - `tests/Feature/CacheManagerTest.php`: 3 tests pass — no regression.
  - `tests/Feature/HeroSliderTest.php`: Pre-existing SQLite migration issue (unrelated).
  - `tests/Feature/LcpImageTest.php` **(NEW)**: Verifies first product card uses `fetchpriority="high"`.

### Center Combo Discount Cards on Homepage (2026-06-28)
- **Issue**: Combo discount cards in the "عروض الكومبو المميزة" section on the homepage were left-aligned when fewer cards existed than grid columns. Tailwind's `justify-center` had no effect with `1fr` grid tracks since they always fill the container width. Also needed 2-column portrait mobile matching the New Arrivals pattern.
- **Solution**:
  - `resources/views/components/store/offers-grid.blade.php`: Hybrid grid+flex approach —
    Container uses `grid grid-cols-2` for exact 2-column portrait mobile, then switches to `md:flex md:flex-wrap md:justify-center` at 768px+ so odd-card counts stay centered on larger screens.
    Card items use `w-full` in grid mode, then `md:flex-1 md:min-w-[280px] md:max-w-sm` in flex mode.
  - Mirrors the New Arrivals section's `grid-cols-2` portrait pattern while preserving the centering fix from the previous iteration.
- **Tests**: No test existed for offers-grid layout; all existing tests pre-date this change and are unrelated.

## Deprecated Code
- `mobile_image_path` field in Slider model/form/blade — replaced by auto-hide on mobile approach. Column remains in database but is no longer used.
