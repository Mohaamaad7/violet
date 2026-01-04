# Facebook Pixel Integration Documentation

## 📋 Overview

This document describes the implementation of dynamic Facebook Pixel integration in the Violet e-commerce store. The Pixel ID is configurable via Admin Settings and tracks key events like PageView and Purchase.

---

## 🎯 Features Implemented

### 1. **Dynamic Pixel ID Management**
- Pixel ID is stored in the database (Settings table)
- Configurable via Filament Admin Panel
- No hardcoded values - fully dynamic

### 2. **Global Pixel Tracking**
- Facebook Pixel script injected in all store pages
- Automatic PageView tracking on every page load
- Only loads when Pixel ID is configured

### 3. **Purchase Event Tracking**
- Tracks successful orders on the order success page
- Sends detailed conversion data:
  - Order total amount
  - Currency (EGP)
  - Product IDs
  - Number of items

---

## 🏗️ Architecture

### Database Schema

**Settings Table:**
```sql
INSERT INTO settings (key, value, type, `group`)
VALUES ('facebook_pixel_id', NULL, 'string', 'tracking');
```

### Files Modified/Created

#### 1. **SettingForm.php** (Modified)
**Path:** `app/Filament/Resources/Settings/Schemas/SettingForm.php`

**Change:** Added 'tracking' to the group options:
```php
'tracking' => 'تتبع الأحداث',
```

#### 2. **facebook-pixel.blade.php** (New Component)
**Path:** `resources/views/components/analytics/facebook-pixel.blade.php`

**Purpose:** Reusable Blade component for Facebook Pixel script

**Code:**
```blade
@props(['pixelId' => null])

@if($pixelId)
<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{{ $pixelId }}');
fbq('track', 'PageView');
</script>
<noscript>
    <img height="1" width="1" style="display:none"
         src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"/>
</noscript>
<!-- End Meta Pixel Code -->
@endif
```

#### 3. **store.blade.php** (Modified)
**Path:** `resources/views/layouts/store.blade.php`

**Change:** Injected Facebook Pixel component in `<head>`:
```blade
{{-- Facebook Pixel --}}
<x-analytics.facebook-pixel :pixelId="setting('facebook_pixel_id')" />
```

#### 4. **order-success-page.blade.php** (Modified)
**Path:** `resources/views/livewire/store/order-success-page.blade.php`

**Change:** Added Purchase event tracking at the end:
```blade
@push('scripts')
{{-- Facebook Pixel Purchase Event --}}
<script>
    // Track Purchase Event
    if (typeof fbq !== 'undefined') {
        fbq('track', 'Purchase', {
            value: {{ $order->total }},
            currency: 'EGP',
            content_ids: @json($order->items->pluck('product_id')->toArray()),
            content_type: 'product',
            num_items: {{ $order->items->sum('quantity') }}
        });
    }
</script>
@endpush
```

#### 5. **Migration** (New)
**Path:** `database/migrations/2026_01_04_221526_add_facebook_pixel_id_setting.php`

**Purpose:** Add facebook_pixel_id setting to database

---

## 🔧 Configuration

### Admin Panel Setup

1. **Navigate to Settings:**
   - Admin Panel → System → Settings → Create New

2. **Add Facebook Pixel ID:**
   - **Key:** `facebook_pixel_id`
   - **Value:** Your Facebook Pixel ID (e.g., `123456789012345`)
   - **Type:** `string`
   - **Group:** `tracking`

3. **Save Setting**

### Alternative: Direct Database Insert

```sql
INSERT INTO settings (key, value, type, `group`, created_at, updated_at)
VALUES (
    'facebook_pixel_id',
    'YOUR_PIXEL_ID_HERE',
    'string',
    'tracking',
    NOW(),
    NOW()
);
```

---

## 🧪 Testing

### 1. **Verify Pixel Installation**

