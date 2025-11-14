# Frontend Layout & Structure

This document describes the storefront layout structure for the Violet e-commerce platform.

## 📁 File Structure

```
resources/views/
├── layouts/
│   └── store.blade.php              # Main store layout
├── components/
│   └── store/
│       ├── header.blade.php         # Header with navigation
│       ├── footer.blade.php         # Footer with links
│       └── breadcrumbs.blade.php    # Breadcrumbs navigation
└── store/
    └── home.blade.php               # Home page

app/Http/Controllers/Store/
└── HomeController.php               # Home controller

routes/
└── web.php                          # Route: GET /
```

## 🎨 Branding Colors

### Violet (Primary)
- `violet-600`: #9333ea (Main brand)
- `violet-700`: #7e22ce (Hover)
- `violet-800`: #6b21a8 (Dark)

### Cream (Background)
- `cream-50`: #fefdfb (Page BG)
- `cream-100`: #fdfcf8 (Section BG)

## 🧩 Components

### 1. Main Layout
**File:** `resources/views/layouts/store.blade.php`

```blade
<x-layouts.store>
    {{-- Your page content --}}
</x-layouts.store>
```

**Features:**
- SEO meta tags
- Livewire integration
- Responsive structure
- Violet/Cream branding

### 2. Header
**File:** `resources/views/components/store/header.blade.php`

**Sections:**
- Top bar (contact info, language)
- Logo & search
- Navigation menu
- Mobile menu

**Icons:**
- User account
- Wishlist (with counter)
- Cart (with counter)

### 3. Footer
**File:** `resources/views/components/store/footer.blade.php`

**Sections:**
- Company info
- Quick links
- Customer service
- Contact & newsletter
- Social links
- Copyright

### 4. Breadcrumbs
**File:** `resources/views/components/store/breadcrumbs.blade.php`

**Usage:**
```blade
<x-store.breadcrumbs :items="[
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Products', 'url' => '/products'],
    ['label' => 'Product Name', 'url' => null]
]" />
```

## 📱 Responsive Breakpoints

- **Mobile:** < 640px (Hamburger menu)
- **Tablet:** 640px - 1024px (Partial layout)
- **Desktop:** > 1024px (Full layout)

## 🚀 Quick Start

### View the Home Page
```bash
# Make sure Vite assets are built
npm run build

# Or run dev server
npm run dev

# Visit
http://violet.test/
```

### Add New Store Page

1. Create view in `resources/views/store/`
2. Create controller in `app/Http/Controllers/Store/`
3. Add route in `routes/web.php`
4. Use layout:
```blade
<x-layouts.store>
    <x-store.breadcrumbs :items="[...]" />
    
    {{-- Your content --}}
</x-layouts.store>
```

## 🔧 JavaScript Functions

Available globally:
```javascript
updateCartCounter(3);        // Update cart badge
updateWishlistCounter(5);    // Update wishlist badge
toggleMobileMenu();          // Toggle mobile navigation
toggleMobileSearch();        // Toggle mobile search
```

## 📋 Component Props

### Breadcrumbs
```php
:items="[
    ['label' => 'Text', 'url' => '/path'],  // Link
    ['label' => 'Text', 'url' => null]      // Current page (no link)
]"
```

## 🎯 Navigation Links

### Main Menu
- Home: `/`
- Products: `/products`
- Categories: `/categories` (with mega menu)
- Offers: `/offers`
- About: `/about`
- Contact: `/contact`

### Footer Links
- Help Center: `/help`
- Shipping: `/shipping`
- Returns: `/returns`
- Track Order: `/track`
- FAQs: `/faq`
- Privacy: `/privacy`
- Terms: `/terms`
- Cookies: `/cookies`

## ✅ Testing

```bash
# Verify route
php artisan route:list --path=/

# Build assets
npm run build

# Clear cache
php artisan optimize:clear
```

## 📚 Documentation

See `docs/TASK_9_1_ACCEPTANCE_REPORT.md` for complete implementation details.

---

**Version:** 1.0  
**Date:** November 13, 2025  
**Task:** 9.1 - Frontend Layout & Structure
