# ⚠️ CRITICAL ISSUES — Combo Discount Implementation
## Addendum to: combo_discount_prompt.md
## Project: Violet E-Commerce — Laravel 12 / Filament v4 / Livewire v3

---

> **AGENT INSTRUCTIONS**
> This document lists **7 bugs and 4 missing features** in the combo discount implementation
> plan you received. Read this BEFORE writing any code. Each issue includes:
> - Exact location of the problem
> - Why it is wrong
> - The exact fix required
>
> Do NOT skip any issue. All 7 bugs must be fixed as part of the implementation.

---

## BUG #1 — N+1 Queries in `getConditionNameAttribute`

### Where
`app/Models/ComboRuleCondition.php` — the `getConditionNameAttribute()` accessor.

### What is wrong
The accessor fires a separate `Category::find()` or `Product::find()` query
for **every single condition object** that is accessed. If a page loads 5 combo
rules each with 3 conditions, that is 15 extra queries just to get names.

```php
// ❌ WRONG — fires a new query every time this property is accessed
public function getConditionNameAttribute(): string
{
    if ($this->condition_type === 'category') {
        return Category::find($this->condition_id)?->name ?? 'صنف محذوف';
    }
    return Product::find($this->condition_id)?->name ?? 'منتج محذوف';
}
```

### The fix
Every place that calls `$rule->conditions` MUST eager-load the related models.
Add a method on `ComboRule` that returns conditions with names pre-loaded:

```php
// In app/Models/ComboRule.php — add this scope:
public function conditionsWithNames(): HasMany
{
    return $this->hasMany(ComboRuleCondition::class)
                ->with(['category:id,name', 'product:id,name']);
}
```

Add the two relationships on `ComboRuleCondition`:

```php
// In app/Models/ComboRuleCondition.php — add these two relationships:
public function category(): BelongsTo
{
    return $this->belongsTo(Category::class, 'condition_id');
}

public function product(): BelongsTo
{
    return $this->belongsTo(Product::class, 'condition_id');
}

// Update the accessor to use the already-loaded relation:
public function getConditionNameAttribute(): string
{
    if ($this->condition_type === 'category') {
        return $this->relationLoaded('category') && $this->category
            ? $this->category->name
            : (Category::find($this->condition_id)?->name ?? 'صنف محذوف');
    }
    return $this->relationLoaded('product') && $this->product
        ? $this->product->name
        : (Product::find($this->condition_id)?->name ?? 'منتج محذوف');
}
```

Everywhere you load combo rules, use `->with('conditions.category', 'conditions.product')`.

---

## BUG #2 — Missing Cache in `combo-progress-hint.blade.php`

### Where
`resources/views/components/store/combo-progress-hint.blade.php` — the `@php` block at the top.

### What is wrong
The component runs `ComboRule::with('conditions')->where('is_active', true)->get()`
on **every product detail page load** with zero caching. The banner component
correctly uses `cache()->remember(...)` but this component does not.

```php
// ❌ WRONG — raw DB query on every /products/{slug} page load
$relevantRules = \App\Models\ComboRule::with('conditions')
    ->where('is_active', true)
    ->get()
    ->filter(...);
```

### The fix
Wrap the query in the same cache key used by the banner:

```php
// ✅ CORRECT — reuse the cached collection
$allActiveRules = cache()->remember('active_combo_rules', 600, function () {
    return \App\Models\ComboRule::with('conditions.category', 'conditions.product')
        ->where('is_active', true)
        ->get()
        ->filter(fn ($r) => $r->isCurrentlyActive())
        ->values();
});

$relevantRules = $allActiveRules->filter(function ($rule) use ($product) {
    return $rule->conditions->contains(function ($c) use ($product) {
        return ($c->condition_type === 'category' && $c->condition_id == $product->category_id)
            || ($c->condition_type === 'product'  && $c->condition_id == $product->id);
    });
});

if ($relevantRules->isEmpty()) return;
```

---

## BUG #3 — Cache Not Invalidated When Admin Saves or Deletes a Combo Rule

### Where
`app/Models/ComboRule.php` — the model has no cache-clearing hooks.

### What is wrong
The banner and progress hint both cache active combo rules under the key
`'active_combo_rules'` for 600 seconds. If an admin enables, disables, edits,
or deletes a rule, customers will see stale data for up to 10 minutes.

### The fix
Add a `booted()` method to `ComboRule` that clears the cache on any write:

```php
// In app/Models/ComboRule.php — add this static method:
protected static function booted(): void
{
    $clearCache = fn () => cache()->forget('active_combo_rules');

    static::saved($clearCache);
    static::deleted($clearCache);
    static::restored($clearCache); // for SoftDeletes restore
}
```

---

## BUG #4 — Wrong Cart Reading in `combo-progress-hint.blade.php`

### Where
`resources/views/components/store/combo-progress-hint.blade.php` — inside the `@php` block.

### What is wrong
The original plan reads the cart from the PHP session:

