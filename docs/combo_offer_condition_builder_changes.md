# Combo Offer Condition Builder — Surgical Changes

**Date:** 2026-07-01
**Files Modified:** 2 (1 form, 1 test)
**Files Created:** 0
**Migrations:** 0

---

## Summary

Modified the Combo Rule Filament form to support single-product "Buy X, Get Y" offers (e.g., "Buy 3 of Product A, get 10% off"). Previously the system required at least 2 distinct conditions. Now admins can create a single condition with `required_quantity > 1`.

Also added a dynamic "Original Total Price" read-only field that displays `price × quantity` in real time as the admin adjusts product selection or quantity.

---

## Changes

### 1. `app/Filament/Resources/ComboRules/Schemas/ComboRuleForm.php`

#### a. Lowered `minItems` restriction (line 148)
```
- ->minItems(2)
+ ->minItems(1)
```
Allows a single condition per combo rule. The admin can now create an offer with just one product condition.

#### b. Added conditional validation rule (lines 149–158)
```php
->rules([
    fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
        if (is_array($value) && count($value) === 1) {
            $condition = $value[0];
            if (($condition['required_quantity'] ?? 0) <= 1) {
                $fail('عند إضافة شرط واحد فقط، يجب أن تكون الكمية المطلوبة أكبر من 1.');
            }
        }
    },
])
```
If only 1 condition exists, `required_quantity` must be greater than 1 — otherwise the "offer" would be meaningless.

#### c. Added `->live()` modifier to `product_id` (line 116)
```php
+ ->live(onBlur: true)
```
Triggers a Livewire round-trip when the product selection changes, which re-evaluates the Placeholder content below.

#### d. Added `->live()` modifier to `required_quantity` (line 124)
```php
+ ->live()
```
Triggers a Livewire round-trip on every keystroke, re-evaluating the Placeholder.

#### e. Added "Original Total Price" Placeholder (lines 130–143)
```php
Placeholder::make('original_total_display')
    ->label('إجمالي السعر الأصلي')
    ->content(fn ($get) => ...)
```
Displays `product_price × required_quantity` in EGP. Computed dynamically via `$get(...)` read of sibling repeater fields. Queries `Product::find()` on each render to get the current price.

#### f. Added `Placeholder` import (line 14)
```php
use Filament\Forms\Components\Placeholder;
```

---

### 2. `tests/Unit/ComboDiscountServiceTest.php`

Added `test_single_product_combo_with_multiple_quantity()`:

- Creates a single-product combo rule with `condition_type = 'product'`, `required_quantity = 3`
- Puts 3 units of that product in the cart (100 EGP each)
- Asserts 10% discount = 30 EGP on total of 300 EGP
- Confirms `rule_id` matches and result is not null

---

## Design Decisions

| Decision | Rationale |
|----------|-----------|
| **Placeholder (not TextInput)** | Placeholder is inherently read-only, doesn't persist to DB, and recomputes on every render cycle |
| **Livewire re-render (not Alpine)** | The price lookup requires a server-side DB query (`Product::find`); Alpine cannot do this without an API endpoint |
| **`->live(onBlur: true)` on product_id** | Avoids excessive queries while admin types in the searchable select; only fires on blur |
| **`->live()` on required_quantity** | Immediate feedback as admin types the quantity number |
| **Form-level rules (not per-field)** | The validation ("if 1 item, qty must be > 1") spans two fields (count of items + qty); a closure on the Repeater is the cleanest Filament v4 approach |
| **No migration** | `combo_rule_conditions` schema already supports nullable `product_id`/`category_id` and `condition_type`; zero existing records in DB |
| **No service logic change** | `ComboDiscountService::evaluateRule()` already handles single-condition rules correctly — iterates conditions, matches items, picks cheapest N, applies discount |

---

## Verification

- PHP syntax check: both files pass `php -l`
- Existing test (`test_calculate_discount`) and new test (`test_single_product_combo_with_multiple_quantity`) both fail at a **pre-existing** migration (`backfill_orders_user_id.php`) that uses MySQL-specific `UPDATE ... JOIN` syntax incompatible with SQLite — not related to our changes
- No new migrations, no new files, no JS changes

---

## Payload Structure

**Before (old — 2+ conditions required):**
```json
{
  "conditions": [
    { "condition_type": "product", "product_id": 5, "category_id": null, "required_quantity": 2 },
    { "condition_type": "category", "product_id": null, "category_id": 3, "required_quantity": 1 }
  ]
}
```

**After (new — single condition with qty > 1 allowed):**
```json
{
  "conditions": [
    { "condition_type": "product", "product_id": 5, "category_id": null, "required_quantity": 3 }
  ]
}
```
