# Homepage Components - Quick Reference

**Task 9.2 Completion Date:** November 14, 2025

---

## 🎯 Components Overview

### 1. Hero Slider
**Component:** `<livewire:store.hero-slider />`  
**File:** `app/Livewire/Store/HeroSlider.php`  
**View:** `resources/views/livewire/store/hero-slider.blade.php`

**Usage:**
```blade
<livewire:store.hero-slider />
```

**Features:**
- Auto-fetches active sliders (`Slider::active()->get()`)
- Swiper.js carousel (fade effect, 5s autoplay)
- Responsive: 400px (mobile) → 600px (desktop)
- Fallback to static hero if no sliders
- Navigation + pagination (only if >1 slide)

---

### 2. Featured Products
**Component:** `<livewire:store.featured-products />`  
**File:** `app/Livewire/Store/FeaturedProducts.php`  
**View:** `resources/views/livewire/store/featured-products.blade.php`

**Usage:**
```blade
<livewire:store.featured-products />
```

**Features:**
- Displays up to 8 featured products
- Uses ProductCard component
- Empty state handling
- "View All Featured Products" button

**Query:**
```php
Product::with(['category', 'images'])
    ->where('is_featured', true)
    ->where('status', 'active')
    ->take(8)
    ->get()
```

---

### 3. Product Card
**Component:** `<x-store.product-card :product="$product" />`  
**File:** `resources/views/components/store/product-card.blade.php`

**Usage:**
```blade
@foreach($products as $product)
    <x-store.product-card :product="$product" />
@endforeach
```

**Props:**
- `product` (Product model instance)

**Features:**
- Primary image display
- Product name (linkable)
- Category badge (linkable)
- Price + sale price handling
- Discount percentage badge
- Stock status indicator
- Add to Cart button (Livewire event)
- Wishlist button
- Quick View on hover

---

### 4. Banners Section
**Component:** `<livewire:store.banners-section position="homepage_middle" />`  
**File:** `app/Livewire/Store/BannersSection.php`  
**View:** `resources/views/livewire/store/banners-section.blade.php`

**Usage:**
```blade
{{-- Homepage middle position --}}
<livewire:store.banners-section position="homepage_middle" />

{{-- Homepage top position --}}
<livewire:store.banners-section position="homepage_top" />

{{-- Sidebar position --}}
<livewire:store.banners-section position="sidebar_top" />
```

**Props:**
- `position` (string, default: 'homepage_middle')

**Features:**
- Adaptive layouts (1-4+ banners)
- Clickable with link_url
- Gradient overlays
- Hover scale effect

**Query:**
```php
Banner::active()
    ->position($this->position)
    ->get()
```

---

## 🎨 Brand Styling

### Colors

**Primary (Violet):**
- `violet-600`: Buttons, prices (`#9333ea`)
- `violet-700`: Hover states (`#7e22ce`)
- `violet-100`: Badges (`#f3e8ff`)

**Secondary (Cream):**
- `cream-50`: Backgrounds (`#fefdfb`)
- `cream-100`: Light backgrounds (`#fdfcf8`)

### Typography

**Headings (Serif):**
```blade
<h1 class="text-4xl font-serif font-bold">
    Heading Text
</h1>
```

**Body (Sans-serif):**
```blade
<p class="text-base font-sans">
    Body text (default)
</p>
```

**Fonts:**
- Serif: Playfair Display (headings)
- Sans: Figtree (body, buttons)

---

## 📱 Responsive Breakpoints

```javascript
{
    'sm': '640px',   // 2 columns
    'md': '768px',   // Tablet
    'lg': '1024px',  // 4 columns
}
```

**Grid Usage:**
```blade
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    {{-- Cards here --}}
</div>
```

---

## 🔧 Swiper.js Configuration

**Installed Version:** 11.1.15

**Import in `app.js`:**
```javascript
import Swiper from 'swiper/bundle';
import 'swiper/css/bundle';
window.Swiper = Swiper;
```

**Basic Configuration:**
```javascript
new Swiper('.hero-swiper', {
    loop: true,
    autoplay: { delay: 5000 },
    pagination: { el: '.swiper-pagination', clickable: true },
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
    effect: 'fade',
});
```

---

## 🛠️ Livewire Events

### Add to Cart
```blade
<button wire:click="$dispatch('add-to-cart', { productId: {{ $product->id }} })">
    Add to Cart
</button>
```

**Listening Component:**
```php
protected $listeners = ['add-to-cart' => 'handleAddToCart'];

public function handleAddToCart($productId)
{
    // Add to cart logic
}
```

### Add to Wishlist
```blade
<button wire:click="$dispatch('add-to-wishlist', { productId: {{ $product->id }} })">
    ♥
</button>
```

---

## 🗂️ Database Queries

### Get Active Sliders
```php
Slider::where('is_active', true)
    ->orderBy('order')
    ->get();
```

### Get Featured Products
```php
Product::with(['category', 'images'])
    ->where('is_featured', true)
    ->where('status', 'active')
    ->take(8)
    ->get();
```

### Get Banners by Position
```php
Banner::where('is_active', true)
    ->where('position', 'homepage_middle')
    ->get();
```

---

## 📋 Routes

### Homepage
```php
Route::get('/', [HomeController::class, 'index'])->name('home');
```

### Product Detail (Placeholder)
```php
Route::get('/products/{product:slug}', function() {
    return 'Product detail page (Coming soon)';
})->name('product.show');
```

### Category Page (Placeholder)
```php
Route::get('/categories/{category:slug}', function() {
    return 'Category page (Coming soon)';
})->name('category.show');
```

---

## 🚀 Build Commands

```bash
# Development (with hot reload)
npm run dev

# Production (optimized)
npm run build

# View compiled assets
ls public/build/assets/
```

---

## 🧪 Testing Checklist

### Visual Tests
- [ ] Hero slider displays and rotates
- [ ] Featured products grid shows (if products exist)
- [ ] Add to Cart buttons visible
- [ ] Brand colors applied (violet/cream)
- [ ] Serif font on headings
- [ ] Responsive on mobile/tablet/desktop

### Database Tests
```sql
-- Check for sliders
SELECT * FROM sliders WHERE is_active = 1;

-- Check for featured products
SELECT * FROM products WHERE is_featured = 1 AND status = 'active';

-- Check for banners
SELECT * FROM banners WHERE is_active = 1 AND position = 'homepage_middle';
```

---

## 📝 Next Steps (Task 9.3)

1. **Product Listing Page:** `/products` route
2. **Product Detail Page:** `/products/{slug}` route
3. **Category Page:** `/categories/{slug}` route
4. **Cart Functionality:** Implement cart storage
5. **Wishlist Functionality:** Implement wishlist storage

---

**Last Updated:** November 14, 2025  
**Maintained by:** Violet Dev Team