```php
// ❌ WRONG — this is for a session-only cart; Violet uses a DB cart
$cartCategoryIds = collect(session('cart', []))->pluck('category_id')->filter()->unique();
```

Violet's `CartService` stores carts in the `carts` and `cart_items` database
tables (for both guests and authenticated users). The session key `'cart'` does
NOT contain the cart items.

### The fix
Read the cart through `CartService` or directly from the DB:

```php
// ✅ CORRECT — read from DB via CartService
/** @var \App\Services\CartService $cartService */
$cartService = app(\App\Services\CartService::class);
$cartItems   = $cartService->getItems(); // returns Collection of CartItem with product relation

$cartCategoryIds = $cartItems
    ->pluck('product.category_id')
    ->filter()
    ->unique();
```

Confirm the exact method name by reading `app/Services/CartService.php` first.
Use whatever method that component already uses to get cart items.

---

## BUG #5 — Combo Not Recalculated After Cart Merge on Login

### Where
`app/Listeners/MergeCartOnLogin.php` (or wherever guest cart merge happens).

### What is wrong
When a guest with a qualifying combo cart logs in, their guest cart merges with
their DB cart via `MergeCartOnLogin`. After the merge, `recalculateTotals()`
needs to run so the combo discount is evaluated and stored in session.
The original plan does not mention this step.

### The fix
At the end of `MergeCartOnLogin::handle()`, call recalculate:

```php
// In app/Listeners/MergeCartOnLogin.php — at the end of handle():
app(\App\Services\CartService::class)->recalculateTotals();
```

Confirm the exact method signature from `CartService.php` before adding this.

---

## BUG #6 — N+1 in Filament Table (`conditions_summary` column)

### Where
`app/Filament/Resources/ComboRuleResource.php` — inside `table()`.

### What is wrong
The `conditions_summary` column uses `getStateUsing` which accesses
`$record->conditions`. If `conditions` is not eager-loaded in the table query,
Filament fires one query per row to load conditions.

```php
// ❌ WRONG — triggers N+1 if conditions not eager-loaded
Tables\Columns\TextColumn::make('conditions_summary')
    ->getStateUsing(fn ($record) =>
        $record->conditions->map(fn ($c) => $c->condition_name)->implode(' + ')
    ),
```

### The fix
Override `getEloquentQuery()` in the Resource to eager-load conditions:

```php
// In app/Filament/Resources/ComboRuleResource.php — add this method:
public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
{
    return parent::getEloquentQuery()
        ->with(['conditions.category', 'conditions.product']);
}
```

---

## BUG #7 — No Decrement of `uses_count` When Order Is Returned

### Where
`app/Services/ReturnService.php` — inside the method that completes a return
(likely `approve()` or `complete()`).

### What is wrong
The plan calls `$rule->increment('uses_count')` when an order is placed.
But when that order is returned and completed, `uses_count` is never decremented
and the `combo_rule_usages` record is never removed or flagged. This means
a rule with `max_uses = 100` will be exhausted faster than it should be.

### The fix
In `ReturnService`, after a return is marked as `completed`, check if the
original order had a combo rule and reverse the usage:

```php
// In app/Services/ReturnService.php — inside complete() or wherever
// return status changes to 'completed':

if ($order->combo_rule_id && $order->combo_discount_amount > 0) {
    // Decrement uses_count (floor at 0)
    \App\Models\ComboRule::where('id', $order->combo_rule_id)
        ->where('uses_count', '>', 0)
        ->decrement('uses_count');

    // Delete or flag the usage record
    \App\Models\ComboRuleUsage::where('order_id', $order->id)
        ->where('combo_rule_id', $order->combo_rule_id)
        ->delete();
}
```

---

## MISSING FEATURE #1 — Cache Invalidation for `ComboRuleCondition` Changes

### Why
When an admin edits the **conditions** of a rule (not the rule itself), the
`ComboRule::saved()` hook fires but only if the rule model itself is dirty.
Changes to child `ComboRuleCondition` records through the Filament Repeater
save the conditions separately and do NOT touch the parent `ComboRule` record.

### The fix
Add a `booted()` hook on `ComboRuleCondition` as well:

```php
// In app/Models/ComboRuleCondition.php:
protected static function booted(): void
{
    $clearCache = fn () => cache()->forget('active_combo_rules');
    static::saved($clearCache);
    static::deleted($clearCache);
}
```

---

## MISSING FEATURE #2 — Unit Tests for `ComboDiscountService`

### Why
`ComboDiscountService::evaluate()` contains the most critical business logic
in this feature. It must be tested with PHPUnit before going to production.

### Minimum required tests
Create `tests/Unit/ComboDiscountServiceTest.php` and cover these cases:

