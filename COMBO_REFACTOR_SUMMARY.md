# Combo Landing Page — Mobile UX Refactor Summary

**Date:** 2026-07-11  
**Branch:** `agents-mobile-ux-refactor-alpine-blade`  
**Engineer:** Copilot (Staff-level surgical refactor)

---

## 1. Files Modified

| File | Change Type |
|---|---|
| `resources/views/livewire/store/combo-landing-page.blade.php` | **Full view rewrite** (presentation layer only) |
| `docs/combo/refactor-summary.md` | **New** — this document |

**Zero backend files were changed.** The PHP Livewire component (`app/Livewire/Store/ComboLandingPage.php`), all models, services, and migrations are untouched.

---

## 2. Problem Statement

The original mobile UX had two critical friction points:
1. **Offer cards** stacked vertically one-per-row, requiring heavy scrolling to compare options.
2. **Category-condition product selection** used a "Slot 1, Slot 2, …" loop — the full product grid was repeated for every slot. A user needing 5 items would scroll through the product grid 5 times. This was confusing and slow.

---

## 3. Technical Changes Made

### 3.1 Tier Selector — CSS Grid Refactor

**Before:**
```blade
<div class="grid grid-cols-1 sm:grid-cols-{{ min(count($tiers), 3) }} gap-4">
```

**After:**
```blade
<div class="grid grid-cols-2 gap-3">
    @foreach($tiers as $index => $tier)
        <button @class(['col-span-2' => $index === 0, ...])>
```

**`col-span-2` decision rationale:**  
The `ComboRule` model and `tiers` JSON array contain **no `is_featured` or `is_bestseller` column**. However, `ComboLandingPage::mount()` explicitly sorts tiers descending by quantity:
```php
usort($this->tiers, fn($a, $b) => $b['quantity'] <=> $a['quantity']);
```
Therefore **`$index === 0` always holds the highest-quantity / greatest-savings tier** — the objective "best value" offer. `col-span-2` in a 2-column grid makes this tier span the full row, creating a natural visual hierarchy without any invented or assumed property names. A "Best Value 🔥" badge is also added exclusively to index 0.

---

### 3.2 Smart Quantity Selector — Alpine.js Component

**Before:** For each category condition with N required items, Blade rendered the full product grid N times (once per "slot"), each with Livewire `wire:click` calls. This caused N full-page network round-trips for product selection.

**After:** A single Alpine.js `x-data` component per category condition. Products are rendered **once**. A `+` / `−` stepper next to each product allows fluid quantity selection. All state changes are pure client-side Alpine — zero network latency.

#### Alpine `x-data` State Object (per category condition)

```js
{
  conditionId: <int>,         // PHP condition ID
  limit: <int>,               // required_quantity from selected tier
  products: [...],            // PHP product array, embedded as JSON

  quantities: {},             // { productId: count }   — reactive
  variantSelections: {},      // { productId: variantId } — reactive

  get total() {               // sum of all quantities
    return Object.values(this.quantities).reduce((s, v) => s + v, 0);
  },

  isFulfilled() {             // true iff total===limit AND all variant requirements met
    if (this.total !== this.limit) return false;
    return !Object.entries(this.quantities).some(([pidStr, qty]) => {
      if (qty <= 0) return false;
      const product = this.products.find(p => p.id === parseInt(pidStr));
      return product && product.has_variants && !this.variantSelections[parseInt(pidStr)];
    });
  },

  increment(productId) { … }, // guarded by total >= limit
  decrement(productId) { … }, // cleans up variantSelections when qty reaches 0
  pickVariant(productId, variantId) { … },
  buildSlots() { … },         // converts quantities map → slot-indexed object
  broadcast() { … }           // dispatches combo:condition-updated to window
}
```

**`wire:key`**: Every category condition wrapper has `wire:key="cat-cond-{{ $conditionId }}-{{ $selectedTierIndex }}"`. When the user selects a different tier, Livewire re-renders and this key changes, causing Alpine to **destroy and reinitialize** the component with the new `limit`. This resets all quantities cleanly.

---

### 3.3 Sticky Summary Bar — New Alpine Component

A `fixed bottom-0 left-0 right-0 z-50` bar replaces the original `sticky` bar. It has its own `x-data` that:

- Listens for `combo:condition-updated` window events dispatched by each category condition
- Tracks `conditionStats: {}` — a map of `conditionId → {total, limit, fulfilled, slots}`
- Exposes reactive getters: `totalSelected`, `totalRequired`, `allCategoryFulfilled()`, `allProductsFulfilled($wire.selections)`

**Progress display:** `"{{ totalSelected }} / {{ totalRequired }} قطعة"` — live-updating via Alpine.

**CTA active/disabled logic:**
```js
// Disabled unless ALL category conditions fulfilled AND all product variant requirements met
x-bind:disabled="!allCategoryFulfilled() || !allProductsFulfilled($wire.selections) || processing"
```

**Tier change reset:** `x-init` installs a `$watch('$wire.selectedTierIndex', ...)` watcher. When tier changes, `conditionStats = {}` is cleared. The category condition components re-broadcast via `x-init` on re-mount, repopulating stats correctly.

---

### 3.4 Alpine → Livewire Bridge

