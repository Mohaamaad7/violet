# Task 4.4 — Guest Order Tracking

## ✅ Task Status: COMPLETED

**Date:** 2024-12-02  
**Phase:** 4 — Customer Experience  
**Developer:** AI Assistant (Claude)

---

## 📋 Requirements Met

| Requirement | Status | Details |
|-------------|--------|---------|
| Form input by order number + email/phone | ✅ | Two-field form with validation |
| Order status display with timeline | ✅ | Visual timeline with icons and dates |
| Guest order lookup | ✅ | Supports guest_email and guest_phone |
| Registered user order lookup | ✅ | Falls back to user email/phone |
| Bilingual support (EN/AR) | ✅ | Full translations added |

---

## 🗂 Files Created

### Livewire Component
- `app/Livewire/Store/TrackOrder.php` — Main tracking component with:
  - Order lookup by order_number + email/phone
  - Guest order support (guest_email, guest_phone)
  - Registered user order support (user.email, user.phone)
  - Status timeline computation
  - Input validation and error handling

### Blade View
- `resources/views/livewire/store/track-order.blade.php`
  - Search form with order number and contact info fields
  - Error message display
  - Order summary card with gradient header
  - Visual status timeline with icons
  - Order items list with images
  - Price breakdown (subtotal, discount, shipping, tax, total)
  - Shipping address display
  - Order notes section
  - RTL/LTR support

### Tests
- `tests/Feature/TrackOrder/GuestTrackOrderTest.php` — 12 tests covering:
  - Page renders successfully
  - Guest can track by email
  - Guest can track by phone
  - Error when order not found
  - Validation for required fields
  - Registered user can track by email
  - Clear resets form
  - Order items display
  - Status timeline display
  - Security: wrong email shows error
  - Cancelled order display
  - Whitespace trimming

---

## 🗂 Files Modified

### Routes
- `routes/web.php`
  - Added: `GET /track-order` → `TrackOrder::class` named `track-order`

### Translations
- `lang/en/messages.php` — Added `track_order` array with 35+ keys
- `lang/ar/messages.php` — Added `track_order` array with Arabic translations

---

## 🏗 Architecture

### Component Flow
```
1. User visits /track-order
2. Enters order number + email OR phone
3. Component validates inputs
4. Searches orders table:
   - First: Guest orders (user_id = null) by guest_email/guest_phone
   - Fallback: Registered user orders by user.email/user.phone
5. If found: Display order details with timeline
6. If not found: Show error message
```

### Status Timeline Logic
```php
$statusOrder = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];

// For cancelled orders:
// Show all passed statuses + cancelled

// For normal orders:
// Show all statuses, mark completed ones, highlight current
```

### Security
- Order lookup requires BOTH order_number AND matching email/phone
- Cannot view orders without matching contact information
- No authentication required (public page for guest tracking)

---

## 🔍 Key Features

### 1. Dual Contact Method Support
```php
$order = Order::where('order_number', $orderNumber)
    ->where(function ($query) use ($contactInfo) {
        $query->where('guest_email', $contactInfo)
              ->orWhere('guest_phone', $contactInfo);
    })
    ->whereNull('user_id')
    ->first();
```

### 2. Visual Timeline
- 5 status steps: Pending → Confirmed → Processing → Shipped → Delivered
- Special handling for cancelled orders
- Icons: clock, check-circle, cog, truck, check-badge, x-circle
- Color coding: gray, blue, yellow, purple, green, red

### 3. Order Details Display
- Order number with gradient header
- Order date
- Status badges with colors
- Payment status
- Items count
- Total amount
- Order items with images
- Price breakdown
- Shipping address
- Order notes

---

## 🧪 Testing

### Run Tests
```bash
php artisan test --filter=GuestTrackOrderTest
```

### Manual Testing
1. Create a test order:
```php
php artisan tinker
$order = Order::factory()->create([
    'order_number' => 'TEST-001',
    'guest_email' => 'test@test.com',
    'guest_phone' => '01234567890',
    'user_id' => null,
    'status' => 'processing',
]);
```

2. Visit `/track-order`
3. Enter order number: `TEST-001`
4. Enter contact: `test@test.com` or `01234567890`
5. Click "Track Order"

---

## 📦 Dependencies

- **Models Used:**
  - `Order` — Main order data
  - `OrderItem` — Order line items
  - `OrderStatusHistory` — Status change history
  - `User` — For registered user order lookup

- **External:**
  - Heroicons (via Blade components)
  - TailwindCSS for styling

---

## 🌐 Routes

| Method | URI | Name | Controller |
|--------|-----|------|------------|
| GET | `/track-order` | `track-order` | `TrackOrder::class` |

---

## 🔤 Translations

### English Keys Added (`lang/en/messages.php`)
```php
'track_order' => [
    'title' => 'Track Your Order',
    'subtitle' => 'Enter your order number...',
    'order_number' => 'Order Number',
    'contact_info' => 'Email or Phone',
    'search' => 'Track Order',
    'not_found' => 'We couldn\'t find an order...',
    'timeline_title' => 'Order Timeline',
    'status' => [...],
    'payment' => [...],
    // ... 35+ keys total
]
```

### Arabic Keys Added (`lang/ar/messages.php`)
```php
'track_order' => [
    'title' => 'تتبع طلبك',
    'subtitle' => 'أدخل رقم الطلب والبريد الإلكتروني...',
    // ... full Arabic translations
]
```

---

## 📝 Notes

1. **No Authentication Required** — This is a public page allowing guests to track orders
2. **Security** — Requires matching order number AND contact info
3. **Fallback Support** — Works for both guest and registered user orders
4. **Timeline Computed Property** — Uses `getStatusTimelineProperty()` for reactive updates
5. **Status History** — Integrates with `OrderStatusHistory` model for accurate timestamps

---

## ✅ Acceptance Criteria

- [x] Public page accessible at `/track-order`
- [x] Search form with order number and email/phone fields
- [x] Guest orders trackable by guest_email or guest_phone
- [x] Registered user orders trackable by user.email
- [x] Visual status timeline with icons
- [x] Order details display (items, totals, address)
- [x] Error handling for not found orders
- [x] Bilingual support (EN/AR)
- [x] RTL/LTR layout support
- [x] Feature tests created