```
1. Returns discount = 0 when cart is empty
2. Returns discount = 0 when no active combo rules exist
3. Returns discount = 0 when cart has only 1 of 2 required categories
4. Returns correct percentage discount when all conditions are met
5. Returns correct fixed discount when all conditions are met
6. Returns the HIGHEST discount when multiple rules match
7. Returns discount = 0 when rule is inactive (is_active = false)
8. Returns discount = 0 when rule has expired (ends_at in the past)
9. Returns discount = 0 when rule has reached max_uses
10. Handles condition_type = 'product' correctly (not just 'category')
```

Use `ComboRule::factory()` and `ComboRuleCondition::factory()`. Follow the
pattern in `tests/Unit/BatchServiceTest.php` as a style reference.

---

## MISSING FEATURE #3 — Order Confirmation Email Must Show Combo Discount

### Why
The order confirmation email template (`order-confirmation.html`) currently
shows: subtotal, coupon discount (if any), shipping, total.
If a combo discount was applied, customers will not see it in their email,
which creates confusion.

### The fix
In `EmailTemplateService` or wherever the order confirmation variables are
built, add the combo discount variable:

```php
// Add to the variables array passed to the email template:
'combo_discount_amount' => $order->combo_discount_amount > 0
    ? number_format($order->combo_discount_amount, 2)
    : null,
'combo_rule_name' => $order->comboRule?->name,
```

In `order-confirmation.html`, add a conditional row in the totals table:

```html
{{-- Add after coupon discount row, before shipping --}}
@if($combo_discount_amount)
<tr>
  <td>عرض {{ $combo_rule_name }} 🎁</td>
  <td>- {{ $combo_discount_amount }} ج.م</td>
</tr>
@endif
```

---

## MISSING FEATURE #4 — Seeder for Testing

### Why
Without a seeder, testing the feature manually requires navigating to
`/admin/combo-rules/create`, filling the form, and repeating for each
test scenario. A seeder enables fast `php artisan db:seed` testing.

### Create `database/seeders/ComboRuleSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ComboRule;
use App\Models\ComboRuleCondition;
use Illuminate\Database\Seeder;

class ComboRuleSeeder extends Seeder
{
    public function run(): void
    {
        // Requires at least 3 categories to exist (created by other seeders)
        $categories = Category::limit(3)->pluck('id');

        if ($categories->count() < 2) {
            $this->command->warn('ComboRuleSeeder: Not enough categories. Skipping.');
            return;
        }

        $rule = ComboRule::create([
            'name'           => 'كومبو تجريبي — اشترِ من 2 أصناف ووفّر 15%',
            'description'    => 'اشترِ أي منتج من الصنفين المحددين واحصل على خصم 15%',
            'discount_type'  => 'percentage',
            'discount_value' => 15,
            'is_active'      => true,
            'starts_at'      => null,
            'ends_at'        => null,
            'max_uses'       => null,
        ]);

        foreach ($categories->take(2) as $categoryId) {
            ComboRuleCondition::create([
                'combo_rule_id'  => $rule->id,
                'condition_type' => 'category',
                'condition_id'   => $categoryId,
                'min_quantity'   => 1,
            ]);
        }

        $this->command->info("ComboRuleSeeder: Created rule [{$rule->name}]");
    }
}
```

Add to `database/seeders/DatabaseSeeder.php`:
```php
$this->call(ComboRuleSeeder::class);
```

---

## IMPLEMENTATION ORDER (follow this sequence)

```
1.  Database migration        — create_combo_rules_tables + alter orders
2.  Models                    — ComboRule, ComboRuleCondition (with fix #1), ComboRuleUsage
3.  Model booted() hooks      — fix #3 on ComboRule + missing feature #1 on ComboRuleCondition
4.  ComboDiscountService      — as written in the plan (no changes needed)
5.  CartService integration   — add recalculate call + fix #4 (correct cart reading)
6.  MergeCartOnLogin fix      — fix #5
7.  OrderService integration  — as written in the plan
8.  ReturnService fix         — fix #7
9.  Filament Resource         — ComboRuleResource with fix #6 (getEloquentQuery)
10. Blade components          — combo-offer-banner + combo-progress-hint with fix #2
11. Cart page blade           — add banner + hint + summary line
12. Email template            — missing feature #3
13. Translations              — lang/ar + lang/en as in the plan
14. AppServiceProvider        — register singleton
15. ComboRuleSeeder           — missing feature #4
16. Unit tests                — missing feature #2
17. php artisan migrate + cache:clear + config:clear + view:clear
```

---

## FILES TO READ BEFORE WRITING ANY CODE

Read these files completely before touching CartService or OrderService:

- `app/Services/CartService.php` — understand `recalculateTotals()` signature and cart item structure
- `app/Services/CouponService.php` — follow this exact pattern for ComboDiscountService
- `app/Services/ReturnService.php` — find the correct method to hook into for Bug #7
- `app/Listeners/MergeCartOnLogin.php` — find where to add the recalculate call for Bug #5
- `app/Filament/Resources/CouponResource.php` — use as style reference for ComboRuleResource

---

*Addendum prepared for: Violet E-Commerce — Combo Discount Feature*
*All issues must be resolved before submitting a PR.*
