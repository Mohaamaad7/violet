# AGENT TASK — Combo Discount Feature + Quantity Bundle Extension
# Project: Violet E-Commerce — Laravel 12 / Filament v4 / Livewire v3

---

## YOUR MISSION

Implement the Combo Discount feature for the Violet e-commerce project.
You have been given 4 reference documents. Read them ALL before writing a single line of code.

---

## REFERENCE DOCUMENTS (read in this order)

### Document 1 — `combo_discount_prompt__1_.md`
The original feature specification. Database design, service architecture, admin UI, blade components, translations. This is your primary blueprint.

### Document 2 — `combo_discount_issues_for_agent.md`
7 bugs and 4 missing features found in Document 1. Each bug has an exact fix. All 7 bugs must be fixed during implementation. Do not skip any.

### Document 3 — `implementation_plan.md`
Your predecessor agent read the actual project codebase and found 8 discrepancies between Document 1's assumptions and the real code. These corrections override Document 1 where they conflict. The 25-step implementation order at the bottom of this document is your execution sequence.

### Document 4 — `combo_discount_clarifications.md`
4 ambiguous points in Document 3 that have been clarified with exact code. These override Document 3 where they conflict.

---

## NEW REQUIREMENT — Offer Type Extension

After the 4 documents above were written, a new business requirement emerged that none of them cover. You must implement it as part of this task.

### What happened
A client needed to create this offer: "Buy 3 units of the same product and get a 4th for free (or get a fixed price of 105 EGP instead of 140 EGP)."

When they tried to create this in the admin panel, the system rejected it with:
**"أصناف العرض يجب أن يحتوي على الأقل على 2 عنصر"**

### Why it was rejected
The current system defines a combo as: "buy from at least 2 different categories."
The new requirement is: "buy a quantity of the same product/category."
These are two different offer models. The system only supports the first.

### The solution you must implement

Add an `offer_type` field to the `combo_rules` table with two possible values:

- `multi_category` — the existing behavior (buy from 2+ different categories)
- `quantity_bundle` — the new behavior (buy X units from the same category/product)

This affects exactly 5 places:

**1. Migration**
Add `offer_type` as an enum column to the `combo_rules` table.
Default value: `multi_category`.

**2. ComboRule Model**
Add `offer_type` to `$fillable` and add a cast.
Add a helper: `isQuantityBundle(): bool`.

**3. ComboDiscountService::evaluate()**
Current logic: checks if the cart contains at least one item from each required category.
New logic: if `offer_type === 'quantity_bundle'`, check if the cart contains >= `min_quantity` units from the required category instead.
Both paths return the same structure.

**4. ComboRuleResource (Filament admin form)**
Add a radio/select field for `offer_type` at the top of the form (before the conditions repeater).
When `quantity_bundle` is selected:
- Change the validation from "min 2 conditions" to "exactly 1 condition required"
- Show a helper text: "حدد الصنف أو المنتج وحدد الكمية المطلوبة في حقل min_quantity"

When `multi_category` is selected:
- Keep the existing validation (min 2 conditions)

**5. combo-progress-hint.blade.php**
When the matched rule is `quantity_bundle`, the hint message should say:
"أضف [X] قطعة من [category name] للحصول على [discount]"
instead of the multi-category message.

---

## EXECUTION ORDER

Follow the 25-step order in `implementation_plan.md` exactly.
Insert the new requirement steps at these positions:

- After step 1 (combo_rules migration): add `offer_type` column to the same migration
- After step 3 (ComboRule model): add `offer_type` cast and `isQuantityBundle()` helper
- Step 7 (ComboDiscountService): implement both `multi_category` and `quantity_bundle` logic
- Step 12 (Filament Resource): add `offer_type` radio + conditional validation
- Step 14 (combo-progress-hint): handle both offer types in the hint message

---

## BEFORE YOU WRITE ANY CODE

Read these files from the project:

1. `app/Services/CartService.php` — find the method that returns cart items with product relation
2. `app/Services/CouponService.php` — use as the style reference for ComboDiscountService
3. `app/Services/ReturnService.php` — find `processReturn()` to add the uses_count decrement
4. `app/Livewire/Store/CheckoutPage.php` — understand `recalculateTotal()` and `placeOrder()`
5. `app/Filament/Resources/Coupons/CouponResource.php` — use as style reference for ComboRuleResource

Do not guess method names. Read the files first.

---

## DONE WHEN

- [ ] All 25 steps in `implementation_plan.md` completed
- [ ] All 7 bugs from `combo_discount_issues_for_agent.md` fixed
- [ ] All 4 clarifications from `combo_discount_clarifications.md` applied
- [ ] `offer_type` field works in admin: creating a `quantity_bundle` offer with 1 condition passes validation
- [ ] `offer_type` field works in service: a cart with 3 units of the same category triggers the discount
- [ ] `php artisan test --filter=ComboDiscountServiceTest` passes (covers both offer types)
- [ ] `php artisan migrate` runs without errors
- [ ] `php artisan cache:clear && config:clear && view:clear` runs without errors
