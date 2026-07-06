# Combo Offer Fixes — July 2026

## Overview

This document describes the surgical changes made to the Combo Offer (ComboRule) module
in the Admin Panel and Storefront to fix pricing logic and improve UX.

## Changes

### 1. Datatable: "View on Storefront" Link

**File:** `app/Filament/Resources/ComboRules/Tables/ComboRulesTable.php`

Added a `TextColumn` with an external-link icon that links to the combo's public landing
page (`route('combo.show', ['slug' => $record->slug])`), opening in a new tab.
Visible only when the record has a slug.

### 2. Filament Form: Use Active Sale Price

**File:** `app/Filament/Resources/ComboRules/Schemas/ComboRuleForm.php`

**Problem:** The `original_total_display` Placeholder used `$product->price` (regular price)
instead of `$product->final_price` (which returns `sale_price ?? price`). When a product had
an active sale, the form displayed inflated "original total" values.

**Fix:** Changed `$product->price` → `$product->final_price` in the Placeholder content callback.
If the product is on sale (`$product->is_on_sale`), the display now appends
"(سعر القطعة بعد الخصم: X EGP)".

### 3. Filament Form: Sale Hint on Product Select

**File:** `app/Filament/Resources/ComboRules/Schemas/ComboRuleForm.php`

Added `->hint()` and `->hintColor()` to the `product_id` Select. When the selected product
is on sale, a warning-colored hint appears below the dropdown: "⚠️ هذا المنتج عليه خصم حالي: X EGP".

### 4. Filament Form: Dynamic Sale Warning Alert

**File:** `app/Filament/Resources/ComboRules/Schemas/ComboRuleForm.php`

Added a `Placeholder` (rendered as HTML) immediately after the `product_id` Select that
shows an amber warning box when the selected product has an active discount:

```
⚠️ تنبيه: هذا المنتج عليه خصم حاليًا.
سعر البيع الحالي: X EGP (بدلاً من Y EGP)
التوفير في هذا الشرط: Z EGP
```

The warning is dynamic — it appears/disappears as the product selection changes.

### 5. Combo Landing Page: Enriched Condition Data

**File:** `app/Livewire/Store/ComboLandingPage.php`

Added `regular_price` (float) and `is_on_sale` (bool) fields to both product-type and
category-type condition data payloads. These are exposed to the Blade view for UI rendering.

### 6. Combo Landing Page: Sale Badges on Storefront

**File:** `resources/views/livewire/store/combo-landing-page.blade.php`

- **Product-type:** When `is_on_sale` is true, the product price now shows:
  - Strikethrough regular price
  - Sale price in red
  - "خصم" badge
- **Category-type:** Each product in the selection grid shows the same strikethrough + sale
  price treatment when on sale.

## Files Modified

| File | Change Type |
|------|-------------|
| `app/Filament/Resources/ComboRules/Tables/ComboRulesTable.php` | Added column |
| `app/Filament/Resources/ComboRules/Schemas/ComboRuleForm.php` | Fixed price logic, added hints & warnings |
| `app/Livewire/Store/ComboLandingPage.php` | Enriched data payload |
| `resources/views/livewire/store/combo-landing-page.blade.php` | Sale UI badges |
| `docs/combo/combo-fixes.md` | This file |

## Database

No migration needed. The pricing fix uses existing columns:
- `products.price` (regular price)
- `products.sale_price` (active sale price, nullable)
- `Product::final_price` accessor (returns `sale_price ?? price`)
- `Product::is_on_sale` accessor (returns `sale_price < price`)

## Verification

1. Create a Combo Offer with a product that has an active sale price
2. In the admin form, verify the product select shows the sale hint
3. Verify the amber warning box appears with correct sale info
4. Verify the "original total" uses the sale price
5. On the storefront landing page, verify the sale badge and strikethrough appear
6. Verify the total combo price calculation uses the active sale price
