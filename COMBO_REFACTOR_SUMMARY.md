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

---

## Phase 2: UI/UX Refinements

**Date:** 2026-07-11  
**Commit:** `fix(combo): resolve badge overlap, text truncation, and state reset on tier change`

Three QA bugs were identified after Phase 1 and corrected surgically:

---

### P2.1 Badge Overlap Fix

**Problem:** The "الأوفر" tier badge used `absolute -top-2.5 left-1/2 -translate-x-1/2` positioning, causing it to overlap the pricing text inside the card and destroy readability.

**Fix:** Removed all `absolute` positioning. The tier button (`<button>`) was changed from `relative p-4` to `flex flex-col items-center gap-1 p-4`. The badge `<span>` is now the **first child** in the flex column, flowing naturally above the pricing content without any overlap.

```blade
{{-- Before --}}
<button class="... relative p-4 ...">
    <span class="absolute -top-2.5 left-1/2 -translate-x-1/2 ...">الأوفر</span>
    <div>...pricing...</div>

{{-- After --}}
<button class="... flex flex-col items-center gap-1 p-4 ...">
    <span class="...">الأوفر</span>  {{-- naturally first, no overlap --}}
    <div>...pricing...</div>
```

---

### P2.2 Product Name Truncation Fix

**Problem:** Long Arabic product names (e.g. "فلور فايوليت أثير مخمرية") were being cut off by `truncate` inside a flexbox row with no overflow protection, making product names unreadable on small screens.

**Fix:** Applied the strict Flexbox constraint pattern across the product row:

| Container | Old Classes | New Classes |
|---|---|---|
| Outer row | `flex items-center gap-3` | `flex items-center gap-2` |
| Image | `w-14 h-14 ... shrink-0` | `w-12 h-12 ... flex-shrink-0` |
| Text | `flex-1 min-w-0` + `truncate` | `flex-1 min-w-0` + **`break-words`** |
| Stepper | `flex items-center gap-1.5 shrink-0` | `w-24 flex-shrink-0 flex items-center justify-center gap-1.5` |

**Key rule:** `flex-shrink-0` on edges (image + stepper) prevents them from squeezing. `min-w-0` on the text container allows it to shrink below its natural content width. `break-words` lets the text wrap to a second line instead of overflowing.

---

### P2.3 State Preservation on Tier Change (Upgrade/Downgrade)

**Problem:** The original `wire:key="cat-cond-{{ $conditionId }}-{{ $selectedTierIndex }}"` caused Livewire to **destroy and recreate** the Alpine component on every tier change, wiping all user-selected quantities back to zero.

**Root cause:** Livewire's `wire:key` is equivalent to Vue's `:key` — a key change forces DOM destruction and reinitialisation of the Alpine component.

**Fix — Three coordinated changes:**

#### 1. Remove `$selectedTierIndex` from `wire:key`
```blade
{{-- Before --}}
wire:key="cat-cond-{{ $conditionId }}-{{ $selectedTierIndex }}"

{{-- After --}}
wire:key="cat-cond-{{ $conditionId }}"
```
The Alpine component now survives tier changes.

#### 2. Add `tierQuantities` array + `overflow` getter to Alpine `x-data`
```blade
@php
    $tierQuantitiesJson = json_encode(array_column($tiers, 'quantity'));
@endphp
```
```js
x-data="{
    limit: {{ $data['required_quantity'] }},
    tierQuantities: {{ $tierQuantitiesJson }},  // e.g. [5, 3, 1]

    get overflow() {
        return Math.max(0, this.total - this.limit);
    },
    ...
}"
```

#### 3. Add `$watch` in `x-init` to reactively update `limit`
```js
x-init="
    broadcast();
    $watch('$wire.selectedTierIndex', function(idx) {
        limit = tierQuantities[idx];
        broadcast();
    });
"
```
When the user selects a different tier, `$wire.selectedTierIndex` changes server-side. Alpine's `$watch` fires, updates `limit` to the new tier's quantity, and re-broadcasts the updated state.

**Upgrade scenario (3→5):** `limit` increases. Existing quantities (≤3) are within the new limit. User simply continues adding 2 more items.

**Downgrade scenario (5→3):** `limit` decreases. If `total > limit`, `overflow > 0`. Card header pill and progress bar turn **red**. The sticky bar shows an Arabic warning and the CTA remains disabled.

---

### P2.4 Overflow Warning in Sticky Bar (Downgrade)

**Problem:** No user feedback when a downgrade causes `total > limit`.

**Fix:** The sticky summary bar's `x-data` gained an `overflowQuantity` getter and `isReady()` was updated to guard against it:

```js
get overflowQuantity() {
    return Math.max(0, this.totalSelected - this.totalRequired);
},

isReady(wireSelections) {
    return this.overflowQuantity === 0 &&
           this.allCategoryFulfilled() &&
           this.allProductsFulfilled(wireSelections);
},
```

**UI:** When `overflowQuantity > 0`, the normal progress row is hidden and replaced with a localized Arabic warning:

```blade
<div x-show="overflowQuantity > 0" class="... bg-red-50 border border-red-200 ...">
    <span x-text="'اختياراتك الحالية تتجاوز العرض المختار. يرجى إزالة ' + overflowQuantity + ' قطعة.'"></span>
</div>
<div x-show="overflowQuantity === 0 && totalRequired > 0">
    ... normal progress ...
</div>
```

The tier-reset watcher (`$watch('$wire.selectedTierIndex', function(){ conditionStats = {}; })`) was **removed** from the sticky bar's `x-init` — it is no longer needed since Alpine components no longer reinitialise on tier change.

---

### P2.5 Card Visual States (3-way color coding)

The per-card progress pill and progress bar now support three states:

| State | Condition | Color |
|---|---|---|
| In progress | `total < limit` | Violet |
| Fulfilled | `total === limit` | Green |
| Overflow (downgrade) | `total > limit` | Red |

The success banner (`"اختياراتك مكتملة! 🎉"`) now uses `x-show="total === limit"` (strict equality) instead of `total >= limit`, so it does NOT appear during an overflow state.

---

### P2 Dry Run Trace

| Step | Action | State |
|---|---|---|
| 1 | Select Tier 1 (limit=3) | `limit=3, total=0` |
| 2 | Add 3 items | `total=3, overflow=0` → green pill, CTA active |
| 3 | Switch to Tier 2 (limit=5) | `$watch` fires → `limit=5`. `quantities` unchanged. `total=3, overflow=0` → violet pill |
| 4 | Add 2 more items | `total=5, overflow=0` → green pill, CTA active |
| 5 | Switch back to Tier 1 (limit=3) | `$watch` fires → `limit=3`. `quantities` unchanged. `total=5, overflow=2` → red pill, sticky bar warning shown, CTA disabled |
| 6 | Click `−` twice on any items | `total=3, overflow=0` → green pill, warning disappears, normal progress shows, CTA active |