The existing Livewire methods `addAllToCart()` and `buyNow()` read `$this->selections` directly — they accept no parameters. The bridge works as a two-step chain on the CTA `@click` handler:

```js
// Step 1: Build Alpine's final selections in the exact format Livewire expects
const catPayload = {};
Object.entries(conditionStats).forEach(([cidStr, stats]) => {
  catPayload[parseInt(cidStr)] = stats.slots; // slot-indexed: {0:{product_id,variant_id}, 1:{...}}
});

// Step 2: Merge with existing $wire.selections (preserves product-type condition selections)
const merged = Object.assign({}, $wire.selections || {}, catPayload);

// Step 3: Set Livewire property, THEN call method (chained promises = correct order)
$wire.set('selections', merged)
  .then(() => $wire.addAllToCart());  // or $wire.buyNow()
```

**Why this is safe:**
- `$wire.set()` returns a Promise that resolves after Livewire acknowledges the property update
- `addAllToCart()` is called in `.then()` — guaranteed to run AFTER selections are set server-side
- `Object.assign` preserves product-type condition selections already managed by Livewire
- No new Livewire methods, no hidden inputs, no new form submissions

**`buildSlots()` output format** (matches what `executeComboToCart()` expects):
```js
// Alpine output                // PHP interpretation
{                               // array:
  0: {product_id:1,variant_id:5},  // [0 => ['product_id'=>1,'variant_id'=>5],
  1: {product_id:1,variant_id:5},  //  1 => ['product_id'=>1,'variant_id'=>5],
  2: {product_id:2,variant_id:null} //  2 => ['product_id'=>2,'variant_id'=>null]]
}
```

---

## 4. UX Changes — Before vs After

| UX Aspect | Before | After |
|---|---|---|
| Tier layout | 1 column stacked | 2-column grid, best tier spans full width |
| Product selection | Full grid repeated N times (Slot 1, Slot 2…) | Products listed once, +/− counters |
| Network calls on +/− | 1 Livewire round-trip per click | **Zero** — pure Alpine |
| Over-selection | No client-side guard (server validates) | `+` buttons disabled when limit reached |
| Under-selection CTA | Always enabled (server-side validation on submit) | CTA disabled until exact quantity filled |
| Variant requirement | Shown after slot product pick | Appears inline under product when qty > 0 |
| Progress feedback | None | Live progress bar + pill counter + sticky bar progress |
| Submit bridge | `wire:click="addAllToCart"` direct | Alpine sets selections → then calls `addAllToCart` |

---

## 5. Constraint Compliance

| Constraint | Status |
|---|---|
| NO BACKEND CHANGES | ✅ Zero PHP changes |
| ZERO LATENCY | ✅ All +/−, disable, enable via Alpine |
| MOBILE-FIRST | ✅ Tailwind `grid-cols-2`, compact padding, fixed bottom bar |
| STRICT STATE HANDOFF | ✅ `$wire.set('selections', merged).then(() => $wire.addAllToCart())` |
| col-span-2 based on real data | ✅ `$index === 0` (sorted by quantity DESC in PHP) |
| No invented Livewire methods | ✅ Only `addAllToCart()` and `buyNow()` used |

---

## 6. Architecture Diagram

```
┌─────────────────────────────────────────────────────────┐
│  Livewire Component: ComboLandingPage                   │
│                                                         │
│  PHP State: $tiers, $conditionData, $selections,        │
│             $comboPrice, $selectedTierIndex             │
│                                                         │
│  ┌──────────────────────────────────────────────────┐   │
│  │ Alpine: Category Condition (conditionId=42)      │   │
│  │  x-data: {quantities, variantSelections, limit}  │   │
│  │  → dispatches combo:condition-updated to window  │   │
│  └─────────────────────┬────────────────────────────┘   │
│                        │ window event                   │
│  ┌─────────────────────▼────────────────────────────┐   │
│  │ Alpine: Sticky Bar                               │   │
│  │  x-data: {conditionStats, processing}            │   │
│  │  @combo:condition-updated.window → update stats  │   │
│  │  CTA @click → $wire.set(selections) → addAllToCart│  │
│  └──────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
```

---

## 7. Future Engineer Notes

1. **Adding a new tier field (e.g. `is_featured`):** If you add this field to the `tiers` JSON schema and the migration, update the `col-span-2` condition in the tier loop from `$index === 0` to `$tier['is_featured'] ?? false`. The sort order in `ComboLandingPage::mount()` can then be relaxed.

2. **Adding a new condition type:** If a new `condition_type` (e.g. `'bundle'`) is added, add a new `@elseif` branch in the conditions loop. The sticky bar's `categoryConditionCount` tracks all non-product conditions; adjust the PHP `@php` block accordingly.

3. **Multi-condition combos:** The architecture supports multiple category conditions in the same combo. Each gets its own Alpine component, each broadcasts to the same window event. The sticky bar aggregates all of them via `conditionStats`.

4. **Variant-only combos (all product-type):** `categoryConditionCount = 0` → `allCategoryFulfilled()` returns `true` immediately. The progress section is hidden. CTA is enabled once all product variant selections are made (tracked via `$wire.selections` reactivity).