**Using Meta Pixel Helper Chrome Extension:**
1. Install: [Meta Pixel Helper](https://chrome.google.com/webstore/detail/meta-pixel-helper/fdgfkebogiimcoedlicjlajpkdmockpc)
2. Visit your store
3. Extension should show green icon with Pixel ID

### 2. **Test PageView Event**

```javascript
// Open browser console on any store page
console.log(fbq); // Should show function
```

**Expected:** Pixel fires on every page load

### 3. **Test Purchase Event**

**Steps:**
1. Complete a test order
2. Land on success page
3. Open browser console
4. Check Network tab for Facebook requests

**Expected:**
```javascript
// Purchase event payload
{
    value: 500.00,
    currency: 'EGP',
    content_ids: [1, 2, 3],
    content_type: 'product',
    num_items: 3
}
```

### 4. **Verify in Events Manager**

1. Go to [Facebook Events Manager](https://business.facebook.com/events_manager)
2. Select your Pixel
3. Check "Test Events" tab
4. Complete a test purchase
5. Event should appear in real-time

---

## 🛡️ Security & Best Practices

### ✅ **What We Did Right**

1. **No Hardcoded IDs:** Pixel ID is stored in database, not in code
2. **Null Safety:** Component only renders if `$pixelId` is set
3. **Type Safety:** Purchase event checks `typeof fbq !== 'undefined'`
4. **Clean Code:** Used Blade components for reusability

### 🔒 **Security Considerations**

- **Public Data:** Pixel ID is not sensitive (it's public in HTML anyway)
- **Server-Side Events:** For sensitive data, use Facebook Conversions API (not implemented yet)

---

## 📊 Events Tracked

### Current Implementation

| Event | Location | Data Sent |
|-------|----------|-----------|
| `PageView` | All pages | Basic page info |
| `Purchase` | Order success page | Order total, currency, product IDs, quantity |

### Future Enhancements

Potential events to add:

| Event | Trigger | Location |
|-------|---------|----------|
| `ViewContent` | Product view | Product detail page |
| `AddToCart` | Add to cart | Product listing/detail |
| `InitiateCheckout` | Start checkout | Checkout page |
| `AddPaymentInfo` | Payment method selected | Checkout |
| `Search` | Product search | Search results |

---

## 🔄 How It Works

### Flow Diagram

```
1. User visits store
   ↓
2. store.blade.php loads
   ↓
3. Calls setting('facebook_pixel_id')
   ↓
4. If Pixel ID exists:
   - Injects facebook-pixel component
   - Initializes fbq() function
   - Tracks PageView
   ↓
5. User completes purchase
   ↓
6. Redirected to order-success-page
   ↓
7. Purchase event script fires
   ↓
8. Sends conversion data to Facebook
```

---

## 🐛 Troubleshooting

### Issue: Pixel Not Loading

**Symptom:** No pixel detected by Meta Pixel Helper

**Solutions:**
1. Check setting value:
   ```php
   php artisan tinker
   >>> setting('facebook_pixel_id');
   ```
2. Clear cache:
   ```powershell
   php artisan view:clear
   php artisan cache:clear
   ```
3. Verify Pixel ID format (15-16 digits)

### Issue: Purchase Event Not Firing

**Symptom:** PageView works, but no Purchase event

**Solutions:**
1. Check browser console for errors
2. Verify `$order` variable is available in Livewire component
3. Check if `@push('scripts')` is supported in layout
4. Ensure order items have valid product IDs

### Issue: Duplicate PageView Events

**Symptom:** Multiple PageView events on single page

**Solution:** Check if Pixel is included in multiple layouts (should only be in `store.blade.php`)

---

## 📝 Code Examples

### Programmatically Update Pixel ID

```php
use App\Models\Setting;

// Update Pixel ID
Setting::set('facebook_pixel_id', '123456789012345', 'string', 'tracking');

// Get current value
$pixelId = setting('facebook_pixel_id');
```

### Add Custom Event in Blade

```blade
@push('scripts')
<script>
    if (typeof fbq !== 'undefined') {
        fbq('track', 'ViewContent', {
            content_ids: ['{{ $product->id }}'],
            content_type: 'product',
            value: {{ $product->price }},
            currency: 'EGP'
        });
    }
</script>
@endpush
```

---

## 📚 References

- [Meta Pixel Documentation](https://developers.facebook.com/docs/meta-pixel)
- [Standard Events Reference](https://developers.facebook.com/docs/meta-pixel/reference#standard-events)
- [Meta Pixel Helper Extension](https://chrome.google.com/webstore/detail/meta-pixel-helper/fdgfkebogiimcoedlicjlajpkdmockpc)

---

## ✅ Acceptance Criteria

- [x] Facebook Pixel ID stored in database (Settings table)
- [x] Configurable via Admin Panel (Filament Settings Resource)
- [x] Pixel script injected in store layout head
- [x] PageView event tracked on all pages
- [x] Purchase event tracked on order success page
- [x] Purchase event includes: value, currency, content_ids, content_type
- [x] No errors when Pixel ID is null/empty
- [x] Code follows Laravel best practices
- [x] Documentation created

---

## 👨‍💻 Developer Notes

**Implementation Date:** January 4, 2026  
**Laravel Version:** 11.x  
**Filament Version:** 4.x  

**Key Decisions:**
1. Used `setting()` helper for clean access to Pixel ID
2. Created reusable Blade component for maintainability
3. Used `@push('scripts')` for page-specific tracking
4. Added `typeof fbq !== 'undefined'` check for safety

**Future Improvements:**
- Add more standard events (ViewContent, AddToCart, etc.)
- Implement Facebook Conversions API for server-side tracking
- Add admin toggle to enable/disable tracking
- Support multiple pixels for different audiences
