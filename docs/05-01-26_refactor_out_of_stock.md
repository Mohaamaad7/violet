# Refactor "Out of Stock" UI Indicator

**Date:** 05-01-2026  
**Author:** AI Assistant (Antigravity)  
**Status:** ✅ Completed

---

## Goal

The previous "Out of Stock" indicator used a small red dot (`bg-red-400 rounded-full`), which was **ambiguous and unclear** to users. This change refactors the indicator to display a **clear, readable text badge** that explicitly states "Out of Stock" (or the localized equivalent), improving user experience and reducing confusion.

---

## Affected Files

| File Path | Description |
|-----------|-------------|
| `resources/views/components/store/product-card.blade.php` | Main product card component used throughout the store |

---

## Code Changes

### Old Code (Removed)

**Location:** Lines 95-100 in `product-card.blade.php`

```blade
{{-- Stock Indicator - Minimal --}}
@if($product->stock > 0)
    <span class="w-2 h-2 bg-green-500 rounded-full" title="{{ __('store.product_details.in_stock') }}"></span>
@else
    <span class="w-2 h-2 bg-red-400 rounded-full" title="{{ __('store.product.out_of_stock') }}"></span>
@endif
```

**Issues with the old approach:**
- Small 8x8px red dot was not immediately recognizable
- Required hover to see the tooltip - poor mobile UX
- Did not provide clear visual text indicating stock status
- Ambiguous meaning without tooltip context

---

### New Code (Added)

**Location:** Lines 95-100 in `product-card.blade.php`

```blade
{{-- Stock Indicator --}}
@if($product->stock > 0)
    <span class="w-2 h-2 bg-green-500 rounded-full" title="{{ __('store.product_details.in_stock') }}"></span>
@else
    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">{{ __('store.out_of_stock') }}</span>
@endif
```

**New Tailwind CSS Classes:**

| Class | Purpose |
|-------|---------|
| `bg-red-100` | Light red background for visual contrast |
| `text-red-800` | Dark red text for readability |
| `text-xs` | Small font size (12px) to fit the card layout |
| `font-medium` | Semi-bold weight for emphasis |
| `px-2.5` | Horizontal padding (10px) |
| `py-0.5` | Vertical padding (2px) |
| `rounded` | Subtle rounded corners |

**Translation Key Used:** 
- `{{ __('store.out_of_stock') }}`
- English: "Out of Stock"
- Arabic: "غير متوفر"

---

## Verification

### Button Disabled for Out-of-Stock Items

The "Add to Cart" button is **already correctly disabled** for out-of-stock products. The existing code at lines 107-110 handles this:

```blade
<button @if($product->stock > 0)
    @click="adding = true; window.Livewire.dispatch('add-to-cart', { productId: {{ $product->id }}, quantity: 1 })"
@else disabled @endif :disabled="adding"
    class="... disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed ...">
```

**Verified behaviors:**
- ✅ Button is visually disabled (gray background, gray text)
- ✅ Button cursor shows `not-allowed` state
- ✅ Click events are prevented when stock is 0
- ✅ The new text badge clearly indicates "Out of Stock"

---

## Visual Comparison

| Before | After |
|--------|-------|
| 🔴 Small red dot (8x8px) | 📛 Text badge "Out of Stock" |
| Tooltip-dependent | Self-explanatory |
| Poor mobile UX | Mobile-friendly |

---

## Notes

- The filter sidebar indicators in `product-list.blade.php` (lines 292 and 561) were **intentionally NOT changed**. These are filter option indicators, not product status badges, and the small dots are appropriate for that context.
- The cosmetics theme (`components/cosmetics/product-card.blade.php`) already uses text labels for stock status, so no changes were needed there.
- Translation key `store.out_of_stock` already exists in both `lang/en/store.php` and `lang/ar/store.php`.
