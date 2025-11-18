# Task 9.5 — Quick Test Guide

## Start Server

```powershell
# Use PHP 8.3+ (Laravel 12 requirement)
C:\server\bin\php\php-8.3.24-Win32-vs16-x64\php.exe artisan serve
```

Then open: http://localhost:8000

## Test Scenarios

### 1. Product Listing → Add to Cart (CRITICAL)
✅ **Steps**:
1. Go to http://localhost:8000/products
2. Click "Add to Cart" on any product
3. **Watch for**:
   - Button shows spinner and "Adding..." text
   - Button disables (no double-clicks)
   - Green toast appears: "تمت إضافة المنتج للسلة"
   - Slide-over opens from right
   - Header cart counter updates (e.g., 0 → 1)

✅ **Expected Result**: All 5 visual feedbacks happen

### 2. Slide-over Cart
✅ **Steps**:
1. After adding item, slide-over should be open
2. Click "+/-" buttons to adjust quantity
3. Click "Remove" (red button)
4. **Watch for**: Toast confirmation, item disappears

### 3. Full Cart Page
✅ **Steps**:
1. Add 2-3 products
2. Navigate to http://localhost:8000/cart
3. **Verify**:
   - All items listed with images
   - Quantity controls work
   - Subtotal, Shipping, VAT, Total all calculate
   - "Checkout" button present

### 4. Guest Persistence
✅ **Steps**:
1. Open incognito/private window
2. Add items to cart
3. Refresh page
4. **Verify**: Cart counter still shows items (cookie persists)

### 5. Merge on Login (Advanced)
✅ **Steps**:
1. As guest, add "Product A"
2. Login (use existing user account)
3. Check cart
4. **Verify**: Guest items now in user cart

## Database Verification

```powershell
# Check carts table
C:\server\bin\php\php-8.3.24-Win32-vs16-x64\php.exe artisan tinker
```

Then in Tinker:
```php
// See all carts
\App\Models\Cart::with('items.product')->get();

// Count guest carts
\App\Models\Cart::whereNull('user_id')->count();

// Count user carts
\App\Models\Cart::whereNotNull('user_id')->count();

// See cart items
\App\Models\CartItem::with('product')->get();
```

## Debugging Tools

### Check Livewire Events (Browser Console)
```js
// Listen to all cart events
window.addEventListener('cart-updated', (e) => console.log('Cart updated:', e));
window.addEventListener('cart-count-updated', (e) => console.log('Count:', e.detail.count));
window.addEventListener('show-toast', (e) => console.log('Toast:', e.detail.message));
```

### Check Cookie
Open DevTools → Application → Cookies → http://localhost:8000
- Find: `cart_session_id` (should be a UUID like `a1b2c3d4-...`)
- Expires: ~30 days from now

## Troubleshooting

### Issue: Button doesn't respond
**Fix**: Open browser console, look for JavaScript errors. Ensure Alpine.js loaded.

### Issue: No toast appears
**Fix**: Check `store.blade.php` has toast listener script. Check `#toast-container` exists.

### Issue: Counter doesn't update
**Fix**: Header must have Alpine listener for `cart-count-updated` event.

### Issue: "Cart not found"
**Fix**: Check database has `carts` and `cart_items` tables:
```powershell
C:\server\bin\php\php-8.3.24-Win32-vs16-x64\php.exe artisan migrate:status | Select-String cart
```

## Success Criteria
- [ ] Button shows loading state ✅
- [ ] Toast notification appears ✅
- [ ] Slide-over opens automatically ✅
- [ ] Header counter updates ✅
- [ ] Database records created ✅
- [ ] No dead clicks (every click = action) ✅
- [ ] Cart persists on refresh ✅

## Performance Check
- Open Network tab in DevTools
- Click "Add to Cart"
- **Expected**: 1 Livewire AJAX call (~50-100ms response)
- **Expected**: 3 SQL queries (cart + items + products with media)

All checks passing = **Task 9.5 Complete** 🎉
