# Task 9.5 — Hybrid Shopping Cart System: Acceptance Report

Date: 2025-11-18
Owner: Violet Project

## Overview
Implements a full DB-persistent cart for guests and users with UUID cookie tracking, merge-on-login, and real-time slide-over UI using Livewire 3 + Alpine.js.

## Scope
- Service: `app/Services/CartService.php`
- Livewire Components:
  - Mini Cart: `app/Livewire/Store/CartManager.php` + `resources/views/livewire/store/cart-manager.blade.php`
  - Full Cart Page: `app/Livewire/Store/CartPage.php` + `resources/views/livewire/store/cart-page.blade.php`
- Listener: `app/Listeners/MergeCartOnLogin.php` (registered in `AppServiceProvider`)
- UI Integrations:
  - Header counter + open slide-over
  - Product Details add-to-cart
  - Product Card add-to-cart (listing) with loading state
- Routes: `/cart`, `/checkout`

## Key Requirements — Status
- DB-only persistence: ✅ (no PHP session)
- Guest UUID cookie (30 days): ✅
- Merge guest cart → user on login: ✅
- Eager loading product media: ✅
- Zero dead clicks: ✅ (loading, toast, slide-over open, counter update)

## Event Flow
1. Product Card → dispatch `add-to-cart` (productId, quantity)
2. CartManager listens `#[On('add-to-cart')]` → calls CartService
3. On success → dispatch browser events:
   - `cart-updated`
   - `cart-count-updated` (detail: `{ count: <int> }`)
   - `show-toast` (success)
4. Slide-over opens automatically
5. Header badge updates via `window.addEventListener('cart-count-updated', ...)`

## Tests Performed

### A. Guest Flow
- Action: From product listing, click Add to Cart
- Expected:
  - Button shows spinner and disables (no dead click)
  - Slide-over opens with the item
  - Header counter increments
  - Toast: "تمت إضافة المنتج للسلة"
  - DB: `carts.session_id` populated, `cart_items` row inserted
- Result: ✅

### B. User Flow
- Action: Logged in user adds items
- Expected:
  - Uses `carts.user_id`
  - Items persistent across reloads
  - Header counter accurate
- Result: ✅

### C. Merge Flow
- Action: As guest, add item; then login
- Expected:
  - Items merged into user cart (respect stock)
  - Guest cart deleted; cookie cleared
- Result: ✅

### D. Stock Validation
- Action: Add beyond available stock
- Expected: Error toast, quantity not exceeded
- Result: ✅

### E. Full Cart Page
- Action: Go to `/cart`
- Expected:
  - Items listed with images (Spatie media thumbnails)
  - Quantity adjust works with validation
  - Remove item works
  - Summary shows Subtotal, Shipping, VAT (15%), Total
- Result: ✅

## Notes
- Header counter listens to Livewire v3 browser-dispatched events via `window.addEventListener`.
- Product card button uses Alpine `adding` state as a local loading UI since Cart action runs in a different Livewire component.
- Shipping rule: free over 200 SAR; VAT 15%.

## Follow-ups
- Implement Wishlist persistence (future task)
- Checkout page (payment, addresses) — separate scope

## How to Verify Locally

1) Start server (adjust PHP path if needed):
```powershell
C:\server\bin\php\php-8.1.10-Win32-vs16-x64\php.exe artisan serve
```

2) Navigate to product listing, click Add to Cart
- Observe spinner → toast → counter → slide-over

3) Visit `/cart` for full cart page

## Definition of Done
- All flows above pass ✅
- No dead clicks ✅
- DB persistence verified ✅
- UI is reactive and consistent ✅
